<?php
session_start();
require 'db_connect.php';
require 'language_loader.php';

$message = '';
$token_valid = false;

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Validate the token
    $stmt = $pdo->prepare("SELECT * FROM password_resets WHERE token = ? AND expires_at > NOW()");
    $stmt->execute([$token]);
    $reset_request = $stmt->fetch();

    if ($reset_request) {
        $token_valid = true;
        $email = $reset_request['email'];

        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['password']) && isset($_POST['confirm_password'])) {
            $password = $_POST['password'];
            $confirm_password = $_POST['confirm_password'];

            if ($password !== $confirm_password) {
                $message = $lang['passwords_do_not_match'];
            } elseif (strlen($password) < 6) {
                $message = $lang['password_too_short'];
            } else {
                // Hash the new password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Update the user's password
                $update_stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
                if ($update_stmt->execute([$hashed_password, $email])) {
                    // Delete the token
                    $delete_stmt = $pdo->prepare("DELETE FROM password_resets WHERE email = ?");
                    $delete_stmt->execute([$email]);

                    // Redirect to login page with success message
                    $_SESSION['success_message'] = $lang['password_reset_success'];
                    header("Location: index.php");
                    exit();
                } else {
                    $message = $lang['password_reset_error'];
                }
            }
        }
    } else {
        $message = $lang['invalid_or_expired_token'];
    }
} else {
    $message = $lang['no_token_provided'];
}
?>
<!DOCTYPE html>
<html lang="<?= $_SESSION['language'] ?? 'en' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $lang['reset_password'] ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="login-container">
        <form action="reset_password.php?token=<?= htmlspecialchars($_GET['token'] ?? '') ?>" method="post" class="login-form">
            <h2><?= $lang['reset_password'] ?></h2>
            <?php if (!empty($message)): ?>
                <p class="message error"><?= htmlspecialchars($message) ?></p>
            <?php endif; ?>

            <?php if ($token_valid): ?>
                <div class="input-group">
                    <input type="password" name="password" placeholder="<?= $lang['new_password'] ?>" required>
                </div>
                <div class="input-group">
                    <input type="password" name="confirm_password" placeholder="<?= $lang['confirm_new_password'] ?>" required>
                </div>
                <button type="submit" class="btn"><?= $lang['reset_password'] ?></button>
            <?php endif; ?>
             <div class="bottom-text">
                <a href="index.php"><?= $lang['back_to_login'] ?></a>
            </div>
        </form>
    </div>
</body>
</html>