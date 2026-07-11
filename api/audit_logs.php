<?php
require_once __DIR__ . '/../src/core/Middleware.php';
require_once __DIR__ . '/../src/config/database.php';
require_once __DIR__ . '/../src/config/auth.php';
require_once __DIR__ . '/../src/core/AuditLogger.php';

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

$params = Middleware::getQueryParams();

$filters = [];
if (!empty($_GET['user_id'])) $filters['user_id'] = $_GET['user_id'];
if (!empty($_GET['action'])) $filters['action'] = $_GET['action'];
if (!empty($_GET['entity_type'])) $filters['entity_type'] = $_GET['entity_type'];

$result = AuditLogger::search($filters, $params['page'], $params['per_page']);
Response::paginated($result['records'], $result['total'], $params['page'], $params['per_page']);
