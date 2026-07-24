<?php
// get_user_wishlist.php
session_start();
require_once 'db_connect.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

try {
    // گرفتن اطلاعات کامل ماشین‌هایی که این کاربر لایک کرده
    $stmt = $pdo->prepare("
        SELECT p.* 
        FROM wishlist w 
        JOIN products p ON w.product_id = p.id 
        WHERE w.user_id = ? 
        ORDER BY w.created_at DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $wishlist = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'wishlist' => $wishlist]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
?>