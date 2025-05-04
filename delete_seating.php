<?php

include("config.php");

// Check if the user is logged in and has the "admin" role
session_start();
if (!isset($_SESSION['user_name']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Check if the seat ID is provided in the URL
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $seat_id = mysqli_real_escape_string($conn, $_GET['id']);

    // Delete the seat from the database
    $deleteQuery = "DELETE FROM seats WHERE seat_id = ?";
    $stmt = mysqli_prepare($conn, $deleteQuery);
    mysqli_stmt_bind_param($stmt, "s", $seat_id);

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success_message'] = "Seat deleted successfully!";
    } else {
        $_SESSION['error_message'] = "Error deleting seat: " . mysqli_error($conn);
    }

    mysqli_stmt_close($stmt);
} else {
    $_SESSION['error_message'] = "Invalid seat ID.";
}

// Redirect back to the seating management page
header("Location: seatings.php");
exit();