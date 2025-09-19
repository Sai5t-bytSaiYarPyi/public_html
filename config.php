<?php
// config.php (Updated for SendGrid API)

// ** EMAIL CONFIGURATION (SendGrid API) **
// --- START: YOUR SENDGRID API KEY ---
// SendGrid မှာရလာတဲ့ သင့်ရဲ့ API Key အရှည်ကြီးကို ဒီနေရာမှာ ကူးထည့်ပါ။
define('SENDGRID_API_KEY', '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'); 
// --- END: YOUR SENDGRID API KEY ---


// The name and email address that will appear as the sender
// SendGrid မှာ Verify လုပ်ထားတဲ့ သင့်ရဲ့ email လိပ်စာကိုထည့်ပါ။
define('MAIL_FROM_ADDRESS', 'noreply@najuianime.online'); 
define('MAIL_FROM_NAME', 'Aether Stream Notifier');

define('ADMIN_EMAIL', 'najuanimevipweb@gmail.com');


// --- OLD HOSTINGER SMTP (No longer needed for Forgot Password) ---
/*
define('SMTP_HOST', 'smtp.hostinger.com');
define('SMTP_USERNAME', 'naju_anime@najuanime.wuaze.com');
define('SMTP_PASSWORD', 'SaiYarPyi@2007'); 
define('SMTP_PORT', 465);
define('SMTP_SECURE', 'ssl');
*/

?>