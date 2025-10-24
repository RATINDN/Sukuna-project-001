<?php
session_start();
require_once 'db_connect.php';

// Set headers for JSON response
header('Content-Type: application/json');

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] != 1) {
    echo json_encode(['success' => false, 'error' => 'دسترسی غیرمجاز']);
    exit();
}

// Check if action and user_id are provided
if (!isset($_POST['action']) || !isset($_POST['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'پارامترهای ناقص']);
    exit();
}

$action = $_POST['action'];
$userId = (int)$_POST['user_id'];

// Validate user_id
if ($userId <= 0) {
    echo json_encode(['success' => false, 'error' => 'شناسه کاربر نامعتبر است']);
    exit();
}

// Get user information
try {
    $stmt = $pdo->prepare("SELECT id, role FROM car WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        echo json_encode(['success' => false, 'error' => 'کاربر یافت نشد']);
        exit();
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'خطای پایگاه داده: ' . $e->getMessage()]);
    exit();
}

// Process the action
switch ($action) {
    case 'delete':
        // Check if trying to delete an admin
        if ($user['role'] == 1) {
            echo json_encode(['success' => false, 'error' => 'حذف حساب مدیر مجاز نیست']);
            exit();
        }
        
        // Check if trying to delete self
        if ($user['id'] == $_SESSION['user_id']) {
            echo json_encode(['success' => false, 'error' => 'حذف حساب خود مجاز نیست']);
            exit();
        }
        
        // Delete the user
        try {
            $stmt = $pdo->prepare("DELETE FROM car WHERE id = ?");
            $stmt->execute([$userId]);
            
            if ($stmt->rowCount() > 0) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => 'حذف کاربر ناموفق بود']);
            }
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'error' => 'خطای پایگاه داده: ' . $e->getMessage()]);
        }
        break;
        
    case 'promote':
        // Check if user is already an admin
        if ($user['role'] == 1) {
            echo json_encode(['success' => false, 'error' => 'کاربر در حال حاضر مدیر است']);
            exit();
        }
        
        // Promote user to admin
        try {
            $stmt = $pdo->prepare("UPDATE car SET role = 1 WHERE id = ?");
            $stmt->execute([$userId]);
            
            if ($stmt->rowCount() > 0) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => 'ارتقای کاربر ناموفق بود']);
            }
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'error' => 'خطای پایگاه داده: ' . $e->getMessage()]);
        }
        break;
        
    case 'demote':
        // Check if user is not an admin
        if ($user['role'] != 1) {
            echo json_encode(['success' => false, 'error' => 'کاربر در حال حاضر مدیر نیست']);
            exit();
        }
        
        // Check if trying to demote self
        if ($user['id'] == $_SESSION['user_id']) {
            echo json_encode(['success' => false, 'error' => 'تنزل حساب خود مجاز نیست']);
            exit();
        }
        
        // Demote admin to regular user
        try {
            $stmt = $pdo->prepare("UPDATE car SET role = 0 WHERE id = ?");
            $stmt->execute([$userId]);
            
            if ($stmt->rowCount() > 0) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => 'تنزل کاربر ناموفق بود']);
            }
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'error' => 'خطای پایگاه داده: ' . $e->getMessage()]);
        }
        break;
        
    default:
        echo json_encode(['success' => false, 'error' => 'عملیات نامعتبر']);
        break;
}
?>