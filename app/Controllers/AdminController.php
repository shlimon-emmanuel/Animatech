<?php
namespace App\Controllers;

use App\Models\UserModel;

class AdminController {
    private $userModel;

    public function __construct() {
        $this->userModel = new UserModel();
        
        // Vérifier si on est dans une route admin avant de vérifier les permissions
        // Cela évite la vérification lors de l'initialisation du contrôleur dans le routeur
        $action = $_GET['action'] ?? '';
        if ($action === 'admin') {
            $this->checkPermissions();
        }
    }
    
    /**
     * Vérifie si l'utilisateur connecté est un superadmin
     */
    private function checkPermissions() {
        // Vérifier si l'utilisateur est connecté
        if (!isset($_SESSION['user']) && !isset($_SESSION['user_id'])) {
            $_SESSION['error'] = "Vous devez être connecté pour accéder à cette page";
            header('Location: index.php?action=login');
            exit;
        }
        
        // Déterminer l'ID de l'utilisateur en fonction du format de session
        $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 
                (isset($_SESSION['user']['id']) ? $_SESSION['user']['id'] : null);
        
        if (!$userId) {
            $_SESSION['error'] = "Erreur d'identification de l'utilisateur";
            header('Location: index.php');
            exit;
        }
        
        // Ajouter un debug log
        error_log("Vérification des permissions pour l'utilisateur ID: $userId");
        
        // Vérifier si l'utilisateur est un superadmin
        if (!$this->userModel->isSuperAdmin($userId)) {
            $_SESSION['error'] = "Vous n'avez pas les droits pour accéder à cette page";
            header('Location: index.php');
            exit;
        }
    }
    
    /**
     * Affiche le tableau de bord d'administration
     */
    public function dashboard() {
        $users = $this->userModel->getAllUsers();
        require_once APP_PATH . '/Views/admin/dashboard.php';
    }
    
    /**
     * Gestion des utilisateurs - liste tous les utilisateurs
     */
    public function manageUsers() {
        $searchTerm = $_GET['search'] ?? '';
        
        if (!empty($searchTerm)) {
            $users = $this->userModel->searchUsers($searchTerm);
        } else {
            $users = $this->userModel->getAllUsers();
        }
        
        require_once APP_PATH . '/Views/admin/users.php';
    }
    
    /**
     * Affiche le formulaire d'édition d'un utilisateur
     */
    public function editUser() {
        if (!isset($_GET['id'])) {
            $_SESSION['error'] = "ID d'utilisateur manquant";
            header('Location: index.php?action=admin&subaction=users');
            exit;
        }
        
        $userId = (int)$_GET['id'];
        $user = $this->userModel->getUserById($userId);
        
        if (!$user) {
            $_SESSION['error'] = "Utilisateur introuvable";
            header('Location: index.php?action=admin&subaction=users');
            exit;
        }
        
        require_once APP_PATH . '/Views/admin/edit_user.php';
    }
    
    /**
     * Traite la mise à jour d'un utilisateur
     */
    public function updateUser() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?action=admin&subaction=users');
            exit;
        }
        
        $userId = (int)$_POST['user_id'];
        $username = htmlspecialchars($_POST['username']);
        $email = htmlspecialchars($_POST['email']);
        $role = htmlspecialchars($_POST['role']);
        
        // Validation de base
        if (empty($username) || empty($email) || empty($role)) {
            $_SESSION['error'] = "Tous les champs sont obligatoires";
            header("Location: index.php?action=admin&subaction=editUser&id=$userId");
            exit;
        }
        
        // Traitement de l'image de profil si présente
        $profilePicture = null;
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'assets/uploads/profiles/';
            
            if (!file_exists($uploadDir)) {
                if (!mkdir($uploadDir, 0777, true)) {
                    $_SESSION['error'] = "Erreur: Impossible de créer le dossier d'upload";
                    header("Location: index.php?action=admin&subaction=editUser&id=$userId");
                    exit;
                }
            }
            
            $fileExtension = pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
            $fileName = 'user_' . $userId . '_' . time() . '.' . $fileExtension;
            $targetFile = $uploadDir . $fileName;
            
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $fileType = $_FILES['profile_picture']['type'];
            
            if (in_array($fileType, $allowedTypes)) {
                if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $targetFile)) {
                    $profilePicture = $targetFile;
                    
                    // Récupérer l'ancienne image
                    $user = $this->userModel->getUserById($userId);
                    if ($user && !empty($user['profile_picture']) && file_exists($user['profile_picture'])) {
                        unlink($user['profile_picture']);
                    }
                }
            } else {
                $_SESSION['error'] = "Format d'image non supporté. Utilisez JPG, PNG, GIF ou WEBP.";
                header("Location: index.php?action=admin&subaction=editUser&id=$userId");
                exit;
            }
        }
        
        // Mise à jour de l'utilisateur
        $success = $this->userModel->updateUserByAdmin($userId, $username, $email, $role, $profilePicture);
        
        if ($success) {
            $_SESSION['success'] = "Utilisateur mis à jour avec succès";
        } else {
            $_SESSION['error'] = "Erreur lors de la mise à jour de l'utilisateur";
        }
        
        header('Location: index.php?action=admin&subaction=users');
        exit;
    }
    
    /**
     * Supprime un utilisateur
     */
    public function deleteUser() {
        if (!isset($_GET['id'])) {
            $_SESSION['error'] = "ID d'utilisateur manquant";
            header('Location: index.php?action=admin&subaction=users');
            exit;
        }
        
        $userId = (int)$_GET['id'];
        
        // Empêcher de supprimer son propre compte
        $currentUserId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : $_SESSION['user']['id'];
        if ($userId === (int)$currentUserId) {
            $_SESSION['error'] = "Vous ne pouvez pas supprimer votre propre compte";
            header('Location: index.php?action=admin&subaction=users');
            exit;
        }
        
        // Récupérer l'utilisateur pour vérifier et supprimer l'image de profil
        $user = $this->userModel->getUserById($userId);
        
        // Supprimer l'utilisateur
        $success = $this->userModel->deleteUser($userId);
        
        if ($success) {
            // Supprimer l'image de profil si elle existe
            if ($user && !empty($user['profile_picture']) && file_exists($user['profile_picture'])) {
                unlink($user['profile_picture']);
            }
            
            $_SESSION['success'] = "Utilisateur supprimé avec succès";
        } else {
            $_SESSION['error'] = "Erreur lors de la suppression de l'utilisateur";
        }
        
        header('Location: index.php?action=admin&subaction=users');
        exit;
    }
    
    /**
     * Affiche la page de gestion du cache Redis (NoSQL)
     */
    public function manageCache() {
        // Vérification des droits d'administration déjà effectuée dans le constructeur
        
        // Initialiser les variables par défaut au cas où Redis ne serait pas disponible
        $redisStatus = false;
        $cacheCount = 0;
        $memoryUsage = '0 MB';
        $uptime = '0 sec';
        $cacheHits = 0;
        $hitRate = 0;
        $timeSaved = 0;
        $apiRequestsAvoided = 0;
        
        try {
            // Charger le modèle de cache
            require_once APP_PATH . '/Models/CacheModel.php';
            $cacheModel = new \App\Models\CacheModel();
            
            // Récupérer les statistiques Redis
            $redisStatus = true;
            
            // Ces lignes peuvent échouer si Redis n'est pas disponible
            // mais c'est OK, on utilise les valeurs par défaut
            try {
                $redisClient = \App\Config\Redis::getClient();
                $info = $redisClient->info();
                
                // Statistiques de base
                $dbSize = $redisClient->dbSize();
                $cacheCount = $dbSize;
                
                // Mémoire utilisée
                $memoryBytes = $info['Memory']['used_memory'] ?? 0;
                $memoryUsage = round($memoryBytes / (1024 * 1024), 2) . ' MB';
                
                // Temps de fonctionnement
                $uptimeSeconds = $info['Server']['uptime_in_seconds'] ?? 0;
                if ($uptimeSeconds < 60) {
                    $uptime = $uptimeSeconds . ' sec';
                } elseif ($uptimeSeconds < 3600) {
                    $uptime = round($uptimeSeconds / 60, 1) . ' min';
                } else {
                    $uptime = round($uptimeSeconds / 3600, 1) . ' heures';
                }
                
                // Statistiques de performance (simulées pour l'exemple)
                $stats = $redisClient->get('api:stats') ?: json_encode([
                    'hits' => 0,
                    'misses' => 0,
                    'time_saved' => 0,
                    'requests_avoided' => 0
                ]);
                
                $stats = json_decode($stats, true);
                $cacheHits = $stats['hits'] ?? 0;
                $cacheMisses = $stats['misses'] ?? 0;
                $totalRequests = $cacheHits + $cacheMisses;
                $hitRate = $totalRequests > 0 ? round(($cacheHits / $totalRequests) * 100, 1) : 0;
                $timeSaved = round($stats['time_saved'] ?? 0, 2);
                $apiRequestsAvoided = $stats['requests_avoided'] ?? 0;
            } catch (\Exception $e) {
                error_log("Erreur lors de la récupération des statistiques Redis: " . $e->getMessage());
                // On continue avec les valeurs par défaut
            }
            
        } catch (\Exception $e) {
            // En cas d'erreur, journaliser mais ne pas faire planter l'application
            error_log("Erreur dans AdminController::manageCache: " . $e->getMessage());
        }
        
        // Afficher la vue
        require_once APP_PATH . '/Views/admin/cache.php';
    }
    
    /**
     * Traite les actions de nettoyage du cache Redis
     */
    public function clearCache() {
        // Vérification du jeton CSRF
        if (!isset($_POST['csrf_token']) || !$this->validateCsrfToken($_POST['csrf_token'])) {
            $_SESSION['error'] = "Erreur de sécurité. Veuillez réessayer.";
            header("Location: index.php?action=admin&subaction=cache");
            exit;
        }
        
        try {
            // Charger le modèle de cache
            require_once APP_PATH . '/Models/CacheModel.php';
            $cacheModel = new \App\Models\CacheModel();
            
            // Vider tout le cache
            if (isset($_POST['clear_all']) && $_POST['clear_all'] == 1) {
                $success = $cacheModel->clearAllApiCache();
                if ($success) {
                    $_SESSION['success'] = "Le cache Redis a été vidé avec succès.";
                } else {
                    $_SESSION['error'] = "Erreur lors du vidage du cache Redis.";
                }
            }
            
            // Vider uniquement le cache des films
            if (isset($_POST['clear_movies']) && $_POST['clear_movies'] == 1) {
                $success = true;
                $keys = ['popular_movies:', 'filtered_movies:', 'movie:'];
                
                foreach ($keys as $keyPrefix) {
                    try {
                        $redisClient = \App\Config\Redis::getClient();
                        $keysToDelete = $redisClient->keys("api:tmdb:$keyPrefix*");
                        
                        if (!empty($keysToDelete)) {
                            $redisClient->del($keysToDelete);
                        }
                    } catch (\Exception $e) {
                        error_log("Erreur lors de la suppression des clés $keyPrefix: " . $e->getMessage());
                        $success = false;
                    }
                }
                
                if ($success) {
                    $_SESSION['success'] = "Le cache des films a été vidé avec succès.";
                } else {
                    $_SESSION['error'] = "Erreur lors du vidage du cache des films.";
                }
            }
            
        } catch (\Exception $e) {
            $_SESSION['error'] = "Une erreur est survenue: " . $e->getMessage();
            error_log("Erreur dans AdminController::clearCache: " . $e->getMessage());
        }
        
        // Rediriger vers la page de gestion du cache
        header("Location: index.php?action=admin&subaction=cache");
        exit;
    }
    
    /**
     * Vérifie si le jeton CSRF est valide
     */
    private function validateCsrfToken($token) {
        if (!isset($_SESSION['csrf_tokens']) || !is_array($_SESSION['csrf_tokens']) || 
            !isset($_SESSION['csrf_tokens'][$token])) {
            return false;
        }
        
        // Vérifier si le token n'a pas expiré
        if (time() > $_SESSION['csrf_tokens'][$token]) {
            unset($_SESSION['csrf_tokens'][$token]);
            return false;
        }
        
        // Utilisation unique - supprimer le token après utilisation
        unset($_SESSION['csrf_tokens'][$token]);
        return true;
    }
} 