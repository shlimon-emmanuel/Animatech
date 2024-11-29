document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const movieGrid = document.getElementById('movieGrid');
    let currentPage = 1;
    let isLoading = false;
    let searchTimeout;
    let currentSearchTerm = '';

    // Fonction de recherche modifiée
    searchInput.addEventListener('input', function(e) {
        clearTimeout(searchTimeout);
        const searchTerm = e.target.value.trim();
        currentSearchTerm = searchTerm;
        
        searchTimeout = setTimeout(async () => {
            currentPage = 1;
            isLoading = true;
            
            const loading = document.getElementById('loading');
            loading.style.display = 'block';
            
            try {
                const response = await fetch(`index.php?action=search&query=${encodeURIComponent(searchTerm)}&page=${currentPage}`);
                const data = await response.json();
                
                movieGrid.innerHTML = '';
                
                if (data.movies && data.movies.length > 0) {
                    data.movies.forEach(movie => {
                        const movieCard = createMovieCard(movie);
                        movieGrid.appendChild(movieCard);
                    });
                }
            } catch (error) {
                console.error('Erreur lors de la recherche:', error);
            } finally {
                isLoading = false;
                loading.style.display = 'none';
            }
        }, 500);
    });

    // Modification du chargement infini pour prendre en compte la recherche
    async function loadMoreMovies() {
        if (isLoading) return;
        
        isLoading = true;
        currentPage++;
        
        const loading = document.getElementById('loading');
        loading.style.display = 'block';

        try {
            const url = currentSearchTerm
                ? `index.php?action=search&query=${encodeURIComponent(currentSearchTerm)}&page=${currentPage}`
                : `index.php?action=loadMore&page=${currentPage}`;
                
            const response = await fetch(url);
            const data = await response.json();
            
            if (data.movies && data.movies.length > 0) {
                data.movies.forEach(movie => {
                    const movieCard = createMovieCard(movie);
                    movieGrid.appendChild(movieCard);
                });
            }
        } catch (error) {
            console.error('Erreur lors du chargement des films:', error);
        } finally {
            isLoading = false;
            loading.style.display = 'none';
        }
    }

    // Création d'une carte de film
    function createMovieCard(movie) {
        const card = document.createElement('div');
        card.className = 'movie-card';
        card.setAttribute('data-title', movie.title.toLowerCase());
        
        card.innerHTML = `
            <a href="index.php?action=view&id=${movie.id}">
                ${movie.poster_path ? 
                    `<img src="https://image.tmdb.org/t/p/w500${movie.poster_path}" 
                          alt="${movie.title}">` : ''}
                <h3>${movie.title}</h3>
            </a>
        `;
        
        return card;
    }

    // Détection du scroll
    window.addEventListener('scroll', () => {
        if ((window.innerHeight + window.scrollY) >= document.documentElement.scrollHeight - 500) {
            loadMoreMovies();
        }
    });
}); 