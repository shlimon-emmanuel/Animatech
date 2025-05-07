<?php
// Définir les chemins
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', __DIR__);

// Démarrer la session
session_start();

// Inclure la configuration
require_once 'app/config/config.php';
require_once 'app/Models/MovieModel.php';

echo "<h1>Test d'ajout de réponse</h1>";

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user']) || !isset($_SESSION['user']['id'])) {
    echo "<p style='color:red;'>Vous devez être connecté pour effectuer ce test.</p>";
    echo "<p><a href='index.php?action=login'>Se connecter</a></p>";
    exit;
}

// Obtenir le premier commentaire disponible
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    // Vérifier les commentaires existants
    $stmt = $pdo->query("SELECT c.*, m.title as movie_title 
                         FROM comments c 
                         JOIN movies m ON c.movie_id = m.id 
                         ORDER BY c.created_at DESC 
                         LIMIT 1");
    $comment = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$comment) {
        echo "<p style='color:red;'>Aucun commentaire trouvé dans la base de données.</p>";
        exit;
    }
    
    echo "<div style='background-color:#333; padding:15px; margin:15px 0; border-radius:5px;'>";
    echo "<h3>Commentaire sélectionné pour le test</h3>";
    echo "<p><strong>ID:</strong> " . $comment['id'] . "</p>";
    echo "<p><strong>Film:</strong> " . htmlspecialchars($comment['movie_title']) . "</p>";
    echo "<p><strong>Contenu:</strong> " . htmlspecialchars($comment['content']) . "</p>";
    echo "</div>";
    
    // Formulaire de test
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply_content'])) {
        $content = trim($_POST['reply_content']);
        $commentId = (int)$_POST['comment_id'];
        $userId = $_SESSION['user']['id'];
        $movieId = $comment['movie_id'];
        
        try {
            $model = new App\Models\MovieModel(OMDB_API_KEY);
            $result = $model->addCommentReply($commentId, $userId, $content);
            
            if ($result) {
                echo "<p style='color:green;'>Réponse ajoutée avec succès!</p>";
                
                // Afficher les réponses actuelles
                $replies = $model->getCommentReplies($commentId);
                
                echo "<h3>Réponses actuelles:</h3>";
                echo "<ul>";
                foreach ($replies as $reply) {
                    echo "<li>" . htmlspecialchars($reply['content']) . " (par " . htmlspecialchars($reply['username']) . ")</li>";
                }
                echo "</ul>";
                
            } else {
                echo "<p style='color:red;'>Erreur lors de l'ajout de la réponse.</p>";
            }
        } catch (Exception $e) {
            echo "<p style='color:red;'>Exception: " . $e->getMessage() . "</p>";
        }
    }
    
    ?>
    <form method="POST" style="margin-top:20px;">
        <input type="hidden" name="comment_id" value="<?= $comment['id'] ?>">
        <div style="margin-bottom:15px;">
            <label for="reply_content" style="display:block; margin-bottom:5px;">Votre réponse:</label>
            <textarea name="reply_content" id="reply_content" style="width:100%; height:100px; padding:8px;" required></textarea>
        </div>
        <button type="submit" style="background:#4CAF50; color:white; border:none; padding:10px 15px; cursor:pointer;">Ajouter la réponse</button>
    </form>
    
    <div style="margin-top:20px;">
        <a href="index.php?action=view&id=<?= $comment['movie_id'] ?>" style="color:#2196F3; text-decoration:none;">Retour à la page du film</a>
    </div>
    <?php
    
} catch (PDOException $e) {
    echo "<p style='color:red;'>Erreur de base de données: " . $e->getMessage() . "</p>";
}
?> 