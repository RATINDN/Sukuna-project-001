<?php
$host = 'localhost';
$dbname = 'ratindnir_car';        // your database name
$username = 'ratindnir'; // your MySQL user name
$password = '7vqpM0v?9FaQ2emda';       // your MySQL password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("SET NAMES 'utf8'");
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
