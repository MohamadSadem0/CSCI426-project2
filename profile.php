<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'action/db_connection.php';
require_once 'action/decryption.php';

$decryption = new Decryption();

// Fetch user info
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Decrypt user info
$user_info = [
    'firstName' => $decryption->decrypt($user['firstname']),
    'lastName' => $decryption->decrypt($user['lastname']),
    'profile_picture' => $user['profile_picture']
];

// Fetch follower count
$stmt = $pdo->prepare("SELECT COUNT(*) FROM follows WHERE followed_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$followerCount = $stmt->fetchColumn();

// Fetch following count
$stmt = $pdo->prepare("SELECT COUNT(*) FROM follows WHERE follower_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$followingCount = $stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Profile</title>
  <link rel="stylesheet" href="navBar.css">
  <link rel="stylesheet" href="./css/profile.css">
</head>

<body>
  <?php include 'navBar.php'; ?>

  <?php if (isset($_SESSION['errors']) && !empty($_SESSION['errors'])): ?>
  <div class="error-container">
    <?php foreach ($_SESSION['errors'] as $error): ?>
    <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
    <?php endforeach; unset($_SESSION['errors']); ?>
  </div>
  <?php endif; ?>

  <div class="content">
    <div class="profile-info">
      <div class="header">
        <span>Profile</span>
      </div>

      <form class="form" action="action/update_profile.php" method="POST" enctype="multipart/form-data">
        <div class="profile-image-container">
          <img src="assets/profileUploads/<?php echo htmlspecialchars($user_info['profile_picture']); ?>"
            alt="Profile Image" class="profile-img" id="profile-preview">
          <label class="change-photo-btn">
            <ion-icon name="camera-outline"></ion-icon>
            Change Photo
            <input type="file" name="profile_picture" accept="image/*" id="profile-upload">
          </label>
        </div>

        <label>
          <input class="input" type="text" name="firstname"
            value="<?php echo htmlspecialchars($user_info['firstName']); ?>" placeholder="" required>
          <span>First Name</span>
        </label>

        <label>
          <input class="input" type="text" name="lastname"
            value="<?php echo htmlspecialchars($user_info['lastName']); ?>" placeholder="" required>
          <span>Last Name</span>
        </label>

        <button class="submit">Update Profile</button>
      </form>

      <div class="stats">
        <p>Followers: <?php echo $followerCount; ?></p>
        <p>Following: <?php echo $followingCount; ?></p>
      </div>
    </div>
  </div>

  <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
  <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
  <script src="./js/nav.js"></script>
  <script src="./js/profile.js"></script>
</body>

</html>