<?php
// Start the session if not already started
session_start();

// Unset all session variables
$_SESSION = array();

// If it's desired to kill the session cookie, do that as well
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-42000, '/');
}

// Finally, destroy the session
session_destroy();

// Redirect to the home page or login page
header("Location: index.php");
exit();
?>