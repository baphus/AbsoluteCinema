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
            <div class="content-wrapper">
                <div class="management-header">
                    <h2> User Management </h2>
                </div>

                <div class="table">
                    <table>
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
                                            <button class="btn-icon btn-edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn-icon btn-delete">
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
    <script>
        <?php if (count($users) > 0): ?>
            <?php foreach ($users as $user): ?>
                console.log("User ID: <?php echo htmlspecialchars($user['user_id']); ?>");
            <?php endforeach; ?>
        <?php endif; ?>
    </script>
</body>
</html>