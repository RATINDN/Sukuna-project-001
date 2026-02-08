<?php
require_once 'db_connect.php';
session_start();

if (!isset($_GET['id']) || !isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// دریافت اطلاعات قرارداد
$stmt = $pdo->prepare("SELECT * FROM contracts WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $user_id]);
$contract = $stmt->fetch();

if (!$contract) {
    die("قرارداد یافت نشد.");
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>قرارداد <?php echo $contract['tracking_code']; ?></title>
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet">
    <style>
        :root {
            --print-font: 'Vazirmatn', sans-serif;
        }
        body { 
            font-family: var(--print-font); 
            background: #525659; 
            margin: 0; 
            padding: 20px; 
            display: flex; 
            justify-content: center; 
        }
        
        .paper {
            background: white; 
            width: 210mm; 
            min-height: 296mm;
            padding: 15mm 20mm; 
            box-shadow: 0 0 15px rgba(0,0,0,0.5);
            position: relative; 
            color: black; 
            box-sizing: border-box;
        }

        /* هدر رسمی */
        .header { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            border-bottom: 2px solid #000; 
            padding-bottom: 10px; 
            margin-bottom: 20px; 
        }
        .header-logo h1 { margin: 0; font-size: 22px; font-weight: 900; }
        .header-logo span { font-size: 12px; color: #555; }
        .header-info { text-align: left; font-size: 13px; line-height: 1.8; }

        /* عناوین مواد */
        h3 { 
            background: #eee; 
            padding: 5px 10px; 
            border-radius: 5px; 
            font-size: 14px; 
            margin-top: 20px; 
            margin-bottom: 10px;
            border-right: 4px solid #000;
        }

        /* محتوا */
        p, li { font-size: 13px; line-height: 1.8; text-align: justify; margin: 5px 0; }
        .info-grid { 
            display: grid; 
            grid-template-columns: 1fr 1fr; 
            gap: 15px; 
            margin-bottom: 5px; 
            font-size: 13px;
        }
        strong { font-weight: 800; }

        /* باکس تاییدیه */
        .confirmation-box {
            border: 1px dashed #333;
            padding: 10px;
            margin-top: 20px;
            background-color: #fafafa;
            border-radius: 8px;
        }

        /* بخش امضا */
        .signature-section { 
            margin-top: 40px; 
            display: flex; 
            justify-content: space-between; 
            align-items: flex-end; 
        }
        .sign-box { 
            width: 40%; 
            text-align: center; 
            border-top: 1px solid #000; 
            padding-top: 10px; 
            position: relative; 
            height: 120px; /* فضای کافی برای مهر و امضا */
        }
        
        /* مهر شرکت */
        .stamp {
            position: absolute;
            top: 10px;
            left: 25%;
            width: 110px;
            height: 110px;
            border: 3px solid #d32f2f;
            border-radius: 50%;
            color: #d32f2f;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            font-weight: bold;
            font-size: 16px;
            transform: rotate(-20deg);
            opacity: 0.7;
            mask-image: url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAIAAACQd1PeAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAA5JREFUeNpiYGBgAAgwAAAEAAGbA+oJAAAAAElFTkSuQmCC'); 
            z-index: 1;
            background: rgba(255, 255, 255, 0.1);
        }
        .stamp span { font-size: 10px; margin-top: 2px; }

        /* امضای فروشنده (SVG) */
        .seller-sign {
            position: absolute;
            top: 20px;
            left: 20%;
            width: 140px;
            height: 80px;
            z-index: 2; /* روی مهر بیفتد */
            transform: rotate(-5deg);
        }
        .seller-sign path {
            fill: none;
            stroke: #000080; /* رنگ خودکار آبی تیره */
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        /* دکمه‌ها */
        .action-bar { position: fixed; bottom: 30px; right: 30px; display: flex; gap: 10px; z-index: 1000; }
        .btn { padding: 12px 25px; border: none; border-radius: 50px; cursor: pointer; color: white; font-family: inherit; font-weight: bold; box-shadow: 0 5px 15px rgba(0,0,0,0.3); transition: transform 0.2s; }
        .btn:hover { transform: translateY(-3px); }
        .btn-print { background: #2196F3; }
        .btn-home { background: #4CAF50; text-decoration: none; display: inline-block; }

        /* تنظیمات پرینت */
        @page { size: A4; margin: 0; }
        @media print {
            body { background: white; margin: 0; padding: 0; }
            .paper { width: 100%; height: 100%; box-shadow: none; padding: 15mm; margin: 0; }
            .action-bar { display: none !important; }
            h3 { background: none; border: 1px solid #000; border-right: 4px solid #000; }
        }
    </style>
</head>
<body>

    <div class="action-bar">
        <a href="index.php" class="btn btn-home">بازگشت به فروشگاه</a>
        <button class="btn btn-print" onclick="window.print()">🖨️ پرینت قرارداد</button>
    </div>

    <div class="paper">
        <div class="header">
            <div class="header-logo">
                <h1>فروشگاه خودروی لوکس</h1>
                <span>LUXURY CAR DEALERSHIP</span>
            </div>
            <div class="header-info">
                <div><strong>شماره قرارداد:</strong> <?php echo $contract['tracking_code']; ?></div>
                <div><strong>تاریخ صدور:</strong> <?php echo date('Y/m/d', strtotime($contract['created_at'])); ?></div>
                <div><strong>وضعیت:</strong> <?php echo $contract['status'] == 'pending' ? 'در انتظار پرداخت' : 'نهایی شده'; ?></div>
            </div>
        </div>

        <div style="text-align: center; margin-bottom: 20px;">
            <h2 style="margin: 0; border: 1px solid #000; display: inline-block; padding: 5px 20px; border-radius: 20px;">قرارداد فروش قطعی خودرو</h2>
        </div>

        <h3>ماده ۱: طرفین معامله</h3>
        <div class="info-grid">
            <div>
                <strong>فروشنده:</strong> شرکت خودروی لوکس (سهامی خاص)
                <br>
                به شماره ثبت ۹۸۷۶۵۴ و شناسه ملی ۱۴۰۰۱۲۳۴۵۶۷
            </div>
            <div>
                <strong>خریدار:</strong> خانم/آقای <?php echo htmlspecialchars($contract['real_name']); ?>
                <br>
                کد ملی: <?php echo htmlspecialchars($contract['national_id']); ?> | تلفن: <?php echo htmlspecialchars($contract['phone']); ?>
            </div>
        </div>
        <p><strong>آدرس خریدار:</strong> <?php echo htmlspecialchars($contract['address']); ?> (کد پستی: <?php echo htmlspecialchars($contract['postal_code']); ?></p>

        <h3>ماده ۲: موضوع قرارداد</h3>
        <p>فروش یک دستگاه خودرو با مشخصات ذیل که خریدار آن را رویت نموده و از سلامت فنی و ظاهری آن اطمینان حاصل کرده است:</p>
        <div class="info-grid">
            <div><strong>نام خودرو:</strong> <?php echo htmlspecialchars($contract['car_name']); ?></div>
            <div><strong>رنگ بدنه:</strong> <?php echo htmlspecialchars($contract['car_color']); ?></div>
            <div><strong>قیمت نهایی:</strong> <?php echo htmlspecialchars($contract['car_price']); ?></div>
            <div><strong>متعلقات:</strong> سوئیچ یدک، کارت گارانتی، دفترچه راهنما</div>
        </div>

        <h3>ماده ۳: شرایط پرداخت و تحویل</h3>
        <p>۱-۳. خریدار متعهد می‌گردد مبلغ کل قرارداد را از طریق درگاه‌های بانکی متصل به سایت پرداخت نماید.</p>
        <p>۲-۳. فروشنده متعهد است حداکثر ظرف مدت ۷ روز کاری پس از تسویه حساب کامل، خودرو را به آدرس اعلامی خریدار تحویل دهد.</p>

        <h3>ماده ۴: حل اختلاف و قوه قهریه</h3>
        <p>در صورت بروز هرگونه اختلاف، طرفین تلاش خواهند کرد موضوع را از طریق مذاکره حل و فصل نمایند. در صورت عدم توافق، مراجع قضایی صالح به رسیدگی خواهند بود. همچنین در موارد فورس ماژور (سیل، زلزله و...) تا رفع مانع، انجام تعهدات به تعویق خواهد افتاد.</p>

        <div class="confirmation-box">
            <p style="margin: 0; font-size: 12px;">
                اینجانب <strong><?php echo htmlspecialchars($contract['real_name']); ?></strong> 
                متعهد می‌شوم خودروی <strong><?php echo htmlspecialchars($contract['car_name']); ?></strong> 
                به رنگ <strong><?php echo htmlspecialchars($contract['car_color']); ?></strong> 
                را به مبلغ <strong><?php echo htmlspecialchars($contract['car_price']); ?></strong> 
                خریداری نموده و صحت تمامی اطلاعات وارد شده را تایید می‌نمایم.
            </p>
        </div>

        <div class="signature-section">
            <div class="sign-box">
                <strong>مهر و امضای فروشنده</strong>
                
                <!-- مهر شرکت -->
                <div class="stamp">
                    لوکس کار
                    <span>مهر و امضا</span>
                    <span>معتبر</span>
                </div>

                <!-- امضای فروشنده (طرح دستی با SVG) -->
                <svg class="seller-sign" viewBox="0 0 200 100">
                    <path d="M20,60 C50,40 80,80 120,50 S180,70 190,40" fill="none" />
                    <path d="M40,70 C60,60 80,70 100,60" fill="none" />
                    <path d="M10,50 Q40,90 30,30" fill="none" />
                </svg>
            </div>

            <div class="sign-box">
                <strong>امضای خریدار</strong>
                <br>
                <img src="<?php echo $contract['signature']; ?>" style="max-height: 80px; max-width: 150px; margin-top: 5px;">
                <br>
                <small style="font-size: 10px; color: #666;">(امضای دیجیتال ثبت شده در سیستم)</small>
            </div>
        </div>

        <div style="text-align: center; font-size: 10px; margin-top: 30px; color: #999;">
            این سند به صورت سیستمی صادر شده و فاقد قلم‌خوردگی معتبر است. | شناسه رهگیری: <?php echo $contract['tracking_code']; ?>
        </div>
    </div>

</body>
</html>