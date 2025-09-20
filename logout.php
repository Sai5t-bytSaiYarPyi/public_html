<?php
// Session ကို စတင်ပါ။
session_start();

// Session ထဲက data အားလုံးကို ရှင်းလင်းပါ။
session_unset();

// Session ကို လုံးဝ ဖျက်သိမ်းပါ။
session_destroy();

// အသုံးပြုသူကို ပင်မစာမျက်နှာ (Login Page) ဆီသို့ ပြန်ပို့ပါ။
header("Location: /"); 
exit;
?>

