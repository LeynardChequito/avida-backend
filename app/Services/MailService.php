<?php
namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class MailService
{
    public static function sendEmail($to, $subject, $body)
    {
        Log::info("Attempting to send email to: " . $to); // ✅ Log email attempt

        $mail = new PHPMailer(true);

        try {
            // SMTP Configuration
            $mail->isSMTP();
            $mail->Host       = env('MAIL_HOST', 'smtp.gmail.com');
            $mail->SMTPAuth   = true;
            $mail->Username   = env('MAIL_USERNAME');
            $mail->Password   = env('MAIL_PASSWORD');
            $mail->SMTPSecure = env('MAIL_ENCRYPTION', PHPMailer::ENCRYPTION_STARTTLS);
            $mail->Port       = env('MAIL_PORT', 587);

            // Sender and recipient
            $mail->setFrom(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
            $mail->addAddress($to);

            // Email content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;

            // ✅ Send Email
            if (!$mail->send()) {
                Log::error("Email sending failed: " . $mail->ErrorInfo);
                return false;
            }

            Log::info("Email successfully sent to: " . $to);
            return true;
        } catch (Exception $e) {
            Log::error("PHPMailer Exception: " . $e->getMessage());
            return false;
        }
    }
}
