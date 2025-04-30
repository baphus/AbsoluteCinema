<!DOCTYPE html>
<html lang="en">
<head>
  <style> 
  header {
    background-color: #3498db;
    padding: 15px 40px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    height: 100px;
  }
  
  .logo {
    color: white;
    font-size: 32px;
    font-weight: bold;
    display: flex;
    align-items: center;
  }
  
  .logo span {
    color: #1a2f38;
  }
  
  .logo-icon {
    margin-left: 10px;
  }
  
  nav {
    display: flex;
    gap: 30px;
  }
  
  nav a {
    color: white;
    text-decoration: none;
    font-size: 18px;
    font-weight: bold;
  }
  
  nav a.active {
    color: #ff3131;
  }

  </style>
</head>
<body>
<header> 
      <div class="logo">
        Absolute<span style="color: #1a2f38;">Cinema</span>
        <span class="logo-icon">
          <img src="styles/images/logo.gif" alt="Logo" width="50" height="50" style="color: #1a2f38;"/>
        </span>
      </div>
      <nav>
        <?php
          $current_page = basename($_SERVER['PHP_SELF']);
        ?>
        <a href="index.php" class="<?= $current_page == 'index.php' ? 'active' : '' ?>">Home</a>
        <a href="all-movies.php" class="<?= $current_page == 'all-movies.php' ? 'active' : '' ?>">Movies</a>
        <a href="login.php" class="<?= $current_page == 'login.php' ? 'active' : '' ?> login-link">Log in</a>
      </nav> 
</header>
</body>
</html>