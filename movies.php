<?php
session_start();

include("config.php");

// Check if the user is logged in and has the "admin" role
if (!isset($_SESSION['user_name']) || $_SESSION['role'] !== 'admin') {
    // Redirect to the login page if the user is not an admin
    header("Location: login.php");
    exit();
}

// Process form submission for adding new movies
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_movie'])) {
    $title = $_POST['title'];
    $genre = $_POST['genre'];
    $duration = $_POST['duration'];
    $rating = $_POST['rating'];
    $description = $_POST['description'];
    $director = $_POST['director'];
    $release_date = $_POST['release_date'];
    $status = $_POST['status'];
    $date_added = date("Y-m-d"); // Current date

    // Handle poster and banner file uploads
    $poster = "posters/default_poster.jpg"; // Default poster path
    $banner = "banners/default_banner.jpg"; // Default banner path

    if (isset($_FILES['poster']) && $_FILES['poster']['size'] > 0) {
        $target_dir = __DIR__ . "/posters/"; // Use absolute path for the posters directory
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true); // Create the directory if it doesn't exist
        }
        $file_extension = pathinfo($_FILES["poster"]["name"], PATHINFO_EXTENSION);
        $poster_file = $target_dir . "poster_" . time() . "." . $file_extension;

        if (move_uploaded_file($_FILES["poster"]["tmp_name"], $poster_file)) {
            $poster = "posters/" . basename($poster_file); // Store relative path for database
        }
    }

    if (isset($_FILES['banner']) && $_FILES['banner']['size'] > 0) {
        $target_dir = __DIR__ . "/banners/"; // Use absolute path for the banners directory
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true); // Create the directory if it doesn't exist
        }
        $file_extension = pathinfo($_FILES["banner"]["name"], PATHINFO_EXTENSION);
        $banner_file = $target_dir . "banner_" . time() . "." . $file_extension;

        if (move_uploaded_file($_FILES["banner"]["tmp_name"], $banner_file)) {
            $banner = "banners/" . basename($banner_file); // Store relative path for database
        }
    }

    // Insert movie into database
    $insertQuery = "INSERT INTO movies (title, genre, duration, rating, description, director, release_date, date_added, status, poster, banner) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($conn, $insertQuery);
    mysqli_stmt_bind_param($stmt, "ssissssssss", 
                           $title, $genre, $duration, $rating, $description, 
                           $director, $release_date, $date_added, $status, $poster, $banner);

    if (mysqli_stmt_execute($stmt)) {
        $success_message = "Movie added successfully!";
    } else {
        $error_message = "Error adding movie: " . mysqli_error($conn);
    }

    mysqli_stmt_close($stmt);
}

// Process form submission for updating movies
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_movie'])) {
    $movie_id = $_POST['movie_id'];
    $title = $_POST['title'];
    $genre = $_POST['genre'];
    $duration = $_POST['duration'];
    $rating = $_POST['rating'];
    $description = $_POST['description'];
    $director = $_POST['director'];
    $release_date = $_POST['release_date'];
    $status = $_POST['status'];

    // Handle poster and banner file uploads if new files are uploaded
    $poster = $_POST['current_poster']; // Default to current poster
    $banner = $_POST['current_banner']; // Default to current banner

    // Check if new poster is uploaded
    if (isset($_FILES['poster']) && $_FILES['poster']['size'] > 0) {
        $target_dir = "uploads/posters/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $file_extension = pathinfo($_FILES["poster"]["name"], PATHINFO_EXTENSION);
        $poster_file = $target_dir . "poster_" . $movie_id . "_" . time() . "." . $file_extension;

        if (move_uploaded_file($_FILES["poster"]["tmp_name"], $poster_file)) {
            $poster = $poster_file;
        }
    }

    // Check if new banner is uploaded
    if (isset($_FILES['banner']) && $_FILES['banner']['size'] > 0) {
        $target_dir = "uploads/banners/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $file_extension = pathinfo($_FILES["banner"]["name"], PATHINFO_EXTENSION);
        $banner_file = $target_dir . "banner_" . $movie_id . "_" . time() . "." . $file_extension;

        if (move_uploaded_file($_FILES["banner"]["tmp_name"], $banner_file)) {
            $banner = $banner_file;
        }
    }

    // Update movie in database
    $updateQuery = "UPDATE movies SET 
                    title = ?, 
                    genre = ?, 
                    duration = ?, 
                    rating = ?, 
                    description = ?, 
                    director = ?, 
                    release_date = ?, 
                    status = ?, 
                    poster = ?, 
                    banner = ? 
                    WHERE movie_id = ?";  

    $stmt = mysqli_prepare($conn, $updateQuery);
    mysqli_stmt_bind_param($stmt, "ssisssssssi", 
                           $title, $genre, $duration, $rating, $description, 
                           $director, $release_date, $status, $poster, $banner, $movie_id);

    if (mysqli_stmt_execute($stmt)) {
        $success_message = "Movie updated successfully!";
    } else {
        $error_message = "Error updating movie: " . mysqli_error($conn);
    }

    mysqli_stmt_close($stmt);
}

// Initialize sort variables
$sortBy = isset($_GET['sort-by']) ? $_GET['sort-by'] : 'movie_id'; // Default sort by ID

// Build the query based on the sort option
$getMoviesQuery = "SELECT * FROM movies";
if (!empty($sortBy)) {
    $allowedSortColumns = ['movie_id', 'title', 'genre', 'duration', 'rating', 'description', 'director', 'release_date', 'date_added', 'status', 'poster', 'banner'];
    if (in_array($sortBy, $allowedSortColumns)) {
        $getMoviesQuery .= " ORDER BY $sortBy ASC";
    }
}

$result = mysqli_query($conn, $getMoviesQuery);

if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Absolute Cinema</title>
    <link rel="stylesheet" href="/styles/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script>
      document.addEventListener('DOMContentLoaded', function () {
        const addModal = document.getElementById('addModal');
        const addMovieBtn = document.getElementById('add-movie-btn');
        const addModalCloseBtn = document.querySelector('#addModal .close-btn');

        addMovieBtn.addEventListener('click', function () {
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

        window.openEditModal = function (movieId, title, genre, duration, rating, description, director, releaseDate, status, poster, banner) {
          document.getElementById('edit_movie_id').value = movieId;
          document.getElementById('edit_title').value = title;
          document.getElementById('edit_genre').value = genre;
          document.getElementById('edit_duration').value = duration;
          document.getElementById('edit_rating').value = rating;
          document.getElementById('edit_description').value = description;
          document.getElementById('edit_director').value = director;
          document.getElementById('edit_release_date').value = releaseDate;
          document.getElementById('edit_status').value = status;
          document.getElementById('current_poster').value = poster;
          document.getElementById('current_banner').value = banner;
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
            <h2>Movie Management</h2>
            <button id="add-movie-btn" class="btn">
                <i class="fas fa-plus"></i> Add Movie
              </button>
            <div>
              <form method="GET" action="movies.php" id="sort-form">
                <label for="sort-by">Sort by:</label>
                <select id="sort-by" name="sort-by" onchange="document.getElementById('sort-form').submit();">
                  <option value="movie_id" <?php echo $sortBy === 'movie_id' ? 'selected' : ''; ?>>By ID</option>
                  <option value="title" <?php echo $sortBy === 'title' ? 'selected' : ''; ?>>By Title</option>
                  <option value="genre" <?php echo $sortBy === 'genre' ? 'selected' : ''; ?>>By Genre</option>
                  <option value="duration" <?php echo $sortBy === 'duration' ? 'selected' : ''; ?>>By Duration</option>
                  <option value="rating" <?php echo $sortBy === 'rating' ? 'selected' : ''; ?>>By Rating</option>
                  <option value="description" <?php echo $sortBy === 'description' ? 'selected' : ''; ?>>By Description</option>
                  <option value="director" <?php echo $sortBy === 'director' ? 'selected' : ''; ?>>By Director</option>
                  <option value="release_date" <?php echo $sortBy === 'release_date' ? 'selected' : ''; ?>>By Release Date</option>
                  <option value="date_added" <?php echo $sortBy === 'date_added' ? 'selected' : ''; ?>>By Date Added</option>
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
                  <th>Title</th>
                  <th>Genre</th>
                  <th>Duration</th>
                  <th>Rating</th>
                  <th>Description</th>
                  <th>Director</th>
                  <th>Release Date</th>
                  <th>Date Added</th>
                  <th>Status</th>
                  <th>Poster</th>
                  <th>Banner</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php while ($movie = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($movie['movie_id']); ?></td>
                            <td><?php echo htmlspecialchars($movie['title']); ?></td>
                            <td><?php echo htmlspecialchars($movie['genre']); ?></td>
                            <td><?php echo htmlspecialchars($movie['duration']); ?> mins</td>
                            <td><?php echo htmlspecialchars($movie['rating']); ?></td>
                            <td><?php echo htmlspecialchars(substr($movie['description'], 0, 50) . '...'); ?></td>
                            <td><?php echo htmlspecialchars($movie['director']); ?></td>
                            <td><?php echo htmlspecialchars($movie['release_date']); ?></td>
                            <td><?php echo htmlspecialchars($movie['date_added']); ?></td>
                            <td><?php echo htmlspecialchars($movie['status']); ?></td>
                            <td><img src="<?php echo htmlspecialchars($movie['poster']); ?>" alt="Poster" style="width: 50px;"></td>
                            <td><img src="<?php echo htmlspecialchars($movie['banner']); ?>" alt="Banner" style="width: 100px;"></td>
                            <td class="actions">
                                <button class="btn-icon btn-edit" onclick="openEditModal('<?php echo $movie['movie_id']; ?>', '<?php echo $movie['title']; ?>', '<?php echo $movie['genre']; ?>', '<?php echo $movie['duration']; ?>', '<?php echo $movie['rating']; ?>', '<?php echo $movie['description']; ?>', '<?php echo $movie['director']; ?>', '<?php echo $movie['release_date']; ?>', '<?php echo $movie['status']; ?>', '<?php echo $movie['poster']; ?>', '<?php echo $movie['banner']; ?>')">
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
                        <td colspan="13">No movies found.</td>
                    </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>  
      </main>
    </div>
    <?php include("edit-movie-modal.html") ?>
  </body>
</html>
