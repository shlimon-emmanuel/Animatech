<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Films Favoris</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;700&family=Rajdhani:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <?php require_once APP_PATH . '/Views/includes/header.php'; ?>

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
</body>
</html> 