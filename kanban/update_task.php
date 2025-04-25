<?php
require_once 'config.php';

ini_set('display_errors', 0);
header('Content-Type: application/json');

$response = ['success' => false, 'errors' => []];

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Méthode non autorisée', 405);
    }

    // Vérification CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        throw new Exception('Token CSRF invalide', 403);
    }

    $taskId = (int)($_POST['id'] ?? 0);
    if ($taskId <= 0) {
        throw new Exception('ID de tâche invalide', 400);
    }

    // Validation des données
    $data = [
        'title' => trim($_POST['title'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'status' => $_POST['status'] ?? 'todo',
        'due_date' => !empty($_POST['due_date']) ? $_POST['due_date'] : null
    ];

    // Validation
    if (empty($data['title'])) {
        $response['errors']['title'] = 'Le titre est obligatoire';
    }

    if (!in_array($data['status'], ['todo', 'inprogress', 'blocked', 'done'])) {
        $response['errors']['status'] = 'Statut invalide';
    }

    if (!empty($response['errors'])) {
        throw new Exception('Validation failed', 422);
    }

    // Mise à jour
    $stmt = $pdo->prepare("UPDATE tasks SET 
                          title = :title,
                          description = :description,
                          status = :status,
                          due_date = :due_date,
                          updated_at = NOW()
                          WHERE id = :id");

    $success = $stmt->execute([
        ':title' => $data['title'],
        ':description' => $data['description'],
        ':status' => $data['status'],
        ':due_date' => $data['due_date'],
        ':id' => $taskId
    ]);

    if ($success) {
        $response['success'] = true;
        $response['message'] = 'Tâche mise à jour';
    } else {
        throw new Exception('Échec de la mise à jour');
    }

} catch (PDOException $e) {
    $response['message'] = 'Erreur base de données';
    error_log('PDO Error: ' . $e->getMessage());
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    http_response_code($e->getCode() ?: 400);
}

echo json_encode($response);
exit;