<?php
require_once __DIR__ . '/../core/Paginator.php';
class User {
    private $conn;
    private $table_name = "users";

    public $id;
    public $name;
    public $email;
    public $password;
    public $role;
    public $role_id;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function login() {
        $query = "SELECT id, name, password, role, role_id FROM " . $this->table_name . " WHERE email = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $this->email=htmlspecialchars(strip_tags($this->email));
        $stmt->bindParam(1, $this->email);
        $stmt->execute();
        return $stmt;
    }

    public function create() {
        $check_query = "SELECT id FROM " . $this->table_name . " WHERE email = ? LIMIT 0,1";
        $check_stmt = $this->conn->prepare($check_query);
        $this->email = htmlspecialchars(strip_tags($this->email));
        $check_stmt->bindParam(1, $this->email);
        $check_stmt->execute();
        
        if($check_stmt->rowCount() > 0){
            return false;
        }

        if (empty($this->role_id)) {
            $roleStmt = $this->conn->prepare("SELECT id FROM roles WHERE name = ?");
            $roleStmt->execute([$this->role]);
            $this->role_id = $roleStmt->fetchColumn();
        }

        $query = "INSERT INTO " . $this->table_name . " (name, email, password, role, role_id) VALUES (:name, :email, :password, :role, :role_id)";
        $stmt = $this->conn->prepare($query);

        $this->name = htmlspecialchars(strip_tags((string)$this->name));
        $this->password = !empty($this->password) ? password_hash($this->password, PASSWORD_BCRYPT) : null;
        $this->role = htmlspecialchars(strip_tags((string)$this->role));

        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":password", $this->password);
        $stmt->bindParam(":role", $this->role);
        $stmt->bindParam(":role_id", $this->role_id);

        try {
            return $stmt->execute();
        } catch (Throwable $e) {
            error_log("User create error: " . $e->getMessage());
            return false;
        }
    }

    public function read() {
        $query = "SELECT u.*, r.display_name as role_display 
                  FROM " . $this->table_name . " u
                  LEFT JOIN roles r ON u.role_id = r.id
                  ORDER BY u.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function updateRole($id, $role, $role_id = null) {
        if ($role_id === null) {
            $rStmt = $this->conn->prepare("SELECT id FROM roles WHERE name = ?");
            $rStmt->execute([$role]);
            $role_id = $rStmt->fetchColumn();
        }
        $query = "UPDATE " . $this->table_name . " SET role = :role, role_id = :role_id WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":role", $role);
        $stmt->bindParam(":role_id", $role_id);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }

    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }

    public function getByRole($role) {
        $query = "SELECT id, name, email, role, created_at FROM " . $this->table_name . " WHERE role = ? ORDER BY name";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$role]);
        return $stmt;
    }

    public function readPaginated($params = []) {
        $p = Paginator::build(array_merge($params, ['allowed_sorts' => ['created_at', 'name', 'email', 'role']]));

        $joins = "LEFT JOIN roles r ON u.role_id = r.id";
        $fromTable = $this->table_name . " u";
        $where = [];

        if ($p['search']) {
            $searchClause = Paginator::searchClause(['u.name', 'u.email', 'u.role', 'r.display_name'], $p['search']);
            if ($searchClause) $where[] = $searchClause;
        }

        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $orderClause = "ORDER BY u.{$p['sort']} {$p['order']}";
        $limitClause = "LIMIT {$p['perPage']} OFFSET {$p['offset']}";

        $countSql = "SELECT COUNT(*) FROM $fromTable $joins $whereClause";
        $countStmt = $this->conn->prepare($countSql);
        Paginator::bindSearch($countStmt, $p['search']);
        $countStmt->execute();

        $dataSql = "SELECT u.*, r.display_name as role_display FROM $fromTable $joins $whereClause $orderClause $limitClause";
        $dataStmt = $this->conn->prepare($dataSql);
        Paginator::bindSearch($dataStmt, $p['search']);
        $dataStmt->execute();

        return Paginator::paginatedResponse($dataStmt, $countStmt, $p['page'], $p['perPage']);
    }
}
