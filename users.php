<?php
session_start();
include("config.php");

// Check if the user is logged in and has the "admin" role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    // Redirect to the login page if the user is not an admin
    header("Location: login.php");
    exit();
}

// Fetch all users from the database
$getUsersQuery = "SELECT * FROM users";
$result = mysqli_query($conn, $getUsersQuery);

if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}

// Store the fetched rows in an array
$users = [];
while ($user = mysqli_fetch_assoc($result)) {
    $users[] = $user;
}

// Handle user update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_user'])) {
    $user_id = $_POST['user_id'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $role = $_POST['role'];

    $updateQuery = "UPDATE users SET 
                    first_name = ?, 
                    last_name = ?, 
                    email = ?, 
                    phone = ?, 
                    role = ? 
                    WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $updateQuery);
    mysqli_stmt_bind_param($stmt, "ssssss", $first_name, $last_name, $email, $phone, $role, $user_id);

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success_message'] = "User updated successfully!";
    } else {
        $_SESSION['error_message'] = "Error updating user: " . mysqli_error($conn);
    }

    mysqli_stmt_close($stmt);

    // Redirect to avoid form resubmission
    header("Location: users.php");
    exit();
}

// Handle user deletion
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_user'])) {
    $user_id = $_POST['user_id'];

    $deleteQuery = "DELETE FROM users WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $deleteQuery);
    mysqli_stmt_bind_param($stmt, "s", $user_id);

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success_message'] = "User deleted successfully!";
    } else {
        $_SESSION['error_message'] = "Error deleting user: " . mysqli_error($conn);
    }

    mysqli_stmt_close($stmt);

    // Redirect to avoid form resubmission
    header("Location: users.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Users</title>
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
                    <h2> User Management </h2>
                    <button class="btn btn-refresh" onclick="location.reload();">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                </div>

                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>First Name</th>
                                <th>Last Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Role</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($users) > 0): ?>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($user['user_id']); ?></td>
                                        <td><?php echo htmlspecialchars($user['first_name']); ?></td>
                                        <td><?php echo htmlspecialchars($user['last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td><?php echo htmlspecialchars($user['phone']); ?></td>
                                        <td><?php echo htmlspecialchars($user['role']); ?></td>
                                        <td><?php echo htmlspecialchars($user['created_at']); ?></td>
                                        <td class="actions">
                                            <button class="btn-icon btn-edit" onclick="openEditUserModal('<?php echo htmlspecialchars($user['user_id']); ?>', '<?php echo htmlspecialchars($user['first_name']); ?>', '<?php echo htmlspecialchars($user['last_name']); ?>', '<?php echo htmlspecialchars($user['email']); ?>', '<?php echo htmlspecialchars($user['phone']); ?>', '<?php echo htmlspecialchars($user['role']); ?>')">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn-icon btn-delete" onclick="openDeleteUserModal('<?php echo htmlspecialchars($user['user_id']); ?>')">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8">No users found in the database.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
    <?php include("users-modals.html"); ?>
</body>
</html>