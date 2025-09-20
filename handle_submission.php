<?php
session_start();
require 'config.php';    // For Email and Admin Email configs
require 'db_connect.php'; 

// Import PHPMailer classes into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load PHPMailer files
require 'PHPMailer-6.9.1/src/Exception.php';
require 'PHPMailer-6.9.1/src/PHPMailer.php';
require 'PHPMailer-6.9.1/src/SMTP.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /');
    exit();
}

// --- Create 'uploads' directory if it doesn't exist ---
$upload_dir = __DIR__ . '/uploads/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// --- Handle File Upload ---
if (isset($_FILES['receipt']) && $_FILES['receipt']['error'] == 0) {
    $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
    if (in_array($_FILES['receipt']['type'], $allowed_types)) {
        
        $username = trim($_POST['username']);
        $phone = trim($_POST['phone']);
        
        // Create a unique filename
        $file_extension = pathinfo($_FILES['receipt']['name'], PATHINFO_EXTENSION);
        $new_filename = uniqid('receipt_', true) . '.' . $file_extension;
        $destination = $upload_dir . $new_filename;
        
        if (move_uploaded_file($_FILES['receipt']['tmp_name'], $destination)) {
            // --- Send Email Notification to Admin ---
            $mail = new PHPMailer(true);
            try {
                //Server settings
                $mail->isSMTP();
                $mail->Host       = SMTP_HOST;
                $mail->SMTPAuth   = true;
                $mail->Username   = SMTP_USERNAME;
                $mail->Password   = SMTP_PASSWORD;
                $mail->SMTPSecure = SMTP_SECURE;
                $mail->Port       = SMTP_PORT;

                //Recipients
                $mail->setFrom(MAIL_FROM_ADDRESS, MAIL_FROM_NAME);
                $mail->addAddress(ADMIN_EMAIL); // Send to the admin's email from config.php
                
                //Attachments
                $mail->addAttachment($destination, $new_filename); // Attach the uploaded receipt

                //Content
                $mail->isHTML(true);
                $mail->Subject = 'New VIP Payment Submission: ' . $username;
                $mail->Body    = "<h2>New VIP Payment Submission</h2>
                                  <p>A user has submitted their payment proof for a VIP subscription.</p>
                                  <hr>
                                  <p><strong>Username:</strong> " . htmlspecialchars($username) . "</p>
                                  <p><strong>Phone Number:</strong> " . htmlspecialchars($phone) . "</p>
                                  <hr>
                                  <p>The payment receipt is attached to this email.</p>
                                  <p>Please verify the payment and grant VIP access via the Admin Panel.</p>";
                $mail->AltBody = "New VIP Payment Submission.\n\nUsername: " . $username . "\nPhone: " . $phone . "\n\nPlease check the attached receipt.";

                $mail->send();

            } catch (Exception $e) {
                // Optional: Log the error to a file if email fails.
                // For the user, we proceed as normal.
                // error_log("Mailer Error: " . $mail->ErrorInfo);
            }
            
            // Redirect to the pending page, regardless of email success
            header('Location: /payment_pending');
            exit();
        }
    }
}

// If upload failed or something went wrong, redirect back.
header('Location: /submit_proof?error=1');
exit();
?>