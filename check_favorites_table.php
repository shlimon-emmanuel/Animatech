<?php
// Configuration de base
define('DB_HOST', 'localhost');
define('DB_NAME', 'userauth');
define('DB_USER', 'root');
define('DB_PASS', '');

try {
    // Connect to the database
    $db = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    echo "<h2>Vérification des tables de favoris</h2>";
    
    // Check for favorites table
    $tables = $db->query("SHOW TABLES LIKE 'favorites'")->fetchAll(PDO::FETCH_COLUMN);
    if (count($tables) > 0) {
        echo "<p style='color:green'>✓ Table 'favorites' exists</p>";
        
        // Show structure
        echo "<h3>Structure de la table 'favorites'</h3>";
        $structure = $db->query("DESCRIBE favorites")->fetchAll(PDO::FETCH_ASSOC);
        echo "<pre>" . print_r($structure, true) . "</pre>";
        
        // Show content
        echo "<h3>Contenu de la table 'favorites'</h3>";
        $content = $db->query("SELECT * FROM favorites LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
        echo "<pre>" . print_r($content, true) . "</pre>";
    } else {
        echo "<p style='color:red'>✗ Table 'favorites' n'existe pas</p>";
    }
    
    // Check for simple_favorites table
    $tables = $db->query("SHOW TABLES LIKE 'simple_favorites'")->fetchAll(PDO::FETCH_COLUMN);
    if (count($tables) > 0) {
        echo "<p style='color:green'>✓ Table 'simple_favorites' exists</p>";
        
        // Show structure
        echo "<h3>Structure de la table 'simple_favorites'</h3>";
        $structure = $db->query("DESCRIBE simple_favorites")->fetchAll(PDO::FETCH_ASSOC);
        echo "<pre>" . print_r($structure, true) . "</pre>";
        
        // Show content
        echo "<h3>Contenu de la table 'simple_favorites'</h3>";
        $content = $db->query("SELECT * FROM simple_favorites LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
        echo "<pre>" . print_r($content, true) . "</pre>";
    } else {
        echo "<p style='color:red'>✗ Table 'simple_favorites' n'existe pas</p>";
    }
    
} catch (PDOException $e) {
    echo "<h2>Erreur de base de données</h2>";
    echo "<p style='color:red'>" . $e->getMessage() . "</p>";
} 