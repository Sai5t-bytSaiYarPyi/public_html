<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) { header('Location: /admin'); exit; }
require '../db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_code'])) {
    $duration = $_POST['duration'];
    $new_code = strtoupper('AETHER-' . uniqid());
    
    $expiry_date = new DateTime();
    if ($duration === '1_month') { $expiry_date->add(new DateInterval('P1M')); }
    elseif ($duration === '3_months') { $expiry_date->add(new DateInterval('P3M')); }
    elseif ($duration === '6_months') { $expiry_date->add(new DateInterval('P6M')); }
    elseif ($duration === '1_year') { $expiry_date->add(new DateInterval('P1Y')); }
    $expiry_date_str = $expiry_date->format('Y-m-d');

    $sql = "INSERT INTO codes (access_code, expiry_date, is_used) VALUES (?, ?, 0)";
    $pdo->prepare($sql)->execute([$new_code, $expiry_date_str]);

    header("Location: /admin/manage_codes?status=code_added");
    exit;
}

if (isset($_GET['delete_id']) && is_numeric($_GET['delete_id'])) {
    $sql = "DELETE FROM codes WHERE id = ?";
    $pdo->prepare($sql)->execute([$_GET['delete_id']]);
    header("Location: /admin/manage_codes?status=code_deleted");
    exit;
}

$codes = $pdo->query("SELECT * FROM codes ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
$current_page = 'codes';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage VIP Codes - Admin Panel</title>
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
                <a href="/admin/manage_users" class="admin-nav-item">Manage Users</a>
                <a href="/admin/manage_codes" class="admin-nav-item <?php echo ($current_page === 'codes') ? 'active' : ''; ?>">Manage VIP Codes</a>
                <a href="/home" class="admin-nav-item" target="_blank">View Live Site</a>
            </nav>
        </aside>
        <main class="dashboard-main-content">
            <header class="dashboard-header">
                <h1>Manage VIP Codes</h1>
                <a href="/admin/dashboard" class="btn btn-secondary">&larr; Back to Dashboard</a>
            </header>

            <div class="form-container">
                <h3>Generate New VIP Code</h3>
                <form action="/admin/manage_codes" method="POST">
                    <div class="form-group">
                        <label for="duration">Select Duration</label>
                        <select id="duration" name="duration">
                            <option value="1_month">1 Month</option>
                            <option value="3_months">3 Months</option>
                            <option value="6_months">6 Months</option>
                            <option value="1_year">1 Year</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <button type="submit" name="add_code" class="btn btn-primary">Generate New Code</button>
                    </div>
                </form>
            </div>
            
            <div class="content-table" style="margin-top: 30px;">
                <h3>Existing / Unused VIP Codes</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Access Code</th>
                            <th>Reference Expiry</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($codes as $code): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($code['access_code']); ?></strong></td>
                                <td><?php echo htmlspecialchars($code['expiry_date']); ?></td>
                                <td>
                                    <?php if ($code['is_used'] == 1) { echo '<span class="status-expired">Used</span>'; } else { echo '<span class="status-active">Unused</span>'; } ?>
                                </td>
                                <td class="actions">
                                    <a href="/admin/manage_codes?delete_id=<?php echo $code['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>