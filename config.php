<?php
// Email Configuration for PHPMailer using Gmail SMTP
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_USERNAME', 'najuanimevipweb@gmail.com');
define('SMTP_PASSWORD', 'wlrz hfnu ciwk yozz');
define('SMTP_PORT', 587); 
define('SMTP_SECURE', 'tls');

// The name and email address that will appear as the sender
define('MAIL_FROM_ADDRESS', 'najuanimevipweb@gmail.com'); // It's better to use the same as SMTP_USERNAME
define('MAIL_FROM_NAME', 'Aether Stream Notifier');

// --- NEW: Your Admin Email Address ---
// All payment notifications will be sent to this email.
define('ADMIN_EMAIL', 'najuanimevipweb@gmail.com'); // <-- ဒီနေရာမှာ သင့်ရဲ့ ကိုယ်ပိုင် email လိပ်စာကိုထည့်ပါ။

// --- Telegram Bot Configuration (No longer used but can be kept) ---
define('TELEGRAM_BOT_TOKEN', 'YOUR_BOT_TOKEN_HERE'); 
define('TELEGRAM_CHAT_ID', 'YOUR_CHAT_ID_HERE');      
?>