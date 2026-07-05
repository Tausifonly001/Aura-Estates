<?php
class CsrfProtection {
    public static function generate() {
        Auth::startSession();
        if (empty($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
            $_SESSION['_csrf_time'] = time();
        }
        return $_SESSION['_csrf_token'];
    }

    public static function token() {
        return self::generate();
    }

    public static function field() {
        return '<input type="hidden" name="_csrf_token" value="' . self::generate() . '">';
    }

    public static function validate($token = null) {
        Auth::startSession();
        if (empty($_SESSION['_csrf_token'])) {
            return false;
        }
        $token = $token ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['_csrf_token'] ?? null;
        if (!$token || !hash_equals($_SESSION['_csrf_token'], $token)) {
            return false;
        }
        if (time() - $_SESSION['_csrf_time'] > 7200) {
            return false;
        }
        return true;
    }

    public static function validateOrFail($token = null) {
        if (!self::validate($token)) {
            self::fail();
        }
    }

    public static function check() {
        if (!in_array($_SERVER['REQUEST_METHOD'], ['POST', 'PUT', 'DELETE', 'PATCH'])) return;
        $noCsrfPaths = ['/api/auth.php'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        foreach ($noCsrfPaths as $path) {
            if (strpos($uri, $path) !== false) return;
        }
        self::validateOrFail();
    }

    private static function fail($msg = 'Invalid CSRF token.') {
        http_response_code(419);
        echo json_encode(['success' => false, 'message' => $msg]);
        exit;
    }

    public static function refresh() {
        Auth::startSession();
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['_csrf_time'] = time();
    }
}
