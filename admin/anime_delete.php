<?php
session_start();
// Security Check
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}
require '../db_connect.php';

// Check if ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: manage_anime.php");
    exit;
}
$anime_id = $_GET['id'];

// Prepare and execute the delete statement
$sql = "DELETE FROM animes WHERE id = ?";
$stmt = $pdo->prepare($sql);

if ($stmt->execute([$anime_id])) {
    // Redirect back to the list page with a success status
    header("Location: manage_anime.php?status=deleted");
    exit;
} else {
    // Optional: handle delete failure
    header("Location: manage_anime.php?status=error");
    exit;
}
?>