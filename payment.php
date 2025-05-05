<?php 
session_start();
include("config.php");

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    error_log("User ID not set in session. Redirecting to login.");
    header("Location: login.php");
    exit;
} else {
    $userID = $_SESSION['user_id'];
    error_log("User ID: $userID");
}

// Check if booking_id is set in the session
if (!isset($_SESSION['booking_id'])) {
    error_log("No booking ID in session. Redirecting to index.");
    header("Location: index.php");
    exit;
}

$booking_id = $_SESSION['booking_id'];
$total_price = isset($_SESSION['total_price']) ? $_SESSION['total_price'] : 0;

// Fetch booking details
$bookingQuery = "
    SELECT b.*, s.show_date, s.start_time, m.title, m.poster, m.duration, m.rating, scr.screen_name
    FROM bookings b
    JOIN showtimes s ON b.showtime_id = s.showtime_id
    JOIN movies m ON s.movie_id = m.movie_id
    JOIN screens scr ON s.screen_id = scr.screen_id
    WHERE b.booking_id = ? AND b.user_id = ?";
$stmt = mysqli_prepare($conn, $bookingQuery);
mysqli_stmt_bind_param($stmt, "ss", $booking_id, $userID);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$result || mysqli_num_rows($result) === 0) {
    error_log("Booking not found for ID: $booking_id and User ID: $userID");
    header("Location: index.php");
    exit;
}

$booking = mysqli_fetch_assoc($result);

// Fetch user information
$userQuery = "SELECT * FROM users WHERE user_id = ?";
$stmt = mysqli_prepare($conn, $userQuery);
mysqli_stmt_bind_param($stmt, "s", $userID);
mysqli_stmt_execute($stmt);
$userResult = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($userResult);

// Fetch selected seats
$seatsQuery = "
    SELECT s.row_label, s.seat_number
    FROM booking_seats bs
    JOIN seats s ON bs.seat_id = s.seat_id
    WHERE bs.booking_id = ?
    ORDER BY s.row_label, s.seat_number";
$stmt = mysqli_prepare($conn, $seatsQuery);
mysqli_stmt_bind_param($stmt, "s", $booking_id);
mysqli_stmt_execute($stmt);
$seatsResult = mysqli_stmt_get_result($stmt);

$seats = [];
while ($seat = mysqli_fetch_assoc($seatsResult)) {
    $seats[] = $seat['row_label'] . $seat['seat_number'];
}
$seatsString = implode(', ', $seats);
$ticket_count = count($seats);

// Calculate breakdown
$ticket_price_per_seat = 200; // Base ticket price
$booking_fee = 50; // Fixed booking fee
$tax_rate = 0.12; // 12% tax
$subtotal = $ticket_price_per_seat * $ticket_count;
$tax = $subtotal * $tax_rate;
$total = $subtotal + $booking_fee + $tax;

// Process payment submission - simplified to just create a payment record
$payment_success = false;
$payment_error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_payment'])) {
    // Start transaction
    mysqli_begin_transaction($conn);
    
    try {
        // Update booking status to 'confirmed'
        $updateBookingQuery = "UPDATE bookings SET status = 'confirmed', total_price = ? WHERE booking_id = ?";
        $stmt = mysqli_prepare($conn, $updateBookingQuery);
        mysqli_stmt_bind_param($stmt, "ds", $total, $booking_id);
        mysqli_stmt_execute($stmt);
        
        // Create a payment record with static data
        $payment_id = 'PAY' . time() . rand(1000, 9999);
        $card_number = '4111111111111111'; // Example static card number
        $cardholder_name = $user['first_name'] . ' ' . $user['last_name'];
        $expiry_date = '12/28';
        $cvv = '123';
        $billing_first_name = $user['first_name'];
        $billing_last_name = $user['last_name'];
        $billing_email = $user['email'];
        $billing_address = '123 Main St';
        $billing_city = 'Metro Manila';
        $billing_zip_code = '1000';
        $billing_country = 'Philippines';
        $status = 'Paid';
        
        // Insert payment record
        $insertPaymentQuery = "
            INSERT INTO payments (
                payment_id, booking_id, card_number, cardholder_name, expiry_date, cvv,
                billing_first_name, billing_last_name, billing_email, billing_address,
                billing_city, billing_zip_code, billing_country, amount_paid, payment_date, status
            ) VALUES (
                ?, ?, ?, ?, ?, ?,
                ?, ?, ?, ?,
                ?, ?, ?, ?, NOW(), ?
            )";
        
        $stmt = mysqli_prepare($conn, $insertPaymentQuery);
        mysqli_stmt_bind_param($stmt, "ssssssssssssds", 
            $payment_id, $booking_id, $card_number, $cardholder_name, $expiry_date, $cvv,
            $billing_first_name, $billing_last_name, $billing_email, $billing_address,
            $billing_city, $billing_zip_code, $billing_country, $total, $status
        );
        mysqli_stmt_execute($stmt);
        
        // Commit transaction
        mysqli_commit($conn);
        
        // Set success flag and redirect
        $payment_success = true;
        
        // Redirect to confirmation page
        header("Location: ticket.php?booking_id=$booking_id");
        exit;
        
    } catch (Exception $e) {
        // Rollback transaction on error
        mysqli_rollback($conn);
        error_log("Payment processing failed: " . $e->getMessage());
        $payment_error = "There was an error processing your payment: " . $e->getMessage();
    }
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
        
        <?php if ($payment_error): ?>
            <div class="error-message"><?php echo htmlspecialchars($payment_error); ?></div>
        <?php endif; ?>
        
        <?php if ($payment_success): ?>
            <div class="success-message">Payment successful! Redirecting to your tickets...</div>
        <?php else: ?>

        <form method="POST" action="payment.php" id="paymentForm">
            <div class="payment-grid">
                <div class="payment-notice">
                    <h2>Simplified Payment Process</h2>
                    <p>This is a demonstration payment page. Click the "Pay Now" button to create a booking with static payment information.</p>
                    <p>No payment validation will be performed. This will create a booking record with "confirmed" status and generate a payment record in the database.</p>
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
                            <p>Seats: <?php echo htmlspecialchars($seatsString); ?></p>
                        </div>
                    </div>
                    
                    <div class="price-breakdown">
                        <div class="price-row">
                            <span>Ticket (<?php echo $ticket_count; ?>)</span>
                            <span>₱<?php echo number_format($subtotal, 2); ?></span>
                        </div>
                        <div class="price-row">
                            <span>Booking Fee</span>
                            <span>₱<?php echo number_format($booking_fee, 2); ?></span>
                        </div>
                        <div class="price-row">
                            <span>Tax (12%)</span>
                            <span>₱<?php echo number_format($tax, 2); ?></span>
                        </div>
                        <div class="price-row total">
                            <span>Total</span>
                            <span>₱<?php echo number_format($total, 2); ?></span>
                        </div>
                    </div>
                    
                    <button type="submit" name="submit_payment" class="pay-button">Pay ₱<?php echo number_format($total, 2); ?></button>
                </div>
            </div>
        </form>
        <?php endif; ?>
    </main>
    
    <?php include("footer.php");?>
</body>
</html>