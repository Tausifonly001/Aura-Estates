<?php
class AuditLogger {
    private static $db = null;
    private static $table = 'audit_logs';

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
            user_id INT DEFAULT NULL,
            user_name VARCHAR(100) DEFAULT NULL,
            action VARCHAR(50) NOT NULL,
            entity_type VARCHAR(50) NOT NULL,
            entity_id VARCHAR(50) DEFAULT NULL,
            description TEXT DEFAULT NULL,
            old_values JSON DEFAULT NULL,
            new_values JSON DEFAULT NULL,
            ip_address VARCHAR(45) DEFAULT NULL,
            user_agent VARCHAR(500) DEFAULT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_user (user_id),
            INDEX idx_action (action),
            INDEX idx_entity (entity_type, entity_id),
            INDEX idx_created (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }

    public static function log($action, $entityType, $entityId = null, $description = null, $oldValues = null, $newValues = null) {
        $db = self::getDB();
        Auth::startSession();
        $stmt = $db->prepare("INSERT INTO " . self::$table . "
            (user_id, user_name, action, entity_type, entity_id, description, old_values, new_values, ip_address, user_agent)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $_SESSION['user_id'] ?? null,
            $_SESSION['user_name'] ?? 'System',
            $action,
            $entityType,
            $entityId,
            $description,
            $oldValues ? json_encode($oldValues) : null,
            $newValues ? json_encode($newValues) : null,
            $_SERVER['REMOTE_ADDR'] ?? null,
            substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500)
        ]);
    }

    public static function search($filters = [], $page = 1, $perPage = 50) {
        $db = self::getDB();
        $where = [];
        $params = [];
        if (!empty($filters['user_id'])) { $where[] = 'user_id = ?'; $params[] = $filters['user_id']; }
        if (!empty($filters['action'])) { $where[] = 'action = ?'; $params[] = $filters['action']; }
        if (!empty($filters['entity_type'])) { $where[] = 'entity_type = ?'; $params[] = $filters['entity_type']; }
        if (!empty($filters['entity_id'])) { $where[] = 'entity_id = ?'; $params[] = $filters['entity_id']; }
        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $offset = ($page - 1) * $perPage;
        $total = $db->prepare("SELECT COUNT(*) FROM " . self::$table . " $whereClause");
        $total->execute($params);
        $stmt = $db->prepare("SELECT * FROM " . self::$table . " $whereClause ORDER BY created_at DESC LIMIT $perPage OFFSET $offset");
        $stmt->execute($params);
        return ['records' => $stmt->fetchAll(PDO::FETCH_ASSOC), 'total' => $total->fetchColumn()];
    }
}
