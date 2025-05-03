<?php
include("config.php");

// Initialize sort variables
$sortBy = isset($_GET['sort-by']) ? $_GET['sort-by'] : 'showtime_id'; // Default sort by ID

// Build the query based on the sort option
$getShowtimesQuery = "SELECT * FROM showtimes";
if (!empty($sortBy)) {
    $allowedSortColumns = ['showtime_id', 'movie_title', 'screen', 'date', 'time', 'price', 'status'];
    if (in_array($sortBy, $allowedSortColumns)) {
        $getShowtimesQuery .= " ORDER BY $sortBy ASC";
    }
}

$result = mysqli_query($conn, $getShowtimesQuery);

if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Showtimes</title>
    <link rel="stylesheet" href="/styles/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script>
        function sortShowtimes() {
            document.getElementById("sort-form").submit();
        }
    </script>
</head>
<body>
    <?php include("header.php") ?>

    <div class="dashboard-layout">
        <?php include("sidebar.php") ?>

        <main class="main-content">
            <div class="main-header"></div>

            <div class="content-wrapper">
                <div class="showtime-management-header">
                    <h2>Showtime Management</h2>
                    <div class="showtime-management-actions">
                        <form method="GET" action="showtimes.php" id="sort-form">
                            <label for="sort-by">Sort by:</label>
                            <select id="sort-by" name="sort-by" onchange="sortShowtimes()">
                                <option value="showtime_id" <?php echo $sortBy === 'showtime_id' ? 'selected' : ''; ?>>By ID</option>
                                <option value="movie_title" <?php echo $sortBy === 'movie_title' ? 'selected' : ''; ?>>By Movie Title</option>
                                <option value="screen" <?php echo $sortBy === 'screen' ? 'selected' : ''; ?>>By Screen</option>
                                <option value="date" <?php echo $sortBy === 'date' ? 'selected' : ''; ?>>By Date</option>
                                <option value="time" <?php echo $sortBy === 'time' ? 'selected' : ''; ?>>By Time</option>
                                <option value="price" <?php echo $sortBy === 'price' ? 'selected' : ''; ?>>By Price</option>
                                <option value="status" <?php echo $sortBy === 'status' ? 'selected' : ''; ?>>By Status</option>
                            </select>
                        </form>
                    </div>
                </div>

                <div class="showtime-list-table">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Movie Title</th>
                                <th>Screen</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($showtime = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($showtime['showtime_id']); ?></td>
                                    <td><?php echo htmlspecialchars($showtime['movie_title']); ?></td>
                                    <td><?php echo htmlspecialchars($showtime['screen']); ?></td>
                                    <td><?php echo htmlspecialchars($showtime['date']); ?></td>
                                    <td><?php echo htmlspecialchars($showtime['time']); ?></td>
                                    <td>₱<?php echo htmlspecialchars(number_format($showtime['price'], 2)); ?></td>
                                    <td><?php echo htmlspecialchars($showtime['status']); ?></td>
                                    <td class="actions">
                                        <?php 
                                            $showtimeData = json_encode($showtime);
                                            $showtimeData = htmlspecialchars($showtimeData, ENT_QUOTES);
                                        ?>
                                        <button class="btn-icon btn-edit" onclick="openEditModal('<?php echo $showtimeData; ?>')">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn-icon btn-delete">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
</html>