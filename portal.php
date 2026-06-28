<?php
require_once __DIR__ . '/src/config/auth.php';
Auth::startSession();
if(isset($_SESSION['user_id'])) {
    $role = $_SESSION['role'] ?? $_SESSION['user_role'] ?? '';
    if ($role === 'admin') {
    header("Location: /admin/dashboard");
} elseif (in_array($_SESSION['role'], ['tenant', 'user', 'maintenance_staff', 'property_manager'])) {
    header("Location: /user/dashboard");
} else {
    header("Location: /user/dashboard");
}
} else {
header("Location: /login");
}
exit;
