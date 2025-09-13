<?php
session_start();
// Security Check
if (!isset($_SESSION['admin_logged_in'])) { header('Location: /admin'); exit; }
require '../db_connect.php';

$error_message = '';
$success_message = '';

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $thumbnail_url = trim($_POST['thumbnail_url']);
    $genre = trim($_POST['genre']);
    $type = $_POST['type'];

    if (empty($title) || empty($description) || empty($thumbnail_url) || empty($genre)) {
        $error_message = "Please fill in all required fields.";
    } else {
        $sql = "INSERT INTO animes (title, description, thumbnail_url, genre, type) VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute([$title, $description, $thumbnail_url, $genre, $type])) {
            header("Location: /admin/manage_anime?status=added");
            exit;
        } else {
            $error_message = "Failed to add anime. Please try again.";
        }
    }
}
$current_page = 'anime'; // For active sidebar link
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Anime - Admin Panel</title>
    <link rel="stylesheet" href="admin_style.css">
</head>
<body>
    <div class="dashboard-wrapper">
        <aside class="admin-sidebar">
            <div class="admin-sidebar-header">Aether Stream</div>
            <nav class="admin-nav">
                <a href="/admin/dashboard" class="admin-nav-item">Dashboard</a>
                <a href="/admin/manage_anime" class="admin-nav-item <?php echo ($current_page === 'anime') ? 'active' : ''; ?>">Manage Anime</a>
                <a href="/admin/manage_users" class="admin-nav-item">Manage Users</a>
                <a href="/admin/manage_codes" class="admin-nav-item">Manage VIP Codes</a>
                <a href="/home" class="admin-nav-item" target="_blank">View Live Site</a>
            </nav>
        </aside>

        <main class="dashboard-main-content">
            <header class="dashboard-header">
                <h1>Add New Anime</h1>
                <a href="/admin/manage_anime" class="btn btn-secondary">&larr; Back to Anime List</a>
            </header>

            <div class="form-container">
                <?php if ($error_message): ?>
                    <div class="message error"><p><?php echo $error_message; ?></p></div>
                <?php endif; ?>

                <form action="/admin/anime_add" method="POST">
                    <div class="form-group">
                        <label for="title">Anime Title</label>
                        <input type="text" id="title" name="title" required>
                    </div>
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="5" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="thumbnail_url">Thumbnail URL</label>
                        <input type="text" id="thumbnail_url" name="thumbnail_url" placeholder="https://...image.jpg" required>
                    </div>
                    <div class="form-group">
                        <label for="genre">Genre(s)</label>
                        <input type="text" id="genre" name="genre" placeholder="e.g., Action, Fantasy" required>
                    </div>
                    <div class="form-group">
                        <label for="type">Type</label>
                        <select id="type" name="type">
                            <option value="New Arrival">New Arrival</option>
                            <option value="Popular">Popular</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Save Anime</button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>