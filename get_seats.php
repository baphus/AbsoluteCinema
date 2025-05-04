<?php
include("config.php");

if (isset($_GET['showtime_id'])) {
    $showtime_id = mysqli_real_escape_string($conn, $_GET['showtime_id']);

    $query = "SELECT seat_id, row_label, seat_number 
              FROM seats 
              WHERE showtime_id = ? AND status = 'available' 
              ORDER BY row_label, seat_number";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $showtime_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $seats = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $seats[] = $row;
    }

    header('Content-Type: application/json');
    echo json_encode($seats);
}
?>