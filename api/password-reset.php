<?php
require_once __DIR__ . '/../src/core/Middleware.php';
require_once __DIR__ . '/../src/core/AuditLogger.php';
require_once __DIR__ . '/../src/config/database.php';
require_once __DIR__ . '/../src/services/ResendService.php';

Middleware::api();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST':
        $data = Middleware::getJsonInput();
        $action = $_GET['action'] ?? '';

        if ($action === 'forgot') {
            $validator = new Validator($data);
            $validator->required('email')->email('email');
            if (!$validator->passes()) Response::error('Valid email required.', 422, $validator->errors());

            try {
                $db = (new Database())->getConnection();
            } catch (Exception $e) {
                http_response_code(503);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'System temporarily unavailable. Please try again later.']);
                exit;
            }
            $stmt = $db->prepare("SELECT id, name, email FROM users WHERE email = ?");
            $stmt->execute([$data->email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                $rawToken = bin2hex(random_bytes(32));
                $hashedToken = hash('sha256', $rawToken);
                $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
                $db->prepare("DELETE FROM password_resets WHERE email = ?")->execute([$data->email]);
                $db->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)")->execute([$data->email, $hashedToken, $expires]);

                $sent = ResendService::sendPasswordReset($data->email, $rawToken, $user['name']);
                AuditLogger::log('password_reset_request', 'user', $user['id'], "Password reset requested for {$data->email}");
            }
            Response::success(null, 'If the email exists, a reset link has been sent.');
        }

        if ($action === 'reset') {
            $validator = new Validator($data);
            $validator->required('token')->required('password')->minLength('password', 8);
            if (!$validator->passes()) Response::error('Validation failed.', 422, $validator->errors());

            if (!Auth::validatePassword($data->password)) {
                Response::error(Auth::getPasswordRequirements(), 422);
            }

            try {
                $db = (new Database())->getConnection();
            } catch (Exception $e) {
                http_response_code(503);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'System temporarily unavailable. Please try again later.']);
                exit;
            }
            $hashedToken = hash('sha256', $data->token);
            $stmt = $db->prepare("SELECT email FROM password_resets WHERE token = ? AND expires_at > NOW() AND used = 0");
            $stmt->execute([$hashedToken]);
            $reset = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$reset) Response::error('Invalid or expired token.', 400);

            $hash = password_hash($data->password, PASSWORD_BCRYPT);
            $db->prepare("UPDATE users SET password = ? WHERE email = ?")->execute([$hash, $reset['email']]);
            $db->prepare("UPDATE password_resets SET used = 1 WHERE token = ?")->execute([$hashedToken]);

            // Revoke all existing sessions for this user
            $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$reset['email']]);
            $userId = $stmt->fetchColumn();
            if ($userId) {
                Auth::destroyUserSessions((int)$userId);
            }

            AuditLogger::log('password_reset', 'user', $userId, "Password reset completed for {$reset['email']}");
            Response::success(null, 'Password reset successful. All existing sessions have been invalidated. You can now log in.');
        }
        break;
}
