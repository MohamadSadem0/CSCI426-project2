<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register</title>
  <!-- Link to your styles -->
  <link rel="stylesheet" href="navBar.css">
  <link rel="stylesheet" href="./css/register.css">
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
    <form class="form" action="action/register_handler.php" method="POST" enctype="multipart/form-data"
      onsubmit="return showSpinner(event)">
      <p class="header">Register </p>
      <p class="message">Signup now and get full access to our app. </p>
      <div class="flex">
        <label>
          <input class="input" type="text" name="firstname" placeholder="" required>
          <span>Firstname</span>
        </label>

        <label>
          <input class="input" type="text" name="lastname" placeholder="" required>
          <span>Lastname</span>
        </label>
      </div>

      <label>
        <input class="input" type="email" name="email" placeholder="" required>
        <span>Email</span>
      </label>

      <label>
        <input class="input" type="password" name="password" placeholder="" required>
        <span>Password</span>
      </label>

      <label>
        <input class="input" type="password" name="confirm_password" placeholder="" required>
        <span>Confirm password</span>
      </label>

      <label class="file-upload">
        <input class="file-input" type="file" name="profile_picture" accept="image/*" required>
        <span>Upload Photo</span>
      </label>

      <button class="submit">Submit</button>

      <!-- Display errors if they exist -->
      <?php if(isset($_SESSION['errors']) && !empty($_SESSION['errors'])): ?>
      <div class="error-container">
        <?php 
          foreach($_SESSION['errors'] as $error): ?>
        <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endforeach; 
          // Clear the errors after displaying them
          unset($_SESSION['errors']);
          ?>
      </div>
      <?php endif; ?>

      <p class="signin">Already have an account? <a href="login.php">Signin</a></p>
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