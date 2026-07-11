<?php
require_once __DIR__ . '/../src/config/database.php';

header('Content-Type: application/json');

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Check tables
    $tables = [];
    $result = $db->query("SELECT table_name FROM information_schema.tables WHERE table_schema = current_schema() ORDER BY table_name");
    while ($row = $result->fetch()) {
        $tables[] = $row['table_name'];
    }
    
    // Check admin user
    $admin = null;
    try {
        $stmt = $db->prepare("SELECT id, name, email, role FROM users WHERE email = ?");
        $stmt->execute(['admin@aura.com']);
        $admin = $stmt->fetch();
    } catch (Exception $e) {
        $admin = ['error' => $e->getMessage()];
    }
    
    echo json_encode([
        'success' => true,
        'tables' => $tables,
        'admin_user' => $admin,
        'table_count' => count($tables)
    ], JSON_PRETTY_PRINT);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
}
