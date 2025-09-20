<?php
session_start();
require 'db_connect.php';
require 'language_loader.php';

$message = '';
$message_type = ''; // 'success' or 'error'

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['email'])) {
    $email = trim($_POST['email']);

    // Check if email exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        $token = bin2hex(random_bytes(50));
        $expires = new DateTime('now +1 hour');
        $expires_at = $expires->format('Y-m-d H:i:s');

        try {
            // 1. Transaction ကို စတင်ပါ
            $pdo->beginTransaction();

            // 2. password_resets table ထဲကို token ထည့်ပါ
            $stmt = $pdo->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
            $stmt->execute([$email, $token, $expires_at]);

            // 3. email_queue table ထဲကို အီးမေးလ်အကြောင်းအရာ ထည့်ပါ
            $reset_link = "https://" . $_SERVER['HTTP_HOST'] . "/reset_password?token=" . $token;
            $subject = 'Your Password Reset Request';
            
            $body = '
                <!DOCTYPE html>
                <html lang="en">
                <head><meta charset="UTF-8"><title>Password Reset</title><style>body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0; } .container { max-width: 600px; margin: 20px auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.1); } .header { background-color: #007AFF; color: #ffffff; padding: 20px; text-align: center; } .header h1 { margin: 0; font-size: 24px; } .content { padding: 30px; line-height: 1.6; color: #333; } .content p { margin: 0 0 15px; } .button-container { text-align: center; margin: 20px 0; } .button { background-color: #007AFF; color: #ffffff; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold; } .footer { background-color: #f4f4f4; color: #888; text-align: center; padding: 15px; font-size: 12px; } </style></head>
                <body><div class="container"><div class="header"><h1>Aether Stream</h1></div><div class="content"><p>Hello,</p><p>We received a request to reset the password for your account. Please click the button below to set a new password.</p><div class="button-container"><a href="' . htmlspecialchars($reset_link) . '" class="button">Reset Your Password</a></div><p>If you did not request a password reset, please ignore this email. This link is valid for one hour.</p><p>Thank you,<br>The Aether Stream Team</p></div><div class="footer"><p>&copy; ' . date("Y") . ' Aether Stream. All Rights Reserved.</p></div></div></body>
                </html>';

            $queue_stmt = $pdo->prepare("INSERT INTO email_queue (recipient_email, subject, body, status) VALUES (?, ?, ?, 'pending')");
            $queue_stmt->execute([$email, $subject, $body]);

            // 4. အားလုံးအောင်မြင်ရင် Transaction ကို အတည်ပြုပါ
            $pdo->commit();

            // 5. User ကို အောင်မြင်ကြောင်းပြပါ
            $message = $lang['reset_link_sent'];
            $message_type = 'success';

        } catch (PDOException $e) {
            // တစ်ခုခုမှားယွင်းခဲ့ရင် Transaction ကို ပြန်ဖျက်သိမ်းပါ
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            
            // User ကို ယေဘုယျ error message ပဲပြပါ (လုံခြုံရေးအတွက်)
            $message = "An error occurred. Please try again later.";
            // error အစစ်အမှန်ကို ကိုယ်တိုင်သိနိုင်ဖို့ log file ထဲမှာ မှတ်ထားပါ
            error_log("Forgot Password Error: " . $e->getMessage());
            $message_type = 'error';
        }
    } else {
        die("DEBUG MESSAGE: The email was not found in the database. This is the correct code path.");
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($_SESSION['lang'] ?? 'en'); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($lang['forgot_password']); ?> - <?php echo htmlspecialchars($lang['aether_stream']); ?></title>
    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="stylesheet" href="style.css?v=1.1">
</head>
<body>
    <div class="login-container">
        <div class="logo"><?php echo htmlspecialchars($lang['aether_stream']); ?></div>
        <h2><?php echo htmlspecialchars($lang['reset_password']); ?></h2>
        <?php if (!empty($message)): ?>
            <div class="message <?php echo htmlspecialchars($message_type); ?>"><p><?php echo htmlspecialchars($message); ?></p></div>
        <?php endif; ?>
        <?php if ($message_type !== 'success'): ?>
        <form method="POST" action="/forgot_password.php">
            <p style="color: #ccc; margin-bottom: 20px;"><?php echo htmlspecialchars($lang['forgot_password_instructions']); ?></p>
            <input type="email" name="email" class="input-field" placeholder="<?php echo htmlspecialchars($lang['email_address']); ?>" required autofocus>
            <button type="submit" class="submit-btn"><?php echo htmlspecialchars($lang['send_reset_link']); ?></button>
        </form>
        <?php endif; ?>
        <div class="form-footer-link" style="text-align: center; margin-top: 20px;">
            <p><a href="/"><?php echo htmlspecialchars($lang['back_to_login']); ?></a></p>
        </div>
    </div>
</body>
</html>