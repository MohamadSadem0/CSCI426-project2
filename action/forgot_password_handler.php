<?php
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.use_only_cookies', 1);
session_start();

require_once 'db_connection.php';
require_once 'encryption.php';
require_once 'decryption.php';

$encryption = new Encryption();
$decryption = new Decryption();

function isPasswordStrong($password) {
    if (strlen($password) < 8) return false;
    if (!preg_match('/[A-Z]/', $password)) return false;
    if (!preg_match('/[a-z]/', $password)) return false;
    if (!preg_match('/[0-9]/', $password)) return false;
    if (!preg_match('/[!@#$%^&*()\-_=+{};:,<.>]/', $password)) return false;
    return true;
}

function getMostImportantError($errors) {
    $errorPriority = [
        'email_format' => 1,
        'email_not_found' => 2,
        'password_match' => 3,
        'password_strength' => 4,
        'database' => 5
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
        header("Location: ../forgot_password.php");
        exit();
    }
    $_SESSION['last_attempt'] = time();

    $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $errors = [];
    
    if (empty($email) || empty($new_password) || empty($confirm_password)) {
        $_SESSION['errors'] = ["All fields are required."];
        header("Location: ../forgot_password.php");
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email_format'] = "Invalid email format";
    }

    if ($new_password !== $confirm_password) {
        $errors['password_match'] = "Passwords do not match";
    }

    if (!isPasswordStrong($new_password)) {
        $errors['password_strength'] = "Password must be at least 8 characters long with uppercase, lowercase, numbers, and special characters";
    }

    try {
        $stmt = $pdo->prepare("SELECT * FROM users");
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $found_user = null;

        foreach ($users as $user) {
            $decrypted_email = $decryption->decrypt($user['email']);
            if ($decrypted_email === $email) {
                $found_user = $user;
                break;
            }
        }

        if (!$found_user) {
            $errors['email_not_found'] = "Email not found";
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = [getMostImportantError($errors)];
            header("Location: ../forgot_password.php");
            exit();
        }

        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$hashed_password, $found_user['id']]);

        $_SESSION['success'] = "Password has been reset successfully.";
        header("Location: ../login.php");
        exit();

    } catch (Exception $e) {
        error_log("Password reset error: " . $e->getMessage());
        $errors['database'] = "An error occurred. Please try again later.";
        $_SESSION['errors'] = [getMostImportantError($errors)];
        header("Location: ../forgot_password.php");
        exit();
    }
}
?>