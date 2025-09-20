<?php
// This script should be run by a cron job, not accessed via browser.
if (php_sapi_name() !== 'cli') {
    die("This script can only be run from the command line.");
}

require 'db_connect.php';

// PHPMailer files
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer-6.9.1/src/Exception.php';
require 'PHPMailer-6.9.1/src/PHPMailer.php';
require 'PHPMailer-6.9.1/src/SMTP.php';

// Fetch pending emails from the queue (e.g., 5 at a time to avoid timeout)
$stmt = $pdo->prepare("SELECT * FROM email_queue WHERE status = 'pending' ORDER BY created_at ASC LIMIT 5");
$stmt->execute();
$emails_to_send = $stmt->fetchAll();

if (empty($emails_to_send)) {
    echo "No pending emails to send.\n";
    exit;
}

$mail = new PHPMailer(true);

// --- Your SendGrid Configuration ---
$mail->isSMTP();
$mail->Host       = 'smtp.sendgrid.net';
$mail->SMTPAuth   = true;
$mail->Username   = 'apikey';
$mail->Password   = 'getenv('SENDGRID_API_KEY'); // Use your NEW API Key
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port       = 587;
$mail->CharSet    = 'UTF-8';
$mail->setFrom('no-reply@najuanime.online', 'Aether Stream');
// --- End of Configuration ---

foreach ($emails_to_send as $email_job) {
    try {
        $mail->clearAddresses(); // Clear previous recipient
        $mail->addAddress($email_job['recipient']);
        $mail->Subject = $email_job['subject'];
        $mail->Body    = $email_job['body'];
        $mail->isHTML(true);

        $mail->send();

        // If sent successfully, update status to 'sent'
        $update_stmt = $pdo->prepare("UPDATE email_queue SET status = 'sent', processed_at = NOW() WHERE id = ?");
        $update_stmt->execute([$email_job['id']]);
        echo "Successfully sent email to " . $email_job['recipient'] . "\n";

    } catch (Exception $e) {
        // If failed, update status to 'failed' to avoid retrying indefinitely
        $update_stmt = $pdo->prepare("UPDATE email_queue SET status = 'failed', processed_at = NOW() WHERE id = ?");
        $update_stmt->execute([$email_job['id']]);
        echo "Failed to send email to " . $email_job['recipient'] . ". Error: " . $mail->ErrorInfo . "\n";
    }
}
