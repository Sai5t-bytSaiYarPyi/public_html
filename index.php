<?php
session_start();
require 'language_loader.php'; // Load language file first
require 'db_connect.php';

if (isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in'] === true) {
    header('Location: /home');
    exit();
}

$error_message = '';
$success_message = '';

if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error_message = $lang['all_fields_required'];
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['is_logged_in'] = true;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            
            if ($user['vip_expiry_date'] !== null && new DateTime() < new DateTime($user['vip_expiry_date'])) {
                $_SESSION['is_vip'] = true;
            } else {
                $_SESSION['is_vip'] = false;
            }
            
            header("Location: /home");
            exit();
        } else {
            $error_message = $lang['invalid_credentials'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['lang']; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $lang['login']; ?> - <?php echo $lang['aether_stream']; ?></title>
    <link rel="icon" type="image/png" href="favicon.png">
    <link rel="stylesheet" href="style.css?v=1.1">
</head>
<body>
    <div class="login-container">
        <div class="logo"><?php echo $lang['aether_stream']; ?></div>
        <h2><?php echo $lang['welcome_back']; ?></h2>

        <?php if ($error_message): ?>
            <div class="message error"><p><?php echo $error_message; ?></p></div>
        <?php endif; ?>
        <?php if ($success_message): ?>
            <div class="message success"><p><?php echo $success_message; ?></p></div>
        <?php endif; ?>

        <form method="POST" action="/">
            <input type="text" name="username" class="input-field" placeholder="<?php echo $lang['username']; ?>" required autofocus>
            <input type="password" name="password" class="input-field" placeholder="<?php echo $lang['password']; ?>" required>
            <button type="submit" class="submit-btn"><?php echo $lang['login']; ?></button>
        </form>

        <div class="form-footer-link" style="text-align: center;">
            <p style="margin-bottom: 10px;">
                <a href="/forgot_password"><?php echo $lang['forgot_password']; ?></a>
            </p>
            <p>
                <?php echo $lang['dont_have_account']; ?> <a href="/register"><?php echo $lang['register_here']; ?></a>
            </p>
        </div>
    </div>
</body>
</html>