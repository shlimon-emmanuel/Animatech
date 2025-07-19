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