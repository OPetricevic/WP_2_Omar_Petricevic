<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../../vendor/autoload.php';

function sendPasswordResetEmail($email, $resetToken) {
    $mail = new PHPMailer(true);

    try {
        // Update the path to the .env file
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
        $dotenv->load();

        // Debugging logs for environment variables
        error_log("DEBUG: SMTP_EMAIL is " . $_ENV['SMTP_EMAIL']);
        error_log("DEBUG: SMTP_PASSWORD is " . $_ENV['SMTP_PASSWORD']);

        // SMTP configuration
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = $_ENV['SMTP_EMAIL']; 
        $mail->Password = $_ENV['SMTP_PASSWORD'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Email content
        $mail->setFrom($_ENV['SMTP_EMAIL'], 'WP_2_OMAR_PETRICEVIC');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset Request';
        $mail->Body = "
        <p>We received a password reset request for your account.</p>
        <p>If you made this request, click the link below to reset your password:</p>
        <a href='http://localhost:8000/auth/reset-password?token=$resetToken'>Reset Password</a>
        <p>If you did not request this, you can safely ignore this email.</p>
    ";    

        $mail->send();
        error_log("Password reset email sent to $email.");
    } catch (Exception $e) {
        error_log("Email sending failed: " . $e->getMessage());
        throw new Exception("Could not send email.");
    }
}
