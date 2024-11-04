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

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terabox Video Player</title>
    <link rel="stylesheet" href="https://cdn.plyr.io/3.7.8/plyr.css" />
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #f0f0f0;
            font-family: Arial, sans-serif;
        }

        .video-container {
            position: relative;
            max-width: 100%;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<body>

    <div class="video-container" id="videoContainer">
        <video id="videoPlayer" playsinline autoplay muted controls>
            <source id="videoSource" src="<?php echo htmlspecialchars($downloadLink); ?>" type="video/mp4">
            Your browser does not support the video tag.
        </video>
    </div>

    <!-- Plyr.js JavaScript -->
    <script src="https://cdn.plyr.io/3.7.8/plyr.polyfilled.js"></script>
</body>

</html>