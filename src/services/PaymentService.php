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

    private static function getDb() {
        require_once __DIR__ . '/../config/database.php';
        return (new Database())->getConnection();
    }

    private static function ensureTable() {
        $db = self::getDb();
        $db->exec("CREATE TABLE IF NOT EXISTS payment_orders (
            id SERIAL PRIMARY KEY,
            razorpay_order_id VARCHAR(100) NOT NULL UNIQUE,
            user_id INT NOT NULL,
            amount INT NOT NULL,
            currency VARCHAR(3) NOT NULL DEFAULT 'INR',
            purpose VARCHAR(255) DEFAULT NULL,
            reference_type VARCHAR(50) DEFAULT NULL,
            reference_id INT DEFAULT NULL,
            status VARCHAR(20) DEFAULT 'created',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
    }

    public static function createOrder($amount, $currency = 'INR', $receipt = null, $notes = []) {
        self::init();
        self::ensureTable();

        $userId = $notes['user_id'] ?? null;
        $purpose = $notes['purpose'] ?? 'general';

        $order = self::$api->order->create([
            'amount' => $amount * 100,
            'currency' => $currency,
            'receipt' => $receipt ?? uniqid('rcpt_'),
            'notes' => $notes,
        ]);

        $db = self::getDb();
        $stmt = $db->prepare("INSERT INTO payment_orders (razorpay_order_id, user_id, amount, currency, purpose, status) VALUES (?, ?, ?, ?, ?, 'created')");
        $stmt->execute([$order['id'], $userId, $amount * 100, $currency, $purpose]);

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
        } catch (\Exception $e) {
            error_log("Razorpay signature verification failed: " . $e->getMessage());
            return false;
        }

        $db = self::getDb();
        $stmt = $db->prepare("SELECT * FROM payment_orders WHERE razorpay_order_id = ?");
        $stmt->execute([$orderId]);
        $orderRecord = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$orderRecord) {
            error_log("Payment order not found in local DB: $orderId");
            return false;
        }

        if ($orderRecord['status'] === 'paid') {
            error_log("Payment order already marked paid (replay detected): $orderId");
            return false;
        }

        try {
            $payment = self::$api->payment->fetch($paymentId)->toArray();
        } catch (\Exception $e) {
            error_log("Failed to fetch payment details from Razorpay: " . $e->getMessage());
            return false;
        }

        if (!isset($payment['status']) || $payment['status'] !== 'captured') {
            error_log("Payment not captured: {$payment['status']}");
            return false;
        }

        if ((int)$payment['amount'] !== (int)$orderRecord['amount']) {
            error_log("Payment amount mismatch: expected {$orderRecord['amount']}, got {$payment['amount']}");
            return false;
        }

        if ($payment['currency'] !== $orderRecord['currency']) {
            error_log("Payment currency mismatch: expected {$orderRecord['currency']}, got {$payment['currency']}");
            return false;
        }

        $updateStmt = $db->prepare("UPDATE payment_orders SET status = 'paid' WHERE razorpay_order_id = ? AND status = 'created'");
        $updateStmt->execute([$orderId]);
        if ($updateStmt->rowCount() === 0) {
            error_log("Race condition or replay: order $orderId already processed");
            return false;
        }

        return true;
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
