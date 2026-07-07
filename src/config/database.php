<?php
class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $port;
    private $charset = "utf8mb4";
    public $conn;
    private $dsn_from_url = null;

    public function __construct() {
        $this->host = getenv('DB_HOST') ?: 'localhost';
        $this->db_name = getenv('DB_NAME') ?: 'aura_estates';
        $this->username = getenv('DB_USER') ?: 'root';
        $this->password = getenv('DB_PASSWORD') ?: '';
        $this->port = getenv('DB_PORT') ?: null;

        if (getenv('DB_URL')) {
            $this->dsn_from_url = getenv('DB_URL');
        } else {
            $this->dsn_from_url = null;
        }
    }

    private function detectDriver() {
        if ($this->dsn_from_url) {
            if (strpos($this->dsn_from_url, 'mysql:') === 0) return 'mysql';
            return 'pgsql';
        }
        $driverEnv = getenv('DB_DRIVER');
        if ($driverEnv === 'mysql') return 'mysql';
        if ($driverEnv === 'pgsql') return 'pgsql';
        if ($this->port == '3306') return 'mysql';
        if ($this->port == '5432') return 'pgsql';
        if (extension_loaded('pdo_mysql')) return 'mysql';
        if (extension_loaded('pgsql')) return 'pgsql';
        return 'mysql';
    }

    public function getConnection() {
        if ($this->conn !== null) return $this->conn;

        try {
            $driver = $this->detectDriver();

            if ($this->dsn_from_url) {
                $dsn = $this->dsn_from_url;
            } elseif ($driver === 'mysql') {
                $port = $this->port ?: '3306';
                $dsn = "mysql:host={$this->host};port={$port};dbname={$this->db_name};charset={$this->charset}";
            } else {
                $port = $this->port ?: '5432';
                $dsn = "pgsql:host={$this->host};port={$port};dbname={$this->db_name}";
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
