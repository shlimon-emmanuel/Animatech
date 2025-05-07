<?php
/**
 * Script pour vérifier la connexion à la base de données et la structure de la table users
 */

// Inclure les fichiers nécessaires
require_once 'Core/config.php';

// Activer l'affichage des erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<html><head><title>Vérification de la base de données</title>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    h1, h2 { color: #333; }
    .success { color: green; background: #e8f5e9; padding: 10px; margin: 5px 0; border-radius: 4px; }
    .error { color: red; background: #ffebee; padding: 10px; margin: 5px 0; border-radius: 4px; }
    .warning { color: orange; background: #fff3e0; padding: 10px; margin: 5px 0; border-radius: 4px; }
    .info { color: blue; background: #e3f2fd; padding: 10px; margin: 5px 0; border-radius: 4px; }
    table { border-collapse: collapse; width: 100%; margin: 15px 0; }
    table, th, td { border: 1px solid #ddd; }
    th, td { padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
</style></head><body>";

echo "<h1>Vérification de la base de données</h1>";

// Test de la connexion à la base de données
try {
    $db = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "<div class='success'>Connexion à la base de données réussie!</div>";
    
    // Vérifier l'existence de la table users
    $stmt = $db->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() > 0) {
        echo "<div class='success'>La table 'users' existe.</div>";
        
        // Vérifier la structure de la table users
        $stmt = $db->query("DESCRIBE users");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h2>Structure de la table 'users'</h2>";
        echo "<table>";
        echo "<tr><th>Champ</th><th>Type</th><th>Null</th><th>Clé</th><th>Défaut</th><th>Extra</th></tr>";
        
        $hasProfilePicture = false;
        foreach ($columns as $column) {
            echo "<tr>";
            foreach ($column as $key => $value) {
                echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
                
                if ($key === 'Field' && $value === 'profile_picture') {
                    $hasProfilePicture = true;
                }
            }
            echo "</tr>";
        }
        echo "</table>";
        
        if (!$hasProfilePicture) {
            echo "<div class='warning'>La colonne 'profile_picture' n'existe pas dans la table 'users'. Voulez-vous l'ajouter?</div>";
            
            // Formulaire pour ajouter la colonne si nécessaire
            echo "<form action='' method='post'>";
            echo "<input type='hidden' name='add_profile_column' value='1'>";
            echo "<button type='submit'>Ajouter la colonne 'profile_picture'</button>";
            echo "</form>";
        } else {
            echo "<div class='success'>La colonne 'profile_picture' existe dans la table 'users'.</div>";
        }
        
        // Afficher quelques utilisateurs pour vérification
        $stmt = $db->query("SELECT id, username, email, profile_picture, created_at FROM users LIMIT 5");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($users) > 0) {
            echo "<h2>Échantillon d'utilisateurs (5 premiers)</h2>";
            echo "<table>";
            echo "<tr>";
            foreach (array_keys($users[0]) as $key) {
                echo "<th>" . htmlspecialchars($key) . "</th>";
            }
            echo "</tr>";
            
            foreach ($users as $user) {
                echo "<tr>";
                foreach ($user as $value) {
                    echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
                }
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<div class='warning'>Aucun utilisateur trouvé dans la base de données.</div>";
        }
    } else {
        echo "<div class='error'>La table 'users' n'existe pas!</div>";
        
        // Formulaire pour créer la table users si nécessaire
        echo "<form action='' method='post'>";
        echo "<input type='hidden' name='create_users_table' value='1'>";
        echo "<button type='submit'>Créer la table 'users'</button>";
        echo "</form>";
    }
    
    // Traiter la création de la table users
    if (isset($_POST['create_users_table'])) {
        $db->exec("
            CREATE TABLE users (
                id INT(11) AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) NOT NULL,
                email VARCHAR(100) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                profile_picture VARCHAR(255) DEFAULT 'assets/img/default-profile.png',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8
        ");
        echo "<div class='success'>Table 'users' créée avec succès!</div>";
        echo "<meta http-equiv='refresh' content='2'>";  // Rafraîchir la page après 2 secondes
    }
    
    // Traiter l'ajout de la colonne profile_picture
    if (isset($_POST['add_profile_column'])) {
        $db->exec("
            ALTER TABLE users
            ADD COLUMN profile_picture VARCHAR(255) DEFAULT 'assets/img/default-profile.png'
        ");
        echo "<div class='success'>Colonne 'profile_picture' ajoutée avec succès!</div>";
        echo "<meta http-equiv='refresh' content='2'>";  // Rafraîchir la page après 2 secondes
    }
    
} catch (PDOException $e) {
    echo "<div class='error'>Erreur de connexion à la base de données: " . htmlspecialchars($e->getMessage()) . "</div>";
    
    echo "<div class='info'>";
    echo "<h2>Configuration actuelle:</h2>";
    echo "Host: " . htmlspecialchars(DB_HOST) . "<br>";
    echo "Database: " . htmlspecialchars(DB_NAME) . "<br>";
    echo "User: " . htmlspecialchars(DB_USER) . "<br>";
    echo "</div>";
}

echo "<p><a href='index.php?action=profile' style='display: inline-block; margin-top: 20px; padding: 10px 15px; background: #2196F3; color: white; text-decoration: none; border-radius: 4px;'>Retour à la page de profil</a></p>";

echo "</body></html>";
?> 