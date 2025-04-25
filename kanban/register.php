<?php
require_once 'config.php';

// Redirection si déjà connecté
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$errors = [];
$formData = [
    'name' => '',
    'email' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData = [
        'name' => trim($_POST['name'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'password' => $_POST['password'] ?? '',
        'password_confirm' => $_POST['password_confirm'] ?? ''
    ];

    // Validation
    if (empty($formData['name'])) {
        $errors['name'] = 'Le nom est obligatoire';
    } elseif (strlen($formData['name']) > 100) {
        $errors['name'] = 'Le nom ne doit pas dépasser 100 caractères';
    }

    if (empty($formData['email'])) {
        $errors['email'] = 'L\'email est obligatoire';
    } elseif (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Email invalide';
    } else {
        // Vérification unicité email
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$formData['email']]);
        if ($stmt->fetch()) {
            $errors['email'] = 'Cet email est déjà utilisé';
        }
    }

    if (empty($formData['password'])) {
        $errors['password'] = 'Le mot de passe est obligatoire';
    } elseif (strlen($formData['password']) < 8) {
        $errors['password'] = 'Le mot de passe doit faire au moins 8 caractères';
    }

    if ($formData['password'] !== $formData['password_confirm']) {
        $errors['password_confirm'] = 'Les mots de passe ne correspondent pas';
    }

    // Si pas d'erreurs, création du compte
    if (empty($errors)) {
        $hashedPassword = password_hash($formData['password'], PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
        if ($stmt->execute([$formData['name'], $formData['email'], $hashedPassword])) {
            $_SESSION['registration_success'] = true;
            header('Location: login.php');
            exit;
        } else {
            $errors['general'] = 'Une erreur est survenue lors de la création du compte';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription | Kanban App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/login.css"> <!-- Réutilise le même CSS que login -->
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-6 col-lg-4">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <div class="text-center mb-4">
                            <i class="fas fa-tasks fa-3x text-primary mb-3"></i>
                            <h2 class="h4">Créer un compte</h2>
                            <p class="text-muted">Commencez à organiser vos projets</p>
                        </div>

                        <?php if (!empty($errors['general'])): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($errors['general']) ?></div>
                        <?php endif; ?>

                        <form method="POST" action="register.php" novalidate>
                            <div class="mb-3">
                                <label for="name" class="form-label">Nom complet</label>
                                <input type="text" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" 
                                       id="name" name="name" value="<?= htmlspecialchars($formData['name']) ?>" required>
                                <?php if (isset($errors['name'])): ?>
                                <div class="invalid-feedback"><?= htmlspecialchars($errors['name']) ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" 
                                       id="email" name="email" value="<?= htmlspecialchars($formData['email']) ?>" required>
                                <?php if (isset($errors['email'])): ?>
                                <div class="invalid-feedback"><?= htmlspecialchars($errors['email']) ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">Mot de passe</label>
                                <input type="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" 
                                       id="password" name="password" required>
                                <?php if (isset($errors['password'])): ?>
                                <div class="invalid-feedback"><?= htmlspecialchars($errors['password']) ?></div>
                                <?php endif; ?>
                                <small class="form-text text-muted">Minimum 8 caractères</small>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password_confirm" class="form-label">Confirmez le mot de passe</label>
                                <input type="password" class="form-control <?= isset($errors['password_confirm']) ? 'is-invalid' : '' ?>" 
                                       id="password_confirm" name="password_confirm" required>
                                <?php if (isset($errors['password_confirm'])): ?>
                                <div class="invalid-feedback"><?= htmlspecialchars($errors['password_confirm']) ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="d-grid mb-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-user-plus me-2"></i> S'inscrire
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="card-footer text-center py-3">
                        <p class="mb-0">Déjà inscrit ? <a href="login.php">Se connecter</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Validation côté client basique
    document.querySelector('form').addEventListener('submit', function(e) {
        const password = document.getElementById('password');
        const confirm = document.getElementById('password_confirm');
        
        if (password.value.length < 8) {
            e.preventDefault();
            alert('Le mot de passe doit faire au moins 8 caractères');
            password.focus();
            return false;
        }
        
        if (password.value !== confirm.value) {
            e.preventDefault();
            alert('Les mots de passe ne correspondent pas');
            confirm.focus();
            return false;
        }
    });
    </script>
</body>
</html>