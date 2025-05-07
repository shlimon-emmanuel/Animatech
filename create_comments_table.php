<?php
// Inclure la configuration
require_once 'app/config/config.php';

// Connexion à la base de données
try {
    $db = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8', DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connexion à la base de données réussie!<br>";
    
    // Créer la table des commentaires si elle n'existe pas
    $db->exec("
        CREATE TABLE IF NOT EXISTS comments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            movie_id INT NOT NULL,
            content TEXT NOT NULL,
            rating INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            KEY idx_user (user_id),
            KEY idx_movie (movie_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8
    ");
    echo "Table 'comments' créée ou déjà existante<br>";
    
    // Créer la table de réponses aux commentaires si elle n'existe pas
    $db->exec("
        CREATE TABLE IF NOT EXISTS comment_replies (
            id INT AUTO_INCREMENT PRIMARY KEY,
            comment_id INT NOT NULL,
            user_id INT NOT NULL,
            content TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            KEY idx_comment (comment_id),
            KEY idx_user (user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8
    ");
    echo "Table 'comment_replies' créée ou déjà existante<br>";
    
    // Vérifier les tables existantes
    $result = $db->query("SHOW TABLES");
    $tables = $result->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<br>Tables existantes dans la base de données:<br>";
    foreach ($tables as $table) {
        echo "- " . $table . "<br>";
    }
    
} catch (PDOException $e) {
    echo "Erreur de connexion à la base de données: " . $e->getMessage();
}
?> 