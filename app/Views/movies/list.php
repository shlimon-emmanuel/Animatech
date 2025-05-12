<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Films d'Animation</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;700&family=Rajdhani:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* Styles de secours au cas où le CSS externe ne fonctionne pas */
        :root {
            --neon-blue: #00f3ff;
            --neon-purple: #9d4edd;
            --dark-bg: #0a0a1f;
            --darker-bg: #050510;
            --text-glow: 0 0 10px var(--neon-blue);
        }

        body {
            background-color: var(--dark-bg);
            color: white;
            font-family: 'Rajdhani', sans-serif;
            margin: 0;
            padding: 0;
            line-height: 1.6;
        }

        /* Header et Navigation */
        .nav-menu {
            background-color: var(--darker-bg);
            padding: 15px 5%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 0 15px rgba(0, 243, 255, 0.3);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .logo-container {
            display: flex;
            align-items: center;
        }

        .logo {
            height: 50px;
            margin-right: 15px;
        }

        .site-title {
            font-family: 'Orbitron', sans-serif;
            color: var(--neon-blue);
            font-size: 24px;
            margin: 0;
            text-shadow: var(--text-glow);
        }

        .nav-links {
            display: flex;
            gap: 25px;
            align-items: center;
        }

        .nav-link {
            color: var(--neon-blue);
            text-decoration: none;
            font-family: 'Orbitron', sans-serif;
            font-weight: 500;
            transition: text-shadow 0.3s, color 0.3s;
            font-size: 17px;
            position: relative;
            padding: 5px 0;
        }

        .nav-link:hover {
            text-shadow: var(--text-glow);
            color: white;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: 0;
            left: 0;
            background-color: var(--neon-blue);
            transition: width 0.3s;
        }

        .nav-link:hover::after {
            width: 100%;
            box-shadow: var(--text-glow);
        }

        .welcome-text {
            color: var(--neon-purple);
            margin-right: 15px;
            font-weight: 500;
        }

        /* Grille de films */
        .movie-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 30px;
            padding: 40px 5%;
            margin-top: 20px;
        }

        .movie-card {
            background-color: var(--darker-bg);
            border-radius: 12px;
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
            box-shadow: 0 0 5px var(--neon-purple);
            height: 420px;
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
            height: 350px;
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

        /* Carousel des films à venir */
        .carousel-section {
            padding: 15px 0 10px;
            text-align: center;
            position: relative;
            overflow: hidden;
            background-color: rgba(10, 10, 31, 0.8);
        }
        
        .carousel-title {
            font-family: 'Orbitron', sans-serif;
            color: var(--neon-purple);
            text-shadow: 0 0 10px var(--neon-purple);
            margin-bottom: 10px;
            font-size: 22px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .carousel-container {
            position: relative;
            margin: 0 auto;
            max-width: 96%;
            width: 1200px;
            height: 390px; /* Réduit mais suffisant pour voir l'affiche complète */
            perspective: 1000px;
        }
        
        .carousel-track {
            position: absolute;
            width: 100%;
            height: 100%;
            transform-style: preserve-3d;
            transition: transform 0.8s ease-in-out;
        }
        
        .carousel-slide {
            position: absolute;
            width: 20%; /* Format d'affiche standard mais plus petit */
            height: 100%;
            left: 40%; /* Centré: (100% - 20%) / 2 */
            top: 0;
            box-sizing: border-box;
            transform-origin: center center;
            transform-style: preserve-3d;
            transition: all 0.8s ease;
        }
        
        .carousel-movie {
            width: 100%;
            height: 100%;
            background-color: var(--darker-bg);
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 0 20px rgba(157, 78, 221, 0.4);
            transition: all 0.5s ease;
            position: relative;
            transform-origin: center center;
            transform-style: preserve-3d;
            backface-visibility: hidden;
            display: flex;
            flex-direction: column;
        }
        
        .carousel-movie.active {
            transform: scale(1.05);
            box-shadow: 0 0 40px var(--neon-blue);
            z-index: 10;
        }
        
        .carousel-movie.prev, .carousel-movie.next {
            filter: brightness(0.6);
            transform: scale(0.8);
            z-index: 5;
        }
        
        .carousel-movie.prev {
            transform: translateX(-160%) scale(0.8) rotateY(10deg);
        }
        
        .carousel-movie.next {
            transform: translateX(160%) scale(0.8) rotateY(-10deg);
        }
        
        .carousel-movie a {
            text-decoration: none;
            color: white;
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        
        .carousel-movie img {
            width: 100%;
            height: 88%;
            object-fit: cover;
            object-position: top; /* Pour s'assurer que le haut de l'affiche est visible */
        }
        
        .carousel-info {
            padding: 5px 8px;
            text-align: center;
            height: 12%;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .carousel-movie h3 {
            font-family: 'Orbitron', sans-serif;
            color: var(--neon-blue);
            margin: 0 0 3px;
            font-size: 14px;
            text-shadow: var(--text-glow);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .carousel-date {
            color: var(--neon-purple);
            font-weight: 600;
            font-size: 11px;
        }
        
        .countdown {
            color: #fff;
            font-size: 10px;
            margin-top: 1px;
            font-weight: bold;
        }
        
        .feature-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: var(--neon-purple);
            color: white;
            padding: 6px 10px;
            border-radius: 6px;
            font-family: 'Orbitron', sans-serif;
            font-size: 13px;
            font-weight: bold;
            box-shadow: 0 0 15px var(--neon-purple);
            z-index: 5;
            letter-spacing: 1px;
        }
        
        /* Version spéciale pour les films vraiment très proches */
        .feature-badge.soon {
            background-color: #ff3860;
            box-shadow: 0 0 15px #ff3860;
            animation: pulse 1.5s infinite;
        }
        
        @keyframes pulse {
            0% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.8; transform: scale(1.05); }
            100% { opacity: 1; transform: scale(1); }
        }
        
        .carousel-nav {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 30;
        }
        
        .carousel-button {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background-color: rgba(5, 5, 16, 0.6);
            color: var(--neon-blue);
            border: 2px solid rgba(0, 243, 255, 0.7);
            border-radius: 50%;
            width: 50px;
            height: 50px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            transition: all 0.3s ease;
            box-shadow: 0 0 5px var(--neon-blue);
            z-index: 40;
            pointer-events: auto;
        }
        
        #prevButton {
            left: -15px;
        }
        
        #nextButton {
            right: -15px;
        }
        
        @media (min-width: 768px) {
            #prevButton {
                left: -25px;
            }
            
            #nextButton {
                right: -25px;
            }
        }
        
        @media (min-width: 992px) {
            #prevButton {
                left: -35px;
            }
            
            #nextButton {
                right: -35px;
            }
        }
        
        .carousel-button:hover {
            background-color: rgba(0, 243, 255, 0.4);
            color: white;
            box-shadow: 0 0 15px var(--neon-blue);
        }
        
        /* Responsive carousel */
        @media (max-width: 900px) {
            .carousel-container {
                height: 350px;
                max-width: 98%;
            }
            
            .carousel-title {
                font-size: 20px;
                margin-bottom: 10px;
            }
            
            .carousel-button {
                width: 40px;
                height: 40px;
                font-size: 20px;
            }
            
            .carousel-slide {
                width: 22%;
                left: 39%;
            }
            
            .carousel-movie.prev {
                transform: translateX(-150%) scale(0.8) rotateY(10deg);
            }
            
            .carousel-movie.next {
                transform: translateX(150%) scale(0.8) rotateY(-10deg);
            }
        }
        
        @media (max-width: 767px) {
            .carousel-container {
                height: 320px;
            }
            
            .carousel-slide {
                width: 26%;
                left: 37%;
            }
            
            .carousel-movie.prev {
                transform: translateX(-130%) scale(0.7) rotateY(15deg);
            }
            
            .carousel-movie.next {
                transform: translateX(130%) scale(0.7) rotateY(-15deg);
            }
            
            .carousel-title {
                font-size: 18px;
                margin-bottom: 8px;
            }
            
            #prevButton {
                left: 5px;
            }
            
            #nextButton {
                right: 5px;
            }
        }
        
        @media (max-width: 480px) {
            .carousel-section {
                padding: 12px 0 8px;
            }
            
            .carousel-container {
                height: 260px;
                max-width: 100%;
            }
            
            .carousel-slide {
                width: 32%;
                left: 34%;
            }
            
            .carousel-movie.prev {
                transform: translateX(-120%) scale(0.7) rotateY(15deg);
                opacity: 0.5;
            }
            
            .carousel-movie.next {
                transform: translateX(120%) scale(0.7) rotateY(-15deg);
                opacity: 0.5;
            }
            
            .carousel-title {
                margin-bottom: 8px;
                font-size: 16px;
            }
            
            .search-container {
                padding-top: 15px;
            }
            
            .carousel-button {
                width: 34px;
                height: 34px;
                font-size: 16px;
                background-color: rgba(5, 5, 16, 0.8);
            }
        }

        /* Barre de recherche */
        .search-container {
            padding: 15px 5% 15px;
            display: flex;
            justify-content: center;
            margin-top: 0;
        }

        #searchInput {
            width: 60%;
            padding: 12px 20px;
            border: 2px solid var(--neon-purple);
            background-color: rgba(5, 5, 16, 0.7);
            color: white;
            border-radius: 30px;
            font-family: 'Rajdhani', sans-serif;
            font-size: 18px;
            box-shadow: 0 0 5px rgba(157, 78, 221, 0.5);
            transition: all 0.3s ease;
        }

        #searchInput:focus {
            border-color: var(--neon-blue);
            box-shadow: 0 0 15px var(--neon-blue);
            outline: none;
            width: 65%;
        }

        .loading {
            text-align: center;
            padding: 30px;
            color: var(--neon-purple);
            font-size: 22px;
            font-family: 'Orbitron', sans-serif;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .movie-grid {
                grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
                gap: 20px;
                padding: 20px 5%;
            }
            
            .movie-card {
                height: 300px;
            }
            
            .movie-card img {
                height: 240px;
            }
            
            .movie-card h3 {
                font-size: 14px;
                padding: 10px 5px;
            }
            
            #searchInput {
                width: 90%;
            }
            
            #searchInput:focus {
                width: 95%;
            }
            
            .nav-menu {
                flex-direction: column;
                padding: 15px;
            }
            
            .nav-links {
                margin-top: 15px;
                flex-wrap: wrap;
                justify-content: center;
            }
        }

        /* No results */
        .no-results {
            text-align: center;
            padding: 40px;
            grid-column: 1 / -1;
            font-size: 18px;
            color: var(--neon-purple);
        }
    </style>
</head>
<body>
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

    <!-- Barre de recherche -->
    <div class="search-container">
        <input type="text" id="searchInput" placeholder="Rechercher un film d'animation...">
    </div>
    
    <!-- Résultats de recherche container -->
    <div id="searchResults" class="movie-grid" style="display: none;"></div>

    <div class="filter-panel">
        <div class="filter-header">
            <h2>Filtres</h2>
            <button id="toggleFilters" class="toggle-button">
                <span class="icon">◀</span>
            </button>
        </div>
        <div class="filter-content">
            <form id="filtersForm" action="index.php" method="get">
                <div class="filter-group">
                    <label for="sort_by">Trier par :</label>
                    <select name="sort_by" id="sort_by">
                        <?php foreach ($sortOptions as $value => $label): ?>
                            <option value="<?= $value ?>" <?= $currentSort === $value ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="with_original_language">Langue :</label>
                    <select name="with_original_language" id="with_original_language">
                        <option value="">Toutes</option>
                        <option value="ja" <?= isset($currentFilters['with_original_language']) && $currentFilters['with_original_language'] === 'ja' ? 'selected' : '' ?>>Japonais</option>
                        <option value="en" <?= isset($currentFilters['with_original_language']) && $currentFilters['with_original_language'] === 'en' ? 'selected' : '' ?>>Anglais</option>
                        <option value="fr" <?= isset($currentFilters['with_original_language']) && $currentFilters['with_original_language'] === 'fr' ? 'selected' : '' ?>>Français</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="vote_average_gte">Note minimale :</label>
                    <select name="vote_average_gte" id="vote_average_gte">
                        <option value="0">Toutes</option>
                        <option value="5" <?= isset($currentFilters['vote_average.gte']) && $currentFilters['vote_average.gte'] == 5 ? 'selected' : '' ?>>5+</option>
                        <option value="6" <?= isset($currentFilters['vote_average.gte']) && $currentFilters['vote_average.gte'] == 6 ? 'selected' : '' ?>>6+</option>
                        <option value="7" <?= isset($currentFilters['vote_average.gte']) && $currentFilters['vote_average.gte'] == 7 ? 'selected' : '' ?>>7+</option>
                        <option value="8" <?= isset($currentFilters['vote_average.gte']) && $currentFilters['vote_average.gte'] == 8 ? 'selected' : '' ?>>8+</option>
                        <option value="9" <?= isset($currentFilters['vote_average.gte']) && $currentFilters['vote_average.gte'] == 9 ? 'selected' : '' ?>>9+</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="year">Année de sortie :</label>
                    <select name="year" id="year">
                        <option value="">Toutes</option>
                        <?php for ($year = date('Y'); $year >= 1950; $year--): ?>
                            <option value="<?= $year ?>" <?= isset($currentFilters['year']) && $currentFilters['year'] == $year ? 'selected' : '' ?>><?= $year ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                
                <div class="filter-group date-range">
                    <label>Période de sortie :</label>
                    <div class="date-inputs">
                        <input type="date" name="primary_release_date_gte" id="primary_release_date_gte" placeholder="De" 
                            value="<?= isset($currentFilters['primary_release_date.gte']) ? $currentFilters['primary_release_date.gte'] : '' ?>">
                        <span>à</span>
                        <input type="date" name="primary_release_date_lte" id="primary_release_date_lte" placeholder="À" 
                            value="<?= isset($currentFilters['primary_release_date.lte']) ? $currentFilters['primary_release_date.lte'] : '' ?>">
                    </div>
                </div>
                
                <div class="filter-actions">
                    <button type="submit" class="filter-button">Appliquer</button>
                    <a href="index.php" class="reset-button">Réinitialiser</a>
                </div>
            </form>
        </div>
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
        Chargement des films d'animation...
    </div>

    <script src="assets/js/search.js"></script>
    
    <!-- Script pour le carousel -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const track = document.getElementById('carouselTrack');
        const slides = Array.from(document.querySelectorAll('.carousel-slide'));
        const movies = Array.from(document.querySelectorAll('.carousel-movie'));
        const nextButton = document.getElementById('nextButton');
        const prevButton = document.getElementById('prevButton');
        
        if (!track || slides.length < 3 || !nextButton || !prevButton) return;
        
        // Configuration initiale
        let positions = [0, 1, 2]; // 0 = gauche (prev), 1 = centre (active), 2 = droite (next)
        
        // Fonction pour mettre à jour le carousel
        function updateCarousel() {
            slides.forEach((slide, index) => {
                const position = positions[index];
                const movie = slide.querySelector('.carousel-movie');
                
                // Réinitialiser les classes
                movie.classList.remove('prev', 'active', 'next');
                
                // Ajouter la classe appropriée selon la position
                if (position === 0) {
                    movie.classList.add('prev');
                    slide.style.zIndex = '5';
                } else if (position === 1) {
                    movie.classList.add('active');
                    slide.style.zIndex = '10';
                } else if (position === 2) {
                    movie.classList.add('next');
                    slide.style.zIndex = '5';
                }
            });
        }
        
        // Navigation vers la droite
        nextButton.addEventListener('click', function() {
            // Déplacer les positions : 0->2, 1->0, 2->1
            positions = positions.map(pos => (pos + 2) % 3);
            updateCarousel();
        });
        
        // Navigation vers la gauche
        prevButton.addEventListener('click', function() {
            // Déplacer les positions : 0->1, 1->2, 2->0
            positions = positions.map(pos => (pos + 1) % 3);
            updateCarousel();
        });
        
        // Défilement automatique
        let autoplayInterval = setInterval(() => {
            nextButton.click();
        }, 5000);
        
        // Arrêter le défilement automatique au survol
        const carouselContainer = document.querySelector('.carousel-container');
        carouselContainer.addEventListener('mouseenter', () => {
            clearInterval(autoplayInterval);
        });
        
        // Reprendre le défilement automatique à la sortie
        carouselContainer.addEventListener('mouseleave', () => {
            autoplayInterval = setInterval(() => {
                nextButton.click();
            }, 5000);
        });
    });
    </script>
</body>
</html>