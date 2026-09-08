<?php
require_once __DIR__ . '/../PHPMailer/Exception.php';
require_once __DIR__ . '/../PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// --- Fill these in with your own Gmail address + App Password ---
const SMTP_USERNAME = 'hinsclips@gmail.com';
const SMTP_PASSWORD = 'ytwf cgow efwr sbdu';
const SMTP_FROM_NAME = 'Hinlo Airsoft Zone';

function generate_otp(): string
{
    return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
}

function send_otp_email(string $toEmail, string $toName, string $otp): bool
{
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom(SMTP_USERNAME, SMTP_FROM_NAME);
        $mail->addAddress($toEmail, $toName);

        $mail->isHTML(true);
        $mail->Subject = 'Your Hinlo Airsoft Zone verification code';
        $mail->Body    = "<p>Hi " . htmlspecialchars($toName) . ",</p>"
            . "<p>Your verification code is:</p>"
            . "<h2 style='letter-spacing:4px;'>{$otp}</h2>"
            . "<p>This code expires in 10 minutes.</p>";
        $mail->AltBody = "Your verification code is: {$otp} (expires in 10 minutes)";

        $mail->send();
        return true;
    } 
    
    
    
    
    
    
    
    catch (Exception $e) {
        error_log('OTP email failed: ' . $mail->ErrorInfo);
        return false;
    }
}

function send_order_confirmation_email(
    string $toEmail,
    string $toName,
    string $productName,
    int $quantity,
    string $location,
    string $contactNumber,
    int $orderId
): bool {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom(SMTP_USERNAME, SMTP_FROM_NAME);
        $mail->addAddress($toEmail, $toName);

        $mail->isHTML(true);
        $mail->Subject = "Order Confirmed — Hinlo Airsoft Zone (#$orderId)";
        $mail->Body    = "<p>Hi " . htmlspecialchars($toName) . ",</p>"
            . "<p>Thanks for your order! Here's a summary:</p>"
            . "<table cellpadding='6' style='border-collapse:collapse;'>"
            . "<tr><td><strong>Order #</strong></td><td>{$orderId}</td></tr>"
            . "<tr><td><strong>Product</strong></td><td>" . htmlspecialchars($productName) . "</td></tr>"
            . "<tr><td><strong>Quantity</strong></td><td>{$quantity}</td></tr>"
            . "<tr><td><strong>Location</strong></td><td>" . htmlspecialchars($location) . "</td></tr>"
            . "<tr><td><strong>Contact number</strong></td><td>" . htmlspecialchars($contactNumber) . "</td></tr>"
            . "<tr><td><strong>Status</strong></td><td>Pending</td></tr>"
            . "</table>"
            . "<p>We'll reach out to arrange payment and pickup/delivery. You can check your order status anytime by logging in and visiting My Orders.</p>";
        $mail->AltBody = "Order #{$orderId} confirmed: {$quantity}x {$productName} to {$location}, contact {$contactNumber}. Status: Pending.";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('Order confirmation email failed: ' . $mail->ErrorInfo);
        return false;
    }
}
function send_order_completed_email(
    string $toEmail,
    string $toName,
    string $productName,
    int $quantity,
    int $orderId
): bool {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom(SMTP_USERNAME, SMTP_FROM_NAME);
        $mail->addAddress($toEmail, $toName);

        $mail->isHTML(true);
        $mail->Subject = "Order Completed — Hinlo Airsoft Zone (#$orderId)";
        $mail->Body    = "<p>Hi " . htmlspecialchars($toName) . ",</p>"
            . "<p>Your order #{$orderId} ({$quantity}x " . htmlspecialchars($productName) . ") has been marked as <strong>completed</strong>.</p>"
            . "<p>Thanks for ordering from Hinlo Airsoft Zone!</p>";
        $mail->AltBody = "Your order #{$orderId} ({$quantity}x {$productName}) has been marked as completed. Thanks for ordering!";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('Order completed email failed: ' . $mail->ErrorInfo);
        return false;
    }
}