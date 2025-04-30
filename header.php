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
    gap: 10px; /* Add spacing between text and logo */
  }
  
  .logo span {
    color: #1a2f38;
  }
  
  .logo-icon img {
    height: 50px; /* Adjust the height of the logo */
    width: auto; /* Maintain aspect ratio */
    object-fit: contain;
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
  nav a:hover{
    color: #ff3131;
  }
  nav a.active {
    color: #1a2f38;
  }

  .dropdown {
    position: relative;
    display: inline-block;
  }

  .dropdown-content {
    padding-bottom: 5px;
    display: none;
    position: absolute;
    background-color: white;
    min-width: 150px;
    box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.2);
    z-index: 1;
    border-radius: 5px;
  }

  .dropdown-content a {
    color: black;
    padding: 10px 15px;
    text-decoration: none;
    display: block;
  }

  .dropdown-content a:hover {
    background-color: #f1f1f1;
  }

  .dropdown:hover .dropdown-content {
    display: block;
  }


  </style>
</head>
<body>
<header> 
  <div class="logo">
    <span>Absolute</span><span style="color: #1a2f38;">Cinema</span>
    <div class="logo-icon">
      <img src="images/logo.gif" alt="Logo" />
    </div>
  </div>
  <nav>
    <?php $current_page = basename($_SERVER['PHP_SELF']); ?>
    <a href="index.php" class="<?= $current_page == 'index.php' ? 'active' : '' ?>">Home</a>
    <a href="all-movies.php" class="<?= $current_page == 'all-movies.php' ? 'active' : '' ?>">Movies</a>
    
    <?php if (isset($_SESSION['user_name'])): ?>
      <div class="dropdown">
        <a href="#" class="dropbtn"><?= htmlspecialchars($_SESSION['user_name']); ?></a>
        <div class="dropdown-content">
          <a href="user-profile.php">Profile</a>
          <a href="logout.php">Log Out</a>
        </div>
      </div>
    <?php else: ?>
      <a href="login.php" class="<?= $current_page == 'login.php' ? 'active' : '' ?> login-link">Log in</a>
    <?php endif; ?>
  </nav> 
</header>
</body>
</html>