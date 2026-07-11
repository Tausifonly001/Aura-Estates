<?php
require_once __DIR__ . '/../src/core/Middleware.php';
require_once __DIR__ . '/../src/config/database.php';
require_once __DIR__ . '/../src/config/auth.php';
require_once __DIR__ . '/../src/core/AuditLogger.php';
require_once __DIR__ . '/../src/controllers/TestimonialController.php';

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
$controller = new TestimonialController($db);
$testimonial = new Testimonial($db);

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        $params = Middleware::getQueryParams();
        if (isset($_GET['id'])) {
            $result = json_decode($controller->getOne($_GET['id']), true);
            if (!empty($result['id'])) {
                Response::success($result);
            } else {
                Response::error('Not found.', 404);
            }
        } else {
            $result = $testimonial->readPaginated($params);
            Response::paginated($result['records'], $result['pagination']['total'], $params['page'], $params['per_page']);
        }
        break;

    case 'POST':
        Middleware::auth('testimonials_create');
        $data = Middleware::getJsonInput();

        $validator = new Validator($data);
        $validator->required('name', 'Name')
            ->required('content', 'Content');
        if (!$validator->passes()) {
            Response::error('Validation failed.', 422, $validator->errors());
        }

        $output = $controller->create($data);
        $code = http_response_code();
        $result = json_decode($output, true);
        if ($code >= 200 && $code < 300) {
            AuditLogger::log('create', 'testimonial', $db->lastInsertId(), 'Testimonial created', null, (array)$data);
            Response::success(null, $result['message'] ?? 'Testimonial created.', $code);
        } else {
            Response::error($result['message'] ?? 'Unable to create testimonial.', $code);
        }
        break;

    case 'PUT':
        Middleware::auth('testimonials_edit');
        $data = Middleware::getJsonInput();

        $validator = new Validator($data);
        $validator->required('id', 'ID');
        if (!$validator->passes()) {
            Response::error('Validation failed.', 422, $validator->errors());
        }

        $output = $controller->update($data);
        $code = http_response_code();
        $result = json_decode($output, true);
        if ($code >= 200 && $code < 300) {
            AuditLogger::log('update', 'testimonial', $data->id ?? null, 'Testimonial updated', null, (array)$data);
            Response::success(null, $result['message'] ?? 'Testimonial updated.', $code);
        } else {
            Response::error($result['message'] ?? 'Unable to update testimonial.', $code);
        }
        break;

    case 'DELETE':
        Middleware::auth('testimonials_delete');
        if (isset($_GET['id'])) {
            $output = $controller->delete($_GET['id']);
            $code = http_response_code();
            $result = json_decode($output, true);
            if ($code >= 200 && $code < 300) {
                AuditLogger::log('delete', 'testimonial', $_GET['id'], 'Testimonial deleted');
                Response::success(null, $result['message'] ?? 'Testimonial deleted.', $code);
            } else {
                Response::error($result['message'] ?? 'Unable to delete testimonial.', $code);
            }
        }
        break;
}
