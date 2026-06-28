<?php
require_once __DIR__ . '/../src/core/Middleware.php';
require_once __DIR__ . '/../src/config/database.php';
require_once __DIR__ . '/../src/config/auth.php';
require_once __DIR__ . '/../src/core/AuditLogger.php';
require_once __DIR__ . '/../src/controllers/RentalController.php';

Middleware::api();

$database = new Database();
$db = $database->getConnection();
$controller = new RentalController($db);
$rental = new Rental($db);

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        Middleware::auth('rentals_view');
        $params = Middleware::getQueryParams();
        if (isset($_GET['id'])) {
            $result = json_decode($controller->getOne($_GET['id']), true);
            if (!empty($result['id'])) {
                Response::success($result);
            } else {
                Response::error($result['message'] ?? 'Not found.', 404);
            }
        } elseif (isset($_GET['user_id'])) {
            $result = json_decode($controller->getByUser($_GET['user_id']), true);
            Response::success($result);
        } elseif (isset($_GET['my_properties']) && isset($_GET['user_id'])) {
            $result = json_decode($controller->getMyRentalProperties($_GET['user_id']), true);
            Response::success($result);
        } else {
            $result = $rental->readAllPaginated($params);
            Response::paginated($result['records'], $result['pagination']['total'], $params['page'], $params['per_page']);
        }
        break;

    case 'POST':
        Middleware::auth('rentals_create');
        $data = Middleware::getJsonInput();
        $output = $controller->create($data);
        $code = http_response_code();
        $result = json_decode($output, true);
        if ($code >= 200 && $code < 300) {
            AuditLogger::log('create', 'rental', $db->lastInsertId(), 'Rental created', null, (array)$data);
            Response::success(null, $result['message'] ?? 'Rental created.', $code);
        } else {
            Response::error($result['message'] ?? 'Unable to create rental.', $code);
        }
        break;

    case 'PUT':
        if (isset($_GET['terminate']) && isset($_GET['id'])) {
            Middleware::auth('rentals_terminate');
            $output = $controller->terminate($_GET['id']);
            $code = http_response_code();
            $result = json_decode($output, true);
            if ($code >= 200 && $code < 300) {
                AuditLogger::log('terminate', 'rental', $_GET['id'], 'Rental terminated');
                Response::success(null, $result['message'] ?? 'Rental terminated.', $code);
            } else {
                Response::error($result['message'] ?? 'Unable to terminate.', $code);
            }
        }
        break;
}
