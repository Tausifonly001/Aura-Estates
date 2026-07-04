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

        $startTime = time();
        $db = (new Database())->getConnection();
        $lastCheck = time();

        while (true) {
            if (connection_aborted()) break;
            if ((time() - $startTime) > self::$maxConnectionTime) {
                echo "event: timeout\ndata: {\"message\":\"Connection timeout\"}\n\n";
                ob_flush(); flush();
                break;
            }

            $events = [];
            if ($userId) {
                $stmt = $db->prepare("SELECT COUNT(*) FROM maintenance_requests WHERE created_at >= FROM_UNIXTIME(?) AND (assigned_to = ? OR ? = 1)");
                $adminStmt = $db->prepare("SELECT role FROM users WHERE id = ?");
                $adminStmt->execute([$userId]);
                $adminCheck = $adminStmt->fetchColumn();
                $isAdmin = $adminCheck === 'admin';
                $stmt->execute([$lastCheck, $userId, $isAdmin ? 1 : 0]);
                $newMaint = (int)$stmt->fetchColumn();
                if ($newMaint) $events[] = ['type' => 'new_maintenance', 'count' => $newMaint];

                $stmt2 = $db->prepare("SELECT COUNT(*) FROM amenity_bookings WHERE created_at >= FROM_UNIXTIME(?)");
                $stmt2->execute([$lastCheck]);
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
    }
}
