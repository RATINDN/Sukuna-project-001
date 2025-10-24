<?php
/**
 * Resend verification code
 * Handles AJAX requests to resend verification codes
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Debug: Log all session variables
error_log("Session variables in resend_code.php:");
foreach ($_SESSION as $key => $value) {
    if (is_array($value)) {
        error_log("$key: " . json_encode($value));
    } else {
        error_log("$key: $value");
    }
}

// Include database connection
require_once 'db_connect.php';

// Include PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'PHPMailer-master/src/Exception.php';
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';

// Set content type to JSON
header('Content-Type: application/json');

// Check if this is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'روش درخواست نامعتبر است']);
    exit;
}

// Check if action is resend or test
if (!isset($_POST['action'])) {
    echo json_encode(['success' => false, 'message' => 'عملیات نامعتبر است']);
    exit;
}

// Handle test action
if ($_POST['action'] === 'test') {
    error_log("Test action received in resend_code.php");
    echo json_encode([
        'success' => true,
        'message' => 'Test successful',
        'session_data' => [
            'has_pending_email' => isset($_SESSION['pending_verification_email']),
            'has_verification_code' => isset($_SESSION['verification_code']),
            'timestamp' => time()
        ]
    ]);
    exit;
}

// If not a test, must be a resend action
if ($_POST['action'] !== 'resend') {
    echo json_encode(['success' => false, 'message' => 'عملیات نامعتبر است']);
    exit;
}

// Check if there's a pending verification in session
if (!isset($_SESSION['pending_verification_email'])) {
    error_log("Error: pending_verification_email not set in session");
    echo json_encode(['success' => false, 'message' => 'هیچ تاییدی در انتظار یافت نشد']);
    exit;
}

// Debug: Log the email we're working with
error_log("Resending verification code to: " . $_SESSION['pending_verification_email']);

// Get email from session
$email = $_SESSION['pending_verification_email'];

// Generate new 6-digit verification code
$verification_code = sprintf('%06d', mt_rand(0, 999999));

// Store new verification code in session
$_SESSION['verification_code'] = $verification_code;

// Set expiration time (15 minutes from now to match auth.php)
$_SESSION['verification_code_expires_at'] = time() + (15 * 60);

// If this is a signup verification, update the verification code in the database
$verification_type = $_SESSION['pending_verification_type'] ?? 'login';
if ($verification_type === 'signup') {
    try {
        // Update the verification code in the database
        $stmt = $pdo->prepare("UPDATE car SET active = ? WHERE email = ?");
        $stmt->execute([$verification_code, $email]);
        
        if ($stmt->rowCount() === 0) {
            error_log("Warning: Failed to update verification code in database for email: $email");
        } else {
            error_log("Successfully updated verification code in database for email: $email");
        }
    } catch (PDOException $e) {
        error_log("Error updating verification code in database: " . $e->getMessage());
        // Continue anyway since we have the code in session
    }
}

// Get username from database or session
$username = '';
if (isset($_SESSION['pending_signup_data']) && isset($_SESSION['pending_signup_data']['fullname'])) {
    // For signup, get username from pending signup data
    $username = $_SESSION['pending_signup_data']['fullname'];
} else {
    // For login, get username from database
    try {
        $stmt = $pdo->prepare("SELECT user_name FROM car WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            $username = $user['user_name'];
        }
    } catch (PDOException $e) {
        error_log("Error fetching username: " . $e->getMessage());
    }
}

// Send verification email
try {
    // Create PHPMailer instance
    $mail = new PHPMailer(true);
    
    // Server settings
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'dnratin@gmail.com';
    $mail->Password = 'umyk yewt awet nyri'; // App Password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port = 465;
    $mail->CharSet = 'UTF-8';
    
    // Recipients
    $mail->setFrom('dnratin@gmail.com', 'Luxury Cars Verification');
    $mail->addAddress($email);
    
    // Content
    $mail->isHTML(true);
    $mail->Subject = 'کد تایید مجدد';
    
    // Email body with verification code
    $mail->Body = '
    <div style="font-family: \'Vazirmatn\', Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; border-radius: 5px; direction: rtl; text-align: right;">
        <h2 style="color: #333; text-align: center;">کد تایید</h2>
        <p style="font-size: 16px; line-height: 1.5; color: #666;">سلام ' . htmlspecialchars($username) . '،</p>
        <p style="font-size: 16px; line-height: 1.5; color: #666;">با تشکر از شما برای استفاده از وبسایت خودروهای لوکس. لطفاً از کد زیر برای تایید حساب کاربری خود استفاده کنید:</p>
        <div style="text-align: center; margin: 30px 0;">
            <div style="font-size: 32px; font-weight: bold; letter-spacing: 5px; color: #333; padding: 15px; background-color: #f7f7f7; border-radius: 5px; display: inline-block;">' . $verification_code . '</div>
        </div>
        <p style="font-size: 16px; line-height: 1.5; color: #666;">این کد تا 15 دقیقه دیگر معتبر است.</p>
        <p style="font-size: 16px; line-height: 1.5; color: #666;">اگر شما این کد را درخواست نکرده‌اید، لطفاً این ایمیل را نادیده بگیرید.</p>
        <div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; color: #999; font-size: 14px;">
            &copy; ' . date('Y') . ' خودروهای لوکس. تمامی حقوق محفوظ است.
        </div>
    </div>';
    
    // Plain text version
    $mail->AltBody = "سلام $username\nکد تایید شما: $verification_code\nاین کد تا 15 دقیقه دیگر معتبر است.";
    
    // Send email
    $mail->send();
    
    // Set session variable to show success message on verify.php
    $_SESSION['email_just_sent'] = true;
    
    // Return success response with additional debug info
    $response = [
        'success' => true,
        'message' => 'کد تایید با موفقیت ارسال شد',
        'code' => $verification_code, // Include the code in the response for debugging
        'debug' => [
            'email' => $email,
            'username' => $username,
            'time' => date('Y-m-d H:i:s')
        ]
    ];
    error_log("Success response: " . json_encode($response));
    echo json_encode($response);
    
    // Log successful resend
    error_log("Verification code resent successfully to: $email");
    
} catch (Exception $e) {
    // Log error
    error_log("Error sending verification email: " . $mail->ErrorInfo);
    
    // Return error response with more details
    $errorResponse = [
        'success' => false,
        'message' => 'خطا در ارسال کد تایید. لطفاً دوباره تلاش کنید.',
        'error_details' => $mail->ErrorInfo
    ];
    error_log("Error response: " . json_encode($errorResponse));
    echo json_encode($errorResponse);
}