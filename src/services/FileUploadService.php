<?php
class FileUploadService {
    private static $uploadDir = '';
    private static $allowedImages = ['jpg', 'jpeg', 'png', 'webp'];
    private static $allowedDocs = ['pdf', 'doc', 'docx'];
    private static $maxSize = 10 * 1024 * 1024;
    private static $allowedMimeTypes = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'webp' => 'image/webp',
        'pdf' => 'application/pdf',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    ];

    private static function getDb() {
        require_once __DIR__ . '/../config/database.php';
        $db = (new Database())->getConnection();
        return $db;
    }

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

    public static function upload($file, $subdir = 'properties', $userId = null) {
        self::init();
        if ($file['error'] !== UPLOAD_ERR_OK) return ['error' => 'Upload failed: code ' . $file['error']];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $isImage = in_array($ext, self::$allowedImages);
        $isDoc = in_array($ext, self::$allowedDocs);
        if (!$isImage && !$isDoc) return ['error' => "File type .$ext not allowed."];
        if ($file['size'] > self::$maxSize) return ['error' => 'File too large. Max 10MB.'];

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $detectedMime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        $expectedMime = self::$allowedMimeTypes[$ext] ?? null;
        if ($expectedMime && $detectedMime !== $expectedMime) {
            return ['error' => "File content type ($detectedMime) does not match extension ($ext)."];
        }

        $hash = bin2hex(random_bytes(8));
        $filename = $hash . '.' . $ext;
        $dest = self::$uploadDir . '/' . $subdir . '/' . $filename;
        if (!move_uploaded_file($file['tmp_name'], $dest)) return ['error' => 'Failed to save file.'];

        $db = self::getDb();
        $db->prepare("CREATE TABLE IF NOT EXISTS uploads (
            id SERIAL PRIMARY KEY,
            user_id INT DEFAULT NULL,
            subdir VARCHAR(50) NOT NULL,
            filename VARCHAR(255) NOT NULL,
            original_name VARCHAR(255) NOT NULL,
            mime_type VARCHAR(100) NOT NULL,
            file_size INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )")->execute();
        $stmt = $db->prepare("INSERT INTO uploads (user_id, subdir, filename, original_name, mime_type, file_size) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$userId, $subdir, $filename, $file['name'], $detectedMime, $file['size']]);
        $fileId = $db->lastInsertId();

        return ['url' => 'uploads/' . $subdir . '/' . $filename, 'filename' => $filename, 'file_id' => (int)$fileId];
    }

    public static function delete($path, $userId = null) {
        $uploadDir = self::$uploadDir ?: __DIR__ . '/../../uploads';
        $full = realpath($uploadDir . '/' . ltrim($path, '/'));
        $realUploadDir = realpath($uploadDir);
        if ($full === false || $realUploadDir === false || strpos($full, $realUploadDir) !== 0) {
            return false;
        }

        $db = self::getDb();
        $filename = basename($full);
        $stmt = $db->prepare("SELECT id, user_id FROM uploads WHERE filename = ?");
        $stmt->execute([$filename]);
        $record = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($record) {
            if ($userId && (int)$record['user_id'] !== (int)$userId) {
                return false;
            }
            $db->prepare("DELETE FROM uploads WHERE id = ?")->execute([$record['id']]);
        }

        if (file_exists($full)) unlink($full);
        return true;
    }
}
