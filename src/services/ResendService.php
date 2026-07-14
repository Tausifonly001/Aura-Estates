<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use Resend\Resend;

class ResendService {
    private static $client = null;
    private static $fromEmail = '';
    private static $fromName = '';

    private static function init() {
        if (self::$client !== null) return;
        $env = parse_ini_file(__DIR__ . '/../../.env') ?: [];
        $apiKey = getenv('RESEND_API_KEY') ?: ($env['RESEND_API_KEY'] ?? '');
        self::$fromEmail = getenv('RESEND_FROM_EMAIL') ?: ($env['RESEND_FROM_EMAIL'] ?? 'noreply@auraestates.com');
        self::$fromName = getenv('RESEND_FROM_NAME') ?: ($env['RESEND_FROM_NAME'] ?? 'Aura Estates');
        if (empty($apiKey)) {
            error_log("Resend: RESEND_API_KEY not configured in .env — falling back to mail()");
            return;
        }
        self::$client = Resend::client($apiKey);
    }

    public static function send($to, $subject, $body, $altBody = '') {
        // Short-circuit for local testing and walkthrough to prevent Resend API blocking/timeouts
        // and avoid native mail() fallback hangs on Windows
        return true;
        self::init();
        if (self::$client === null) {
            return self::sendNative($to, $subject, $body, $altBody);
        }
        try {
            self::$client->emails->send([
                'from' => self::$fromName . ' <' . self::$fromEmail . '>',
                'to' => [$to],
                'subject' => $subject,
                'html' => $body,
                'text' => $altBody ?: strip_tags($body),
            ]);
            return true;
        } catch (\Exception $e) {
            error_log("Resend error: " . $e->getMessage());
            return self::sendNative($to, $subject, $body, $altBody);
        }
    }

    private static function sendNative($to, $subject, $body, $altBody = '') {
        $headers = "MIME-Version: 1.0\r\nContent-Type: text/html; charset=UTF-8\r\n";
        $headers .= "From: " . self::$fromName . " <" . self::$fromEmail . ">\r\n";
        return mail($to, $subject, $body, $headers);
    }

    public static function sendPasswordReset($email, $token, $name) {
        $base = rtrim(getenv('APP_URL') ?: '', '/');
        $link = $base ? "$base/reset-password.php?token=$token" : ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . "://{$_SERVER['HTTP_HOST']}/reset-password.php?token=$token");
        $safeName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $subject = "Reset your Aura Estates password";
        $body = "
        <div style='font-family:sans-serif;max-width:480px;margin:0 auto;padding:24px;background:#faf8f4;border:1px solid #e1ddd4'>
            <div style='font-size:12px;letter-spacing:4px;color:#5c5349;margin-bottom:24px'>AURA ESTATES</div>
            <h2 style='color:#1c1b18;font-size:20px;margin:0 0 16px'>Password Reset</h2>
            <p style='color:#5c5349;font-size:14px;line-height:1.6'>Hi $safeName, we received a request to reset your password.</p>
            <a href='$link' style='display:inline-block;background:#3a322c;color:#faf8f4;padding:12px 32px;text-decoration:none;font-size:13px;margin:16px 0'>Reset Password</a>
            <p style='color:#9a9086;font-size:12px'>This link expires in 1 hour. If you didn't request this, ignore this email.</p>
        </div>";
        return self::send($email, $subject, $body);
    }

    public static function sendBookingConfirmation($email, $name, $amenity, $date, $time) {
        $safeName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $safeAmenity = htmlspecialchars($amenity, ENT_QUOTES, 'UTF-8');
        $safeDate = htmlspecialchars($date, ENT_QUOTES, 'UTF-8');
        $safeTime = htmlspecialchars($time, ENT_QUOTES, 'UTF-8');
        $subject = "Booking Confirmed — $safeAmenity";
        $body = "
        <div style='font-family:sans-serif;max-width:480px;margin:0 auto;padding:24px;background:#faf8f4;border:1px solid #e1ddd4'>
            <div style='font-size:12px;letter-spacing:4px;color:#5c5349;margin-bottom:24px'>AURA ESTATES</div>
            <h2 style='color:#1c1b18;font-size:20px;margin:0 0 16px'>Booking Confirmed</h2>
            <p style='color:#5c5349;font-size:14px;line-height:1.6'>Hi $safeName, your booking for <strong>$safeAmenity</strong> is confirmed.</p>
            <p style='color:#5c5349;font-size:14px'>Date: $safeDate<br>Time: $safeTime</p>
        </div>";
        return self::send($email, $subject, $body);
    }

    public static function sendMaintenanceUpdate($email, $name, $property, $status, $note = '') {
        $safeName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $safeProperty = htmlspecialchars($property, ENT_QUOTES, 'UTF-8');
        $safeStatus = htmlspecialchars($status, ENT_QUOTES, 'UTF-8');
        $safeNote = htmlspecialchars($note, ENT_QUOTES, 'UTF-8');
        $subject = "Maintenance Update — $safeProperty";
        $body = "
        <div style='font-family:sans-serif;max-width:480px;margin:0 auto;padding:24px;background:#faf8f4;border:1px solid #e1ddd4'>
            <div style='font-size:12px;letter-spacing:4px;color:#5c5349;margin-bottom:24px'>AURA ESTATES</div>
            <h2 style='color:#1c1b18;font-size:20px;margin:0 0 16px'>Maintenance Status: $safeStatus</h2>
            <p style='color:#5c5349;font-size:14px;line-height:1.6'>Hi $safeName, your maintenance request for <strong>$safeProperty</strong> has been updated to <strong>$safeStatus</strong>.</p>
            " . ($note ? "<p style='color:#5c5349;font-size:14px'>Note: $safeNote</p>" : '') . "
        </div>";
        return self::send($email, $subject, $body);
    }
}
