<?php
session_start();
require 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['is_logged_in'])) {
    header('Location: /');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $anime_id = $_POST['anime_id'];
    $user_id = $_SESSION['user_id'];
    $rating = $_POST['rating'];

    // Validate rating
    if ($rating >= 1 && $rating <= 5) {
        // Use INSERT ... ON DUPLICATE KEY UPDATE to handle both new and updated ratings in one query
        $sql = "INSERT INTO ratings (user_id, anime_id, rating) VALUES (:user_id, :anime_id, :rating)
                ON DUPLICATE KEY UPDATE rating = :rating";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':user_id' => $user_id,
            ':anime_id' => $anime_id,
            ':rating' => $rating
        ]);

        // After updating the rating, recalculate the average and count
        $update_anime_sql = "
            UPDATE animes a
            SET
                a.average_rating = (SELECT AVG(r.rating) FROM ratings r WHERE r.anime_id = :anime_id),
                a.rating_count = (SELECT COUNT(r.id) FROM ratings r WHERE r.anime_id = :anime_id)
            WHERE a.id = :anime_id
        ";
        
        $update_stmt = $pdo->prepare($update_anime_sql);
        $update_stmt->execute([':anime_id' => $anime_id]);
    }
}

// Redirect back to the anime page
if (isset($_SERVER['HTTP_REFERER'])) {
    header('Location: ' . $_SERVER['HTTP_REFERER']);
} else {
    header('Location: /watch?id=' . $anime_id);
}
exit();