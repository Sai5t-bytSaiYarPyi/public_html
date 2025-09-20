<?php
require 'language_loader.php';
require 'db_connect.php';

// **FIXED**: Removed login check.

// Get Manhwa ID from the URL and validate it
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: /manhwas');
    exit();
}
$manhwa_id = (int)$_GET['id'];

// Fetch the details of the Manhwa series
$manhwa_stmt = $pdo->prepare("SELECT * FROM manhwas WHERE id = ?");
$manhwa_stmt->execute([$manhwa_id]);
$manhwa = $manhwa_stmt->fetch(PDO::FETCH_ASSOC);

if (!$manhwa) {
    header('Location: /manhwas');
    exit();
}

// Fetch all chapters for this Manhwa
$chapters_stmt = $pdo->prepare("SELECT * FROM manhwa_chapters WHERE manhwa_id = ? ORDER BY CAST(chapter_number AS UNSIGNED) ASC, chapter_number ASC");
$chapters_stmt->execute([$manhwa_id]);
$chapters = $chapters_stmt->fetchAll(PDO::FETCH_ASSOC);

$current_page_nav = 'manhwa';
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['lang']; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($manhwa['title']); ?> - Chapters</title>
    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="stylesheet" href="/style.css?v=1.9">
    <style>
        .manhwa-detail-header { display: flex; gap: 30px; margin-bottom: 30px; background-color: #1a1a1a; padding: 20px; border-radius: 12px; }
        .manhwa-cover img { width: 200px; height: auto; border-radius: 8px; }
        .manhwa-info h1 { font-size: 28px; margin-top: 0; }
        .manhwa-info p { color: #ccc; line-height: 1.7; }
        .chapter-list-area { background-color: #1a1a1a; padding: 20px; border-radius: 12px; }
        .chapter-list { display: flex; flex-direction: column; gap: 1px; max-height: 500px; overflow-y: auto; }
        .chapter-item { display: block; padding: 15px; background-color: #2c2c2c; color: #fff; text-decoration: none; font-weight: bold; transition: background-color 0.2s; border-radius: 5px; margin-bottom: 5px;}
        .chapter-item:hover { background-color: #007AFF; }
    </style>
</head>
<body>
    <div class="main-container">
        <aside class="sidebar">
            <div class="sidebar-logo"><?php echo $lang['aether_stream']; ?></div>
            <nav class="sidebar-nav">
                <a href="/home" class="nav-item <?php echo ($current_page_nav === 'home') ? 'active' : ''; ?>"><?php echo $lang['home']; ?></a>
                <a href="/browse" class="nav-item <?php echo ($current_page_nav === 'browse') ? 'active' : ''; ?>"><?php echo $lang['browse']; ?></a>
                <a href="/manhwas" class="nav-item <?php echo ($current_page_nav === 'manhwa') ? 'active' : ''; ?>"><?php echo $lang['manhwa']; ?></a>
                <a href="/my_list" class="nav-item <?php echo (isset($current_page_nav) && $current_page_nav === 'my_list') ? 'active' : ''; ?>"><?php echo $lang['my_list']; ?></a>
                <a href="/history" class="nav-item <?php echo (isset($current_page_nav) && $current_page_nav === 'history') ? 'active' : ''; ?>">History</a>
                <a href="/settings" class="nav-item <?php echo (isset($current_page_nav) && $current_page_nav === 'settings') ? 'active' : ''; ?>"><?php echo $lang['settings']; ?></a>
            </nav>
             <div class="sidebar-footer">
                <?php if (isset($_SESSION['is_logged_in'])): ?>
                    <a href="/logout" class="logout-btn"><?php echo $lang['logout']; ?></a>
                <?php else: ?>
                    <a href="/" class="logout-btn">Login</a>
                <?php endif; ?>
            </div>
        </aside>
        <main class="main-content">
            <header class="main-header">
                 <a href="/manhwas" class="back-link">&larr; Back to Manhwa List</a>
            </header>
            
            <div class="manhwa-detail-header">
                <div class="manhwa-cover">
                    <img src="<?php echo htmlspecialchars($manhwa['cover_image_url']); ?>" alt="<?php echo htmlspecialchars($manhwa['title']); ?>">
                </div>
                <div class="manhwa-info">
                    <h1><?php echo htmlspecialchars($manhwa['title']); ?></h1>
                    <p><strong>Author:</strong> <?php echo htmlspecialchars($manhwa['author']); ?></p>
                    <p><strong>Status:</strong> <?php echo htmlspecialchars($manhwa['status']); ?></p>
                    <p><?php echo nl2br(htmlspecialchars($manhwa['description'])); ?></p>
                </div>
            </div>

            <div class="chapter-list-area">
                <h2 style="margin-top:0;">Chapters</h2>
                <div class="chapter-list">
                    <?php if (empty($chapters)): ?>
                        <p>No chapters have been added for this series yet.</p>
                    <?php else: ?>
                        <?php foreach ($chapters as $chapter): ?>
                            <a href="/manhwa_reader?id=<?php echo $chapter['id']; ?>" class="chapter-item">
                                Chapter <?php echo htmlspecialchars($chapter['chapter_number']); ?>
                                <?php if ($chapter['chapter_title']): ?>
                                    - <?php echo htmlspecialchars($chapter['chapter_title']); ?>
                                <?php endif; ?>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</body>
</html>