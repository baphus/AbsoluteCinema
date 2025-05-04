<?php
include("config.php");

// Check if the user is logged in and has the "admin" role
session_start();
if (!isset($_SESSION['user_name']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Display messages if they exist
if (isset($_SESSION['success_message'])) {
    echo '<div class="alert alert-success">' . $_SESSION['success_message'] . '</div>';
    unset($_SESSION['success_message']);
}

if (isset($_SESSION['error_message'])) {
    echo '<div class="alert alert-danger">' . $_SESSION['error_message'] . '</div>';
    unset($_SESSION['error_message']);
}

// Process form submission for adding new screens
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_screen'])) {
    // Generate a unique screen ID first
    $screen_id = substr(uniqid("SCRN_"), 0, 9);
    $screen_name = $_POST['screen_name'];
    $capacity = $_POST['capacity'];
    $screen_type = $_POST['screen_type'];
    $audio_system = $_POST['audio_system'];
    $status = $_POST['status'];
    $notes = $_POST['notes'];
    
    mysqli_begin_transaction($conn);

    try {
        // Insert screen
        $insertQuery = "INSERT INTO screens (screen_id, screen_name, capacity, screen_type, audio_system, status, notes) 
                        VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $insertQuery);
        
        if (!$stmt) {
            throw new Exception("Failed to prepare screen insert statement: " . mysqli_error($conn));
        }
        
        mysqli_stmt_bind_param($stmt, "ssissss", 
            $screen_id, 
            $screen_name, 
            $capacity, 
            $screen_type, 
            $audio_system, 
            $status, 
            $notes
        );

        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Failed to insert screen: " . mysqli_stmt_error($stmt));
        }
        
        mysqli_stmt_close($stmt);

        // Create seats
        $seats_per_row = 10;
        $num_rows = ceil($capacity / $seats_per_row);
        $row_labels = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 
                          'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
        $seats_created = 0;

        for ($row = 0; $row < $num_rows && $seats_created < $capacity; $row++) {
            if ($row >= count($row_labels)) {
                throw new Exception("Too many rows needed for capacity. Maximum rows: " . count($row_labels));
            }

            $row_label = $row_labels[$row]; // This ensures row_label is a single character
            $seats_this_row = min($seats_per_row, $capacity - $seats_created);

            for ($seat_num = 1; $seat_num <= $seats_this_row; $seat_num++) {
                $seat_id = sprintf("SEAT_%s_%s_%02d", $screen_id, $row_label, $seat_num);
                $seatQuery = "INSERT INTO seats (seat_id, screen_id, row_label, seat_number, status) 
                             VALUES (?, ?, ?, ?, 'available')";
                $seatStmt = mysqli_prepare($conn, $seatQuery);

                if (!$seatStmt) {
                    throw new Exception("Failed to prepare seat insert statement: " . mysqli_error($conn));
                }

                // Explicitly bind row_label as a single character
                mysqli_stmt_bind_param($seatStmt, "sssi", 
                    $seat_id, 
                    $screen_id, 
                    $row_label,  // This is now guaranteed to be a single character
                    $seat_num
                );

                if (!mysqli_stmt_execute($seatStmt)) {
                    throw new Exception("Failed to insert seat: " . mysqli_stmt_error($seatStmt));
                }

                mysqli_stmt_close($seatStmt);
                $seats_created++;
            }
        }

        mysqli_commit($conn);
        $_SESSION['success_message'] = "Screen and " . $seats_created . " seats added successfully!";
        header("Location: screens.php");
        exit();

    } catch (Exception $e) {
        mysqli_rollback($conn);
        $_SESSION['error_message'] = "Error: " . $e->getMessage();
        header("Location: screens.php");
        exit();
    }
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