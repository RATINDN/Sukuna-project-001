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
if ($view === 'dashboard') {
    try {
        $totalUsers = $pdo->query("SELECT COUNT(*) FROM car")->fetchColumn();
        $newContracts = $pdo->query("SELECT COUNT(*) FROM contracts WHERE status = 'pending'")->fetchColumn();
        
        $stmt = $pdo->query("SELECT car_price FROM contracts WHERE status = 'paid'");
        $prices = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $totalRevenue = 0;
        foreach ($prices as $p) {
            $totalRevenue += (int)preg_replace('/[^0-9]/', '', $p);
        }

        // اصلاح شد: ملاک ۳ دقیقه برای هماهنگی با جاوااسکریپت
        $stmt = $pdo->query("SELECT COUNT(*) FROM car WHERE last_activity > DATE_SUB(NOW(), INTERVAL 3 MINUTE)");
        $onlineUsers = $stmt->fetchColumn();

        // آمار تکمیلی
        $stmt = $pdo->prepare("SELECT * FROM contracts ORDER BY created_at DESC LIMIT 5");
        $stmt->execute();
        $recentOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $pdo->query("SELECT car_name, COUNT(*) as count FROM contracts GROUP BY car_name ORDER BY count DESC LIMIT 1");
        $popularCar = $stmt->fetch(PDO::FETCH_ASSOC);
        $topCarName = $popularCar ? $popularCar['car_name'] : 'هنوز فروشی نیست';

    } catch (PDOException $e) {
        $error = "خطا: " . $e->getMessage();
    }
}
// بقیه لاجیک‌ها (کاربران و قراردادها) بدون تغییر...
elseif ($view === 'users') {
    try {
        $stmt = $pdo->prepare("SELECT id, user_name, email, phone, role, status, last_activity FROM car ORDER BY id DESC");
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) { $error = $e->getMessage(); }
} elseif ($view === 'contracts') {
    try {
        $stmt = $pdo->prepare("SELECT * FROM contracts ORDER BY created_at DESC");
        $stmt->execute();
        $contracts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) { $error = $e->getMessage(); }
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
                <div class="stat-card card-users">
                    <div class="stat-info"><h3><?php echo number_format($totalUsers); ?></h3><p>کاربران عضو</p></div>
                    <div class="stat-icon"><i class="fas fa-users"></i></div>
                </div>
                <!-- کارت آنلاین با آیدی مخصوص برای آپدیت زنده -->
                <div class="stat-card card-online">
                    <div class="stat-info">
                        <h3 id="live-online-count"><?php echo number_format($onlineUsers); ?></h3>
                        <p>کاربران آنلاین</p>
                    </div>
                    <div class="stat-icon"><i class="fas fa-wifi"></i></div>
                </div>
                <div class="stat-card card-contracts">
                    <div class="stat-info"><h3><?php echo number_format($newContracts); ?></h3><p>در انتظار بررسی</p></div>
                    <div class="stat-icon"><i class="fas fa-clock"></i></div>
                </div>
                <div class="stat-card card-revenue">
                    <div class="stat-info"><h3 style="font-size:18px;"><?php echo number_format($totalRevenue); ?> <small style="font-size:10px;">تومان</small></h3><p>درآمد کل</p></div>
                    <div class="stat-icon"><i class="fas fa-wallet"></i></div>
                </div>
                <div class="stat-card card-popular">
                    <div class="stat-info"><h3 style="font-size:16px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:150px;"><?php echo $topCarName; ?></h3><p>پرفروش‌ترین مدل</p></div>
                    <div class="stat-icon"><i class="fas fa-trophy"></i></div>
                </div>
            </div>

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

            <?php if ($view !== 'dashboard'): ?>
            <div style="text-align: left; margin-bottom: 15px;">
                <a href="export_excel.php?type=<?php echo $view; ?>" class="btn-excel"><i class="fas fa-file-excel"></i> دانلود گزارش اکسل</a>
            </div>
            <div class="users-table-container">
                <table class="users-table">
                    <?php if ($view === 'users'): ?>
                    <thead><tr><th>شناسه</th><th>نام کاربری</th><th>ایمیل</th><th>تلفن</th><th>نقش</th><th>وضعیت</th><th>فعالیت</th><th>عملیات</th></tr></thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                        <tr data-user-id="<?php echo $user['id']; ?>">
                            <td><?php echo $user['id']; ?></td>
                            <td><?php echo htmlspecialchars($user['user_name']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars($user['phone']); ?></td>
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
                        <tr>
                            <td><a href="admin.php?view=users&search=<?php echo $contract['user_id']; ?>" class="user-id-link"><?php echo $contract['user_id']; ?> 🔗</a></td>
                            <td><?php echo htmlspecialchars($contract['tracking_code']); ?></td>
                            <td><?php echo htmlspecialchars($contract['real_name']); ?></td>
                            <td><?php echo htmlspecialchars($contract['car_name']); ?></td>
                            <td><?php echo htmlspecialchars($contract['car_price']); ?></td>
                            <td><?php if($contract['status'] == 'pending'): ?><span class="badge badge-warning">در انتظار</span><?php elseif($contract['status'] == 'paid'): ?><span class="badge badge-success">نهایی شده</span><?php else: ?><span class="badge" style="background:red; color:white;">رد شده</span><?php endif; ?></td>
                            <td dir="ltr"><?php echo date('Y/m/d H:i', strtotime($contract['created_at'])); ?></td>
                            <td class="actions"><button class="view-contract-btn" data-contract="<?php echo htmlspecialchars(json_encode($contract), ENT_QUOTES, 'UTF-8'); ?>">مدیریت</button></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <?php endif; ?>
                </table>
            </div>
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

    <script src="js/login signup.js"></script>
    <script src="js/activity_tracker.js"></script>
   <script>
       document.addEventListener('DOMContentLoaded', function() {
           const urlParams = new URLSearchParams(window.location.search);
           const searchParam = urlParams.get('search');
           const currentView = urlParams.get('view') || 'dashboard'; // پیش‌فرض داشبورد
           
           if (currentView === 'users' && searchParam) {
               const searchInput = document.getElementById('admin-search');
               if (searchInput) {
                   searchInput.value = searchParam;
                   setTimeout(() => { searchInput.dispatchEvent(new Event('input')); }, 100);
               }
           }

           const menuToggle = document.getElementById('menuToggle');
           const adminSidebar = document.getElementById('adminSidebar');
           const confirmationModal = document.getElementById('confirmationModal');
           let currentAction = null; let currentUserId = null;
           
           if(menuToggle) menuToggle.addEventListener('click', () => adminSidebar.classList.toggle('active'));
           document.addEventListener('click', (e) => { if (adminSidebar && !adminSidebar.contains(e.target) && menuToggle && !menuToggle.contains(e.target) && adminSidebar.classList.contains('active')) adminSidebar.classList.remove('active'); });
           
           const successPopup = document.getElementById('successPopup');
           function showSuccess(msg) {
               if(!successPopup) return;
               successPopup.textContent = msg;
               successPopup.style.display = 'block';
               setTimeout(() => successPopup.style.display = 'none', 3000);
           }
           
           function openConfirm(title, msg, action, userId) {
               document.getElementById('confirmationTitle').textContent = title;
               document.getElementById('confirmationMessage').textContent = msg;
               currentAction = action; currentUserId = userId;
               confirmationModal.style.display = 'flex';
           }
           
           document.getElementById('cancelActionBtn').addEventListener('click', () => confirmationModal.style.display = 'none');
           document.getElementById('confirmActionBtn').addEventListener('click', () => {
               confirmationModal.style.display = 'none';
               fetch('admin_actions.php', { method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded'}, body: `action=${currentAction}&user_id=${currentUserId}` })
               .then(r=>r.json()).then(d => {
                   if(d.success) { 
                       if(currentAction === 'delete') document.querySelector(`tr[data-user-id="${currentUserId}"]`).remove();
                       else window.location.reload();
                       showSuccess('عملیات موفق'); 
                   } else alert(d.error);
               });
           });

           document.querySelectorAll('.delete-btn').forEach(btn => btn.addEventListener('click', (e) => { e.preventDefault(); openConfirm('حذف', 'مطمئنید؟', 'delete', btn.dataset.userId); }));
           document.querySelectorAll('.promote-btn').forEach(btn => btn.addEventListener('click', (e) => { e.preventDefault(); openConfirm('ارتقا', 'مطمئنید؟', 'promote', btn.dataset.userId); }));
           document.querySelectorAll('.demote-btn').forEach(btn => btn.addEventListener('click', (e) => { e.preventDefault(); openConfirm('تنزل', 'مطمئنید؟', 'demote', btn.dataset.userId); }));

           const searchInput = document.getElementById('admin-search');
           if(searchInput) {
               searchInput.addEventListener('input', function() {
                   const term = this.value.toLowerCase();
                   document.querySelectorAll('.users-table tbody tr').forEach(row => { row.style.display = row.innerText.toLowerCase().includes(term) ? '' : 'none'; });
               });
           }

           const contractModal = document.getElementById('contractDetailsModal');
           let currentContractId = null;
           document.getElementById('closeContractModalBtn')?.addEventListener('click', () => contractModal.style.display = 'none');

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
           if(updateStatusBtn) {
               updateStatusBtn.addEventListener('click', function() {
                   const status = document.getElementById('cd-status-select').value;
                   this.innerText = '...'; this.disabled = true;
                   fetch('admin_actions.php', { method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded'}, body: `action=update_contract_status&contract_id=${currentContractId}&new_status=${status}` })
                   .then(r=>r.json()).then(d => {
                       if(d.success) { showSuccess('وضعیت تغییر کرد'); setTimeout(()=> location.reload(), 1000); }
                       else { alert(d.error); this.innerText = 'ثبت تغییر'; this.disabled = false; }
                   });
               });
           }
       });

       // ===============================================
       // منطق آپدیت زنده (Real-Time) - برای کاربران و داشبورد
       // ===============================================
       let statusUpdateInterval;
       function updateActivityStatuses() {
           
           // درخواست به سرور
           fetch('get_online_status.php')
               .then(response => response.json())
               .then(data => {
                   if (data.success && data.users) {
                       
                       // 1. آپدیت جدول کاربران (اگر در تب کاربران باشیم)
                       data.users.forEach(user => {
                           const cell = document.querySelector(`.activity-status-cell[data-user-id="${user.id}"] .activity-status`);
                           if (cell) {
                               const timeAgo = calculateTimeAgo(user.last_activity);
                               let isOnline = false;
                               if (user.last_activity) {
                                   const now = new Date();
                                   const last = new Date(user.last_activity);
                                   if((now - last)/60000 <= 3) isOnline = true;
                               }
                               const txt = isOnline ? `🟢 آنلاین (${timeAgo})` : `🔴 آفلاین (${timeAgo})`;
                               cell.innerHTML = txt;
                               cell.className = 'activity-status ' + (isOnline ? 'status-online' : 'status-offline');
                           }
                       });

                       // 2. آپدیت عدد داشبورد (اگر در تب داشبورد باشیم)
                       const dashboardCountEl = document.getElementById('live-online-count');
                       if (dashboardCountEl) {
                           // شمارش کاربران آنلاین (زیر 3 دقیقه)
                           const onlineCount = data.users.filter(u => {
                               if(!u.last_activity) return false;
                               return (new Date() - new Date(u.last_activity))/60000 <= 3;
                           }).length;
                           
                           dashboardCountEl.innerText = onlineCount;
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

       document.addEventListener('DOMContentLoaded', function() {
           updateActivityStatuses();
           statusUpdateInterval = setInterval(updateActivityStatuses, 30000); // هر 30 ثانیه
       });

       window.addEventListener('beforeunload', function() {
           if (statusUpdateInterval) clearInterval(statusUpdateInterval);
       });
   </script>
</body>
</html>