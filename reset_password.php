<?php
session_start();
require 'db_connect.php';

$error_message = '';
$success_message = '';
$token_valid = false;
$token = $_GET['token'] ?? '';

if (empty($token)) {
    $error_message = "Invalid or missing reset token.";
} else {
    $hashed_token = hash('sha256', $token);
    
    $stmt = $pdo->prepare("SELECT * FROM password_resets WHERE token = ? AND expires_at > NOW()");
    $stmt->execute([$hashed_token]);
    $reset_request = $stmt->fetch();

    if ($reset_request) {
        $token_valid = true;
        $email_to_update = $reset_request['email'];
    } else {
        $error_message = "This password reset link is invalid or has expired.";
    }
}

// Handle the form submission for the new password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $token_valid) {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password !== $confirm_password) {
        $error_message = "Passwords do not match.";
    } elseif (strlen($new_password) < 6) {
        $error_message = "Password must be at least 6 characters long.";
    } else {
        // Update the user's password
        $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $update_stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
        $update_stmt->execute([$new_hashed_password, $email_to_update]);

        // Delete the used token
        $delete_stmt = $pdo->prepare("DELETE FROM password_resets WHERE email = ?");
        $delete_stmt->execute([$email_to_update]);
        
        $success_message = "Your password has been reset successfully! You can now log in with your new password.";
        $token_valid = false; // Hide the form after success
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set New Password - Aether Stream</title>
    <link rel="icon" type="image/png" href="favicon.png">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="login-container">
        <div class="logo">AETHER STREAM</div>
        <h2>Set a New Password</h2>

        <?php if ($error_message): ?>
            <div class="message error"><p><?php echo $error_message; ?></p></div>
        <?php endif; ?>
        <?php if ($success_message): ?>
            <div class="message success"><p><?php echo $success_message; ?></p></div>
        <?php endif; ?>
        
        <?php if ($token_valid): ?>
            <form method="POST" action="reset_password.php?token=<?php echo htmlspecialchars($token); ?>">
                <input type="password" name="new_password" class="input-field" placeholder="Enter New Password" required>
                <input type="password" name="confirm_password" class="input-field" placeholder="Confirm New Password" required>
                <button type="submit" class="submit-btn">Reset Password</button>
            </form>
        <?php endif; ?>

        <div class="form-footer-link">
            <a href="index.php">&larr; Back to Login</a>
        </div>
    </div>
</body>
</html>