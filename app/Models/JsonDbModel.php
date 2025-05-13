<?php
namespace App\Models;

/**
 * Modèle de base de données NoSQL basé sur JSON
 * Système de stockage léger qui ne nécessite aucune installation
 */
class JsonDbModel {
    private $baseDir;
    private $defaultExpiry = 3600; // 1 heure par défaut
    
    /**
     * Constructeur - initialise le dossier de stockage
     */
    public function __construct() {
        // Définir le dossier de stockage des données JSON
        $this->baseDir = ROOT_PATH . '/storage/nosql';
        
        // Créer le dossier s'il n'existe pas
        if (!file_exists($this->baseDir)) {
            mkdir($this->baseDir, 0755, true);
        }
        
        // Créer un fichier .htaccess pour sécuriser le dossier
        $htaccessFile = $this->baseDir . '/.htaccess';
        if (!file_exists($htaccessFile)) {
            file_put_contents($htaccessFile, "Deny from all");
        }
        
        // Créer un fichier index.html vide pour éviter le listage du dossier
        $indexFile = $this->baseDir . '/index.html';
        if (!file_exists($indexFile)) {
            file_put_contents($indexFile, "<!DOCTYPE html><html><head><title>Access Denied</title></head><body><h1>Access Denied</h1></body></html>");
        }
    }
    
    /**
     * Met en cache les données dans un fichier JSON
     * 
     * @param string $collection Nom de la collection (comme une table en SQL)
     * @param string $key Clé unique pour identifier les données
     * @param mixed $data Données à stocker
     * @param int $expiry Durée de validité en secondes (défaut 1h)
     * @return bool Succès de l'opération
     */
    public function store($collection, $key, $data, $expiry = null) {
        $expiryTime = time() + ($expiry ?? $this->defaultExpiry);
        
        // Créer le dossier de la collection s'il n'existe pas
        $collectionDir = $this->baseDir . '/' . $this->sanitizePath($collection);
        if (!file_exists($collectionDir)) {
            if (!mkdir($collectionDir, 0755, true)) {
                error_log("Erreur: Impossible de créer le dossier de collection: " . $collectionDir);
                return false;
            }
        }
        
        // Préparer les données avec métadonnées
        $document = [
            'key' => $key,
            'data' => $data,
            'expiry' => $expiryTime,
            'created_at' => time()
        ];
        
        // Chemin du fichier JSON
        $filePath = $collectionDir . '/' . $this->sanitizePath($key) . '.json';
        
        // Enregistrer les données en JSON
        try {
            $result = file_put_contents($filePath, json_encode($document, JSON_PRETTY_PRINT));
            if ($result === false) {
                error_log("Erreur lors de l'écriture des données JSON: " . $filePath);
                return false;
            }
            return true;
        } catch (\Exception $e) {
            error_log("Erreur lors du stockage des données JSON: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Récupère les données depuis un fichier JSON
     * 
     * @param string $collection Nom de la collection
     * @param string $key Clé unique
     * @return mixed Données ou null si absent/expiré
     */
    public function get($collection, $key) {
        // Chemin du fichier JSON
        $filePath = $this->baseDir . '/' . $this->sanitizePath($collection) . '/' . $this->sanitizePath($key) . '.json';
        
        // Vérifier si le fichier existe
        if (!file_exists($filePath)) {
            return null;
        }
        
        // Lire et décoder le fichier JSON
        try {
            $content = file_get_contents($filePath);
            $document = json_decode($content, true);
            
            // Vérifier si les données sont expirées
            if (time() > $document['expiry']) {
                // Supprimer le fichier expiré
                @unlink($filePath);
                return null;
            }
            
            // Incrémenter les statistiques de cache hits
            $this->incrementStat('hits');
            
            // Calculer le temps économisé (simulé à 0.5 seconde par requête)
            $this->incrementStat('time_saved', 0.5);
            $this->incrementStat('requests_avoided');
            
            return $document['data'];
        } catch (\Exception $e) {
            error_log("Erreur lors de la récupération des données JSON: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Vérifie si une clé existe et n'est pas expirée
     * 
     * @param string $collection Nom de la collection
     * @param string $key Clé à vérifier
     * @return bool True si la clé existe et n'est pas expirée
     */
    public function has($collection, $key) {
        // Récupérer les données pour vérifier l'existence et l'expiration
        return $this->get($collection, $key) !== null;
    }
    
    /**
     * Supprime une entrée
     * 
     * @param string $collection Nom de la collection
     * @param string $key Clé à supprimer
     * @return bool Succès de l'opération
     */
    public function delete($collection, $key) {
        // Chemin du fichier JSON
        $filePath = $this->baseDir . '/' . $this->sanitizePath($collection) . '/' . $this->sanitizePath($key) . '.json';
        
        // Supprimer le fichier s'il existe
        if (file_exists($filePath)) {
            return @unlink($filePath);
        }
        
        return true; // Le fichier n'existait pas, donc considéré comme supprimé
    }
    
    /**
     * Nettoie toutes les entrées d'une collection
     * 
     * @param string $collection Nom de la collection à vider
     * @return bool Succès de l'opération
     */
    public function clearCollection($collection) {
        $collectionDir = $this->baseDir . '/' . $this->sanitizePath($collection);
        
        // Vérifier si le dossier existe
        if (!file_exists($collectionDir) || !is_dir($collectionDir)) {
            return true; // Le dossier n'existe pas, donc considéré comme vide
        }
        
        $success = true;
        
        // Parcourir tous les fichiers JSON dans le dossier
        foreach (glob($collectionDir . '/*.json') as $file) {
            if (!@unlink($file)) {
                $success = false;
                error_log("Impossible de supprimer le fichier: " . $file);
            }
        }
        
        return $success;
    }
    
    /**
     * Récupère les statistiques du cache
     * 
     * @return array Statistiques (hits, misses, time_saved, etc.)
     */
    public function getStats() {
        $statsFile = $this->baseDir . '/stats.json';
        
        if (file_exists($statsFile)) {
            try {
                $content = file_get_contents($statsFile);
                return json_decode($content, true) ?: [
                    'hits' => 0,
                    'misses' => 0,
                    'time_saved' => 0,
                    'requests_avoided' => 0
                ];
            } catch (\Exception $e) {
                error_log("Erreur lors de la lecture des statistiques: " . $e->getMessage());
            }
        }
        
        // Statistiques par défaut
        return [
            'hits' => 0,
            'misses' => 0,
            'time_saved' => 0,
            'requests_avoided' => 0
        ];
    }
    
    /**
     * Récupère des informations sur la base NoSQL
     * 
     * @return array Informations sur la base de données
     */
    public function getInfo() {
        // Collections (dossiers)
        $collections = [];
        foreach (glob($this->baseDir . '/*', GLOB_ONLYDIR) as $dir) {
            $dirName = basename($dir);
            if ($dirName !== 'temp') { // Ignorer les dossiers spéciaux
                $fileCount = count(glob($dir . '/*.json'));
                $collections[$dirName] = $fileCount;
            }
        }
        
        // Calculer la taille totale
        $totalSize = $this->getDirSize($this->baseDir);
        
        // Information sur la durée d'existence
        $creationTime = filemtime($this->baseDir) ?: time();
        $uptime = time() - $creationTime;
        
        return [
            'collections' => $collections,
            'total_size' => $totalSize,
            'total_size_formatted' => $this->formatSize($totalSize),
            'document_count' => array_sum($collections),
            'uptime' => $uptime,
            'uptime_formatted' => $this->formatUptime($uptime)
        ];
    }
    
    /**
     * Vérifie si la base NoSQL est accessible
     * 
     * @return bool Toujours true car basé sur des fichiers
     */
    public function isAvailable() {
        return is_dir($this->baseDir) && is_writable($this->baseDir);
    }
    
    /**
     * Sanitize les noms de chemin pour éviter les injections
     * 
     * @param string $path Chemin à sécuriser
     * @return string Chemin sécurisé
     */
    private function sanitizePath($path) {
        // Remplacer les deux-points qui sont particulièrement problématiques sous Windows
        $path = str_replace(':', '_', $path);
        
        // Remplacer les caractères non autorisés
        $path = preg_replace('/[^a-zA-Z0-9_\-.]/', '_', $path);
        
        // Éviter les noms de fichiers vides
        if (empty($path)) {
            $path = 'default';
        }
        return $path;
    }
    
    /**
     * Incrémente une statistique
     * 
     * @param string $stat Statistique à incrémenter
     * @param float $value Valeur à ajouter (défaut 1)
     */
    private function incrementStat($stat, $value = 1) {
        $statsFile = $this->baseDir . '/stats.json';
        $stats = $this->getStats();
        
        if (!isset($stats[$stat])) {
            $stats[$stat] = 0;
        }
        
        $stats[$stat] += $value;
        
        try {
            file_put_contents($statsFile, json_encode($stats, JSON_PRETTY_PRINT));
        } catch (\Exception $e) {
            error_log("Erreur lors de la mise à jour des statistiques: " . $e->getMessage());
        }
    }
    
    /**
     * Calcule la taille d'un dossier
     * 
     * @param string $dir Chemin du dossier
     * @return int Taille en octets
     */
    private function getDirSize($dir) {
        $size = 0;
        foreach (glob(rtrim($dir, '/').'/*', GLOB_NOSORT) as $each) {
            $size += is_file($each) ? filesize($each) : $this->getDirSize($each);
        }
        return $size;
    }
    
    /**
     * Formate une taille en octets en format lisible
     * 
     * @param int $bytes Taille en octets
     * @return string Taille formatée (ex: "2.5 MB")
     */
    private function formatSize($bytes) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, 2) . ' ' . $units[$pow];
    }
    
    /**
     * Formate une durée en secondes en format lisible
     * 
     * @param int $seconds Durée en secondes
     * @return string Durée formatée
     */
    private function formatUptime($seconds) {
        if ($seconds < 60) {
            return $seconds . ' sec';
        } elseif ($seconds < 3600) {
            return round($seconds / 60, 1) . ' min';
        } else {
            return round($seconds / 3600, 1) . ' heures';
        }
    }
} 