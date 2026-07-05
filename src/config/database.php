<?php
class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $port;
    private $charset = "utf8mb4";
    public $conn;
    private $driver = "pgsql";

    public function __construct() {
        $this->host = getenv('DB_HOST') ?: 'localhost';
        $this->db_name = getenv('DB_NAME') ?: 'aura_estates';
        $this->username = getenv('DB_USER') ?: 'root';
        $this->password = getenv('DB_PASSWORD') ?: '';
        $this->port = getenv('DB_PORT') ?: '5432';

        if (getenv('DB_URL')) {
            $this->dsn_from_url = getenv('DB_URL');
        } else {
            $this->dsn_from_url = null;
        }
    }

    public function getConnection() {
        if ($this->conn !== null) return $this->conn;

        try {
            if ($this->dsn_from_url) {
                $dsn = $this->dsn_from_url;
            } else {
                $dsn = "pgsql:host={$this->host};port={$this->port};dbname={$this->db_name}";
            }
            $this->conn = new PDO($dsn, $this->username, $this->password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch(PDOException $exception) {
            throw $exception;
        }

        return $this->conn;
    }
}
?>
