<?php
header('Content-Type: application/json; charset=UTF-8');

try {
    require_once __DIR__ . '/../src/core/Middleware.php';
    require_once __DIR__ . '/../src/config/database.php';
    require_once __DIR__ . '/../src/models/Property.php';

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
    $property = new Property($db);

    $method = $_SERVER['REQUEST_METHOD'];

    switch($method) {
        case 'GET':
            $params = Middleware::getQueryParams();
            if (isset($_GET['id'])) {
                $property->id = $_GET['id'];
                $stmt = $property->readOne();
                $data = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($data) {
                    Response::success($data);
                } else {
                    Response::error('Not found.', 404);
                }
            } else {
                $result = $property->readPaginated($params);
                Response::paginated($result['records'], $result['pagination']['total'], $params['page'], $params['per_page']);
            }
            break;
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'file' => basename($e->getFile()),
        'line' => $e->getLine()
    ]);
}
