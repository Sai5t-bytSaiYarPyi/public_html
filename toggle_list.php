<?php
session_start();
require 'db_connect.php';

// NEW: Verify CSRF token first
if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    // If token is invalid, stop the script
    exit('Invalid CSRF token.');
}

// အသုံးပြုသူ Login ဝင်ထားမှ ဒီ feature ကို သုံးခွင့်ပြုပါ။
if (!isset($_SESSION['is_logged_in'])) {
    header('Location: /');
    exit;
}

// anime_id ပါလာသလား စစ်ဆေးပါ။
if (isset($_POST['anime_id']) && is_numeric($_POST['anime_id'])) {
    $anime_id = $_POST['anime_id'];
    $user_id = $_SESSION['user_id'];

    // အဲ့ဒီ Anime က စာရင်းထဲမှာ ရှိပြီးသားလား စစ်ဆေးပါ။
    $stmt_check = $pdo->prepare("SELECT id FROM user_favorites WHERE user_id = ? AND anime_id = ?");
    $stmt_check->execute([$user_id, $anime_id]);
    $exists = $stmt_check->fetch();

    if ($exists) {
        // ရှိပြီးသားဖြစ်နေရင် စာရင်းထဲကနေ ပြန်ဖျက်ပါ။
        $stmt_delete = $pdo->prepare("DELETE FROM user_favorites WHERE user_id = ? AND anime_id = ?");
        $stmt_delete->execute([$user_id, $anime_id]);
    } else {
        // မရှိသေးရင် စာရင်းထဲကို အသစ်ထည့်ပါ။
        $stmt_add = $pdo->prepare("INSERT INTO user_favorites (user_id, anime_id) VALUES (?, ?)");
        $stmt_add->execute([$user_id, $anime_id]);
    }

    // အသုံးပြုသူကို သူလာခဲ့တဲ့ စာမျက်နှာဆီကိုပဲ ပြန်ပို့ပေးလိုက်ပါ။
    if (isset($_SERVER['HTTP_REFERER'])) {
        header('Location: ' . $_SERVER['HTTP_REFERER']);
    } else {
        header('Location: /home'); // Fallback redirect
    }
    exit;

} else {
    // anime_id မပါလာရင် Homepage ကိုပဲ ပြန်ပို့ပါ။
    header('Location: /home');
    exit;
}
?>