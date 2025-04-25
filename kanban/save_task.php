<?php
require_once __DIR__.'/../config.php';

header('Content-Type: application/json');

$response = [
    'success' => false,
    'message' => '',
    'errors' => [],
    'task' => null
];

try {
    // 1. Vérification de la méthode HTTP
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Méthode non autorisée', 405);
    }

    // 2. Validation CSRF
    if (CSRF_PROTECTION && empty($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        throw new Exception('Token CSRF invalide', 403);
    }

    // 3. Récupération et nettoyage des données
    $taskId = isset($_POST['id']) ? (int)$_POST['id'] : null;
    $data = [
        'title' => trim($_POST['title'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'status' => $_POST['status'] ?? 'todo',
        'due_date' => !empty($_POST['due_date']) ? $_POST['due_date'] : null
    ];

    // 4. Validation des données
    $validationErrors = validateTaskData($data);
    if ($validationErrors !== true) {
        $response['errors'] = $validationErrors;
        throw new Exception('Données invalides', 422);
    }

    // 5. Formatage des données
    $formattedDueDate = $data['due_date'] ? date('Y-m-d H:i:s', strtotime($data['due_date'])) : null;

    // 6. Début de transaction
    $pdo->beginTransaction();

    // 7. Logique de création/mise à jour
    if ($taskId) {
        // MISE À JOUR EXISTANTE
        $stmt = $pdo->prepare("
            UPDATE tasks 
            SET 
                title = :title,
                description = :description,
                status = :status,
                due_date = :due_date,
                updated_at = NOW()
            WHERE id = :id
        ");

        $stmt->execute([
            ':title' => htmlspecialchars($data['title'], ENT_QUOTES),
            ':description' => htmlspecialchars($data['description'], ENT_QUOTES),
            ':status' => $data['status'],
            ':due_date' => $formattedDueDate,
            ':id' => $taskId
        ]);

        if ($stmt->rowCount() === 0) {
            throw new Exception('Aucune tâche trouvée avec cet ID', 404);
        }

        $message = 'Tâche mise à jour avec succès';
    } else {
        // CRÉATION NOUVELLE TÂCHE
        // Calcul de la position
        $stmt = $pdo->prepare("
            SELECT COALESCE(MAX(position), 0) + 1 
            FROM tasks 
            WHERE status = :status
        ");
        $stmt->execute([':status' => $data['status']]);
        $position = $stmt->fetchColumn();

        $stmt = $pdo->prepare("
            INSERT INTO tasks (
                title, 
                description, 
                status, 
                position, 
                due_date, 
                created_at, 
                updated_at
            ) VALUES (
                :title,
                :description,
                :status,
                :position,
                :due_date,
                NOW(),
                NOW()
            )
        ");

        $stmt->execute([
            ':title' => htmlspecialchars($data['title'], ENT_QUOTES),
            ':description' => htmlspecialchars($data['description'], ENT_QUOTES),
            ':status' => $data['status'],
            ':position' => $position,
            ':due_date' => $formattedDueDate
        ]);

        $taskId = $pdo->lastInsertId();
        $message = 'Tâche créée avec succès';
    }

    // 8. Récupération de la tâche complète
    $stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = ?");
    $stmt->execute([$taskId]);
    $task = $stmt->fetch(PDO::FETCH_ASSOC);

    // 9. Commit de la transaction
    $pdo->commit();

    // 10. Préparation de la réponse
    $response = [
        'success' => true,
        'message' => $message,
        'task' => $task
    ];

} catch (PDOException $e) {
    $pdo->rollBack();
    $response['message'] = 'Erreur de base de données';
    if (APP_DEBUG) {
        $response['error_details'] = $e->getMessage();
    }
    error_log('PDOException: ' . $e->getMessage());
    http_response_code(500);
} catch (Exception $e) {
    $pdo->rollBack();
    $response['message'] = $e->getMessage();
    http_response_code($e->getCode() ?: 400);
}

echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

/**
 * Valide les données de la tâche
 */
function validateTaskData($data) {
    $errors = [];

    // Validation titre
    if (empty($data['title'])) {
        $errors['title'] = 'Le titre est obligatoire';
    } elseif (strlen($data['title']) > 255) {
        $errors['title'] = 'Le titre ne doit pas dépasser 255 caractères';
    }

    // Validation description
    if (strlen($data['description']) > 2000) {
        $errors['description'] = 'La description ne doit pas dépasser 2000 caractères';
    }

    // Validation statut
    $allowedStatuses = ['todo', 'inprogress', 'blocked', 'done'];
    if (!in_array($data['status'], $allowedStatuses)) {
        $errors['status'] = 'Statut invalide';
    }

    // Validation date
    if ($data['due_date'] && !strtotime($data['due_date'])) {
        $errors['due_date'] = 'Format de date invalide';
    }

    return empty($errors) ? true : $errors;
}