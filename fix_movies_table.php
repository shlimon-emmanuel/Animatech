<?php
// Script pour créer la table movies et corriger les problèmes de récupération des favoris
// Placer ce fichier à la racine du projet et l'exécuter

// Activer l'affichage des erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Charger la configuration de base de données
require_once 'app/Config/config.php';

echo '<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h1, h2 { color: #333; }
.success { color: green; background: #e8f5e9; padding: 10px; border-radius: 5px; margin: 5px 0; }
.error { color: red; background: #ffebee; padding: 10px; border-radius: 5px; margin: 5px 0; }
.info { color: blue; background: #e3f2fd; padding: 10px; border-radius: 5px; margin: 5px 0; }
</style>';

echo '<h1>Réparation des tables de la base de données</h1>';

try {
    // Connexion à la base de données
    $db = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo '<div class="success">Connexion à la base de données réussie.</div>';
    
    // Vérifier si la table movies existe
    $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    $moviesTableExists = in_array('movies', $tables);
    
    if (!$moviesTableExists) {
        echo '<div class="info">La table "movies" n\'existe pas, création en cours...</div>';
        
        // Créer la table movies
        $db->exec("
            CREATE TABLE IF NOT EXISTS movies (
                id INT PRIMARY KEY,
                title VARCHAR(255) NOT NULL,
                overview TEXT,
                poster_path VARCHAR(255),
                backdrop_path VARCHAR(255),
                release_date DATE,
                popularity FLOAT,
                vote_average FLOAT,
                vote_count INT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8
        ");
        
        echo '<div class="success">Table "movies" créée avec succès.</div>';
    } else {
        echo '<div class="info">La table "movies" existe déjà.</div>';
    }
    
    // Vérifier la table favorites
    $favoritesTableExists = in_array('favorites', $tables);
    
    if (!$favoritesTableExists) {
        echo '<div class="info">La table "favorites" n\'existe pas, création en cours...</div>';
        
        // Créer la table favorites
        $db->exec("
            CREATE TABLE IF NOT EXISTS favorites (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                movie_id INT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY unique_favorite (user_id, movie_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8
        ");
        
        echo '<div class="success">Table "favorites" créée avec succès.</div>';
    } else {
        echo '<div class="info">La table "favorites" existe déjà.</div>';
    }
    
    // Vérifier si nous avons une table "simple_favorites" (mentionnée dans les logs)
    $simpleFavoritesTableExists = in_array('simple_favorites', $tables);
    
    if ($simpleFavoritesTableExists) {
        echo '<div class="info">Table "simple_favorites" trouvée.</div>';
        
        // Vérifier si nous devons migrer les données
        $stmt = $db->query("SELECT COUNT(*) FROM simple_favorites");
        $simpleFavoritesCount = $stmt->fetchColumn();
        
        if ($simpleFavoritesCount > 0) {
            echo '<div class="info">Nombre d\'entrées dans simple_favorites: ' . $simpleFavoritesCount . '</div>';
            
            // Transférer les données de simple_favorites vers favorites
            $db->exec("
                INSERT IGNORE INTO favorites (user_id, movie_id, created_at)
                SELECT user_id, movie_id, created_at FROM simple_favorites
            ");
            
            echo '<div class="success">Données migrées de simple_favorites vers favorites.</div>';
        }
    }
    
    // Récupérer tous les favoris pour vérifier les films manquants
    $stmt = $db->query("SELECT DISTINCT movie_id FROM favorites");
    $favoriteMovieIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (count($favoriteMovieIds) > 0) {
        echo '<div class="info">Films en favoris trouvés: ' . count($favoriteMovieIds) . '</div>';
        
        // Vérifier quels films sont déjà dans la table movies
        $placeholders = str_repeat('?,', count($favoriteMovieIds) - 1) . '?';
        $stmt = $db->prepare("SELECT id FROM movies WHERE id IN ($placeholders)");
        $stmt->execute($favoriteMovieIds);
        $existingMovieIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $missingMovieIds = array_diff($favoriteMovieIds, $existingMovieIds);
        
        if (count($missingMovieIds) > 0) {
            echo '<div class="info">Films manquants dans la table movies: ' . count($missingMovieIds) . '</div>';
            
            // Pour chaque film manquant, nous allons créer une entrée temporaire
            foreach ($missingMovieIds as $movieId) {
                $stmt = $db->prepare("
                    INSERT INTO movies (id, title, poster_path, release_date)
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([
                    $movieId, 
                    "Film #" . $movieId,
                    "", // poster_path vide
                    date("Y-m-d") // date actuelle
                ]);
            }
            
            echo '<div class="success">Entrées temporaires créées pour les films manquants.</div>';
            echo '<div class="info">Remarque : Les informations de films seront mises à jour lors de la visite de leurs pages.</div>';
        } else {
            echo '<div class="success">Tous les films en favoris sont déjà présents dans la table movies.</div>';
        }
    } else {
        echo '<div class="info">Aucun film en favoris trouvé.</div>';
    }
    
    // Vérification finale
    echo '<h2>Vérification finale</h2>';
    
    // Vérifier si la table movies existe
    $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo '<div class="info">Tables existantes : ' . implode(', ', $tables) . '</div>';
    
    // Vérifier le contenu de la table favorites
    $stmt = $db->query("SELECT COUNT(*) FROM favorites");
    $favoritesCount = $stmt->fetchColumn();
    echo '<div class="info">Nombre total de favoris : ' . $favoritesCount . '</div>';
    
    // Vérifier le contenu de la table movies
    $stmt = $db->query("SELECT COUNT(*) FROM movies");
    $moviesCount = $stmt->fetchColumn();
    echo '<div class="info">Nombre total de films : ' . $moviesCount . '</div>';
    
    echo '<div class="success">Script exécuté avec succès. Veuillez rafraîchir votre page de profil pour voir si les favoris apparaissent correctement.</div>';
    
} catch (PDOException $e) {
    echo '<div class="error">Erreur : ' . $e->getMessage() . '</div>';
}
?> 