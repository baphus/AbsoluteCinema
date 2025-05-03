<?php
session_start();
include("config.php");

// Check if the user is logged in and has the "admin" role
if (!isset($_SESSION['user_name']) || $_SESSION['role'] !== 'admin') {
    // Redirect to the login page if the user is not an admin
    header("Location: login.php");
    exit();
}

// Initialize sort variables
$sortBy = isset($_GET['sort-by']) ? $_GET['sort-by'] : 'user_id'; // Default sort by ID

// Build the query based on the sort option
$getUsersQuery = "SELECT * FROM users";
if (!empty($sortBy)) {
    $allowedSortColumns = ['user_id', 'first_name', 'last_name', 'email', 'phone', 'role', 'created_at'];
    if (in_array($sortBy, $allowedSortColumns)) {
        $getUsersQuery .= " ORDER BY $sortBy ASC";
    }
}

$result = mysqli_query($conn, $getUsersQuery);

if (!$result) {
    die("Query failed: " . mysqli_error($conn));
} else {
    echo "Query executed successfully. Rows returned: " . mysqli_num_rows($result);
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
    <script>
        function sortUsers() {
            document.getElementById("sort-form").submit();
        }
    </script>
</head>
<body>
    <?php include("header.php") ?>
    <div class="dashboard-layout">
        <?php include("sidebar.php") ?>
        <main class="main-content">
            <div class="content-wrapper">
                <div class="management-header">
                    <h2>User Management</h2>
                    <div>
                        <form method="GET" action="users.php" id="sort-form">
                            <label for="sort-by">Sort by:</label>
                            <select id="sort-by" name="sort-by" onchange="sortUsers()">
                                <option value="user_id" <?php echo $sortBy === 'user_id' ? 'selected' : ''; ?>>By ID</option>
                                <option value="first_name" <?php echo $sortBy === 'first_name' ? 'selected' : ''; ?>>By First Name</option>
                                <option value="last_name" <?php echo $sortBy === 'last_name' ? 'selected' : ''; ?>>By Last Name</option>
                                <option value="email" <?php echo $sortBy === 'email' ? 'selected' : ''; ?>>By Email</option>
                                <option value="phone" <?php echo $sortBy === 'phone' ? 'selected' : ''; ?>>By Phone</option>
                                <option value="role" <?php echo $sortBy === 'role' ? 'selected' : ''; ?>>By Role</option>
                                <option value="created_at" <?php echo $sortBy === 'created_at' ? 'selected' : ''; ?>>By Created At</option>
                            </select>
                        </form>
                    </div>
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
                            <?php if (mysqli_num_rows($result) > 0): ?>
                                <?php while ($user = mysqli_fetch_assoc($result)): ?>
                                    <?php echo "<pre>"; print_r($user); echo "</pre>"; ?>
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
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8">No users found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
</html>