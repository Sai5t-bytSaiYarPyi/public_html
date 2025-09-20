<?php
session_start();
require 'language_loader.php';
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['lang']; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submission Received - Aether Stream</title>
    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="stylesheet" href="/style.css?v=1.9">
</head>
<body>
    <div class="login-container" style="text-align: center;">
        <div class="logo">Aether Stream</div>
        <h2>Submission Received!</h2>
        <div class="message success">
            <p>သင်၏ ငွေလွှဲပြေစာအား ကျွန်ုပ်တို့ လက်ခံရရှိပါသည်။</p>
        </div>
        <p style="color: #ccc; line-height: 1.7;">Admin မှ သင်၏ ငွေပေးချေမှုကို စစ်ဆေးပြီးနောက် (အများဆုံး ၂၄ နာရီအတွင်း) သင်၏ Account အတွက် VIP access ကို ဖွင့်ပေးပါမည်။</p>
        <p style="color: #ccc; margin-top: 15px;">အရေးပေါ်ဆက်သွယ်ရန်၊ သို့မဟုတ် အချိန်ကြာမြင့်နေပါက ကျွန်ုပ်တို့၏ <a href="https://t.me/NaJu_New" target="_blank" style="color: #007AFF;">Telegram Support</a> သို့ ဆက်သွယ်ပါ။</p>
        
        <a href="/home" class="btn btn-primary" style="margin-top: 30px;">ပင်မစာမျက်နှာသို့ ပြန်သွားရန်</a>
    </div>
</body>
</html>