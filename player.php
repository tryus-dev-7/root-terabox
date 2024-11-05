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
    <link rel="stylesheet" href="https://cdn.plyr.io/3.3.12/plyr.css">
    <style>
        /* Add top margin to the video container */
        .video-container {
            margin-top: 30px;
            /* 30px margin on top only */
            box-shadow: 0px 4px 20px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
        }
    </style>
</head>

<body>
    <div class="video-container">
        <video poster="<?php echo $posterImg; ?>" id="player" playsinline controls muted autoplay>
            <source src="<?php echo htmlspecialchars($downloadLink); ?>" type="video/mp4">
        </video>
    </div>
    <!-- Plyr JS -->
    <script src="https://cdn.plyr.io/3.3.12/plyr.js"></script>
    <script>const player = new Plyr('#player');</script>
</body>

</html>