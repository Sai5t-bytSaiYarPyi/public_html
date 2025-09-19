<?php
session_start();
require 'db_connect.php';
require 'language_loader.php';

$error_message = '';
$success_message = '';
$token_valid = false;
$token = $_GET['token'] ?? '';

if (empty($token)) {
    $error_message = $lang['invalid_token'];
} else {
    $hashed_token = hash('sha256', $token);
    
    $stmt = $pdo->prepare("SELECT * FROM password_resets WHERE token = ? AND expires_at > NOW()");
    $stmt->execute([$hashed_token]);
    $reset_request = $stmt->fetch();

    if ($reset_request) {
        $token_valid = true;
        $email_to_update = $reset_request['email'];
    } else {
        $error_message = $lang['invalid_token'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $token_valid) {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password !== $confirm_password) {
        $error_message = $lang['passwords_no_match'];
    } elseif (strlen($new_password) < 6) {
        $error_message = $lang['password_min_length'];
    } else {
        $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $update_stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
        $update_stmt->execute([$new_hashed_password, $email_to_update]);

        $delete_stmt = $pdo->prepare("DELETE FROM password_resets WHERE email = ?");
        $delete_stmt->execute([$email_to_update]);
        
        $success_message = $lang['password_reset_success'];
        $token_valid = false;
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['lang']; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $lang['set_new_password']; ?></title>
    <link rel="stylesheet" href="https://darkgreen-crane-161567.hostingersite.com/style.css">
</head>
<body>
    <div class="login-container">
        <div class="logo"><?php echo $lang['aether_stream']; ?></div>
        <h2><?php echo $lang['set_new_password']; ?></h2>

        <?php if ($error_message): ?>
            <div class="message error"><p><?php echo $error_message; ?></p></div>
        <?php endif; ?>
        <?php if ($success_message): ?>
            <div class="message success"><p><?php echo $success_message; ?></p></div>
        <?php endif; ?>
        
        <?php if ($token_valid): ?>
            <form method="POST" action="reset_password.php?token=<?php echo htmlspecialchars($token); ?>">
                <input type="password" name="new_password" class="input-field" placeholder="<?php echo $lang['enter_new_password']; ?>" required>
                <input type="password" name="confirm_password" class="input-field" placeholder="<?php echo $lang['confirm_new_password']; ?>" required>
                <button type="submit" class="submit-btn"><?php echo $lang['reset_password']; ?></button>
            </form>
        <?php endif; ?>

        <div class="form-footer-link">
            <a href="/">&larr; <?php echo $lang['back_to_login']; ?></a>
        </div>
    </div>
</body>
</html>