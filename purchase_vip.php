<?php
session_start();
require 'language_loader.php';
require 'db_connect.php';

if (!isset($_SESSION['is_logged_in'])) {
    header('Location: /');
    exit();
}
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['lang']; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase VIP - Aether Stream</title>
    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="stylesheet" href="/style.css?v=1.9">
    <style>
        .purchase-container { max-width: 800px; margin: 20px auto; padding: 30px; background-color: #1a1a1a; border-radius: 12px; }
        .instructions { color: #ccc; line-height: 1.8; }
        .payment-info { margin-top: 30px; padding-top: 20px; border-top: 1px solid #333; }
        .payment-method { margin-bottom: 20px; }
        .payment-method h3 { color: #007AFF; margin-bottom: 10px; }
        .payment-method p { font-size: 16px; }
        .payment-method p strong { color: #fff; font-size: 18px; }
        .qr-code { max-width: 150px; margin-top: 10px; border-radius: 8px; }
        .next-step-btn { display: block; width: 100%; text-align: center; margin-top: 30px; font-size: 18px; padding: 15px; }
    </style>
</head>
<body>
    <div class="main-container">
        <aside class="sidebar">
            </aside>
        <main class="main-content">
            <header class="main-header">
                 <h2>Become a VIP Member</h2>
            </header>
            <div class="purchase-container">
                <h2 style="text-align:center; margin-bottom: 20px;">VIP အစီအစဉ်များနှင့် ငွေပေးချေမှု လမ်းညွှန်</h2>
                <div class="instructions">
                    <p>၁။ အောက်တွင်ဖော်ပြထားသော VIP အစီအစဉ်များထဲမှ မိမိနှစ်သက်ရာတစ်ခုကို ရွေးချယ်ပါ။</p>
                    <p>၂။ KBZPay သို့မဟုတ် WavePay ဖြင့် သတ်မှတ်ထားသော ဖုန်းနံပါတ်သို့ ငွေလွှဲပါ။</p>
                    <p>၃။ ငွေလွှဲပြီးပါက ငွေလွှဲပြေစာ (screenshot) ကို သေချာသိမ်းဆည်းထားပါ။</p>
                    <p>၄။ အောက်ဆုံးရှိ "ငွေလွှဲပြီးပါပြီ၊ ပြေစာတင်ရန်" ခလုတ်ကိုနှိပ်၍ နောက်တစ်ဆင့်သို့ သွားပါ။</p>
                </div>

                <div class="payment-info">
                    <div class="payment-method">
                        <h3>KBZPay</h3>
                        <p>Phone Number: <strong>09885697152</strong></p>
                        <p>Account Name: <strong>Ma Su Su Latt</strong></p>
                        <img src="/kbz_qr.jpg" alt="KBZPay QR Code" class="qr-code">
                    </div>
                    <div class="payment-method">
                        <h3>WavePay</h3>
                        <p>Phone Number: <strong>09684324433</strong></p>
                        <p>Account Name: <strong>Kyar Paw</strong></p>
                        <img src="/wave_qr.jpg" alt="WavePay QR Code" class="qr-code">
                    </div>
                </div>

                <a href="/submit_proof" class="btn btn-primary next-step-btn">ငွေလွှဲပြီးပါပြီ၊ ပြေစာတင်ရန်</a>
            </div>
             <div style="text-align:center; margin-top: 20px;">
                <a href="/home">&larr; Back to Home</a>
            </div>
        </main>
    </div>
</body>
</html>