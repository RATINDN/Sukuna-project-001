<?php
session_start();
require_once 'db_connect.php';
require_once 'PHPMailer-master/src/PHPMailer.php';
require_once 'PHPMailer-master/src/SMTP.php';
require_once 'PHPMailer-master/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// If user is already logged in, redirect to home page
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$errors = [];
$success = false;

// Define colors array once
$colors = [
  '#4CAF50', '#2196F3', '#9C27B0', '#FF5722', '#607D8B',
  '#E91E63', '#3F51B5', '#009688', '#FFC107', '#795548',
  '#F44336', '#00BCD4', '#8BC34A', '#CDDC39', '#FFEB3B',
  '#FF9800', '#FFB300', '#8D6E63', '#00E676', '#1DE9B6',
  '#00B8D4', '#2962FF', '#D500F9', '#C51162', '#FF1744',
  '#FF6F00', '#AEEA00', '#00C853', '#B2FF59', '#76FF03',
  '#64DD17', '#FFD600', '#FFAB00', '#FF6D00', '#A1887F',
  '#90A4AE', '#B0BEC5', '#263238', '#212121', '#757575',
  '#BDBDBD', '#E0E0E0', '#F5F5F5', '#FFFFFF', '#000000',
  '#F06292', '#BA68C8', '#9575CD', '#7986CB', '#64B5F6',
  '#4FC3F7', '#4DD0E1', '#4DB6AC', '#81C784', '#AED581',
  '#DCE775', '#FFF176', '#FFD54F', '#FFB74D', '#A1887F',
  '#E57373', '#F06292', '#BA68C8', '#9575CD', '#7986CB',
  '#64B5F6', '#4FC3F7', '#4DD0E1', '#4DB6AC', '#81C784',
  '#AED581', '#DCE775', '#FFF176', '#FFD54F', '#FFB74D',
  '#A1887F', '#90A4AE', '#B0BEC5', '#263238', '#212121',
  '#757575', '#BDBDBD', '#E0E0E0', '#F5F5F5', '#FFFFFF',
  '#000000', '#F44336', '#E91E63', '#9C27B0', '#673AB7',
  '#3F51B5', '#2196F3', '#03A9F4', '#00BCD4', '#009688',
  '#4CAF50', '#8BC34A', '#CDDC39', '#FFEB3B', '#FFC107',
  '#FF9800', '#FF5722', '#795548', '#607D8B', '#B71C1C',
  '#880E4F', '#4A148C', '#311B92', '#1A237E', '#0D47A1',
  '#01579B', '#006064', '#004D40', '#1B5E20', '#33691E',
  '#827717', '#F57F17', '#FF6F00', '#E65100', '#BF360C'
];

// Handle signup form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm-password']);

    // Add form_type hidden field to your form
    echo '<input type="hidden" name="form_type" value="signup">';

    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM car WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetchColumn() > 0) {
            $errors['email'] = "این ایمیل قبلاً ثبت شده است";
        }

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM car WHERE phone = ?");
        $stmt->execute([$phone]);
        if ($stmt->fetchColumn() > 0) {
            $errors['phone'] = "این شماره تلفن قبلاً ثبت شده است";
        }

        if (empty($errors)) {
            // Generate verification code and hash password
            $verification_code = sprintf('%06d', mt_rand(0, 999999));
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Select random avatar color
            $avatar_color = $colors[array_rand($colors)];
            
            try {
                $pdo->beginTransaction();
                
                // Insert new user
                $stmt = $pdo->prepare("INSERT INTO car (user_name, email, phone, password, avatar_color, status, active) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $fullname,
                    $email, 
                    $phone,
                    $hashed_password,
                    $avatar_color,
                    0, // unverified status
                    $verification_code
                ]);
                
                $user_id = $pdo->lastInsertId();
                $pdo->commit();

                // Store data in session
                $_SESSION['pending_signup_data'] = [
                    'user_id' => $user_id,
                    'fullname' => $fullname,
                    'email' => $email,
                    'phone' => $phone,
                    'avatar_color' => $avatar_color
                ];
                
                $_SESSION['verification_code'] = $verification_code;
                $_SESSION['verification_code_expires_at'] = time() + (15 * 60);
                $_SESSION['pending_verification_email'] = $email;
                $_SESSION['pending_verification_type'] = 'signup';

                // Redirect to verification page
                header("Location: verify.php?type=signup&send=1");
                exit();

            } catch (PDOException $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                $errors['database'] = "خطای پایگاه داده: " . $e->getMessage();
                error_log("Database error during signup: " . $e->getMessage());
            }
        }
    } catch (PDOException $e) {
        $errors['database'] = "خطای پایگاه داده: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ثبت نام</title>
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/signup.css">
  <link rel="stylesheet" href="css/loginstyle.css">

  <!-- فونت وزیر برای فارسی -->
  <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet">
  <link rel="preconnect" href="https://cdn.jsdelivr.net">
  <style>
    body { background: var(--bg-color); }
    * { color: var(--text-color); }
    .success-popup {
      position: fixed;
      top: 20px;
      right: 20px;
      background-color: #4CAF50;
      color: white;
      padding: 15px 20px;
      border-radius: 5px;
      box-shadow: 0 4px 8px rgba(0,0,0,0.2);
      z-index: 1000;
      animation: slideIn 0.5s, fadeOut 0.5s 2.5s forwards;
      display: none;
    }
    @keyframes slideIn {
      from { top: -50px; opacity: 0; }
      to { top: 20px; opacity: 1; }
    }
    @keyframes fadeOut {
      to { opacity: 0; visibility: hidden; }
    }
  </style>
</head>
<body>
  <div class="success-popup" id="successPopup">ثبت نام با موفقیت انجام شد</div>

  <div class="container-load" id="container-load">
    <span class="container20" id="container20">
      <div class="dot"></div>
      <div class="dot"></div>
      <div class="dot"></div>
      <div class="dot"></div>
      <div class="dot"></div>
      <div class="dot"></div>
      <div class="dot"></div>
      <div class="dot"></div>
      <div class="dot"></div>
      <div class="dot"></div>
      <div class="dot"></div>
      <div class="dot"></div>
      <div class="dot"></div>
      <div class="dot"></div>
      <div class="dot"></div>
    </span>
  </div>

  <div class="history">
    <div class="redoButton" style="position: relative; right: 10px;">
      <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="arrow-left" style="color: white !important;" viewBox="0 0 16 16">
        <path fill-rule="evenodd" d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8"/>
      </svg>
    </div>
    <div class="backButton" style="position: relative; left: 10px;">
      <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="arrow-right" style="color: white !important;" viewBox="0 0 16 16">
        <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8"/>
      </svg>
    </div>
  </div>
  <main class="signup-container">
    <div class="signup-wrapper">
      <div class="mon" id="model">
        <svg onclick="darkmode()" xmlns="http://www.w3.org/2000/svg" width="35" height="35" fill="currentColor" class="moon2" id="moon2" viewBox="0 0 16 16">
          <path d="M6 .278a.77.77 0 0 1 .08.858 7.2 7.2 0 0 0-.878 3.46c0 4.021 3.278 7.277 7.318 7.277q.792-.001 1.533-.16a.79.79 0 0 1 .81.316.73.73 0 0 1-.031.893A8.35 8.35 0 0 1 8.344 16C3.734 16 0 12.286 0 7.71 0 4.266 2.114 1.312 5.124.06A.75.75 0 0 1 6 .278"/>
        </svg>
        <svg onclick="darkmode()" xmlns="http://www.w3.org/2000/svg" width="35" height="35" id="sun2" fill="currentColor" class="sun2" style="display: none;" viewBox="0 0 16 16">
          <path d="M12 8a4 4 0 1 1-8 0 4 4 0 0 1 8 0M8 0a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 0m0 13a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 13m8-5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2a.5.5 0 0 1 .5.5M3 8a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2A.5.5 0 0 1 3 8m10.657-5.657a.5.5 0 0 1 0 .707l-1.414 1.415a.5.5 0 1 1-.707-.708l1.414-1.414a.5.5 0 0 1 .707 0m-9.193 9.193a.5.5 0 0 1 0 .707L3.05 13.657a.5.5 0 0 1-.707-.707l1.414-1.414a.5.5 0 0 1 .707 0m9.193 2.121a.5.5 0 0 1-.707 0l-1.414-1.414a.5.5 0 0 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .707M4.464 4.465a.5.5 0 0 1-.707 0L2.343 3.05a.5.5 0 1 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .708"/>
        </svg>
      </div>

      <div class="signup-header">
        <h1 class="signup-title">ثبت نام</h1>
        <p class="signup-subtitle">لطفا اطلاعات خود را وارد کنید</p>
      </div>

      <form class="signup-form" method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" id="signup-form">
        <div class="input-group">
          <label for="fullname">نام کامل</label>
          <input type="text" id="fullname" name="fullname" placeholder="" value="<?php echo isset($fullname) ? htmlspecialchars($fullname) : ''; ?>">
          <div class="error-container"><?php echo isset($errors['fullname']) ? $errors['fullname'] : ''; ?></div>
        </div>

        <div class="input-group">
          <label for="email">ایمیل</label>
          <input type="email" id="email" name="email" placeholder="" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
          <div class="error-container"><?php echo isset($errors['email']) ? $errors['email'] : ''; ?></div>
        </div>

        <div class="input-group">
          <label for="phone">شماره تلفن</label>
          <input type="tel" id="phone" name="phone" placeholder="" value="<?php echo isset($phone) ? htmlspecialchars($phone) : ''; ?>">
          <div class="error-container"><?php echo isset($errors['phone']) ? $errors['phone'] : ''; ?></div>
        </div>

        <div class="input-group">
          <label for="password">رمز عبور</label>
          <div class="holder">
            <input type="password" id="password" class="pass" name="password" placeholder="">
            <span class="toggle-password" onclick="togglePasswordVisibility('password')">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" style="cursor: pointer;" class="bi-eye" viewBox="0 0 16 16">
                <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM8 3a5 5 0 0 1 5 5 5 5 0 0 1-5 5 5 5 0 0 1-5-5 5 5 0 0 1 5-5z"/>
                <path d="M8 5a3 3 0 0 0 0 6 3 3 0 0 0 0-6z"/>
              </svg>
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" style="cursor: pointer; display: none;" class="bi-eye-slash" viewBox="0 0 16 16">
                <path d="M13.359 11.238C15.06 9.72 16 8 16 8s-3-5.5-8-5.5a7 7 0 0 0-2.79.588l.77.771A6 6 0 0 1 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755q-.247.248-.517.486z"/>
                <path d="M11.297 9.176a3.5 3.5 0 0 0-4.474-4.474l.823.823a2.5 2.5 0 0 1 2.829 2.829zm-2.943 1.299.822.822a3.5 3.5 0 0 1-4.474-4.474l.823.823a2.5 2.5 0 0 0 2.829 2.829"/>
                <path d="M3.35 5.47q-.27.24-.518.487A13 13 0 0 0 1.172 8l.195.288c.335.48.83 1.12 1.465 1.755C4.121 11.332 5.881 12.5 8 12.5c.716 0 1.39-.133 2.02-.36l.77.772A7 7 0 0 1 8 13.5C3 13.5 0 8 0 8s.939-1.721 2.641-3.238l.708.709zm10.296 8.884-12-12 .708-.708 12 12z"/>
              </svg>
            </span>
          </div>
          <div class="error-container"><?php echo isset($errors['password']) ? $errors['password'] : ''; ?></div>
        </div>

        <div class="input-group">
          <label for="confirm-password">تکرار رمز عبور</label>
          <div class="holder">
            <input type="password" id="confirm-password" class="pass" name="confirm-password" placeholder="">
            <span class="toggle-password" onclick="togglePasswordVisibility('confirm-password')">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" style="cursor: pointer;" class="bi-eye" viewBox="0 0 16 16">
                <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM8 3a5 5 0 0 1 5 5 5 5 0 0 1-5 5 5 5 0 0 1-5-5 5 5 0 0 1 5-5z"/>
                <path d="M8 5a3 3 0 0 0 0 6 3 3 0 0 0 0-6z"/>
              </svg>
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" style="cursor: pointer; display: none;" class="bi-eye-slash" viewBox="0 0 16 16">
                <path d="M13.359 11.238C15.06 9.72 16 8 16 8s-3-5.5-8-5.5a7 7 0 0 0-2.79.588l.77.771A6 6 0 0 1 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755q-.247.248-.517.486z"/>
                <path d="M11.297 9.176a3.5 3.5 0 0 0-4.474-4.474l.823.823a2.5 2.5 0 0 1 2.829 2.829zm-2.943 1.299.822.822a3.5 3.5 0 0 1-4.474-4.474l.823.823a2.5 2.5 0 0 0 2.829 2.829"/>
                <path d="M3.35 5.47q-.27.24-.518.487A13 13 0 0 0 1.172 8l.195.288c.335.48.83 1.12 1.465 1.755C4.121 11.332 5.881 12.5 8 12.5c.716 0 1.39-.133 2.02-.36l.77.772A7 7 0 0 1 8 13.5C3 13.5 0 8 0 8s.939-1.721 2.641-3.238l.708.709zm10.296 8.884-12-12 .708-.708 12 12z"/>
              </svg>
            </span>
          </div>
          <div class="error-container"><?php echo isset($errors['confirm_password']) ? $errors['confirm_password'] : ''; ?></div>
        </div>

        <button type="submit" class="auth-btn">ثبت نام</button>
      </form>

      <div class="login-section">
        قبلاً ثبت نام کرده‌اید؟ 
        <a href="login.php" class="switch-link">وارد شوید</a>
      </div>
    </div>
  </main>

  
  <script src="js/login signup.js"></script>
  <script src="js/signup.js"></script>
  <script src="backbutton.js"></script>
  <script src="js/settings.js"></script>
  
  <!-- No popup JS here, handled by login.php after redirect -->
</body>
</html>
