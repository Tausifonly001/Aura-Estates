<?php
require_once __DIR__ . '/CsrfProtection.php';
require_once __DIR__ . '/RateLimiter.php';
require_once __DIR__ . '/Response.php';
require_once __DIR__ . '/Validator.php';
require_once __DIR__ . '/../config/auth.php';

class Middleware {
    private static $csrfExemptPaths = [
        '/api/auth',
        '/api/auth.php',
        '/api/password-reset',
        '/api/password-reset.php',
        '/api/inquiry',
        '/api/inquiry.php',
    ];

    private static function isCsrfExempt($endpoint) {
        foreach (self::$csrfExemptPaths as $path) {
            if (strpos($endpoint, $path) !== false) {
                return true;
            }
        }
        return false;
    }

    public static function cors() {
        $allowedOrigins = [
            'http://localhost/aura-estates',
            'http://localhost:3000',
            'http://localhost:8080',
        ];
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        if (in_array($origin, $allowedOrigins)) {
            header("Access-Control-Allow-Origin: $origin");
        }
        header("Content-Type: application/json; charset=UTF-8");
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
        header("Access-Control-Max-Age: 86400");
        header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-CSRF-TOKEN");
        header("Access-Control-Allow-Credentials: true");
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(204);
            exit;
        }
    }

    public static function api() {
        self::cors();
        $endpoint = parse_url($_SERVER['REQUEST_URI'] ?? 'api', PHP_URL_PATH);
        RateLimiter::check($endpoint, 120, 60);
        if (in_array($_SERVER['REQUEST_METHOD'], ['POST', 'PUT', 'DELETE'])) {
            if (strpos($endpoint, '/api/') !== false && !self::isCsrfExempt($endpoint)) {
                CsrfProtection::validateOrFail();
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
        if (($_SESSION['user_id'] ?? null) !== $targetUserId) {
            self::auth($permission);
        }
    }

    public static function getJsonInput() {
        $input = file_get_contents("php://input");
        if (strlen($input) > 1048576) {
            Response::error('Request payload too large. Max 1MB.', 413);
        }
        $data = json_decode($input);
        if (!$data && $_SERVER['REQUEST_METHOD'] !== 'GET') {
            Response::error('Invalid JSON input.', 400);
        }
        return $data;
    }

    public static function getQueryParams() {
        $allowedSorts = ['created_at', 'updated_at', 'name', 'email', 'title', 'price', 'status', 'priority'];
        $sort = $_GET['sort'] ?? 'created_at';
        if (!in_array($sort, $allowedSorts)) {
            $sort = 'created_at';
        }
        return [
            'page' => max(1, (int)($_GET['page'] ?? 1)),
            'per_page' => min(100, max(1, (int)($_GET['per_page'] ?? 50))),
            'search' => trim($_GET['search'] ?? ''),
            'sort' => $sort,
            'order' => strtoupper($_GET['order'] ?? 'DESC') === 'ASC' ? 'ASC' : 'DESC',
            'filter' => $_GET['filter'] ?? []
        ];
    }
}
