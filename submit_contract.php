<?php
// تنظیمات برای جلوگیری از نمایش خطا در خروجی JSON
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

    // 3. دریافت داده‌ها از فرم
    $product_id = isset($_POST['product_id']) ? $_POST['product_id'] : null;
    $car_name = $_POST['car_name'] ?? '';
    $car_price = $_POST['car_price'] ?? '';
    $car_color = $_POST['car_color'] ?? ''; 
    $real_name = $_POST['real_name'] ?? '';
    $national_id = $_POST['national_id'] ?? '';
    $address = $_POST['address'] ?? '';
    $postal_code = $_POST['postal_code'] ?? '';
    $signature = $_POST['signature'] ?? '';

    if (strlen($signature) < 500) {
        throw new Exception('امضا دریافت نشد یا ناقص است.');
    }

    if (!$product_id) {
        throw new Exception('خطا در شناسایی محصول. لطفا صفحه را رفرش کنید.');
    }

    $tracking_code = 'LX-' . mt_rand(10000, 99999);
    $contract_text = "اینجانب $real_name متعهد به خرید خودروی $car_name به رنگ $car_color به قیمت $car_price می‌باشم.";

    // شروع تراکنش امن
    $pdo->beginTransaction();

    // 4. بررسی موجودی رنگ قبل از ثبت نهایی (قفل کردن سطر)
    $stmtProd = $pdo->prepare("SELECT inventory, colors_inventory FROM products WHERE id = ? FOR UPDATE");
    $stmtProd->execute([$product_id]);
    $product = $stmtProd->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        throw new Exception('محصول مورد نظر یافت نشد.');
    }

    // دیکد کردن JSON رنگ‌ها
    $colorsArr = json_decode($product['colors_inventory'], true);
    if (!is_array($colorsArr)) $colorsArr = [];
    
    // بررسی موجودی رنگ انتخابی
    $currentQty = 0;
    
    // چک کردن اینکه آیا کلید رنگ وجود دارد؟
    if (isset($colorsArr[$car_color])) {
        // اگر فرمت جدید باشد (آبجکت)
        if (is_array($colorsArr[$car_color])) {
            $currentQty = intval($colorsArr[$car_color]['qty']);
        } else {
            // اگر فرمت قدیمی باشد (عدد)
            $currentQty = intval($colorsArr[$car_color]);
        }
    }

    if ($currentQty <= 0) {
        throw new Exception("متاسفانه در همین لحظه، موجودی رنگ '$car_color' به پایان رسید!");
    }

    // 5. کسر موجودی (هوشمند)
    if (is_array($colorsArr[$car_color])) {
        // فرمت جدید: مقدار qty را کم می‌کنیم
        $colorsArr[$car_color]['qty'] = max(0, $currentQty - 1);
    } else {
        // فرمت قدیمی: خود مقدار را کم می‌کنیم
        $colorsArr[$car_color] = max(0, $currentQty - 1);
    }

    // *** اصلاح باگ اصلی: محاسبه صحیح مجموع کل ***
    // (قبلا array_sum بود که اشتباه بود، الان دستی جمع می‌زنیم)
    $new_total_inventory = 0;
    foreach ($colorsArr as $c) {
        if (is_array($c)) {
            // اگر فرمت جدید بود (آبجکت)
            $new_total_inventory += intval($c['qty']);
        } else {
            // اگر فرمت قدیمی بود (عدد)
            $new_total_inventory += intval($c);
        }
    }
    // *******************************************

    $colors_json = json_encode($colorsArr, JSON_UNESCAPED_UNICODE);

    // آپدیت موجودی در دیتابیس
    $updateProd = $pdo->prepare("UPDATE products SET inventory = ?, colors_inventory = ? WHERE id = ?");
    $updateProd->execute([$new_total_inventory, $colors_json, $product_id]);

    // 6. ثبت قرارداد
    $sql = "INSERT INTO contracts 
            (user_id, product_id, tracking_code, real_name, national_id, phone, address, postal_code, car_name, car_price, car_color, contract_text, signature, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $user_id, $product_id, $tracking_code, $real_name, $national_id, $phone, 
        $address, $postal_code, $car_name, $car_price, $car_color, 
        $contract_text, $signature
    ]);

    $contract_id = $pdo->lastInsertId();

    // پایان موفقیت آمیز تراکنش
    $pdo->commit();

    echo json_encode([
        'success' => true, 
        'contract_id' => $contract_id,
        'message' => 'قرارداد با موفقیت ثبت شد'
    ]);

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack(); 
    }
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['success' => false, 'error' => 'خطای دیتابیس: ' . $e->getMessage()]);
}
?>