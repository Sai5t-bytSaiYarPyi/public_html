<?php
session_start();
require 'db_connect.php';
require 'language_loader.php';

// PHPMailer files
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer-6.9.1/src/Exception.php';
require 'PHPMailer-6.9.1/src/PHPMailer.php';
require 'PHPMailer-6.9.1/src/SMTP.php';

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['email'])) {
    $email = $_POST['email'];

    // Check if email exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        // Generate a unique token
        $token = bin2hex(random_bytes(50));
        // Set expiry time (e.g., 1 hour from now)
        $expires = new DateTime('now +1 hour');
        $expires_at = $expires->format('Y-m-d H:i:s');

        // Store the token in the database
        try {
            $stmt = $pdo->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
            $stmt->execute([$email, $token, $expires_at]);

            // Send the email
            $mail = new PHPMailer(true);
            try {
                //Server settings -- သင့်ရဲ့ Email Server အချက်အလက်ဖြည့်ပါ
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com'; // သင့်ရဲ့ SMTP server
                $mail->SMTPAuth   = true;
                $mail->Username   = 'najuanimevipweb@gmail.com'; // သင့်ရဲ့ SMTP username
                $mail->Password   = 'nffx afxm zqch owzs'; // သင့်ရဲ့ SMTP password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                $mail->Port       = 465;

                //Recipients
                $mail->setFrom('from@example.com', 'Your Website Name');
                $mail->addAddress($email);

                // Content
                $reset_link = "https://" . $_SERVER['HTTP_HOST'] . "/reset_password.php?token=" . $token;
                $mail->isHTML(true);
                $mail->Subject = 'Password Reset Request';
                $mail->Body    = "Hello,<br><br>Please click on the link below to reset your password:<br><a href='{$reset_link}'>{$reset_link}</a><br><br>This link will expire in 1 hour.<br><br>Thank you.";
                $mail->AltBody = 'To reset your password, please visit this link: ' . $reset_link;

                $mail->send();
                $message = $lang['reset_link_sent'];
            } catch (Exception $e) {
                $message = $lang['email_send_error'] . ": {$mail->ErrorInfo}";
            }
        } catch (PDOException $e) {
            $message = "Database error: " . $e->getMessage();
        }
    } else {
        $message = $lang['email_not_found'];
    }
}
?>
<!DOCTYPE html>
<html lang="<?= $_SESSION['language'] ?? 'en' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $lang['forgot_password'] ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="login-container">
        <form action="forgot_password.php" method="post" class="login-form">
            <h2><?= $lang['forgot_password'] ?></h2>
            <?php if (!empty($message)): ?>
                <p class="message"><?= htmlspecialchars($message) ?></p>
            <?php endif; ?>
            <p><?= $lang['forgot_password_instructions'] ?></p>
            <div class="input-group">
                <input type="email" name="email" placeholder="<?= $lang['email'] ?>" required>
            </div>
            <button type="submit" class="btn"><?= $lang['send_reset_link'] ?></button>
            <div class="bottom-text">
                <a href="index.php"><?= $lang['back_to_login'] ?></a>
            </div>
        </form>
    </div>
</body>
</html>
