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
                $seat_id = uniqid("seat_"); // Generate a unique seat_id
                $seatQuery = "INSERT INTO seats (seat_id, screen_id, row_label, seat_number, status) VALUES (?, ?, ?, ?, 'available')";
                $seatStmt = mysqli_prepare($conn, $seatQuery);
                mysqli_stmt_bind_param($seatStmt, "sisi", $seat_id, $screen_id, $row_label, $i); // Use $i for unique seat_number per row
                mysqli_stmt_execute($seatStmt);
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
    while($screen = mysqli_fetch_assoc($result) > 0 ){
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
    <link rel="stylesheet" href="/styles/modal.css">
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
                                            <button class="btn-icon btn-delete" onclick="if(confirm('Are you sure you want to delete this screen?')) window.location.href='delete_screen.php?id=<?php echo $screen['screen_id']; ?>';">
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

    <!-- Add Screen Modal -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Add Screen</h3>
                <span class="close-btn">&times;</span>
            </div>
            <form method="POST" action="screens.php">
                <div class="form-group">
                    <label for="screen_name">Screen Name</label>
                    <input type="text" id="screen_name" name="screen_name" required>
                </div>
                <div class="form-group">
                    <label for="capacity">Capacity</label>
                    <input type="number" id="capacity" name="capacity" required>
                </div>
                <div class="form-group">
                    <label for="screen_type">Screen Type</label>
                    <input type="text" id="screen_type" name="screen_type" required>
                </div>
                <div class="form-group">
                    <label for="audio_system">Audio System</label>
                    <input type="text" id="audio_system" name="audio_system" required>
                </div>
                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status" required>
                        <option value="Available">Available</option>
                        <option value="Unavailable">Unavailable</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="notes">Notes</label>
                    <textarea id="notes" name="notes"></textarea>
                </div>
                <div class="form-actions">
                    <button type="submit" name="add_screen" class="btn">Add Screen</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Screen Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Edit Screen</h3>
                <span class="close-btn">&times;</span>
            </div>
            <form method="POST" action="screens.php">
                <input type="hidden" id="edit_screen_id" name="screen_id">
                <div class="form-group">
                    <label for="edit_screen_name">Screen Name</label>
                    <input type="text" id="edit_screen_name" name="screen_name" required>
                </div>
                <div class="form-group">
                    <label for="edit_capacity">Capacity</label>
                    <input type="number" id="edit_capacity" name="capacity" required>
                </div>
                <div class="form-group">
                    <label for="edit_screen_type">Screen Type</label>
                    <input type="text" id="edit_screen_type" name="screen_type" required>
                </div>
                <div class="form-group">
                    <label for="edit_audio_system">Audio System</label>
                    <input type="text" id="edit_audio_system" name="audio_system" required>
                </div>
                <div class="form-group">
                    <label for="edit_status">Status</label>
                    <select id="edit_status" name="status" required>
                        <option value="Available">Available</option>
                        <option value="Unavailable">Unavailable</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="edit_notes">Notes</label>
                    <textarea id="edit_notes" name="notes"></textarea>
                </div>
                <div class="form-actions">
                    <button type="submit" name="update_screen" class="btn">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>