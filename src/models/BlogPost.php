<?php
require_once __DIR__ . '/../core/Paginator.php';
class BlogPost {
    private $conn;
    private $table_name = "blog_posts";

    public $id;
    public $title;
    public $slug;
    public $excerpt;
    public $content;
    public $author;
    public $category;
    public $cover_image;
    public $status;
    public $published_at;
    public $created_at;
    public $updated_at;

    public $statusFilter = null;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function read() {
        $query = "SELECT * FROM " . $this->table_name;
        $where = [];
        if ($this->statusFilter) {
            $where[] = "status = :status_filter";
        }
        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $query .= " $whereClause ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        if ($this->statusFilter) {
            $stmt->bindValue(':status_filter', $this->statusFilter, PDO::PARAM_STR);
        }
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

    public function readBySlug($slug) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE slug = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $slug);
        $stmt->execute();
        return $stmt;
    }

    public function readPaginated($params = []) {
        $p = Paginator::build(array_merge($params, ['allowed_sorts' => ['created_at', 'title', 'category', 'status', 'published_at']]));

        $baseQuery = "FROM " . $this->table_name;
        $where = [];

        if ($p['search']) {
            $searchClause = Paginator::searchClause(['title', 'excerpt', 'content', 'author', 'category'], $p['search']);
            if ($searchClause) $where[] = $searchClause;
        }

        if ($this->statusFilter) {
            $where[] = "status = :status_filter";
        }

        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $orderClause = "ORDER BY {$p['sort']} {$p['order']}";
        $limitClause = "LIMIT {$p['perPage']} OFFSET {$p['offset']}";

        $countSql = "SELECT COUNT(*) $baseQuery $whereClause";
        $countStmt = $this->conn->prepare($countSql);
        Paginator::bindSearch($countStmt, $p['search']);
        if ($this->statusFilter) {
            $countStmt->bindValue(':status_filter', $this->statusFilter, PDO::PARAM_STR);
        }
        $countStmt->execute();

        $dataSql = "SELECT * $baseQuery $whereClause $orderClause $limitClause";
        $dataStmt = $this->conn->prepare($dataSql);
        Paginator::bindSearch($dataStmt, $p['search']);
        if ($this->statusFilter) {
            $dataStmt->bindValue(':status_filter', $this->statusFilter, PDO::PARAM_STR);
        }
        $dataStmt->execute();

        return Paginator::paginatedResponse($dataStmt, $countStmt, $p['page'], $p['perPage']);
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " SET
            title=:title, slug=:slug, excerpt=:excerpt, content=:content,
            author=:author, category=:category, cover_image=:cover_image,
            status=:status, published_at=:published_at";

        $stmt = $this->conn->prepare($query);

        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->slug = htmlspecialchars(strip_tags($this->slug));
        $this->excerpt = htmlspecialchars(strip_tags($this->excerpt));
        $this->content = htmlspecialchars(strip_tags($this->content));
        $this->author = htmlspecialchars(strip_tags($this->author));
        $this->category = htmlspecialchars(strip_tags($this->category));
        $this->cover_image = htmlspecialchars(strip_tags($this->cover_image));
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->published_at = htmlspecialchars(strip_tags($this->published_at));

        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":slug", $this->slug);
        $stmt->bindParam(":excerpt", $this->excerpt);
        $stmt->bindParam(":content", $this->content);
        $stmt->bindParam(":author", $this->author);
        $stmt->bindParam(":category", $this->category);
        $stmt->bindParam(":cover_image", $this->cover_image);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":published_at", $this->published_at);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . " SET
            title=:title, slug=:slug, excerpt=:excerpt, content=:content,
            author=:author, category=:category, cover_image=:cover_image,
            status=:status, published_at=:published_at
            WHERE id=:id";

        $stmt = $this->conn->prepare($query);

        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->slug = htmlspecialchars(strip_tags($this->slug));
        $this->excerpt = htmlspecialchars(strip_tags($this->excerpt));
        $this->content = htmlspecialchars(strip_tags($this->content));
        $this->author = htmlspecialchars(strip_tags($this->author));
        $this->category = htmlspecialchars(strip_tags($this->category));
        $this->cover_image = htmlspecialchars(strip_tags($this->cover_image));
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->published_at = htmlspecialchars(strip_tags($this->published_at));
        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":slug", $this->slug);
        $stmt->bindParam(":excerpt", $this->excerpt);
        $stmt->bindParam(":content", $this->content);
        $stmt->bindParam(":author", $this->author);
        $stmt->bindParam(":category", $this->category);
        $stmt->bindParam(":cover_image", $this->cover_image);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":published_at", $this->published_at);
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
