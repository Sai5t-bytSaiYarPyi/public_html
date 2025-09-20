<?php
/* Aether Stream - Database Connection File
*/

// --- START: YOUR DATABASE DETAILS ---
$db_host = "localhost"; 
$db_name = "u873003635_20252026"; 
$db_user = "u873003635_naju_anime";
$db_pass = "pVyHAH1?g";
// --- END: YOUR DATABASE DETAILS ---


// --- Do not edit below this line ---
try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e){
    die("Error: Unable to connect to the service. Please try again later.");
}

date_default_timezone_set('Asia/Yangon');

// --- CENTRALIZED SESSION MANAGEMENT ---
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 86400, // Session lasts for 1 day
        'path' => '/',
        'secure' => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

// --- RE-CHECK VIP STATUS & EXPIRY DATE ON EVERY PAGE LOAD ---
if (isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in'] === true) {
    try {
        $stmt = $pdo->prepare("SELECT vip_expiry_date FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user_vip_status = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user_vip_status && $user_vip_status['vip_expiry_date'] !== null) {
            $expiry_date = new DateTime($user_vip_status['vip_expiry_date']);
            $_SESSION['vip_expiry_date_display'] = $expiry_date->format('M j, Y'); 
            
            if (new DateTime() < $expiry_date) {
                $_SESSION['is_vip'] = true;
            } else {
                $_SESSION['is_vip'] = false;
            }
        } else {
            $_SESSION['is_vip'] = false;
            unset($_SESSION['vip_expiry_date_display']);
        }

        // --- NEW: FETCH UNREAD NOTIFICATIONS ---
        $notif_stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? AND is_read = 0 ORDER BY created_at DESC LIMIT 10");
        $notif_stmt->execute([$_SESSION['user_id']]);
        $_SESSION['unread_notifications'] = $notif_stmt->fetchAll(PDO::FETCH_ASSOC);
        $_SESSION['unread_notification_count'] = count($_SESSION['unread_notifications']);
        // --- END: FETCH UNREAD NOTIFICATIONS ---

    } catch (PDOException $e) {
        $_SESSION['is_vip'] = false;
        unset($_SESSION['vip_expiry_date_display']);
    }
}
?>
