<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer-6.9.1/src/Exception.php';
require 'PHPMailer-6.9.1/src/PHPMailer.php';
require 'PHPMailer-6.9.1/src/SMTP.php';
require 'config.php'; // Our email config
require 'db_connect.php'; // Our database connection

$message = '';
$message_type = ''; // 'success' or 'error'

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $email = trim($_POST['email']);

    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        // User exists, generate a token
        $token = bin2hex(random_bytes(32));
        $hashed_token = hash('sha256', $token);
        $expires_at = new DateTime('+1 hour');
        $expires_at_str = $expires_at->format('Y-m-d H:i:s');

        // Store the hashed token in the database
        $sql = "INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)";
        $pdo->prepare($sql)->execute([$email, $hashed_token, $expires_at_str]);

        // Create the reset link
        $reset_link = "https://najuanime.wuaze.com/reset_password.php?token=" . $token;

        // Send the email
        $mail = new PHPMailer(true);
        try {
            //Server settings
            $mail->isSMTP();
            $mail->Host       = SMTP_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = SMTP_USERNAME;
            $mail->Password   = SMTP_PASSWORD;
            $mail->SMTPSecure = SMTP_SECURE;
            $mail->Port       = SMTP_PORT;

            //Recipients
            $mail->setFrom(MAIL_FROM_ADDRESS, MAIL_FROM_NAME);
            $mail->addAddress($email);

            //Content
            $mail->isHTML(true);
            $mail->Subject = 'Password Reset Request for Aether Stream';
            $mail->AltBody = "Hi there,\n\nYou requested a password reset for your Aether Stream account. Please visit the following link to set a new password. This link is valid for 1 hour.\n\nLink: " . $reset_link . "\n\nIf you did not request this, please ignore this email.\n\nThanks,\nAether Stream Support";

            $mail->send();
            
        } catch (Exception $e) {
            // In a real production site, you would log this error instead of showing it.
            // For now, we just proceed silently.
        }
    }
    
    // Always show a generic success message to prevent people from checking if an email is registered.
    $message = "If an account with that email exists, a password reset link has been sent. Please check your inbox (and spam folder).";
    $message_type = 'success';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Aether Stream</title>
    <link rel="icon" type="image/png" href="favicon.png">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="login-container">
        <div class="logo">AETHER STREAM</div>
        <h2>Reset Your Password</h2>

        <?php if ($message): ?>
            <div class="message <?php echo htmlspecialchars($message_type); ?>"><p><?php echo htmlspecialchars($message); ?></p></div>
        <?php endif; ?>

        <form action="forgot_password.php" method="POST">
            <p style="color: #ccc; margin-bottom: 20px;">Enter your registered email address below. We will send you a link to reset your password.</p>
            <input type="email" name="email" class="input-field" placeholder="Your Email Address" required autofocus>
            <button type="submit" class="submit-btn">Send Reset Link</button>
        </form>

        <div class="form-footer-link">
            <a href="index.php">&larr; Back to Login</a>
        </div>
    </div>
</body>
</html>

