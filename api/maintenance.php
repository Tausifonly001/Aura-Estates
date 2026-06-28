<?php
require_once __DIR__ . '/../src/core/Middleware.php';
require_once __DIR__ . '/../src/config/database.php';
require_once __DIR__ . '/../src/config/auth.php';
require_once __DIR__ . '/../src/core/AuditLogger.php';
require_once __DIR__ . '/../src/services/ResendService.php';
require_once __DIR__ . '/../src/services/EmailService.php';
require_once __DIR__ . '/../src/controllers/MaintenanceController.php';

Middleware::api();

$database = new Database();
$db = $database->getConnection();
$controller = new MaintenanceController($db);
$maintenance = new MaintenanceRequest($db);

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        $params = Middleware::getQueryParams();
        if (isset($_GET['staff_list'])) {
            Middleware::auth('maintenance_update');
            $result = json_decode($controller->getStaffList(), true);
            Response::success($result);
        } elseif (isset($_GET['id'])) {
            Middleware::auth('maintenance_view');
            $result = json_decode($controller->getOne($_GET['id']), true);
            if (!empty($result['id'])) {
                Response::success($result);
            } else {
                Response::error($result['message'] ?? 'Not found.', 404);
            }
        } elseif (isset($_GET['email'])) {
            Middleware::auth('maintenance_view');
            $result = json_decode($controller->getByEmail($_GET['email']), true);
            Response::success($result);
        } elseif (isset($_GET['user_id'])) {
            Middleware::auth('maintenance_view');
            $result = json_decode($controller->getByUser($_GET['user_id']), true);
            Response::success($result);
        } elseif (isset($_GET['stats'])) {
            Middleware::auth('maintenance_view');
            $result = json_decode($controller->getStats(), true);
            Response::success($result);
        } else {
            Middleware::auth('maintenance_view');
            $result = $maintenance->readPaginated($params);
            Response::paginated($result['records'], $result['pagination']['total'], $params['page'], $params['per_page']);
        }
        break;

    case 'POST':
        Middleware::auth();
        $data = Middleware::getJsonInput();
        $output = $controller->create($data);
        $code = http_response_code();
        $result = json_decode($output, true);
        if ($code >= 200 && $code < 300) {
            $maintId = $result['id'] ?? $db->lastInsertId();
            AuditLogger::log('create', 'maintenance_request', $maintId, 'Maintenance request created', null, (array)$data);
            try {
                $user = Auth::getUser();
                if ($user && !empty($user['email'])) {
                    ResendService::sendMaintenanceUpdate($user['email'], $user['name'] ?? 'Tenant', $data->property_title ?? 'Property', 'received', 'Your request has been received and is being reviewed.');
                    EmailService::sendMaintenanceAlert($user['name'] ?? 'Tenant', $user['email'], $data->property_title ?? 'Property', $data->priority ?? 'medium');
                }
            } catch (Exception $e) { error_log("Maintenance email: " . $e->getMessage()); }
            Response::success(['id' => $maintId], $result['message'] ?? 'Maintenance request created.', $code);
        } else {
            Response::error($result['message'] ?? 'Unable to create request.', $code);
        }
        break;

    case 'PUT':
        Middleware::auth('maintenance_update');
        $data = Middleware::getJsonInput();
        if (isset($data->assign)) {
            $output = $controller->assignStaff($data);
            $code = http_response_code();
            $result = json_decode($output, true);
            if ($code >= 200 && $code < 300) {
                AuditLogger::log('assign', 'maintenance_request', $data->id ?? null, 'Staff assigned to maintenance request');
                Response::success(null, $result['message'] ?? 'Staff assigned.', $code);
            } else {
                Response::error($result['message'] ?? 'Unable to assign.', $code);
            }
        } else {
            $output = $controller->updateStatus($data);
            $code = http_response_code();
            $result = json_decode($output, true);
            if ($code >= 200 && $code < 300) {
                AuditLogger::log('update', 'maintenance_request', $data->id ?? null, 'Maintenance status updated');
                try {
                    $stmt = $db->prepare("SELECT u.email, u.name, p.title FROM maintenance_requests m JOIN users u ON m.user_id = u.id LEFT JOIN properties p ON m.property_id = p.id WHERE m.id = ?");
                    $stmt->execute([$data->id]);
                    $mUser = $stmt->fetch(PDO::FETCH_ASSOC);
                    if ($mUser && !empty($mUser['email'])) {
                        ResendService::sendMaintenanceUpdate($mUser['email'], $mUser['name'], $mUser['title'] ?? 'Property', $data->status ?? 'updated', $data->note ?? '');
                    }
                } catch (Exception $e) { error_log("Maintenance status email: " . $e->getMessage()); }
                Response::success(null, $result['message'] ?? 'Status updated.', $code);
            } else {
                Response::error($result['message'] ?? 'Unable to update.', $code);
            }
        }
        break;

    case 'DELETE':
        Middleware::auth('maintenance_delete');
        if (isset($_GET['id'])) {
            $output = $controller->delete($_GET['id']);
            $code = http_response_code();
            $result = json_decode($output, true);
            if ($code >= 200 && $code < 300) {
                AuditLogger::log('delete', 'maintenance_request', $_GET['id'], 'Maintenance request deleted');
                Response::success(null, $result['message'] ?? 'Request deleted.', $code);
            } else {
                Response::error($result['message'] ?? 'Unable to delete.', $code);
            }
        }
        break;
}
