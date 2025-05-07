<?php
// app/Models/FavoriteModel.php

namespace App\Models;

use PDO;
use PDOException;

class FavoriteModel {
    private $db;

    public function __construct() {
        try {
            $this->db = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8",
                DB_USER,
                DB_PASS,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            
            // Créer la table favorites si elle n'existe pas
            $this->createFavoritesTable();
        } catch (PDOException $e) {
            die("Erreur de connexion à la base de données: " . $e->getMessage());
        }
    }
    
    /**
     * Crée la table des favoris avec une structure simple
     */
    private function createFavoritesTable() {
        try {
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS favorites (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NOT NULL,
                    movie_id INT NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    UNIQUE KEY unique_favorite (user_id, movie_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8
            ");
            error_log("Table favorites vérifiée/créée avec succès");
        } catch (PDOException $e) {
            error_log("Erreur lors de la création de la table favorites: " . $e->getMessage());
        }
    }
    
    /**
     * Ajoute un film aux favoris
     */
    public function addToFavorites($userId, $movieId) {
        if (!$userId || !$movieId) {
            error_log("FavoriteModel::addToFavorites - Paramètres invalides - userId: $userId, movieId: $movieId");
            return false;
        }
        
        try {
            // Vérifier d'abord si le film est déjà en favoris
            if ($this->isFavorite($userId, $movieId)) {
                error_log("FavoriteModel::addToFavorites - Le film est déjà en favoris - userId: $userId, movieId: $movieId");
                return true;
            }
            
            // Insérer dans les favoris
            $stmt = $this->db->prepare("INSERT INTO favorites (user_id, movie_id) VALUES (?, ?)");
            $success = $stmt->execute([$userId, $movieId]);
            
            if ($success) {
                error_log("FavoriteModel::addToFavorites - Film ajouté aux favoris avec succès - userId: $userId, movieId: $movieId");
                return true;
            } else {
                error_log("FavoriteModel::addToFavorites - Échec de l'insertion - " . implode(", ", $stmt->errorInfo()));
                return false;
            }
        } catch (PDOException $e) {
            error_log("FavoriteModel::addToFavorites - Exception: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Supprime un film des favoris
     */
    public function removeFromFavorites($userId, $movieId) {
        if (!$userId || !$movieId) {
            error_log("FavoriteModel::removeFromFavorites - Paramètres invalides");
            return false;
        }
        
        try {
            $stmt = $this->db->prepare("DELETE FROM favorites WHERE user_id = ? AND movie_id = ?");
            $success = $stmt->execute([$userId, $movieId]);
            
            if ($success) {
                error_log("FavoriteModel::removeFromFavorites - Film retiré des favoris avec succès");
                return true;
            } else {
                error_log("FavoriteModel::removeFromFavorites - Échec de la suppression");
                return false;
            }
        } catch (PDOException $e) {
            error_log("FavoriteModel::removeFromFavorites - Exception: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Vérifie si un film est dans les favoris
     */
    public function isFavorite($userId, $movieId) {
        if (!$userId || !$movieId) {
            error_log("FavoriteModel::isFavorite - Paramètres invalides");
            return false;
        }
        
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM favorites WHERE user_id = ? AND movie_id = ?");
            $stmt->execute([$userId, $movieId]);
            $count = $stmt->fetchColumn();
            
            error_log("FavoriteModel::isFavorite - Résultat: " . ($count > 0 ? "true" : "false") . " pour userId: $userId, movieId: $movieId");
            
            return $count > 0;
        } catch (PDOException $e) {
            error_log("FavoriteModel::isFavorite - Exception: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Récupère tous les films favoris d'un utilisateur
     */
    public function getUserFavorites($userId) {
        if (!$userId) {
            error_log("FavoriteModel::getUserFavorites - ID utilisateur invalide");
            return [];
        }
        
        try {
            error_log("FavoriteModel::getUserFavorites - Tentative de récupération des favoris pour l'utilisateur $userId");
            
            // Vérifier si la table existe
            $tableCheck = $this->db->query("SHOW TABLES LIKE 'favorites'")->rowCount();
            if ($tableCheck == 0) {
                error_log("FavoriteModel::getUserFavorites - La table favorites n'existe pas, création en cours");
                $this->createFavoritesTable();
            }
            
            $stmt = $this->db->prepare("SELECT movie_id FROM favorites WHERE user_id = ?");
            $stmt->execute([$userId]);
            $movieIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            error_log("FavoriteModel::getUserFavorites - " . count($movieIds) . " favoris trouvés pour l'utilisateur $userId");
            error_log("FavoriteModel::getUserFavorites - Liste des IDs de films: " . implode(", ", $movieIds ?: []));
            
            return $movieIds;
        } catch (PDOException $e) {
            error_log("FavoriteModel::getUserFavorites - Exception: " . $e->getMessage());
            return [];
        }
    }
}
?> 