<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? $pageTitle : 'ANIMATECH - Plateforme de découverte et gestion de films d\'animation' ?></title>
    <meta name="description" content="<?= isset($pageDescription) ? $pageDescription : 'ANIMATECH est une application web complète dédiée aux films d\'animation. Catalogue interactif avec API TMDB, système d\'authentification, gestion de favoris, commentaires et notation. Architecture MVC en PHP natif avec base de données MySQL.' ?>">
    <meta name="keywords" content="application web PHP, API TMDB, films animation, architecture MVC, base de données MySQL, authentification utilisateur, gestion favoris, système de commentaires, développement web, projet étudiant">
    <meta name="author" content="ANIMATECH">
    <meta property="og:title" content="<?= isset($pageTitle) ? $pageTitle : 'ANIMATECH - Films d\'Animation en Streaming' ?>">
    <meta property="og:description" content="<?= isset($pageDescription) ? $pageDescription : 'Plateforme dédiée aux films d\'animation avec catalogue complet, favoris et commentaires.' ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= BASE_URL . ($_SERVER['REQUEST_URI'] ?? '') ?>">
    <meta property="og:image" content="<?= BASE_URL ?>/assets/img/logo.svg">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= isset($pageTitle) ? $pageTitle : 'ANIMATECH - Films d\'Animation' ?>">
    <meta name="twitter:description" content="<?= isset($pageDescription) ? $pageDescription : 'Votre plateforme de films d\'animation préférée' ?>">
    <link rel="icon" href="assets/img/logo.svg" type="image/svg+xml">
    <link rel="icon" href="assets/images/logo.png" type="image/png">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;700&family=Rajdhani:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<nav class="nav-menu">
    <div class="logo-container">
        <img src="assets/img/logo.svg" alt="ANIMATECH Logo" class="logo">
        <h1 class="site-title">ANIMATECH</h1>
    </div>
    
    <div class="nav-links">
        <?php if (isset($_SESSION['user'])): ?>
            <span class="welcome-text">Bienvenue, <?= htmlspecialchars($_SESSION['user']['username']) ?></span>
            <a href="index.php" class="nav-link">Accueil</a>
            <a href="index.php?action=profile" class="nav-link">Profil</a>
            
            <?php 
            // Vérifier si l'utilisateur est admin ou superadmin
            $isAdmin = false;
            if (isset($_SESSION['user']['role']) && ($_SESSION['user']['role'] === 'admin' || $_SESSION['user']['role'] === 'superadmin')) {
                $isAdmin = true;
            } elseif (isset($_SESSION['role']) && ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'superadmin')) {
                $isAdmin = true;
            }
            
            if ($isAdmin): 
            ?>
                <a href="index.php?action=admin" class="nav-link admin-link">
                    <i class="fas fa-user-shield"></i> Administration
                </a>
            <?php endif; ?>
            
            <a href="index.php?action=logout" class="nav-link">Déconnexion</a>
        <?php else: ?>
            <a href="index.php" class="nav-link">Accueil</a>
            <a href="index.php?action=login" class="nav-link">Connexion</a>
            <a href="index.php?action=register" class="nav-link">Inscription</a>
        <?php endif; ?>
    </div>
</nav>

 