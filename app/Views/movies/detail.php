<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($movie['title'] ?? 'Détails du film') ?></title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🎬</text></svg>" type="image/svg+xml">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;700&family=Rajdhani:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* Styles spécifiques pour la page de détails de film */
        .movie-detail-page {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .movie-title {
            font-family: 'Orbitron', sans-serif;
            color: var(--neon-blue);
            text-shadow: var(--text-glow);
            text-align: center;
            margin-bottom: 30px;
            font-size: 2.5rem;
            padding: 20px 0;
        }

        /* Messages d'alerte */
        .alert {
            padding: 15px 20px;
            margin: 20px 0;
            border-radius: 8px;
            font-weight: 500;
        }

        .alert-error {
            background-color: rgba(220, 53, 69, 0.1);
            border: 1px solid #dc3545;
            color: #dc3545;
        }

        .alert-success {
            background-color: rgba(40, 167, 69, 0.1);
            border: 1px solid #28a745;
            color: #28a745;
        }

        /* Section des favoris */
        .favorites-section {
            text-align: center;
            margin: 30px 0;
            padding: 20px;
            background-color: rgba(10, 10, 31, 0.3);
            border-radius: 12px;
            border: 1px solid rgba(157, 78, 221, 0.2);
        }

        .favorite-btn {
            background-color: transparent;
            border: 2px solid var(--neon-purple);
            color: var(--neon-purple);
            padding: 12px 24px;
            border-radius: 25px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: 'Orbitron', sans-serif;
            font-weight: 500;
            font-size: 1rem;
        }

        .favorite-btn:hover {
            background-color: var(--neon-purple);
            color: white;
            box-shadow: 0 0 20px rgba(157, 78, 221, 0.5);
        }

        .favorite-btn.active {
            background-color: var(--neon-purple);
            color: white;
        }

        /* Contenu principal du film */
        .movie-main-content {
            display: grid;
            grid-template-columns: 350px 1fr;
            gap: 40px;
            margin: 40px 0;
            align-items: start;
        }

        .movie-poster-section {
            text-align: center;
        }

        .movie-poster {
            width: 100%;
            max-width: 350px;
            height: auto;
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0, 243, 255, 0.3);
            transition: transform 0.3s ease;
        }

        .movie-poster:hover {
            transform: scale(1.02);
        }

        .movie-info-section {
            background-color: var(--darker-bg);
            padding: 30px;
            border-radius: 12px;
            border: 1px solid rgba(157, 78, 221, 0.2);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }

        .movie-info-section p {
            margin: 20px 0;
            font-size: 1.1rem;
            line-height: 1.6;
            color: #e1e1e1;
        }

        .movie-info-section strong {
            color: var(--neon-blue);
        }

        .movie-rating {
            display: inline-block;
            background-color: var(--neon-purple);
            color: white;
            padding: 10px 20px;
            border-radius: 25px;
            font-weight: bold;
            margin-top: 15px;
            font-size: 1.1rem;
        }

        /* Section bande-annonce */
        .trailer-section {
            margin: 50px 0;
            padding: 30px;
            background-color: var(--darker-bg);
            border-radius: 12px;
            border: 1px solid rgba(0, 243, 255, 0.2);
        }

        .trailer-title {
            font-family: 'Orbitron', sans-serif;
            color: var(--neon-blue);
            text-shadow: var(--text-glow);
            font-size: 2rem;
            margin-bottom: 25px;
            text-align: center;
        }

        .video-container {
            position: relative;
            width: 100%;
            padding-bottom: 56.25%; /* Ratio 16:9 */
            height: 0;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
        }

        .video-container iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border: none;
        }

        .video-info {
            margin-top: 15px;
            text-align: center;
        }

        .video-info p {
            color: var(--neon-blue);
            font-size: 1.1rem;
            font-weight: 500;
        }

        .no-trailer {
            text-align: center;
            color: #999;
            font-style: italic;
            font-size: 1.1rem;
            padding: 40px;
        }

        /* Section commentaires */
        .comments-section {
            margin: 50px 0;
        }

        .comments-title {
            font-family: 'Orbitron', sans-serif;
            color: var(--neon-blue);
            text-shadow: var(--text-glow);
            font-size: 2rem;
            margin-bottom: 30px;
            text-align: center;
        }

        /* Formulaire de commentaire */
        .comment-form {
            background-color: var(--darker-bg);
            padding: 25px;
            border-radius: 12px;
            border: 1px solid rgba(0, 243, 255, 0.2);
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--neon-blue);
            font-weight: 600;
        }

        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            background-color: rgba(0, 243, 255, 0.1);
            border: 2px solid rgba(0, 243, 255, 0.3);
            border-radius: 8px;
            color: white;
            font-family: 'Rajdhani', sans-serif;
            font-size: 1rem;
            transition: all 0.3s ease;
            box-sizing: border-box;
        }

        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }

        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--neon-blue);
            box-shadow: 0 0 10px rgba(0, 243, 255, 0.3);
        }

        /* Liste des commentaires */
        .comments-list {
            margin-top: 30px;
        }

        .comment-card {
            background-color: rgba(30, 30, 30, 0.8);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            border-left: 4px solid var(--neon-purple);
            transition: all 0.3s ease;
        }

        .comment-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.4);
        }

        .comment-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .comment-user-info {
            display: flex;
            align-items: center;
        }

        .comment-profile-picture,
        .default-comment-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 12px;
        }

        .comment-profile-picture {
            object-fit: cover;
            border: 2px solid var(--neon-purple);
        }

        .default-comment-icon {
            background-color: var(--neon-purple);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }

        .comment-author {
            font-weight: 600;
            color: var(--neon-blue);
            font-size: 1.1rem;
        }

        .comment-author-link {
            text-decoration: none;
            color: inherit;
            transition: color 0.3s ease;
        }

        .comment-author-link:hover {
            color: var(--neon-purple);
        }

        .comment-rating {
            color: #ffd700;
            font-size: 1.2rem;
        }

        .comment-content {
            margin: 15px 0;
            line-height: 1.6;
            color: #e1e1e1;
            font-size: 1rem;
        }

        .comment-date {
            color: #999;
            font-size: 0.9rem;
            margin-bottom: 15px;
        }

        .comment-actions {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
        }

        .btn-small {
            padding: 6px 12px;
            font-size: 0.85rem;
            background-color: transparent;
            border: 2px solid var(--neon-blue);
            color: var(--neon-blue);
            border-radius: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-small:hover {
            background-color: var(--neon-blue);
            color: var(--darker-bg);
        }

        .btn-small.delete {
            border-color: #dc3545;
            color: #dc3545;
        }

        .btn-small.delete:hover {
            background-color: #dc3545;
            color: white;
        }

        /* Formulaire de réponse */
        .reply-form {
            margin-top: 15px;
            padding: 15px;
            background-color: rgba(0, 0, 0, 0.3);
            border-radius: 8px;
            border: 1px solid rgba(0, 243, 255, 0.2);
        }

        .reply-textarea {
            width: 100%;
            min-height: 80px;
            padding: 10px;
            background-color: rgba(0, 243, 255, 0.1);
            border: 2px solid rgba(0, 243, 255, 0.3);
            border-radius: 6px;
            color: white;
            font-family: 'Rajdhani', sans-serif;
            resize: vertical;
            box-sizing: border-box;
        }

        .reply-buttons {
            display: flex;
            gap: 8px;
            margin-top: 10px;
        }

        .reply-buttons .btn-small.cancel {
            border-color: #999;
            color: #999;
        }

        .reply-buttons .btn-small.cancel:hover {
            background-color: #999;
            color: white;
        }

        /* Réponses */
        .replies-container {
            margin-left: 30px;
            margin-top: 15px;
        }

        .reply-card {
            background-color: rgba(20, 20, 30, 0.6);
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
            border-left: 3px solid var(--neon-blue);
        }

        .reply-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .reply-user-info {
            display: flex;
            align-items: center;
        }

        .reply-profile-picture,
        .default-reply-icon {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            margin-right: 10px;
        }

        .reply-profile-picture {
            object-fit: cover;
            border: 2px solid var(--neon-blue);
        }

        .default-reply-icon {
            background-color: var(--neon-blue);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
        }

        .reply-author {
            font-weight: 600;
            color: var(--neon-blue);
        }

        .reply-author-link {
            text-decoration: none;
            color: inherit;
            transition: color 0.3s ease;
        }

        .reply-author-link:hover {
            color: var(--neon-purple);
        }

        .reply-content {
            color: #e1e1e1;
            line-height: 1.5;
            margin-bottom: 8px;
        }

        .reply-date {
            color: #999;
            font-size: 0.8rem;
        }

        .reply-actions {
            margin-top: 8px;
            text-align: right;
        }

        /* Skeleton loader */
        .comments-skeleton {
            margin: 20px 0;
        }

        .skeleton-card {
            background-color: rgba(30, 30, 30, 0.5);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .skeleton-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .skeleton-flex {
            display: flex;
            align-items: center;
        }

        .skeleton-header-col {
            margin-left: 12px;
        }

        .skeleton-loading {
            background: linear-gradient(90deg, rgba(255,255,255,0.1) 25%, rgba(255,255,255,0.2) 50%, rgba(255,255,255,0.1) 75%);
            background-size: 200% 100%;
            animation: skeleton-loading 1.5s infinite;
            border-radius: 4px;
        }

        @keyframes skeleton-loading {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }

        /* Bouton de retour */
        .back-link {
            display: inline-block;
            margin-top: 40px;
            padding: 12px 24px;
            background-color: transparent;
            border: 2px solid var(--neon-blue);
            color: var(--neon-blue);
            text-decoration: none;
            border-radius: 25px;
            font-family: 'Orbitron', sans-serif;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .back-link:hover {
            background-color: var(--neon-blue);
            color: var(--darker-bg);
            box-shadow: 0 0 20px rgba(0, 243, 255, 0.5);
        }

        /* Design responsive */
        @media (max-width: 768px) {
            .movie-main-content {
                grid-template-columns: 1fr;
                gap: 25px;
            }

            .movie-poster-section {
                order: 1;
            }

            .movie-info-section {
                order: 2;
                padding: 20px;
            }

            .movie-poster {
                max-width: 280px;
            }

            .replies-container {
                margin-left: 15px;
            }

            .comment-header,
            .reply-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }
        }

        @media (max-width: 480px) {
            .movie-detail-page {
                padding: 15px;
            }

            .movie-title {
                font-size: 1.8rem;
            }

            .trailer-section,
            .comment-form,
            .movie-info-section {
                padding: 20px 15px;
            }

            .comment-actions {
                flex-wrap: wrap;
            }

            .reply-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <?php require_once APP_PATH . '/Views/includes/header.php'; ?>
    
    <div class="movie-detail-page">
        <?php if (!empty($movie)): ?>
            <h1 class="movie-title"><?= htmlspecialchars($movie['title']) ?></h1>
            
            <!-- Messages d'alerte -->
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error">
                    <?= htmlspecialchars($_SESSION['error']) ?>
                    <?php unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <?= htmlspecialchars($_SESSION['success']) ?>
                    <?php unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>
            
            <!-- Section favoris -->
            <?php if (isset($_SESSION['user']) || isset($_SESSION['user_id'])): ?>
                <div class="favorites-section">
                    <?php if ($isFavorite): ?>
                        <form action="index.php?action=removeFavorite" method="POST" class="favorite-form">
                            <input type="hidden" name="movie_id" value="<?= $movie['id'] ?>">
                            <input type="hidden" name="csrf_token" value="<?= isset($_SESSION['csrf_token']) ? $_SESSION['csrf_token'] : '' ?>">
                            <button type="submit" class="favorite-btn active">
                                ★ Retirer des favoris
                            </button>
                        </form>
                    <?php else: ?>
                        <form action="index.php?action=addFavorite" method="POST" class="favorite-form">
                            <input type="hidden" name="movie_id" value="<?= $movie['id'] ?>">
                            <input type="hidden" name="csrf_token" value="<?= isset($_SESSION['csrf_token']) ? $_SESSION['csrf_token'] : '' ?>">
                            <button type="submit" class="favorite-btn">
                                ☆ Ajouter aux favoris
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- Contenu principal du film -->
            <div class="movie-main-content">
                <div class="movie-poster-section">
                    <?php if (!empty($movie['poster_path'])): ?>
                        <img class="movie-poster" 
                             src="https://image.tmdb.org/t/p/w500<?= htmlspecialchars($movie['poster_path']) ?>" 
                             alt="<?= htmlspecialchars($movie['title']) ?>">
                    <?php endif; ?>
                </div>
                
                <div class="movie-info-section">
                    <p><strong>Date de sortie:</strong> <?= htmlspecialchars($movie['release_date']) ?></p>
                    <?php if (!empty($movie['overview'])): ?>
                        <p><strong>Synopsis:</strong> <?= htmlspecialchars($movie['overview']) ?></p>
                    <?php endif; ?>
                    <div class="movie-rating">
                        <strong>Note moyenne:</strong> <?= htmlspecialchars($movie['vote_average']) ?>/10
                    </div>
                </div>
            </div>

            <!-- Section bande-annonce -->
            <div class="trailer-section">
                <h2 class="trailer-title">Bande Annonce</h2>
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
                <?php endif; ?>
            </div>

            <!-- Section commentaires -->
            <div class="comments-section">
                <h2 class="comments-title">Commentaires</h2>
                
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

                <!-- Skeleton loader -->
                <div id="comments-skeleton" class="comments-skeleton">
                    <?php for($i = 0; $i < 3; $i++): ?>
                    <div class="skeleton-card">
                        <div class="skeleton-header">
                            <div class="skeleton-flex">
                                <div class="skeleton-loading" style="width: 40px; height: 40px; border-radius: 50%;"></div>
                                <div class="skeleton-header-col">
                                    <div class="skeleton-loading" style="width: 120px; height: 14px; margin-bottom: 5px;"></div>
                                    <div class="skeleton-loading" style="width: 80px; height: 10px;"></div>
                                </div>
                            </div>
                            <div class="skeleton-loading" style="width: 80px; height: 14px;"></div>
                        </div>
                        <div class="skeleton-loading" style="width: 100%; height: 60px; margin-bottom: 10px;"></div>
                        <div class="skeleton-loading" style="width: 120px; height: 12px; margin-top: 10px;"></div>
                    </div>
                    <?php endfor; ?>
                </div>

                <!-- Liste des commentaires -->
                <div class="comments-list" id="comments-container" style="display: none;">
                    <?php if (!empty($comments)): ?>
                        <?php foreach ($comments as $comment): ?>
                            <div class="comment-card" id="comment-<?= $comment['id'] ?>">
                                <div class="comment-header">
                                    <div class="comment-user-info">
                                        <?php if (!empty($comment['profile_picture']) && $comment['profile_picture'] !== 'assets/img/default-profile.png'): ?>
                                            <a href="index.php?action=profile&user_id=<?= $comment['user_id'] ?>" class="comment-author-link">
                                                <img src="<?= $comment['profile_picture'] ?>" 
                                                    alt="Photo de profil" class="comment-profile-picture">
                                            </a>
                                        <?php else: ?>
                                            <a href="index.php?action=profile&user_id=<?= $comment['user_id'] ?>" class="comment-author-link">
                                                <div class="default-comment-icon">
                                                    <i class="fa-solid fa-user"></i>
                                                </div>
                                            </a>
                                        <?php endif; ?>
                                        <a href="index.php?action=profile&user_id=<?= $comment['user_id'] ?>" class="comment-author-link">
                                            <span class="comment-author"><?= htmlspecialchars($comment['username']) ?></span>
                                        </a>
                                    </div>
                                    <span class="comment-rating"><?= str_repeat('★', $comment['rating']) ?></span>
                                </div>
                                <p class="comment-content"><?= nl2br(htmlspecialchars($comment['content'])) ?></p>
                                <span class="comment-date"><?= date('d/m/Y H:i', strtotime($comment['created_at'])) ?></span>
                                
                                <div class="comment-actions">
                                    <?php if (isset($_SESSION['user'])): ?>
                                        <button class="btn-small reply-btn" 
                                                onclick="toggleReplyForm(<?= $comment['id'] ?>)">
                                            Répondre
                                        </button>
                                    <?php endif; ?>
                                    
                                    <?php 
                                    $isAdmin = false;
                                    if (isset($_SESSION['user']['role']) && ($_SESSION['user']['role'] === 'admin' || $_SESSION['user']['role'] === 'superadmin')) {
                                        $isAdmin = true;
                                    } elseif (isset($_SESSION['role']) && ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'superadmin')) {
                                        $isAdmin = true;
                                    }
                                    
                                    if ($isAdmin): 
                                    ?>
                                        <a href="index.php?action=deleteComment&comment_id=<?= $comment['id'] ?>&movie_id=<?= $movie['id'] ?>" 
                                           class="btn-small delete" 
                                           onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce commentaire?')">
                                            <i class="fas fa-trash"></i> Supprimer
                                        </a>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Formulaire de réponse -->
                                <?php if (isset($_SESSION['user'])): ?>
                                    <div id="reply-form-<?= $comment['id'] ?>" class="reply-form" style="display: none;">
                                        <form onsubmit="submitReply(event, <?= $comment['id'] ?>, <?= $movie['id'] ?>)">
                                            <div class="form-group">
                                                <textarea name="reply-content" 
                                                        id="reply-content-<?= $comment['id'] ?>" 
                                                        placeholder="Votre réponse..." 
                                                        class="reply-textarea"
                                                        required></textarea>
                                            </div>
                                            <div class="reply-buttons">
                                                <button type="submit" class="btn-small">Envoyer</button>
                                                <button type="button" class="btn-small cancel" 
                                                        onclick="toggleReplyForm(<?= $comment['id'] ?>)">Annuler</button>
                                            </div>
                                        </form>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Réponses -->
                                <?php if (!empty($comment['replies'])): ?>
                                    <div class="replies-container" id="replies-container-<?= $comment['id'] ?>">
                                        <?php foreach ($comment['replies'] as $reply): ?>
                                            <div class="reply-card">
                                                <div class="reply-header">
                                                    <div class="reply-user-info">
                                                        <?php if (!empty($reply['profile_picture']) && $reply['profile_picture'] !== 'assets/img/default-profile.png'): ?>
                                                            <a href="index.php?action=profile&user_id=<?= $reply['user_id'] ?>" class="reply-author-link">
                                                                <img src="<?= $reply['profile_picture'] ?>" 
                                                                    alt="Photo de profil" class="reply-profile-picture">
                                                            </a>
                                                        <?php else: ?>
                                                            <a href="index.php?action=profile&user_id=<?= $reply['user_id'] ?>" class="reply-author-link">
                                                                <div class="default-reply-icon">
                                                                    <i class="fa-solid fa-user"></i>
                                                                </div>
                                                            </a>
                                                        <?php endif; ?>
                                                        <a href="index.php?action=profile&user_id=<?= $reply['user_id'] ?>" class="reply-author-link">
                                                            <span class="reply-author"><?= htmlspecialchars($reply['username']) ?></span>
                                                        </a>
                                                    </div>
                                                    <span class="reply-date"><?= date('d/m/Y H:i', strtotime($reply['created_at'])) ?></span>
                                                </div>
                                                <p class="reply-content"><?= nl2br(htmlspecialchars($reply['content'])) ?></p>
                                                
                                                <?php if ($isAdmin): ?>
                                                    <div class="reply-actions">
                                                        <a href="index.php?action=deleteCommentReply&reply_id=<?= $reply['id'] ?>&movie_id=<?= $movie['id'] ?>" 
                                                           class="btn-small delete" 
                                                           onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette réponse?')">
                                                            <i class="fas fa-trash"></i> Supprimer
                                                        </a>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="replies-container" id="replies-container-<?= $comment['id'] ?>"></div>
                                <?php endif; ?>
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
        
        <a href="index.php" class="back-link">Retour à la liste</a>
    </div>

    <!-- Scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Gestion des skeletons
            const commentsSkeleton = document.getElementById('comments-skeleton');
            const commentsContainer = document.getElementById('comments-container');
            
            setTimeout(() => {
                commentsSkeleton.style.display = 'none';
                commentsContainer.style.display = 'block';
                
                const commentCards = document.querySelectorAll('.comment-card');
                commentCards.forEach((card, index) => {
                    card.style.opacity = '0';
                    card.style.transform = 'translateY(20px)';
                    
                    setTimeout(() => {
                        card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                        card.style.opacity = '1';
                        card.style.transform = 'translateY(0)';
                    }, 100 * index);
                });
            }, 1500);
        });
        
        // Fonction pour afficher/masquer le formulaire de réponse
        function toggleReplyForm(commentId) {
            const replyForm = document.getElementById(`reply-form-${commentId}`);
            if (replyForm.style.display === 'none' || replyForm.style.display === '') {
                replyForm.style.display = 'block';
                const textarea = document.getElementById(`reply-content-${commentId}`);
                setTimeout(() => textarea.focus(), 100);
            } else {
                replyForm.style.display = 'none';
            }
        }
        
        // Fonction pour soumettre une réponse
        async function submitReply(event, commentId, movieId) {
            event.preventDefault();
            
            const textarea = document.getElementById(`reply-content-${commentId}`);
            const content = textarea.value.trim();
            
            if (!content) {
                alert('Veuillez entrer un message');
                return;
            }
            
            const submitBtn = event.target.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.textContent;
            submitBtn.disabled = true;
            submitBtn.textContent = 'Envoi...';
            
            try {
                const formData = new FormData();
                formData.append('comment_id', commentId);
                formData.append('content', content);
                formData.append('movie_id', movieId);
                
                const response = await fetch('index.php?action=addReply', {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                
                if (!response.ok) {
                    throw new Error(`Erreur serveur: ${response.status}`);
                }
                
                const data = await response.json();
                
                if (data.success) {
                    addReplyToDOM(data.reply, commentId);
                    textarea.value = '';
                    toggleReplyForm(commentId);
                } else {
                    alert(data.message || 'Erreur lors de l\'ajout de la réponse');
                }
            } catch (error) {
                console.error('Erreur:', error);
                alert('Une erreur est survenue lors de l\'envoi de votre réponse');
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = originalBtnText;
            }
        }
        
        // Fonction pour ajouter une réponse au DOM
        function addReplyToDOM(reply, commentId) {
            const repliesContainer = document.getElementById(`replies-container-${commentId}`);
            
            const profileImage = reply.profile_picture && reply.profile_picture !== 'assets/img/default-profile.png'
                ? `<a href="index.php?action=profile&user_id=${reply.user_id}" class="reply-author-link"><img src="${reply.profile_picture}" alt="Photo de profil" class="reply-profile-picture"></a>`
                : `<a href="index.php?action=profile&user_id=${reply.user_id}" class="reply-author-link"><div class="default-reply-icon"><i class="fa-solid fa-user"></i></div></a>`;
            
            const formattedDate = new Date(reply.created_at).toLocaleString('fr-FR', {
                day: '2-digit', month: '2-digit', year: 'numeric',
                hour: '2-digit', minute: '2-digit'
            });
            
            const newReplyHTML = `
                <div class="reply-card">
                    <div class="reply-header">
                        <div class="reply-user-info">
                            ${profileImage}
                            <a href="index.php?action=profile&user_id=${reply.user_id}" class="reply-author-link">
                                <span class="reply-author">${reply.username}</span>
                            </a>
                        </div>
                        <span class="reply-date">${formattedDate}</span>
                    </div>
                    <p class="reply-content">${reply.content}</p>
                </div>
            `;
            
            repliesContainer.innerHTML += newReplyHTML;
            
            const replyCard = repliesContainer.lastElementChild;
            if (replyCard) {
                replyCard.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        }
    </script>
</body>
</html> 