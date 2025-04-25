<?php
require_once 'config.php';

// Désactive l'affichage des erreurs HTML
ini_set('display_errors', 0);
header('Content-Type: application/json');

$response = ['success' => false];

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
        $taskId = (int)$_GET['id'];
        
        $stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = ?");
        $stmt->execute([$taskId]);
        $task = $stmt->fetch();
        
        if ($task) {
            // Formatage des données
            $response = [
                'success' => true,
                'task' => [
                    'id' => $task['id'],
                    'title' => $task['title'],
                    'description' => $task['description'],
                    'status' => $task['status'],
                    'due_date' => $task['due_date'] ? date('Y-m-d', strtotime($task['due_date'])) : null
                ]
            ];
        } else {
            $response['message'] = 'Tâche non trouvée';
        }
    } else {
        throw new Exception('Requête invalide', 400);
    }
} catch (PDOException $e) {
    $response['message'] = 'Erreur base de données';
    error_log('PDO Error: ' . $e->getMessage());
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    http_response_code($e->getCode() ?: 400);
}

// Envoi strictement au format JSON
echo json_encode($response);
exit;