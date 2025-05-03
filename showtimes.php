<?php
include("config.php");

// Check if the user is logged in and has the "admin" role
session_start();
if (!isset($_SESSION['user_name']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Process form submission for adding new showtimes
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_showtime'])) {
    $movie_title = $_POST['movie_title'];
    $screen = $_POST['screen'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    $price = $_POST['price'];
    $status = $_POST['status'];

    $insertQuery = "INSERT INTO showtimes (movie_title, screen, date, time, price, status) 
                    VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $insertQuery);
    mysqli_stmt_bind_param($stmt, "ssssds", $movie_title, $screen, $date, $time, $price, $status);

    if (mysqli_stmt_execute($stmt)) {
        $success_message = "Showtime added successfully!";
    } else {
        $error_message = "Error adding showtime: " . mysqli_error($conn);
    }

    mysqli_stmt_close($stmt);
}

// Process form submission for updating showtimes
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_showtime'])) {
    $showtime_id = $_POST['showtime_id'];
    $movie_title = $_POST['movie_title'];
    $screen = $_POST['screen'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    $price = $_POST['price'];
    $status = $_POST['status'];

    $updateQuery = "UPDATE showtimes SET 
                    movie_title = ?, 
                    screen = ?, 
                    date = ?, 
                    time = ?, 
                    price = ?, 
                    status = ? 
                    WHERE showtime_id = ?";
    $stmt = mysqli_prepare($conn, $updateQuery);
    mysqli_stmt_bind_param($stmt, "ssssdsi", $movie_title, $screen, $date, $time, $price, $status, $showtime_id);

    if (mysqli_stmt_execute($stmt)) {
        $success_message = "Showtime updated successfully!";
    } else {
        $error_message = "Error updating showtime: " . mysqli_error($conn);
    }

    mysqli_stmt_close($stmt);
}

// Initialize sort variables
$sortBy = isset($_GET['sort-by']) ? $_GET['sort-by'] : 'showtime_id'; // Default sort by ID
$sortOrder = isset($_GET['sort-order']) ? $_GET['sort-order'] : 'ASC'; // Default sort order

// Build the query based on the sort option
$getShowtimesQuery = "SELECT * FROM showtimes";
$allowedSortColumns = ['showtime_id', 'movie_title', 'screen', 'date', 'time', 'price', 'status'];
if (in_array($sortBy, $allowedSortColumns)) {
    $getShowtimesQuery .= " ORDER BY $sortBy $sortOrder";
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
      document.addEventListener('DOMContentLoaded', function () {
        const addModal = document.getElementById('addModal');
        const addShowtimeBtn = document.getElementById('add-showtime-btn');
        const addModalCloseBtn = document.querySelector('#addModal .close-btn');

        addShowtimeBtn.addEventListener('click', function () {
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

        const editModal = document.getElementById('editModal');
        const editModalCloseBtn = document.querySelector('#editModal .close-btn');

        window.openEditModal = function (showtime_id, movie_title, screen, date, time, price, status) {
          document.getElementById('edit_showtime_id').value = showtime_id;
          document.getElementById('edit_movie_title').value = movie_title;
          document.getElementById('edit_screen').value = screen;
          document.getElementById('edit_date').value = date;
          document.getElementById('edit_time').value = time;
          document.getElementById('edit_price').value = price;
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
                    <h2>Showtime Management</h2>
                    <div>
                        <button id="add-showtime-btn" class="btn">
                            <i class="fas fa-plus"></i> Add Showtime
                        </button>
                        <form method="GET" action="showtimes.php" id="sort-form">
                            <label for="sort-by">Sort by:</label>
                            <select id="sort-by" name="sort-by" onchange="document.getElementById('sort-form').submit();">
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

                <div class="table">
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
                                        <button class="btn-icon btn-edit" onclick="openEditModal('<?php echo $showtime['showtime_id']; ?>', '<?php echo $showtime['movie_title']; ?>', '<?php echo $showtime['screen']; ?>', '<?php echo $showtime['date']; ?>', '<?php echo $showtime['time']; ?>', '<?php echo $showtime['price']; ?>', '<?php echo $showtime['status']; ?>')">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn-icon btn-delete" onclick="if(confirm('Are you sure you want to delete this showtime?')) window.location.href='delete_showtime.php?id=<?php echo $showtime['showtime_id']; ?>';">
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

    <!-- Add Showtime Modal -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Add Showtime</h3>
                <span class="close-btn">&times;</span>
            </div>
            <form method="POST" action="showtimes.php">
                <div class="form-group">
                    <label for="movie_title">Movie Title</label>
                    <input type="text" id="movie_title" name="movie_title" required>
                </div>
                <div class="form-group">
                    <label for="screen">Screen</label>
                    <input type="text" id="screen" name="screen" required>
                </div>
                <div class="form-group">
                    <label for="date">Date</label>
                    <input type="date" id="date" name="date" required>
                </div>
                <div class="form-group">
                    <label for="time">Time</label>
                    <input type="time" id="time" name="time" required>
                </div>
                <div class="form-group">
                    <label for="price">Price</label>
                    <input type="number" id="price" name="price" step="0.01" required>
                </div>
                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status" required>
                        <option value="Available">Available</option>
                        <option value="Unavailable">Unavailable</option>
                    </select>
                </div>
                <div class="form-actions">
                    <button type="submit" name="add_showtime" class="btn">Add Showtime</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Showtime Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Edit Showtime</h3>
                <span class="close-btn">&times;</span>
            </div>
            <form method="POST" action="showtimes.php">
                <input type="hidden" id="edit_showtime_id" name="showtime_id">
                <div class="form-group">
                    <label for="edit_movie_title">Movie Title</label>
                    <input type="text" id="edit_movie_title" name="movie_title" required>
                </div>
                <div class="form-group">
                    <label for="edit_screen">Screen</label>
                    <input type="text" id="edit_screen" name="screen" required>
                </div>
                <div class="form-group">
                    <label for="edit_date">Date</label>
                    <input type="date" id="edit_date" name="date" required>
                </div>
                <div class="form-group">
                    <label for="edit_time">Time</label>
                    <input type="time" id="edit_time" name="time" required>
                </div>
                <div class="form-group">
                    <label for="edit_price">Price</label>
                    <input type="number" id="edit_price" name="price" step="0.01" required>
                </div>
                <div class="form-group">
                    <label for="edit_status">Status</label>
                    <select id="edit_status" name="status" required>
                        <option value="Available">Available</option>
                        <option value="Unavailable">Unavailable</option>
                    </select>
                </div>
                <div class="form-actions">
                    <button type="submit" name="update_showtime" class="btn">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>