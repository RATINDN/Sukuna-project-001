<?php
session_start();
require_once 'db_connect.php';

// بررسی ادمین
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] != 1) {
    header("Location: index.php");
    exit();
}

$type = $_GET['type'] ?? 'users';

// نام‌گذاری فایل و هدرها
$fileName = 'Report_' . ucfirst($type) . '_' . date('Y-m-d_H-i');
header('Content-Type: application/vnd.ms-excel; charset=utf-8');
header("Content-Disposition: attachment; filename={$fileName}.xls");
header('Pragma: no-cache');
header('Expires: 0');

$currentDate = date('Y/m/d H:i');

// ==========================================
// محاسبه آمارهای خلاصه (مینی داشبورد اکسل)
// ==========================================
$reportTitle = '';
$summaryText = '';
$span = 0;

if ($type === 'users') {
    $reportTitle = 'گزارش لیست کاربران سیستم';
    $span = 8; // افزایش تعداد ستون‌ها برای تاریخ ثبت‌نام
    
    $stmt = $pdo->query("SELECT role FROM car");
    $allUsers = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $totU = count($allUsers);
    $totA = count(array_filter($allUsers, function($r) { return $r == 1; }));
    $totN = $totU - $totA;
    
    $summaryText = "📊 خلاصه وضعیت:  کل کاربران ($totU نفر)   |   مدیران ($totA نفر)   |   کاربران عادی ($totN نفر)";
} 
elseif ($type === 'contracts') {
    $reportTitle = 'گزارش قراردادها و سفارشات';
    $span = 12; // افزایش ستون‌ها برای تاریخ پرداخت
    
    $stmt = $pdo->query("SELECT status, car_price FROM contracts");
    $allC = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $totC = count($allC);
    $totPaid = 0; $totPend = 0; $totRej = 0; $totalIncome = 0;
    
    foreach($allC as $c) {
        if($c['status'] == 'paid') {
            $totPaid++;
            $totalIncome += (int)preg_replace('/[^0-9]/', '', $c['car_price']);
        }
        elseif($c['status'] == 'pending') { $totPend++; }
        else { $totRej++; }
    }
    
    $incomeStr = number_format($totalIncome);
    $summaryText = "📊 خلاصه وضعیت:  کل سفارشات ($totC)  |  موفق ($totPaid)  |  در انتظار ($totPend)  |  رد شده ($totRej)   ---   💰 مجموع درآمد قطعی: $incomeStr تومان";
} 
elseif ($type === 'products') {
    $reportTitle = 'گزارش وضعیت موجودی محصولات';
    $span = 14; // افزایش ستون‌ها برای تاریخ ثبت و ویرایش
    
    $stmt = $pdo->query("SELECT inventory FROM products");
    $allP = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $totP = count($allP);
    $totInv = array_sum($allP);
    
    $summaryText = "📊 خلاصه وضعیت:  تنوع خودروها ($totP مدل)   |   موجودی کل انبار ($totInv دستگاه)";
}
elseif ($type === 'reports') {
    $reportTitle = 'گزارش سیستم (صف ایمیل‌ها)';
    $span = 7;
    
    $stmt = $pdo->query("SELECT status, COUNT(*) as cnt FROM email_queue GROUP BY status");
    $stats = ['pending' => 0, 'sent' => 0, 'failed' => 0, 'processing' => 0];
    while($r = $stmt->fetch(PDO::FETCH_ASSOC)) { $stats[$r['status']] = $r['cnt']; }
    
    $summaryText = "📊 آمار ایمیل‌ها:  کل ارسال شده‌ها ({$stats['sent']})   |   در صف انتظار ({$stats['pending']})   |   ناموفق ({$stats['failed']})";
}

// ==========================================
// استایل‌های حرفه‌ای (با شکستن هوشمند متون)
// ==========================================
$style = '
<style>
    body { font-family: "Segoe UI", Tahoma, Arial, sans-serif; direction: rtl; }
    table { border-collapse: collapse; border: 2px solid #2c3e50; width: 100%; }
    .report-title { background-color: #2c3e50; color: #ffffff; font-size: 18px; font-weight: bold; padding: 15px; text-align: center; }
    .report-summary { background-color: #ecf0f1; color: #2c3e50; font-size: 14px; font-weight: bold; padding: 10px; text-align: center; border: 1px solid #bdc3c7; border-bottom: 2px solid #2c3e50;}
    th { background-color: #34495e; color: #ffffff; border: 1px solid #7f8c8d; padding: 12px; font-weight: bold; text-align: center; white-space: nowrap; }
    td { border: 1px solid #bdc3c7; padding: 10px; text-align: center; vertical-align: middle; font-size: 13px; color: #2c3e50; }
    .row-even { background-color: #f8f9fa; }
    .row-odd { background-color: #ffffff; }
    .wrap-text { white-space: normal; width: 350px; line-height: 1.6; text-align: right; }
    .nowrap-text { white-space: nowrap; }
    .price-cell { color: #27ae60; font-weight: bold; white-space: nowrap; }
    .text-str { mso-number-format:"\@"; white-space: nowrap; } 
    .status-paid { background-color: #d4edda; color: #155724; font-weight: bold; white-space: nowrap; }
    .status-pending { background-color: #fff3cd; color: #856404; font-weight: bold; white-space: nowrap; }
    .status-rejected { background-color: #f8d7da; color: #721c24; font-weight: bold; white-space: nowrap; }
</style>
';

echo '<html xmlns:x="urn:schemas-microsoft-com:office:excel">';
echo '<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">' . $style . '</head>';
echo '<body><table>';
echo "<thead><tr><th colspan='{$span}' class='report-title'>{$reportTitle} | تاریخ تهیه گزارش: {$currentDate}</th></tr>
      <tr><th colspan='{$span}' class='report-summary'>{$summaryText}</th></tr>";

// ==========================================
// 1. جدول کاربران
// ==========================================
if ($type === 'users') {
    echo '  <tr>
                <th>شناسه (ID)</th>
                <th>نام کاربری</th>
                <th>ایمیل</th>
                <th>شماره تماس</th>
                <th>نقش کاربر</th>
                <th>وضعیت تایید</th>
                <th>تاریخ ثبت‌نام</th>
                <th>آخرین فعالیت</th>
            </tr>
          </thead><tbody>';
    
    $stmt = $pdo->prepare("SELECT id, user_name, email, phone, role, status, created_at, last_activity FROM car ORDER BY id DESC");
    $stmt->execute();
    
    $i = 0;
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $bgClass = ($i % 2 == 0) ? 'row-even' : 'row-odd';
        $role = $row['role'] == 1 ? 'مدیر سیستم' : 'کاربر عادی';
        $status = $row['status'] == 1 ? 'تایید شده' : 'تایید نشده';
        $created = $row['created_at'] ? $row['created_at'] : 'قدیمی';
        
        echo "<tr class='$bgClass'>
                <td>{$row['id']}</td>
                <td class='nowrap-text' style='font-weight:bold;'>{$row['user_name']}</td>
                <td class='text-str'>{$row['email']}</td>
                <td class='text-str'>{$row['phone']}</td>
                <td class='nowrap-text'>$role</td>
                <td class='nowrap-text'>$status</td>
                <td dir='ltr' class='text-str'>{$created}</td>
                <td dir='ltr' class='text-str'>{$row['last_activity']}</td>
              </tr>";
        $i++;
    }
}

// ==========================================
// 2. جدول قراردادها
// ==========================================
elseif ($type === 'contracts') {
    echo '  <tr>
                <th>ID</th>
                <th>کد رهگیری</th>
                <th>نام خریدار</th>
                <th>کد ملی</th>
                <th>شماره تماس</th>
                <th>آدرس دقیق</th>
                <th>خودرو</th>
                <th>رنگ</th>
                <th>مبلغ نهایی</th>
                <th>وضعیت</th>
                <th>زمان ثبت سفارش</th>
                <th>زمان پرداخت دقیق</th>
            </tr>
          </thead><tbody>';
    
    $stmt = $pdo->prepare("SELECT id, tracking_code, real_name, national_id, phone, address, car_name, car_color, car_price, status, created_at, paid_at FROM contracts ORDER BY id DESC");
    $stmt->execute();
    
    $i = 0;
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $bgClass = ($i % 2 == 0) ? 'row-even' : 'row-odd';
        
        $statusText = ''; $statusClass = '';
        if($row['status'] == 'paid') { $statusText = 'نهایی شده'; $statusClass = 'status-paid'; } 
        elseif($row['status'] == 'pending') { $statusText = 'در انتظار'; $statusClass = 'status-pending'; } 
        else { $statusText = 'رد شده'; $statusClass = 'status-rejected'; }
        
        $car_price = str_replace(' ', '&nbsp;', $row['car_price']);
        $paid_time = $row['paid_at'] ? $row['paid_at'] : '-';

        echo "<tr class='$bgClass'>
                <td>{$row['id']}</td>
                <td class='text-str' style='font-weight:bold;'>{$row['tracking_code']}</td>
                <td class='nowrap-text' style='font-weight:bold;'>{$row['real_name']}</td>
                <td class='text-str'>{$row['national_id']}</td>
                <td class='text-str'>{$row['phone']}</td>
                <td class='wrap-text'>{$row['address']}</td>
                <td class='nowrap-text'>{$row['car_name']}</td>
                <td class='nowrap-text'>{$row['car_color']}</td>
                <td class='price-cell'>{$car_price}</td>
                <td class='$statusClass'>$statusText</td>
                <td dir='ltr' class='text-str'>{$row['created_at']}</td>
                <td dir='ltr' class='text-str' style='color:#27ae60; font-weight:bold;'>{$paid_time}</td>
              </tr>";
        $i++;
    }
}

// ==========================================
// 3. جدول محصولات
// ==========================================
elseif ($type === 'products') {
    echo '  <tr>
                <th>ID</th>
                <th>نام خودرو</th>
                <th>برند سازنده</th>
                <th>قیمت نهایی</th>
                <th>قیمت پیشین</th>
                <th>موجودی</th>
                <th>میزان تقاضا (لایک)</th>
                <th>آمار دقیق رنگ‌بندی</th>
                <th>قدرت</th>
                <th>شتاب</th>
                <th>موتور</th>
                <th>ویژه</th>
                <th>زمان ایجاد محصول</th>
                <th>آخرین ویرایش</th>
            </tr>
          </thead><tbody>';
    
    $stmt = $pdo->prepare("SELECT p.*, COUNT(w.id) as likes_count FROM products p LEFT JOIN wishlist w ON p.id = w.product_id GROUP BY p.id ORDER BY p.id DESC");
    $stmt->execute();
    
    $i = 0;
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $bgClass = ($i % 2 == 0) ? 'row-even' : 'row-odd';
        
        $price = is_numeric($row['price']) ? number_format($row['price']) . '&nbsp;تومان' : str_replace(' ', '&nbsp;', $row['price']);
        $old_price = (!empty($row['old_price']) && is_numeric($row['old_price'])) ? number_format($row['old_price']) . '&nbsp;تومان' : '-';
        $in_slider = $row['in_slider'] == 1 ? 'بله' : 'خیر';

        $colors_text = '';
        if (!empty($row['colors_inventory']) && $row['colors_inventory'] !== 'null') {
            $colorsArr = json_decode($row['colors_inventory'], true);
            if (is_array($colorsArr)) {
                $color_parts = [];
                foreach ($colorsArr as $cName => $cData) {
                    $qty = is_array($cData) ? $cData['qty'] : $cData;
                    $color_parts[] = "{$cName}&nbsp;({$qty}&nbsp;عدد)";
                }
                $colors_text = implode(' | ', $color_parts);
            }
        }
        if (empty($colors_text)) $colors_text = 'نامشخص';

        $likes_text = $row['likes_count'];
        if ($row['inventory'] == 0 && $row['likes_count'] > 0) {
            $likes_text .= ' (نیاز به شارژ)';
        }
        
        $updated_time = ($row['updated_at'] && $row['updated_at'] !== $row['created_at']) ? $row['updated_at'] : '-';
        $created_time = $row['created_at'] ? $row['created_at'] : '-';

        echo "<tr class='$bgClass'>
                <td>{$row['id']}</td>
                <td class='wrap-text' style='font-weight:bold; width: 200px;'>{$row['name']}</td>
                <td class='nowrap-text'>{$row['brand']}</td>
                <td class='price-cell'>{$price}</td>
                <td class='nowrap-text'>{$old_price}</td>
                <td style='font-size:16px; font-weight:bold; color:#d35400;'>{$row['inventory']}</td>
                <td class='nowrap-text' style='color:#e91e63; font-weight:bold;'>{$likes_text}</td>
                <td class='wrap-text' style='text-align:center;'>{$colors_text}</td>
                <td class='nowrap-text'>{$row['hp']}</td>
                <td class='nowrap-text'>{$row['accel']}</td>
                <td class='nowrap-text'>{$row['engine']}</td>
                <td class='nowrap-text'>{$in_slider}</td>
                <td dir='ltr' class='text-str'>{$created_time}</td>
                <td dir='ltr' class='text-str'>{$updated_time}</td>
              </tr>";
        $i++;
    }
}

// ==========================================
// 4. جدول گزارشات سیستم (ایمیل‌ها)
// ==========================================
elseif ($type === 'reports') {
    echo '  <tr>
                <th>ID</th>
                <th>نام گیرنده</th>
                <th>ایمیل گیرنده</th>
                <th>موضوع ایمیل</th>
                <th>وضعیت ارسال</th>
                <th>زمان ایجاد</th>
                <th>زمان ارسال</th>
            </tr>
          </thead><tbody>';
    
    $stmt = $pdo->prepare("SELECT * FROM email_queue ORDER BY id DESC");
    $stmt->execute();
    
    $i = 0;
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $bgClass = ($i % 2 == 0) ? 'row-even' : 'row-odd';
        
        $statusText = ''; $statusClass = '';
        if($row['status'] == 'sent') { $statusText = 'ارسال شده'; $statusClass = 'status-paid'; } 
        elseif($row['status'] == 'pending') { $statusText = 'در صف انتظار'; $statusClass = 'status-pending'; } 
        elseif($row['status'] == 'processing') { $statusText = 'در حال پردازش'; $statusClass = 'status-pending'; } 
        else { $statusText = 'خطا در ارسال'; $statusClass = 'status-rejected'; }

        echo "<tr class='$bgClass'>
                <td>{$row['id']}</td>
                <td class='nowrap-text'>".htmlspecialchars($row['recipient_name'], ENT_QUOTES, 'UTF-8')."</td>
                <td class='text-str'>".htmlspecialchars($row['recipient_email'], ENT_QUOTES, 'UTF-8')."</td>
                <td class='wrap-text'>".htmlspecialchars($row['subject'], ENT_QUOTES, 'UTF-8')."</td>
                <td class='$statusClass'>$statusText</td>
                <td dir='ltr' class='text-str'>{$row['created_at']}</td>
                <td dir='ltr' class='text-str'>".($row['sent_at'] ? $row['sent_at'] : '-')."</td>
              </tr>";
        $i++;
    }
}

echo '</tbody></table></body></html>';
exit();
?>