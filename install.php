<?php
http_response_code(403);
header('Content-Type: application/json');
echo json_encode(['success' => false, 'message' => 'This setup script is disabled. Use CLI and migrations instead.']);
exit;
