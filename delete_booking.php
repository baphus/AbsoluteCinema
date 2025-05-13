<?php
session_start();
include("config.php");

if (!isset($_SESSION['user_name']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET['id'])) {
    $booking_id = $_GET['id'];

    mysqli_begin_transaction($conn);

    try {

        $deleteBookingQuery = "DELETE FROM bookings WHERE booking_id = ?";
        $stmt = mysqli_prepare($conn, $deleteBookingQuery);
        mysqli_stmt_bind_param($stmt, "s", $booking_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        mysqli_commit($conn);
        $_SESSION['success_message'] = "Booking deleted successfully and seat set to available.";
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $_SESSION['error_message'] = "Error deleting booking: " . $e->getMessage();
    }

    header("Location: bookings.php");
    exit();
} else {
    header("Location: bookings.php");
    exit();
}
?>
