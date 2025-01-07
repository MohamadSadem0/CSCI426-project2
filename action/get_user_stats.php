<?php
session_start();
require_once 'db_connection.php';
require_once 'decryption.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

if (!isset($_GET['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User ID not provided']);
    exit;
}

try {
    $decryption = new Decryption();
    $user_id = $_GET['user_id'];
    
    if ($user_id === $_SESSION['user_id']) {
        $id_to_use = $user_id;
    } else {
        $id_to_use = $user_id;
    }

    error_log("Checking stats for user_id: " . $id_to_use);

    $stmt = $pdo->prepare("
        SELECT COUNT(*) as followers 
        FROM follows 
        WHERE followed_id = ?
    ");
    $stmt->execute([$id_to_use]);
    $followers = $stmt->fetch(PDO::FETCH_ASSOC)['followers'];

    $stmt = $pdo->prepare("
        SELECT COUNT(*) as following 
        FROM follows 
        WHERE follower_id = ?
    ");
    $stmt->execute([$id_to_use]);
    $following = $stmt->fetch(PDO::FETCH_ASSOC)['following'];

    error_log("Found followers: " . $followers . ", following: " . $following);

    echo json_encode([
        'success' => true,
        'followers' => $followers,
        'following' => $following
    ]);
} catch (PDOException $e) {
    error_log("Error in get_user_stats: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error']);
} 