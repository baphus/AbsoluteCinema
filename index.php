<?php 
session_start();


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
<?php include("header.php");
?>
  
  <section class="hero">
    <h1>Experience Movies Like Never Before</h1>
    <p>Book your tickets online and skip the queues</p>
    <a href="movies.html" class="cta-button">BOOK NOW</a>
  </section>

  <main>
    <section>
      <div class="section-header">
        <h2>Featured Movies</h2>
        <a href="all-movies.php" class="view-all">View all</a>
      </div>
      
      <div class="movie-cards">
        <div class="movie-card">
          <img src="/styles/images/the-accountant.jpg" alt="The Accountant" class="movie-image">
          <div class="movie-details">
            <h3 class="movie-title">The Accountant</h3>
            <p class="movie-info">Sci-Fi | 148 mins | PG-13</p>
            <a href="booking.html?movie=accountant" class="book-now-btn">Book Now</a>
          </div>
        </div>
        
        <div class="movie-card">
          <img src="/api/placeholder/400/300" alt="Thunderbolts*" class="movie-image">
          <div class="movie-details">
            <h3 class="movie-title">Thunderbolts*</h3>
            <p class="movie-info">Sci-Fi | 148 mins | PG-13</p>
            <a href="booking.html?movie=thunderbolts" class="book-now-btn">Book Now</a>
          </div>
        </div>
        
        <div class="movie-card">
          <img src="/api/placeholder/400/300" alt="Flow" class="movie-image">
          <div class="movie-details">
            <h3 class="movie-title">Flow</h3>
            <p class="movie-info">Sci-Fi | 148 mins | PG-13</p>
            <a href="booking.html?movie=flow" class="book-now-btn">Book Now</a>
          </div>
        </div>
      </div>
    </section>
    
    <section class="why-choose-us">
      <h2>Why Choose Us</h2>
      <div class="benefits">
        <div class="benefit-card">
          <div class="benefit-icon">
            <i class="fas fa-ticket-alt"></i>
          </div>
          <h3 class="benefit-title">Easy Booking</h3>
          <p class="benefit-text">Book your movie tickets in just a few clicks. No more waiting in line!</p>
        </div>
        
        <div class="benefit-card">
          <div class="benefit-icon">
            <i class="fas fa-film"></i>
          </div>
          <h3 class="benefit-title">Show off your taste</h3>
          <p class="benefit-text">Reveal your ticket history. Show off your movie taste!</p>
        </div>
        
        <div class="benefit-card">
          <div class="benefit-icon">
            <i class="fas fa-percent"></i>
          </div>
          <h3 class="benefit-title">Special Offers</h3>
          <p class="benefit-text">Enjoy discounts and special offers on selected movies and shows.</p>
        </div>
      </div>
    </section>
    
    <section class="upcoming-section">
      <h2>Upcoming Movies</h2>
      <div class="upcoming-movies">
        <div class="upcoming-movie">
          <img src="/api/placeholder/300/400" alt="Upcoming Movie 1">
        </div>
        <div class="upcoming-movie">
          <img src="/api/placeholder/300/400" alt="Upcoming Movie 2">
        </div>
        <div class="upcoming-movie">
          <img src="/api/placeholder/300/400" alt="Upcoming Movie 3">
        </div>
        <div class="upcoming-movie">
          <img src="/api/placeholder/300/400" alt="Upcoming Movie 4">
        </div>
      </div>
    </section>
  </main>
<?php include("footer.php")?>
</body>
</html>