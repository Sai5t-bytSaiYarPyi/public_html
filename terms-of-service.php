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
    <title>Terms of Service - <?php echo $lang['aether_stream']; ?></title>
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
                 <h2>Terms of Service</h2>
            </header>
            <div class="static-page-content">
                <h1>Terms of Service</h1>
                <p>Aether Stream ကို အသုံးပြုခြင်းဖြင့် သင်သည် ဤဝန်ဆောင်မှုဆိုင်ရာ စည်းကမ်းချက်များကို သဘောတူပြီးဖြစ်ပါသည်။</p>

                <h2>Account နှင့် သက်ဆိုင်သော စည်းကမ်းချက်များ</h2>
                <ul>
                    <li>အသုံးပြုသူများသည် မိမိတို့၏ account နှင့် password လုံခြုံရေးကို တာဝန်ယူရပါမည်။</li>
                    <li>Website ၏ ဝန်ဆောင်မှုများကို အလွဲသုံးစားလုပ်ခြင်း၊ တိုက်ခိုက်ခြင်း၊ မဖွယ်မရာ comment များရေးသားခြင်း မပြုလုပ်ရပါ။</li>
                </ul>

                <h2>ဝန်ဆောင်မှုများ</h2>
                <ul>
                    <li><strong>VIP Membership (Anime):</strong> Anime ဇာတ်လမ်းတွဲများကြည့်ရှုရန်အတွက် VIP အဖွဲ့ဝင်ဖြစ်ရန်လိုအပ်ပြီး သတ်မှတ်ထားသော ဝန်ဆောင်ခပေးဆောင်ရန် လိုအပ်ပါသည်။</li>
                    <li><strong>Free Content (Manhwa):</strong> ကျွန်ုပ်တို့ website ရှိ Manhwa များအားလုံးကို အခမဲ့ဖတ်ရှုနိုင်ပါသည်။ ၎င်းသည် အသုံးပြုသူများအား စိတ်ရင်းစေတနာအမှန်ဖြင့် အခမဲ့ဖတ်ရှုစေလိုသော ရည်ရွယ်ချက်ဖြင့် ဝန်ဆောင်မှုပေးခြင်းဖြစ်ပါသည်။</li>
                </ul>

                <h2>စည်းကမ်းချက်များ ပြောင်းလဲခြင်း</h2>
                <p>Aether Stream အနေဖြင့် ဤစည်းကမ်းချက်များကို အချိန်မရွေး ပြောင်းလဲပိုင်ခွင့်ရှိပါသည်။</p>
                
                <h2>ဆက်သွယ်ရန်</h2>
                <p>ဤစည်းကမ်းချက်များနှင့် ပတ်သက်၍ မေးမြန်းလိုသည်များရှိပါက အောက်ပါနေရာများတွင် ဆက်သွယ်နိုင်ပါသည်။</p>
                <ul>
                    <li><strong>Telegram:</strong> @NaJu_New</li>
                    <li><strong>Facebook Page:</strong> m.me/678463315349337</li>
                </ul>
            </div>
        </main>
    </div>
</body>
</html>