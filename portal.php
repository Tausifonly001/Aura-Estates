<?php
require_once __DIR__ . '/src/config/auth.php';
Auth::startSession();

$basePrefix = Auth::getBasePrefix();

if(isset($_SESSION['user_id'])) {
    header('Location: ' . Auth::getDashboardUrl());
} else {
    header('Location: ' . $basePrefix . '/login');
}
exit;
