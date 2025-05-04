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
    $screen_id = $_POST['screen_id'];
    $row_label = $_POST['row_label'];
    $seat_number = $_POST['seat_number'];
    $status = $_POST['status'];

    $updateQuery = "UPDATE seats SET screen_id = ?, row_label = ?, seat_number = ?, status = ? WHERE seat_id = ?";
    $stmt = mysqli_prepare($conn, $updateQuery);
    mysqli_stmt_bind_param($stmt, "isiss", $screen_id, $row_label, $seat_number, $status, $seat_id);

    if (mysqli_stmt_execute($stmt)) {
        $success_message = "Seating updated successfully!";
    } else {
        $error_message = "Error updating seating: " . mysqli_error($conn);
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
    <link rel="stylesheet" href="/styles/modal.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script>
      document.addEventListener('DOMContentLoaded', function () {
        // Edit Seating Modal
        const editModal = document.getElementById('editModal');
        const editModalCloseBtn = document.querySelector('#editModal .close-btn');

        window.openEditModal = function (seat_id, screen_id, row_label, seat_number, status) {
          document.getElementById('edit_seat_id').value = seat_id;
          document.getElementById('edit_screen_id').value = screen_id;
          document.getElementById('edit_row_label').value = row_label;
          document.getElementById('edit_seat_number').value = seat_number;
          document.getElementById('edit_status').value = status;
          editModal.style.display = 'block';
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
            <div class="content-wrapper">
                <div class="management-header">
                    <h2>Seating Management</h2>
                </div>

                <div class="table">
                    <table>
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
                                            <button class="btn-icon btn-edit" onclick="openEditModal('<?php echo $seating['seat_id']; ?>', '<?php echo $seating['screen_id']; ?>', '<?php echo $seating['row_label']; ?>', '<?php echo $seating['seat_number']; ?>', '<?php echo $seating['status']; ?>')">
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

    <!-- Edit Seating Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Edit Seating</h3>
                <span class="close-btn">&times;</span>
            </div>
            <form method="POST" action="seatings.php">
                <input type="hidden" id="edit_seat_id" name="seat_id">
                <div class="form-group">
                    <label for="edit_screen_id">Screen ID</label>
                    <input type="number" id="edit_screen_id" name="screen_id" required>
                </div>
                <div class="form-group">
                    <label for="edit_row_label">Row Label</label>
                    <input type="text" id="edit_row_label" name="row_label" required>
                </div>
                <div class="form-group">
                    <label for="edit_seat_number">Seat Number</label>
                    <input type="number" id="edit_seat_number" name="seat_number" required>
                </div>
                <div class="form-group">
                    <label for="edit_status">Status</label>
                    <select id="edit_status" name="status" required>
                        <option value="available">Available</option>
                        <option value="occupied">Occupied</option>
                    </select>
                </div>
                <div class="form-actions">
                    <button type="submit" name="update_seating" class="btn">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>