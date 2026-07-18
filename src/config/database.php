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
                $this->ensureDefaultData($this->conn);
            }
        } catch(PDOException $exception) {
            throw $exception;
        }

        return $this->conn;
    }

    private function bootstrapSchema($pdo) {
        try {
            $check = $pdo->query("SELECT 1 FROM information_schema.tables WHERE table_name = 'users' AND table_schema = current_schema()");
            $checkProps = $pdo->query("SELECT 1 FROM information_schema.tables WHERE table_name = 'properties' AND table_schema = current_schema()");
            if ($check->fetch() && $checkProps->fetch()) return;

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

    private function ensureDefaultData($pdo) {
        try {
            try {
                $amenityCount = (int)$pdo->query("SELECT COUNT(*) FROM amenities")->fetchColumn();
            } catch (Throwable $t) {
                $amenityCount = 0;
            }
            if ($amenityCount === 0) {
                $pdo->exec("INSERT INTO amenities (name, description, capacity, is_available) VALUES
                    ('Rooftop Pool & Spa', 'Temperature-controlled rooftop pool with panoramic city views. Spa services available on request.', 12, true),
                    ('Secure Parking Garage', 'Underground parking with 24/7 security monitoring and electric vehicle charging stations.', 50, true),
                    ('Fitness Center', 'State-of-the-art gym with Peloton bikes, free weights, and yoga studio.', 20, true),
                    ('Residents Lounge', 'Private lounge with fireplace, library, and complimentary refreshments.', 30, true),
                    ('Conference Room', 'Professional meeting room with AV equipment and video conferencing.', 12, true)");
            }

            try {
                $propCount = (int)$pdo->query("SELECT COUNT(*) FROM properties")->fetchColumn();
            } catch (Throwable $t) {
                $propCount = 0;
            }
            if ($propCount === 0) {
                $pdo->exec("INSERT INTO properties (id, title, description, property_type, status, price, bedrooms, bathrooms, area_sqft, location, address, city, state, zip_code, latitude, longitude, image, features, is_featured) VALUES
                    (1, 'The Meridian Penthouse', 'A crown-jewel penthouse with panoramic city views, designer finishes, and private rooftop terrace.', 'Penthouse', 'available', 18500.00, 4, 4, 4200, 'Upper East Side, New York', '1200 Park Avenue', 'New York', 'NY', '10128', 40.7831, -73.9565, 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=800', '[\"Rooftop Terrace\",\"Private Elevator\",\"Wine Cellar\",\"Smart Home\",\"Concierge\"]', true),
                    (2, 'Villa Serenity', 'Mediterranean-inspired estate with infinity pool, landscaped gardens, and ocean-facing grand salon.', 'Villa', 'available', 22000.00, 6, 5, 6800, 'Malibu, California', '24500 Pacific Coast Highway', 'Malibu', 'CA', '90265', 34.0259, -118.7798, 'https://images.unsplash.com/photo-1613490493576-7fde63acd811?w=800', '[\"Infinity Pool\",\"Ocean View\",\"Home Theater\",\"Guest House\",\"Wine Cellar\"]', true),
                    (3, 'Skyline Tower Residence', 'Ultra-modern residence in the heart of downtown with floor-to-ceiling glass and smart home integration.', 'Apartment', 'available', 9500.00, 3, 3, 2800, 'Downtown, Chicago', '150 North Michigan Avenue', 'Chicago', 'IL', '60601', 41.8827, -87.6233, 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=800', '[\"Floor-to-Ceiling Windows\",\"Smart Home\",\"Gym Access\",\"Rooftop Lounge\",\"Doorman\"]', true),
                    (4, 'The Grand Manor', 'A stately heritage property with modern amenities, sprawling gardens, and classic architectural details.', 'Villa', 'available', 35000.00, 7, 6, 9500, 'Beverly Hills, California', '612 N Rodeo Drive', 'Beverly Hills', 'CA', '90210', 34.0674, -118.4003, 'https://images.unsplash.com/photo-1580587771525-78b9dba3b914?w=800', '[\"Gardens\",\"Pool\",\"Tennis Court\",\"Home Theater\",\"Wine Cellar\",\"Staff Quarters\"]', true),
                    (5, 'Harbour View Suite', 'Waterfront luxury with marina views, contemporary design, and resort-style amenities.', 'Apartment', 'available', 12000.00, 2, 2, 1800, 'Marina Bay, San Francisco', '88 Marina Boulevard', 'San Francisco', 'CA', '94123', 37.8070, -122.4095, 'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=800', '[\"Marina View\",\"Balcony\",\"Concierge\",\"Parking\",\"Gym\"]', false),
                    (6, 'Alpine Retreat', 'Mountain estate with ski-in access, timber-frame great room, and panoramic valley views.', 'Villa', 'available', 28000.00, 5, 4, 5200, 'Aspen, Colorado', '315 Snowmass Drive', 'Aspen', 'CO', '81611', 39.1911, -106.8175, 'https://images.unsplash.com/photo-1518780664697-55e3ad937233?w=800', '[\"Ski-In Access\",\"Hot Tub\",\"Fireplace\",\"Mud Room\",\"Equipment Storage\"]', false)");
            } else {
                try {
                    $prop2 = (int)$pdo->query("SELECT COUNT(*) FROM properties WHERE id = 2")->fetchColumn();
                    if ($prop2 === 0) {
                        $pdo->exec("INSERT INTO properties (id, title, description, property_type, status, price, bedrooms, bathrooms, area_sqft, location, address, city, state, zip_code, latitude, longitude, image, features, is_featured) VALUES
                            (2, 'Villa Serenity', 'Mediterranean-inspired estate with infinity pool, landscaped gardens, and ocean-facing grand salon.', 'Villa', 'available', 22000.00, 6, 5, 6800, 'Malibu, California', '24500 Pacific Coast Highway', 'Malibu', 'CA', '90265', 34.0259, -118.7798, 'https://images.unsplash.com/photo-1613490493576-7fde63acd811?w=800', '[\"Infinity Pool\",\"Ocean View\",\"Home Theater\",\"Guest House\",\"Wine Cellar\"]', true)");
                    }
                } catch (Throwable $t) {}
            }
        } catch (Throwable $e) {
            error_log('ensureDefaultData error: ' . $e->getMessage());
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
