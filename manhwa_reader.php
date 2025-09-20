<?php
require 'language_loader.php';
require 'db_connect.php';

// **FIXED**: Removed login check.

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: /manhwas');
    exit();
}
$chapter_id = (int)$_GET['id'];

// Fetch chapter details and also the main manhwa title
$chapter_stmt = $pdo->prepare("
    SELECT mc.id, mc.chapter_number, m.id as manhwa_id, m.title as manhwa_title
    FROM manhwa_chapters mc
    JOIN manhwas m ON mc.manhwa_id = m.id
    WHERE mc.id = ?
");
$chapter_stmt->execute([$chapter_id]);
$chapter = $chapter_stmt->fetch(PDO::FETCH_ASSOC);

if (!$chapter) {
    header('Location: /manhwas');
    exit();
}

// Fetch all images for this chapter, ordered by page number
$images_stmt = $pdo->prepare("SELECT image_url FROM chapter_images WHERE chapter_id = ? ORDER BY page_number ASC");
$images_stmt->execute([$chapter_id]);
$images = $images_stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['lang']; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reading <?php echo htmlspecialchars($chapter['manhwa_title']); ?> - Chapter <?php echo htmlspecialchars($chapter['chapter_number']); ?></title>
    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="stylesheet" href="/style.css?v=1.9">
    <style>
        body { background-color: #000; }
        .reader-container { max-width: 800px; margin: 0 auto; }
        .reader-header { padding: 15px; background-color: #1a1a1a; text-align: center; color: #fff; }
        .reader-header a { color: #007AFF; text-decoration: none; }
        .reader-images img {
            width: 100%;
            height: auto;
            display: block;
            margin: 0;
            padding: 0;
        }
    </style>
</head>
<body>
    <div class="reader-container">
        <style>
          .download-btn {
            display: inline-block;
            background-color: #28a745;
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            margin-top: 10px;
            }
          .download-btn:hover {
            background-color: #218838;
           }
        </style>
        <div class="reader-header">
            <h1><a href="/manhwa_chapters?id=<?php echo $chapter['manhwa_id']; ?>"><?php echo htmlspecialchars($chapter['manhwa_title']); ?></a></h1>
            <h2>Chapter <?php echo htmlspecialchars($chapter['chapter_number']); ?></h2>
            <a href="/download_chapter?id=<?php echo $chapter_id; ?>" class="download-btn">Download This Chapter</a>
        </div>
        <div class="reader-images">
            <?php if (empty($images)): ?>
                <p style="color: #fff; text-align: center; padding: 50px;">Images for this chapter are not available yet.</p>
            <?php else: ?>
                <?php foreach ($images as $image): ?>
                    <img src="<?php echo htmlspecialchars($image['image_url']); ?>" alt="Page">
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
         <div class="reader-header" style="margin-top: 20px;">
            <p><a href="/manhwas"> &larr; Back to Full List</a></p>
        </div>
    </div>
</body>
</html>