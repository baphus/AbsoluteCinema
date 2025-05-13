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

// Check if form was submitted (payment button clicked)
if (isset($_POST['submit_payment']) && isset($_POST['booking_id'])) {
    $booking_id = $_POST['booking_id'];
    
    // Update booking status to 'confirmed' or 'paid'
    $updateQuery = "UPDATE bookings SET status = 'confirmed' WHERE booking_id = ? AND user_id = ?";
    $stmt = mysqli_prepare($conn, $updateQuery);
    mysqli_stmt_bind_param($stmt, "ss", $booking_id, $userID);
    $result = mysqli_stmt_execute($stmt);
    
    if ($result) {
        // Redirect to confirmation page with booking id
        header("Location: confirmation.php?booking_id=" . urlencode($booking_id));
        exit;
    } else {
        // Error handling
        $error_message = "Failed to process payment. Please try again.";
    }
}

// If no booking_id in URL, redirect to index
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
// FIXED QUERY: Based on the actual table structure without seat-booking direct relationship
$bookingQuery = "
    SELECT b.*, s1.show_date, s1.start_time, m.title, m.poster, m.duration, m.rating, scr.screen_name
        FROM bookings b
        JOIN showtimes s1 ON b.showtime_id = s1.showtime_id
        JOIN movies m ON s1.movie_id = m.movie_id
        JOIN screens scr ON s1.screen_id = scr.screen_id
        WHERE b.user_id = ? AND s1.show_date = ?
        ORDER BY s1.start_time, scr.screen_name
";
$stmt = mysqli_prepare($conn, $bookingQuery);
mysqli_stmt_bind_param($stmt, "ss", $userID, $target_show_date);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Collect booking info and use the first row for display
$displayData = null;

if ($result && mysqli_num_rows($result) > 0) {
    // Just use the first booking for display
    $displayData = mysqli_fetch_assoc($result);
    
    // Reset the result pointer to process all bookings if needed
    mysqli_data_seek($result, 0);
    
    // For seat display, we'll just show seat count since we don't have individual seat info
    $total_seats = 0;
    while ($booking = mysqli_fetch_assoc($result)) {
        $total_seats += $booking['seat_count'];
    }
    
    $seat_display = $total_seats . " seat(s)";
} else {
    error_log("No bookings found for User ID: $userID on show date: $target_show_date");
    header("Location: index.php");
    exit;
}

// Generate a transaction ID for display purposes
$transaction_id = "TXN" . rand(100000, 999999);
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
        
        <?php if(isset($error_message)): ?>
        <div class="error-message">
            <?php echo htmlspecialchars($error_message); ?>
        </div>
        <?php endif; ?>
        
        <form method="POST" action="payment.php" id="paymentForm">
            <input type="hidden" name="booking_id" value="<?php echo htmlspecialchars($booking_id); ?>">
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
                            <img src="<?php echo htmlspecialchars($displayData['poster']); ?>" alt="<?php echo htmlspecialchars($displayData['title']); ?> movie poster">
                        </div>
                        <div class="movie-details">
                            <h3><?php echo htmlspecialchars($displayData['title']); ?></h3>
                            <p><?php echo htmlspecialchars($displayData['rating']); ?> | <?php echo htmlspecialchars($displayData['duration']); ?> mins</p>
                            <p><?php echo date('F d', strtotime($displayData['show_date'])); ?>, <?php echo date('h:i A', strtotime($displayData['start_time'])); ?></p>
                            <p>Screen: <?php echo htmlspecialchars($displayData['screen_name']); ?></p>
                            <p>Seats: <?php echo htmlspecialchars($seat_display); ?></p> <!-- Multiple seats -->
                        </div>
                    </div>
                    
                    <div class="price-breakdown">
                        <div class="price-row">
                            <span>Tickets (<?php echo htmlspecialchars($displayData['seat_count']); ?>)</span>
                            <span>₱<?php echo number_format($displayData['total_price'], 2); ?></span>
                        </div>
                        <div class="price-row total">
                            <span>Total</span>
                            <span>₱<?php echo number_format($displayData['total_price'], 2); ?></span>
                        </div>
                    </div>
                    
                    <button type="submit" name="submit_payment" class="pay-button">Pay ₱<?php echo number_format($displayData['total_price'], 2); ?></button>
                </div>
            </div>
        </form>
    </main>
    
    <?php include("footer.php");?>
</body>
</html>