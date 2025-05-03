<?php
session_start();
include("modal.html");
include("config.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Absolute Cinema - User Profile</title>
    <link rel="stylesheet" href="/styles/user-profile.css">
</head>
<body>
    <?php include("header.php")?>
    <main>
        <div class="profile-header">
            <div class="profile-avatar">
                <img src="/api/placeholder/100/100" alt="Profile avatar">
            </div>
            <div class="profile-info">
                <h1>John Doe</h1>
                <p>@johndoe@example.com</p>
                <p>Member since February 2025</p>
            </div>
        </div>

        <div class="bookings-section">
            <div class="bookings-header">
                <h2>My Bookings</h2>
                <button class="filter-btn">All time ↓</button>
            </div>

            <div class="booking-cards">
                <!-- Booking Card 1 -->
                <div class="booking-card">
                    <div class="booking-image">
                        <img src="/api/placeholder/150/150" alt="Movie placeholder">
                    </div>
                    <div class="booking-details">
                        <div class="booking-number">Booking #812345</div>
                        <h3 class="booking-title">Inception</h3>
                        <p class="booking-meta">Sci-Fi | 148 mins | PG-13</p>
                        
                        <div class="booking-info-grid">
                            <div class="booking-info-column">
                                <p class="booking-info-label">Date & Time</p>
                                <p class="booking-info-value">28 April 2025</p>
                                <p class="booking-info-value">7:30 P.M</p>
                            </div>
                            <div class="booking-info-column">
                                <p class="booking-info-label">Screen & Seats</p>
                                <p class="booking-info-value">Screen 3</p>
                                <p class="booking-info-value">Seat: B2</p>
                            </div>
                            <div class="booking-info-column">
                                <p class="booking-info-label">Total Amount</p>
                                <p class="booking-info-value">$42.00</p>
                            </div>
                        </div>
                        
                        <p class="booking-timestamp">Booked on: 25 Apr 2025, 10:15 AM</p>
                    </div>
                </div>

                <!-- Booking Card 2 -->
                <div class="booking-card">
                    <div class="booking-image">
                        <img src="/api/placeholder/150/150" alt="Movie placeholder">
                    </div>
                    <div class="booking-details">
                        <div class="booking-number">Booking #812345</div>
                        <h3 class="booking-title">Inception</h3>
                        <p class="booking-meta">Sci-Fi | 148 mins | PG-13</p>
                        
                        <div class="booking-info-grid">
                            <div class="booking-info-column">
                                <p class="booking-info-label">Date & Time</p>
                                <p class="booking-info-value">28 April 2025</p>
                                <p class="booking-info-value">7:30 P.M</p>
                            </div>
                            <div class="booking-info-column">
                                <p class="booking-info-label">Screen & Seats</p>
                                <p class="booking-info-value">Screen 3</p>
                                <p class="booking-info-value">Seat: A2</p>
                            </div>
                            <div class="booking-info-column">
                                <p class="booking-info-label">Total Amount</p>
                                <p class="booking-info-value">$42.00</p>
                            </div>
                        </div>
                        
                        <p class="booking-timestamp">Booked on: 25 Apr 2025, 10:15 AM</p>
                    </div>
                </div>

                <!-- Booking Card 3 -->
                <div class="booking-card">
                    <div class="booking-image">
                        <img src="/api/placeholder/150/150" alt="Movie placeholder">
                    </div>
                    <div class="booking-details">
                        <div class="booking-number">Booking #812345</div>
                        <h3 class="booking-title">Inception</h3>
                        <p class="booking-meta">Sci-Fi | 148 mins | PG-13</p>
                        
                        <div class="booking-info-grid">
                            <div class="booking-info-column">
                                <p class="booking-info-label">Date & Time</p>
                                <p class="booking-info-value">28 April 2025</p>
                                <p class="booking-info-value">7:30 P.M</p>
                            </div>
                            <div class="booking-info-column">
                                <p class="booking-info-label">Screen & Seats</p>
                                <p class="booking-info-value">Screen 3</p>
                                <p class="booking-info-value">Seat: F2</p>
                            </div>
                            <div class="booking-info-column">
                                <p class="booking-info-label">Total Amount</p>
                                <p class="booking-info-value">$42.00</p>
                            </div>
                        </div>
                        
                        <p class="booking-timestamp">Booked on: 25 Apr 2025, 10:15 AM</p>
                    </div>
                </div>
            </div>

            <div class="pagination">
                <a href="#" class="page-btn prev-btn">PREV</a>
                <a href="#" class="page-btn active">1</a>
                <a href="#" class="page-btn">2</a>
                <a href="#" class="page-btn">3</a>
                <a href="#" class="page-btn">4</a>
                <a href="#" class="page-btn next-btn">NEXT</a>
            </div>
        </div>
    </main>
    <?php include("footer.php")?>
</body>
</html>