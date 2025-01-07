<?php
session_start();
require_once 'db_connection.php';
require_once 'encryption.php';
require_once 'decryption.php';

$encryption = new Encryption();
$decryption = new Decryption();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $encrypted_post_id = $data['post_id'] ?? null;
    $content = trim($data['content'] ?? '');
    $parent_id = $data['parent_id'] ?? null;
    $user_id = $_SESSION['user_id'];

    if (!$encrypted_post_id || empty($content)) {
        http_response_code(400);
        echo json_encode(['error' => 'Post ID and content are required']);
        exit();
    }

    try {
        $post_id = $decryption->decryptId($encrypted_post_id);
        error_log("Decrypted post ID for comment: " . $post_id);

        $uniqueCommentId = uniqid(true);
        $encryptedCommentId = $encryption->encryptId($uniqueCommentId);

        $stmt = $pdo->prepare("
            INSERT INTO comments (id, post_id, user_id, content, parent_id) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$encryptedCommentId, $encrypted_post_id, $user_id, $content, $parent_id]);

        $stmt = $pdo->prepare("
            SELECT firstname, lastname, profile_picture 
            FROM users 
            WHERE id = ?
        ");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM comments WHERE post_id = ?");
        $stmt->execute([$encrypted_post_id]); 
        $comment_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

        echo json_encode([
            'success' => true,
            'commentCount' => $comment_count,
            'comments' => [[
                'id' => $encryptedCommentId,
                'content' => $content,
                'created_at' => date('Y-m-d H:i:s'),
                'parent_id' => $parent_id,
                'firstname' => $decryption->decrypt($user['firstname']),
                'lastname' => $decryption->decrypt($user['lastname']),
                'profile_picture' => $user['profile_picture']
            ]]
        ]);

    } catch (Exception $e) {
        error_log("Comment error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Server error']);
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $post_id = $_GET['post_id'] ?? null;

    if (!$post_id) {
        http_response_code(400);
        echo json_encode(['error' => 'Post ID required']);
        exit();
    }

    try {
        error_log("GET request received for comments");
        error_log("Encrypted Post ID: " . $post_id);
        
        $decrypted_post_id = $decryption->decryptId($post_id);
        error_log("Decrypted Post ID: " . $decrypted_post_id);
        
        $checkStmt = $pdo->prepare("SELECT id FROM posts WHERE id = ?");
        $checkStmt->execute([$decrypted_post_id]);
        $postExists = $checkStmt->fetch();
        error_log("Post exists: " . ($postExists ? 'Yes' : 'No'));

        $stmt = $pdo->prepare("
            SELECT 
                c.id, c.content, c.created_at, c.parent_id,
                u.firstname, u.lastname, u.profile_picture
            FROM comments c
            JOIN users u ON c.user_id = u.id
            WHERE c.post_id = ?
            ORDER BY c.created_at DESC
        ");
        
        $stmt->execute([$post_id]);
        $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        error_log("Number of comments found: " . count($comments));

        foreach ($comments as &$comment) {
            $comment['firstname'] = $decryption->decrypt($comment['firstname']);
            $comment['lastname'] = $decryption->decrypt($comment['lastname']);
        }

        echo json_encode([
            'success' => true,
            'comments' => $comments
        ]);

    } catch (Exception $e) {
        error_log("Error fetching comments: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Server error']);
        exit();
    }
}
?>