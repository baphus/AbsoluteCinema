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
        $showtimeQuery = "SELECT s.*, scr.screen_name, scr.screen_id 
                         FROM showtimes s 
                         JOIN screens scr ON s.screen = scr.screen_name 
                         WHERE s.movie_title = ? AND s.status = 'Available' 
                         AND s.date >= CURDATE() 
                         ORDER BY s.date, s.time";
        $stmt = mysqli_prepare($conn, $showtimeQuery);
        mysqli_stmt_bind_param($stmt, "s", $movie['title']);
        mysqli_stmt_execute($stmt);
        $showtimeResult = mysqli_stmt_get_result($stmt);
        $showtimes = [];
        while ($row = mysqli_fetch_assoc($showtimeResult)) {
            $showtimes[] = $row;
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
                    <h2>Select Date & Time</h2>
                    <label class="date-label">Available Showtimes</label>
                    <div class="showtime-options">
                        <?php foreach ($showtimes as $showtime): ?>
                            <div class="showtime-option">
                                <input type="radio" name="showtime" 
                                       id="showtime_<?php echo $showtime['showtime_id']; ?>" 
                                       value="<?php echo $showtime['showtime_id']; ?>"
                                       data-screen="<?php echo htmlspecialchars($showtime['screen_name']); ?>"
                                       data-price="<?php echo htmlspecialchars($showtime['price']); ?>">
                                <label for="showtime_<?php echo $showtime['showtime_id']; ?>">
                                    <?php 
                                        echo date('M d', strtotime($showtime['date'])) . ' - ' . 
                                             date('h:i A', strtotime($showtime['time'])) . ' - ' .
                                             htmlspecialchars($showtime['screen_name']); 
                                    ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="booking-section">
                    <h2>Select your seats</h2>
                    <div id="screen-selection" style="display: none;">
                        <div class="screen-info">
                            <p>Screen: <span id="selected-screen"></span></p>
                            <p>Price per seat: ₱<span id="seat-price">0.00</span></p>
                        </div>
                        
                        <div class="seat-selection">
                            <div id="seat-map" class="seat-map">
                                <!-- Seats will be loaded dynamically -->
                            </div>
                        </div>
                    </div>
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
                        <p><strong>Date:</strong> April 26, 2025</p>
                        <p><strong>Time:</strong> 11:00 AM</p>
                        <p><strong>Screen:</strong> Cinema 2</p>
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
                            <span id="ticketPrice">$0.00</span>
                        </div>
                        <div class="price-row">
                            <span>Booking Fee</span>
                            <span>$1.00</span>
                        </div>
                        <div class="price-row">
                            <span>Tax</span>
                            <span>$0.00</span>
                        </div>
                        <div class="price-total">
                            <span>Total</span>
                            <span id="totalPrice">$1.00</span>
                        </div>

                    <a href="#" class="payment-button">Continue to Payment</a>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const showtimeOptions = document.querySelectorAll('input[name="showtime"]');
            const screenSelection = document.getElementById('screen-selection');
            const selectedScreen = document.getElementById('selected-screen');
            const seatPrice = document.getElementById('seat-price');
            const seatMap = document.getElementById('seat-map');

            showtimeOptions.forEach(option => {
                option.addEventListener('change', function() {
                    selectedScreen.textContent = this.dataset.screen;
                    seatPrice.textContent = parseFloat(this.dataset.price).toFixed(2);
                    screenSelection.style.display = 'block';
                    
                    // Load seats for selected screen
                    fetch(`get_seats.php?screen=${this.dataset.screen}`)
                        .then(response => response.json())
                        .then(seats => {
                            displaySeats(seats);
                        });
                });
            });

            function displaySeats(seats) {
                seatMap.innerHTML = '';
                let currentRow = '';
                let rowDiv;

                seats.forEach(seat => {
                    if (seat.row_label !== currentRow) {
                        currentRow = seat.row_label;
                        rowDiv = document.createElement('div');
                        rowDiv.className = 'seat-row';
                        rowDiv.innerHTML = `<div class="row-label">${currentRow}</div>`;
                        seatMap.appendChild(rowDiv);
                    }

                    const seatButton = document.createElement('button');
                    seatButton.className = `seat ${seat.status.toLowerCase()}`;
                    seatButton.dataset.seatId = seat.seat_id;
                    seatButton.textContent = seat.seat_number;
                    
                    if (seat.status === 'available') {
                        seatButton.addEventListener('click', () => selectSeat(seatButton));
                    }

                    rowDiv.appendChild(seatButton);
                });
            }

            function selectSeat(seatButton) {
                seatButton.classList.toggle('selected');
                updateBookingSummary();
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            const ticketCountSelect = document.getElementById('ticketCount');
            const seatSelect = document.getElementById('seatSelect');
            const selectedSeatsDisplay = document.getElementById('selectedSeatsDisplay');
            const selectedSeatsText = document.getElementById('selectedSeatsText');
            const ticketCountDisplay = document.getElementById('ticketCountDisplay');
            const ticketPrice = document.getElementById('ticketPrice');
            const totalPrice = document.getElementById('totalPrice');
            
            let selectedSeats = [];
            let maxSeats = 1;
            
            // Update maximum seats based on ticket count
            ticketCountSelect.addEventListener('change', function() {
                maxSeats = parseInt(this.value);
                ticketCountDisplay.textContent = maxSeats;
                
                // Remove excess selected seats if ticket count is decreased
                if (selectedSeats.length > maxSeats) {
                    selectedSeats = selectedSeats.slice(0, maxSeats);
                    updateSeatsDisplay();
                    updatePriceCalculation();
                }
            });
            
            // Handle seat selection
            seatSelect.addEventListener('change', function() {
                const selectedSeat = this.value;
                
                if (selectedSeat && !selectedSeats.includes(selectedSeat)) {
                    if (selectedSeats.length < maxSeats) {
                        selectedSeats.push(selectedSeat);
                        updateSeatsDisplay();
                        updatePriceCalculation();
                    } else {
                        alert(`You can only select up to ${maxSeats} seat(s). Please remove a seat first.`);
                    }
                }
                
                // Reset dropdown to default option after selection
                this.selectedIndex = 0;
            });
            
            // Update the visual display of selected seats
            function updateSeatsDisplay() {
                if (selectedSeats.length === 0) {
                    selectedSeatsDisplay.innerHTML = '<div class="no-seats">No seats selected</div>';
                    selectedSeatsText.textContent = 'No seat selected';
                } else {
                    selectedSeatsDisplay.innerHTML = '';
                    selectedSeats.forEach(seat => {
                        const seatElement = document.createElement('div');
                        seatElement.classList.add('seat');
                        seatElement.textContent = seat;
                        seatElement.setAttribute('data-seat', seat);
                        seatElement.addEventListener('click', function() {
                            removeSeat(seat);
                        });
                        selectedSeatsDisplay.appendChild(seatElement);
                    });
                    
                    selectedSeatsText.textContent = selectedSeats.join(', ');
                }
            }
            
            // Remove a seat when clicked
            function removeSeat(seat) {
                selectedSeats = selectedSeats.filter(s => s !== seat);
                updateSeatsDisplay();
                updatePriceCalculation();
            }
            
            // Calculate and update price information
            function updatePriceCalculation() {
                let price = 0;
                
                selectedSeats.forEach(seat => {
                    // Premium seats (rows A-B)
                    if (seat.startsWith('A') || seat.startsWith('B')) {
                        price += 14;
                    } 
                    // Standard seats (rows C-E)
                    else if (seat.startsWith('C') || seat.startsWith('D') || seat.startsWith('E')) {
                        price += 12;
                    }
                });
                
                // Update price displays
                ticketPrice.textContent = `$${price.toFixed(2)}`;
                totalPrice.textContent = `$${(price + 1).toFixed(2)}`; // Adding $1 booking fee
                ticketCountDisplay.textContent = selectedSeats.length;
            }
            
            // Initialize
            updateSeatsDisplay();
        });
    </script>    <?php include("footer.php"); ?>
</body>

</html>