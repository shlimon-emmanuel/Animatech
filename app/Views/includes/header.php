<nav class="nav-menu">
    <?php if (isset($_SESSION['user'])): ?>
        <span class="welcome-text">Bienvenue, <?= htmlspecialchars($_SESSION['user']['username']) ?></span>
        <a href="index.php" class="nav-link">Accueil</a>
        <a href="index.php?action=favorites" class="nav-link">Favoris</a>
        <a href="index.php?action=profile" class="nav-link">Profil</a>
        <a href="index.php?action=logout" class="nav-link">Déconnexion</a>
    <?php else: ?>
        <a href="index.php" class="nav-link">Accueil</a>
        <a href="index.php?action=login" class="nav-link">Connexion</a>
        <a href="index.php?action=register" class="nav-link">Inscription</a>
    <?php endif; ?>
</nav> 