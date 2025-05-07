</main>

<footer class="bg-dark text-light py-3 mt-5">
    <div class="container">
        <p class="text-center">&copy; <?= date('Y') ?> ANIMATECH - Panel Admin</p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Activer les tooltips Bootstrap
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Activer la navigation active
    const currentPath = window.location.search;
    const navLinks = document.querySelectorAll('.navbar-nav .nav-link');
    
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