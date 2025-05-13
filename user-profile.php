<?php
// Start with session debugging
session_start();
include("config.php");

// Debugging section - Comment out or remove in production
//echo "<div style='background-color: #f8f9fa; padding: 10px; margin-bottom: 15px; border: 1px solid #ddd;'>";
//echo "<h3>Session Debug Info</h3>";
//echo "SESSION user_id: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'Not set') . "<br>";
//echo "SESSION user_name: " . (isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Not set') . "<br>";
//echo "</div>";
// End debugging section

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch user details with error checking
$user_id = $_SESSION['user_id'];
$userQuery = "SELECT user_id, first_name, last_name, email, phone, created_at, role FROM users WHERE user_id = ?";
$userStmt = mysqli_prepare($conn, $userQuery);

if (!$userStmt) {
    die("Error in user query preparation: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($userStmt, "s", $user_id);
$userExecute = mysqli_stmt_execute($userStmt);

if (!$userExecute) {
    die("Error executing user query: " . mysqli_stmt_error($userStmt));
}

$userResult = mysqli_stmt_get_result($userStmt);

// Check if user data was found
if ($userRow = mysqli_fetch_assoc($userResult)) {
    $fetched_user_id = $userRow['user_id']; // Explicitly fetch to verify
    $first_name = $userRow['first_name'];
    $last_name = $userRow['last_name'];
    $email = $userRow['email'];
    $phone = $userRow['phone'];
    $created_at = $userRow['created_at'];
    $role = $userRow['role'];
    
    // More debugging
    //echo "<div style='background-color: #f8f9fa; padding: 10px; margin-bottom: 15px; border: 1px solid #ddd;'>";
    //echo "<h3>Database User Debug Info</h3>";
    //echo "Fetched user_id: $fetched_user_id<br>";
    //echo "Role: $role<br>";
    //echo "</div>";
} else {
    // Handle the case where user data wasn't found
    echo "<div style='background-color: #ffdddd; padding: 10px; margin-bottom: 15px; border: 1px solid #f8d7da;'>";
    echo "<h3>Error: User Not Found</h3>";
    echo "No user data found for ID: $user_id<br>";
    echo "SQL Error: " . mysqli_error($conn);
    echo "</div>";
    
    $first_name = "Unknown";
    $last_name = "User";
    $email = "";
    $phone = "";
    $created_at = date("Y-m-d H:i:s");
    $role = "";
}
mysqli_stmt_close($userStmt);

// Fetch user bookings with movie poster and proper error handling
$bookingsQuery = "
    SELECT 
        b.booking_id,
        b.seat_count, 
        m.title, 
        m.genre, 
        m.duration, 
        m.rating,
        m.poster, 
        s.screen_name, 
        b.booking_date, 
        st.show_date, 
        st.start_time, 
        b.total_price, 
        b.status 
    FROM bookings b
    JOIN showtimes st ON b.showtime_id = st.showtime_id
    JOIN movies m ON st.movie_id = m.movie_id
    JOIN screens s ON st.screen_id = s.screen_id
    WHERE b.user_id = ?
    ORDER BY b.booking_date DESC";

$bookingsStmt = mysqli_prepare($conn, $bookingsQuery);

if (!$bookingsStmt) {
    die("Error in bookings query preparation: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($bookingsStmt, "s", $user_id);
$bookingsExecute = mysqli_stmt_execute($bookingsStmt);

if (!$bookingsExecute) {
    die("Error executing bookings query: " . mysqli_stmt_error($bookingsStmt));
}

$bookingsResult = mysqli_stmt_get_result($bookingsStmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Absolute Cinema - User Profile</title>
    <link rel="stylesheet" href="/styles/user-profile.css">
</head>
<body>
    <?php include("header.php") ?>
    <main>
        <!-- Profile Header -->
        <div class="profile-header">
            <div class="profile-avatar">
                <img src="/absolute-cinema-martin-scorsese.png" alt="Profile avatar">
            </div>
            <div class="profile-info">
                <h1><?php echo htmlspecialchars($first_name . ' ' . $last_name); ?></h1>
                <p>Email: <?php echo htmlspecialchars($email); ?></p>
                <p>Phone: <?php echo htmlspecialchars($phone); ?></p>
                <p>Member since: <?php echo date("F Y", strtotime($created_at)); ?></p>
                <p>Account type: <?php echo htmlspecialchars($role); ?></p>
            </div>
        </div> 

        <!-- Bookings Section -->
        <div class="bookings-section">
            <div class="bookings-header">
                <h2>My Bookings</h2>
            </div>

            <div class="booking-cards">
                <?php if (mysqli_num_rows($bookingsResult) > 0): ?>
                    <?php while ($booking = mysqli_fetch_assoc($bookingsResult)): ?>
                        <div class="booking-card">
                            <div class="booking-image">
                                <?php if (!empty($booking['poster'])): ?>
                                    <img src="<?php echo htmlspecialchars($booking['poster']); ?>" alt="<?php echo htmlspecialchars($booking['title']); ?> poster">
                                <?php else: ?>
                                    <img src="/images/default-movie-poster.jpg" alt="Default movie poster">
                                <?php endif; ?>
                            </div>
                            <div class="booking-details">
                                <div class="booking-number">Booking #<?php echo htmlspecialchars($booking['booking_id']); ?></div>
                                <h3 class="booking-title"><?php echo htmlspecialchars($booking['title']); ?></h3>
                                <p class="booking-meta"><?php echo htmlspecialchars($booking['genre']); ?> | <?php echo htmlspecialchars($booking['duration']); ?> mins | <?php echo htmlspecialchars($booking['rating']); ?></p>
                                
                                <div class="booking-info-grid">
                                    <div class="booking-info-column">
                                        <p class="booking-info-label">Date & Time</p>
                                        <p class="booking-info-value"><?php echo date("d M Y", strtotime($booking['show_date'])); ?></p>
                                        <p class="booking-info-value"><?php echo date('h:i A', strtotime($booking['start_time'])); ?></p>
                                    </div>
                                    <div class="booking-info-column">
                                        <p class="booking-info-label">Screen & Seat</p>
                                        <p class="booking-info-value"><?php echo htmlspecialchars($booking['screen_name']); ?></p>
                                        <p class="booking-info-value">Seat Count: <?php echo htmlspecialchars($booking['seat_count']); ?></p>
                                    </div>
                                    <div class="booking-info-column">
                                        <p class="booking-info-label">Total Price</p>
                                        <p class="booking-info-value">$<?php echo htmlspecialchars($booking['total_price']); ?></p>
                                    </div>
                                </div>
                                
                                <p class="booking-timestamp">Booked on: <?php echo date("d M Y, h:i A", strtotime($booking['booking_date'])); ?></p>
                                <p class="booking-status">Status: <?php echo htmlspecialchars($booking['status']); ?></p>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No bookings found for this user account (ID: <?php echo $user_id; ?>).</p>
                <?php endif; ?>
            </div>
        </div>
    </main>
    <?php include("footer.php") ?>
</body>
</html>