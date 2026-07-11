<?php
require_once __DIR__ . '/../src/core/Middleware.php';
require_once __DIR__ . '/../src/config/database.php';
require_once __DIR__ . '/../src/config/auth.php';
require_once __DIR__ . '/../src/core/AuditLogger.php';

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

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        if (isset($_GET['action']) && $_GET['action'] === 'me') {
            Auth::startSession();
            $user = Auth::getUser();
            if ($user) {
                $token = $_SESSION['_csrf_token'] ?? CsrfProtection::generate();
                Response::success([
                    'authenticated' => true,
                    'user' => $user,
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

            if (!Auth::validatePassword($data->password)) {
                Response::error(Auth::getPasswordRequirements(), 422);
            }

            $stmt = $db->prepare('SELECT id FROM users WHERE email = ?');
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
                $uid = (int)$db->lastInsertId();
                Auth::establishSession([
                    'id' => $uid,
                    'name' => $data->name,
                    'email' => $data->email,
                    'role' => 'tenant',
                    'role_id' => $tenantRoleId
                ]);

                AuditLogger::log('create', 'user', $uid, "User registered: {$data->email}");
                Response::success([
                    'user_id' => $uid,
                    'user' => [
                        'id' => $uid,
                        'name' => $data->name,
                        'email' => $data->email,
                        'role' => 'tenant'
                    ],
                    '_csrf_token' => $_SESSION['_csrf_token'] ?? CsrfProtection::generate()
                ], 'Registration successful.', 201);
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
                AuditLogger::log('login', 'user', $user['id'], "User logged in: {$user['email']}");
                Response::success([
                    'authenticated' => true,
                    'user' => [
                        'id' => $user['id'],
                        'name' => $user['name'],
                        'email' => $user['email'],
                        'role' => $user['role']
                    ],
                    '_csrf_token' => $_SESSION['_csrf_token'] ?? CsrfProtection::generate()
                ], 'Login successful.');
            } else {
                Response::error('Invalid credentials.', 401);
            }
        }

        if ($action === 'logout') {
            $user = Auth::getUser();
            if ($user) {
                AuditLogger::log('logout', 'user', $user['id'], "User logged out: {$user['email']}");
            }
            Auth::logout();
            Response::success(null, 'Logged out.');
        }
        break;
}
