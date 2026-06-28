<?php
require_once __DIR__ . '/../src/services/SSEService.php';
require_once __DIR__ . '/../src/config/auth.php';

Auth::startSession();
$userId = $_SESSION['user_id'] ?? null;
SSEService::stream($userId);
