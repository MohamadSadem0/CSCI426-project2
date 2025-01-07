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

function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_SESSION['last_attempt']) && time() - $_SESSION['last_attempt'] < 2) {
        $_SESSION['errors'] = ["Please wait a moment before trying again."];
        header("Location: ../login.php");
        exit();
    }
    $_SESSION['last_attempt'] = time();

    $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $_SESSION['errors'] = ["All fields are required."];
        header("Location: ../login.php");
        exit();
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

        if ($found_user && password_verify($password, $found_user['password'])) {
            $_SESSION['user_id'] = $found_user['id'];
            $_SESSION['firstname'] = $decryption->decrypt($found_user['firstname']);
            $_SESSION['lastname'] = $decryption->decrypt($found_user['lastname']);
            $_SESSION['email'] = $decryption->decrypt($found_user['email']);
            $_SESSION['profile_picture'] = $found_user['profile_picture'];
            
            $password = null;
            
            session_write_close();
            
            header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
            header("Cache-Control: post-check=0, pre-check=0", false);
            header("Pragma: no-cache");
            header("Location: ../index.php");
            exit();
        } else {
            $_SESSION['errors'] = ["Invalid email or password."];
            header("Location: ../login.php");
            exit();
        }
    } catch (Exception $e) {
        error_log("Login error: " . $e->getMessage());
        $_SESSION['errors'] = ["An error occurred. Please try again later."];
        header("Location: ../login.php");
        exit();
    }
}
?>