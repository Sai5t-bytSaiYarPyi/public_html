<?php
// forgot_password.php (Corrected Version for Direct Sending)

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require 'db_connect.php';
require 'config.php';
require 'language_loader.php';

// Load PHPMailer classes
require 'PHPMailer-6.9.1/src/Exception.php';
require 'PHPMailer-6.9.1/src/PHPMailer.php';
require 'PHPMailer-6.9.1/src/SMTP.php';


$message = '';
$message_type = ''; // 'success' or 'error'

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $email = trim($_POST['email']);

    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        // --- Token Generation (This part is correct) ---
        $token = bin2hex(random_bytes(32));
        $hashed_token = hash('sha256', $token);
        $expires_at = new DateTime('+1 hour');
        $expires_at_str = $expires_at->format('Y-m-d H:i:s');

        $sql_reset = "INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)";
        $pdo->prepare($sql_reset)->execute([$email, $hashed_token, $expires_at_str]);

        $reset_link = "https://najuanime.wuaze.com/reset_password.php?token=" . $token;

        // --- Direct Email Sending Logic (This is the new part) ---
        $mail = new PHPMailer(true);
        
        // --- ERROR DEBUGGING CODE ---
        // This will show us any errors if the email fails to send.
        $mail->SMTPDebug = SMTP::DEBUG_SERVER; 
        $mail->Debugoutput = 'html'; 
        // --- END DEBUGGING CODE ---

        try {
            //Server settings from config.php
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
            $mail->CharSet = 'UTF-8';
            $mail->Subject = 'Password Reset Request for Aether Stream';
            $mail->Body    = "Hi there,<br><br>You requested a password reset for your Aether Stream account. Please click the link below to set a new password. This link is valid for 1 hour.<br><br><a href='" . $reset_link . "'>Reset Your Password</a><br><br>If you did not request this, please ignore this email.<br><br>Thanks,<br>Aether Stream Support";
            $mail->AltBody = "Hi there,\n\nYou requested a password reset for your Aether Stream account. Please visit the following link to set a new password. This link is valid for 1 hour.\n\nLink: " . $reset_link . "\n\nIf you did not request this, please ignore this email.\n\nThanks,\nAether Stream Support";

            $mail->send();
            $message = $lang['forgot_password_success'];
            $message_type = 'success';

        } catch (Exception $e) {
            // If sending fails, show an error message
            $message = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            $message_type = 'error';
        }
    } else {
        // If user not found, still show a generic success message for security
        $message = $lang['forgot_password_success'];
        $message_type = 'success';
    }
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
        <h2><?php echo $lang['reset_your_password']; ?></h2>

        <?php if ($message): ?>
            <div class="message <?php echo htmlspecialchars($message_type); ?>"><p><?php echo nl2br(htmlspecialchars($message)); ?></p></div>
        <?php endif; ?>

        <form action="forgot_password.php" method="POST">
            <p style="color: #ccc; margin-bottom: 20px;"><?php echo $lang['forgot_password_instructions']; ?></p>
            <input type="email" name="email" class="input-field" placeholder="Your Email Address" required autofocus>
            <button type="submit" class="submit-btn"><?php echo $lang['send_reset_link']; ?></button>
        </form>

        <div class="form-footer-link">
            <a href="/">&larr; <?php echo $lang['back_to_login']; ?></a>
        </div>
    </div>
</body>
</html>