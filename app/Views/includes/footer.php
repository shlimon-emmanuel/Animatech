<footer class="footer">
    <div class="container">
        <div class="footer-content">
            <div class="footer-logo">
                <span class="logo-text">ANIMATECH</span>
                <p>Découvrez l'univers des films d'animation</p>
            </div>
            <div class="footer-links">
                <h4>Liens utiles</h4>
                <ul>
                    <li><a href="index.php">Accueil</a></li>
                    <li><a href="index.php?action=search">Rechercher</a></li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li><a href="index.php?action=profile">Mon profil</a></li>
                    <?php else: ?>
                        <li><a href="index.php?action=login">Connexion</a></li>
                        <li><a href="index.php?action=register">Inscription</a></li>
                    <?php endif; ?>
                    <li><a href="index.php?action=mentions-legales">Mentions légales</a></li>
                </ul>
            </div>
            <div class="footer-legal">
                <p>&copy; <?php echo date('Y'); ?> ANIMATECH - Tous droits réservés</p>
                <p><a href="index.php?action=mentions-legales">Politique de confidentialité</a> | <a href="index.php?action=rgpd">RGPD</a></p>
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Activer les tooltips Bootstrap si disponibles
document.addEventListener('DOMContentLoaded', function() {
    if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
    
    // Activer la navigation active
    const currentPath = window.location.search;
    const navLinks = document.querySelectorAll('.nav-links .nav-link');
    
    navLinks.forEach(link => {
        if (link.getAttribute('href') === 'index.php' && currentPath === '') {
            link.classList.add('active');
        } else if (currentPath.includes(link.getAttribute('href').split('?')[1])) {
            link.classList.add('active');
        }
    });
});
</script>
</body>
</html> 