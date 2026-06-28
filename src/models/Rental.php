<?php
require_once __DIR__ . '/../core/Paginator.php';
class Rental {
    private $conn;
    private $table_name = "rentals";

    public $id;
    public $user_id;
    public $property_id;
    public $start_date;
    public $end_date;
    public $monthly_rent;
    public $status;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function readAll() {
        $query = "SELECT r.*, p.title as property_title, p.location, p.main_image, p.property_type,
                  u.name as user_name, u.email as user_email
                  FROM " . $this->table_name . " r
                  LEFT JOIN properties p ON r.property_id = p.id
                  LEFT JOIN users u ON r.user_id = u.id
                  ORDER BY r.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function readByUser() {
        $query = "SELECT r.*, p.title as property_title, p.description, p.location, p.main_image,
                  p.property_type, p.bedrooms, p.bathrooms, p.area_sqft
                  FROM " . $this->table_name . " r
                  LEFT JOIN properties p ON r.property_id = p.id
                  WHERE r.user_id = ?
                  ORDER BY r.status ASC, r.end_date DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->user_id);
        $stmt->execute();
        return $stmt;
    }

    public function readOne() {
        $query = "SELECT r.*, p.title as property_title, p.location, p.main_image, p.property_type
                  FROM " . $this->table_name . " r
                  LEFT JOIN properties p ON r.property_id = p.id
                  WHERE r.id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        return $stmt;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " SET
            user_id=:user_id, property_id=:property_id, start_date=:start_date,
            end_date=:end_date, monthly_rent=:monthly_rent, status='active'";
        $stmt = $this->conn->prepare($query);
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $this->property_id = htmlspecialchars(strip_tags($this->property_id));
        $this->start_date = htmlspecialchars(strip_tags($this->start_date));
        $this->end_date = htmlspecialchars(strip_tags($this->end_date));
        $this->monthly_rent = htmlspecialchars(strip_tags($this->monthly_rent));
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":property_id", $this->property_id);
        $stmt->bindParam(":start_date", $this->start_date);
        $stmt->bindParam(":end_date", $this->end_date);
        $stmt->bindParam(":monthly_rent", $this->monthly_rent);
        if($stmt->execute()) {
            $pdo = $this->conn;
            $pdo->prepare("UPDATE properties SET is_available = 0 WHERE id = ?")->execute([$this->property_id]);
            return true;
        }
        return false;
    }

    public function terminate() {
        $query = "UPDATE " . $this->table_name . " SET status='terminated', end_date=CURDATE() WHERE id=? AND status='active'";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        if($stmt->execute() && $stmt->rowCount() > 0) {
            $row = $this->readOne()->fetch(PDO::FETCH_ASSOC);
            if($row && !empty($row['property_id'])) {
                $this->conn->prepare("UPDATE properties SET is_available = 1 WHERE id = ?")->execute([$row['property_id']]);
            }
            return true;
        }
        return false;
    }

    public function readAllPaginated($params = []) {
        $p = Paginator::build(array_merge($params, ['allowed_sorts' => ['created_at', 'start_date', 'end_date', 'monthly_rent', 'status'], 'sort' => $params['sort'] ?? 'start_date']));

        $joins = "LEFT JOIN properties p ON r.property_id = p.id
                  LEFT JOIN users u ON r.user_id = u.id";
        $fromTable = $this->table_name . " r";
        $where = [];

        if ($p['search']) {
            $searchClause = Paginator::searchClause(['p.title', 'u.name', 'r.status'], $p['search']);
            if ($searchClause) $where[] = $searchClause;
        }

        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        if ($p['sort'] === 'status') {
            $orderClause = "ORDER BY FIELD(r.status, 'active', 'terminated', 'expired') {$p['order']}";
        } else {
            $orderClause = "ORDER BY r.{$p['sort']} {$p['order']}";
        }

        $limitClause = "LIMIT {$p['perPage']} OFFSET {$p['offset']}";

        $countSql = "SELECT COUNT(*) FROM $fromTable $joins $whereClause";
        $countStmt = $this->conn->prepare($countSql);
        Paginator::bindSearch($countStmt, $p['search']);
        $countStmt->execute();

        $dataSql = "SELECT r.*, p.title as property_title, p.location, p.main_image, p.property_type,
                    u.name as user_name, u.email as user_email
                    FROM $fromTable $joins $whereClause $orderClause $limitClause";
        $dataStmt = $this->conn->prepare($dataSql);
        Paginator::bindSearch($dataStmt, $p['search']);
        $dataStmt->execute();

        return Paginator::paginatedResponse($dataStmt, $countStmt, $p['page'], $p['perPage']);
    }
}
