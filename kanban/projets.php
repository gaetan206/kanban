<?php
// includes
require_once 'config.php';
require_once 'functions.php';

// Récupérer les projets depuis la base
$req = $pdo->query("SELECT * FROM projets ORDER BY date_creation DESC");
$projets = $req->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-folder-open"></i> Liste des projets</h2>
    <a href="ajouter_projet.php" class="btn btn-primary">
        <i class="fas fa-plus-circle"></i> Ajouter un projet
    </a>
</div>

<?php if (count($projets) > 0): ?>
    <div class="table-responsive">
        <table class="table table-hover table-bordered align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Nom</th>
                    <th>Description</th>
                    <th>Date de création</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($projets as $projet): ?>
                    <tr>
                        <td><?= htmlspecialchars($projet['nom']) ?></td>
                        <td><?= htmlspecialchars(substr($projet['description'], 0, 50)) ?>...</td>
                        <td><?= date('d/m/Y', strtotime($projet['date_creation'])) ?></td>
                        <td>
                            <span class="badge bg-<?= $projet['statut'] === 'actif' ? 'success' : 'secondary' ?>">
                                <?= ucfirst($projet['statut']) ?>
                            </span>
                        </td>
                        <td>
                            <a href="details_projet.php?id=<?= $projet['id'] ?>" class="btn btn-sm btn-info">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="modifier_projet.php?id=<?= $projet['id'] ?>" class="btn btn-sm btn-warning">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="supprimer_projet.php?id=<?= $projet['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer ce projet ?')">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <div class="alert alert-info">
        Aucun projet trouvé. <a href="ajouter_projet.php">Créer un nouveau projet</a>.
    </div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
