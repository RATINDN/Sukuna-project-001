<?php
session_start();
require_once 'db_connect.php';

// If user is already logged in, redirect to home page
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$errors = [];
$success = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // No validation here - all validation is in JavaScript

    try {
        $stmt = $pdo->prepare("SELECT id, password, user_name, avatar_color FROM car WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            // Email not found
            $errors['email'] = "ایمیل شما اشتباه است";
        } else if (!password_verify($password, $user['password'])) {
            // Password incorrect
            $errors['password'] = "پسورد شما اشتباه است";
        } else {
            // Password is correct, proceed to verification
            $_SESSION['pending_verification_email'] = $email;
            $_SESSION['user_data'] = $user; // Contains id, user_name, avatar_color
            $_SESSION['pending_verification_type'] = 'login';
            
            // Redirect to verification page with send=1 parameter to force sending the initial email
            header("Location: verify.php?type=login&send=1");
            exit();
        }
    } catch (PDOException $e) {
        $errors['database'] = "خطای پایگاه داده: " . $e->getMessage();
    }
}

if (isset($_SESSION['login_success'])) {
    $success = true;
    unset($_SESSION['login_success']);
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ورود به حساب کاربری</title>
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/loginstyle.css">
  <link rel="stylesheet" href="css/signup.css">
  <!-- لینک کردن استایل‌های فریم‌ورک پاپ‌آپ‌های سفارشی -->
<link rel="stylesheet" href="css/custom_dialogs.css">

<!-- لینک کردن جاوااسکریپت موتور اعلانات سراسری -->
<script src="js/custom_dialogs.js"></script>
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
      display: none;
      animation: slideIn 0.5s, fadeOut 0.5s 2.5s forwards;
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
  <div class="success-popup" id="successPopup">ورود با موفقیت انجام شد</div>
  <div class="success-popup" id="signupSuccessPopup" style="display:none;">ثبت نام با موفقیت انجام شد</div>

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
    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="arrow-left" style="color: white !important;"   viewBox="0 0 16 16">
      <path fill-rule="evenodd" d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8"/>
    </svg>
  </div>
  <div class="backButton" style="position: relative; left: 10px;">
    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="arrow-right" style="color: white !important;"   viewBox="0 0 16 16">
      <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8"/>
    </svg>
  </div>
</div>
  <main class="login-container">
    <div class="login-wrapper">
      <div class="mon" id="model">
        <svg onclick="darkmode()" xmlns="http://www.w3.org/2000/svg" width="35" height="35" fill="currentColor" class="moon2" id="moon2" viewBox="0 0 16 16">
          <path d="M6 .278a.77.77 0 0 1 .08.858 7.2 7.2 0 0 0-.878 3.46c0 4.021 3.278 7.277 7.318 7.277q.792-.001 1.533-.16a.79.79 0 0 1 .81.316.73.73 0 0 1-.031.893A8.35 8.35 0 0 1 8.344 16C3.734 16 0 12.286 0 7.71 0 4.266 2.114 1.312 5.124.06A.75.75 0 0 1 6 .278"/>
        </svg>

        <svg onclick="darkmode()" xmlns="http://www.w3.org/2000/svg" width="35" height="35" id="sun2" fill="currentColor" class="sun2" style="display: none;" viewBox="0 0 16 16">
          <path d="M12 8a4 4 0 1 1-8 0 4 4 0 0 1 8 0M8 0a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 0m0 13a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 13m8-5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2a.5.5 0 0 1 .5.5M3 8a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2A.5.5 0 0 1 3 8m10.657-5.657a.5.5 0 0 1 0 .707l-1.414 1.415a.5.5 0 1 1-.707-.708l1.414-1.414a.5.5 0 0 1 .707 0m-9.193 9.193a.5.5 0 0 1 0 .707L3.05 13.657a.5.5 0 0 1-.707-.707l1.414-1.414a.5.5 0 0 1 .707 0m9.193 2.121a.5.5 0 0 1-.707 0l-1.414-1.414a.5.5 0 0 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .707M4.464 4.465a.5.5 0 0 1-.707 0L2.343 3.05a.5.5 0 1 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .708"/>
        </svg>
      </div>

      <div class="login-header">
        <h1 class="login-title">خوش آمدید!</h1>
        <p class="login-subtitle">لطفا وارد حساب کاربری خود شوید</p>
      </div>

      <form class="login-form" method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" id="login-form">
        <div class="input-group">
          <label for="email">ایمیل</label>
          <input type="email" id="email" name="email"  placeholder="" >
          <div class="error-container"></div>
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
          <div class="error-container"></div>
        </div>

        <div class="login-options">
          <label class="checkbox-container">
            <input type="checkbox">
            <span class="checkmark"></span>
            مرا به خاطر بسپار
          </label>
          <a href="#" class="forgot-password">رمز عبور را فراموش کردید؟</a>
        </div>

        <button type="submit" class="auth-btn">ورود به حساب</button>
      </form> 

      <div class="signup-section">
        حساب کاربری ندارید؟ 
                <a href="signup.php" class="switch-link">ثبت نام کنید</a>
      </div>
    </div>  
  </main>

  
  <script src="js/login signup.js"></script>
  <script src="js/login.js"></script>
  <script src="backbutton.js"></script>
  <script src="js/settings.js"></script>
  

  <script>
    // Show popup after successful login (PHP)
    <?php if ($success): ?>
    document.addEventListener('DOMContentLoaded', function() {
      const popup = document.getElementById('successPopup');
      popup.style.display = 'block';
      // Animation handles fade out and hiding
    });
    <?php endif; ?>

    // Show server-side errors if they exist
    <?php if (!empty($errors)): ?>
    document.addEventListener('DOMContentLoaded', function() {
      <?php if (isset($errors['email'])): ?>
      const emailInput = document.getElementById('email');
      showError(emailInput, '<?php echo $errors['email']; ?>');
      <?php endif; ?>
      
      <?php if (isset($errors['password'])): ?>
      const passwordInput = document.getElementById('password');
      showError(passwordInput, '<?php echo $errors['password']; ?>');
      <?php endif; ?>
    });
    <?php endif; ?>

    // Show popup after successful login
    document.addEventListener('DOMContentLoaded', function() {
      if (sessionStorage.getItem('loginSuccess')) {
        sessionStorage.removeItem('loginSuccess');
        if (typeof window.showToast === 'function') {
            window.showToast('ورود با موفقیت انجام شد!', 'success');
        }
      }
    });
  </script>
</body>
</html>
</qodoArtifact>
