    <div class="movies-grid" id="movies-container">
        <!-- Skeleton loader (affiché pendant le chargement) -->
        <div id="skeleton-grid" class="skeleton-grid">
            <?php for ($i = 0; $i < 12; $i++): ?>
            <div class="skeleton-card">
                <div class="skeleton-image skeleton-loading"></div>
                <div style="padding: 10px;">
                    <div class="skeleton-text skeleton-loading" style="margin-top: 10px;"></div>
                    <div class="skeleton-text-sm skeleton-loading" style="margin-top: 8px;"></div>
                </div>
            </div>
            <?php endfor; ?>
        </div>
        
        <?php if (!empty($movies)): ?>
            <?php foreach ($movies as $movie): ?>
                <div class="movie-card">
                    <a href="index.php?action=view&id=<?= $movie['id'] ?>">
                        <?php if (!empty($movie['poster_path'])): ?>
                            <img src="https://image.tmdb.org/t/p/w300<?= htmlspecialchars($movie['poster_path']) ?>" 
                                alt="<?= htmlspecialchars($movie['title']) ?>" loading="lazy" 
                                class="movie-poster">
                        <?php else: ?>
                            <div class="no-poster">
                                <i class="fa-solid fa-film"></i>
                                <span>Pas d'affiche</span>
                            </div>
                        <?php endif; ?>
                        <div class="movie-rating">
                            <span><?= number_format($movie['vote_average'], 1) ?></span>
                            <i class="fa-solid fa-star"></i>
                        </div>
                        <div class="movie-info">
                            <h2><?= htmlspecialchars($movie['title']) ?></h2>
                            <p class="movie-year"><?= substr($movie['release_date'], 0, 4) ?></p>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Aucun film trouvé.</p>
        <?php endif; ?>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Cacher le skeleton une fois que le contenu est chargé
            const skeletonGrid = document.getElementById('skeleton-grid');
            const moviesContainer = document.getElementById('movies-container');
            const movieCards = document.querySelectorAll('.movie-card');
            
            // Masquer les cartes de films pendant le chargement
            movieCards.forEach(card => {
                card.style.display = 'none';
            });
            
            // Variable pour suivre le nombre d'images chargées
            let loadedImages = 0;
            const totalImages = movieCards.length;
            
            // Préchargement des images pour les affiches
            const posterImages = document.querySelectorAll('.movie-poster');
            
            // Si aucune image à charger, afficher directement le contenu
            if (posterImages.length === 0) {
                showContent();
            } else {
                posterImages.forEach(img => {
                    // Gérer les images déjà en cache ou chargées rapidement
                    if (img.complete) {
                        imageLoaded();
                    } else {
                        // Écouter l'événement load
                        img.addEventListener('load', imageLoaded);
                    }
                    
                    img.addEventListener('error', function() {
                        // En cas d'erreur de chargement d'image, remplacer par l'icône
                        const noImage = document.createElement('div');
                        noImage.className = 'no-poster';
                        noImage.innerHTML = '<i class="fa-solid fa-film"></i><span>Pas d\'affiche</span>';
                        this.parentNode.replaceChild(noImage, this);
                        
                        // Marquer comme chargée même en cas d'erreur
                        imageLoaded();
                    });
                });
            }
            
            // Fonction appelée quand une image est chargée
            function imageLoaded() {
                loadedImages++;
                
                // Si toutes les images sont chargées ou après un certain pourcentage
                if (loadedImages >= totalImages || loadedImages >= Math.max(3, Math.floor(totalImages * 0.3))) {
                    showContent();
                }
            }
            
            // Fonction pour afficher le contenu
            function showContent() {
                // Masquer le skeleton
                skeletonGrid.style.display = 'none';
                
                // Afficher les cartes avec une animation
                movieCards.forEach((card, index) => {
                    setTimeout(() => {
                        card.style.display = 'block';
                        card.classList.add('skeleton-fade');
                    }, 50 * index);
                });
            }
        });

        // Fonction pour le bouton "Charger plus"
        async function loadMoreMovies(page) {
            const loadMoreBtn = document.getElementById('load-more-btn');
            if (loadMoreBtn) {
                loadMoreBtn.textContent = 'Chargement...';
                loadMoreBtn.disabled = true;
            }
            
            try {
                const response = await fetch(`index.php?action=loadMore&page=${page}`);
                const data = await response.json();
                
                if (data.html) {
                    const moviesContainer = document.getElementById('movies-container');
                    
                    // Créer un conteneur temporaire pour parser le HTML
                    const tempContainer = document.createElement('div');
                    tempContainer.innerHTML = data.html;
                    
                    // Ajouter chaque élément avec animation
                    const newMovies = tempContainer.querySelectorAll('.movie-card');
                    newMovies.forEach((movie, index) => {
                        // Masquer initialement
                        movie.style.opacity = '0';
                        movie.style.transform = 'translateY(20px)';
                        
                        // Ajouter au DOM
                        moviesContainer.appendChild(movie);
                        
                        // Animer après un court délai
                        setTimeout(() => {
                            movie.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                            movie.style.opacity = '1';
                            movie.style.transform = 'translateY(0)';
                        }, 50 * index);
                    });
                    
                    if (data.has_more) {
                        if (loadMoreBtn) {
                            loadMoreBtn.textContent = 'Charger plus';
                            loadMoreBtn.disabled = false;
                            loadMoreBtn.onclick = function() {
                                loadMoreMovies(page + 1);
                            };
                        }
                    } else if (loadMoreBtn) {
                        loadMoreBtn.remove();
                    }
                }
            } catch (error) {
                console.error('Erreur lors du chargement des films:', error);
                if (loadMoreBtn) {
                    loadMoreBtn.textContent = 'Réessayer';
                    loadMoreBtn.disabled = false;
                }
            }
        }
    </script> 