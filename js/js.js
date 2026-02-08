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
  const brandFilter = document.getElementById('brand-filter');
  const searchInput = document.getElementById('search');
  const boxes = document.querySelectorAll('.box');

  function filterCars() {
    const query = searchInput.value.toLowerCase();
    const selectedBrand = brandFilter.value.toLowerCase();

    boxes.forEach(box => {
      const title = box.getAttribute('data-title').toLowerCase();
      const matchesQuery = title.includes(query);
      const matchesBrand = selectedBrand === "" || title.includes(selectedBrand);

      if (matchesQuery && matchesBrand) {
        box.classList.remove('hide');
      } else {
        box.classList.add('hide');
      }
    });
  }

  searchInput.addEventListener('input', filterCars);
  brandFilter.addEventListener('change', filterCars);
});

document.addEventListener('DOMContentLoaded', () => {
  const searchInput = document.getElementById('search-mobile');
  const brandFilter = document.getElementById('brand-filter-mobile');
  const boxes = document.querySelectorAll('.box-mobile');

  function filterCars() {
    const query = searchInput.value.toLowerCase();
    const selectedBrand = brandFilter.value.toLowerCase();

    boxes.forEach(box => {
      const title = box.getAttribute('data-title').toLowerCase();
      const matchesQuery = title.includes(query);
      const matchesBrand = selectedBrand === "" || title.includes(selectedBrand);

      if (matchesQuery && matchesBrand) {
        box.classList.remove('hide');
      } else {
        box.classList.add('hide');
      }
    });
  }

  searchInput.addEventListener('input', filterCars);
  brandFilter.addEventListener('change', filterCars);
});







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
  
  // دیتابیس ماشین‌ها (همان قبلی)
  const carDatabase = {
    'آئودی A6 e-tron 2024': { hp: '469 hp', accel: '3.9s', engine: 'Electric' },
    'لامبورگینی آونتادور ۲۰۱۱': { hp: '700 hp', accel: '2.9s', engine: 'V12' },
    'رولزرویس فانتوم ۲۰۱۷': { hp: '563 hp', accel: '5.1s', engine: 'V12' },
    'فراری لافراری ۲۰۱۳': { hp: '950 hp', accel: '2.4s', engine: 'Hybrid' },
    'رنج روور ۲۰۲۳': { hp: '523 hp', accel: '4.4s', engine: 'V8' },
    'مرسدس بنز S-Class 2021': { hp: '496 hp', accel: '4.4s', engine: 'V8' },
    'فراری F8 تریبوتو ۲۰۱۴': { hp: '710 hp', accel: '2.9s', engine: 'V8 Turbo' },
    '۲۰۱۳ فراری f8 تروبیتو': { hp: '710 hp', accel: '2.9s', engine: 'V8 Turbo' }
  };

  // تنظیمات امضا (Canvas) - مثل قبل
  const canvas = document.getElementById('signaturePad');
  const ctx = canvas.getContext('2d');
  let isDrawing = false;

  function resizeCanvas() {
      const ratio = Math.max(window.devicePixelRatio || 1, 1);
      canvas.width = canvas.offsetWidth * ratio;
      canvas.height = canvas.offsetHeight * ratio;
      ctx.scale(ratio, ratio);
  }

  function startDraw(e) {
      isDrawing = true;
      ctx.beginPath();
      ctx.lineWidth = 2;
      ctx.lineCap = 'round';
      ctx.strokeStyle = '#000';
      const { x, y } = getPos(e);
      ctx.moveTo(x, y);
  }

  function draw(e) {
      if (!isDrawing) return;
      e.preventDefault();
      const { x, y } = getPos(e);
      ctx.lineTo(x, y);
      ctx.stroke();
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
  });

  // متغیرهای گلوبال قرارداد
  let contractData = { name: '', price: '', color: 'مشکی' };

  // ============================================================
  // بخش اصلی: باز کردن مودال (اصلاح شده برای کلیک روی کل باکس و قیمت درست)
  // ============================================================
  
  // انتخاب تمام باکس‌ها (گرید دسکتاپ، گرید موبایل و اسلایدها)
  const clickTargets = document.querySelectorAll('.box, .box-mobile, .swiper-slide, .button');
  
  clickTargets.forEach(target => {
    target.addEventListener('click', function(e) {
      // اگر روی دکمه‌های خاصی که نباید مودال باز کنن کلیک شد جلوگیری کن (اختیاری)
      
      e.preventDefault();

      if (typeof isLoggedIn !== 'undefined' && !isLoggedIn) {
        alert("لطفا ابتدا وارد حساب کاربری شوید.");
        window.location.href = "login.php";
        return;
      }

      // استخراج اطلاعات از Data Attributes (روش مطمئن)
      let title = this.getAttribute('data-title');
      let rawPrice = this.getAttribute('data-price');
      let oldPrice = this.getAttribute('data-old-price');
      let imgSrc = this.getAttribute('data-image');

      // هندل کردن دکمه وسط صفحه (هدر) که data attributes ندارد
      if (this.classList.contains('button') && !title) {
          title = document.querySelector('.detail').innerText.replace('مدل جدید ما', '').trim();
          rawPrice = "1,670,000,000"; // قیمت پیش فرض برای هدر
          imgSrc = "images/car-1.webp";
      }

      // اگر قیمت پیدا نشد (محض اطمینان)
      if (!rawPrice) rawPrice = "توافقی";

      // فرمت کردن قیمت برای نمایش (با تومان)
      let displayPrice = rawPrice + " تومان";
      
      // اگر تخفیف داشت
      if (oldPrice) {
          // در مودال هم خط خورده نمایش بده
          displayPrice = `<del style="color:red; font-size:0.8em;">${oldPrice}</del> ${rawPrice} تومان`;
          // قیمت نهایی برای قرارداد (بدون تگ HTML)
          contractData.price = rawPrice + " تومان";
      } else {
          contractData.price = displayPrice;
      }

      contractData.name = title;

      // پر کردن UI مودال
      document.getElementById('modalCarName').innerText = title;
      document.getElementById('modalCarPrice').innerHTML = displayPrice; // innerHTML برای نمایش تگ del
      document.getElementById('modalCarImage').src = imgSrc;

      // مشخصات فنی
      let specs = { hp: '---', accel: '---', engine: '---' };
      for (const [key, value] of Object.entries(carDatabase)) {
          if (title && (title.includes(key) || key.includes(title))) {
              specs = value;
              break;
          }
      }
      document.getElementById('specHP').innerText = specs.hp;
      document.getElementById('specAccel').innerText = specs.accel;
      document.getElementById('specEngine').innerText = specs.engine;

      document.getElementById('buyModal').style.display = 'flex';
      goToStep1();
      setTimeout(resizeCanvas, 100);
    });
  });

  // بقیه کدها (انتخاب رنگ، نویگیشن، ولیدیشن و ارسال فرم) بدون تغییر می‌ماند...
  // (ادامه کدهای قبلی JS اینجا قرار می‌گیرد)
  
  // انتخاب رنگ
  document.querySelectorAll('.color-dot').forEach(dot => {
    dot.addEventListener('click', function(e) {
      e.stopPropagation(); // جلوگیری از بسته شدن یا تداخل کلیک
      document.querySelectorAll('.color-dot').forEach(d => d.classList.remove('selected'));
      this.classList.add('selected');
      contractData.color = this.getAttribute('data-color');
      document.getElementById('selectedColorLabel').innerText = 'رنگ انتخاب شده: ' + contractData.color;
    });
  });

  function updatePreviewText() {
      const name = document.getElementById('inputRealName').value || '...';
      document.getElementById('previewName').innerText = name;
      document.getElementById('previewCar').innerText = contractData.name;
      document.getElementById('previewColor').innerText = contractData.color;
      document.getElementById('previewPrice').innerText = contractData.price;
  }

  document.getElementById('inputRealName').addEventListener('input', updatePreviewText);

  window.goToStep2 = function() {
      document.getElementById('inputRealName').value = typeof userName !== 'undefined' ? userName : '';
      fetch('get_user_data.php').then(r=>r.json()).then(d=>{ if(d.phone) document.getElementById('readOnlyPhone').value = d.phone; });
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

  

    // ولیدیشن و ارسال فرم (کد قبلی)
    const nameInput = document.getElementById('inputRealName');
    const nidInput = document.getElementById('inputNID');
    const postalInput = document.getElementById('inputPostal');
  
    function validateInput(input, condition, msg) {
        const parent = input.parentElement;
        let err = parent.querySelector('.validation-error');
        if(!err) {
            err = document.createElement('div');
            err.className = 'validation-error';
            parent.appendChild(err);
        }
        if(condition) {
            input.classList.remove('invalid'); input.classList.add('valid');
            err.style.display = 'none';
            return true;
        } else {
            input.classList.remove('valid'); input.classList.add('invalid');
            err.innerText = msg; err.style.display = 'block';
            return false;
        }
    }
  
    // ===== NEW: Complete Iranian National ID (کد ملی) Validation Function =====
    function isValidIranianNationalID(input) {
        // Remove any spaces or dashes
        const code = input.replace(/\s+|-/g, '');
        
        // Check if it's exactly 10 digits
        if (!/^\d{10}$/.test(code)) {
            return false;
        }
        
        // Check for all same digits (invalid like 0000000000, 1111111111, etc.)
        if (/^(\d)\1{9}$/.test(code)) {
            return false;
        }
        
        // Calculate check digit using the standard Iranian algorithm
        let check = 0;
        for (let i = 0; i < 9; i++) {
            check += parseInt(code.charAt(i)) * (10 - i);
        }
        
        check = check % 11;
        
        // The check digit should be 0 if remainder is less than 2, otherwise it's the remainder
        const checkDigit = check < 2 ? 0 : (11 - check);
        
        // Compare with the last digit
        return parseInt(code.charAt(9)) === checkDigit;
    }
    // ===== END OF NEW: National ID Validation =====
  
    // NEW: Updated National ID Input Listener with Complete Validation
    nidInput.addEventListener('input', function() { 
        // Allow only digits while typing
        this.value = this.value.replace(/\D/g, '');
        
        // Validate only if 10 digits are entered
        if (this.value.length === 10) {
            const isValid = isValidIranianNationalID(this.value);
            validateInput(
                this, 
                isValid, 
                "کد ملی نامعتبر است. لطفا کد ملی صحیح را وارد کنید"
            );
        } else if (this.value.length > 0) {
            validateInput(this, false, "کد ملی باید ۱۰ رقم باشد");
        } else {
            // Clear validation styling if empty
            this.classList.remove('valid', 'invalid');
            const err = this.parentElement.querySelector('.validation-error');
            if (err) err.style.display = 'none';
        }
    });
  
    // Postal Code Validation (unchanged)
    postalInput.addEventListener('input', function() { 
        this.value = this.value.replace(/\D/g, ''); 
        validateInput(this, this.value.length === 10, "کد پستی باید ۱۰ رقم باشد"); 
    });
  
    // Name Validation (unchanged)
    nameInput.addEventListener('input', function() { 
        validateInput(this, this.value.length >= 5, "نام کامل وارد کنید"); 
        updatePreviewText(); 
    });
  
    // NEW: Updated Form Submission with Complete National ID Validation
    document.getElementById('contractForm').addEventListener('submit', function(e) {
      e.preventDefault();
      
      // Validate all fields before submission
      const isNameValid = validateInput(nameInput, nameInput.value.length >= 5, "نام کامل وارد کنید");
      const isNIDValid = isValidIranianNationalID(nidInput.value);
      validateInput(nidInput, isNIDValid, "کد ملی نامعتبر است. لطفا کد ملی صحیح را وارد کنید");
      const isPostalValid = validateInput(postalInput, postalInput.value.length === 10, "کد پستی باید ۱۰ رقم باشد");
      
      if (!isNameValid || !isNIDValid || !isPostalValid) {
          alert("لطفا تمام فیلدهای ضروری را به درستی پر کنید");
          return;
      }
      
      const signatureData = canvas.toDataURL('image/png');
      if(signatureData.length < 3000) { 
          alert("لطفا امضا کنید."); 
          return; 
      }
  
      const btn = document.querySelector('.btn-success-modal');
      btn.innerText = 'در حال ارسال...';
      btn.disabled = true;
  
      const formData = new FormData();
      formData.append('car_name', contractData.name);
      formData.append('car_price', contractData.price);
      formData.append('car_color', contractData.color);
      formData.append('real_name', nameInput.value);
      formData.append('national_id', nidInput.value);
      formData.append('address', document.getElementById('inputAddress').value);
      formData.append('postal_code', postalInput.value);
      formData.append('signature', signatureData);
  
      fetch('submit_contract.php', { method: 'POST', body: formData })
      .then(async response => {
          const text = await response.text();
          try { return JSON.parse(text); } catch { throw new Error(text); }
      })
      .then(data => {
          if(data.success) window.location.href = 'print_contract.php?id=' + data.contract_id;
          else { alert('خطا: ' + data.error); btn.innerText = 'ثبت نهایی'; btn.disabled = false; }
      })
      .catch(err => {
          console.error(err); alert('خطای سرور'); btn.innerText = 'ثبت نهایی'; btn.disabled = false;
      });
    });
});