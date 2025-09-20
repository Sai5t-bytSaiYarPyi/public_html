<?php
session_start();
// Security & DB Connection
if (!isset($_SESSION['admin_logged_in'])) { header('Location: index.php'); exit; }
require '../db_connect.php';

// Check if episode ID and anime ID are provided
if (!isset($_GET['id']) || !isset($_GET['anime_id'])) {
    header("Location: manage_anime.php"); exit;
}
$episode_id = $_GET['id'];
$anime_id = $_GET['anime_id'];

// Handle form submission for updating
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ep_number = trim($_POST['episode_number']);
    $ep_title = trim($_POST['episode_title']);
    $ep_video_url = trim($_POST['video_url']);
    
    $sql = "UPDATE episodes SET episode_number = ?, title = ?, video_url = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute([$ep_number, $ep_title, $ep_video_url, $episode_id])) {
        header("Location: manage_episodes.php?id=$anime_id&status=ep_updated");
        exit;
    } else {
        $error_message = "Failed to update episode.";
    }
}

// Fetch existing episode data
$stmt = $pdo->prepare("SELECT * FROM episodes WHERE id = ?");
$stmt->execute([$episode_id]);
$episode = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$episode) {
    header("Location: manage_episodes.php?id=$anime_id"); exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Episode - Admin Panel</title>
    <link rel="stylesheet" href="admin_style.css">
</head>
<body>
    <div class="dashboard-wrapper">
         <header class="dashboard-header">
            <div class="header-title">Aether Stream Admin Panel</div>
            <div class="header-user">
                Welcome, <strong><?php echo htmlspecialchars($_SESSION['admin_username']); ?></strong>
                <a href="logout.php" class="logout-link">Logout</a>
            </div>
        </header>
        <main class="dashboard-main">
            <div class="page-header">
                <h1>Edit Episode #<?php echo htmlspecialchars($episode['episode_number']); ?></h1>
                <a href="manage_episodes.php?id=<?php echo $anime_id; ?>" class="btn-secondary">&larr; Back to Episode List</a>
            </div>
            <div class="form-container">
                <form action="episode_edit.php?id=<?php echo $episode_id; ?>&anime_id=<?php echo $anime_id; ?>" method="POST">
                    <div class="form-group">
                        <label for="episode_number">Episode Number</label>
                        <input type="number" name="episode_number" value="<?php echo htmlspecialchars($episode['episode_number']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="episode_title">Episode Title</label>
                        <input type="text" name="episode_title" value="<?php echo htmlspecialchars($episode['title']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="video_url">Video URL</label>
                        <input type="text" name="video_url" value="<?php echo htmlspecialchars($episode['video_url']); ?>" required>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn-primary">Update Episode</button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>