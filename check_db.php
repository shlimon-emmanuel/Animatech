<?php
// Définir les chemins
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', __DIR__);

// Inclure la configuration
require_once 'app/config/config.php';

echo "<h1>Vérification de la base de données</h1>";

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "<p>Connexion à la base de données réussie.</p>";
    
    // Vérifier l'existence de la table comment_replies
    $stmt = $pdo->query("SHOW TABLES LIKE 'comment_replies'");
    $exists = $stmt->rowCount() > 0;
    
    echo "<p>La table comment_replies " . ($exists ? "existe" : "n'existe PAS") . ".</p>";
    
    if (!$exists) {
        echo "<h2>Création de la table comment_replies</h2>";
        
        // Créer la table
        $sql = "CREATE TABLE IF NOT EXISTS `comment_replies` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `comment_id` int(11) NOT NULL,
          `user_id` int(11) NOT NULL,
          `content` text NOT NULL,
          `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          KEY `comment_id` (`comment_id`),
          KEY `user_id` (`user_id`),
          CONSTRAINT `comment_replies_ibfk_1` FOREIGN KEY (`comment_id`) REFERENCES `comments` (`id`) ON DELETE CASCADE,
          CONSTRAINT `comment_replies_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
        
        try {
            $pdo->exec($sql);
            echo "<p>Table comment_replies créée avec succès.</p>";
        } catch (PDOException $e) {
            echo "<p>Erreur lors de la création de la table: " . $e->getMessage() . "</p>";
            
            // Vérifier si la table comments existe
            $stmt = $pdo->query("SHOW TABLES LIKE 'comments'");
            $commentsExists = $stmt->rowCount() > 0;
            echo "<p>La table comments " . ($commentsExists ? "existe" : "n'existe PAS") . ".</p>";
            
            // Vérifier la structure de la table comments
            if ($commentsExists) {
                $stmt = $pdo->query("DESCRIBE comments");
                echo "<h3>Structure de la table comments:</h3>";
                echo "<pre>";
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    print_r($row);
                }
                echo "</pre>";
            }
            
            // Vérifier si la table users existe
            $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
            $usersExists = $stmt->rowCount() > 0;
            echo "<p>La table users " . ($usersExists ? "existe" : "n'existe PAS") . ".</p>";
        }
    } else {
        // Afficher la structure de la table
        $stmt = $pdo->query("DESCRIBE comment_replies");
        echo "<h3>Structure de la table comment_replies:</h3>";
        echo "<pre>";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            print_r($row);
        }
        echo "</pre>";
        
        // Vérifier les données existantes
        $stmt = $pdo->query("SELECT COUNT(*) FROM comment_replies");
        $count = $stmt->fetchColumn();
        echo "<p>Nombre de réponses dans la table: $count</p>";
    }
    
} catch (PDOException $e) {
    echo "<p>Erreur de connexion à la base de données: " . $e->getMessage() . "</p>";
}
?> 