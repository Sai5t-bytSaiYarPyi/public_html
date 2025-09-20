<?php
session_start();
require 'language_loader.php';
require 'db_connect.php';

$is_vip = isset($_SESSION['is_vip']) && $_SESSION['is_vip'] === true;

if (!isset($_SESSION['is_logged_in']) || !isset($_SESSION['is_vip']) || $_SESSION['is_vip'] !== true) {
    $_SESSION['error_message'] = $lang['vip_needed_message'];
    header('Location: /activate_vip');
    exit();
}

// Get the genre from the URL, if it's not there, redirect to browse
$genre_name = trim($_GET['name'] ?? '');
if (empty($genre_name)) {
    header('Location: /browse');
    exit();
}

// --- Pagination Logic START ---
$items_per_page = 12;
$current_page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($current_page < 1) $current_page = 1;

// Get total number of animes for this genre using a named placeholder
$total_items_stmt = $pdo->prepare("SELECT COUNT(*) FROM animes WHERE genre LIKE :genre");
$total_items_stmt->execute([':genre' => "%{$genre_name}%"]);
$total_items = $total_items_stmt->fetchColumn();
$total_pages = ceil($total_items / $items_per_page);

if ($current_page > $total_pages && $total_pages > 0) $current_page = $total_pages;

// Calculate the offset for the query
$offset = ($current_page - 1) * $items_per_page;

// Fetch animes for the current page and genre using all named placeholders
$stmt = $pdo->prepare("SELECT * FROM animes WHERE genre LIKE :genre ORDER BY title ASC LIMIT :limit OFFSET :offset");
$stmt->bindValue(':genre', "%{$genre_name}%");
$stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$animes = $stmt->fetchAll(PDO::FETCH_ASSOC);
// --- Pagination Logic END ---

$current_page_nav = 'browse';
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['lang']; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Genre: <?php echo htmlspecialchars($genre_name); ?> - <?php echo $lang['aether_stream']; ?></title>
    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="stylesheet" href="/style.css?v=1.4">
</head>
<body>
    <div class="main-container">
        <aside class="sidebar">
            <div class="sidebar-logo"><?php echo $lang['aether_stream']; ?></div>
            <nav class="sidebar-nav">
                <a href="/home" class="nav-item">Home</a>
                <a href="/browse" class="nav-item active">Browse</a>
                <a href="/my_list" class="nav-item">My List</a>
                <a href="/history" class="nav-item <?php echo ($current_page_nav === 'history') ? 'active' : ''; ?>">History</a>
                <a href="/settings" class="nav-item">Settings</a>
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
                    <h2 class="row-title">Genre: "<?php echo htmlspecialchars($genre_name); ?>"</h2>
                    <?php if (empty($animes)): ?>
                        <p class="no-results">No anime found in this genre.</p>
                    <?php else: ?>
                        <div class="browse-grid">
                            <?php foreach ($animes as $anime): ?>
                            <a href="/watch?id=<?php echo $anime['id']; ?>" class="anime-card">
                                <img src="<?php echo htmlspecialchars($anime['thumbnail_url']); ?>" alt="<?php echo htmlspecialchars($anime['title']); ?>">
                                <div class="card-title"><?php echo htmlspecialchars($anime['title']); ?></div>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($current_page > 1): ?>
                        <a href="/genre?name=<?php echo urlencode($genre_name); ?>&page=<?php echo $current_page - 1; ?>">&laquo; Prev</a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="/genre?name=<?php echo urlencode($genre_name); ?>&page=<?php echo $i; ?>" class="<?php echo ($i == $current_page) ? 'active' : ''; ?>"><?php echo $i; ?></a>
                    <?php endfor; ?>
                    
                    <?php if ($current_page < $total_pages): ?>
                        <a href="/genre?name=<?php echo urlencode($genre_name); ?>&page=<?php echo $current_page + 1; ?>">Next &raquo;</a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

            </section>
        </main>
    </div>
</body>
</html>