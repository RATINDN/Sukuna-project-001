<?php
// تنظیمات برای نمایش خطاهای مهلک به صورت جیسون
ini_set('display_errors', 0);
error_reporting(E_ALL);

session_start();
require_once 'db_connect.php';

header('Content-Type: application/json; charset=utf-8');

try {
    // 1. بررسی لاگین
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('لطفا ابتدا وارد حساب کاربری شوید.');
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('درخواست نامعتبر');
    }

    $user_id = $_SESSION['user_id'];

    // 2. دریافت شماره تلفن از دیتابیس
    $stmtUser = $pdo->prepare("SELECT phone FROM car WHERE id = ?");
    $stmtUser->execute([$user_id]);
    $userRow = $stmtUser->fetch(PDO::FETCH_ASSOC);
    $phone = $userRow['phone'] ?? '---';

    // 3. دریافت داده‌ها
    $car_name = $_POST['car_name'] ?? '';
    $car_price = $_POST['car_price'] ?? '';
    $car_color = $_POST['car_color'] ?? '';
    $real_name = $_POST['real_name'] ?? '';
    $national_id = $_POST['national_id'] ?? '';
    $address = $_POST['address'] ?? '';
    $postal_code = $_POST['postal_code'] ?? '';
    $signature = $_POST['signature'] ?? '';

    // بررسی حجم امضا (اگر خالی بود یا خیلی کم بود)
    if (strlen($signature) < 500) {
        throw new Exception('امضا دریافت نشد یا ناقص است.');
    }

    // تولید کد پیگیری
    $tracking_code = 'LX-' . mt_rand(10000, 99999);
    $contract_text = "اینجانب $real_name متعهد به خرید خودروی $car_name به رنگ $car_color به قیمت $car_price می‌باشم.";

    // 4. ثبت در دیتابیس (دقت کن ستون‌ها دقیقاً باید در دیتابیس باشند)
    // اگر ارور "Column not found" گرفتی، یعنی یکی از این‌ها در دیتابیس نیست
    $sql = "INSERT INTO contracts 
            (user_id, tracking_code, real_name, national_id, phone, address, postal_code, car_name, car_price, car_color, contract_text, signature, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')";
    
    $stmt = $pdo->prepare($sql);
    
    $result = $stmt->execute([
        $user_id, $tracking_code, $real_name, $national_id, $phone, 
        $address, $postal_code, $car_name, $car_price, $car_color, 
        $contract_text, $signature
    ]);

    if ($result) {
        echo json_encode([
            'success' => true, 
            'contract_id' => $pdo->lastInsertId(),
            'message' => 'قرارداد با موفقیت ثبت شد'
        ]);
    } else {
        throw new Exception('خطا در اجرای کوئری دیتابیس');
    }

} catch (Exception $e) {
    // بازگرداندن متن دقیق ارور برای دیباگ
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'خطای SQL: ' . $e->getMessage()]);
}
?>