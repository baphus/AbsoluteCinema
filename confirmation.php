<?php
session_start();
include("config.php");

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    error_log("User ID not set in session. Redirecting to login.");
    header("Location: login.php");
    exit;
}

$userID = $_SESSION['user_id'];

// Check if booking ID is provided
if (!isset($_GET['booking_id']) || empty($_GET['booking_id'])) {
    error_log("No booking ID in URL. Redirecting to index.");
    header("Location: index.php");
    exit;
}

$booking_id = $_GET['booking_id'];

// Fetch booking details
$bookingQuery = "
    SELECT b.*, s.show_date, s.start_time, m.title, m.poster, m.duration, m.rating, scr.screen_name
    FROM bookings b
    JOIN showtimes s ON b.showtime_id = s.showtime_id
    JOIN movies m ON s.movie_id = m.movie_id
    JOIN screens scr ON s.screen_id = scr.screen_id
    WHERE b.booking_id = ? AND b.user_id = ?
";
$stmt = mysqli_prepare($conn, $bookingQuery);
mysqli_stmt_bind_param($stmt, "ss", $booking_id, $userID);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Check if booking exists
if (!$result || mysqli_num_rows($result) === 0) {
    error_log("Booking not found for ID: $booking_id and User ID: $userID");
    header("Location: index.php");
    exit;
}

$booking = mysqli_fetch_assoc($result);

// Generate a transaction ID
$transaction_id = "TXN" . rand(1000000, 9999999);

// For demo purposes, assume payment was made with a credit card
$payment_method = "Credit Card (**** " . rand(1000, 9999) . ")";

// Format the date and time
$formatted_date = date('F j, Y', strtotime($booking['show_date']));
$formatted_time = date('g:i A', strtotime($booking['start_time']));

// For seat display, we'll use seat count since we don't have specific seat info
$seat_display = $booking['seat_count'] . " seat(s)";

// Format the price
$formatted_price = "₱" . number_format($booking['total_price'], 2);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmation - Absolute Cinema</title>
    <link rel="stylesheet" href="/styles/confirmation.css">
</head>
<body>
        <?php include("header.php");?>    
    <div class="confirmation-container">
        <div class="confirmation-header">
            <div class="check-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                    <path d="M9 16.17l-4.17-4.17-1.42 1.41 5.59 5.59 12-12-1.41-1.41z" fill="white"/>
                </svg>
            </div>
            <h1>Booking Confirmed!</h1>
            <p>Your tickets have been booked successfully.</p>
        </div>
        <div class="ticket-container">
            <div class="ticket-left">
                <img src="<?php echo htmlspecialchars($booking['poster']); ?>" alt="<?php echo htmlspecialchars($booking['title']); ?> Movie Poster" class="movie-poster">
               
                <div class="ticket-details">
                    <h2 class="movie-title"><?php echo htmlspecialchars($booking['title']); ?></h2>
                    <div class="ticket-info">
                        <p><strong><?php echo htmlspecialchars($booking['rating']); ?> | <?php echo htmlspecialchars($booking['duration']); ?> mins</strong></p>
                        <p><strong>Date & Time:</strong> <?php echo $formatted_date; ?> at <?php echo $formatted_time; ?></p>
                        <p><strong>Cinema:</strong> <?php echo htmlspecialchars($booking['screen_name']); ?></p>
                        <p><strong>Seats:</strong> <?php echo htmlspecialchars($seat_display); ?></p>
                        <p><strong>Booking ID:</strong> #<?php echo htmlspecialchars($booking_id); ?></p>
                    </div>
                </div>
            </div>
           
            <div class="ticket-divider"></div>
           
            <div class="ticket-right">
                <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='120' height='120' viewBox='0 0 120 120'%3E%3Crect width='120' height='120' fill='white'/%3E%3Cpath d='M0,0 L120,0 L120,120 L0,120 Z' fill='none' stroke='black' stroke-width='5'/%3E%3Cpath d='M20,20 L100,20 L100,100 L20,100 Z' fill='none' stroke='black' stroke-width='5'/%3E%3Cpath d='M40,40 L80,40 L80,80 L40,80 Z' fill='none' stroke='black' stroke-width='5'/%3E%3Cpath d='M50,50 L70,50 L70,70 L50,70 Z' fill='black'/%3E%3C/svg%3E" alt="QR Code" class="qr-code">
            </div>
        </div>
       
        <div class="ticket-note">
            Please arrive at least 15 minutes before the show time. Present this confirmation or the QR code at the ticket counter.
        </div>
        <div class="payment-section">
            <div class="payment-box">
                <h3>Payment Information</h3>
                <div class="payment-info">
                    <p><strong>Amount Paid:</strong> <?php echo $formatted_price; ?></p>
                    <p><strong>Payment Method:</strong> <?php echo $payment_method; ?></p>
                    <p><strong>Transaction ID:</strong> <?php echo $transaction_id; ?></p>
                </div>
            </div>
           
            <div class="payment-box">
                <h3>Customer Information</h3>
                <div class="payment-info">
                    <p><strong>Name:</strong> <?php echo htmlspecialchars($booking['first_name'] ?? 'Customer') . ' ' . htmlspecialchars($booking['last_name'] ?? ''); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($booking['email'] ?? 'Not provided'); ?></p>
                    <p><strong>Booking Date:</strong> <?php echo date('F j, Y', strtotime($booking['booking_date'])); ?></p>
                </div>
            </div>
        </div>
        <div class="action-buttons">
            <a href="#" class="btn btn-primary">Download Tickets</a>
            <a href="#" class="btn btn-secondary">Email Receipt</a>
            <a href="index.php" class="btn btn-danger">Return to Home</a>
        </div>
    </div>
    
    <?php include("footer.php");?>
</body>
</html>