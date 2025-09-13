<?php
// session_start() is handled by db_connect.php
require 'language_loader.php';
require 'db_connect.php';

$is_vip = isset($_SESSION['is_vip']) && $_SESSION['is_vip'] === true;

if (!isset($_SESSION['is_logged_in'])) { header('Location: /'); exit(); }

$user_id = $_SESSION['user_id'];
$current_page_nav = 'settings';

// Fetch User Details (vip_expiry_date is now fetched in db_connect.php and stored in session)
$user_stmt = $pdo->prepare("SELECT username, email FROM users WHERE id = ?");
$user_stmt->execute([$user_id]);
$user = $user_stmt->fetch(PDO::FETCH_ASSOC);

// Fetch latest items from My List
$my_list_stmt = $pdo->prepare("
    SELECT a.id, a.title, a.thumbnail_url 
    FROM user_favorites uf JOIN animes a ON uf.anime_id = a.id
    WHERE uf.user_id = ? ORDER BY uf.added_at DESC LIMIT 6
");
$my_list_stmt->execute([$user_id]);
$my_list_items = $my_list_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch latest items from Watch History
$history_stmt = $pdo->prepare("
    SELECT a.id, a.title, a.thumbnail_url, wh.episode_number
    FROM watch_history wh JOIN animes a ON wh.anime_id = a.id
    WHERE wh.user_id = ? ORDER BY wh.watched_at DESC LIMIT 6
");
$history_stmt->execute([$user_id]);
$history_items = $history_stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle Password Change Form
$error_message = '';
$success_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['current_password'])) {
    // CSRF token logic should be here
    
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_new_password = $_POST['confirm_new_password'];
    
    if (empty($current_password) || empty($new_password) || empty($confirm_new_password)) {
        $error_message = $lang['all_fields_required'];
    } elseif ($new_password !== $confirm_new_password) {
        $error_message = $lang['passwords_no_match'];
    } else {
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user_pass = $stmt->fetch();
        if ($user_pass && password_verify($current_password, $user_pass['password'])) {
            $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            if ($update_stmt->execute([$new_hashed_password, $user_id])) {
                $success_message = $lang['password_updated_success'];
            } else {
                $error_message = "Failed to update password.";
            }
        } else {
            $error_message = $lang['incorrect_current_password'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['lang']; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - <?php echo $lang['aether_stream']; ?></title>
    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="stylesheet" href="/style.css?v=1.9">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="main-container">
        <aside class="sidebar">
            <div class="sidebar-logo"><?php echo $lang['aether_stream']; ?></div>
            <nav class="sidebar-nav">
                <a href="/home" class="nav-item">Home</a>
                <a href="/browse" class="nav-item">Browse</a>
                <a href="/manhwas" class="nav-item <?php echo ($current_page_nav === 'manhwa') ? 'active' : ''; ?>"><?php echo $lang['manhwa']; ?></a>
                <a href="/my_list" class="nav-item">My List</a>
                <a href="/history" class="nav-item">History</a>
                <a href="/settings" class="nav-item active">Settings</a>
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

            <div class="settings-layout">
                <div class="profile-card">
                    <h3 style="margin-top:0; margin-bottom: 15px;">Profile Details</h3>
                    <ul class="profile-details">
                        <li><strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></li>
                        <li><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></li>
                        <li><strong>VIP Status:</strong> 
                            <?php if (isset($_SESSION['is_vip']) && $_SESSION['is_vip']): ?>
                                <span style="color: #28a745;">Active until <?php echo htmlspecialchars($_SESSION['vip_expiry_date_display']); ?></span>
                            <?php else: ?>
                                <span style="color: #dc3545;">Inactive</span>
                            <?php endif; ?>
                        </li>
                    </ul>
                     <?php if (!isset($_SESSION['is_vip']) || !$_SESSION['is_vip']): ?>
                        <a href="/purchase_vip" class="btn btn-primary" style="margin-top: 15px;">Upgrade to VIP</a>
                    <?php else: ?>
                         <a href="/purchase_vip" class="btn btn-secondary" style="margin-top: 15px;">Renew / Extend VIP</a>
                    <?php endif; ?>
                </div>
                <div class="settings-form-container">
                    <h3><?php echo $lang['change_password']; ?></h3>
                    <?php if ($error_message): ?><div class="message error"><p><?php echo $error_message; ?></p></div><?php endif; ?>
                    <?php if ($success_message): ?><div class="message success"><p><?php echo $success_message; ?></p></div><?php endif; ?>
                    <form action="/settings" method="POST">
                        <div class="form-group">
                            <label for="current_password"><?php echo $lang['current_password']; ?></label>
                            <input type="password" id="current_password" name="current_password" required>
                        </div>
                        <div class="form-group">
                            <label for="new_password"><?php echo $lang['new_password']; ?></label>
                            <input type="password" id="new_password" name="new_password" required>
                        </div>
                        <div class="form-group">
                            <label for="confirm_new_password"><?php echo $lang['confirm_new_password']; ?></label>
                            <input type="password" id="confirm_new_password" name="confirm_new_password" required>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary"><?php echo $lang['change_password']; ?></button>
                        </div>
                    </form>
                </div>
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