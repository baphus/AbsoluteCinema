<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Fetch user details from the database if needed
// Example: Display user name
echo "<h1>Welcome, " . htmlspecialchars($_SESSION['user_name']) . "!</h1>";
?>