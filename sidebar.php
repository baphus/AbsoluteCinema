<?php
$currentpage = basename($_SERVER["PHP_SELF"]);
?>

<aside class="sidebar">
    <nav class="sidebar-nav">
        <ul>
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

<link rel="stylesheet" href="/styles/sidebar.css">