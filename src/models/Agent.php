<?php
require_once __DIR__ . '/../core/Paginator.php';
class Agent {
    private $conn;
    private $table_name = "agents";

    public $id;
    public $name;
    public $title;
    public $bio;
    public $image_url;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function read() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function readOne() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        return $stmt;
    }

    public function readPaginated($params = []) {
        $p = Paginator::build(array_merge($params, ['allowed_sorts' => ['created_at', 'name']]));

        $baseQuery = "FROM " . $this->table_name;
        $where = [];

        if ($p['search']) {
            $searchClause = Paginator::searchClause(['name', 'title', 'bio'], $p['search']);
            if ($searchClause) $where[] = $searchClause;
        }

        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $orderClause = "ORDER BY {$p['sort']} {$p['order']}";
        $limitClause = "LIMIT {$p['perPage']} OFFSET {$p['offset']}";

        $countSql = "SELECT COUNT(*) $baseQuery $whereClause";
        $countStmt = $this->conn->prepare($countSql);
        Paginator::bindSearch($countStmt, $p['search']);
        $countStmt->execute();

        $dataSql = "SELECT * $baseQuery $whereClause $orderClause $limitClause";
        $dataStmt = $this->conn->prepare($dataSql);
        Paginator::bindSearch($dataStmt, $p['search']);
        $dataStmt->execute();

        return Paginator::paginatedResponse($dataStmt, $countStmt, $p['page'], $p['perPage']);
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " (name, title, bio, image_url) VALUES (:name, :title, :bio, :image_url)";
        $stmt = $this->conn->prepare($query);

        $this->name = htmlspecialchars(strip_tags((string)$this->name));
        $this->title = htmlspecialchars(strip_tags((string)$this->title));
        $this->bio = htmlspecialchars(strip_tags((string)$this->bio));
        $this->image_url = htmlspecialchars(strip_tags((string)$this->image_url));

        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":bio", $this->bio);
        $stmt->bindParam(":image_url", $this->image_url);

        try {
            return $stmt->execute();
        } catch (Throwable $e) {
            error_log("Agent create error: " . $e->getMessage());
            return false;
        }
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . " SET
            name=:name, title=:title, bio=:bio, image_url=:image_url
            WHERE id=:id";

        $stmt = $this->conn->prepare($query);

        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->bio = htmlspecialchars(strip_tags($this->bio));
        $this->image_url = htmlspecialchars(strip_tags($this->image_url));
        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":bio", $this->bio);
        $stmt->bindParam(":image_url", $this->image_url);
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
}
