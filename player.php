<?php
// Database connection settings
$servername = "localhost"; // Change as needed
$username = "bijoyknat_androkali"; // Change to your DB username
$password = "=ot5(j$4glD8"; // Change to your DB password
$dbname = "bijoyknat_terabox"; // Change to your DB name

// Create a new mysqli instance
$mysqli = new mysqli($servername, $username, $password, $dbname);

// Check for connection errors
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Get the short_id from the query string
$shortId = isset($_GET['id']) ? $_GET['id'] : null;

$downloadLink = '';
if ($shortId) {
    // Prepare and execute the SQL statement to retrieve the download link
    $stmt = $mysqli->prepare("SELECT download_link FROM downloads WHERE short_id = ?");
    $stmt->bind_param("s", $shortId); // "s" indicates that the parameter is a string
    $stmt->execute();
    $stmt->bind_result($downloadLink);
    $stmt->fetch();
    $stmt->close();

    if (empty($downloadLink)) {
        die("Video not found.");
    }
} else {
    die("No short_id provided.");
}

$mysqli->close();
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <!-- Plyr.io Player -->
    <link rel="stylesheet" href="https://cdn.plyr.io/3.7.8/plyr.css" />
</head>

<body style="margin:0px;">
    <video poster="" id="player" playsinline controls autoplay muted>
        <source src="<?php echo htmlspecialchars($downloadLink); ?>" type="video/mp4">
    </video>
    <!-- Plyr JS -->
    <script src="https://cdn.plyr.io/3.7.8/plyr.polyfilled.js"></script>
    <script>
        alert(<?php echo htmlspecialchars($downloadLink); ?>);
        const player = new Plyr('#player');
    </script>
</body>

</html>