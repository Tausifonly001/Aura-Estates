<?php
require_once __DIR__ . '/../src/core/Middleware.php';
require_once __DIR__ . '/../src/config/database.php';
require_once __DIR__ . '/../src/config/auth.php';
require_once __DIR__ . '/../src/core/AuditLogger.php';
require_once __DIR__ . '/../src/controllers/AmenityController.php';

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
$controller = new AmenityController($db);
$amenity = new Amenity($db);

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        $params = Middleware::getQueryParams();
        if (isset($_GET['action']) && $_GET['action'] === 'check_capacity' && isset($_GET['id']) && isset($_GET['date'])) {
            $result = json_decode($controller->checkCapacity($_GET['id'], $_GET['date']), true);
            Response::success($result);
        } elseif (isset($_GET['id'])) {
            $result = json_decode($controller->getAmenity($_GET['id']), true);
            if (!empty($result['id'])) {
                Response::success($result);
            } else {
                Response::error($result['message'] ?? 'Not found.', 404);
            }
        } elseif (isset($_GET['active'])) {
            $result = json_decode($controller->getActiveAmenities(), true);
            Response::success($result);
        } else {
            $result = $amenity->readPaginated($params);
            $records = array_map(function($r) {
                $r['is_active'] = 1;
                $r['is_available'] = 1;
                $r['location'] = !empty($r['location']) ? $r['location'] : 'Clubhouse / Main Building';
                return $r;
            }, $result['records'] ?? []);
            Response::paginated($records, $result['pagination']['total'] ?? count($records), $params['page'], $params['per_page']);
        }
        break;

    case 'POST':
        Middleware::auth('amenities_create');
        $data = Middleware::getJsonInput();
        $output = $controller->createAmenity($data);
        $code = http_response_code();
        $result = json_decode($output, true);
        if ($code >= 200 && $code < 300) {
            AuditLogger::log('create', 'amenity', $db->lastInsertId(), 'Amenity created', null, (array)$data);
            Response::success(null, $result['message'] ?? 'Amenity created.', $code);
        } else {
            Response::error($result['message'] ?? 'Unable to create.', $code);
        }
        break;

    case 'PUT':
        Middleware::auth('amenities_edit');
        $data = Middleware::getJsonInput();
        $output = $controller->updateAmenity($data);
        $code = http_response_code();
        $result = json_decode($output, true);
        if ($code >= 200 && $code < 300) {
            AuditLogger::log('update', 'amenity', $data->id ?? null, 'Amenity updated', null, (array)$data);
            Response::success(null, $result['message'] ?? 'Amenity updated.', $code);
        } else {
            Response::error($result['message'] ?? 'Unable to update.', $code);
        }
        break;

    case 'DELETE':
        Middleware::auth('amenities_delete');
        if (isset($_GET['id'])) {
            $output = $controller->deleteAmenity($_GET['id']);
            $code = http_response_code();
            $result = json_decode($output, true);
            if ($code >= 200 && $code < 300) {
                AuditLogger::log('delete', 'amenity', $_GET['id'], 'Amenity deleted');
                Response::success(null, $result['message'] ?? 'Amenity deleted.', $code);
            } else {
                Response::error($result['message'] ?? 'Unable to delete.', $code);
            }
        }
        break;
}
