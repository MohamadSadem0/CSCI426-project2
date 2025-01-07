<?php
session_start();
require_once 'db_connection.php';
require_once 'encryption.php';

$encryption = new Encryption();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $userId = $input['user_id'];
    $currentUserId = $_SESSION['user_id'];

    try {
        // Check if already following
        $stmt = $pdo->prepare("SELECT * FROM follows WHERE follower_id = ? AND followed_id = ?");
        $stmt->execute([$currentUserId, $userId]);
        $isFollowing = $stmt->fetch();

        if ($isFollowing) {
            // Unfollow
            $stmt = $pdo->prepare("DELETE FROM follows WHERE follower_id = ? AND followed_id = ?");
            $stmt->execute([$currentUserId, $userId]);
            $following = false;
        } else {
            // Follow: Generate encrypted follow ID
            $uniqueFollowId = uniqid(true);
            $encryptedFollowId = $encryption->encryptId($uniqueFollowId);

            if (!$encryptedFollowId) {
                throw new Exception("Error generating encrypted follow ID");
            }

            $stmt = $pdo->prepare("INSERT INTO follows (id, follower_id, followed_id) VALUES (?, ?, ?)");
            $stmt->execute([$encryptedFollowId, $currentUserId, $userId]);
            $following = true;
        }

        echo json_encode(['success' => true, 'following' => $following]);
    } catch (Exception $e) {
        error_log("Follow error: " . $e->getMessage());
        echo json_encode(['success' => false]);
    }
}
?>