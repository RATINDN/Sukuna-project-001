<?php
session_start();
require_once 'db_connect.php';
require_once 'PHPMailer-master/src/PHPMailer.php';
require_once 'PHPMailer-master/src/SMTP.php';
require_once 'PHPMailer-master/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$errors = [];
$email_to_verify = $_SESSION['pending_verification_email'] ?? null;
$verification_type = $_GET['type'] ?? 'login'; // 'login' or 'signup'

// Debug session variables
error_log("Session variables on verify.php load:");
error_log("pending_verification_email: " . ($email_to_verify ? $email_to_verify : 'not set'));
error_log("verification_type: " . $verification_type);
error_log("verification_code: " . (isset($_SESSION['verification_code']) ? $_SESSION['verification_code'] : 'not set'));
error_log("verification_code_expires_at: " . (isset($_SESSION['verification_code_expires_at']) ? $_SESSION['verification_code_expires_at'] : 'not set'));

if (!$email_to_verify) {
    header("Location: signup.php");
    exit();
}

// Check if verification code has been sent
// Check for send parameter in URL to force sending a new code
$send_new_code = isset($_GET['send']) && $_GET['send'] === '1';

// If we have the send parameter, store it in session and redirect to remove it from URL
// This prevents generating new codes on page refresh
if ($send_new_code) {
    $_SESSION['should_send_code'] = true;
    // Redirect to the same page without the send parameter
    header("Location: verify.php?type=" . $verification_type);
    exit();
}

// Check if we should send a code (either no code exists or session flag is set)
if (!isset($_SESSION['verification_code']) || (isset($_SESSION['should_send_code']) && $_SESSION['should_send_code'])) {
    // Clear the flag
    if (isset($_SESSION['should_send_code'])) {
        unset($_SESSION['should_send_code']);
    }
    
    // If we already have a code but are forcing a new one, clear the old one
    if (isset($_SESSION['verification_code'])) {
        error_log("Generating new verification code and replacing existing one");
        unset($_SESSION['verification_code']);
        unset($_SESSION['verification_code_expires_at']);
    }
    
    // For signup, the verification code is already stored in the database and session
    // For login, we need to generate a new code
    if ($verification_type === 'login') {
        // Generate 6-digit verification code
        $verification_code = sprintf('%06d', mt_rand(0, 999999));
        $_SESSION['verification_code'] = $verification_code;
        $_SESSION['verification_code_expires_at'] = time() + (15 * 60); // Code expires in 15 minutes
    } else if ($verification_type === 'signup' && !isset($_SESSION['verification_code'])) {
        // For signup, if code is not in session, retrieve it from database
        try {
            $stmt = $pdo->prepare("SELECT active FROM car WHERE email = ?");
            $stmt->execute([$email_to_verify]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result && $result['active']) {
                $verification_code = $result['active'];
                $_SESSION['verification_code'] = $verification_code;
                $_SESSION['verification_code_expires_at'] = time() + (15 * 60); // Reset expiration time
            } else {
                // If not found, generate a new code and update the database
                $verification_code = sprintf('%06d', mt_rand(0, 999999));
                $_SESSION['verification_code'] = $verification_code;
                $_SESSION['verification_code_expires_at'] = time() + (15 * 60);
                
                // Update the verification code in the database
                $updateStmt = $pdo->prepare("UPDATE car SET active = ? WHERE email = ?");
                $updateStmt->execute([$verification_code, $email_to_verify]);
            }
        } catch (PDOException $e) {
            error_log("Error retrieving verification code from database: " . $e->getMessage());
            // Generate a new code as fallback
            $verification_code = sprintf('%06d', mt_rand(0, 999999));
            $_SESSION['verification_code'] = $verification_code;
            $_SESSION['verification_code_expires_at'] = time() + (15 * 60);
        }
    } else {
        // Use existing code from session
        $verification_code = $_SESSION['verification_code'];
    }
    
    // Get username based on verification type
    $username = '';
    if ($verification_type === 'signup' && isset($_SESSION['pending_signup_data'])) {
        $username = $_SESSION['pending_signup_data']['fullname'];
    } elseif ($verification_type === 'login' && isset($_SESSION['user_data'])) {
        $username = $_SESSION['user_data']['user_name'];
    }
    
    // Send verification email
    try {
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
        $mail->addAddress($email_to_verify, $username);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = $verification_type === 'login' ? 'کد تایید ورود به حساب کاربری' : 'کد تایید حساب کاربری شما';
        
        // Email body with verification code
        $mail->Body = '
        <div style="font-family: \'Vazirmatn\', Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; border-radius: 5px; direction: rtl; text-align: right;">
            <h2 style="color: #333; text-align: center;">' . ($verification_type === 'login' ? 'کد تایید ورود' : 'کد تایید ثبت نام') . '</h2>
            <p style="font-size: 16px; line-height: 1.5; color: #666;">درود ' . htmlspecialchars($username) . '،</p>
            <p style="font-size: 16px; line-height: 1.5; color: #666;">با تشکر از شما برای استفاده از وبسایت خودروهای لوکس. لطفاً از کد زیر برای ' . ($verification_type === 'login' ? 'ورود به' : 'تایید') . ' حساب کاربری خود استفاده کنید:</p>
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
        $mail->AltBody = "درود " . $username . "\nکد تایید شما: " . $verification_code . "\nاین کد تا 15 دقیقه دیگر معتبر است.";
        
        // Send email
        $mail->send();
        $_SESSION['verification_sent'] = true;
        $_SESSION['verification_sent_time'] = time(); // Record when the code was sent
        
        // Add debug log
        error_log("Initial verification email sent successfully to: " . $email_to_verify);
        
        // Set a session variable to show a success message on the page
        $_SESSION['email_just_sent'] = true;
    } catch (Exception $e) {
        $errors['email_send'] = "خطا در ارسال ایمیل تایید: " . $mail->ErrorInfo;
        // Add detailed error logging
        error_log("Error sending initial verification email: " . $e->getMessage());
        error_log("PHPMailer Error: " . $mail->ErrorInfo);
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['verification_code'])) {
    $entered_code = trim($_POST['verification_code']);

    if (empty($entered_code)) {
        $errors['code'] = "کد تایید را وارد کنید.";
    } elseif (!isset($_SESSION['verification_code']) || $entered_code != $_SESSION['verification_code']) {
        $errors['code'] = "کد تایید نامعتبر است.";
    } elseif (time() > $_SESSION['verification_code_expires_at']) {
        $errors['code'] = "کد تایید منقضی شده است. لطفاً کد جدید درخواست کنید.";
    } else {
        // Code is valid and not expired
        if ($verification_type === 'signup' && isset($_SESSION['pending_signup_data'])) {
            $signup_data = $_SESSION['pending_signup_data'];
            try {
                // Begin transaction for data consistency
                $pdo->beginTransaction();
                
                // Update existing user: set status to 1 (verified) and clear the verification code
                $stmt = $pdo->prepare("UPDATE car SET status = 1, active = NULL WHERE email = ? AND active = ?");
                $stmt->execute([
                    $signup_data['email'],
                    $_SESSION['verification_code']
                ]);
                
                // Check if the update was successful
                if ($stmt->rowCount() === 0) {
                    throw new Exception("Failed to update user status. Verification code may be incorrect.");
                }
                
                // Commit the transaction
                $pdo->commit();
                
                // Log successful verification
                error_log("User {$signup_data['email']} successfully verified and status set to 1");
                $user_id = $signup_data['user_id'];

                // Get the user's role from the database
                $roleStmt = $pdo->prepare("SELECT role FROM car WHERE id = ?");
                $roleStmt->execute([$user_id]);
                $roleResult = $roleStmt->fetch(PDO::FETCH_ASSOC);
                $role = $roleResult ? $roleResult['role'] : 0;

                // Set all necessary session variables for the new user
                $_SESSION['user_id'] = $user_id;
                $_SESSION['user_name'] = $signup_data['fullname'];
                $_SESSION['avatar_color'] = $signup_data['avatar_color'];
                $_SESSION['status'] = 1; // Store verified status in session
                $_SESSION['role'] = $role; // Store role in session
                
                // Log session data for debugging
                error_log("New user session data set: " . print_r($_SESSION, true));

                unset($_SESSION['pending_signup_data']);
                unset($_SESSION['verification_code']);
                unset($_SESSION['pending_verification_email']);
                unset($_SESSION['verification_code_expires_at']);
                unset($_SESSION['verification_sent']);
                
                // Ensure session data is written to storage
                session_write_close();
                session_start();
                
                // Double-check that session data is set
                if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_name'])) {
                    error_log("WARNING: Session data not properly set after registration. Attempting to fix.");
                    $_SESSION['user_id'] = $user_id;
                    $_SESSION['user_name'] = $signup_data['fullname'];
                    $_SESSION['avatar_color'] = $signup_data['avatar_color'];
                    $_SESSION['status'] = 1;
                    session_write_close();
                    session_start();
                }
                
                echo "<script>
                // Store data in sessionStorage for client-side access
                sessionStorage.setItem('signupSuccess', '1');
                sessionStorage.setItem('justVerified', '1');
                sessionStorage.setItem('openProfileOnLoad', '1');
                sessionStorage.setItem('userName', " . json_encode($signup_data['fullname']) . ");
                sessionStorage.setItem('avatarColor', " . json_encode($signup_data['avatar_color']) . ");
                
                console.log('Registration complete, redirecting to index.php');
                console.log('Session data set: user_id=" . $_SESSION['user_id'] . ", user_name=" . $_SESSION['user_name'] . "');
                
                // Add a longer delay to ensure session variables are fully set before redirect
                setTimeout(function() {
                    window.location.href='index.php?new_registration=1';
                }, 1500);
            </script>";
                exit();

            } catch (PDOException $e) {
                // Roll back the transaction on error
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                $errors['database'] = "خطای پایگاه داده: " . $e->getMessage();
                error_log("Database error during verification: " . $e->getMessage());
            }
        } elseif ($verification_type === 'login' && isset($_SESSION['pending_verification_email'])) {
             try {
                $stmt = $pdo->prepare("SELECT id, user_name, avatar_color, role, status FROM car WHERE email = ?");
                $stmt->execute([$_SESSION['pending_verification_email']]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($user) {
                    // Set all necessary session variables for the logged-in user
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['user_name'];
                    $_SESSION['avatar_color'] = $user['avatar_color'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['status'] = $user['status'];
                    
                    // Log session data for debugging
                    error_log("Login user session data set: " . print_r($_SESSION, true));

                    unset($_SESSION['pending_verification_email']);
                    unset($_SESSION['verification_code']);
                    unset($_SESSION['pending_verification_email']);
                    unset($_SESSION['verification_code_expires_at']);
                    unset($_SESSION['verification_sent']);

                    echo "<script>
                        // Store data in sessionStorage for client-side access
                        sessionStorage.setItem('loginSuccess', '1');
                        sessionStorage.setItem('justVerified', '1');
                        sessionStorage.setItem('userName', " . json_encode($user['user_name']) . ");
                        sessionStorage.setItem('avatarColor', " . json_encode($user['avatar_color']) . ");
                        
                        console.log('Login complete, redirecting to index.php');
                        
                        // Add a longer delay to ensure session variables are fully set before redirect
                        setTimeout(function() {
                            window.location.href='index.php';
                        }, 800);
                    </script>";
                    exit();
                } else {
                    $errors['user_not_found'] = "کاربری با این ایمیل یافت نشد.";
                }
            } catch (PDOException $e) {
                $errors['database'] = "خطای پایگاه داده: " . $e->getMessage();
            }
        } else {
            // Should not happen if session is managed correctly
            $errors['state'] = "خطای وضعیت. لطفاً دوباره تلاش کنید.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>تایید کد</title>
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/verify.css">
  <link rel="stylesheet" href="css/loginstyle.css">
  <link rel="stylesheet" href="css/signup.css">
  <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet">
</head>

<body>
  <div class="auth-container">
    <div class="auth-wrapper">
      <div class="mon" id="model">
        <svg onclick="darkmode()" xmlns="http://www.w3.org/2000/svg" width="35" height="35" fill="currentColor"
          class="moon2" id="moon2" viewBox="0 0 16 16">
          <path
            d="M6 .278a.77.77 0 0 1 .08.858 7.2 7.2 0 0 0-.878 3.46c0 4.021 3.278 7.277 7.318 7.277q.792-.001 1.533-.16a.79.79 0 0 1 .81.316.73.73 0 0 1-.031.893A8.35 8.35 0 0 1 8.344 16C3.734 16 0 12.286 0 7.71 0 4.266 2.114 1.312 5.124.06A.75.75 0 0 1 6 .278" />
        </svg>
        <svg onclick="darkmode()" xmlns="http://www.w3.org/2000/svg" width="35" height="35" id="sun2"
          fill="currentColor" class="sun2" style="display: none;" viewBox="0 0 16 16">
          <path
            d="M12 8a4 4 0 1 1-8 0 4 4 0 0 1 8 0M8 0a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 0m0 13a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 13m8-5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2a.5.5 0 0 1 .5.5M3 8a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2A.5.5 0 0 1 3 8m10.657-5.657a.5.5 0 0 1 0 .707l-1.414 1.415a.5.5 0 1 1-.707-.708l1.414-1.414a.5.5 0 0 1 .707 0m-9.193 9.193a.5.5 0 0 1 0 .707L3.05 13.657a.5.5 0 0 1-.707-.707l1.414-1.414a.5.5 0 0 1 .707 0m9.193 2.121a.5.5 0 0 1-.707 0l-1.414-1.414a.5.5 0 0 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .707M4.464 4.465a.5.5 0 0 1-.707 0L2.343 3.05a.5.5 0 1 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .708" />
        </svg>
      </div>
      <div class="auth-header">
        <h1 class="auth-title">تایید ایمیل</h1>
        <p class="auth-subtitle">یک کد 6 رقمی به ایمیل <?php echo htmlspecialchars($email_to_verify); ?> ارسال شده است.
          لطفاً آن را وارد کنید.</p>

        <?php if(isset($_SESSION['email_just_sent'])): ?>
        <?php unset($_SESSION['email_just_sent']); // Clear the message without showing it ?>
        <?php endif; ?>

        <?php if(isset($errors['email_send'])): ?>
        <div class="error-message"><?php echo htmlspecialchars($errors['email_send']); ?></div>
        <?php endif; ?>
      </div>

      <form id="verification-form" class="auth-form" method="post"
        action="verify.php?type=<?php echo htmlspecialchars($verification_type); ?>">
        <div class="input-group">
          <label for="verification_code">کد تایید</label>
          <input type="text" id="verification_code" name="verification_code" placeholder="------" maxlength="6"
            pattern="\d{6}" inputmode="numeric" required>
          <div class="error-container">
            <?php if(isset($errors['code'])) echo htmlspecialchars($errors['code']); ?>
            <?php if(isset($errors['database'])) echo htmlspecialchars($errors['database']); ?>
            <?php if(isset($errors['user_not_found'])) echo htmlspecialchars($errors['user_not_found']); ?>
            <?php if(isset($errors['state'])) echo htmlspecialchars($errors['state']); ?>
          </div>
        </div>
        <button type="submit" class="auth-btn">تایید کد</button>
      </form>

      <div class="form-switch" style="margin-top: 1rem;">
        <p id="resend-timer-message">ارسال مجدد کد تا <span id="countdown">30</span> ثانیه دیگر.</p>
        <button id="resend-code-btn" class="switch-link" disabled>ارسال مجدد کد</button>
      </div>
      <div class="form-switch" style="margin-top: 0.5rem;">
        <a href="login.php" class="switch-link">بازگشت به صفحه ورود</a>
      </div>
    </div>
  </div>
 
  <script src="js/verify.js"></script>
  <script src="js/login signup.js"></script>
  <script src="js/settings.js"></script>
</body>

</html>
