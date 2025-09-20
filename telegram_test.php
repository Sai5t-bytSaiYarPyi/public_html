<?php
// This script will help diagnose Telegram connection issues.
require 'config.php';

echo "<h1>Telegram Connection Test</h1>";

// Check if constants are defined
if (!defined('TELEGRAM_BOT_TOKEN') || !defined('TELEGRAM_CHAT_ID')) {
    die("<p>Error: TELEGRAM_BOT_TOKEN or TELEGRAM_CHAT_ID is not defined in config.php</p>");
}

$bot_token = TELEGRAM_BOT_TOKEN;
$chat_id = TELEGRAM_CHAT_ID;
$message = "This is a test message from Aether Stream website. Time: " . date('Y-m-d H:i:s');

echo "<p>Attempting to send a test message...</p>";
echo "<p><b>Bot Token:</b> " . substr($bot_token, 0, 10) . "...</p>";
echo "<p><b>Chat ID:</b> " . $chat_id . "</p>";
echo "<hr>";

// Using Telegram's sendMessage method (simpler than sending a photo)
$api_url = "https://api.telegram.org/bot{$bot_token}/sendMessage";

$post_fields = [
    'chat_id' => $chat_id,
    'text' => $message,
    'parse_mode' => 'Markdown'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// Execute the request and get the response
$response = curl_exec($ch);
$curl_error = curl_error($ch);
curl_close($ch);

echo "<h2>Test Results:</h2>";

if ($curl_error) {
    echo "<p style='color:red;'><b>cURL Error:</b> " . htmlspecialchars($curl_error) . "</p>";
    echo "<p>This error usually means your hosting server cannot connect to the Telegram server. This is very common on free hosting plans.</p>";
} else {
    echo "<p style='color:green;'><b>cURL request was successful.</b></p>";
    echo "<p><b>Response from Telegram Server:</b></p>";
    echo "<pre>" . htmlspecialchars($response) . "</pre>";
    echo "<p>Please check your Telegram group/channel now to see if the test message arrived.</p>";
}
?>