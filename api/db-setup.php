<?php
header('Content-Type: application/json');

// Only allow from admin or internal
$migrationPath = __DIR__ . '/../database/migrate.php';

if (php_sapi_name() === 'cli') {
    echo "Running migration from CLI...\n";
    include $migrationPath;
    echo json_encode(['success' => true]);
    exit;
}

// Check if running from web
$secret = $_GET['secret'] ?? '';
if ($secret !== 'aura-db-setup-2026') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Forbidden']);
    exit;
}

ob_start();
include $migrationPath;
$output = ob_get_clean();

echo json_encode([
    'success' => true,
    'output' => $output,
    'message' => 'Migration executed'
]);
