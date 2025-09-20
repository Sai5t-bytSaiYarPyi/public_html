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
    <title>Copyright Notice - <?php echo $lang['aether_stream']; ?></title>
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
                 <h2>Copyright Notice</h2>
            </header>
            <div class="static-page-content">
                <h1>Copyright & DMCA Notice</h1>
                <p>Aether Stream တွင် တင်ဆက်ထားသော Anime နှင့် Manhwa များအားလုံးသည် မူလဖန်တီးသူများနှင့် ထုတ်လုပ်သူများ၏ မူပိုင်ခွင့်များသာဖြစ်ပါသည်။ ကျွန်ုပ်တို့သည် ဤ content များကို ပိုင်ဆိုင်ခြင်းမရှိပါ။</p>
                
                <p>ကျွန်ုပ်တို့၏ အဓိကရည်ရွယ်ချက်မှာ ဘာသာစကားအခက်အခဲကြောင့် မကြည့်ရှုနိုင်၊ မဖတ်ရှုနိုင်သော ပရိသတ်များအတွက် အခမဲ့ ကြည့်ရှုဖတ်ရှုနိုင်ရန် စေတနာဖြင့် ဘာသာပြန် တင်ဆက်ပေးခြင်းဖြစ်ပြီး မည်သည့်စီးပွားရေးအမြတ်အစွန်းအတွက်မှ ရည်ရွယ်ခြင်းမရှိပါ။</p>

                <h2>မူပိုင်ခွင့်ရှင်များသို့ အသိပေးချက် (DMCA)</h2>
                <p>အကယ်၍ သင်သည် မူပိုင်ခွင့်ရှင်တစ်ဦးဖြစ်ပြီး သင်၏ content ကို ကျွန်ုပ်တို့ website မှ ဖယ်ရှားလိုပါက၊ ကျေးဇူးပြု၍ အောက်ပါလိပ်စာများသို့ ဆက်သွယ်အကြောင်းကြားပေးပါ။ သင်၏ တောင်းဆိုချက်ကို ရရှိပြီးနောက် အမြန်ဆုံး ဖယ်ရှားပေးပါမည်။</p>
                
                <h2>ဆက်သွယ်ရန်</h2>
                <ul>
                    <li><strong>Telegram:</strong> @NaJu_New</li>
                    <li><strong>Facebook Page:</strong> m.me/678463315349337</li>
                </ul>
            </div>
        </main>
    </div>
</body>
</html>