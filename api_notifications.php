<?php
// api_notifications.php
session_start();
require_once 'db_connect.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];

try {
    // اگر درخواست GET بود: دریافت نوتیفیکیشن‌ها و تعداد نخوانده‌ها
    if ($method === 'GET') {
        // تعداد نخوانده‌ها
        $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
        $stmtCount->execute([$user_id]);
        $unread_count = $stmtCount->fetchColumn();

        // لیست 10 پیام آخر
        $stmtMsgs = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 10");
        $stmtMsgs->execute([$user_id]);
        $notifications = $stmtMsgs->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true, 
            'unread_count' => $unread_count, 
            'notifications' => $notifications
        ]);
    } 
    // اگر درخواست POST بود (خواندن پیام‌ها یا حذف آن‌ها)
    elseif ($method === 'POST') {
      // اگر درخواست برای حذف کل پیام‌ها بود
      if (isset($_POST['action']) && $_POST['action'] === 'delete_all') {
        $delAllStmt = $pdo->prepare("DELETE FROM notifications WHERE user_id = ?");
        $delAllStmt->execute([$user_id]);
        echo json_encode(['success' => true]);
        exit();
    }
      // اگر درخواست برای حذف یک پیام خاص بود
      if (isset($_POST['action']) && $_POST['action'] === 'delete') {
          $notif_id = (int)$_POST['notif_id'];
          $delStmt = $pdo->prepare("DELETE FROM notifications WHERE id = ? AND user_id = ?");
          $delStmt->execute([$notif_id, $user_id]);
          echo json_encode(['success' => true]);
          exit();
      }

      // اگر درخواست عادی بود (مارک کردن همه به عنوان خوانده شده)
      $updateStmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0");
      $updateStmt->execute([$user_id]);
      
      echo json_encode(['success' => true]);
  }
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
?>