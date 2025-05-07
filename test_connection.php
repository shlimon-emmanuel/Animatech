<?php
// Inclure la configuration
require_once 'app/config/config.php';

// Connexion à la base de données
try {
    $db = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8', DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connexion à la base de données réussie!<br>";
    
    // Créer la table des utilisateurs si elle n'existe pas
    $db->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(100) NOT NULL,
            email VARCHAR(255) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            profile_picture VARCHAR(255) DEFAULT 'assets/img/default-profile.png',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_email (email)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8
    ");
    echo "Table 'users' créée ou déjà existante<br>";
    
    // Créer la table des favoris si elle n'existe pas
    $db->exec("
        CREATE TABLE IF NOT EXISTS favorites (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            movie_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_favorite (user_id, movie_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8
    ");
    echo "Table 'favorites' créée ou déjà existante<br>";
    
    // Créer la table des films si elle n'existe pas
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
    echo "Table 'movies' créée ou déjà existante<br>";
    
    // Vérifier si l'utilisateur de test existe déjà
    $checkUser = $db->prepare("SELECT id FROM users WHERE email = ?");
    $checkUser->execute(['test@example.com']);
    $user = $checkUser->fetch(PDO::FETCH_ASSOC);
    
    $userId = null;
    
    // Créer l'utilisateur de test s'il n'existe pas
    if (!$user) {
        $stmt = $db->prepare("
            INSERT INTO users (username, email, password) 
            VALUES (?, ?, ?)
        ");
        $stmt->execute(['TestUser', 'test@example.com', password_hash('password123', PASSWORD_DEFAULT)]);
        $userId = $db->lastInsertId();
        echo "Utilisateur de test créé avec ID: $userId<br>";
    } else {
        $userId = $user['id'];
        echo "Utilisateur de test existe déjà avec ID: $userId<br>";
    }
    
    // Films de test à ajouter
    $testMovies = [
        [
            'id' => 372058,
            'title' => "Your Name",
            'overview' => "Mitsuha, adolescente coincée dans une famille traditionnelle, rêve de quitter ses montagnes natales pour découvrir la vie trépidante de Tokyo.",
            'poster_path' => '/vpM5eHiZs6QcaL2eiIEeQspcBab.jpg',
            'release_date' => '2016-08-26',
            'vote_average' => 8.5
        ],
        [
            'id' => 128,
            'title' => "Princesse Mononoké",
            'overview' => "Au XVe siècle, durant l'ère Muromachi, la forêt japonaise, jadis protégée par des animaux géants, se dépeuple à cause de l'homme.",
            'poster_path' => '/kpeCsmMEKGi4MbJXgcM7xji1kYA.jpg',
            'release_date' => '1997-07-12',
            'vote_average' => 8.4
        ],
        [
            'id' => 129,
            'title' => "Le Voyage de Chihiro",
            'overview' => "Chihiro, une fillette de 10 ans, est en route vers sa nouvelle demeure en compagnie de ses parents.",
            'poster_path' => '/dDzYp2pDvkT3tQ3qcJjzVw1K5ha.jpg',
            'release_date' => '2001-07-20',
            'vote_average' => 8.5
        ]
    ];
    
    // Ajouter chaque film et le mettre en favori
    foreach ($testMovies as $movie) {
        // Vérifier si le film existe déjà
        $checkMovie = $db->prepare("SELECT id FROM movies WHERE id = ?");
        $checkMovie->execute([$movie['id']]);
        $existingMovie = $checkMovie->fetch(PDO::FETCH_ASSOC);
        
        if (!$existingMovie) {
            // Ajouter le film
            $stmt = $db->prepare("
                INSERT INTO movies (id, title, overview, poster_path, release_date, vote_average) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $movie['id'],
                $movie['title'],
                $movie['overview'],
                $movie['poster_path'],
                $movie['release_date'],
                $movie['vote_average']
            ]);
            echo "Film '{$movie['title']}' ajouté<br>";
        } else {
            echo "Film '{$movie['title']}' existe déjà<br>";
        }
        
        // Vérifier si le favori existe déjà
        $checkFavorite = $db->prepare("SELECT id FROM favorites WHERE user_id = ? AND movie_id = ?");
        $checkFavorite->execute([$userId, $movie['id']]);
        $existingFavorite = $checkFavorite->fetch(PDO::FETCH_ASSOC);
        
        if (!$existingFavorite) {
            // Ajouter aux favoris
            $stmt = $db->prepare("
                INSERT INTO favorites (user_id, movie_id) 
                VALUES (?, ?)
            ");
            $stmt->execute([$userId, $movie['id']]);
            echo "Film '{$movie['title']}' ajouté aux favoris<br>";
        } else {
            echo "Film '{$movie['title']}' déjà en favoris<br>";
        }
    }
    
    // Afficher résumé
    echo "<br>Résumé:<br>";
    echo "Nombre d'utilisateurs: " . $db->query("SELECT COUNT(*) FROM users")->fetchColumn() . "<br>";
    echo "Nombre de films: " . $db->query("SELECT COUNT(*) FROM movies")->fetchColumn() . "<br>";
    echo "Nombre de favoris: " . $db->query("SELECT COUNT(*) FROM favorites")->fetchColumn() . "<br>";
    
    echo "<br>Pour utiliser cet utilisateur, connectez-vous avec:<br>";
    echo "Email: test@example.com<br>";
    echo "Mot de passe: password123<br>";
    
} catch (PDOException $e) {
    echo "Erreur de connexion à la base de données: " . $e->getMessage();
}
?> 