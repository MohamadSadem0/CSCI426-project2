<?php
$host = 'localhost';
$port = 3307;
$dbname = 'blog_db';
$user = 'root';
$pass = '123';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $createTableQuery = "
        CREATE TABLE IF NOT EXISTS users (
            id VARCHAR(255) PRIMARY KEY,
            firstname VARCHAR(255) NOT NULL,
            lastname VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            profile_picture VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ";
    $pdo->exec($createTableQuery);
    
    $createTableQuery = "
        CREATE TABLE IF NOT EXISTS posts (
            id VARCHAR(255) PRIMARY KEY,
            user_id VARCHAR(255) NOT NULL,
            image VARCHAR(255) NOT NULL,
            description TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id)
        )
    ";
    $pdo->exec($createTableQuery);
    
    $createLikesTable = "
        CREATE TABLE IF NOT EXISTS likes (
            id VARCHAR(255) PRIMARY KEY,
            post_id VARCHAR(255) NOT NULL,
            user_id VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            UNIQUE KEY unique_like (post_id, user_id)
        )
    ";
    $pdo->exec($createLikesTable);

    $createCommentsTable = "
        CREATE TABLE IF NOT EXISTS comments (
            id VARCHAR(255) PRIMARY KEY,
            post_id VARCHAR(255) NOT NULL,
            user_id VARCHAR(255) NOT NULL,
            content TEXT NOT NULL,
            parent_id VARCHAR(255) DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (parent_id) REFERENCES comments(id) ON DELETE CASCADE
        )
    ";
    $pdo->exec($createCommentsTable);
    
    $pdo->exec("ALTER TABLE posts ADD INDEX IF NOT EXISTS created_at_idx (created_at DESC)");
    $pdo->exec("ALTER TABLE likes ADD INDEX IF NOT EXISTS post_user_idx (post_id, user_id)");
    $pdo->exec("ALTER TABLE comments ADD INDEX IF NOT EXISTS post_created_idx (post_id, created_at DESC)");
    $pdo->exec("ALTER TABLE comments ADD INDEX IF NOT EXISTS parent_idx (parent_id)");
    
    $pdo->exec("ALTER TABLE comments MODIFY post_id VARCHAR(255) NOT NULL");
    
    $createFollowsTable = "
        CREATE TABLE IF NOT EXISTS follows (
            id VARCHAR(255) PRIMARY KEY,
            follower_id VARCHAR(255) NOT NULL,
            followed_id VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (follower_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (followed_id) REFERENCES users(id) ON DELETE CASCADE,
            UNIQUE KEY unique_follow (follower_id, followed_id)
        )
    ";
    $pdo->exec($createFollowsTable);

} catch (PDOException $e) {
    error_log('Database connection failed: ' . $e->getMessage());
    throw $e;
}
?>