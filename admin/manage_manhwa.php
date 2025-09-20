<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) { header('Location: /admin'); exit; }
require '../db_connect.php';

// Handle Add New Manhwa Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_manhwa'])) {
    $title = trim($_POST['title']);
    $author = trim($_POST['author']);
    $description = trim($_POST['description']);
    $cover_image_url = trim($_POST['cover_image_url']);
    $status = trim($_POST['status']);

    if (!empty($title) && !empty($cover_image_url)) {
        $sql = "INSERT INTO manhwas (title, author, description, cover_image_url, status) VALUES (?, ?, ?, ?, ?)";
        $pdo->prepare($sql)->execute([$title, $author, $description, $cover_image_url, $status]);
        header("Location: /admin/manage_manhwa?status=added");
        exit;
    }
}

// Handle Delete Manhwa
if (isset($_GET['delete_id']) && is_numeric($_GET['delete_id'])) {
    $sql = "DELETE FROM manhwas WHERE id = ?";
    $pdo->prepare($sql)->execute([$_GET['delete_id']]);
    header("Location: /admin/manage_manhwa?status=deleted");
    exit;
}

// Fetch all existing manhwas
$manhwas = $pdo->query("SELECT * FROM manhwas ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
$current_page_nav = 'manhwa_admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Manhwa - Admin Panel</title>
    <link rel="stylesheet" href="admin_style.css">
</head>
<body>
    <div class="dashboard-wrapper">
        <aside class="admin-sidebar">
            <div class="admin-sidebar-header">Aether Stream</div>
            <nav class="admin-nav">
                <a href="/admin/dashboard" class="admin-nav-item">Dashboard</a>
                <a href="/admin/manage_anime" class="admin-nav-item">Manage Anime</a>
                <a href="/admin/manage_manhwa" class="admin-nav-item active">Manage Manhwa</a>
                <a href="/admin/manage_users" class="admin-nav-item">Manage Users</a>
                <a href="/admin/manage_codes" class="admin-nav-item">Manage VIP Codes</a>
                <a href="/home" class="admin-nav-item" target="_blank">View Live Site</a>
            </nav>
        </aside>
        <main class="dashboard-main-content">
            <header class="dashboard-header">
                <h1>Manage Manhwa Series</h1>
                <a href="/admin/dashboard" class="btn btn-secondary">&larr; Back to Dashboard</a>
            </header>

            <div class="form-container">
                <h3>Add New Manhwa</h3>
                <form action="/admin/manage_manhwa" method="POST">
                    <div class="form-group">
                        <label for="title">Manhwa Title</label>
                        <input type="text" name="title" required>
                    </div>
                    <div class="form-group">
                        <label for="author">Author</label>
                        <input type="text" name="author">
                    </div>
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea name="description" rows="4"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="cover_image_url">Cover Image URL</label>
                        <input type="text" name="cover_image_url" placeholder="https://...image.jpg" required>
                    </div>
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select name="status">
                            <option value="Ongoing">Ongoing</option>
                            <option value="Completed">Completed</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <button type="submit" name="add_manhwa" class="btn btn-primary">Add Manhwa</button>
                    </div>
                </form>
            </div>
            
            <div class="content-table" style="margin-top: 30px;">
                <h3>Existing Manhwa Series</h3>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Cover</th>
                            <th>Title</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($manhwas)): ?>
                            <tr><td colspan="5" style="text-align:center;">No manhwa series found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($manhwas as $manhwa): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($manhwa['id']); ?></td>
                                    <td><img src="<?php echo htmlspecialchars($manhwa['cover_image_url']); ?>" alt="Cover" class="thumbnail-preview"></td>
                                    <td><?php echo htmlspecialchars($manhwa['title']); ?></td>
                                    <td><?php echo htmlspecialchars($manhwa['status']); ?></td>
                                    <td class="actions">
                                        <a href="/admin/manage_manhwa_chapters?id=<?php echo $manhwa['id']; ?>" class="btn btn-success">Chapters</a>
                                        <a href="/admin/manage_manhwa?delete_id=<?php echo $manhwa['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure? This will delete all chapters and images of this manhwa.');">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>