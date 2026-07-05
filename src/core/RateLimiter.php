<?php
class RateLimiter {
    private static $db = null;
    private static $table = 'rate_limits';

    private static $sensitiveEndpoints = [
        '/api/auth.php' => [10, 60],
        '/api/password-reset.php' => [5, 300],
        '/api/inquiry.php' => [5, 60],
    ];

    private static function getDB() {
        if (self::$db === null) {
            include_once __DIR__ . '/../config/database.php';
            $database = new Database();
            self::$db = $database->getConnection();
            self::ensureTable();
        }
        return self::$db;
    }

    private static function ensureTable() {
        self::$db->exec("CREATE TABLE IF NOT EXISTS " . self::$table . " (
            id BIGSERIAL PRIMARY KEY,
            identifier VARCHAR(255) NOT NULL,
            endpoint VARCHAR(100) NOT NULL,
            hits INT DEFAULT 1,
            window_start TIMESTAMP NOT NULL
        )");
    }

    public static function check($endpoint, $maxRequests = 60, $windowSeconds = 60) {
        $db = self::getDB();
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $userId = isset($_SESSION) && session_id() ? ($_SESSION['user_id'] ?? 'anon') : 'anon';
        $identifier = $userId . ':' . $ip;

        // Apply endpoint-specific limits if configured
        if (isset(self::$sensitiveEndpoints[$endpoint])) {
            $maxRequests = self::$sensitiveEndpoints[$endpoint][0];
            $windowSeconds = self::$sensitiveEndpoints[$endpoint][1];
        }

        $window = date('Y-m-d H:i:s', time() - $windowSeconds);

        $stmt = $db->prepare("SELECT SUM(hits) as total FROM " . self::$table . "
            WHERE identifier = ? AND endpoint = ? AND window_start > ?");
        $stmt->execute([$identifier, $endpoint, $window]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row && $row['total'] >= $maxRequests) {
            $retryAfter = $windowSeconds;
            header('Retry-After: ' . $retryAfter);
            http_response_code(429);
            echo json_encode([
                'success' => false,
                'message' => "Rate limit exceeded. Max $maxRequests requests per " . ($windowSeconds > 60 ? ($windowSeconds/60) . ' minutes' : "$windowSeconds seconds") . '.',
                'retry_after' => $retryAfter
            ]);
            exit;
        }

        $stmt = $db->prepare("INSERT INTO " . self::$table . " (identifier, endpoint, window_start) VALUES (?, ?, NOW())");
        $stmt->execute([$identifier, $endpoint]);

        if (rand(1, 10) === 1) {
            self::cleanup();
        }
    }

    private static function cleanup() {
        $db = self::$db;
        $db->exec("DELETE FROM " . self::$table . " WHERE window_start < DATE_SUB(NOW(), INTERVAL 1 HOUR)");
    }

    public static function checkLoginAttempts($email) {
        $db = self::getDB();
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $window = date('Y-m-d H:i:s', time() - 900);

        $stmt = $db->prepare("SELECT COUNT(*) as total FROM " . self::$table . "
            WHERE identifier LIKE ? AND endpoint = '/api/auth.php' AND window_start > ?");
        $stmt->execute(["%:$ip", $window]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row && $row['total'] >= 5) {
            return false;
        }

        $stmt2 = $db->prepare("SELECT COUNT(*) as total FROM " . self::$table . "
            WHERE identifier LIKE ? AND endpoint = '/api/auth.php' AND window_start > ?");
        $stmt2->execute(["%:" . md5($email), $window]);
        $row2 = $stmt2->fetch(PDO::FETCH_ASSOC);

        if ($row2 && $row2['total'] >= 10) {
            return false;
        }

        return true;
    }
}
