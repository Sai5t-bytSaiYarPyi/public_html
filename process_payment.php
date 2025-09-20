<?php
session_start();
require 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['is_logged_in'])) {
    header('Location: /');
    exit();
}

// Verify CSRF token
if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    exit('Invalid request.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['plan_key'])) {
    $plan_key = $_POST['plan_key'];
    
    // Define plans again to get price and other details
    $vip_plans = [
        '1_month' => ['name' => '1 Month VIP', 'price' => 5000, 'duration' => 'P1M'],
        '3_months' => ['name' => '3 Months VIP', 'price' => 12000, 'duration' => 'P3M'],
        '6_months' => ['name' => '6 Months VIP', 'price' => 20000, 'duration' => 'P6M'],
    ];

    if (!array_key_exists($plan_key, $vip_plans)) {
        exit('Invalid plan selected.');
    }

    $selected_plan = $vip_plans[$plan_key];
    $user_id = $_SESSION['user_id'];
    
    // --- START: PAYMENT GATEWAY INTEGRATION (PLACEHOLDER) ---
    // ဒီနေရာမှာ သင့်ရဲ့ Payment Gateway (WavePay, KBZPay, etc.) ကပေးတဲ့ SDK/API code တွေကို ထည့်သွင်းရပါမယ်။
    // ဥပမာ - gateway ကို order create လုပ်ဖို့ request ပို့တာမျိုး၊ ပြီးရင် gateway ရဲ့ payment page ကို redirect လုပ်တာမျိုးတွေပါ။
    
    // For this simulation, we will generate a unique order ID and pretend the payment is successful.
    $order_id = 'AETHER-' . time() . '-' . $user_id;
    
    // Insert a "pending" transaction into our database
    $sql = "INSERT INTO transactions (user_id, plan_name, amount, payment_gateway, transaction_id, payment_status) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id, $selected_plan['name'], $selected_plan['price'], 'SimulatedGateway', $order_id, 'pending']);
    $transaction_db_id = $pdo->lastInsertId();

    // အစစ်အမှန်ဆိုရင် ဒီနေရာမှာ Payment Gateway Page ကို redirect လုပ်ရပါမယ်။
    // Simulation မှာတော့ ကျတော်တို့က အောင်မြင်တယ်လို့ယူဆပြီး payment_success.php ကို တန်းသွားပါမယ်။
    // Gateway ကနေ ကိုယ့်ဆီပြန်လာမယ့် URL (callback URL) ပုံစံမျိုးပါ။
    header('Location: /payment_success?trans_id=' . urlencode($order_id) . '&db_id=' . $transaction_db_id);
    exit();
    
    // --- END: PAYMENT GATEWAY INTEGRATION (PLACEHOLDER) ---

} else {
    header('Location: /purchase_vip');
    exit();
}