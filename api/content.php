<?php
require_once __DIR__ . '/../src/core/Middleware.php';
require_once __DIR__ . '/../src/config/database.php';
require_once __DIR__ . '/../src/models/SiteContent.php';

Middleware::api();

try {
    $db = (new Database())->getConnection();
} catch (Exception $e) {
    http_response_code(503);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'System temporarily unavailable. Please try again later.']);
    exit;
}
$content = new SiteContent($db);
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $page = $_GET['page'] ?? null;
        $section = $_GET['section'] ?? null;
        $key = $_GET['key'] ?? null;

        if (!$page) {
            Response::success($content->getAll());
        } elseif ($key && $section) {
            $val = $content->get($page, $section, $key);
            Response::success(['value' => $val]);
        } elseif ($section) {
            Response::success($content->get($page, $section));
        } else {
            Response::success($content->get($page));
        }
        break;

    case 'POST':
        Middleware::auth('content_update');
        $data = Middleware::getJsonInput();
        $ok = $content->set($data->page, $data->section, $data->key_name, $data->value, $data->type ?? 'text', $data->sort_order ?? 0);
        if ($ok) {
            AuditLogger::log('update', 'site_content', null, "Content updated: {$data->page}/{$data->section}/{$data->key_name}");
            Response::success(null, 'Content updated.');
        } else {
            Response::error('Update failed.', 500);
        }
        break;

    case 'DELETE':
        Middleware::auth('content_update');
        if (!isset($_GET['id'])) Response::error('ID required.', 400);
        $content->delete($_GET['id']);
        AuditLogger::log('delete', 'site_content', $_GET['id'], 'Content deleted');
        Response::success(null, 'Deleted.');
        break;
}
