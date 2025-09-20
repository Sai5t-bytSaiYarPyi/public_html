<?php
session_start();
require 'language_loader.php';
require 'db_connect.php';

// Check if logged in first
if (!isset($_SESSION['is_logged_in'])) {
    header('Location: /');
    exit();
}

// Get VIP status from session (which is updated by db_connect.php)
$is_vip = isset($_SESSION['is_vip']) && $_SESSION['is_vip'] === true;

// This query is needed for the expiry notification logic on this page
$user_vip_status_stmt = $pdo->prepare("SELECT vip_expiry_date FROM users WHERE id = ?");
$user_vip_status_stmt->execute([$_SESSION['user_id']]);
$user_vip_status = $user_vip_status_stmt->fetch(PDO::FETCH_ASSOC);

// Fetch content for the page
$stmt_new = $pdo->query("SELECT id, title, thumbnail_url, genre FROM animes WHERE type = 'New Arrival' ORDER BY id DESC LIMIT 5");
$new_arrivals = $stmt_new->fetchAll(PDO::FETCH_ASSOC);
$stmt_popular = $pdo->query("SELECT id, title, description, thumbnail_url, genre FROM animes WHERE type = 'Popular' ORDER BY id DESC LIMIT 5");
$popular_series = $stmt_popular->fetchAll(PDO::FETCH_ASSOC);
$hero_anime = !empty($popular_series) ? $popular_series[0] : null;
$current_page = 'home';
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['lang']; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $lang['home']; ?> - <?php echo $lang['aether_stream']; ?></title>
    <link rel="icon" type="image/png" href="favicon.png">
    <link rel="stylesheet" href="style.css?v=1.9">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="main-container">
        <aside class="sidebar">
            <div class="sidebar-logo"><?php echo $lang['aether_stream']; ?></div>
            <nav class="sidebar-nav">
                <a href="/home" class="nav-item <?php echo ($current_page === 'home') ? 'active' : ''; ?>"><?php echo $lang['home']; ?></a>
                <a href="/browse" class="nav-item">Browse</a>
                <a href="/manhwas" class="nav-item <?php echo ($current_page_nav === 'manhwa') ? 'active' : ''; ?>"><?php echo $lang['manhwa']; ?></a>
                <a href="/my_list" class="nav-item">My List</a>
                <a href="/history" class="nav-item">History</a>
                <a href="/settings" class="nav-item">Settings</a>
            </nav>
            <div class="sidebar-footer">
                <a href="/logout" class="logout-btn"><?php echo $lang['logout']; ?></a>
            </div>
        </aside>
        <main class="main-content">

            <?php
            if ($is_vip && isset($user_vip_status['vip_expiry_date'])) {
                $expiry_date = new DateTime($user_vip_status['vip_expiry_date']);
                $today = new DateTime();
                $interval = $today->diff($expiry_date);
                $days_left = (int)$interval->format('%r%a'); 

                if ($days_left >= 0 && $days_left <= 7) {
                    echo '<div class="message success" style="text-align: center; margin-bottom: 20px; background-color: #ffc107; color: #333; border-color: #ffc107;">';
                    if ($days_left == 0) {
                        echo '<p>Your VIP membership expires today! <a href="/purchase_vip" style="color: #333; font-weight: bold; text-decoration: underline;">Renew now</a> to continue access.</p>';
                    } else {
                        echo '<p>Your VIP membership will expire in ' . $days_left . ' day(s). <a href="/purchase_vip" style="color: #333; font-weight: bold; text-decoration: underline;">Renew now</a> to avoid interruption.</p>';
                    }
                    echo '</div>';
                }
            }
            ?>
            <header class="main-header">
                <form action="/search" method="GET" class="search-bar">
                    <input type="text" name="query" placeholder="<?php echo $lang['search_for_anime']; ?>" required>
                    <button type="submit"><?php echo $lang['search']; ?></button>
                </form>
                <div class="user-profile">
                    <div class="language-switcher">
                        <a href="/change_language?lang=en" class="<?php echo ($_SESSION['lang'] === 'en') ? 'active' : ''; ?>">EN</a> | 
                        <a href="/change_language?lang=mm" class="<?php echo ($_SESSION['lang'] === 'mm') ? 'active' : ''; ?>">MM</a>
                    </div>

                    <div class="notification-bell" id="notification-bell">
                        <i class="fas fa-bell"></i>
                        <?php if (isset($_SESSION['unread_notification_count']) && $_SESSION['unread_notification_count'] > 0): ?>
                            <span class="notification-badge" id="notification-badge"><?php echo $_SESSION['unread_notification_count']; ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($is_vip): ?>
                        <span title="Expires on <?php echo $_SESSION['vip_expiry_date_display']; ?>"><?php echo $lang['vip_member']; ?></span>
                    <?php else: ?>
                        <a href="/purchase_vip" class="btn btn-primary">Get VIP</a>
                    <?php endif; ?>
                </div>

                <div class="notification-dropdown" id="notification-dropdown">
                    <?php if (isset($_SESSION['unread_notifications']) && !empty($_SESSION['unread_notifications'])): ?>
                        <?php foreach ($_SESSION['unread_notifications'] as $notification): ?>
                            <a href="<?php echo htmlspecialchars($notification['link']); ?>" class="notification-item">
                                <?php echo htmlspecialchars($notification['message']); ?>
                                <small><?php echo (new DateTime($notification['created_at']))->format('M j, Y H:i'); ?></small>
                            </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-notifications">No new notifications</div>
                    <?php endif; ?>
                </div>
            </header>

            <script>
            document.addEventListener('DOMContentLoaded', function() {
                const bell = document.getElementById('notification-bell');
                const dropdown = document.getElementById('notification-dropdown');
                const badge = document.getElementById('notification-badge');

                if (bell) {
                    bell.addEventListener('click', function(event) {
                        event.stopPropagation();
                        dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';

                        if (badge && dropdown.style.display === 'block') {
                            fetch('/mark_notifications_read.php', { method: 'POST' })
                            .then(response => response.json())
                            .then(data => {
                                if (data.status === 'success') {
                                    badge.style.display = 'none';
                                }
                            });
                        }
                    });
                }

                document.addEventListener('click', function() {
                    if (dropdown) {
                        dropdown.style.display = 'none';
                    }
                });
            });
            </script>
            
            <?php if (!$is_vip): ?>
            <div class="message error" style="text-align: center; margin-bottom: 20px;">
                <p>Content is locked. <a href="/purchase_vip" style="color: #ffc4c4; font-weight: bold; text-decoration: underline;">Upgrade to VIP</a> to unlock all anime!</p>
            </div>
            <?php endif; ?>

            <?php if ($hero_anime): ?>
            <section class="hero-section" style="background-image: linear-gradient(to top, #121212, rgba(18, 18, 18, 0.5)), url('<?php echo htmlspecialchars($hero_anime['thumbnail_url']); ?>');">
                <div class="hero-content">
                    <h1 class="hero-title"><?php echo htmlspecialchars($hero_anime['title']); ?></h1>
                    <p class="hero-description"><?php echo htmlspecialchars(mb_substr($hero_anime['description'], 0, 150)); ?>...</p>
                    <div class="hero-buttons">
                        <?php if ($is_vip): ?>
                            <a href="/watch?id=<?php echo $hero_anime['id']; ?>" class="btn btn-primary"><?php echo $lang['watch_now']; ?></a>
                        <?php else: ?>
                            <a href="/purchase_vip" class="btn btn-primary"><?php echo $lang['watch_now']; ?></a>
                        <?php endif; ?>
                    </div>
                </div>
            </section>
            <?php endif; ?>

            <section class="anime-rows">
                <div class="anime-row">
                    <h2 class="row-title"><?php echo $lang['new_arrivals']; ?></h2>
                    <div class="row-content" <?php if (!$is_vip) echo 'style="filter: blur(5px); pointer-events: none;"'; ?>>
                        <?php foreach ($new_arrivals as $anime): ?>
                        <a href="/watch?id=<?php echo $anime['id']; ?>" class="anime-card">
                            <img src="<?php echo htmlspecialchars($anime['thumbnail_url']); ?>" alt="<?php echo htmlspecialchars($anime['title']); ?>">
                            <div class="card-title"><?php echo htmlspecialchars($anime['title']); ?></div>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="anime-row">
                    <h2 class="row-title"><?php echo $lang['popular_series']; ?></h2>
                    <div class="row-content" <?php if (!$is_vip) echo 'style="filter: blur(5px); pointer-events: none;"'; ?>>
                        <?php foreach ($popular_series as $anime): ?>
                        <a href="/watch?id=<?php echo $anime['id']; ?>" class="anime-card">
                            <img src="<?php echo htmlspecialchars($anime['thumbnail_url']); ?>" alt="<?php echo htmlspecialchars($anime['title']); ?>">
                            <div class="card-title"><?php echo htmlspecialchars($anime['title']); ?></div>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
        </main>
    </div>
</body>
</html>