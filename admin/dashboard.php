<?php
// session_start() is now handled by db_connect.php
require '../db_connect.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) { 
    header('Location: /admin'); 
    exit; 
}

$total_animes = $pdo->query("SELECT count(*) FROM animes")->fetchColumn();
$total_codes = $pdo->query("SELECT count(*) FROM codes")->fetchColumn();
$active_codes = $pdo->query("SELECT count(*) FROM codes WHERE is_used = 0")->fetchColumn();
$total_users = $pdo->query("SELECT count(*) FROM users")->fetchColumn();
$current_page = 'dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Admin Panel</title>
    <link rel="stylesheet" href="admin_style.css">
</head>
<body>
    <div class="dashboard-wrapper">
        <aside class="admin-sidebar">
            <div class="admin-sidebar-header">Aether Stream</div>
            <nav class="admin-nav">
                <a href="/admin/dashboard" class="admin-nav-item <?php echo ($current_page === 'dashboard') ? 'active' : ''; ?>">Dashboard</a>
                <a href="/admin/manage_anime" class="admin-nav-item">Manage Anime</a>
                <a href="/admin/manage_users" class="admin-nav-item">Manage Users</a>
                <a href="/admin/manage_manhwa" class="admin-nav-item <?php echo ($current_page_nav === 'manhwa_admin') ? 'active' : ''; ?>">Manage Manhwa</a>
                <a href="/admin/manage_codes" class="admin-nav-item">Manage VIP Codes</a>
                <a href="/home" class="admin-nav-item" target="_blank">View Live Site</a>
            </nav>
        </aside>

        <main class="dashboard-main-content">
            <header class="dashboard-header">
                <h1>Dashboard</h1>
                <div>Welcome, <strong><?php echo htmlspecialchars($_SESSION['admin_username']); ?></strong> <a href="/admin/logout" class="btn btn-secondary">Logout</a></div>
            </header>
            
            <section class="quick-stats">
                <div class="stat-card">
                    <h2><?php echo $total_animes; ?></h2>
                    <p>Total Anime Series</p>
                </div>
                <div class="stat-card">
                    <h2><?php echo $total_users; ?></h2>
                    <p>Total Users</p>
                </div>
                <div class="stat-card">
                    <h2><?php echo $total_codes; ?></h2>
                    <p>Total VIP Codes</p>
                </div>
                 <div class="stat-card">
                    <h2><?php echo $active_codes; ?></h2>
                    <p>Unused VIP Codes</p>
                </div>
            </section>
        </main>
    </div>
</body>
</html>