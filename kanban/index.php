<?php
require_once 'config.php';
require_once 'includes/header.php';

// Vérification connexion utilisateur
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Récupération des projets
$stmt = $pdo->prepare("SELECT * FROM projects WHERE user_id = ? OR id IN 
                      (SELECT project_id FROM project_members WHERE user_id = ?)");
$stmt->execute([$_SESSION['user_id'], $_SESSION['user_id']]);
$projects = $stmt->fetchAll();
?>

<div class="container mt-5">
    <h1>Mes Projets</h1>
    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#newProjectModal">
        <i class="fas fa-plus"></i> Nouveau projet
    </button>

    <div class="row">
        <?php foreach ($projects as $project): ?>
        <div class="col-md-4 mb-4">
            <div class="card project-card">
                <div class="card-body">
                    <h5 class="card-title"><?= htmlspecialchars($project['name']) ?></h5>
                    <p class="card-text"><?= htmlspecialchars($project['description']) ?></p>
                    <a href="pages/kanban.php?project_id=<?= $project['id'] ?>" class="btn btn-primary">
                        Ouvrir
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Modal Nouveau Projet -->
<div class="modal fade" id="newProjectModal">
    <!-- Contenu du formulaire -->
</div>

<?php require_once 'includes/footer.php'; ?>