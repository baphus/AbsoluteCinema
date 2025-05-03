<?php
include("config.php");

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
    <title><?php echo htmlspecialchars($movie['title']); ?> - Absolute Cinema</title>
    <link rel="stylesheet" href="styles/movie.css">
    <style>
        .movie-hero {
            background: url('<?php echo htmlspecialchars($movie['banner']); ?>') no-repeat center center;
            background-size: cover;
            height: 300px; /* Adjust height as needed */
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-shadow: 0 2px 5px rgba(0, 0, 0, 0.7);
        }
    </style>
</head>
<body>
    <?php include("header.php"); ?>
    <div class="movie-hero"> </div>
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
                    <a href="booking.php?movie_id=<?php echo $movie_id; ?>" class="btn btn-primary">Book Ticket</a>
                    <a href="#" class="btn btn-secondary">Watch Trailer</a>
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
                    </table>
                </div>
            </div>

            <div class="right-column">
                <div class="showtimes-box">
                    <h2>Showtimes</h2>
                    <div class="showtimes-date">Today</div>
                    <div class="showtimes-grid">
                        <div class="time-slot">11:00 AM</div>
                        <div class="time-slot">2:30 PM</div>
                        <div class="time-slot">6:00 PM</div>
                        <div class="time-slot">9:30 PM</div>
                    </div>

                    <div class="showtimes-date">Tomorrow</div>
                    <div class="showtimes-grid">
                        <div class="time-slot">11:00 AM</div>
                        <div class="time-slot">2:30 PM</div>
                        <div class="time-slot">6:00 PM</div>
                        <div class="time-slot">9:30 PM</div>
                    </div>

                    <a href="booking.php?movie_id=<?php echo $movie_id; ?>" class="book-ticket-large">Book Ticket Now</a>
                </div>
            </div>
        </div>
    </div>

    <?php include("footer.php"); ?>
</body>
</html>