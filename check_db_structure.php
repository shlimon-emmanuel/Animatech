<?php
// Activer l'affichage des erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo '<h1>Vérification de la structure de la base de données</h1>';

// Inclure le fichier de configuration pour accéder à la base de données
if (file_exists('app/config/config.php')) {
    require_once 'app/config/config.php';
} else {
    die("<p style='color: red;'>Fichier de configuration introuvable</p>");
}

try {
    // Connexion à la base de données
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "<p style='color: green;'>✓ Connexion à la base de données réussie</p>";
    
    // Liste des tables
    echo "<h2>Tables dans la base de données</h2>";
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<ul>";
    foreach ($tables as $table) {
        echo "<li>" . htmlspecialchars($table) . "</li>";
    }
    echo "</ul>";
    
    // Structure de la table users
    echo "<h2>Structure de la table 'users'</h2>";
    
    if (in_array('users', $tables)) {
        $stmt = $pdo->query("DESCRIBE users");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background-color: #f2f2f2;'>";
        echo "<th>Nom du champ</th>";
        echo "<th>Type</th>";
        echo "<th>Null</th>";
        echo "<th>Clé</th>";
        echo "<th>Défaut</th>";
        echo "<th>Extra</th>";
        echo "</tr>";
        
        foreach ($columns as $column) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($column['Field']) . "</td>";
            echo "<td>" . htmlspecialchars($column['Type']) . "</td>";
            echo "<td>" . htmlspecialchars($column['Null']) . "</td>";
            echo "<td>" . htmlspecialchars($column['Key']) . "</td>";
            echo "<td>" . (isset($column['Default']) ? htmlspecialchars($column['Default']) : '<em>NULL</em>') . "</td>";
            echo "<td>" . htmlspecialchars($column['Extra']) . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        
        // Aperçu des données
        echo "<h2>Aperçu des données utilisateurs</h2>";
        $stmt = $pdo->query("SELECT id, username, email, profile_picture, created_at FROM users LIMIT 5");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($users) > 0) {
            echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%;'>";
            echo "<tr style='background-color: #f2f2f2;'>";
            echo "<th>ID</th>";
            echo "<th>Username</th>";
            echo "<th>Email</th>";
            echo "<th>Photo de profil</th>";
            echo "<th>Date de création</th>";
            echo "</tr>";
            
            foreach ($users as $user) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($user['id']) . "</td>";
                echo "<td>" . htmlspecialchars($user['username']) . "</td>";
                echo "<td>" . htmlspecialchars($user['email']) . "</td>";
                echo "<td>" . htmlspecialchars($user['profile_picture'] ?? '<em>NULL</em>') . "</td>";
                echo "<td>" . htmlspecialchars($user['created_at']) . "</td>";
                echo "</tr>";
            }
            
            echo "</table>";
        } else {
            echo "<p>Aucun utilisateur trouvé dans la base de données.</p>";
        }
    } else {
        echo "<p style='color: red;'>La table 'users' n'existe pas dans la base de données.</p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Erreur de base de données : " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>

<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    h1, h2 { color: #333; }
    table { width: 100%; margin: 15px 0; border-collapse: collapse; }
    th { background-color: #f2f2f2; text-align: left; }
    td, th { padding: 10px; border: 1px solid #ddd; }
    tr:nth-child(even) { background-color: #f9f9f9; }
    ul { padding-left: 20px; }
</style> 