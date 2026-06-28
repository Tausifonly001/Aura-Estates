<?php
require_once __DIR__ . '/../src/core/Middleware.php';
require_once __DIR__ . '/../src/services/FileUploadService.php';

Middleware::api();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST':
        Middleware::auth();
        if (!isset($_FILES['file'])) Response::error('No file uploaded.', 400);
        $subdir = $_POST['subdir'] ?? 'properties';
        if (!in_array($subdir, ['properties', 'avatars', 'blog', 'documents'])) Response::error('Invalid subdirectory.', 400);
        $result = FileUploadService::upload($_FILES['file'], $subdir);
        if (isset($result['error'])) Response::error($result['error'], 400);
        Response::success($result, 'File uploaded.');
        break;

    case 'DELETE':
        Middleware::auth();
        $data = Middleware::getJsonInput();
        if (empty($data->path)) Response::error('File path required.', 400);
        FileUploadService::delete($data->path);
        Response::success(null, 'File deleted.');
        break;
}
