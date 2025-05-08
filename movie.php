<?php
session_start();
include("config.php");

if (isset($_GET['movie_id'])) {
    $movie_id = mysqli_real_escape_string($conn, $_GET['movie_id']); 
    
    // Fetch the movie details from the database
    $getMovieQuery = "SELECT * FROM movies WHERE movie_id = '$movie_id'";
    $result = mysqli_query($conn, $getMovieQuery);

    if (!$result) {
        // Handle database query error
        die("Error: " . mysqli_error($conn));
    }

    if (mysqli_num_rows($result) > 0) {
        $movie = mysqli_fetch_assoc($result);
    } else {
        // Redirect to a 404 page or show an error if the movie is not found
        header("Location: 404.php");
        exit;
    }
    
    // Fetch movie showtimes from database
    $today = date("Y-m-d");
    $tomorrow = date("Y-m-d", strtotime("+1 day"));
    
    $getShowtimesQuery = "SELECT s.*, sc.screen_name, sc.screen_type 
                         FROM showtimes s 
                         JOIN screens sc ON s.screen_id = sc.screen_id 
                         WHERE s.movie_id = '$movie_id' 
                         ORDER BY s.show_date, s.start_time";
    
    $showtimesResult = mysqli_query($conn, $getShowtimesQuery);
    
    if (!$showtimesResult) {
        // Handle database query error
        die("Error fetching showtimes: " . mysqli_error($conn));
    }
    
    // Group showtimes by date
    $showtimes = [];
    while ($row = mysqli_fetch_assoc($showtimesResult)) {
        $showtimes[$row['show_date']][] = $row;
    }
    
    // Separate showtimes into today, tomorrow, and future dates
    $todayShowtimes = isset($showtimes[$today]) ? $showtimes[$today] : [];
    $tomorrowShowtimes = isset($showtimes[$tomorrow]) ? $showtimes[$tomorrow] : [];
    
    // All other future dates
    $futureShowtimes = [];
    foreach ($showtimes as $date => $times) {
        if ($date != $today && $date != $tomorrow && $date > $today) {
            $futureShowtimes[$date] = $times;
        }
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
    <title><?php echo htmlspecialchars($movie['title']); ?> - Absolute Cinema</title>
    <link rel="stylesheet" href="styles/movie.css">
    <style>
        .movie-hero {
            background: url('<?php echo htmlspecialchars($movie['banner']); ?>') no-repeat center center;
            background-size: cover;
            height: 300px; 
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-shadow: 0 2px 5px rgba(0, 0, 0, 0.7);
        }
        
        /* New styles for showtimes */
        .date-tab {
            display: inline-block;
            padding: 10px 15px;
            background-color: #f2f2f2;
            cursor: pointer;
            margin-right: 5px;
            border-radius: 5px 5px 0 0;
            font-weight: bold;
        }
        
        .date-tab.active {
            background-color: #e0e0e0;
            border-bottom: 3px solid #ff0000;
        }
        
        .showtimes-content {
            display: none;
            padding: 15px;
            background-color: #f8f8f8;
            border-radius: 0 0 5px 5px;
            margin-bottom: 20px;
        }
        
        .showtimes-content.active {
            display: block;
        }
        
        .book-now-btn {
            display: block;
            width: 100%;
            padding: 12px;
            background-color: #e63946;
            color: white;
            text-align: center;
            text-decoration: none;
            font-weight: bold;
            border-radius: 5px;
            margin-top: 10px;
            transition: background-color 0.3s;
        }
        
        .book-now-btn:hover {
            background-color: #c1121f;
        }
        
        .time-slot {
            display: inline-block;
            padding: 10px 15px;
            margin: 5px;
            background-color: white;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-decoration: none;
            color: #333;
            transition: all 0.3s;
        }
        
        .time-slot:hover {
            background-color: #f0f0f0;
            transform: translateY(-2px);
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .time-slot .screen-type {
            display: block;
            font-size: 0.8em;
            color: #666;
        }
        
        .time-slot .price {
            display: block;
            font-weight: bold;
            margin-top: 5px;
        }
        
        .no-showtimes-message {
            padding: 15px;
            text-align: center;
            color: #666;
            font-style: italic;
        }
    </style>
    <script>
        function openShowtimeTab(evt, dateId) {
            // Hide all showtime content
            var tabcontent = document.getElementsByClassName("showtimes-content");
            for (var i = 0; i < tabcontent.length; i++) {
                tabcontent[i].classList.remove("active");
            }
            
            // Remove "active" class from all tabs
            var tablinks = document.getElementsByClassName("date-tab");
            for (var i = 0; i < tablinks.length; i++) {
                tablinks[i].classList.remove("active");
            }
            
            // Show the selected tab content and add "active" class to the button
            document.getElementById(dateId).classList.add("active");
            evt.currentTarget.classList.add("active");
        }
        
        // Initialize the first tab as active when page loads
        window.onload = function() {
            // Select the first tab by default
            var firstTab = document.querySelector('.date-tab');
            if (firstTab) {
                firstTab.click();
            }
        }
    </script>
</head>
<body>
    <?php include("header.php"); ?>
    
    <div class="movie-hero">
    </div>
    
    <div class="movie-container">
        <div class="movie-info-container">
            <div class="movie-poster">
                <img src="<?php echo htmlspecialchars($movie['poster']); ?>" alt="<?php echo htmlspecialchars($movie['title']); ?> movie poster">
            </div>

            <div class="movie-details">
                <h1 class="movie-title"><?php echo htmlspecialchars($movie['title']); ?></h1>
                <div class="movie-meta">
                    <span class="duration-badge"><?php echo htmlspecialchars($movie['rating']); ?></span>
                    <span class="genre"><?php echo htmlspecialchars($movie['duration']); ?> mins</span>
                    <span class="genre"><?php echo htmlspecialchars($movie['genre']); ?></span>
                </div>

                <div class="action-buttons">
                    <a href="#showtimes-section" class="btn btn-primary">View Showtimes</a>
                    <a href='<?php echo $movie['trailer-url'];?>' class="btn btn-secondary">Watch Trailer</a>
                </div>
            </div>
        </div>

        <div class="content-grid">
            <div class="left-column">
                <div class="summary-box">
                    <h2>Summary</h2>
                    <p><?php echo nl2br(htmlspecialchars($movie['description'])); ?></p>
                </div>

                <div class="movie-info-box">
                    <h2>Movie Information</h2>
                    <table class="movie-info-table">
                        <tr>
                            <td>Title</td>
                            <td><?php echo htmlspecialchars($movie['title']); ?></td>
                        </tr>
                        <tr>
                            <td>Genre</td>
                            <td><?php echo htmlspecialchars($movie['genre']); ?></td>
                        </tr>
                        <tr>
                            <td>Duration</td>
                            <td><?php echo htmlspecialchars($movie['duration']); ?> minutes</td>
                        </tr>
                        <tr>
                            <td>Rating</td>
                            <td><?php echo htmlspecialchars($movie['rating']); ?></td>
                        </tr>
                        <tr>
                            <td>Director</td>
                            <td><?php echo htmlspecialchars($movie['director']); ?></td>
                        </tr>
                        <tr>
                            <td>Release Date</td>
                            <td><?php echo htmlspecialchars(date("F j, Y", strtotime($movie['release_date']))); ?></td>
                        </tr>
                        <tr>
                            <td>Status</td>
                            <td><?php echo htmlspecialchars($movie['status']); ?></td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="right-column">
                <div id="showtimes-section" class="showtimes-box">
                    <h2>Showtimes</h2>
                    
                    <?php if (empty($todayShowtimes) && empty($tomorrowShowtimes) && empty($futureShowtimes)): ?>
                        <div class="no-showtimes-message">No showtimes available for this movie.</div>
                    <?php else: ?>
                        <div class="date-tabs">
                            <?php if (!empty($todayShowtimes)): ?>
                                <div class="date-tab" onclick="openShowtimeTab(event, 'today-showtimes')">Today</div>
                            <?php endif; ?>
                            
                            <?php if (!empty($tomorrowShowtimes)): ?>
                                <div class="date-tab" onclick="openShowtimeTab(event, 'tomorrow-showtimes')">Tomorrow</div>
                            <?php endif; ?>
                            
                            <?php if (!empty($futureShowtimes)): ?>
                                <div class="date-tab" onclick="openShowtimeTab(event, 'future-showtimes')">Future Dates</div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Today's showtimes -->
                        <?php if (!empty($todayShowtimes)): ?>
                            <div id="today-showtimes" class="showtimes-content">
                                <h3>Today - <?php echo date("F j", strtotime($today)); ?></h3>
                                <div class="showtimes-grid">
                                    <?php foreach ($todayShowtimes as $time): ?>
                                        <div class="time-slot">
                                            <?php echo date("g:i A", strtotime($time['start_time'])); ?>
                                            <span class="screen-type"><?php echo htmlspecialchars($time['screen_type']); ?></span>
                                            <span class="price">$<?php echo htmlspecialchars(number_format($time['price'], 2)); ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <a href="booking.php?movie_id=<?php echo urlencode($movie_id); ?>" class="book-now-btn">Book Now</a>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Tomorrow's showtimes -->
                        <?php if (!empty($tomorrowShowtimes)): ?>
                            <div id="tomorrow-showtimes" class="showtimes-content">
                                <h3>Tomorrow - <?php echo date("F j", strtotime($tomorrow)); ?></h3>
                                <div class="showtimes-grid">
                                    <?php foreach ($tomorrowShowtimes as $time): ?>
                                        <div class="time-slot">
                                            <?php echo date("g:i A", strtotime($time['start_time'])); ?>
                                            <span class="screen-type"><?php echo htmlspecialchars($time['screen_type']); ?></span>
                                            <span class="price">$<?php echo htmlspecialchars(number_format($time['price'], 2)); ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <a href="booking.php?movie_id=<?php echo urlencode($movie_id); ?>" class="book-now-btn">Book Now</a>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Future dates showtimes -->
                        <?php if (!empty($futureShowtimes)): ?>
                            <div id="future-showtimes" class="showtimes-content">
                                <?php foreach ($futureShowtimes as $date => $times): ?>
                                    <h3><?php echo date("l, F j", strtotime($date)); ?></h3>
                                    <div class="showtimes-grid">
                                        <?php foreach ($times as $time): ?>
                                            <div class="time-slot">
                                                <?php echo date("g:i A", strtotime($time['start_time'])); ?>
                                                <span class="screen-type"><?php echo htmlspecialchars($time['screen_type']); ?></span>
                                                <span class="price">$<?php echo htmlspecialchars(number_format($time['price'], 2)); ?></span>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <a href="booking.php?movie_id=<?php echo urlencode($movie_id); ?>" class="book-now-btn">Book Now</a>
                                    <hr style="margin: 20px 0;">
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php include("footer.php"); ?>
</body>
</html>