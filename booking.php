<?php
session_start();
include("config.php");

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    error_log("User ID not set in session. Redirecting to login.");
    header("Location: login.php");
    exit;
} else {
    $userID = $_SESSION['user_id'];
    error_log("User ID: $userID");
}

// Check if movie_id is provided in the URL
if (isset($_GET['movie_id']) && !empty($_GET['movie_id'])) {
    $movie_id = mysqli_real_escape_string($conn, $_GET['movie_id']);
    error_log("Movie ID from URL: $movie_id");

    // Fetch the movie details
    $movieQuery = "SELECT * FROM movies WHERE movie_id = ?";
    $stmt = mysqli_prepare($conn, $movieQuery);
    mysqli_stmt_bind_param($stmt, "s", $movie_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result && mysqli_num_rows($result) > 0) {
        $movie = mysqli_fetch_assoc($result);
        error_log("Movie found: " . $movie['title']);

        // Fetch available showtimes for this movie
        $showtimeQuery = "
            SELECT s.showtime_id, s.show_date, s.start_time, scr.screen_name, scr.screen_id 
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
        if (empty($showtimes)) {
            error_log("No showtimes found for movie ID: $movie_id");
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
        if (empty($screens)) {
            error_log("No screens found for movie ID: $movie_id");
        }
    } else {
        error_log("Movie not found for ID: $movie_id");
        header("Location: 404.php");
        exit;
    }
} else {
    error_log("Movie ID is missing or empty in the URL.");
    header("Location: index.php");
    exit;
}

// Handle POST request for screen selection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['screen_id'])) {
    $screen_id = mysqli_real_escape_string($conn, $_POST['screen_id']);
    error_log("Selected Screen ID: $screen_id");

    $showtimeQuery = "
        SELECT s.showtime_id, s.show_date, s.start_time, scr.screen_name 
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
    error_log("Selected Showtime ID: $showtime_id");

    // Fetch available seats for the selected showtime
    $seatQuery = "
        SELECT seat_id, row_label, seat_number 
        FROM seats 
        WHERE screen_id = (SELECT screen_id FROM showtimes WHERE showtime_id = ?) 
        AND status = 'available' 
        ORDER BY row_label, seat_number";
    $stmt = mysqli_prepare($conn, $seatQuery);
    mysqli_stmt_bind_param($stmt, "s", $showtime_id);
    mysqli_stmt_execute($stmt);
    $seatResult = mysqli_stmt_get_result($stmt);

    $seats = [];
    while ($seat = mysqli_fetch_assoc($seatResult)) {
        $seats[] = $seat;
    }

    if (empty($seats)) {
        error_log("No seats found for Showtime ID: $showtime_id");
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
                                                   htmlspecialchars($showtime['screen_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </form>
                    <?php endif; ?>
                </div>

                <!-- Ticket Count and Seat Selection -->
                <div class="booking-section">
                    <?php if (isset($_POST['showtime_id'])): ?>
                        <h2>Select Seats</h2>
                        <label for="ticket-count">Number of Tickets:</label>
                        <input type="number" id="ticket-count" name="ticket_count" min="1" max="<?php echo count($seats); ?>" value="1" required>
                        <p><strong>Note:</strong> You can select up to the number of tickets entered.</p>
                        <div id="seat-layout">
                            <?php
                            if (!empty($seats)) {
                                foreach ($seats as $seat) {
                                    echo '<div class="seat">';
                                    echo '<input type="checkbox" id="seat_' . htmlspecialchars($seat['seat_id']) . '" name="seat_ids[]" value="' . htmlspecialchars($seat['seat_id']) . '" class="seat-checkbox">';
                                    echo '<label for="seat_' . htmlspecialchars($seat['seat_id']) . '">' . htmlspecialchars($seat['row_label'] . $seat['seat_number']) . '</label>';
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
                        <p><strong>Date:</strong> <span id="selectedDate"><?php echo isset($_POST['showtime_id']) ? date('M d, Y', strtotime($showtimes[array_search($_POST['showtime_id'], array_column($showtimes, 'showtime_id'))]['show_date'])) : 'N/A'; ?></span></p>
                        <p><strong>Time:</strong> <span id="selectedTime"><?php echo isset($_POST['showtime_id']) ? date('h:i A', strtotime($showtimes[array_search($_POST['showtime_id'], array_column($showtimes, 'showtime_id'))]['start_time'])) : 'N/A'; ?></span></p>
                        <p><strong>Screen:</strong> <span id="selectedScreen"><?php echo isset($_POST['screen_id']) ? htmlspecialchars($screens[array_search($_POST['screen_id'], array_column($screens, 'screen_id'))]['screen_name']) : 'N/A'; ?></span></p>
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

                    <a href="payment.html" class="payment-button">Continue to Payment</a>
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
            const bookingFee = 50; // Fixed booking fee
            const ticketPricePerSeat = 200; // Example ticket price per seat

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
            }

            // Enforce ticket count limit
            function enforceTicketLimit() {
                const ticketCount = parseInt(ticketCountInput.value, 10) || 1;
                const selectedCount = Array.from(seatCheckboxes).filter(checkbox => checkbox.checked).length;

                seatCheckboxes.forEach(checkbox => {
                    if (selectedCount >= ticketCount) {
                        if (!checkbox.checked) {
                            checkbox.disabled = true;
                        }
                    } else {
                        checkbox.disabled = false;
                    }
                });

                updateSelectedSeats();
            }

            // Add event listeners to seat checkboxes
            seatCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', enforceTicketLimit);
            });

            // Add event listener to ticket count input
            ticketCountInput.addEventListener('input', enforceTicketLimit);

            // Initialize the seat selection and summary
            enforceTicketLimit();
        });
    </script>
</body>
</html>