<?php
namespace App\Controllers;

use App\Models\UserModel;

class AuthController {
    private $userModel;

    public function __construct() {
        $this->userModel = new UserModel();
    }

    // Méthode pour générer un token CSRF
    private function generateCsrfToken() {
        // Initialiser le tableau des tokens s'il n'existe pas
        if (!isset($_SESSION['csrf_tokens'])) {
            $_SESSION['csrf_tokens'] = [];
        }
        
        // Utiliser un token unique pour chaque formulaire avec une durée de vie limitée
        $token = bin2hex(random_bytes(32));
        
        // Stocker le token avec son expiration (1 heure)
        $_SESSION['csrf_tokens'][$token] = time() + 3600;
        
        // Nettoyer les anciens tokens expirés
        $this->cleanExpiredCsrfTokens();
        
        return $token;
    }

    // Méthode pour nettoyer les tokens CSRF expirés
    private function cleanExpiredCsrfTokens() {
        if (isset($_SESSION['csrf_tokens']) && is_array($_SESSION['csrf_tokens'])) {
            foreach ($_SESSION['csrf_tokens'] as $token => $expiry) {
                if (time() > $expiry) {
                    unset($_SESSION['csrf_tokens'][$token]);
                }
            }
        }
    }

    // Méthode pour valider un token CSRF
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

    public function showLoginForm() {
        $csrf_token = $this->generateCsrfToken();
        require_once APP_PATH . '/Views/auth/login.php';
    }

    public function showRegisterForm() {
        $csrf_token = $this->generateCsrfToken();
        require_once APP_PATH . '/Views/auth/register.php';
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Vérification CSRF
            if (!isset($_POST['csrf_token']) || !$this->validateCsrfToken($_POST['csrf_token'])) {
                $_SESSION['error'] = 'Erreur de sécurité. Veuillez réessayer.';
                header('Location: index.php?action=login');
                exit;
            }
            
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            error_log("Tentative de connexion pour l'email: $email");
            
            $user = $this->userModel->login($email, $password);
            if ($user) {
                // Assurez-vous que le champ 'role' est présent
                if (!isset($user['role'])) {
                    $user['role'] = 'user'; // Valeur par défaut si non définie
                }
                
                // Enregistrer toutes les informations utilisateur dans la session
                $_SESSION['user'] = $user;
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                
                if (isset($user['profile_picture'])) {
                    $_SESSION['profile_picture'] = $user['profile_picture'];
                }
                
                // Régénérer le token CSRF après connexion réussie
                $this->generateCsrfToken();
                
                error_log("Connexion réussie pour l'utilisateur ID: {$user['id']}, Rôle: {$user['role']}");
                
                // Rediriger vers le tableau de bord admin si superadmin
                if ($user['role'] === 'superadmin') {
                    header('Location: index.php?action=admin');
                } else {
                    header('Location: index.php');
                }
                exit;
            }
            
            error_log("Échec de connexion pour l'email: $email");
            $_SESSION['error'] = 'Email ou mot de passe incorrect';
            header('Location: index.php?action=login');
            exit;
        }
    }

    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Vérification CSRF avec debug
                error_log("DEBUG Registration - CSRF Token reçu: " . ($_POST['csrf_token'] ?? 'ABSENT'));
                error_log("DEBUG Registration - Tokens en session: " . print_r($_SESSION['csrf_tokens'] ?? [], true));
                
                if (!isset($_POST['csrf_token'])) {
                    throw new \Exception('Token CSRF manquant. Veuillez réessayer.');
                }
                
                if (!$this->validateCsrfToken($_POST['csrf_token'])) {
                    throw new \Exception('Token CSRF invalide. Veuillez réessayer.');
                }
                
                // Nettoyage et validation des entrées
                $username = filter_var(trim($_POST['username'] ?? ''), FILTER_SANITIZE_STRING);
                $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
                $password = $_POST['password'] ?? '';
                
                // Validation approfondie
                if (empty($username) || empty($email) || empty($password)) {
                    throw new \Exception('Tous les champs sont obligatoires');
                }
                
                // Validation du nom d'utilisateur
                if (strlen($username) < 3 || strlen($username) > 50) {
                    throw new \Exception('Le nom d\'utilisateur doit contenir entre 3 et 50 caractères');
                }
                
                if (!preg_match('/^[a-zA-Z0-9_-]+$/', $username)) {
                    throw new \Exception('Le nom d\'utilisateur ne peut contenir que des lettres, chiffres, tirets et underscores');
                }
                
                // Validation de l'email
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    throw new \Exception('Format d\'email invalide');
                }
                
                // Validation du mot de passe
                if (strlen($password) < 8) {
                    throw new \Exception('Le mot de passe doit contenir au moins 8 caractères');
                }
                
                if (!preg_match('/[A-Z]/', $password) || 
                    !preg_match('/[a-z]/', $password) || 
                    !preg_match('/[0-9]/', $password)) {
                    throw new \Exception('Le mot de passe doit contenir au moins une majuscule, une minuscule et un chiffre');
                }
                
                // Tentative d'inscription
                if ($this->userModel->register($username, $email, $password)) {
                    $_SESSION['success'] = 'Inscription réussie ! Vous pouvez maintenant vous connecter.';
                    header('Location: index.php?action=login');
                    exit;
                } else {
                    throw new \Exception('L\'email ou le nom d\'utilisateur est peut-être déjà utilisé');
                }
                
            } catch (\Exception $e) {
                error_log("Erreur d'inscription: " . $e->getMessage());
                $_SESSION['error'] = $e->getMessage();
                header('Location: index.php?action=register');
                exit;
            }
        }
        
        // Afficher le formulaire d'inscription
        $csrf_token = $this->generateCsrfToken();
        require_once APP_PATH . '/Views/auth/register.php';
    }

    public function logout() {
        session_destroy();
        header('Location: index.php');
        exit;
    }

    public function showProfile() {
        // Vérifie si on essaie de voir le profil d'un autre utilisateur
        if (isset($_GET['user_id'])) {
            $userId = (int)$_GET['user_id'];
            
            // Vérifier si l'utilisateur existe
            $userInfo = $this->userModel->getUserById($userId);
            if (!$userInfo) {
                $_SESSION['error'] = "Utilisateur introuvable";
                header('Location: index.php');
                exit;
            }
            
            // Continuer avec le profil de cet utilisateur
        } else {
            // Profil de l'utilisateur connecté
            if (!isset($_SESSION['user']) && !isset($_SESSION['user_id'])) {
                header('Location: index.php?action=login');
                exit;
            }
            
            // Déterminer l'ID de l'utilisateur en fonction du format de session
            $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 
                    (isset($_SESSION['user']['id']) ? $_SESSION['user']['id'] : null);
            
            if (!$userId) {
                $_SESSION['error'] = "Erreur: Impossible d'identifier l'utilisateur";
                header('Location: index.php');
                exit;
            }
        }
        
        // Définir BASE_URL s'il n'est pas déjà défini
        if (!defined('BASE_URL')) {
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'];
            define('BASE_URL', $protocol . '://' . $host);
        }
        
        // Récupérer les informations utilisateur depuis la base de données
        $userInfo = $this->userModel->getUserById($userId);
        
        // Si on consulte son propre profil, synchroniser les sessions
        if (!isset($_GET['user_id'])) {
            // Synchroniser les sessions - assurer la cohérence entre les formats
            if (isset($_SESSION['user']['id']) && !isset($_SESSION['user_id'])) {
                $_SESSION['user_id'] = $_SESSION['user']['id'];
            }
            
            if (isset($_SESSION['user_id']) && !isset($_SESSION['user']['id']) && !isset($_SESSION['user'])) {
                $_SESSION['user'] = [
                    'id' => $_SESSION['user_id'],
                    'username' => $userInfo['username'] ?? ($_SESSION['username'] ?? 'Utilisateur'),
                    'email' => $userInfo['email'] ?? ($_SESSION['email'] ?? ''),
                    'profile_picture' => $userInfo['profile_picture'] ?? ($_SESSION['profile_picture'] ?? 'assets/img/default-profile.png')
                ];
            }
            
            // Mettre à jour les propriétés individuelles
            if (!isset($_SESSION['username']) && isset($userInfo['username'])) {
                $_SESSION['username'] = $userInfo['username'];
            }
            
            if (!isset($_SESSION['email']) && isset($userInfo['email'])) {
                $_SESSION['email'] = $userInfo['email'];
            }
            
            if (!isset($_SESSION['profile_picture']) && isset($userInfo['profile_picture'])) {
                $_SESSION['profile_picture'] = $userInfo['profile_picture'];
            }
        }
        
        // Gestion des onglets dynamiques
        $tab = isset($_GET['tab']) ? $_GET['tab'] : 'favorites';
        
        // Valider que l'onglet est valide
        if (!in_array($tab, ['favorites', 'reviews', 'replies'])) {
            $tab = 'favorites';
        }
        
        // Préparation des données pour chaque onglet
        $activeTab = $tab;
        $pageTitle = 'Profil de ' . ($userInfo['username'] ?? 'Utilisateur');
        $username = $userInfo['username'] ?? 'Utilisateur';
        $isOwnProfile = !isset($_GET['user_id']);
        
        // Initialiser les compteurs
        $favoriteCount = 0;
        $commentCount = 0;
        $replyCount = 0;
        
        // Charger les favoris si c'est l'onglet actif ou pour les compteurs
        $favorites = [];
        require_once APP_PATH . '/Models/FavoriteModel.php';
        require_once APP_PATH . '/Models/MovieModel.php';
        
        $favoriteModel = new \App\Models\FavoriteModel();
        $movieModel = new \App\Models\MovieModel(TMDB_API_KEY);
        
        // Récupérer les IDs des films favoris
        $favoriteMovieIds = $favoriteModel->getUserFavorites($userId);
        $favoriteCount = count($favoriteMovieIds);
        
        // Si l'onglet actif est "favorites", récupérer les détails des films
        if ($activeTab === 'favorites') {
            foreach ($favoriteMovieIds as $movieId) {
                $movie = $movieModel->getMovieById($movieId);
                if ($movie) {
                    // Convertir l'objet en tableau associatif si nécessaire
                    if (is_object($movie)) {
                        $favorites[] = json_decode(json_encode($movie), true);
                    } else {
                        $favorites[] = $movie;
                    }
                }
            }
        }
        
        // TODO: Charger les commentaires et réponses si nécessaire
        
        require_once APP_PATH . '/Views/auth/profile.php';
    }

    public function updateProfile() {
        if (!isset($_SESSION['user']) && !isset($_SESSION['user_id'])) {
            header('Location: index.php?action=login');
            exit;
        }

        // Déterminer l'ID de l'utilisateur en fonction du format de session
        $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 
                (isset($_SESSION['user']['id']) ? $_SESSION['user']['id'] : null);
                
        if (!$userId) {
            $_SESSION['error'] = "Erreur: Impossible d'identifier l'utilisateur";
            header('Location: index.php?action=profile');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Debug - Enregistrer les données POST reçues
            error_log("DEBUG - Données POST reçues dans updateProfile: " . print_r($_POST, true));
            error_log("DEBUG - Données FILES reçues dans updateProfile: " . print_r($_FILES, true));
            
            $username = htmlspecialchars($_POST['username']);
            $email = htmlspecialchars($_POST['email']);
            $profilePicture = null;
            
            // Vérifier si le mot de passe est modifié
            $updatePassword = false;
            if (!empty($_POST['current_password']) && !empty($_POST['new_password']) && 
                !empty($_POST['confirm_password'])) {
                
                if ($_POST['new_password'] !== $_POST['confirm_password']) {
                    $_SESSION['error'] = "Les mots de passe ne correspondent pas";
                    header('Location: index.php?action=profile');
                    exit;
                }
                
                // Vérifier le mot de passe actuel
                if (!$this->userModel->verifyPassword($userId, $_POST['current_password'])) {
                    $_SESSION['error'] = "Mot de passe actuel incorrect";
                    header('Location: index.php?action=profile');
                    exit;
                }
                
                $updatePassword = true;
            }
            
            // Traitement de l'upload de la photo de profil
            if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
                // S'assurer que le répertoire existe
                $uploadDir = 'assets/uploads/profiles/';
                if (!file_exists($uploadDir)) {
                    if (!mkdir($uploadDir, 0777, true)) {
                        error_log("Erreur: Impossible de créer le dossier d'upload " . $uploadDir);
                        $_SESSION['error'] = "Erreur: Impossible de créer le dossier d'upload";
                        header('Location: index.php?action=profile');
                        exit;
                    }
                }
                
                // Validation du type MIME
                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                $finfo = new \finfo(FILEINFO_MIME_TYPE);
                $fileType = $finfo->file($_FILES['profile_picture']['tmp_name']);
                
                if (!in_array($fileType, $allowedTypes)) {
                    $_SESSION['error'] = "Type de fichier non autorisé. Utilisez JPG, PNG, GIF ou WEBP.";
                    header('Location: index.php?action=profile');
                    exit;
                }
                
                // Générer un nom de fichier unique
                $extension = pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
                $newFilename = 'user_' . $userId . '_' . time() . '.' . $extension;
                $targetFile = $uploadDir . $newFilename;
                
                // Déplacer le fichier uploadé
                if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $targetFile)) {
                    // Succès - Définir le chemin pour la base de données
                    $profilePicture = $targetFile;
                    error_log("Image uploadée avec succès: " . $profilePicture);
                    
                    // Supprimer l'ancienne image si elle existe
                    $oldProfilePicture = '';
                    if (isset($_SESSION['user']['profile_picture'])) {
                        $oldProfilePicture = $_SESSION['user']['profile_picture'];
                    } elseif (isset($_SESSION['profile_picture'])) {
                        $oldProfilePicture = $_SESSION['profile_picture'];
                    }
                    
                    // Vérifier si l'ancienne image n'est pas l'image par défaut
                    if (!empty($oldProfilePicture) && 
                        $oldProfilePicture != 'assets/img/default-profile.png' &&
                        file_exists($oldProfilePicture)) {
                            unlink($oldProfilePicture);
                            error_log("Ancienne image supprimée: " . $oldProfilePicture);
                    }
                } else {
                    error_log("Erreur lors du déplacement du fichier uploadé vers " . $targetFile);
                    $_SESSION['error'] = "Erreur lors de l'upload de l'image";
                    header('Location: index.php?action=profile');
                    exit;
                }
            }

            try {
                // Préparer les données à mettre à jour
                $userData = [
                    'username' => $username,
                    'email' => $email
                ];
                
                if ($profilePicture) {
                    $userData['profile_picture'] = $profilePicture;
                }
                
                if ($updatePassword) {
                    $userData['password'] = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
                }
                
                error_log("Données utilisateur à mettre à jour: " . print_r($userData, true));
                
                // Mettre à jour l'utilisateur
                $updateResult = $this->userModel->updateUser($userId, $userData);
                
                if ($updateResult) {
                    // Mettre à jour les données de session
                    if (isset($_SESSION['user'])) {
                        $_SESSION['user']['username'] = $username;
                        $_SESSION['user']['email'] = $email;
                        
                        if ($profilePicture) {
                            $_SESSION['user']['profile_picture'] = $profilePicture;
                        }
                    }
                    
                    // Mettre à jour aussi le format alternatif de session
                    $_SESSION['username'] = $username;
                    $_SESSION['email'] = $email;
                    
                    if ($profilePicture) {
                        $_SESSION['profile_picture'] = $profilePicture;
                    }
                    
                    $_SESSION['success'] = "Profil mis à jour avec succès";
                } else {
                    $_SESSION['error'] = "Erreur lors de la mise à jour du profil";
                }
            } catch (\Exception $e) {
                error_log("Exception lors de la mise à jour du profil: " . $e->getMessage());
                $_SESSION['error'] = "Une erreur est survenue: " . $e->getMessage();
            }
            
            header('Location: index.php?action=profile');
            exit;
        } else {
            // Si c'est une requête GET, afficher le formulaire d'édition
            // Récupérer les informations utilisateur pour pré-remplir le formulaire
            $userInfo = $this->userModel->getUserById($userId);
            
            if (!$userInfo) {
                $_SESSION['error'] = "Erreur: Utilisateur introuvable";
                header('Location: index.php');
                exit;
            }
            
            // Afficher le formulaire d'édition
            require_once APP_PATH . '/Views/auth/edit-profile.php';
        }
    }
} 