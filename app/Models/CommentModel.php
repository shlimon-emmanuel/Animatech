<?php

namespace App\Models;

use PDO;
use PDOException;

class CommentModel {
    private $db;

    public function __construct() {
        try {
            $this->db = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8",
                DB_USER,
                DB_PASS,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        } catch(PDOException $e) {
            die("Erreur de connexion : " . $e->getMessage());
        }
    }

    /**
     * Ajouter un commentaire
     */
    public function addComment($userId, $movieId, $content) {
        try {
            $stmt = $this->db->prepare("INSERT INTO comments (user_id, movie_id, content) VALUES (?, ?, ?)");
            return $stmt->execute([$userId, $movieId, $content]);
        } catch(PDOException $e) {
            error_log("Exception PDO lors de l'ajout d'un commentaire: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupérer les commentaires d'un film
     */
    public function getMovieComments($movieId) {
        try {
            $stmt = $this->db->prepare("
                SELECT c.*, u.username, u.profile_picture 
                FROM comments c 
                JOIN users u ON c.user_id = u.id 
                WHERE c.movie_id = ? 
                ORDER BY c.created_at DESC
            ");
            $stmt->execute([$movieId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Exception PDO lors de la récupération des commentaires: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Récupérer les commentaires d'un utilisateur
     */
    public function getUserComments($userId, $limit = null) {
        try {
            error_log("CommentModel::getUserComments - Début de la méthode pour userId: " . $userId);
            $sql = "
                SELECT c.* 
                FROM comments c 
                WHERE c.user_id = ? 
                ORDER BY c.created_at DESC
            ";
            
            if ($limit !== null) {
                $sql .= " LIMIT " . intval($limit);
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId]);
            $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Enrichir les commentaires avec les titres de films
            foreach ($comments as &$comment) {
                try {
                    // Récupérer le titre du film
                    $stmtMovie = $this->db->prepare("SELECT title FROM movies WHERE id = ?");
                    $stmtMovie->execute([$comment['movie_id']]);
                    $movie = $stmtMovie->fetch(PDO::FETCH_ASSOC);
                    
                    if ($movie) {
                        $comment['movie_title'] = $movie['title'];
                    } else {
                        $comment['movie_title'] = 'Film #' . $comment['movie_id'];
                    }
                } catch(PDOException $e) {
                    // On ignore les erreurs et on continue
                    $comment['movie_title'] = 'Film #' . $comment['movie_id'];
                }
            }
            
            error_log("CommentModel::getUserComments - Nombre de commentaires trouvés: " . count($comments));
            return $comments;
        } catch(PDOException $e) {
            error_log("Exception PDO lors de la récupération des commentaires utilisateur: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Récupérer les réponses d'un utilisateur
     */
    public function getUserReplies($userId, $limit = null) {
        try {
            $sql = "
                SELECT r.*, c.content as original_comment, c.movie_id
                FROM comment_replies r 
                JOIN comments c ON r.comment_id = c.id 
                WHERE r.user_id = ? 
                ORDER BY r.created_at DESC
            ";
            
            if ($limit !== null) {
                $sql .= " LIMIT " . intval($limit);
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId]);
            $replies = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // On essaie d'enrichir les données avec le titre du film et l'auteur du commentaire,
            // mais on ne bloque pas si ces tables n'existent pas
            foreach ($replies as &$reply) {
                try {
                    // Récupérer le titre du film
                    $stmtMovie = $this->db->prepare("SELECT title FROM movies WHERE id = ?");
                    $stmtMovie->execute([$reply['movie_id']]);
                    $movie = $stmtMovie->fetch(PDO::FETCH_ASSOC);
                    
                    if ($movie) {
                        $reply['movie_title'] = $movie['title'];
                    } else {
                        $reply['movie_title'] = 'Film #' . $reply['movie_id'];
                    }
                    
                    // Récupérer l'auteur du commentaire
                    $stmtUser = $this->db->prepare("SELECT username FROM users WHERE id = (SELECT user_id FROM comments WHERE id = ?)");
                    $stmtUser->execute([$reply['comment_id']]);
                    $user = $stmtUser->fetch(PDO::FETCH_ASSOC);
                    
                    if ($user) {
                        $reply['comment_author'] = $user['username'];
                    } else {
                        $reply['comment_author'] = 'Utilisateur inconnu';
                    }
                } catch(PDOException $e) {
                    // On ignore les erreurs et on continue
                    $reply['movie_title'] = 'Film #' . $reply['movie_id'];
                    $reply['comment_author'] = 'Utilisateur inconnu';
                }
            }
            
            return $replies;
        } catch(PDOException $e) {
            error_log("Exception PDO lors de la récupération des réponses utilisateur: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Compter le nombre de commentaires d'un utilisateur
     */
    public function getUserCommentCount($userId) {
        try {
            error_log("CommentModel::getUserCommentCount - Début de la méthode pour userId: " . $userId);
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM comments WHERE user_id = ?");
            $stmt->execute([$userId]);
            $count = $stmt->fetchColumn();
            error_log("CommentModel::getUserCommentCount - Nombre total de commentaires: " . $count);
            return $count;
        } catch(PDOException $e) {
            error_log("Exception PDO lors du comptage des commentaires: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Compter le nombre de réponses d'un utilisateur
     */
    public function getUserReplyCount($userId) {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM comment_replies WHERE user_id = ?");
            $stmt->execute([$userId]);
            return $stmt->fetchColumn();
        } catch(PDOException $e) {
            error_log("Exception PDO lors du comptage des réponses: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Supprimer un commentaire
     */
    public function deleteComment($commentId, $userId) {
        try {
            // Vérifier que l'utilisateur est bien le propriétaire du commentaire
            $stmt = $this->db->prepare("SELECT user_id FROM comments WHERE id = ?");
            $stmt->execute([$commentId]);
            $comment = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$comment || $comment['user_id'] != $userId) {
                return false;
            }
            
            $stmt = $this->db->prepare("DELETE FROM comments WHERE id = ?");
            return $stmt->execute([$commentId]);
        } catch(PDOException $e) {
            error_log("Exception PDO lors de la suppression d'un commentaire: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Ajouter une réponse à un commentaire
     */
    public function addReply($commentId, $userId, $content) {
        try {
            $stmt = $this->db->prepare("INSERT INTO comment_replies (comment_id, user_id, content) VALUES (?, ?, ?)");
            return $stmt->execute([$commentId, $userId, $content]);
        } catch(PDOException $e) {
            error_log("Exception PDO lors de l'ajout d'une réponse: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupérer les réponses d'un commentaire
     */
    public function getCommentReplies($commentId) {
        try {
            $stmt = $this->db->prepare("
                SELECT r.*, u.username, u.profile_picture 
                FROM comment_replies r 
                JOIN users u ON r.user_id = u.id 
                WHERE r.comment_id = ? 
                ORDER BY r.created_at ASC
            ");
            $stmt->execute([$commentId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Exception PDO lors de la récupération des réponses: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Supprimer une réponse
     */
    public function deleteReply($replyId, $userId) {
        try {
            // Vérifier que l'utilisateur est bien le propriétaire de la réponse
            $stmt = $this->db->prepare("SELECT user_id FROM comment_replies WHERE id = ?");
            $stmt->execute([$replyId]);
            $reply = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$reply || $reply['user_id'] != $userId) {
                return false;
            }
            
            $stmt = $this->db->prepare("DELETE FROM comment_replies WHERE id = ?");
            return $stmt->execute([$replyId]);
        } catch(PDOException $e) {
            error_log("Exception PDO lors de la suppression d'une réponse: " . $e->getMessage());
            return false;
        }
    }
} 