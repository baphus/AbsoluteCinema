<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmation - Absolute Cinema</title>
    <link rel="stylesheet" href="styles/confirmation.css"
/</head>
<body>
    <div class="confirmation-container">
        <div class="confirmation-header">
            <div class="check-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                    <path d="M9 16.17l-4.17-4.17-1.42 1.41 5.59 5.59 12-12-1.41-1.41z" fill="white"/>
                </svg>
            </div>
            <h1>Booking Confirmed!</h1>
            <p>Your tickets have been booked succesfully.</p>
        </div>

        <div class="ticket-container">
            <div class="ticket-left">
                <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='80' height='80' viewBox='0 0 80 80'%3E%3Crect width='80' height='80' fill='%23e0f2fe'/%3E%3Cpath d='M10,60 Q25,50 40,60 Q55,70 70,60 L70,80 L10,80 Z' fill='%2393c5fd'/%3E%3Ccircle cx='60' cy='20' r='10' fill='%23fbbf24'/%3E%3C/svg%3E" alt="Movie Poster" class="movie-poster">
                
                <div class="ticket-details">
                    <h2 class="movie-title">Inception</h2>
                    <div class="ticket-info">
                        <p><strong>PG-13 | 148 mins</strong></p>
                        <p><strong>Date & Time:</strong> April 26, 2025 at 11:00 A.M</p>
                        <p><strong>Cinema:</strong> Cinema 3</p>
                        <p><strong>Seats:</strong> B4, B5</p>
                        <p><strong>Booking ID:</strong> #MOV12345</p>
                    </div>
                </div>
            </div>
            
            <div class="ticket-divider"></div>
            
            <div class="ticket-right">
                <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='120' height='120' viewBox='0 0 120 120'%3E%3Crect width='120' height='120' fill='white'/%3E%3Cpath d='M0,0 L120,0 L120,120 L0,120 Z' fill='none' stroke='black' stroke-width='5'/%3E%3Cpath d='M20,20 L100,20 L100,100 L20,100 Z' fill='none' stroke='black' stroke-width='5'/%3E%3Cpath d='M40,40 L80,40 L80,80 L40,80 Z' fill='none' stroke='black' stroke-width='5'/%3E%3Cpath d='M50,50 L70,50 L70,70 L50,70 Z' fill='black'/%3E%3C/svg%3E" alt="QR Code" class="qr-code">
            </div>
        </div>
        
        <div class="ticket-note">
            Please arrive at least 15 minutes before the show time. Present this confirmation or the QR code at the ticket counter.
        </div>

        <div class="payment-section">
            <div class="payment-box">
                <h3>Payment Information</h3>
                <div class="payment-info">
                    <p><strong>Amount Paid:</strong> $28.60</p>
                    <p><strong>Payment Method:</strong> Credit Card (**** 3456)</p>
                    <p><strong>Transaction ID:</strong> TXN987654321</p>
                </div>
            </div>
            
            <div class="payment-box">
                <h3>Payment Information</h3>
                <div class="payment-info">
                    <p><strong>Amount Paid:</strong> $28.60</p>
                    <p><strong>Payment Method:</strong> Credit Card (**** 3456)</p>
                    <p><strong>Transaction ID:</strong> TXN987654321</p>
                </div>
            </div>
        </div>

        <div class="action-buttons">
            <a href="#" class="btn btn-primary">Download Tickets</a>
            <a href="#" class="btn btn-secondary">Email Receipt</a>
            <a href="#" class="btn btn-danger">Return to Home</a>
        </div>
    </div>
</body>
</html>