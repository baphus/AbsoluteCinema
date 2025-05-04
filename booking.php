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
if (isset($_GET['movie_id'])) {
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
            SELECT s.*, scr.screen_name, scr.screen_id, m.title AS movie_title 
            FROM showtimes s
            JOIN screens scr ON s.screen_id = scr.screen_id
            JOIN movies m ON s.movie_id = m.movie_id
            WHERE s.movie_id = ? AND s.status = 'Available' 
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
            WHERE s.movie_id = ? AND s.status = 'Available' AND s.show_date >= CURDATE()
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
    // Redirect to the homepage or show an error if no movie_id is provided
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Tickets - <?php echo htmlspecialchars($movie['title']); ?> - Absolute Cinema</title>
    <link rel="stylesheet" href="styles/booking.css"> 
    <style>

    </style>
</head>
<body>
<?php include("header.php") ?>

    <div class="container">
        <h1 class="page-title">Book tickets for: <?php echo htmlspecialchars($movie['title']); ?></h1>
        <p class="movie-meta"><?php echo htmlspecialchars($movie['genre']); ?> | <?php echo htmlspecialchars($movie['duration']); ?> mins | <?php echo htmlspecialchars($movie['rating']); ?></p>

        <div class="booking-grid">
            <div class="left-column">
                <div class="booking-section">
                    <h2>Select Screen</h2>
                    <label for="screen-dropdown">Available Screens:</label>
                    <select id="screen-dropdown" name="screen" required>
                        <option value="">Select a screen</option>
                        <?php foreach ($screens as $screen): ?>
                            <option value="<?php echo htmlspecialchars($screen['screen_id']); ?>">
                                <?php echo htmlspecialchars($screen['screen_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="booking-section">
                    <h2>Select Date & Time</h2>
                    <form method="POST" action="booking.php?movie_id=<?php echo htmlspecialchars($movie_id); ?>">
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
                </div>

                <div class="booking-section">
                    <h2>Select Seats</h2>
                    <label for="seat-dropdown">Available Seats:</label>
                    <select id="seat-dropdown" name="seats[]" multiple required>
                        <option value="">Select seats</option>
                        <?php
                        if (isset($_POST['showtime_id'])) {
                            $showtime_id = mysqli_real_escape_string($conn, $_POST['showtime_id']);

                            // Fetch available seats for the selected showtime
                            $seatQuery = "SELECT seat_id, row_label, seat_number 
                                          FROM seats 
                                          WHERE showtime_id = ? AND status = 'available' 
                                          ORDER BY row_label, seat_number";
                            $stmt = mysqli_prepare($conn, $seatQuery);
                            mysqli_stmt_bind_param($stmt, "s", $showtime_id);
                            mysqli_stmt_execute($stmt);
                            $seatResult = mysqli_stmt_get_result($stmt);

                            while ($seat = mysqli_fetch_assoc($seatResult)) {
                                echo '<option value="' . htmlspecialchars($seat['seat_id']) . '">' .
                                     htmlspecialchars($seat['row_label'] . $seat['seat_number']) .
                                     '</option>';
                            }
                        }
                        ?>
                    </select>
                </div>
            </div>

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
                        <p><strong>Date:</strong> <span id="selectedDate">N/A</span></p>
                        <p><strong>Time:</strong> <span id="selectedTime">N/A</span></p>
                        <p><strong>Screen:</strong> <span id="selectedScreen">N/A</span></p>
                    </div>

                    <div class="divider"></div>

                    <div class="seats-selection">
                        <p><strong>Selected Seat/s</strong></p>
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
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const screenDropdown = document.getElementById('screen-dropdown');
            const datetimeDropdown = document.getElementById('datetime-dropdown');
            const seatDropdown = document.getElementById('seat-dropdown');

            // Reset dropdowns
            function resetDropdown(dropdown, placeholder) {
                dropdown.innerHTML = `<option value="">${placeholder}</option>`;
                dropdown.disabled = true;
            }

            // Load showtimes when a screen is selected
            screenDropdown.addEventListener('change', function () {
                const screenId = this.value;

                // Reset dependent dropdowns
                resetDropdown(datetimeDropdown, 'Select a date & time');
                resetDropdown(seatDropdown, 'Select seats');

                if (screenId) {
                    // Enable and populate the date & time dropdown
                    fetch(`get_showtimes.php?screen_id=${screenId}`)
                        .then(response => response.json())
                        .then(showtimes => {
                            datetimeDropdown.disabled = false;
                            showtimes.forEach(showtime => {
                                const option = document.createElement('option');
                                option.value = showtime.showtime_id;
                                option.textContent = `${showtime.show_date} - ${showtime.start_time}`;
                                datetimeDropdown.appendChild(option);
                            });
                        });
                }
            });

            // Load seats when a date & time is selected
            datetimeDropdown.addEventListener('change', function () {
                const showtimeId = this.value;

                // Reset the seat dropdown
                resetDropdown(seatDropdown, 'Select seats');

                if (showtimeId) {
                    // Enable and populate the seat dropdown
                    fetch(`get_seats.php?showtime_id=${showtimeId}`)
                        .then(response => response.json())
                        .then(seats => {
                            seatDropdown.disabled = false;
                            seats.forEach(seat => {
                                const option = document.createElement('option');
                                option.value = seat.seat_id;
                                option.textContent = `${seat.row_label}${seat.seat_number}`;
                                seatDropdown.appendChild(option);
                            });
                        });
                }
            });
        });
    </script>
    <?php include("footer.php"); ?>
</body>
</html>