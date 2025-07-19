<?php
// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id']) && !isset($_SESSION['user']['id']) && !isset($_GET['user_id'])) {
    header('Location: index.php?action=login');
    exit;
}

// Définir les constantes si elles ne sont pas déjà définies
if (!defined('BASE_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    define('BASE_URL', $protocol . '://' . $host);
}

if (!defined('VIEW_PATH')) define('VIEW_PATH', APP_PATH . '/Views');

// Déterminer si on consulte son propre profil ou celui d'un autre utilisateur
$isOwnProfile = !isset($_GET['user_id']);

// Déterminer l'ID de l'utilisateur en fonction du format de session ou du paramètre d'URL
if ($isOwnProfile) {
    $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : (isset($_SESSION['user']['id']) ? $_SESSION['user']['id'] : null);
} else {
    $userId = (int)$_GET['user_id'];
}

error_log("Profile.php - Session data: " . print_r($_SESSION, true));
error_log("Profile.php - User ID determined: " . $userId);

// Récupérer les informations de l'utilisateur depuis la base de données
require_once APP_PATH . '/Models/UserModel.php';
$userModel = new App\Models\UserModel();
$userInfo = $userModel->getUserById($userId);
error_log("Profile.php - User info retrieved: " . print_r($userInfo, true));

// Récupérer les favoris de l'utilisateur
require_once APP_PATH . '/Models/MovieModel.php';
$movieModel = new App\Models\MovieModel(TMDB_API_KEY);
$favorites = $movieModel->getUserFavorites($userId);

// Formater les favoris pour la vue
$formattedFavorites = [];
foreach ($favorites as $movie) {
    if ($movie) {
        $formattedFavorites[] = json_decode(json_encode($movie), true);
    }
}
$favorites = $formattedFavorites;
$favoriteCount = count($favorites);

// Récupérer les commentaires et réponses de l'utilisateur
require_once APP_PATH . '/Models/CommentModel.php';
$commentModel = new App\Models\CommentModel();
$userComments = $commentModel->getUserComments($userId, 5);
error_log("Profile.php - User comments retrieved: " . print_r($userComments, true));
$commentCount = $commentModel->getUserCommentCount($userId);
error_log("Profile.php - Comment count: " . $commentCount);

// Récupérer les réponses aux commentaires
$userReplies = $commentModel->getUserReplies($userId, 5);
error_log("Profile.php - User replies retrieved: " . print_r($userReplies, true));
$replyCount = $commentModel->getUserReplyCount($userId);
error_log("Profile.php - Reply count: " . $replyCount);

// Récupérer le nom d'utilisateur
if ($isOwnProfile) {
    $username = isset($_SESSION['user']['username']) ? $_SESSION['user']['username'] : 
               (isset($_SESSION['username']) ? $_SESSION['username'] : 
               (isset($userInfo['username']) && $userInfo['username'] !== null ? $userInfo['username'] : 'Utilisateur'));
} else {
    $username = isset($userInfo['username']) && $userInfo['username'] !== null ? $userInfo['username'] : 'Utilisateur';
}

// Définir le titre de la page
$pageTitle = "Profil de " . htmlspecialchars($username);

// Initialiser les variables pour les messages flash
$successMessage = $errorMessage = '';

// Vérifier les messages de succès ou d'erreur
if (isset($_SESSION['success'])) {
    $successMessage = $_SESSION['success'];
    unset($_SESSION['success']);
}

if (isset($_SESSION['error'])) {
    $errorMessage = $_SESSION['error'];
    unset($_SESSION['error']);
}

// Obtenir l'onglet actif s'il est dans l'URL
$activeTab = isset($_GET['tab']) ? $_GET['tab'] : 'favorites';

// Valider que l'onglet est valide
if (!in_array($activeTab, ['favorites', 'reviews', 'replies'])) {
    $activeTab = 'favorites';
}

// Inclure le header qui contient la structure HTML et le menu de navigation
require_once APP_PATH . '/Views/includes/header.php';
?>

<!-- CSS spécifique pour la page de profil -->
<link rel="stylesheet" href="assets/css/profile.css">

    <div class="profile-container">
        <?php if (!empty($successMessage)): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($successMessage) ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($errorMessage)): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars($errorMessage) ?>
            </div>
        <?php endif; ?>

        <div class="profile-header">
            <!-- Skeleton pour le header de profil (affiché pendant le chargement) -->
            <div id="profile-header-skeleton" class="profile-header-skeleton" style="width: 100%; display: flex; gap: 2rem;">
                <div class="skeleton-avatar skeleton-loading"></div>
                <div style="flex: 1;">
                    <div class="skeleton-loading" style="height: 32px; width: 200px; margin-bottom: 15px;"></div>
                    <div class="skeleton-loading" style="height: 16px; width: 150px; margin-bottom: 20px;"></div>
                    <div class="skeleton-loading skeleton-button"></div>
                </div>
                <div style="display: flex; gap: 1.5rem; align-self: center;">
                    <?php for ($i = 0; $i < 3; $i++): ?>
                    <div style="display: flex; flex-direction: column; align-items: center;">
                        <div class="skeleton-loading" style="height: 35px; width: 35px; margin-bottom: 10px;"></div>
                        <div class="skeleton-loading" style="height: 14px; width: 60px;"></div>
                    </div>
                    <?php endfor; ?>
                </div>
            </div>

            <!-- Contenu réel du header (initialement caché) -->
            <div id="profile-header-content" style="display: none; width: 100%;">
                <div class="profile-avatar<?= $isOwnProfile ? ' own-profile' : '' ?>">
                    <?php if (isset($userInfo['profile_picture']) && !empty($userInfo['profile_picture']) && $userInfo['profile_picture'] !== 'assets/img/default-profile.png'): ?>
                        <?php if ($isOwnProfile): ?>
                        <img src="<?= htmlspecialchars($userInfo['profile_picture']) ?>" alt="Photo de profil" loading="lazy" 
                             onerror="this.onerror=null; this.parentNode.innerHTML='<div class=\'default-avatar\'><i class=\'fa-solid fa-user\'></i></div>';">
                        <?php else: ?>
                        <img src="<?= htmlspecialchars($userInfo['profile_picture']) ?>" alt="Photo de profil" loading="lazy" 
                             onclick="openProfileImageModal(this.src)" 
                             class="zoomable-profile-image"
                             onerror="this.onerror=null; this.parentNode.innerHTML='<div class=\'default-avatar\'><i class=\'fa-solid fa-user\'></i></div>';">
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="default-avatar">
                            <i class="fa-solid fa-user"></i>
                        </div>
                    <?php endif; ?>
                    <?php if ($isOwnProfile): ?>
                    <div class="avatar-overlay">
                        <a href="index.php?action=edit-profile" title="Changer d'image" class="change-avatar-link">
                            <i class="fas fa-camera"></i>
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="profile-info">
                    <h1><?= htmlspecialchars($username) ?></h1>
                    <p class="profile-joined">Membre depuis <?= isset($userInfo['created_at']) ? date('d/m/Y', strtotime($userInfo['created_at'])) : date('d/m/Y') ?></p>
                    <?php if ($isOwnProfile): ?>
                    <div class="profile-actions">
                        <a href="index.php?action=edit-profile" class="neon-button small">Éditer le profil</a>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="profile-stats">
                    <div class="stat-card" title="Nombre de films favoris">
                        <span class="stat-value"><?= $favoriteCount ?></span>
                        <span class="stat-label">Favoris</span>
                    </div>
                    <div class="stat-card" title="Nombre de commentaires publiés">
                        <span class="stat-value"><?= $commentCount ?></span>
                        <span class="stat-label">Avis</span>
                    </div>
                    <div class="stat-card" title="Nombre de réponses aux commentaires">
                        <span class="stat-value"><?= $replyCount ?></span>
                        <span class="stat-label">Réponses</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="profile-content">
            <div class="profile-tabs">
                <a href="index.php?action=profile&tab=favorites" class="tab-button <?= $activeTab === 'favorites' ? 'active' : '' ?>">
                    <i class="fas fa-heart"></i> Favoris
                </a>
                <a href="index.php?action=profile&tab=reviews" class="tab-button <?= $activeTab === 'reviews' ? 'active' : '' ?>">
                    <i class="fas fa-comment"></i> Avis
                </a>
                <a href="index.php?action=profile&tab=replies" class="tab-button <?= $activeTab === 'replies' ? 'active' : '' ?>">
                    <i class="fas fa-reply"></i> Réponses
                </a>
            </div>

            <!-- Onglet Favoris -->
            <div class="tab-content <?= $activeTab === 'favorites' ? 'active' : '' ?>" id="favorites-tab">
                <!-- Skeleton pour les favoris -->
                <div id="favorites-skeleton" class="skeleton-grid">
                    <?php for ($i = 0; $i < 6; $i++): ?>
                    <div class="skeleton-card">
                        <div class="skeleton-image skeleton-loading"></div>
                        <div style="padding: 10px;">
                            <div class="skeleton-text skeleton-loading" style="margin-top: 10px;"></div>
                            <div class="skeleton-text-sm skeleton-loading" style="margin-top: 8px;"></div>
                        </div>
                    </div>
                    <?php endfor; ?>
                </div>

                <!-- Contenu réel des favoris (initialement caché) -->
                <div id="favorites-content" style="display: none;">
                    <?php if (!empty($favorites)): ?>
                        <div class="favorites-grid">
                            <?php foreach ($favorites as $movie): ?>
                                <div class="favorite-card">
                                    <a href="index.php?action=view&id=<?= $movie['id'] ?>">
                                        <div class="movie-poster">
                                            <?php if (!empty($movie['poster_path'])): ?>
                                                <img src="https://image.tmdb.org/t/p/w300<?= htmlspecialchars($movie['poster_path']) ?>" 
                                                    alt="<?= htmlspecialchars($movie['title'] ?? 'Film') ?>">
                                            <?php else: ?>
                                                <div class="no-poster">
                                                    <i class="fa-solid fa-film"></i>
                                                </div>
                                            <?php endif; ?>
                                            <div class="movie-rating">
                                                <span><?= number_format($movie['vote_average'], 1) ?></span>
                                                <i class="fa-solid fa-star"></i>
                                            </div>
                                        </div>
                                        <div class="movie-info">
                                            <h3><?= htmlspecialchars($movie['title'] ?? 'Film sans titre') ?></h3>
                                            <span class="movie-year"><?= !empty($movie['release_date']) ? substr($movie['release_date'], 0, 4) : 'N/A' ?></span>
                                        </div>
                                    </a>
                                    <form action="index.php?action=removeFavorite" method="POST" class="remove-favorite">
                                        <input type="hidden" name="movie_id" value="<?= $movie['id'] ?>">
                                        <button type="submit" class="remove-btn" title="Retirer des favoris">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-section">
                            <i class="fa-solid fa-heart-broken"></i>
                            <p>Vous n'avez pas encore de films favoris.</p>
                            <a href="index.php?action=home" class="neon-button">Découvrir des films</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Onglet Avis -->
            <div class="tab-content <?= $activeTab === 'reviews' ? 'active' : '' ?>" id="reviews-tab">
                <!-- Skeleton pour les avis -->
                <div id="reviews-skeleton" class="reviews-skeleton">
                    <?php for ($i = 0; $i < 3; $i++): ?>
                    <div class="skeleton-card">
                        <div class="skeleton-header">
                            <div class="skeleton-loading" style="width: 180px; height: 16px;"></div>
                            <div class="skeleton-header-col" style="align-items: flex-end;">
                                <div class="skeleton-loading" style="width: 100px; height: 14px; margin-bottom: 5px;"></div>
                                <div class="skeleton-loading" style="width: 80px; height: 12px;"></div>
                            </div>
                        </div>
                        <div class="skeleton-loading" style="width: 100%; height: 80px; margin: 15px 0;"></div>
                        <div class="skeleton-loading" style="width: 120px; height: 14px; margin-left: auto;"></div>
                    </div>
                    <?php endfor; ?>
                </div>

                <!-- Contenu réel des avis (initialement caché) -->
                <div id="reviews-content" style="display: none;">
                    <?php if (!empty($userComments)): ?>
                        <div class="reviews-list">
                            <?php foreach ($userComments as $comment): ?>
                                <div class="review-card">
                                    <div class="review-header">
                                        <a href="index.php?action=view&id=<?= $comment['movie_id'] ?>" class="review-movie-title">
                                            <?= htmlspecialchars($comment['movie_title'] ?? 'Film sans titre') ?>
                                        </a>
                                        <div class="review-meta">
                                            <span class="review-date"><?= !empty($comment['created_at']) ? date('d/m/Y à H:i', strtotime($comment['created_at'])) : 'Date inconnue' ?></span>
                                            <?php if (isset($comment['rating'])): ?>
                                                <span class="review-rating">
                                                    <?= str_repeat('<i class="fas fa-star"></i>', $comment['rating']) ?>
                                                    <?= str_repeat('<i class="far fa-star"></i>', 5 - $comment['rating']) ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="review-content">
                                        <?= nl2br(htmlspecialchars(substr($comment['content'] ?? '', 0, 300))) ?>
                                        <?= (isset($comment['content']) && strlen($comment['content']) > 300) ? '...' : '' ?>
                                    </div>
                                    <div class="review-actions">
                                        <a href="index.php?action=view&id=<?= $comment['movie_id'] ?>#comment-<?= $comment['id'] ?>" class="review-link">
                                            Voir l'avis complet
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <?php if ($commentCount > 5): ?>
                            <div class="view-all-button">
                                <a href="index.php?action=profile&show_all_comments=1" class="neon-button">Voir tous les avis (<?= $commentCount ?>)</a>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="empty-section">
                            <i class="fa-solid fa-comment-slash"></i>
                            <p>Vous n'avez pas encore posté d'avis.</p>
                            <a href="index.php?action=home" class="neon-button">Découvrir des films</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Onglet Réponses -->
            <div class="tab-content <?= $activeTab === 'replies' ? 'active' : '' ?>" id="replies-tab">
                <!-- Skeleton pour les réponses -->
                <div id="replies-skeleton" class="replies-skeleton">
                    <?php for ($i = 0; $i < 3; $i++): ?>
                    <div class="skeleton-card">
                        <div class="skeleton-header">
                            <div class="skeleton-loading" style="width: 180px; height: 16px;"></div>
                            <div class="skeleton-loading" style="width: 100px; height: 14px;"></div>
                        </div>
                        <div class="skeleton-card" style="background-color: rgba(15, 15, 25, 0.6); margin: 10px 0; padding: 10px;">
                            <div class="skeleton-loading" style="width: 150px; height: 14px; margin-bottom: 10px;"></div>
                            <div class="skeleton-loading" style="width: 100%; height: 30px;"></div>
                        </div>
                        <div class="skeleton-loading" style="width: 100%; height: 40px; margin: 15px 0;"></div>
                        <div class="skeleton-loading" style="width: 150px; height: 14px; margin-left: auto;"></div>
                    </div>
                    <?php endfor; ?>
                </div>

                <!-- Contenu réel des réponses (initialement caché) -->
                <div id="replies-content" style="display: none;">
                    <?php if (!empty($userReplies)): ?>
                        <div class="replies-list">
                            <?php foreach ($userReplies as $reply): ?>
                                <div class="reply-card">
                                    <div class="reply-header">
                                        <a href="index.php?action=view&id=<?= $reply['movie_id'] ?>" class="reply-movie-title">
                                            <?= htmlspecialchars($reply['movie_title'] ?? 'Film sans titre') ?>
                                        </a>
                                        <span class="reply-date"><?= !empty($reply['created_at']) ? date('d/m/Y à H:i', strtotime($reply['created_at'])) : 'Date inconnue' ?></span>
                                    </div>
                                    <div class="reply-context">
                                        <div class="original-comment">
                                            <span class="comment-author">Réponse à <strong><?= htmlspecialchars($reply['comment_author']) ?></strong> :</span>
                                            <p class="comment-preview">"<?= htmlspecialchars(substr($reply['original_comment'], 0, 100)) . (strlen($reply['original_comment']) > 100 ? '...' : '') ?>"</p>
                                        </div>
                                    </div>
                                    <div class="reply-content">
                                        <p><?= nl2br(htmlspecialchars($reply['content'])) ?></p>
                                    </div>
                                    <div class="reply-actions">
                                        <a href="index.php?action=view&id=<?= $reply['movie_id'] ?>#comment-<?= $reply['comment_id'] ?>" class="reply-link">
                                            Voir la discussion complète
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <?php if ($replyCount > 5): ?>
                            <div class="view-all-button">
                                <a href="index.php?action=profile&show_all_replies=1" class="neon-button">Voir toutes les réponses (<?= $replyCount ?>)</a>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="empty-section">
                            <i class="fa-solid fa-reply-slash"></i>
                            <p>Vous n'avez pas encore répondu à des commentaires.</p>
                            <a href="index.php?action=home" class="neon-button">Participer aux discussions</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal pour agrandir la photo de profil -->
    <div id="profileImageModal" class="image-modal">
        <span class="close-modal" onclick="closeProfileImageModal()">&times;</span>
        <img class="modal-content" id="modalProfileImage">
    </div>

    <style>
        /* Styles pour la page de profil */
        .profile-container {
            max-width: 1200px;
            margin: 80px auto 40px; /* Ajustement pour laisser de la place au header fixe */
            padding: 0 2rem;
            color: var(--text-color);
        }

        /* Alertes */
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            animation: fadeIn 0.5s ease-out;
        }

        .alert-success {
            background-color: rgba(0, 128, 0, 0.2);
            border: 1px solid var(--neon-green);
            color: var(--neon-green);
        }

        .alert-error {
            background-color: rgba(255, 0, 0, 0.2);
            border: 1px solid var(--neon-red);
            color: var(--neon-red);
        }

        /* Header de profil */
        .profile-header {
            display: flex;
            flex-wrap: wrap;
            gap: 2rem;
            padding: 2rem;
            background-color: rgba(20, 20, 35, 0.7);
            border-radius: 12px;
            box-shadow: 0 0 20px rgba(110, 84, 255, 0.3);
            position: relative;
            overflow: hidden;
            margin-bottom: 4rem; /* Augmenté pour avoir plus d'espace entre le header et les onglets */
        }

        .profile-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, var(--neon-purple), var(--neon-blue));
            box-shadow: 0 0 10px var(--neon-blue);
            z-index: 1;
        }

        .profile-avatar {
            flex: 0 0 120px;
            position: relative;
        }

        /* Ajouter le curseur pointer uniquement pour son propre profil */
        .profile-avatar.own-profile {
            cursor: pointer;
        }

        .profile-avatar img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--neon-purple);
            box-shadow: 0 0 10px var(--neon-blue);
            transition: filter 0.3s ease;
        }

        .avatar-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            background-color: rgba(20, 20, 35, 0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .profile-avatar.own-profile:hover .avatar-overlay {
            opacity: 1;
        }

        .profile-avatar.own-profile:hover img, 
        .profile-avatar.own-profile:hover .default-avatar {
            filter: blur(2px);
        }

        .change-avatar-link {
            color: var(--neon-blue);
            font-size: 2rem;
            text-decoration: none;
            text-shadow: 0 0 10px var(--neon-blue);
            transition: transform 0.3s ease;
        }

        .change-avatar-link:hover {
            transform: scale(1.2);
        }

        .default-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background-color: var(--darker-bg);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: var(--neon-purple);
            border: 2px solid var(--neon-purple);
            box-shadow: 0 0 10px var(--neon-blue);
            transition: filter 0.3s ease;
        }

        .profile-info {
            flex: 1;
            min-width: 250px;
        }

        .profile-info h1 {
            font-family: 'Orbitron', sans-serif;
            font-size: 2.5rem;
            margin: 0 0 0.5rem 0;
            color: var(--neon-blue);
            text-shadow: 0 0 5px var(--neon-blue);
        }

        .profile-joined {
            color: var(--text-color);
            opacity: 0.7;
            margin-bottom: 1rem;
        }

        .profile-actions {
            margin-top: 1rem;
        }

        .profile-stats {
            display: flex;
            gap: 1.5rem;
            padding: 1rem;
            background-color: rgba(15, 15, 25, 0.6);
            border-radius: 8px;
            align-self: center;
            flex-wrap: wrap;
        }

        .stat-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            min-width: 80px;
        }

        .stat-value {
            font-family: 'Orbitron', sans-serif;
            font-size: 2rem;
            color: var(--neon-purple);
            text-shadow: 0 0 5px var(--neon-purple);
        }

        .stat-label {
            font-size: 0.85rem;
            color: var(--text-color);
            opacity: 0.8;
        }

        /* Tabs */
        .profile-tabs {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
            margin-bottom: 1.5rem;
            border-bottom: 1px solid rgba(110, 84, 255, 0.3);
            padding-bottom: 0.5rem;
        }

        .tab-button {
            background: none;
            border: none;
            color: var(--text-color);
            font-family: 'Orbitron', sans-serif;
            font-size: 1.1rem;
            padding: 0.5rem 1rem;
            cursor: pointer;
            position: relative;
            transition: all 0.3s ease;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .tab-button:hover {
            color: var(--neon-blue);
        }

        .tab-button.active {
            color: var(--neon-blue);
        }

        .tab-button.active::after {
            content: '';
            position: absolute;
            bottom: -0.5rem;
            left: 0;
            width: 100%;
            height: 3px;
            background-color: var(--neon-blue);
            box-shadow: 0 0 8px var(--neon-blue);
        }

        .tab-content {
            display: none;
            animation: fadeIn 0.5s ease-out;
        }

        .tab-content.active {
            display: block;
        }

        /* Favorites Section */
        .favorites-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .favorite-card {
            position: relative;
            transition: transform 0.3s ease;
            background-color: rgba(20, 20, 35, 0.5);
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
        }

        .favorite-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(5, 217, 232, 0.2);
        }

        .favorite-card a {
            text-decoration: none;
            color: var(--text-color);
        }

        .movie-poster {
            position: relative;
            height: 260px;
            overflow: hidden;
        }

        .movie-poster img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .favorite-card:hover .movie-poster img {
            transform: scale(1.05);
        }

        .no-poster {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: var(--darker-bg);
            color: var(--neon-purple);
            font-size: 3rem;
        }

        .movie-rating {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: rgba(0, 0, 0, 0.7);
            color: var(--neon-purple);
            padding: 0.3rem 0.5rem;
            border-radius: 4px;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 0.3rem;
            font-size: 0.9rem;
            z-index: 2;
        }

        .movie-info {
            padding: 1rem;
        }

        .movie-info h3 {
            font-family: 'Orbitron', sans-serif;
            font-size: 1rem;
            margin: 0 0 0.5rem 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .movie-year {
            font-size: 0.8rem;
            opacity: 0.7;
        }

        .remove-favorite {
            position: absolute;
            top: 10px;
            left: 10px;
            z-index: 3;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .favorite-card:hover .remove-favorite {
            opacity: 1;
        }

        .remove-btn {
            background-color: rgba(255, 42, 109, 0.8);
            color: white;
            border: none;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .remove-btn:hover {
            background-color: var(--neon-red);
            transform: scale(1.1);
            box-shadow: 0 0 10px var(--neon-red);
        }

        /* Reviews & Replies Sections */
        .reviews-list, .replies-list {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .review-card, .reply-card {
            background-color: rgba(20, 20, 35, 0.7);
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 0 5px rgba(110, 84, 255, 0.2);
            transition: box-shadow 0.3s;
            position: relative;
            overflow: hidden;
        }

        .review-card::after, .reply-card::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 3px;
            height: 100%;
            background-color: var(--neon-purple);
        }

        .review-card:hover, .reply-card:hover {
            box-shadow: 0 0 10px var(--neon-blue);
        }

        .review-header, .reply-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid rgba(110, 84, 255, 0.2);
        }

        .review-movie-title, .reply-movie-title {
            font-family: 'Orbitron', sans-serif;
            font-size: 1.1rem;
            color: var(--neon-blue);
            text-decoration: none;
            text-shadow: 0 0 3px var(--neon-blue);
            max-width: 70%;
        }

        .review-movie-title:hover, .reply-movie-title:hover {
            text-decoration: underline;
        }

        .review-meta {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 0.5rem;
        }

        .review-date, .reply-date {
            font-size: 0.85rem;
            color: var(--text-color);
            opacity: 0.7;
        }

        .review-rating {
            color: var(--neon-purple);
        }

        .review-rating i, .fas.fa-star {
            color: var(--neon-purple);
        }

        .far.fa-star {
            color: rgba(157, 78, 221, 0.4);
        }

        .review-content, .reply-content {
            line-height: 1.6;
            margin-bottom: 1rem;
        }

        .reply-context {
            background-color: rgba(15, 15, 25, 0.6);
            border-radius: 5px;
            padding: 1rem;
            margin-bottom: 1rem;
        }

        .original-comment {
            position: relative;
            padding-left: 1rem;
        }

        .original-comment::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 2px;
            height: 100%;
            background-color: var(--neon-pink);
        }

        .comment-author {
            font-size: 0.9rem;
            color: var(--text-color);
            margin-bottom: 0.5rem;
            display: block;
        }

        .comment-preview {
            font-style: italic;
            opacity: 0.8;
            margin: 0;
        }

        .review-actions, .reply-actions {
            text-align: right;
        }

        .review-link, .reply-link {
            color: var(--neon-purple);
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.3s ease;
        }

        .review-link:hover, .reply-link:hover {
            color: var(--neon-blue);
            text-decoration: underline;
        }

        /* Empty States */
        .empty-section {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 3rem 1rem;
            text-align: center;
            background-color: rgba(20, 20, 35, 0.3);
            border-radius: 8px;
            border: 1px dashed rgba(110, 84, 255, 0.3);
        }

        .empty-section i {
            font-size: 3rem;
            color: var(--neon-purple);
            margin-bottom: 1rem;
            opacity: 0.6;
        }

        .empty-section p {
            margin-bottom: 1.5rem;
            opacity: 0.8;
        }

        /* View All Button */
        .view-all-button {
            display: flex;
            justify-content: center;
            margin-top: 1rem;
        }

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Responsive */
        @media (max-width: 900px) {
            .profile-header {
                flex-direction: column;
                align-items: center;
                gap: 1.5rem;
                text-align: center;
            }
            
            .profile-info {
                display: flex;
                flex-direction: column;
                align-items: center;
            }
            
            .profile-stats {
                width: 100%;
                justify-content: center;
            }
            
            .favorites-grid {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            }
            
            .review-header, .reply-header {
                flex-direction: column;
                gap: 0.5rem;
                align-items: flex-start;
            }
            
            .review-meta {
                align-items: flex-start;
            }
            
            .review-movie-title, .reply-movie-title {
                max-width: 100%;
            }
        }

        @media (max-width: 600px) {
            .profile-container {
                padding: 0 1rem;
            }
            
            .profile-tabs {
                justify-content: space-between;
                flex-wrap: wrap;
            }
            
            .tab-button {
                font-size: 0.9rem;
                padding: 0.5rem;
            }
            
            .movie-poster {
                height: 200px;
            }
            
            .profile-info h1 {
                font-size: 2rem;
            }
            
            .stat-value {
                font-size: 1.5rem;
            }
            
            .favorites-grid {
                grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
                gap: 1rem;
            }
        }

        /* Styles pour la modal d'image */
        .image-modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.9);
            animation: fadeIn 0.3s ease;
        }

        .modal-content {
            margin: auto;
            display: block;
            max-width: 80%;
            max-height: 80%;
            animation: zoomIn 0.3s ease;
            border: 2px solid var(--neon-purple);
            box-shadow: 0 0 20px var(--neon-blue);
            border-radius: 5px;
        }

        .close-modal {
            position: absolute;
            top: 20px;
            right: 30px;
            color: #f1f1f1;
            font-size: 40px;
            font-weight: bold;
            transition: color 0.3s ease;
            cursor: pointer;
        }

        .close-modal:hover {
            color: var(--neon-purple);
            text-shadow: 0 0 10px var(--neon-purple);
        }

        @keyframes zoomIn {
            from {transform: scale(0.5); opacity: 0;}
            to {transform: scale(1); opacity: 1;}
        }

        /* Curseur pour les images cliquables */
        .zoomable-profile-image {
            cursor: zoom-in;
            transition: transform 0.3s ease;
        }

        .zoomable-profile-image:hover {
            transform: scale(1.05);
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Gestion des skeletons
            const profileHeaderSkeleton = document.getElementById('profile-header-skeleton');
            const profileHeaderContent = document.getElementById('profile-header-content');
            const favoritesSkeleton = document.getElementById('favorites-skeleton');
            const favoritesContent = document.getElementById('favorites-content');
            const reviewsSkeleton = document.getElementById('reviews-skeleton');
            const reviewsContent = document.getElementById('reviews-content');
            const repliesSkeleton = document.getElementById('replies-skeleton');
            const repliesContent = document.getElementById('replies-content');

            // Simuler un temps de chargement pour démontrer le skeleton
            setTimeout(() => {
                // Header de profil
                profileHeaderSkeleton.style.display = 'none';
                profileHeaderContent.style.display = 'flex';
                profileHeaderContent.style.opacity = '0';
                setTimeout(() => {
                    profileHeaderContent.style.transition = 'opacity 0.5s ease';
                    profileHeaderContent.style.opacity = '1';
                }, 100);

                // Contenu des onglets
                const activeTab = '<?= $activeTab ?>';
                
                // Favoris
                if (activeTab === 'favorites') {
                    favoritesSkeleton.style.display = 'none';
                    favoritesContent.style.display = 'block';
                    
                    // Animation des cartes de favoris
                    const favoriteCards = favoritesContent.querySelectorAll('.favorite-card');
                    favoriteCards.forEach((card, index) => {
                        card.style.opacity = '0';
                        card.style.transform = 'translateY(20px)';
                        
                        setTimeout(() => {
                            card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                            card.style.opacity = '1';
                            card.style.transform = 'translateY(0)';
                        }, 50 * index);
                    });
                }
                
                // Avis
                if (activeTab === 'reviews') {
                    reviewsSkeleton.style.display = 'none';
                    reviewsContent.style.display = 'block';
                    
                    // Animation des avis
                    const reviewCards = reviewsContent.querySelectorAll('.review-card');
                    reviewCards.forEach((card, index) => {
                        card.style.opacity = '0';
                        card.style.transform = 'translateY(20px)';
                        
                        setTimeout(() => {
                            card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                            card.style.opacity = '1';
                            card.style.transform = 'translateY(0)';
                        }, 50 * index);
                    });
                }
                
                // Réponses
                if (activeTab === 'replies') {
                    repliesSkeleton.style.display = 'none';
                    repliesContent.style.display = 'block';
                    
                    // Animation des réponses
                    const replyCards = repliesContent.querySelectorAll('.reply-card');
                    replyCards.forEach((card, index) => {
                        card.style.opacity = '0';
                        card.style.transform = 'translateY(20px)';
                        
                        setTimeout(() => {
                            card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                            card.style.opacity = '1';
                            card.style.transform = 'translateY(0)';
                        }, 50 * index);
                    });
                }
            }, 1500); // Délai de 1.5 secondes pour la démo

            // Préchargement des images pour les affiches
            const posterImages = document.querySelectorAll('.movie-poster img');
            posterImages.forEach(img => {
                const newImg = new Image();
                newImg.src = img.src;
                
                newImg.onload = function() {
                    img.classList.add('loaded');
                };
            });
            
            // Confirmation pour retirer un film des favoris
            const removeForms = document.querySelectorAll('.remove-favorite');
            removeForms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    if (!confirm('Êtes-vous sûr de vouloir retirer ce film de vos favoris ?')) {
                        e.preventDefault();
                    }
                });
            });
        });

        // Fonctions pour la modal d'image de profil
        function openProfileImageModal(imageSrc) {
            const modal = document.getElementById('profileImageModal');
            const modalImg = document.getElementById('modalProfileImage');
            modal.style.display = 'flex';
            modalImg.src = imageSrc;
            
            // Empêcher le défilement de la page quand la modal est ouverte
            document.body.style.overflow = 'hidden';
            
            // Fermer la modal quand on clique en dehors de l'image
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    closeProfileImageModal();
                }
            });
            
            // Fermer la modal avec la touche Échap
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeProfileImageModal();
                }
            });
        }

        function closeProfileImageModal() {
            const modal = document.getElementById('profileImageModal');
            modal.style.display = 'none';
            
            // Réactiver le défilement de la page
            document.body.style.overflow = 'auto';
        }
    </script>

<?php require_once APP_PATH . '/Views/includes/footer.php'; ?>