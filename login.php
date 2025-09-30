<?php
include 'config.php';
session_start(); // Start a session

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        echo '<script>alert("Please fill in both fields.")</script>';
    } 
    // Check kung Gmail address
   if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo '<script>alert("Please enter a valid email address.")</script>';
} elseif (!str_ends_with($email, '@gmail.com')) {
    echo '<script>alert("Only Gmail addresses are allowed.")</script>';
}
    // Check kung may capital letter ang password
    elseif (!preg_match('/[A-Z]/', $password)) {
        echo '<script>alert("Password must contain at least one capital letter.")</script>';
    } 
    else {
        //  Use prepared statement to prevent SQL injection
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user['password'])) {
                // Set session values from DB (ensure `role` exists in your users table)
                $_SESSION['user_id'] = $user['id']; 
                $_SESSION['email'] = $email;
                $_SESSION['fullname'] = $user['fullname'] ?? '';
                $_SESSION['role'] = $user['role'] ?? 'user';

                // Redirect depende sa role
                if ($_SESSION['role'] === 'admin') {
                    header("Location: Dashboard.php");
                } else {
                    header("Location: index.html");
                }
                exit;
            } else {
                echo '<script>alert("Wrong password!")</script>';
            }
        } else {
            echo '<script>alert("Email not found!")</script>';
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <link rel="stylesheet" href="login1.css">
  <meta charset="UTF-8">
  <title>Login to Saplot</title>
  <link rel="stysheet" href="styles.css">
</head>
<body>
  <div class="login-container">
    <h2>Login to Saplot</h2>
    <form method="POST">
      <input type="text" name="email" placeholder="Email" required minlength="8">
      <input type="password" name="password" placeholder="Password" required>
      <div class="options">
        <label><input type="checkbox" name="remember"> Remember me</label>
        <a href="#">Forgot Password?</a>
      </div>
      <button type="submit" name="login">Login</button>
      <p class="register">Don't you have an account? <a href="register.php">Register</a></p>
    </form>
  </div>
</body>
</html>