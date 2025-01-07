<?php
session_start();
require_once 'db_connection.php';
require_once 'handle_upload.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post_id = $_POST['post_id'] ?? null;
    $description = $_POST['description'] ?? '';
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

        $filename = $post['image'];

        if (isset($_FILES['post_image']) && $_FILES['post_image']['error'] === UPLOAD_ERR_OK) {
            $old_image_path = "../assets/postsUploads/" . $post['image'];
            if (file_exists($old_image_path)) {
                unlink($old_image_path);
            }

            $filename = handleFileUpload($_FILES['post_image'], 'assets/postsUploads');
            if (!$filename) {
                throw new Exception("Failed to upload new image");
            }
        }

        $stmt = $pdo->prepare("UPDATE posts SET description = ?, image = ? WHERE id = ?");
        $stmt->execute([$description, $filename, $post_id]);

        echo json_encode([
            'success' => true,
            'message' => 'Post updated successfully',
            'image' => $filename
        ]);
    } catch (Exception $e) {
        error_log("Update post error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Server error']);
    }
}
?>