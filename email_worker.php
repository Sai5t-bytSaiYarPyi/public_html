<?php
// email_worker.php (New File)

// This script should be run by a cron job, e.g., every minute.

// Ignore user aborts and allow the script to run forever
ignore_user_abort(true);
set_time_limit(0);

require 'PHPMailer-6.9.1/src/Exception.php';
require 'PHPMailer-6.9.1/src/PHPMailer.php';
require 'PHPMailer-6.9.1/src/SMTP.php';
require 'config.php';
require 'db_connect.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Fetch pending emails from the queue (limit to 10 per run to avoid timeout)
$stmt = $pdo->prepare("SELECT * FROM email_queue WHERE status = 'pending' LIMIT 10");
$stmt->execute();
$emails_to_send = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($emails_to_send) === 0) {
    echo "No pending emails to send.\n";
    exit;
}

$mail = new PHPMailer(true);

foreach ($emails_to_send as $email_job) {
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = SMTP_SECURE;
        $mail->Port       = SMTP_PORT;

        // Recipients
        $mail->setFrom(MAIL_FROM_ADDRESS, MAIL_FROM_NAME);
        $mail->addAddress($email_job['recipient_email']);

        // Content
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = $email_job['subject'];
        $mail->Body    = $email_job['body'];
        $mail->AltBody = $email_job['alt_body'];

        $mail->send();

        // Update status to 'sent'
        $update_stmt = $pdo->prepare("UPDATE email_queue SET status = 'sent', sent_at = NOW() WHERE id = ?");
        $update_stmt->execute([$email_job['id']]);

        echo "Email to " . $email_job['recipient_email'] . " sent successfully.\n";

    } catch (Exception $e) {
        // Update status to 'failed'
        $update_stmt = $pdo->prepare("UPDATE email_queue SET status = 'failed' WHERE id = ?");
        $update_stmt->execute([$email_job['id']]);
        
        echo "Failed to send email to " . $email_job['recipient_email'] . ". Error: " . $mail->ErrorInfo . "\n";
    }

    // Clear addresses for the next loop
    $mail->clearAddresses();
    $mail->clearAttachments();
}