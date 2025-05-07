document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const movieGrid = document.getElementById('movieGrid');
    const loading = document.getElementById('loading');
    const filterPanel = document.querySelector('.filter-panel');
    const toggleFilters = document.getElementById('toggleFilters');
    
    let currentPage = 1;
    let isLoading = false;
    let searchTimeout;
    let currentSearchTerm = '';
    let hasMorePages = true;
    
    // Fonction pour récupérer les paramètres de filtres actuels
    function getCurrentFilterParams() {
        // Si nous sommes en mode recherche, on utilise seulement le terme de recherche
        if (currentSearchTerm.trim() !== '') {
            return { query: currentSearchTerm };
        }
        
        // Sinon, on récupère tous les filtres
        const params = {};
        
        // Récupérer le tri
        const sortBy = document.getElementById('sort_by');
        if (sortBy && sortBy.value) {
            params.sort_by = sortBy.value;
        }
        
        // Récupérer la langue originale
        const language = document.getElementById('with_original_language');
        if (language && language.value) {
            params.with_original_language = language.value;
        }
        
        // Récupérer la note minimale
        const rating = document.getElementById('vote_average_gte');
        if (rating && rating.value && rating.value !== '0') {
            params.vote_average_gte = rating.value;
        }
        
        // Récupérer l'année
        const year = document.getElementById('year');
        if (year && year.value) {
            params.year = year.value;
        }
        
        // Récupérer les dates de sortie
        const dateFrom = document.getElementById('primary_release_date_gte');
        if (dateFrom && dateFrom.value) {
            params.primary_release_date_gte = dateFrom.value;
        }
        
        const dateTo = document.getElementById('primary_release_date_lte');
        if (dateTo && dateTo.value) {
            params.primary_release_date_lte = dateTo.value;
        }
        
        return params;
    }
    
    // Fonction pour construire l'URL avec tous les paramètres
    function buildUrl(baseUrl, params) {
        const url = new URL(baseUrl, window.location.origin);
        
        Object.keys(params).forEach(key => {
            if (params[key] !== null && params[key] !== undefined && params[key] !== '') {
                url.searchParams.append(key, params[key]);
            }
        });
        
        return url.toString();
    }

    // Fonction de recherche modifiée
    searchInput.addEventListener('input', function(e) {
        clearTimeout(searchTimeout);
        const searchTerm = e.target.value.trim();
        currentSearchTerm = searchTerm;
        
        searchTimeout = setTimeout(async () => {
            currentPage = 1;
            hasMorePages = true;
            isLoading = true;
            
            loading.style.display = 'block';
            
            try {
                const response = await fetch(`index.php?action=search&query=${encodeURIComponent(searchTerm)}&page=${currentPage}`);
                if (!response.ok) {
                    throw new Error(`Erreur HTTP: ${response.status}`);
                }
                
                const data = await response.json();
                
                movieGrid.innerHTML = '';
                
                if (data.movies && data.movies.length > 0) {
                    data.movies.forEach(movie => {
                        const movieCard = createMovieCard(movie);
                        movieGrid.appendChild(movieCard);
                    });
                    
                    // Vérifier s'il y a d'autres pages
                    hasMorePages = data.total_pages > currentPage;
                } else {
                    movieGrid.innerHTML = '<div class="no-results"><p>Aucun film d\'animation trouvé. Essayez une recherche différente.</p></div>';
                    hasMorePages = false;
                }
            } catch (error) {
                console.error('Erreur lors de la recherche:', error);
                movieGrid.innerHTML = '<div class="no-results"><p>Une erreur est survenue lors de la recherche. Veuillez réessayer.</p></div>';
            } finally {
                isLoading = false;
                loading.style.display = 'none';
            }
        }, 500);
    });

    // Modification du chargement infini pour prendre en compte tous les filtres
    async function loadMoreMovies() {
        if (isLoading || !hasMorePages) return;
        
        isLoading = true;
        currentPage++;
        
        loading.style.display = 'block';

        try {
            // Récupérer tous les paramètres de filtres actuels
            const params = getCurrentFilterParams();
            params.page = currentPage;
            
            // Construire l'URL appropriée en fonction du mode (recherche ou filtres)
            let url;
            if (params.query) {
                // Mode recherche
                url = buildUrl('index.php?action=search', params);
            } else {
                // Mode filtres ou par défaut
                url = buildUrl('index.php?action=loadMore', params);
            }
                
            const response = await fetch(url);
            if (!response.ok) {
                throw new Error(`Erreur HTTP: ${response.status}`);
            }
            
            const data = await response.json();
            
            if (data.movies && data.movies.length > 0) {
                data.movies.forEach(movie => {
                    const movieCard = createMovieCard(movie);
                    movieGrid.appendChild(movieCard);
                });
                
                // Vérifier s'il y a d'autres pages
                hasMorePages = data.total_pages > currentPage;
            } else {
                hasMorePages = false;
            }
            
            // Si c'est la dernière page, afficher un message
            if (!hasMorePages) {
                const endMessage = document.createElement('div');
                endMessage.className = 'end-message';
                endMessage.innerHTML = '<p>Vous avez atteint la fin des résultats.</p>';
                movieGrid.appendChild(endMessage);
            }
        } catch (error) {
            console.error('Erreur lors du chargement des films:', error);
            hasMorePages = false;
            
            const errorMessage = document.createElement('div');
            errorMessage.className = 'error-message';
            errorMessage.innerHTML = '<p>Une erreur est survenue lors du chargement des films.</p>';
            movieGrid.appendChild(errorMessage);
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
                          alt="${movie.title}">` : 
                    `<div class="no-poster">Pas d'affiche disponible</div>`}
                <h3>${movie.title}</h3>
                <div class="movie-info-overlay">
                    <div class="movie-rating">${movie.vote_average ? movie.vote_average.toFixed(1) : 'N/A'}</div>
                    <div class="movie-year">${movie.release_date ? movie.release_date.split('-')[0] : 'N/A'}</div>
                </div>
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
    
    // Gestion du panneau de filtres (pour le replier/déplier)
    if (toggleFilters) {
        toggleFilters.addEventListener('click', function() {
            filterPanel.classList.toggle('collapsed');
            
            // Mémoriser l'état du panneau dans localStorage
            const isCollapsed = filterPanel.classList.contains('collapsed');
            localStorage.setItem('filterPanelCollapsed', isCollapsed);
        });
        
        // Restaurer l'état du panneau au chargement
        const wasCollapsed = localStorage.getItem('filterPanelCollapsed') === 'true';
        if (wasCollapsed) {
            filterPanel.classList.add('collapsed');
        }
    }
    
    // Gestion de la soumission du formulaire de filtres
    const filtersForm = document.getElementById('filtersForm');
    if (filtersForm) {
        filtersForm.addEventListener('submit', function(e) {
            // Supprimer les champs vides pour ne pas encombrer l'URL
            const formElements = Array.from(this.elements);
            formElements.forEach(element => {
                if (element.type !== 'submit' && !element.value) {
                    element.disabled = true;
                }
            });
            
            // La soumission du formulaire se fait normalement (le formulaire recharge la page)
        });
    }
    
    // Chargement initial si nécessaire
    if (movieGrid.children.length > 0 && movieGrid.querySelector('.movie-card')) {
        // Des films sont déjà chargés, configurer le chargement pour la page suivante
        hasMorePages = true;
    }
}); 