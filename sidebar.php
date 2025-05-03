
<style>
@import url('https://fonts.googleapis.com/css2?family=Abril+Fatface&family=IBM+Plex+Sans:ital,wght@0,100..700;1,100..700&family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Montserrat:ital,wght@0,100..900;1,100..900&family=Roboto:ital,wght@0,100..900;1,100..900&display=swap');


.sidebar {
  width: 250px;
  background-color: #3498db;
  color: #17242a;
  display: flex;
  flex-direction: column;
  flex-shrink: 0;
  transition: all 0.3s;
  min-height: calc(50vh - 70px);
  position: relative;
}

.sidebar-header {
  padding: 20px 15px;
  border-bottom: 1px solid #31729a;
}

.sidebar-header .logo {
  font-size: 1.8em;
  font-weight: bold;
  color: #fff;
}
.sidebar-header .logo span {
  color: #17242a;
}

.sidebar-nav {
  flex-grow: 1;
  padding-top: 20px;
}

.sidebar-nav ul li a {
  display: flex;
  align-items: center;
  padding: 14px 25px;
  color: #17242a;
  font-weight: bold;
  transition: all 0.3s ease;
  position: relative;
}

.sidebar-nav ul li a:hover {
  background-color: rgba(23, 36, 42, 0.2);
  color: #fff;
}

.sidebar-nav ul li.active a {
  background-color: #17242a;
  color: #fff;
  font-weight: bold;
}
.sidebar-nav ul li.active a::before {
  content: "";
  width: 5px;
  height: 100%;
  background-color: red;
  position: absolute;
  left: 0;
  top: 0;
}

.sidebar-nav ul li a.active {
  background-color: #17242a;
  color: #fff;
  font-weight: bold;
  position: relative;
}

.sidebar-nav ul li a.active::before {
  content: "";
  width: 5px;
  height: 100%;
  background-color: red;
  position: absolute;
  left: 0;
  top: 0;
}
</style>

<aside class="sidebar">
    <nav class="sidebar-nav">
        <ul>
            <?php $currentpage = basename($_SERVER["PHP_SELF"]); ?>
            <li><a class="<?php echo $currentpage == "dashboard.php" ? 'active' : ''; ?>" href="dashboard.php"><span class="icon"><i class="fas fa-tachometer-alt"></i></span> Dashboard</a></li>
            <li><a class="<?php echo $currentpage == "users.php" ? 'active' : ''; ?>" href="users.php"><span class="icon"><i class="fas fa-chart-bar"></i></span> Users</a></li>
            <li><a class="<?php echo $currentpage == "movies.php" ? 'active' : ''; ?>" href="movies.php"><span class="icon"><i class="fas fa-film"></i></span> Movies</a></li>
            <li><a class="<?php echo $currentpage == "showtimes.php" ? 'active' : ''; ?>" href="showtimes.php"><span class="icon"><i class="fas fa-clock"></i></span> Showtimes</a></li>
            <li><a class="<?php echo $currentpage == "screens.php" ? 'active' : ''; ?>" href="screens.php"><span class="icon"><i class="fas fa-tv"></i></span> Screens</a></li>
            <li><a class="<?php echo $currentpage == "bookings.php" ? 'active' : ''; ?>" href="bookings.php"><span class="icon"><i class="fas fa-ticket-alt"></i></span> Bookings</a></li>
            <li><a class="<?php echo $currentpage == "seatings.php" ? 'active' : ''; ?>" href="seatings.php"><span class="icon"><i class="fas fa-chair"></i></span> Seatings</a></li>
      
        </ul>
    </nav>
</aside>