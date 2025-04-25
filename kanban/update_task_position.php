<?php
require_once 'config.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

try {
    // 1. Vérification de la méthode HTTP
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Méthode non autorisée', 405);
    }

    // 2. Vérification CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        throw new Exception('Token CSRF invalide', 403);
    }

    // 3. Validation des données
    $taskId = (int)($_POST['task_id'] ?? 0);
    $newStatus = $_POST['status'] ?? '';
    $newPosition = (int)($_POST['position'] ?? 0);

    if ($taskId <= 0) throw new Exception('ID de tâche invalide', 400);
    if (!in_array($newStatus, ['todo', 'inprogress', 'blocked', 'done'])) {
        throw new Exception('Statut invalide', 400);
    }

    // 4. Début de transaction
    $pdo->beginTransaction();

    // 5. Récupération ancienne position
    $stmt = $pdo->prepare("SELECT status, position FROM tasks WHERE id = ? FOR UPDATE");
    $stmt->execute([$taskId]);
    $oldData = $stmt->fetch();

    if (!$oldData) throw new Exception('Tâche introuvable', 404);

    // 6. Réorganisation des positions
    if ($oldData['status'] !== $newStatus) {
        // Cas 1: Changement de colonne
        // a. Décale les tâches dans l'ancienne colonne
        $pdo->prepare("UPDATE tasks SET position = position - 1 
                      WHERE status = ? AND position > ?")
           ->execute([$oldData['status'], $oldData['position']]);

        // b. Fait de la place dans la nouvelle colonne
        $pdo->prepare("UPDATE tasks SET position = position + 1 
                      WHERE status = ? AND position >= ? AND id != ?")
           ->execute([$newStatus, $newPosition, $taskId]);
    } else {
        // Cas 2: Même colonne
        if ($newPosition > $oldData['position']) {
            // Déplacement vers le bas
            $pdo->prepare("UPDATE tasks SET position = position - 1 
                          WHERE status = ? AND position BETWEEN ? AND ? AND id != ?")
               ->execute([$newStatus, $oldData['position'] + 1, $newPosition, $taskId]);
        } else {
            // Déplacement vers le haut
            $pdo->prepare("UPDATE tasks SET position = position + 1 
                          WHERE status = ? AND position BETWEEN ? AND ? AND id != ?")
               ->execute([$newStatus, $newPosition, $oldData['position'] - 1, $taskId]);
        }
    }

    // 7. Mise à jour de la tâche
    $pdo->prepare("UPDATE tasks SET status = ?, position = ?, updated_at = NOW() 
                  WHERE id = ?")
       ->execute([$newStatus, $newPosition, $taskId]);

    // 8. Validation
    $pdo->commit();

    $response = ['success' => true];

} catch (PDOException $e) {
    $pdo->rollBack();
    $response['message'] = 'Erreur base de données: ' . $e->getMessage();
    error_log('PDO Error: ' . $e->getMessage());
} catch (Exception $e) {
    $pdo->rollBack();
    $response['message'] = $e->getMessage();
    http_response_code($e->getCode() ?: 400);
}

echo json_encode($response);