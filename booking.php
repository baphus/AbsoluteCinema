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
    $movie_id = intval($_GET['movie_id']); // Sanitize the input to prevent SQL injection

    // Fetch the movie details from the database
    $getMovieQuery = "SELECT * FROM movies WHERE movie_id = $movie_id";
    $result = mysqli_query($conn, $getMovieQuery);

    if ($result && mysqli_num_rows($result) > 0) {
        $movie = mysqli_fetch_assoc($result);
    } else {
        // Redirect to a 404 page or show an error if the movie is not found
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
                    <label class="date-label">Date</label>
                    <div class="date-options">
                        <button class="date-option">Apr 26</button>
                        <button class="date-option">Apr 27</button>
                        <button class="date-option">Apr 28</button>
                        <button class="date-option">Apr 29</button>
                        <button class="date-option">Apr 30</button>
                    </div>

                    <label class="time-label">Show Time</label>
                    <div class="time-options">
                        <button class="date-option">11:00 AM</button>
                        <button class="date-option">2:30 PM</button>
                        <button class="date-option">6:00 PM</button>
                        <button class="date-option">9:30 PM</button>
                    </div>
                </div>

                <div class="booking-section">
                    <h2>Select your seat</h2>
                    <label class="tickets-label">Number of Tickets:</label>
                    <select class="tickets-select" id="ticketCount">
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4">4</option>
                        <option value="5">5</option>
                    </select>

                    <label class="seats-label">Available seats</label>
                    <select class="seat-dropdown" id="seatSelect">
                        <option value="">-- Select a seat --</option>
                        <option class="seat-section" disabled>A-section (Premium)</option>
                        <option value="A1">A1</option>
                        <option value="A2">A2</option>
                        <option value="A3">A3</option>
                        <option value="A4">A4</option>
                        <option value="A5">A5</option>
                        <option class="seat-section" disabled>B-section (Premium)</option>
                        <option value="B1">B1</option>
                        <option value="B2">B2</option>
                        <option value="B3">B3</option>
                        <option value="B4">B4</option>
                        <option value="B5">B5</option>
                        <option class="seat-section" disabled>C-section (Standard)</option>
                        <option value="C1">C1</option>
                        <option value="C2">C2</option>
                        <option value="C3">C3</option>
                        <option value="C4">C4</option>
                        <option value="C5">C5</option>
                        <option class="seat-section" disabled>D-section (Standard)</option>
                        <option value="D1">D1</option>
                        <option value="D2">D2</option>
                        <option value="D3">D3</option>
                        <option value="D4">D4</option>
                        <option value="D5">D5</option>
                        <option class="seat-section" disabled>E-section (Standard)</option>
                        <option value="E1">E1</option>
                        <option value="E2">E2</option>
                        <option value="E3">E3</option>
                        <option value="E4">E4</option>
                        <option value="E5">E5</option>
                    </select>

                    <div id="selectedSeatsDisplay" class="screen">
                        <div class="no-seats">No seats selected</div>
                    </div>

                    <div class="seat-info">
                        <div class="seat-info-title">Seat Information:</div>
                        <ul>
                            <li>Rows A-B: Premium seats ($14 each)</li>
                            <li>Rows C-E: Standard seats ($12 each)</li>
                            <li>Some seats are unavailable as they have already been booked</li>
                        </ul>
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