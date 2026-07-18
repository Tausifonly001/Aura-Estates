<?php
require_once __DIR__ . '/../core/Paginator.php';
class MaintenanceRequest {
    private $conn;
    private $table_name = "maintenance_requests";

    public $id;
    public $user_id;
    public $subject;
    public $property_id;
    public $tenant_name;
    public $tenant_email;
    public $issue_description;
    public $priority;
    public $status;
    public $assigned_to;
    public $assigned_name;
    public $created_at;
    public $resolved_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function createTable() {
        try {
            $this->conn->exec("ALTER TABLE " . $this->table_name . " ADD COLUMN IF NOT EXISTS user_id INT DEFAULT NULL AFTER id");
            $this->conn->exec("ALTER TABLE " . $this->table_name . " ADD COLUMN IF NOT EXISTS subject VARCHAR(255) DEFAULT NULL AFTER tenant_email");
        } catch(PDOException $e) {}
    }

    public function read() {
        $this->createTable();
        $query = "SELECT m.*, p.title as property_title, p.location as property_location,
                  u.name as assigned_name
                  FROM " . $this->table_name . " m
                  LEFT JOIN properties p ON m.property_id = p.id
                  LEFT JOIN users u ON m.assigned_to = u.id
                  ORDER BY 
                    FIELD(m.status, 'pending', 'in_progress', 'completed'),
                    FIELD(m.priority, 'urgent', 'high', 'medium', 'low'),
                    m.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function readOne() {
        $query = "SELECT m.*, p.title as property_title, u.name as assigned_name
                  FROM " . $this->table_name . " m
                  LEFT JOIN properties p ON m.property_id = p.id
                  LEFT JOIN users u ON m.assigned_to = u.id
                  WHERE m.id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        return $stmt;
    }

    public function readByProperty() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE property_id = ? ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->property_id);
        $stmt->execute();
        return $stmt;
    }

    public function readByEmail() {
        $query = "SELECT m.*, p.title as property_title, u.name as assigned_name
                  FROM " . $this->table_name . " m
                  LEFT JOIN properties p ON m.property_id = p.id
                  LEFT JOIN users u ON m.assigned_to = u.id
                  WHERE m.tenant_email = ? ORDER BY m.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->tenant_email);
        $stmt->execute();
        return $stmt;
    }

    public function create() {
        $this->createTable();
        $query = "INSERT INTO " . $this->table_name . " (property_id, tenant_name, tenant_email, issue_description, priority, user_id, subject, status) VALUES (:property_id, :tenant_name, :tenant_email, :issue_description, :priority, :user_id, :subject, 'pending')";
        $stmt = $this->conn->prepare($query);

        $propId = !empty($this->property_id) && is_numeric($this->property_id) && $this->property_id > 0 ? (int)$this->property_id : null;
        if ($propId !== null) {
            try {
                $check = $this->conn->prepare("SELECT id FROM properties WHERE id = ?");
                $check->execute([$propId]);
                if (!$check->fetchColumn()) $propId = null;
            } catch (Throwable $t) { $propId = null; }
        }

        $this->tenant_name = htmlspecialchars(strip_tags((string)$this->tenant_name));
        $this->tenant_email = htmlspecialchars(strip_tags((string)$this->tenant_email));
        $this->issue_description = htmlspecialchars(strip_tags((string)$this->issue_description));
        $this->priority = htmlspecialchars(strip_tags((string)$this->priority));
        $userId = !empty($this->user_id) && is_numeric($this->user_id) ? (int)$this->user_id : null;
        $subject = !empty($this->subject) ? htmlspecialchars(strip_tags((string)$this->subject)) : 'General Maintenance';

        if ($propId === null) {
            $stmt->bindValue(":property_id", null, PDO::PARAM_NULL);
        } else {
            $stmt->bindValue(":property_id", $propId, PDO::PARAM_INT);
        }
        $stmt->bindParam(":tenant_name", $this->tenant_name);
        $stmt->bindParam(":tenant_email", $this->tenant_email);
        $stmt->bindParam(":issue_description", $this->issue_description);
        $stmt->bindParam(":priority", $this->priority);
        if ($userId === null) {
            $stmt->bindValue(":user_id", null, PDO::PARAM_NULL);
        } else {
            $stmt->bindValue(":user_id", $userId, PDO::PARAM_INT);
        }
        $stmt->bindParam(":subject", $subject);

        try {
            if($stmt->execute()) {
                $this->id = $this->conn->lastInsertId();
                return true;
            }
        } catch (Throwable $e) {
            error_log("MaintenanceRequest create error: " . $e->getMessage());
        }
        return false;
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . " SET
            status=:status" . ($this->resolved_at ? ", resolved_at=:resolved_at" : "") . "
            WHERE id=:id";

        $stmt = $this->conn->prepare($query);
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":id", $this->id);
        if($this->resolved_at) {
            $stmt->bindParam(":resolved_at", $this->resolved_at);
        }

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function assign() {
        $query = "UPDATE " . $this->table_name . " SET assigned_to=:assigned_to WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        $this->assigned_to = htmlspecialchars(strip_tags($this->assigned_to));
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(":assigned_to", $this->assigned_to);
        $stmt->bindParam(":id", $this->id);
        return $stmt->execute();
    }

    public function getStaffList() {
        $query = "SELECT u.id, u.name, u.email FROM users u
                  LEFT JOIN roles r ON u.role_id = r.id
                  WHERE r.name IN ('maintenance_staff', 'property_manager', 'admin')
                  ORDER BY u.name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(1, $this->id);
        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function getStats() {
        $query = "SELECT
            (SELECT COUNT(*) FROM " . $this->table_name . " WHERE status = 'pending') as pending_count,
            (SELECT COUNT(*) FROM " . $this->table_name . " WHERE status = 'in_progress') as in_progress_count,
            (SELECT COUNT(*) FROM " . $this->table_name . " WHERE status = 'completed') as completed_count,
            (SELECT COUNT(*) FROM " . $this->table_name . ") as total_count,
            (SELECT ROUND(AVG(TIMESTAMPDIFF(HOUR, created_at, resolved_at)), 1) FROM " . $this->table_name . " WHERE resolved_at IS NOT NULL) as avg_resolution_hours,
            (SELECT ROUND(
                (SELECT COUNT(*) FROM " . $this->table_name . " WHERE status = 'completed') * 100.0 /
                NULLIF((SELECT COUNT(*) FROM " . $this->table_name . " WHERE status != 'pending'), 0)
            , 1)) as completion_rate,
            (SELECT COUNT(*) FROM " . $this->table_name . " WHERE status = 'pending' AND created_at < DATE_SUB(NOW(), INTERVAL 48 HOUR)) as overdue_count";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function readPaginated($params = []) {
        $explicitSort = isset($params['sort']) ? $params['sort'] : null;
        $p = Paginator::build(array_merge($params, ['allowed_sorts' => ['created_at', 'status', 'priority', 'tenant_name']]));

        $joins = "LEFT JOIN properties p ON m.property_id = p.id
                  LEFT JOIN users u ON m.assigned_to = u.id";
        $fromTable = $this->table_name . " m";
        $where = [];

        if ($p['search']) {
            $searchClause = Paginator::searchClause(['m.tenant_name', 'm.tenant_email', 'm.issue_description', 'p.title'], $p['search']);
            if ($searchClause) $where[] = $searchClause;
        }

        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        if ($p['sort'] === 'status') {
            $orderClause = "ORDER BY FIELD(m.status, 'pending', 'in_progress', 'completed') {$p['order']}, m.created_at DESC";
        } elseif ($p['sort'] === 'priority') {
            $orderClause = "ORDER BY FIELD(m.priority, 'urgent', 'high', 'medium', 'low') {$p['order']}, m.created_at DESC";
        } elseif ($explicitSort === 'created_at') {
            $orderClause = "ORDER BY m.created_at {$p['order']}";
        } elseif ($explicitSort === 'tenant_name') {
            $orderClause = "ORDER BY m.tenant_name {$p['order']}";
        } else {
            $orderClause = "ORDER BY FIELD(m.status, 'pending', 'in_progress', 'completed'), FIELD(m.priority, 'urgent', 'high', 'medium', 'low'), m.created_at DESC";
        }

        $limitClause = "LIMIT {$p['perPage']} OFFSET {$p['offset']}";

        $countSql = "SELECT COUNT(*) FROM $fromTable $joins $whereClause";
        $countStmt = $this->conn->prepare($countSql);
        Paginator::bindSearch($countStmt, $p['search']);
        $countStmt->execute();

        $dataSql = "SELECT m.*, p.title as property_title, p.location as property_location,
                    u.name as assigned_name
                    FROM $fromTable $joins $whereClause $orderClause $limitClause";
        $dataStmt = $this->conn->prepare($dataSql);
        Paginator::bindSearch($dataStmt, $p['search']);
        $dataStmt->execute();

        return Paginator::paginatedResponse($dataStmt, $countStmt, $p['page'], $p['perPage']);
    }
}
