<?php
session_start();
require 'language_loader.php';
require 'db_connect.php';

if (!isset($_SESSION['is_logged_in'])) { header('Location: /'); exit(); }

$user_id = $_SESSION['user_id'];
$current_page_nav = 'profile';

// Fetch User Details
$user_stmt = $pdo->prepare("SELECT username, email FROM users WHERE id = ?");
$user_stmt->execute([$user_id]);
$user = $user_stmt->fetch(PDO::FETCH_ASSOC);

// Fetch latest items from My List
$my_list_stmt = $pdo->prepare("
    SELECT a.id, a.title, a.thumbnail_url 
    FROM user_favorites uf
    JOIN animes a ON uf.anime_id = a.id
    WHERE uf.user_id = ? 
    ORDER BY uf.added_at DESC LIMIT 6
");
$my_list_stmt->execute([$user_id]);
$my_list_items = $my_list_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch latest items from Watch History
$history_stmt = $pdo->prepare("
    SELECT a.id, a.title, a.thumbnail_url, wh.episode_number
    FROM watch_history wh
    JOIN animes a ON wh.anime_id = a.id
    WHERE wh.user_id = ?
    ORDER BY wh.watched_at DESC LIMIT 6
");
$history_stmt->execute([$user_id]);
$history_items = $history_stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['lang']; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - <?php echo $lang['aether_stream']; ?></title>
    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="stylesheet" href="/style.css?v=1.6">
</head>
<body>
    <div class="main-container">
        <aside class="sidebar"></aside>
        <main class="main-content">
            <header class="main-header">
                 <h2>My Profile</h2>
                <div class="user-profile">
                     <div class="language-switcher">
                        <a href="/change_language?lang=en" class="<?php echo ($_SESSION['lang'] === 'en') ? 'active' : ''; ?>">EN</a> | 
                        <a href="/change_language?lang=mm" class="<?php echo ($_SESSION['lang'] === 'mm') ? 'active' : ''; ?>">MM</a>
                    </div>
                    <span><?php echo $lang['vip_member']; ?></span>
                </div>
            </header>

            <div class="profile-card">
                <ul class="profile-details">
                    <li><strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></li>
                    <li><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></li>
                </ul>
                <a href="/settings" class="btn btn-secondary" style="margin-top: 15px;">Change Password</a>
            </div>

            <section class="anime-rows">
                <div class="anime-row">
                    <div class="section-header">
                        <h2 class="row-title">Recent Watch History</h2>
                        <a href="/history" class="view-all-link">View All &raquo;</a>
                    </div>
                    <?php if (empty($history_items)): ?>
                        <p class="no-results">Your watch history is empty.</p>
                    <?php else: ?>
                        <div class="row-content">
                            <?php foreach ($history_items as $item): ?>
                            <a href="/watch?id=<?php echo $item['id']; ?>&ep=<?php echo $item['episode_number']; ?>" class="anime-card">
                                <img src="<?php echo htmlspecialchars($item['thumbnail_url']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>">
                                <div class="card-title"><?php echo htmlspecialchars($item['title']); ?></div>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="anime-row">
                     <div class="section-header">
                        <h2 class="row-title">Recently Added to My List</h2>
                        <a href="/my_list" class="view-all-link">View All &raquo;</a>
                    </div>
                    <?php if (empty($my_list_items)): ?>
                        <p class="no-results">Your list is empty.</p>
                    <?php else: ?>
                        <div class="row-content">
                            <?php foreach ($my_list_items as $item): ?>
                            <a href="/watch?id=<?php echo $item['id']; ?>" class="anime-card">
                                <img src="<?php echo htmlspecialchars($item['thumbnail_url']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>">
                                <div class="card-title"><?php echo htmlspecialchars($item['title']); ?></div>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        </main>
    </div>
</body>
</html>