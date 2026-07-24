<?php
session_start();
require_once 'db_connect.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require_once 'PHPMailer-master/src/Exception.php';
require_once 'PHPMailer-master/src/PHPMailer.php';
require_once 'PHPMailer-master/src/SMTP.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] != 1) {
    echo json_encode(['success' => false, 'error' => 'دسترسی غیرمجاز']);
    exit();
}

$action = isset($_POST['action']) ? $_POST['action'] : '';

function uploadImage($tmp_name, $name) {
    if(empty($tmp_name)) return '';
    $ext = pathinfo($name, PATHINFO_EXTENSION);
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];
    if (!in_array(strtolower($ext), $allowed)) throw new Exception('فرمت عکس غیرمجاز است.');
    if (!is_dir('images')) mkdir('images', 0777, true);
    $newName = 'images/img_' . time() . '_' . rand(1000,9999) . '.' . $ext;
    if (!move_uploaded_file($tmp_name, $newName)) throw new Exception('خطا در آپلود عکس.');
    return $newName;
}

function uploadAudio($tmp_name, $name) {
    if(empty($tmp_name)) return null;
    $ext = pathinfo($name, PATHINFO_EXTENSION);
    $allowed =['mp3', 'wav', 'ogg'];
    if (!in_array(strtolower($ext), $allowed)) throw new Exception('فرمت فایل صوتی غیرمجاز است.');
    if (!is_dir('audio')) mkdir('audio', 0777, true);
    $newName = 'audio/sound_' . time() . '_' . rand(1000,9999) . '.' . $ext;
    if (!move_uploaded_file($tmp_name, $newName)) throw new Exception('خطا در آپلود فایل صوتی.');
    return $newName;
}

try {
    if (in_array($action,['delete', 'promote', 'demote'])) {
        $userId = (int)$_POST['user_id'];
        if ($userId == $_SESSION['user_id']) throw new Exception('عملیات روی خودتان مجاز نیست.');
        
        // if ($action == 'delete') $pdo->prepare("DELETE FROM car WHERE id = ?")->execute([$userId]);
        
        if ($action == 'delete') {
            // =========================================================
            // سیستم حذف هوشمند کاربر (Pre-Deletion Cleanup)
            // =========================================================
                    // 0. گرفتن ایمیل کاربر برای متوقف کردن ایمیل‌های در صف انتظار
                    $stmtUser = $pdo->prepare("SELECT email FROM car WHERE id = ?");
                    $stmtUser->execute([$userId]);
                    $deletedUserEmail = $stmtUser->fetchColumn();
        
                    if ($deletedUserEmail) {
                        // حذف تمام ایمیل‌هایی که هنوز ارسال نشده‌اند تا برای کاربرِ حذف شده ایمیلی نرود
                        $pdo->prepare("DELETE FROM email_queue WHERE recipient_email = ? AND status IN ('pending', 'processing')")->execute([$deletedUserEmail]);
                    }
            // 1. پیدا کردن قراردادهای "در انتظار" کاربر برای بازگرداندن ماشین‌ها به انبار
            $stmtPending = $pdo->prepare("SELECT product_id, car_color FROM contracts WHERE user_id = ? AND status = 'pending' FOR UPDATE");
            $stmtPending->execute([$userId]);
            $pendingContracts = $stmtPending->fetchAll(PDO::FETCH_ASSOC);

            foreach ($pendingContracts as $pc) {
                $pId = $pc['product_id'];
                $colorName = $pc['car_color'];
                
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
                        
                        // بازگرداندن ۱ عدد به انبار
                        $colorsArr[$colorName] = ['hex' => $currentHex, 'qty' => $currentQty + 1, 'img' => $currentImg];
                        
                        $new_total = 0;
                        foreach ($colorsArr as $c) { $new_total += is_array($c) ? intval($c['qty']) : intval($c); }
                        
                        // آپدیت انبار
                        $pdo->prepare("UPDATE products SET inventory = ?, colors_inventory = ? WHERE id = ?")
                            ->execute([$new_total, json_encode($colorsArr, JSON_UNESCAPED_UNICODE), $pId]);
                    }
                }
            }

            // 2. حذف کامل قراردادهای زباله (در انتظار و رد شده)
            $pdo->prepare("DELETE FROM contracts WHERE user_id = ? AND status IN ('pending', 'rejected')")->execute([$userId]);

            // 3. در نهایت حذف خود کاربر (قراردادهای موفق به طور خودکار در دیتابیس SET NULL می‌شوند)
            $pdo->prepare("DELETE FROM car WHERE id = ?")->execute([$userId]);
        }
        elseif ($action == 'promote') $pdo->prepare("UPDATE car SET role = 1 WHERE id = ?")->execute([$userId]); 
        elseif ($action == 'demote') $pdo->prepare("UPDATE car SET role = 0 WHERE id = ?")->execute([$userId]); 
        
        echo json_encode(['success' => true]);
    }

    elseif ($action == 'update_contract_status') {
        $cId = $_POST['contract_id'];
        $st = $_POST['new_status'];
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("SELECT product_id, car_color, status FROM contracts WHERE id = ? FOR UPDATE");
        $stmt->execute([$cId]);
        $contract = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($contract && !empty($contract['product_id'])) {
            $oldStatus = $contract['status'];
            $productId = $contract['product_id'];
            $colorName = $contract['car_color'];
            $inventoryChange = 0;
            if ($st == 'rejected' && $oldStatus != 'rejected') $inventoryChange = 1; 
            elseif ($oldStatus == 'rejected' && $st != 'rejected') $inventoryChange = -1;

            if ($inventoryChange != 0) {
                $pStmt = $pdo->prepare("SELECT colors_inventory FROM products WHERE id = ? FOR UPDATE");
                $pStmt->execute([$productId]);
                $prod = $pStmt->fetch(PDO::FETCH_ASSOC);

                if ($prod) {
                    $colorsArr = json_decode($prod['colors_inventory'], true);
                    if (!is_array($colorsArr)) $colorsArr =[];

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

                    $newQty = max(0, $currentQty + $inventoryChange);
                    $colorsArr[$colorName] =['hex' => $currentHex, 'qty' => $newQty, 'img' => $currentImg];
                    
                    $new_total = 0;
                    foreach ($colorsArr as $c) { $new_total += is_array($c) ? intval($c['qty']) : intval($c); }
                    $pdo->prepare("UPDATE products SET inventory = ?, colors_inventory = ? WHERE id = ?")
                        ->execute([$new_total, json_encode($colorsArr, JSON_UNESCAPED_UNICODE), $productId]);
                }
            }
        }
        // $pdo->prepare("UPDATE contracts SET status = ? WHERE id = ?")->execute([$st, $cId]);
        // اگر وضعیت به "نهایی شده" تغییر کرد، زمان پرداخت را ثبت کن. در غیر این صورت زمان پرداخت را خالی کن.
        if ($st === 'paid') {
            $pdo->prepare("UPDATE contracts SET status = ?, paid_at = CURRENT_TIMESTAMP WHERE id = ?")->execute([$st, $cId]);
        } else {
            $pdo->prepare("UPDATE contracts SET status = ?, paid_at = NULL WHERE id = ?")->execute([$st, $cId]);
        }
        $pdo->commit();
        echo json_encode(['success' => true]);
    }

    elseif ($action == 'add_product') {
        if (!isset($_FILES['product_image']) || $_FILES['product_image']['error'] != 0) {
            throw new Exception('انتخاب تصویر اصلی خودرو الزامی است.');
        }

        $mainImagePath = uploadImage($_FILES['product_image']['tmp_name'], $_FILES['product_image']['name']);
        
        $soundPath = null;
        if (isset($_FILES['engine_sound']) && $_FILES['engine_sound']['error'] == 0) {
            $soundPath = uploadAudio($_FILES['engine_sound']['tmp_name'], $_FILES['engine_sound']['name']);
        }

        $colors =[];
        $total_inventory = 0;

        if (isset($_POST['color_names']) && is_array($_POST['color_names'])) {
            for ($i = 0; $i < count($_POST['color_names']); $i++) {
                $cName = trim($_POST['color_names'][$i]);
                if ($cName === '') continue;
                $cHex = $_POST['color_hexes'][$i];
                $cQty = (int)$_POST['color_qtys'][$i];
                $cImg = ''; 
                if (isset($_FILES['color_images']['error'][$i]) && $_FILES['color_images']['error'][$i] == 0) {
                    $cImg = uploadImage($_FILES['color_images']['tmp_name'][$i], $_FILES['color_images']['name'][$i]);
                }
                $colors[$cName] =['hex' => $cHex, 'qty' => $cQty, 'img' => $cImg];
                $total_inventory += $cQty;
            }
        }
        $colors_json = json_encode($colors, JSON_UNESCAPED_UNICODE);

        // اضافه شدن is_vip به کوئری
        $stmt = $pdo->prepare("INSERT INTO products (name, brand, price, old_price, inventory, colors_inventory, image, engine_sound, hp, accel, engine, in_slider, is_vip) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $_POST['name'], $_POST['brand'], $_POST['price'], 
            !empty($_POST['old_price']) ? $_POST['old_price'] : null, 
            $total_inventory, $colors_json, $mainImagePath, $soundPath, 
            $_POST['hp'], $_POST['accel'], $_POST['engine'], 
            isset($_POST['in_slider']) ? 1 : 0,
            isset($_POST['is_vip']) ? 1 : 0 // ثبت مقدار VIP
        ]);

        // // -----------------------------------------------------------------
        // // موتور ارسال نوتیفیکیشن هدفمند برای محصول جدید
        // // -----------------------------------------------------------------
        // $new_product_id = $pdo->lastInsertId(); // گرفتن آیدی ماشینی که الان ثبت شد
        // $notif_target = $_POST['notif_target'] ?? 'none';
        // $product_name = $_POST['name'];

        // if ($notif_target !== 'none') {
        //     $usersToNotify = [];
            
        //     if ($notif_target === 'all') {
        //         // دریافت همه کاربران
        //         $uStmt = $pdo->query("SELECT id FROM car");
        //         $usersToNotify = $uStmt->fetchAll(PDO::FETCH_COLUMN);
        //     } else {
        //         // منطق کلاب VIP (گرفتن کاربرانی که تعداد خریدهای موفقشان به حد نصاب رسیده)
        //         $min_purchases = ($notif_target === 'diamond') ? 3 : 1;
        //         $uStmt = $pdo->prepare("
        //             SELECT user_id 
        //             FROM contracts 
        //             WHERE status = 'paid' 
        //             GROUP BY user_id 
        //             HAVING COUNT(*) >= ?
        //         ");
        //         $uStmt->execute([$min_purchases]);
        //         $usersToNotify = $uStmt->fetchAll(PDO::FETCH_COLUMN);
        //     }

        //     if (!empty($usersToNotify)) {
        //         // تغییر متن نوتیفیکیشن بر اساس نوع کاربر
        //         $notifTitle = ($notif_target === 'all') ? "🚗 خودروی جدید رسید!" : "💎 پیشنهاد انحصاری VIP";
        //         $notifMsg = "خودروی لوکس $product_name به تازگی به نمایشگاه اضافه شده است. همین الان روی این پیام کلیک کنید و آن را ببینید.";
                
        //         $insertNotif = $pdo->prepare("INSERT INTO notifications (user_id, type, title, message, reference_id) VALUES (?, 'new_product', ?, ?, ?)");
                
        //         foreach ($usersToNotify as $uid) {
        //             $insertNotif->execute([$uid, $notifTitle, $notifMsg, $new_product_id]);
        //         }
        //     }
        // }
        // -----------------------------------------------------------------

        // -----------------------------------------------------------------
        // موتور ارسال نوتیفیکیشن و صف ایمیل (Bulk Insert)
        // -----------------------------------------------------------------
        $new_product_id = $pdo->lastInsertId();
        $notif_target = $_POST['notif_target'] ?? 'none';
        $product_name = $_POST['name'];

        if ($notif_target !== 'none') {
            $notifTitle = ($notif_target === 'all') ? "🚗 خودروی جدید رسید!" : "💎 پیشنهاد انحصاری VIP";
            $notifMsg = "خودروی لوکس $product_name به تازگی به نمایشگاه اضافه شده است. همین الان روی این پیام کلیک کنید و آن را ببینید.";

            // قالب HTML ایمیل (به دو بخش تقسیم شده تا با SQL CONCAT نام کاربر را وسط آن قرار دهیم)
            $mailSubject = $notifTitle;
            $mailPrefix = '<div style="font-family: Tahoma, Arial, sans-serif; direction: rtl; text-align: right; border: 1px solid #ddd; padding: 20px; border-radius: 10px;"><h2 style="color: #e91e63;">خبر خوب برای شما!</h2><p>درود <b>';
            $mailSuffix = '</b> عزیز،</p><p>خودروی <b>'.$product_name.'</b> به سایت اضافه شد. پیشنهاد می‌کنیم فرصت را از دست ندهید.</p><br><a href="https://ratindn.ir" style="background-color: #e91e63; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">مشاهده و خرید</a></div>';

            $sqlNotif = ""; $sqlEmail = ""; $paramsNotif = []; $paramsEmail = [];

            if ($notif_target === 'all') {
                // 1. کوئری نوتیفیکیشن برای همه
                $sqlNotif = "INSERT INTO notifications (user_id, type, title, message, reference_id) SELECT id, 'new_product', ?, ?, ? FROM car WHERE role = 0";
                $paramsNotif = [$notifTitle, $notifMsg, $new_product_id];
                
                // 2. کوئری صف ایمیل برای همه (جادوی اضافه کردن اسم کاربر در متن ایمیل)
                $sqlEmail = "INSERT INTO email_queue (recipient_email, recipient_name, subject, body) SELECT email, user_name, ?, CONCAT(?, user_name, ?) FROM car WHERE role = 0";
                $paramsEmail = [$mailSubject, $mailPrefix, $mailSuffix];
            } else {
                // منطق VIP
                $min_purchases = ($notif_target === 'diamond') ? 3 : 1;
                
                // 1. کوئری نوتیفیکیشن VIP
                $sqlNotif = "INSERT INTO notifications (user_id, type, title, message, reference_id) 
                             SELECT c.id, 'new_product', ?, ?, ? FROM car c JOIN contracts ct ON c.id = ct.user_id 
                             WHERE c.role = 0 AND ct.status = 'paid' GROUP BY c.id HAVING COUNT(ct.id) >= ?";
                $paramsNotif = [$notifTitle, $notifMsg, $new_product_id, $min_purchases];
                
                // 2. کوئری صف ایمیل VIP
                $sqlEmail = "INSERT INTO email_queue (recipient_email, recipient_name, subject, body) 
                             SELECT c.email, c.user_name, ?, CONCAT(?, c.user_name, ?) FROM car c JOIN contracts ct ON c.id = ct.user_id 
                             WHERE c.role = 0 AND ct.status = 'paid' GROUP BY c.id HAVING COUNT(ct.id) >= ?";
                $paramsEmail = [$mailSubject, $mailPrefix, $mailSuffix, $min_purchases];
            }

           // دریافت نحوه ارسال از فرم ادمین
           $notif_method = $_POST['notif_method'] ?? 'bell_only';

           // اجرای برق‌آسای کوئری‌ها بر اساس انتخاب ادمین
           if ($sqlNotif !== "") {
               // همیشه نوتیفیکیشن زنگوله را برای گروه هدف ثبت کن
               $pdo->prepare($sqlNotif)->execute($paramsNotif); 
               
               // فقط اگر ادمین گزینه "زنگوله + ایمیل" را انتخاب کرده بود، ایمیل‌ها را در صف قرار بده
               if ($notif_method === 'both' && $sqlEmail !== "") {
                   $pdo->prepare($sqlEmail)->execute($paramsEmail);
               }
           }
        }
        // -----------------------------------------------------------------
        
        echo json_encode(['success' => true, 'message' => 'محصول با موفقیت اضافه شد.']);
    }

    elseif ($action == 'edit_product') {
        $id = $_POST['product_id'];
        
        $stmt = $pdo->prepare("SELECT name, inventory, colors_inventory FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $oldProdData = $stmt->fetch(PDO::FETCH_ASSOC);
        $oldColors = json_decode($oldProdData['colors_inventory'], true) ?:[];
        $oldInventory = (int)$oldProdData['inventory']; // این خط به سیستم می‌فهماند موجودی قبلی چند بوده
        $productName = $oldProdData['name']; 
        $stmt->execute([$id]);
        $oldColors = json_decode($stmt->fetchColumn(), true) ?:[];
        $oldColorImages =[];
        foreach($oldColors as $c) { if(is_array($c) && !empty($c['img'])) $oldColorImages[] = $c['img']; }

        $colors =[];
        $total_inventory = 0;
        $newColorImages =[];

        if (isset($_POST['color_names']) && is_array($_POST['color_names'])) {
            for ($i = 0; $i < count($_POST['color_names']); $i++) {
                $cName = trim($_POST['color_names'][$i]);
                if ($cName === '') continue;
                $cHex = $_POST['color_hexes'][$i];
                $cQty = (int)$_POST['color_qtys'][$i];
                $existingImg = $_POST['existing_color_imgs'][$i] ?? '';
                $finalImg = $existingImg;

                if (isset($_FILES['color_images']['error'][$i]) && $_FILES['color_images']['error'][$i] == 0) {
                    if ($existingImg && file_exists($existingImg)) unlink($existingImg);
                    $finalImg = uploadImage($_FILES['color_images']['tmp_name'][$i], $_FILES['color_images']['name'][$i]);
                }
                if($finalImg) $newColorImages[] = $finalImg;
                $colors[$cName] =['hex' => $cHex, 'qty' => $cQty, 'img' => $finalImg];
                $total_inventory += $cQty;
            }
        }
        
        $orphanedImages = array_diff($oldColorImages, $newColorImages);
        foreach($orphanedImages as $orphan) if(file_exists($orphan)) unlink($orphan);

        $colors_json = json_encode($colors, JSON_UNESCAPED_UNICODE);
        
        // اضافه شدن is_vip به کوئری آپدیت
        $sql = "UPDATE products SET name=?, brand=?, price=?, old_price=?, inventory=?, colors_inventory=?, hp=?, accel=?, engine=?, in_slider=?, is_vip=? ";
        $params =[
            $_POST['name'], $_POST['brand'], $_POST['price'], 
            !empty($_POST['old_price']) ? $_POST['old_price'] : null, 
            $total_inventory, $colors_json, $_POST['hp'], $_POST['accel'], $_POST['engine'], 
            isset($_POST['in_slider']) ? 1 : 0,
            isset($_POST['is_vip']) ? 1 : 0 // ذخیره مقدار VIP
        ];

        if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == 0) {
            $stmt = $pdo->prepare("SELECT image FROM products WHERE id = ?");
            $stmt->execute([$id]);
            $oldMainImg = $stmt->fetchColumn();
            if($oldMainImg && file_exists($oldMainImg)) unlink($oldMainImg); 
            $mainImagePath = uploadImage($_FILES['product_image']['tmp_name'], $_FILES['product_image']['name']);
            $sql .= ", image=? ";
            $params[] = $mainImagePath;
        }

        if (isset($_FILES['engine_sound']) && $_FILES['engine_sound']['error'] == 0) {
            $stmt = $pdo->prepare("SELECT engine_sound FROM products WHERE id = ?");
            $stmt->execute([$id]);
            $oldSound = $stmt->fetchColumn();
            if($oldSound && file_exists($oldSound)) unlink($oldSound); 
            $soundPath = uploadAudio($_FILES['engine_sound']['tmp_name'], $_FILES['engine_sound']['name']);
            $sql .= ", engine_sound=? ";
            $params[] = $soundPath;
        }

        $sql .= " WHERE id=?";
        $params[] = $id;
        
        // اجرای آپدیت محصول
        $pdo->prepare($sql)->execute($params);
        
        // // ============================================================
        // // سیستم هوشمند اطلاع‌رسانی موجودی (Restock Notification)
        // // ============================================================
        // // 1. بررسی می‌کنیم که آیا موجودی قبلاً صفر بوده و الان بیشتر از صفر شده؟
        // if (!isset($oldInventory)) {
        //     // اگر متغیر ست نشده بود، میگیریمش
        //     $stmtCheck = $pdo->prepare("SELECT inventory, name FROM products WHERE id = ?");
        //     $stmtCheck->execute([$id]);
        //     $pData = $stmtCheck->fetch(PDO::FETCH_ASSOC);
        //     $productName = $pData['name'];
        // }

        // // اگر ماشین قبلا صفر بوده و الان موجود شده (total_inventory متغیری است که در کدهای شما محاسبه شده)
        // if ($oldInventory == 0 && $total_inventory > 0) {
            
        //     // 2. پیدا کردن تمام کاربرانی که این ماشین را لایک کرده‌اند
        //     $wStmt = $pdo->prepare("
        //         SELECT w.user_id, c.email, c.user_name 
        //         FROM wishlist w 
        //         JOIN car c ON w.user_id = c.id 
        //         WHERE w.product_id = ?
        //     ");
        //     $wStmt->execute([$id]);
        //     $interestedUsers = $wStmt->fetchAll(PDO::FETCH_ASSOC);

        //     if (count($interestedUsers) > 0) {
        //         // آماده‌سازی کوئری برای ثبت نوتیفیکیشن زنگوله
        //         $notifStmt = $pdo->prepare("INSERT INTO notifications (user_id, type, title, message, reference_id) VALUES (?, 'restock', ?, ?, ?)");
                
        //         $title = "خودروی رویایی شما موجود شد!";
        //         $msg = "خبر خوب! خودروی $productName که در گاراژ آرزوهای شما بود، هم‌اکنون شارژ شد. فرصت را از دست ندهید.";

        //         // تنظیمات ایمیل
        //         $mail = new PHPMailer(true);
        //         try {
        //             $mail->isSMTP();
        //             $mail->Host = 'smtp.gmail.com'; 
        //             $mail->SMTPAuth = true;
        //             $mail->Username = 'dnratin@gmail.com'; // ایمیل شما
        //             $mail->Password = 'yydd utlt bqkv eaps'; // اپ پسورد شما
        //             $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        //             $mail->Port = 465;
        //             $mail->CharSet = 'UTF-8';
        //             $mail->setFrom('dnratin@gmail.com', 'فروشگاه خودروهای لوکس');
        //             $mail->isHTML(true);
        //             $mail->Subject = '🔥 موجود شدن خودروی محبوب شما';

        //             // ارسال برای تک تک کاربران
        //             foreach ($interestedUsers as $u) {
        //                 // الف) ثبت در زنگوله سایت
        //                 $notifStmt->execute([$u['user_id'], $title, $msg, $id]);

        //                 // ب) ارسال ایمیل
        //                 $mail->clearAddresses();
        //                 $mail->addAddress($u['email'], $u['user_name']);
        //                 $mail->Body = '
        //                 <div style="font-family: Tahoma, Arial, sans-serif; direction: rtl; text-align: right; border: 1px solid #ddd; padding: 20px; border-radius: 10px;">
        //                     <h2 style="color: #e91e63;">خبر خوب برای شما!</h2>
        //                     <p>درود <b>'.$u['user_name'].'</b> عزیز،</p>
        //                     <p>خودروی <b>'.$productName.'</b> که قبلاً به لیست علاقه‌مندی‌های خود (گاراژ آرزوها) اضافه کرده بودید، هم‌اکنون در سایت موجود شد!</p>
        //                     <p>با توجه به محدود بودن موجودی، پیشنهاد می‌کنیم هرچه سریع‌تر به سایت مراجعه کرده و قرارداد خود را نهایی کنید.</p>
        //                     <br>
        //                     <a href="https://ratindn.ir" style="background-color: #e91e63; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold;">مشاهده و خرید سریع</a>
        //                 </div>';
                        
        //                 $mail->send();
        //             }
                    
        //         } catch (Exception $e) {
        //             // در صورت خطای ایمیل، بقیه فرآیند متوقف نشود
        //             error_log("خطا در ارسال ایمیل موجودی: " . $mail->ErrorInfo);
        //         }
        //     }
        // }
        // // ============================================================
// ============================================================
        // سیستم هوشمند اطلاع‌رسانی موجودی (Restock Notification - Bulk Insert)
        // ============================================================
        if (!isset($oldInventory)) {
            $stmtCheck = $pdo->prepare("SELECT inventory, name FROM products WHERE id = ?");
            $stmtCheck->execute([$id]);
            $pData = $stmtCheck->fetch(PDO::FETCH_ASSOC);
            $productName = $pData['name'];
        }

        // اگر ماشین قبلا صفر بوده و الان موجود شده است
        if ($oldInventory == 0 && $total_inventory > 0) {
            
            $notifTitle = "🔥 موجود شدن خودروی محبوب شما";
            $notifMsg = "خبر خوب! خودروی $productName که در گاراژ آرزوهای شما بود، هم‌اکنون شارژ شد. فرصت را از دست ندهید.";
            
            // قالب HTML ایمیل
            $mailSubject = $notifTitle;
            $mailPrefix = '<div style="font-family: Tahoma, Arial, sans-serif; direction: rtl; text-align: right; border: 1px solid #ddd; padding: 20px; border-radius: 10px;"><h2 style="color: #e91e63;">خبر خوب برای شما!</h2><p>درود <b>';
            $mailSuffix = '</b> عزیز،</p><p>خودروی <b>'.$productName.'</b> که قبلاً به لیست علاقه‌مندی‌های خود (گاراژ آرزوها) اضافه کرده بودید، هم‌اکنون در سایت موجود شد!</p><p>با توجه به محدود بودن موجودی، پیشنهاد می‌کنیم هرچه سریع‌تر به سایت مراجعه کرده و خرید خود را نهایی کنید.</p><br><a href="https://ratindn.ir" style="background-color: #e91e63; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold;">مشاهده و خرید سریع</a></div>';

            // 1. ثبت گروهی نوتیفیکیشن‌ها (زنگوله) با یک دستور فوق‌سریع
            $sqlNotif = "INSERT INTO notifications (user_id, type, title, message, reference_id) 
                         SELECT user_id, 'restock', ?, ?, ? FROM wishlist WHERE product_id = ?";
            $pdo->prepare($sqlNotif)->execute([$notifTitle, $notifMsg, $id, $id]);

            // 2. ثبت گروهی ایمیل‌ها در صف (Queue) برای ارسال توسط ربات پس‌زمینه
            $sqlEmail = "INSERT INTO email_queue (recipient_email, recipient_name, subject, body) 
                         SELECT c.email, c.user_name, ?, CONCAT(?, c.user_name, ?) 
                         FROM wishlist w JOIN car c ON w.user_id = c.id 
                         WHERE w.product_id = ?";
            $pdo->prepare($sqlEmail)->execute([$mailSubject, $mailPrefix, $mailSuffix, $id]);
        }
        // ============================================================
        echo json_encode(['success' => true, 'message' => 'محصول با موفقیت ویرایش شد.']);
        
    }

    elseif ($action == 'delete_product') {
        $id = $_POST['product_id'];
        $stmt = $pdo->prepare("SELECT image, engine_sound, colors_inventory FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $prod = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($prod) {
            if($prod['image'] && file_exists($prod['image'])) unlink($prod['image']);
            if($prod['engine_sound'] && file_exists($prod['engine_sound'])) unlink($prod['engine_sound']);
            if($prod['colors_inventory']) {
                $cols = json_decode($prod['colors_inventory'], true);
                if(is_array($cols)) {
                    foreach($cols as $c) {
                        if(is_array($c) && !empty($c['img']) && file_exists($c['img'])) { unlink($c['img']); }
                    }
                }
            }
        }
        $pdo->prepare("DELETE FROM products WHERE id = ?")->execute([$id]);
        echo json_encode(['success' => true]);
    }

    // ==========================================
    // عملیات مربوط به گزارشات سیستم (ایمیل‌ها)
    // ==========================================
    elseif ($action == 'retry_email') {
        $id = (int)$_POST['email_id'];
        // برگرداندن ایمیل به صف (وضعیت pending) تا ربات دوباره آن را بفرستد
        $pdo->prepare("UPDATE email_queue SET status = 'pending' WHERE id = ?")->execute([$id]);
        echo json_encode(['success' => true]);
    }

    // تلاش مجدد برای تمام ایمیل‌های ناموفق (برگرداندن همه به صف)
    elseif ($action == 'retry_all_failed') {
        $pdo->exec("UPDATE email_queue SET status = 'pending' WHERE status = 'failed'");
        echo json_encode(['success' => true]);
    }
    
    elseif ($action == 'delete_email') {
        $id = (int)$_POST['email_id'];
        $pdo->prepare("DELETE FROM email_queue WHERE id = ?")->execute([$id]);
        echo json_encode(['success' => true]);
    }
    
    elseif ($action == 'delete_bulk_emails') {
        $type = $_POST['del_type'];
        if ($type === 'failed') {
            $pdo->exec("DELETE FROM email_queue WHERE status = 'failed'");
        } elseif ($type === 'sent') {
            $pdo->exec("DELETE FROM email_queue WHERE status = 'sent'");
        }
        echo json_encode(['success' => true]);
    }

    // ==========================================
    // عملیات مدیریت نوتیفیکیشن‌ها توسط ادمین
    // ==========================================
    elseif ($action == 'delete_notif_admin') {
        $id = (int)$_POST['notif_id'];
        $pdo->prepare("DELETE FROM notifications WHERE id = ?")->execute([$id]);
        echo json_encode(['success' => true]);
    }
    
    elseif ($action == 'delete_bulk_notifs_admin') {
        // مدیر می‌تواند تمام نوتیفیکیشن‌های سیستم را یکجا پاک کند
        $pdo->exec("DELETE FROM notifications");
        echo json_encode(['success' => true]);
    }

    // ==========================================
    // عملیات حذف گروهی با چک‌باکس (ایمیل‌ها و نوتیف‌ها)
    // ==========================================
    elseif ($action == 'delete_selected_emails') {
        $ids = json_decode($_POST['ids'], true);
        if (!empty($ids) && is_array($ids)) {
            // ساختن پارامترهای امن ( ?,?,? ) به تعداد آیدی‌ها برای امنیت کامل
            $inQuery = implode(',', array_fill(0, count($ids), '?'));
            $stmt = $pdo->prepare("DELETE FROM email_queue WHERE id IN ($inQuery)");
            $stmt->execute($ids);
        }
        echo json_encode(['success' => true]);
    }
    
    elseif ($action == 'delete_selected_notifs') {
        $ids = json_decode($_POST['ids'], true);
        if (!empty($ids) && is_array($ids)) {
            $inQuery = implode(',', array_fill(0, count($ids), '?'));
            $stmt = $pdo->prepare("DELETE FROM notifications WHERE id IN ($inQuery)");
            $stmt->execute($ids);
        }
        echo json_encode(['success' => true]);
    }

    






    // ==========================================
    // عملیات مدیریت قراردادها (حذف هوشمند)
    // ==========================================
    elseif ($action == 'delete_contract') {
        $cId = (int)$_POST['contract_id'];
        
        $pdo->beginTransaction();
        
        // دریافت اطلاعات قرارداد برای بررسی وضعیت و موجودی
        $stmt = $pdo->prepare("SELECT product_id, car_color, status FROM contracts WHERE id = ? FOR UPDATE");
        $stmt->execute([$cId]);
        $contract = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($contract) {
            // 1. محافظت از قراردادهای پرداخت شده
            if ($contract['status'] === 'paid') {
                throw new Exception('قراردادهای پرداخت شده به دلایل مالی قابل حذف نیستند.');
            }
            
            // 2. اگر قرارداد "در انتظار" بود، یعنی ماشین هنوز رزرو است. باید آن را به انبار برگردانیم!
            // (اگر "رد شده" باشد، قبلاً موقع رد شدن به انبار برگشته است، پس کاری با انبار نداریم).
            if ($contract['status'] === 'pending' && !empty($contract['product_id'])) {
                $productId = $contract['product_id'];
                $colorName = $contract['car_color'];
                
                $pStmt = $pdo->prepare("SELECT colors_inventory FROM products WHERE id = ? FOR UPDATE");
                $pStmt->execute([$productId]);
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
                    
                    // برگرداندن ۱ عدد ماشین به موجودی این رنگ
                    $colorsArr[$colorName] = ['hex' => $currentHex, 'qty' => $currentQty + 1, 'img' => $currentImg];
                    
                    $new_total = 0;
                    foreach ($colorsArr as $c) { $new_total += is_array($c) ? intval($c['qty']) : intval($c); }
                    
                    $pdo->prepare("UPDATE products SET inventory = ?, colors_inventory = ? WHERE id = ?")
                        ->execute([$new_total, json_encode($colorsArr, JSON_UNESCAPED_UNICODE), $productId]);
                }
            }
            
            // 3. حذف کامل رکورد قرارداد از دیتابیس
            $pdo->prepare("DELETE FROM contracts WHERE id = ?")->execute([$cId]);
        }
        
        $pdo->commit();
        echo json_encode(['success' => true]);
    }
    else { throw new Exception('عملیات نامعتبر است.'); }

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

?>