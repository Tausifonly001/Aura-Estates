<?php
$db_host = getenv('DB_HOST') ?: 'mysql.railway.internal';
$db_name = getenv('DB_NAME') ?: 'railway';
$db_user = getenv('DB_USER') ?: 'root';
$db_pass = getenv('DB_PASSWORD') ?: '';
$db_port = getenv('DB_PORT') ?: '3306';

$max_retries = 15;
$retry = 0;

while ($retry < $max_retries) {
    try {
        $pdo = new PDO("mysql:host=$db_host;port=$db_port;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
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

$stmt = $pdo->query("SELECT COUNT(*) as cnt FROM information_schema.tables WHERE table_schema = " . $pdo->quote($db_name));
$row = $stmt->fetch();

if ((int)$row['cnt'] > 0) {
    exit(0);
}

$sql_path = __DIR__ . '/database.sql';
if (!file_exists($sql_path)) {
    fwrite(STDERR, "SQL file not found: $sql_path\n");
    exit(1);
}

$sql = file_get_contents($sql_path);

$sql = preg_replace('/^CREATE DATABASE\s+.*?;/im', '', $sql);
$sql = preg_replace('/^USE\s+.*?;/im', '', $sql);
$sql = trim($sql);

$statements = array_filter(
    array_map('trim', explode(';', $sql)),
    fn($s) => !empty($s)
);

foreach ($statements as $statement) {
    try {
        $pdo->exec($statement);
    } catch (PDOException $e) {
        fwrite(STDERR, "SQL error: {$e->getMessage()}\nSQL: $statement\n");
        exit(1);
    }
}

fwrite(STDOUT, "Database migrated successfully.\n");
