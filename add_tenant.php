<?php
include_once __DIR__ . '/src/config/database.php';
$database = new Database();
$db = $database->getConnection();

try {
    $email = 'tenant@aura.com';
    $password = 'password123';
    $hash = password_hash($password, PASSWORD_BCRYPT);
    
    // Check if tenant exists
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->rowCount() == 0) {
        $stmt = $db->prepare("INSERT INTO users (name, email, password, role) VALUES ('John Tenant', ?, ?, 'user')");
        $stmt->execute([$email, $hash]);
        $tenant_id = $db->lastInsertId();
        
        // Update rentals and bookings to belong to this tenant so they have dummy data
        $db->prepare("UPDATE rentals SET user_id = ?")->execute([$tenant_id]);
        $db->prepare("UPDATE amenity_bookings SET user_id = ?")->execute([$tenant_id]);
        echo "Tenant user created. Email: $email | Password: $password";
    } else {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $tenant_id = $row['id'];
        $db->prepare("UPDATE rentals SET user_id = ?")->execute([$tenant_id]);
        $db->prepare("UPDATE amenity_bookings SET user_id = ?")->execute([$tenant_id]);
        echo "Tenant user already exists. Rentals mapped to Tenant.";
    }
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
