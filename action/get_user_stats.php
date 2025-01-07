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
    
    // If the user_id matches the session user_id, use it directly (for profile page)
    if ($user_id === $_SESSION['user_id']) {
        $id_to_use = $user_id;
    } else {
        // For other users, use the encrypted ID as is
        $id_to_use = $user_id;
    }

    // Debug log
    error_log("Checking stats for user_id: " . $id_to_use);

    // Get followers count
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as followers 
        FROM follows 
        WHERE followed_id = ?
    ");
    $stmt->execute([$id_to_use]);
    $followers = $stmt->fetch(PDO::FETCH_ASSOC)['followers'];

    // Get following count
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as following 
        FROM follows 
        WHERE follower_id = ?
    ");
    $stmt->execute([$id_to_use]);
    $following = $stmt->fetch(PDO::FETCH_ASSOC)['following'];

    // Debug log
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