<?php
session_start();
require 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['is_logged_in'])) {
    header('Location: /');
    exit();
}

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        exit('Invalid CSRF token.');
    }

    $review_title = trim($_POST['review_title']);
    $review_body = trim($_POST['review_body']);
    $anime_id = $_POST['anime_id'];
    $user_id = $_SESSION['user_id'];

    // Basic validation
    if (!empty($review_title) && !empty($review_body) && !empty($anime_id)) {
        $sql = "INSERT INTO comments (user_id, anime_id, review_title, review_body) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user_id, $anime_id, $review_title, $review_body]);
    }

    // Redirect back to the watch page
    header("Location: /watch?id=" . $anime_id);
    exit();
} else {
    // If not a POST request, just redirect to home
    header("Location: /home");
    exit();
}
?>