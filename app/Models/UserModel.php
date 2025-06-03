<?php

namespace App\Models;

use PDO;
use PDOException;

class UserModel {
    private $db;

    public function __construct() {
        try {
            $this->db = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
                ]
            );
        } catch(PDOException $e) {
            error_log("Erreur de connexion à la base de données: " . $e->getMessage());
            throw new \Exception("Erreur de connexion à la base de données");
        }
    }

    public function register($username, $email, $password) {
        try {
            // Début de la transaction
            $this->db->beginTransaction();
            
            // Vérifications de sécurité supplémentaires
            $username = substr(trim($username), 0, 50);
            $email = filter_var($email, FILTER_SANITIZE_EMAIL);
            
            // Vérifier si l'email existe déjà
            $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                throw new \Exception("Cet email est déjà utilisé");
            }
            
            // Vérifier si le nom d'utilisateur existe déjà
            $stmt = $this->db->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
            $stmt->execute([$username]);
            if ($stmt->fetch()) {
                throw new \Exception("Ce nom d'utilisateur est déjà utilisé");
            }
            
            // Hasher le mot de passe avec un coût approprié
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT, ['cost' => 12]);
            
            // Insérer le nouvel utilisateur
            $stmt = $this->db->prepare("
                INSERT INTO users (username, email, password, created_at) 
                VALUES (?, ?, ?, NOW())
            ");
            
            $success = $stmt->execute([$username, $email, $hashedPassword]);
            
            if (!$success) {
                throw new \Exception("Erreur lors de l'insertion de l'utilisateur");
            }
            
            // Valider la transaction
            $this->db->commit();
            return true;
            
        } catch (\Exception $e) {
            // Annuler la transaction en cas d'erreur
            $this->db->rollBack();
            error_log("Erreur d'inscription: " . $e->getMessage());
            throw $e;
        }
    }

    public function login($email, $password) {
        try {
            error_log("Tentative de connexion pour l'email: $email");
            
            $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            error_log("Résultat de recherche d'utilisateur: " . ($user ? "Utilisateur trouvé (ID: {$user['id']})" : "Aucun utilisateur trouvé"));
    
            if ($user) {
                $passwordMatch = password_verify($password, $user['password']);
                error_log("Vérification du mot de passe: " . ($passwordMatch ? "Réussi" : "Échec"));
                
                if ($passwordMatch) {
                    unset($user['password']);  // Ne pas stocker le mot de passe dans la session
                    
                    $role = isset($user['role']) ? $user['role'] : 'non défini';
                    error_log("Connexion réussie pour l'utilisateur ID: {$user['id']}, Rôle: {$role}");
                    return $user;
                }
            }
            
            error_log("Échec de connexion - Utilisateur non trouvé ou mot de passe incorrect");
            return false;
        } catch(PDOException $e) {
            error_log("Exception PDO lors de la connexion: " . $e->getMessage());
            return false;
        }
    }

    public function updateProfile($userId, $username, $email, $profilePicture = null) {
        try {
            if ($profilePicture) {
                $sql = "UPDATE users SET username = ?, email = ?, profile_picture = ? WHERE id = ?";
                $stmt = $this->db->prepare($sql);
                $success = $stmt->execute([$username, $email, $profilePicture, $userId]);
                
                if (!$success) {
                    error_log("Erreur SQL lors de la mise à jour du profil utilisateur $userId: " . json_encode($stmt->errorInfo()));
                }
                
                return $success;
            } else {
                $sql = "UPDATE users SET username = ?, email = ? WHERE id = ?";
                $stmt = $this->db->prepare($sql);
                $success = $stmt->execute([$username, $email, $userId]);
                
                if (!$success) {
                    error_log("Erreur SQL lors de la mise à jour du profil utilisateur $userId: " . json_encode($stmt->errorInfo()));
                }
                
                return $success;
            }
        } catch(PDOException $e) {
            error_log("Exception PDO lors de la mise à jour du profil utilisateur $userId: " . $e->getMessage());
            throw $e; // Propager l'exception pour qu'elle soit capturée par le contrôleur
        }
    }

    public function getUserById($userId) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                unset($user['password']);
                
                // Vérifier si l'image de profil existe sans la remplacer
                if (isset($user['profile_picture']) && !empty($user['profile_picture'])) {
                    // On ajoute une propriété pour indiquer si l'image existe physiquement
                    $user['image_exists'] = file_exists($user['profile_picture']);
                    
                    if (!$user['image_exists']) {
                        error_log("Image de profil non trouvée: " . $user['profile_picture'] . " pour l'utilisateur ID: " . $userId);
                    }
                } else {
                    $user['image_exists'] = false;
                    $user['profile_picture'] = 'assets/img/default-profile.png';
                }
            }
            
            return $user;
        } catch(PDOException $e) {
            error_log("Erreur lors de la récupération de l'utilisateur ID " . $userId . ": " . $e->getMessage());
            return false;
        }
    }
    
    public function verifyPassword($userId, $password) {
        try {
            $stmt = $this->db->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['password'])) {
                return true;
            }
            return false;
        } catch(PDOException $e) {
            error_log("Exception PDO lors de la vérification du mot de passe pour l'utilisateur $userId: " . $e->getMessage());
            return false;
        }
    }
    
    public function updateUser($userId, $userData) {
        try {
            // Vérifier d'abord si l'utilisateur existe
            $checkStmt = $this->db->prepare("SELECT id FROM users WHERE id = ?");
            $checkStmt->execute([$userId]);
            
            if (!$checkStmt->fetch()) {
                error_log("Tentative de mise à jour d'un utilisateur inexistant (ID: $userId)");
                return false;
            }
            
            // Préparer les champs et valeurs pour la mise à jour
            $fields = [];
            $values = [];
            
            foreach ($userData as $field => $value) {
                $fields[] = "$field = ?";
                $values[] = $value;
                error_log("Champ de mise à jour: $field, Valeur: " . (is_string($value) ? $value : json_encode($value)));
            }
            
            // Ajouter l'ID utilisateur à la fin du tableau de valeurs pour la clause WHERE
            $values[] = $userId;
            
            // Construire la requête SQL
            $sql = "UPDATE users SET " . implode(", ", $fields) . " WHERE id = ?";
            error_log("Requête SQL: $sql");
            
            // Exécuter la requête préparée
            $stmt = $this->db->prepare($sql);
            $success = $stmt->execute($values);
            
            if (!$success) {
                error_log("Échec de la mise à jour SQL. Erreur: " . json_encode($stmt->errorInfo()));
                return false;
            }
            
            // Vérifier si des lignes ont été affectées
            if ($stmt->rowCount() === 0) {
                error_log("Mise à jour réussie mais aucune ligne modifiée (aucun changement ou ID inexistant)");
                // On retourne quand même true même si aucune ligne n'a été modifiée
                // car techniquement la requête a réussi
                return true;
            }
            
            error_log("Mise à jour de l'utilisateur $userId réussie. Lignes affectées: " . $stmt->rowCount());
            return true;
        } catch(PDOException $e) {
            error_log("Exception PDO lors de la mise à jour de l'utilisateur $userId: " . $e->getMessage());
            return false;
        }
    }

    // --- Nouvelles méthodes pour le superadmin ---

    /**
     * Récupère tous les utilisateurs pour l'administration
     */
    public function getAllUsers() {
        try {
            $stmt = $this->db->prepare("SELECT id, username, email, profile_picture, role, created_at FROM users ORDER BY id DESC");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Exception lors de la récupération des utilisateurs: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Recherche des utilisateurs par nom ou email
     */
    public function searchUsers($searchTerm) {
        try {
            $searchTerm = "%$searchTerm%";
            $stmt = $this->db->prepare("SELECT id, username, email, profile_picture, role FROM users 
                                    WHERE username LIKE ? OR email LIKE ? 
                                    ORDER BY id DESC");
            $stmt->execute([$searchTerm, $searchTerm]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Exception lors de la recherche d'utilisateurs: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Met à jour le rôle d'un utilisateur
     */
    public function updateUserRole($userId, $role) {
        try {
            $stmt = $this->db->prepare("UPDATE users SET role = ? WHERE id = ?");
            return $stmt->execute([$role, $userId]);
        } catch(PDOException $e) {
            error_log("Exception lors de la mise à jour du rôle: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Vérifie si un utilisateur est superadmin
     */
    public function isSuperAdmin($userId) {
        try {
            error_log("Vérification du rôle superadmin pour l'utilisateur ID: $userId");
            
            if (!$userId) {
                error_log("ID utilisateur invalide pour la vérification superadmin");
                return false;
            }

            $stmt = $this->db->prepare("SELECT role FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $isSuperAdmin = $user && isset($user['role']) && $user['role'] === 'superadmin';
            error_log("Résultat vérification superadmin pour utilisateur $userId: " . ($isSuperAdmin ? 'OUI' : 'NON'));
            
            return $isSuperAdmin;
        } catch(PDOException $e) {
            error_log("Exception lors de la vérification du rôle superadmin: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Met à jour le profil d'un utilisateur par un admin
     */
    public function updateUserByAdmin($userId, $username, $email, $role, $profilePicture = null) {
        try {
            $fields = ["username = ?", "email = ?", "role = ?"];
            $values = [$username, $email, $role];
            
            if ($profilePicture) {
                $fields[] = "profile_picture = ?";
                $values[] = $profilePicture;
            }
            
            // Ajouter l'ID à la fin pour la clause WHERE
            $values[] = $userId;
            
            $sql = "UPDATE users SET " . implode(", ", $fields) . " WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            
            return $stmt->execute($values);
        } catch(PDOException $e) {
            error_log("Exception lors de la mise à jour d'un utilisateur par l'admin: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Supprime un utilisateur
     */
    public function deleteUser($userId) {
        try {
            $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
            return $stmt->execute([$userId]);
        } catch(PDOException $e) {
            error_log("Exception lors de la suppression d'un utilisateur: " . $e->getMessage());
            return false;
        }
    }
}
