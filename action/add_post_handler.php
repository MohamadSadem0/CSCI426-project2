<?php
session_start();
require_once 'db_connection.php';
require_once 'handle_upload.php';
require_once 'encryption.php';

$encryption = new Encryption();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $description = trim($_POST['description'] ?? '');
    $errors = [];

    if (!isset($_FILES['post_image']) || $_FILES['post_image']['error'] !== UPLOAD_ERR_OK) {
        $errors[] = "Please select a valid image file.";
    } else {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($_FILES['post_image']['type'], $allowed_types)) {
            $errors[] = "Only JPG, PNG, and GIF files are allowed.";
        }
    }

    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        header("Location: ../addPost.php");
        exit();
    }

    $filename = handleFileUpload($_FILES['post_image'], 'assets/postsUploads');
    if (!$filename) {
        $_SESSION['errors'] = ["Failed to upload image."];
        header("Location: ../addPost.php");
        exit();
    }

    try {
        $uniquePostId = uniqid(true);
        $encryptedPostId = $encryption->encryptId($uniquePostId);

        if (!$encryptedPostId) {
            throw new Exception("Error generating encrypted post ID");
        }

        $stmt = $pdo->prepare("INSERT INTO posts (id, user_id, image, description) VALUES (?, ?, ?, ?)");
        $stmt->execute([$encryptedPostId, $_SESSION['user_id'], $filename, $description]);

        $_SESSION['success_message'] = "Post created successfully!";
        header("Location: ../index.php");
        exit();
    } catch (Exception $e) {
        if (file_exists("../postsUploads/$filename")) {
            unlink("../postsUploads/$filename");
        }
        error_log("Post creation error: " . $e->getMessage());
        $_SESSION['errors'] = ["An error occurred. Please try again later."];
        header("Location: ../addPost.php");
        exit();
    }
}
?>