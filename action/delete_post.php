<?php
session_start();
require_once 'db_connection.php';

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

    try {
        $stmt = $pdo->prepare("SELECT image FROM posts WHERE id = ? AND user_id = ?");
        $stmt->execute([$post_id, $user_id]);
        $post = $stmt->fetch();

        if (!$post) {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized']);
            exit();
        }

        $image_path = "../assets/postsUploads/" . $post['image'];
        if (file_exists($image_path)) {
            unlink($image_path);
        }

        $stmt = $pdo->prepare("DELETE FROM posts WHERE id = ? AND user_id = ?");
        $stmt->execute([$post_id, $user_id]);

        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        error_log("Delete post error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Server error']);
    }
}
?>