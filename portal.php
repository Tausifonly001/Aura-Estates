<?php
require_once __DIR__ . '/src/config/auth.php';
Auth::startSession();

// Calculate base path prefix dynamically
$docRoot = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
$projectRoot = str_replace('\\', '/', __DIR__);
$basePath = str_replace($docRoot, '', $projectRoot);
$basePrefix = rtrim($basePath, '/');

if(isset($_SESSION['user_id'])) {
    $role = $_SESSION['role'] ?? $_SESSION['user_role'] ?? '';
    if ($role === 'admin') {
        header("Location: " . $basePrefix . "/admin/dashboard");
    } elseif (in_array($_SESSION['role'], ['tenant', 'user', 'maintenance_staff', 'property_manager'])) {
        header("Location: " . $basePrefix . "/user/dashboard");
    } else {
        header("Location: " . $basePrefix . "/user/dashboard");
    }
} else {
    header("Location: " . $basePrefix . "/login");
}
exit;
