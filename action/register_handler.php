<?php
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.use_only_cookies', 1);
session_start();

require_once 'db_connection.php';
require_once 'handle_upload.php';
require_once 'encryption.php';

$encryption = new Encryption();

function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

function isPasswordStrong($password) {
    if (strlen($password) < 2) return false;
    if (!preg_match('/[A-Z]/', $password)) return false;
    if (!preg_match('/[a-z]/', $password)) return false;
    if (!preg_match('/[0-9]/', $password)) return false;
    if (!preg_match('/[!@#$%^&*()\-_=+{};:,<.>]/', $password)) return false;
    return true;
}

function getMostImportantError($errors) {
    $errorPriority = [
        'name_length' => 1,
        'email_exists' => 2,
        'password_match' => 3,
        'password_strength' => 4,
        'email_format' => 5,
        'rate_limit' => 6
    ];

    $prioritizedErrors = [];
    
    foreach ($errors as $type => $message) {
        $priority = $errorPriority[$type] ?? 999;
        $prioritizedErrors[$priority] = $message;
    }
    
    ksort($prioritizedErrors);
    return reset($prioritizedErrors);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_SESSION['last_attempt']) && time() - $_SESSION['last_attempt'] < 2) {
        $_SESSION['errors'] = ["Please wait a moment before trying again."];
        header("Location: ../register.php");
        exit();
    }
    $_SESSION['last_attempt'] = time();

    $firstname = sanitizeInput($_POST['firstname'] ?? '');
    $lastname = sanitizeInput($_POST['lastname'] ?? '');
    $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $errors = [];
    
    if (strlen($firstname) < 2) {
        $errors['name_length'] = "First name must be at least 2 characters long";
    }
    if (strlen($lastname) < 2) {
        $errors['name_length'] = "Last name must be at least 2 characters long";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email_format'] = "Invalid email format";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetchColumn() > 0) {
                $errors['email_exists'] = "Email already exists";
            }
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            $errors['database'] = "An error occurred. Please try again later.";
        }
    }

    if ($password !== $confirm_password) {
        $errors['password_match'] = "Passwords do not match";
    }
    if (!isPasswordStrong($password)) {
        $errors['password_strength'] = "Password must be at least 8 characters long with uppercase, lowercase, numbers, and special characters";
    }

    if (!empty($errors)) {
        $_SESSION['errors'] = [getMostImportantError($errors)];
        header("Location: ../register.php");
        exit();
    }
    $filename = handleFileUpload($_FILES['profile_picture'], 'assets/profileUploads');

    try {
        $uniqueId = uniqid(true);
        
        $encryptedId = $encryption->encryptId($uniqueId);
        if (!$encryptedId) {
            throw new Exception("Error generating encrypted ID");
        }

        $encryptedFirstname = $encryption->encrypt($firstname);
        $encryptedLastname = $encryption->encrypt($lastname);
        $encryptedEmail = $encryption->encrypt($email);

        $pdo->beginTransaction();

        $stmt = $pdo->prepare("
            INSERT INTO users (id, firstname, lastname, email, password, profile_picture) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        $hashed_password = password_hash($password, PASSWORD_ARGON2ID, [
            'memory_cost' => 65536,
            'time_cost' => 4,
            'threads' => 3
        ]);
        
        $stmt->execute([
            $encryptedId, 
            $encryptedFirstname, 
            $encryptedLastname, 
            $encryptedEmail, 
            $hashed_password, 
            $filename
        ]);

        $pdo->commit();
        
        // Clear sensitive data
        $password = null;
        $confirm_password = null;
        $hashed_password = null;

        $_SESSION['success_message'] = "Registration successful! Please login.";
        header("Location: ../login.php");
        exit();
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("Registration error: " . $e->getMessage());
        $_SESSION['errors'] = ["Registration failed. Please try again later."];
        header("Location: ../register.php");
        exit();
    }
}
?>