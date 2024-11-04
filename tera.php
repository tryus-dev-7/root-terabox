<?php

function getDownloadLink($fileId) {
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

    // Return only the filename and download link
    return [
        'title' => $infoData['list'][0]['filename'],
        'link' => $downloadData['downloadLink']
    ];
}

// Get the file ID from the URL
if (isset($_GET['id'])) {
    $fileId = $_GET['id']; // Retrieve file ID from the query string
    $result = getDownloadLink($fileId);

    // Set the content type to JSON
    header('Content-Type: application/json');
    
    // Return the result as JSON
    echo json_encode($result);
} else {
    // Return an error message as JSON if no ID is provided
    header('Content-Type: application/json');
    echo json_encode(['error' => 'No file ID provided.']);
}
?>
