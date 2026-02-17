<?php
session_start();
require_once 'db_connect.php';

// بررسی ادمین
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] != 1) {
    header("Location: index.php");
    exit();
}

$type = $_GET['type'] ?? 'users';

// تنظیم هدرها برای فایل اکسل (XLS)
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename=Report_' . $type . '_' . date('Y-m-d') . '.xls');
header('Pragma: no-cache');
header('Expires: 0');

// استایل‌های CSS برای خوشگل کردن جدول اکسل
$style = '
<style>
    body { font-family: Tahoma, Arial, sans-serif; }
    table { width: 100%; border-collapse: collapse; direction: rtl; }
    th { background-color: #1976D2; color: #ffffff; border: 1px solid #000000; padding: 10px; font-weight: bold; text-align: center; }
    td { border: 1px solid #cccccc; padding: 8px; text-align: center; vertical-align: middle; }
    .row-even { background-color: #f2f2f2; }
    .status-paid { background-color: #e8f5e9; color: #2e7d32; font-weight: bold; }
    .status-pending { background-color: #fff3e0; color: #ef6c00; font-weight: bold; }
    .status-rejected { background-color: #ffebee; color: #c62828; font-weight: bold; }
</style>
';

// شروع خروجی (یک فایل HTML که اکسل آن را میفهمد)
echo '<html xmlns:x="urn:schemas-microsoft-com:office:excel">';
echo '<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">' . $style . '</head>';
echo '<body>';

// ==========================================
// خروجی جدول کاربران
// ==========================================
if ($type === 'users') {
    echo '<table>';
    echo '<thead>
            <tr>
                <th style="width:50px;">ID</th>
                <th style="width:150px;">نام کاربری</th>
                <th style="width:200px;">ایمیل</th>
                <th style="width:150px;">تلفن</th>
                <th style="width:100px;">نقش</th>
                <th style="width:100px;">وضعیت</th>
                <th style="width:200px;">آخرین فعالیت</th>
            </tr>
          </thead><tbody>';
    
    $stmt = $pdo->prepare("SELECT id, user_name, email, phone, role, status, last_activity FROM car");
    $stmt->execute();
    
    $i = 0;
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $bgClass = ($i % 2 == 0) ? 'row-even' : '';
        $role = $row['role'] == 1 ? 'مدیر' : 'کاربر عادی';
        $status = $row['status'] == 1 ? 'تایید شده' : 'تایید نشده';
        
        echo "<tr class='$bgClass'>
                <td>{$row['id']}</td>
                <td>{$row['user_name']}</td>
                <td>{$row['email']}</td>
                <td>{$row['phone']}</td>
                <td>$role</td>
                <td>$status</td>
                <td>{$row['last_activity']}</td>
              </tr>";
        $i++;
    }
    echo '</tbody></table>';
}

// ==========================================
// خروجی جدول قراردادها (با رنگ‌بندی وضعیت)
// ==========================================
elseif ($type === 'contracts') {
    echo '<table>';
    echo '<thead>
            <tr>
                <th style="width:50px;">ID</th>
                <th style="width:120px;">کد رهگیری</th>
                <th style="width:150px;">نام خریدار</th>
                <th style="width:120px;">کد ملی</th>
                <th style="width:120px;">تلفن</th>
                <th style="width:250px;">آدرس</th>
                <th style="width:150px;">خودرو</th>
                <th style="width:100px;">رنگ</th>
                <th style="width:150px;">قیمت نهایی</th>
                <th style="width:150px;">وضعیت</th>
                <th style="width:150px;">تاریخ ثبت</th>
            </tr>
          </thead><tbody>';
    
    $stmt = $pdo->prepare("SELECT id, tracking_code, real_name, national_id, phone, address, car_name, car_color, car_price, status, created_at FROM contracts");
    $stmt->execute();
    
    $i = 0;
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $bgClass = ($i % 2 == 0) ? 'row-even' : '';
        
        // رنگ‌بندی سلول وضعیت
        $statusText = '';
        $statusClass = '';
        
        if($row['status'] == 'paid') {
            $statusText = 'نهایی شده';
            $statusClass = 'status-paid';
        } elseif($row['status'] == 'pending') {
            $statusText = 'در انتظار';
            $statusClass = 'status-pending';
        } else {
            $statusText = 'رد شده';
            $statusClass = 'status-rejected';
        }
        
        echo "<tr class='$bgClass'>
                <td>{$row['id']}</td>
                <td>{$row['tracking_code']}</td>
                <td style='font-weight:bold;'>{$row['real_name']}</td>
                <td>{$row['national_id']}</td>
                <td>{$row['phone']}</td>
                <td>{$row['address']}</td>
                <td>{$row['car_name']}</td>
                <td>{$row['car_color']}</td>
                <td>{$row['car_price']}</td>
                <td class='$statusClass'>$statusText</td>
                <td>{$row['created_at']}</td>
              </tr>";
        $i++;
    }
    echo '</tbody></table>';
}

echo '</body></html>';
exit();
?>