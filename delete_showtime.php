<?php
include("config.php");
session_start();

// Check if the user is logged in and has the "admin" role
if (!isset($_SESSION['user_name']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Check if the showtime ID is provided
if (isset($_GET['id'])) {
    $showtime_id = mysqli_real_escape_string($conn, $_GET['id']);

    // Delete the showtime
    $deleteQuery = "DELETE FROM showtimes WHERE showtime_id = ?";
    $stmt = mysqli_prepare($conn, $deleteQuery);
    mysqli_stmt_bind_param($stmt, "s", $showtime_id);

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success_message'] = "Showtime deleted successfully!";
    } else {
        $_SESSION['error_message'] = "Error deleting showtime: " . mysqli_error($conn);
    }

    mysqli_stmt_close($stmt);
} else {
    $_SESSION['error_message'] = "Invalid request. Showtime ID is missing.";
}

// Redirect back to the showtimes page
header("Location: showtimes.php");
exit();
?>