<?php
session_start();
require_once 'db_connect.php';

// بررسی دسترسی ادمین
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] != 1) {
    header("Location: index.php");
    exit();
}

$view = $_GET['view'] ?? 'dashboard';

// ============================================
// 1. منطق داشبورد
// ============================================
// ============================================
// 1. منطق داشبورد و دریافت اطلاعات نمودارها
// ============================================
if ($view === 'dashboard') {
    try {
        $totalUsers = $pdo->query("SELECT COUNT(*) FROM car")->fetchColumn();
        $newContracts = $pdo->query("SELECT COUNT(*) FROM contracts WHERE status = 'pending'")->fetchColumn();
        
        $stmt = $pdo->query("SELECT car_price FROM contracts WHERE status = 'paid'");
        $prices = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $totalRevenue = 0;
        foreach ($prices as $p) {
            // حذف حروف و ویرگول برای جمع زدن مبلغ
            $totalRevenue += (int)preg_replace('/[^0-9]/', '', $p);
        }

        $stmt = $pdo->query("SELECT COUNT(*) FROM car WHERE last_activity > DATE_SUB(NOW(), INTERVAL 3 MINUTE)");
        $onlineUsers = $stmt->fetchColumn();

        $stmt = $pdo->prepare("SELECT * FROM contracts ORDER BY created_at DESC LIMIT 5");
        $stmt->execute();
        $recentOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $pdo->query("SELECT car_name, COUNT(*) as count FROM contracts GROUP BY car_name ORDER BY count DESC LIMIT 1");
        $popularCar = $stmt->fetch(PDO::FETCH_ASSOC);
        $topCarName = $popularCar ? $popularCar['car_name'] : 'هنوز فروشی نیست';

        // ----------------------------------------------------
        // --- داده‌های مخصوص نمودارها (اضافه شده جدید) ---
        // ----------------------------------------------------
        
        // 1. داده‌های نمودار وضعیت قراردادها (دایره‌ای)
        $statusStmt = $pdo->query("SELECT status, COUNT(*) as count FROM contracts GROUP BY status");
        $statusDataDB = $statusStmt->fetchAll(PDO::FETCH_ASSOC);
        $chartStatusData =['paid' => 0, 'pending' => 0, 'rejected' => 0];
        foreach($statusDataDB as $row) { $chartStatusData[$row['status']] = $row['count']; }

        // 2. داده‌های نمودار پرفروش‌ترین ماشین‌ها (ستونی)
        $topCarsStmt = $pdo->query("SELECT car_name, COUNT(*) as count FROM contracts WHERE status='paid' GROUP BY car_name ORDER BY count DESC LIMIT 5");
        $chartTopCars = $topCarsStmt->fetchAll(PDO::FETCH_ASSOC);

        // 3. داده‌های نمودار روند فروش 7 روز گذشته (خطی)
        $trendStmt = $pdo->query("
            SELECT DATE(created_at) as date, COUNT(*) as count 
            FROM contracts 
            WHERE created_at >= DATE(NOW()) - INTERVAL 7 DAY
            GROUP BY DATE(created_at) 
            ORDER BY date ASC
        ");
        $chartTrendData = $trendStmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        $error = "خطا: " . $e->getMessage();
    }
}
// بقیه لاجیک‌ها (کاربران و قراردادها) بدون تغییر...
elseif ($view === 'users') {
    try {
        // دریافت تمام کاربران به همراه تعداد خریدهای موفق برای فیلتر زنده در جاوااسکریپت
        $stmt = $pdo->prepare("
            SELECT c.*, 
            (SELECT COUNT(*) FROM contracts WHERE user_id = c.id AND status = 'paid') as paid_count 
            FROM car c 
            ORDER BY c.id DESC
        ");
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // محاسبه آمار برای کارت‌های بالای صفحه
        $totalUsersCount = count($users);
        $adminCount = count(array_filter($users, function($u) { return $u['role'] == 1; }));
        $normalUserCount = $totalUsersCount - $adminCount;

    } catch (PDOException $e) { $error = $e->getMessage(); }
} elseif ($view === 'contracts') {
    try {
        $stmt = $pdo->prepare("SELECT * FROM contracts ORDER BY created_at DESC");
        $stmt->execute();
        $contracts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) { $error = $e->getMessage(); }
}
elseif ($view === 'reports') {
    try {
        // آمار ایمیل‌ها
        $stmtStats = $pdo->query("SELECT status, COUNT(*) as cnt FROM email_queue GROUP BY status");
        $emailStats = ['pending' => 0, 'sent' => 0, 'failed' => 0, 'processing' => 0];
        while($r = $stmtStats->fetch(PDO::FETCH_ASSOC)) { $emailStats[$r['status']] = $r['cnt']; }
        
        $totalNotifs = $pdo->query("SELECT COUNT(*) FROM notifications")->fetchColumn();
        
        // واکشی 300 ایمیل آخر
        $stmtEmails = $pdo->query("SELECT * FROM email_queue ORDER BY id DESC LIMIT 300");
        $emails = $stmtEmails->fetchAll(PDO::FETCH_ASSOC);

        // واکشی 300 نوتیفیکیشن آخر به همراه نام کاربر
        $stmtNotifs = $pdo->query("
            SELECT n.*, c.user_name 
            FROM notifications n 
            JOIN car c ON n.user_id = c.id 
            ORDER BY n.created_at DESC 
            LIMIT 300
        ");
        $admin_notifs = $stmtNotifs->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) { $error = $e->getMessage(); }
}
elseif ($view === 'products') {
    try {
        // دریافت محصولات به همراه تعداد لایک‌هایی که در جدول wishlist خورده‌اند
        $stmt = $pdo->query("
            SELECT p.*, COUNT(w.id) as likes_count 
            FROM products p 
            LEFT JOIN wishlist w ON p.id = w.product_id 
            GROUP BY p.id 
            ORDER BY p.created_at DESC
        ");
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $brandStmt = $pdo->query("SELECT DISTINCT brand FROM products WHERE brand IS NOT NULL AND brand != '' ORDER BY brand ASC");
        $uniqueBrands = $brandStmt->fetchAll(PDO::FETCH_COLUMN);

    } catch (PDOException $e) {
        $error = "خطا در دریافت محصولات: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>پنل مدیریت | لوکس کار</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="css/loginstyle.css">
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
   <!-- لینک کردن استایل‌های فریم‌ورک پاپ‌آپ‌های سفارشی -->
<link rel="stylesheet" href="css/custom_dialogs.css">

<!-- لینک کردن جاوااسکریپت موتور اعلانات سراسری -->
<script src="js/custom_dialogs.js"></script>
    <!-- اضافه کردن کتابخانه قدرتمند Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="success-popup" id="successPopup" style="display:none;"></div>
    
    <div class="admin-layout">
        <!-- Sidebar -->
        <div class="admin-sidebar" id="adminSidebar">
            <div class="admin-sidebar-header"><h1>پنل مدیریت</h1></div>
            <div class="admin-sidebar-content">
                <a href="admin.php?view=dashboard" class="nav-link <?php echo $view === 'dashboard' ? 'active' : ''; ?>">📊 داشبورد</a>
                <a href="admin.php?view=users" class="nav-link <?php echo $view === 'users' ? 'active' : ''; ?>">👥 کاربران</a>
                <a href="admin.php?view=contracts" class="nav-link <?php echo $view === 'contracts' ? 'active' : ''; ?>">📄 قراردادها</a>
                <a href="admin.php?view=products" class="nav-link <?php echo $view === 'products' ? 'active' : ''; ?>">🚗 مدیریت محصولات</a>
                <a href="admin.php?view=reports" class="nav-link <?php echo $view === 'reports' ? 'active' : ''; ?>">📡 گزارشات سیستم</a>
                <hr style="border-top: 1px solid rgba(255,255,255,0.1); margin: 20px 0;">
                <a href="index.php" class="back-to-site">بازگشت به سایت</a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="admin-main">
            
            <div class="admin-header">
                <div class="header-left">
                    <button id="menuToggle" class="menu-toggle"><i class="fas fa-bars"></i></button>
                    <h2 style="width : fit-content !important">
                        <?php 
                              if($view === 'dashboard') echo 'داشبورد مدیریتی';
                             elseif($view === 'contracts') echo 'مدیریت قراردادها';
                             elseif($view === 'products') echo 'مدیریت محصولات'; // <--- این خط جدید است
                             elseif($view === 'reports') echo 'گزارشات سیستم'; /* این خط اضافه شد */
                            else echo 'مدیریت کاربران';
                          ?>
                    </h2>
                    <?php if($view !== 'dashboard'): ?>
                    <div class="search-bar" style="position: relative; right: 5px; width :fit-content;">
                        <input type="search" name="" placeholder="جستجو..." style="width: 50%;" id="admin-search">
                    </div>
                    <?php endif; ?>
                </div>
                <div class="mon" id="model">
                    <svg onclick="darkmode()" xmlns="http://www.w3.org/2000/svg" width="35" height="35" fill="currentColor" class="moon2" id="moon2" viewBox="0 0 16 16"><path d="M6 .278a.77.77 0 0 1 .08.858 7.2 7.2 0 0 0-.878 3.46c0 4.021 3.278 7.277 7.318 7.277q.792-.001 1.533-.16a.79.79 0 0 1 .81.316.73.73 0 0 1-.031.893A8.35 8.35 0 0 1 8.344 16C3.734 16 0 12.286 0 7.71 0 4.266 2.114 1.312 5.124.06A.75.75 0 0 1 6 .278"/></svg>
                    <svg onclick="darkmode()" xmlns="http://www.w3.org/2000/svg" width="35" height="35" id="sun2" fill="currentColor" class="sun2" style="display: none;" viewBox="0 0 16 16"><path d="M12 8a4 4 0 1 1-8 0 4 4 0 0 1 8 0M8 0a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 0m0 13a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 13m8-5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2a.5.5 0 0 1 .5.5M3 8a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2A.5.5 0 0 1 3 8m10.657-5.657a.5.5 0 0 1 0 .707l-1.414 1.415a.5.5 0 1 1-.707-.708l1.414-1.414a.5.5 0 0 1 .707 0m-9.193 9.193a.5.5 0 0 1 0 .707L3.05 13.657a.5.5 0 0 1-.707-.707l1.414-1.414a.5.5 0 0 1 .707 0m9.193 2.121a.5.5 0 0 1-.707 0l-1.414-1.414a.5.5 0 0 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .707M4.464 4.465a.5.5 0 0 1-.707 0L2.343 3.05a.5.5 0 1 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .708"/></svg>
                </div>
            </div>
            
            <?php if (isset($error)): ?> <div class="error-message"><?php echo $error; ?></div> <?php endif; ?>

                <?php if ($view === 'dashboard'): ?>
            <div class="dashboard-grid">
                
                <!-- کارت کاربران کل -->
                <div class="stat-card card-users" onclick="window.location.href='admin.php?view=users&filter=all'" style="cursor: pointer;">
                    <div class="stat-info"><h3><?php echo number_format($totalUsers); ?></h3><p>کاربران عضو</p></div>
                    <div class="stat-icon"><i class="fas fa-users"></i></div>
                </div>
                
                <!-- کارت کاربران آنلاین -->
                <div class="stat-card card-online" onclick="window.location.href='admin.php?view=users&filter=online'" style="cursor: pointer;">
                    <div class="stat-info">
                        <h3 id="live-online-count"><?php echo number_format($onlineUsers); ?></h3>
                        <p>کاربران آنلاین</p>
                    </div>
                    <div class="stat-icon"><i class="fas fa-wifi"></i></div>
                </div>
                
                <!-- کارت در انتظار بررسی (همونی که تو عکس خواستی) -->
                <div class="stat-card card-contracts" onclick="window.location.href='admin.php?view=contracts&filter=pending'" style="cursor: pointer;">
                    <div class="stat-info"><h3><?php echo number_format($newContracts); ?></h3><p>در انتظار بررسی</p></div>
                    <div class="stat-icon"><i class="fas fa-clock"></i></div>
                </div>
                
                <!-- کارت درآمد کل (میره تو بخش قراردادها و فقط پرداخت شده ها رو میاره) -->
                <div class="stat-card card-revenue" onclick="window.location.href='admin.php?view=contracts&filter=paid'" style="cursor: pointer;">
                    <div class="stat-info"><h3 style="font-size:18px;"><?php echo number_format($totalRevenue); ?> <small style="font-size:10px;">تومان</small></h3><p>درآمد کل</p></div>
                    <div class="stat-icon"><i class="fas fa-wallet"></i></div>
                </div>
                
                <!-- کارت پرفروش‌ترین مدل (میره تو بخش محصولات) -->
                <div class="stat-card card-popular" onclick="window.location.href='admin.php?view=products'" style="cursor: pointer;">
                    <div class="stat-info"><h3 style="font-size:16px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:150px;"><?php echo $topCarName; ?></h3><p>پرفروش‌ترین مدل</p></div>
                    <div class="stat-icon"><i class="fas fa-trophy"></i></div>
                </div>

            </div>

          <!-- ========================================= -->
            <!-- بخش نمودارهای گرافیکی (نسخه ضد انفجار) -->
            <!-- ========================================= -->
            <div class="charts-grid" style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-bottom: 30px; width: 100%;">
                
                <!-- نمودار خطی -->
                <div style="background: var(--admin-bg-color); border: 1px solid var(--admin-border-color); border-radius: 16px; padding: 20px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); overflow: hidden;">
                    <h3 style="margin-top: 0; font-size: 16px; color: var(--admin-text-color); border-bottom: 1px solid var(--admin-border-color); padding-bottom: 10px;">📈 روند سفارشات (۷ روز گذشته)</h3>
                    <!-- این دیوِ جادویی مشکل تغییر سایز رو حل میکنه -->
                    <div style="position: relative; height: 300px; width: 100%;">
                        <canvas id="trendChart"></canvas>
                    </div>
                </div>

                <!-- نمودار دایره‌ای -->
                <div style="background: var(--admin-bg-color); border: 1px solid var(--admin-border-color); border-radius: 16px; padding: 20px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); overflow: hidden;">
                    <h3 style="margin-top: 0; font-size: 16px; color: var(--admin-text-color); border-bottom: 1px solid var(--admin-border-color); padding-bottom: 10px;">📊 وضعیت کلی قراردادها</h3>
                    <div style="position: relative; height: 300px; width: 100%; display: flex; justify-content: center;">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>

                <!-- نمودار ستونی -->
                <div style="grid-column: 1 / -1; background: var(--admin-bg-color); border: 1px solid var(--admin-border-color); border-radius: 16px; padding: 20px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); overflow: hidden;">
                    <h3 style="margin-top: 0; font-size: 16px; color: var(--admin-text-color); border-bottom: 1px solid var(--admin-border-color); padding-bottom: 10px;">🏆 پرفروش‌ترین خودروهای لوکس</h3>
                    <div style="position: relative; height: 300px; width: 100%;">
                        <canvas id="topCarsChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- استایل ریسپانسیو برای موبایل -->
            <style>
                @media (max-width: 992px) { 
                    .charts-grid { grid-template-columns: 1fr !important; } 
                }
            </style>


            <div class="recent-orders-container">
                <div class="recent-header">
                    <h3>🛒 آخرین فعالیت‌های فروشگاه</h3>
                    <a href="admin.php?view=contracts" style="font-size:12px; color:var(--admin-secondary-color);">مشاهده همه &leftarrow;</a>
                </div>
                <table class="mini-table">
                    <thead><tr><th>خریدار</th><th>خودرو</th><th>وضعیت</th><th>مبلغ</th><th>زمان</th></tr></thead>
                    <tbody>
                        <?php foreach ($recentOrders as $order): ?>
                        <tr>
                            <td style="font-weight:bold;"><?php echo htmlspecialchars($order['real_name']); ?></td>
                            <td><?php echo htmlspecialchars($order['car_name']); ?></td>
                            <td><?php if($order['status']=='pending'): ?><span style="color:#ff9800; font-size:11px;">● در انتظار</span><?php elseif($order['status']=='paid'): ?><span style="color:#4caf50; font-size:11px;">● نهایی شده</span><?php else: ?><span style="color:#f44336; font-size:11px;">● رد شده</span><?php endif; ?></td>
                            <td class="price-text"><?php echo htmlspecialchars($order['car_price']); ?></td>
                            <td style="font-size:11px; color:#888;" dir="ltr"><?php echo date('H:i | Y/m/d', strtotime($order['created_at'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($recentOrders)) echo "<tr><td colspan='5' style='text-align:center; padding:20px;'>هنوز سفارشی ثبت نشده است</td></tr>"; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>

            <?php if ($view !== 'dashboard' && $view !== 'reports'): ?>
            
           <!-- 1. نوار ابزار بالا (فیلتر + دکمه اکسل) -->
           <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; margin-bottom: 15px; gap: 10px;">
                
           <?php if ($view === 'users'): ?>
                <!-- فیلترهای زنده (بدون رفرش) برای بخش کاربران -->
                <div class="filter-container" style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                    
                    <select id="userStatusFilter" onchange="applyGlobalUserFilter()" style="padding: 8px; border-radius: 8px; border: 1px solid var(--admin-border-color); background: var(--admin-bg-color); color: var(--admin-text-color); font-family: 'Vazirmatn'; cursor: pointer;">
                        <option value="all">👥 همه وضعیت‌ها</option>
                        <option value="online">🟢 فقط آنلاین‌ها</option>
                        <option value="offline">🔴 فقط آفلاین‌ها</option>
                    </select>

                    <select id="userRoleFilter" onchange="applyGlobalUserFilter()" style="padding: 8px; border-radius: 8px; border: 1px solid var(--admin-border-color); background: var(--admin-bg-color); color: var(--admin-text-color); font-family: 'Vazirmatn'; cursor: pointer;">
                        <option value="all">🛡️ همه نقش‌ها</option>
                        <option value="1">🛡️ مدیران سیستم</option>
                        <option value="0">👤 کاربران عادی</option>
                    </select>

                    <select id="userOrdersFilter" onchange="applyGlobalUserFilter()" style="padding: 8px; border-radius: 8px; border: 1px solid var(--admin-border-color); background: var(--admin-bg-color); color: var(--admin-text-color); font-family: 'Vazirmatn'; cursor: pointer;">
                        <option value="all">🛒 سوابق خرید (همه)</option>
                        <option value="3plus">💎 عضو الماس (۳+ خرید)</option>
                        <option value="1plus">👑 عضو طلایی (۱ تا ۲ خرید)</option>
                        <option value="0">🥉 بدون خرید موفق</option>
                    </select>


                    <!-- سیستم مرتب‌سازی کاربران -->
                    <select id="userSortFilter" onchange="applyGlobalUserFilter()" style="padding: 8px; border-radius: 8px; border: 1px solid var(--admin-border-color); background: var(--admin-bg-color); color: var(--admin-text-color); font-family: 'Vazirmatn'; cursor: pointer;">
                        <option value="newest" <?php echo (!isset($_GET['sort']) || $_GET['sort'] == 'newest') ? 'selected' : ''; ?>>🕒 جدیدترین ثبت‌نام‌ها</option>
                        <option value="oldest" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'oldest') ? 'selected' : ''; ?>>⏳ قدیمی‌ترین ثبت‌نام‌ها</option>
                        <option value="active_desc" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'active_desc') ? 'selected' : ''; ?>>🟢 اخیراً فعال (تازه‌ترین حضور)</option>
                        <option value="active_asc" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'active_asc') ? 'selected' : ''; ?>>🔴 بیشترین زمان غیبت (غیرفعال‌ترین)</option>
                    </select>

                   <!-- فیلتر یکپارچه بازه زمانی (Date Range & Quick Presets) -->
                   <div style="display: flex; align-items: center; gap: 8px; background: var(--admin-bg-color); border: 1px solid var(--admin-border-color); border-radius: 8px; padding: 6px 10px;">
                        
                        <!-- دکمه‌های سریع (بدون پس‌زمینه) -->
                        <select id="userQuickDate" onchange="applyQuickDateFilter()" style="border: none; outline: none; background: transparent; color: var(--admin-primary-color); font-family: 'Vazirmatn'; font-size: 12px; font-weight: bold; cursor: pointer;">
                            <option value="custom">📅 تاریخ دلخواه...</option>
                            <option value="all">همه زمان‌ها</option>
                            <option value="7days">۷ روز گذشته</option>
                            <option value="30days">۳۰ روز گذشته</option>
                            <option value="6months">۶ ماه گذشته</option>
                        </select>

                        <!-- خط جداکننده زیبا -->
                        <div style="width: 1px; height: 20px; background: var(--admin-border-color); margin: 0 2px;"></div>

                        <label style="font-size: 12px; color: var(--admin-text-color); font-weight: bold;">از:</label>
                        <input type="date" id="userFromDate" onchange="resetQuickDateAndFilter()" style="border: none; outline: none; background: transparent; color: var(--admin-text-color); font-family: 'Vazirmatn'; font-size: 12px; cursor: pointer;">
                        
                        <label style="font-size: 12px; color: var(--admin-text-color); font-weight: bold; margin-left: 5px;">تا:</label>
                        <input type="date" id="userToDate" onchange="resetQuickDateAndFilter()" style="border: none; outline: none; background: transparent; color: var(--admin-text-color); font-family: 'Vazirmatn'; font-size: 12px; cursor: pointer;">
                    </div>
                </div>
                
                <?php elseif ($view === 'contracts'): ?>
                <div class="filter-container" style="display: flex; align-items: center; gap: 10px;">
                    <label style="font-weight: bold;">فیلتر قراردادها:</label>
                    <select id="contractFilterSelect" onchange="applyContractFilter()" style="padding: 8px 15px; border-radius: 8px; border: 1px solid var(--admin-border-color); background: var(--admin-bg-color); color: var(--admin-text-color); font-family: 'Vazirmatn'; cursor: pointer;">
                        <option value="all">📄 همه قراردادها</option>
                        <option value="paid">🟢 نهایی شده (موفق)</option>
                        <option value="pending">⏳ در انتظار پرداخت</option>
                        <option value="rejected">🔴 رد شده (لغو)</option>
                    </select>
                </div>

                <?php elseif ($view === 'products'): ?>
                <div class="filter-container" style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                    
                    <?php 
                        // خواندن مقادیر از لینک برای نگه داشتن وضعیت دراپ‌دان‌ها بعد از رفرش
                        $bFilter = $_GET['brand'] ?? 'all';
                        $sFilter = $_GET['status'] ?? 'all';
                        $srtFilter = $_GET['sort'] ?? 'newest';
                    ?>

                    <!-- فیلتر برندها -->
                    <select id="productBrandFilter" onchange="applyProductFilter()" style="padding: 8px; border-radius: 8px; border: 1px solid var(--admin-border-color); background: var(--admin-bg-color); color: var(--admin-text-color); font-family: 'Vazirmatn'; cursor: pointer;">
                        <option value="all" <?php echo $bFilter === 'all' ? 'selected' : ''; ?>>🚗 همه برندها</option>
                        <?php foreach($uniqueBrands as $br): ?>
                            <option value="<?php echo htmlspecialchars($br, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $bFilter === $br ? 'selected' : ''; ?>><?php echo htmlspecialchars($br, ENT_QUOTES, 'UTF-8'); ?></option>
                        <?php endforeach; ?>
                    </select>

                    <!-- فیلتر وضعیت و موجودی -->
                    <select id="productStatusFilter" onchange="applyProductFilter()" style="padding: 8px; border-radius: 8px; border: 1px solid var(--admin-border-color); background: var(--admin-bg-color); color: var(--admin-text-color); font-family: 'Vazirmatn'; cursor: pointer;">
                        <option value="all" <?php echo $sFilter === 'all' ? 'selected' : ''; ?>>📊 همه وضعیت‌ها</option>
                        <option value="in_stock" <?php echo $sFilter === 'in_stock' ? 'selected' : ''; ?>>✅ فقط موجود در انبار</option>
                        <option value="out_of_stock" <?php echo $sFilter === 'out_of_stock' ? 'selected' : ''; ?>>❌ فقط ناموجودها</option>
                        <option value="discount" <?php echo $sFilter === 'discount' ? 'selected' : ''; ?>>🔥 تخفیف‌دارها</option>
                        <option value="slider" <?php echo $sFilter === 'slider' ? 'selected' : ''; ?>>⭐ ویژه (اسلایدر)</option>
                    </select>

                    <!-- سیستم مرتب‌سازی پیشرفته (Sorting) -->
                    <select id="productSortFilter" onchange="applyProductFilter()" style="padding: 8px; border-radius: 8px; border: 1px solid var(--admin-border-color); background: var(--admin-bg-color); color: var(--admin-text-color); font-family: 'Vazirmatn'; cursor: pointer;">
                        <option value="newest" <?php echo $srtFilter === 'newest' ? 'selected' : ''; ?>>🕒 جدیدترین محصولات (زمان ثبت)</option>
                        <option value="oldest" <?php echo $srtFilter === 'oldest' ? 'selected' : ''; ?>>⏳ قدیمی‌ترین محصولات</option>
                        <option value="updated_desc" <?php echo $srtFilter === 'updated_desc' ? 'selected' : ''; ?>>🔄 اخیراً ویرایش شده</option>
                        <option value="updated_asc" <?php echo $srtFilter === 'updated_asc' ? 'selected' : ''; ?>>🕰️ قدیمی‌ترین ویرایش</option>
                        <option value="likes_desc" <?php echo $srtFilter === 'likes_desc' ? 'selected' : ''; ?>>❤️ محبوب‌ترین‌ها (بیشترین تقاضا)</option>
                        <option value="likes_asc" <?php echo $srtFilter === 'likes_asc' ? 'selected' : ''; ?>>💔 کمترین تقاضا</option>
                        <option value="price_desc" <?php echo $srtFilter === 'price_desc' ? 'selected' : ''; ?>>💰 گران‌ترین به ارزان‌ترین</option>
                        <option value="price_asc" <?php echo $srtFilter === 'price_asc' ? 'selected' : ''; ?>>📉 ارزان‌ترین به گران‌ترین</option>
                    </select>

                </div>
                <?php else: ?>
                <div></div> 
                <?php endif; ?>

                <?php if ($view !== 'reports'): ?>
                <a href="export_excel.php?type=<?php echo $view; ?>" class="btn-excel"><i class="fas fa-file-excel"></i> دانلود گزارش اکسل</a>
                <?php endif; ?>
            </div>
<!-- 2. نوار آماری (لینک دار شده با جاوااسکریپت) -->
<div class="summary-badges-container">
                
<?php if ($view === 'users'): ?>
                    <div class="summary-badge" onclick="filterFromCard('all')" style="cursor: pointer;">
                        <div class="badge-icon" style="background: linear-gradient(135deg, #2196F3, #1976D2);"><i class="fas fa-users"></i></div>
                        <div class="badge-info"><span>کل کاربران (یافت شده)</span><strong id="card-total-users"><?php echo number_format($totalUsersCount); ?></strong></div>
                    </div>
                    <div class="summary-badge" onclick="filterFromCard('admin')" style="cursor: pointer;">
                        <div class="badge-icon" style="background: linear-gradient(135deg, #00C853, #009624);"><i class="fas fa-user-shield"></i></div>
                        <div class="badge-info"><span>مدیران (یافت شده)</span><strong id="card-admin-users"><?php echo number_format($adminCount); ?></strong></div>
                    </div>
                    <div class="summary-badge" onclick="filterFromCard('normal')" style="cursor: pointer;">
                        <div class="badge-icon" style="background: linear-gradient(135deg, #FF9800, #F57C00);"><i class="fas fa-user"></i></div>
                        <div class="badge-info"><span>کاربران عادی (یافت شده)</span><strong id="card-normal-users"><?php echo number_format($normalUserCount); ?></strong></div>
                    </div>

                    <?php elseif ($view === 'contracts'): ?>
                    <div class="summary-badge" onclick="window.location.href='admin.php?view=contracts&filter=all'" style="cursor: pointer;">
                        <div class="badge-icon" style="background: linear-gradient(135deg, #9C27B0, #7B1FA2);"><i class="fas fa-file-contract"></i></div>
                        <div class="badge-info"><span>کل قراردادها (یافت شده)</span><strong id="card-total-contracts"><?php echo number_format(count($contracts)); ?></strong></div>
                    </div>
                    <div class="summary-badge" onclick="window.location.href='admin.php?view=contracts&filter=paid'" style="cursor: pointer;">
                        <div class="badge-icon" style="background: linear-gradient(135deg, #4CAF50, #388E3C);"><i class="fas fa-check-double"></i></div>
                        <div class="badge-info"><span>موفق (یافت شده)</span><strong id="card-paid-contracts"><?php echo number_format(count(array_filter($contracts, function($c){return $c['status'] == 'paid';}))); ?></strong></div>
                    </div>
                    <div class="summary-badge" onclick="window.location.href='admin.php?view=contracts&filter=pending'" style="cursor: pointer;">
                        <div class="badge-icon" style="background: linear-gradient(135deg, #FFC107, #FFA000);"><i class="fas fa-hourglass-half"></i></div>
                        <div class="badge-info"><span>در انتظار (یافت شده)</span><strong id="card-pending-contracts"><?php echo number_format(count(array_filter($contracts, function($c){return $c['status'] == 'pending';}))); ?></strong></div>
                    </div>
                    <div class="summary-badge" onclick="window.location.href='admin.php?view=contracts&filter=rejected'" style="cursor: pointer;">
                        <div class="badge-icon" style="background: linear-gradient(135deg, #f44336, #d32f2f);"><i class="fas fa-ban"></i></div>
                        <div class="badge-info"><span>رد شده (یافت شده)</span><strong id="card-rejected-contracts"><?php echo number_format(count(array_filter($contracts, function($c){return $c['status'] == 'rejected';}))); ?></strong></div>
                    </div>

                    <?php elseif ($view === 'products'): ?>
                    <div class="summary-badge">
                        <div class="badge-icon" style="background: linear-gradient(135deg, #3F51B5, #303F9F);"><i class="fas fa-car-side"></i></div>
                        <div class="badge-info"><span>تنوع خودروها (یافت شده)</span><strong id="card-total-products"><?php echo number_format(count($products)); ?></strong></div>
                    </div>
                    <div class="summary-badge">
                        <div class="badge-icon" style="background: linear-gradient(135deg, #009688, #00796B);"><i class="fas fa-warehouse"></i></div>
                        <div class="badge-info"><span>موجودی انبار (یافت شده)</span><strong id="card-total-inventory"><?php echo number_format(array_sum(array_column($products, 'inventory'))); ?></strong></div>
                    </div>
                <?php endif; ?>

            </div>

            <div class="users-table-container">
                <table class="users-table">
                    <?php if ($view === 'users'): ?>
                        <!-- اضافه کردن تیتر تاریخ عضویت -->
                        <thead><tr><th>شناسه</th><th>نام کاربری</th><th>ایمیل</th><th>تلفن</th><th>عضویت</th><th>نقش</th><th>وضعیت</th><th>فعالیت</th><th>عملیات</th></tr></thead>                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr data-user-id="<?php echo $user['id']; ?>" 
                            data-role="<?php echo $user['role']; ?>" 
                            data-orders="<?php echo $user['paid_count']; ?>"
                            data-activity="<?php echo strtotime($user['last_activity'] ?? '0'); ?>"
                            data-created="<?php echo strtotime($user['created_at']); ?>">
                            <td><?php echo $user['id']; ?></td>
                            <td>
                                <?php echo htmlspecialchars($user['user_name']); ?>
                                <?php 
                                    // نمایش مدال VIP بر اساس تعداد خرید
                                    if ($user['paid_count'] >= 3) {
                                        echo '<span title="عضو الماس VIP ('.$user['paid_count'].' خرید)" style="font-size:16px; margin-right:5px; cursor:help;">💎</span>';
                                    } elseif ($user['paid_count'] >= 1) {
                                        echo '<span title="عضو طلایی ('.$user['paid_count'].' خرید)" style="font-size:16px; margin-right:5px; cursor:help;">👑</span>';
                                    } else {
                                        echo '<span title="کاربر عادی (بدون خرید قطعی)" style="font-size:14px; margin-right:5px; cursor:help; opacity:0.5;">🥉</span>';
                                    }
                                ?>
                            </td>                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars($user['phone']); ?></td>
                              <!-- ستون جدید: تاریخ عضویت -->
                              <!-- ستون تاریخ عضویت (اصلاح شده برای جلوگیری از شکستن خط) -->
                            <td style="font-size: 11px; color: gray; white-space: nowrap;" dir="ltr">
                                <?php echo isset($user['created_at']) ? date('Y/m/d', strtotime($user['created_at'])) : 'قدیمی'; ?>
                            </td>
                            <td><?php echo $user['role'] == 1 ? 'مدیر' : 'کاربر عادی'; ?></td>
                            <td><?php echo $user['status'] == 1 ? 'تایید شده' : 'تایید نشده'; ?></td>
                            <td class="activity-status-cell" data-user-id="<?php echo $user['id']; ?>"><span class="activity-status">...</span></td>
                            <td class="actions">
                                <?php if ($user['role'] == 0): ?>
                                    <button class="promote-btn" data-user-id="<?php echo $user['id']; ?>">ارتقا</button>
                                    <button class="delete-btn" data-user-id="<?php echo $user['id']; ?>">حذف</button>
                                <?php else: ?>
                                    <?php if ($user['id'] != $_SESSION['user_id']): ?><button class="demote-btn" data-user-id="<?php echo $user['id']; ?>">تنزل</button><?php else: ?><span class="current-user-label">شما</span><?php endif; ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <?php elseif ($view === 'contracts'): ?>
                    <thead><tr><th>ID کاربر</th><th>کد پیگیری</th><th>خریدار</th><th>خودرو</th><th>قیمت</th><th>وضعیت</th><th>تاریخ</th><th>عملیات</th></tr></thead>
                    <tbody>
                        <?php foreach ($contracts as $contract): ?>
                            <tr data-status="<?php echo $contract['status']; ?>">
                            <td><a href="admin.php?view=users&search=<?php echo $contract['user_id']; ?>" class="user-id-link"><?php echo $contract['user_id']; ?> 🔗</a></td>
                            <td><?php echo htmlspecialchars($contract['tracking_code']); ?></td>
                            <td><?php echo htmlspecialchars($contract['real_name']); ?></td>
                            <td><?php echo htmlspecialchars($contract['car_name']); ?></td>
                            <td><?php echo htmlspecialchars($contract['car_price']); ?></td>
                            <td><?php if($contract['status'] == 'pending'): ?><span class="badge badge-warning">در انتظار</span><?php elseif($contract['status'] == 'paid'): ?><span class="badge badge-success">نهایی شده</span><?php else: ?><span class="badge" style="background:red; color:white;">رد شده</span><?php endif; ?></td>
                                <td style="font-size: 11px; color: gray; text-align: center; line-height: 1.5;" dir="ltr">
                                ثبت: <?php echo date('Y/m/d H:i', strtotime($contract['created_at'])); ?><br>
                                <?php if($contract['status'] === 'paid' && !empty($contract['paid_at'])): ?>
                                    <span style="color: #2e7d32; font-weight: bold;">پرداخت: <?php echo date('Y/m/d H:i', strtotime($contract['paid_at'])); ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="actions" style="display: flex; gap: 5px; justify-content: center; align-items: center;">
                                <button class="view-contract-btn" data-contract="<?php echo htmlspecialchars(json_encode($contract), ENT_QUOTES, 'UTF-8'); ?>">مدیریت</button>
                                
                                <!-- دکمه چاپ سند (در تب جدید باز می‌شود) -->
                                <a href="print_contract.php?id=<?php echo $contract['id']; ?>" target="_blank" class="btn-edit" style="background: #2196F3; text-decoration: none; padding: 6px 10px; font-size: 13px; display: inline-block;">چاپ 🖨️</a>
                                
                                <!-- دکمه حذف هوشمند (برای قراردادهای پرداخت شده مخفی می‌شود) -->
                                <?php if($contract['status'] !== 'paid'): ?>
                                    <button onclick="deleteContractAdmin(<?php echo $contract['id']; ?>)" class="delete-btn" style="padding: 6px 10px; font-size: 13px;">حذف 🗑️</button>
                                <?php endif; ?>
                            </td>                        </tr>
                        <?php endforeach; ?>
                    </tbody>

               <!-- ========================== -->
                    <!-- بخش ۳: جدول محصولات (اصلاح شده و حرفه‌ای) -->
                    <!-- ========================== -->
                    <?php elseif ($view === 'products'): ?>
                        <thead>
                        <tr>
                            <th>تصویر</th>
                            <th>نام خودرو</th>
                            <th>برند</th>
                            <th>وضعیت قیمت و تخفیف</th>
                            <th>میزان تقاضا</th>
                            <th>موجودی</th>
                            <th>زمان ثبت / ویرایش</th> <!-- این تیتر اضافه شد -->
                            <th>وضعیت</th>
                            <th>عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr style="background-color: transparent !important; border:none;">
                            <td colspan="7" style="text-align: left; padding: 10px 0;">
                                <button id="addProductBtn" class="btn-excel" style="background: var(--admin-primary-color); cursor: pointer;">
                                    <i class="fas fa-plus"></i> افزودن خودروی جدید
                                </button>
                            </td>
                        </tr>

                        <?php foreach ($products as $p): ?>
                        <!-- اضافه کردن دیتا برای فیلتر -->
                        <tr class="product-item-row"
                            data-id="<?php echo $p['id']; ?>"
                            data-brand="<?php echo htmlspecialchars($p['brand'], ENT_QUOTES, 'UTF-8'); ?>" 
                            data-discount="<?php echo (!empty($p['old_price']) && $p['old_price'] > 0) ? '1' : '0'; ?>"
                            data-slider="<?php echo $p['in_slider']; ?>"
                            data-stock="<?php echo ($p['inventory'] > 0) ? '1' : '0'; ?>"
                            data-inventory="<?php echo $p['inventory']; ?>"
                            data-price="<?php echo $p['price']; ?>"
                            data-likes="<?php echo $p['likes_count']; ?>"
                            data-created="<?php echo strtotime($p['created_at']); ?>"
                            data-updated="<?php echo strtotime($p['updated_at'] ?? $p['created_at']); ?>">
<!-- نمایش عکس و صدای موتور در جدول -->
<td style="text-align: center;">
                                <img src="<?php echo htmlspecialchars($p['image']); ?>" class="product-img-thumb" alt="car" style="margin-bottom: 5px;">
                                <br>
                                <?php if (!empty($p['engine_sound'])): ?>
                                    <!-- پلیر کوچک برای ادمین -->
                                    <audio controls style="height: 25px; width: 90px; outline: none;">
                                        <source src="<?php echo htmlspecialchars($p['engine_sound']); ?>" type="audio/mpeg">
                                    </audio>
                                <?php else: ?>
                                    <span style="font-size: 10px; color: gray; background: #eee; padding: 2px 5px; border-radius: 4px;">بدون صدا</span>
                                <?php endif; ?>
                            </td>                            <td style="font-weight:bold;"><?php echo htmlspecialchars($p['name']); ?></td>
                            <td style="color:#555;"><?php echo htmlspecialchars($p['brand']); ?></td> 
                            
                            <!-- ستون قیمت جدید با قابلیت تشخیص تخفیف -->
                            <td style="text-align: center; max-width: 160px;">
                                <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 4px;">
                                    
                                    <?php if (!empty($p['old_price']) && $p['old_price'] > 0): ?>
                                        <!-- حالت تخفیف دار -->
                                        <del style="color: #f44336; font-size: 11px; display: block;" dir="ltr">
                                            <?php echo number_format($p['old_price']); ?>
                                        </del>
                                        <div style="color: #4CAF50; font-weight: bold; font-size: 13px;" dir="ltr">
                                            <?php echo number_format($p['price']); ?> <span style="font-size: 10px; color: gray;">تومان</span>
                                        </div>
                                        <span style="background: #ff9800; color: white; border-radius: 4px; padding: 2px 6px; font-size: 10px;">تخفیف دارد</span>
                                    
                                    <?php else: ?>
                                        <!-- حالت قیمت عادی -->
                                        <div style="font-weight: bold; color: var(--admin-text-color); font-size: 13px; word-break: break-word;" dir="ltr">
                                            <?php echo number_format($p['price']); ?> <span style="font-size: 10px; color: gray;">تومان</span>
                                        </div>
                                    <?php endif; ?>

                                </div>
                            </td>
                            <td style="text-align: center;">
    <div style="font-size: 14px; font-weight: bold; color: #e91e63;">
        <?php echo $p['likes_count']; ?> ❤️
    </div>
    
    <?php if ($p['inventory'] == 0 && $p['likes_count'] > 0): ?>
        <!-- سیستم هوشمند: اگر ناموجود بود ولی مردم لایک کرده بودند، هشدار بده! -->
        <span style="display: block; margin-top: 5px; background: #ff9800; color: white; padding: 3px 6px; border-radius: 4px; font-size: 9px; font-weight: bold; animation: pulse 1.5s infinite;">
            🔥 پیشنهاد شارژ انبار
        </span>
    <?php elseif ($p['likes_count'] >= 5): ?>
        <!-- اگر لایک‌ها بالای 5 تا بود، برچسب محبوب بزن -->
        <span style="display: block; margin-top: 5px; background: #9c27b0; color: white; padding: 2px 5px; border-radius: 4px; font-size: 9px;">
            ⭐ بسیار محبوب
        </span>
    <?php endif; ?>
</td>
                            <td><strong style="font-size: 16px; color: var(--admin-primary-color);"><?php echo $p['inventory']; ?></strong></td>
                            <!-- ستون جدید: زمان ثبت و ویرایش -->
                            <td style="font-size: 11px; color: gray; text-align: left;" dir="ltr">
                                ثبت: <?php echo isset($p['created_at']) ? date('Y/m/d H:i', strtotime($p['created_at'])) : '-'; ?><br>
                                <?php if(isset($p['updated_at']) && $p['updated_at'] !== $p['created_at']): ?>
                                    ویرایش: <?php echo date('Y/m/d H:i', strtotime($p['updated_at'])); ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php echo $p['in_slider'] ? '<span class="badge badge-success">اسلایدر</span>' : '<span class="badge" style="background: #757575; color: white;">عادی</span>'; ?>
                            </td>
                            
                            <td class="actions">
                                <!-- استفاده از JSON_UNESCAPED_UNICODE برای جلوگیری از خراب شدن حروف فارسی در دیتا -->
                                <button class="btn-edit edit-product-btn" data-product='<?php echo json_encode($p, JSON_HEX_APOS | JSON_UNESCAPED_UNICODE); ?>'>ویرایش</button>
                                <button class="delete-btn" onclick="deleteProduct(<?php echo $p['id']; ?>)">حذف</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <?php endif; ?>
                </table>
            </div>
            <?php endif; ?>
            <?php if ($view === 'reports'): ?>
  <!-- هدر ابزارها ایمیل + دراپ‌دان -->
  <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; margin-bottom: 15px; gap: 10px;">
        <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
            <!-- فیلتر ایمیل‌ها -->
            <select id="emailStatusFilter" onchange="applyReportFilters()" style="padding: 8px; border-radius: 8px; border: 1px solid var(--admin-border-color); background: var(--admin-bg-color); color: var(--admin-text-color); font-family: 'Vazirmatn'; cursor: pointer;">
                <option value="all">📧 همه ایمیل‌ها</option>
                <option value="sent">✅ ارسال شده</option>
                <option value="pending">⏳ در انتظار</option>
                <option value="failed">❌ ناموفق</option>
            </select>
            
            <button onclick="bulkDeleteEmails('failed')" style="background: var(--admin-danger-color); color: white; border: none; padding: 8px 15px; border-radius: 8px; cursor: pointer; font-family: 'Vazirmatn'; font-weight: bold;">🗑️ حذف همه ناموفق‌ها</button>
            <button onclick="retryAllFailedEmails()" style="background: var(--admin-secondary-color); color: white; border: none; padding: 8px 15px; border-radius: 8px; cursor: pointer; font-family: 'Vazirmatn'; font-weight: bold;">🔄 تلاش مجدد برای همه</button>
            <button onclick="bulkDeleteEmails('sent')" style="background: #757575; color: white; border: none; padding: 8px 15px; border-radius: 8px; cursor: pointer; font-family: 'Vazirmatn'; font-weight: bold;">🧹 پاکسازی ارسال‌شده‌ها</button>
            <button id="btnDelSelEmail" onclick="deleteSelected('email')" style="display:none; background: #e91e63; color: white; border: none; padding: 8px 15px; border-radius: 8px; cursor: pointer; font-family: 'Vazirmatn'; font-weight: bold;"></button>
        </div>
        <a href="export_excel.php?type=reports" class="btn-excel"><i class="fas fa-file-excel"></i> دانلود گزارش اکسل</a>
    </div>

    <!-- مینی داشبورد آماری (لینک‌دار شده) -->
    <div class="summary-badges-container">
        <div class="summary-badge" onclick="filterReports('email', 'sent')" style="cursor: pointer;">
            <div class="badge-icon" style="background: linear-gradient(135deg, #4CAF50, #388E3C);"><i class="fas fa-paper-plane"></i></div>
            <div class="badge-info"><span>ایمیل‌های موفق</span><strong id="live-sent"><?php echo number_format($emailStats['sent']); ?></strong></div>
        </div>
        <div class="summary-badge" onclick="filterReports('email', 'pending')" style="cursor: pointer;">
            <div class="badge-icon" style="background: linear-gradient(135deg, #FFC107, #FFA000);"><i class="fas fa-hourglass-half"></i></div>
            <div class="badge-info"><span>در صف انتظار</span><strong id="live-pending"><?php echo number_format($emailStats['pending'] + $emailStats['processing']); ?></strong></div>
        </div>
        <div class="summary-badge" onclick="filterReports('email', 'failed')" style="cursor: pointer;">
            <div class="badge-icon" style="background: linear-gradient(135deg, #f44336, #d32f2f);"><i class="fas fa-exclamation-triangle"></i></div>
            <div class="badge-info"><span>خطا در ارسال</span><strong id="live-failed"><?php echo number_format($emailStats['failed']); ?></strong></div>
        </div>
        <div class="summary-badge" onclick="filterReports('notif', 'all')" style="cursor: pointer;">
            <div class="badge-icon" style="background: linear-gradient(135deg, #9C27B0, #7B1FA2);"><i class="fas fa-bell"></i></div>
            <div class="badge-info"><span>کل نوتیفیکیشن‌ها</span><strong><?php echo number_format($totalNotifs); ?></strong></div>
        </div>
    </div>

    <!-- جدول گزارشات ایمیل -->
    <div class="users-table-container">
        <table class="users-table">
            <thead>
                <tr>
                <th style="width: 30px;"><input type="checkbox" id="selectAllEmails" onclick="toggleAll(this, 'email-cb')"></th>
                    <th>گیرنده</th>
                    <th>موضوع ایمیل</th>
                    <th>وضعیت</th>
                    <th>زمان ایجاد / ارسال</th>
                    <th>عملیات</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($emails)) echo "<tr><td colspan='5' style='text-align:center;'>هیچ گزارشی یافت نشد.</td></tr>"; ?>
                <?php foreach ($emails as $em): ?>
                    <tr class="email-item-row" id="email-row-<?php echo $em['id']; ?>" data-status="<?php echo $em['status']; ?>">
                <td><input type="checkbox" class="email-cb" value="<?php echo $em['id']; ?>" onclick="checkSelection('email')"></td>
                    <td>
                        <div style="font-weight:bold; font-size: 13px;"><?php echo htmlspecialchars($em['recipient_name'], ENT_QUOTES, 'UTF-8'); ?></div>
                        <div style="color:gray; font-size: 11px; direction: ltr; text-align: right;"><?php echo htmlspecialchars($em['recipient_email'], ENT_QUOTES, 'UTF-8'); ?></div>
                    </td>
                    <td style="font-size: 12px; max-width: 250px; white-space: normal; line-height: 1.5;">
                        <?php echo htmlspecialchars($em['subject'], ENT_QUOTES, 'UTF-8'); ?>
                    </td>
                    <td>
                        <?php if($em['status'] == 'sent'): ?>
                            <span class="badge badge-success">ارسال شده</span>
                        <?php elseif($em['status'] == 'failed'): ?>
                            <span class="badge" style="background:#f44336; color:white;">ناموفق</span>
                        <?php else: ?>
                            <span class="badge badge-warning">در صف انتظار</span>
                        <?php endif; ?>
                    </td>
                    <td style="font-size: 11px; color: gray;" dir="ltr">
                        ایجاد: <?php echo $em['created_at']; ?><br>
                        ارسال: <?php echo $em['sent_at'] ? $em['sent_at'] : '-'; ?>
                    </td>
                    <td class="actions">
                        <?php if($em['status'] == 'failed'): ?>
                            <!-- دکمه تلاش مجدد فقط برای ایمیل‌های ناموفق -->
                            <button onclick="retryEmail(<?php echo $em['id']; ?>)" style="background: var(--admin-secondary-color); color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer; font-size: 11px; font-family:'Vazirmatn';">تلاش مجدد 🔄</button>
                        <?php endif; ?>
                        <button onclick="deleteEmail(<?php echo $em['id']; ?>)" class="delete-btn" style="padding: 5px 10px; font-size: 11px;">حذف 🗑️</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

  <!-- ============================================== -->
    <!-- بخش دوم گزارشات: مدیریت نوتیفیکیشن‌ها + دراپ‌دان -->
    <!-- ============================================== -->
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; margin: 40px 0 15px 0; gap: 10px; border-top: 2px dashed var(--admin-border-color); padding-top: 30px;">
        <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
            <h3 style="margin: 0; color: var(--admin-primary-color);">🔔 تاریخچه نوتیفیکیشن‌ها</h3>
            
            <!-- فیلتر نوتیفیکیشن‌ها -->
            <select id="notifTypeFilter" onchange="applyReportFilters()" style="padding: 8px; border-radius: 8px; border: 1px solid var(--admin-border-color); background: var(--admin-bg-color); color: var(--admin-text-color); font-family: 'Vazirmatn'; cursor: pointer;">
                <option value="all">🔔 همه اعلان‌ها</option>
                <option value="new_product">🆕 محصول جدید</option>
                <option value="restock">🔥 شارژ انبار</option>
                <option value="ticket_reply">💬 پاسخ تیکت</option>
                <option value="system_broadcast">📢 پیام همگانی</option>
            </select>
        </div>
        
        <div style="display: flex; gap: 10px;">
            <button id="btnDelSelNotif" onclick="deleteSelected('notif')" style="display:none; background: #e91e63; color: white; border: none; padding: 8px 15px; border-radius: 8px; cursor: pointer; font-family: 'Vazirmatn'; font-weight: bold;"></button>
            <button onclick="bulkDeleteNotifs()" style="background: var(--admin-danger-color); color: white; border: none; padding: 8px 15px; border-radius: 8px; cursor: pointer; font-family: 'Vazirmatn'; font-weight: bold;">🗑️ حذف کل اعلانات سایت</button>
        </div>
    </div>

    <div class="users-table-container">
        <table class="users-table">
            <thead>
                <tr>
                <th style="width: 30px;"><input type="checkbox" id="selectAllNotifs" onclick="toggleAll(this, 'notif-cb')"></th>
                    <th>گیرنده پیام</th>
                    <th>نوع اعلان</th>
                    <th>محتوای پیام</th>
                    <th>وضعیت</th>
                    <th>زمان ارسال</th>
                    <th>عملیات</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($admin_notifs)) echo "<tr><td colspan='6' style='text-align:center;'>هیچ اعلانی در سیستم ثبت نشده است.</td></tr>"; ?>
                <?php foreach ($admin_notifs as $n): ?>
                    <tr class="notif-item-row" id="notif-admin-row-<?php echo $n['id']; ?>" data-type="<?php echo $n['type']; ?>">
                <td><input type="checkbox" class="notif-cb" value="<?php echo $n['id']; ?>" onclick="checkSelection('notif')"></td>
                    <td style="font-weight:bold; font-size: 13px;"><?php echo htmlspecialchars($n['user_name']); ?></td>
                    <td>
                        <?php 
                            if($n['type'] == 'new_product') echo '<span class="badge" style="background:#9c27b0; color:white;">محصول جدید</span>';
                            elseif($n['type'] == 'restock') echo '<span class="badge" style="background:#ff9800; color:white;">شارژ انبار</span>';
                            elseif($n['type'] == 'ticket_reply') echo '<span class="badge" style="background:#2196F3; color:white;">پاسخ تیکت</span>';
                            else echo '<span class="badge" style="background:#757575; color:white;">عمومی</span>';
                        ?>
                    </td>
                    <td style="font-size: 12px; max-width: 250px; white-space: normal; line-height: 1.5;">
                        <strong style="display: block; margin-bottom: 5px;"><?php echo htmlspecialchars($n['title']); ?></strong>
                        <span style="color: var(--secondary-text);"><?php echo htmlspecialchars($n['message']); ?></span>
                    </td>
                    <td>
                        <?php if($n['is_read'] == 1): ?>
                            <span style="color: #4CAF50; font-size: 11px;">👁️ خوانده شده</span>
                        <?php else: ?>
                            <span style="color: gray; font-size: 11px;">✉️ تحویل داده شده</span>
                        <?php endif; ?>
                    </td>
                    <td style="font-size: 11px; color: gray;" dir="ltr"><?php echo $n['created_at']; ?></td>
                    <td class="actions">
                        <button onclick="deleteAdminNotif(<?php echo $n['id']; ?>)" class="delete-btn" style="padding: 5px 10px; font-size: 11px;">حذف 🗑️</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

   <!-- اسکریپت‌های اختصاصی بخش گزارشات سیستم -->
   <script>
        // ==========================================
        // 1. عملیات مدیریت نوتیفیکیشن‌ها
        // ==========================================
        function deleteAdminNotif(id) {
            if(!confirm('آیا از حذف این نوتیفیکیشن از پنل کاربر مطمئن هستید؟')) return;
            fetch('admin_actions.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=delete_notif_admin&notif_id=${id}`
            }).then(r => r.json()).then(d => {
                if(d.success) {
                    const row = document.getElementById('notif-admin-row-' + id);
                    if(row) {
                        row.style.opacity = '0';
                        setTimeout(() => row.remove(), 300);
                    }
                }
            });
        }

        function bulkDeleteNotifs() {
            if(!confirm('هشدار! با این کار تمام نوتیفیکیشن‌های کاربران از دیتابیس پاک خواهد شد. آیا مطمئن هستید؟')) return;
            fetch('admin_actions.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=delete_bulk_notifs_admin`
            }).then(r => r.json()).then(d => {
                if(d.success) location.reload();
            });
        }

        // ==========================================
        // 2. عملیات مدیریت ایمیل‌ها
        // ==========================================
        function retryEmail(id) {
            if(!confirm('آیا می‌خواهید این ایمیل دوباره به صف ارسال برگردد؟')) return;
            fetch('admin_actions.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=retry_email&email_id=${id}`
            }).then(r => r.json()).then(d => {
                if(d.success) location.reload();
            });
        }

        function retryAllFailedEmails() {
            if(!confirm('آیا می‌خواهید تمام ایمیل‌های ناموفق دوباره به صف ارسال برگردند؟')) return;
            fetch('admin_actions.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=retry_all_failed`
            }).then(r => r.json()).then(d => {
                if(d.success) location.reload();
            });
        }

        function deleteEmail(id) {
            if(!confirm('آیا از حذف این گزارش مطمئن هستید؟')) return;
            fetch('admin_actions.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=delete_email&email_id=${id}`
            }).then(r => r.json()).then(d => {
                if(d.success) {
                    const row = document.getElementById('email-row-' + id);
                    if(row) {
                        row.style.opacity = '0';
                        setTimeout(() => row.remove(), 300);
                    }
                }
            });
        }

        function bulkDeleteEmails(type) {
            const msg = type === 'failed' ? 'آیا از حذف تمام ایمیل‌های ناموفق مطمئن هستید؟' : 'آیا از پاکسازی تمام گزارش‌های ارسال‌شده مطمئن هستید؟';
            if(!confirm(msg)) return;
            
            fetch('admin_actions.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=delete_bulk_emails&del_type=${type}`
            }).then(r => r.json()).then(d => {
                if(d.success) location.reload();
            });
        }

        // ==========================================
        // 3. سیستم انتخاب گروهی (Bulk Selection)
        // ==========================================
        function toggleAll(source, className) {
            const checkboxes = document.querySelectorAll('.' + className);
            checkboxes.forEach(cb => {
                const row = cb.closest('tr');
                if (row.style.display !== 'none') {
                    cb.checked = source.checked;
                }
            });
            const type = className.split('-')[0];
            checkSelection(type);
        }

        function checkSelection(type) {
            const checkedBoxes = document.querySelectorAll(`.${type}-cb:checked`);
            const btn = document.getElementById(`btnDelSel${type === 'email' ? 'Email' : 'Notif'}`);
            
            if (checkedBoxes.length > 0) {
                btn.style.display = 'inline-block';
                btn.innerText = `حذف ${checkedBoxes.length} مورد انتخاب شده 🗑️`;
                btn.style.animation = "popBadge 0.3s ease"; 
            } else {
                btn.style.display = 'none';
                document.getElementById(`selectAll${type === 'email' ? 'Emails' : 'Notifs'}`).checked = false;
            }
        }

        async function deleteSelected(type) {
            const checkedBoxes = document.querySelectorAll(`.${type}-cb:checked`);
            if (checkedBoxes.length === 0) return;

            if (!confirm(`آیا از حذف برای همیشه ${checkedBoxes.length} مورد مطمئن هستید؟`)) return;

            const ids = Array.from(checkedBoxes).map(cb => cb.value);
            const actionName = type === 'email' ? 'delete_selected_emails' : 'delete_selected_notifs';

            try {
                const res = await fetch('admin_actions.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=${actionName}&ids=${JSON.stringify(ids)}`
                });
                const data = await res.json();
                
                if (data.success) {
                    checkedBoxes.forEach(cb => {
                        const row = cb.closest('tr');
                        row.style.opacity = '0';
                        row.style.transform = 'translateX(20px)';
                        row.style.transition = 'all 0.3s ease';
                        setTimeout(() => row.remove(), 300);
                    });
                    document.getElementById(`btnDelSel${type === 'email' ? 'Email' : 'Notif'}`).style.display = 'none';
                    document.getElementById(`selectAll${type === 'email' ? 'Emails' : 'Notifs'}`).checked = false;
                }
            } catch (err) {
                console.error('خطا در حذف گروهی:', err);
            }
        }

    // ==========================================
        // موتور مانیتورینگ زنده (Live AJAX Polling)
        // ==========================================
        setInterval(async () => {
            try {
                const res = await fetch('api_live_reports.php');
                const data = await res.json();
                
                if (data.success) {
                    // 1. آپدیت کردن اعداد مینی‌داشبورد
                    const pendingTotal = (data.stats.pending || 0) + (data.stats.processing || 0);
                    
                    const elSent = document.getElementById('live-sent');
                    if(elSent) elSent.innerText = new Intl.NumberFormat().format(data.stats.sent || 0);
                    
                    const elPending = document.getElementById('live-pending');
                    if(elPending) elPending.innerText = new Intl.NumberFormat().format(pendingTotal);
                    
                    const elFailed = document.getElementById('live-failed');
                    if(elFailed) elFailed.innerText = new Intl.NumberFormat().format(data.stats.failed || 0);

                    // 2. آپدیت زنده جدول و رنگ‌ها
                    data.emails.forEach(em => {
                        const row = document.getElementById('email-row-' + em.id);
                        if (row) {
                            // 🟢 LINE 1: This goes right here, inside the "if(row)" block! 
                            // It updates the secret HTML attribute so the filter knows the new status.
                            row.setAttribute('data-status', em.status);

                            const statusTd = row.cells[3]; 
                            const timeTd = row.cells[4];   
                            const actionTd = row.cells[5]; 

                            if (em.status === 'sent') {
                                statusTd.innerHTML = '<span class="badge badge-success">ارسال شده</span>';
                                if(!timeTd.innerHTML.includes(em.sent_at)) {
                                    timeTd.innerHTML = timeTd.innerHTML.split('<br>')[0] + '<br>ارسال: ' + em.sent_at;
                                }
                                if(actionTd.innerHTML.includes('تلاش مجدد')) {
                                    actionTd.innerHTML = `<button onclick="deleteEmail(${em.id})" class="delete-btn" style="padding: 5px 10px; font-size: 11px;">حذف 🗑️</button>`;
                                }
                            } 
                            else if (em.status === 'failed') {
                                statusTd.innerHTML = '<span class="badge" style="background:#f44336; color:white;">ناموفق</span>';
                                actionTd.innerHTML = `
                                    <button onclick="retryEmail(${em.id})" style="background: var(--admin-secondary-color); color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer; font-size: 11px; font-family:'Vazirmatn'; margin-left: 5px;">تلاش مجدد 🔄</button>
                                    <button onclick="deleteEmail(${em.id})" class="delete-btn" style="padding: 5px 10px; font-size: 11px;">حذف 🗑️</button>
                                `;
                            } 
                            else {
                                statusTd.innerHTML = '<span class="badge badge-warning">در صف انتظار</span>';
                                actionTd.innerHTML = `<button onclick="deleteEmail(${em.id})" class="delete-btn" style="padding: 5px 10px; font-size: 11px;">حذف 🗑️</button>`;
                            }
                        }
                    }); // <-- End of the forEach loop

                    // 🟢 LINE 2: This goes right here, OUTSIDE the loop, but INSIDE "if (data.success)"!
                    // After all rows are updated, this triggers the filter to hide/show the newly updated rows.
                    if(typeof applyReportFilters === 'function') applyReportFilters();
                }
            } catch (err) {
                console.log("در حال تلاش برای اتصال لایو...");
            }
        }, 5000);
    </script>
<?php endif; ?>
        </div>
    </div>

    <!-- مودال‌های تایید و قرارداد -->
    <div id="confirmationModal" class="confirmation-modal">
        <div class="confirmation-dialog">
            <h3 id="confirmationTitle">تایید عملیات</h3><p id="confirmationMessage"></p>
            <div class="confirmation-buttons"><button id="cancelActionBtn">انصراف</button><button id="confirmActionBtn">تایید</button></div>
        </div>
    </div>

    <div id="contractDetailsModal" class="confirmation-modal">
        <div class="confirmation-dialog" style="max-width: 600px; width: 95%;">
            <div style="display:flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--admin-border-color); padding-bottom: 10px; margin-bottom: 15px;">
                <h3 style="margin: 0; color: var(--admin-primary-color);">مدیریت قرارداد</h3>
                <button id="closeContractModalBtn" style="background: none; border: none; font-size: 24px; color: var(--admin-text-color); cursor: pointer;">&times;</button>
            </div>
            <div style="max-height: 65vh; overflow-y: auto; text-align: right; line-height: 1.8;">
                <div class="info-grid">
                    <p><strong>کد پیگیری:</strong> <span id="cd-tracking"></span></p>
                    <p><strong>تاریخ ثبت:</strong> <span id="cd-date" dir="ltr"></span></p>
                    <p><strong>نام خریدار:</strong> <span id="cd-name"></span></p>
                    <p><strong>کد ملی:</strong> <span id="cd-nid"></span></p>
                    <p><strong>تلفن:</strong> <span id="cd-phone" dir="ltr"></span></p>
                    <p><strong>کد پستی:</strong> <span id="cd-postal"></span></p>
                </div>
                <p><strong>آدرس:</strong> <span id="cd-address"></span></p>
                <hr style="border: 0; border-top: 1px solid var(--admin-border-color); margin: 15px 0;">
                <div class="info-grid">
                    <p><strong>خودرو:</strong> <span id="cd-car"></span></p>
                    <p><strong>رنگ:</strong> <span id="cd-color"></span></p>
                    <p><strong>قیمت:</strong> <span id="cd-price"></span></p>
                </div>
                <div class="status-change-box">
                    <label for="cd-status-select">تغییر وضعیت قرارداد:</label>
                    <div class="status-actions">
                        <select id="cd-status-select" class="status-select">
                            <option value="pending">🟡 در انتظار پرداخت</option>
                            <option value="paid">🟢 نهایی شده (موفق)</option>
                            <option value="rejected">🔴 رد شده (لغو)</option>
                        </select>
                        <button id="updateStatusBtn" class="btn-update">ثبت تغییر</button>
                    </div>
                </div>
                <div class="signature-display-box">
                    <strong class="signature-title">امضای دیجیتال خریدار:</strong>
                    <img id="cd-signature" class="signature-img" src="" alt="امضا">
                </div>
            </div>
        </div>
    </div>

    <!-- Product Modal (فرم افزودن و ویرایش) -->
    <div id="productModal" class="confirmation-modal">
        <div class="confirmation-dialog" style="max-width: 600px; width: 95%;">
            <div style="display:flex;  justify-content: space-between; margin-bottom: 15px;">
                <h3 id="productModalTitle" style="color:var(--admin-primary-color);">افزودن محصول جدید</h3>
                <button onclick="document.getElementById('productModal').style.display='none'" style="background:none; border:none; font-size:24px; cursor:pointer; color: var(--admin-text-color);">&times;</button>
            </div>
            
            <form id="productForm" enctype="multipart/form-data">
                <input type="hidden" name="action" id="productAction" value="add_product">
                <input type="hidden" name="product_id" id="productId">
                
                <div class="modal-grid-2">
                    <div class="input-group">
                        <label>نام خودرو</label>
                        <input type="text" name="name" id="pName" required style="width:100%; padding:8px; border:1px solid #ccc; border-radius:5px;">
                    </div>
                    <!-- <div class="input-group">
                        <label>برند سازنده</label>
                        <select name="brand" id="pBrand" required style="width:100%; padding:8px; border:1px solid #ccc; border-radius:5px; font-family:'Vazirmatn'; background:#fff;">
                            <option value="">انتخاب کنید...</option>
                            <option value="آئودی">آئودی</option>
                            <option value="لامبورگینی">لامبورگینی</option>
                            <option value="رولزرویس">رولزرویس</option>
                            <option value="فراری">فراری</option>
                            <option value="رنج روور">رنج روور</option>
                            <option value="مرسدس بنز">مرسدس بنز</option>
                            <option value="سایر">سایر</option>
                        </select>
                    </div> -->

                    <div class="input-group">
                        <label>برند سازنده</label>
                        <!-- اینپوت با قابلیت تایپ و انتخاب -->
                        <input type="text" name="brand" id="pBrand" list="brandListSuggestions" 
                               placeholder="تایپ کنید یا از لیست انتخاب کنید..." 
                               required 
                               style="width:100%; padding:8px; border:1px solid #ccc; border-radius:5px; font-family:'Vazirmatn';">
                        
                        <!-- لیست پیشنهادها (از برندهای موجود در دیتابیس پر میشه) -->
                        <datalist id="brandListSuggestions">
                            <option value="آئودی">
                            <option value="لامبورگینی">
                            <option value="رولزرویس">
                            <option value="فراری">
                            <option value="رنج روور">
                            <option value="مرسدس بنز">
                            <option value="سایر">
                            <!-- اضافه کردن برندهایی که قبلاً ثبت شده -->
                            <?php if(!empty($uniqueBrands)): ?>
                                <?php foreach($uniqueBrands as $ub): ?>
                                    <option value="<?php echo htmlspecialchars($ub); ?>">
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </datalist>
                    </div>
                </div>

                <div class="modal-grid-2">
                    <div class="input-group">
                        <label>قیمت نهایی (تومان)</label>
                        <!-- ترکیب طلایی برای قیمت: text + inputmode + regex -->
                        <input type="text" name="price" id="pPrice" required 
                               inputmode="numeric" 
                               oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                               placeholder="فقط عدد (بدون ویرگول)"
                               style="width:100%; padding:8px; border:1px solid #ccc; border-radius:5px;">
                    </div>
                    <div class="input-group">
                        <label>قیمت قبل تخفیف (اختیاری)</label>
                        <input type="text" name="old_price" id="pOldPrice" 
                               inputmode="numeric" 
                               oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                               style="width:100%; padding:8px; border:1px solid #ccc; border-radius:5px;">
                    </div>
                </div>

                <div class="modal-grid-2">
                    <!-- <div class="input-group">
                        <label>موجودی انبار</label>
                        <input type="text" name="inventory" id="pInventory" value="1" required 
                               inputmode="numeric" 
                               oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                               style="width:100%; padding:8px; border:1px solid #ccc; border-radius:5px;">
                    </div> -->

                    <!-- بخش جدید موجودی بر اساس رنگ -->
<!-- بخش جدید موجودی بر اساس رنگ (نسخه اصلاح شده) -->
<!-- <div class="input-group" style="grid-column: span 2;">
    <label>موجودی انبار بر اساس رنگ (تعداد هر رنگ را وارد کنید)</label>
    
    <div class="color-inventory-container">
        <div class="color-box">
            <label>مشکی ⬛</label>
            <input type="number" name="color_black" id="pColorBlack" value="0" min="0">
        </div>
        <div class="color-box">
            <label>سفید ⬜</label>
            <input type="number" name="color_white" id="pColorWhite" value="0" min="0">
        </div>
        <div class="color-box">
            <label>قرمز 🟥</label>
            <input type="number" name="color_red" id="pColorRed" value="0" min="0">
        </div>
        <div class="color-box">
            <label>آبی 🟦</label>
            <input type="number" name="color_blue" id="pColorBlue" value="0" min="0">
        </div>
        <div class="color-box">
            <label>نقره‌ای 🔲</label>
            <input type="number" name="color_silver" id="pColorSilver" value="0" min="0">
        </div>
    </div>

    <small style="color: gray; display:block; margin-top:5px;">جمع کل موجودی به صورت خودکار در سایت محاسبه می‌شود.</small>
</div> -->

<!-- بخش افزودن داینامیک رنگ‌ها -->
<div class="input-group" style="grid-column: span 2;">
    <label>رنگ‌بندی و موجودی (هر رنگی که ماشین دارد را اضافه کنید)</label>
    
    <div id="dynamicColorsContainer" style="display: flex; flex-direction: column; gap: 10px; background: rgba(128, 128, 128, 0.1); padding: 10px; border-radius: 5px; border: 1px solid var(--admin-border-color); max-height: 200px; overflow-y: auto;">
        <!-- ردیف‌های رنگ با جاوااسکریپت اینجا اضافه می‌شوند -->
    </div>
    
    <button type="button" id="addNewColorBtn" style="margin-top: 10px; padding: 8px; background: var(--admin-secondary-color); color: white; border: none; border-radius: 5px; cursor: pointer; font-family: 'Vazirmatn';">
        + افزودن رنگ جدید
    </button>
    <small style="color: gray; display:block; margin-top:5px;">جمع کل موجودی به صورت خودکار محاسبه می‌شود.</small>
</div>
                    <div class="input-group">
    <label>قدرت (اسب بخار)</label>
    <input type="text" name="hp" id="pHp" placeholder="مثلاً 700" style="width:100%; padding:8px; border:1px solid #ccc; border-radius:5px;">
</div>
                </div>

                <div class="modal-grid-2" style="margin-top:10px;">
                <div class="input-group">
    <label>شتاب 0-100 (ثانیه)</label>
    <input type="text" name="accel" id="pAccel" placeholder="مثلاً 2.9" style="width:100%; padding:8px; border:1px solid #ccc; border-radius:5px;">
</div>                    <div class="input-group"><label>موتور</label><input type="text" name="engine" id="pEngine" placeholder="مثلا V12 Turbo" style="width:100%; padding:8px; border:1px solid #ccc; border-radius:5px;"></div>
                </div>

                <div class="slider-check-container">
                    <input type="checkbox" name="in_slider" id="pInSlider">
                    <label for="pInSlider" style="cursor:pointer; margin-right:5px;">نمایش در اسلایدر بالای سایت (ویژه)</label>
                </div>
                

                <div class="file-upload-box">
                    <label style="display:block; margin-bottom:5px; font-weight:bold;">تصویر خودرو</label>
                    <input type="file" name="product_image" id="pImage" accept="image/*">
                    <p style="font-size:11px; color:#666; margin-top:5px;">فرمت‌های مجاز: JPG, PNG, WEBP</p>
                </div>

                <!-- کادر آپلود صدای موتور -->
<!-- کادر آپلود و پخش صدای موتور -->
<div class="file-upload-box" style="margin-top: 15px; border-color: #ff9800; background: rgba(255, 152, 0, 0.05);">
                    <label style="display:block; margin-bottom:5px; font-weight:bold; color: #e65100;">🔊 صدای موتور (اختیاری)</label>
                    
                    <!-- اینجا اگر ماشین از قبل صدا داشته باشه نشون داده میشه -->
                    <div id="currentSoundContainer" style="display: none; margin-bottom: 10px; background: #fff; padding: 5px; border-radius: 5px; border: 1px solid #ffcc80;">
                        <span style="font-size: 11px; color: #d84315; display: block; margin-bottom: 5px;">صدای فعلی:</span>
                        <audio id="currentSoundPlayer" controls style="width: 100%; height: 30px;"></audio>
                    </div>

                    <input type="file" name="engine_sound" id="pSound" accept="audio/mp3, audio/wav, audio/ogg">
                    <p style="font-size:11px; color:#666; margin-top:5px;">فرمت‌های مجاز: MP3, WAV (حجم کمتر از 2 مگابایت)</p>
                </div>
<!-- سیستم هوشمند اعلان محصول جدید -->
<div class="input-group" id="notificationSettingsGroup" style="margin-top: 15px; border-top: 1px dashed var(--admin-border-color); padding-top: 15px;">
    
    <!-- انتخاب گروه هدف -->
    <label style="color: var(--admin-primary-color); font-weight: bold; margin-bottom: 5px; display: block;">📢 چه کسانی از این محصول باخبر شوند؟</label>
    <select name="notif_target" id="pNotifTarget" style="width:100%; padding:8px; border:1px solid #ccc; border-radius:5px; font-family:'Vazirmatn'; background:#fff; margin-bottom: 15px;">
        <option value="none">🔕 بدون ارسال اعلان (اضافه شدن در سکوت)</option>
        <option value="diamond">💎 فقط کاربران الماس (۳ خرید به بالا)</option>
        <option value="gold_diamond">👑 کاربران طلایی و الماس (حداقل ۱ خرید)</option>
        <option value="all">👥 ارسال برای تمام کاربران سایت</option>
    </select>

    <!-- انتخاب روش ارسال -->
    <label style="color: var(--admin-primary-color); font-weight: bold; margin-bottom: 5px; display: block;">نحوه ارسال اعلان (جلوگیری از اسپم)</label>
    <select name="notif_method" id="pNotifMethod" style="width:100%; padding:8px; border:1px solid #ccc; border-radius:5px; font-family:'Vazirmatn'; background:#fff;">
        <option value="bell_only">🔔 فقط زنگوله سایت (بدون ارسال ایمیل)</option>
        <option value="both">📧 زنگوله سایت + ارسال ایمیل</option>
    </select>
    <small style="color: gray; display:block; margin-top:5px; font-size: 11px;">پیشنهاد: ایمیل را فقط برای خودروهای بسیار خاص روشن کنید.</small>

</div>
                <button type="submit" class="btn-update" style="width:100%; padding:12px; margin-top:10px;">ذخیره تغییرات</button>
            </form>
        </div>
    </div>

    <script src="js/login signup.js"></script>
    <script src="js/activity_tracker.js"></script>
    <script>
    // متغیرهای گلوبال برای دسترسی راحت‌تر
    let currentAction = null;
    let currentTargetId = null;
    let statusUpdateInterval;

    document.addEventListener('DOMContentLoaded', function() {

     // ============================================================
        // رندر کردن نمودارهای گرافیکی (Chart.js) - واکنش‌گرای زنده به دارک مود
        // ============================================================
        <?php if ($view === 'dashboard'): ?>
        
        // متغیرها برای نگهداری نمونه‌ی نمودارها (تا بتونیم بعدا رنگشون رو عوض کنیم)
        let statusChartInst, trendChartInst, topCarsChartInst;

        // تنظیم فونت پایه
        Chart.defaults.font.family = 'Vazirmatn';

        function initCharts() {
            const isDark = document.body.classList.contains('darkmode');
            // رنگ‌ها رو کمی پررنگ‌تر کردم تا قشنگ دیده بشن
            const gridLineColor = isDark ? 'rgba(255, 255, 255, 0.15)' : 'rgba(0, 0, 0, 0.1)';
            const gridBorderColor = isDark ? 'rgba(255, 255, 255, 0.3)' : 'rgba(0, 0, 0, 0.2)'; // خطوط اصلی محور X و Y
            const labelColor = isDark ? '#eeeeee' : '#666666';

            Chart.defaults.color = labelColor;

            // 1. نمودار دایره ای
            const statusCtx = document.getElementById('statusChart');
            if (statusCtx) {
                statusChartInst = new Chart(statusCtx, {
                    type: 'doughnut',
                    data: {
                        labels:['نهایی شده', 'در انتظار', 'رد شده'],
                        datasets: [{
                            data:[
                                <?php echo $chartStatusData['paid'] ?? 0; ?>, 
                                <?php echo $chartStatusData['pending'] ?? 0; ?>, 
                                <?php echo $chartStatusData['rejected'] ?? 0; ?>
                            ],
                            backgroundColor:['#4CAF50', '#FFC107', '#f44336'],
                            borderWidth: isDark ? 2 : 0,
                            borderColor: isDark ? '#1e1e1e' : '#fff',
                            hoverOffset: 10
                        }]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false, cutout: '70%',
                        plugins: { legend: { position: 'bottom', labels: { color: labelColor } } }
                    }
                });
            }

            // 2. نمودار خطی
            const trendCtx = document.getElementById('trendChart');
            if (trendCtx) {
                const trendDataRaw = <?php echo json_encode($chartTrendData); ?>;
                const labels = trendDataRaw.map(item => item.date);
                const dataPts = trendDataRaw.map(item => item.count);

                trendChartInst = new Chart(trendCtx, {
                    type: 'line',
                    data: {
                        labels: labels.length > 0 ? labels : ['بدون دیتا'],
                        datasets:[{
                            label: 'تعداد سفارشات',
                            data: dataPts.length > 0 ? dataPts :[0],
                            borderColor: '#2196F3',
                            backgroundColor: isDark ? 'rgba(33, 150, 243, 0.3)' : 'rgba(33, 150, 243, 0.15)',
                            borderWidth: 3, tension: 0.4, fill: true,
                            pointBackgroundColor: isDark ? '#1e1e1e' : '#fff',
                            pointBorderColor: '#2196F3', pointRadius: 5
                        }]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        scales: { 
                            x: { 
                                grid: { color: gridLineColor, borderColor: gridBorderColor }, 
                                ticks: { color: labelColor } 
                            },
                            y: { 
                                beginAtZero: true, 
                                grid: { color: gridLineColor, borderColor: gridBorderColor }, 
                                ticks: { stepSize: 1, color: labelColor } 
                            } 
                        },
                        plugins: { legend: { display: false } }
                    }
                });
            }

            // 3. نمودار ستونی
            const topCarsCtx = document.getElementById('topCarsChart');
            if (topCarsCtx) {
                const topCarsRaw = <?php echo json_encode($chartTopCars); ?>;
                const carNames = topCarsRaw.map(item => item.car_name);
                const carCounts = topCarsRaw.map(item => item.count);

                topCarsChartInst = new Chart(topCarsCtx, {
                    type: 'bar',
                    data: {
                        labels: carNames.length > 0 ? carNames :['هنوز فروشی ثبت نشده'],
                        datasets:[{
                            label: 'تعداد فروش موفق',
                            data: carCounts.length > 0 ? carCounts : [0],
                            backgroundColor: isDark ? 'rgba(156, 39, 176, 0.8)' : 'rgba(156, 39, 176, 0.6)',
                            borderColor: '#9C27B0', borderWidth: 1, borderRadius: 5
                        }]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        scales: { 
                            x: { 
                                grid: { color: gridLineColor, borderColor: gridBorderColor }, 
                                ticks: { color: labelColor } 
                            },
                            y: { 
                                beginAtZero: true, 
                                grid: { color: gridLineColor, borderColor: gridBorderColor }, 
                                ticks: { stepSize: 1, color: labelColor } 
                            } 
                        },
                        plugins: { legend: { display: false } }
                    }
                });
            }
        }

        // رندر اولیه نمودارها
        initCharts();

        // ============================================
        // سیستم جادویی تغییر رنگ زنده نمودارها (بدون رفرش)
        // ============================================
        function updateChartsThemeLive() {
            const isDark = document.body.classList.contains('darkmode');
            const gridLineColor = isDark ? 'rgba(255, 255, 255, 0.15)' : 'rgba(0, 0, 0, 0.1)';
            const gridBorderColor = isDark ? 'rgba(255, 255, 255, 0.3)' : 'rgba(0, 0, 0, 0.2)';
            const labelColor = isDark ? '#eeeeee' : '#666666';

            // آپدیت نمودار دایره‌ای
            if (statusChartInst) {
                statusChartInst.options.plugins.legend.labels.color = labelColor;
                statusChartInst.data.datasets[0].borderColor = isDark ? '#1e1e1e' : '#fff';
                statusChartInst.data.datasets[0].borderWidth = isDark ? 2 : 0;
                statusChartInst.update();
            }

            // آپدیت نمودار خطی
            if (trendChartInst) {
                trendChartInst.options.scales.x.grid.color = gridLineColor;
                trendChartInst.options.scales.x.grid.borderColor = gridBorderColor;
                trendChartInst.options.scales.x.ticks.color = labelColor;
                trendChartInst.options.scales.y.grid.color = gridLineColor;
                trendChartInst.options.scales.y.grid.borderColor = gridBorderColor;
                trendChartInst.options.scales.y.ticks.color = labelColor;
                trendChartInst.data.datasets[0].backgroundColor = isDark ? 'rgba(33, 150, 243, 0.3)' : 'rgba(33, 150, 243, 0.15)';
                trendChartInst.data.datasets[0].pointBackgroundColor = isDark ? '#1e1e1e' : '#fff';
                trendChartInst.update();
            }

            // آپدیت نمودار ستونی
            if (topCarsChartInst) {
                topCarsChartInst.options.scales.x.grid.color = gridLineColor;
                topCarsChartInst.options.scales.x.grid.borderColor = gridBorderColor;
                topCarsChartInst.options.scales.x.ticks.color = labelColor;
                topCarsChartInst.options.scales.y.grid.color = gridLineColor;
                topCarsChartInst.options.scales.y.grid.borderColor = gridBorderColor;
                topCarsChartInst.options.scales.y.ticks.color = labelColor;
                topCarsChartInst.data.datasets[0].backgroundColor = isDark ? 'rgba(156, 39, 176, 0.8)' : 'rgba(156, 39, 176, 0.6)';
                topCarsChartInst.update();
            }
        }

        // ردیاب (Observer) برای گوش دادن به دکمه دارک مود
        // هر وقت کلاس بادی عوض بشه، این ردیاب سریعاً تابع بالا رو صدا میزنه
        const themeObserver = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.attributeName === "class") {
                    updateChartsThemeLive();
                }
            });
        });
        themeObserver.observe(document.body, { attributes: true });

        <?php endif; ?>
        // ============================================================
        // تنظیمات اولیه و خواندن پارامترهای URL (برای تمام بخش‌ها)
        // ============================================================
        const urlParams = new URLSearchParams(window.location.search);
        const searchParam = urlParams.get('search');
        const filterParam = urlParams.get('filter');
        const roleParam = urlParams.get('role');
        const ordersParam = urlParams.get('orders');
        const brandParam = urlParams.get('brand');
        const statusParam = urlParams.get('status');
        const sortParam = urlParams.get('sort');
        const fromParam = urlParams.get('from'); // اضافه شد
        const toParam = urlParams.get('to');     // اضافه شد
        const currentView = urlParams.get('view') || 'dashboard';

        // اعمال سرچ اگر در URL بود
        if (searchParam) {
            const searchInput = document.getElementById('admin-search');
            if (searchInput) {
                searchInput.value = searchParam;
                setTimeout(() => { searchInput.dispatchEvent(new Event('input')); }, 100);
            }
        }

        // اعمال فیلترهای تمام صفحات بر اساس URL
        if (currentView === 'users') {
            if (filterParam && document.getElementById('userStatusFilter')) document.getElementById('userStatusFilter').value = filterParam;
            if (roleParam && document.getElementById('userRoleFilter')) document.getElementById('userRoleFilter').value = roleParam;
            if (ordersParam && document.getElementById('userOrdersFilter')) document.getElementById('userOrdersFilter').value = ordersParam;
            if (sortParam && document.getElementById('userSortFilter')) document.getElementById('userSortFilter').value = sortParam;
            if (fromParam && document.getElementById('userFromDate')) document.getElementById('userFromDate').value = fromParam;
            if (toParam && document.getElementById('userToDate')) document.getElementById('userToDate').value = toParam;
            
            // اجرای خودکار فیلتر هنگام لود شدن صفحه
            if (filterParam || roleParam || ordersParam || sortParam || fromParam || toParam) setTimeout(applyGlobalUserFilter, 100);
        }
        else if (currentView === 'contracts') {
            if (filterParam && document.getElementById('contractFilterSelect')) {
                document.getElementById('contractFilterSelect').value = filterParam;
                setTimeout(applyContractFilter, 100);
            }
        } 
        else if (currentView === 'products') {
            if (brandParam && document.getElementById('productBrandFilter')) document.getElementById('productBrandFilter').value = brandParam;
            if (statusParam && document.getElementById('productStatusFilter')) document.getElementById('productStatusFilter').value = statusParam;
            
            // اگر فیلتری در لینک بود، آن را روی جدول اعمال کن
            if (brandParam || statusParam) setTimeout(applyProductFilter, 100);
        }

        else if (currentView === 'products') {
            if (brandParam && document.getElementById('productBrandFilter')) document.getElementById('productBrandFilter').value = brandParam;
            if (statusParam && document.getElementById('productStatusFilter')) document.getElementById('productStatusFilter').value = statusParam;
            // خواندن فیلتر مرتب‌سازی از لینک
            if (sortParam && document.getElementById('productSortFilter')) document.getElementById('productSortFilter').value = sortParam;
            
            if (brandParam || statusParam || sortParam) setTimeout(applyProductFilter, 100);
            // در لحظه لود شدن صفحه اجرا کنیم تا ردیف‌های جدول به صورت فیزیکی جابه‌جا و مرتب شوند
            setTimeout(applyProductFilter, 100);
        }
        // شروع آپدیت وضعیت آنلاین بودن
        updateActivityStatuses();
        statusUpdateInterval = setInterval(updateActivityStatuses, 30000);


        // ============================================================
        // 2. مدیریت منو موبایل و سایدبار
        // ============================================================
        const menuToggle = document.getElementById('menuToggle');
        const adminSidebar = document.getElementById('adminSidebar');

        if (menuToggle) {
            menuToggle.addEventListener('click', () => adminSidebar.classList.toggle('active'));
        }

        // بستن منو وقتی بیرونش کلیک میشه
        document.addEventListener('click', (e) => {
            if (adminSidebar && !adminSidebar.contains(e.target) && 
                menuToggle && !menuToggle.contains(e.target) && 
                adminSidebar.classList.contains('active')) {
                adminSidebar.classList.remove('active');
            }
        });


        // ============================================================
        // 3. جستجوی زنده در جداول (Search Input)
        // ============================================================
        // const searchInput = document.getElementById('admin-search');
        // if (searchInput) {
        //     searchInput.addEventListener('input', function() {
        //         const term = this.value.toLowerCase();
        //         const rows = document.querySelectorAll('.users-table tbody tr');
                
        //         rows.forEach(row => {
        //             // فقط سطرهای اصلی رو فیلتر کن (نه هدر یا دکمه افزودن)
        //             if(row.getElementsByTagName('td').length > 1) {
        //                 const text = row.innerText.toLowerCase();
        //                 row.style.display = text.includes(term) ? '' : 'none';
        //             }
        //         });
        //     });
        // }

        // ============================================================
        // 3. جستجوی زنده یکپارچه (Unified Live Search)
        // ============================================================
        const searchInput = document.getElementById('admin-search');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                // به جای اینکه سرچ خودش تصمیم بگیرد، به توابع فیلتر می‌گوید اجرا شوند
                // تا هم سرچ و هم دراپ‌دان‌ها همزمان بررسی شوند
                if (typeof applyGlobalUserFilter === 'function' && document.getElementById('userStatusFilter')) {
                    applyGlobalUserFilter();
                } else if (typeof applyProductFilter === 'function' && document.getElementById('productBrandFilter')) {
                    applyProductFilter();
                } else if (typeof applyContractFilter === 'function' && document.getElementById('contractFilterSelect')) {
                    applyContractFilter();
                } else {
                    // حالت پیش‌فرض برای صفحاتی که فیلتر پیشرفته ندارند
                    const term = this.value.toLowerCase();
                    const rows = document.querySelectorAll('.users-table tbody tr');
                    rows.forEach(row => {
                        if(row.getElementsByTagName('td').length > 1) {
                            row.style.display = row.innerText.toLowerCase().includes(term) ? '' : 'none';
                        }
                    });
                }
            });
        }

        // ============================================================
        // 4. مدیریت مودال‌های تایید (حذف و ارتقا)
        // ============================================================
        const confirmationModal = document.getElementById('confirmationModal');
        const confirmActionBtn = document.getElementById('confirmActionBtn');
        const cancelActionBtn = document.getElementById('cancelActionBtn');

        // بستن مودال
        if (cancelActionBtn) {
            cancelActionBtn.addEventListener('click', () => confirmationModal.style.display = 'none');
        }

        // انجام عملیات بعد از تایید
        if (confirmActionBtn) {
            confirmActionBtn.addEventListener('click', () => {
                confirmationModal.style.display = 'none';

                let bodyData = `action=${currentAction}`;
                if (currentAction === 'delete_product') {
                    bodyData += `&product_id=${currentTargetId}`;
                } else {
                    bodyData += `&user_id=${currentTargetId}`;
                }

                fetch('admin_actions.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: bodyData
                }).then(r => r.json()).then(d => {
                    if (d.success) {
                        // برای آپدیت شدن آمار بالای صفحه، حتماً صفحه را رفرش می‌کنیم
                        window.location.reload();
                    } else {
                        alert(d.error || 'خطایی رخ داد');
                    }
                }).catch(() => alert('خطا در ارتباط با سرور'));
            });
        }

        // لیسنر دکمه‌های جدول کاربران
        document.querySelectorAll('.delete-btn').forEach(btn => {
            if (btn.hasAttribute('data-user-id')) {
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    openConfirm('حذف کاربر', 'آیا مطمئن هستید؟', 'delete', btn.dataset.userId);
                });
            }
        });

        document.querySelectorAll('.promote-btn').forEach(btn => btn.addEventListener('click', (e) => {
            e.preventDefault();
            openConfirm('ارتقا', 'مطمئنید؟', 'promote', btn.dataset.userId);
        }));

        document.querySelectorAll('.demote-btn').forEach(btn => btn.addEventListener('click', (e) => {
            e.preventDefault();
            openConfirm('تنزل', 'مطمئنید؟', 'demote', btn.dataset.userId);
        }));


        // ============================================================
        // 5. مدیریت قراردادها (مشاهده و تغییر وضعیت)
        // ============================================================
        const contractModal = document.getElementById('contractDetailsModal');
        let currentContractId = null;

        if (document.getElementById('closeContractModalBtn')) {
            document.getElementById('closeContractModalBtn').addEventListener('click', () => contractModal.style.display = 'none');
        }

        document.querySelectorAll('.view-contract-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const data = JSON.parse(this.getAttribute('data-contract'));
                currentContractId = data.id;

                document.getElementById('cd-tracking').innerText = data.tracking_code;
                document.getElementById('cd-date').innerText = data.created_at;
                document.getElementById('cd-name').innerText = data.real_name;
                document.getElementById('cd-nid').innerText = data.national_id;
                document.getElementById('cd-phone').innerText = data.phone;
                document.getElementById('cd-postal').innerText = data.postal_code;
                document.getElementById('cd-address').innerText = data.address;
                document.getElementById('cd-car').innerText = data.car_name;
                document.getElementById('cd-color').innerText = data.car_color;
                document.getElementById('cd-price').innerText = data.car_price;
                document.getElementById('cd-signature').src = data.signature;
                document.getElementById('cd-status-select').value = data.status;

                contractModal.style.display = 'flex';
            });
        });

        const updateStatusBtn = document.getElementById('updateStatusBtn');
        if (updateStatusBtn) {
            updateStatusBtn.addEventListener('click', function() {
                const status = document.getElementById('cd-status-select').value;
                this.innerText = '...';
                this.disabled = true;
                
                fetch('admin_actions.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=update_contract_status&contract_id=${currentContractId}&new_status=${status}`
                }).then(r => r.json()).then(d => {
                    if (d.success) {
                        showSuccess('وضعیت تغییر کرد');
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        alert(d.error);
                        this.innerText = 'ثبت تغییر';
                        this.disabled = false;
                    }
                });
            });
        }


     

        // تابع ساخت ردیف رنگ جدید
        // window.addColorRow = function(name = '', hex = '#000000', qty = 0) {
        //     if (!colorsContainer) return;
        //     const row = document.createElement('div');
        //     row.style.display = 'flex';
        //     row.style.gap = '10px';
        //     row.style.alignItems = 'center';

        //     row.innerHTML = `
        //         <input type="text" name="color_names[]" placeholder="نام رنگ" value="${name}" required style="flex: 2; padding: 5px; border-radius: 4px; border: 1px solid #ccc; font-family: 'Vazirmatn';">
        //         <input type="color" name="color_hexes[]" value="${hex}" style="flex: 1; height: 35px; cursor: pointer; border: none; padding: 0;">
        //         <input type="number" name="color_qtys[]" placeholder="تعداد" value="${qty}" min="0" required style="flex: 1; padding: 5px; text-align: center; border-radius: 4px; border: 1px solid #ccc;">
        //         <button type="button" onclick="this.parentElement.remove()" style="background: #f44336; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer;">X</button>
        //     `;
        //     colorsContainer.appendChild(row);
        // }

       // ============================================================
        // 6. مدیریت محصولات (افزودن، ویرایش و رنگ‌های داینامیک)
        // ============================================================
        const productModal = document.getElementById('productModal');
        const productForm = document.getElementById('productForm');
        const colorsContainer = document.getElementById('dynamicColorsContainer');
        const addColorBtn = document.getElementById('addNewColorBtn');
// ============================================================
        // تابع ساخت ردیف رنگ جدید (ظاهر ریسپانسیو + پیش‌نمایش لایو)
        // ============================================================
        window.addColorRow = function(name = '', hex = '#000000', qty = 0, imgPath = '') {
            if (!colorsContainer) return;
            
            // ساخت یک ID تصادفی برای ارتباط لیبل و اینپوت فایل
            const randomId = 'file_' + Math.random().toString(36).substr(2, 9);
            
            const row = document.createElement('div');
            // استایل‌های این لاین برای جلوگیری از بهم ریختگی تو موبایل عالیه
            row.style.cssText = `
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
                gap: 10px;
                align-items: center;
                background: var(--admin-bg-color);
                padding: 12px;
                border-radius: 8px;
                border: 1px solid var(--admin-border-color);
                box-shadow: 0 2px 5px rgba(0,0,0,0.05);
                position: relative;
            `;

            // بررسی اینکه عکس از قبل وجود داره یا نه
            const hasImg = imgPath && imgPath.trim() !== '';
            const imgSrc = hasImg ? imgPath : '';
            const imgDisplay = hasImg ? 'block' : 'none';
            const placeholderDisplay = hasImg ? 'none' : 'flex';

            row.innerHTML = `
                <!-- نام رنگ -->
                <input type="text" name="color_names[]" placeholder="نام رنگ (مثل قرمز)" value="${name}" required 
                       style="padding: 8px; border-radius: 6px; border: 1px solid #ccc; font-family: 'Vazirmatn'; width: 100%; box-sizing: border-box;">
                
                <!-- انتخاب کد رنگ و تعداد در یک ستون -->
                <div style="display: flex; gap: 5px;">
                    <input type="color" name="color_hexes[]" value="${hex}" 
                           style="width: 40px; height: 38px; cursor: pointer; border: 1px solid #ccc; border-radius: 6px; padding: 0;">
                    <input type="number" name="color_qtys[]" placeholder="تعداد" value="${qty}" min="0" required 
                           style="flex: 1; padding: 8px; text-align: center; border-radius: 6px; border: 1px solid #ccc; width: 100%; box-sizing: border-box;">
                </div>
                
                <!-- دکمه آپلود عکس اختصاصی (شیک و مدرن) -->
                <div style="position: relative; width: 100%;">
                    <input type="hidden" name="existing_color_imgs[]" value="${imgPath}">
                    <input type="file" name="color_images[]" id="${randomId}" accept="image/*" style="display: none;">
                    
                    <label for="${randomId}" style="display: flex; align-items: center; gap: 10px; cursor: pointer; padding: 5px; border: 1px dashed var(--admin-secondary-color); border-radius: 6px; background: rgba(33, 150, 243, 0.05);">
                        <img class="preview-img" src="${imgSrc}" style="width: 35px; height: 35px; object-fit: cover; border-radius: 4px; display: ${imgDisplay}; box-shadow: 0 1px 3px rgba(0,0,0,0.2);">
                        <div class="preview-placeholder" style="width: 35px; height: 35px; background: #eee; border-radius: 4px; display: ${placeholderDisplay}; align-items: center; justify-content: center; font-size: 16px; color: #999;">📸</div>
                        <span style="font-size: 11px; color: var(--admin-secondary-color); font-weight: bold;">آپلود عکس رنگ</span>
                    </label>
                </div>

                <!-- دکمه حذف -->
                <button type="button" onclick="this.parentElement.remove()" 
                        style="background: #f44336; color: white; border: none; height: 38px; border-radius: 6px; cursor: pointer; font-weight: bold; transition: 0.2s;">
                    حذف رنگ
                </button>
            `;

            // --- اضافه کردن رویداد لایو (Live Preview) برای عکس ---
            const fileInput = row.querySelector(`input[type="file"]`);
            const previewImg = row.querySelector('.preview-img');
            const placeholder = row.querySelector('.preview-placeholder');

            fileInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(event) {
                        previewImg.src = event.target.result;
                        previewImg.style.display = 'block';
                        placeholder.style.display = 'none';
                    }
                    reader.readAsDataURL(file);
                }
            });

            colorsContainer.appendChild(row);
        }

        if (addColorBtn) addColorBtn.addEventListener('click', () => addColorRow());

        // دکمه "افزودن خودروی جدید"
        const addProductBtn = document.getElementById('addProductBtn');
        if (addProductBtn) {
            addProductBtn.addEventListener('click', () => {
                productForm.reset();
                document.getElementById('productAction').value = 'add_product';
                document.getElementById('productModalTitle').innerText = 'افزودن محصول جدید';
                document.getElementById('pImage').required = true;

                if (colorsContainer) {
                    colorsContainer.innerHTML = '';
                    addColorRow(); // یک ردیف خالی اضافه کن
                }
                productModal.style.display = 'flex';
            });
        }

        // دکمه‌های "ویرایش محصول" (تغییر یافته برای خواندن عکس رنگ)
        document.querySelectorAll('.edit-product-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const p = JSON.parse(this.getAttribute('data-product'));

                document.getElementById('productAction').value = 'edit_product';
                document.getElementById('productId').value = p.id;
                document.getElementById('pName').value = p.name;
                // --- مدیریت نمایش صدای موتور در مودال ویرایش ---
                document.getElementById('pSound').value = ''; // خالی کردن اینپوت فایل
                const soundContainer = document.getElementById('currentSoundContainer');
                const soundPlayer = document.getElementById('currentSoundPlayer');
                
                if (p.engine_sound && p.engine_sound !== 'null' && p.engine_sound !== '') {
                    soundContainer.style.display = 'block';
                    soundPlayer.src = p.engine_sound;
                } else {
                    soundContainer.style.display = 'none';
                    soundPlayer.src = '';
                }
                document.getElementById('pBrand').value = p.brand;
                document.getElementById('pPrice').value = p.price;
                document.getElementById('pOldPrice').value = p.old_price;
                document.getElementById('pHp').value = p.hp;
                document.getElementById('pAccel').value = p.accel;
                document.getElementById('pEngine').value = p.engine;
                document.getElementById('pInSlider').checked = (p.in_slider == 1);

                document.getElementById('productModalTitle').innerText = 'ویرایش: ' + p.name;
                document.getElementById('pImage').required = false;

                if (colorsContainer) {
                    colorsContainer.innerHTML = '';
                    if (p.colors_inventory && p.colors_inventory !== "null" && p.colors_inventory !== "{}") {
                        try {
                            let colors = JSON.parse(p.colors_inventory);
                            Object.keys(colors).forEach(colorName => {
                                let colorData = colors[colorName];
                                let hex = '#000000';
                                let qty = 0;
                                let img = '';

                                if (typeof colorData === 'object' && colorData !== null) {
                                    hex = colorData.hex || '#000000';
                                    qty = parseInt(colorData.qty) || 0;
                                    img = colorData.img || ''; // گرفتن عکس رنگ
                                } else {
                                    qty = parseInt(colorData) || 0;
                                }
                                addColorRow(colorName, hex, qty, img);
                            });
                        } catch (e) {
                            console.error("Error parsing colors", e);
                            addColorRow();
                        }
                    } else {
                        addColorRow();
                    }
                }
                productModal.style.display = 'flex';
            });
        });

        // ارسال فرم محصول

        // ارسال فرم محصول
        if (productForm) {
            productForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                const btn = this.querySelector('button[type="submit"]');
                const originalText = btn.innerText;
                btn.innerText = 'در حال ذخیره...';
                btn.disabled = true;

                fetch('admin_actions.php', {
                    method: 'POST',
                    body: formData
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        showSuccess('عملیات با موفقیت انجام شد');
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        alert('خطا: ' + data.error);
                        btn.innerText = originalText;
                        btn.disabled = false;
                    }
                })
                .catch(err => {
                    alert('خطا در ارتباط با سرور');
                    btn.innerText = originalText;
                    btn.disabled = false;
                });
            });
        }
    });

    // ============================================================
    // 7. توابع کمکی (Helper Functions)
    // ============================================================

    // تابع گلوبال حذف محصول
    window.deleteProduct = function(id) {
        openConfirm('حذف محصول', 'آیا از حذف این خودرو مطمئن هستید؟ عکس آن نیز پاک خواهد شد.', 'delete_product', id);
    }

    // باز کردن مودال تایید
    function openConfirm(title, msg, action, id) {
        document.getElementById('confirmationTitle').textContent = title;
        document.getElementById('confirmationMessage').textContent = msg;
        currentAction = action;
        currentTargetId = id;
        document.getElementById('confirmationModal').style.display = 'flex';
    }

   // نمایش پیام موفقیت با سیستم جدید
   function showSuccess(msg) {
        if (typeof window.showToast === 'function') {
            window.showToast(msg, 'success');
        } else {
            alert(msg);
        }
    }

    // فیلتر کاربران
    window.applyUserFilter = function() {
        const filter = document.getElementById('userFilterSelect');
        if (!filter) return;
        
        const filterValue = filter.value;
        const rows = document.querySelectorAll('.users-table tbody tr');

        rows.forEach(row => {
            const status = row.getAttribute('data-status') || 'offline';
            if (filterValue === 'all') row.style.display = '';
            else if (filterValue === 'online') row.style.display = (status === 'online') ? '' : 'none';
            else if (filterValue === 'offline') row.style.display = (status !== 'online') ? '' : 'none';
        });
    }

    // فیلتر قراردادها
    // window.applyContractFilter = function() {
    //     const filter = document.getElementById('contractFilterSelect');
    //     if (!filter) return;
        
    //     const filterValue = filter.value;
    //     const rows = document.querySelectorAll('.users-table tbody tr');

    //     rows.forEach(row => {
    //         const status = row.getAttribute('data-status');
    //         if(!status) return;
            
    //         if (filterValue === 'all') row.style.display = '';
    //         else row.style.display = (status === filterValue) ? '' : 'none';
    //     });
    // }

   // =========================================================
        // فیلتر قراردادها (همراه با آپدیت آدرس URL)
        // =========================================================
        // window.applyContractFilter = function() {
        //     const searchTerm = document.getElementById('admin-search')?.value.toLowerCase() || '';
        //     const filter = document.getElementById('contractFilterSelect');
        //     if (!filter) return;
            
        //     const filterValue = filter.value;

        //     // آپدیت کردن آدرس URL به صورت سایلنت
        //     const currentUrl = new URL(window.location);
        //     currentUrl.searchParams.set('filter', filterValue);
        //     window.history.replaceState({}, '', currentUrl);

        //     const rows = document.querySelectorAll('.users-table tbody tr');
        //     rows.forEach(row => {
        //         const status = row.getAttribute('data-status');
        //         if(!status) return;
                
        //         const rowText = row.innerText.toLowerCase();
        //         let matchSearch = rowText.includes(searchTerm);
        //         let matchStatus = (filterValue === 'all') || (status === filterValue);
                
        //         row.style.display = (matchSearch && matchStatus) ? '' : 'none';
        //     });
        // }

        // =========================================================
        // فیلتر قراردادها (داینامیک)
        // =========================================================
        window.applyContractFilter = function() {
            const searchTerm = document.getElementById('admin-search')?.value.toLowerCase() || '';
            const filter = document.getElementById('contractFilterSelect');
            if (!filter) return;
            const filterValue = filter.value;

            const currentUrl = new URL(window.location);
            currentUrl.searchParams.set('filter', filterValue);
            window.history.replaceState({}, '', currentUrl);

            const rows = document.querySelectorAll('.users-table tbody tr');
            
            // شمارنده‌های زنده
            let vTotal = 0, vPaid = 0, vPending = 0, vRejected = 0;

            rows.forEach(row => {
                const status = row.getAttribute('data-status');
                if(!status) return;
                
                const rowText = row.innerText.toLowerCase();
                let matchSearch = rowText.includes(searchTerm);
                let matchStatus = (filterValue === 'all') || (status === filterValue);
                
                if (matchSearch && matchStatus) {
                    row.style.display = '';
                    vTotal++;
                    if (status === 'paid') vPaid++;
                    else if (status === 'pending') vPending++;
                    else if (status === 'rejected') vRejected++;
                } else {
                    row.style.display = 'none';
                }
            });

            // آپدیت کارت‌های بالای صفحه قراردادها
            const cTotal = document.getElementById('card-total-contracts');
            const cPaid = document.getElementById('card-paid-contracts');
            const cPending = document.getElementById('card-pending-contracts');
            const cRejected = document.getElementById('card-rejected-contracts');
            
            if(cTotal) cTotal.innerText = new Intl.NumberFormat().format(vTotal);
            if(cPaid) cPaid.innerText = new Intl.NumberFormat().format(vPaid);
            if(cPending) cPending.innerText = new Intl.NumberFormat().format(vPending);
            if(cRejected) cRejected.innerText = new Intl.NumberFormat().format(vRejected);
        }

    // فیلتر محصولات
    // window.applyProductFilter = function() {
    //     const brandFilter = document.getElementById('productBrandFilter').value;
    //     const statusFilter = document.getElementById('productStatusFilter').value;
    //     const rows = document.querySelectorAll('.users-table tbody tr');

    //     rows.forEach(row => {
    //         if (!row.hasAttribute('data-brand')) return;

    //         const rowBrand = row.getAttribute('data-brand');
    //         const hasDiscount = row.getAttribute('data-discount') === '1';
    //         const inSlider = row.getAttribute('data-slider') === '1';
    //         const hasStock = row.getAttribute('data-stock') === '1';

    //         let brandMatch = (brandFilter === 'all') || (rowBrand === brandFilter);
    //         let statusMatch = false;

    //         if (statusFilter === 'all') statusMatch = true;
    //         else if (statusFilter === 'discount') statusMatch = hasDiscount;
    //         else if (statusFilter === 'slider') statusMatch = inSlider;
    //         else if (statusFilter === 'out_of_stock') statusMatch = !hasStock;
    //         else if (statusFilter === 'in_stock') statusMatch = hasStock;

    //         row.style.display = (brandMatch && statusMatch) ? '' : 'none';
    //     });
    // }

    // =========================================================
        // فیلتر و مرتب‌سازی محصولات (Instant DOM Sorting Engine)
        // =========================================================
        // window.applyProductFilter = function() {
        //     const searchTerm = document.getElementById('admin-search')?.value.toLowerCase() || '';
        //     const brandFilter = document.getElementById('productBrandFilter')?.value || 'all';
        //     const statusFilter = document.getElementById('productStatusFilter')?.value || 'all';
        //     const sortFilter = document.getElementById('productSortFilter')?.value || 'newest';

        //     // 1. آپدیت کردن آدرس URL به صورت سایلنت
        //     const currentUrl = new URL(window.location);
        //     currentUrl.searchParams.set('brand', brandFilter);
        //     currentUrl.searchParams.set('status', statusFilter);
        //     currentUrl.searchParams.set('sort', sortFilter);
        //     window.history.replaceState({}, '', currentUrl);

        //     const tbody = document.querySelector('.users-table tbody');
        //     // فقط ردیف‌های مربوط به محصولات را می‌گیریم (دکمه افزودن محصول را نادیده می‌گیریم)
        //     const rows = Array.from(tbody.querySelectorAll('tr.product-item-row'));

        //     // 2. اعمال فیلتر (مخفی یا نمایان کردن)
        //     rows.forEach(row => {
        //         const rowText = row.innerText.toLowerCase();
        //         const rowBrand = row.getAttribute('data-brand');
        //         const hasDiscount = row.getAttribute('data-discount') === '1';
        //         const inSlider = row.getAttribute('data-slider') === '1';
        //         const hasStock = row.getAttribute('data-stock') === '1';

        //         let matchSearch = rowText.includes(searchTerm);
        //         let brandMatch = (brandFilter === 'all') || (rowBrand === brandFilter);
        //         let statusMatch = false;

        //         if (statusFilter === 'all') statusMatch = true;
        //         else if (statusFilter === 'discount') statusMatch = hasDiscount;
        //         else if (statusFilter === 'slider') statusMatch = inSlider;
        //         else if (statusFilter === 'in_stock') statusMatch = hasStock;
        //         else if (statusFilter === 'out_of_stock') statusMatch = !hasStock;

        //         row.style.display = (matchSearch && brandMatch && statusMatch) ? '' : 'none';
        //     });

        //     // // 3. اعمال مرتب‌سازی (Sorting)
        //     // rows.sort((a, b) => {
        //     //     const priceA = parseFloat(a.getAttribute('data-price')) || 0;
        //     //     const priceB = parseFloat(b.getAttribute('data-price')) || 0;
        //     //     const likesA = parseInt(a.getAttribute('data-likes')) || 0;
        //     //     const likesB = parseInt(b.getAttribute('data-likes')) || 0;
        //     //     const idA = parseInt(a.getAttribute('data-id')) || 0;
        //     //     const idB = parseInt(b.getAttribute('data-id')) || 0;

        //     //     switch(sortFilter) {
        //     //         case 'price_desc': return priceB - priceA; // گران‌ترین
        //     //         case 'price_asc':  return priceA - priceB; // ارزان‌ترین
        //     //         case 'likes_desc': return likesB - likesA; // محبوب‌ترین
        //     //         case 'likes_asc':  return likesA - likesB; // کمترین لایک
        //     //         case 'newest': 
        //     //         default:           return idB - idA;       // جدیدترین (بر اساس ID)
        //     //     }
        //     // });

        //     // 3. اعمال مرتب‌سازی فوق‌سریع (Sorting) بر اساس اعداد و زمان
        //     rows.sort((a, b) => {
        //         const priceA = parseFloat(a.getAttribute('data-price')) || 0;
        //         const priceB = parseFloat(b.getAttribute('data-price')) || 0;
                
        //         const likesA = parseInt(a.getAttribute('data-likes')) || 0;
        //         const likesB = parseInt(b.getAttribute('data-likes')) || 0;
                
        //         // زمان‌ها به صورت عدد (Timestamp) از PHP دریافت شده‌اند
        //         const createdA = parseInt(a.getAttribute('data-created')) || 0;
        //         const createdB = parseInt(b.getAttribute('data-created')) || 0;
                
        //         const updatedA = parseInt(a.getAttribute('data-updated')) || 0;
        //         const updatedB = parseInt(b.getAttribute('data-updated')) || 0;

        //         switch(sortFilter) {
        //             case 'price_desc':   return priceB - priceA;     // گران‌ترین
        //             case 'price_asc':    return priceA - priceB;     // ارزان‌ترین
        //             case 'likes_desc':   return likesB - likesA;     // محبوب‌ترین
        //             case 'likes_asc':    return likesA - likesB;     // کمترین تقاضا
        //             case 'updated_desc': return updatedB - updatedA; // اخیراً ویرایش شده
        //             case 'updated_asc':  return updatedA - updatedB; // قدیمی‌ترین ویرایش
        //             case 'oldest':       return createdA - createdB; // قدیمی‌ترین ثبت
        //             case 'newest': 
        //             default:             return createdB - createdA; // جدیدترین ثبت (بر اساس تاریخ واقعی)
        //         }
        //     });

        //     // 4. چیدن مجدد ردیف‌ها در صفحه (DOM Reordering بدون رفرش)
        //     rows.forEach(row => tbody.appendChild(row));
        // }

        // =========================================================
        // فیلتر و مرتب‌سازی محصولات (داینامیک)
        // =========================================================
        window.applyProductFilter = function() {
            const searchTerm = document.getElementById('admin-search')?.value.toLowerCase() || '';
            const brandFilter = document.getElementById('productBrandFilter')?.value || 'all';
            const statusFilter = document.getElementById('productStatusFilter')?.value || 'all';
            const sortFilter = document.getElementById('productSortFilter')?.value || 'newest';

            const currentUrl = new URL(window.location);
            currentUrl.searchParams.set('brand', brandFilter);
            currentUrl.searchParams.set('status', statusFilter);
            currentUrl.searchParams.set('sort', sortFilter);
            window.history.replaceState({}, '', currentUrl);

            const tbody = document.querySelector('.users-table tbody');
            const rows = Array.from(tbody.querySelectorAll('tr.product-item-row'));

            // شمارنده‌های زنده محصولات
            let vVariety = 0;
            let vInventory = 0;

            rows.forEach(row => {
                const rowText = row.innerText.toLowerCase();
                const rowBrand = row.getAttribute('data-brand');
                const hasDiscount = row.getAttribute('data-discount') === '1';
                const inSlider = row.getAttribute('data-slider') === '1';
                const hasStock = row.getAttribute('data-stock') === '1';
                const rowInvCount = parseInt(row.getAttribute('data-inventory')) || 0;

                let matchSearch = rowText.includes(searchTerm);
                let brandMatch = (brandFilter === 'all') || (rowBrand === brandFilter);
                let statusMatch = false;

                if (statusFilter === 'all') statusMatch = true;
                else if (statusFilter === 'discount') statusMatch = hasDiscount;
                else if (statusFilter === 'slider') statusMatch = inSlider;
                else if (statusFilter === 'in_stock') statusMatch = hasStock;
                else if (statusFilter === 'out_of_stock') statusMatch = !hasStock;

                if (matchSearch && brandMatch && statusMatch) {
                    row.style.display = '';
                    vVariety++;
                    vInventory += rowInvCount;
                } else {
                    row.style.display = 'none';
                }
            });

            // آپدیت کارت‌های بالای صفحه محصولات
            const cVariety = document.getElementById('card-total-products');
            const cInventory = document.getElementById('card-total-inventory');
            if(cVariety) cVariety.innerText = new Intl.NumberFormat().format(vVariety);
            if(cInventory) cInventory.innerText = new Intl.NumberFormat().format(vInventory);

            // اعمال مرتب‌سازی
            rows.sort((a, b) => {
                const priceA = parseFloat(a.getAttribute('data-price')) || 0;
                const priceB = parseFloat(b.getAttribute('data-price')) || 0;
                const likesA = parseInt(a.getAttribute('data-likes')) || 0;
                const likesB = parseInt(b.getAttribute('data-likes')) || 0;
                const createdA = parseInt(a.getAttribute('data-created')) || 0;
                const createdB = parseInt(b.getAttribute('data-created')) || 0;
                const updatedA = parseInt(a.getAttribute('data-updated')) || 0;
                const updatedB = parseInt(b.getAttribute('data-updated')) || 0;

                switch(sortFilter) {
                    case 'price_desc':   return priceB - priceA;
                    case 'price_asc':    return priceA - priceB;
                    case 'likes_desc':   return likesB - likesA;
                    case 'likes_asc':    return likesA - likesB;
                    case 'updated_desc': return updatedB - updatedA;
                    case 'updated_asc':  return updatedA - updatedB;
                    case 'oldest':       return createdA - createdB;
                    case 'newest': 
                    default:             return createdB - createdA;
                }
            });

            rows.forEach(row => tbody.appendChild(row));
        }

    // آپدیت زنده وضعیت‌ها
    function updateActivityStatuses() {
        const cells = document.querySelectorAll('.activity-status-cell');
        const dashboardCount = document.getElementById('live-online-count');
        
        if(cells.length === 0 && !dashboardCount) return;

        fetch('get_online_status.php')
            .then(response => response.json())
            .then(data => {
                if (data.success && data.users) {
                    data.users.forEach(user => {
                        const cell = document.querySelector(`.activity-status-cell[data-user-id="${user.id}"] .activity-status`);
                        const row = document.querySelector(`tr[data-user-id="${user.id}"]`);
                        
                        if (cell) {
                            const timeAgo = calculateTimeAgo(user.last_activity);
                            let isOnline = false;
                            
                            if (user.last_activity) {
                                const now = new Date();
                                const last = new Date(user.last_activity);
                                if((now - last)/60000 <= 3) isOnline = true;
                            }

                            if(row) row.setAttribute('data-status', isOnline ? 'online' : 'offline');

                            let txt = '';
                            let className = 'activity-status ';

                            if (isOnline) {
                                txt = `🟢 آنلاین (${timeAgo})`;
                                className += 'status-online';
                            } else if (!user.last_activity) {
                                txt = `⚪ بدون فعالیت`; 
                                className += 'status-offline';
                            } else {
                                txt = `🔴 آفلاین (${timeAgo})`;
                                className += 'status-offline';
                            }

                            cell.innerHTML = txt;
                            cell.className = className;
                        }
                    });

                    if(dashboardCount) {
                        const onlineCount = data.users.filter(u => {
                            if(!u.last_activity) return false;
                            return (new Date() - new Date(u.last_activity))/60000 <= 3;
                        }).length;
                        dashboardCount.innerText = onlineCount;
                    }

                    // اعمال مجدد فیلتر اگر در صفحه کاربران هستیم
                    // اعمال مجدد فیلتر زنده با توابع جدید
                    if(document.getElementById('userStatusFilter') && typeof applyGlobalUserFilter === 'function') {
                        applyGlobalUserFilter();
                    }
                }
            })
            .catch(e => console.error(e));
    }

    function calculateTimeAgo(lastActivity) {
        if (!lastActivity) return 'نامشخص';
        const now = new Date();
        const last = new Date(lastActivity);
        const diffMs = now - last;
        const diffM = Math.floor(diffMs / 60000);
        if (diffM < 1) return 'هم اکنون';
        if (diffM < 60) return `${diffM} دقیقه پیش`;
        const diffH = Math.floor(diffM / 60);
        if (diffH < 24) return `${diffH} ساعت پیش`;
        return `${Math.floor(diffH / 24)} روز پیش`;
    }

    window.addEventListener('beforeunload', function() {
        if (statusUpdateInterval) clearInterval(statusUpdateInterval);
    });
// =========================================================
        // فیلترهای زنده و بدون رفرش (Instant DOM Filtering)
        // =========================================================

        // کلیک روی کارت‌های بالای صفحه برای تغییر سلکت‌باکس‌ها
        window.filterFromCard = function(type) {
            document.getElementById('userStatusFilter').value = 'all';
            document.getElementById('userOrdersFilter').value = 'all';
            
            if (type === 'admin') {
                document.getElementById('userRoleFilter').value = '1';
            } else if (type === 'normal') {
                document.getElementById('userRoleFilter').value = '0';
            } else {
                document.getElementById('userRoleFilter').value = 'all';
            }
            
            applyGlobalUserFilter(); // اجرای فیلتر به صورت آنی
        }
        window.applyGlobalUserFilter = function() {
            const searchTerm = document.getElementById('admin-search')?.value.toLowerCase() || '';
            const statusFilter = document.getElementById('userStatusFilter')?.value || 'all';
            const roleFilter = document.getElementById('userRoleFilter')?.value || 'all';
            const ordersFilter = document.getElementById('userOrdersFilter')?.value || 'all';
            const sortFilter = document.getElementById('userSortFilter')?.value || 'newest';
            const fromDate = document.getElementById('userFromDate')?.value || '';
            const toDate = document.getElementById('userToDate')?.value || '';

            // تبدیل تاریخ‌ها به ثانیه (Timestamp) برای مقایسه ریاضی
            const fromStamp = fromDate ? new Date(fromDate).getTime() / 1000 : 0;
            const toStamp = toDate ? (new Date(toDate).getTime() / 1000) + 86399 : Infinity; // +86399 یعنی تا پایان همان روز

            // آپدیت کردن آدرس URL
            const currentUrl = new URL(window.location);
            currentUrl.searchParams.set('filter', statusFilter);
            currentUrl.searchParams.set('role', roleFilter);
            currentUrl.searchParams.set('orders', ordersFilter);
            currentUrl.searchParams.set('sort', sortFilter);
            if(fromDate) currentUrl.searchParams.set('from', fromDate); else currentUrl.searchParams.delete('from');
            if(toDate) currentUrl.searchParams.set('to', toDate); else currentUrl.searchParams.delete('to');
            window.history.replaceState({}, '', currentUrl);
            
            const tbody = document.querySelector('.users-table tbody');
            const rows = Array.from(tbody.querySelectorAll('tr'));

            // شمارنده‌های زنده برای کارت‌های بالای صفحه
            let visibleTotal = 0;
            let visibleAdmins = 0;
            let visibleNormals = 0;

            // 1. اعمال فیلترها
            rows.forEach(row => {
                if(!row.hasAttribute('data-role')) return;

                const rowText = row.innerText.toLowerCase();
                const rowStatus = row.getAttribute('data-status') || 'offline';
                const rowRole = row.getAttribute('data-role');
                const rowOrders = parseInt(row.getAttribute('data-orders') || '0');
                const rowCreated = parseInt(row.getAttribute('data-created')) || 0;

                let matchSearch = rowText.includes(searchTerm);
                let matchStatus = (statusFilter === 'all') || (statusFilter === 'online' && rowStatus === 'online') || (statusFilter === 'offline' && rowStatus !== 'online');
                let matchRole = (roleFilter === 'all') || (rowRole === roleFilter);
                
                let matchOrders = false;
                if (ordersFilter === 'all') matchOrders = true;
                else if (ordersFilter === '3plus' && rowOrders >= 3) matchOrders = true;
                else if (ordersFilter === '1plus' && rowOrders >= 1 && rowOrders < 3) matchOrders = true;
                else if (ordersFilter === '0' && rowOrders === 0) matchOrders = true;

                // فیلتر تاریخ عضویت
                let matchDate = true;
                if (rowCreated > 0) {
                    if (fromStamp > 0 && rowCreated < fromStamp) matchDate = false;
                    if (toStamp !== Infinity && rowCreated > toStamp) matchDate = false;
                }

                // نمایش سطر و آپدیت شمارنده‌ها
                if (matchSearch && matchStatus && matchRole && matchOrders && matchDate) {
                    row.style.display = '';
                    visibleTotal++;
                    if (rowRole === '1') visibleAdmins++; else visibleNormals++;
                } else {
                    row.style.display = 'none';
                }
            });

            // آپدیت زنده کارت‌های بالای صفحه
            const cardTotal = document.getElementById('card-total-users');
            const cardAdmin = document.getElementById('card-admin-users');
            const cardNormal = document.getElementById('card-normal-users');
            if(cardTotal) cardTotal.innerText = new Intl.NumberFormat().format(visibleTotal);
            if(cardAdmin) cardAdmin.innerText = new Intl.NumberFormat().format(visibleAdmins);
            if(cardNormal) cardNormal.innerText = new Intl.NumberFormat().format(visibleNormals);

            // 2. اعمال مرتب‌سازی
            rows.sort((a, b) => {
                if(!a.hasAttribute('data-user-id') || !b.hasAttribute('data-user-id')) return 0;
                const idA = parseInt(a.getAttribute('data-user-id')) || 0;
                const idB = parseInt(b.getAttribute('data-user-id')) || 0;
                const activityA = parseInt(a.getAttribute('data-activity')) || 0;
                const activityB = parseInt(b.getAttribute('data-activity')) || 0;

                switch(sortFilter) {
                    case 'active_desc': return activityB - activityA; 
                    case 'active_asc':  return activityA - activityB; 
                    case 'oldest':      return idA - idB;             
                    case 'newest': 
                    default:            return idB - idA;             
                }
            });

            rows.forEach(row => tbody.appendChild(row));
        }

        // ==========================================
        // حذف هوشمند قرارداد
        // ==========================================
        window.deleteContractAdmin = function(id) {
            if(!confirm('آیا از حذف این قرارداد مطمئن هستید؟\n\nنکته: در صورتی که قرارداد در حالت "در انتظار" باشد، خودرو به صورت خودکار به انبار باز می‌گردد.')) return;
            
            fetch('admin_actions.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=delete_contract&contract_id=${id}`
            }).then(r => r.json()).then(d => {
                if(d.success) {
                    // رفرش صفحه برای آپدیت شدن آمار بالای صفحه و موجودی انبار
                    window.location.reload();
                } else {
                    alert(d.error || 'خطایی رخ داد');
                }
            }).catch(err => console.error('خطا در ارتباط با سرور:', err));
        }

        // =========================================================
        // منطق دکمه‌های سریع تاریخ (Quick Date Presets)
        // =========================================================
        window.applyQuickDateFilter = function() {
            const preset = document.getElementById('userQuickDate').value;
            const fromInput = document.getElementById('userFromDate');
            const toInput = document.getElementById('userToDate');
            
            if (preset === 'custom') return;

            if (preset === 'all') {
                fromInput.value = '';
                toInput.value = '';
            } else {
                const today = new Date();
                const pastDate = new Date();
                
                if (preset === '7days') pastDate.setDate(today.getDate() - 7);
                else if (preset === '30days') pastDate.setDate(today.getDate() - 30);
                else if (preset === '6months') pastDate.setMonth(today.getMonth() - 6);

                // فرمت کردن تاریخ به YYYY-MM-DD
                fromInput.value = pastDate.toISOString().split('T')[0];
                toInput.value = today.toISOString().split('T')[0];
            }
            
            // اجرای فیلتر اصلی
            applyGlobalUserFilter();
        }

        // اگر کاربر دستی تقویم را تغییر داد، دراپ‌دان برگردد روی "تاریخ دلخواه"
        window.resetQuickDateAndFilter = function() {
            document.getElementById('userQuickDate').value = 'custom';
            applyGlobalUserFilter();
        }

        // ==========================================
        // فیلتر هوشمند بخش گزارشات
        // ==========================================
        window.filterReports = function(table, value) {
            if (table === 'email') {
                document.getElementById('emailStatusFilter').value = value;
                window.scrollTo({ top: 0, behavior: 'smooth' });
            } else if (table === 'notif') {
                document.getElementById('notifTypeFilter').value = value;
                document.getElementById('notifTypeFilter').scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
            applyReportFilters();
        }

        window.applyReportFilters = function() {
            const searchTerm = document.getElementById('admin-search')?.value.toLowerCase() || '';
            const emailStatus = document.getElementById('emailStatusFilter')?.value || 'all';
            const notifType = document.getElementById('notifTypeFilter')?.value || 'all';

            // آپدیت URL
            const currentUrl = new URL(window.location);
            currentUrl.searchParams.set('email_status', emailStatus);
            currentUrl.searchParams.set('notif_type', notifType);
            window.history.replaceState({}, '', currentUrl);

            // فیلتر جدول ایمیل‌ها
            document.querySelectorAll('.email-item-row').forEach(row => {
                const status = row.getAttribute('data-status');
                const matchSearch = row.innerText.toLowerCase().includes(searchTerm);
                const matchStatus = (emailStatus === 'all') || (status === emailStatus) || (emailStatus === 'pending' && status === 'processing');
                row.style.display = (matchSearch && matchStatus) ? '' : 'none';
            });

            // فیلتر جدول نوتیفیکیشن‌ها
            document.querySelectorAll('.notif-item-row').forEach(row => {
                const type = row.getAttribute('data-type');
                const matchSearch = row.innerText.toLowerCase().includes(searchTerm);
                const matchType = (notifType === 'all') || (type === notifType);
                row.style.display = (matchSearch && matchType) ? '' : 'none';
            });
        }

    // ============================================================
// شبیه‌ساز ربات سرور (Cron Job Simulator) مخصوص لوکال‌هاست
// ============================================================
// این تابع هر 30 ثانیه یک‌بار فایل ارسال ایمیل را در پس‌زمینه اجرا می‌کند
setInterval(() => {
fetch('cron_mailer.php?token=LUXURY_CAR_CRON_SECRET_2026_XYZ')
        .then(response => response.text())
        .then(data => {
            // اگر ایمیلی ارسال شده باشد، در کنسول مرورگر لاگ می‌اندازد (برای تست شما)
            if(data && !data.includes("No pending emails")) {
                console.log("🤖 گزارش ربات پس‌زمینه: " + data);
            }
        })
        .catch(error => console.error("خطا در اجرای ربات:", error));
}, 30000); // 30000 میلی‌ثانیه = 30 ثانیه

// ============================================================
        // شبیه‌ساز ربات قراردادها (Auto-Reject Simulator)
        // ============================================================
        // این تابع هر 5 دقیقه یک‌بار اجرا می‌شود تا قراردادهای رها شده را لغو کند
        setInterval(() => {
            fetch('cron_contracts.php?token=LUXURY_CAR_CRON_SECRET_2026_XYZ')
                .then(response => response.text())
                .then(data => {
                    if(data && !data.includes("No expired contracts found")) {
                        console.log("🧹 گزارش ربات قراردادها: " + data);
                    }
                })
                .catch(error => console.error("خطا در اجرای ربات قرارداد:", error));
        }, 300000); // 300000 میلی‌ثانیه = 5 دقیقه
</script>
</body>
</html>