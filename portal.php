<?php
require_once __DIR__ . '/src/config/auth.php';
Auth::startSession();
if(isset($_SESSION['user_id'])) {
    $role = $_SESSION['role'] ?? $_SESSION['user_role'] ?? '';
    if ($role === 'admin') {
        header("Location: admin/dashboard.php");
    } elseif (in_array($role, ['user', 'tenant'])) {
        header("Location: user/dashboard.php");
    } else {
        header("Location: user/dashboard.php");
    }
} else {
    header("Location: login.php");
}
exit;
