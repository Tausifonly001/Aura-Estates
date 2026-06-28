<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use Razorpay\Api\Api;

class PaymentService {
    private static $api = null;
    private static $keyId = '';
    private static $keySecret = '';

    private static function init() {
        if (self::$api !== null) return;
        $env = parse_ini_file(__DIR__ . '/../../.env') ?: [];
        self::$keyId = getenv('RAZORPAY_KEY_ID') ?: ($env['RAZORPAY_KEY_ID'] ?? '');
        self::$keySecret = getenv('RAZORPAY_KEY_SECRET') ?: ($env['RAZORPAY_KEY_SECRET'] ?? '');
        if (empty(self::$keyId) || empty(self::$keySecret)) {
            throw new RuntimeException('Razorpay keys not configured. Set RAZORPAY_KEY_ID and RAZORPAY_KEY_SECRET in .env');
        }
        self::$api = new Api(self::$keyId, self::$keySecret);
    }

    public static function createOrder($amount, $currency = 'INR', $receipt = null, $notes = []) {
        self::init();
        $order = self::$api->order->create([
            'amount' => $amount * 100,
            'currency' => $currency,
            'receipt' => $receipt ?? uniqid('rcpt_'),
            'notes' => $notes,
        ]);
        return [
            'id' => $order['id'],
            'amount' => $order['amount'],
            'currency' => $order['currency'],
            'receipt' => $order['receipt'],
            'status' => $order['status'],
        ];
    }

    public static function verifyPayment($orderId, $paymentId, $signature) {
        self::init();
        $attributes = [
            'razorpay_order_id' => $orderId,
            'razorpay_payment_id' => $paymentId,
            'razorpay_signature' => $signature,
        ];
        try {
            self::$api->utility->verifyPaymentSignature($attributes);
            return true;
        } catch (\Exception $e) {
            error_log("Razorpay signature verification failed: " . $e->getMessage());
            return false;
        }
    }

    public static function fetchPayment($paymentId) {
        self::init();
        return self::$api->payment->fetch($paymentId)->toArray();
    }

    public static function getKeyId() {
        self::init();
        return self::$keyId;
    }
}
