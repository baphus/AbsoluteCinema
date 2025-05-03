<?php
$host = 'localhost'; // Database host
$dbname = 'absolute_cinema_db'; // Database name
$username = 'root'; // Database username
$password = ''; // Database password (leave empty for default XAMPP setup)

// Create a connection
$conn = mysqli_connect($host, $username, $password, $dbname);

// Check the connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>