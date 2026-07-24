<?php
// api_get_product.php
// این فایل اطلاعات زنده و لحظه‌ای یک ماشین را از دیتابیس می‌گیرد و می‌فرستد

require_once 'db_connect.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'آیدی نامعتبر است.']);
    exit();
}

$id = (int)$_GET['id'];

try {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($product) {
        echo json_encode(['success' => true, 'product' => $product]);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'این خودرو از سیستم حذف شده است.']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'خطای سرور در دریافت اطلاعات.']);
}
?>