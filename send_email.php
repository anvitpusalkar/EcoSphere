<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; 

function sendOrderConfirmationEmail($to, $name, $orderSummary, $total) {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = '';
        $mail->Password = ''; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Recipients
        $mail->setFrom('noreply@ecosphere.in', 'EcoSphere');
        $mail->addAddress($to, $name);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Order Confirmation - EcoSphere';
        $mail->Body    = "
            <h2>Thank you for your order, {$name}!</h2>
            <h3>Order Summary:</h3>
            {$orderSummary}
            <h3>Total: ₹{$total}</h3>
            <p>We'll process your order shortly and send you a shipping confirmation.</p>
        ";

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}
?>