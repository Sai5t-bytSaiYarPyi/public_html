<?php
session_start();
// Security Check
if (!isset($_SESSION['admin_logged_in'])) { header('Location: index.php'); exit; }
require '../db_connect.php';

// Check if episode ID and anime ID are provided
if (!isset($_GET['id']) || !isset($_GET['anime_id'])) {
    header("Location: manage_anime.php"); exit;
}
$episode_id = $_GET['id'];
$anime_id = $_GET['anime_id']; // For redirecting back to the correct page

// Prepare and execute the delete statement
$sql = "DELETE FROM episodes WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$episode_id]);

// Redirect back to the episode list page
header("Location: manage_episodes.php?id=$anime_id&status=ep_deleted");
exit;
?>