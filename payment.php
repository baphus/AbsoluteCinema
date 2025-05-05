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

if (!isset($_GET['booking_id']) || empty($_GET['booking_id'])) {
    error_log("No booking ID in URL. Redirecting to index.");
    header("Location: index.php");
    exit;
}

$booking_id = $_GET['booking_id'];

// Fetch user details
$userQuery = "SELECT * FROM users WHERE user_id = ?";
$stmt = mysqli_prepare($conn, $userQuery);
mysqli_stmt_bind_param($stmt, "s", $userID);
mysqli_stmt_execute($stmt);
$userResult = mysqli_stmt_get_result($stmt);

if ($userResult && mysqli_num_rows($userResult) > 0) {
    $user = mysqli_fetch_assoc($userResult);
} else {
    error_log("User not found for ID: $userID");
    header("Location: login.php");
    exit;
}

// User details
$cardholder_name = $user['first_name'] . ' ' . $user['last_name'];
$billing_first_name = $user['first_name'];
$billing_last_name = $user['last_name'];
$billing_email = $user['email'];

// Step 1: Get the show_date for the current booking
$showDateQuery = "
    SELECT s.show_date
    FROM bookings b
    JOIN showtimes s ON b.showtime_id = s.showtime_id
    WHERE b.booking_id = ?
";
$stmt = mysqli_prepare($conn, $showDateQuery);
mysqli_stmt_bind_param($stmt, "s", $booking_id);
mysqli_stmt_execute($stmt);
$showDateResult = mysqli_stmt_get_result($stmt);

if (!$showDateResult || mysqli_num_rows($showDateResult) === 0) {
    error_log("Show date not found for booking_id: $booking_id");
    header("Location: index.php");
    exit;
}

$row = mysqli_fetch_assoc($showDateResult);
$target_show_date = $row['show_date'];

// Step 2: Fetch all bookings for the user on that show_date
$bookingQuery = "
    SELECT b.*, s1.show_date, s1.start_time, m.title, m.poster, m.duration, m.rating, scr.screen_name, s2.seat_number, s2.row_label
    FROM bookings b
    JOIN showtimes s1 ON b.showtime_id = s1.showtime_id
    JOIN movies m ON s1.movie_id = m.movie_id
    JOIN screens scr ON s1.screen_id = scr.screen_id
    JOIN seats s2 ON b.seat_id = s2.seat_id
    WHERE b.user_id = ? AND s1.show_date = ?
    ORDER BY s1.start_time, scr.screen_name
";
$stmt = mysqli_prepare($conn, $bookingQuery);
mysqli_stmt_bind_param($stmt, "ss", $userID, $target_show_date);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Collect seat info and use the first row for display
$seats = [];
$displayData = null;

if ($result && mysqli_num_rows($result) > 0) {
    while ($booking = mysqli_fetch_assoc($result)) {
        $seats[] = $booking['row_label'] . $booking['seat_number'];

        // Use the first booking row to extract movie/showtime data
        if ($displayData === null) {
            $displayData = $booking;
        }
    }

    $seat_display = implode(', ', $seats);
} else {
    error_log("No bookings found for User ID: $userID on show date: $target_show_date");
    header("Location: index.php");
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - Absolute Cinema</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="/styles/payment.css">
    <style>
        .error-message {
            color: #ff3333;
            background-color: #ffeeee;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
            border: 1px solid #ff3333;
        }
        
        .success-message {
            color: #33aa33;
            background-color: #eeffee;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
            border: 1px solid #33aa33;
        }
        
        .payment-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .payment-title {
            font-size: 24px;
            margin-bottom: 10px;
        }
        
        .payment-subtitle {
            color: #666;
            margin-bottom: 30px;
        }
        
        .payment-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }
        
        .order-summary {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
        }
        
        .movie-ticket {
            display: flex;
            margin-bottom: 20px;
            border-bottom: 1px solid #eee;
            padding-bottom: 20px;
        }
        
        .movie-thumbnail {
            width: 100px;
            flex-shrink: 0;
        }
        
        .movie-thumbnail img {
            width: 100%;
            border-radius: 4px;
        }
        
        .movie-details {
            padding-left: 15px;
        }
        
        .movie-details h3 {
            margin: 0 0 5px 0;
        }
        
        .movie-details p {
            margin: 2px 0;
            color: #666;
            font-size: 14px;
        }
        
        .price-breakdown {
            margin-bottom: 20px;
        }
        
        .price-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        
        .price-row.total {
            font-weight: bold;
            border-top: 1px solid #ddd;
            padding-top: 10px;
            margin-top: 10px;
        }
        
        .pay-button {
            background-color: #e53935;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 4px;
            font-size: 16px;
            width: 100%;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .pay-button:hover {
            background-color: #c62828;
        }
    </style>
</head>
<body>
    <?php include("header.php");?>
    
    <main class="payment-container">
        <h1 class="payment-title">Payment</h1>
        <p class="payment-subtitle">Complete your booking by making payment</p>
        
        <form method="POST" action="payment.php" id="paymentForm">
            <div class="payment-grid">
                <div class="payment-notice">
                    <h2>Simplified Payment Process</h2>
                    <p>This is a demonstration payment page. Click the "Pay Now" button to simulate a booking with static payment information.</p>
                    <p>No payment validation will be performed. This will just display a confirmation of your booking.</p>
                </div>
                
                <div class="order-summary">
                    <h2>Order Summary</h2>
                    <div class="movie-ticket">
                        <div class="movie-thumbnail">
                            <img src="<?php echo htmlspecialchars($booking['poster']); ?>" alt="<?php echo htmlspecialchars($booking['title']); ?> movie poster">
                        </div>
                        <div class="movie-details">
                            <h3><?php echo htmlspecialchars($booking['title']); ?></h3>
                            <p><?php echo htmlspecialchars($booking['rating']); ?> | <?php echo htmlspecialchars($booking['duration']); ?> mins</p>
                            <p><?php echo date('F d', strtotime($booking['show_date'])); ?>, <?php echo date('h:i A', strtotime($booking['start_time'])); ?></p>
                            <p>Screen: <?php echo htmlspecialchars($booking['screen_name']); ?></p>
                            <p>Seats: <?php echo htmlspecialchars($seat_display); ?></p> <!-- Multiple seats -->
                        </div>
                    
                    <div class="price-breakdown">
                        <div class="price-row">
                            <span>Ticket</span>
                            <span>₱100.00</span>
                        </div>
                        <div class="price-row total">
                            <span>Total</span>
                            <span>₱100.00</span>
                        </div>
                    </div>
                    
                    <button type="submit" name="submit_payment" class="pay-button">Pay ₱100.00</button>
                </div>
            </div>
        </form>
    </main>
    
    <?php include("footer.php");?>
</body>
</html>
</html>