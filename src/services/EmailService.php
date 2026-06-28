<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailService {
    private static $config = null;

    private static function getConfig() {
        if (self::$config === null) {
            $env = parse_ini_file(__DIR__ . '/../../.env') ?: [];
            self::$config = [
                'host' => getenv('SMTP_HOST') ?: ($env['SMTP_HOST'] ?? 'smtp.gmail.com'),
                'port' => (int)(getenv('SMTP_PORT') ?: ($env['SMTP_PORT'] ?? 587)),
                'username' => getenv('SMTP_USERNAME') ?: ($env['SMTP_USERNAME'] ?? ''),
                'password' => getenv('SMTP_PASSWORD') ?: ($env['SMTP_PASSWORD'] ?? ''),
                'from_email' => getenv('MAIL_FROM_EMAIL') ?: ($env['MAIL_FROM_EMAIL'] ?? 'noreply@auraestates.com'),
                'from_name' => getenv('MAIL_FROM_NAME') ?: ($env['MAIL_FROM_NAME'] ?? 'Aura Estates'),
                'admin_email' => getenv('ADMIN_EMAIL') ?: ($env['ADMIN_EMAIL'] ?? 'admin@auraestates.com'),
            ];
        }
        return self::$config;
    }

    public static function send($to, $subject, $body, $altBody = '') {
        $config = self::getConfig();
        if (empty($config['username']) || empty($config['password'])) {
            return self::sendNative($to, $subject, $body, $altBody);
        }
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = $config['host'];
            $mail->SMTPAuth = true;
            $mail->Username = $config['username'];
            $mail->Password = $config['password'];
            $mail->SMTPSecure = $config['port'] === 465 ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = $config['port'];
            $mail->CharSet = 'UTF-8';
            $mail->setFrom($config['from_email'], $config['from_name']);
            $mail->addAddress($to);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $body;
            $mail->AltBody = $altBody ?: strip_tags($body);
            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("PHPMailer: " . $mail->ErrorInfo);
            return self::sendNative($to, $subject, $body, $altBody);
        }
    }

    private static function sendNative($to, $subject, $body, $altBody = '') {
        $config = self::getConfig();
        $headers = "MIME-Version: 1.0\r\nContent-Type: text/html; charset=UTF-8\r\n";
        $headers .= "From: " . $config['from_name'] . " <" . $config['from_email'] . ">\r\n";
        return mail($to, $subject, $body, $headers);
    }

    public static function sendContactNotification($name, $email, $message) {
        $config = self::getConfig();
        $subject = "New Contact Inquiry — $name";
        $body = "
        <div style='font-family:sans-serif;max-width:480px;margin:0 auto;padding:24px;background:#faf8f4;border:1px solid #e1ddd4'>
            <div style='font-size:12px;letter-spacing:4px;color:#5c5349;margin-bottom:24px'>AURA ESTATES — ADMIN</div>
            <h2 style='color:#1c1b18;font-size:20px;margin:0 0 16px'>New Contact Inquiry</h2>
            <p style='color:#5c5349;font-size:14px'><strong>Name:</strong> $name</p>
            <p style='color:#5c5349;font-size:14px'><strong>Email:</strong> $email</p>
            <p style='color:#5c5349;font-size:14px'><strong>Message:</strong><br>" . nl2br($message) . "</p>
        </div>";
        return self::send($config['admin_email'], $subject, $body);
    }

    public static function sendInquiryNotification($name, $email, $property, $type) {
        $config = self::getConfig();
        $subject = "New Property Inquiry — $property";
        $body = "
        <div style='font-family:sans-serif;max-width:480px;margin:0 auto;padding:24px;background:#faf8f4;border:1px solid #e1ddd4'>
            <div style='font-size:12px;letter-spacing:4px;color:#5c5349;margin-bottom:24px'>AURA ESTATES — ADMIN</div>
            <h2 style='color:#1c1b18;font-size:20px;margin:0 0 16px'>New $type Inquiry</h2>
            <p style='color:#5c5349;font-size:14px'><strong>Name:</strong> $name</p>
            <p style='color:#5c5349;font-size:14px'><strong>Email:</strong> $email</p>
            <p style='color:#5c5349;font-size:14px'><strong>Property:</strong> $property</p>
        </div>";
        return self::send($config['admin_email'], $subject, $body);
    }

    public static function sendBookingAlert($userName, $userEmail, $amenity, $date, $time) {
        $config = self::getConfig();
        $subject = "New Booking — $amenity by $userName";
        $body = "
        <div style='font-family:sans-serif;max-width:480px;margin:0 auto;padding:24px;background:#faf8f4;border:1px solid #e1ddd4'>
            <div style='font-size:12px;letter-spacing:4px;color:#5c5349;margin-bottom:24px'>AURA ESTATES — ADMIN</div>
            <h2 style='color:#1c1b18;font-size:20px;margin:0 0 16px'>New Amenity Booking</h2>
            <p style='color:#5c5349;font-size:14px'><strong>User:</strong> $userName ($userEmail)</p>
            <p style='color:#5c5349;font-size:14px'><strong>Amenity:</strong> $amenity</p>
            <p style='color:#5c5349;font-size:14px'><strong>Date:</strong> $date</p>
            <p style='color:#5c5349;font-size:14px'><strong>Time:</strong> $time</p>
        </div>";
        return self::send($config['admin_email'], $subject, $body);
    }

    public static function sendMaintenanceAlert($userName, $userEmail, $property, $priority) {
        $config = self::getConfig();
        $subject = "New Maintenance Request — $property ($priority)";
        $body = "
        <div style='font-family:sans-serif;max-width:480px;margin:0 auto;padding:24px;background:#faf8f4;border:1px solid #e1ddd4'>
            <div style='font-size:12px;letter-spacing:4px;color:#5c5349;margin-bottom:24px'>AURA ESTATES — ADMIN</div>
            <h2 style='color:#1c1b18;font-size:20px;margin:0 0 16px'>New Maintenance Request</h2>
            <p style='color:#5c5349;font-size:14px'><strong>User:</strong> $userName ($userEmail)</p>
            <p style='color:#5c5349;font-size:14px'><strong>Property:</strong> $property</p>
            <p style='color:#5c5349;font-size:14px'><strong>Priority:</strong> $priority</p>
        </div>";
        return self::send($config['admin_email'], $subject, $body);
    }
}
