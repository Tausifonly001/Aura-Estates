<?php
require_once __DIR__ . '/../src/core/Middleware.php';
require_once __DIR__ . '/../src/config/database.php';
require_once __DIR__ . '/../src/config/auth.php';
require_once __DIR__ . '/../src/core/AuditLogger.php';
require_once __DIR__ . '/../src/controllers/AgentController.php';

Middleware::api();

$database = new Database();
$db = $database->getConnection();
$controller = new AgentController($db);
$agent = new Agent($db);

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
            $result = $agent->readPaginated($params);
            Response::paginated($result['records'], $result['pagination']['total'], $params['page'], $params['per_page']);
        }
        break;

    case 'POST':
        Middleware::auth('agents_create');
        $data = Middleware::getJsonInput();

        $validator = new Validator($data);
        $validator->required('name', 'Name');
        if (!$validator->passes()) {
            Response::error('Validation failed.', 422, $validator->errors());
        }

        $output = $controller->create($data);
        $code = http_response_code();
        $result = json_decode($output, true);
        if ($code >= 200 && $code < 300) {
            AuditLogger::log('create', 'agent', $db->lastInsertId(), 'Agent created', null, (array)$data);
            Response::success(null, $result['message'] ?? 'Agent created.', $code);
        } else {
            Response::error($result['message'] ?? 'Unable to create agent.', $code);
        }
        break;

    case 'PUT':
        Middleware::auth('agents_edit');
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
            AuditLogger::log('update', 'agent', $data->id ?? null, 'Agent updated', null, (array)$data);
            Response::success(null, $result['message'] ?? 'Agent updated.', $code);
        } else {
            Response::error($result['message'] ?? 'Unable to update agent.', $code);
        }
        break;

    case 'DELETE':
        Middleware::auth('agents_delete');
        if (isset($_GET['id'])) {
            $output = $controller->delete($_GET['id']);
            $code = http_response_code();
            $result = json_decode($output, true);
            if ($code >= 200 && $code < 300) {
                AuditLogger::log('delete', 'agent', $_GET['id'], 'Agent deleted');
                Response::success(null, $result['message'] ?? 'Agent deleted.', $code);
            } else {
                Response::error($result['message'] ?? 'Unable to delete agent.', $code);
            }
        }
        break;
}
