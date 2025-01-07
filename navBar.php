<?php
require_once __DIR__ . '/action/db_connection.php';
require_once __DIR__ . '/action/decryption.php';

$current_page = basename($_SERVER['PHP_SELF']);
$decryption = new Decryption();

// Check if user is logged in and get their info
$user_info = null;
if (isset($_SESSION['user_id'])) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            $user_info = [
                'firstName' => $decryption->decrypt($user['firstname']),
                'lastName' => $decryption->decrypt($user['lastname']),
                'profile_picture' => $user['profile_picture']
            ];
        }
    } catch (Exception $e) {
        error_log("Error fetching user info: " . $e->getMessage());
    }
}
?>

<div class="navigation">
  <ul>
    <?php if (!isset($_SESSION['user_id'])): ?>
    <!-- Show when user is NOT logged in -->
    <li class="list no-pointer">
      <a href="#">
        <span class="icon">
          <ion-icon name="book-outline"></ion-icon>
        </span>
        <span class="title blog-title">BLOG</span>
      </a>
    </li>

    <li class="list <?= ($current_page == 'index.php') ? 'active' : ''; ?>">
      <a href="index.php">
        <span class="icon">
          <ion-icon name="home-outline"></ion-icon>
        </span>
        <span class="title">Blog Home</span>
      </a>
    </li>

    <li class="list <?= ($current_page == 'login.php') ? 'active' : ''; ?>">
      <a href="login.php">
        <span class="icon">
          <ion-icon name="log-in-outline"></ion-icon>
        </span>
        <span class="title">Login</span>
      </a>
    </li>

    <li class="list <?= ($current_page == 'register.php') ? 'active' : ''; ?>">
      <a href="register.php">
        <span class="icon">
          <ion-icon name="person-add-outline"></ion-icon>
        </span>
        <span class="title">Register</span>
      </a>
    </li>

    <?php else: ?>
    <!-- Show when user is logged in -->
    <li class="list no-pointer">
      <a href="#">
        <span class="icon">
          <?php if (!empty($user_info['profile_picture'])): ?>
          <img src="assets/profileUploads/<?php echo htmlspecialchars($user_info['profile_picture']); ?>" alt="Profile"
            class="profile-img" onerror="this.onerror=null; this.src='assets/1.jpg';" />
          <?php else: ?>
          <ion-icon name="person-outline"></ion-icon>
          <?php endif; ?>
        </span>
        <span class="title">
          <?php echo htmlspecialchars($user_info['firstName'] . ' ' . $user_info['lastName']); ?>
        </span>
      </a>
    </li>

    <li class="list <?= ($current_page == 'index.php') ? 'active' : ''; ?>">
      <a href="index.php">
        <span class="icon">
          <ion-icon name="home-outline"></ion-icon>
        </span>
        <span class="title">Home</span>
      </a>
    </li>

    <li class="list <?= ($current_page == 'profile.php') ? 'active' : ''; ?>">
      <a href="profile.php">
        <span class="icon">
          <ion-icon name="person-outline"></ion-icon>
        </span>
        <span class="title">Profile</span>
      </a>
    </li>

    <li class="list <?= ($current_page == 'addPost.php') ? 'active' : ''; ?>">
      <a href="addPost.php">
        <span class="icon">
          <ion-icon name="create-outline"></ion-icon>
        </span>
        <span class="title">Add Post</span>
      </a>
    </li>

    <li class="list">
      <a href="action/logout.php">
        <span class="icon">
          <ion-icon name="log-out-outline"></ion-icon>
        </span>
        <span class="title">Logout</span>
      </a>
    </li>
    <?php endif; ?>
  </ul>
</div>