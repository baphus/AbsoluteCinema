<?php
include("config.php");

// Check if the user is logged in and has the "admin" role
session_start();
if (!isset($_SESSION['user_name']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Process form submission for adding new showtimes
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_showtime'])) {
    $showtime_id = uniqid("SHWTME_");
    $movie_id = $_POST['movie_id'];
    $screen_id = $_POST['screen_id'];
    $show_date = $_POST['show_date'];
    $start_time = $_POST['start_time'];
    $price = $_POST['price'];
    $status = $_POST['status'];

    $insertQuery = "INSERT INTO showtimes (showtime_id, movie_id, screen_id, show_date, start_time, price, status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $insertQuery);
    mysqli_stmt_bind_param($stmt, "sssssss", $showtime_id, $movie_id, $screen_id, $show_date, $start_time, $price, $status);

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success_message'] = "Showtime added successfully!";
    } else {
        $_SESSION['error_message'] = "Error adding showtime: " . mysqli_error($conn);
    }

    mysqli_stmt_close($stmt);
}

// Process form submission for updating showtimes
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_showtime'])) {
    $showtime_id = $_POST['showtime_id'];
    $movie_id = $_POST['movie_id'];
    $screen_id = $_POST['screen_id'];
    $show_date = $_POST['show_date'];
    $start_time = $_POST['start_time'];
    $price = $_POST['price'];
    $status = $_POST['status'];

    $updateQuery = "UPDATE showtimes SET 
                    movie_id = ?, 
                    screen_id = ?, 
                    show_date = ?, 
                    start_time = ?, 
                    price = ?, 
                    status = ? 
                    WHERE showtime_id = ?";
    $stmt = mysqli_prepare($conn, $updateQuery);
    mysqli_stmt_bind_param($stmt, "sssssss", $movie_id, $screen_id, $show_date, $start_time, $price, $status, $showtime_id);

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success_message'] = "Showtime updated successfully!";
    } else {
        $_SESSION['error_message'] = "Error updating showtime: " . mysqli_error($conn);
    }

    mysqli_stmt_close($stmt);
}

// Process form submission for deleting showtimes
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_showtime'])) {
    $showtime_id = $_POST['showtime_id'];

    $deleteQuery = "DELETE FROM showtimes WHERE showtime_id = ?";
    $stmt = mysqli_prepare($conn, $deleteQuery);
    mysqli_stmt_bind_param($stmt, "s", $showtime_id);

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success_message'] = "Showtime deleted successfully!";
    } else {
        $_SESSION['error_message'] = "Error deleting showtime: " . mysqli_error($conn);
    }

    mysqli_stmt_close($stmt);
}
// Fetch all showtimes with movie titles
$getShowtimesQuery = "
    SELECT s.showtime_id, m.title AS movie_title, scr.screen_name, s.show_date, s.start_time, s.price, s.status
    FROM showtimes s
    JOIN movies m ON s.movie_id = m.movie_id
    JOIN screens scr ON s.screen_id = scr.screen_id
    ORDER BY s.showtime_id ASC";
$result = mysqli_query($conn, $getShowtimesQuery);

$showtimes = [];
while ($showtime = mysqli_fetch_assoc($result)) {
    $showtimes[] = $showtime;
}

if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}

// Fetch all movies
$getMoviesQuery = "SELECT movie_id, title FROM movies WHERE status = 'SHOWING' ORDER BY title ASC";
$moviesResult = mysqli_query($conn, $getMoviesQuery);
$movies = [];
while ($movie = mysqli_fetch_assoc($moviesResult)) {
    $movies[] = $movie;
}

// Fetch all screens
$getScreensQuery = "SELECT screen_id, screen_name FROM screens WHERE status = 'active' ORDER BY screen_name ASC";
$screensResult = mysqli_query($conn, $getScreensQuery);
$screens = [];
while ($screen = mysqli_fetch_assoc($screensResult)) {
    $screens[] = $screen;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Showtimes</title>
    <link rel="stylesheet" href="/styles/dashboard.css">
    <link rel="stylesheet" href="/styles/modal.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include("header.php") ?>
    <div class="dashboard-layout">
        <?php include("sidebar.php") ?>

        <main class="main-content">
        <?php if (isset($_SESSION['success_message'])): ?>
            <p><?php echo htmlspecialchars($_SESSION['success_message']); ?></p>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <p><?php echo htmlspecialchars($_SESSION['error_message']); ?></p>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>
            <div class="content-wrapper">
                <div class="management-header">
                    <h2>Showtime Management</h2>
                    <button id="add-showtime-btn" class="btn">
                        <i class="fas fa-plus"></i> Add Showtime
                    </button>
                </div>

                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Movie Title</th>
                                <th>Screen</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (count($showtimes) > 0): ?>
                            <?php foreach ($showtimes as $showtime): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($showtime['showtime_id']); ?></td>
                                    <td><?php echo htmlspecialchars($showtime['movie_title']); ?></td> 
                                    <td><?php echo htmlspecialchars($showtime['screen_name']); ?></td>
                                    <td><?php echo htmlspecialchars($showtime['show_date']); ?></td>
                                    <td><?php echo htmlspecialchars($showtime['start_time']); ?></td>
                                    <td>₱<?php echo htmlspecialchars(number_format($showtime['price'], 2)); ?></td>
                                    <td><?php echo htmlspecialchars($showtime['status']); ?></td>
                                    <td class="actions">
                                        <button class="btn-icon btn-edit" onclick="openEditModal('<?php echo $showtime['showtime_id']; ?>', '<?php echo $showtime['movie_title']; ?>', '<?php echo $showtime['screen_name']; ?>', '<?php echo $showtime['show_date']; ?>', '<?php echo $showtime['start_time']; ?>', '<?php echo $showtime['price']; ?>', '<?php echo $showtime['status']; ?>')">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn-icon btn-delete" onclick="openDeleteModal('<?php echo $showtime['showtime_id']; ?>')">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="8" style="text-align: center;">No showtimes found.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
    <?php include("showtime-modals.html")?>
</body>
</html>