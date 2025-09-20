<?php
require 'db_connect.php';
require('fpdf/fpdf.php'); // Assuming FPDF is in a folder named 'fpdf'

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid Chapter ID.");
}
$chapter_id = (int)$_GET['id'];

// Fetch chapter details for a proper filename
$stmt_chapter = $pdo->prepare("
    SELECT mc.chapter_number, m.title as manhwa_title
    FROM manhwa_chapters mc
    JOIN manhwas m ON mc.manhwa_id = m.id
    WHERE mc.id = ?
");
$stmt_chapter->execute([$chapter_id]);
$chapter = $stmt_chapter->fetch(PDO::FETCH_ASSOC);

if (!$chapter) {
    die("Chapter not found.");
}

// Fetch all images for this chapter
$images_stmt = $pdo->prepare("SELECT image_url FROM chapter_images WHERE chapter_id = ? ORDER BY page_number ASC");
$images_stmt->execute([$chapter_id]);
$images = $images_stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($images)) {
    die("No images found for this chapter.");
}

// Create a new PDF instance
$pdf = new FPDF('P', 'mm', 'A4');

// Loop through images and add them to the PDF
foreach ($images as $image) {
    $image_path = $_SERVER['DOCUMENT_ROOT'] . $image['image_url'];
    if (file_exists($image_path)) {
        list($width, $height) = getimagesize($image_path);
        
        // Calculate image orientation and size for A4 page
        $a4_width_mm = 210;
        $a4_height_mm = 297;
        
        // Add a page
        $pdf->AddPage('P', 'A4');
        
        // Add the image to the page, fitting it to the page width
        $pdf->Image($image_path, 0, 0, $a4_width_mm, 0);
    }
}

// Sanitize filename for download
$download_filename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $chapter['manhwa_title']) . '_Ch_' . $chapter['chapter_number'] . '.pdf';

// Output the PDF to the browser
$pdf->Output('D', $download_filename);
exit;