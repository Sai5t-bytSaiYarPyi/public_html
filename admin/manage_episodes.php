<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) { header('Location: index.php'); exit; }
require '../db_connect.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) { header("Location: manage_anime.php"); exit; }
$anime_id = $_GET['id'];

$stmt_anime = $pdo->prepare("SELECT title FROM animes WHERE id = ?");
$stmt_anime->execute([$anime_id]);
$anime = $stmt_anime->fetch(PDO::FETCH_ASSOC);

if (!$anime) { header("Location: manage_anime.php"); exit; }

$error_message = '';
$success_message = '';

// Handle SINGLE episode add
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_episode'])) {
    $ep_number = trim($_POST['episode_number']);
    $ep_title = trim($_POST['episode_title']);
    $ep_video_url = trim($_POST['video_url']);

    if (!empty($ep_number) && !empty($ep_title) && !empty($ep_video_url)) {
        $sql = "INSERT INTO episodes (anime_id, episode_number, title, video_url) VALUES (?, ?, ?, ?)";
        $pdo->prepare($sql)->execute([$anime_id, $ep_number, $ep_title, $ep_video_url]);
        $success_message = "Successfully added a single episode.";
    }
}

// Handle BULK episode add with individual titles and URLs
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_add_episodes'])) {
    $start_ep = filter_input(INPUT_POST, 'start_episode', FILTER_VALIDATE_INT);
    $end_ep = filter_input(INPUT_POST, 'end_episode', FILTER_VALIDATE_INT);
    $titles_list = trim($_POST['titles_list']);
    $urls_list = trim($_POST['urls_list']);

    $titles_array = !empty($titles_list) ? explode("\n", str_replace("\r", "", $titles_list)) : [];
    $urls_array = !empty($urls_list) ? explode("\n", str_replace("\r", "", $urls_list)) : [];
    
    $titles_count = count($titles_array);
    $urls_count = count($urls_array);
    $ep_count = ($end_ep - $start_ep) + 1;

    if ($start_ep && $end_ep && $end_ep >= $start_ep && $titles_count == $ep_count && $urls_count == $ep_count) {
        $sql = "INSERT INTO episodes (anime_id, episode_number, title, video_url) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);

        $item_index = 0;
        for ($i = $start_ep; $i <= $end_ep; $i++) {
            $ep_number = $i;
            $ep_title = trim($titles_array[$item_index]);
            $ep_video_url = trim($urls_array[$item_index]);
            
            $stmt->execute([$anime_id, $ep_number, $ep_title, $ep_video_url]);
            $item_index++;
        }
        $success_message = "Successfully added " . $ep_count . " episodes in bulk.";
    } else {
        if ($titles_count != $ep_count) {
            $error_message = "Error: The number of titles ($titles_count) does not match the number of episodes ($ep_count).";
        } elseif ($urls_count != $ep_count) {
            $error_message = "Error: The number of URLs ($urls_count) does not match the number of episodes ($ep_count).";
        } else {
            $error_message = "Invalid input for bulk add. Please check all fields.";
        }
    }
}

$stmt_episodes = $pdo->prepare("SELECT * FROM episodes WHERE anime_id = ? ORDER BY episode_number ASC");
$stmt_episodes->execute([$anime_id]);
$episodes = $stmt_episodes->fetchAll(PDO::FETCH_ASSOC);
$current_page_nav = 'anime';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Episodes - Admin Panel</title>
    <link rel="stylesheet" href="admin_style.css">
</head>
<body>
    <div class="dashboard-wrapper">
        <aside class="admin-sidebar">
            <div class="admin-sidebar-header">Aether Stream</div>
            <nav class="admin-nav">
                <a href="/admin/dashboard" class="admin-nav-item">Dashboard</a>
                <a href="/admin/manage_anime" class="admin-nav-item active">Manage Anime</a>
                <a href="/admin/manage_manhwa" class="admin-nav-item">Manage Manhwa</a>
                <a href="/admin/manage_users" class="admin-nav-item">Manage Users</a>
                <a href="/admin/manage_codes" class="admin-nav-item">Manage VIP Codes</a>
                <a href="/home" class="admin-nav-item" target="_blank">View Live Site</a>
            </nav>
        </aside>
        <main class="dashboard-main-content">
            <header class="dashboard-header">
                <h1>Manage Episodes for: "<?php echo htmlspecialchars($anime['title']); ?>"</h1>
                <a href="/admin/manage_anime" class="btn btn-secondary">&larr; Back to Anime List</a>
            </header>
            
            <?php if ($error_message): ?><div class="message error"><p><?php echo $error_message; ?></p></div><?php endif; ?>
            <?php if ($success_message): ?><div class="message success"><p><?php echo $success_message; ?></p></div><?php endif; ?>

            <div class="form-container">
                <h3>Bulk Add Episodes</h3>
                <form action="manage_episodes.php?id=<?php echo $anime_id; ?>" method="POST">
                    <div style="display: flex; gap: 20px;">
                        <div class="form-group" style="flex: 1;">
                            <label for="start_episode">Start Episode</label>
                            <input type="number" name="start_episode" placeholder="e.g., 1" required>
                        </div>
                        <div class="form-group" style="flex: 1;">
                            <label for="end_episode">End Episode</label>
                            <input type="number" name="end_episode" placeholder="e.g., 12" required>
                        </div>
                    </div>
                     <div class="form-group">
                        <label for="titles_list">Episode Titles (One title per line, in order)</label>
                        <textarea name="titles_list" rows="12" placeholder="Title for Episode 1&#x0a;Title for Episode 2&#x0a;Title for Episode 3" required></textarea>
                        <small style="color: #888;">The number of lines must match the total number of episodes.</small>
                    </div>
                    <div class="form-group">
                        <label for="urls_list">Video URLs (One URL per line, in order)</label>
                        <textarea name="urls_list" rows="12" placeholder="URL for Episode 1&#x0a;URL for Episode 2&#x0a;URL for Episode 3" required></textarea>
                         <small style="color: #888;">The number of lines must match the total number of episodes.</small>
                    </div>
                    <div class="form-group">
                        <button type="submit" name="bulk_add_episodes" class="btn btn-primary">Add Episodes in Bulk</button>
                    </div>
                </form>
            </div>

            <hr style="border-color: #333; margin: 40px 0;">

            <div class="form-container">
                <h3>Add a Single Episode</h3>
                <form action="manage_episodes.php?id=<?php echo $anime_id; ?>" method="POST">
                    <div class="form-group">
                        <label for="episode_number">Episode Number</label>
                        <input type="number" name="episode_number" required>
                    </div>
                    <div class="form-group">
                        <label for="episode_title">Episode Title</label>
                        <input type="text" name="episode_title" required>
                    </div>
                    <div class="form-group">
                        <label for="video_url">Video URL</label>
                        <input type="text" name="video_url" placeholder="https://...video.mp4" required>
                    </div>
                    <div class="form-group">
                        <button type="submit" name="add_episode" class="btn btn-secondary">Add Single Episode</button>
                    </div>
                </form>
            </div>
            
            <div class="content-table" style="margin-top: 30px;">
                <h3>Existing Episodes</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Ep. Number</th>
                            <th>Title</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($episodes)): ?>
                            <tr><td colspan="3" style="text-align:center;">No episodes found for this series yet.</td></tr>
                        <?php else: ?>
                            <?php foreach ($episodes as $episode): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($episode['episode_number']); ?></td>
                                    <td><?php echo htmlspecialchars($episode['title']); ?></td>
                                    <td class="actions">
                                        <a href="episode_edit.php?id=<?php echo $episode['id']; ?>&anime_id=<?php echo $anime_id; ?>" class="btn btn-secondary">Edit</a>
                                        <a href="episode_delete.php?id=<?php echo $episode['id']; ?>&anime_id=<?php echo $anime_id; ?>" class="btn btn-danger" onclick="return confirm('Are you sure?');">Delete</a>
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