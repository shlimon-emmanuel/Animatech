<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($movie['title'] ?? 'Détails du film') ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;700&family=Rajdhani:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <?php require_once APP_PATH . '/app/Views/includes/header.php'; ?>
    
    <div class="movie-detail">
        <?php if (!empty($movie)): ?>
            <h1><?= htmlspecialchars($movie['title']) ?></h1>
            
            <?php if (isset($_SESSION['user'])): ?>
                <div class="favorite-section">
                    <?php if ($isFavorite): ?>
                        <form action="index.php?action=removeFavorite" method="POST" class="favorite-form">
                            <input type="hidden" name="movie_id" value="<?= $movie['id'] ?>">
                            <button type="submit" class="neon-button favorite-btn active">
                                ★ Retirer des favoris
                            </button>
                        </form>
                    <?php else: ?>
                        <form action="index.php?action=addFavorite" method="POST" class="favorite-form">
                            <input type="hidden" name="movie_id" value="<?= $movie['id'] ?>">
                            <button type="submit" class="neon-button favorite-btn">
                                ☆ Ajouter aux favoris
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($movie['poster_path'])): ?>
                <img class="movie-poster" 
                     src="https://image.tmdb.org/t/p/w500<?= htmlspecialchars($movie['poster_path']) ?>" 
                     alt="<?= htmlspecialchars($movie['title']) ?>">
            <?php endif; ?>
            
            <div class="movie-info">
                <p><strong>Date de sortie:</strong> <?= htmlspecialchars($movie['release_date']) ?></p>
                <?php if (!empty($movie['overview'])): ?>
                    <p><strong>Synopsis:</strong> <?= htmlspecialchars($movie['overview']) ?></p>
                <?php endif; ?>
                <div class="rating">
                    <strong>Note moyenne:</strong> <?= htmlspecialchars($movie['vote_average']) ?>/10
                </div>
            </div>

            <!-- Section bande-annonce -->
            <div class="trailer-section">
                <h2>Bande Annonce</h2>
                <?php if ($trailer && isset($trailer['key'])): ?>
                    <div class="video-container">
                        <iframe
                            width="100%"
                            height="500"
                            src="https://www.youtube.com/embed/<?= htmlspecialchars($trailer['key']) ?>"
                            frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowfullscreen>
                        </iframe>
                    </div>
                    <div class="video-info">
                        <p><?= htmlspecialchars($trailer['name']) ?></p>
                    </div>
                <?php else: ?>
                    <p class="no-trailer">Aucune bande-annonce disponible pour ce film.</p>
                    <?php
                    // Débogage - Afficher les informations de l'API
                    if (isset($videos) && !empty($videos)):
                        echo "<pre style='color: white;'>";
                        print_r($videos);
                        echo "</pre>";
                    endif;
                    ?>
                <?php endif; ?>
            </div>

            <!-- Section Commentaires -->
            <div class="comments-section">
                <h2>Commentaires</h2>
                
                <?php if (isset($_SESSION['user'])): ?>
                    <form action="index.php?action=addComment" method="POST" class="comment-form">
                        <input type="hidden" name="movie_id" value="<?= $movie['id'] ?>">
                        <div class="form-group">
                            <label for="rating">Note (1-5)</label>
                            <select name="rating" id="rating" required>
                                <?php for($i = 1; $i <= 5; $i++): ?>
                                    <option value="<?= $i ?>"><?= str_repeat('★', $i) ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="content">Votre avis</label>
                            <textarea name="content" id="content" required></textarea>
                        </div>
                        <button type="submit" class="neon-button">Publier</button>
                    </form>
                <?php endif; ?>

                <div class="comments-list">
                    <?php if (!empty($comments)): ?>
                        <?php foreach ($comments as $comment): ?>
                            <div class="comment-card">
                                <div class="comment-header">
                                    <span class="comment-author"><?= htmlspecialchars($comment['username']) ?></span>
                                    <span class="comment-rating"><?= str_repeat('★', $comment['rating']) ?></span>
                                </div>
                                <p class="comment-content"><?= htmlspecialchars($comment['content']) ?></p>
                                <span class="comment-date"><?= date('d/m/Y H:i', strtotime($comment['created_at'])) ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>Aucun commentaire pour le moment.</p>
                    <?php endif; ?>
                </div>
            </div>
        <?php else: ?>
            <p>Film non trouvé.</p>
        <?php endif; ?>
        
        <a href="index.php" class="nav-link">Retour à la liste</a>
    </div>
</body>
</html> 