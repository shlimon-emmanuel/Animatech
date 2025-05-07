<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cinetech - Films d'Animation</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;700&family=Rajdhani:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <header>
        <nav class="nav-menu">
            <div class="logo-container">
                <img src="assets/images/logo.png" alt="Cinetech Logo" class="logo">
                <h1 class="site-title">Cinetech</h1>
            </div>
            <div class="nav-links">
                <a href="index.php" class="nav-link">Accueil</a>
                <?php if (isset($_SESSION['user'])): ?>
                    <span class="welcome-text">Bienvenue, <?= htmlspecialchars($_SESSION['user']['username']) ?></span>
                    <a href="index.php?action=profile" class="nav-link">Profil</a>
                    
                    <a href="index.php?action=logout" class="nav-link">Déconnexion</a>
                <?php else: ?>
                    <a href="index.php?action=login" class="nav-link">Connexion</a>
                    <a href="index.php?action=register" class="nav-link">Inscription</a>
                <?php endif; ?>
            </div>
        </nav>
    </header>
    
    <!-- Section des films populaires à venir -->
    <div class="popular-upcoming-films">
        <h2 class="section-title">Films populaires à venir</h2>
        <div class="films-slider" id="upcomingFilmsSlider">
            <!-- Les films seront chargés dynamiquement via JavaScript -->
            <div class="loading" id="sliderLoading">Chargement des films populaires...</div>
        </div>
    </div>
    
    <div class="search-container">
        <input type="text" id="searchInput" placeholder="Rechercher un film d'animation...">
    </div>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-error"><?= $error ?></div>
    <?php endif; ?>
    
    <?php if (isset($success)): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>
</body>
</html> 

