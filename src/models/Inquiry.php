<?php
class Inquiry {
    private $conn;
    private $table_name = "inquiries";

    public $id;
    public $property_id;
    public $name;
    public $email;
    public $message;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " (property_id, name, email, message) VALUES (:property_id, :name, :email, :message)";
        $stmt = $this->conn->prepare($query);

        $propId = !empty($this->property_id) && is_numeric($this->property_id) && $this->property_id > 0 ? (int)$this->property_id : null;
        if ($propId !== null) {
            try {
                $check = $this->conn->prepare("SELECT id FROM properties WHERE id = ?");
                $check->execute([$propId]);
                if (!$check->fetchColumn()) {
                    $propId = null;
                }
            } catch (Throwable $t) {
                $propId = null;
            }
        }

        $this->name = htmlspecialchars(strip_tags((string)$this->name));
        $this->email = htmlspecialchars(strip_tags((string)$this->email));
        $this->message = htmlspecialchars(strip_tags((string)$this->message));

        if ($propId === null) {
            $stmt->bindValue(":property_id", null, PDO::PARAM_NULL);
        } else {
            $stmt->bindValue(":property_id", $propId, PDO::PARAM_INT);
        }
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":message", $this->message);

        try {
            return $stmt->execute();
        } catch (Throwable $e) {
            error_log("Inquiry create error: " . $e->getMessage());
            return false;
        }
    }
}
?>
