<?php
session_start();
require_once 'db_connect.php';

header('Content-Type: application/json');

// بررسی ادمین بودن
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] != 1) {
    echo json_encode(['success' => false, 'error' => 'دسترسی غیرمجاز']);
    exit();
}

if (!isset($_POST['action'])) {
    echo json_encode(['success' => false, 'error' => 'پارامترهای ناقص']);
    exit();
}

$action = $_POST['action'];

// عملیات مربوط به کاربران
if (in_array($action, ['delete', 'promote', 'demote'])) {
    $userId = (int)$_POST['user_id'];
    
    // جلوگیری از حذف خود ادمین
    if ($userId == $_SESSION['user_id']) {
        echo json_encode(['success' => false, 'error' => 'عملیات روی حساب خودتان مجاز نیست']);
        exit();
    }

    try {
        if ($action == 'delete') {
            $stmt = $pdo->prepare("DELETE FROM car WHERE id = ?");
            $stmt->execute([$userId]);
        } elseif ($action == 'promote') {
            $stmt = $pdo->prepare("UPDATE car SET role = 1 WHERE id = ?");
            $stmt->execute([$userId]);
        } elseif ($action == 'demote') {
            $stmt = $pdo->prepare("UPDATE car SET role = 0 WHERE id = ?");
            $stmt->execute([$userId]);
        }
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'خطای دیتابیس']);
    }
}

// ============================================
// عملیات جدید: تغییر وضعیت قرارداد
// ============================================
elseif ($action == 'update_contract_status') {
    $contractId = (int)$_POST['contract_id'];
    $newStatus = $_POST['new_status'];

    // لیست وضعیت‌های مجاز
    $allowedStatuses = ['pending', 'paid', 'rejected'];

    if (!in_array($newStatus, $allowedStatuses)) {
        echo json_encode(['success' => false, 'error' => 'وضعیت نامعتبر است']);
        exit();
    }

    try {
        $stmt = $pdo->prepare("UPDATE contracts SET status = ? WHERE id = ?");
        $stmt->execute([$newStatus, $contractId]);
        
        echo json_encode(['success' => true, 'message' => 'وضعیت قرارداد تغییر کرد']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'خطای دیتابیس: ' . $e->getMessage()]);
    }
} 
else {
    echo json_encode(['success' => false, 'error' => 'عملیات نامعتبر']);
}
?>