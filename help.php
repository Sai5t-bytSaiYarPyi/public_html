<?php
require 'language_loader.php';
require 'db_connect.php';
$current_page_nav = ''; // No active nav item
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['lang']; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Help Center - <?php echo $lang['aether_stream']; ?></title>
    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="stylesheet" href="/style.css?v=1.9">
    <style>
        .static-page-content { background-color: #1a1a1a; padding: 30px 40px; border-radius: 12px; color: #ccc; line-height: 1.8; font-size: 16px; }
        .static-page-content h1 { color: #fff; border-bottom: 1px solid #333; padding-bottom: 15px; margin-top: 0; margin-bottom: 25px; }
        .static-page-content h2 { color: #fff; margin-top: 30px; }
        .static-page-content p { margin-bottom: 20px; }
        .static-page-content ul { padding-left: 20px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="main-container">
        <aside class="sidebar">
            <div class="sidebar-logo"><?php echo $lang['aether_stream']; ?></div>
            <nav class="sidebar-nav">
                <a href="/home" class="nav-item">Home</a>
                <a href="/browse" class="nav-item">Browse</a>
                <a href="/manhwas" class="nav-item">Manhwa</a>
                <a href="/my_list" class="nav-item">My List</a>
                <a href="/history" class="nav-item">History</a>
                <a href="/settings" class="nav-item">Settings</a>
            </nav>
            <div class="sidebar-footer">
                 <?php if (isset($_SESSION['is_logged_in'])): ?>
                    <a href="/logout" class="logout-btn"><?php echo $lang['logout']; ?></a>
                <?php else: ?>
                    <a href="/" class="logout-btn">Login</a>
                <?php endif; ?>
            </div>
        </aside>
        <main class="main-content">
            <header class="main-header">
                 <h2>Help Center</h2>
            </header>
            <div class="static-page-content">
                <h1>Help Center (အမေးများသော မေးခွန်းများ)</h1>

                <h2>VIP အဖွဲ့ဝင်ဖြစ်ရန် ဘာလုပ်ရမလဲ?</h2>
                <p>Anime ဇာတ်လမ်းတွဲများအားလုံးကို ကြည့်ရှုရန် VIP အဖွဲ့ဝင်ဖြစ်ရန်လိုအပ်ပါသည်။ VIP ဝယ်ယူလိုပါက ကျွန်ုပ်တို့၏ Facebook Page သို့မဟုတ် Telegram သို့ ဆက်သွယ်ပြီး VIP ဝယ်ယူနိုင်ပါသည်။</p>
                
                <h2>Anime နှင့် Manhwa ဘာကွာခြားပါသလဲ?</h2>
                <p>ကျွန်ုပ်တို့ website တွင် Anime ဇာတ်လမ်းတွဲများမှာ VIP အဖွဲ့ဝင်များအတွက်သာ ဖြစ်ပြီး၊ Manhwa ဇာတ်လမ်းတွဲများမှာ အသုံးပြုသူတိုင်းအတွက် အခမဲ့ဖြစ်ပါသည်။</p>

                <h2>စကားဝှက် (Password) မေ့သွားပါက ဘာလုပ်ရမလဲ?</h2>
                <p>Login ဝင်သည့်စာမျက်နှာတွင်ရှိသော "Forgot Password?" ဆိုသည့် link ကိုနှိပ်ပြီး သင့် email ကိုဖြည့်စွက်ကာ password အသစ်ပြန်လည် သတ်မှတ်နိုင်ပါသည်။</p>

                <h2>တခြားအခက်အခဲများရှိပါက ဘယ်သူ့ကို ဆက်သွယ်ရမလဲ?</h2>
                <p>အခြား မေးမြန်းလိုသည်များ သို့မဟုတ် အခက်အခဲများရှိပါက အောက်ပါနေရာများတွင် ၂၄ နာရီပတ်လုံး ဆက်သွယ်မေးမြန်းနိုင်ပါသည်။</p>
                <ul>
                    <li><strong>Telegram:</strong> @NaJu_New</li>
                    <li><strong>Facebook Page:</strong> m.me/678463315349337</li>
                </ul>
            </div>
        </main>
    </div>
</body>
</html>