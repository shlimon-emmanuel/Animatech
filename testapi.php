<?php
// Test de l'API TMDB pour les films d'animation à venir

// Configuration
define('TMDB_API_KEY', 'e592f1f6d22e8a0437cd5fe1db8915c0');
define('TMDB_API_URL', 'https://api.themoviedb.org/3/');

// Fonctions utilitaires
function callApi($endpoint, $params = []) {
    $url = TMDB_API_URL . $endpoint . '?api_key=' . TMDB_API_KEY;
    
    foreach ($params as $key => $value) {
        $url .= "&{$key}=" . urlencode($value);
    }
    
    echo "API URL: " . $url . "<br>";
    
    $response = file_get_contents($url);
    
    if ($response === false) {
        echo "Erreur lors de l'appel à l'API<br>";
        return null;
    }
    
    return json_decode($response);
}

// Date d'aujourd'hui
$today = date('Y-m-d');

// 1. Test standard pour films d'animation à venir
echo "<h2>1. Films d'animation à venir (standard)</h2>";
$params1 = [
    'language' => 'fr-FR',
    'sort_by' => 'popularity.desc',
    'primary_release_date.gte' => $today,
    'with_genres' => '16', // Animation
    'page' => 1
];

$results1 = callApi('discover/movie', $params1);
displayResults($results1);

// 2. Test avec vote_count plus bas
echo "<h2>2. Films d'animation à venir (vote_count >= 0)</h2>";
$params2 = [
    'language' => 'fr-FR',
    'sort_by' => 'popularity.desc',
    'primary_release_date.gte' => $today,
    'with_genres' => '16', // Animation
    'vote_count.gte' => '0', // Pour inclure les films avec moins de votes
    'page' => 1
];

$results2 = callApi('discover/movie', $params2);
displayResults($results2);

// 3. Test avec date de sortie future plus éloignée
echo "<h2>3. Films d'animation avec date de sortie à 6 mois</h2>";
$sixMonthsLater = date('Y-m-d', strtotime('+6 months'));
$params3 = [
    'language' => 'fr-FR',
    'sort_by' => 'popularity.desc',
    'primary_release_date.gte' => $today,
    'primary_release_date.lte' => $sixMonthsLater,
    'with_genres' => '16', // Animation
    'vote_count.gte' => '0',
    'page' => 1
];

$results3 = callApi('discover/movie', $params3);
displayResults($results3);

// 4. Test pour films populaires avec genre animation (sans filtre de date)
echo "<h2>4. Films d'animation populaires (sans filtre de date)</h2>";
$params4 = [
    'language' => 'fr-FR',
    'sort_by' => 'popularity.desc',
    'with_genres' => '16', // Animation
    'page' => 1
];

$results4 = callApi('discover/movie', $params4);
displayResults($results4);

/**
 * Affiche les résultats de l'API de manière formatée
 */
function displayResults($results) {
    if (!$results || empty($results->results)) {
        echo "<div style='color: red;'>Aucun résultat trouvé</div>";
        return;
    }
    
    echo "<div>Nombre de résultats: " . count($results->results) . "</div>";
    echo "<div>Nombre total de pages: " . $results->total_pages . "</div>";
    echo "<div>Nombre total de résultats: " . $results->total_results . "</div>";
    
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Titre</th><th>Date de sortie</th><th>Popularité</th><th>Poster</th></tr>";
    
    foreach ($results->results as $movie) {
        echo "<tr>";
        echo "<td>" . $movie->id . "</td>";
        echo "<td>" . $movie->title . "</td>";
        echo "<td>" . ($movie->release_date ?? 'Inconnue') . "</td>";
        echo "<td>" . $movie->popularity . "</td>";
        
        if (!empty($movie->poster_path)) {
            echo "<td><img src='https://image.tmdb.org/t/p/w92" . $movie->poster_path . "' alt='Poster'></td>";
        } else {
            echo "<td>Pas de poster</td>";
        }
        
        echo "</tr>";
    }
    
    echo "</table>";
}

?> 