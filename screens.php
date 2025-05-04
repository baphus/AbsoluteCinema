<?php
include("config.php");

// Check if the user is logged in and has the "admin" role
session_start();
if (!isset($_SESSION['user_name']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Process form submission for adding new screens
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_screen'])) {
    $screen_name = $_POST['screen_name'];
    $capacity = $_POST['capacity'];
    $screen_type = $_POST['screen_type'];
    $audio_system = $_POST['audio_system'];
    $status = $_POST['status'];
    $notes = $_POST['notes'];

    $insertQuery = "INSERT INTO screens (screen_name, capacity, screen_type, audio_system, status, notes) 
                    VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $insertQuery);
    mysqli_stmt_bind_param($stmt, "sissss", $screen_name, $capacity, $screen_type, $audio_system, $status, $notes);

    if (mysqli_stmt_execute($stmt)) {
        $screen_id = mysqli_insert_id($conn); // Get the ID of the newly created screen
    
        // Automatically create 9 seats for the new screen
        $rows = ['A', 'B', 'C']; // Example row labels
    
        foreach ($rows as $row_label) {
            for ($i = 1; $i <= 3; $i++) { // Create 3 seats per row
                // Generate a unique seat_id using screen_id, row, and seat number
                $seat_id = "SEAT_{$screen_id}_{$row_label}_{$i}";
                $seatQuery = "INSERT INTO seats (seat_id, screen_id, row_label, seat_number, status) VALUES (?, ?, ?, ?, 'available')";
                $seatStmt = mysqli_prepare($conn, $seatQuery);
                mysqli_stmt_bind_param($seatStmt, "sisi", $seat_id, $screen_id, $row_label, $i);
                mysqli_stmt_execute($seatStmt);
                mysqli_stmt_close($seatStmt);
            }
        }
    
        $success_message = "Screen and its 9 seats added successfully!";
    } else {
        $error_message = "Error adding screen: " . mysqli_error($conn);
    }

    mysqli_stmt_close($stmt);
}

// Process form submission for updating screens
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_screen'])) {
    $screen_id = $_POST['screen_id'];
    $screen_name = $_POST['screen_name'];
    $capacity = $_POST['capacity'];
    $screen_type = $_POST['screen_type'];
    $audio_system = $_POST['audio_system'];
    $status = $_POST['status'];
    $notes = $_POST['notes'];

    $updateQuery = "UPDATE screens SET screen_name = ?, capacity = ?, screen_type = ?, audio_system = ?, status = ?, notes = ? 
                    WHERE screen_id = ?";
    $stmt = mysqli_prepare($conn, $updateQuery);
    mysqli_stmt_bind_param($stmt, "sissssi", $screen_name, $capacity, $screen_type, $audio_system, $status, $notes, $screen_id);

    if (mysqli_stmt_execute($stmt)) {
        $success_message = "Screen updated successfully!";
    } else {
        $error_message = "Error updating screen: " . mysqli_error($conn);
    }

    mysqli_stmt_close($stmt);
}

// Fetch all screens
$getScreensQuery = "SELECT * FROM screens ORDER BY screen_id ASC";
$result = mysqli_query($conn, $getScreensQuery);

$screens = [];
while ($screen = mysqli_fetch_assoc($result)) {
    $screens[] = $screen;
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
    <title>Admin Dashboard - Screens</title>
    <link rel="stylesheet" href="/styles/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script>
      document.addEventListener('DOMContentLoaded', function () {
        // Add Screen Modal
        const addModal = document.getElementById('addModal');
        const addScreenBtn = document.getElementById('add-screen-btn');
        const addModalCloseBtn = document.querySelector('#addModal .close-btn');

        addScreenBtn.addEventListener('click', function () {
          addModal.style.display = 'block';
        });

        addModalCloseBtn.addEventListener('click', function () {
          addModal.style.display = 'none';
        });

        window.addEventListener('click', function (event) {
          if (event.target == addModal) {
            addModal.style.display = 'none';
          }
        });

        // Edit Screen Modal
        const editModal = document.getElementById('editModal');
        const editModalCloseBtn = document.querySelector('#editModal .close-btn');

        window.openEditModal = function (screen_id, screen_name, capacity, screen_type, audio_system, status, notes) {
          document.getElementById('edit_screen_id').value = screen_id;
          document.getElementById('edit_screen_name').value = screen_name;
          document.getElementById('edit_capacity').value = capacity;
          document.getElementById('edit_screen_type').value = screen_type;
          document.getElementById('edit_audio_system').value = audio_system;
          document.getElementById('edit_status').value = status;
          document.getElementById('edit_notes').value = notes;
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

        // Delete Screen Modal
        const deleteModal = document.getElementById('deleteModal');
        const deleteModalCloseBtns = document.querySelectorAll('#deleteModal .close-btn');

        window.openDeleteModal = function (screenId) {
            document.getElementById('delete_screen_id').value = screenId;
            deleteModal.style.display = 'block';
        };

        // Close delete modal
        deleteModalCloseBtns.forEach(btn => {
            btn.addEventListener('click', function () {
                deleteModal.style.display = 'none';
            });
        });

        // Close modal when clicking outside
        window.addEventListener('click', function (event) {
            if (event.target == deleteModal) {
                deleteModal.style.display = 'none';
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
                    <h2>Screen Management</h2>
                    <button id="add-screen-btn" class="btn">
                        <i class="fas fa-plus"></i> Add Screen
                    </button>
                </div>

                <div class="table">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Screen Name</th>
                                <th>Capacity</th>
                                <th>Screen Type</th>
                                <th>Audio System</th>
                                <th>Status</th>
                                <th>Notes</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($screens) > 0): ?>
                                <?php foreach ($screens as $screen) : ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($screen['screen_id']); ?></td>
                                        <td><?php echo htmlspecialchars($screen['screen_name']); ?></td>
                                        <td><?php echo htmlspecialchars($screen['capacity']); ?></td>
                                        <td><?php echo htmlspecialchars($screen['screen_type']); ?></td>
                                        <td><?php echo htmlspecialchars($screen['audio_system']); ?></td>
                                        <td><?php echo htmlspecialchars($screen['status']); ?></td>
                                        <td><?php echo htmlspecialchars($screen['notes']); ?></td>
                                        <td class="actions">
                                            <button class="btn-icon btn-edit" onclick="openEditModal('<?php echo $screen['screen_id']; ?>', '<?php echo $screen['screen_name']; ?>', '<?php echo $screen['capacity']; ?>', '<?php echo $screen['screen_type']; ?>', '<?php echo $screen['audio_system']; ?>', '<?php echo $screen['status']; ?>', '<?php echo $screen['notes']; ?>')">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn-icon btn-delete" onclick="openDeleteModal('<?php echo $screen['screen_id']; ?>')">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" style="text-align: center;">No screens found in the database.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
    <?php include("screens-modals.html")?>
</html>