<?php
session_start();
require_once 'action/db_connection.php';
require_once 'action/fetch_posts.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$result = fetchPosts($pdo, $_SESSION['user_id']);
$posts = $result['posts'];
$likeCounts = $result['likeCounts'];
$userLikes = $result['userLikes'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Home</title>
  <link rel="stylesheet" href="navBar.css">
  <link rel="stylesheet" href="./css/index.css">
</head>

<body>
  <?php include 'navBar.php'; ?>

  <div class="main__page">
    <div class="posts">
      <?php if (empty($posts)): ?>
      <p class="no-posts">No posts to display.</p>
      <?php else: ?>
      <?php
      $shown_users = array();
      foreach ($posts as $post): ?>
      <div class="post-card">
        <div class="post-header">
          <div class="user-info">
            <img src="assets/profileUploads/<?php echo htmlspecialchars($post['profile_picture']); ?>" alt="User Image"
              onerror="this.src='assets/1.jpg';">
            <div class="user-meta">
              <span class="username <?php echo ($post['user_id'] !== $_SESSION['user_id']) ? 'clickable' : ''; ?>">
                <?php echo htmlspecialchars($post['firstname'] . ' ' . $post['lastname']); ?>
              </span>
            </div>
          </div>
          <?php if ($post['user_id'] !== $_SESSION['user_id'] && !in_array($post['user_id'], $shown_users)): ?>
          <button class="follow-button" data-user-id="<?php echo $post['user_id']; ?>">
            <?php echo $post['is_following'] ? 'Unfollow' : 'Follow'; ?>
          </button>
          <?php $shown_users[] = $post['user_id']; endif; ?>
          <?php if ($post['user_id'] === $_SESSION['user_id']): ?>
          <div class="post-options-container">
            <button class="post-options">
              <ion-icon name="ellipsis-horizontal"></ion-icon>
            </button>
            <div class="options-dropdown">
              <button class="option-item edit-post" data-post-id="<?php echo $post['id']; ?>">
                <ion-icon name="create-outline"></ion-icon>
                Edit Post
              </button>
              <button class="option-item delete-post" data-post-id="<?php echo $post['id']; ?>">
                <ion-icon name="trash-outline"></ion-icon>
                Delete Post
              </button>
            </div>
          </div>
          <?php endif; ?>
        </div>

        <div class="post-image-container">
          <img class="post-image" src="assets/postsUploads/<?php echo htmlspecialchars($post['image']); ?>"
            alt="Post Image" onerror="this.src='assets/1.jpg';">
        </div>

        <div class="post-actions">
          <div class="action-buttons">
            <div class="action-group">
              <?php
                $like_count = $likeCounts[$post['id']] ?? 0;
                $user_liked = in_array($post['id'], $userLikes);
              ?>
              <button class="action-button like-button <?php echo $user_liked ? 'liked' : ''; ?>"
                data-post-id="<?php echo $post['id']; ?>">
                <ion-icon name="<?php echo $user_liked ? 'heart' : 'heart-outline'; ?>"></ion-icon>
              </button>
              <span class="action-count like-count"><?php echo $like_count; ?></span>
            </div>
            <div class="action-group">
              <button class="action-button comment-button" data-post-id="<?php echo htmlspecialchars($post['id']); ?>">
                <ion-icon name="chatbubble-outline"></ion-icon>
              </button>
              <?php
                $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM comments WHERE post_id = ?");
                $stmt->execute([$post['id']]); 
                $comment_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
              ?>
              <span class="action-count comment-count"><?php echo $comment_count; ?></span>
            </div>
            <div class="action-group">
              <button class="action-button">
                <ion-icon name="paper-plane-outline"></ion-icon>
              </button>
              <span class="action-count">0</span>
            </div>
          </div>
          <button class="action-button bookmark">
            <ion-icon name="bookmark-outline"></ion-icon>
          </button>
        </div>

        <div class="post-content">
          <div class="caption">
            <span class="username <?php echo ($post['user_id'] !== $_SESSION['user_id']) ? 'clickable' : ''; ?>">
              <?php echo htmlspecialchars($post['firstname'] . ' ' . $post['lastname']); ?>
            </span>
            <span class="description"><?php echo htmlspecialchars($post['description']); ?></span>
          </div>
          <div class="post-date">
            <?php echo date('F j, Y', strtotime($post['created_at'])); ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

  <div class="comment-modal" id="commentModal">
    <div class="modal-content">
      <span class="close-modal">&times;</span>
      <h2>Comments</h2>
      <div class="comments-list"></div>
      <form class="comment-form">
        <input type="text" placeholder="Add a comment...">
        <button class="post-comment" type="button">Post</button>
      </form>
    </div>
  </div>

  <div class="edit-modal" id="editModal">
    <div class="modal-content">
      <div class="modal-header">
        <h2>Edit Post</h2>
        <span class="close-modal">&times;</span>
      </div>
      <form class="edit-form" enctype="multipart/form-data">
        <div class="current-image-container">
          <img class="current-post-image" src="assets/postsUploads/<?php echo htmlspecialchars($post['image']); ?>"
            alt="Current post image">
        </div>
        <label class="file-upload">
          <span>Change Image</span>
          <input type="file" name="post_image" accept="image/*" class="edit-image-input">
        </label>
        <textarea class="edit-description" placeholder="Edit your description..."></textarea>
        <button type="button" class="update-post-btn">Update Post</button>
      </form>
    </div>
  </div>

  <div class="user-stats-modal">
    <div class="user-stats-content">
      <button class="close-stats-modal">&times;</button>
      <div class="user-stats-header">
        <img src="" alt="User Profile" id="modal-user-img">
        <h3 id="modal-username"></h3>
      </div>
      <div class="user-stats-info">
        <div class="user-stats-item">
          <div class="user-stats-count" id="modal-followers">0</div>
          <div class="user-stats-label">Followers</div>
        </div>
        <div class="user-stats-item">
          <div class="user-stats-count" id="modal-following">0</div>
          <div class="user-stats-label">Following</div>
        </div>
      </div>
    </div>
  </div>

  <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
  <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
  <script type="module" src="./js/nav.js"></script>
  <script type="module" src="./js/index.js"></script>
</body>

</html>