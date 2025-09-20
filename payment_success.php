<?php
session_start();
require 'language_loader.php';
require 'db_connect.php';

if (!isset($_SESSION['is_logged_in'])) {
    header('Location: /');
    exit();
}

if (!isset($_GET['trans_id']) || !isset($_GET['db_id'])) {
    header('Location: /home');
    exit();
}

$transaction_id = $_GET['trans_id'];
$db_id = $_GET['db_id'];
$user_id = $_SESSION['user_id'];
$error_message = '';
$success_message = '';

// --- START: PAYMENT VERIFICATION (PLACEHOLDER) ---
// ဒီနေရာမှာ Payment Gateway ကနေပြန်ရောက်လာတဲ့ transaction ID က တကယ်ပဲ ငွေလွှဲအောင်မြင်ခဲ့သလားဆိုတာကို
// Gateway ရဲ့ API ကို ပြန်ပြီးစစ်ဆေးရပါမယ်။
// ဥပမာ: $gateway->verifyTransaction($transaction_id);

// Simulation မှာတော့ ကျတော်တို့က အောင်မြင်တယ်လို့ပဲ တိုက်ရိုက်ယူဆလိုက်ပါမယ်။
$payment_was_successful = true; 
// --- END: PAYMENT VERIFICATION (PLACEHOLDER) ---


if ($payment_was_successful) {
    // 1. Fetch the transaction details from our database
    $stmt = $pdo->prepare("SELECT * FROM transactions WHERE id = ? AND user_id = ? AND transaction_id = ? AND payment_status = 'pending'");
    $stmt->execute([$db_id, $user_id, $transaction_id]);
    $transaction = $stmt->fetch();

    if ($transaction) {
        // 2. Update the transaction status to 'completed'
        $update_trans = $pdo->prepare("UPDATE transactions SET payment_status = 'completed' WHERE id = ?");
        $update_trans->execute([$db_id]);

        // 3. Update the user's VIP expiry date
        $plan_name = $transaction['plan_name'];
        $duration = '';
        if (strpos($plan_name, '1 Month') !== false) $duration = 'P1M';
        if (strpos($plan_name, '3 Months') !== false) $duration = 'P3M';
        if (strpos($plan_name, '6 Months') !== false) $duration = 'P6M';

        if (!empty($duration)) {
            $user_stmt = $pdo->prepare("SELECT vip_expiry_date FROM users WHERE id = ?");
            $user_stmt->execute([$user_id]);
            $current_expiry = $user_stmt->fetchColumn();

            $new_expiry_date = new DateTime();
            if ($current_expiry !== null && new DateTime($current_expiry) > $new_expiry_date) {
                $new_expiry_date = new DateTime($current_expiry);
            }
            
            $new_expiry_date->add(new DateInterval($duration));
            $new_expiry_date_str = $new_expiry_date->format('Y-m-d');
            
            $update_user = $pdo->prepare("UPDATE users SET vip_expiry_date = ? WHERE id = ?");
            $update_user->execute([$new_expiry_date_str, $user_id]);

            $_SESSION['is_vip'] = true; // Update session
            $success_message = "Payment successful! Your VIP membership has been updated.";
            header('Refresh: 4; URL=/home');
        } else {
            $error_message = 'Could not determine plan duration.';
        }
    } else {
        $error_message = 'Invalid or already processed transaction.';
    }
} else {
    // If payment was not successful
    $update_trans = $pdo->prepare("UPDATE transactions SET payment_status = 'failed' WHERE id = ?");
    $update_trans->execute([$db_id]);
    $error_message = 'Payment failed. Please try again or contact support.';
}
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['lang']; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Status - <?php echo $lang['aether_stream']; ?></title>
    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="stylesheet" href="/style.css?v=1.8">
</head>
<body>
    <div class="login-container" style="text-align: center;">
        <div class="logo"><?php echo $lang['aether_stream']; ?></div>
        <?php if ($success_message): ?>
            <h2>Payment Successful</h2>
            <div class="message success"><p><?php echo htmlspecialchars($success_message); ?></p></div>
            <p style="color: #ccc;">You will be redirected to the homepage shortly.</p>
        <?php else: ?>
            <h2>Payment Failed</h2>
            <div class="message error"><p><?php echo htmlspecialchars($error_message); ?></p></div>
            <a href="/purchase_vip" class="btn btn-primary" style="margin-top:20px;">Try Again</a>
        <?php endif; ?>
    </div>
</body>
</html>