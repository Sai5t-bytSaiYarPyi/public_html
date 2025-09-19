<?php
// forgot_password.php (Updated to use SendGrid API)

// Load necessary files
require 'db_connect.php';
require 'config.php';
require 'language_loader.php';
// Load the SendGrid library that you uploaded
require 'sendgrid-php/sendgrid-php.php';

$message = '';
$message_type = ''; // 'success' or 'error'

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $email_to_reset = trim($_POST['email']);

    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email_to_reset]);
    $user = $stmt->fetch();

    if ($user) {
        // --- Token Generation (This logic is still good) ---
        $token = bin2hex(random_bytes(32));
        $hashed_token = hash('sha256', $token);
        $expires_at = new DateTime('+1 hour');
        $expires_at_str = $expires_at->format('Y-m-d H:i:s');

        // Delete any old tokens for this email first
        $del_stmt = $pdo->prepare("DELETE FROM password_resets WHERE email = ?");
        $del_stmt->execute([$email_to_reset]);
        
        // Insert the new token
        $sql_reset = "INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)";
        $pdo->prepare($sql_reset)->execute([$email_to_reset, $hashed_token, $expires_at_str]);

        // Construct the reset link
        $reset_link = "https://najuianime.online/reset_password.php?token=" . $token;

        // --- Send Email using SendGrid API ---
        $email = new \SendGrid\Mail\Mail(); 
        $email->setFrom(MAIL_FROM_ADDRESS, MAIL_FROM_NAME);
        $email->setSubject("Password Reset Request for Aether Stream");
        $email->addTo($email_to_reset);

        // HTML Body
        $html_content = "Hi there,<br><br>You requested a password reset for your Aether Stream account. Please click the link below to set a new password. This link is valid for 1 hour.<br><br>
                         <a href='" . $reset_link . "' style='background-color:#007AFF;color:white;padding:10px 15px;text-decoration:none;border-radius:5px;'>Reset Your Password</a>
                         <br><br>If you did not request this, please ignore this email.<br><br>Thanks,<br>Aether Stream Support";
        $email->addContent("text/html", $html_content);
        
        // Plain Text Body (for older email clients)
        $plain_text_content = "Hi there,\n\nYou requested a password reset for your Aether Stream account. Please visit the following link to set a new password. This link is valid for 1 hour.\n\nLink: " . $reset_link . "\n\nIf you did not request this, please ignore this email.\n\nThanks,\nAether Stream Support";
        $email->addContent("text/plain", $plain_text_content);

        // Create SendGrid object and send the email
        $sendgrid = new \SendGrid(SENDGRID_API_KEY);
        try {
            $response = $sendgrid->send($email);
            if ($response->statusCode() == 202) { // 202 means Accepted
                $message = $lang['forgot_password_success'];
                $message_type = 'success';
            } else {
                $message = 'Error: Could not send email. Status: ' . $response->statusCode();
                $message_type = 'error';
            }
        } catch (Exception $e) {
            $message = 'Error sending email: '. $e->getMessage();
            $message_type = 'error';
        }

    } else {
        // Security: Even if user not found, show a generic success message
        // This prevents people from guessing which emails are registered.
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

        <form action="/forgot_password" method="POST">
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