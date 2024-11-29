<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = htmlspecialchars($_POST['username']);
    $email = htmlspecialchars($_POST['email']);
    $profile_picture = $_FILES['profile_picture'] ?? null;

    $profilePicturePath = $_SESSION['user']['profile_picture'];

    // Upload de l'image
    if ($profile_picture && $profile_picture['error'] === UPLOAD_ERR_OK) {
        $targetDir = ASSETS_PATH . '/uploads/';
        $targetFile = $targetDir . basename($profile_picture['name']);
        move_uploaded_file($profile_picture['tmp_name'], $targetFile);
        $profilePicturePath = '/assets/uploads/' . basename($profile_picture['name']);
    }

    $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, profile_picture = ? WHERE id = ?");
    $stmt->execute([$username, $email, $profilePicturePath, $_SESSION['user']['id']]);

    $_SESSION['user']['username'] = $username;
    $_SESSION['user']['email'] = $email;
    $_SESSION['user']['profile_picture'] = $profilePicturePath;

    $_SESSION['success'] = "Profil mis à jour avec succès.";
}

include 'profile_form.php';
?>
