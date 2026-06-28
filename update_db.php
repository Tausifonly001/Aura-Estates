<?php
include_once __DIR__ . '/src/config/database.php';
$database = new Database();
$db = $database->getConnection();

try {
    // 1. Add is_available to properties
    $db->exec("ALTER TABLE properties ADD COLUMN IF NOT EXISTS is_available TINYINT(1) DEFAULT 1");
    
    // 2. Create rentals table
    $db->exec("CREATE TABLE IF NOT EXISTS rentals (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        property_id INT NOT NULL,
        start_date DATE NOT NULL,
        end_date DATE NOT NULL,
        monthly_rent DECIMAL(15, 2) NOT NULL,
        status ENUM('active', 'terminated', 'expired') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE
    )");

    // 3. Insert a dummy rental so the user has something to see
    $stmt = $db->query("SELECT id FROM rentals LIMIT 1");
    if($stmt->rowCount() == 0) {
        $db->exec("INSERT IGNORE INTO rentals (user_id, property_id, start_date, end_date, monthly_rent, status) VALUES 
        (1, 1, '2025-01-01', '2026-12-31', 5000.00, 'active'),
        (1, 2, '2024-06-01', '2025-05-31', 3500.00, 'expired')");
        $db->exec("UPDATE properties SET is_available = 0 WHERE id = 1");
    }

    echo "Database schema patched successfully!";
} catch(PDOException $e) {
    echo "Error patching DB: " . $e->getMessage();
}
?>
