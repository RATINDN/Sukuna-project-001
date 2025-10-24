<?php
require_once 'db_connect.php';

$type = isset($_GET['type']) ? $_GET['type'] : '';
$value = isset($_GET['value']) ? trim($_GET['value']) : '';

$result = ['exists' => false];

if ($type === 'email') {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM car WHERE email = ?");
    $stmt->execute([$value]);
    $result['exists'] = $stmt->fetchColumn() > 0;
} elseif ($type === 'phone') {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM car WHERE phone = ?");
    $stmt->execute([$value]);
    $result['exists'] = $stmt->fetchColumn() > 0;
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($result);
exit;
