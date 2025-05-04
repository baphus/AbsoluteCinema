<?php
include("config.php");

if (isset($_GET['screen_id'])) {
    $screen_id = mysqli_real_escape_string($conn, $_GET['screen_id']);

    $query = "SELECT showtime_id, show_date, start_time 
              FROM showtimes 
              WHERE screen_id = ? AND status = 'Available' AND show_date >= CURDATE()
              ORDER BY show_date, start_time";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $screen_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $showtimes = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $showtimes[] = $row;
    }

    header('Content-Type: application/json');
    echo json_encode($showtimes);
}
?>