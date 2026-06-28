<?php
require_once __DIR__ . '/../core/Paginator.php';
class Amenity {
    private $conn;
    private $table_name = "amenities";

    public $id;
    public $property_id;
    public $name;
    public $description;
    public $capacity;
    public $location;
    public $image;
    public $is_active;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function read() {
        $query = "SELECT a.*, p.title as property_title
                  FROM " . $this->table_name . " a
                  LEFT JOIN properties p ON a.property_id = p.id
                  ORDER BY a.name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function readActive() {
        $query = "SELECT a.*, p.title as property_title
                  FROM " . $this->table_name . " a
                  LEFT JOIN properties p ON a.property_id = p.id
                  WHERE a.is_active = 1
                  ORDER BY a.name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function readOne() {
        $query = "SELECT a.*, p.title as property_title
                  FROM " . $this->table_name . " a
                  LEFT JOIN properties p ON a.property_id = p.id
                  WHERE a.id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        return $stmt;
    }

    public function readByProperty() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE property_id = ? AND is_active = 1 ORDER BY name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->property_id);
        $stmt->execute();
        return $stmt;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " SET
            property_id=:property_id, name=:name, description=:description,
            capacity=:capacity, location=:location, image=:image, is_active=1";

        $stmt = $this->conn->prepare($query);

        $this->property_id = htmlspecialchars(strip_tags($this->property_id));
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->capacity = htmlspecialchars(strip_tags($this->capacity));
        $this->location = htmlspecialchars(strip_tags($this->location));
        $this->image = htmlspecialchars(strip_tags($this->image));

        $stmt->bindParam(":property_id", $this->property_id);
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":capacity", $this->capacity);
        $stmt->bindParam(":location", $this->location);
        $stmt->bindParam(":image", $this->image);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . " SET
            property_id=:property_id, name=:name, description=:description,
            capacity=:capacity, location=:location, image=:image, is_active=:is_active
            WHERE id=:id";

        $stmt = $this->conn->prepare($query);

        $this->property_id = htmlspecialchars(strip_tags($this->property_id));
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->capacity = htmlspecialchars(strip_tags($this->capacity));
        $this->location = htmlspecialchars(strip_tags($this->location));
        $this->image = htmlspecialchars(strip_tags($this->image));
        $this->is_active = htmlspecialchars(strip_tags($this->is_active));
        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(":property_id", $this->property_id);
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":capacity", $this->capacity);
        $stmt->bindParam(":location", $this->location);
        $stmt->bindParam(":image", $this->image);
        $stmt->bindParam(":is_active", $this->is_active);
        $stmt->bindParam(":id", $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
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
            (SELECT COUNT(*) FROM " . $this->table_name . " WHERE is_active = 1) as active_count,
            (SELECT COUNT(*) FROM " . $this->table_name . ") as total_count";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function readPaginated($params = []) {
        $p = Paginator::build(array_merge($params, ['allowed_sorts' => ['created_at', 'name', 'capacity', 'location']]));

        $joins = "LEFT JOIN properties p ON a.property_id = p.id";
        $fromTable = $this->table_name . " a";
        $where = [];

        if ($p['search']) {
            $searchClause = Paginator::searchClause(['a.name', 'a.description', 'a.location', 'p.title'], $p['search']);
            if ($searchClause) $where[] = $searchClause;
        }

        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $orderClause = "ORDER BY a.{$p['sort']} {$p['order']}";
        $limitClause = "LIMIT {$p['perPage']} OFFSET {$p['offset']}";

        $countSql = "SELECT COUNT(*) FROM $fromTable $joins $whereClause";
        $countStmt = $this->conn->prepare($countSql);
        Paginator::bindSearch($countStmt, $p['search']);
        $countStmt->execute();

        $dataSql = "SELECT a.*, p.title as property_title FROM $fromTable $joins $whereClause $orderClause $limitClause";
        $dataStmt = $this->conn->prepare($dataSql);
        Paginator::bindSearch($dataStmt, $p['search']);
        $dataStmt->execute();

        return Paginator::paginatedResponse($dataStmt, $countStmt, $p['page'], $p['perPage']);
    }
}
