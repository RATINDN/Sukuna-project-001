<?php
session_start();
require_once 'db_connect.php';
header('Content-Type: application/json; charset=utf-8');

// فقط ادمین حق دیدن این آمار را دارد
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] != 1) {
    http_response_code(403);
    exit(json_encode(['error' => 'Access Denied']));
}

try {
    // 1. گرفتن آمار داشبورد
    $stmtStats = $pdo->query("SELECT status, COUNT(*) as cnt FROM email_queue GROUP BY status");
    $stats = ['pending' => 0, 'sent' => 0, 'failed' => 0, 'processing' => 0];
    while($r = $stmtStats->fetch(PDO::FETCH_ASSOC)) { $stats[$r['status']] = $r['cnt']; }
    
    // 2. گرفتن وضعیت 300 ایمیل آخر برای آپدیت جدول
    $stmtEmails = $pdo->query("SELECT id, status, sent_at FROM email_queue ORDER BY id DESC LIMIT 300");
    $emails = $stmtEmails->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'stats' => $stats, 'emails' => $emails]);
} catch (PDOException $e) {
    echo json_encode(['success' => false]);
}
?>