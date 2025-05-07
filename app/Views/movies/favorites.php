<?php
// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user'])) {
    header('Location: index.php?action=login');
    exit;
}

// Définir le titre de la page
$pageTitle = "Mes Films Favoris - Cinetech";

require_once APP_PATH . '/Views/header.php';
?>

<h1>Mes Films Favoris</h1>

<div class="movie-grid">
    <?php if (!empty($favorites)): ?>
        <?php foreach ($favorites as $movie): ?>
            <div class="movie-card">
                <a href="index.php?action=view&id=<?= $movie['id'] ?>">
                    <?php if (!empty($movie['poster_path'])): ?>
                        <img src="https://image.tmdb.org/t/p/w500<?= htmlspecialchars($movie['poster_path']) ?>" 
                             alt="<?= htmlspecialchars($movie['title']) ?>">
                    <?php endif; ?>
                    <h3><?= htmlspecialchars($movie['title']) ?></h3>
                </a>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>Vous n'avez pas encore de films favoris.</p>
    <?php endif; ?>
</div>

<style>
    /* Styles spécifiques à la page des favoris */
        h1 {
            text-align: center;
            font-family: 'Orbitron', sans-serif;
            color: var(--neon-blue);
            margin: 40px 0;
            text-shadow: var(--text-glow);
        }

        /* Grille de films */
        .movie-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 30px;
            padding: 40px 5%;
        }

        .movie-card {
            background-color: var(--darker-bg);
            border-radius: 12px;
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
            box-shadow: 0 0 5px var(--neon-purple);
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .movie-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 0 15px var(--neon-blue);
        }

        .movie-card a {
            text-decoration: none;
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .movie-card img {
            width: 100%;
            height: 320px;
            object-fit: cover;
        }

        .movie-card h3 {
            padding: 15px;
            text-align: center;
            font-family: 'Orbitron', sans-serif;
            color: var(--neon-blue);
            text-shadow: var(--text-glow);
            margin: 0;
            flex-grow: 1;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Message en cas d'absence de favoris */
        .movie-grid p {
            grid-column: 1 / -1;
            text-align: center;
            padding: 30px;
            font-size: 18px;
            color: var(--neon-purple);
        }
    </style>
</body>
</html> 