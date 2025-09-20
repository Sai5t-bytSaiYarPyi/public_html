<?php
// Start the session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Define the list of supported languages
$supported_languages = ['en', 'mm'];
$default_language = 'en';

// Set language based on session, fallback to default
$current_lang = $_SESSION['lang'] ?? $default_language;

// If the stored language is not supported, reset to default
if (!in_array($current_lang, $supported_languages)) {
    $current_lang = $default_language;
    $_SESSION['lang'] = $current_lang;
}

// Define the path to the language file
$lang_file_path = __DIR__ . '/lang/' . $current_lang . '.php';

// Include the language file
if (file_exists($lang_file_path)) {
    require_once $lang_file_path;
} else {
    // This should ideally not happen if languages are configured correctly,
    // but as a safety net, fallback to English.
    require_once __DIR__ . '/lang/en.php';
}

