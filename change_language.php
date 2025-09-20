<?php
session_start();

// List of allowed languages
$allowed_langs = ['en', 'mm'];

if (isset($_GET['lang']) && in_array($_GET['lang'], $allowed_langs)) {
    $_SESSION['lang'] = $_GET['lang'];
}

// Redirect back to the previous page
if (isset($_SERVER['HTTP_REFERER'])) {
    header('Location: ' . $_SERVER['HTTP_REFERER']);
} else {
    // Fallback if the referrer is not available
    header('Location: index.php');
}
exit();

