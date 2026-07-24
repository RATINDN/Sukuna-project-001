// js/notification.js
class NotificationManager {

  
  constructor() {
      this.bell = document.getElementById('notificationBell');
      this.badge = document.getElementById('notificationBadge');
      this.dropdown = document.getElementById('notificationDropdown');
      this.list = document.getElementById('notifList');
      
      if (this.bell && typeof isLoggedIn !== 'undefined' && isLoggedIn) {
          this.init();

          
      }

      
  }

  

  init() {

    // اتصال دکمه حذف همه
    const clearAllBtn = document.getElementById('clearAllNotifsBtn');
    if (clearAllBtn) {
        clearAllBtn.addEventListener('click', (e) => {
            e.stopPropagation(); // جلوگیری از بسته شدن منو
            this.clearAllNotifications();
        });
    }
      // لود اولیه نوتیفیکیشن‌ها
      this.fetchNotifications();

      // چک کردن اتوماتیک هر 30 ثانیه (بدون رفرش صفحه)
      setInterval(() => this.fetchNotifications(), 30000);

      // باز و بسته کردن دراپ‌دان
      this.bell.addEventListener('click', (e) => {
          e.stopPropagation();
          this.toggleDropdown();
      });

      // بستن با کلیک بیرون
      document.addEventListener('click', (e) => {
          if (!this.bell.contains(e.target) && this.dropdown.style.display === 'flex') {
              this.dropdown.style.display = 'none';
          }
      });
  }

  async fetchNotifications() {
      try {
          const response = await fetch('api_notifications.php');
          if (!response.ok) return;
          const data = await response.json();

          if (data.success) {
              this.updateBadge(data.unread_count);
              this.renderList(data.notifications);
          }
      } catch (error) { console.error("Notif Error:", error); }
  }

  updateBadge(count) {
      if (count > 0) {
          this.badge.innerText = count > 99 ? '99+' : count;
          this.badge.style.display = 'flex';
      } else {
          this.badge.style.display = 'none';
      }
  }
  renderList(notifications) {
    if (notifications.length === 0) {
        this.list.innerHTML = '<div style="padding: 20px; text-align: center; color: gray; font-size: 12px;">هیچ اعلانی ندارید</div>';
        return;
    }

    // تولید کدهای HTML (باگ ظاهری برطرف شد و دکمه ضربدر اضافه شد)
    this.list.innerHTML = notifications.map(n => `
        <div class="notif-item ${n.is_read == 0 ? 'unread' : ''} ${(n.type === 'restock' || n.type === 'new_product') ? 'clickable-notif' : ''}" 
             data-ref-id="${n.reference_id}" 
             ${(n.type === 'restock' || n.type === 'new_product') ? 'style="cursor: pointer;" title="برای مشاهده کلیک کنید"' : ''}>
            
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div class="notif-title">${n.type === 'new_product' ? '🆕 ' : (n.type === 'restock' ? '🔥 ' : '🔔 ')}${n.title}</div>
                
                <!-- دکمه حذف -->
                <button class="delete-notif-btn" data-notif-id="${n.id}" title="حذف اعلان" style="background: none; border: none; color: #999; cursor: pointer; font-size: 18px; line-height: 1; padding: 0 5px; transition: 0.2s;">&times;</button>
            </div>

            <div class="notif-msg">${n.message}</div>
            <span class="notif-time" dir="ltr" style="text-align: left;">${n.created_at}</span>
        </div>
    `).join('');

 // ==========================================
        // 1. لاجیک کلیک روی خود پیام برای باز کردن مودال هوشمند
        // ==========================================
        this.list.querySelectorAll('.clickable-notif').forEach(item => {
            item.addEventListener('click', async (e) => {
                // اگر روی دکمه ضربدر کلیک شده بود، مودال رو باز نکن
                if (e.target.classList.contains('delete-notif-btn')) return;

                const productId = item.getAttribute('data-ref-id');
                
                // بستن منوی نوتیفیکیشن
                this.dropdown.style.display = 'none';
                
                // نشان دادن حالت لودینگ روی نوتیفیکیشن (UX)
                const titleDiv = item.querySelector('.notif-title');
                const originalTitle = titleDiv.innerHTML;
                titleDiv.innerHTML = '⏳ در حال ارتباط...';

                // بررسی می‌کنیم که آیا تابع هوشمند ما در صفحه اصلی وجود دارد یا خیر
                if (typeof window.openSmartModal === 'function') {
                    // فراخوانی مستقیم از دیتابیس بدون نیاز به رفرش
                    await window.openSmartModal(productId);
                    titleDiv.innerHTML = originalTitle;
                } else {
                    // روش قدیمی (فقط برای زمانی که تابع هوشمند لود نشده باشد)
                    const productCard = document.querySelector(`.box[data-id="${productId}"]`) || 
                                        document.querySelector(`.box-mobile[data-id="${productId}"]`);
                    
                    if (productCard) {
                        productCard.click();
                    } else {
                        alert('این خودرو در حال حاضر در صفحه اصلی قابل مشاهده نیست.');
                    }
                    titleDiv.innerHTML = originalTitle;
                }
            });
        });

    // ==========================================
    // 2. لاجیک کلیک روی دکمه حذف پیام (ضربدر)
    // ==========================================
    this.list.querySelectorAll('.delete-notif-btn').forEach(btn => {
        btn.addEventListener('click', async (e) => {
            e.stopPropagation(); // جلوگیری از کلیک روی بدنه پیام
            
            const notifId = btn.getAttribute('data-notif-id');
            const notifItem = btn.closest('.notif-item');

            try {
                // ارسال درخواست حذف به سرور
                const res = await fetch('api_notifications.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'action=delete&notif_id=' + notifId
                });
                
                const data = await res.json();
                
                if (data.success) {
                    // انیمیشن محو شدن پیام
                    notifItem.style.opacity = '0';
                    notifItem.style.transform = 'translateX(20px)';
                    notifItem.style.transition = 'all 0.3s ease';
                    
                    setTimeout(() => {
                        notifItem.remove();
                        // اگر لیست خالی شد، متن "هیچ اعلانی ندارید" را نشان بده
                        if (this.list.children.length === 0) {
                            this.list.innerHTML = '<div style="padding: 20px; text-align: center; color: gray; font-size: 12px;">هیچ اعلانی ندارید</div>';
                        }
                    }, 300);
                }
            } catch(err) {
                console.error('خطا در حذف اعلان:', err);
            }
        });
    });
}

  async toggleDropdown() {
      if (this.dropdown.style.display === 'flex') {
          this.dropdown.style.display = 'none';
      } else {
          this.dropdown.style.display = 'flex';
          // وقتی باز شد، پیام‌ها را "خوانده شده" کن
          if (this.badge.style.display !== 'none') {
              this.badge.style.display = 'none';
              await fetch('api_notifications.php', { method: 'POST' });
              // حذف کلاس unread از ظاهر
              document.querySelectorAll('.notif-item.unread').forEach(el => el.classList.remove('unread'));
          }
      }
  }

  async clearAllNotifications() {
    if (!confirm('آیا از حذف تمام اعلانات مطمئن هستید؟')) return;

    try {
        const res = await fetch('api_notifications.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=delete_all'
        });
        const data = await res.json();
        
        if (data.success) {
            // پاک کردن کل لیست با یک افکت نرم
            this.list.style.opacity = '0';
            setTimeout(() => {
                this.list.innerHTML = '<div style="padding: 20px; text-align: center; color: gray; font-size: 12px;">هیچ اعلانی ندارید</div>';
                this.list.style.opacity = '1';
            }, 300);
        }
    } catch(err) {
        console.error('خطا در حذف کل اعلانات:', err);
    }
}
}

document.addEventListener('DOMContentLoaded', () => {
  new NotificationManager();
});