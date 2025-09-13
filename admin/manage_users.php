<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) { header('Location: /admin'); exit; }
require '../db_connect.php';

// CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

$success_message = '';
$error_message = '';
$search_query = $_GET['q'] ?? ''; // Keep search query for redirects

// Handle Form Submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Check for all POST requests
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error_message = "Invalid CSRF token.";
    } else {
        // Handle Password Reset
        if (isset($_POST['reset_password'])) {
            $user_id_to_reset = $_POST['user_id'];
            $new_password = $_POST['new_password'];

            if (empty($new_password) || strlen($new_password) < 6) {
                $error_message = "Password must be at least 6 characters long.";
            } else {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $sql = "UPDATE users SET password = ? WHERE id = ?";
                if ($pdo->prepare($sql)->execute([$hashed_password, $user_id_to_reset])) {
                    $success_message = "Password for user ID #$user_id_to_reset has been updated.";
                } else {
                    $error_message = "Failed to update password.";
                }
            }
        }

        // NEW: Handle VIP Expiry Update with Duration
        if (isset($_POST['update_vip'])) {
            $user_id_to_update = $_POST['user_id'];
            $duration = $_POST['vip_duration'];
            $new_expiry_date = null;

            if ($duration === 'remove') {
                $new_expiry_date = null;
            } else {
                // Get the user's current expiry date
                $stmt_current = $pdo->prepare("SELECT vip_expiry_date FROM users WHERE id = ?");
                $stmt_current->execute([$user_id_to_update]);
                $current_expiry_date_str = $stmt_current->fetchColumn();

                $start_date = new DateTime(); // Start from today
                
                // If user has an active subscription in the future, add to that date instead of today
                if ($current_expiry_date_str && (new DateTime($current_expiry_date_str) > $start_date)) {
                    $start_date = new DateTime($current_expiry_date_str);
                }
                
                // Add the selected duration
                if ($duration === '1_month') $start_date->add(new DateInterval('P1M'));
                if ($duration === '3_months') $start_date->add(new DateInterval('P3M'));
                if ($duration === '6_months') $start_date->add(new DateInterval('P6M'));
                if ($duration === '1_year') $start_date->add(new DateInterval('P1Y'));
                
                $new_expiry_date = $start_date->format('Y-m-d');
            }

            // Update the database
            $sql = "UPDATE users SET vip_expiry_date = ? WHERE id = ?";
            if ($pdo->prepare($sql)->execute([$new_expiry_date, $user_id_to_update])) {
                $success_message = "VIP status for user ID #$user_id_to_update has been updated.";
            } else {
                $error_message = "Failed to update VIP status.";
            }
        }
    }
}

// Search Logic
$search_param = "%{$search_query}%";
$base_sql = " FROM users";
$where_sql = "";
if (!empty($search_query)) {
    $where_sql = " WHERE username LIKE :query OR email LIKE :query";
}

// Pagination Logic
$items_per_page = 10;
$current_page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($current_page < 1) $current_page = 1;

$total_items_stmt = $pdo->prepare("SELECT COUNT(*) " . $base_sql . $where_sql);
if (!empty($search_query)) {
    $total_items_stmt->bindParam(':query', $search_param);
}
$total_items_stmt->execute();
$total_items = $total_items_stmt->fetchColumn();
$total_pages = ceil($total_items / $items_per_page);

if ($current_page > $total_pages && $total_pages > 0) $current_page = $total_pages;

$offset = ($current_page - 1) * $items_per_page;

$sql = "SELECT id, username, email, vip_expiry_date, created_at" . $base_sql . $where_sql . " ORDER BY id DESC LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($sql);
if (!empty($search_query)) {
    $stmt->bindParam(':query', $search_param);
}
$stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

$current_page_nav = 'users';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Admin Panel</title>
    <link rel="stylesheet" href="admin_style.css">
</head>
<body>
    <div class="dashboard-wrapper">
        <aside class="admin-sidebar">
             <div class="admin-sidebar-header">Aether Stream</div>
            <nav class="admin-nav">
                <a href="/admin/dashboard" class="admin-nav-item">Dashboard</a>
                <a href="/admin/manage_anime" class="admin-nav-item">Manage Anime</a>
                <a href="/admin/manage_manhwa" class="admin-nav-item <?php echo ($current_page_nav === 'manhwa_admin') ? 'active' : ''; ?>">Manage Manhwa</a>
                <a href="/admin/manage_users" class="admin-nav-item <?php echo ($current_page_nav === 'users') ? 'active' : ''; ?>">Manage Users</a>
                <a href="/admin/manage_codes" class="admin-nav-item">Manage VIP Codes</a>
                <a href="/home" class="admin-nav-item" target="_blank">View Live Site</a>
            </nav>
        </aside>
        <main class="dashboard-main-content">
            <header class="dashboard-header">
                <h1>Manage Users</h1>
                 <a href="/admin/dashboard" class="btn btn-secondary">&larr; Back to Dashboard</a>
            </header>

            <?php if ($success_message): ?><div class="message success"><p><?php echo $success_message; ?></p></div><?php endif; ?>
            <?php if ($error_message): ?><div class="message error"><p><?php echo $error_message; ?></p></div><?php endif; ?>
            
            <div class="form-container" style="margin-bottom: 20px;">
                <form action="/admin/manage_users" method="GET" style="display: flex; gap: 10px;">
                    <input type="text" name="q" placeholder="Search by Username or Email..." value="<?php echo htmlspecialchars($search_query); ?>" style="flex-grow: 1; padding: 10px;">
                    <button type="submit" class="btn btn-secondary">Search</button>
                </form>
            </div>

            <div class="content-table">
                <table>
                    <thead>
                        <tr>
                            <th>User ID</th>
                            <th>Username / Email</th>
                            <th>VIP Expiry</th>
                            <th style="min-width: 420px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                             <tr><td colspan="4" style="text-align:center;">No users found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user['id']); ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($user['username']); ?></strong><br>
                                        <small><?php echo htmlspecialchars($user['email']); ?></small>
                                    </td>
                                    <td>
                                        <?php if ($user['vip_expiry_date']): ?>
                                            <strong style="<?php echo (new DateTime($user['vip_expiry_date']) < new DateTime('today')) ? 'color:red;' : 'color:green;'; ?>">
                                                <?php echo htmlspecialchars($user['vip_expiry_date']); ?>
                                            </strong>
                                        <?php else: ?>
                                            N/A
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                                            <form action="/admin/manage_users?page=<?php echo $current_page; ?><?php echo !empty($search_query) ? '&q=' . urlencode($search_query) : ''; ?>" method="POST" class="inline-form">
                                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <input type="text" name="new_password" placeholder="New Password" required>
                                                <button type="submit" name="reset_password" class="btn btn-secondary">Reset Pass</button>
                                            </form>

                                            <form action="/admin/manage_users?page=<?php echo $current_page; ?><?php echo !empty($search_query) ? '&q=' . urlencode($search_query) : ''; ?>" method="POST" class="inline-form">
                                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <select name="vip_duration" style="padding: 8px;">
                                                    <option value="1_month">+ 1 Month</option>
                                                    <option value="3_months">+ 3 Months</option>
                                                    <option value="6_months">+ 6 Months</option>
                                                    <option value="1_year">+ 1 Year</option>
                                                    <option value="" disabled>---</option>
                                                    <option value="remove">Remove VIP</option>
                                                </select>
                                                <button type="submit" name="update_vip" class="btn btn-success">Update VIP</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="pagination">
                <?php 
                    $query_string = !empty($search_query) ? '&q=' . urlencode($search_query) : '';
                ?>
                <?php if ($current_page > 1): ?>
                    <a href="/admin/manage_users?page=<?php echo $current_page - 1; ?><?php echo $query_string; ?>">&laquo; Prev</a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="/admin/manage_users?page=<?php echo $i; ?><?php echo $query_string; ?>" class="<?php echo ($i == $current_page) ? 'active' : ''; ?>"><?php echo $i; ?></a>
                <?php endfor; ?>
                
                <?php if ($current_page < $total_pages): ?>
                    <a href="/admin/manage_users?page=<?php echo $current_page + 1; ?><?php echo $query_string; ?>">Next &raquo;</a>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>