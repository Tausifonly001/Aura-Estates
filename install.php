<?php
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
    echo "Error: " . $e->getMessage();
}
?>
