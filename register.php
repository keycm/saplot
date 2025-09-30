<?php 
session_start();
include 'config.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') { 
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $fullname = trim($_POST['fullname'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $confirm  = $_POST['confirm_password'] ?? '';

    // Validation for fullname (letters + spaces only)
    if (!preg_match("/^[a-zA-Z\s]+$/", $fullname)) {
        $error = "Full name must contain only letters and spaces (no numbers or symbols).";
    }
    // Validation for allowed email domains
    elseif (!preg_match("/^[\w\.\-]+@(gmail\.com|email\.com)$/", $email)) {
        $error = "Email must be either @gmail.com or @email.com only.";
    }
    // Validation for password rule (min 8 chars, at least 1 capital letter)
    elseif (!preg_match("/^(?=.*[A-Z]).{8,}$/", $password)) {
        $error = "Password must be at least 8 characters and contain at least one capital letter.";
    }
    elseif ($password !== $confirm) {
        $error = "Passwords do not match!";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT); 

        // Use prepared statement for security
        $stmt = $conn->prepare("INSERT INTO users (username, password, fullname, email) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $username, $hashed_password, $fullname, $email);

        if ($stmt->execute()) { 
            $success = "Registration successful!";
        } else { 
            $error = "Error: " . $stmt->error; 
        } 
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Register to Saplot</title>
  <link rel="stylesheet" href="register1.css">
</head>
<body>
  <div class="background">
    <div class="form-box">
      <h2>Register to Saplot</h2>

      <form action="" method="POST">
        <input type="text" name="fullname" placeholder="Full Name" required minlength="8">
        <input type="text" name="username" placeholder="Username" required minlength="4">
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" 
               required minlength="8" 
               pattern="^(?=.*[A-Z]).{8,}$" 
               title="Password must be at least 8 characters and contain one capital letter">
        <input type="password" name="confirm_password" placeholder="Confirm Password" required>
        <button type="submit" name="register">Register</button>
        <p class="login">Have an account? <a href="login.php">Login</a></p>
     </form>
    </div>
  </div>

  <!-- Modal -->
  <div id="messageModal" class="modal">
    <div class="modal-content">
      <span class="close-btn">&times;</span>
      <p id="modalMessage"></p>
    </div>
  </div>

  <script>
    document.addEventListener("DOMContentLoaded", function () {
        const modal = document.getElementById("messageModal");
        const modalMsg = document.getElementById("modalMessage");
        const closeBtn = document.querySelector(".close-btn");

        <?php if (!empty($success)): ?>
            modalMsg.textContent = "<?php echo $success; ?>";
            modalMsg.style.color = "green";
            modal.style.display = "flex";
        <?php elseif (!empty($error)): ?>
            modalMsg.textContent = "<?php echo $error; ?>";
            modalMsg.style.color = "red";
            modal.style.display = "flex";
        <?php endif; ?>

        closeBtn.onclick = function () {
            modal.style.display = "none";
        };
        window.onclick = function (event) {
            if (event.target === modal) {
                modal.style.display = "none";
            }
        };
    });
  </script>
</body>
</html>
