<?php
session_start();
require 'db_connect.php';

if (isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in'] === true) {
    $user_id = $_SESSION['user_id'];
    
    $sql = "UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
    
    // Clear notifications from session to update the UI
    $_SESSION['unread_notifications'] = [];
    $_SESSION['unread_notification_count'] = 0;
    
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
}
?>