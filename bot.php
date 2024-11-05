<?php

$TELEGRAM_BOT_TOKEN = '7638807691:AAEZbT6fD_cmUBIYcbLtAOcWJfqkOEpTE4I';
$API_ENDPOINT = "http://terabox.bijoyknath.site/tera.php";
$ADMIN_CHAT_ID = '1237570780';
$CHANNEL_ID = "@RootNetworkz";  // Use the channel username with "@" or the numeric channel ID
$USER_DATA_FILE = 'user_data.json';
$maintenance_mode = false; // Set to true to enable maintenance mode, false to disable

// Load or initialize user data
$userData = file_exists($USER_DATA_FILE) ? json_decode(file_get_contents($USER_DATA_FILE), true) : [];

// Save user data
function saveUserData($userData)
{
    global $USER_DATA_FILE;
    file_put_contents($USER_DATA_FILE, json_encode($userData));
}

function sanitizeForMarkdown($text)
{
    // Escape all the special characters for MarkdownV2
    $text = str_replace(
        ['_', '*', '[', ']', '(', ')', '~', '`', '>', '#', '+', '=', '|', '{', '}', '!'],
        ' ',
        $text
    );
    return $text; // Return sanitized text
}

// Function to verify subscription to a channel
function isUserSubscribed($userId)
{
    global $TELEGRAM_BOT_TOKEN, $CHANNEL_ID;

    // Telegram API endpoint for checking channel membership
    $url = "https://api.telegram.org/bot$TELEGRAM_BOT_TOKEN/getChatMember";
    $data = [
        'chat_id' => $CHANNEL_ID,
        'user_id' => $userId,
    ];

    // Send the request to the Telegram API
    $response = file_get_contents($url . "?" . http_build_query($data));
    $responseArray = json_decode($response, true);

    // Check if the user is subscribed
    if ($responseArray['ok'] && in_array($responseArray['result']['status'], ['member', 'administrator'])) {
        return true;
    }

    return false;
}

// Send a message to a Telegram chat// Send a message to a Telegram chat
function sendMessage($chatId, $text, $keyboard = null, $parseMode = "Markdown")
{
    global $TELEGRAM_BOT_TOKEN;
    $url = "https://api.telegram.org/bot$TELEGRAM_BOT_TOKEN/sendMessage";
    $data = [
        'chat_id' => $chatId,
        'text' => $text,
        'parse_mode' => $parseMode,
        'reply_markup' => $keyboard ? json_encode($keyboard) : null
    ];

    // Send request and return response data
    return json_decode(file_get_contents($url . '?' . http_build_query($data)), true);
}

// Delete a message by chat ID and message ID
function deleteMessage($chatId, $messageId)
{
    global $TELEGRAM_BOT_TOKEN;
    $url = "https://api.telegram.org/bot$TELEGRAM_BOT_TOKEN/deleteMessage";
    file_get_contents($url . '?' . http_build_query(['chat_id' => $chatId, 'message_id' => $messageId]));
}

// Fetch download links from the API
function fetchDownloadLinks($id)
{
    global $API_ENDPOINT;
    $response = json_decode(file_get_contents("$API_ENDPOINT?id=$id"), true);

    // Check if the response contains expected data
    return isset($response['title'], $response['download_link'], $response['short_id']) ? [
        'title' => $response['title'],
        'link' => $response['download_link'],
        'id' => $response['short_id']
    ] : null;
}

// Extract video ID from the given URL
function extractVideoId($url)
{
    if (preg_match('/\/s\/(.+)/', $url, $matches)) {
        return $matches[1];
    }
    return null;
}

function extractVideoId2($url)
{
    if (preg_match('/\/s\/1(.+)/', $url, $matches)) {
        return $matches[1];
    }
    return null;
}

// Show typing action in the chat
function sendChatAction($chatId, $action = "typing")
{
    global $TELEGRAM_BOT_TOKEN;
    $url = "https://api.telegram.org/bot$TELEGRAM_BOT_TOKEN/sendChatAction";
    file_get_contents($url . '?' . http_build_query(['chat_id' => $chatId, 'action' => $action]));
}

// Handle updates from Telegram
$content = file_get_contents("php://input");
$update = json_decode($content, true);

if (isset($update['message'])) {
    $message = $update['message'];
    $chatId = $message['chat']['id'];
    $text = $message['text'];
    $username = $message['chat']['username'] ?? 'None';


    if ($maintenance_mode) {
        // Handle start command
        if (!in_array($chatId, $userData)) {
            $userData[] = $chatId;
            saveUserData($userData);
            $totalUsers = count($userData);
            sendMessage($ADMIN_CHAT_ID, "➡️ *New User Started The Bot :*\n🆔 User ID : $chatId\n👨🏻‍💻 Username : $username\n🌐 Total Users : $totalUsers", null, "Markdown");
        }
        // If maintenance mode is on, send a maintenance message
        sendMessage($chatId, "*🚧 Maintenance Mode 🚧*\n\n_➤ Please check back later...._", null, "Markdown");
    } else {
        if ($text === '/start') {
            // Handle start command
            if (!in_array($chatId, $userData)) {
                $userData[] = $chatId;
                saveUserData($userData);
                $totalUsers = count($userData);
                sendMessage($ADMIN_CHAT_ID, "➡️ *New User Started The Bot :*\n🆔 User ID : $chatId\n👨🏻‍💻 Username : $username\n🌐 Total Users : $totalUsers", null, "Markdown");
            }
            $firstName = $message['chat']['first_name'] ?? 'there';
            sendMessage($chatId, "*🙋‍♂ Hello, $firstName!*\n➖➖➖➖➖➖➖➖➖➖➖➖➖\nWelcome Back!\n\n[Join Here](https://t.me/RootNetworkz) | [Support](https://t.me/IronRoot999)\n\nJust send me the link....", null, "Markdown");
        } else {
            // Show typing status
            sendChatAction($chatId, "typing");
            //sleep(1); // Simulate a delay for better user experience

            // Handle URL and send download links
            $genMessage = sendMessage($chatId, "*⚡ Generating video...*", null, "Markdown");
            $videoId = extractVideoId($text);
            $watchId = extractVideoId2($text);

            $downloadLinks = fetchDownloadLinks($videoId);

            if ($downloadLinks) {
                $title = sanitizeForMarkdown($downloadLinks['title']);
                $videoLink = $downloadLinks['link'];
                $shortId = $downloadLinks['id'];
                $watchVideoLink = "http://t.me/teraboxdownloadofficialbot/playtera?startapp=$shortId&mode=compact";
                $watchVideoLink2 = "https://www.1024terabox.com/sharing/embed?surl=$watchId&autoplay=true&mute=false";


                // Create keyboard for download options
                $keyboard = [
                    'inline_keyboard' => [
                        [['text' => '▶️ Player 1.0', 'url' => $watchVideoLink]],
                        [['text' => '▶️ Player 2.0', 'url' => $watchVideoLink2]],
                        [['text' => '⬇️ Download Video', 'url' => $videoLink]],
                    ]
                ];


                sendMessage($chatId, "*➡️ Title :* $title\n\n_Choose an option below:_", $keyboard, "Markdown");
                // Delete generating message if it was sent
                if (isset($genMessage['result'])) {
                    deleteMessage($chatId, $genMessage['result']['message_id']);
                }
            } else {
                // Delete generating message if it was sent
                if (isset($genMessage['result'])) {
                    deleteMessage($chatId, $genMessage['result']['message_id']);
                }
                sendMessage($chatId, "*⚠️ Invalid URL*\n\n_Please check the URL and try again._", null, "Markdown");
            }
        }
    }
}
