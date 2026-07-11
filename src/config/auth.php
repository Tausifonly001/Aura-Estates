<?php
require_once __DIR__ . '/../core/RateLimiter.php';
require_once __DIR__ . '/../core/CsrfProtection.php';

class Auth {
    private static $db = null;

    private static function getDB() {
        if (self::$db === null) {
            include_once __DIR__ . '/database.php';
            $database = new Database();
            self::$db = $database->getConnection();
        }
        return self::$db;
    }

    private static function getClientIp(): string {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '';
        if (strpos($ip, ',') !== false) $ip = trim(explode(',', $ip)[0]);
        return $ip;
    }

    private static function clearSessionCookie() {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 3600, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }

    public static function startSession() {
        if (session_status() === PHP_SESSION_NONE) {
            $isSecure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
            $isSecure = $isSecure || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
            session_set_cookie_params([
                'httponly' => true,
                'secure' => $isSecure,
                'samesite' => 'Strict'
            ]);
            session_start();
        }

        $inactive = time() - ($_SESSION['_last_activity'] ?? time());
        if ($inactive > 7200) {
            self::destroySessionRecord();
            $_SESSION = [];
            self::clearSessionCookie();
            session_destroy();
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
        }

        $_SESSION['_last_activity'] = time();
    }

    public static function establishSession(array $row) {
        self::startSession();
        session_regenerate_id(true);

        $ip = self::getClientIp();
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['user_name'] = $row['name'];
        $_SESSION['user_email'] = $row['email'];
        $_SESSION['role'] = $row['role'];
        $_SESSION['user_role'] = $row['role'];
        $_SESSION['role_id'] = $row['role_id'] ?? null;
        $_SESSION['_fingerprint'] = hash('sha256', ($_SERVER['HTTP_USER_AGENT'] ?? '') . '|' . $ip);
        $_SESSION['_last_activity'] = time();

        CsrfProtection::refresh();
        self::trackSession((int)$row['id']);
    }

    private static function trackSession(int $userId) {
        try {
            $db = self::getDB();
            $sessionId = session_id();
            if (!$sessionId) {
                return;
            }

            $stmt = $db->prepare('DELETE FROM sessions WHERE session_id = ?');
            $stmt->execute([$sessionId]);

            $stmt = $db->prepare('INSERT INTO sessions (user_id, session_id, ip_address, user_agent) VALUES (?, ?, ?, ?)');
            $stmt->execute([
                $userId,
                $sessionId,
                self::getClientIp(),
                substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500)
            ]);
        } catch (Throwable $e) {
            // sessions table may not exist yet on older installs
        }
    }

    private static function destroySessionRecord() {
        try {
            $db = self::getDB();
            $sessionId = session_id();
            if ($sessionId) {
                $stmt = $db->prepare('DELETE FROM sessions WHERE session_id = ?');
                $stmt->execute([$sessionId]);
            }
        } catch (Throwable $e) {
        }
    }

    public static function destroyUserSessions(int $userId) {
        try {
            $db = self::getDB();
            $stmt = $db->prepare('DELETE FROM sessions WHERE user_id = ?');
            $stmt->execute([$userId]);
        } catch (Throwable $e) {
        }
    }

    private static function isSessionRecordValid(int $userId): bool {
        try {
            $db = self::getDB();
            $sessionId = session_id();
            if (!$sessionId) {
                return false;
            }

            $stmt = $db->prepare('SELECT id FROM sessions WHERE session_id = ? AND user_id = ?');
            $stmt->execute([$sessionId, $userId]);
            if ($stmt->fetchColumn()) {
                $db->prepare('UPDATE sessions SET last_activity = CURRENT_TIMESTAMP WHERE session_id = ?')
                    ->execute([$sessionId]);
                return true;
            }

            $countStmt = $db->query('SELECT COUNT(*) FROM sessions');
            if ((int)$countStmt->fetchColumn() === 0) {
                self::trackSession($userId);
                return true;
            }

            return false;
        } catch (Throwable $e) {
            return true;
        }
    }

    public static function getUser() {
        self::startSession();
        if (isset($_SESSION['user_id'])) {
            $expectedFingerprint = hash('sha256', ($_SERVER['HTTP_USER_AGENT'] ?? '') . '|' . self::getClientIp());
            if (!isset($_SESSION['_fingerprint']) || $_SESSION['_fingerprint'] !== $expectedFingerprint) {
                self::destroySessionRecord();
                $_SESSION = [];
                self::clearSessionCookie();
                session_destroy();
                return null;
            }

            if (!self::isSessionRecordValid((int)$_SESSION['user_id'])) {
                self::destroySessionRecord();
                $_SESSION = [];
                self::clearSessionCookie();
                session_destroy();
                return null;
            }

            return [
                'id' => $_SESSION['user_id'],
                'name' => $_SESSION['user_name'] ?? '',
                'email' => $_SESSION['user_email'] ?? '',
                'role' => $_SESSION['role'] ?? $_SESSION['user_role'] ?? '',
                'role_id' => $_SESSION['role_id'] ?? null
            ];
        }
        return null;
    }

    public static function isAuthenticated() {
        $user = self::getUser();
        return $user !== null;
    }

    public static function getDashboardUrl($role = null) {
        $role = $role ?? ($_SESSION['role'] ?? $_SESSION['user_role'] ?? '');
        $prefix = self::getBasePrefix();
        if (in_array($role, ['admin', 'property_manager', 'maintenance_staff'], true)) {
            return $prefix . '/admin/dashboard';
        }
        return $prefix . '/user/dashboard';
    }

    public static function requireAuth() {
        self::startSession();
        if (!self::isAuthenticated()) {
            http_response_code(401);
            if (self::isAjaxRequest()) {
                echo json_encode(['message' => 'Unauthorized.', 'authenticated' => false]);
            } else {
                header('Location: ' . self::getBasePrefix() . '/login');
            }
            exit;
        }
    }

    public static function requireRole($role) {
        self::startSession();
        $user = self::getUser();
        if (!$user) {
            self::redirectOrJson(401, 'Unauthorized.');
        }
        $roles = is_array($role) ? $role : [$role];
        if (!in_array($user['role'], $roles, true)) {
            self::redirectOrJson(403, 'Forbidden. Insufficient permissions.');
        }
    }

    public static function requireStaff() {
        self::requireRole(['admin', 'property_manager', 'maintenance_staff']);
    }

    public static function requirePermission($permission) {
        self::startSession();
        $user = self::getUser();
        if (!$user) {
            self::redirectOrJson(401, 'Unauthorized.');
        }
        if (!self::hasPermission($user['id'], $permission)) {
            self::redirectOrJson(403, 'Forbidden. Missing permission: ' . $permission);
        }
    }

    public static function hasPermission($userId, $permissionName) {
        $db = self::getDB();
        $stmt = $db->prepare('SELECT COUNT(*) FROM user_permissions
                              WHERE user_id = ? AND permission_name = ?');
        $stmt->execute([$userId, $permissionName]);
        return $stmt->fetchColumn() > 0;
    }

    public static function login($email, $password, $redirect = null) {
        self::startSession();

        if (!RateLimiter::checkLoginAttempts($email)) {
            return false;
        }

        try {
            $db = self::getDB();
            $stmt = $db->prepare('SELECT id, name, email, password, role, role_id FROM users WHERE email = ?');
            $stmt->execute([$email]);

            if ($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if (password_verify($password, $row['password'])) {
                    self::establishSession($row);

                    if ($redirect && !self::isAjaxRequest()) {
                        header('Location: ' . $redirect);
                        exit;
                    }
                    return $row;
                }
            }
        } catch (Throwable $e) {
            error_log('Auth login error: ' . $e->getMessage());
            return false;
        }

        return false;
    }

    public static function logout($redirect = null) {
        self::startSession();
        self::destroySessionRecord();
        $_SESSION = [];
        self::clearSessionCookie();
        session_destroy();
        if ($redirect) {
            header('Location: ' . $redirect);
            exit;
        }
    }

    public static function isAjaxRequest() {
        return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') ||
               !empty($_GET['ajax']) ||
               (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);
    }

    private static function redirectOrJson($code, $message) {
        http_response_code($code);
        if (self::isAjaxRequest()) {
            echo json_encode(['message' => $message]);
        } else {
            self::startSession();
            $_SESSION['error'] = $message;
            $back = self::getBasePrefix() . '/login';
            if (!empty($_SERVER['HTTP_REFERER'])) {
                $parsed = parse_url($_SERVER['HTTP_REFERER']);
                if ($parsed !== false && isset($parsed['host']) && $parsed['host'] === ($_SERVER['HTTP_HOST'] ?? '')) {
                    $back = $_SERVER['HTTP_REFERER'];
                }
            }
            header('Location: ' . $back);
        }
        exit;
    }

    public static function getUserPermissions($userId) {
        $db = self::getDB();
        $stmt = $db->prepare('SELECT DISTINCT p.name as permission_name, p.module
                              FROM user_permissions up
                              JOIN permissions p ON up.permission_name = p.name
                              WHERE up.user_id = ?');
        $stmt->execute([$userId]);
        $perms = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $perms[] = $row;
        }
        return $perms;
    }

    public static function getUserRoleDisplay($userId) {
        $db = self::getDB();
        $stmt = $db->prepare('SELECT r.display_name FROM roles r
                              JOIN users u ON u.role_id = r.id
                              WHERE u.id = ?');
        $stmt->execute([$userId]);
        return $stmt->fetchColumn() ?: 'User';
    }

    public static function getAllRoles() {
        $db = self::getDB();
        $stmt = $db->query('SELECT * FROM roles ORDER BY display_name');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getAllPermissions() {
        $db = self::getDB();
        $stmt = $db->query('SELECT * FROM permissions ORDER BY module, display_name');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function validatePassword($password) {
        if (strlen($password) < 8) {
            return false;
        }
        if (!preg_match('/[A-Z]/', $password)) {
            return false;
        }
        if (!preg_match('/[a-z]/', $password)) {
            return false;
        }
        if (!preg_match('/[0-9]/', $password)) {
            return false;
        }
        return true;
    }

    public static function getPasswordRequirements() {
        return 'Password must be at least 8 characters long and contain at least one uppercase letter, one lowercase letter, and one digit.';
    }

    public static function getBasePrefix() {
        static $prefix = null;
        if ($prefix === null) {
            $docRoot = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
            $projectRoot = str_replace('\\', '/', dirname(__DIR__, 2));
            $basePath = str_replace($docRoot, '', $projectRoot);
            $prefix = rtrim($basePath, '/');
        }
        return $prefix;
    }
}
