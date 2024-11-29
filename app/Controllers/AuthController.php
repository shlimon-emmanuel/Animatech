<?php
namespace App\Controllers;

use App\Models\UserModel;

class AuthController {
    private $userModel;

    public function __construct() {
        $this->userModel = new UserModel();
    }

    public function showLoginForm() {
        require_once '../Views/auth/login.php';
    }

    public function showRegisterForm() {
        require_once '../Views/auth/register.php';
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            $user = $this->userModel->login($email, $password);
            if ($user) {
                $_SESSION['user'] = $user;
                header('Location: index.php');
                exit;
            }
            
            $_SESSION['error'] = 'Email ou mot de passe incorrect';
            header('Location: index.php?action=login');
            exit;
        }
    }

    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            if ($this->userModel->register($username, $email, $password)) {
                $_SESSION['success'] = 'Inscription réussie !';
                header('Location: index.php?action=login');
                exit;
            }
            
            $_SESSION['error'] = 'Erreur lors de l\'inscription';
            header('Location: index.php?action=register');
            exit;
        }
    }

    public function logout() {
        session_destroy();
        header('Location: index.php');
        exit;
    }

    public function showProfile() {
        if (!isset($_SESSION['user'])) {
            header('Location: index.php?action=login');
            exit;
        }
        require_once APP_PATH . '/Views/auth/profile.php';
    }

    public function updateProfile() {
        if (!isset($_SESSION['user'])) {
            header('Location: index.php?action=login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = htmlspecialchars($_POST['username']);
            $email = htmlspecialchars($_POST['email']);

            if ($this->userModel->updateProfile($_SESSION['user']['id'], $username, $email)) {
                $_SESSION['user']['username'] = $username;
                $_SESSION['user']['email'] = $email;
                $_SESSION['success'] = "Profil mis à jour avec succès";
            } else {
                $_SESSION['error'] = "Erreur lors de la mise à jour du profil";
            }
            
            header('Location: index.php?action=profile');
            exit;
        }
    }
} 