<?php require_once APP_PATH . '/Views/includes/header.php'; ?>

    <!-- Debug information -->
    <?php if (isset($_GET['debug']) && $_GET['debug'] === '1'): ?>
    <div style="padding: 20px; background-color: #333; color: #fff; margin: 20px; border-radius: 5px;">
        <h3>Debug Info</h3>
        <pre><?php 
            echo "Films à venir trouvés: " . (isset($upcomingMovies) && isset($upcomingMovies->results) ? count($upcomingMovies->results) : 'Aucun');
            if (isset($upcomingMovies) && isset($upcomingMovies->results) && !empty($upcomingMovies->results)) {
                echo "\n\nPremiers films trouvés:\n";
                $counter = 0;
                foreach ($upcomingMovies->results as $movie) {
                    if ($counter < 3) {
                        echo "- " . $movie->title . " (Sortie: " . ($movie->release_date ?? 'inconnue') . ")\n";
                        echo "  Poster: " . ($movie->poster_path ? 'Présent' : 'Absent') . "\n";
                        $counter++;
                    }
                }
            }
        ?></pre>
    </div>
    <?php endif; ?>

    <!-- Carousel de films à venir -->
    <?php if (isset($upcomingMovies) && !empty($upcomingMovies->results)): ?>
    <section class="carousel-section">
        <h2 class="carousel-title">Films Animation Les Plus Attendus</h2>
        
        <div class="carousel-container">
            <div class="carousel-track" id="carouselTrack">
                <?php 
                // Filtrer les films valides (avec affiche, date de sortie future dans les 90 jours, et langues appropriées)
                $validMovies = array_filter($upcomingMovies->results, function($movie) {
                    // First check if movie is an object and has a title
                    if (!is_object($movie) || !isset($movie->title)) {
                        return false;
                    }
                    
                    // Vérifier si le titre utilise des caractères latins
                    $hasNonLatinChars = preg_match('/[\p{Han}\p{Hiragana}\p{Katakana}\p{Cyrillic}\p{Arabic}]/u', $movie->title);
                    
                    // Calculer la date limite (90 jours à partir d'aujourd'hui)
                    $today = time();
                    $futureLimit = strtotime('+90 days', $today);
                    
                    // Vérifier si la date de sortie est dans les 90 prochains jours
                    $releaseDate = !empty($movie->release_date) ? strtotime($movie->release_date) : 0;
                    $isComingSoon = ($releaseDate > $today && $releaseDate <= $futureLimit);
                    
                    // Conditions: avoir une affiche, date future dans les 90 jours, très populaire, et uniquement caractères latins
                    return !empty($movie->poster_path) && 
                           !empty($movie->release_date) && 
                           $isComingSoon &&
                           isset($movie->popularity) && $movie->popularity > 20 &&
                           !$hasNonLatinChars;
                });
                
                // Si pas assez de films dans les 90 jours, on élargit à 180 jours
                if (count($validMovies) < 3) {
                    $validMovies = array_filter($upcomingMovies->results, function($movie) {
                        // First check if movie is an object and has a title
                        if (!is_object($movie) || !isset($movie->title)) {
                            return false;
                        }
                        
                        $hasNonLatinChars = preg_match('/[\p{Han}\p{Hiragana}\p{Katakana}\p{Cyrillic}\p{Arabic}]/u', $movie->title);
                        
                        $today = time();
                        $futureLimit = strtotime('+180 days', $today);
                        $releaseDate = !empty($movie->release_date) ? strtotime($movie->release_date) : 0;
                        $isComingSoon = ($releaseDate > $today && $releaseDate <= $futureLimit);
                        
                        return !empty($movie->poster_path) && 
                               !empty($movie->release_date) && 
                               $isComingSoon &&
                               isset($movie->popularity) && $movie->popularity > 15 &&
                               !$hasNonLatinChars;
                    });
                }
                
                // Créer un score combiné basé sur la popularité et l'imminence de la sortie
                foreach ($validMovies as &$movie) {
                    $daysUntilRelease = ceil((strtotime($movie->release_date) - time()) / (60 * 60 * 24));
                    
                    // Plus la sortie est proche, plus le score d'imminence est élevé (max 100)
                    $proximityScore = max(0, 100 - $daysUntilRelease);
                    
                    // Score combiné: 30% popularité + 70% imminence pour favoriser les sorties imminentes
                    $movie->combined_score = ($movie->popularity * 0.3) + ($proximityScore * 0.7);
                }
                unset($movie); // Détacher la référence
                
                // Trier les films par ce score combiné
                usort($validMovies, function($a, $b) {
                    return $b->combined_score - $a->combined_score;
                });
                
                // Prendre les 3 films les plus attendus
                $upcomingFilms = array_slice($validMovies, 0, 3);
                
                // S'assurer qu'on a exactement 3 films
                if (count($upcomingFilms) < 3) {
                    // Définir des films de secours
                    $backupPopularMovies = array_filter($upcomingMovies->results, function($movie) use ($upcomingFilms) {
                        // First check if movie is an object and has a title
                        if (!is_object($movie) || !isset($movie->title)) {
                            return false;
                        }
                        
                        $hasNonLatinChars = preg_match('/[\p{Han}\p{Hiragana}\p{Katakana}\p{Cyrillic}\p{Arabic}]/u', $movie->title);
                        return !empty($movie->poster_path) && 
                               !empty($movie->release_date) && 
                               strtotime($movie->release_date) > time() &&
                               !$hasNonLatinChars &&
                               !in_array($movie, $upcomingFilms);
                    });
                    
                    // Trier par popularité
                    usort($backupPopularMovies, function($a, $b) {
                        return $b->popularity - $a->popularity;
                    });
                    
                    // Compléter notre liste
                    $upcomingFilms = array_merge($upcomingFilms, array_slice($backupPopularMovies, 0, 3 - count($upcomingFilms)));
                }
                
                // Afficher les 3 films
                foreach ($upcomingFilms as $index => $movie):
                    $class = $index === 0 ? 'prev' : ($index === 1 ? 'active' : 'next');
                    
                    // Calculer le nombre de jours jusqu'à la sortie
                    $releaseDate = strtotime($movie->release_date);
                    $today = time();
                    $daysUntil = ceil(($releaseDate - $today) / (60 * 60 * 24));
                ?>
                <div class="carousel-slide" data-position="<?= $index ?>">
                    <div class="carousel-movie <?= $class ?>">
                        <a href="index.php?action=view&id=<?= $movie->id ?>">
                            <span class="feature-badge <?= $daysUntil <= 7 ? 'soon' : '' ?>">J-<?= $daysUntil ?></span>
                            <img src="https://image.tmdb.org/t/p/w500<?= $movie->poster_path ?>" alt="<?= htmlspecialchars($movie->title) ?>">
                            <div class="carousel-info">
                                <h3><?= htmlspecialchars($movie->title) ?></h3>
                                <?php if (isset($movie->release_date)): ?>
                                    <div class="carousel-date">Sortie: <?= date('d/m/Y', strtotime($movie->release_date)) ?></div>
                                    <div class="countdown"><?= $daysUntil <= 7 ? 'Très bientôt!' : 'Dans ' . $daysUntil . ' jours' ?></div>
                                <?php endif; ?>
                            </div>
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Navigation arrows inside container -->
            <div class="carousel-nav">
                <button class="carousel-button" id="prevButton">←</button>
                <button class="carousel-button" id="nextButton">→</button>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Après la section carousel et avant la grille de films -->
    <div class="content-wrapper">
        <!-- Barre de recherche -->
        <div class="search-container">
            <input type="text" id="searchInput" placeholder="Rechercher un film d'animation...">
        </div>

        <!-- Panneau de filtres -->
        <div class="filter-panel collapsed">
            <div class="filter-header">
                <h2>Filtres</h2>
                <button type="button" class="toggle-button" aria-label="Toggle filters">
                    <span class="icon">▼</span>
                </button>
            </div>
            <div class="filter-content">
                <form id="filtersForm">
                    <div class="filter-group">
                        <label for="sort_by">Trier par :</label>
                        <select name="sort_by" id="sort_by">
                            <option value="popularity.desc">Popularité (décroissante)</option>
                            <option value="popularity.asc">Popularité (croissante)</option>
                            <option value="release_date.desc">Date de sortie (récent → ancien)</option>
                            <option value="release_date.asc">Date de sortie (ancien → récent)</option>
                            <option value="vote_average.desc">Note (décroissante)</option>
                            <option value="vote_average.asc">Note (croissante)</option>
                            <option value="original_title.asc">Titre (A-Z)</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="with_original_language">Langue :</label>
                        <select name="with_original_language" id="with_original_language">
                            <option value="">Toutes</option>
                            <option value="ja">Japonais</option>
                            <option value="en">Anglais</option>
                            <option value="fr">Français</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="vote_average_gte">Note minimale :</label>
                        <select name="vote_average_gte" id="vote_average_gte">
                            <option value="">Toutes</option>
                            <option value="5">5+</option>
                            <option value="6">6+</option>
                            <option value="7">7+</option>
                            <option value="8">8+</option>
                            <option value="9">9+</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="year">Année de sortie :</label>
                        <select name="year" id="year">
                            <option value="">Toutes</option>
                            <?php for ($year = date('Y'); $year >= 1950; $year--): ?>
                                <option value="<?= $year ?>"><?= $year ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label>Période de sortie :</label>
                        <div class="date-inputs">
                            <input type="date" name="primary_release_date_gte" id="primary_release_date_gte" placeholder="De">
                            <span>à</span>
                            <input type="date" name="primary_release_date_lte" id="primary_release_date_lte" placeholder="À">
                        </div>
                    </div>

                    <div class="filter-actions">
                        <button type="submit" class="filter-button">Appliquer</button>
                        <button type="button" class="reset-button">Réinitialiser</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Grille de films -->
        <div id="movieGrid" class="movie-grid">
            <?php if (isset($results) && !empty($results->results)): ?>
                <?php foreach ($results->results as $movie): ?>
                    <?php
                    // Convert array to object if necessary
                    if (is_array($movie)) {
                        $movie = (object)$movie;
                    }
                    
                    // Skip invalid movies
                    if (!isset($movie->title) || !isset($movie->id)) {
                        continue;
                    }
                    
                    $title = isset($movie->title) ? $movie->title : '';
                    $posterPath = isset($movie->poster_path) ? $movie->poster_path : '';
                    ?>
                    <div class="movie-card" data-title="<?= htmlspecialchars(strtolower($title)) ?>">
                        <a href="index.php?action=view&id=<?= htmlspecialchars($movie->id) ?>">
                            <?php if ($posterPath): ?>
                                <img src="https://image.tmdb.org/t/p/w500<?= htmlspecialchars($posterPath) ?>" 
                                     alt="<?= htmlspecialchars($title) ?>">
                            <?php else: ?>
                                <div class="no-poster">No poster available</div>
                            <?php endif; ?>
                            <h3><?= htmlspecialchars($title) ?></h3>
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-results">
                    <p>Aucun film d'animation trouvé. Essayez une recherche différente.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div id="loading" class="loading" style="display: none;">
        Chargement des films d'animation...
    </div>

    <!-- Scripts -->
    <script src="assets/js/search.js"></script>
    <script src="assets/js/carousel.js"></script>
    <script src="assets/js/filters.js"></script>

<?php require_once APP_PATH . '/Views/includes/footer.php'; ?>