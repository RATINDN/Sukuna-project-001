<?php
// api_wishlist.php
session_start();
require_once 'db_connect.php';

// تنظیم هدر برای پاسخ JSON
header('Content-Type: application/json; charset=utf-8');

// بررسی لاگین بودن کاربر
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'لطفا ابتدا وارد حساب کاربری شوید.']);
    exit();
}

$user_id = $_SESSION['user_id'];

// دریافت دیتای ارسال شده توسط جاوااسکریپت (Fetch)
$data = json_decode(file_get_contents('php://input'), true);
$product_id = isset($data['product_id']) ? (int)$data['product_id'] : 0;

if (!$product_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'شناسه محصول نامعتبر است.']);
    exit();
}

try {
    // بررسی می‌کنیم آیا کاربر قبلاً این ماشین را لایک کرده؟
    $stmt = $pdo->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user_id, $product_id]);
    $exists = $stmt->fetch();

    if ($exists) {
        // اگر قبلاً لایک کرده، پس الان می‌خواد حذفش کنه (آنلایک)
        $delStmt = $pdo->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
        $delStmt->execute([$user_id, $product_id]);
        
        echo json_encode(['success' => true, 'is_favorited' => false]);
    } else {
        // اگر لایک نکرده، به لیست علاقه‌مندی‌ها اضافه‌اش می‌کنیم
        $insStmt = $pdo->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)");
        $insStmt->execute([$user_id, $product_id]);
        
        echo json_encode(['success' => true, 'is_favorited' => true]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'خطای سرور: ' . $e->getMessage()]);
}
?>