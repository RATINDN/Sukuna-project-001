const menuBox = document.getElementById('menu-box'); 
const menuBox2 = document.getElementById('overlay2');  
const li1 = document.getElementById("li1");
const anotherButton = document.getElementById("move"); 
const bi_down = document.getElementById("bi-down");



// let ismenu = true;

// function openMenu() {
//   menuBox.style.display = "flex";
//   menuBox2.style.display = "flex";
//   menuBox2.style.transition = "0.5s ease-in-out";
//   menuBox2.style.height = "100%";
//   body.style.overflow = "hidden";
//   ismenu = false;
// }

// function closeMenu() {
//   menuBox.style.display = "none";
//   menuBox2.style.transition = "0.5s ease-in-out";
//   menuBox2.style.height = "0";
//   body.style.overflow = "visible";
//   ismenu = true;
// }




// li1.addEventListener('click', openMenu);
// anotherButton.addEventListener('click', openMenu); // Add event listener to the new button

// li1.addEventListener('dblclick', closeMenu);
// anotherButton.addEventListener('dblclick', closeMenu);

let ismenu = true;

function toggleMenu() {

  menuBox.style.display = "flex";
  menuBox2.style.display = "flex";
  menuBox2.style.transition = "0.5s ease-in-out";
  menuBox2.style.height = "100%";
  body.style.overflow = "hidden";
  bi_down.style.transform = "rotate(180deg)";
  li1.style.backgroundColor = "var(--hover-color-li) !important";
  li1.style.borderBottom = li1;

  if (ismenu) {
    ismenu = false;
  } else {
    menuBox.style.display = "none";
    menuBox2.style.transition = "0.5s ease-in-out";
    menuBox2.style.height = "0";
    body.style.overflow = "visible";
    ismenu = true;
    bi_down.style.transform = "rotate(0deg)";
    li1.style.backgroundColor = "rgba(0, 0, 0, 0)";

  }
}

li1.addEventListener('click', toggleMenu);
anotherButton.addEventListener('click', toggleMenu); // Add event listener to the new button
  

   
// window.addEventListener('scroll', function() {
//   const scrollBar = document.getElementById("scroll");
//   const scrollTop = document.documentElement.scrollTop;
//   const scrollHeight = document.documentElement.scrollHeight;
//   const clientHeight = document.documentElement.clientHeight;
//   const scrollPercentage = scrollTop / (scrollHeight - clientHeight);
  
  // Use transform for better performance
//   scrollBar.style.transform = `scaleX(${scrollPercentage})`;
// });


    /* =========================================
   نوار پیشرفت اسکرول (Scroll Progress Bar)
   ========================================= */
window.addEventListener('scroll', () => {
  // ۱. مقدار اسکرول شده از بالا
  const scrollTop = document.documentElement.scrollTop || document.body.scrollTop;
  
  // ۲. کل ارتفاع قابل اسکرول صفحه (کل ارتفاع - ارتفاع پنجره مرورگر)
  const scrollHeight = document.documentElement.scrollHeight - document.documentElement.clientHeight;
  
  // ۳. محاسبه درصد (چند درصد پایین رفتیم؟)
  const scrolled = (scrollTop / scrollHeight) * 100;
  
  // ۴. اعمال به عرض نوار
  const progressBar =  document.getElementById("scroll-progress");
  if (progressBar) {
      progressBar.style.width = scrolled + "%";
  }
});
    



  // function closetoggleMenu(){
  // menuBox.style.display ="none";
  // menuBox2.style.transition ="0.5s ease-in-out";
  // menuBox2.style.height ="0";
  // body.style.overflow ="visible";


  //   }
  
  
 






    
  




const menu = document.getElementById("myMenu")
const overlay = document.getElementById('overlay')



 
function openNav() {
  menu.style.width = "40%";
  overlay.style.display = "flex"

}

function closeNav() {
  menu.style.width = "0%";
  overlay.style.display = 'none'




}




const closee = document.getElementById("close")
const openmenu = document.getElementById("openmenu")
const body = document.body;  
const mobile = document.getElementById('parent-mobile' );  
const select = document.getElementById('brand-filter-mobile' );
closee.addEventListener('click', closeNav2);






function opencar() {
  openmenu.style.height = "100vh";
  openmenu.style.width = "100%";
  mobile.style.height = "300px";
  mobile.style.width = "90%";
  select.style.display = "flex";
  select.style.paddingRight = "10px";
  

  body.style.overflow= " hidden ";




}

function closeNav2() {
  openmenu.style.height = "0%";
  openmenu.style.width = "0%";
  mobile.style.height = "0";
  mobile.style.width = "0";
  select.style.display = "none";

  body.style.overflow = "  visible";


}

const mode = document.getElementById('mode' );  
const mode2 = document.getElementById('mode2' );  



// function darkmode(){
//   const element = document.body;
//   const nav = document.getElementById('nav');
//   const bow = document.getElementById('bow');
//   const reg = document.getElementById('register');
//   const scroll2 = document.getElementById('scroll');
//   const button1 = document.getElementById("button-read1");
//   const button2 = document.getElementById("button-read2");
//   const button3 = document.getElementById("button-read3");
//   const moon = document.getElementById("moon");
//   const sun = document.getElementById("sun");
//   const li1 = document.getElementById("li1");
//   const btn = document.getElementById("closebtn");
//   const veil2 = document.getElementById("veil2");
//   const lb1 = document.getElementById("lb1");
//   const boxing1 = document.getElementById("boxing1");
//   const boxing2 = document.getElementById("boxing2");
//   const boxing3 = document.getElementById("boxing3");
//   const boxing4 = document.getElementById("boxing4");
//   const boxing5 = document.getElementById("boxing5");
//   const boxing6 = document.getElementById("boxing6");
//   const boxing7 = document.getElementById("boxing7");
//   const search_box = document.getElementById("search-box")
//   const search=document.getElementById("search")
//   const search_box_2 = document.getElementById("search-box-2")
//   const search_2 = document.querySelector(".search-input")









//   sun.classList.toggle("const-sun");
//   moon.classList.toggle("const-moon");
// element.classList.toggle("dark-mode");
// nav.classList.toggle("dark-mode");
// bow.classList.toggle("const-bow");
// reg.classList.toggle("const-register");
// scroll2.classList.toggle("const-scroll");
// button1.classList.toggle("const-button2");
// button2.classList.toggle("const-button2");
// button3.classList.toggle("const-button2");
// li1.classList.toggle("const-li1");
// btn.classList.toggle("const-closebtn");
// veil2.classList.toggle("const-veil2");
// lb1.classList.toggle("const-lb");
// menu.classList.toggle("const-menu");
// openmenu.classList.toggle("const-open");
// boxing1.classList.toggle("const-box");
// boxing2.classList.toggle("const-box");
// boxing3.classList.toggle("const-box");
// boxing4.classList.toggle("const-box");
// boxing5.classList.toggle("const-box");
// boxing6.classList.toggle("const-box");
// boxing7.classList.toggle("const-box");
// search_box.classList.toggle("const-search")
// search.classList.toggle("const-search-mode")
// search_box_2.classList.toggle("const-search")
// search_2.classList.toggle("const-search-mode")

   















// }


// function darkmode() {
//   const element = document.body;
//   const brandFilter = document.getElementById('brand-filter');
//   const brandFilter_mobile = document.getElementById('brand-filter-mobile');
// const ios = document.getElementById('ios');

//   const myMenu = document.getElementById('myMenu');
//   const nav = document.getElementById('nav');
//   const bow = document.getElementById('bow');
//   const reg = document.getElementById('register');
//   const scroll2 = document.getElementById('scroll');
//   const button1 = document.getElementById("button-read1");
//   const button2 = document.getElementById("button-read2");
//   const button3 = document.getElementById("button-read3");
//   const moon = document.getElementById("moon");
//   const sun = document.getElementById("sun");
//   const li1 = document.getElementById("li1");
//   const btn = document.getElementById("closebtn");
//   const veil2 = document.getElementById("veil2");
//   const lb1 = document.querySelector(".lb");
//   const boxing1 = document.getElementById("boxing1");
//   const boxing2 = document.getElementById("boxing2");
//   const boxing3 = document.getElementById("boxing3");
//   const boxing4 = document.getElementById("boxing4");
//   const boxing5 = document.getElementById("boxing5");
//   const boxing6 = document.getElementById("boxing6");
//   const boxing7 = document.getElementById("boxing7");
//   const search_box = document.getElementById("search-box");
//   const search = document.getElementById("search");
//   const search_box_2 = document.getElementById("search-box-2");
//   const search_2 = document.querySelector(".search-input");
//   const openmenu = document.getElementById("openmenu"); // Mobile menu

//   const isDark = element.classList.toggle("dark-mode");
//   localStorage.setItem("darkMode", isDark);
//   brandFilter_mobile.classList.toggle("const-select");
//   brandFilter.classList.toggle("const-select");
//   ios.classList.toggle("const-ios");
//   sun.classList.toggle("const-sun");
//   moon.classList.toggle("const-moon");
//   nav.classList.toggle("dark-mode");
//   bow.classList.toggle("const-bow");
//   reg.classList.toggle("const-register");
//   scroll2.classList.toggle("const-scroll");
//   button1.classList.toggle("const-button2");
//   button2.classList.toggle("const-button2");
//   button3.classList.toggle("const-button2");
//   li1.classList.toggle("const-li1");
//   btn.classList.toggle("const-closebtn");
//   veil2.classList.toggle("const-veil2");
//   lb1.classList.toggle("const-lb");
//   boxing1.classList.toggle("const-box");
//   boxing2.classList.toggle("const-box");
//   boxing3.classList.toggle("const-box");
//   boxing4.classList.toggle("const-box");
//   boxing5.classList.toggle("const-box");
//   boxing6.classList.toggle("const-box");
//   boxing7.classList.toggle("const-box");
//   search_box.classList.toggle("const-search");
//   search.classList.toggle("const-search-mode");
//   search_box_2.classList.toggle("const-search");
//   search_2.classList.toggle("const-search-mode");
//   openmenu.classList.toggle("const-menu"); // Toggle dark mode for mobile menu
//   myMenu.classList.toggle("dark-mode");
// }

// window.addEventListener('load', () => {
//   const isDark = localStorage.getItem("darkMode") === "true";
//   const element = document.body;
//   const brandFilter = document.getElementById('brand-filter');
//   const brandFilter_mobile = document.getElementById('brand-filter-mobile');
// const ios = document.getElementById('ios');
//   const nav = document.getElementById('nav');
//   const bow = document.getElementById('bow');
//   const reg = document.getElementById('register');
//   const scroll2 = document.getElementById('scroll');
//   const button1 = document.getElementById("button-read1");
//   const button2 = document.getElementById("button-read2");
//   const button3 = document.getElementById("button-read3");
//   const moon = document.getElementById("moon");
//   const sun = document.getElementById("sun");
//   const li1 = document.getElementById("li1");
//   const btn = document.getElementById("closebtn");
//   const veil2 = document.getElementById("veil2");
//   const lb1 = document.querySelector(".lb");
//   const boxing1 = document.getElementById("boxing1");
//   const boxing2 = document.getElementById("boxing2");
//   const boxing3 = document.getElementById("boxing3");
//   const boxing4 = document.getElementById("boxing4");
//   const boxing5 = document.getElementById("boxing5");
//   const boxing6 = document.getElementById("boxing6");
//   const boxing7 = document.getElementById("boxing7");
//   const search_box = document.getElementById("search-box");
//   const search = document.getElementById("search");
//   const search_box_2 = document.getElementById("search-box-2");
//   const search_2 = document.querySelector(".search-input");
//   const openmenu = document.getElementById("openmenu"); // Mobile menu
//   const myMenu = document.getElementById('myMenu');

//   if (isDark) {
//     brandFilter_mobile.classList.add("const-select");
//     brandFilter.classList.add("const-select");
//     element.classList.add("dark-mode");
//     ios.classList.add("const-ios");
//     sun.classList.add("const-sun");
//     moon.classList.add("const-moon");
//     nav.classList.add("dark-mode");
//     bow.classList.add("const-bow");
//     reg.classList.add("const-register");
//     scroll2.classList.add("const-scroll");
//     button1.classList.add("const-button2");
//     button2.classList.add("const-button2");
//     button3.classList.add("const-button2");
//     li1.classList.add("const-li1");
//     btn.classList.add("const-closebtn");
//     veil2.classList.add("const-veil2");
//     lb1.classList.add("const-lb");
//     boxing1.classList.add("const-box");
//     boxing2.classList.add("const-box");
//     boxing3.classList.add("const-box");
//     boxing4.classList.add("const-box");
//     boxing5.classList.add("const-box");
//     boxing6.classList.add("const-box");
//     boxing7.classList.add("const-box");
//     search_box.classList.add("const-search");
//     search.classList.add("const-search-mode");
//     search_box_2.classList.add("const-search");
//     search_2.classList.add("const-search-mode");
//     openmenu.classList.add("const-menu"); // Apply dark mode to mobile menu
//     myMenu.classList.add("dark-mode");
//   } else {
//     brandFilter_mobile.classList.remove("const-select");
//     brandFilter.classList.remove("const-select");
//     element.classList.remove("dark-mode");
//     ios.classList.remove("const-ios");
//     sun.classList.remove("const-sun");
//     moon.classList.remove("const-moon");
//     nav.classList.remove("dark-mode");
//     bow.classList.remove("const-bow");
//     reg.classList.remove("const-register");
//     scroll2.classList.remove("const-scroll");
//     button1.classList.remove("const-button2");
//     button2.classList.remove("const-button2");
//     button3.classList.remove("const-button2");
//     li1.classList.remove("const-li1");
//     btn.classList.remove("const-closebtn");
//     veil2.classList.remove("const-veil2");
//     lb1.classList.remove("const-lb");
//     boxing1.classList.remove("const-box");
//     boxing2.classList.remove("const-box");
//     boxing3.classList.remove("const-box");
//     boxing4.classList.remove("const-box");
//     boxing5.classList.remove("const-box");
//     boxing6.classList.remove("const-box");
//     boxing7.classList.remove("const-box");
//     search_box.classList.remove("const-search");
//     search.classList.remove("const-search-mode");
//     search_box_2.classList.remove("const-search");
//     search_2.classList.remove("const-search-mode");
//     openmenu.classList.remove("const-menu"); // Remove dark mode from mobile menu
//     myMenu.classList.remove("dark-mode");
//   }
// });

function darkmode() {
  const body = document.body;
  const moon = document.getElementById("moon");
  const sun = document.getElementById("sun");

  const isDark = body.classList.toggle("darkmode");
  localStorage.setItem("darkMode", isDark);

  if (moon && sun) {
    if (isDark) {
      moon.style.display = "none";
      sun.style.display = "block";
    } else {
      moon.style.display = "block";
      sun.style.display = "none";
    }
  }
}

window.addEventListener('load', () => {
  const isDark = localStorage.getItem("darkMode") === "true";
  const body = document.body;
  const moon = document.getElementById("moon");
  const sun = document.getElementById("sun");

  if (isDark) {
    body.classList.add("darkmode");
    if (moon && sun) {
      moon.style.display = "none";
      sun.style.display = "block";
    }
  } else {
    body.classList.remove("darkmode");
    if (moon && sun) {
      moon.style.display = "block";
      sun.style.display = "none";
    }
  }
});


var swiper = new Swiper(".mySwiper", {
  slidesPerView: "auto",
  centeredSlides: true,
  grabCursor: true,
  loop: true,

  spaceBetween: 30,

  autoplay: {
    delay: 2500,
    disableOnInteraction: false,
  },
  pagination: {
    el: ".swiper-pagination",
    clickable: true,
  },


});






// window.addEventListener("load", function() {
//   const loader = document.getElementById('container-load');
//   loader.style.opacity = '0';
//   setTimeout(() => {
//     loader.style.display = 'none';
//   }, 500); 
// });


// Add this function at the end of your js.js file
function hideLoader() {
  const loader = document.getElementById('container-load');
  if (loader) {
    loader.style.opacity = '0';
    setTimeout(() => {
      loader.style.display = 'none';
    }, 500);
  }
}

// Replace your existing load event listener with this:
window.addEventListener("load", function() {
  // Set a maximum timeout for the loader
  const loaderTimeout = setTimeout(() => {
    hideLoader();
  }, 5000); // Force hide after 5 seconds if stuck

  // Try to hide loader normally
  hideLoader();
  
  // Clear timeout if loader hides normally
  clearTimeout(loaderTimeout);
});

// Add error handling for resources
window.addEventListener('error', function(e) {
  if (e.target.tagName === 'IMG' || e.target.tagName === 'SCRIPT' || e.target.tagName === 'LINK') {
    // If a resource fails to load, still hide the loader
    hideLoader();
  }
}, true);

// Add backup hiding mechanism
document.addEventListener('DOMContentLoaded', function() {
  setTimeout(hideLoader, 3000); // Backup timeout after DOM loads
});



 


// document.addEventListener('DOMContentLoaded', () => {
//   const searchInput = document.getElementById('search');
//   const boxes = document.querySelectorAll('.box');

//   searchInput.addEventListener('input', () => {
//     const query = searchInput.value.toLowerCase();

//     boxes.forEach(box => {
//       const title = box.getAttribute('data-title').toLowerCase();
//       if (title.includes(query)) {
//         box.classList.remove('hide');
//       } else { 
//         box.classList.add('hide');
        
//       }
//     });
//   });
// });
document.addEventListener('DOMContentLoaded', () => {
  // const brandFilter = document.getElementById('brand-filter');
  // const searchInput = document.getElementById('search');
  // const boxes = document.querySelectorAll('.box');
  

  // function filterCars() {
  //   const query = searchInput.value.toLowerCase();
  //   const selectedBrand = brandFilter.value.toLowerCase();

  //   boxes.forEach(box => {
  //     const title = box.getAttribute('data-title').toLowerCase();
  //     const matchesQuery = title.includes(query);
  //     const matchesBrand = selectedBrand === "" || title.includes(selectedBrand);

  //     if (matchesQuery && matchesBrand) {
  //       box.classList.remove('hide');
  //     } else {
  //       box.classList.add('hide');
  //     }
  //   });
  // }

  // // لاجیک فیلتر کردن محصولات
  // function filterCars() {
  //   const query = searchInput.value.toLowerCase();
  //   const selectedBrand = brandFilter.value; // مقدار انتخاب شده از منو

  //   boxes.forEach(box => {
  //     const title = box.getAttribute('data-title').toLowerCase();
  //     const carBrand = box.getAttribute('data-brand'); // برند واقعی از دیتابیس

  //     const matchesQuery = title.includes(query);
  //     // اگر "همه برندها" بود یا برند ماشین با انتخاب کاربر یکی بود
  //     const matchesBrand = selectedBrand === "" || carBrand === selectedBrand;

      // if (matchesQuery && matchesBrand) {
      //   box.classList.remove('hide');
      // } else {
      //   box.classList.add('hide');
      // }
  //   });
  // }

  // searchInput.addEventListener('input', filterCars);
  // brandFilter.addEventListener('change', filterCars);


  // لاجیک فیلتر کردن محصولات (اصلاح شده)
  function filterCars() {
    // گرفتن مقدار از سرچ باکس فعال (موبایل یا دسکتاپ)
    const queryDesktop = document.getElementById('search').value.toLowerCase();
    const queryMobile = document.getElementById('search-mobile').value.toLowerCase();
    const query = queryDesktop || queryMobile; // هر کدام پر بود

    // گرفتن برند از سلکت باکس فعال
    const brandDesktop = document.getElementById('brand-filter').value;
    const brandMobile = document.getElementById('brand-filter-mobile').value;
    const selectedBrand = brandDesktop || brandMobile;

    // انتخاب همه باکس‌ها (هم موبایل هم دسکتاپ)
    const allBoxes = document.querySelectorAll('.box, .box-mobile');

    allBoxes.forEach(box => {
      const title = box.getAttribute('data-title').toLowerCase();
      const carBrand = box.getAttribute('data-brand'); // برند محصول از دیتابیس

      const matchesQuery = title.includes(query);
      const matchesBrand = selectedBrand === "" || carBrand === selectedBrand;

      if (matchesQuery && matchesBrand) {
        box.classList.remove('hide');
      } else {
        box.classList.add('hide');
      }
    });
  }

  // اتصال ایونت‌ها
  document.getElementById('search').addEventListener('input', filterCars);
  document.getElementById('search-mobile').addEventListener('input', filterCars);
  document.getElementById('brand-filter').addEventListener('change', filterCars);
  document.getElementById('brand-filter-mobile').addEventListener('change', filterCars);
});

// document.addEventListener('DOMContentLoaded', () => {
//   const searchInput = document.getElementById('search-mobile');
//   const brandFilter = document.getElementById('brand-filter-mobile');
//   const boxes = document.querySelectorAll('.box-mobile');

  // function filterCars() {
  //   const query = searchInput.value.toLowerCase();
  //   const selectedBrand = brandFilter.value.toLowerCase();

  //   boxes.forEach(box => {
  //     const title = box.getAttribute('data-title').toLowerCase();
  //     const matchesQuery = title.includes(query);
  //     const matchesBrand = selectedBrand === "" || title.includes(selectedBrand);

  //     if (matchesQuery && matchesBrand) {
  //       box.classList.remove('hide');
  //     } else {
  //       box.classList.add('hide');
  //     }
  //   });
  // }

  // لاجیک فیلتر کردن محصولات
//   function filterCars() {
//     const query = searchInput.value.toLowerCase();
//     const selectedBrand = brandFilter.value; // مقدار انتخاب شده از منو

//     boxes.forEach(box => {
//       const title = box.getAttribute('data-title').toLowerCase();
//       const carBrand = box.getAttribute('data-brand'); // برند واقعی از دیتابیس

//       const matchesQuery = title.includes(query);
//       // اگر "همه برندها" بود یا برند ماشین با انتخاب کاربر یکی بود
//       const matchesBrand = selectedBrand === "" || carBrand === selectedBrand;

//       if (matchesQuery && matchesBrand) {
//         box.classList.remove('hide');
//       } else {
//         box.classList.add('hide');
//       }
//     });
//   }

//   searchInput.addEventListener('input', filterCars);
//   brandFilter.addEventListener('change', filterCars);
// });







window.addEventListener('scroll', function() {
  const pageUpButton = document.querySelector('.page-up');
  if (window.scrollY > 150) {   
    pageUpButton.style.width = '50px';
  } else {
    pageUpButton.style.width = '0';
  }
});

document.querySelector('.page-up').addEventListener('click', function() {
  window.scrollTo({
    top: 0,
    behavior: 'smooth'    
  });
});

document.addEventListener('DOMContentLoaded', function() {
  const lorem = document.getElementById("lorem");
  const buttonRead2 = document.getElementById("button-read2");
  const defaultText = lorem.innerHTML; // ذخیره متن پیش‌فرض
  let isDefault = true; // وضعیت پیش‌فرض

  if (buttonRead2) {
    buttonRead2.addEventListener('click', function() {
      if (isDefault) {
        lorem.classList.add('hidden');
        setTimeout(() => {
          lorem.innerHTML += " ستون و سطرآنچنان که لازم است و برای شرایط فعلی تکنولوژی مورد نیاز و کاربردهای متنوع با هدف بهبود ابزارهای کاربردی";
          lorem.classList.remove('hidden');
        }, 500);
        buttonRead2.textContent = "بستن"; // تغییر متن دکمه به "بستن"
      } else {
        lorem.classList.add('hidden');
        setTimeout(() => {
          lorem.innerHTML = defaultText; // بازگرداندن به متن پیش‌فرض
          lorem.classList.remove('hidden');
        }, 500);
        buttonRead2.textContent = "مطالعه بیشتر"; // تغییر متن دکمه به "مطالعه بیشتر"
      }
      isDefault = !isDefault; // تغییر وضعیت
    });
  }
});

document.addEventListener('DOMContentLoaded', function() {
  const lorem = document.getElementById("lorem2");
  const buttonRead3 = document.getElementById("button-read3");
  const defaultText = lorem.innerHTML; // ذخیره متن پیش‌فرض
  let isDefault = true; // وضعیت پیش‌فرض

  if (buttonRead3) {
    buttonRead3.addEventListener('click', function() {
      if (isDefault) {
        lorem.classList.add('hidden');
        setTimeout(() => {
          lorem.innerHTML += " ستون و سطرآنچنان که لازم است و برای شرایط فعلی تکنولوژی مورد نیاز و کاربردهای متنوع با هدف بهبود ابزارهای کاربردی";
          lorem.classList.remove('hidden');
        }, 500);
        buttonRead3.textContent = "بستن"; // تغییر متن دکمه به "بستن"
      } else {
        lorem.classList.add('hidden');
        setTimeout(() => {
          lorem.innerHTML = defaultText; // بازگرداندن به متن پیش‌فرض
          lorem.classList.remove('hidden');
        }, 500);
        buttonRead3.textContent = "مطالعه بیشتر"; // تغییر متن دکمه به "مطالعه بیشتر"
      }
      isDefault = !isDefault; // تغییر وضعیت
    });
  }
});


document.addEventListener('DOMContentLoaded', function() {
  const lorem = document.getElementById("lorem3");
  const buttonRead1 = document.getElementById("button-read1");
  const defaultText = lorem.innerHTML; // ذخیره متن پیش‌فرض
  let isDefault = true; // وضعیت پیش‌فرض

  if (buttonRead1) {
    buttonRead1.addEventListener('click', function() {
      if (isDefault) {
        lorem.classList.add('hidden');
        setTimeout(() => {
          lorem.innerHTML += " ستون و سطرآنچنان که لازم است و برای شرایط فعلی تکنولوژی مورد نیاز و کاربردهای متنوع با هدف بهبود ابزارهای کاربردی";
          lorem.classList.remove('hidden');
        }, 500);
        buttonRead1.textContent = "بستن"; // تغییر متن دکمه به "بستن"
      } else {
        lorem.classList.add('hidden');
        setTimeout(() => {
          lorem.innerHTML = defaultText; // بازگرداندن به متن پیش‌فرض
          lorem.classList.remove('hidden');
        }, 500);
        buttonRead1.textContent = "مطالعه بیشتر"; // تغییر متن دکمه به "مطالعه بیشتر"
      }
      isDefault = !isDefault; // تغییر وضعیت
    });
  }
});




const faviconLink = document.createElement('link');
faviconLink.rel = 'icon';
faviconLink.href = 'images/favicon (1).ico'; 
faviconLink.type = 'image/x-icon';

document.head.appendChild(faviconLink);




// alert("Welcome to my shop" )


// Profile avatar dropdown functionality (simplified & readable)
document.addEventListener('DOMContentLoaded', function() {
  // Elements
  const avatar = document.getElementById('profileAvatar');
  const dropdown = document.getElementById('profileDropdown');
  const userInitial = document.getElementById('userInitial');
  const dropdownUserInitial = document.getElementById('dropdownUserInitial');
  const dropdownUsername = document.getElementById('dropdownUsername');

// Use a fixed animation duration in milliseconds
const animMs = 300; // 300ms = 0.3 seconds

  // Only run if user is logged in and elements exist
  if (!(avatar && dropdown && typeof userName !== 'undefined' && userName)) return;

  // Set username and initials
  const initial = userName.charAt(0).toUpperCase();
  if (dropdownUsername) dropdownUsername.textContent = userName;
  if (userInitial) userInitial.textContent = initial;
  if (dropdownUserInitial) dropdownUserInitial.textContent = initial;

  // Set avatar color
  const color = (typeof avatarColor !== 'undefined' && avatarColor)
      ? avatarColor
      : [
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
      ][userName.charCodeAt(0) % 20];
  avatar.style.backgroundColor = color;
  if (dropdownUserInitial && dropdownUserInitial.parentElement)
      dropdownUserInitial.parentElement.style.backgroundColor = color;

  // Hide dropdown initially
  dropdown.style.display = 'none';

  // Toggle dropdown on avatar click
  avatar.addEventListener('click', function(e) {
      e.preventDefault();
      e.stopPropagation();
      avatar.classList.add('active');
      setTimeout(() => avatar.classList.remove('active'), animMs);

      if (dropdown.style.display === 'none') {
          dropdown.classList.add('show-dropdown');
          dropdown.style.display = 'block';
      } else {
          hideDropdown();
      }
  });

  // Hide dropdown when clicking outside
  document.addEventListener('click', function(e) {
      if (
          dropdown.style.display === 'block' &&
          !avatar.contains(e.target) &&
          !dropdown.contains(e.target)
      ) {
          hideDropdown();
      }
  });

  // Hide dropdown with animation
  function hideDropdown() {
      dropdown.classList.remove('show-dropdown');
      dropdown.classList.add('hide-dropdown');
      setTimeout(() => {
          if (!dropdown.classList.contains('show-dropdown')) {
              dropdown.style.display = 'none';
              dropdown.classList.remove('hide-dropdown');
          }
      }, animMs);
  }
});

document.addEventListener('DOMContentLoaded', function() {
  
  // // دیتابیس ماشین‌ها
  // const carDatabase = {
  //   'آئودی A6 e-tron 2024': { hp: '469 hp', accel: '3.9s', engine: 'Electric' },
  //   'لامبورگینی آونتادور ۲۰۱۱': { hp: '700 hp', accel: '2.9s', engine: 'V12' },
  //   'رولزرویس فانتوم ۲۰۱۷': { hp: '563 hp', accel: '5.1s', engine: 'V12' },
  //   'فراری لافراری ۲۰۱۳': { hp: '950 hp', accel: '2.4s', engine: 'Hybrid' },
  //   'رنج روور ۲۰۲۳': { hp: '523 hp', accel: '4.4s', engine: 'V8' },
  //   'مرسدس بنز S-Class 2021': { hp: '496 hp', accel: '4.4s', engine: 'V8' },
  //   'فراری F8 تریبوتو ۲۰۱۴': { hp: '710 hp', accel: '2.9s', engine: 'V8 Turbo' },
  //   '۲۰۱۳ فراری f8 تروبیتو': { hp: '710 hp', accel: '2.9s', engine: 'V8 Turbo' }
  // };

  // =========================================
  // تنظیمات امضا (Canvas)
  // =========================================
  const canvas = document.getElementById('signaturePad');
  const ctx = canvas.getContext('2d');
  let isDrawing = false;
  let isSignatureEmpty = true;

  function resizeCanvas() {
      const ratio = Math.max(window.devicePixelRatio || 1, 1);
      canvas.width = canvas.offsetWidth * ratio;
      canvas.height = canvas.offsetHeight * ratio;
      ctx.scale(ratio, ratio);
      isSignatureEmpty = true;
  }

  function startDraw(e) {
      isDrawing = true;
      ctx.beginPath();
      ctx.lineWidth = 2;
      ctx.lineCap = 'round';
      ctx.strokeStyle = '#000';
      const { x, y } = getPos(e);
      ctx.moveTo(x, y);
      
      // پاک کردن خطای امضا به محض شروع
      const sigError = document.getElementById('sig-error');
      const sigContainer = document.querySelector('.canvas-container');
      if(sigError) sigError.style.display = 'none';
      if(sigContainer) sigContainer.classList.remove('invalid');
  }

  function draw(e) {
      if (!isDrawing) return;
      e.preventDefault();
      const { x, y } = getPos(e);
      ctx.lineTo(x, y);
      ctx.stroke();
      isSignatureEmpty = false;
  }

  function endDraw() { isDrawing = false; }

  function getPos(e) {
      const rect = canvas.getBoundingClientRect();
      const clientX = e.touches ? e.touches[0].clientX : e.clientX;
      const clientY = e.touches ? e.touches[0].clientY : e.clientY;
      return { x: clientX - rect.left, y: clientY - rect.top };
  }

  canvas.addEventListener('mousedown', startDraw);
  canvas.addEventListener('mousemove', draw);
  canvas.addEventListener('mouseup', endDraw);
  canvas.addEventListener('touchstart', startDraw, {passive: false});
  canvas.addEventListener('touchmove', draw, {passive: false});
  canvas.addEventListener('touchend', endDraw);

  document.getElementById('clearSignBtn').addEventListener('click', () => {
      ctx.clearRect(0, 0, canvas.width, canvas.height);
      isSignatureEmpty = true;
  });

  // ---------------------------------------------------------

  let contractData = { name: '', price: '', color: 'مشکی' };

  // باز کردن مودال
// کلمه .button را حذف کردیم
// --- بخش مدیریت کلیک روی محصولات و باز شدن مودال ---
// ======================================================
// سیستم هوشمند و داینامیک برای نمایش جزئیات فنی محصول
// ======================================================
// ======================================================
// سیستم هوشمند نمایش جزئیات و کنترل موجودی رنگ‌ها
// ======================================================
// ======================================================
// سیستم نهایی و هوشمند کنترل موجودی (نسخه ضد باگ)
// ======================================================
// ======================================================
// سیستم نهایی باز کردن مودال و ساخت رنگ‌ها (نسخه سالم)
// ======================================================
// document.addEventListener('click', function (e) {
//   // 1. پیدا کردن باکس محصول
//   const container = e.target.closest('.box, .box-mobile, .swiper-slide, .wishlist-card');
//   if (!container) return;

//   // 2. بررسی لاگین
//   if (typeof isLoggedIn !== 'undefined' && !isLoggedIn) {
//       alert("لطفا ابتدا وارد حساب کاربری شوید.");
//       window.location.href = "login.php";
//       return;
//   }

//   // 3. دریافت اطلاعات از HTML
//   const id = container.getAttribute('data-id');
//   const title = container.getAttribute('data-title');
//   const rawPrice = container.getAttribute('data-price') || '0';
//   const oldPrice = container.getAttribute('data-old-price');
//   const imgSrc = container.getAttribute('data-image');
//   const engineSound = container.getAttribute('data-sound');
  
//   // مشخصات فنی
//   const hp = container.getAttribute('data-hp');
//   const accel = container.getAttribute('data-accel');
//   const engine = container.getAttribute('data-engine');
  
//   // دریافت JSON رنگ‌ها (مهمترین بخش)
//   const colorsJSON = container.getAttribute('data-colors') || '{}';

//   if (!title) return;

//   // 4. نمایش اطلاعات در مودال
//   const formattedPrice = Number(rawPrice).toLocaleString();
//   let priceHTML = (oldPrice && oldPrice !== "null" && oldPrice !== "") 
//       ? `<del style="color:red; font-size:0.8em;">${Number(oldPrice).toLocaleString()} تومان</del> ${formattedPrice} تومان`
//       : `${formattedPrice} تومان`;

//   document.getElementById('modalCarName').innerText = title;
//   document.getElementById('modalCarPrice').innerHTML = priceHTML;
//   document.getElementById('modalCarImage').src = imgSrc;
//   document.getElementById('specHP').innerText = (hp && hp !== '---') ? hp : '---';
//   document.getElementById('specAccel').innerText = (accel && accel !== '---') ? accel : '---';
//   document.getElementById('specEngine').innerText = (engine && engine !== '---') ? engine : '---';

//   // ذخیره در متغیر گلوبال برای استفاده در مرحله بعد
//   contractData.id = id;
//   contractData.name = title;
//   contractData.price = formattedPrice + " تومان";

//   // ==========================================
//   // 5. ساخت دایره‌های رنگ (Create Elements)
//   // ==========================================
//   let availableColors = {};
//   try {
//       availableColors = JSON.parse(colorsJSON);
//   } catch(err) { console.error("JSON Error:", err); }

//   // پیدا کردن جایگاه رنگ‌ها در مودال
//   let colorsContainerDiv = document.getElementById('modalColorsContainer');
  
//   // اگر آیدی رو پیدا نکرد، با کلاس پیداش کن (محکم‌کاری)
//   if (!colorsContainerDiv) {
//       colorsContainerDiv = document.querySelector('.color-selection-area .colors-container');
//   }

//   if (colorsContainerDiv) {
//       colorsContainerDiv.innerHTML = ''; // پاک کردن دایره‌های قبلی
      
//       let firstAvailableColorName = null;
//       let firstAvailableColorQty = 0;
//       let isAnyColorAvailable = false;
// // حلقه روی رنگ‌های موجود در دیتابیس
// const colorKeys = Object.keys(availableColors);
        
// if (colorKeys.length > 0) {
//     colorKeys.forEach(colorName => {
//         let colorData = availableColors[colorName];
//         let qty = 0;
//         let hex = '#ccc'; 
//         let specificImg = ''; // متغیر عکس رنگ

//         // تشخیص فرمت جدید (آبجکت) یا قدیم (عدد)
//         if (typeof colorData === 'object' && colorData !== null) {
//             qty = parseInt(colorData.qty);
//             hex = colorData.hex;
//             specificImg = colorData.img || ''; // گرفتن عکس مخصوص رنگ
//         } else {
//             qty = parseInt(colorData);
//             // حدس رنگ برای محصولات قدیمی
//             if(colorName.includes('مشکی')) hex='#000';
//             else if(colorName.includes('سفید')) hex='#fff';
//             else if(colorName.includes('قرمز')) hex='#f00';
//             else if(colorName.includes('آبی')) hex='#00f';
//         }

//         // ساختن دایره رنگ
//         const dot = document.createElement('div');
//         dot.className = 'color-dot';
//         dot.setAttribute('data-color', colorName);
//         dot.style.backgroundColor = hex;
        
//         // بردر برای رنگ سفید
//         if(hex && (hex.toLowerCase() === '#ffffff' || hex.toLowerCase() === '#fff')) {
//             dot.style.border = '1px solid #ccc';
//         }

//         if (qty > 0) {
//             isAnyColorAvailable = true;
            
//             // انتخاب اتوماتیک اولین رنگ موجود
//             if (!firstAvailableColorName) {
//                 firstAvailableColorName = colorName;
//                 firstAvailableColorQty = qty;
//                 dot.classList.add('selected');
                
//                 // اگر اولین رنگ عکس داشت، همون اول عکسش رو لود کن
//                 if(specificImg) {
//                     document.getElementById('modalCarImage').src = specificImg;
//                 }
//             }

//             // ایونت کلیک روی دایره
//             dot.addEventListener('click', function(e) {
//                 e.stopPropagation();
//                 // برداشتن انتخاب بقیه
//                 colorsContainerDiv.querySelectorAll('.color-dot').forEach(d => d.classList.remove('selected'));
//                 this.classList.add('selected');
                
//                 // آپدیت داده‌ها
//                 contractData.color = colorName;
//                 document.getElementById('selectedColorLabel').innerText = `رنگ انتخاب شده: ${colorName} (موجودی: ${qty})`;
                
//                 // ============================================
//                 // جادوی تغییر عکس با افکت محو شدن (Fade Effect)
//                 // ============================================
//                 const carImgEl = document.getElementById('modalCarImage');
//                 carImgEl.style.transition = "opacity 0.2s ease-in-out";
//                 carImgEl.style.opacity = 0.5; // عکس رو کمرنگ کن
                
//                 setTimeout(() => {
//                     // عکس رو عوض کن (اگر عکس داشت عکس خودش، وگرنه عکس اصلی ماشین)
//                     carImgEl.src = specificImg ? specificImg : imgSrc;
//                     carImgEl.style.opacity = 1; // عکس رو برگردون
//                 }, 200);
                
//                 // فعال کردن دکمه
//                 const btn = document.querySelector('.btn-primary-modal');
//                 btn.innerText = 'تایید و تنظیم قرارداد ←';
//                 btn.classList.remove('btn-out-of-stock');
//                 btn.onclick = window.goToStep2;
//             });
//         } else {
//             // رنگ ناموجود
//             dot.classList.add('disabled');
//         }

//         colorsContainerDiv.appendChild(dot);
//     });
//       } else {
//           colorsContainerDiv.innerHTML = '<span style="font-size:12px; color:red">رنگی تعریف نشده است</span>';
//       }

//       // --- وضعیت دکمه نهایی ---
//       const submitBtnModal1 = document.querySelector('.btn-primary-modal'); 

//       if (!isAnyColorAvailable) {
//           // ناموجود
//           submitBtnModal1.innerText = 'خودرو در حال حاضر ناموجود است';
//           submitBtnModal1.classList.add('btn-out-of-stock');
//           submitBtnModal1.onclick = null; 
//           document.getElementById('selectedColorLabel').innerText = 'تمامی رنگ‌ها ناموجود است';
//           contractData.color = '';
//       } else {
//           // موجود
//           submitBtnModal1.innerText = 'تایید و تنظیم قرارداد ←';
//           submitBtnModal1.classList.remove('btn-out-of-stock');
//           submitBtnModal1.onclick = window.goToStep2;

//           if (firstAvailableColorName) {
//               contractData.color = firstAvailableColorName;
//               document.getElementById('selectedColorLabel').innerText = `رنگ انتخاب شده: ${firstAvailableColorName} (موجودی: ${firstAvailableColorQty})`;
//           }
//       }
//   }
// // --- 1. مدیریت دکمه صدای موتور ---
// const soundBtn = document.getElementById('engineSoundBtn');
// const audioPlayer = document.getElementById('carAudioPlayer');

// // توقف صدای قبلی (اگر داشت پخش می‌شد)
// audioPlayer.pause();
// audioPlayer.currentTime = 0;
// soundBtn.innerHTML = "🔊 صدای موتور";
// soundBtn.style.background = "#212121";

// if (engineSound && engineSound.trim() !== '') {
//     soundBtn.style.display = 'flex';
//     audioPlayer.src = engineSound;
    
//     // ایونت کلیک روی دکمه صدا
//     soundBtn.onclick = function(e) {
//         e.preventDefault();
//         if (audioPlayer.paused) {
//             audioPlayer.play();
//             soundBtn.innerHTML = "⏸️ توقف صدا";
//             soundBtn.style.background = "#d32f2f"; // قرمز میشه موقع پخش
//         } else {
//             audioPlayer.pause();
//             soundBtn.innerHTML = "🔊 صدای موتور";
//             soundBtn.style.background = "#212121";
//         }
//     };
    
//     // وقتی صدا تموم شد دکمه برگرده حالت اول
//     audioPlayer.onended = function() {
//         soundBtn.innerHTML = "🔊 صدای موتور";
//         soundBtn.style.background = "#212121";
//     };
// } else {
//     soundBtn.style.display = 'none'; // اگه صدا نداشت مخفی میشه
// }

// // --- 2. مدیریت سیستم روانشناسی FOMO (تعداد بازدیدکنندگان) ---
// const fomoEl = document.getElementById('fomoCounter');
// // ساخت یک عدد رندوم بین 2 تا 7 برای شروع
// let currentViewers = Math.floor(Math.random() * 6) + 2;
// fomoEl.innerText = currentViewers;

// // هر 5 ثانیه عدد یکم بالا پایین بشه که طبیعی جلوه کنه
// if(window.fomoInterval) clearInterval(window.fomoInterval);
// window.fomoInterval = setInterval(() => {
//     // 70 درصد مواقع ممکنه تغییر کنه
//     if(Math.random() > 0.3) {
//         // یا یکی کم میشه یا یکی زیاد (بین حداقل 2 و حداکثر 9)
//         const change = Math.random() > 0.5 ? 1 : -1;
//         currentViewers += change;
//         if(currentViewers < 2) currentViewers = 2;
//         if(currentViewers > 9) currentViewers = 9;
//         fomoEl.innerText = currentViewers;
//     }
// }, 5000);
//   // 6. باز کردن مودال
//   document.getElementById('buyModal').style.display = 'flex';
//   if (typeof goToStep1 === 'function') goToStep1();
  
//   setTimeout(() => {
//       if (typeof resizeCanvas === 'function') resizeCanvas();
//       if (ctx) ctx.clearRect(0, 0, canvas.width, canvas.height);
//       isSignatureEmpty = true;
//   }, 100);

//   // مخفی کردن خطاها
//   document.querySelectorAll('.contract-modal .error-message').forEach(el => el.style.display = 'none');
//   document.querySelectorAll('.input-group-modal input').forEach(el => el.classList.remove('invalid', 'valid'));
// });



// ============================================================
// موتور هوشمند باز کردن مودال (Smart Live Modal Opener)
// ============================================================
window.openSmartModal = async function(productId) {
  // 1. بررسی لاگین بودن کاربر
  if (typeof isLoggedIn !== 'undefined' && !isLoggedIn) {
      alert("لطفا ابتدا وارد حساب کاربری خود شوید.");
      window.location.href = "login.php";
      return;
  }

  try {
      // 2. درخواست اطلاعات زنده از سرور (بدون نیاز به خواندن HTML)
      const response = await fetch('api_get_product.php?id=' + productId);
      const data = await response.json();

      if (!data.success) {
          alert(data.error || 'این خودرو در حال حاضر در دسترس نیست.');
          
       // =========================================================
                    // حذف فیزیکی و فوری کارت خودرو از صفحه اصلی بدون رفرش (UX محشر)
                    // =========================================================
                    const card = document.querySelector(`.box[data-id="${productId}"]`) || 
                                 document.querySelector(`.box-mobile[data-id="${productId}"]`) ||
                                 document.querySelector(`.swiper-slide[data-id="${productId}"]`);
                    
                    if (card) {
                        card.style.opacity = '0';
                        card.style.transform = 'scale(0.9)';
                        card.style.transition = 'all 0.4s ease';
                        setTimeout(() => {
                            card.remove();
                            // در صورت استفاده از اسلایدر سوئیپر، آن را آپدیت کن تا فضای خالی پر شود
                            if (typeof swiper !== 'undefined') swiper.update(); 
                        }, 400);
                    }
                    return;
                }

      

      const car = data.product;

      // 3. آماده‌سازی اطلاعات برای نمایش در مودال
      const formattedPrice = Number(car.price).toLocaleString();
      let priceHTML = (car.old_price && car.old_price > 0) 
          ? `<del style="color:red; font-size:0.8em;">${Number(car.old_price).toLocaleString()} تومان</del> ${formattedPrice} تومان`
          : `${formattedPrice} تومان`;

      // چاپ اطلاعات پایه در مودال
      document.getElementById('modalCarName').innerText = car.name;
      document.getElementById('modalCarPrice').innerHTML = priceHTML;
      document.getElementById('modalCarImage').src = car.image;
      document.getElementById('specHP').innerText = (car.hp && car.hp !== '---') ? car.hp : '---';
      document.getElementById('specAccel').innerText = (car.accel && car.accel !== '---') ? car.accel : '---';
      document.getElementById('specEngine').innerText = (car.engine && car.engine !== '---') ? car.engine : '---';

      // آپدیت متغیر گلوبال قرارداد
      if (typeof contractData === 'undefined') window.contractData = {};
      contractData.id = car.id;
      contractData.name = car.name;
      contractData.price = formattedPrice + " تومان";

      // ==========================================
      // 4. ساخت دایره‌های رنگ با دیتای زنده
      // ==========================================
      let availableColors = {};
      try { availableColors = JSON.parse(car.colors_inventory || '{}'); } catch(e) {}

      let colorsContainerDiv = document.getElementById('modalColorsContainer');
      if (!colorsContainerDiv) colorsContainerDiv = document.querySelector('.color-selection-area .colors-container');

      if (colorsContainerDiv) {
          colorsContainerDiv.innerHTML = ''; 
          let firstAvailableColorName = null;
          let firstAvailableColorQty = 0;
          let isAnyColorAvailable = false;

          Object.keys(availableColors).forEach(colorName => {
              let cData = availableColors[colorName];
              let qty = typeof cData === 'object' ? parseInt(cData.qty) : parseInt(cData);
              let hex = typeof cData === 'object' ? cData.hex : '#ccc';
              let specificImg = typeof cData === 'object' ? (cData.img || '') : '';

              const dot = document.createElement('div');
              dot.className = 'color-dot';
              dot.style.backgroundColor = hex;
              if(hex.toLowerCase() === '#ffffff' || hex.toLowerCase() === '#fff') dot.style.border = '1px solid #ccc';

              if (qty > 0) {
                  isAnyColorAvailable = true;
                  if (!firstAvailableColorName) {
                      firstAvailableColorName = colorName;
                      firstAvailableColorQty = qty;
                      dot.classList.add('selected');
                      if(specificImg) document.getElementById('modalCarImage').src = specificImg;
                  }

                  dot.addEventListener('click', function(e) {
                      e.stopPropagation();
                      colorsContainerDiv.querySelectorAll('.color-dot').forEach(d => d.classList.remove('selected'));
                      this.classList.add('selected');
                      contractData.color = colorName;
                      document.getElementById('selectedColorLabel').innerText = `رنگ انتخاب شده: ${colorName} (موجودی: ${qty})`;

                      const carImgEl = document.getElementById('modalCarImage');
                      carImgEl.style.transition = "opacity 0.2s ease-in-out";
                      carImgEl.style.opacity = 0.5;
                      setTimeout(() => {
                          carImgEl.src = specificImg ? specificImg : car.image;
                          carImgEl.style.opacity = 1;
                      }, 200);

                      const btn = document.querySelector('.btn-primary-modal');
                      btn.innerText = 'تایید و تنظیم قرارداد ←';
                      btn.classList.remove('btn-out-of-stock');
                      btn.onclick = window.goToStep2;
                  });
              } else {
                  dot.classList.add('disabled');
              }
              colorsContainerDiv.appendChild(dot);
          });

          // وضعیت دکمه نهایی بر اساس موجودی لایو
          const submitBtnModal1 = document.querySelector('.btn-primary-modal'); 
          if (!isAnyColorAvailable) {
              submitBtnModal1.innerText = 'خودرو در حال حاضر ناموجود است';
              submitBtnModal1.classList.add('btn-out-of-stock');
              submitBtnModal1.onclick = null; 
              document.getElementById('selectedColorLabel').innerText = 'تمامی رنگ‌ها ناموجود است';
              contractData.color = '';
          } else {
              submitBtnModal1.innerText = 'تایید و تنظیم قرارداد ←';
              submitBtnModal1.classList.remove('btn-out-of-stock');
              submitBtnModal1.onclick = window.goToStep2;
              contractData.color = firstAvailableColorName;
              document.getElementById('selectedColorLabel').innerText = `رنگ انتخاب شده: ${firstAvailableColorName} (موجودی: ${firstAvailableColorQty})`;
          }
      }

      // ==========================================
      // 5. مدیریت صدای موتور
      // ==========================================
      const soundBtn = document.getElementById('engineSoundBtn');
      const audioPlayer = document.getElementById('carAudioPlayer');
      if (soundBtn && audioPlayer) {
          audioPlayer.pause();
          audioPlayer.currentTime = 0; 
          soundBtn.innerHTML = "🔊 صدای موتور";
          soundBtn.style.background = "#212121";

          if (car.engine_sound && car.engine_sound.trim() !== '') { 
              soundBtn.style.display = 'flex';
              audioPlayer.src = car.engine_sound;

              soundBtn.onclick = function(e) {
                  e.preventDefault();
                  if (audioPlayer.paused) {
                      audioPlayer.play();
                      soundBtn.innerHTML = "⏸️ توقف صدا";
                      soundBtn.style.background = "#d32f2f"; 
                  } else {
                      audioPlayer.pause();
                      soundBtn.innerHTML = "🔊 صدای موتور";
                      soundBtn.style.background = "#212121";
                  }
              };

              audioPlayer.onended = function() {
                  soundBtn.innerHTML = "🔊 صدای موتور";
                  soundBtn.style.background = "#212121";
              };
          } else { 
              soundBtn.style.display = 'none'; 
          }
      }

      // ==========================================
      // 6. مدیریت سیستم روانشناسی FOMO
      // ==========================================
      const fomoEl = document.getElementById('fomoCounter');
      if (fomoEl) {
          let currentViewers = Math.floor(Math.random() * 6) + 2; 
          fomoEl.innerText = currentViewers;

          if(window.fomoInterval) clearInterval(window.fomoInterval); 
          window.fomoInterval = setInterval(() => {
              if(Math.random() > 0.3) { 
                  const change = Math.random() > 0.5 ? 1 : -1;
                  currentViewers += change; 
                  if(currentViewers < 2) currentViewers = 2;
                  if(currentViewers > 9) currentViewers = 9; 
                  fomoEl.innerText = currentViewers; 
              }
          }, 5000);
      }

      // ==========================================
      // 7. نمایش نهایی مودال
      // ==========================================
      document.getElementById('buyModal').style.display = 'flex';
      if (typeof goToStep1 === 'function') goToStep1();

      setTimeout(() => { 
          if (typeof resizeCanvas === 'function') resizeCanvas(); 
          const canvas = document.getElementById('signaturePad');
          if (canvas) {
              const ctx = canvas.getContext('2d');
              if (ctx) ctx.clearRect(0, 0, canvas.width, canvas.height); 
          }
          if (typeof isSignatureEmpty !== 'undefined') isSignatureEmpty = true;
      }, 100);

      document.querySelectorAll('.contract-modal .error-message').forEach(el => el.style.display = 'none');
      document.querySelectorAll('.input-group-modal input').forEach(el => el.classList.remove('invalid', 'valid'));

  } catch (err) {
      console.error(err);
      alert('خطا در ارتباط با سرور. لطفا اینترنت خود را بررسی کنید.');
  }
};

// ======================================================
// سیستم یکپارچه گوش دادن به کلیک‌ها (Routing)
// ======================================================
document.addEventListener('click', function (e) {
  const container = e.target.closest('.box, .box-mobile, .swiper-slide, .wishlist-card');
  if (!container) return;

  e.preventDefault();

  const productId = container.getAttribute('data-id');
  if (!productId) return;

  if (typeof window.openSmartModal === 'function') {
      window.openSmartModal(productId);
  } else {
      alert('سیستم در حال به‌روزرسانی است. لطفا صفحه را رفرش کنید.');
  }
});

  function updatePreviewText() {
      const name = document.getElementById('inputRealName').value || '...';
      document.getElementById('previewName').innerText = name;
      document.getElementById('previewCar').innerText = contractData.name;
      document.getElementById('previewColor').innerText = contractData.color;
      document.getElementById('previewPrice').innerText = contractData.price;
  }

  document.getElementById('inputRealName').addEventListener('input', updatePreviewText);

  // ============================================================
// رفتن به مرحله دوم (فرم قرارداد) همراه با کنترل دسترسی
// ============================================================
window.goToStep2 = function() {
  // 1. گارد امنیتی: اگر کاربر مهمان است، اجازه ورود به فرم را نده و به صفحه لاگین بفرست
  if (typeof isLoggedIn === 'undefined' || isLoggedIn === false) {
      // ذخیره آدرس صفحه فعلی تا بعد از لاگین کاربر رو برگردونیم همینجا (UX عالی)
      sessionStorage.setItem('redirectAfterLogin', window.location.href);
      
      alert("لطفا برای تنظیم قرارداد و خرید نهایی، ابتدا وارد حساب کاربری خود شوید.");
      window.location.href = "login.php";
      return;
  }

  // 2. اگر لاگین بود، اطلاعاتش رو پر کن و فرم رو نشون بده
  document.getElementById('inputRealName').value = typeof userName !== 'undefined' ? userName : '';
  
  fetch('get_user_data.php')
      .then(r => r.json())
      .then(d => { 
          if(d.phone) document.getElementById('readOnlyPhone').value = d.phone; 
      })
      .catch(e => console.error("خطا در دریافت اطلاعات کاربر"));

  document.getElementById('step1').style.display = 'none';
  document.getElementById('step2').style.display = 'block';
  setTimeout(resizeCanvas, 100);
  updatePreviewText();
}

  window.goToStep1 = function() {
      document.getElementById('step1').style.display = 'block';
      document.getElementById('step2').style.display = 'none';
  }

  window.closeBuyModal = function() {
      document.getElementById('buyModal').style.display = 'none';
  }

  // ===============================================
  // سیستم اعتبارسنجی (اصلاح شده و دقیق)
  // ===============================================
  
  const nameInput = document.getElementById('inputRealName');
  const nidInput = document.getElementById('inputNID');
  const postalInput = document.getElementById('inputPostal');
  const addressInput = document.getElementById('inputAddress');

  function toggleError(input, isValid, msg) {
      const parent = input.parentElement;
      const errorDiv = parent.querySelector('.error-message');
      
      if (isValid) {
          input.classList.remove('invalid');
          input.classList.add('valid');
          if (errorDiv) {
              errorDiv.style.display = 'none';
              errorDiv.innerText = '';
          }
          return true;
      } else {
          input.classList.remove('valid');
          input.classList.add('invalid');
          if (errorDiv) {
              errorDiv.innerText = msg;
              errorDiv.style.display = 'block';
          }
          return false;
      }
  }

  // تابع الگوریتم صحیح کد ملی
  function isValidIranianNationalID(input) {
      if (!/^\d{10}$/.test(input)) return false;
      if (/^(\d)\1{9}$/.test(input)) return false;
      const check = +input[9];
      let sum = 0;
      for (let i = 0; i < 9; i++) sum += (+input[i]) * (10 - i);
      const remainder = sum % 11;
      return (remainder < 2 && check === remainder) || (remainder >= 2 && check === 11 - remainder);
  }

  // --- لیسنر کد ملی ---
  nidInput.addEventListener('input', function() { 
      this.value = this.value.replace(/\D/g, ''); // فقط عدد
      
      // اگر ۱۰ رقم شد، الگوریتم را چک کن
      if(this.value.length === 10) {
          if (!isValidIranianNationalID(this.value)) {
              toggleError(this, false, "کد ملی نامعتبر است");
          } else {
              toggleError(this, true, "");
          }
      } else {
          // هنگام تایپ اگر هنوز ۱۰ رقم نشده، ارور نده تا کاربر اذیت نشود
          // اما کلاس valid را برمیداریم
          this.classList.remove('valid', 'invalid');
          const err = this.parentElement.querySelector('.error-message');
          if(err) err.style.display = 'none';
      }
  });

  // وقتی کاربر از فیلد خارج شد (Blur)، اگر ۱۰ رقم نبود گیر بده
  nidInput.addEventListener('blur', function() {
      if(this.value.length > 0 && this.value.length < 10) {
          toggleError(this, false, "کد ملی نباید کمتر از ۱۰ رقم باشد");
      }
  });

  // --- لیسنر کد پستی ---
  postalInput.addEventListener('input', function() { 
      this.value = this.value.replace(/\D/g, ''); 
      if(this.value.length === 10) {
          toggleError(this, true, "");
      } else {
          this.classList.remove('valid', 'invalid');
          const err = this.parentElement.querySelector('.error-message');
          if(err) err.style.display = 'none';
      }
  });

  // وقتی کاربر از فیلد خارج شد (Blur)، اگر ۱۰ رقم نبود گیر بده
  postalInput.addEventListener('blur', function() {
      if(this.value.length > 0 && this.value.length < 10) {
          toggleError(this, false, "کد پستی باید دقیقا ۱۰ رقم باشد");
      }
  });

  // --- لیسنر نام ---
  nameInput.addEventListener('input', function() { 
      toggleError(this, this.value.length >= 5, "نام و نام خانوادگی باید حداقل ۵ حرف باشد");
      updatePreviewText(); 
  });

  // --- لیسنر آدرس ---
  addressInput.addEventListener('input', function() {
      toggleError(this, this.value.length >= 10, "آدرس باید دقیق‌تر باشد");
  });

  // ===============================================
  // ارسال نهایی فرم (بررسی‌های سخت‌گیرانه)
  // ===============================================
 // ===============================================
  // ارسال نهایی فرم (بررسی‌های سخت‌گیرانه)
  // ===============================================
  document.getElementById('contractForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    let hasError = false;

    // 1. بررسی نام
    if (!toggleError(nameInput, nameInput.value.length >= 5, "نام کامل را وارد کنید")) hasError = true;

    // 2. بررسی کد ملی
    if (nidInput.value.length !== 10) {
        toggleError(nidInput, false, "کد ملی نباید کمتر از ۱۰ رقم باشد");
        hasError = true;
    } else if (!isValidIranianNationalID(nidInput.value)) {
        toggleError(nidInput, false, "کد ملی نامعتبر است");
        hasError = true;
    }

    // 3. بررسی کد پستی
    if (!/^\d{10}$/.test(postalInput.value)) {
        toggleError(postalInput, false, "کد پستی باید دقیقا ۱۰ رقم باشد");
        hasError = true;
    }

    // 4. بررسی آدرس
    if (!toggleError(addressInput, addressInput.value.length >= 10, "آدرس را کامل بنویسید")) hasError = true;

    // 5. بررسی امضا
    const sigError = document.getElementById('sig-error');
    const sigContainer = document.querySelector('.canvas-container');
    if (isSignatureEmpty) {
        sigError.innerText = "لطفا قرارداد را امضا کنید";
        sigError.style.display = 'block';
        sigContainer.classList.add('invalid');
        hasError = true;
    } else {
        sigError.style.display = 'none';
        sigContainer.classList.remove('invalid');
    }

    // اگر خطایی بود، متوقف شو
    if (hasError) return;

    // --- شروع ارسال ---
    const btn = document.querySelector('.btn-success-modal');
    const originalText = btn.innerText;
    btn.innerText = 'در حال ارسال...';
    btn.disabled = true;
    
    // آماده‌سازی داده‌ها (ترتیب درست اینجاست)
    const signatureData = canvas.toDataURL('image/png');
    
    // اول باید FormData ساخته بشه (این خط باید بالا باشه)
    const formData = new FormData(); 

    // حالا اطلاعات رو اضافه می‌کنیم
    formData.append('product_id', contractData.id); // <--- حالا این خط درسته
    formData.append('car_name', contractData.name);
    formData.append('car_price', contractData.price);
    formData.append('car_color', contractData.color);
    formData.append('real_name', nameInput.value);
    formData.append('national_id', nidInput.value);
    formData.append('address', addressInput.value);
    formData.append('postal_code', postalInput.value);
    formData.append('signature', signatureData);

    fetch('submit_contract.php', { method: 'POST', body: formData })
    .then(async response => {
        const text = await response.text();
        try { return JSON.parse(text); } catch { throw new Error(text); }
    })
    .then(data => {
        if(data.success) {
             // هدایت به درگاه پرداخت
             window.location.href = 'payment.php?id=' + data.contract_id;       
        } else { 
            alert('خطا: ' + data.error); 
            btn.innerText = originalText; 
            btn.disabled = false; 
        }
    })
    .catch(err => {
        console.error(err); 
        alert('خطای سرور: ارتباط با دیتابیس برقرار نشد.'); 
        btn.innerText = originalText; 
        btn.disabled = false;
    });
  });

  // ============================================
  // افکت تایپ‌نویس (Typewriter Effect)
  // ============================================
  const typeTextSpan = document.querySelector(".type-text");
  const phrases = [
      "فروشگاه تخصصی خودروهای لوکس",
      "آئودی A6 e-tron مدل ۲۰۲۴",
      "خرید آسان، مطمئن و آنلاین",
      "رویای رانندگی را تجربه کنید"
  ];
  
  let phraseIndex = 0;
  let charIndex = 0;
  let isDeleting = false;
  let typeSpeed = 100;

  function typeWriter() {
      if (!typeTextSpan) return; // اگر المنت نبود اجرا نکن
      
      const currentPhrase = phrases[phraseIndex];
      
      if (isDeleting) {
          // در حال پاک کردن
          typeTextSpan.textContent = currentPhrase.substring(0, charIndex - 1);
          charIndex--;
          typeSpeed = 50; // سرعت پاک کردن بیشتره
      } else {
          // در حال تایپ کردن
          typeTextSpan.textContent = currentPhrase.substring(0, charIndex + 1);
          charIndex++;
          typeSpeed = 100; // سرعت تایپ نرمال
      }

      if (!isDeleting && charIndex === currentPhrase.length) {
          // وقتی تایپ تموم شد، کمی صبر کن
          isDeleting = true;
          typeSpeed = 2000; // ۲ ثانیه مکث کن تا کاربر بخونه
      } else if (isDeleting && charIndex === 0) {
          // وقتی پاک شد، برو جمله بعدی
          isDeleting = false;
          phraseIndex = (phraseIndex + 1) % phrases.length; // لوپ بی‌نهایت
          typeSpeed = 500;
      }

      setTimeout(typeWriter, typeSpeed);
  }

  // شروع تایپ
  typeWriter();
});