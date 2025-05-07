<?php

namespace App\Controllers;

use App\Models\User;
use \Core\Database;
use \Core\Session;

class UserController
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    public function login()
    {
        // Vérifier si l'utilisateur est déjà connecté
        if (Session::get('user')) {
            header('Location: /');
            exit;
        }
        
        // Si le formulaire est soumis
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            
            // Vérifier les champs
            if (empty($email) || empty($password)) {
                Session::set('error', 'Tous les champs sont obligatoires');
                header('Location: /login');
                exit;
            }
            
            // Récupérer l'utilisateur
            $user = $this->db->prepare('SELECT * FROM users WHERE email = ?', [$email]);
            
            // Vérifier si l'utilisateur existe
            if (!$user) {
                Session::set('error', 'Email ou mot de passe incorrect');
                header('Location: /login');
                exit;
            }
            
            // Vérifier le mot de passe
            if (!password_verify($password, $user['password'])) {
                Session::set('error', 'Email ou mot de passe incorrect');
                header('Location: /login');
                exit;
            }
            
            // Supprimer le mot de passe de la session
            unset($user['password']);
            
            // Connecter l'utilisateur
            Session::set('user', $user);
            
            // Rediriger vers la page d'accueil
            header('Location: /');
            exit;
        }
        
        // Afficher la page de connexion
        require_once 'app/Views/auth/login.php';
    }
    
    public function register()
    {
        // Vérifier si l'utilisateur est déjà connecté
        if (Session::get('user')) {
            header('Location: /');
            exit;
        }
        
        // Si le formulaire est soumis
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $password_confirm = $_POST['password_confirm'] ?? '';
            
            // Vérifier les champs
            if (empty($username) || empty($email) || empty($password) || empty($password_confirm)) {
                Session::set('error', 'Tous les champs sont obligatoires');
                header('Location: /register');
                exit;
            }
            
            // Vérifier si les mots de passe correspondent
            if ($password !== $password_confirm) {
                Session::set('error', 'Les mots de passe ne correspondent pas');
                header('Location: /register');
                exit;
            }
            
            // Vérifier si l'email est déjà utilisé
            $user = $this->db->prepare('SELECT * FROM users WHERE email = ?', [$email]);
            
            if ($user) {
                Session::set('error', 'Cet email est déjà utilisé');
                header('Location: /register');
                exit;
            }
            
            // Vérifier si le nom d'utilisateur est déjà utilisé
            $user = $this->db->prepare('SELECT * FROM users WHERE username = ?', [$username]);
            
            if ($user) {
                Session::set('error', 'Ce nom d\'utilisateur est déjà utilisé');
                header('Location: /register');
                exit;
            }
            
            // Hasher le mot de passe
            $password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insérer l'utilisateur
            $this->db->prepare('INSERT INTO users (username, email, password) VALUES (?, ?, ?)', [$username, $email, $password]);
            
            // Connecter l'utilisateur
            $user = $this->db->prepare('SELECT * FROM users WHERE email = ?', [$email]);
            
            // Supprimer le mot de passe de la session
            unset($user['password']);
            
            // Connecter l'utilisateur
            Session::set('user', $user);
            
            // Rediriger vers la page d'accueil
            header('Location: /');
            exit;
        }
        
        // Afficher la page d'inscription
        require_once 'app/Views/auth/register.php';
    }
    
    public function logout()
    {
        // Déconnecter l'utilisateur
        Session::destroy();
        
        // Rediriger vers la page d'accueil
        header('Location: /');
        exit;
    }
    
    public function profile()
    {
        // Vérifier si l'utilisateur est connecté
        if (!Session::get('user')) {
            header('Location: /login');
            exit;
        }
        
        // Afficher la page de profil
        require_once 'app/Views/auth/profile.php';
    }

    public function updateProfile()
    {
        // Vérifier si l'utilisateur est connecté
        if (!Session::get('user')) {
            header('Location: /login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $user = Session::get('user');
            $userId = $user['id'];
            $username = $_POST['username'] ?? $user['username'];
            $email = $_POST['email'] ?? $user['email'];
            $profilePicture = $user['profile_picture']; // Valeur actuelle par défaut

            // Vérifier si l'email est déjà utilisé par un autre utilisateur
            $existingUser = $this->db->prepare('SELECT id FROM users WHERE email = ? AND id != ?', [$email, $userId]);
            if ($existingUser) {
                Session::set('error', 'Cet email est déjà utilisé par un autre utilisateur');
                header('Location: /profile');
                exit;
            }

            // Vérifier si le nom d'utilisateur est déjà utilisé par un autre utilisateur
            $existingUser = $this->db->prepare('SELECT id FROM users WHERE username = ? AND id != ?', [$username, $userId]);
            if ($existingUser) {
                Session::set('error', 'Ce nom d\'utilisateur est déjà utilisé par un autre utilisateur');
                header('Location: /profile');
                exit;
            }

            // Gestion du téléchargement de la photo de profil
            if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === 0) {
                $allowed = ['jpg', 'jpeg', 'png', 'gif'];
                $filename = $_FILES['profile_picture']['name'];
                $fileTmpName = $_FILES['profile_picture']['tmp_name'];
                $fileSize = $_FILES['profile_picture']['size'];
                $fileExt = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

                // Vérifier l'extension du fichier
                if (!in_array($fileExt, $allowed)) {
                    Session::set('error', 'Format de fichier non autorisé. Seuls les formats jpg, jpeg, png et gif sont acceptés.');
                    header('Location: /profile');
                    exit;
                }

                // Vérifier la taille du fichier (max 2MB)
                if ($fileSize > 2097152) {
                    Session::set('error', 'La taille du fichier est trop grande. Maximum 2MB.');
                    header('Location: /profile');
                    exit;
                }

                // Créer un nom de fichier unique
                $newFilename = uniqid('profile_', true) . '.' . $fileExt;
                $uploadPath = 'assets/images/profiles/' . $newFilename;

                // Créer le répertoire s'il n'existe pas
                if (!file_exists('assets/images/profiles/')) {
                    mkdir('assets/images/profiles/', 0777, true);
                }

                // Déplacer le fichier
                if (move_uploaded_file($fileTmpName, $uploadPath)) {
                    // Supprimer l'ancienne image si elle existe et n'est pas l'image par défaut
                    if ($profilePicture && $profilePicture !== 'default.jpg' && file_exists('assets/images/profiles/' . $profilePicture)) {
                        unlink('assets/images/profiles/' . $profilePicture);
                    }
                    $profilePicture = $newFilename;
                } else {
                    Session::set('error', 'Une erreur est survenue lors du téléchargement de l\'image');
                    header('Location: /profile');
                    exit;
                }
            }

            // Mise à jour du profil dans la base de données
            $this->db->prepare(
                'UPDATE users SET username = ?, email = ?, profile_picture = ? WHERE id = ?',
                [$username, $email, $profilePicture, $userId]
            );

            // Mettre à jour les données de session
            $updatedUser = $this->db->prepare('SELECT * FROM users WHERE id = ?', [$userId]);
            unset($updatedUser['password']);
            Session::set('user', $updatedUser);

            Session::set('success', 'Votre profil a été mis à jour avec succès');
            header('Location: /profile');
            exit;
        }

        // Si la méthode n'est pas POST, rediriger vers la page de profil
        header('Location: /profile');
        exit;
    }

    public function changePassword()
    {
        // Vérifier si l'utilisateur est connecté
        if (!Session::get('user')) {
            header('Location: /login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $user = Session::get('user');
            $userId = $user['id'];
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            // Vérifier si tous les champs sont remplis
            if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
                Session::set('error', 'Tous les champs sont obligatoires');
                header('Location: /profile');
                exit;
            }

            // Vérifier si les nouveaux mots de passe correspondent
            if ($newPassword !== $confirmPassword) {
                Session::set('error', 'Les nouveaux mots de passe ne correspondent pas');
                header('Location: /profile');
                exit;
            }

            // Récupérer le mot de passe actuel de l'utilisateur
            $userPassword = $this->db->prepare('SELECT password FROM users WHERE id = ?', [$userId]);

            // Vérifier si le mot de passe actuel est correct
            if (!password_verify($currentPassword, $userPassword['password'])) {
                Session::set('error', 'Le mot de passe actuel est incorrect');
                header('Location: /profile');
                exit;
            }

            // Hasher le nouveau mot de passe
            $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);

            // Mettre à jour le mot de passe dans la base de données
            $this->db->prepare(
                'UPDATE users SET password = ? WHERE id = ?',
                [$newPasswordHash, $userId]
            );

            Session::set('success', 'Votre mot de passe a été modifié avec succès');
            header('Location: /profile');
            exit;
        }

        // Si la méthode n'est pas POST, rediriger vers la page de profil
        header('Location: /profile');
        exit;
    }
} 