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
    $movie_id = uniqid("MOVIE#");
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
    $insertQuery = "INSERT INTO movies (movie_id, title, genre, duration, rating, description, director, release_date, date_added, status, poster, banner) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($conn, $insertQuery);
    mysqli_stmt_bind_param($stmt, "ssissssssss", 
                           $movie_id, $title, $genre, $duration, $rating, $description, 
                           $director, $release_date, $date_added, $status, $poster, $banner);

    if (mysqli_stmt_execute($stmt)) {
        $success_message = "Movie added successfully!";
    } else {
        $error_message = "Error adding movie: " . mysqli_error($conn);
    }

    mysqli_stmt_close($stmt);
}

//Delete movie form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_movie'])) {
  $movie_id = $_POST['movie_id'];

  $deleteQuery = "DELETE FROM movies WHERE movie_id = ?";
  $stmt = mysqli_prepare($conn, $deleteQuery);
  mysqli_stmt_bind_param($stmt, "s", $movie_id);

  if (mysqli_stmt_execute($stmt)) {
      $success_message = "Movie deleted successfully!";
  } else {
      $error_message = "Error deleting movie: " . mysqli_error($conn);
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

// Store movies in an array
$movies = [];
while ($movie = mysqli_fetch_assoc($result)) {
    $movies[] = $movie;
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
      });

      document.addEventListener('DOMContentLoaded', function () {
        const deleteModal = document.getElementById('deleteModal');
        const deleteModalCloseBtns = document.querySelectorAll('#deleteModal .close-btn');

        // Open the delete modal
        window.openDeleteModal = function (movieId) {
            document.getElementById('delete_movie_id').value = movieId;
            deleteModal.style.display = 'block';
        };

        // Close the delete modal
        deleteModalCloseBtns.forEach(btn => {
            btn.addEventListener('click', function () {
                deleteModal.style.display = 'none';
            });
        });

        // Close modal when clicking outside the modal content
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
                <?php if (count($movies) > 0): ?>
                    <?php foreach ($movies as $movie) : ?>
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
                                <button class="btn-icon btn-delete" onclick="openDeleteModal('<?php echo htmlspecialchars($movie['movie_id']); ?>')">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
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
  </body>
  <?php include("modals.html")?>
</html>
