<?php
require_once 'config.php';
require_once 'functions.php';

// Récupérer toutes les tâches
$tasks = getTasks($pdo);
$columns = [
    'todo' => 'À faire',
    'inprogress' => 'En cours',
    'blocked' => 'Bloqué',
    'done' => 'Terminé'
];

// Générer un token CSRF pour les formulaires
$csrfToken = generateCsrfToken();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= $csrfToken ?>">
    <title>Tableau Kanban</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- jQuery UI pour le drag and drop -->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.0/themes/base/jquery-ui.css">
    
    <!-- CSS personnalisé -->
    <style>
        .kanban-board {
            min-height: 80vh;
        }
        
        .kanban-column {
            min-height: 70vh;
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
        }
        
        .kanban-card {
            border-radius: 12px !important;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            border: none;
            margin-bottom: 1rem;
            cursor: move;
        }
        
        .kanban-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
        }
        
        .column-header {
            border-radius: 10px 10px 0 0 !important;
            padding: 0.75rem 1.25rem;
        }
        
        .task-actions .btn {
            border-radius: 50%;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0;
        }
        
        .add-task-btn {
            border-radius: 50% !important;
            width: 36px;
            height: 36px;
        }
        
        .card-placeholder {
            border: 2px dashed #ccc;
            background-color: #f8f9fa;
            height: 100px;
            margin-bottom: 15px;
            border-radius: 12px;
        }
    </style>
</head>
<body>
    <div class="container-fluid py-2">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h2 fw-bold mb-0">Tableau Kanban</h1>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#helpModal">
                <i class="fas fa-question-circle me-2"></i>Aide
            </button>
        </div>
        
        <div class="row kanban-board">
            <?php foreach ($columns as $status => $title): 
                $color = getStatusColor($status);
            ?>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-header column-header text-white d-flex justify-content-between align-items-center" 
                         style="background-color: <?= $color ?>">
                        <h5 class="card-title mb-0 fw-bold"><?= $title ?></h5>
                        <button class="btn btn-sm btn-light add-task add-task-btn" 
                                data-status="<?= $status ?>" title="Ajouter une tâche">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                    <div class="card-body kanban-column p-3" data-status="<?= $status ?>">
                        <?php foreach ($tasks as $task): ?>
                            <?php if ($task['status'] == $status): ?>
                                <div class="card mb-3 kanban-card task-card" 
                                     data-task-id="<?= $task['id'] ?>">
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h6 class="card-title fw-bold mb-0"><?= htmlspecialchars($task['title']) ?></h6>
                                            <?php if ($task['due_date']): ?>
                                                <span class="badge bg-<?= (strtotime($task['due_date']) < time() ? 'danger' : 'info') ?>">
                                                    <?= date('d/m/Y', strtotime($task['due_date'])) ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        <?php if (!empty($task['description'])): ?>
                                            <p class="card-text small text-muted mb-2">
                                                <?= nl2br(htmlspecialchars(substr($task['description'], 0, 100))) ?>
                                                <?= strlen($task['description']) > 100 ? '...' : '' ?>
                                            </p>
                                        <?php endif; ?>
                                        <div class="d-flex float-end task-actions">
                                            <button class="btn btn-sm btn-outline-primary edit-task me-2" 
                                                    data-id="<?= $task['id'] ?>" title="Modifier">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger delete-task" 
                                                    data-id="<?= $task['id'] ?>" title="Supprimer">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                    <div class="card-footer bg-transparent">
                        <small class="text-muted">
                            <?= count(array_filter($tasks, fn($t) => $t['status'] === $status)) ?> tâches
                        </small>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Modal pour les tâches -->
    <?php include 'includes/task-modal.php'; ?>
    
    <!-- Modal d'aide -->
    <div class="modal fade" id="helpModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Aide - Tableau Kanban</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h6>Fonctionnalités :</h6>
                    <ul>
                        <li>Glisser-déposer les tâches entre les colonnes</li>
                        <li>Cliquer sur <i class="fas fa-plus"></i> pour ajouter une tâche</li>
                        <li>Cliquer sur <i class="fas fa-edit"></i> pour modifier une tâche</li>
                        <li>Cliquer sur <i class="fas fa-trash"></i> pour supprimer une tâche</li>
                    </ul>
                    <h6>Statuts :</h6>
                    <ul class="list-unstyled">
                        <li><span class="badge" style="background-color: #FF9F40">À faire</span></li>
                        <li><span class="badge" style="background-color: #2E86DE">En cours</span></li>
                        <li><span class="badge" style="background-color: #EE5253">Bloqué</span></li>
                        <li><span class="badge" style="background-color: #10AC84">Terminé</span></li>
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery, Bootstrap JS, jQuery UI -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.0/jquery-ui.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Script personnalisé -->
    <script src="assets/js/script.js"></script>
</body>
</html>