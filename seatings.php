<?php
include("config.php");

// Check if the user is logged in and has the "admin" role
session_start();
if (!isset($_SESSION['user_name']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Process form submission for updating seatings
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_seating'])) {
    $seat_id = $_POST['seat_id'];
    $status = $_POST['status'];

    $updateQuery = "UPDATE seats SET status = ? WHERE seat_id = ?";
    $stmt = mysqli_prepare($conn, $updateQuery);
    mysqli_stmt_bind_param($stmt, "ss", $status, $seat_id);

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success_message']  = "Seat status updated successfully!";
    } else {
        $_SESSION['error_message']  = "Error updating seat status: " . mysqli_error($conn);
    }

    mysqli_stmt_close($stmt);
}

// Fetch all seatings
$getSeatingsQuery = "SELECT * FROM seats ORDER BY seat_id ASC";
$result = mysqli_query($conn, $getSeatingsQuery);

$seatings = [];
while ($seating = mysqli_fetch_assoc($result)) {
    $seatings[] = $seating;
}
if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Seats</title>
    <link rel="stylesheet" href="/styles/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   
</head>
<body>
    <?php include("header.php") ?>
    <div class="dashboard-layout">
        <?php include("sidebar.php") ?>

        <main class="main-content">
                 
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
        <?php echo htmlspecialchars($_SESSION['success_message']); ?>
        <?php unset($_SESSION['success_message']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($_SESSION['error_message']); ?>
                <?php unset($_SESSION['error_message']); ?>
            </div>
        <?php endif; ?>

            <div class="content-wrapper">
                <div class="management-header">
                    <h2>Seating Management</h2>
                </div>

                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Seat ID</th>
                                <th>Screen ID</th>
                                <th>Row Label</th>
                                <th>Seat Number</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($seatings) > 0): ?>
                                <?php foreach ($seatings as $seating): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($seating['seat_id']); ?></td>
                                        <td><?php echo htmlspecialchars($seating['screen_id']); ?></td>
                                        <td><?php echo htmlspecialchars($seating['row_label']); ?></td>
                                        <td><?php echo htmlspecialchars($seating['seat_number']); ?></td>
                                        <td><?php echo htmlspecialchars($seating['status']); ?></td>
                                        <td class="actions">
                                            <button class="btn-icon btn-edit" onclick="openEditModal('<?php echo htmlspecialchars($seating['seat_id']); ?>', '<?php echo htmlspecialchars($seating['screen_id']); ?>', '<?php echo htmlspecialchars($seating['row_label']); ?>', <?php echo htmlspecialchars($seating['seat_number']); ?>, '<?php echo htmlspecialchars($seating['status']); ?>')">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn-icon btn-delete" onclick="if(confirm('Are you sure you want to delete this seating?')) window.location.href='delete_seating.php?id=<?php echo $seating['seat_id']; ?>';">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" style="text-align: center;">No seatings found in the database.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
    <?php include("seatings-modals.html")?>
</html>