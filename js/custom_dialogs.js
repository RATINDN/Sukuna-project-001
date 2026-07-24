// =========================================================
// سیستم هوشمند توست‌ها و مودال‌های سفارشی (Perfect Touch & Hover Edition)
// =========================================================

(function () {
  'use strict';

  let activeToasts = [];
  const MAX_TOASTS = 3;

  function initContainers() {
      let container = document.getElementById('toastContainer');
      if (!container) {
          container = document.createElement('div');
          container.id = 'toastContainer';
          document.body.appendChild(container);

          // توقف زمانِ تمام توست‌ها وقتی موس روی کل کانتینر (استک) می‌رود
          container.addEventListener('mouseenter', () => {
              container.isHovered = true;
              activeToasts.forEach(t => t.pauseTimer());
          });
          // شروع مجدد زمانِ همه توست‌ها وقتی موس خارج می‌شود
          container.addEventListener('mouseleave', () => {
              container.isHovered = false;
              activeToasts.forEach(t => t.startTimer());
          });
      }

      if (!document.getElementById('customConfirmOverlay')) {
          const overlay = document.createElement('div');
          overlay.id = 'customConfirmOverlay';
          overlay.className = 'custom-confirm-overlay';
          overlay.innerHTML = `
              <div class="custom-confirm-card">
                  <h3 class="custom-confirm-title" id="confirmTitle">تایید عملیات</h3>
                  <p class="custom-confirm-msg" id="confirmMsg">آیا از انجام این کار مطمئن هستید؟</p>
                  <div class="custom-confirm-buttons">
                      <button class="custom-confirm-btn custom-confirm-btn-yes" id="confirmBtnYes">تایید</button>
                      <button class="custom-confirm-btn custom-confirm-btn-no" id="confirmBtnNo">انصراف</button>
                  </div>
              </div>
          `;
          document.body.appendChild(overlay);
      }
  }

  if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', initContainers);
  } else {
      initContainers();
  }

  // =========================================================
  // 1. نمایش توست شناور (showToast)
  // =========================================================
  // =========================================================
    // 1. نمایش توست شناور (showToast)
    // =========================================================
    window.showToast = function (message, type = 'info', duration = 4000) {
        initContainers();
        const container = document.getElementById('toastContainer');

        let icon = '🔔';
        if (type === 'error') icon = '🔴';
        else if (type === 'success') icon = '🟢';
        else if (type === 'warning') icon = '🟡';

        const toast = document.createElement('div');
        toast.className = `custom-toast toast-${type}`;
        toast.innerHTML = `
            <span class="toast-icon">${icon}</span>
            <span class="toast-message">${message}</span>
            <button class="toast-close-btn" title="بستن">&times;</button>
        `;

        toast.querySelector('.toast-close-btn').addEventListener('click', (e) => {
            e.stopPropagation();
            removeToast(toast);
        });

        // -----------------------------------------------------
        // سیستم مدیریت زمان هوشمند
        // -----------------------------------------------------
        toast.startTimer = () => {
            if (duration > 0 && !toast.isDismissing && !container.isHovered && !toast.isDragging) {
                toast.autoDismissTimer = setTimeout(() => removeToast(toast), duration);
            }
        };

        toast.pauseTimer = () => {
            if (toast.autoDismissTimer) {
                clearTimeout(toast.autoDismissTimer);
                toast.autoDismissTimer = null;
            }
        };

        setupDragToDismiss(toast, container);

        // =====================================================
        // انیمیشن ورود نرم (Smooth Entry Animation)
        // =====================================================
        // ۱. حالت اولیه (مخفی و کمی بالاتر)
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(-30px) scale(0.9)';

        activeToasts.unshift(toast);
        container.appendChild(toast);

        // ۲. ترفند مجبور کردن مرورگر به رندر کردن فریم اولیه
        void toast.offsetWidth;

        // ۳. پاک کردن استایل‌های اولیه تا ترنزیشن‌های CSS به نرمی اجرا شوند
        toast.style.opacity = '';
        toast.style.transform = '';
        // =====================================================

        if (activeToasts.length > MAX_TOASTS) {
            const oldest = activeToasts.pop();
            removeToast(oldest, false);
        }

        recalculateStack();
        toast.startTimer();
    };

 // بازچینی لایه‌های ۳بعدی و محاسبه ارتفاع داینامیک
 function recalculateStack() {
    let totalExpandedHeight = 0; // برای جمع زدن ارتفاع توست‌ها در حالت باز

    activeToasts.forEach((toast, index) => {
        toast.setAttribute('data-index', index);
        
        // تنظیم متغیر CSS برای جایگاه دقیق این توست در حالت هاور (باز شده)
        toast.style.setProperty('--hover-offset', totalExpandedHeight + 'px');
        
        // اضافه کردن ارتفاع فیزیکی این توست + 12 پیکسل فاصله، برای توستِ بعدی
        totalExpandedHeight += toast.offsetHeight + 12;
    });
}

  // =========================================================
  // 2. حذف فوق‌نرم توست
  // =========================================================
  function removeToast(toast, removeFromArr = true) {
      if (!toast || toast.isDismissing) return;
      toast.isDismissing = true;
      toast.pauseTimer();

      toast.style.pointerEvents = 'none'; 
      toast.style.transition = 'all 0.3s cubic-bezier(0.5, 0, 0.2, 1)';
      toast.style.opacity = '0';
      toast.style.transform = 'translate3d(0, -50px, 0) scale(0.85)';

      if (removeFromArr) {
          activeToasts = activeToasts.filter(t => t !== toast);
          recalculateStack();
      }

      setTimeout(() => {
          if (toast.parentNode) toast.parentNode.removeChild(toast);
      }, 300);
  }

  // =========================================================
  // 3. فیزیک لمس و کشیدن (Flawless Mobile & Desktop Touch)
  // =========================================================
  function setupDragToDismiss(toast, container) {
      let startY = 0, startX = 0, currentY = 0, currentX = 0;

      const onMove = (e) => {
          if (!toast.isDragging) return;
          // جلوگیری از اسکرول شدن سایت در موبایل هنگام کشیدن توست
          if (e.cancelable) e.preventDefault(); 
          
          const client = e.touches ? e.touches[0] : e;
          currentY = client.clientY - startY;
          currentX = client.clientX - startX;

          if (currentY < 15 || Math.abs(currentX) > 10) {
              toast.style.transform = `translate3d(${currentX}px, ${currentY}px, 0) scale(0.95)`;
              toast.style.opacity = Math.max(0, 1 - (Math.abs(currentY) / 150) - (Math.abs(currentX) / 200));
          }
      };

      const onEnd = () => {
          if (!toast.isDragging) return;
          toast.isDragging = false;
          container.classList.remove('is-dragging');
          
          // حذف شنونده‌های گلوبال برای جلوگیری از مموری‌لیک
          window.removeEventListener('mousemove', onMove);
          window.removeEventListener('mouseup', onEnd);
          window.removeEventListener('touchmove', onMove);
          window.removeEventListener('touchend', onEnd);

          toast.style.transition = 'transform 0.4s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.4s ease';

          if (currentY < -40 || Math.abs(currentX) > 60) {
              removeToast(toast);
          } else {
              toast.style.transform = '';
              toast.style.opacity = '';
              toast.style.zIndex = '';
              toast.startTimer();
          }
          currentY = 0; currentX = 0;
      };

      const onStart = (e) => {
          if (e.target.closest('.toast-close-btn')) return; 
          toast.isDragging = true;
          toast.pauseTimer();
          container.classList.add('is-dragging');
          
          const client = e.touches ? e.touches[0] : e;
          startY = client.clientY;
          startX = client.clientX;
          
          toast.style.transition = 'none';
          toast.style.zIndex = '100';

          // اضافه کردن شنونده‌ها فقط در زمان کشیدن
          window.addEventListener('mousemove', onMove);
          window.addEventListener('mouseup', onEnd);
          window.addEventListener('touchmove', onMove, { passive: false });
          window.addEventListener('touchend', onEnd);
      };

      toast.addEventListener('mousedown', onStart);
      toast.addEventListener('touchstart', onStart, { passive: true });
  }

  // =========================================================
  // 4. هک هوشمند تابع alert پیش‌فرض
  // =========================================================
  window.alert = function (message) {
      const msg = String(message).toLowerCase();
      let type = 'warning'; 

      if (
          msg.includes('خطا') || msg.includes('اشتباه') || msg.includes('ناموفق') || 
          msg.includes('ممنوع') || msg.includes('مسدود') || msg.includes('حذف شده') || 
          msg.includes('error') || msg.includes('failed') || msg.includes('invalid') || 
          msg.includes('unauthorized') || msg.includes('forbidden') || msg.includes('limit') || 
          msg.includes('400') || msg.includes('401') || msg.includes('403') || 
          msg.includes('429') || msg.includes('500')
      ) {
          type = 'error';
      } 
      else if (
          msg.includes('موفق') || msg.includes('تایید') || msg.includes('ثبت شد') || 
          msg.includes('ارسال شد') || msg.includes('ذخیره شد') || msg.includes('success') || 
          msg.includes('done')
      ) {
          type = 'success';
      }

      window.showToast(message, type);
  };

  // =========================================================
  // 5. مودال سفارشی تایید (showConfirm)
  // =========================================================
  window.showConfirm = function (title, message, onConfirm, onCancel = null) {
      initContainers();
      const overlay = document.getElementById('customConfirmOverlay');
      const titleEl = document.getElementById('confirmTitle');
      const msgEl = document.getElementById('confirmMsg');
      const btnYes = document.getElementById('confirmBtnYes');
      const btnNo = document.getElementById('confirmBtnNo');

      titleEl.innerText = title || 'تایید عملیات';
      msgEl.innerText = message || 'آیا مطمئن هستید؟';

      overlay.classList.add('active');

      const newBtnYes = btnYes.cloneNode(true);
      const newBtnNo = btnNo.cloneNode(true);
      btnYes.parentNode.replaceChild(newBtnYes, btnYes);
      btnNo.parentNode.replaceChild(newBtnNo, btnNo);

      const closeConfirm = () => {
          overlay.classList.remove('active');
          document.removeEventListener('keydown', handleKey);
      };

      newBtnYes.addEventListener('click', () => { closeConfirm(); if (typeof onConfirm === 'function') onConfirm(); });
      newBtnNo.addEventListener('click', () => { closeConfirm(); if (typeof onCancel === 'function') onCancel(); });

      const handleKey = (e) => {
          if (e.key === 'Escape') { closeConfirm(); if (typeof onCancel === 'function') onCancel(); } 
          else if (e.key === 'Enter') { closeConfirm(); if (typeof onConfirm === 'function') onConfirm(); }
      };
      document.addEventListener('keydown', handleKey);
  };

})();