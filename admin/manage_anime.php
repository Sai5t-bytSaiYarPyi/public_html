<?php
session_start(); // THIS LINE WAS MISSING
if (!isset($_SESSION['admin_logged_in'])) { header('Location: /admin'); exit; }
require '../db_connect.php';

// NEW: Search Logic
$search_query = $_GET['q'] ?? '';
$search_param = "%{$search_query}%";

$base_sql = " FROM animes";
$where_sql = "";
if (!empty($search_query)) {
    $where_sql = " WHERE title LIKE :query OR genre LIKE :query";
}

// --- Pagination Logic START ---
$items_per_page = 10;
$current_page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($current_page < 1) $current_page = 1;

// Get total items with search condition
$total_items_stmt = $pdo->prepare("SELECT COUNT(*) " . $base_sql . $where_sql);
if (!empty($search_query)) {
    $total_items_stmt->bindParam(':query', $search_param);
}
$total_items_stmt->execute();
$total_items = $total_items_stmt->fetchColumn();
$total_pages = ceil($total_items / $items_per_page);

if ($current_page > $total_pages && $total_pages > 0) $current_page = $total_pages;

$offset = ($current_page - 1) * $items_per_page;

// Fetch items for the current page with search condition
$sql = "SELECT id, title, genre, thumbnail_url" . $base_sql . $where_sql . " ORDER BY id DESC LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($sql);
if (!empty($search_query)) {
    $stmt->bindParam(':query', $search_param);
}
$stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$animes = $stmt->fetchAll(PDO::FETCH_ASSOC);
// --- Pagination Logic END ---

$current_page_nav = 'anime';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Anime - Admin Panel</title>
    <link rel="stylesheet" href="admin_style.css">
</head>
<body>
    <div class="dashboard-wrapper">
         <aside class="admin-sidebar">
            <div class="admin-sidebar-header">Aether Stream</div>
            <nav class="admin-nav">
                <a href="/admin/dashboard" class="admin-nav-item">Dashboard</a>
                <a href="/admin/manage_anime" class="admin-nav-item <?php echo ($current_page_nav === 'anime') ? 'active' : ''; ?>">Manage Anime</a>
                <a href="/admin/manage_users" class="admin-nav-item">Manage Users</a>
                <a href="/admin/manage_manhwa" class="admin-nav-item <?php echo ($current_page_nav === 'manhwa_admin') ? 'active' : ''; ?>">Manage Manhwa</a>
                <a href="/admin/manage_codes" class="admin-nav-item">Manage VIP Codes</a>
                 <a href="/home" class="admin-nav-item" target="_blank">View Live Site</a>
            </nav>
        </aside>
        <main class="dashboard-main-content">
            <header class="dashboard-header">
                <h1>Manage Anime Series</h1>
                <a href="/admin/anime_add" class="btn btn-primary">Add New Anime</a>
            </header>

            <div class="form-container" style="margin-bottom: 20px;">
                <form action="/admin/manage_anime" method="GET" style="display: flex; gap: 10px;">
                    <input type="text" name="q" placeholder="Search by Title or Genre..." value="<?php echo htmlspecialchars($search_query); ?>" style="flex-grow: 1; padding: 10px;">
                    <button type="submit" class="btn btn-secondary">Search</button>
                </form>
            </div>

            <div class="content-table">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Thumbnail</th>
                            <th>Title</th>
                            <th>Genre</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($animes)): ?>
                            <tr><td colspan="5" style="text-align:center;">
                                <?php echo !empty($search_query) ? 'No anime found for "' . htmlspecialchars($search_query) . '".' : 'No anime found.'; ?>
                            </td></tr>
                        <?php else: ?>
                            <?php foreach ($animes as $anime): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($anime['id']); ?></td>
                                    <td><img src="<?php echo htmlspecialchars($anime['thumbnail_url']); ?>" alt="Thumbnail" class="thumbnail-preview"></td>
                                    <td><?php echo htmlspecialchars($anime['title']); ?></td>
                                    <td><?php echo htmlspecialchars($anime['genre']); ?></td>
                                    <td class="actions">
                                        <a href="/admin/manage_episodes?id=<?php echo $anime['id']; ?>" class="btn btn-success">Episodes</a>
                                        <a href="/admin/anime_edit?id=<?php echo $anime['id']; ?>" class="btn btn-secondary">Edit</a>
                                        <a href="/admin/anime_delete?id=<?php echo $anime['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure?');">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php 
                    $query_string = !empty($search_query) ? '&q=' . urlencode($search_query) : '';
                ?>
                <?php if ($current_page > 1): ?>
                    <a href="/admin/manage_anime?page=<?php echo $current_page - 1; ?><?php echo $query_string; ?>">&laquo; Prev</a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="/admin/manage_anime?page=<?php echo $i; ?><?php echo $query_string; ?>" class="<?php echo ($i == $current_page) ? 'active' : ''; ?>"><?php echo $i; ?></a>
                <?php endfor; ?>
                
                <?php if ($current_page < $total_pages): ?>
                    <a href="/admin/manage_anime?page=<?php echo $current_page + 1; ?><?php echo $query_string; ?>">Next &raquo;</a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>