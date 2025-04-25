<?php
require_once 'config.php';

echo "<h1>Debug Information</h1>";

// Test connexion DB
try {
    $stmt = $pdo->query("SELECT 1");
    echo "<p style='color:green'>✅ Connexion DB fonctionnelle</p>";
    
    // Vérifie si la table existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'tasks'");
    if ($stmt->rowCount() > 0) {
        echo "<p style='color:green'>✅ Table 'tasks' existe</p>";
    } else {
        echo "<p style='color:red'>❌ Table 'tasks' manquante</p>";
    }
} catch (PDOException $e) {
    echo "<p style='color:red'>❌ Erreur DB: " . $e->getMessage() . "</p>";
}

// Test sessions
echo "<h2>Session</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// Test CSRF
echo "<h2>CSRF Token</h2>";
echo "<p>Token: " . (isset($_SESSION['csrf_token']) ? $_SESSION['csrf_token'] : 'Non généré') . "</p>";

// Test requête AJAX simulée
echo "<h2>Test AJAX</h2>";
echo <<<HTML
<button onclick="testAjax()">Tester update_task.php</button>
<script>
function testAjax() {
    fetch('update_task.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=test&csrf_token=<?= $_SESSION['csrf_token'] ?? '' ?>'
    })
    .then(r => r.json())
    .then(console.log)
    .catch(console.error);
}
</script>
HTML;