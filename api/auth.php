<?php
require_once __DIR__ . '/../src/core/Middleware.php';
require_once __DIR__ . '/../src/config/database.php';
require_once __DIR__ . '/../src/config/auth.php';
require_once __DIR__ . '/../src/core/AuditLogger.php';

Middleware::api();

$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        if (isset($_GET['action']) && $_GET['action'] === 'me') {
            Auth::startSession();
            if (isset($_SESSION['user_id'])) {
                $token = $_SESSION['_csrf_token'] ?? CsrfProtection::generate();
                Response::success([
                    'user' => [
                        'id' => $_SESSION['user_id'],
                        'name' => $_SESSION['user_name'],
                        'email' => $_SESSION['user_email'],
                        'role' => $_SESSION['role']
                    ],
                    '_csrf_token' => $token
                ]);
            } else {
                Response::success(['authenticated' => false, 'user' => null]);
            }
        }
        break;

    case 'POST':
        $data = Middleware::getJsonInput();
        $action = $_GET['action'] ?? '';

        if ($action === 'register') {
            $validator = new Validator($data);
            $validator->required('name', 'Name')
                ->required('email', 'Email')
                ->email('email')
                ->required('password', 'Password')
                ->minLength('password', 8);
            if (!$validator->passes()) {
                Response::error('Validation failed.', 422, $validator->errors());
            }

            $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$data->email]);
            if ($stmt->rowCount() > 0) {
                Response::error('Email already registered.', 409);
            }

            $roleStmt = $db->prepare("SELECT id FROM roles WHERE name = 'tenant'");
            $roleStmt->execute();
            $tenantRoleId = $roleStmt->fetchColumn();

            $hash = password_hash($data->password, PASSWORD_BCRYPT);
            $stmt = $db->prepare("INSERT INTO users (name, email, password, role, role_id) VALUES (?, ?, ?, 'tenant', ?)");
            if ($stmt->execute([$data->name, $data->email, $hash, $tenantRoleId])) {
                $uid = $db->lastInsertId();
                Auth::startSession();
                $_SESSION['user_id'] = $uid;
                $_SESSION['user_name'] = $data->name;
                $_SESSION['user_email'] = $data->email;
                $_SESSION['role'] = 'tenant';
                $_SESSION['user_role'] = 'tenant';
                $_SESSION['role_id'] = $tenantRoleId;

                AuditLogger::log('create', 'user', $uid, "User registered: {$data->email}");
                Response::success(['user_id' => $uid], 'Registration successful.', 201);
            } else {
                Response::error('Registration failed.', 503);
            }
        }

        if ($action === 'login') {
            $validator = new Validator($data);
            $validator->required('email', 'Email')->required('password', 'Password');
            if (!$validator->passes()) {
                Response::error('Validation failed.', 422, $validator->errors());
            }

            $user = Auth::login($data->email, $data->password);
            if ($user) {
                Auth::startSession();
                $token = $_SESSION['_csrf_token'] ?? CsrfProtection::generate();
                Response::success([
                    'user' => [
                        'id' => $user['id'],
                        'name' => $user['name'],
                        'email' => $user['email'],
                        'role' => $user['role']
                    ],
                    '_csrf_token' => $token
                ], 'Login successful.');
            } else {
                Response::error('Invalid credentials.', 401);
            }
        }

        if ($action === 'logout') {
            Auth::startSession();
            $_SESSION = [];
            session_destroy();
            Response::success(null, 'Logged out.');
        }
        break;
}
