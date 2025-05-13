<?php
session_start();
include("config.php");

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo("User ID not set in session. Redirecting to login.");
    header("Location: login.php");
    exit;
} else {
    $userID = $_SESSION['user_id'];
}

// Check if movie_id is provided in the URL
if (isset($_GET['movie_id']) && !empty($_GET['movie_id'])) {
    $movie_id = mysqli_real_escape_string($conn, $_GET['movie_id']);

    // Fetch the movie details
    $movieQuery = "SELECT * FROM movies WHERE movie_id = ?";
    $stmt = mysqli_prepare($conn, $movieQuery);
    mysqli_stmt_bind_param($stmt, "s", $movie_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result && mysqli_num_rows($result) > 0) {
        $movie = mysqli_fetch_assoc($result);

        // Fetch available showtimes for this movie
        $showtimeQuery = "
            SELECT s.showtime_id, s.show_date, s.start_time, s.price, scr.screen_name, scr.screen_id 
            FROM showtimes s
            JOIN screens scr ON s.screen_id = scr.screen_id
            WHERE s.movie_id = ? AND s.status = 'available' 
            AND s.show_date >= CURDATE()
            ORDER BY s.show_date, s.start_time";
        $stmt = mysqli_prepare($conn, $showtimeQuery);
        mysqli_stmt_bind_param($stmt, "s", $movie_id);
        mysqli_stmt_execute($stmt);
        $showtimeResult = mysqli_stmt_get_result($stmt);
        $showtimes = [];
        while ($row = mysqli_fetch_assoc($showtimeResult)) {
            $showtimes[] = $row;
        }

        // Fetch available screens for this movie
        $screenQuery = "
            SELECT DISTINCT scr.screen_id, scr.screen_name 
            FROM screens scr
            JOIN showtimes s ON scr.screen_id = s.screen_id
            WHERE s.movie_id = ? AND s.status = 'available' AND scr.status = 'active' AND s.show_date >= CURDATE()
            ORDER BY scr.screen_name";
        $stmt = mysqli_prepare($conn, $screenQuery);
        mysqli_stmt_bind_param($stmt, "s", $movie_id);
        mysqli_stmt_execute($stmt);
        $screenResult = mysqli_stmt_get_result($stmt);
        $screens = [];
        while ($row = mysqli_fetch_assoc($screenResult)) {
            $screens[] = $row;
        }
    } else {
        header("Location: 404.php");
        exit;
    }
} else {
    header("Location: index.php");
    exit;
}

// Handle POST request for screen selection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['screen_id'])) {
    $screen_id = mysqli_real_escape_string($conn, $_POST['screen_id']);

    $showtimeQuery = "
        SELECT s.showtime_id, s.show_date, s.start_time, s.price, scr.screen_name 
        FROM showtimes s
        JOIN screens scr ON s.screen_id = scr.screen_id
        WHERE s.screen_id = ? AND s.movie_id = ? AND s.status = 'available' 
        AND s.show_date >= CURDATE()
        ORDER BY s.show_date, s.start_time";
    $stmt = mysqli_prepare($conn, $showtimeQuery);
    mysqli_stmt_bind_param($stmt, "ss", $screen_id, $movie_id);
    mysqli_stmt_execute($stmt);
    $showtimeResult = mysqli_stmt_get_result($stmt);

    $showtimes = [];
    while ($row = mysqli_fetch_assoc($showtimeResult)) {
        $showtimes[] = $row;
    }
}

// Handle POST request for showtime selection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['showtime_id'])) {
    $showtime_id = mysqli_real_escape_string($conn, $_POST['showtime_id']);

    // Fetch selected showtime details including price
    $selectedShowtimeQuery = "
        SELECT s.showtime_id, s.show_date, s.start_time, s.price, scr.screen_name 
        FROM showtimes s
        JOIN screens scr ON s.screen_id = scr.screen_id
        WHERE s.showtime_id = ?";
    $stmt = mysqli_prepare($conn, $selectedShowtimeQuery);
    mysqli_stmt_bind_param($stmt, "s", $showtime_id);
    mysqli_stmt_execute($stmt);
    $showtimeResult = mysqli_stmt_get_result($stmt);
    $selectedShowtime = mysqli_fetch_assoc($showtimeResult);

    // Fetch available seats for the selected showtime
    $seatQuery = "
        SELECT seat_id, row_label, seat_number, status 
        FROM seats 
        WHERE screen_id = (SELECT screen_id FROM showtimes WHERE showtime_id = ?) 
        AND status IN ('available', 'occupied') 
        ORDER BY row_label, seat_number";
    $stmt = mysqli_prepare($conn, $seatQuery);
    mysqli_stmt_bind_param($stmt, "s", $showtime_id);
    mysqli_stmt_execute($stmt);
    $seatResult = mysqli_stmt_get_result($stmt);

    $seats = [];
    while ($seat = mysqli_fetch_assoc($seatResult)) {
        $seats[] = $seat;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_booking'])) {
    $showtime_id = mysqli_real_escape_string($conn, $_POST['selected_showtime_id']);
    $selected_seats = isset($_POST['selected_seat_ids']) ? $_POST['selected_seat_ids'] : [];

    if (empty($selected_seats)) {
        $error_message = "Please select at least one seat before continuing.";
    } else {
        // Get the ticket price from showtime
        $priceQuery = "SELECT price FROM showtimes WHERE showtime_id = ?";
        $stmt = mysqli_prepare($conn, $priceQuery);
        mysqli_stmt_bind_param($stmt, "s", $showtime_id);
        mysqli_stmt_execute($stmt);
        $priceResult = mysqli_stmt_get_result($stmt);
        $priceRow = mysqli_fetch_assoc($priceResult);
        $ticket_price_per_seat = $priceRow['price'];
        
        $ticket_count = count($selected_seats);
        $booking_fee = 50;
        $total_price = ($ticket_price_per_seat * $ticket_count) + $booking_fee;
        $individual_price = $ticket_price_per_seat + ($booking_fee / $ticket_count);

        try {
            // Start transaction only once
            mysqli_begin_transaction($conn);

            $booking_id = uniqid("BKNG#");
            $escaped_booking_id = mysqli_real_escape_string($conn, $booking_id);
            $escaped_user_id = mysqli_real_escape_string($conn, $userID);
            $escaped_showtime_id = mysqli_real_escape_string($conn, $showtime_id);

            $seat_count = count($selected_seats);
            $total_price = $seat_count * $individual_price;

            // Insert single booking with seat_count
            $insertBookingQuery = "
                INSERT INTO bookings (booking_id, user_id, showtime_id, seat_count, booking_date, total_price, status) 
                VALUES ('$escaped_booking_id', '$escaped_user_id', '$escaped_showtime_id', $seat_count, NOW(), $total_price, 'pending')
            ";

            if (!mysqli_query($conn, $insertBookingQuery)) {
                throw new Exception("Booking insert failed: " . mysqli_error($conn));
            }

            // Update each selected seat to 'occupied'
            $updateSeatQuery = "UPDATE seats SET status = 'occupied' WHERE seat_id = ?";
            $stmt = mysqli_prepare($conn, $updateSeatQuery);
            if (!$stmt) {
                throw new Exception("Prepare failed for seat update: " . mysqli_error($conn));
            }

            foreach ($selected_seats as $seat_id) {
                $escaped_seat_id = mysqli_real_escape_string($conn, $seat_id);
                mysqli_stmt_bind_param($stmt, "s", $escaped_seat_id);
                if (!mysqli_stmt_execute($stmt)) {
                    throw new Exception("Seat update failed for seat $escaped_seat_id: " . mysqli_stmt_error($stmt));
                }
            }

            mysqli_commit($conn);

            // Redirect to payment page
            header("Location: payment.php?booking_id=" . urlencode($escaped_booking_id));
            exit;

        } catch (Exception $e) {
            mysqli_rollback($conn);
            $error_message = "Booking error: " . $e->getMessage();
        }
    }
}   
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Tickets - <?php echo htmlspecialchars($movie['title']); ?> - Absolute Cinema</title>
    <link rel="stylesheet" href="styles/booking.css"> 
</head>
<body>
<?php include("header.php") ?>

    <div class="container">
        <h1 class="page-title">Book tickets for: <?php echo htmlspecialchars($movie['title']); ?></h1>
        <p class="movie-meta"><?php echo htmlspecialchars($movie['genre']); ?> | <?php echo htmlspecialchars($movie['duration']); ?> mins | <?php echo htmlspecialchars($movie['rating']); ?></p>

        <?php if (isset($error_message)): ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <div class="booking-grid">
            <div class="left-column">
                <!-- Screen Selection -->
                <div class="booking-section">
                    <form method="POST" action="booking.php?movie_id=<?php echo urlencode($movie_id); ?>">
                        <h2>Select Screen</h2>
                        <label for="screen-dropdown">Available Screens:</label>
                        <select id="screen-dropdown" name="screen_id" required onchange="this.form.submit()">
                            <option value="">Select a screen</option>
                            <?php foreach ($screens as $screen): ?>
                                <option value="<?php echo htmlspecialchars($screen['screen_id']); ?>" 
                                    <?php echo (isset($_POST['screen_id']) && $_POST['screen_id'] == $screen['screen_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($screen['screen_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </div>
                            
                <!-- Showtime Selection -->
                <div class="booking-section">
                    <?php if (isset($_POST['screen_id'])): ?>
                        <form method="POST" action="booking.php?movie_id=<?php echo urlencode($movie_id); ?>">
                            <input type="hidden" name="screen_id" value="<?php echo htmlspecialchars($_POST['screen_id']); ?>">
                            <h2>Select Date & Time</h2>
                            <label for="datetime-dropdown">Available Showtimes:</label>
                            <select id="datetime-dropdown" name="showtime_id" required onchange="this.form.submit()">
                                <option value="">Select a date & time</option>
                                <?php foreach ($showtimes as $showtime): ?>
                                    <option value="<?php echo htmlspecialchars($showtime['showtime_id']); ?>" 
                                        <?php echo (isset($_POST['showtime_id']) && $_POST['showtime_id'] == $showtime['showtime_id']) ? 'selected' : ''; ?>>
                                        <?php echo date('M d', strtotime($showtime['show_date'])) . ' - ' . 
                                                   date('h:i A', strtotime($showtime['start_time'])) . ' - ' . 
                                                   htmlspecialchars($showtime['screen_name']) . ' - ₱' .
                                                   htmlspecialchars($showtime['price']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </form>
                    <?php endif; ?>
                </div>

                <!-- Ticket Count and Seat Selection -->
                <div class="booking-section">
                    <?php if (isset($error_message)): ?>
                        <div class="error-message" style="color: red; background-color: #ffeeee; padding: 10px; margin-bottom: 15px; border-radius: 4px; border: 1px solid #ffaaaa;">
                            <?php echo $error_message; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($_POST['showtime_id'])): ?>
                        <form id="bookingForm" method="POST" action="booking.php?movie_id=<?php echo urlencode($movie_id); ?>">
                            <input type="hidden" name="selected_showtime_id" value="<?php echo htmlspecialchars($_POST['showtime_id']); ?>">
                            <input type="hidden" name="screen_id" value="<?php echo htmlspecialchars($_POST['screen_id']); ?>">
                            <input type="hidden" id="ticket-price" value="<?php echo htmlspecialchars($selectedShowtime['price']); ?>">
                            
                            <h2>Select Seats</h2>
                            <label for="ticket-count">Number of Tickets:</label>
                            <input type="number" id="ticket-count" name="ticket_count" min="1" max="<?php echo count(array_filter($seats, function($seat) { return $seat['status'] === 'available'; })); ?>" value="1" required>
                            <p><strong>Note:</strong> You can select up to the number of tickets entered.</p>
                            
                            <div id="seat-layout">
                                <?php
                                if (!empty($seats)) {
                                    foreach ($seats as $seat) {
                                        $isOccupied = $seat['status'] === 'occupied'; // Check if the seat is occupied
                                        echo '<div class="seat">';
                                        echo '<input type="checkbox" id="seat_' . htmlspecialchars($seat['seat_id']) . '" name="selected_seat_ids[]" value="' . htmlspecialchars($seat['seat_id']) . '" class="seat-checkbox" ' . ($isOccupied ? 'disabled' : '') . '>';
                                        echo '<label for="seat_' . htmlspecialchars($seat['seat_id']) . '" class="' . ($isOccupied ? 'occupied' : '') . '">' . htmlspecialchars($seat['row_label'] . $seat['seat_number']) . '</label>';
                                        echo '</div>';
                                    }
                                } else {
                                    echo '<p>No available seats for this showtime.</p>';
                                }
                                ?>
                            </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Booking Summary -->
            <div class="right-column">
                <div class="booking-summary">
                    <h2>Booking Summary</h2>
                    <div class="movie-thumbnail">
                        <div class="movie-thumb">
                            <img src="<?php echo htmlspecialchars($movie['poster']); ?>" alt="<?php echo htmlspecialchars($movie['title']); ?> thumbnail">
                        </div>
                        <div class="movie-thumb-details">
                            <div class="movie-thumb-title"><?php echo htmlspecialchars($movie['title']); ?></div>
                            <div><?php echo htmlspecialchars($movie['rating']); ?> | <?php echo htmlspecialchars($movie['duration']); ?> mins</div>
                        </div>
                    </div>

                    <div class="booking-details">
                        <p><strong>Date:</strong> <span id="selectedDate"><?php echo isset($_POST['showtime_id']) ? date('M d, Y', strtotime($selectedShowtime['show_date'])) : 'N/A'; ?></span></p>
                        <p><strong>Time:</strong> <span id="selectedTime"><?php echo isset($_POST['showtime_id']) ? date('h:i A', strtotime($selectedShowtime['start_time'])) : 'N/A'; ?></span></p>
                        <p><strong>Screen:</strong> <span id="selectedScreen"><?php echo isset($_POST['showtime_id']) ? htmlspecialchars($selectedShowtime['screen_name']) : 'N/A'; ?></span></p>
                        <?php if (isset($_POST['showtime_id'])): ?>
                        <p><strong>Ticket Price:</strong> ₱<?php echo htmlspecialchars($selectedShowtime['price']); ?> per ticket</p>
                        <?php endif; ?>
                    </div>

                    <div class="divider"></div>

                    <div class="seats-selection">
                        <p><strong>Selected Seat/s:</strong></p>
                        <p id="selectedSeatsText">No seat selected</p>
                    </div>

                    <div class="divider"></div>

                    <div class="price-breakdown">
                        <div class="price-row">
                            <span>Ticket (<span id="ticketCountDisplay">0</span>)</span>
                            <span id="ticketPrice">₱0.00</span>
                        </div>
                        <div class="price-row">
                            <span>Booking Fee</span>
                            <span>₱50.00</span>
                        </div>
                        <div class="price-total">
                            <span>Total</span>
                            <span id="totalPrice">₱50.00</span>
                        </div>
                    </div>
                                
                    <?php if (isset($_POST['showtime_id'])): ?>
                        <button type="submit" name="submit_booking" class="payment-button" id="continueButton" disabled> Continue to Payment</button>
                    </form>
                    <?php else: ?>
                        <button class="payment-button disabled" disabled>Continue to Payment</button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php include("footer.php"); ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
        const ticketCountInput = document.getElementById('ticket-count');
        const seatCheckboxes = document.querySelectorAll('.seat-checkbox');
        const selectedSeatsText = document.getElementById('selectedSeatsText');
        const ticketCountDisplay = document.getElementById('ticketCountDisplay');
        const ticketPrice = document.getElementById('ticketPrice');
        const totalPrice = document.getElementById('totalPrice');
        const continueButton = document.getElementById('continueButton');
        const bookingFee = 50; // Fixed booking fee
        const ticketPricePerSeat = parseFloat(document.getElementById('ticket-price')?.value || 0);

        // Update selected seats in the booking summary
        function updateSelectedSeats() {
            const selectedSeats = Array.from(seatCheckboxes)
                .filter(checkbox => checkbox.checked)
                .map(checkbox => checkbox.nextElementSibling.textContent.trim());
            selectedSeatsText.textContent = selectedSeats.length > 0 ? selectedSeats.join(', ') : 'No seat selected';

            // Update ticket count and price
            const selectedCount = selectedSeats.length;
            ticketCountDisplay.textContent = selectedCount;
            ticketPrice.textContent = `₱${(selectedCount * ticketPricePerSeat).toFixed(2)}`;
            totalPrice.textContent = `₱${(selectedCount * ticketPricePerSeat + bookingFee).toFixed(2)}`;
            
            // Enable or disable the continue button based on seat selection
            if (continueButton) {
                continueButton.disabled = selectedCount === 0;
            }
        }

        // Enforce ticket count limit
        function enforceTicketLimit() {
            const ticketCount = parseInt(ticketCountInput.value, 10) || 1;
            const selectedCheckboxes = Array.from(seatCheckboxes).filter(cb => cb.checked);
            const selectedCount = selectedCheckboxes.length;

            // Enable all seats first (except permanently occupied ones)
            seatCheckboxes.forEach(checkbox => {
                // Don't change status of seats that are permanently disabled (occupied)
                if (!checkbox.hasAttribute('data-occupied')) {
                    // Always allow selected seats to be deselected
                    checkbox.disabled = false;
                }
            });

            // Then disable only unselected seats if we've reached the ticket limit
            if (selectedCount >= ticketCount) {
                seatCheckboxes.forEach(checkbox => {
                    // Only disable unselected seats, leave selected seats enabled so they can be deselected
                    if (!checkbox.checked && !checkbox.hasAttribute('data-occupied')) {
                        checkbox.disabled = true;
                    }
                });
            }

            updateSelectedSeats();
        }

        // Mark occupied seats (do this once at initialization)
        seatCheckboxes.forEach(checkbox => {
            if (checkbox.disabled) {
                // Add a data attribute to permanently disabled (occupied) seats
                checkbox.setAttribute('data-occupied', 'true');
            }
        });

        // Add listener to all checkboxes and ticket count input
        seatCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', () => {
                enforceTicketLimit();
                updateSelectedSeats();
            });
        });
        
        ticketCountInput.addEventListener('input', () => {
            enforceTicketLimit();
            updateSelectedSeats();
        });

        // Initialize on page load
        enforceTicketLimit();
        updateSelectedSeats();
    });
    </script>
</body>
</html>