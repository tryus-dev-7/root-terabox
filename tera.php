<?php

// Database connection settings
$servername = "localhost"; // Change as needed
$username = "bijoyknat_androkali"; // Change to your DB username
$password = "@godboy2213bkna"; // Change to your DB password
$dbname = "bijoyknat_terabox"; // Change to your DB name

// Create a connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

function getDownloadLink($fileId, $conn) {
    // Step 1: Fetch the file information
    $infoUrl = "https://terabox.hnn.workers.dev/api/get-info?shorturl=$fileId";
    $infoResponse = file_get_contents($infoUrl);
    $infoData = json_decode($infoResponse, true);

    if (!$infoData['ok']) {
        return ['error' => 'Failed to retrieve file info.'];
    }

    // Extracting necessary parameters
    $shareid = $infoData['shareid'];
    $uk = $infoData['uk'];
    $sign = $infoData['sign'];
    $timestamp = $infoData['timestamp'];
    $fs_id = $infoData['list'][0]['fs_id'];

    // Step 2: Fetch the download link
    $downloadUrl = "https://terabox.hnn.workers.dev/api/get-download";
    $postData = json_encode([
        'shareid' => $shareid,
        'uk' => $uk,
        'sign' => $sign,
        'timestamp' => $timestamp,
        'fs_id' => $fs_id
    ]);

    $ch = curl_init($downloadUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($postData)
    ]);

    $downloadResponse = curl_exec($ch);
    curl_close($ch);

    $downloadData = json_decode($downloadResponse, true);

    if (!$downloadData['ok']) {
        return ['error' => 'Failed to retrieve download link.'];
    }

    // Get filename and download link
    $filename = $infoData['list'][0]['filename'];
    $downloadLink = $downloadData['downloadLink'];

    // Step 3: Insert the download link into the database
    $shortUrl = generateShortUrl($conn); // Generate short URL
    $stmt = $conn->prepare("INSERT INTO downloads (filename, download_link, short_url) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $filename, $downloadLink, $shortUrl);

    if (!$stmt->execute()) {
        return ['error' => 'Failed to store download link.'];
    }
    $stmt->close();

    // Return the filename, download link, and short URL
    return [
        'title' => $filename,
        'download_link' => $downloadLink,
        'short_url' => $shortUrl
    ];
}

// Function to generate a short URL (this is a simple implementation)
function generateShortUrl($conn) {
    // Generate a unique short code
    $shortCode = substr(md5(uniqid(rand(), true)), 0, 6);
    return "https://terabox.bijoyknath.site/s/" . $shortCode; // Replace with your domain
}

// Get the file ID from the URL
if (isset($_GET['id'])) {
    $fileId = $_GET['id']; // Retrieve file ID from the query string
    $result = getDownloadLink($fileId, $conn);

    // Set the content type to JSON
    header('Content-Type: application/json');
    
    // Return the result as JSON
    echo json_encode($result);
} else {
    // Return an error message as JSON if no ID is provided
    header('Content-Type: application/json');
    echo json_encode(['error' => 'No file ID provided.']);
}

// Close the database connection
$conn->close();
?>
