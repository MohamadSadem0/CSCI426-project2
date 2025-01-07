<?php
require_once 'db_connection.php';
require_once 'decryption.php';

function fetchPosts($pdo, $user_id) {
    $decryption = new Decryption();
    
    try {
        $stmt = $pdo->prepare("
            SELECT DISTINCT 
                p.id,
                p.user_id,
                p.image,
                p.description,
                p.created_at,
                u.firstname,
                u.lastname,
                u.profile_picture,
                CASE WHEN f.follower_id IS NOT NULL THEN 1 ELSE 0 END as is_following
            FROM posts p
            JOIN users u ON p.user_id = u.id 
            LEFT JOIN follows f ON f.followed_id = u.id AND f.follower_id = ?
            ORDER BY p.created_at DESC
        ");
        $stmt->execute([$user_id]);
        $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        error_log("Raw posts data:");
        foreach ($posts as $post) {
            error_log(sprintf(
                "ID: %s, Description: %s",
                $post['id'],
                $post['description']
            ));
        }

        foreach ($posts as &$post) {
            $post['firstname'] = $decryption->decrypt($post['firstname']);
            $post['lastname'] = $decryption->decrypt($post['lastname']);
        }

        $likesStmt = $pdo->prepare("
            SELECT post_id, COUNT(*) as count 
            FROM likes 
            GROUP BY post_id
        ");
        $likesStmt->execute();
        $likeCounts = $likesStmt->fetchAll(PDO::FETCH_KEY_PAIR);

        $userLikesStmt = $pdo->prepare("
            SELECT post_id 
            FROM likes 
            WHERE user_id = ?
        ");
        $userLikesStmt->execute([$user_id]);
        $userLikes = $userLikesStmt->fetchAll(PDO::FETCH_COLUMN);

        return [
            'posts' => $posts,
            'likeCounts' => $likeCounts,
            'userLikes' => $userLikes
        ];
    } catch (Exception $e) {
        error_log("Error fetching posts: " . $e->getMessage());
        return [
            'posts' => [],
            'likeCounts' => [],
            'userLikes' => []
        ];
    }
}
?>