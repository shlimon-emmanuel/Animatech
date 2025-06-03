<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? $pageTitle : 'AnimaTech' ?></title>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700&family=Rajdhani:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/variables.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>/assets/img/favicon.png">
</head>
<body>
    <header>
        <nav class="nav-menu">
            <div class="logo-container">
                <a href="<?= BASE_URL ?>">
                    <img src="<?= BASE_URL ?>/assets/img/logo.png" alt="AnimaTech Logo" class="logo">
                </a>
                <h1 class="site-title">AnimaTech</h1>
            </div>
            
            <div class="nav-links">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <span class="welcome-text">Bienvenue, <?= htmlspecialchars($_SESSION['username']) ?></span>
                    <a href="<?= BASE_URL ?>?action=profile" class="nav-link">Mon Profil</a>
                    <a href="<?= BASE_URL ?>?action=favorites" class="nav-link">Mes Favoris</a>
                    <a href="<?= BASE_URL ?>?action=logout" class="nav-link">Déconnexion</a>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>?action=login" class="nav-link">Connexion</a>
                    <a href="<?= BASE_URL ?>?action=register" class="nav-link">Inscription</a>
                <?php endif; ?>
            </div>
        </nav>
    </header>
    
    <main class="main-container">
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