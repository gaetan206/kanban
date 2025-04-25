<?php
require_once 'config.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

// Log des données reçues (pour débogage)
file_put_contents('debug.log', print_r($_POST, true), FILE_APPEND);


require_once __DIR__.'/config.php'; // Si config.php est dans le même dossier

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

try {
    // Vérification méthode et token CSRF
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception('Méthode non autorisée', 405);
    if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        throw new Exception('Token CSRF invalide', 403);
    }

    // Validation des données
    $required = ['title', 'status'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("Le champ $field est requis", 400);
        }
    }

    $title = trim($_POST['title']);
    $description = trim($_POST['description'] ?? '');
    $status = $_POST['status'];
    $dueDate = !empty($_POST['due_date']) ? $_POST['due_date'] : null;

    if (strlen($title) > 255) throw new Exception('Le titre est trop long (255 caractères max)', 400);
    if (strlen($description) > 2000) throw new Exception('La description est trop longue (2000 caractères max)', 400);

    // Calcul de la position
    $stmt = $pdo->prepare("SELECT COALESCE(MAX(position), 0) + 1 FROM tasks WHERE status = ?");
    $stmt->execute([$status]);
    $position = $stmt->fetchColumn();

    // Insertion
    $stmt = $pdo->prepare("
        INSERT INTO tasks (title, description, status, position, due_date) 
        VALUES (?, ?, ?, ?, ?)
    ");
    
    $success = $stmt->execute([
        htmlspecialchars($title),
        htmlspecialchars($description),
        $status,
        $position,
        $dueDate
    ]);

    if ($success) {
        $response = [
            'success' => true,
            'message' => 'Tâche créée avec succès',
            'task_id' => $pdo->lastInsertId()
        ];
    } else {
        throw new Exception('Échec de la création de la tâche');
    }

} catch (PDOException $e) {
    $response['message'] = 'Erreur base de données: ' . (APP_DEBUG ? $e->getMessage() : '');
    error_log('PDO Error: ' . $e->getMessage());
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>