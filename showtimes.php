<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Absolute Cinema</title>
    <link rel="stylesheet" href="styles/dashboard.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:ital,wght@0,100..700;1,100..700&family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Montserrat:ital,wght@0,100..900;1,100..900&family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body>
    <?php include("header.php")?>

    <div class="dashboard-layout">
    <?php include("sidebar.php")?>

        <main class="main-content">
            <div class="main-header">
                </div>

            <div class="content-wrapper">
                <div class="showtime-management-header">
                    <h2>Manage Showtimes</h2>
                    <div class="showtime-management-actions">
                        <div class="filter-section">
                            <label for="filter-by-showtime">Filter by:</label>
                            <select id="filter-by-showtime">
                                <option>By ID</option>
                                <option>By Movie</option>
                                <option>By Screen</option>
                                <option>By Date</option>
                                <option>By Time</option>
                            </select>
                        </div>
                        <input type="text" placeholder="By ID" class="filter-input">
                        <button class="btn btn-add-showtime">+ Add Showtime</button>
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
                            <tr>
                                <td>1</td>
                                <td>Inception</td>
                                <td>Screen 1</td>
                                <td>2025-05-05</td>
                                <td>10:00 AM</td>
                                <td>₱250.00</td>
                                <td>Active</td>
                                <td class="actions">
                                    <button class="btn-icon btn-edit"><i class="fas fa-edit"></i></button>
                                    <button class="btn-icon btn-delete"><i class="fas fa-trash-alt"></i></button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>

    </div>

    <script src="admin_script.js"></script>
    <p class="flaticon-attribution">
        <a href="https://www.flaticon.com/free-icons/dashboard" title="dashboard icons"></a>
    </p>
</body>
</html>