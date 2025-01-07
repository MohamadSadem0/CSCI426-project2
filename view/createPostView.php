<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Create Post</title>
  <link rel="stylesheet" href="../navBar.css">
  <link rel="stylesheet" href="../css/addPost.css">
</head>

<body>
  <div class="spinner-overlay" id="spinner">
    <div class="spinner"></div>
  </div>

  <?php include '../navBar.php'; ?>

  <div class="main__page">
    <form class="form" action="../action/add_post_handler.php" method="POST" enctype="multipart/form-data"
      onsubmit="return showSpinner(event)">
      <p class="header">Create a New Post</p>
      <p class="message">Share your thoughts with the community.</p>

      <label class="file-upload">
        <span>Upload Image</span>
        <input class="file-input" type="file" name="post_image" accept="image/*" required>
      </label>

      <label class="label" for="description">
        <span>Description</span>
        <textarea class="input" name="description" rows="4"></textarea>
      </label>

      <button class="submit" type="submit">Submit Post</button>

      <!-- Display errors if they exist -->
      <?php if (!empty($errors)): ?>
      <div class="error-container">
        <?php foreach ($errors as $error): ?>
        <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </form>
  </div>

  <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
  <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
  <script src="../js/nav.js"></script>

  <script>
  function showSpinner(event) {
    event.preventDefault();
    const form = document.querySelector('.form');
    const errorContainer = document.querySelector('.error-container');

    if (!form.checkValidity() || errorContainer) {
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
      if (errorContainer) errorContainer.remove();
    });
  });
  </script>
</body>

</html>
