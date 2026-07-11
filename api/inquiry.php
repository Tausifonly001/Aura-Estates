<?php
require_once __DIR__ . '/../src/core/Middleware.php';
require_once __DIR__ . '/../src/config/database.php';
require_once __DIR__ . '/../src/core/AuditLogger.php';
require_once __DIR__ . '/../src/controllers/InquiryController.php';

Middleware::api();

try {
    $database = new Database();
    $db = $database->getConnection();
} catch (Exception $e) {
    http_response_code(503);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'System temporarily unavailable. Please try again later.']);
    exit;
}
$controller = new InquiryController($db);

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'POST':
        $data = Middleware::getJsonInput();

        $validator = new Validator($data);
        $validator->required('property_id', 'Property')
            ->required('name', 'Name')
            ->required('email', 'Email')
            ->email('email')
            ->required('message', 'Message');
        if (!$validator->passes()) {
            Response::error('Validation failed.', 422, $validator->errors());
        }

        $output = $controller->create($data);
        $code = http_response_code();
        $result = json_decode($output, true);
        if ($code >= 200 && $code < 300) {
            AuditLogger::log('create', 'inquiry', $db->lastInsertId(), "Inquiry sent for property {$data->property_id}");
            Response::success(null, $result['message'] ?? 'Inquiry was sent.', $code);
        } else {
            Response::error($result['message'] ?? 'Unable to send inquiry.', $code);
        }
        break;
}
