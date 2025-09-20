<?php
require __DIR__ . '/sendgrid-php/sendgrid-php.php';

// ----- အရေးကြီး: သင်၏ API KEY အသစ်ကို ဤနေရာတွင် ကူးထည့်ပါ -----
$apiKey = getenv('SENDGRID_API_KEY'); 

$email = new \SendGrid\Mail\Mail();
$email->setFrom("no-reply@najuanime.online", "SendGrid Test Script");
$email->setSubject("Testing SendGrid API Key from Diagnostic Script");

// ----- အရေးကြီး: သင် လက်ခံရရှိနိုင်သော သင်၏ ကိုယ်ပိုင် Email ကိုပြောင်းထည့်ပါ -----
$email->addTo("najuanimevipweb@gmail.com", "Test User"); 
$email->addContent("text/plain", "This is a test email from the diagnostic script.");
$email->addContent("text/html", "<strong>This is a test email from the diagnostic script. If you receive this, the API key is working.</strong>");

$sendgrid = new \SendGrid($apiKey);

echo "Attempting to send email...<br><hr>";

try {
    $response = $sendgrid->send($email);
    echo "<h2>SUCCESS!</h2>";
    echo "Email sent! Here is the response from SendGrid:<br>";
    echo "Status Code: " . $response->statusCode() . "<br>";
    echo "Headers: <br><pre>";
    print_r($response->headers());
    echo "</pre>";
    echo "Body: <br><pre>";
    print_r($response->body());
    echo "</pre>";
} catch (Exception $e) {
    echo "<h2>ERROR!</h2>";
    echo 'Caught exception: ' . $e->getMessage() . "<br>";
    echo 'Status Code: ' . $e->getCode() . "<br>";
}
?>
