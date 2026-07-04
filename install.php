<?php
if (php_sapi_name() !== 'cli' && (!isset($_SERVER['HTTP_HOST']) || strpos($_SERVER['HTTP_HOST'], 'localhost') === false)) {
    http_response_code(403);
    echo "Access denied.";
    exit;
}

$host = "localhost";
$username = "root";
$password = "";

try {
    $conn = new PDO("mysql:host=$host", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->exec("DROP DATABASE IF EXISTS aura_estates");
    $sql = file_get_contents(__DIR__ . "/database.sql");
    $conn->exec($sql);
    echo "Database created successfully.";
} catch(PDOException $e) {
    error_log("Install error: " . $e->getMessage());
    echo "Error: Unable to create database.";
}
?>
