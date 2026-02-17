<?php
// جلوگیری از نمایش خطاهای PHP در خروجی (برای سالم ماندن JSON)
error_reporting(0);
ini_set('display_errors', 0);

session_start();
require_once 'db_connect.php';

header('Content-Type: application/json; charset=utf-8');

try {
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('دسترسی غیرمجاز');
    }

    $user_id = $_SESSION['user_id'];
    
    // دریافت قراردادها
    $stmt = $pdo->prepare("SELECT id, tracking_code, car_name, car_price, status, created_at FROM contracts WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$user_id]);
    $contracts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'contracts' => $contracts]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'خطای دیتابیس: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>