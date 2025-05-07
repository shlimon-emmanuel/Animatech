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
        /* Styles supplémentaires */
        .comment-author-link, .reply-author-link {
            text-decoration: none;
            color: inherit;
            transition: color 0.3s ease;
        }
        
        .comment-author-link:hover, .reply-author-link:hover {
            color: #6E54FF;
            text-decoration: underline;
        }
        
        .comment-author, .reply-author {
            font-weight: bold;
        }
        
        /* Reste du CSS */
        .movie-detail-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
            display: flex;
            flex-direction: column;
            gap: 2rem;
        }
        
        /* Styles pour les commentaires */
        .comment-card {
            position: relative;
            background-color: rgba(30, 30, 30, 0.8);
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
            border-left: 3px solid #7b2cbf;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .comment-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.3);
        }
        
        .comment-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .comment-user-info {
            display: flex;
            align-items: center;
        }
        
        .comment-profile-picture {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 10px;
        }
        
        .default-comment-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #7b2cbf;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
        }
        
        .comment-author {
            font-weight: 600;
            color: #fff;
        }
        
        .comment-author-link {
            text-decoration: none;
            color: inherit;
        }
        
        .comment-rating {
            color: #ffd700;
            font-size: 18px;
        }
        
        .comment-content {
            margin-bottom: 10px;
            line-height: 1.5;
            color: #e1e1e1;
        }
        
        .comment-date {
            color: #999;
            font-size: 0.8rem;
            display: block;
            margin-bottom: 10px;
        }

        .comment-actions {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
        }
        
        .delete-btn {
            background-color: #dc3545;
            border-color: #dc3545;
            color: white;
        }
        
        .delete-btn:hover {
            background-color: #c82333;
            border-color: #bd2130;
            color: white;
        }
        
        .delete-btn.small {
            font-size: 0.75rem;
            padding: 0.2rem 0.5rem;
        }
        
        .reply-actions {
            margin-top: 5px;
            display: flex;
            justify-content: flex-end;
        }
        
        /* Styles pour le squelette des commentaires */
    </style>
</head>
<body>
    <?php require_once APP_PATH . '/Views/includes/header.php'; ?>
    
    <div class="movie-detail">
        <?php if (!empty($movie)): ?>
            <h1><?= htmlspecialchars($movie['title']) ?></h1>
            
            <!-- Afficher les messages d'erreur et de succès -->
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
            
            <?php if (isset($_SESSION['user']) || isset($_SESSION['user_id'])): ?>
                <div class="favorite-section">
                    <?php if ($isFavorite): ?>
                        <form action="index.php?action=removeFavorite" method="POST" class="favorite-form" id="removeFavoriteForm">
                            <input type="hidden" name="movie_id" value="<?= $movie['id'] ?>">
                            <input type="hidden" name="csrf_token" value="<?= isset($_SESSION['csrf_token']) ? $_SESSION['csrf_token'] : '' ?>">
                            <button type="submit" class="neon-button favorite-btn active">
                                ★ Retirer des favoris
                            </button>
                        </form>
                    <?php else: ?>
                        <form action="index.php?action=addFavorite" method="POST" class="favorite-form" id="addFavoriteForm">
                            <input type="hidden" name="movie_id" value="<?= $movie['id'] ?>">
                            <input type="hidden" name="csrf_token" value="<?= isset($_SESSION['csrf_token']) ? $_SESSION['csrf_token'] : '' ?>">
                            <button type="submit" class="neon-button favorite-btn">
                                ☆ Ajouter aux favoris
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
                
                <script>
                // Logging pour débogage des favoris
                document.addEventListener('DOMContentLoaded', function() {
                    console.log("Détail du film - ID:", <?= $movie['id'] ?>);
                    console.log("Statut favori:", <?= $isFavorite ? 'true' : 'false' ?>);
                    console.log("Session utilisateur présente:", <?= isset($_SESSION['user']) ? 'true' : 'false' ?>);
                    console.log("Session user_id présente:", <?= isset($_SESSION['user_id']) ? 'true' : 'false' ?>);
                    
                    // Intercepter les soumissions de formulaires pour journalisation
                    const addFavoriteForm = document.getElementById('addFavoriteForm');
                    if (addFavoriteForm) {
                        addFavoriteForm.addEventListener('submit', function(event) {
                            console.log("Soumission du formulaire d'ajout aux favoris...");
                            // Ne pas bloquer la soumission normale
                        });
                    }
                    
                    const removeFavoriteForm = document.getElementById('removeFavoriteForm');
                    if (removeFavoriteForm) {
                        removeFavoriteForm.addEventListener('submit', function(event) {
                            console.log("Soumission du formulaire de retrait des favoris...");
                            // Ne pas bloquer la soumission normale
                        });
                    }
                });
                </script>
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

                <!-- Skeleton Loader pour les commentaires -->
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
                                    <!-- Bouton de réponse -->
                                    <?php if (isset($_SESSION['user'])): ?>
                                        <button class="reply-btn neon-button-sm" 
                                                onclick="toggleReplyForm(<?= $comment['id'] ?>)">
                                            Répondre
                                        </button>
                                    <?php endif; ?>
                                    
                                    <!-- Bouton de suppression pour les admins et superadmins -->
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
                                           class="delete-btn neon-button-sm" 
                                           onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce commentaire?')">
                                            <i class="fas fa-trash"></i> Supprimer
                                        </a>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Formulaire de réponse (initialement caché) -->
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
                                                <button type="submit" class="neon-button-sm">Envoyer</button>
                                                <button type="button" class="neon-button-sm cancel" 
                                                        onclick="toggleReplyForm(<?= $comment['id'] ?>)">Annuler</button>
                                            </div>
                                        </form>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Affichage des réponses -->
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
                                                        <span class="reply-author"><?= htmlspecialchars($reply['username']) ?></span>
                                                    </div>
                                                    <span class="reply-date"><?= date('d/m/Y H:i', strtotime($reply['created_at'])) ?></span>
                                                </div>
                                                <p class="reply-content"><?= nl2br(htmlspecialchars($reply['content'])) ?></p>
                                                
                                                <!-- Bouton de suppression pour les réponses (admin) -->
                                                <?php if ($isAdmin): ?>
                                                    <div class="reply-actions">
                                                        <a href="index.php?action=deleteCommentReply&reply_id=<?= $reply['id'] ?>&movie_id=<?= $movie['id'] ?>" 
                                                           class="delete-btn neon-button-sm small" 
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
        
        <a href="index.php" class="nav-link">Retour à la liste</a>
    </div>

    <!-- Script pour gérer les skeletons et les réponses aux commentaires -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Gestion des skeletons
            const commentsSkeleton = document.getElementById('comments-skeleton');
            const commentsContainer = document.getElementById('comments-container');
            
            // Simuler un chargement pour la démo
            setTimeout(() => {
                // Cacher le skeleton
                commentsSkeleton.style.display = 'none';
                
                // Afficher les commentaires avec animation
                commentsContainer.style.display = 'block';
                
                // Animer les commentaires
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
            }, 1500); // Délai de 1.5 secondes pour la démo
            
            // Fonction pour afficher/masquer le formulaire de réponse
            window.toggleReplyForm = function(commentId) {
                const replyForm = document.getElementById(`reply-form-${commentId}`);
                if (replyForm.style.display === 'none' || replyForm.style.display === '') {
                    replyForm.style.display = 'block';
                    
                    // Obtenir le textarea et se concentrer dessus
                    const textarea = document.getElementById(`reply-content-${commentId}`);
                    
                    // Vérifiez si le textarea est correctement récupéré
                    if (!textarea) {
                        console.error(`Textarea avec ID 'reply-content-${commentId}' non trouvé!`);
                        return;
                    }
                    
                    console.log("Textarea trouvé:", textarea);
                    
                    // Assurer que le textarea est visible et accessible
                    setTimeout(() => {
                        textarea.focus();
                        console.log("Focus appliqué sur le textarea");
                        
                        // Tester l'ajout de texte
                        textarea.value = "";
                        console.log("Valeur du textarea réinitialisée");
                    }, 100);
                } else {
                    replyForm.style.display = 'none';
                }
            };
        });
        
        // Surveillance des événements sur les textareas pour débogage
        document.addEventListener('DOMContentLoaded', function() {
            // Sélectionner tous les textareas de réponse
            const replyTextareas = document.querySelectorAll('.reply-textarea');
            
            console.log("Nombre de textareas de réponse trouvés:", replyTextareas.length);
            
            replyTextareas.forEach((textarea, index) => {
                console.log(`Textarea #${index} ID:`, textarea.id);
                
                // Ajouter des gestionnaires d'événements
                textarea.addEventListener('focus', function() {
                    console.log(`Textarea #${index} a reçu le focus`);
                });
                
                textarea.addEventListener('input', function(e) {
                    console.log(`Textarea #${index} valeur modifiée:`, this.value);
                });
                
                textarea.addEventListener('blur', function() {
                    console.log(`Textarea #${index} a perdu le focus, valeur finale:`, this.value);
                });
            });
        });
        
        // Fonction pour soumettre une réponse via AJAX
        async function submitReply(event, commentId, movieId) {
            event.preventDefault();
            
            const textarea = document.getElementById(`reply-content-${commentId}`);
            
            if (!textarea) {
                console.error(`Textarea reply-content-${commentId} non trouvé!`);
                alert("Erreur: Impossible de trouver le champ de réponse");
                return;
            }
            
            const content = textarea.value.trim();
            console.log("Contenu de la réponse:", content, "Longueur:", content.length);
            
            if (!content) {
                alert('Veuillez entrer un message');
                return;
            }
            
            // Afficher un indicateur de chargement
            const submitBtn = event.target.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.textContent;
            submitBtn.disabled = true;
            submitBtn.textContent = 'Envoi en cours...';
            
            try {
                const formData = new FormData();
                formData.append('comment_id', commentId);
                formData.append('content', content);
                formData.append('movie_id', movieId);
                
                console.log('Envoi de la réponse:', {
                    commentId: commentId,
                    movieId: movieId,
                    contentLength: content.length
                });
                
                const response = await fetch('index.php?action=addReply', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                // Vérifier si la réponse est OK
                if (!response.ok) {
                    throw new Error(`Erreur serveur: ${response.status}`);
                }
                
                let data;
                
                try {
                    data = await response.json();
                } catch (parseError) {
                    console.error('Erreur de parsing JSON:', await response.text());
                    throw new Error('La réponse du serveur n\'est pas un JSON valide');
                }
                
                console.log('Réponse du serveur:', data);
                
                if (data.success) {
                    // Ajouter la nouvelle réponse à la liste des réponses
                    addReplyToDOM(data.reply, commentId);
                    // Réinitialiser et masquer le formulaire
                    textarea.value = '';
                    toggleReplyForm(commentId);
                } else {
                    alert(data.message || 'Erreur lors de l\'ajout de la réponse');
                }
            } catch (error) {
                console.error('Erreur:', error);
                alert('Une erreur est survenue lors de l\'envoi de votre réponse: ' + error.message);
            } finally {
                // Restaurer l'état du bouton
                submitBtn.disabled = false;
                submitBtn.textContent = originalBtnText;
            }
        }
        
        // Fonction pour ajouter une réponse au DOM
        function addReplyToDOM(reply, commentId) {
            const repliesContainer = document.getElementById(`replies-container-${commentId}`);
            
            // Créer et ajouter la nouvelle réponse au DOM
            const profileImage = reply.profile_picture && reply.profile_picture !== 'assets/img/default-profile.png'
                ? `<a href="index.php?action=profile&user_id=${reply.user_id}" class="reply-author-link"><img src="${reply.profile_picture}" alt="Photo de profil" class="reply-profile-picture"></a>`
                : `<a href="index.php?action=profile&user_id=${reply.user_id}" class="reply-author-link"><div class="default-reply-icon"><i class="fa-solid fa-user"></i></div></a>`;
            
            const formattedDate = new Date(reply.created_at).toLocaleString('fr-FR', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
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
            
            // Ajouter la réponse au conteneur
            repliesContainer.innerHTML += newReplyHTML;
            
            // Faire un smooth scroll vers la nouvelle réponse
            const replyCard = repliesContainer.lastElementChild;
            if (replyCard) {
                replyCard.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        }
    </script>
</body>
</html> 