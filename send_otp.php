<?php
// send_otp.php
// Manual include for PHPMailer

require_once 'PHPMailer-master/src/Exception.php';
require_once 'PHPMailer-master/src/PHPMailer.php';
require_once 'PHPMailer-master/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

function sendOtpEmail($recipientEmail, $otp) {
    $mail = new PHPMailer(true);
    
    try {
        // Disable debug for production
        $mail->SMTPDebug = SMTP::DEBUG_OFF;
        
        // Server settings - Using SSL on port 465 (more reliable)
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'omhirapara111@gmail.com';
        $mail->Password   = 'mnfraoifrfqcxcax';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // SSL encryption
        $mail->Port       = 465; // SSL port
        
        // SSL options to fix certificate issues
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        
        // Timeout setting
        $mail->Timeout = 30;
        
        // Recipients
        $mail->setFrom('omhirapara111@gmail.com', 'E-Doc System');
        $mail->addAddress($recipientEmail);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset OTP - E-Doc System';
        
        // Email body
        $mail->Body = '
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px; }
                .header { background-color: #2c3e50; color: white; padding: 10px; text-align: center; border-radius: 5px 5px 0 0; }
                .content { padding: 20px; }
                .otp-code { font-size: 32px; font-weight: bold; color: #2c3e50; text-align: center; letter-spacing: 5px; margin: 20px 0; }
                .footer { text-align: center; font-size: 12px; color: #777; margin-top: 20px; padding-top: 10px; border-top: 1px solid #ddd; }
                .warning { color: #e74c3c; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h2>E-Doc System</h2>
                </div>
                <div class="content">
                    <p>Hello,</p>
                    <p>We received a request to reset your password for your E-Doc System account.</p>
                    <p>Use the following OTP (One-Time Password) to reset your password:</p>
                    <div class="otp-code">' . $otp . '</div>
                    <p>This OTP is valid for <strong>5 minutes</strong>.</p>
                    <p>If you did not request a password reset, please ignore this email or contact support if you have concerns.</p>
                    <p class="warning">Never share this OTP with anyone. Our support team will never ask for your OTP.</p>
                </div>
                <div class="footer">
                    <p>&copy; ' . date('Y') . ' E-Doc System. All rights reserved.</p>
                    <p>This is an automated message, please do not reply to this email.</p>
                </div>
            </div>
        </body>
        </html>
        ';
        
        $mail->AltBody = 'Your OTP for password reset is: ' . $otp . '. This OTP is valid for 5 minutes.';
        
        $mail->send();
        return true;
        
    } catch (Exception $e) {
        // Log the error
        error_log("Mailer Error: " . $mail->ErrorInfo);
        return false;
    }
}
?>