<?php
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
    
    // دریافت قراردادها + عکس ماشین با استفاده از JOIN
    $stmt = $pdo->prepare("
        SELECT c.id, c.tracking_code, c.car_name, c.car_color, c.car_price, c.status, c.created_at, p.image 
        FROM contracts c 
        LEFT JOIN products p ON c.product_id = p.id 
        WHERE c.user_id = ? 
        ORDER BY c.created_at DESC
    ");
    $stmt->execute([$user_id]);
    $contracts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // محاسبه رتبه بر اساس تعداد خریدهای موفق
    $paidCount = 0;
    foreach ($contracts as $c) {
        if ($c['status'] === 'paid') $paidCount++;
    }

    $rank = 'عضو برنزی 🥉';
    $rankColor = '#cd7f32'; 
    
    if ($paidCount >= 3) { 
        $rank = 'عضو الماس VIP 💎'; 
        $rankColor = '#00bcd4'; 
    } elseif ($paidCount >= 1) { 
        $rank = 'عضو طلایی 👑'; 
        $rankColor = '#ffd700'; 
    }

    echo json_encode([
        'success' => true, 
        'contracts' => $contracts,
        'rank' => $rank,
        'rankColor' => $rankColor,
        'paidCount' => $paidCount
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'خطای سیستم']);
}
?>