<?php
include("config.php");

if (isset($_GET['screen'])) {
    $screen = mysqli_real_escape_string($conn, $_GET['screen']);
    
    $query = "SELECT * FROM seats WHERE screen_id IN 
              (SELECT screen_id FROM screens WHERE screen_name = ?) 
              ORDER BY row_label, seat_number";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $screen);
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