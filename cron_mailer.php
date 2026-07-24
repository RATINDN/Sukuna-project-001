<?php


// رمز عبور مخفی (یک عبارت سخت و پیچیده که فقط خودتان می‌دانید)
$secret_token = "LUXURY_CAR_CRON_SECRET_2026_XYZ";

// بررسی می‌کنیم که آیا رمز در لینک ارسال شده یا خیر؟
if (!isset($_GET['token']) || $_GET['token'] !== $secret_token) {
    http_response_code(403);
    die("⛔ Access Denied! You are not allowed to run this bot.");
}

require_once 'db_connect.php';
// ... بقیه کدهای فایل سر جایش می‌ماند ...

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'PHPMailer-master/src/Exception.php';
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';

// برای جلوگیری از اینکه دو تا ربات همزمان یک ایمیل را بفرستند، اول وضعیت ۵۰ تای اول را processing می‌کنیم
$pdo->beginTransaction();
$stmtFetch = $pdo->prepare("SELECT id, recipient_email, recipient_name, subject, body FROM email_queue WHERE status = 'pending' LIMIT 50 FOR UPDATE");
$stmtFetch->execute();
$emails = $stmtFetch->fetchAll(PDO::FETCH_ASSOC);

if (count($emails) > 0) {
    // آیدی ایمیل‌هایی که گرفتیم رو استخراج میکنیم
    $emailIds = array_column($emails, 'id');
    $placeholders = implode(',', array_fill(0, count($emailIds), '?'));
    
    // وضعیتشون رو به processing تغییر میدیم
    $stmtUpdate = $pdo->prepare("UPDATE email_queue SET status = 'processing' WHERE id IN ($placeholders)");
    $stmtUpdate->execute($emailIds);
}
$pdo->commit();

// اگر ایمیلی در صف نبود، خارج شو
if (count($emails) === 0) {
    die("No pending emails found.");
}

// حالا با خیال راحت شروع به ارسال می‌کنیم
$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com'; 
    $mail->SMTPAuth = true;
    $mail->Username = 'dnratin@gmail.com'; // ایمیل شما
    $mail->Password = 'yydd utlt bqkv eaps'; // اپ پسورد شما
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port = 465;
    $mail->CharSet = 'UTF-8';
    $mail->setFrom('dnratin@gmail.com', 'فروشگاه خودروهای لوکس');
    $mail->isHTML(true);

    foreach ($emails as $email) {
        try {
            $mail->clearAllRecipients();
            $mail->addAddress($email['recipient_email'], $email['recipient_name']);
            $mail->Subject = $email['subject'];
            $mail->Body = $email['body'];
            $mail->send();

            // اگر با موفقیت ارسال شد، وضعیت را به sent تغییر بده
            $pdo->prepare("UPDATE email_queue SET status = 'sent', sent_at = CURRENT_TIMESTAMP WHERE id = ?")->execute([$email['id']]);
        } catch (Exception $e) {
            // اگر این یک دونه ایمیل خطا خورد، وضعیتش رو failed کن که بعدا بررسی کنیم
            $pdo->prepare("UPDATE email_queue SET status = 'failed' WHERE id = ?")->execute([$email['id']]);
        }
    }
    echo "Successfully processed " . count($emails) . " emails.";
} catch (Exception $e) {
    echo "SMTP Configuration Error: " . $mail->ErrorInfo;
}

// =========================================================
// پاکسازی خودکار دیتابیس (حذف رکوردهای قدیمی برای افزایش سرعت)
// =========================================================

// چون این فایل هر 1 دقیقه اجرا می‌شود، نیازی نیست هر دقیقه دیتابیس را پاکسازی کنیم.
// با این دستور می‌گوییم: یک عدد تصادفی بین 1 تا 60 انتخاب کن.
// اگر عدد 1 درآمد (یعنی تقریباً ساعتی یک‌بار یا روزی چند بار)، آن وقت تمیزکاری را انجام بده!

if (mt_rand(1, 60) === 1) {
  // 1. پاک کردن ایمیل‌های "ارسال شده" که مال بیشتر از 30 روز پیش هستند
  $pdo->exec("DELETE FROM email_queue WHERE status = 'sent' AND sent_at < NOW() - INTERVAL 30 DAY");

  // 2. پاک کردن نوتیفیکیشن‌های قدیمی
  $pdo->exec("DELETE FROM notifications WHERE created_at < NOW() - INTERVAL 60 DAY");
  
  // برای اینکه خودمان در لوکال تست متوجه شویم کی تمیزکاری کرده:
  // echo " تمیزکاری دیتابیس هم انجام شد! ";
}
?>