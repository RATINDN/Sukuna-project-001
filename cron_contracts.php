<?php
// cron_contracts.php
// ربات پس‌زمینه برای بررسی و لغو قراردادهای منقضی شده

// 1. امنیت: فقط اجازه اجرا با توکن معتبر
$secret_token = "LUXURY_CAR_CRON_SECRET_2026_XYZ";
if (!isset($_GET['token']) || $_GET['token'] !== $secret_token) {
    http_response_code(403);
    die("⛔ Access Denied! You are not allowed to run this bot.");
}

require_once 'db_connect.php';

// =========================================================
// سیستم هوشمند لغو خودکار قراردادهای رها شده (Auto-Reject)
// =========================================================

try {
    $pdo->beginTransaction();

    // پیدا کردن قراردادهای معلق قدیمی (24 ساعت)
    $stmtExp = $pdo->prepare("SELECT id, product_id, car_color FROM contracts WHERE status = 'pending' AND created_at < NOW() - INTERVAL 24 HOUR FOR UPDATE");
    $stmtExp->execute();
    $expiredContracts = $stmtExp->fetchAll(PDO::FETCH_ASSOC);

    if (count($expiredContracts) > 0) {
        foreach ($expiredContracts as $ec) {
            $cId = $ec['id'];
            $pId = $ec['product_id'];
            $colorName = $ec['car_color'];

            // تغییر وضعیت قرارداد به "رد شده"
            $pdo->prepare("UPDATE contracts SET status = 'rejected' WHERE id = ?")->execute([$cId]);

            // بازگرداندن خودرو به انبار
            if (!empty($pId)) {
                $pStmt = $pdo->prepare("SELECT colors_inventory FROM products WHERE id = ? FOR UPDATE");
                $pStmt->execute([$pId]);
                $prod = $pStmt->fetch(PDO::FETCH_ASSOC);

                if ($prod) {
                    $colorsArr = json_decode($prod['colors_inventory'], true);
                    if (!is_array($colorsArr)) $colorsArr = [];

                    $currentQty = 0; $currentHex = '#000000'; $currentImg = '';
                    if (isset($colorsArr[$colorName])) {
                        if (is_array($colorsArr[$colorName])) {
                            $currentQty = intval($colorsArr[$colorName]['qty']);
                            $currentHex = $colorsArr[$colorName]['hex'];
                            $currentImg = $colorsArr[$colorName]['img'] ?? '';
                        } else {
                            $currentQty = intval($colorsArr[$colorName]);
                        }
                    }

                    // برگرداندن ۱ عدد به موجودی
                    $colorsArr[$colorName] = ['hex' => $currentHex, 'qty' => $currentQty + 1, 'img' => $currentImg];

                    $new_total = 0;
                    foreach ($colorsArr as $c) { $new_total += is_array($c) ? intval($c['qty']) : intval($c); }

                    $pdo->prepare("UPDATE products SET inventory = ?, colors_inventory = ? WHERE id = ?")
                        ->execute([$new_total, json_encode($colorsArr, JSON_UNESCAPED_UNICODE), $pId]);
                }
            }
        }
        echo "Successfully rejected " . count($expiredContracts) . " abandoned contracts and restocked inventory.";
    } else {
        echo "No expired contracts found.";
    }

    $pdo->commit();
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Auto-Reject Error: " . $e->getMessage());
}
?>