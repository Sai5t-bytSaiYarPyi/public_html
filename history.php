<?php
// session_start() has been removed because it is already in db_connect.php
require 'language_loader.php';
require 'db_connect.php';

$is_vip = isset($_SESSION['is_vip']) && $_SESSION['is_vip'] === true;

if (!isset($_SESSION['is_logged_in'])) { header('Location: /'); exit(); }

$user_id = $_SESSION['user_id'];
$current_page_nav = 'history';

// Pagination Logic for History
$items_per_page = 12;
$current_page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($current_page < 1) $current_page = 1;

$total_items_stmt = $pdo->prepare("SELECT COUNT(*) FROM watch_history WHERE user_id = ?");
$total_items_stmt->execute([$user_id]);
$total_items = $total_items_stmt->fetchColumn();
$total_pages = ceil($total_items / $items_per_page);

if ($current_page > $total_pages && $total_pages > 0) $current_page = $total_pages;

$offset = ($current_page - 1) * $items_per_page;

$sql = "SELECT a.id, a.title, a.thumbnail_url, wh.episode_number, wh.watched_at
        FROM watch_history wh
        JOIN animes a ON wh.anime_id = a.id
        WHERE wh.user_id = :user_id
        ORDER BY wh.watched_at DESC
        LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$history_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['lang']; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Watch History - <?php echo $lang['aether_stream']; ?></title>
    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="stylesheet" href="/style.css?v=1.5">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="main-container">
        <aside class="sidebar">
            <div class="sidebar-logo"><?php echo $lang['aether_stream']; ?></div>
            <nav class="sidebar-nav">
                <a href="/home" class="nav-item <?php echo ($current_page_nav === 'home') ? 'active' : ''; ?>">Home</a>
                <a href="/browse" class="nav-item <?php echo ($current_page_nav === 'browse') ? 'active' : ''; ?>">Browse</a>
                <a href="/manhwas" class="nav-item <?php echo ($current_page_nav === 'manhwa') ? 'active' : ''; ?>"><?php echo $lang['manhwa']; ?></a>
                <a href="/my_list" class="nav-item <?php echo ($current_page_nav === 'my_list') ? 'active' : ''; ?>">My List</a>
                <a href="/history" class="nav-item <?php echo ($current_page_nav === 'history') ? 'active' : ''; ?>">History</a>
            
                <a href="/settings" class="nav-item <?php echo ($current_page_nav === 'settings') ? 'active' : ''; ?>">Settings</a>
            </nav>
            <div class="sidebar-footer">
                <a href="/logout" class="logout-btn">Logout</a>
            </div>
        </aside>
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
                    <h2 class="row-title">My Watch History</h2>
                    <?php if (empty($history_items)): ?>
                        <p class="no-results">Your watch history is empty.</p>
                    <?php else: ?>
                        <div class="browse-grid">
                            <?php foreach ($history_items as $item): ?>
                            <a href="/watch?id=<?php echo $item['id']; ?>&ep=<?php echo $item['episode_number']; ?>" class="anime-card">
                                <img src="<?php echo htmlspecialchars($item['thumbnail_url']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>">
                                <div class="card-title"><?php echo htmlspecialchars($item['title']); ?></div>
                                <div class="card-subtitle">Watched Episode <?php echo $item['episode_number']; ?></div>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($current_page > 1): ?>
                        <a href="/history?page=<?php echo $current_page - 1; ?>">&laquo; Prev</a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="/history?page=<?php echo $i; ?>" class="<?php echo ($i == $current_page) ? 'active' : ''; ?>"><?php echo $i; ?></a>
                    <?php endfor; ?>
                    
                    <?php if ($current_page < $total_pages): ?>
                        <a href="/history?page=<?php echo $current_page + 1; ?>">Next &raquo;</a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </section>
            <footer class="main-footer" style="text-align: center; padding: 30px 0 10px 0; border-top: 1px solid #2a2a2a; margin-top: 40px;">
                <div style="display: flex; justify-content: center; gap: 20px; flex-wrap: wrap;">
                    <a href="/privacy-policy" style="color: #888; text-decoration: none;">Privacy Policy</a>
                    <a href="/terms-of-service" style="color: #888; text-decoration: none;">Terms of Service</a>
                    <a href="/copyright" style="color: #888; text-decoration: none;">Copyright Notice</a>
                    <a href="/help" style="color: #888; text-decoration: none;">Help Center</a>
                </div>
                <p style="color: #666; font-size: 14px; margin-top: 20px;">© <?php echo date("Y"); ?> Aether Stream. All Rights Reserved.</p>
            </footer>
        </main>
    </div>
</body>
</html>