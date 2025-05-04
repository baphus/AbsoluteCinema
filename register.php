<?php 
include("config.php");
$error_message = ""; // Initialize an error message variable

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = uniqid("Cine");
    $firstname = mysqli_real_escape_string($conn, $_POST['firstname']);
    $lastname = mysqli_real_escape_string($conn, $_POST['lastname']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $role = 'user';
    $password = $_POST['password'];
    $confirmpassword = $_POST['confirmpassword'];

    // Check if email already exists
    $emailQuery = "SELECT email FROM users WHERE email = '$email'";
    $emailResult = mysqli_query($conn, $emailQuery);

    if (mysqli_num_rows($emailResult) > 0) {
        echo "This email is already taken.";
    } else {
        // Check if passwords match
        if ($password === $confirmpassword) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $query = "INSERT INTO users (user_id, first_name, last_name, email, phone, role, password) VALUES ('$user_id','$firstname', '$lastname', '$email', '$phone', '$role', '$hashed_password')";
            
            if (mysqli_query($conn, $query)) {
                echo "Successfully registered.";
                header("Location: login.php");
                exit;
            } else {
                echo "Error: Unable to register.";
            }
        } else {
            echo "Passwords do not match.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Create Account | Absolute Cinema</title>
  <link rel="stylesheet" href="styles/login.css"/>
</head>
<body>
<?php include("header.php")?>
  <main>
    <form class="form-box" method="POST" action="register.php">
      <h2>Create an Account</h2>
      
      <div class="input-group">
        <label for="firstname">First name</label>
        <input type="text" id="firstname" name="firstname" required />
      </div>
      
      <div class="input-group">
        <label for="lastname">Last name</label>
        <input type="text" id="lastname" name="lastname" />
      </div>
      
      <div class="input-group">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" required />
      </div>
      
      <div class="input-group">
        <label for="phone">Phone number</label>
        <input type="tel" id="phone" name="phone" />
      </div>
      
      <div class="input-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required />
      </div>
      
      <div class="input-group">
        <label for="confirmpassword">Confirm password</label>
        <input type="password" id="confirmpassword" name="confirmpassword" required />
      </div>
      
      <div class="checkbox-container">
        <input type="checkbox" id="terms" name="terms" required />
        <label for="terms">Agree to <a href="#" style="color: #ff3a3a;">Terms and Conditions</a></label>
      </div>
      
      <button type="submit" class="red-btn">Register</button>
      
      <div class="login-text">
        Already have an account? <a href="login.php" style="color: #ff3a3a;">Login</a>
      </div>
    </form>
  </main>
  <?php include ("footer.php")?>
</body>
</html>