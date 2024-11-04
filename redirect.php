<?php

// Database connection settings
$servername = "localhost"; // Change as needed
$username = "bijoyknat_androkali"; // Change to your DB username
$password = "=ot5(j$4glD8"; // Change to your DB password
$dbname = "bijoyknat_terabox"; // Change to your DB name

// Create a connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the short code from the URL
if (isset($_GET['code'])) {
    $shortCode = $_GET['code'];

    // Prepare and execute the query to find the download link
    $stmt = $conn->prepare("SELECT download_link FROM downloads WHERE short_id = ?");
    $stmt->bind_param("s", $shortCode);
    $stmt->execute();
    $stmt->bind_result($downloadLink);
    
    if ($stmt->fetch()) {
        // Redirect to the download link
        header("Location: $downloadLink");
        exit;
    } else {
        // Short code not found
        header("HTTP/1.0 404 Not Found");
        echo "404 Not Found: The short URL does not exist.";
    }

    $stmt->close();
} else {
    // No code provided
    header("HTTP/1.0 400 Bad Request");
    echo "400 Bad Request: No code provided.";
}

// Close the database connection
$conn->close();
?>
