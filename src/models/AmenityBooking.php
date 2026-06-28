<?php
require_once __DIR__ . '/../core/Paginator.php';
class AmenityBooking {
    private $conn;
    private $table_name = "amenity_bookings";

    public $id;
    public $amenity_id;
    public $user_id;
    public $guest_name;
    public $booking_date;
    public $check_in_time;
    public $check_out_time;
    public $status;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function read() {
        $query = "SELECT b.*, a.name as amenity_name, a.location as amenity_location,
                  p.title as property_title
                  FROM " . $this->table_name . " b
                  LEFT JOIN amenities a ON b.amenity_id = a.id
                  LEFT JOIN properties p ON a.property_id = p.id
                  ORDER BY b.booking_date DESC, b.check_in_time DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function readByAmenityAndDate() {
        $query = "SELECT * FROM " . $this->table_name . "
                  WHERE amenity_id = ? AND booking_date = ?
                  AND status IN ('confirmed', 'checked_in')
                  ORDER BY check_in_time ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->amenity_id);
        $stmt->bindParam(2, $this->booking_date);
        $stmt->execute();
        return $stmt;
    }

    public function readByUser() {
        $query = "SELECT b.*, a.name as amenity_name, a.location as amenity_location,
                  a.capacity, p.title as property_title
                  FROM " . $this->table_name . " b
                  LEFT JOIN amenities a ON b.amenity_id = a.id
                  LEFT JOIN properties p ON a.property_id = p.id
                  WHERE b.user_id = ?
                  ORDER BY b.booking_date DESC, b.check_in_time DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->user_id);
        $stmt->execute();
        return $stmt;
    }

    public function readOne() {
        $query = "SELECT b.*, a.name as amenity_name, a.location as amenity_location,
                  p.title as property_title
                  FROM " . $this->table_name . " b
                  LEFT JOIN amenities a ON b.amenity_id = a.id
                  LEFT JOIN properties p ON a.property_id = p.id
                  WHERE b.id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        return $stmt;
    }

    public function checkConflict() {
        $query = "SELECT COUNT(*) as conflict_count FROM " . $this->table_name . "
                  WHERE amenity_id = :amenity_id
                  AND booking_date = :booking_date
                  AND status IN ('confirmed', 'checked_in')
                  AND (
                      (check_in_time < :check_out_time AND check_out_time > :check_in_time)
                  )";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":amenity_id", $this->amenity_id);
        $stmt->bindParam(":booking_date", $this->booking_date);
        $stmt->bindParam(":check_in_time", $this->check_in_time);
        $stmt->bindParam(":check_out_time", $this->check_out_time);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['conflict_count'];
    }

    public function checkCapacity() {
        $query = "SELECT a.capacity FROM amenities a WHERE a.id = :amenity_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":amenity_id", $this->amenity_id);
        $stmt->execute();
        $amenity = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$amenity) return 0;

        $booked = "SELECT COUNT(*) as booked FROM " . $this->table_name . "
                   WHERE amenity_id = :amenity_id2
                   AND booking_date = :booking_date
                   AND status IN ('confirmed', 'checked_in')
                   AND (
                       (check_in_time < :check_out_time AND check_out_time > :check_in_time)
                   )";
        $stmt2 = $this->conn->prepare($booked);
        $stmt2->bindParam(":amenity_id2", $this->amenity_id);
        $stmt2->bindParam(":booking_date", $this->booking_date);
        $stmt2->bindParam(":check_in_time", $this->check_in_time);
        $stmt2->bindParam(":check_out_time", $this->check_out_time);
        $stmt2->execute();
        $row = $stmt2->fetch(PDO::FETCH_ASSOC);

        return $amenity['capacity'] - $row['booked'];
    }

    public function checkDateAvailability($amenity_id, $date) {
        $query = "SELECT a.capacity FROM amenities a WHERE a.id = :amenity_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":amenity_id", $amenity_id);
        $stmt->execute();
        $amenity = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$amenity) return array("available" => 0, "capacity" => 0);

        $booked = "SELECT COUNT(*) as booked FROM " . $this->table_name . "
                   WHERE amenity_id = :amenity_id2
                   AND booking_date = :booking_date
                   AND status IN ('confirmed', 'checked_in')";
        $stmt2 = $this->conn->prepare($booked);
        $stmt2->bindParam(":amenity_id2", $amenity_id);
        $stmt2->bindParam(":booking_date", $date);
        $stmt2->execute();
        $row = $stmt2->fetch(PDO::FETCH_ASSOC);

        $available = $amenity['capacity'] - $row['booked'];
        if ($available < 0) $available = 0;
        return array("available" => $available, "capacity" => $amenity['capacity']);
    }

    public function create() {
        if ($this->checkConflict() > 0) {
            return "conflict";
        }
        if ($this->checkCapacity() <= 0) {
            return "capacity";
        }

        $query = "INSERT INTO " . $this->table_name . " SET
            amenity_id=:amenity_id, user_id=:user_id, guest_name=:guest_name,
            booking_date=:booking_date, check_in_time=:check_in_time,
            check_out_time=:check_out_time, status='confirmed'";

        $stmt = $this->conn->prepare($query);

        $this->amenity_id = htmlspecialchars(strip_tags($this->amenity_id));
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $this->guest_name = htmlspecialchars(strip_tags($this->guest_name));
        $this->booking_date = htmlspecialchars(strip_tags($this->booking_date));
        $this->check_in_time = htmlspecialchars(strip_tags($this->check_in_time));
        $this->check_out_time = htmlspecialchars(strip_tags($this->check_out_time));

        $stmt->bindParam(":amenity_id", $this->amenity_id);
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":guest_name", $this->guest_name);
        $stmt->bindParam(":booking_date", $this->booking_date);
        $stmt->bindParam(":check_in_time", $this->check_in_time);
        $stmt->bindParam(":check_out_time", $this->check_out_time);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function updateStatus() {
        $query = "UPDATE " . $this->table_name . " SET status=:status WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(":status", $this->status);
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
            (SELECT COUNT(*) FROM " . $this->table_name . " WHERE status = 'confirmed') as upcoming_count,
            (SELECT COUNT(*) FROM " . $this->table_name . " WHERE status = 'checked_in') as active_count,
            (SELECT COUNT(*) FROM " . $this->table_name . " WHERE status = 'checked_out') as completed_count,
            (SELECT COUNT(*) FROM " . $this->table_name . ") as total_count,
            (SELECT COUNT(*) FROM " . $this->table_name . " WHERE booking_date = CURDATE()) as today_count";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function readPaginated($params = []) {
        $p = Paginator::build(array_merge($params, ['allowed_sorts' => ['booking_date', 'check_in_time', 'created_at', 'status']]));

        $joins = "LEFT JOIN amenities a ON b.amenity_id = a.id
                  LEFT JOIN properties p ON a.property_id = p.id";
        $fromTable = $this->table_name . " b";
        $where = [];

        if ($p['search']) {
            $searchClause = Paginator::searchClause(['a.name', 'p.title', 'b.guest_name'], $p['search']);
            if ($searchClause) $where[] = $searchClause;
        }

        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $orderClause = "ORDER BY b.{$p['sort']} {$p['order']}";
        $limitClause = "LIMIT {$p['perPage']} OFFSET {$p['offset']}";

        $countSql = "SELECT COUNT(*) FROM $fromTable $joins $whereClause";
        $countStmt = $this->conn->prepare($countSql);
        Paginator::bindSearch($countStmt, $p['search']);
        $countStmt->execute();

        $dataSql = "SELECT b.*, a.name as amenity_name, a.location as amenity_location,
                    p.title as property_title
                    FROM $fromTable $joins $whereClause $orderClause $limitClause";
        $dataStmt = $this->conn->prepare($dataSql);
        Paginator::bindSearch($dataStmt, $p['search']);
        $dataStmt->execute();

        return Paginator::paginatedResponse($dataStmt, $countStmt, $p['page'], $p['perPage']);
    }
}
