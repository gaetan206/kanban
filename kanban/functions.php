<?php
require_once 'config.php';

/**
 * Récupère toutes les tâches triées par statut et position
 */
function getTasks($pdo) {
    $stmt = $pdo->query("
        SELECT * 
        FROM tasks 
        ORDER BY 
            FIELD(status, 'todo', 'inprogress', 'blocked', 'done'),
            position ASC
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Retourne la couleur associée à un statut
 */
function getStatusColor($status) {
    global $customColors;
    
    // Couleurs personnalisées (définies dans config.php)
    if (isset($customColors[$status])) {
        return $customColors[$status];
    }
    
    // Fallback aux couleurs Bootstrap
    $colors = [
        'todo'       => '#6c757d', // Bootstrap secondary
        'inprogress' => '#0d6efd', // Bootstrap primary
        'blocked'    => '#dc3545', // Bootstrap danger
        'done'       => '#198754'  // Bootstrap success
    ];
    
    return $colors[$status] ?? '#f8f9fa';
}

/**
 * Crée une nouvelle tâche
 */
function createTask($pdo, $title, $description, $status, $dueDate = null) {
    // Trouve la prochaine position disponible
    $stmt = $pdo->prepare("
        SELECT COALESCE(MAX(position), 0) + 1 
        FROM tasks 
        WHERE status = ?
    ");
    $stmt->execute([$status]);
    $position = $stmt->fetchColumn();

    // Insertion
    $stmt = $pdo->prepare("
        INSERT INTO tasks 
        (title, description, status, position, due_date, created_at, updated_at) 
        VALUES (?, ?, ?, ?, ?, NOW(), NOW())
    ");
    
    return $stmt->execute([
        htmlspecialchars($title),
        htmlspecialchars($description),
        $status,
        $position,
        $dueDate
    ]);
}

/**
 * Met à jour une tâche existante
 */
function updateTask($pdo, $id, $title, $description, $status, $dueDate = null) {
    $stmt = $pdo->prepare("
        UPDATE tasks 
        SET 
            title = ?,
            description = ?,
            status = ?,
            due_date = ?,
            updated_at = NOW()
        WHERE id = ?
    ");
    
    return $stmt->execute([
        htmlspecialchars($title),
        htmlspecialchars($description),
        $status,
        $dueDate,
        $id
    ]);
}

/**
 * Met à jour la position d'une tâche
 */
function updateTaskPosition($pdo, $taskId, $newStatus, $newPosition) {
    $pdo->beginTransaction();
    
    try {
        // 1. Récupère l'ancienne position et statut
        $stmt = $pdo->prepare("SELECT status, position FROM tasks WHERE id = ?");
        $stmt->execute([$taskId]);
        $task = $stmt->fetch();
        
        if (!$task) throw new Exception("Tâche introuvable");
        
        $oldStatus = $task['status'];
        $oldPosition = $task['position'];
        
        // 2. Si changement de colonne, décale les autres tâches
        if ($oldStatus !== $newStatus) {
            // Décale les tâches de l'ancienne colonne
            $stmt = $pdo->prepare("
                UPDATE tasks 
                SET position = position - 1 
                WHERE 
                    status = ? 
                    AND position > ?
            ");
            $stmt->execute([$oldStatus, $oldPosition]);
        }
        
        // 3. Décale les tâches de la nouvelle colonne
        $stmt = $pdo->prepare("
            UPDATE tasks 
            SET position = position + 1 
            WHERE 
                status = ? 
                AND position >= ?
                AND id != ?
        ");
        $stmt->execute([$newStatus, $newPosition, $taskId]);
        
        // 4. Met à jour la tâche
        $stmt = $pdo->prepare("
            UPDATE tasks 
            SET 
                status = ?,
                position = ?,
                updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$newStatus, $newPosition, $taskId]);
        
        $pdo->commit();
        return true;
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Erreur update position: " . $e->getMessage());
        return false;
    }
}

/**
 * Supprime une tâche
 */
function deleteTask($pdo, $id) {
    // 1. Récupère la position pour réorganiser après suppression
    $stmt = $pdo->prepare("SELECT status, position FROM tasks WHERE id = ?");
    $stmt->execute([$id]);
    $task = $stmt->fetch();
    
    if (!$task) return false;
    
    $pdo->beginTransaction();
    
    try {
        // 2. Supprime la tâche
        $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ?");
        $stmt->execute([$id]);
        
        // 3. Réorganise les positions
        $stmt = $pdo->prepare("
            UPDATE tasks 
            SET position = position - 1 
            WHERE 
                status = ? 
                AND position > ?
        ");
        $stmt->execute([$task['status'], $task['position']]);
        
        $pdo->commit();
        return true;
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Erreur suppression: " . $e->getMessage());
        return false;
    }
}

/**
 * Valide les données d'une tâche
 */
function validateTaskData($data) {
    $errors = [];
    
    if (empty(trim($data['title']))) {
        $errors['title'] = "Le titre est obligatoire";
    } elseif (strlen(trim($data['title'])) > 255) {
        $errors['title'] = "Le titre est trop long (255 caractères max)";
    }
    
    if (strlen(trim($data['description'] ?? '')) > 2000) {
        $errors['description'] = "La description est trop longue (2000 caractères max)";
    }
    
    $allowedStatuses = ['todo', 'inprogress', 'blocked', 'done'];
    if (!in_array($data['status'] ?? '', $allowedStatuses)) {
        $errors['status'] = "Statut invalide";
    }
    
    return count($errors) ? $errors : true;
}