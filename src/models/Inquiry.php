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
        $query = "INSERT INTO " . $this->table_name . " SET
            property_id=:property_id, name=:name, email=:email, message=:message";
        
        $stmt = $this->conn->prepare($query);

        $this->property_id=htmlspecialchars(strip_tags($this->property_id));
        $this->name=htmlspecialchars(strip_tags($this->name));
        $this->email=htmlspecialchars(strip_tags($this->email));
        $this->message=htmlspecialchars(strip_tags($this->message));

        $stmt->bindParam(":property_id", $this->property_id);
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":message", $this->message);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>
