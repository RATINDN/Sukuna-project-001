<?php
session_start();
require_once 'db_connect.php';

$logged_in = false;
$user_name = '';
$avatar_color = ''; // Initialize avatar color

if (isset($_SESSION['user_id'])) {
    $logged_in = true;
    // Prioritize session data as it's most current after registration/login
    if (isset($_SESSION['user_name']) && isset($_SESSION['avatar_color'])) {
        $user_name = $_SESSION['user_name'];
        $avatar_color = $_SESSION['avatar_color'];
    } else {
        // Fallback to database if session is incomplete
        try {
            $stmt = $pdo->prepare("SELECT user_name, avatar_color FROM car WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                $user_name = $user['user_name'];
                $avatar_color = $user['avatar_color'];
                
                // Populate session for future requests
                $_SESSION['user_name'] = $user_name;
                $_SESSION['avatar_color'] = $avatar_color;
            } else {
                // If user is not in DB, log them out
                $logged_in = false;
                session_destroy();
            }
        } catch (PDOException $e) {
            // Handle error, maybe log out user
            $logged_in = false;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>فروشگاه خودروی لوکس</title>
  <link rel="manifest" href="manifest.json">
  <meta name="theme-color" content="#000000">
  <link rel="apple-touch-icon" href="images/icon-192x192.png">
  <link rel="apple-touch-icon" href="images/icon-512x512.png">
  <link rel="preconnect" href="https://cdn.jsdelivr.net">
  <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/profile.css">
  <!-- Add these meta tags for iOS support -->
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <link rel="apple-touch-icon" href="/icons/icon-192x192.png">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  <meta name="apple-mobile-web-app-status-bar-style" content="black">
  <meta name="apple-mobile-web-app-title" content="فروشگاه خودروی لوکس">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
  <!-- Add iOS icon tags -->
  <!-- <link rel="apple-touch-icon" href="images/icon-192x192.png"> -->
  <!-- <link rel="icon" type="image/x-icon" href="images/favicon (1).ico">  -->
  <!-- <link rel="apple-touch-icon" sizes="152x152" href="images/icon-152x152.png">
 <link rel="apple-touch-icon" sizes="180x180" href="images/icon-180x180.png">
 <link rel="apple-touch-icon" sizes="167x167" href="images/icon-167x167.png">
  -->
  <!-- Add iOS splash screen images -->
  <!-- <link rel="apple-touch-startup-image" href="images/splash-640x1136.png" media="(device-width: 320px) and (device-height: 568px) and (-webkit-device-pixel-ratio: 2)"> -->
  <!-- Add more 

    -->
</head>

<body style="  font-family: var(Vazirmatn-font-face); " aria-disabled="true" id="body">
  <div class="success-popup" id="loginSuccessPopup" style="display:none;">ورود با موفقیت انجام شد</div>
  <style>
  .success-popup {
    position: fixed;
    top: 20px;
    right: 20px;
    background-color: #4CAF50;
    color: white;
    padding: 15px 20px;
    border-radius: 5px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    z-index: 1000;
    display: none;
    animation: slideIn 0.5s, fadeOut 0.5s 2.5s forwards;
  }

  @keyframes slideIn {
    from {
      top: -50px;
      opacity: 0;
    }

    to {
      top: 20px;
      opacity: 1;
    }
  }

  @keyframes fadeOut {
    to {
      opacity: 0;
      visibility: hidden;
    }
  }

  input[type="search"]::-webkit-search-cancel-button {
    background: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 16 16'><line x1='4' y1='4' x2='12' y2='12' stroke='red' stroke-width='2'/><line x1='12' y1='4' x2='4' y2='12' stroke='red' stroke-width='2'/></svg>") no-repeat center center;
    width: 16px;
    height: 16px;
    cursor: pointer;
    -webkit-appearance: none;
  }

  .success-popup {
    position: fixed;
    top: 20px;
    right: 20px;
    background-color: #4CAF50;
    color: white;
    padding: 15px 20px;
    border-radius: 5px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    z-index: 1000;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    animation: slideIn 0.5s, fadeOut 0.5s 2.5s forwards;
  }

  @keyframes slideIn {
    from {
      top: -50px;
      opacity: 0;
    }

    to {
      top: 20px;
      opacity: 1;
    }
  }

  @keyframes fadeOut {
    to {
      opacity: 0;
      visibility: hidden;
    }
  }
  </style>

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
      <div class="dot"></div>
    </span>
  </div>

  <main>

    <!-- iOS Installation Guideline Modal -->
    <div id="iosInstallModal"
      style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center;">
      <div id="ios"
        style="background: #fff; padding: 20px; border-radius: 10px; width: 90%; max-width: 400px; text-align: center; direction: rtl; font-family: 'Vazirmatn', sans-serif;">
        <h2 style="font-size: 1.5rem; margin-bottom: 15px;">نصب اپلیکیشن روی iOS</h2>
        <p style="font-size: 1rem; line-height: 1.5; margin-bottom: 20px;">
          ۱. مرورگر قابل پشتیبانی گزینه "Add to Home Screen" را باز کنید، روی دکمه "اشتراک" کلیک کنید.<br>
          ۲. گزینه "Add to Home Screen" را انتخاب کنید.<br>
          ۳. در صفحه باز شده روی "Add" کلیک کنید.<br><br>
          با نصب اپلیکیشن، تجربه کاربری بهتری خواهید داشت و به راحتی می‌توانید به خدمات ما دسترسی پیدا کنید.
        </p>
        <img src="images/ki.jpg" alt="iOS Installation Guide"
          style="width: 100%; max-width: 300px; margin-bottom: 20px; border-radius: 10px;">
        <button id="closeIosModal"
          style="background: #4CAF50; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">بستن</button>
      </div>
    </div>

    <div class="page-up" id="page-up">
      <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" fill="currentColor" class="bi bi-arrow-90deg-up"
        viewBox="0 0 16 16">
        <path fill-rule="evenodd"
          d="M4.854 1.146a.5.5 0 0 0-.708 0l-4 4a.5.5 0 1 0 .708.708L4 2.707V12.5A2.5 2.5 0 0 0 6.5 15h8a.5.5 0 0 0 0-1h-8A1.5 1.5 0 0 1 5 12.5V2.707l3.146 3.147a.5.5 0 1 0 .708-.708z" />
      </svg>
    </div>
    <div class="history">
      <div class="redoButton" style="position: relative; right: 10px;">

        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="arrow-left"
          style="color: white !important;" viewBox="0 0 16 16">
          <path fill-rule="evenodd"
            d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8" />
        </svg>
      </div>
      <div class="backButton" style="position: relative; left: 10px;">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="arrow-right"
          style="color: white !important;  " viewBox="0 0 16 16">
          <path fill-rule="evenodd"
            d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8" />
        </svg>
      </div>
    </div>

    <nav id="nav" dir="ltr" style="top: 30px;">




      <div class="span" class="closebtn" onclick="openNav()" id="closebtn">
        <svg xmlns="http://www.w3.org/2000/svg" width="80%" height="80%" fill="currentColor" class="arrow"
          viewBox="0 0 16 16">
          <path fill-rule="evenodd"
            d="M3.5 10a.5.5 0 0 1-.5-.5v-8a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 .5.5v8a.5.5 0 0 1-.5.5h-2a.5.5 0 0 0 0 1h2A1.5 1.5 0 0 0 14 9.5v-8A1.5 1.5 0 0 0 12.5 0h-9A1.5 1.5 0 0 0 2 1.5v8A1.5 1.5 0 0 0 3.5 11h2a.5.5 0 0 0 0-1z" />
          <path fill-rule="evenodd"
            d="M7.646 15.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 14.293V5.5a.5.5 0 0 0-1 0v8.793l-2.146-2.147a.5.5 0 0 0-.708.708z" />

        </svg>








      </div>

      <div id="myMenu" onclick="closeNav()">
        <!-- <ul> -->
        <div class="lb" id="lb1" onclick="opencar()">
          <h6 class="li3"> <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
              class="bi bi-chevron-left" viewBox="0 0 16 16" style="position: relative; top:5px;">
              <path fill-rule="evenodd"
                d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0" />
            </svg> خودروها </h6>
        </div>





        <div class="lb3 " id="installButton2" style="display: flex !important;  " aria-hidden="true">
          <h6 class="li4"> <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
              class="bi bi-chevron-left" viewBox="0 0 16 16" style="position: relative; top:5px;">
              <path fill-rule="evenodd"
                d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0" />
            </svg> دانلود اپلیکیشن </h6>
        </div>


        <!-- </ul> -->
      </div>


      <div class="scroll" id="scroll">

      </div>

      <div id="overlay">

      </div>
      <ul>
        <li style="cursor: pointer; " class="li1" id="li1">خودروها <svg xmlns="http://www.w3.org/2000/svg" width="16"
            height="16" fill="currentColor" class="bi-down" id="bi-down" viewBox="0 0 16 16">
            <path fill-rule="evenodd"
              d="M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708" />
          </svg></li>




        <div class="veil" id="menu-box">
          <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" fill="currentColor" class="down2"
            viewBox="0 0 16 16">
            <path fill-rule="evenodd"
              d="M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708" />
          </svg>

          <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" fill="currentColor" class="up"
            viewBox="0 0 16 16" style="display: none;">
            <path fill-rule="evenodd"
              d="M7.646 4.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1-.708.708L8 5.707l-5.646 5.647a.5.5 0 0 1-.708-.708z" />
          </svg>

          <div class="veil2" id="veil2">
            <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" fill="currentColor" viewBox="0 0 16 16"
              class="down">
              <path fill-rule="evenodd"
                d="M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708" />
            </svg>

            <div class="search" id="search-box">
              <div class="search2">

                <input type="search" name="" placeholder="جستجو...." id="search">
                <select id="brand-filter" class="select">
                  <option value="">همه برندها</option>
                  <option value="آئودی">آئودی</option>
                  <option value="لامبورگینی"> لامبورگینی </option>
                  <option value="رولزرویس">رولزرویس</option>
                  <option value="فراری">فراری</option>
                  <option value="رنج روور">رنج روور</option>
                  <option value="مرسدس بنز">مرسدس بنز</option>

                </select>
              </div>
            </div>

            <div class="parent">
              <!-- خودرو ۱ -->

              <div class="box" data-title="آئودی A6 e-tron 2024">
                <div class="box-2" style="height: 90%; border-radius: 15px; background-image: url(images/car-1.webp);">
                  <a href="" style="width: 100%;">
                    <div class="child">

                      <h1 style="font-weight: 400;">+</h1>
                    </div>
                  </a>
                </div>
                <div class="box2" style="display: flex; flex-direction: column; justify-content: space-between;">
                  <div class="box-3">
                    <h4 style="font-weight: 400;">آئودی A6 e-tron 2024</h4>
                  </div>
                  <div class="box3" style="height: 50%; padding-right: 100px;">
                    <h6 style="font-weight: 400;
                      " id="h6">قیمت شروع: <del>۳۹,۰۲۹ دلار</del> ۳۳,۴۵۱ دلار</h6>
                    <h6 style="font-weight: 400; " id="h6">موجودی: ۱۰,۰۰۰ دستگاه</h6>
                  </div>
                </div>
              </div>

              <!-- خودرو ۲ -->
              <div class="box" data-title="لامبورگینی آونتادور ۲۰۱۱">
                <div class="box-2" style="height: 90%; border-radius: 15px; background-image: url(images/1.webp);">
                  <a href="" style="width: 100%;">
                    <div class="child">
                      <h1 style="font-weight: 400;">+</h1>
                    </div>
                  </a>
                </div>
                <div class="box2" style="display: flex; flex-direction: column; justify-content: space-between;">
                  <div class="box-3">
                    <h4 style="font-weight: 400;">لامبورگینی آونتادور ۲۰۱۱</h4>
                  </div>
                  <div class="box3" style="height: 50%;">
                    <h6 style="font-weight: 400;" id="h6">قیمت شروع: ۴۲۴,۰۰۰ دلار</h6>
                    <h6 style="font-weight: 400;" id="h6">موجودی: ۲,۹۵۶ دستگاه</h6>
                  </div>
                </div>
              </div>

              <!-- خودرو ۳ -->
              <div class="box" data-title="رولزرویس فانتوم ۲۰۱۷">
                <div class="box-2" style="height: 90%; border-radius: 15px; background-image: url(images/2.webp);">
                  <a href="" style="width: 100%;">
                    <div class="child">
                      <h1 style="font-weight: 400;">+</h1>
                    </div>
                  </a>
                </div>
                <div class="box2" style="display: flex; flex-direction: column; justify-content: space-between;">
                  <div class="box-3">
                    <h4 style="font-weight: 400;">رولزرویس فانتوم ۲۰۱۷</h4>
                  </div>
                  <div class="box3" style="height: 50%;">
                    <h6 style="font-weight: 400;" id="h6">قیمت شروع: ۱,۵۰۰,۰۰۰ دلار</h6>
                    <h6 style="font-weight: 400;" id="h6">موجودی: ۱۶۳ دستگاه</h6>
                  </div>
                </div>
              </div>

              <!-- خودرو ۴ -->
              <div class="box" data-title="فراری لافراری ۲۰۱۳">
                <div class="box-2" style="height: 90%; border-radius: 15px; background-image: url(images/3.webp);">
                  <a href="" style="width: 100%;">
                    <div class="child">
                      <h1 style="font-weight: 400;">+</h1>
                    </div>
                  </a>
                </div>
                <div class="box2" style="display: flex; flex-direction: column; justify-content: space-between;">
                  <div class="box-3">
                    <h4 style="font-weight: 400;">فراری لافراری ۲۰۱۳</h4>
                  </div>
                  <div class="box3" style="height: 50%;">
                    <h6 style="font-weight: 400;" id="h6">قیمت شروع: ۲,۳۰۰,۰۲۰ دلار</h6>
                    <h6 style="font-weight: 400;" id="h6">موجودی: ۵۰ دستگاه</h6>
                  </div>
                </div>
              </div>

              <!-- خودرو ۵ -->
              <div class="box" data-title="رنج روور ۲۰۲۳">
                <div class="box-2" style="height: 90%; border-radius: 15px; background-image: url(images/4.webp);">
                  <a href="" style="width: 100%;">
                    <div class="child">
                      <h1 style="font-weight: 400;">+</h1>
                    </div>
                  </a>
                </div>
                <div class="box2" style="display: flex; flex-direction: column; justify-content: space-between;">
                  <div class="box-3">
                    <h4 style="font-weight: 400;">رنج روور ۲۰۲۳</h4>
                  </div>
                  <div class="box3" style="height: 50%;">
                    <h6 style="font-weight: 400;" id="h6">قیمت شروع: ۹۵,۶۷۸ دلار</h6>
                    <h6 style="font-weight: 400;" id="h6">موجودی: ۵,۶۹۲ دستگاه</h6>
                  </div>
                </div>
              </div>

              <!-- خودرو ۶ -->
              <div class="box" data-title="مرسدس بنز S-Class 2021">
                <div class="box-2" style="height: 90%; border-radius: 15px; background-image: url(images/5.webp);">
                  <a href="" style="width: 100%;">
                    <div class="child">
                      <h1 style="font-weight: 400;">+</h1>
                    </div>
                  </a>
                </div>
                <div class="box2" style="display: flex; flex-direction: column; justify-content: space-between;">
                  <div class="box-3">
                    <h4 style="font-weight: 400;">مرسدس بنز S-Class 2021</h4>
                  </div>
                  <div class="box3" style="height: 50%;">
                    <h6 style="font-weight: 400;" id="h6">قیمت شروع: ۱,۲۰۰,۰۰۰ دلار</h6>
                    <h6 style="font-weight: 400;" id="h6">موجودی: ۹۶۸ دستگاه</h6>
                  </div>
                </div>
              </div>

              <div class="box" data-title="۲۰۱۳ فراری f8 تروبیتو">

                <div class="box-2" style="height: 90%; border-radius: 15px; background-image: url(images/6.webp);">
                  <a href="" style="width: 100%;">
                    <div class="child">
                      <h1 style=" font-weight: 400;">+</h1>
                    </div>
                  </a>
                </div>
                <div class="box2" style="display: flex; flex-direction: column;  justify-content: space-between;">
                  <div class="box-3">
                    <h4 style="font-weight: 400;">۲۰۱۳ فراری f8 تروبیتو</h4>
                  </div>
                  <div class="box3" style="height: 50%;">
                    <h6 style="font-weight: 400;" id="h6">قیمت شروع: ۱,۲۰۰,۰۰۰ دلار</h6>
                    <h6 style="font-weight: 400;" id="h6">موجودی: ۹۶۸ دستگاه</h6>
                  </div>
                </div>

              </div>
            </div>
          </div>



        </div>

















        </div>














        </div>
        </div>
        </div>


        <li style="cursor: pointer; display: flex !important; " class="li2 " id="installButton" aria-hidden="true">
          دانلود اپلیکیشن</li>



      </ul>


      <div class="mode" id="mode"><svg onclick="darkmode()" xmlns="http://www.w3.org/2000/svg" width="35" height="35"
          id="sun" fill="currentColor" class="sun" style="display: none;" viewBox="0 0 16 16">
          <path
            d="M12 8a4 4 0 1 1-8 0 4 4 0 0 1 8 0M8 0a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 0m0 13a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 13m8-5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2a.5.5 0 0 1 .5.5M3 8a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2A.5.5 0 0 1 3 8m10.657-5.657a.5.5 0 0 1 0 .707l-1.414 1.415a.5.5 0 1 1-.707-.708l1.414-1.414a.5.5 0 0 1 .707 0m-9.193 9.193a.5.5 0 0 1 0 .707L3.05 13.657a.5.5 0 0 1-.707-.707l1.414-1.414a.5.5 0 0 1 .707 0m9.193 2.121a.5.5 0 0 1-.707 0l-1.414-1.414a.5.5 0 0 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .707M4.464 4.465a.5.5 0 0 1-.707 0L2.343 3.05a.5.5 0 1 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .708" />
        </svg>

        <svg onclick="darkmode()" xmlns="http://www.w3.org/2000/svg" width="35" height="35" fill="currentColor"
          style="color: black;" class="moon" id="moon" viewBox="0 0 16 16">
          <path
            d="M6 .278a.77.77 0 0 1 .08.858 7.2 7.2 0 0 0-.878 3.46c0 4.021 3.278 7.277 7.318 7.277q.792-.001 1.533-.16a.79.79 0 0 1 .81.316.73.73 0 0 1-.031.893A8.35 8.35 0 0 1 8.344 16C3.734 16 0 12.286 0 7.71 0 4.266 2.114 1.312 5.124.06A.75.75 0 0 1 6 .278" />
        </svg>
      </div>








      <div class="bow" id="bow">
        <?php if ($logged_in): ?>
        <div class="profile-avatar-container">
        <div class="profile-avatar" id="profileAvatar" style="background-image: url('images/dog.jpg'); background-position: center; background-size: cover; background-repeat: no-repeat; "  >            <span id="userInitial"></span>
          </div>
          <div class="profile-dropdown" id="profileDropdown">
            <div class="dropdown-profile-header">
              <div class="dropdown-avatar" style="background-image: url('images/dog.jpg'); background-position: center; background-size: cover; background-repeat: no-repeat; " >
                <span id="dropdownUserInitial"></span>
              </div>
              <div class="dropdown-username" id="dropdownUsername"></div>
            </div>
            <div class="dropdown-divider"></div>
            <a href="#" id="openProfileLink" class="dropdown-item">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                class="bi bi-person-circle" viewBox="0 0 16 16">
                <path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0" />
                <path fill-rule="evenodd"
                  d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8m8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1" />
              </svg>
              پروفایل
            </a>
            <a href="#" class="dropdown-item" id="settingsLink">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-gear-fill"
                viewBox="0 0 16 16">
                <path
                  d="M9.405 1.05c-.413-1.4-2.397-1.4-2.81 0l-.1.34a1.464 1.464 0 0 1-2.105.872l-.31-.17c-1.283-.698-2.686.705-1.987 1.987l.169.311a1.464 1.464 0 0 1-.872 2.105l-.34.1c-1.4.413-1.4 2.397 0 2.81l.34.1a1.464 1.464 0 0 1 .872 2.105l-.17.31c-.698 1.283.705 2.686 1.987 1.987l.311-.169a1.464 1.464 0 0 1 2.105.872l.1.34c.413 1.4 2.397 1.4 2.81 0l.1-.34a1.464 1.464 0 0 1 2.105-.872l.31.17c1.283.698 2.686-.705-1.987-1.987l-.169-.311a1.464 1.464 0 0 1 .872-2.105l.34-.1c1.4-.413-1.4-2.397 0-2.81l-.34-.1a1.464 1.464 0 0 1-.872-2.105l.17-.31c.698-1.283-.705-2.686-1.987-1.987l-.311.169a1.464 1.464 0 0 1-2.105-.872zM8 10.93a2.929 2.929 0 1 1 0-5.86 2.929 2.929 0 0 1 0 5.858z" />
              </svg>
              تنظیمات
            </a>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 1): ?>
            <a href="admin.php" class="dropdown-item">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                class="bi bi-shield-lock" viewBox="0 0 16 16">
                <path
                  d="M5.338 1.59a61 61 0 0 0-2.837.856.48.48 0 0 0-.328.39c-.554 4.157.726 7.19 2.253 9.188a10.7 10.7 0 0 0 2.287 2.233c.346.244.652.42.893.533q.18.085.293.118a1 1 0 0 0 .101.025 1 1 0 0 0 .1-.025q.114-.034.294-.118c.24-.113.547-.29.893-.533a10.7 10.7 0 0 0 2.287-2.233c1.527-1.997 2.807-5.031 2.253-9.188a.48.48 0 0 0-.328-.39c-.651-.213-1.75-.56-2.837-.855C9.552 1.29 8.531 1.067 8 1.067c-.53 0-1.552.223-2.662.524zM5.072.56C6.157.265 7.31 0 8 0s1.843.265 2.928.56c1.11.3 2.229.655 2.887.87a1.54 1.54 0 0 1 1.044 1.262c.596 4.477-.787 7.795-2.465 9.99a11.8 11.8 0 0 1-2.517 2.453 7 7 0 0 1-1.048.625c-.28.132-.581.24-.829.24s-.548-.108-.829-.24a7 7 0 0 1-1.048-.625 11.8 11.8 0 0 1-2.517-2.453C1.928 10.487.545 7.169 1.141 2.692A1.54 1.54 0 0 1 2.185 1.43 63 63 0 0 1 5.072.56z" />
                <path
                  d="M9.5 6.5a1.5 1.5 0 0 1-1 1.415l.385 1.99a.5.5 0 0 1-.491.595h-.788a.5.5 0 0 1-.49-.595l.384-1.99a1.5 1.5 0 1 1 2-1.415z" />
              </svg>
              ورود به ادمین
            </a>
            <?php endif; ?>
            <a href="javascript:void(0);" onclick="confirmLogout()" class="dropdown-item">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                class="bi bi-box-arrow-right" viewBox="0 0 16 16">
                <path fill-rule="evenodd"
                  d="M10 12.5a.5.5 0 0 1-.5.5h-8a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 .5.5v2a.5.5 0 0 0 1 0v-2A1.5 1.5 0 0 0 9.5 2h-8A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h8a1.5 1.5 0 0 0 1.5-1.5v-2a.5.5 0 0 0-1 0z" />
                <path fill-rule="evenodd"
                  d="M15.854 8.354a.5.5 0 0 0 0-.708l-3-3a.5.5 0 0 0-.708.708L14.293 7.5H5.5a.5.5 0 0 0 0 1h8.793l-2.147 2.146a.5.5 0 0 0 .708.708z" />
              </svg>
              خروج از اکانت
            </a>

          </div>
        </div>
        <script>
        // Pass PHP variables to JavaScript
        const isLoggedIn = <?php echo json_encode($logged_in); ?>;
        const userName = <?php echo json_encode($user_name); ?>;
        const avatarColor = <?php echo json_encode($avatar_color); ?>;
        </script>
        <?php else: ?>
        <a href="login.php" class="register" id="register">ثبت نام / ورود </a>
        <?php endif; ?>

    </nav>


    <div class="menu" id="openmenu">
      <div class=" search-mobile" id="search-box-2">
        <div class="search2">
          <select id="brand-filter-mobile" class="select">
            <option value="">همه برندها</option>
            <option value="آئودی">آئودی</option>
            <option value="لامبورگینی"> لامبورگینی </option>
            <option value="رولزرویس">رولزرویس</option>
            <option value="فراری">فراری</option>
            <option value="رنج روور">رنج روور</option>
            <option value="مرسدس بنز">مرسدس بنز</option>

          </select>
          <input type="search" name="" placeholder="جستجو...." id="search-mobile" class="search-input">
        </div>
        <div class="close" id="close">
          <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" fill="currentColor" class="arrow-close"
            viewBox="0 0 16 16">
            <path fill-rule="evenodd"
              d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8" />
          </svg>
        </div>
      </div>
      <div class="parent-mobile" id="parent-mobile">
        <!-- خودرو ۱ -->
        <div class="box-mobile" id="boxing1" data-title="آئودی A6 e-tron 2024">
          <div class="box-2-mobile" style="height: 90%; border-radius: 15px; background-image: url(images/car-1.webp);">
            <a href="" style="width: 100%;">
              <div class="child-mobile">
                <h1 style="font-weight: 400;">+</h1>
              </div>
            </a>
          </div>
          <div class="box2-mobile" style="display: flex; flex-direction: column; justify-content: space-between;">
            <div class="box-3-mobile">
              <h4 style="font-weight: 400; font-size: normal;" class="h4">آئودی A6 e-tron 2024</h4>
            </div>
            <div class="box3-mobile" style="height: 50%;">
              <h6 style="font-weight: 400; font-size: 10px;" id="h6">قیمت شروع: <del>۳۹,۰۲۹ دلار</del> ۳۳,۴۵۱ دلار</h6>
              <h6 style="font-weight: 400; font-size: 10px;" id="h6">موجودی: ۱۰,۰۰۰ دستگاه</h6>
            </div>
          </div>
        </div>


        <!-- خودرو ۲ -->
        <div class="box-mobile" id="boxing2" data-title="لامبورگینی آونتادور ۲۰۱۱">
          <div class="box-2-mobile" style="height: 90%; border-radius: 15px; background-image: url(images/1.webp);">
            <a href="" style="width: 100%;">
              <div class="child-mobile">
                <h1 style="font-weight: 400;">+</h1>
              </div>
            </a>
          </div>
          <div class="box2-mobile" style="display: flex; flex-direction: column; justify-content: space-between;">
            <div class="box-3-mobile">
              <h4 style="font-weight: 400; font-size: normal;" class="h4">لامبورگینی آونتادور ۲۰۱۱</h4>
            </div>
            <div class="box3-mobile" style="height: 50%;">
              <h6 style="font-weight: 400; font-size: 10px;" id="h6">قیمت شروع: ۴۲۴,۰۰۰ دلار</h6>
              <h6 style="font-weight: 400; font-size: 10px;" id="h6">موجودی: ۲,۹۵۶ دستگاه</h6>
            </div>
          </div>
        </div>

        <!-- خودرو ۳ -->
        <div class="box-mobile" id="boxing3" data-title="رولزرویس فانتوم ۲۰۱۷">
          <div class="box-2-mobile" style="height: 90%; border-radius: 15px; background-image: url(images/2.webp);">
            <a href="" style="width: 100%;">
              <div class="child-mobile">
                <h1 style="font-weight: 400;">+</h1>
              </div>
            </a>
          </div>
          <div class="box2-mobile" style="display: flex; flex-direction: column; justify-content: space-between;">
            <div class="box-3-mobile">
              <h4 style="font-weight: 400; font-size: normal;" class="h4">رولزرویس فانتوم ۲۰۱۷</h4>
            </div>
            <div class="box3-mobile" style="height: 50%;">
              <h6 style="font-weight: 400; font-size: 10px;" id="h6">قیمت شروع: ۱,۵۰۰,۰۰۰ دلار</h6>
              <h6 style="font-weight: 400; font-size: 10px;" id="h6">موجودی: ۱۶۳ دستگاه</h6>
            </div>
          </div>
        </div>

        <!-- خودرو ۴ -->
        <div class="box-mobile" id="boxing4" data-title="فراری لافراری ۲۰۱۳">
          <div class="box-2-mobile" style="height: 90%; border-radius: 15px; background-image: url(images/3.webp);">
            <a href="" style="width: 100%;">
              <div class="child-mobile">
                <h1 style="font-weight: 400;">+</h1>
              </div>
            </a>
          </div>
          <div class="box2-mobile" style="display: flex; flex-direction: column; justify-content: space-between;">
            <div class="box-3-mobile">
              <h4 style="font-weight: 400; font-size: normal;" class="h4">فراری لافراری ۲۰۱۳</h4>
            </div>
            <div class="box3-mobile" style="height: 50%;">
              <h6 style="font-weight: 400; font-size: 10px;" id="h6">قیمت شروع: ۲,۳۰۰,۰۲۰ دلار</h6>
              <h6 style="font-weight: 400; font-size: 10px;" id="h6">موجودی: ۵۰ دستگاه</h6>
            </div>
          </div>
        </div>

        <!-- خودرو ۵ -->
        <div class="box-mobile" id="boxing5" data-title="رنج روور ۲۰۲۳">
          <div class="box-2-mobile" style="height: 90%; border-radius: 15px; background-image: url(images/4.webp);">
            <a href="" style="width: 100%;">
              <div class="child-mobile">
                <h1 style="font-weight: 400;">+</h1>
              </div>
            </a>
          </div>
          <div class="box2-mobile" style="display: flex; flex-direction: column; justify-content: space-between;">
            <div class="box-3-mobile">
              <h4 style="font-weight: 400; font-size: normal;" class="h4">رنج روور ۲۰۲۳</h4>
            </div>
            <div class="box3-mobile" style="height: 50%;">
              <h6 style="font-weight: 400; font-size: 10px;" id="h6">قیمت شروع: ۹۵,۶۷۸ دلار</h6>
              <h6 style="font-weight: 400; font-size: 10px;" id="h6">موجودی: ۵,۶۹۲ دستگاه</h6>
            </div>
          </div>
        </div>

        <!-- خودرو ۶ -->
        <div class="box-mobile" id="boxing6" data-title="مرسدس بنز S-Class 2021">
          <div class="box-2-mobile" style="height: 90%; border-radius: 15px; background-image: url(images/5.webp);">
            <a href="" style="width: 100%;">
              <div class="child-mobile">
                <h1 style="font-weight: 400;">+</h1>
              </div>
            </a>
          </div>
          <div class="box2-mobile" style="display: flex; flex-direction: column; justify-content: space-between;">
            <div class="box-3-mobile">
              <h4 style="font-weight: 400; font-size: normal;" class="h4">مرسدس بنز S-Class 2021</h4>
            </div>
            <div class="box3-mobile" style="height: 50%;">
              <h6 style="font-weight: 400; font-size: 10px;" id="h6">قیمت شروع: ۱,۲۰۰,۰۰۰ دلار</h6>
              <h6 style="font-weight: 400; font-size: 10px;" id="h6">موجودی: ۹۶۸ دستگاه</h6>
            </div>
          </div>
        </div>

        <!-- خودرو ۷ -->
        <div class="box-mobile" id="boxing7" data-title="فراری F8 تریبوتو ۲۰۱۴">
          <div class="box-2-mobile" style="height: 90%; border-radius: 15px; background-image: url(images/6.webp);">
            <a href="" style="width: 100%;">
              <div class="child-mobile">
                <h1 style="font-weight: 400;">+</h1>
              </div>
            </a>
          </div>
          <div class="box2-mobile" style="display: flex; flex-direction: column; justify-content: space-between;">
            <div class="box-3-mobile">
              <h4 style="font-weight: 400; font-size: normal;" class="h4">فراری F8 تریبوتو ۲۰۱۴</h4>
            </div>
            <div class="box3-mobile" style="height: 50%;">
              <h6 style="font-weight: 400; font-size: 10px;" id="h6">قیمت شروع: ۳,۲۵۱,۰۰۰ دلار</h6>
              <h6 style="font-weight: 400; font-size: 10px;" id="h6">موجودی: ۹۶ دستگاه</h6>
            </div>
          </div>
        </div>
      </div>
    </div>





    <div class="container1">


      <div class="child2">


        <div class="child3" style="background-image: url(images/car-1.webp);">
          <div class="child4"></div>
        </div>
      </div>
      <h1
        style="font-family: 'Gill Sans', 'Gill Sans MT', Calibri, 'Trebuchet MS', sans-serif; font-variant: small-caps; color: white;"
        class="move detail">آئودی A6 e-tron 2024 مدل جدید ما</h1> <br>
      <button class="button detail" id="move">جزئیات بیشتر</button>
      <button class="button3 detail" onclick="opencar()">جزئیات بیشتر</button>

      <div class="real">
        <div class="real2" style="animation: load; animation-timeline: view();">
          <img src="images/man.webp" width="100%" height="100%" alt="تصویر مرد">
        </div>
        <div class="real2">
          <div class="real3">
            <h1 style="font-weight: 200 !important;">چرا باید به ما اعتماد کنید؟</h1>
          </div>
          <div class="real3" style="height: 60%;">
            <h3 style="font-weight: 100;" id="lorem">
              لورم ایپسوم متن ساختگی با تولید سادگی نامفهوم از صنعت چاپ و با استفاده از طراحان گرافیک است. چاپگرها و
              متون بلکه روزنامه و مجله در ستون و سطرآنچنان که لازم است و برای شرایط فعلی تکنولوژی مورد نیاز و کاربردهای
              متنوع با هدف بهبود ابزارهای کاربردی می باشد
            </h3>
          </div>
          <div class="real3" style="height: 20%; justify-content: center; align-items: center;">
            <button class="button2" id="button-read2">مطالعه بیشتر</button>
          </div>
        </div>
      </div>



      <div class="swiper mySwiper">
        <div class="swiper-wrapper">
          <!-- اسلاید ۱ -->
          <div class="swiper-slide" style="background-image: url(images/1.webp);">
            <div class="ord">
              <h3>لامبورگینی آونتادور ۲۰۱۱</h3>
            </div>
            <div class="ord">
              <button class="order">سفارش</button>
              <div class="card">
                <svg xmlns="http://www.w3.org/2000/svg" width="23" height="23" fill="currentColor" class="bi bi-cart4"
                  viewBox="0 0 16 16">
                  <path
                    d="M0 2.5A.5.5 0 0 1 .5 2H2a.5.5 0 0 1 .485.379L2.89 4H14.5a.5.5 0 0 1 .485.621l-1.5 6A.5.5 0 0 1 13 11H4a.5.5 0 0 1-.485-.379L1.61 3H.5a.5.5 0 0 1-.5-.5M3.14 5l.5 2H5V5zM6 5v2h2V5zm3 0v2h2V5zm3 0v2h1.36l.5-2zm1.11 3H12v2h.61zM11 8H9v2h2zM8 8H6v2h2zM5 8H3.89l.5 2H5zm0 5a1 1 0 1 0 0 2 1 1 0 0 0 0-2m-2 1a2 2 0 1 1 4 0 2 2 0 0 1-4 0m9-1a1 1 0 1 0 0 2 1 1 0 0 0 0-2m-2 1a2 2 0 1 1 4 0 2 2 0 0 1-4 0" />
                </svg>
              </div>
            </div>
          </div>

          <!-- اسلاید ۲ -->
          <div class="swiper-slide" style="background-image: url(images/2.webp);">
            <div class="ord">
              <h3>رولزرویس فانتوم ۲۰۱۷</h3>
            </div>
            <div class="ord">
              <button class="order">سفارش</button>
              <div class="card">
                <svg xmlns="http://www.w3.org/2000/svg" width="23" height="23" fill="currentColor" class="bi bi-cart4"
                  viewBox="0 0 16 16">
                  <path
                    d="M0 2.5A.5.5 0 0 1 .5 2H2a.5.5 0 0 1 .485.379L2.89 4H14.5a.5.5 0 0 1 .485.621l-1.5 6A.5.5 0 0 1 13 11H4a.5.5 0 0 1-.485-.379L1.61 3H.5a.5.5 0 0 1-.5-.5M3.14 5l.5 2H5V5zM6 5v2h2V5zm3 0v2h2V5zm3 0v2h1.36l.5-2zm1.11 3H12v2h.61zM11 8H9v2h2zM8 8H6v2h2zM5 8H3.89l.5 2H5zm0 5a1 1 0 1 0 0 2 1 1 0 0 0 0-2m-2 1a2 2 0 1 1 4 0 2 2 0 0 1-4 0m9-1a1 1 0 1 0 0 2 1 1 0 0 0 0-2m-2 1a2 2 0 1 1 4 0 2 2 0 0 1-4 0" />
                </svg>
              </div>
            </div>
          </div>

          <!-- اسلاید ۳ -->
          <div class="swiper-slide" style="background-image: url(images/3.webp);">
            <div class="ord">
              <h3>فراری لافراری ۲۰۱۳</h3>
            </div>
            <div class="ord">
              <button class="order">سفارش</button>
              <div class="card">
                <svg xmlns="http://www.w3.org/2000/svg" width="23" height="23" fill="currentColor" class="bi bi-cart4"
                  viewBox="0 0 16 16">
                  <path
                    d="M0 2.5A.5.5 0 0 1 .5 2H2a.5.5 0 0 1 .485.379L2.89 4H14.5a.5.5 0 0 1 .485.621l-1.5 6A.5.5 0 0 1 13 11H4a.5.5 0 0 1-.485-.379L1.61 3H.5a.5.5 0 0 1-.5-.5M3.14 5l.5 2H5V5zM6 5v2h2V5zm3 0v2h2V5zm3 0v2h1.36l.5-2zm1.11 3H12v2h.61zM11 8H9v2h2zM8 8H6v2h2zM5 8H3.89l.5 2H5zm0 5a1 1 0 1 0 0 2 1 1 0 0 0 0-2m-2 1a2 2 0 1 1 4 0 2 2 0 0 1-4 0m9-1a1 1 0 1 0 0 2 1 1 0 0 0 0-2m-2 1a2 2 0 1 1 4 0 2 2 0 0 1-4 0" />
                </svg>
              </div>
            </div>
          </div>

          <!-- اسلاید ۴ -->
          <div class="swiper-slide" style="background-image: url(images/4.webp);">
            <div class="ord">
              <h3>رنج روور ۲۰۲۳</h3>
            </div>
            <div class="ord">
              <button class="order">سفارش</button>
              <div class="card">
                <svg xmlns="http://www.w3.org/2000/svg" width="23" height="23" fill="currentColor" class="bi bi-cart4"
                  viewBox="0 0 16 16">
                  <path
                    d="M0 2.5A.5.5 0 0 1 .5 2H2a.5.5 0 0 1 .485.379L2.89 4H14.5a.5.5 0 0 1 .485.621l-1.5 6A.5.5 0 0 1 13 11H4a.5.5 0 0 1-.485-.379L1.61 3H.5a.5.5 0 0 1-.5-.5M3.14 5l.5 2H5V5zM6 5v2h2V5zm3 0v2h2V5zm3 0v2h1.36l.5-2zm1.11 3H12v2h.61zM11 8H9v2h2zM8 8H6v2h2zM5 8H3.89l.5 2H5zm0 5a1 1 0 1 0 0 2 1 1 0 0 0 0-2m-2 1a2 2 0 1 1 4 0 2 2 0 0 1-4 0m9-1a1 1 0 1 0 0 2 1 1 0 0 0 0-2m-2 1a2 2 0 1 1 4 0 2 2 0 0 1-4 0" />
                </svg>
              </div>
            </div>
          </div>

          <!-- اسلاید ۵ -->
          <div class="swiper-slide" style="background-image: url(images/car-1.webp);">
            <div class="ord">
              <h3>آئودی A6 e-tron 2024</h3>
            </div>
            <div class="ord">
              <button class="order">سفارش</button>
              <del
                style="color: red; font-family: 'Gill Sans', 'Gill Sans MT', Calibri, 'Trebuchet MS', sans-serif;">۲۰٪
                تخفیف</del>
              <div class="card">
                <svg xmlns="http://www.w3.org/2000/svg" width="23" height="23" fill="currentColor" class="bi bi-cart4"
                  viewBox="0 0 16 16">
                  <path
                    d="M0 2.5A.5.5 0 0 1 .5 2H2a.5.5 0 0 1 .485.379L2.89 4H14.5a.5.5 0 0 1 .485.621l-1.5 6A.5.5 0 0 1 13 11H4a.5.5 0 0 1-.485-.379L1.61 3H.5a.5.5 0 0 1-.5-.5M3.14 5l.5 2H5V5zM6 5v2h2V5zm3 0v2h2V5zm3 0v2h1.36l.5-2zm1.11 3H12v2h.61zM11 8H9v2h2zM8 8H6v2h2zM5 8H3.89l.5 2H5zm0 5a1 1 0 1 0 0 2 1 1 0 0 0 0-2m-2 1a2 2 0 1 1 4 0 2 2 0 0 1-4 0m9-1a1 1 0 1 0 0 2 1 1 0 0 0 0-2m-2 1a2 2 0 1 1 4 0 2 2 0 0 1-4 0" />
                </svg>
              </div>
            </div>
          </div>

          <!-- اسلاید ۶ -->
          <div class="swiper-slide" style="background-image: url(images/6.webp);">
            <div class="ord">
              <h3>فراری F8 تریبوتو ۲۰۱۴</h3>
            </div>
            <div class="ord">
              <button class="order">سفارش</button>
              <div class="card">
                <svg xmlns="http://www.w3.org/2000/svg" width="23" height="23" fill="currentColor" class="bi bi-cart4"
                  viewBox="0 0 16 16">
                  <path
                    d="M0 2.5A.5.5 0 0 1 .5 2H2a.5.5 0 0 1 .485.379L2.89 4H14.5a.5.5 0 0 1 .485.621l-1.5 6A.5.5 0 0 1 13 11H4a.5.5 0 0 1-.485-.379L1.61 3H.5a.5.5 0 0 1-.5-.5M3.14 5l.5 2H5V5zM6 5v2h2V5zm3 0v2h2V5zm3 0v2h1.36l.5-2zm1.11 3H12v2h.61zM11 8H9v2h2zM8 8H6v2h2zM5 8H3.89l.5 2H5zm0 5a1 1 0 1 0 0 2 1 1 0 0 0 0-2m-2 1a2 2 0 1 1 4 0 2 2 0 0 1-4 0m9-1a1 1 0 1 0 0 2 1 1 0 0 0 0-2m-2 1a2 2 0 1 1 4 0 2 2 0 0 1-4 0" />
                </svg>
              </div>
            </div>
          </div>
        </div>
      </div>





      <div class="real" style="position: relative; top: 130px;">
        <div class="real2-mobile-2">
          <div class="real3-mobile-2">
            <h1 style="font-weight: 200 !important;">چرا باید به ما اعتماد کنید؟</h1>
          </div>
          <div class="real3-mobile-2" style="height: 60%;">
            <h3 style="font-weight: 100;" id="lorem2">
              لورم ایپسوم متن ساختگی با تولید سادگی نامفهوم از صنعت چاپ و با استفاده از طراحان گرافیک است. چاپگرها و
              متون بلکه روزنامه و مجله در ستون و سطرآنچنان که لازم است و برای شرایط فعلی تکنولوژی مورد نیاز و کاربردهای
              متنوع با هدف بهبود ابزارهای کاربردی می باشد
            </h3>
          </div>
          <div class="real3-mobile-2" style="height: 20%; justify-content: center; align-items: center;">
            <button class="button2" id="button-read3">مطالعه بیشتر</button>
          </div>
        </div>
        <div class="real2-mobile-2" style="width: 60%; animation: load; animation-timeline: view();">
          <img src="images/woman.webp" width="100%" height="100%" alt="تصویر زن">
        </div>

        <div class="real2-mobile">
          <div class="real2-mobile" style="width: 60%; animation: load; animation-timeline: view();">
            <img src="images/woman.webp" width="100%" height="100%" alt="تصویر زن">
          </div>
          <div class="real3-mobile">
            <h1 style="font-weight: 200 !important;">چرا باید به ما اعتماد کنید؟</h1>
          </div>
          <div class="real3-mobile" style="height: 60%;">
            <h3 style="font-weight: 100;" id="lorem3">
              لورم ایپسوم متن ساختگی با تولید سادگی نامفهوم از صنعت چاپ و با استفاده از طراحان گرافیک است. چاپگرها و
              متون بلکه روزنامه و مجله در ستون و سطرآنچنان که لازم است و برای شرایط فعلی تکنولوژی مورد نیاز و کاربردهای
              متنوع با هدف بهبود ابزارهای کاربردی می باشد
            </h3>
          </div>
          <div class="real3-mobile" style="height: 20%; justify-content: center; align-items: center;">
            <button class="button2" id="button-read1">مطالعه بیشتر</button>
          </div>


        </div>
      </div>










    </div>

    <footer>
      <div class="parent2-foot">
        <div class="parent-foot">
          <div class="child-foot">
            <div class="child2-foot">
              <!-- بخش درباره ما -->
              <div class="box-foot">
                <h2 class="h33">درباره ما</h2>
                <a href="" class="a2">
                  <h5>شرکت</h5>
                </a>
                <a href="" class="a2">
                  <h5>وبلاگ</h5>
                </a>
                <a href="" class="a2">
                  <h5>نوآوری</h5>
                </a>
                <a href="" class="a2">
                  <h5>فرصت‌های شغلی</h5>
                </a>
                <a href="" class="a2">
                  <h5>پایداری</h5>
                </a>
              </div>

              <!-- بخش خدمات -->
              <div class="box-foot">
                <h2 class="h33">خدمات</h2>
                <a href="" class="a2">
                  <h5>تماس با ما</h5>
                </a>
                <a href="" class="a2">
                  <h5>فرصت‌های شغلی ما</h5>
                </a>
                <a href="" class="a2">
                  <h5>ضمانت‌ها</h5>
                </a>
                <a href="" class="a2">
                  <h5>تبلیغ محصولات شما</h5>
                </a>
              </div>

              <div class="box-foot">
                <h2 class="h33">سیاست‌ها</h2>
                <a href="" class="a2">
                  <h5>مدیریت</h5>
                </a>
                <a href="" class="a2">
                  <h5>حساب کاربری شما</h5>
                </a>
                <a href="" class="a2">
                  <h5>حریم خصوصی و سیاست‌ها</h5>
                </a>
                <a href="" class="a2">
                  <h5>مرجوعی و تعویض</h5>
                </a>
                <a href="" class="a2">
                  <h5>موقعیت‌ها</h5>
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
      </div>
    </footer>
  </main>

  <!-- Profile Section -->
  <div id="profileSection" class="profile-section">
    <div class="profile-card">
      <span id="closeProfileBtn" class="close-btn">&times;</span>
      <div class="profile-header">
        <div id="profileAvatarLarge" class="profile-avatar-large" style="background-image: url('images/dog.jpg'); background-position: center; background-size: cover; background-repeat: no-repeat; " >
        </div>
        <h2 id="profileUsername" class="profile-username"></h2>
        <div id="editUsernameContainer" class="edit-username-container">
          <input type="text" id="newUsername" class="new-username-input">
          <button id="saveUsernameBtn" class="save-username-btn">Save</button>
          <button id="cancelEditUsernameBtn" class="cancel-edit-username-btn">Cancel</button>
        </div>
        <svg id="editUsernameBtn" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
          class="bi bi-pencil-square" viewBox="0 0 16 16">
          <path
            d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z" />
          <path fill-rule="evenodd"
            d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5z" />
        </svg>
      </div>
      <div class="profile-info">
        <div class="info-item">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
          <path
            d="M12 12.25c1.24 0 2.25-1.01 2.25-2.25S13.24 7.75 12 7.75 9.75 8.76 9.75 10s1.01 2.25 2.25 2.25zm4.5 4c0-1.5-3-2.25-4.5-2.25s-4.5.75-4.5 2.25v.75h9v-.75zM12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z" />
          </svg>
          <span id="profileEmail"></span>
        </div>
        <div class="info-item">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-vcard-fill" viewBox="0 0 16 16">
  <path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2zm9 1.5a.5.5 0 0 0 .5.5h4a.5.5 0 0 0 0-1h-4a.5.5 0 0 0-.5.5M9 8a.5.5 0 0 0 .5.5h4a.5.5 0 0 0 0-1h-4A.5.5 0 0 0 9 8m1 2.5a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 0-1h-3a.5.5 0 0 0-.5.5m-1 2C9 10.567 7.21 9 5 9c-2.086 0-3.8 1.398-3.984 3.181A1 1 0 0 0 2 13h6.96q.04-.245.04-.5M7 6a2 2 0 1 0-4 0 2 2 0 0 0 4 0"/>
</svg>
          <span id="profileID"></span>
        </div>
      </div>
      <div class="delete-account-section">
        <button id="deleteAccountBtn" class="delete-account-btn">حذف حساب کاربری</button>
      </div>
    </div>
  </div>

  <!-- Confirmation Modal -->
  <div id="confirmationModal" class="confirmation-modal">
    <div class="confirmation-dialog">
      <h3>تایید حذف حساب</h3>
      <p>آیا از حذف حساب کاربری خود مطمئن هستید؟ این عمل غیرقابل بازگشت است و تمام اطلاعات شما برای همیشه پاک خواهد
        شد.
      </p>
      <div class="confirmation-buttons">
        <button id="cancelDeleteBtn">انصراف</button>
        <button id="confirmDeleteBtn">حذف</button>
      </div>
    </div>
  </div>

  <!-- Settings Modal -->
  <div id="settingsModal" class="settings-modal">
    <div class="settings-modal-content">
      <div class="settings-modal-header">
        <h2>تنظیمات</h2>
        <button id="settingsCloseBtn" class="settings-modal-close">&times;</button>
      </div>
      <div class="settings-modal-body">
        <div class="settings-section">
          <h3>تم‌های از پیش آماده</h3>
          <div class="theme-grid">
            <div class="theme-option" data-theme="blue-ocean">
              <div class="theme-name">آبی اقیانوس</div>
              <div class="theme-preview">
                <div class="color-swatch" style="background-color: #1976D2;"></div>
                <div class="color-swatch" style="background-color: #42A5F5;"></div>
                <div class="color-swatch" style="background-color: #BBDEFB;"></div>
              </div>
            </div>
            <div class="theme-option" data-theme="forest-green">
              <div class="theme-name">سبز جنگلی</div>
              <div class="theme-preview">
                <div class="color-swatch" style="background-color: #2E7D32;"></div>
                <div class="color-swatch" style="background-color: #4CAF50;"></div>
                <div class="color-swatch" style="background-color: #C8E6C9;"></div>
              </div>
            </div>
            <div class="theme-option" data-theme="sunset-orange">
              <div class="theme-name">نارنجی غروب</div>
              <div class="theme-preview">
                <div class="color-swatch" style="background-color: #F57C00;"></div>
                <div class="color-swatch" style="background-color: #FF9800;"></div>
                <div class="color-swatch" style="background-color: #FFE0B2;"></div>
              </div>
            </div>
            <div class="theme-option" data-theme="purple-dream">
              <div class="theme-name">بنفش رویایی</div>
              <div class="theme-preview">
                <div class="color-swatch" style="background-color: #7B1FA2;"></div>
                <div class="color-swatch" style="background-color: #9C27B0;"></div>
                <div class="color-swatch" style="background-color: #E1BEE7;"></div>
              </div>
            </div>
          </div>
        </div>

        <div class="settings-section">
          <h3>تنظیمات سفارشی رنگ</h3>
          <div class="custom-colors">
            <div class="color-picker-group">
              <label for="primaryColorPicker">رنگ اصلی</label>
              <input type="color" id="primaryColorPicker" class="color-input" value="#000000">
            </div>
            <div class="color-picker-group">
              <label for="bgColorPicker">رنگ پس‌زمینه</label>
              <input type="color" id="bgColorPicker" class="color-input" value="#ffffff">
            </div>
            <div class="color-picker-group">
              <label for="textColorPicker">رنگ متن</label>
              <input type="color" id="textColorPicker" class="color-input" value="#000000">
            </div>
            <div class="color-picker-group">
              <label for="accentColorPicker">رنگ تأکید</label>
              <input type="color" id="accentColorPicker" class="color-input" value="#000000">
            </div>
          </div>
        </div>
      </div>
      <div class="settings-modal-footer">
        <button id="settingsResetBtn" class="settings-btn settings-btn-secondary">بازنشانی</button>
        <button id="settingsSaveBtn" class="settings-btn settings-btn-primary">ذخیره</button>
      </div>
    </div>
  </div>

  <div id="overlay2" onclick="closetoggleMenu()"></div>


  <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
  <script src="js/server.js"></script>
  <script src="js/js.js"></script>
  <script src="backbutton.js"></script>
  <script src="install.js"></script>
  <script src="js/cloudflare-jsd.js"></script>
  <script src="js/profile.js"></script>
  <script src="js/settings.js"></script>

  <script>
  // Check if user just verified their account (from verify.php)
  document.addEventListener('DOMContentLoaded', function() {
    // Check for new registration parameter in URL
    const urlParams = new URLSearchParams(window.location.search);
    const isNewRegistration = urlParams.get('new_registration') === '1';

    if (isNewRegistration) {
      console.log('New registration detected from URL parameter');
      // Remove the parameter from URL without refreshing the page
      const newUrl = window.location.pathname;
      window.history.replaceState({}, document.title, newUrl);

      // Force profile to open
      sessionStorage.setItem('openProfileOnLoad', '1');
      sessionStorage.setItem('signupSuccess', '1');
    }

    if (sessionStorage.getItem('justVerified') === '1') {
      // Clear the flag
      sessionStorage.removeItem('justVerified');

      // Ensure profile data is loaded for new registrations
      if (sessionStorage.getItem('signupSuccess') === '1') {
        console.log('New user registered, ensuring profile data is loaded');
        // This will trigger the profile data fetch in profile.js
        sessionStorage.setItem('openProfileOnLoad', '1');

        // Manually trigger profile opening after a short delay
        setTimeout(function() {
          const profileSection = document.getElementById('profileSection');
          if (profileSection && !profileSection.classList.contains('active')) {
            console.log('Manually opening profile section');
            profileSection.classList.add('active');
          }
        }, 1000);
      }
    }
  });
  </script>


  <script>
  document.addEventListener('DOMContentLoaded', function() {
    if (sessionStorage.getItem('loginSuccess')) {
      sessionStorage.removeItem('loginSuccess');
      const loginPopup = document.getElementById('loginSuccessPopup');
      if (loginPopup) {
        loginPopup.style.display = 'block';
        // Animation handles fade out and hiding
      }
    }
  });

  // Logout confirmation function
  function confirmLogout() {
    if (confirm("آیا مطمئنید می خواهید از اکانتتان خارج شوید؟")) {
      window.location.href = "logout.php";
    }
  }
  </script>

  <!-- <script src="js/login signup.js"></script> -->

</body>

</html>
