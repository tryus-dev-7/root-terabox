<?php

$TELEGRAM_BOT_TOKEN = '7638807691:AAEZbT6fD_cmUBIYcbLtAOcWJfqkOEpTE4I';
$API_ENDPOINT = "https://ytshorts.savetube.me/api/v1/terabox-downloader";
$ADMIN_CHAT_ID = '1237570780';

// Load user data from file or initialize if not exists
$USER_DATA_FILE = 'user_data.json';
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

// Send message to Telegram
function sendMessage($chatId, $text, $keyboard = null, $parseMode = "Markdown") {
    global $TELEGRAM_BOT_TOKEN;
    $url = "https://api.telegram.org/bot$TELEGRAM_BOT_TOKEN/sendMessage";
    $data = [
        'chat_id' => $chatId,
        'text' => $text,
        'parse_mode' => $parseMode
    ];
    if ($keyboard) {
        $data['reply_markup'] = json_encode($keyboard);
    }
    file_get_contents($url . '?' . http_build_query($data));
}

// Delete message
function deleteMessage($chatId, $messageId) {
    global $TELEGRAM_BOT_TOKEN;
    $url = "https://api.telegram.org/bot$TELEGRAM_BOT_TOKEN/deleteMessage";
    $data = ['chat_id' => $chatId, 'message_id' => $messageId];
    file_get_contents($url . '?' . http_build_query($data));
}

// Fetch download links from API
function fetchDownloadLinks($url) {
    global $API_ENDPOINT;
    $payload = json_encode(['url' => $url]);
    $options = [
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/json\r\n",
            'content' => $payload
        ]
    ];
    $context = stream_context_create($options);
    $result = file_get_contents($API_ENDPOINT, false, $context);
    return json_decode($result, true)['response'][0] ?? null;
}

// Extract video ID from URL
function extractVideoId($url) {
    if (preg_match('/\/s\/1?([a-zA-Z0-9]+)/', $url, $matches)) {
        return $matches[1];
    }
    return null;
}

// Handle updates from Telegram
$content = file_get_contents("php://input");
$update = json_decode($content, true);

if (isset($update['message'])) {
    $message = $update['message'];
    $chatId = $message['chat']['id'];
    $text = $message['text'];

    if ($text === '/start') {
        // Handle start command
        if (!in_array($chatId, $userData)) {
            $userData[] = $chatId;
            saveUserData($userData);

            $totalUsers = count($userData);
            sendMessage($ADMIN_CHAT_ID, "➡️ *New User Started The Bot :*\n🆔 User ID : $chatId\n🌐 Total Users : $totalUsers", null, "Markdown");
        }
        sendMessage($chatId, "*🙋‍♂ Hello, $firstName*\n➖➖➖➖➖➖➖➖➖➖➖➖➖\nWelcome Back!\n\n[Join Here](https://t.me/RootNetworkz) | [Support](https://t.me/IronRoot999)\n\nJust send me the link....", null, "Markdown");

    } else {
        // Handle URL and send download links
        sendMessage($chatId, "*⚡ Generating video...*", null, "Markdown");

        $downloadLinks = fetchDownloadLinks($text);
        if ($downloadLinks) {
            $title = addslashes($downloadLinks['title']);
            $hdVideoLink = $downloadLinks['resolutions']['HD Video'] ?? null;
            $fastDownloadLink = $downloadLinks['resolutions']['Fast Download'] ?? null;
            $videoId = extractVideoId($text);
            $watchVideoLink = "http://t.me/teraboxdownloadofficialbot/playtera?startapp=$videoId";

            // Create keyboard
            $keyboard = [
                'inline_keyboard' => [
                    [['text' => '⬇️ Download Video', 'url' => $hdVideoLink]],
                    [['text' => '🚀 Download Video (Fast)', 'url' => $fastDownloadLink]],
                    [['text' => '▶️ Watch Video', 'url' => $watchVideoLink]]
                ]
            ];

            sendMessage($chatId, "*➡️ Title :* $title\n\n_Choose an option below:_", $keyboard, "Markdown");
        } else {
            sendMessage($chatId, "*⚠️ Invalid URL*\n\n_Please check the URL and try again._", null, "Markdown");
        }
    }
}
?>
