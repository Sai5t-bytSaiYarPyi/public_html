<?php
session_start();
require 'language_loader.php';
require 'db_connect.php';

// NEW: Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    header('Location: /');
    exit();
}
$error_message = '';
$success_message = '';
if(isset($_SESSION['error_message'])){
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // NEW: Verify CSRF token
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error_message = 'Invalid request. Please try again.';
    } else {
        $vip_code = trim($_POST['vip_code']);
        $stmt = $pdo->prepare("SELECT * FROM codes WHERE access_code = ? AND is_used = 0");
        $stmt->execute([$vip_code]);
        $code = $stmt->fetch();
        if ($code) {
            $user_id = $_SESSION['user_id'];
            $user_stmt = $pdo->prepare("SELECT vip_expiry_date FROM users WHERE id = ?");
            $user_stmt->execute([$user_id]);
            $current_expiry = $user_stmt->fetchColumn();
            $new_expiry_date = new DateTime();
            if ($current_expiry !== null && new DateTime($current_expiry) > $new_expiry_date) {
                $new_expiry_date = new DateTime($current_expiry);
            }
            $new_expiry_date->add(new DateInterval('P1M'));
            $new_expiry_date_str = $new_expiry_date->format('Y-m-d');
            $update_user = $pdo->prepare("UPDATE users SET vip_expiry_date = ? WHERE id = ?");
            $update_user->execute([$new_expiry_date_str, $user_id]);
            $update_code = $pdo->prepare("UPDATE codes SET is_used = 1, used_by_user_id = ?, used_at = NOW() WHERE id = ?");
            $update_code->execute([$user_id, $code['id']]);
            $_SESSION['is_vip'] = true;
            $success_message = $lang['vip_activation_success'];
            header('Refresh: 3; URL=/home');
        } else {
            $error_message = $lang['invalid_vip_code'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['lang']; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $lang['activate_vip_access']; ?> - <?php echo $lang['aether_stream']; ?></title>
    <link rel="icon" type="image/png" href="favicon.png">
    <link rel="stylesheet" href="style.css?v=1.1">
</head>
<body>
    <div class="login-container">
        <div class="logo"><?php echo $lang['aether_stream']; ?></div>
        <h2><?php echo $lang['activate_vip_access']; ?></h2>
        <?php if ($error_message): ?>
            <div class="message error"><p><?php echo $error_message; ?></p></div>
        <?php endif; ?>
        <?php if ($success_message): ?>
            <div class="message success"><p><?php echo $success_message; ?></p></div>
        <?php endif; ?>
        <?php if (empty($success_message)): ?>
        <form method="POST" action="/activate_vip">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
            <p style="color: #ccc; margin-bottom: 20px;"><?php echo $lang['activate_vip_instructions']; ?></p>
            <input type="text" name="vip_code" class="input-field" placeholder="<?php echo $lang['enter_vip_code']; ?>" required autofocus>
            <button type="submit" class="submit-btn"><?php echo $lang['activate']; ?></button>
        </form>
        <?php endif; ?>
         <div class="form-footer-link">
            <p><a href="/logout"><?php echo $lang['logout']; ?></a></p>
        </div>
    </div>
</body>
</html>