<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) { header('Location: index.php'); exit; }
require '../db_connect.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) { header("Location: manage_anime.php"); exit; }
$anime_id = $_GET['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $thumbnail_url = trim($_POST['thumbnail_url']);
    $genre = trim($_POST['genre']);
    $type = $_POST['type'];

    $sql = "UPDATE animes SET title = ?, description = ?, thumbnail_url = ?, genre = ?, type = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute([$title, $description, $thumbnail_url, $genre, $type, $anime_id])) {
        header("Location: manage_anime.php?status=updated");
        exit;
    }
}

$stmt = $pdo->prepare("SELECT * FROM animes WHERE id = ?");
$stmt->execute([$anime_id]);
$anime = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$anime) { header("Location: manage_anime.php"); exit; }
$current_page = 'anime';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Anime - Admin Panel</title>
    <link rel="stylesheet" href="admin_style.css">
</head>
<body>
    <div class="dashboard-wrapper">
        <aside class="admin-sidebar">
            <div class="admin-sidebar-header">Aether Stream</div>
            <nav class="admin-nav">
                <a href="dashboard.php" class="admin-nav-item">Dashboard</a>
                <a href="manage_anime.php" class="admin-nav-item <?php echo ($current_page === 'anime') ? 'active' : ''; ?>">Manage Anime</a>
                <a href="manage_users.php" class="admin-nav-item">Manage Users</a>
                <a href="manage_codes.php" class="admin-nav-item">Manage VIP Codes</a>
                <a href="../home.php" class="admin-nav-item" target="_blank">View Live Site</a>
            </nav>
        </aside>

        <main class="dashboard-main-content">
            <header class="dashboard-header">
                <h1>Edit "<?php echo htmlspecialchars($anime['title']); ?>"</h1>
                <a href="manage_anime.php" class="btn btn-secondary">&larr; Back to Anime List</a>
            </header>

            <div class="form-container">
                <form action="anime_edit.php?id=<?php echo $anime_id; ?>" method="POST">
                    <div class="form-group">
                        <label for="title">Anime Title</label>
                        <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($anime['title']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="5" required><?php echo htmlspecialchars($anime['description']); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="thumbnail_url">Thumbnail URL</label>
                        <input type="text" id="thumbnail_url" name="thumbnail_url" value="<?php echo htmlspecialchars($anime['thumbnail_url']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="genre">Genre(s)</label>
                        <input type="text" id="genre" name="genre" value="<?php echo htmlspecialchars($anime['genre']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="type">Type</label>
                        <select id="type" name="type">
                            <option value="New Arrival" <?php echo ($anime['type'] === 'New Arrival') ? 'selected' : ''; ?>>New Arrival</option>
                            <option value="Popular" <?php echo ($anime['type'] === 'Popular') ? 'selected' : ''; ?>>Popular</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Update Anime</button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>
