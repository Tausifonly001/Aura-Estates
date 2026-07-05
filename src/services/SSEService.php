<?php
require_once __DIR__ . '/../core/Middleware.php';
require_once __DIR__ . '/../config/database.php';

class SSEService {
    private static $maxConnectionTime = 300;
    private static $maxConnectionsPerUser = 3;

    public static function stream($userId = null) {
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        header('X-Accel-Buffering: no');

        if (!$userId) {
            http_response_code(401);
            echo "event: error\ndata: {\"message\":\"Authentication required\"}\n\n";
            return;
        }

        $db = (new Database())->getConnection();

        // Enforce connection limit
        $stmt = $db->prepare("SELECT COUNT(*) FROM sse_connections WHERE user_id = ? AND connected_at > DATE_SUB(NOW(), INTERVAL 5 MINUTE)");
        $stmt->execute([$userId]);
        $activeConnections = (int)$stmt->fetchColumn();
        if ($activeConnections >= self::$maxConnectionsPerUser) {
            http_response_code(429);
            echo "event: error\ndata: {\"message\":\"Too many concurrent connections. Limit: " . self::$maxConnectionsPerUser . "\"}\n\n";
            ob_flush(); flush();
            return;
        }

        // Determine role
        $stmt = $db->prepare("SELECT role FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $isAdmin = $stmt->fetchColumn() === 'admin';

        // Register this connection
        $db->prepare("CREATE TABLE IF NOT EXISTS sse_connections (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            connected_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_user_time (user_id, connected_at)
        )")->execute();
        $connStmt = $db->prepare("INSERT INTO sse_connections (user_id) VALUES (?)");
        $connStmt->execute([$userId]);
        $connId = $db->lastInsertId();

        $startTime = time();
        $lastCheck = time();

        while (true) {
            if (connection_aborted()) break;
            if ((time() - $startTime) > self::$maxConnectionTime) {
                echo "event: timeout\ndata: {\"message\":\"Connection timeout\"}\n\n";
                ob_flush(); flush();
                break;
            }

            $events = [];
            if ($isAdmin) {
                $stmt = $db->prepare("SELECT COUNT(*) FROM maintenance_requests WHERE created_at >= FROM_UNIXTIME(?)");
                $stmt->execute([$lastCheck]);
                $newMaint = (int)$stmt->fetchColumn();
                if ($newMaint) $events[] = ['type' => 'new_maintenance', 'count' => $newMaint];

                $stmt2 = $db->prepare("SELECT COUNT(*) FROM amenity_bookings WHERE created_at >= FROM_UNIXTIME(?)");
                $stmt2->execute([$lastCheck]);
                $newBookings = (int)$stmt2->fetchColumn();
                if ($newBookings) $events[] = ['type' => 'new_booking', 'count' => $newBookings];
            } else {
                $stmt = $db->prepare("SELECT COUNT(*) FROM maintenance_requests WHERE created_at >= FROM_UNIXTIME(?) AND (assigned_to = ? OR user_id = ?)");
                $stmt->execute([$lastCheck, $userId, $userId]);
                $newMaint = (int)$stmt->fetchColumn();
                if ($newMaint) $events[] = ['type' => 'new_maintenance', 'count' => $newMaint];

                $stmt2 = $db->prepare("SELECT COUNT(*) FROM amenity_bookings WHERE created_at >= FROM_UNIXTIME(?) AND user_id = ?");
                $stmt2->execute([$lastCheck, $userId]);
                $newBookings = (int)$stmt2->fetchColumn();
                if ($newBookings) $events[] = ['type' => 'new_booking', 'count' => $newBookings];
            }

            if (!empty($events)) {
                echo "data: " . json_encode($events) . "\n\n";
                ob_flush(); flush();
            } else {
                echo ": heartbeat\n\n";
                ob_flush(); flush();
            }
            $lastCheck = time();
            sleep(5);
        }

        // Cleanup this connection
        $db->prepare("DELETE FROM sse_connections WHERE id = ?")->execute([$connId]);
    }
}
