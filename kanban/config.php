<?php
session_start();

// Configuration DB
define('DB_HOST', 'localhost');
define('DB_NAME', 'kanban_board');
define('DB_USER', 'root');
define('DB_PASS', '');

// Couleurs personnalisées
$customColors = [
    'todo' => '#FF9F40',
    'inprogress' => '#2E86DE',
    'blocked' => '#EE5253',
    'done' => '#10AC84'
];

try {
    $pdo = new PDO(
        'mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch (PDOException $e) {
    die('Erreur DB: '.$e->getMessage());
}

// Génération token CSRF
function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}
?>