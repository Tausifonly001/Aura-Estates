<?php
require_once __DIR__ . '/../core/Paginator.php';
class Property {
    private $conn;
    private $table_name = "properties";

    public $id;
    public $title;
    public $description;
    public $price;
    public $location;
    public $latitude;
    public $longitude;
    public $property_type;
    public $bedrooms;
    public $bathrooms;
    public $area_sqft;
    public $main_image;
    public $is_available;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Read all properties
    public function read() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Read single property
    public function readOne() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $this->title = $row['title'];
            $this->description = $row['description'];
            $this->price = $row['price'];
            $this->location = $row['location'];
            $this->latitude = $row['latitude'];
            $this->longitude = $row['longitude'];
            $this->property_type = $row['property_type'];
            $this->bedrooms = $row['bedrooms'];
            $this->bathrooms = $row['bathrooms'];
            $this->area_sqft = $row['area_sqft'];
            $this->main_image = $row['main_image'];
        }
        return $stmt;
    }

    // Create property
    public function create() {
        // Validate numeric fields
        if(!is_numeric($this->price) || !is_numeric($this->bedrooms) || !is_numeric($this->bathrooms) || !is_numeric($this->area_sqft)) {
            return false;
        }

        $query = "INSERT INTO " . $this->table_name . " (title, description, price, location, latitude, longitude, property_type, bedrooms, bathrooms, area_sqft, main_image, image, is_available, status) VALUES (:title, :description, :price, :location, :latitude, :longitude, :property_type, :bedrooms, :bathrooms, :area_sqft, :main_image, :main_image, :is_available, 'available')";
        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->title=htmlspecialchars(strip_tags((string)$this->title));
        $this->description=htmlspecialchars(strip_tags((string)$this->description));
        $this->price=htmlspecialchars(strip_tags((string)$this->price));
        $this->location=htmlspecialchars(strip_tags((string)$this->location));
        $this->latitude=htmlspecialchars(strip_tags((string)($this->latitude ?? '')));
        $this->longitude=htmlspecialchars(strip_tags((string)($this->longitude ?? '')));
        $this->property_type=htmlspecialchars(strip_tags((string)$this->property_type));
        $this->bedrooms=htmlspecialchars(strip_tags((string)$this->bedrooms));
        $this->bathrooms=htmlspecialchars(strip_tags((string)$this->bathrooms));
        $this->area_sqft=htmlspecialchars(strip_tags((string)$this->area_sqft));
        $this->main_image=htmlspecialchars(strip_tags((string)$this->main_image));
        $this->is_available=htmlspecialchars(strip_tags((string)($this->is_available ?? 1)));

        // Bind
        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":price", $this->price);
        $stmt->bindParam(":location", $this->location);
        $stmt->bindParam(":latitude", $this->latitude);
        $stmt->bindParam(":longitude", $this->longitude);
        $stmt->bindParam(":property_type", $this->property_type);
        $stmt->bindParam(":bedrooms", $this->bedrooms);
        $stmt->bindParam(":bathrooms", $this->bathrooms);
        $stmt->bindParam(":area_sqft", $this->area_sqft);
        $stmt->bindParam(":main_image", $this->main_image);
        $stmt->bindParam(":is_available", $this->is_available);

        try {
            return $stmt->execute();
        } catch (Throwable $e) {
            error_log("Property create error: " . $e->getMessage());
            return false;
        }
    }

    // Update property
    public function update() {
        // Validate numeric fields
        if(!is_numeric($this->price) || !is_numeric($this->bedrooms) || !is_numeric($this->bathrooms) || !is_numeric($this->area_sqft)) {
            return false;
        }

        $query = "UPDATE " . $this->table_name . " SET
            title=:title, description=:description, price=:price, location=:location, 
            latitude=:latitude, longitude=:longitude,
            property_type=:property_type, bedrooms=:bedrooms, bathrooms=:bathrooms, 
            area_sqft=:area_sqft, main_image=:main_image, is_available=:is_available
            WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->title=htmlspecialchars(strip_tags($this->title));
        $this->description=htmlspecialchars(strip_tags($this->description));
        $this->price=htmlspecialchars(strip_tags($this->price));
        $this->location=htmlspecialchars(strip_tags($this->location));
        $this->latitude=htmlspecialchars(strip_tags($this->latitude ?? ''));
        $this->longitude=htmlspecialchars(strip_tags($this->longitude ?? ''));
        $this->property_type=htmlspecialchars(strip_tags($this->property_type));
        $this->bedrooms=htmlspecialchars(strip_tags($this->bedrooms));
        $this->bathrooms=htmlspecialchars(strip_tags($this->bathrooms));
        $this->area_sqft=htmlspecialchars(strip_tags($this->area_sqft));
        $this->main_image=htmlspecialchars(strip_tags($this->main_image));
        $this->is_available=htmlspecialchars(strip_tags($this->is_available ?? 0));
        $this->id=htmlspecialchars(strip_tags($this->id));

        // Bind
        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":price", $this->price);
        $stmt->bindParam(":location", $this->location);
        $stmt->bindParam(":latitude", $this->latitude);
        $stmt->bindParam(":longitude", $this->longitude);
        $stmt->bindParam(":property_type", $this->property_type);
        $stmt->bindParam(":bedrooms", $this->bedrooms);
        $stmt->bindParam(":bathrooms", $this->bathrooms);
        $stmt->bindParam(":area_sqft", $this->area_sqft);
        $stmt->bindParam(":main_image", $this->main_image);
        $stmt->bindParam(":is_available", $this->is_available);
        $stmt->bindParam(":id", $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Delete property
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $this->id=htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(1, $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function readPaginated($params = []) {
        $p = Paginator::build(array_merge($params, ['allowed_sorts' => ['created_at', 'title', 'price', 'bedrooms', 'bathrooms', 'area_sqft', 'property_type']]));

        $baseQuery = "FROM " . $this->table_name;
        $where = [];
        $binds = [];

        if ($p['search']) {
            $where[] = "(title LIKE :search OR description LIKE :search OR location LIKE :search)";
            $binds[':search'] = '%' . $p['search'] . '%';
        }

        foreach ($p['filter'] as $key => $value) {
            if ($value === '' || $value === null) continue;
            $allowed = ['property_type', 'bedrooms', 'bathrooms', 'location', 'is_available'];
            if (!in_array($key, $allowed)) continue;
            $param = ":filter_$key";
            if ($key === 'bedrooms' || $key === 'bathrooms' || $key === 'is_available') {
                $where[] = "$key = $param";
            } else {
                $where[] = "$key LIKE $param";
                $value = "%$value%";
            }
            $binds[$param] = $value;
        }

        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $orderClause = "ORDER BY {$p['sort']} {$p['order']}";
        $limitClause = "LIMIT {$p['perPage']} OFFSET {$p['offset']}";

        $countSql = "SELECT COUNT(*) $baseQuery $whereClause";
        $countStmt = $this->conn->prepare($countSql);
        foreach ($binds as $param => $value) $countStmt->bindValue($param, $value);
        $countStmt->execute();

        $dataSql = "SELECT * $baseQuery $whereClause $orderClause $limitClause";
        $dataStmt = $this->conn->prepare($dataSql);
        foreach ($binds as $param => $value) $dataStmt->bindValue($param, $value);
        $dataStmt->execute();

        return Paginator::paginatedResponse($dataStmt, $countStmt, $p['page'], $p['perPage']);
    }
}
?>
