<?php
session_start();
include("config.php");

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch user details
$user_id = $_SESSION['user_id'];
$userQuery = "SELECT first_name, last_name, email, phone, created_at FROM users WHERE user_id = ?";
$userStmt = mysqli_prepare($conn, $userQuery);
mysqli_stmt_bind_param($userStmt, "i", $user_id);
mysqli_stmt_execute($userStmt);
mysqli_stmt_bind_result($userStmt, $first_name, $last_name, $email, $phone, $created_at);
mysqli_stmt_fetch($userStmt);
mysqli_stmt_close($userStmt);

// Fetch user bookings
$bookingsQuery = "
    SELECT 
        b.booking_id, 
        m.title, 
        m.genre, 
        m.duration, 
        m.rating, 
        s.screen_name, 
        bd.seat_id, 
        b.booking_date, 
        st.show_date, 
        st.start_time, 
        b.total_price, 
        b.status 
    FROM bookings b
    JOIN showtimes st ON b.showtime_id = st.showtime_id
    JOIN movies m ON st.movie_id = m.movie_id
    JOIN screens s ON st.screen_id = s.screen_id
    JOIN bookingdetails bd ON b.booking_id = bd.booking_id
    WHERE b.user_id = ?
    ORDER BY b.booking_date DESC";
$bookingsStmt = mysqli_prepare($conn, $bookingsQuery);
mysqli_stmt_bind_param($bookingsStmt, "i", $user_id);
mysqli_stmt_execute($bookingsStmt);
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
                                <img src="/api/placeholder/150/150" alt="Movie poster">
                            </div>
                            <div class="booking-details">
                                <div class="booking-number">Booking #<?php echo htmlspecialchars($booking['booking_id']); ?></div>
                                <h3 class="booking-title"><?php echo htmlspecialchars($booking['title']); ?></h3>
                                <p class="booking-meta"><?php echo htmlspecialchars($booking['genre']); ?> | <?php echo htmlspecialchars($booking['duration']); ?> mins | <?php echo htmlspecialchars($booking['rating']); ?></p>
                                
                                <div class="booking-info-grid">
                                    <div class="booking-info-column">
                                        <p class="booking-info-label">Date & Time</p>
                                        <p class="booking-info-value"><?php echo date("d M Y", strtotime($booking['show_date'])); ?></p>
                                        <p class="booking-info-value"><?php echo htmlspecialchars($booking['start_time']); ?></p>
                                    </div>
                                    <div class="booking-info-column">
                                        <p class="booking-info-label">Screen & Seat</p>
                                        <p class="booking-info-value"><?php echo htmlspecialchars($booking['screen_name']); ?></p>
                                        <p class="booking-info-value">Seat: <?php echo htmlspecialchars($booking['seat_id']); ?></p>
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
                    <p>No bookings found.</p>
                <?php endif; ?>
            </div>
        </div>
    </main>
    <?php include("footer.php") ?>
</body>
</html>