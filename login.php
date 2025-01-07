<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login</title>
  <link rel="stylesheet" href="navBar.css">
  <link rel="stylesheet" href="./css/login.css">
</head>

<body>
  <div class="spinner-overlay" id="spinner">
    <div class="spinner"></div>
  </div>

  <?php 
    session_start();
    include 'navBar.php'; 
    ?>

  <div class="main__page">
    <form class="form" action="action/login_handler.php" method="POST" onsubmit="return showSpinner(event)">
      <p class="header">Login</p>
      <p class="message">Welcome back! Please login to your account.</p>

      <label>
        <input class="input" type="email" name="email" placeholder="" required>
        <span>Email</span>
      </label>

      <label>
        <input class="input" type="password" name="password" placeholder="" required>
        <span>Password</span>
      </label>

      <p class="forgot-password">
        <a href="forgot_password.php">Forgot Password?</a>
      </p>

      <button class="submit" type="submit">Login</button>

      <!-- Display errors if they exist -->
      <?php if(isset($_SESSION['errors']) && !empty($_SESSION['errors'])): ?>
      <div class="error-container">
        <?php 
                    foreach($_SESSION['errors'] as $error): ?>
        <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endforeach; 
                    unset($_SESSION['errors']);
                    ?>
      </div>
      <?php endif; ?>

      <p class="signin">Don't have an account? <a href="register.php">Register</a></p>
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

    // Check for any type of error (validation or server-side)
    if (!form.checkValidity() || errorContainer || errorMessage) {
      form.submit();
      return false;
    }

    // Only show spinner if there are no errors at all
    document.getElementById('spinner').style.display = 'flex';

    setTimeout(() => {
      form.submit();
    }, 1000);

    return false;
  }

  // Hide spinner if user navigates back
  window.onpageshow = function(event) {
    if (event.persisted) {
      document.getElementById('spinner').style.display = 'none';
    }
  };

  // Clear errors when input changes
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