document.addEventListener('DOMContentLoaded', function() {
    // Récupérer les éléments du DOM avec vérification d'existence
    const searchInput = document.getElementById('searchInput');
    const movieGrid = document.getElementById('movieGrid');
    const loading = document.getElementById('loading');
    const filterPanel = document.querySelector('.filter-panel');
    const toggleFilters = document.getElementById('toggleFilters');
    
    // Vérifier si les éléments essentiels existent
    const hasSearchFeature = searchInput && movieGrid;
    
    // Définir ces variables uniquement si les éléments existent
    const upcomingFilmsSlider = document.getElementById('upcomingFilmsSlider');
    const sliderLoading = document.getElementById('sliderLoading');
    const hasUpcomingSection = upcomingFilmsSlider && sliderLoading;
    
    // Si les éléments essentiels n'existent pas, ne pas continuer
    if (!hasSearchFeature) {
        console.warn("Éléments de recherche manquants dans le DOM, les fonctionnalités de recherche ne seront pas disponibles.");
        return;
    }
    
    let currentPage = 1;
    let isLoading = false;
    let searchTimeout;
    let currentSearchTerm = '';
    let hasMorePages = true;
    
    // Fonction pour charger les films populaires à venir
    async function loadUpcomingPopularFilms() {
        // Vérifier que les éléments nécessaires existent
        if (!hasUpcomingSection) return;
        
        try {
            sliderLoading.style.display = 'block';
            
            // Récupérer les films populaires à venir
            const response = await fetch('index.php?action=getUpcomingPopular');
            if (!response.ok) {
                throw new Error(`Erreur HTTP: ${response.status}`);
            }
            
            const contentType = response.headers.get("content-type");
            if (!contentType || !contentType.includes("application/json")) {
                throw new Error("Le serveur n'a pas renvoyé du JSON valide");
            }
            
            const data = await response.json();
            
            sliderLoading.style.display = 'none';
            
            // Afficher les 3 premiers films les plus populaires
            if (data.movies && data.movies.length > 0) {
                // Limiter à 3 films
                const topMovies = data.movies.slice(0, 3);
                
                // Nettoyer le contenu précédent
                upcomingFilmsSlider.innerHTML = '';
                
                // Ajouter les films
                topMovies.forEach(movie => {
                    const movieCard = createUpcomingFilmCard(movie);
                    upcomingFilmsSlider.appendChild(movieCard);
                });
            } else {
                upcomingFilmsSlider.innerHTML = '<p class="no-upcoming">Aucun film à venir n\'a été trouvé.</p>';
            }
        } catch (error) {
            console.error('Erreur lors du chargement des films à venir:', error);
            if (upcomingFilmsSlider) {
                upcomingFilmsSlider.innerHTML = '<p class="no-upcoming">Erreur lors du chargement des films à venir.</p>';
            }
        } finally {
            if (sliderLoading) {
                sliderLoading.style.display = 'none';
            }
        }
    }
    
    // Fonction pour créer une carte de film à venir
    function createUpcomingFilmCard(movie) {
        const card = document.createElement('div');
        card.className = 'upcoming-film-card';
        
        // Formater la date de sortie
        const releaseDate = movie.release_date ? 
            new Date(movie.release_date).toLocaleDateString('fr-FR', {
                day: 'numeric',
                month: 'long',
                year: 'numeric'
            }) : 'Date inconnue';
        
        card.innerHTML = `
            <a href="index.php?action=view&id=${movie.id}">
                ${movie.backdrop_path ? 
                    `<img src="https://image.tmdb.org/t/p/w500${movie.backdrop_path}" alt="${movie.title}">` : 
                    `<div class="no-poster">Pas d'affiche disponible</div>`}
                <div class="upcoming-film-info">
                    <h3 class="upcoming-film-title">${movie.title}</h3>
                    <div class="upcoming-film-date">Sortie: ${releaseDate}</div>
                </div>
                <div class="upcoming-film-popularity">❤ ${Math.round(movie.popularity)}</div>
            </a>
        `;
        
        return card;
    }
    
    // Charger les films populaires à venir uniquement si la section existe
    if (hasUpcomingSection) {
        loadUpcomingPopularFilms();
    }
    
    // Fonction pour récupérer les paramètres de filtres actuels
    function getCurrentFilterParams() {
        // Paramètres de base - toujours inclure le genre animation (16)
        const params = {
            with_genres: 16
        };
        
        // Si nous sommes en mode recherche, ajouter le terme de recherche
        if (currentSearchTerm.trim() !== '') {
            params.query = currentSearchTerm;
        }
        
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
        // Ensure we have a valid URL object whether baseUrl is relative or absolute
        let url;
        try {
            // Try to create a URL directly (works if baseUrl is absolute)
            url = new URL(baseUrl);
        } catch (e) {
            // If that fails, it's a relative URL, so prepend the current origin
            url = new URL(baseUrl, window.location.origin + window.location.pathname);
        }
        
        // Make sure page parameter is included
        if (!params.page && currentPage) {
            params.page = currentPage;
        }
        
        // Add all parameters
        Object.keys(params).forEach(key => {
            if (params[key] !== null && params[key] !== undefined && params[key] !== '') {
                url.searchParams.append(key, params[key]);
            }
        });
        
        return url.toString();
    }

    // Fonction pour charger les films populaires
    async function loadPopularMovies() {
        currentPage = 1;
        hasMorePages = true;
        isLoading = true;
        
        loading.style.display = 'block';
        
        try {
            // Appel AJAX pour récupérer les films populaires d'animation (genre 16)
            const response = await fetch('index.php?action=loadMore&page=1&with_genres=16');
            if (!response.ok) {
                throw new Error(`Erreur HTTP: ${response.status}`);
            }
            
            const contentType = response.headers.get("content-type");
            if (!contentType || !contentType.includes("application/json")) {
                throw new Error("Le serveur n'a pas renvoyé du JSON valide");
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
                movieGrid.innerHTML = '<div class="no-results"><p>Aucun film d\'animation trouvé.</p></div>';
                hasMorePages = false;
            }
        } catch (error) {
            console.error('Erreur lors du chargement des films populaires:', error);
            movieGrid.innerHTML = '<div class="no-results"><p>Une erreur est survenue lors du chargement des films. Veuillez rafraîchir la page.</p></div>';
        } finally {
            isLoading = false;
            loading.style.display = 'none';
        }
    }

    // Fonction de recherche modifiée
    searchInput.addEventListener('input', function(e) {
        clearTimeout(searchTimeout);
        const searchTerm = e.target.value.trim();
        currentSearchTerm = searchTerm;
        
        searchTimeout = setTimeout(async () => {
            // Si le champ de recherche est vide, charger les films populaires sans redirection
            if (searchTerm === '') {
                loadPopularMovies();
                return;
            }
            
            currentPage = 1;
            hasMorePages = true;
            isLoading = true;
            
            loading.style.display = 'block';
            
            try {
                // Ajout du paramètre with_genres=16 pour limiter aux films d'animation
                const response = await fetch(`index.php?action=search&query=${encodeURIComponent(searchTerm)}&page=${currentPage}&with_genres=16`);
                if (!response.ok) {
                    throw new Error(`Erreur HTTP: ${response.status}`);
                }
                
                const contentType = response.headers.get("content-type");
                if (!contentType || !contentType.includes("application/json")) {
                    throw new Error("Le serveur n'a pas renvoyé du JSON valide");
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
                movieGrid.innerHTML = '<div class="no-results"><p>Une erreur est survenue lors de la recherche. Veuillez rafraîchir la page.</p></div>';
            } finally {
                isLoading = false;
                loading.style.display = 'none';
            }
        }, 500);
    });

    // Fonction pour charger plus de films
    async function loadMoreMovies() {
        // Si déjà en train de charger ou s'il n'y a plus de pages, ne rien faire
        if (isLoading || !hasMorePages) {
            return;
        }
        
        // Marquer comme en train de charger
        isLoading = true;
        currentPage++;
        
        // Afficher l'indicateur de chargement
        loading.style.display = 'block';
        
        try {
            // Construire l'URL en fonction de la recherche
            let url;
            let params = getCurrentFilterParams();
            params.page = currentPage;
            
            // Ajouter with_genres=16 pour s'assurer que nous obtenons des films d'animation
            if (!params.with_genres) {
                params.with_genres = 16;
            }
            
            // Construire l'URL appropriée en fonction du mode de recherche
            if (currentSearchTerm) {
                url = buildUrl('index.php?action=search', params);
            } else {
                url = buildUrl('index.php?action=loadMore', params);
            }
            
            console.log('Requesting:', url); // Debug: Show the URL being requested
                
            const response = await fetch(url);
            if (!response.ok) {
                throw new Error(`Erreur HTTP: ${response.status}`);
            }
            
            const contentType = response.headers.get("content-type");
            if (!contentType || !contentType.includes("application/json")) {
                console.error('Content-Type non valide:', contentType);
                console.error('Réponse texte:', await response.text());
                throw new Error("Le serveur n'a pas renvoyé du JSON valide");
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
            errorMessage.innerHTML = '<p>Une erreur est survenue lors du chargement des films. Veuillez rafraîchir la page.</p>';
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
        toggleFilters.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
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