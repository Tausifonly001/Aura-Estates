<?php
$db_url = getenv('DB_URL');
$db_host = getenv('DB_HOST') ?: 'localhost';
$db_name = getenv('DB_NAME') ?: 'aura_estates';
$db_user = getenv('DB_USER') ?: 'root';
$db_pass = getenv('DB_PASSWORD') ?: '';
$db_port = getenv('DB_PORT') ?: '5432';

$max_retries = 15;
$retry = 0;

while ($retry < $max_retries) {
    try {
        if ($db_url) {
            $pdo = new PDO($db_url, null, null, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } else {
            $pdo = new PDO("pgsql:host=$db_host;port=$db_port;dbname=$db_name", $db_user, $db_pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        }
        break;
    } catch (PDOException $e) {
        $retry++;
        if ($retry >= $max_retries) {
            fwrite(STDERR, "DB not reachable after {$max_retries} retries: {$e->getMessage()}\n");
            exit(1);
        }
        sleep(2);
    }
}

$pdo->exec("CREATE TABLE IF NOT EXISTS _migrations (
    id SERIAL PRIMARY KEY,
    filename VARCHAR(255) NOT NULL UNIQUE,
    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$applied = [];
$stmt = $pdo->query("SELECT filename FROM _migrations");
while ($row = $stmt->fetch()) {
    $applied[] = $row['filename'];
}

$migrations_dir = __DIR__ . '/migrations';
if (!is_dir($migrations_dir)) {
    $pdo = null;
    exit(0);
}

$files = glob($migrations_dir . '/*.sql');
sort($files);

$count = 0;
foreach ($files as $file) {
    $filename = basename($file);

    if (in_array($filename, $applied)) {
        continue;
    }

    $sql = file_get_contents($file);
    $sql = preg_replace('/^CREATE DATABASE\s+.*?;/im', '', $sql);
    $sql = preg_replace('/^USE\s+.*?;/im', '', $sql);
    $sql = preg_replace('/AUTO_INCREMENT/i', 'SERIAL', $sql);
    $sql = preg_replace('/`/i', '"', $sql);
    $sql = preg_replace('/INSERT IGNORE INTO/i', 'INSERT INTO', $sql);
    $sql = preg_replace('/ON DUPLICATE KEY UPDATE[^;]*;/i', ';', $sql);
    $sql = preg_replace('/ENGINE\s*=\s*\w+/i', '', $sql);
    $sql = preg_replace('/DEFAULT\s+CHARSET\s*=\s*\w+/i', '', $sql);
    $sql = preg_replace('/CHARSET\s*=\s*\w+/i', '', $sql);
    $sql = preg_replace('/BIGINT\s+UNSIGNED/i', 'BIGINT', $sql);
    $sql = preg_replace('/INT\s+UNSIGNED/i', 'INT', $sql);
    $sql = preg_replace('/ON\s+UPDATE\s+CURRENT_TIMESTAMP/i', '', $sql);
    $sql = preg_replace('/INDEX\s+\w+\s*\([^)]+\)/i', '', $sql);
    $sql = preg_replace('/,\s*\)\s*;/i', ')', $sql);
    $sql = preg_replace('/,\s* ENGINE/i', ' ENGINE', $sql);
    $sql = preg_replace("/ENUM\s*\(([^)]+)\)/i", 'VARCHAR(50)', $sql);
    $sql = preg_replace('/\bJSON\b(?!\s*B)/i', 'JSONB', $sql);
    $sql = preg_replace('/DATETIME/i', 'TIMESTAMP', $sql);
    $sql = trim($sql);

    if (empty($sql)) {
        continue;
    }

    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        fn($s) => !empty($s)
    );

    try {
        foreach ($statements as $statement) {
            $statement = trim($statement);
            if (!empty($statement)) {
                $pdo->exec($statement);
            }
        }
        $stmtIns = $pdo->prepare("INSERT INTO _migrations (filename) VALUES (?)");
        $stmtIns->execute([$filename]);
        $count++;
        fwrite(STDOUT, "Applied migration: $filename\n");
    } catch (PDOException $e) {
        fwrite(STDERR, "Migration $filename failed: {$e->getMessage()}\n");
        $pdo = null;
        exit(1);
    }
}

if ($count === 0) {
    fwrite(STDOUT, "No new migrations to apply.\n");
} else {
    fwrite(STDOUT, "Applied $count migration(s) successfully.\n");
}
