<?php
namespace App\Models;

use App\Config\Redis;

/**
 * Modèle de cache utilisant Redis (NoSQL) pour stocker les résultats de l'API
 */
class CacheModel {
    private $redis;
    private $expiry = 3600; // 1 heure par défaut
    
    /**
     * Constructeur - initialise la connexion Redis
     */
    public function __construct() {
        $this->redis = Redis::getClient();
    }
    
    /**
     * Met en cache le résultat d'un appel à l'API
     * 
     * @param string $key Clé unique pour identifier les données
     * @param mixed $data Données à mettre en cache
     * @param int $expiry Durée de validité en secondes (défaut 1h)
     * @return bool Succès de l'opération
     */
    public function cacheApiResult($key, $data, $expiry = null) {
        $expiryTime = $expiry ?? $this->expiry;
        try {
            return $this->redis->setex(
                "api:tmdb:$key", 
                $expiryTime, 
                json_encode($data)
            );
        } catch (\Exception $e) {
            error_log("Erreur lors de la mise en cache: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Récupère les données en cache
     * 
     * @param string $key Clé unique pour identifier les données
     * @return mixed Données en cache ou null si absent/expiré
     */
    public function getCachedApiResult($key) {
        try {
            $data = $this->redis->get("api:tmdb:$key");
            if ($data) {
                return json_decode($data, true);
            }
        } catch (\Exception $e) {
            error_log("Erreur lors de la récupération du cache: " . $e->getMessage());
        }
        return null;
    }
    
    /**
     * Vérifie si une clé existe en cache
     * 
     * @param string $key Clé à vérifier
     * @return bool True si la clé existe en cache
     */
    public function hasCache($key) {
        try {
            return (bool)$this->redis->exists("api:tmdb:$key");
        } catch (\Exception $e) {
            error_log("Erreur lors de la vérification du cache: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Supprime une entrée du cache
     * 
     * @param string $key Clé à supprimer
     * @return bool Succès de l'opération
     */
    public function invalidateCache($key) {
        try {
            return (bool)$this->redis->del("api:tmdb:$key");
        } catch (\Exception $e) {
            error_log("Erreur lors de l'invalidation du cache: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Nettoie toutes les entrées de cache liées à l'API TMDB
     * 
     * @return bool Succès de l'opération
     */
    public function clearAllApiCache() {
        try {
            $keys = $this->redis->keys("api:tmdb:*");
            if (count($keys) > 0) {
                return (bool)$this->redis->del($keys);
            }
            return true;
        } catch (\Exception $e) {
            error_log("Erreur lors du nettoyage du cache: " . $e->getMessage());
            return false;
        }
    }
} 