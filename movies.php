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
    $trailer_url = $_POST['trailer_url']; 
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
    $insertQuery = "INSERT INTO movies (movie_id, title, genre, duration, rating, description, director, release_date, date_added, status, poster, banner, trailer_url) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($conn, $insertQuery);
    mysqli_stmt_bind_param($stmt, "ssissssssssss", 
    $movie_id, $title, $genre, $duration, $rating, $description, 
    $director, $release_date, $date_added, $status, $poster, $banner, $trailer_url);


    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success_message'] = "Movie added successfully!";
    } else {
        $_SESSION['error_message'] = "Error adding movie: " . mysqli_error($conn);
    }

    mysqli_stmt_close($stmt);

    // Redirect to avoid form resubmission
    header("Location: movies.php");
    exit();

    mysqli_stmt_close($stmt);
}

// Update movie form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_movie'])) {
    $movie_id = $_POST['movie_id'];
    $title = $_POST['title'];
    $genre = $_POST['genre'];
    $duration = $_POST['duration'];
    $rating = $_POST['rating'];
    $description = $_POST['description'];
    $director = $_POST['director'];
    $release_date = $_POST['release_date'];
    $trailer_url = $_POST['trailer-url'];
    $status = $_POST['status'];

    // Default: retrieve existing poster and banner
    $query = "SELECT poster, banner FROM movies WHERE movie_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $movie_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $existing_poster, $existing_banner);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    $poster = $existing_poster;
    $banner = $existing_banner;

    // Handle poster upload
    if (isset($_FILES['poster']) && $_FILES['poster']['size'] > 0) {
        $poster_dir = __DIR__ . "/posters/";
        if (!file_exists($poster_dir)) mkdir($poster_dir, 0777, true);
        $poster_name = "poster_" . time() . "." . pathinfo($_FILES["poster"]["name"], PATHINFO_EXTENSION);
        $poster_path = $poster_dir . $poster_name;
        if (move_uploaded_file($_FILES["poster"]["tmp_name"], $poster_path)) {
            $poster = "posters/" . $poster_name;
        }
    }

    // Handle banner upload
    if (isset($_FILES['banner']) && $_FILES['banner']['size'] > 0) {
        $banner_dir = __DIR__ . "/banners/";
        if (!file_exists($banner_dir)) mkdir($banner_dir, 0777, true);
        $banner_name = "banner_" . time() . "." . pathinfo($_FILES["banner"]["name"], PATHINFO_EXTENSION);
        $banner_path = $banner_dir . $banner_name;
        if (move_uploaded_file($_FILES["banner"]["tmp_name"], $banner_path)) {
            $banner = "banners/" . $banner_name;
        }
    }

    // Update query with poster, banner, and trailer_url
    $updateQuery = "UPDATE movies SET 
        title = ?, 
        genre = ?, 
        duration = ?, 
        rating = ?, 
        description = ?, 
        director = ?, 
        release_date = ?, 
        status = ?, 
        trailer_url = ?, 
        poster = ?, 
        banner = ?
        WHERE movie_id = ?";

    $stmt = mysqli_prepare($conn, $updateQuery);
    mysqli_stmt_bind_param($stmt, "ssisssssssss", $title, $genre, $duration, $rating, $description, $director, $release_date, $status, $trailer_url, $poster, $banner, $movie_id);

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success_message'] = "Movie updated successfully!";
    } else {
        $_SESSION['error_message'] = "Error updating movie: " . mysqli_error($conn);
    }

    mysqli_stmt_close($stmt);
    header("Location: movies.php");
    exit();
}

// Delete movie form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_movie'])) {
  $movie_id = $_POST['movie_id'];

  $deleteQuery = "DELETE FROM movies WHERE movie_id = ?";
  $stmt = mysqli_prepare($conn, $deleteQuery);
  mysqli_stmt_bind_param($stmt, "s", $movie_id);

  if (mysqli_stmt_execute($stmt)) {
       $_SESSION['success_message'] = "Movie deleted successfully";
  } else {
       $_SESSION['error_message'] = "Error deleting movie";
  }

  mysqli_stmt_close($stmt);
}

// Initialize sort variables
$sortBy = isset($_GET['sort-by']) ? $_GET['sort-by'] : 'movie_id'; // Default sort by ID

// Build the query based on the sort option
$getMoviesQuery = "SELECT * FROM movies";
if (!empty($sortBy)) {
    $allowedSortColumns = ['movie_id', 'title', 'genre', 'duration', 'rating', 'release_date', 'date_added', 'status', 'poster', 'banner'];
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
                  <th>Trailer URL</th>
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
                            <td>  <a href="<?php echo htmlspecialchars($movie['trailer_url']); ?>" target="_blank">
                                    <?php echo htmlspecialchars($movie['trailer_url'])?>
                                  </a> </td>
                            <td><?php echo htmlspecialchars($movie['date_added']); ?></td>
                            <td><?php echo htmlspecialchars($movie['status']); ?></td>
                            <td><img src="<?php echo htmlspecialchars($movie['poster']); ?>" alt="Poster" style="width: 50px;"></td>
                            <td><img src="<?php echo htmlspecialchars($movie['banner']); ?>" alt="Banner" style="width: 100px;"></td>
                            <td class="actions">
                                <button 
                                    class="btn-icon btn-edit"
                                    data-movie='<?php echo json_encode($movie, JSON_HEX_APOS | JSON_HEX_QUOT); ?>'
                                    onclick="openEditModal(this)">
                                    <i class="fas fa-edit"></i>
                                </button>
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
  <?php include("movie-modals.html")?>
</html>
