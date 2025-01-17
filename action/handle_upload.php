<?php
function handleFileUpload($file, $directory) {
    $errors = [];

    if (isset($file) && $file['error'] === UPLOAD_ERR_OK) {
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];

        if (!in_array($file_extension, $allowed_extensions)) {
            $errors[] = "Invalid file type. Only JPG, JPEG, PNG & GIF files are allowed.";
        }

        if ($file['size'] > 1048576) {
            $errors[] = "File is too large. Maximum size is 1MB.";
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            header("Location: ../register.php");
            exit();
        }

        $upload_dir = '../' . $directory . '/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $timestamp = date('Y-m-d_H-i-s');
        $random = bin2hex(random_bytes(8));
        $new_filename = "@user_" . $timestamp . "@" . $random . "." . $file_extension;
        $upload_path = $upload_dir . $new_filename;
        
        if (move_uploaded_file($file['tmp_name'], $upload_path)) {
            return $new_filename;
        } else {
            $errors[] = "Failed to upload file.";
            $_SESSION['errors'] = $errors;
            header("Location: ../register.php");
            exit();
        }
    } else {
        $errors[] = "No file uploaded or upload error occurred.";
        $_SESSION['errors'] = $errors;
        header("Location: ../register.php");
        exit();
    }
}
?>