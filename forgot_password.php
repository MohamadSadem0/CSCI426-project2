<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Forgot Password</title>
  <!-- External Stylesheets -->
  <link rel="stylesheet" href="navBar.css">
  <link rel="stylesheet" href="./css/forgot_password.css">
</head>

<body>
  <div class="spinner-overlay" id="spinner">
    <div class="spinner"></div>
  </div>

  <?php include 'navBar.php'; ?>

  <div class="main__page">
    <form class="form" action="action/forgot_password_handler.php" method="POST" onsubmit="return showSpinner(event)">
      <p class="header">Reset Password</p>
      <p class="message">Enter your email and new password.</p>

      <label>
        <input class="input" type="email" name="email" placeholder="" required>
        <span>Email</span>
      </label>

      <label>
        <input class="input" type="password" name="new_password" placeholder="" required>
        <span>New Password</span>
      </label>

      <label>
        <input class="input" type="password" name="confirm_password" placeholder="" required>
        <span>Confirm Password</span>
      </label>

      <button class="submit" type="submit">Reset Password</button>

      <!-- Display errors if they exist -->
      <?php if(isset($_SESSION['errors']) && !empty($_SESSION['errors'])): ?>
      <div class="error-container">
        <?php foreach($_SESSION['errors'] as $error): ?>
        <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endforeach; 
        unset($_SESSION['errors']);
        ?>
      </div>
      <?php endif; ?>

      <p class="signin"><a href="login.php">Back to Login</a></p>
    </form>
  </div>

  <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
  <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
  <script src="./js/nav.js"></script>

  <script>
  function showSpinner(event) {
    event.preventDefault();
    const form = document.querySelector('.form');
    const errorContainer = document.querySelector('.error-container');
    const errorMessage = document.querySelector('.error-message');

    if (!form.checkValidity() || errorContainer || errorMessage) {
      form.submit();
      return false;
    }

    document.getElementById('spinner').style.display = 'flex';

    setTimeout(() => {
      form.submit();
    }, 1000);

    return false;
  }

  window.onpageshow = function(event) {
    if (event.persisted) {
      document.getElementById('spinner').style.display = 'none';
    }
  };

  document.querySelectorAll('.input').forEach(input => {
    input.addEventListener('input', () => {
      const errorContainer = document.querySelector('.error-container');
      const errorMessage = document.querySelector('.error-message');
      if (errorContainer) errorContainer.remove();
      if (errorMessage) errorMessage.remove();
    });
  });
  </script>
</body>

</html>