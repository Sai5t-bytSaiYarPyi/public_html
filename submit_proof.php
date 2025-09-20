<?php
session_start();
require 'language_loader.php';
require 'db_connect.php';

if (!isset($_SESSION['is_logged_in'])) {
    header('Location: /');
    exit();
}
$username = $_SESSION['username'];
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['lang']; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Payment Proof - Aether Stream</title>
    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="stylesheet" href="/style.css?v=1.9">
    <style>
        .form-container { max-width: 600px; margin: 20px auto; padding: 30px; background-color: #1a1a1a; border-radius: 12px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-weight: bold; margin-bottom: 8px; color: #ccc; }
        .form-group input { width: 100%; padding: 12px; background-color: #333; border: 1px solid #555; border-radius: 5px; color: #fff; }
        .form-group input[type="file"] { padding: 10px; }
    </style>
</head>
<body>
    <div class="form-container">
        <h2 style="text-align:center; margin-bottom: 20px;">ငွေလွှဲပြေစာ တင်ရန်</h2>
        <p style="color: #ccc; margin-bottom: 20px; text-align: center;">ကျေးဇူးပြု၍ သင်၏ ငွေလွှဲပြေစာ Screenshot နှင့် အချက်အလက်များကို မှန်ကန်စွာဖြည့်စွက်ပေးပါ။</p>

        <form action="/handle_submission" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="username">သင်၏ Aether Stream Username</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" readonly>
            </div>
            <div class="form-group">
                <label for="phone">ငွေလွှဲခဲ့သော ဖုန်းနံပါတ်</label>
                <input type="text" id="phone" name="phone" placeholder="e.g., 09xxxxxxxxx" required>
            </div>
            <div class="form-group">
                <label for="receipt">ငွေလွှဲပြေစာ Screenshot</label>
                <input type="file" id="receipt" name="receipt" accept="image/png, image/jpeg, image/jpg" required>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px; font-size: 16px;">Submit</button>
            </div>
        </form>
         <div style="text-align:center; margin-top: 20px;">
            <a href="/purchase_vip">&larr; Back to Instructions</a>
        </div>
    </div>
</body>
</html>