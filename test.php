<?php

// Telegram bot token and channel ID
$TELEGRAM_BOT_TOKEN = "7638807691:AAEZbT6fD_cmUBIYcbLtAOcWJfqkOEpTE4I";
$CHANNEL_ID = "@RootNetworkz";  // Use the channel username with "@" or the numeric channel ID

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

// Usage Example
$userId = 6491649444;  // Replace with the actual user ID to verify
if (isUserSubscribed($userId)) {
    echo "User is subscribed to the channel (true)";
} else {
    echo "User is not subscribed to the channel (false)";
}

?>