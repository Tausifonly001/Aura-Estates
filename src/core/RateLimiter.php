<?php
class RateLimiter {
    private static $db = null;
    private static $table = 'rate_limits';

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
            id BIGINT AUTO_INCREMENT PRIMARY KEY,
            identifier VARCHAR(255) NOT NULL,
            endpoint VARCHAR(100) NOT NULL,
            hits INT UNSIGNED DEFAULT 1,
            window_start DATETIME NOT NULL,
            INDEX idx_lookup (identifier, endpoint, window_start),
            INDEX idx_cleanup (window_start)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }

    public static function check($endpoint, $maxRequests = 60, $windowSeconds = 60) {
        $db = self::getDB();
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $userId = isset($_SESSION) && session_id() ? ($_SESSION['user_id'] ?? 'anon') : 'anon';
        $identifier = $userId . ':' . $ip;
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

        self::cleanup();
    }

    private static function cleanup() {
        $db = self::$db;
        $db->exec("DELETE FROM " . self::$table . " WHERE window_start < DATE_SUB(NOW(), INTERVAL 1 HOUR)");
    }
}
