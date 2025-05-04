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
    $showtime_id = uniqid("SHWTME_");
    $movie_id = $_POST['movie_id'];
    $screen_id = $_POST['screen_id'];
    $show_date = $_POST['show_date'];
    $start_time = $_POST['start_time'];
    $price = $_POST['price'];
    $status = $_POST['status'];

    $insertQuery = "INSERT INTO showtimes (showtime_id, movie_id, screen_id, show_date, start_time, price, status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $insertQuery);
    mysqli_stmt_bind_param($stmt, "sssssss", $showtime_id, $movie_id, $screen_id, $show_date, $start_time, $price, $status);

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
    $movie_id = $_POST['movie_id'];
    $screen_id = $_POST['screen_id'];
    $show_date = $_POST['show_date'];
    $start_time = $_POST['start_time'];
    $price = $_POST['price'];
    $status = $_POST['status'];

    $updateQuery = "UPDATE showtimes SET 
                    movie_id = ?, 
                    screen_id = ?, 
                    show_date = ?, 
                    start_time = ?, 
                    price = ?, 
                    status = ? 
                    WHERE showtime_id = ?";
    $stmt = mysqli_prepare($conn, $updateQuery);
    mysqli_stmt_bind_param($stmt, "sssssss", $movie_id, $screen_id, $show_date, $start_time, $price, $status, $showtime_id);

    if (mysqli_stmt_execute($stmt)) {
        $success_message = "Showtime updated successfully!";
    } else {
        $error_message = "Error updating showtime: " . mysqli_error($conn);
    }

    mysqli_stmt_close($stmt);
}

// Fetch all showtimes with movie titles
$getShowtimesQuery = "
    SELECT s.showtime_id, m.title AS movie_title, scr.screen_name, s.show_date, s.start_time, s.price, s.status
    FROM showtimes s
    JOIN movies m ON s.movie_id = m.movie_id
    JOIN screens scr ON s.screen_id = scr.screen_id
    ORDER BY s.showtime_id ASC";
$result = mysqli_query($conn, $getShowtimesQuery);

$showtimes = [];
while ($showtime = mysqli_fetch_assoc($result)) {
    $showtimes[] = $showtime;
}

if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}

// Fetch all movies
$getMoviesQuery = "SELECT movie_id, title FROM movies WHERE status = 'SHOWING' ORDER BY title ASC";
$moviesResult = mysqli_query($conn, $getMoviesQuery);
$movies = [];
while ($movie = mysqli_fetch_assoc($moviesResult)) {
    $movies[] = $movie;
}

// Fetch all screens
$getScreensQuery = "SELECT screen_id, screen_name FROM screens WHERE status = 'active' ORDER BY screen_name ASC";
$screensResult = mysqli_query($conn, $getScreensQuery);
$screens = [];
while ($screen = mysqli_fetch_assoc($screensResult)) {
    $screens[] = $screen;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Showtimes</title>
    <link rel="stylesheet" href="/styles/dashboard.css">
    <link rel="stylesheet" href="/styles/modal.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script>
      document.addEventListener('DOMContentLoaded', function () {
        // Add Showtime Modal
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

        // Edit Showtime Modal
        const editModal = document.getElementById('editModal');
        const editModalCloseBtn = document.querySelector('#editModal .close-btn');

        window.openEditModal = function (showtime_id, movie_id, screen_id, show_date, start_time, price, status) {
            document.getElementById('edit_showtime_id').value = showtime_id;
            document.getElementById('edit_movie_id').value = movie_id; // This will select the correct option
            document.getElementById('edit_screen_id').value = screen_id; // This will select the correct option
            document.getElementById('edit_show_date').value = show_date;
            document.getElementById('edit_start_time').value = start_time;
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
                    <button id="add-showtime-btn" class="btn">
                        <i class="fas fa-plus"></i> Add Showtime
                    </button>
                </div>

                <div class="table-container">
                    <table class="table">
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
                        <?php if (count($showtimes) > 0): ?>
                            <?php foreach ($showtimes as $showtime): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($showtime['showtime_id']); ?></td>
                                    <td><?php echo htmlspecialchars($showtime['movie_title']); ?></td> 
                                    <td><?php echo htmlspecialchars($showtime['screen_name']); ?></td>
                                    <td><?php echo htmlspecialchars($showtime['show_date']); ?></td>
                                    <td><?php echo htmlspecialchars($showtime['start_time']); ?></td>
                                    <td>₱<?php echo htmlspecialchars(number_format($showtime['price'], 2)); ?></td>
                                    <td><?php echo htmlspecialchars($showtime['status']); ?></td>
                                    <td class="actions">
                                        <button class="btn-icon btn-edit" onclick="openEditModal('<?php echo $showtime['showtime_id']; ?>', '<?php echo $showtime['movie_title']; ?>', '<?php echo $showtime['screen_name']; ?>', '<?php echo $showtime['show_date']; ?>', '<?php echo $showtime['start_time']; ?>', '<?php echo $showtime['price']; ?>', '<?php echo $showtime['status']; ?>')">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn-icon btn-delete" onclick="if(confirm('Are you sure you want to delete this showtime?')) window.location.href='delete_showtime.php?id=<?php echo $showtime['showtime_id']; ?>';">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="8" style="text-align: center;">No showtimes found.</td></tr>
                        <?php endif; ?>
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
                    <label for="movie_id">Movie</label>
                    <select id="movie_id" name="movie_id" required>
                        <option value="">Select a movie</option>
                        <?php foreach ($movies as $movie): ?>
                            <option value="<?php echo htmlspecialchars($movie['movie_id']); ?>">
                                <?php echo htmlspecialchars($movie['title']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="screen_id">Screen</label>
                    <select id="screen_id" name="screen_id" required>
                        <option value="">Select a screen</option>
                        <?php foreach ($screens as $screen): ?>
                            <option value="<?php echo htmlspecialchars($screen['screen_id']); ?>">
                                <?php echo htmlspecialchars($screen['screen_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="show_date">Date</label>
                    <input type="date" id="show_date" name="show_date" required>
                </div>
                <div class="form-group">
                    <label for="start_time">Time</label>
                    <input type="time" id="start_time" name="start_time" required>
                </div>
                <div class="form-group">
                    <label for="price">Price</label>
                    <input type="number" id="price" name="price" step="0.01" required>
                </div>
                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status" required>
                        <option value="available">Available</option>
                        <option value="inactive">Inactive</option>
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
                    <label for="edit_movie_id">Movie</label>
                    <select id="edit_movie_id" name="movie_id" required>
                        <option value="">Select a movie</option>
                        <?php foreach ($movies as $movie): ?>
                            <option value="<?php echo htmlspecialchars($movie['movie_id']); ?>">
                                <?php echo htmlspecialchars($movie['title']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="edit_screen_id">Screen</label>
                    <select id="edit_screen_id" name="screen_id" required>
                        <option value="">Select a screen</option>
                        <?php foreach ($screens as $screen): ?>
                            <option value="<?php echo htmlspecialchars($screen['screen_id']); ?>">
                                <?php echo htmlspecialchars($screen['screen_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="edit_show_date">Date</label>
                    <input type="date" id="edit_show_date" name="show_date" required>
                </div>
                <div class="form-group">
                    <label for="edit_start_time">Time</label>
                    <input type="time" id="edit_start_time" name="start_time" required>
                </div>
                <div class="form-group">
                    <label for="edit_price">Price</label>
                    <input type="number" id="edit_price" name="price" step="0.01" required>
                </div>
                <div class="form-group">
                    <label for="edit_status">Status</label>
                    <select id="edit_status" name="status" required>
                        <option value="available">Available</option>
                        <option value="inactive">Inactive</option>
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