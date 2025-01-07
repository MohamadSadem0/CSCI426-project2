<?php
session_start();
require_once 'db_connection.php';
require_once 'handle_upload.php';
require_once 'encryption.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $encryption = new Encryption();
    $firstname = trim($_POST['firstname']);
    $lastname = trim($_POST['lastname']);
    $userId = $_SESSION['user_id'];
    $errors = [];

    $filename = $_SESSION['profile_picture'];
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        if ($filename && $filename !== '1.jpg' && file_exists("../assets/profileUploads/" . $filename)) {
            unlink("../assets/profileUploads/" . $filename);
        }
        
        $filename = handleFileUpload($_FILES['profile_picture'], 'assets/profileUploads');
        if (!$filename) {
            $errors[] = "Failed to upload new profile picture.";
        }
    }

    if (empty($errors)) {
        try {
            $encryptedFirstname = $encryption->encrypt($firstname);
            $encryptedLastname = $encryption->encrypt($lastname);

            if (!$encryptedFirstname || !$encryptedLastname) {
                throw new Exception("Encryption failed");
            }

            $stmt = $pdo->prepare("UPDATE users SET firstname = ?, lastname = ?, profile_picture = ? WHERE id = ?");
            $stmt->execute([$encryptedFirstname, $encryptedLastname, $filename, $userId]);

            $_SESSION['profile_picture'] = $filename;

            header("Location: ../profile.php");
            exit();
        } catch (Exception $e) {
            error_log("Profile update error: " . $e->getMessage());
            $errors[] = "An error occurred. Please try again later.";
        }
    }

    $_SESSION['errors'] = $errors;
    header("Location: ../profile.php");
    exit();
}
?>