<?php
session_start();
require_once 'db_connect.php';

if (!isset($_GET['id']) || !isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$contract_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("SELECT * FROM contracts WHERE id = ? AND user_id = ?");
    $stmt->execute([$contract_id, $user_id]);
    $contract = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$contract) {
        die("قرارداد نامعتبر است.");
    }
    
    if($contract['status'] === 'paid') {
        echo "<script>alert('این سفارش قبلا پرداخت شده است.'); window.location.href='print_contract.php?id=$contract_id';</script>";
        exit();
    }
    
} catch (PDOException $e) {
    die("خطا در دیتابیس");
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>درگاه پرداخت امن | لوکس کار</title>
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body {
            background-color: #f0f2f5;
            font-family: 'Vazirmatn', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }
        
        .gateway-card {
            background: white;
            width: 100%;
            max-width: 420px;
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.1);
            overflow: hidden;
            border-top: 5px solid #1976D2;
            position: relative;
        }

        .sms-toast {
            position: absolute;
            top: -100px;
            left: 50%;
            transform: translateX(-50%);
            background-color: #333;
            color: #fff;
            padding: 10px 20px;
            border-radius: 50px;
            font-size: 0.9rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            transition: top 0.5s ease-in-out;
            z-index: 20;
            white-space: nowrap;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .sms-toast.show { top: 20px; }

        .gateway-header {
            background: #f8f9fa;
            color: #333;
            padding: 15px 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .gateway-header h2 { margin: 0; font-size: 1.1rem; }
        .logo { font-weight: bold; color: #1976D2; font-size: 0.9rem; }

        .gateway-body { padding: 25px; }

        .info-box {
            background-color: #e3f2fd;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            border: 1px dashed #1976D2;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 0.9rem;
            color: #555;
        }
        .price { color: #2e7d32; font-weight: 900; font-size: 1.1rem; }
        .tracking { font-family: monospace; letter-spacing: 1px; font-weight: bold; }

        .timer-box {
            text-align: center;
            margin-bottom: 20px;
            color: #d32f2f;
            font-weight: bold;
            font-size: 1.2rem;
            background: #fff0f0;
            padding: 5px;
            border-radius: 5px;
        }

        .input-group { margin-bottom: 20px; position: relative; }
        .input-group label {
            display: block;
            margin-bottom: 6px;
            font-size: 0.85rem;
            color: #666;
            font-weight: bold;
        }
        .input-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-family: 'Vazirmatn', sans-serif;
            font-size: 1rem;
            text-align: center;
            transition: all 0.3s;
            outline: none;
            direction: ltr;
        }
        
        .input-group input:focus {
            border-color: #1976D2;
            box-shadow: 0 0 0 3px rgba(25, 118, 210, 0.1);
        }

        .btn-otp {
            position: absolute;
            left: 5px;
            top: 5px;
            height: 36px;
            border: none;
            background: #1976D2;
            color: white;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.75rem;
            padding: 0 10px;
            transition: 0.3s;
            font-family: 'Vazirmatn';
            min-width: 100px;
            z-index: 5;
        }
        .btn-otp:hover { background: #1565C0; }
        .btn-otp:disabled { background: #cfd8dc; color: #555; cursor: default; }

        input.invalid { border-color: #d32f2f !important; background-color: #fff8f8; }
        input.valid { border-color: #2e7d32 !important; background-color: #f1f8e9; }

        /* استایل خطا - با display block !important برای اطمینان */
        .error-message {
            color: #d32f2f;
            font-size: 0.8rem;
            margin-top: 5px;
            text-align: right;
            display: none;
            font-weight: bold;
            clear: both; /* جلوگیری از تداخل */
            width: 100%;
        }

        .row-inputs { display: flex; gap: 15px; }
        .flex-1 { flex: 1; }

        .btn-pay {
            width: 100%;
            padding: 14px;
            background: #2e7d32;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
            margin-top: 10px;
            box-shadow: 0 4px 6px rgba(46, 125, 50, 0.2);
        }
        .btn-pay:hover { background: #1b5e20; transform: translateY(-2px); }
        .btn-pay:disabled { background: #ccc; cursor: not-allowed; transform: none; box-shadow: none; }
        
        .btn-cancel {
            width: 100%;
            padding: 12px;
            background: transparent;
            color: #757575;
            border: 1px solid #ddd;
            border-radius: 8px;
            margin-top: 10px;
            cursor: pointer;
            font-family: 'Vazirmatn';
            transition: 0.3s;
        }
        .btn-cancel:hover { background: #f5f5f5; color: #333; }

    </style>
</head>
<body>

<div class="gateway-card">
    <div id="smsToast" class="sms-toast">
        <span class="sms-icon">📩</span>
        <span id="smsText"></span>
    </div>

    <div class="gateway-header">
        <h2>پرداخت اینترنتی</h2>
        <span class="logo">SHAPARAK</span>
    </div>
    
    <div class="gateway-body">
        <div class="timer-box">
            <span id="time">10:00</span>
        </div>
        
        <div class="info-box">
            <div class="info-row">
                <span>شماره سفارش:</span>
                <span class="tracking"><?php echo htmlspecialchars($contract['tracking_code']); ?></span>
            </div>
            <div class="info-row">
                <span>مبلغ قابل پرداخت:</span>
                <span class="price"><?php echo htmlspecialchars($contract['car_price']); ?></span>
            </div>
            <div class="info-row">
                <span>پذیرنده:</span>
                <span>فروشگاه خودروی لوکس</span>
            </div>
        </div>

        <form id="paymentForm" action="process_payment.php" method="POST">
            <input type="hidden" name="contract_id" value="<?php echo $contract_id; ?>">
            
            <div class="input-group">
                <label>شماره کارت</label>
                <input type="text" id="cardNumber" placeholder="----  ----  ----  ----" maxlength="25" inputmode="numeric" required>
                <div class="error-message"></div>
            </div>
            
            <div class="row-inputs">
                <div class="input-group flex-1">
                    <label>CVV2</label>
                    <input type="text" id="cvv2" placeholder="***" maxlength="4" inputmode="numeric" required>
                    <div class="error-message"></div>
                </div>
                <div class="input-group flex-1">
                    <label>تاریخ انقضا</label>
                    <div style="display: flex; gap: 5px;">
                        <input type="text" id="expMonth" placeholder="ماه" maxlength="2" inputmode="numeric" required>
                        <span style="font-size: 1.5rem; color: #ccc;">/</span>
                        <input type="text" id="expYear" placeholder="سال" maxlength="2" inputmode="numeric" required>
                    </div>
                    <div id="dateError" class="error-message"></div>
                </div>
            </div>
            
            <div class="input-group">
                <label>رمز دوم (پویا)</label>
                <!-- کانتینر برای دکمه و اینپوت -->
                <div style="position: relative;">
                    <input type="password" id="secondPass" placeholder="رمز پرداخت اینترنتی" maxlength="8" inputmode="numeric" required>
                    <button type="button" id="otpBtn" class="btn-otp">درخواست رمز پویا</button>
                </div>
                <!-- پیام خطا دقیقاً اینجا -->
                <div id="otpError" class="error-message"></div>
            </div>

            <button type="submit" class="btn-pay">پرداخت نهایی</button>
            <a href="index.php" style="text-decoration: none;">
                <button type="button" class="btn-cancel">انصراف و بازگشت</button>
            </a>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // کلیدهای حافظه
    const uniqueKey = "pay_timer_" + "<?php echo $contract_id; ?>";
    const otpKey = "otp_timer_" + "<?php echo $contract_id; ?>";
    const otpValueKey = "otp_val_" + "<?php echo $contract_id; ?>";

    const form = document.getElementById('paymentForm');
    const cardInput = document.getElementById('cardNumber');
    const cvvInput = document.getElementById('cvv2');
    const monthInput = document.getElementById('expMonth');
    const yearInput = document.getElementById('expYear');
    const passInput = document.getElementById('secondPass');
    const otpBtn = document.getElementById('otpBtn');
    const smsToast = document.getElementById('smsToast');
    const smsText = document.getElementById('smsText');
    const timeDisplay = document.getElementById('time');

    // تایمر اصلی
    function startMainTimer() {
        let endTime = localStorage.getItem(uniqueKey);
        
        if (!endTime || new Date().getTime() > endTime) {
            const tenMinutesLater = new Date().getTime() + (10 * 60 * 1000);
            localStorage.setItem(uniqueKey, tenMinutesLater);
            endTime = tenMinutesLater;
        }

        const interval = setInterval(() => {
            const now = new Date().getTime();
            const distance = endTime - now;

            if (distance < 0) {
                clearInterval(interval);
                timeDisplay.innerText = "منقضی شده";
                document.querySelector('.btn-pay').disabled = true;
                document.querySelector('.btn-pay').style.background = '#ccc';
                localStorage.removeItem(uniqueKey);
                localStorage.removeItem(otpValueKey); 
            } else {
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);
                timeDisplay.innerText = `${minutes}:${seconds < 10 ? '0' + seconds : seconds}`;
            }
        }, 1000);
    }
    startMainTimer();

    // مدیریت OTP
    let otpInterval; 

    function checkOtpStatus() {
        const otpEndTime = localStorage.getItem(otpKey);
        if (otpEndTime) {
            const now = new Date().getTime();
            if (now < otpEndTime) {
                otpBtn.disabled = true;
                startOtpCountdown(otpEndTime);
            } else {
                resetOtpState();
            }
        }
    }

    function startOtpCountdown(endTime) {
        if (otpInterval) clearInterval(otpInterval);
        
        otpInterval = setInterval(() => {
            const now = new Date().getTime();
            const distance = endTime - now;
            
            if (distance < 0) {
                clearInterval(otpInterval);
                resetOtpState();
            } else {
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);
                otpBtn.innerText = `${seconds} ثانیه`;
            }
        }, 1000);
    }

    function resetOtpState() {
        otpBtn.disabled = false;
        otpBtn.innerText = "درخواست مجدد";
        localStorage.removeItem(otpKey);
        localStorage.removeItem(otpValueKey); 
    }

    checkOtpStatus();

    otpBtn.addEventListener('click', function() {
        const randomCode = Math.floor(10000 + Math.random() * 90000);
        localStorage.setItem(otpValueKey, randomCode);

        smsText.innerText = `رمز پویای شما: ${randomCode}`;
        smsToast.classList.add('show');
        setTimeout(() => { smsToast.classList.remove('show'); }, 6000);

        const endTime = new Date().getTime() + (120 * 1000);
        localStorage.setItem(otpKey, endTime);
        
        otpBtn.disabled = true;
        startOtpCountdown(endTime);
        
        // پاک کردن خطای قبلی
        toggleError(passInput, true, "", "otpError");
    });

    // ============================================
    // تابع نمایش خطا (مهم: اصلاح شده)
    // ============================================
    function toggleError(input, isValid, msg, customErrorId = null) {
        let errorDiv;
        
        if (customErrorId) {
            errorDiv = document.getElementById(customErrorId);
        } else if (input) {
            errorDiv = input.parentElement.querySelector('.error-message');
        }

        if (isValid) {
            if(input) { input.classList.remove('invalid'); input.classList.add('valid'); }
            if (errorDiv) { 
                errorDiv.style.display = 'none'; 
                errorDiv.innerText = ''; 
            }
            return true;
        } else {
            if(input) { input.classList.remove('valid'); input.classList.add('invalid'); }
            if (errorDiv) { 
                errorDiv.innerText = msg; 
                errorDiv.style.display = 'block'; 
            }
            return false;
        }
    }

    function luhnCheck(val) {
        let sum = 0;
        for (let i = 0; i < val.length; i++) {
            let intVal = parseInt(val.substr(i, 1));
            if (i % 2 === 0) {
                intVal *= 2;
                if (intVal > 9) intVal = 1 + (intVal % 10);
            }
            sum += intVal;
        }
        return (sum % 10) === 0;
    }

    cardInput.addEventListener('input', function() {
        let value = this.value.replace(/\D/g, '');
        let formatted = '';
        for(let i = 0; i < value.length; i++) {
            if(i > 0 && i % 4 === 0) formatted += ' - ';
            formatted += value[i];
        }
        this.value = formatted;
        if (value.length === 16) {
            if (luhnCheck(value)) toggleError(this, true, "");
            else toggleError(this, false, "شماره کارت نامعتبر است");
        } else {
            this.classList.remove('valid', 'invalid');
            toggleError(this, true, ""); 
        }
    });

    cvvInput.addEventListener('input', function() {
        this.value = this.value.replace(/\D/g, '');
        if(this.value.length >= 3) toggleError(this, true, "");
    });

    monthInput.addEventListener('input', function() {
        this.value = this.value.replace(/\D/g, '');
        toggleError(null, true, "", "dateError"); 
        if(this.value.length === 2) {
            let m = parseInt(this.value);
            if(m < 1 || m > 12) {
                toggleError(null, false, "ماه اشتباه است", "dateError");
                this.classList.add('invalid');
            } else {
                this.classList.remove('invalid');
                this.classList.add('valid');
                yearInput.focus();
            }
        }
    });

    yearInput.addEventListener('input', function() {
        this.value = this.value.replace(/\D/g, '');
        if(this.value.length > 0) toggleError(null, true, "", "dateError");
    });

    passInput.addEventListener('input', function() {
        this.value = this.value.replace(/\D/g, '');
        if(this.value.length >= 5) toggleError(this, true, "", "otpError");
    });

    // ============================================
    // ارسال فرم
    // ============================================
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        let hasError = false;

        const rawCard = cardInput.value.replace(/\D/g, '');
        if (rawCard.length !== 16 || !luhnCheck(rawCard)) { toggleError(cardInput, false, "شماره کارت صحیح نیست"); hasError = true; }
        if (cvvInput.value.length < 3) { toggleError(cvvInput, false, "CVV2 معتبر نیست"); hasError = true; }
        
        const m = parseInt(monthInput.value);
        const y = parseInt(yearInput.value);
        if (!m || !y || m < 1 || m > 12 || yearInput.value.length !== 2) { toggleError(null, false, "تاریخ انقضا را کامل وارد کنید", "dateError"); hasError = true; }
        
        // بررسی رمز پویا
        const storedOtp = localStorage.getItem(otpValueKey);
        const enteredPass = passInput.value;

        if (!storedOtp) {
            toggleError(passInput, false, "لطفا ابتدا درخواست رمز پویا دهید", "otpError");
            hasError = true;
        } 
        else if (enteredPass !== storedOtp) {
            toggleError(passInput, false, "رمز پویا اشتباه است", "otpError");
            hasError = true;
        }

        if (hasError) return;

        const btn = document.querySelector('.btn-pay');
        btn.innerText = 'در حال اتصال به درگاه بانک...';
        btn.style.backgroundColor = '#ccc';
        btn.disabled = true;
        
        localStorage.removeItem(uniqueKey);
        localStorage.removeItem(otpKey);
        localStorage.removeItem(otpValueKey);

        setTimeout(() => { this.submit(); }, 2000);
    });
});
</script>

</body>
</html>