<?php
class FileUploadService {
    private static $uploadDir = '';
    private static $allowedImages = ['jpg', 'jpeg', 'png', 'webp'];
    private static $allowedDocs = ['pdf', 'doc', 'docx'];
    private static $maxSize = 10 * 1024 * 1024;

    public static function init() {
        self::$uploadDir = __DIR__ . '/../../uploads';
        if (!is_dir(self::$uploadDir)) mkdir(self::$uploadDir, 0755, true);
        foreach (['properties', 'avatars', 'blog', 'documents'] as $sub) {
            $p = self::$uploadDir . '/' . $sub;
            if (!is_dir($p)) mkdir($p, 0755, true);
        }
        $htaccess = self::$uploadDir . '/.htaccess';
        if (!file_exists($htaccess)) {
            file_put_contents($htaccess, "Options -Indexes\n<FilesMatch \"\.php$\">\n  Deny from all\n</FilesMatch>");
        }
    }

    public static function upload($file, $subdir = 'properties') {
        self::init();
        if ($file['error'] !== UPLOAD_ERR_OK) return ['error' => 'Upload failed: code ' . $file['error']];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $isImage = in_array($ext, self::$allowedImages);
        $isDoc = in_array($ext, self::$allowedDocs);
        if (!$isImage && !$isDoc) return ['error' => "File type .$ext not allowed. Allowed: " . implode(', ', array_merge(self::$allowedImages, self::$allowedDocs))];
        if ($file['size'] > self::$maxSize) return ['error' => 'File too large. Max 10MB.'];
        $hash = bin2hex(random_bytes(8));
        $filename = $hash . '.' . $ext;
        $dest = self::$uploadDir . '/' . $subdir . '/' . $filename;
        if (!move_uploaded_file($file['tmp_name'], $dest)) return ['error' => 'Failed to save file.'];
        return ['url' => 'uploads/' . $subdir . '/' . $filename, 'filename' => $filename, 'path' => $dest];
    }

    public static function delete($path) {
        $uploadDir = self::$uploadDir ?: __DIR__ . '/../../uploads';
        $full = realpath($uploadDir . '/' . ltrim($path, '/'));
        $realUploadDir = realpath($uploadDir);
        if ($full === false || $realUploadDir === false || strpos($full, $realUploadDir) !== 0) {
            return false;
        }
        if (file_exists($full)) unlink($full);
        return true;
    }
}
