<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) { header('Location: /admin'); exit; }
require '../db_connect.php';

// Get Manhwa ID from URL and validate it
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: /admin/manage_manhwa"); exit;
}
$manhwa_id = $_GET['id'];

// Fetch Manhwa title for display
$stmt_manhwa = $pdo->prepare("SELECT title FROM manhwas WHERE id = ?");
$stmt_manhwa->execute([$manhwa_id]);
$manhwa = $stmt_manhwa->fetch(PDO::FETCH_ASSOC);
if (!$manhwa) {
    header("Location: /admin/manage_manhwa"); exit;
}

// Handle Add New Chapter Form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_chapter'])) {
    $chapter_number = trim($_POST['chapter_number']);
    $chapter_title = trim($_POST['chapter_title']);

    if (!empty($chapter_number)) {
        $sql = "INSERT INTO manhwa_chapters (manhwa_id, chapter_number, chapter_title) VALUES (?, ?, ?)";
        $pdo->prepare($sql)->execute([$manhwa_id, $chapter_number, $chapter_title]);
        header("Location: /admin/manage_manhwa_chapters?id=$manhwa_id&status=added");
        exit;
    }
}

// Handle Delete Chapter
if (isset($_GET['delete_chapter_id']) && is_numeric($_GET['delete_chapter_id'])) {
    $sql = "DELETE FROM manhwa_chapters WHERE id = ?";
    $pdo->prepare($sql)->execute([$_GET['delete_chapter_id']]);
    header("Location: /admin/manage_manhwa_chapters?id=$manhwa_id&status=deleted");
    exit;
}

// Fetch all existing chapters for this manhwa
$chapters = $pdo->prepare("SELECT * FROM manhwa_chapters WHERE manhwa_id = ? ORDER BY CAST(chapter_number AS UNSIGNED) DESC, chapter_number DESC");
$chapters->execute([$manhwa_id]);
$chapters_list = $chapters->fetchAll(PDO::FETCH_ASSOC);

$current_page_nav = 'manhwa_admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Chapters - Admin Panel</title>
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
                <h1>Manage Chapters for: "<?php echo htmlspecialchars($manhwa['title']); ?>"</h1>
                <a href="/admin/manage_manhwa" class="btn btn-secondary">&larr; Back to Manhwa List</a>
            </header>

            <div class="form-container">
                <h3>Add New Chapter</h3>
                <form action="/admin/manage_manhwa_chapters?id=<?php echo $manhwa_id; ?>" method="POST">
                    <div class="form-group">
                        <label for="chapter_number">Chapter Number</label>
                        <input type="text" name="chapter_number" placeholder="e.g., 1 or 1.5" required>
                    </div>
                    <div class="form-group">
                        <label for="chapter_title">Chapter Title (Optional)</label>
                        <input type="text" name="chapter_title">
                    </div>
                    <div class="form-group">
                        <button type="submit" name="add_chapter" class="btn btn-primary">Add Chapter</button>
                    </div>
                </form>
            </div>
            
            <div class="content-table" style="margin-top: 30px;">
                <h3>Existing Chapters</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Chapter Number</th>
                            <th>Title</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($chapters_list)): ?>
                            <tr><td colspan="3" style="text-align:center;">No chapters found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($chapters_list as $chapter): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($chapter['chapter_number']); ?></td>
                                    <td><?php echo htmlspecialchars($chapter['chapter_title']); ?></td>
                                    <td class="actions">
                                        <a href="/admin/manage_chapter_images?id=<?php echo $chapter['id']; ?>" class="btn btn-success">Manage Images</a>
                                        <a href="/admin/manage_manhwa_chapters?id=<?php echo $manhwa_id; ?>&delete_chapter_id=<?php echo $chapter['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure? This will delete all images in this chapter.');">Delete</a>
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