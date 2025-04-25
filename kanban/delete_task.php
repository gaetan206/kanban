<?php
require_once 'config.php';
require_once 'functions.php';

header('Content-Type: application/json');

$response = ['success' => false];

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = $_POST['id'] ?? null;
        
        if ($id) {
            $success = deleteTask($pdo, $id);
            
            if ($success) {
                $response = ['success' => true];
            } else {
                throw new Exception('Erreur lors de la suppression');
            }
        }
    }
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>