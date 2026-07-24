<?php
$host = 'localhost';
$dbname = 'car';        // your database name
$username = 'root'; // your MySQL user name
$password = '';       // your MySQL password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("SET NAMES 'utf8mb4'");
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
