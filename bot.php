<?php

$TELEGRAM_BOT_TOKEN = '7638807691:AAEZbT6fD_cmUBIYcbLtAOcWJfqkOEpTE4I';
$API_ENDPOINT = "https://ytshorts.savetube.me/api/v1/terabox-downloader";
$ADMIN_CHAT_ID = '1237570780';  // Replace with your admin chat ID

$USER_DATA_FILE = 'user_data.json';

// Function to send a POST request to Telegram API
function sendMessage($chatId, $text, $keyboard = null) {
    global $TELEGRAM_BOT_TOKEN;
    $url = "https://api.telegram.org/bot$TELEGRAM_BOT_TOKEN/sendMessage";
    $postData = array(
        'chat_id' => $chatId,
        'text' => $text,
        'parse_mode' => 'Markdown',
    );
    if ($keyboard) {
        $postData['reply_markup'] = json_encode($keyboard);
    }
    file_get_contents($url . '?' . http_build_query($postData));
}

// Load or initialize user data
if (file_exists($USER_DATA_FILE)) {
    $userData = json_decode(file_get_contents($USER_DATA_FILE), true);
} else {
    $userData = array();
}

// Save user data
function saveUserData($userData) {
    global $USER_DATA_FILE;
    file_put_contents($USER_DATA_FILE, json_encode($userData));
}

// Function to fetch download links
function fetchDownloadLinks($url) {
    global $API_ENDPOINT;
    $payload = json_encode(array("url" => $url));
    $options = array(
        'http' => array(
            'method'  => 'POST',
            'header'  => "Content-type: application/json\r\n",
            'content' => $payload
        )
    );
    $context  = stream_context_create($options);
    $result = file_get_contents($API_ENDPOINT, false, $context);
    $data = json_decode($result, true);
    return $data['response'][0]['resolutions'] ?? null;
}

// Extract video ID from URL
function extractVideoId($url) {
    preg_match('/\/s\/1?([a-zA-Z0-9]+)/', $url, $matches);
    return $matches[1] ?? null;
}

// Main logic to handle incoming Telegram updates
$content = file_get_contents("php://input");
$update = json_decode($content, true);

if (isset($update['message'])) {
    $message = $update['message'];
    $chatId = $message['chat']['id'];
    $text = $message['text'];
    
    if ($text === '/start') {
        if (!in_array($chatId, $userData)) {
            $userData[] = $chatId;
            saveUserData($userData);

            $totalUsers = count($userData);
            $adminMessage = "➡️ *New User Started The Bot :*\n🆔 User ID : $chatId\n🌐 Total Users : $totalUsers";
            sendMessage($ADMIN_CHAT_ID, $adminMessage);
        }
        $welcomeMessage = "*🙋‍♂ Hello, Welcome Back!*\n\nJust send me the link....";
        sendMessage($chatId, $welcomeMessage);
    } else {
        $downloadLinks = fetchDownloadLinks($text);
        if ($downloadLinks) {
            $hdVideoLink = $downloadLinks['HD Video'] ?? null;
            $fastDownloadLink = $downloadLinks['Fast Download'] ?? null;
            $videoId = extractVideoId($text);
            $watchVideoLink = "http://t.me/teraboxdownloadofficialbot/playtera?startapp=$videoId";

            $keyboard = array(
                'inline_keyboard' => array(
                    array(array('text' => '⬇️ Download Video', 'url' => $hdVideoLink)),
                    array(array('text' => '🚀 Download Video (Fast)', 'url' => $fastDownloadLink)),
                    array(array('text' => '▶️ Watch Video', 'url' => $watchVideoLink))
                )
            );
            $messageText = "*➡️ Video Title*\n\n_Choose an option below:_";
            sendMessage($chatId, $messageText, $keyboard);
        } else {
            sendMessage($chatId, "*⚠️ Invalid URL*\n\n_Please check the URL and try again._");
        }
    }
}
?>
