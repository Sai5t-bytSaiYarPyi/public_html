<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) { header('Location: /admin'); exit; }
require '../db_connect.php';

// Get Chapter ID from URL and validate it
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: /admin/manage_manhwa"); exit;
}
$chapter_id = $_GET['id'];

// Fetch Chapter and Manhwa details for display
$stmt_chapter = $pdo->prepare("
    SELECT mc.id, mc.chapter_number, m.id as manhwa_id, m.title as manhwa_title
    FROM manhwa_chapters mc
    JOIN manhwas m ON mc.manhwa_id = m.id
    WHERE mc.id = ?
");
$stmt_chapter->execute([$chapter_id]);
$chapter = $stmt_chapter->fetch(PDO::FETCH_ASSOC);
if (!$chapter) {
    header("Location: /admin/manage_manhwa"); exit;
}

$error_message = '';
$success_message = '';

// Handle PDF Upload and Conversion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['pdf_file'])) {
    if ($_FILES['pdf_file']['error'] === UPLOAD_ERR_OK) {
        if (!extension_loaded('imagick')) {
            $error_message = "Error: ImageMagick extension is not installed or enabled on this server. Please contact hosting support to enable it.";
        } else {
            $pdf_tmp_path = $_FILES['pdf_file']['tmp_name'];
            
            // Create a unique directory for this chapter's images
            $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/manhwa/' . $chapter_id . '/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            try {
                $imagick = new Imagick();
                $imagick->setResolution(150, 150);
                $imagick->readImage($pdf_tmp_path);
                
                $page_number = 1;
                foreach ($imagick as $page) {
                    $page->setImageFormat('jpeg');
                    $page->setImageCompressionQuality(85);
                    $filename = 'page_' . $page_number . '.jpg';
                    $filepath = $upload_dir . $filename;
                    $page->writeImage($filepath);
                    
                    $image_url = '/uploads/manhwa/' . $chapter_id . '/' . $filename;
                    $sql = "INSERT INTO chapter_images (chapter_id, image_url, page_number) VALUES (?, ?, ?)";
                    $pdo->prepare($sql)->execute([$chapter_id, $image_url, $page_number]);
                    
                    $page_number++;
                }
                $imagick->clear();
                $imagick->destroy();
                $success_message = "Successfully converted and uploaded " . ($page_number - 1) . " pages from PDF.";

            } catch (Exception $e) {
                $error_message = "An error occurred during PDF conversion: " . $e->getMessage();
            }
        }
    } else {
        $error_message = "File upload failed. Error code: " . $_FILES['pdf_file']['error'];
    }
}

// Handle Image Deletion
if (isset($_POST['delete_all_images'])) {
    // Delete files from server
    $dir_path = $_SERVER['DOCUMENT_ROOT'] . '/uploads/manhwa/' . $chapter_id . '/';
    if (is_dir($dir_path)) {
        array_map('unlink', glob("$dir_path/*.*"));
        rmdir($dir_path);
    }
    // Delete records from database
    $sql = "DELETE FROM chapter_images WHERE chapter_id = ?";
    $pdo->prepare($sql)->execute([$chapter_id]);
    $success_message = "All images for this chapter have been deleted.";
}

// Fetch existing images for this chapter
$images = $pdo->prepare("SELECT * FROM chapter_images WHERE chapter_id = ? ORDER BY page_number ASC");
$images->execute([$chapter_id]);
$images_list = $images->fetchAll(PDO::FETCH_ASSOC);

$current_page_nav = 'manhwa_admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Chapter Images - Admin Panel</title>
    <link rel="stylesheet" href="admin_style.css">
    <style>
        .image-preview-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)); gap: 10px; }
        .image-preview-item { border: 1px solid #444; border-radius: 5px; padding: 5px; text-align: center; }
        .image-preview-item img { max-width: 100%; height: auto; border-radius: 4px; }
        .image-preview-item p { font-size: 12px; margin: 5px 0 0 0; color: #ccc; }
    </style>
</head>
<body>
    <div class="dashboard-wrapper">
        <aside class="admin-sidebar">
            <div class="admin-sidebar-header">Aether Stream</div>
            <nav class="admin-nav">
                <a href="/admin/dashboard" class="admin-nav-item">Dashboard</a>
                <a href="/admin/manage_anime" class="admin-nav-item">Manage Anime</a>
                <a href="/admin/manage_manhwa" class="admin-nav-item active">Manage Manhwa</a>
                <a href="/admin/manage_users" class="admin-nav-item">Manage Users</a>
                <a href="/admin/manage_codes" class="admin-nav-item">Manage VIP Codes</a>
                <a href="/home" class="admin-nav-item" target="_blank">View Live Site</a>
            </nav>
        </aside>
        <main class="dashboard-main-content">
            <header class="dashboard-header">
                <h1>Manage Images for: "<?php echo htmlspecialchars($chapter['manhwa_title']); ?> - Chapter <?php echo htmlspecialchars($chapter['chapter_number']); ?>"</h1>
                <a href="/admin/manage_manhwa_chapters?id=<?php echo $chapter['manhwa_id']; ?>" class="btn btn-secondary">&larr; Back to Chapters</a>
            </header>

            <?php if ($error_message): ?><div class="message error"><p><?php echo $error_message; ?></p></div><?php endif; ?>
            <?php if ($success_message): ?><div class="message success"><p><?php echo $success_message; ?></p></div><?php endif; ?>

            <?php if (empty($images_list)): ?>
            <div class="form-container">
                <h3>Upload Chapter PDF</h3>
                <p>Select a PDF file for this chapter. The system will automatically convert it into images.</p>
                <form action="/admin/manage_chapter_images?id=<?php echo $chapter_id; ?>" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="pdf_file">PDF File</label>
                        <input type="file" name="pdf_file" accept=".pdf" required>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Upload and Convert</button>
                    </div>
                </form>
            </div>
            <?php endif; ?>
            
            <div class="content-table" style="margin-top: 30px;">
                <h3>Existing Images (<?php echo count($images_list); ?> pages)</h3>
                <?php if (!empty($images_list)): ?>
                    <form action="/admin/manage_chapter_images?id=<?php echo $chapter_id; ?>" method="POST" style="margin-bottom: 20px;">
                         <button type="submit" name="delete_all_images" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete all images for this chapter?');">Delete All Images</button>
                    </form>
                    <div class="image-preview-grid">
                        <?php foreach ($images_list as $image): ?>
                            <div class="image-preview-item">
                                <img src="<?php echo htmlspecialchars($image['image_url']); ?>" alt="Page <?php echo htmlspecialchars($image['page_number']); ?>">
                                <p>Page <?php echo htmlspecialchars($image['page_number']); ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p>No images have been uploaded for this chapter yet.</p>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>