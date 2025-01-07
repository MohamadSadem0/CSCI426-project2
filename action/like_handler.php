<?php
session_start();
require_once 'db_connection.php';
require_once 'encryption.php';

$encryption = new Encryption();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $post_id = $data['post_id'] ?? null;
    $user_id = $_SESSION['user_id'];

    if (!$post_id) {
        http_response_code(400);
        echo json_encode(['error' => 'Post ID required']);
        exit();
    }

    try {
        // Check if user already liked the post
        $stmt = $pdo->prepare("SELECT id FROM likes WHERE post_id = ? AND user_id = ?");
        $stmt->execute([$post_id, $user_id]);
        $existing_like = $stmt->fetch();

        if ($existing_like) {
            // Unlike: Remove the like
            $stmt = $pdo->prepare("DELETE FROM likes WHERE post_id = ? AND user_id = ?");
            $stmt->execute([$post_id, $user_id]);
            $action = 'unliked';
        } else {
            // Like: Generate encrypted like ID
            $uniqueLikeId = uniqid(true);
            $encryptedLikeId = $encryption->encryptId($uniqueLikeId);

            if (!$encryptedLikeId) {
                throw new Exception("Error generating encrypted like ID");
            }

            // Add new like with encrypted ID
            $stmt = $pdo->prepare("INSERT INTO likes (id, post_id, user_id) VALUES (?, ?, ?)");
            $stmt->execute([$encryptedLikeId, $post_id, $user_id]);
            $action = 'liked';
        }

        // Get updated like count
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM likes WHERE post_id = ?");
        $stmt->execute([$post_id]);
        $like_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

        echo json_encode([
            'success' => true,
            'action' => $action,
            'likeCount' => $like_count
        ]);

    } catch (Exception $e) {
        error_log("Like error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Server error']);
        exit();
    }
}
?>