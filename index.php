<?php 
session_start();
include("config.php");

// Fetch Featured Movies
$queryFeatured = "SELECT * FROM movies WHERE status = 'SHOWING' LIMIT 3;";
$resultFeatured = mysqli_query($conn, $queryFeatured);

if (!$resultFeatured) {
  die("Query failed: " . mysqli_error($conn));
}

$featuredMovies = [];
while ($row = mysqli_fetch_assoc($resultFeatured)) {
    $featuredMovies[] = $row;
}

// Fetch Upcoming Movies
$queryUpcoming = "SELECT * FROM movies WHERE status = 'UPCOMING' LIMIT 3;";
$resultUpcoming = mysqli_query($conn, $queryUpcoming);

if (!$resultUpcoming) {
  die("Query failed: " . mysqli_error($conn));
}

$upcomingMovies = [];
while ($row = mysqli_fetch_assoc($resultUpcoming)) {
    $upcomingMovies[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>AbsoluteCinema - Home</title>
  <link rel="stylesheet" href="styles/index.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
<?php include("header.php"); ?>

<section class="hero">
  <h1>Experience Movies Like Never Before</h1>
  <p>Book your tickets online and skip the queues</p>
  <a href="movies.html" class="cta-button">BOOK NOW</a>
</section>

<main>
  <!-- Featured Movies Section -->
  <section>
    <div class="section-header">
      <h2>Featured Movies</h2>
      <a href="all-movies.php" class="view-all">View all</a>
    </div>
    
    <div class="movie-cards">
      <?php if (count($featuredMovies) > 0): ?>
        <?php foreach ($featuredMovies as $movie): ?>
          <div class="movie-card">
            <img src="<?php echo htmlspecialchars($movie['poster']); ?>" alt="<?php echo htmlspecialchars($movie['title']); ?>" class="movie-image">
            <div class="movie-details">
              <h3 class="movie-title"><?php echo htmlspecialchars($movie['title']); ?></h3>
              <p class="movie-info"><?php echo htmlspecialchars($movie['genre']); ?> | <?php echo htmlspecialchars($movie['duration']); ?> mins | <?php echo htmlspecialchars($movie['rating']); ?></p>
              <a href="booking.html?movie=<?php echo urlencode($movie['title']); ?>" class="book-now-btn">Book Now</a>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p>No featured movies available at the moment.</p>
      <?php endif; ?>
    </div>
  </section>

  <!-- Why Choose Us Section -->
  <section class="why-choose-us">
    <h2>Why Choose Us?</h2>
    <div class="benefits">
      <div class="benefit-card">
        <i class="fas fa-film benefit-icon"></i>
        <h3 class="benefit-title">Wide Selection</h3>
        <p class="benefit-text">Choose from a wide variety of movies across all genres.</p>
      </div>
      <div class="benefit-card">
        <i class="fas fa-ticket-alt benefit-icon"></i>
        <h3 class="benefit-title">Easy Booking</h3>
        <p class="benefit-text">Book your tickets online in just a few clicks.</p>
      </div>
      <div class="benefit-card">
        <i class="fas fa-star benefit-icon"></i>
        <h3 class="benefit-title">Premium Experience</h3>
        <p class="benefit-text">Enjoy the best cinematic experience with us.</p>
      </div>
    </div>
  </section>
  <!-- Upcoming Movies Section -->
  <section>
    <div class="section-header">
      <h2>Upcoming Movies</h2>
      <a href="all-movies.php?filter=upcoming" class="view-all">View all</a>
    </div>
    
    <div class="movie-cards">
      <?php if (count($upcomingMovies) > 0): ?>
        <?php foreach ($upcomingMovies as $movie): ?>
          <div class="movie-card">
            <img src="<?php echo htmlspecialchars($movie['poster']); ?>" alt="<?php echo htmlspecialchars($movie['title']); ?>" class="movie-image">
            <div class="movie-details">
              <h3 class="movie-title"><?php echo htmlspecialchars($movie['title']); ?></h3>
              <p class="movie-info"><?php echo htmlspecialchars($movie['genre']); ?> | <?php echo htmlspecialchars($movie['duration']); ?> mins | <?php echo htmlspecialchars($movie['rating']); ?></p>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p>No upcoming movies available at the moment.</p>
      <?php endif; ?>
    </div>
  </section>
</main>

<?php include("footer.php"); ?>
</body>
</html>