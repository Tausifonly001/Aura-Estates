<?php
class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $port;
    private $charset = "utf8mb4";
    public $conn;
    private static $bootstrapped = false;

    public function __construct() {
        $this->host = getenv('DB_HOST') ?: 'localhost';
        $this->db_name = getenv('DB_NAME') ?: 'aura_estates';
        $this->username = getenv('DB_USER') ?: 'root';
        $this->password = getenv('DB_PASSWORD') ?: '';
        $this->port = getenv('DB_PORT') ?: null;
    }

    private function detectDriver() {
        $driverEnv = getenv('DB_DRIVER');
        if ($driverEnv === 'mysql') return 'mysql';
        if ($driverEnv === 'pgsql') return 'pgsql';
        if ($this->port == '3306') return 'mysql';
        if ($this->port == '5432') return 'pgsql';
        if (extension_loaded('pdo_mysql')) return 'mysql';
        if (extension_loaded('pdo_pgsql')) return 'pgsql';
        return 'mysql';
    }

    public function getConnection() {
        if ($this->conn !== null) return $this->conn;

        try {
            $driver = $this->detectDriver();

            if ($driver === 'mysql') {
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

            if (!self::$bootstrapped) {
                self::$bootstrapped = true;
                if ($driver === 'pgsql') {
                    $this->bootstrapSchema($this->conn);
                }
                $this->ensureOAuthColumns($this->conn, $driver);
            }
        } catch(PDOException $exception) {
            throw $exception;
        }

        return $this->conn;
    }

    private function bootstrapSchema($pdo) {
        try {
            $check = $pdo->query("SELECT 1 FROM information_schema.tables WHERE table_name = 'users' AND table_schema = current_schema()");
            if ($check->fetch()) return;

            $schema = file_get_contents(__DIR__ . '/../../database/schema_bootstrap.sql');
            if ($schema) {
                $statements = array_filter(array_map('trim', explode(';', $schema)), fn($s) => !empty($s));
                foreach ($statements as $stmt) {
                    $pdo->exec($stmt);
                }
            }
        } catch (Exception $e) {
            error_log('Bootstrap schema error: ' . $e->getMessage());
        }
    }

    private function ensureOAuthColumns($pdo, $driver) {
        try {
            if ($driver === 'mysql') {
                $cols = $pdo->query("SHOW COLUMNS FROM users")->fetchAll(PDO::FETCH_COLUMN);
                if (!empty($cols)) {
                    if (!in_array('google_id', $cols)) {
                        $pdo->exec("ALTER TABLE users ADD COLUMN google_id VARCHAR(255) UNIQUE");
                    }
                    if (!in_array('avatar', $cols)) {
                        $pdo->exec("ALTER TABLE users ADD COLUMN avatar VARCHAR(500) NULL");
                    }
                    $pdo->exec("ALTER TABLE users MODIFY COLUMN password VARCHAR(255) NULL");
                }
            } else {
                $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS google_id VARCHAR(255) UNIQUE");
                $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS avatar VARCHAR(500) NULL");
                $pdo->exec("ALTER TABLE users ALTER COLUMN password DROP NOT NULL");
            }
        } catch (Throwable $e) {
            error_log('ensureOAuthColumns error: ' . $e->getMessage());
        }
    }
}
?>
