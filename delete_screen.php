<?php
session_start();
include("config.php");

// Check if the user is logged in and has admin role
if (!isset($_SESSION['user_name']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Check if screen_id is provided and delete_screen action is set
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_screen']) && isset($_POST['screen_id'])) {
    $screen_id = $_POST['screen_id'];
    
    // Start transaction
    mysqli_begin_transaction($conn);
    
    try {
        // First delete all seats associated with the screen
        $deleteSeatsQuery = "DELETE FROM seats WHERE screen_id = ?";
        $stmt = mysqli_prepare($conn, $deleteSeatsQuery);
        mysqli_stmt_bind_param($stmt, "i", $screen_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        // Then delete the screen
        $deleteScreenQuery = "DELETE FROM screens WHERE screen_id = ?";
        $stmt = mysqli_prepare($conn, $deleteScreenQuery);
        mysqli_stmt_bind_param($stmt, "i", $screen_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        // Commit transaction
        mysqli_commit($conn);
        
        $_SESSION['success_message'] = "Screen and associated seats deleted successfully!";
    } catch (Exception $e) {
        // Rollback transaction on error
        mysqli_rollback($conn);
        $_SESSION['error_message'] = "Error deleting screen: " . $e->getMessage();
    }
    
    // Redirect back to screens page
    header("Location: screens.php");
    exit();
} else {
    // If screen_id is not provided, redirect to screens page
    header("Location: screens.php");
    exit();
}
?>