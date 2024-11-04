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

function getDownloadLink($fileId, $conn) {
    // Fetch file information (as you already have)
    $infoUrl = "https://terabox.hnn.workers.dev/api/get-info?shorturl=$fileId";
    $infoResponse = file_get_contents($infoUrl);
    $infoData = json_decode($infoResponse, true);

    if (!$infoData['ok']) {
        return ['error' => 'Failed to retrieve file info.'];
    }

    // Extract parameters
    $shareid = $infoData['shareid'];
    $uk = $infoData['uk'];
    $sign = $infoData['sign'];
    $timestamp = $infoData['timestamp'];
    $fs_id = $infoData['list'][0]['fs_id'];

    // Fetch the download link
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

    // Generate a unique short code
    $shortCode = substr(md5(uniqid(rand(), true)), 0, 6);
    $shortUrl = "https://terabox.bijoyknath.site/s/" . $shortCode; // Create the short URL

    // Prepare and execute the query to insert into the database
    $stmt = $conn->prepare("INSERT INTO downloads (filename, download_link, short_url, short_id) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $infoData['list'][0]['filename'], $downloadData['downloadLink'], $shortUrl, $shortCode);
    if (!$stmt->execute()) {
        return ['error' => 'Failed to store download link.'];
    }
    $stmt->close();

    // Return the response
    return [
        'title' => $infoData['list'][0]['filename'],
        'download_link' => $downloadData['downloadLink'],
        'short_url' => $shortUrl // Return the short URL directly
    ];
}

// Usage example:
if (isset($_GET['id'])) {
    $fileId = $_GET['id'];
    $result = getDownloadLink($fileId, $conn);

    // Set the content type to JSON
    header('Content-Type: application/json');
    echo json_encode($result); // This should return URLs correctly
} else {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'No file ID provided.']);
}

// Close the database connection
$conn->close();
?>
