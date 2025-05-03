<?php
session_start();
include("config.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    // Query to check if the user exists
    $query = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);

        // Verify the password
        if (password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_name'] = $user['first_name'];
            $_SESSION['role'] = $user['role'];
            // Redirect to the homepage
            header("Location: index.php");
            exit;
        } else {
            echo '<script>alert("Invalid password.");</script>';
        }
    } else {
        echo '<script>alert("Invalid email or password.");</script>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="styles/login.css">
  <title>Log In Page | Absolute Cinema</title>
</head>
<body>
  <div class="page-container">
   <?php include("header.php")?>
    <main>
      <form class="form-box" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) ?>">
        <h2>Log In</h2>
        <input type="email" name="email" placeholder="Email" required />
        <input type="password" name="password" placeholder="Password" required />
        <div class="form-options checkbox-container" style="justify-content: space-between;">
          <label><input type="checkbox" name="checkbox" /> Remember me</label>
          <a class="forgotpass" href="#">Forgot Password?</a>
        </div>

        <button type="submit" class="red-btn">Login</button>

        <div class="login-text"> 
          <p>Don't have an account? <a class="register-link" href="/register.php">Register</a></p>
        </div>

        <div class="terms-container">
          <p>By logging in, you agree to our <a href="#">Terms & Conditions</a> and <a href="#">Privacy Policy</a>.</p>
        </div>
      </form>
    </main>
  </div>
  <?php include("footer.php")?>
</body>
</html>
