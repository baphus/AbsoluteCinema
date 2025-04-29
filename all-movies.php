<?php 
session_start();
include("config.php");

$genreFilter = isset($_GET['genre']) ? $_GET['genre'] : 'all';
$sortOrder = isset($_GET['sort']) ? $_GET['sort'] : 'a-z';

$genresQuery = "SELECT DISTINCT genre FROM movies";
$genresResult = mysqli_query($conn, $genresQuery);
$genres = [];
while ($row = mysqli_fetch_assoc($genresResult)) {
    $genres[] = $row['genre'];
}

$query = "SELECT * FROM movies";
if ($genreFilter !== 'all') {
    $query .= " WHERE genre = '" . mysqli_real_escape_string($conn, $genreFilter) . "'";
}
if ($sortOrder === 'a-z') {
    $query .= " ORDER BY title ASC";
} elseif ($sortOrder === 'z-a') {
    $query .= " ORDER BY title DESC";
}

$result = mysqli_query($conn, $query);
if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}

$movies = [];
while ($row = mysqli_fetch_assoc($result)) {
    $movies[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AbsoluteCinema - Movies</title>
    <link rel="stylesheet" href="styles/all-movies.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/js/all.min.js"></script>
</head>
<body>
    <?php include("header.php") ?>
    <main>
        <div class="movies-container">
            <div class="movies-header">
                <h2>Now Showing</h2>
                <div class="filter-options">
                    <form method="GET" class="filter-form">
                        <select name="genre" onchange="this.form.submit()">
                            <option value="all" <?php echo $genreFilter === 'all' ? 'selected' : ''; ?>>All Genres</option>
                            <?php foreach ($genres as $genre): ?>
                                <option value="<?php echo htmlspecialchars($genre); ?>" <?php echo $genreFilter === $genre ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($genre); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                    <form method="GET" class="filter-form">
                        <input type="hidden" name="genre" value="<?php echo htmlspecialchars($genreFilter); ?>">
                        <select name="sort" onchange="this.form.submit()">
                            <option value="a-z" <?php echo $sortOrder === 'a-z' ? 'selected' : ''; ?>>Sort by A-Z</option>
                            <option value="z-a" <?php echo $sortOrder === 'z-a' ? 'selected' : ''; ?>>Sort by Z-A</option>
                        </select>
                    </form>
                </div>
            </div>

            <div class="movie-cards">
                <?php if (count($movies) > 0): ?>
                    <?php foreach ($movies as $movie): ?>
                        <div class="movie-card">
                            <img src="<?php echo htmlspecialchars($movie['poster'] ?? 'images/placeholder-movie.jpg'); ?>" alt="<?php echo htmlspecialchars($movie['title']); ?>" class="movie-image">
                            <div class="movie-details">
                                <h3 class="movie-title"><?php echo htmlspecialchars($movie['title']); ?></h3>
                                <p class="movie-info"><?php echo htmlspecialchars($movie['genre']); ?> | <?php echo htmlspecialchars($movie['duration']); ?> mins | <?php echo htmlspecialchars($movie['rating']); ?></p>
                                <p class="movie-description"><?php echo htmlspecialchars($movie['description']); ?></p>
                                <div class="movie-actions">
                                    <a href="#" class="details-btn">Details</a>
                                    <a href="#" class="book-now-btn">Book</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No movies found for the selected filter.</p>
                <?php endif; ?>
            </div>
        </div>
    </main>
    <?php include("footer.php") ?>
</body>
</html>