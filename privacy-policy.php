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
    <title>Privacy Policy - <?php echo $lang['aether_stream']; ?></title>
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
                 <h2>Privacy Policy</h2>
            </header>
            <div class="static-page-content">
                <h1>Privacy Policy</h1>
                <p>Aether Stream တွင် ကျွန်ုပ်တို့သည် သင်၏ကိုယ်ရေးကိုယ်တာအချက်အလက်များကို လေးစားတန်ဖိုးထားပါသည်။ ဤမူဝါဒသည် ကျွန်ုပ်တို့၏ website ကို သင်အသုံးပြုသည့်အခါ မည်သည့်အချက်အလက်များကို စုဆောင်းပြီး မည်သို့အသုံးပြုသည်ကို ရှင်းပြထားပါသည်။</p>

                <h2>ကျွန်ုပ်တို့ စုဆောင်းသော အချက်အလက်များ</h2>
                <ul>
                    <li><strong>Account အချက်အလက်များ:</strong> သင် account ပြုလုပ်သည့်အခါ သင်ဖြည့်သွင်းသော username, email နှင့် password (hashed) တို့ကို ကျွန်ုပ်တို့ စုဆောင်းပါသည်။ ဤအချက်အလက်များကို သင်၏ account ကို စီမံခန့်ခွဲရန်နှင့် သင့်အား ဝန်ဆောင်မှုပေးရန်အတွက်သာ အသုံးပြုပါသည်။</li>
                    <li><strong>အသုံးပြုမှုဆိုင်ရာ အချက်အလက်များ:</strong> သင်ကြည့်ရှုခဲ့သော ဇာတ်လမ်းများ (Watch History) နှင့် သင်နှစ်သက်သော ဇာတ်လမ်းများ (My List) ကို သင့်အတွက် အဆင်ပြေစေရန် ကျွန်ုပ်တို့ မှတ်သားထားပါသည်။</li>
                    <li><strong>Cookies:</strong> သင့်ရဲ့ login session ကို ထိန်းသိမ်းထားရန်အတွက်သာ cookies များကို အသုံးပြုပါသည်။</li>
                </ul>

                <h2>အချက်အလက်များကို မည်သို့အသုံးပြုသလဲ</h2>
                <p>ကျွန်ုပ်တို့သည် သင်၏ ကိုယ်ရေးကိုယ်တာအချက်အလက်များကို ပြင်ပအဖွဲ့အစည်းများသို့ ရောင်းချခြင်း၊ ဖြန့်ဝေခြင်း သို့မဟုတ် ငှားရမ်းခြင်းကို မည်သည့်အခါမျှ ပြုလုပ်မည်မဟုတ်ပါ။ သင်၏အချက်အလက်အားလုံးကို လုံခြုံစွာသိမ်းဆည်းထားပြီး ဝန်ဆောင်မှုပေးရန်အတွက်သာ အသုံးပြုပါသည်။</p>

                <h2>ဆက်သွယ်ရန်</h2>
                <p>ဤမူဝါဒနှင့် ပတ်သက်၍ မေးမြန်းလိုသည်များရှိပါက အောက်ပါနေရာများတွင် ဆက်သွယ်နိုင်ပါသည်။</p>
                <ul>
                    <li><strong>Telegram:</strong> @NaJu_New</li>
                    <li><strong>Facebook Page:</strong> m.me/678463315349337</li>
                </ul>
            </div>
        </main>
    </div>
</body>
</html>