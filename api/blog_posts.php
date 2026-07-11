<?php
require_once __DIR__ . '/../src/core/Middleware.php';
require_once __DIR__ . '/../src/config/database.php';
require_once __DIR__ . '/../src/config/auth.php';
require_once __DIR__ . '/../src/core/AuditLogger.php';
require_once __DIR__ . '/../src/controllers/BlogPostController.php';

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
$controller = new BlogPostController($db);
$blogPost = new BlogPost($db);

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        $params = Middleware::getQueryParams();
        $user = Auth::getUser();
        $canViewAll = $user && Auth::hasPermission($user['id'], 'blog_view');

        if (isset($_GET['slug'])) {
            $result = json_decode($controller->getBySlug($_GET['slug']), true);
            if (!empty($result['id'])) {
                if ($result['status'] === 'draft' && !$canViewAll) {
                    Response::error('Not found.', 404);
                }
                Response::success($result);
            } else {
                Response::error('Not found.', 404);
            }
        } elseif (isset($_GET['id'])) {
            $result = json_decode($controller->getOne($_GET['id']), true);
            if (!empty($result['id'])) {
                if ($result['status'] === 'draft' && !$canViewAll) {
                    Response::error('Not found.', 404);
                }
                Response::success($result);
            } else {
                Response::error('Not found.', 404);
            }
        } else {
            if (!$canViewAll) {
                $blogPost->statusFilter = 'published';
            }
            $result = $blogPost->readPaginated($params);
            Response::paginated($result['records'], $result['pagination']['total'], $params['page'], $params['per_page']);
        }
        break;

    case 'POST':
        Middleware::auth('blog_create');
        $data = Middleware::getJsonInput();

        $validator = new Validator($data);
        $validator->required('title', 'Title')
            ->required('slug', 'Slug');
        if (!$validator->passes()) {
            Response::error('Validation failed.', 422, $validator->errors());
        }

        $output = $controller->create($data);
        $code = http_response_code();
        $result = json_decode($output, true);
        if ($code >= 200 && $code < 300) {
            AuditLogger::log('create', 'blog_post', $db->lastInsertId(), 'Blog post created', null, (array)$data);
            Response::success(null, $result['message'] ?? 'Blog post created.', $code);
        } else {
            Response::error($result['message'] ?? 'Unable to create blog post.', $code);
        }
        break;

    case 'PUT':
        Middleware::auth('blog_edit');
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
            AuditLogger::log('update', 'blog_post', $data->id ?? null, 'Blog post updated', null, (array)$data);
            Response::success(null, $result['message'] ?? 'Blog post updated.', $code);
        } else {
            Response::error($result['message'] ?? 'Unable to update blog post.', $code);
        }
        break;

    case 'DELETE':
        Middleware::auth('blog_delete');
        if (isset($_GET['id'])) {
            $output = $controller->delete($_GET['id']);
            $code = http_response_code();
            $result = json_decode($output, true);
            if ($code >= 200 && $code < 300) {
                AuditLogger::log('delete', 'blog_post', $_GET['id'], 'Blog post deleted');
                Response::success(null, $result['message'] ?? 'Blog post deleted.', $code);
            } else {
                Response::error($result['message'] ?? 'Unable to delete blog post.', $code);
            }
        }
        break;
}
