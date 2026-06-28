<?php
require_once __DIR__ . '/CsrfProtection.php';
require_once __DIR__ . '/RateLimiter.php';
require_once __DIR__ . '/Response.php';
require_once __DIR__ . '/Validator.php';
require_once __DIR__ . '/../config/auth.php';

class Middleware {
    public static function cors() {
        header("Access-Control-Allow-Origin: *");
        header("Content-Type: application/json; charset=UTF-8");
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
        header("Access-Control-Max-Age: 86400");
        header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-CSRF-TOKEN");
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(204);
            exit;
        }
    }

    public static function api() {
        self::cors();
        RateLimiter::check($_SERVER['REQUEST_URI'] ?? 'api', 120, 60);
        if (in_array($_SERVER['REQUEST_METHOD'], ['POST', 'PUT', 'DELETE'])) {
            if (strpos($_SERVER['REQUEST_URI'] ?? '', '/api/') !== false) {
                CsrfProtection::validate();
            }
        }
    }

    public static function auth($permission = null) {
        Auth::startSession();
        if (!Auth::isAuthenticated()) {
            Response::error('Unauthorized.', 401);
        }
        if ($permission && !Auth::hasPermission($_SESSION['user_id'], $permission)) {
            Response::error('Forbidden. Missing permission: ' . $permission, 403);
        }
    }

    public static function ownerOrPermission($userIdField, $permission) {
        Auth::startSession();
        $targetUserId = $_GET[$userIdField] ?? $_REQUEST[$userIdField] ?? null;
        if ($_SESSION['user_id'] != $targetUserId) {
            self::auth($permission);
        }
    }

    public static function getJsonInput() {
        $data = json_decode(file_get_contents("php://input"));
        if (!$data && $_SERVER['REQUEST_METHOD'] !== 'GET') {
            Response::error('Invalid JSON input.', 400);
        }
        return $data;
    }

    public static function getQueryParams() {
        return [
            'page' => max(1, (int)($_GET['page'] ?? 1)),
            'per_page' => min(100, max(1, (int)($_GET['per_page'] ?? 20))),
            'search' => trim($_GET['search'] ?? ''),
            'sort' => $_GET['sort'] ?? 'created_at',
            'order' => strtoupper($_GET['order'] ?? 'DESC') === 'ASC' ? 'ASC' : 'DESC',
            'filter' => $_GET['filter'] ?? []
        ];
    }
}
