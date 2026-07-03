// js/wishlist.js
class WishlistManager {
  constructor() {
      this.apiEndpoint = 'api_wishlist.php';
      this.init();
  }

  init() {
      // پیدا کردن تمام دکمه‌های قلب در صفحه
      document.querySelectorAll('.wishlist-btn').forEach(btn => {
          btn.addEventListener('click', (e) => this.toggleFavorite(e, btn));
      });
  }

  async toggleFavorite(e, clickedBtn) {
      e.preventDefault();
      e.stopPropagation(); // جلوگیری از باز شدن مودالِ خرید

      const productId = clickedBtn.dataset.productId;
      const isCurrentlyFavorited = clickedBtn.classList.contains('favorited');
      const newFavoriteState = !isCurrentlyFavorited;
      
      // 1. تغییر ظاهریِ آنی تمام دکمه‌های مربوط به این ماشین (دسکتاپ و موبایل)
      this.updateUI(productId, newFavoriteState);

      try {
          // 2. ارسال درخواست به سرور در پس‌زمینه
          const response = await fetch(this.apiEndpoint, {
              method: 'POST',
              headers: {
                  'Content-Type': 'application/json',
                  'Accept': 'application/json'
              },
              body: JSON.stringify({ product_id: productId })
          });

          // 3. اگر کاربر لاگین نبود
          if (response.status === 401) {
              this.updateUI(productId, isCurrentlyFavorited); // برگرداندن قلب‌ها به حالت اول
              alert('برای افزودن به علاقه‌مندی‌ها ابتدا وارد حساب کاربری شوید.');
              window.location.href = 'login.php';
              return;
          }

          const data = await response.json();

          // 4. اگر سرور خطا داد
          if (!data.success) {
              this.updateUI(productId, isCurrentlyFavorited); // برگرداندن قلب‌ها به حالت اول
              console.error('Server Error:', data.error);
          }

      } catch (error) {
          // اگر اینترنت قطع بود
          this.updateUI(productId, isCurrentlyFavorited); // برگرداندن قلب‌ها به حالت اول
          console.error('Network Error:', error);
      }
  }

  // متد تغییر استایل و انیمیشن برای تمام قلب‌های هم‌نام
  updateUI(productId, isFavorited) {
      // پیدا کردن تمام دکمه‌هایی که این ID را دارند
      const allButtonsForThisProduct = document.querySelectorAll(`.wishlist-btn[data-product-id="${productId}"]`);
      
      allButtonsForThisProduct.forEach(btn => {
          if (isFavorited) {
              btn.classList.add('favorited');
          } else {
              btn.classList.remove('favorited');
          }
          
          // اجرای انیمیشن پاپ
          btn.classList.add('pop-anim');
          setTimeout(() => btn.classList.remove('pop-anim'), 300);
      });
  }
}

document.addEventListener('DOMContentLoaded', () => {
  new WishlistManager();
});