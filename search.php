<?php
session_start();
require 'language_loader.php'; // Load language file
require 'db_connect.php';

$is_vip = isset($_SESSION['is_vip']) && $_SESSION['is_vip'] === true;

// Security Check
if (!isset($_SESSION['is_logged_in']) || !isset($_SESSION['is_vip']) || $_SESSION['is_vip'] !== true) {
    $_SESSION['error_message'] = $lang['vip_needed_message'];
    header('Location: activate_vip.php');
    exit();
}

$search_results = [];
$search_query = '';

if (isset($_GET['query']) && !empty(trim($_GET['query']))) {
    $search_query = trim($_GET['query']);
    $sql = "SELECT * FROM animes WHERE title LIKE ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(["%{$search_query}%"]);
    $search_results = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    header('Location: home.php');
    exit();
}

$current_page = 'search'; // No specific active state, but good practice
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['lang']; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $lang['search_results_for']; ?> "<?php echo htmlspecialchars($search_query); ?>"</title>
    <link rel="icon" type="image/png" href="favicon.png">
    <link rel="stylesheet" href="style.css?v=1.1">
</head>
<body>
    <div class="main-container">
        <!-- Sidebar Navigation -->
        <aside class="sidebar">
            <div class="sidebar-logo"><?php echo $lang['aether_stream']; ?></div>
            <nav class="sidebar-nav">
                <a href="home.php" class="nav-item"><?php echo $lang['home']; ?></a>
                <a href="browse.php" class="nav-item"><?php echo $lang['browse']; ?></a>
                <a href="my_list.php" class="nav-item"><?php echo $lang['my_list']; ?></a>
                <a href="settings.php" class="nav-item"><?php echo $lang['settings']; ?></a>
            </nav>
            <div class="sidebar-footer">
                <a href="logout.php" class="logout-btn"><?php echo $lang['logout']; ?></a>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="main-content">
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

                bell.addEventListener('click', function(event) {
                    event.stopPropagation();
                    dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';

                    if (badge && dropdown.style.display === 'block') {
                        // Mark notifications as read via AJAX
                        fetch('/mark_notifications_read.php', {
                            method: 'POST'
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.status === 'success') {
                                badge.style.display = 'none';
                            }
                        });
                    }
                });

                 // Close dropdown if clicked outside
                 document.addEventListener('click', function() {
                    dropdown.style.display = 'none';
                });
             });
            </script>

            <section class="anime-rows">
                <div class="anime-row">
                    <h2 class="row-title"><?php echo $lang['search_results_for']; ?>: "<?php echo htmlspecialchars($search_query); ?>"</h2>
                    
                    <?php if (empty($search_results)): ?>
                        <p class="no-results"><?php echo $lang['no_anime_found']; ?></p>
                    <?php else: ?>
                        <div class="browse-grid">
                            <?php foreach ($search_results as $anime): ?>
                            <a href="watch.php?id=<?php echo $anime['id']; ?>" class="anime-card">
                                <img src="<?php echo htmlspecialchars($anime['thumbnail_url']); ?>" alt="<?php echo htmlspecialchars($anime['title']); ?>">
                                <div class="card-title"><?php echo htmlspecialchars($anime['title']); ?></div>
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
