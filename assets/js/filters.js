document.addEventListener('DOMContentLoaded', function() {
    const filterPanel = document.querySelector('.filter-panel');
    const filterHeader = document.querySelector('.filter-header');
    const filterContent = document.querySelector('.filter-content');
    const movieGrid = document.getElementById('movieGrid');
    const loading = document.getElementById('loading');

    // État global des filtres
    let currentFilters = {
        sort_by: 'popularity.desc',
        with_genres: '16', // Animation genre
        with_original_language: '',
        vote_average_gte: '',
        year: '',
        primary_release_date_gte: '',
        primary_release_date_lte: ''
    };

    let currentPage = 1;
    let isLoading = false;
    let hasMorePages = true;

    // Gestion de l'ouverture/fermeture du panneau
    filterHeader.addEventListener('click', function() {
        filterPanel.classList.toggle('collapsed');
        const icon = filterHeader.querySelector('.icon');
        icon.textContent = filterPanel.classList.contains('collapsed') ? '▼' : '▲';
        
        if (!filterPanel.classList.contains('collapsed')) {
            filterContent.style.maxHeight = filterContent.scrollHeight + 'px';
        } else {
            filterContent.style.maxHeight = '0';
        }
    });

    // Fonction pour charger les films avec les filtres
    async function loadFilteredMovies(page = 1, append = false) {
        if (isLoading || (!append && !hasMorePages)) return;
        
        isLoading = true;
        loading.style.display = 'block';

        if (!append) {
            movieGrid.innerHTML = '';
            currentPage = 1;
            hasMorePages = true;
        }

        try {
            // Construire l'URL avec les filtres
            const params = new URLSearchParams();
            params.append('action', 'loadMore');
            params.append('page', page);
            params.append('ajax', '1');

            // Ajouter tous les filtres non vides
            Object.entries(currentFilters).forEach(([key, value]) => {
                if (value) params.append(key, value);
            });

            console.log('Paramètres de la requête:', params.toString());

            const response = await fetch(`index.php?${params.toString()}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            console.log('Statut de la réponse:', response.status);
            console.log('Type de contenu:', response.headers.get('content-type'));

            const responseText = await response.text();
            console.log('Réponse brute:', responseText);

            let data;
            try {
                data = JSON.parse(responseText);
                console.log('Données parsées:', data);
            } catch (e) {
                console.error('Erreur de parsing JSON:', e);
                throw new Error('Réponse invalide du serveur');
            }

            if (!data || !Array.isArray(data.movies)) {
                console.error('Structure de données invalide:', data);
                throw new Error('Structure de données invalide');
            }

            if (data.movies.length > 0) {
                console.log(`Ajout de ${data.movies.length} films`);
                data.movies.forEach(movie => {
                    const card = createMovieCard(movie);
                    movieGrid.appendChild(card);
                });

                hasMorePages = data.total_pages > page;
                currentPage = page;
                console.log(`Page: ${currentPage}, Plus de pages: ${hasMorePages}`);
            } else if (!append) {
                console.log('Aucun film trouvé');
                movieGrid.innerHTML = '<div class="no-results"><p>Aucun film ne correspond à vos critères.</p></div>';
                hasMorePages = false;
            } else if (page > 1) {
                console.log('Fin des résultats');
                // Vérifier s'il n'y a pas déjà de message de fin
                if (!document.querySelector('.end-message')) {
                    const endMessage = document.createElement('div');
                    endMessage.className = 'end-message';
                    endMessage.innerHTML = '<p>Vous avez atteint la fin des résultats.</p>';
                    movieGrid.appendChild(endMessage);
                }
                hasMorePages = false;
            }
        } catch (error) {
            console.error('Erreur:', error);
            if (!append) {
                movieGrid.innerHTML = '<div class="error-message"><p>Une erreur est survenue lors du chargement des films.</p></div>';
            }
            hasMorePages = false;
        } finally {
            isLoading = false;
            loading.style.display = 'none';
        }
    }

    // Création d'une carte de film
    function createMovieCard(movie) {
        const card = document.createElement('div');
        card.className = 'movie-card';
        
        const releaseDate = movie.release_date ? new Date(movie.release_date).getFullYear() : 'N/A';
        const rating = movie.vote_average ? movie.vote_average.toFixed(1) : 'N/A';
        
        card.innerHTML = `
            <a href="index.php?action=view&id=${movie.id}">
                ${movie.poster_path ? 
                    `<img src="https://image.tmdb.org/t/p/w500${movie.poster_path}" alt="${movie.title}">` : 
                    '<div class="no-poster">Pas d\'affiche disponible</div>'}
                <h3>${movie.title}</h3>
                <div class="movie-info">
                    <div class="movie-details">
                        <span class="year">${releaseDate}</span>
                        <span class="rating">★ ${rating}</span>
                    </div>
                </div>
            </a>
        `;
        
        return card;
    }

    // Gestion du formulaire de filtres
    const filtersForm = document.getElementById('filtersForm');
    filtersForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Mettre à jour l'état des filtres
        const formData = new FormData(filtersForm);
        for (const [key, value] of formData.entries()) {
            currentFilters[key] = value;
        }

        // Recharger les films avec les nouveaux filtres
        loadFilteredMovies(1);
    });

    // Gestion du bouton de réinitialisation
    const resetButton = filtersForm.querySelector('.reset-button');
    resetButton.addEventListener('click', function(e) {
        e.preventDefault();
        
        // Réinitialiser le formulaire
        filtersForm.reset();
        
        // Réinitialiser les filtres à leurs valeurs par défaut
        currentFilters = {
            sort_by: 'popularity.desc',
            with_genres: '16',
            with_original_language: '',
            vote_average_gte: '',
            year: '',
            primary_release_date_gte: '',
            primary_release_date_lte: ''
        };

        // Recharger les films
        loadFilteredMovies(1);
    });

    // Détecter le scroll pour charger plus de films
    window.addEventListener('scroll', () => {
        if ((window.innerHeight + window.scrollY) >= document.documentElement.scrollHeight - 500) {
            if (!isLoading && hasMorePages) {
                loadFilteredMovies(currentPage + 1, true);
            }
        }
    });

    // Chargement initial - désactivé pour éviter le conflit avec la page principale
    // loadFilteredMovies(1);
}); 