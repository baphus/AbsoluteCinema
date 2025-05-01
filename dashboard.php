<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Absolute Cinema</title>
    <link rel="stylesheet" href="/styles/dashboard.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:ital,wght@0,100..700;1,100..700&family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Montserrat:ital,wght@0,100..900;1,100..900&family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body>
    <?php include("header.php")?>

    <div class="dashboard-layout">
    <?php include("sidebar.php")?>
    
        <main class="main-content">
            <div class="main-header">
                </div>

            <div class="content-wrapper">
                <section class="summary-cards">
                    <div class="card summary-card">
                        <div class="card-content">
                            <h4>TOTAL USERS</h4>
                            <span class="count" id="total-users">--</span>
                            <span class="change positive" id="users-change">↑ --%</span>
                        </div>
                        <div class="card-icon users">
                            <span class="icon"><i class="fas fa-users"></i></span>
                        </div>
                    </div>

                    <div class="card summary-card">
                        <div class="card-content">
                            <h4>TOTAL MOVIES</h4>
                            <span class="count" id="total-movies">--</span>
                            <span class="change positive" id="movies-change">↑ --%</span>
                        </div>
                        <div class="card-icon movies">
                            <span class="icon"><i class="fas fa-video"></i></span>
                        </div>
                    </div>

                    <div class="card summary-card">
                        <div class="card-content">
                            <h4>TOTAL BOOKINGS</h4>
                            <span class="count" id="total-bookings">--</span>
                            <span class="change positive" id="bookings-change">↑ --%</span>
                        </div>
                        <div class="card-icon bookings">
                            <span class="icon"><i class="fas fa-shopping-cart"></i></span>
                        </div>
                    </div>
                </section>

                <section class="chart-section">
                    <div class="card chart-card">
                        <h4>Most Booked Movies</h4>
                        <div class="chart-placeholder" id="booked-movies-chart">
                        </div>
                    </div>
                </section>
            </div>
        </main>

    </div>
</body>
</html>