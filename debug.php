<?php
    include("config.php");

    if (!$conn) {
        echo "Connection failed:" . mysqli_connect_error();
    }
    //Writing query for database.
    $sql = "SELECT * FROM users";

    //Querying and getting results
    $result = mysqli_query($conn, $sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<tr><td>" . $row["first_name"] . "</td></tr>" . $row["last_name"] . "</td></tr>"
                . $row["email"] . "</td></tr>" . $row["created_at"] . "</td></tr>";
        }
        echo "</table>";
    } else {
        echo "0 result";
    }

    //Fetch resulting rows as an array
    $informed = mysqli_fetch_all($result, MYSQLI_ASSOC);

    // Freeing result from the memory.
    mysqli_free_result($result);

    mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en-US">
    <head>
        <div class="Contained">
            <div class="row">
                <?php foreach ($informed as $inform) { ?>
                    <div class="col s6 medium-3">
                        <div class="card z-depth-0">
                            <div class="card-content center">
                                <h6><?php echo htmlspecialchars($inform['First Name']); ?></h6>
                                <div><?php echo htmlspecialchars($inform['Last Name']); ?></div>
                            </div>
                            <div class="card-action right-align">
                                <a class="brand-text" href="#">More Info
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
        <title> Email and Name List </title>
    </head>
    <body>
        <table>
            <tr>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Emails</th>
                <th>Date Created</th>
            </tr>
        </table>
    </body>
</html>
