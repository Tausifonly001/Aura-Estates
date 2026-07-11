<?php
require_once __DIR__ . '/../src/core/Middleware.php';
require_once __DIR__ . '/../src/core/AuditLogger.php';
require_once __DIR__ . '/../src/config/database.php';

Middleware::api();
Middleware::auth('dashboard_view');

try {
    $database = new Database();
    $db = $database->getConnection();
} catch (Exception $e) {
    http_response_code(503);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'System temporarily unavailable. Please try again later.']);
    exit;
}
$type = $_GET['type'] ?? '';

$allowedTypes = ['properties', 'maintenance', 'amenities', 'bookings', 'rentals', 'users', 'inquiries', 'blog_posts', 'agents', 'testimonials'];
if (!in_array($type, $allowedTypes)) {
    Response::error('Invalid export type. Allowed: ' . implode(', ', $allowedTypes), 400);
}

$format = $_GET['format'] ?? 'csv';
if ($format !== 'csv') {
    Response::error('Only CSV format is supported.', 400);
}

$columns = [
    'properties' => ['id', 'title', 'price', 'location', 'property_type', 'bedrooms', 'bathrooms', 'area_sqft', 'is_available', 'created_at'],
    'maintenance' => ['id', 'property_id', 'tenant_name', 'tenant_email', 'issue_description', 'priority', 'status', 'assigned_to', 'created_at', 'resolved_at'],
    'amenities' => ['id', 'property_id', 'name', 'description', 'capacity', 'location', 'is_active', 'created_at'],
    'bookings' => ['id', 'amenity_id', 'user_id', 'guest_name', 'booking_date', 'check_in_time', 'check_out_time', 'status', 'created_at'],
    'rentals' => ['id', 'user_id', 'property_id', 'start_date', 'end_date', 'monthly_rent', 'status', 'created_at'],
    'users' => ['id', 'name', 'email', 'role', 'created_at'],
    'inquiries' => ['id', 'property_id', 'name', 'email', 'message', 'status', 'created_at'],
    'blog_posts' => ['id', 'title', 'slug', 'author', 'category', 'status', 'published_at', 'created_at'],
    'agents' => ['id', 'name', 'title', 'created_at'],
    'testimonials' => ['id', 'name', 'location', 'rating', 'created_at'],
];

$tableMap = [
    'properties' => 'properties',
    'maintenance' => 'maintenance_requests',
    'amenities' => 'amenities',
    'bookings' => 'amenity_bookings',
    'rentals' => 'rentals',
    'users' => 'users',
    'inquiries' => 'inquiries',
    'blog_posts' => 'blog_posts',
    'agents' => 'agents',
    'testimonials' => 'testimonials',
];

// Track export for audit
AuditLogger::log('export', $type, $_SESSION['user_id'] ?? null, "Exported $type as $format");

$table = $tableMap[$type];
$cols = implode(', ', $columns[$type]);
$stmt = $db->query("SELECT $cols FROM $table ORDER BY created_at DESC");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$filename = $type . '_' . date('Y-m-d') . '.csv';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen('php://output', 'w');
fputcsv($output, $columns[$type]);
foreach ($rows as $row) {
    $line = [];
    foreach ($columns[$type] as $col) {
        $line[] = $row[$col] ?? '';
    }
    fputcsv($output, $line);
}
fclose($output);
exit;
