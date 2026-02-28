<?php
session_start();
require_once 'db_connect.php';

header('Content-Type: application/json; charset=utf-8');

// بررسی دسترسی ادمین
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] != 1) {
    echo json_encode(['success' => false, 'error' => 'دسترسی غیرمجاز']);
    exit();
}

$action = isset($_POST['action']) ? $_POST['action'] : '';

// ==========================================
// توابع کمکی برای آپلود فایل‌ها
// ==========================================
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
    $allowed = ['mp3', 'wav', 'ogg'];
    if (!in_array(strtolower($ext), $allowed)) throw new Exception('فرمت فایل صوتی غیرمجاز است.');
    if (!is_dir('audio')) mkdir('audio', 0777, true);
    $newName = 'audio/sound_' . time() . '_' . rand(1000,9999) . '.' . $ext;
    if (!move_uploaded_file($tmp_name, $newName)) throw new Exception('خطا در آپلود فایل صوتی.');
    return $newName;
}

try {
    // ====================================================
    // 1. مدیریت کاربران (حذف، ارتقا، تنزل)
    // ====================================================
    if (in_array($action, ['delete', 'promote', 'demote'])) {
        $userId = (int)$_POST['user_id'];
        if ($userId == $_SESSION['user_id']) throw new Exception('عملیات روی خودتان مجاز نیست.');
        
        if ($action == 'delete') $pdo->prepare("DELETE FROM car WHERE id = ?")->execute([$userId]); 
        elseif ($action == 'promote') $pdo->prepare("UPDATE car SET role = 1 WHERE id = ?")->execute([$userId]); 
        elseif ($action == 'demote') $pdo->prepare("UPDATE car SET role = 0 WHERE id = ?")->execute([$userId]); 
        
        echo json_encode(['success' => true]);
    }

    // ====================================================
    // 2. مدیریت وضعیت قراردادها و برگشت موجودی
    // ====================================================
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
                    foreach ($colorsArr as $c) {
                        $new_total += is_array($c) ? intval($c['qty']) : intval($c);
                    }

                    $colors_json = json_encode($colorsArr, JSON_UNESCAPED_UNICODE);
                    $pdo->prepare("UPDATE products SET inventory = ?, colors_inventory = ? WHERE id = ?")
                        ->execute([$new_total, $colors_json, $productId]);
                }
            }
        }

        $pdo->prepare("UPDATE contracts SET status = ? WHERE id = ?")->execute([$st, $cId]);
        $pdo->commit();
        echo json_encode(['success' => true]);
    }

    // ====================================================
    // 3. افزودن محصول جدید (عکس اصلی، عکس رنگ‌ها، صدای موتور)
    // ====================================================
    elseif ($action == 'add_product') {
        if (!isset($_FILES['product_image']) || $_FILES['product_image']['error'] != 0) {
            throw new Exception('انتخاب تصویر اصلی خودرو الزامی است.');
        }

        // آپلود عکس اصلی
        $mainImagePath = uploadImage($_FILES['product_image']['tmp_name'], $_FILES['product_image']['name']);
        
        // آپلود صدای موتور (اختیاری)
        $soundPath = null;
        if (isset($_FILES['engine_sound']) && $_FILES['engine_sound']['error'] == 0) {
            $soundPath = uploadAudio($_FILES['engine_sound']['tmp_name'], $_FILES['engine_sound']['name']);
        }

        // پردازش رنگ‌ها و عکس‌های اختصاصی رنگ
        $colors =[];
        $total_inventory = 0;

        if (isset($_POST['color_names']) && is_array($_POST['color_names'])) {
            for ($i = 0; $i < count($_POST['color_names']); $i++) {
                $cName = trim($_POST['color_names'][$i]);
                if ($cName === '') continue; // اگر اسم رنگ خالی بود رد شو
                
                $cHex = $_POST['color_hexes'][$i];
                $cQty = (int)$_POST['color_qtys'][$i];
                $cImg = ''; 

                // آپلود عکس مخصوص همون رنگ
                if (isset($_FILES['color_images']['error'][$i]) && $_FILES['color_images']['error'][$i] == 0) {
                    $cImg = uploadImage($_FILES['color_images']['tmp_name'][$i], $_FILES['color_images']['name'][$i]);
                }

                $colors[$cName] =['hex' => $cHex, 'qty' => $cQty, 'img' => $cImg];
                $total_inventory += $cQty;
            }
        }
        $colors_json = json_encode($colors, JSON_UNESCAPED_UNICODE);

        // ذخیره در دیتابیس
        $stmt = $pdo->prepare("INSERT INTO products (name, brand, price, old_price, inventory, colors_inventory, image, engine_sound, hp, accel, engine, in_slider) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $_POST['name'], $_POST['brand'], $_POST['price'], 
            !empty($_POST['old_price']) ? $_POST['old_price'] : null, 
            $total_inventory, $colors_json, $mainImagePath, $soundPath, 
            $_POST['hp'], $_POST['accel'], $_POST['engine'], 
            isset($_POST['in_slider']) ? 1 : 0
        ]);
        
        echo json_encode(['success' => true, 'message' => 'محصول با موفقیت اضافه شد.']);
    }

    // ====================================================
    // 4. ویرایش محصول (با سیستم زباله‌روب کامل)
    // ====================================================
    elseif ($action == 'edit_product') {
        $id = $_POST['product_id'];
        
        // --- 1. پردازش رنگ‌ها و زباله‌روب عکسِ رنگ‌ها ---
        $stmt = $pdo->prepare("SELECT colors_inventory FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $oldColors = json_decode($stmt->fetchColumn(), true) ?:[];
        $oldColorImages =[];
        foreach($oldColors as $c) {
            if(is_array($c) && !empty($c['img'])) $oldColorImages[] = $c['img'];
        }

        $colors = [];
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

                // اگر برای این رنگ عکس جدید داده شد، عکس قبلی رو پاک کن
                if (isset($_FILES['color_images']['error'][$i]) && $_FILES['color_images']['error'][$i] == 0) {
                    if ($existingImg && file_exists($existingImg)) unlink($existingImg);
                    $finalImg = uploadImage($_FILES['color_images']['tmp_name'][$i], $_FILES['color_images']['name'][$i]);
                }

                if($finalImg) $newColorImages[] = $finalImg;
                $colors[$cName] =['hex' => $cHex, 'qty' => $cQty, 'img' => $finalImg];
                $total_inventory += $cQty;
            }
        }
        
        // پاک کردن عکس رنگ‌هایی که کلاً از لیست ادمین حذف شدن
        $orphanedImages = array_diff($oldColorImages, $newColorImages);
        foreach($orphanedImages as $orphan) if(file_exists($orphan)) unlink($orphan);

        $colors_json = json_encode($colors, JSON_UNESCAPED_UNICODE);
        
        // --- 2. آماده‌سازی کوئری آپدیت پایه ---
        $sql = "UPDATE products SET name=?, brand=?, price=?, old_price=?, inventory=?, colors_inventory=?, hp=?, accel=?, engine=?, in_slider=? ";
        $params =[
            $_POST['name'], $_POST['brand'], $_POST['price'], 
            !empty($_POST['old_price']) ? $_POST['old_price'] : null, 
            $total_inventory, $colors_json, $_POST['hp'], $_POST['accel'], $_POST['engine'], 
            isset($_POST['in_slider']) ? 1 : 0
        ];

        // --- 3. بررسی آپدیت عکس اصلی ---
        if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == 0) {
            $stmt = $pdo->prepare("SELECT image FROM products WHERE id = ?");
            $stmt->execute([$id]);
            $oldMainImg = $stmt->fetchColumn();
            if($oldMainImg && file_exists($oldMainImg)) unlink($oldMainImg); // پاک کردن عکس قبلی

            $mainImagePath = uploadImage($_FILES['product_image']['tmp_name'], $_FILES['product_image']['name']);
            $sql .= ", image=? ";
            $params[] = $mainImagePath;
        }

        // --- 4. بررسی آپدیت صدای موتور ---
        if (isset($_FILES['engine_sound']) && $_FILES['engine_sound']['error'] == 0) {
            $stmt = $pdo->prepare("SELECT engine_sound FROM products WHERE id = ?");
            $stmt->execute([$id]);
            $oldSound = $stmt->fetchColumn();
            if($oldSound && file_exists($oldSound)) unlink($oldSound); // پاک کردن صدای قبلی

            $soundPath = uploadAudio($_FILES['engine_sound']['tmp_name'], $_FILES['engine_sound']['name']);
            $sql .= ", engine_sound=? ";
            $params[] = $soundPath;
        }

        // اجرای کوئری آپدیت
        $sql .= " WHERE id=?";
        $params[] = $id;
        
        $pdo->prepare($sql)->execute($params);
        echo json_encode(['success' => true, 'message' => 'محصول با موفقیت ویرایش شد.']);
    }

    // ====================================================
    // 5. حذف کلی محصول و تمام فایل‌های وابسته (عکس‌ها و صدا)
    // ====================================================
    elseif ($action == 'delete_product') {
        $id = $_POST['product_id'];
        
        $stmt = $pdo->prepare("SELECT image, engine_sound, colors_inventory FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $prod = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($prod) {
            // پاک کردن عکس اصلی
            if($prod['image'] && file_exists($prod['image'])) unlink($prod['image']);
            
            // پاک کردن صدای موتور
            if($prod['engine_sound'] && file_exists($prod['engine_sound'])) unlink($prod['engine_sound']);
            
            // پاک کردن عکس تک تک رنگ‌ها
            if($prod['colors_inventory']) {
                $cols = json_decode($prod['colors_inventory'], true);
                if(is_array($cols)) {
                    foreach($cols as $c) {
                        if(is_array($c) && !empty($c['img']) && file_exists($c['img'])) {
                            unlink($c['img']);
                        }
                    }
                }
            }
        }

        // حذف رکورد از دیتابیس
        $pdo->prepare("DELETE FROM products WHERE id = ?")->execute([$id]);
        echo json_encode(['success' => true]);
    }

    else {
        throw new Exception('عملیات نامعتبر است.');
    }

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>