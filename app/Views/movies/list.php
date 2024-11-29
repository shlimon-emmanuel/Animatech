<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Films d'Animation</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;700&family=Rajdhani:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <?php require_once APP_PATH . '/Views/includes/header.php'; ?>

    <div class="search-container">
        <input type="text" id="searchInput" placeholder="Rechercher un film d'animation...">
    </div>

    <div id="movieGrid" class="movie-grid">
        <?php if (isset($results) && !empty($results->results)): ?>
            <?php foreach ($results->results as $movie): ?>
                <div class="movie-card" data-title="<?= htmlspecialchars(strtolower($movie->title)) ?>">
                    <a href="index.php?action=view&id=<?= $movie->id ?>">
                        <?php if ($movie->poster_path): ?>
                            <img src="https://image.tmdb.org/t/p/w500<?= $movie->poster_path ?>" 
                                 alt="<?= htmlspecialchars($movie->title) ?>">
                        <?php endif; ?>
                        <h3><?= htmlspecialchars($movie->title) ?></h3>
                    </a>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-results">
                <p>Aucun film d'animation trouvé. Essayez une recherche différente.</p>
            </div>
        <?php endif; ?>
    </div>

    <div id="loading" class="loading" style="display: none;">
        Chargement...
    </div>

    <script src="../../assets/js/search.js"></script>
</body>
</html>