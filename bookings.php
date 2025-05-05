<?php
include("config.php");

// Check if the user is logged in and has the "admin" role
session_start();
if (!isset($_SESSION['user_name']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Process form submission for adding new bookings
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_booking'])) {
    $user_id = $_POST['user_id'];
    $showtime_id = $_POST['showtime_id'];
    $booking_date = $_POST['booking_date'];
    $seat_id = $_POST['seat_id'];
    $total_price = $_POST['total_price'];
    $status = $_POST['status'];

    $insertQuery = "INSERT INTO bookings (user_id, showtime_id, booking_date, seat_id, total_price, status) 
                    VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $insertQuery);
    mysqli_stmt_bind_param($stmt, "isssds", $user_id, $showtime_id, $booking_date, $seat_id, $total_price, $status);

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success_message'] = "Booking added successfully!";
    } else {
        $_SESSION['error_message'] = "Error adding booking: " . mysqli_error($conn);
    }

    mysqli_stmt_close($stmt);
}

// Process form submission for updating bookings
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_booking'])) {
    $booking_id = $_POST['booking_id'];
    $status = $_POST['status'];

    $updateQuery = "UPDATE bookings SET status = ? WHERE booking_id = ?";
    $stmt = mysqli_prepare($conn, $updateQuery);
    mysqli_stmt_bind_param($stmt, "ss", $status, $booking_id);

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success_message'] = "Booking status updated successfully!";
    } else {
        $_SESSION['error_message'] = "Error updating booking: " . mysqli_error($conn);
    }

    mysqli_stmt_close($stmt);
    header("Location: bookings.php");
    exit();
}


// Fetch all bookings
$getBookingsQuery = "SELECT * FROM bookings ORDER BY booking_id ASC";
$result = mysqli_query($conn, $getBookingsQuery);

$bookings = [];
while ($booking = mysqli_fetch_assoc($result)) {
    $bookings[] = $booking;
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
    <title>Admin Dashboard - Bookings</title>
    <link rel="stylesheet" href="/styles/dashboard.css">
    <link rel="stylesheet" href="/styles/modal.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script>
      document.addEventListener('DOMContentLoaded', function () {

        // Edit Booking Modal
        const editModal = document.getElementById('editModal');
        const editModalCloseBtn = document.querySelector('#editModal .close-btn');

        window.openEditModal = function (booking_id, status) {
        document.getElementById('edit_booking_id').value = booking_id;
        document.getElementById('edit_status').value = status;
        document.getElementById('editModal').style.display = 'block';
        };

        editModalCloseBtn.addEventListener('click', function () {
          editModal.style.display = 'none';
        });

        window.addEventListener('click', function (event) {
          if (event.target == editModal) {
            editModal.style.display = 'none';
          }
        });
      });
    </script>
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
                    <h2>Booking Management</h2>
                </div>

                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>User ID</th>
                                <th>Showtime ID</th>
                                <th>Seat ID</th>
                                <th>Booking Date</th>
                                <th>Total Price</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($bookings) > 0): ?>
                                <?php foreach ($bookings as $booking): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($booking['booking_id']); ?></td>
                                        <td><?php echo htmlspecialchars($booking['user_id']); ?></td>
                                        <td><?php echo htmlspecialchars($booking['showtime_id']); ?></td>
                                        <td><?php echo htmlspecialchars($booking['seat_id']); ?></td>
                                        <td><?php echo htmlspecialchars($booking['booking_date']); ?></td>
                                        <td>₱<?php echo htmlspecialchars(number_format($booking['total_price'], 2)); ?></td>
                                        <td><?php echo htmlspecialchars($booking['status']); ?></td>
                                        <td class="actions">
                                        <button class="btn-icon btn-edit" onclick="openEditModal(
                                                            '<?php echo $booking['booking_id']; ?>',
                                                            '<?php echo $booking['status']; ?>'
                                                        )">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                            <button class="btn-icon btn-delete" onclick="if(confirm('Are you sure you want to delete this booking?')) window.location.href='delete_booking.php?id=<?php echo $booking['booking_id']; ?>';">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" style="text-align: center;">No bookings found in the database.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Edit Booking Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
            <h3>Update Booking Status</h3>
            <span class="close-btn">&times;</span>
            </div>
            <form method="POST" action="bookings.php">
            <input type="hidden" id="edit_booking_id" name="booking_id">

            <div class="form-group">
                <label for="edit_status">Status</label>
                <select id="edit_status" name="status" required>
                <option value="confirmed">Confirmed</option>
                <option value="pending">Pending</option>
                <option value="cancelled">Cancelled</option>
                </select>
            </div>

            <div class="form-actions">
                <button type="submit" name="update_booking" class="btn">Save Changes</button>
            </div>
            </form>
        </div>
    </div>
</body>
</html>