/**
 * Profile Section JavaScript (Updated with Contracts History)
 */
document.addEventListener('DOMContentLoaded', function() {
    const profileSection = document.getElementById('profileSection');
    const closeProfileBtn = document.getElementById('closeProfileBtn');
    const profileAvatarLarge = document.getElementById('profileAvatarLarge');
    const profileUsername = document.getElementById('profileUsername');
    const profileEmail = document.getElementById('profileEmail');
    const profileID = document.getElementById('profileID');
    const contractsList = document.getElementById('contractsList'); // کانتینر لیست قراردادها

    // Check for auto-open
    if (sessionStorage.getItem('openProfileOnLoad')) {
        sessionStorage.removeItem('openProfileOnLoad');
        setTimeout(() => fetchUserProfile(true), 500);
    }

    function openProfile() {
        if (profileSection) {
            fetchUserProfile();
            loadUserContracts(); // <--- این خط جدید اضافه شد
            profileSection.classList.add('active');
        }
    }

    function closeProfile() {
        if (profileSection) {
            profileSection.classList.remove('active');
        }
    }

    // باز کردن پروفایل
    document.body.addEventListener('click', function(e) {
        if (e.target.id === 'openProfileLink' || (e.target.parentElement && e.target.parentElement.id === 'openProfileLink')) {
            e.preventDefault();
            openProfile();
        }
    });

    if (closeProfileBtn) closeProfileBtn.addEventListener('click', closeProfile);
    if (profileSection) profileSection.addEventListener('click', (e) => { if (e.target === profileSection) closeProfile(); });

    // دریافت اطلاعات کاربر
    async function fetchUserProfile(forceRefresh = false) {
        try {
            let url = 'get_user_profile.php';
            if (forceRefresh) url += '?force_refresh=1';
            
            const response = await fetch(url, { cache: 'no-store' });
            const data = await response.json();

            if (data.success) {
                profileAvatarLarge.style.backgroundColor = data.avatar_color;
                profileAvatarLarge.textContent = data.username.charAt(0).toUpperCase();
                profileUsername.textContent = data.username;
                profileEmail.textContent = data.email;
                profileID.textContent = 'شناسه کاربری: ' + data.user_id;
            }
        } catch (error) {
            console.error('Fetch error:', error);
        }
    }

  
   // ============================================
    // تابع دریافت و نمایش گاراژ اختصاصی VIP
    // ============================================
    // ============================================
    // تابع دریافت و نمایش گاراژ اختصاصی VIP
    // ============================================
    async function loadUserContracts() {
        const contractsList = document.getElementById('contractsList');
        if(!contractsList) return;
        
        contractsList.innerHTML = '<div class="loader-small">در حال باز کردن درب گاراژ...</div>';

        try {
            const response = await fetch('get_my_contracts.php');
            const data = await response.json();

            if (data.success) {
                document.querySelector('.contracts-title').innerText = "گاراژ اختصاصی من";

                // ساخت بج رتبه VIP و دکمه مشاهده مزایا
                let rankHtml = `
                    <div style="background: ${data.rankColor}15; border: 1px solid ${data.rankColor}; padding: 15px; border-radius: 12px; margin-bottom: 20px; text-align: center; position: relative;">
                        <span style="color: ${data.rankColor}; font-weight: 900; font-size: 1.3rem;">${data.rank}</span><br>
                        <span style="font-size: 0.85rem; color: var(--secondary-text); display: block; margin-top: 5px;">شما تاکنون مالک <b>${data.paidCount}</b> دستگاه خودرو از ما شده‌اید.</span>
                        <button onclick="document.getElementById('vipClubModal').style.display='flex'" style="margin-top: 15px; background: transparent; border: 1px solid ${data.rankColor}; color: ${data.rankColor}; padding: 8px 20px; border-radius: 50px; font-size: 0.85rem; cursor: pointer; font-family: 'Vazirmatn'; font-weight: bold; transition: all 0.3s;" onmouseover="this.style.background='${data.rankColor}'; this.style.color='#fff';" onmouseout="this.style.background='transparent'; this.style.color='${data.rankColor}';">
                            🏆 مشاهده مزایای سطوح VIP
                        </button>
                    </div>
                `;

                // اگر گاراژ خالی بود (کاربر هیچی نخریده)
                if (data.contracts.length === 0) {
                    contractsList.innerHTML = rankHtml + `
                        <div style="text-align:center; padding: 30px 10px; background: var(--card-bg); border-radius: 12px; border: 1px dashed var(--border-color);">
                            <div style="font-size: 40px; margin-bottom: 10px; opacity: 0.5;">🏎️</div>
                            <p style="color: var(--secondary-text); margin-bottom: 15px;">گاراژ رویایی شما در حال حاضر خالی است.</p>
                            <button onclick="document.getElementById('profileSection').classList.remove('active'); window.scrollTo({top: document.querySelector('.container1').offsetTop, behavior: 'smooth'});" style="background: #2196F3; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-family: 'Vazirmatn'; font-weight: bold;">
                                بازدید از نمایشگاه و خرید 
                            </button>
                        </div>
                    `;
                    return;
                }

                // اگر ماشین خریده بود، لیست ماشین‌ها رو نشون بده
                let html = rankHtml + '<div style="display: grid; grid-template-columns: 1fr; gap: 15px;">';

                data.contracts.forEach(c => {
                    let statusText = ''; let statusColor = ''; let actionButtons = '';
                    const viewBtn = `<a href="print_contract.php?id=${c.id}" target="_blank" style="background:#2196F3; color:white; padding:5px 10px; border-radius:5px; text-decoration:none; font-size:12px;">📄 سند</a>`;

                    if(c.status === 'pending') {
                        statusText = '⏳ در انتظار'; statusColor = '#ff9800';
                        actionButtons = `<div style="display:flex; gap:5px;"><a href="payment.php?id=${c.id}" style="background:#4CAF50; color:white; padding:5px 10px; border-radius:5px; text-decoration:none; font-size:12px; font-weight:bold;">💳 پرداخت</a>${viewBtn}</div>`;
                    } 
                    else if(c.status === 'paid') { 
                        statusText = '✅ مالکیت قطعی'; statusColor = '#4CAF50'; actionButtons = viewBtn;
                    }
                    else if(c.status === 'rejected') { 
                        statusText = '❌ لغو شده'; statusColor = '#f44336'; actionButtons = viewBtn;
                    }

                    let imgSrc = (c.image && c.image !== '') ? c.image : 'images/car-1.webp';

                    html += `
                        <div style="background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 12px; overflow: hidden; display: flex; flex-direction: column;">
                            <div style="width: 100%; height: 120px; position: relative; background: #111;">
                                <img src="${imgSrc}" style="width: 100%; height: 100%; object-fit: cover; opacity: 0.9;">
                                <span style="position: absolute; top: 10px; right: 10px; background: ${statusColor}; color: white; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: bold;">${statusText}</span>
                            </div>
                            <div style="padding: 15px; text-align: right;">
                                <h4 style="margin: 0 0 5px 0; color: var(--text-color);">${c.car_name}</h4>
                                <p style="font-size:11px; color:var(--secondary-text); margin: 5px 0;">رنگ: <b>${c.car_color}</b> | کد: ${c.tracking_code}</p>
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 15px; border-top: 1px dashed var(--border-color); padding-top: 10px;">
                                    <span style="font-weight: bold; color: #4CAF50; font-size: 13px;">${c.car_price}</span>
                                    ${actionButtons}
                                </div>
                            </div>
                        </div>
                    `;
                });
                html += '</div>';
                contractsList.innerHTML = html;
            } else {
                contractsList.innerHTML = '<div class="no-data">خطا در دریافت اطلاعات</div>';
            }
        } catch (error) {
            contractsList.innerHTML = '<div class="no-data">خطای ارتباط با سرور</div>';
        }
    }
    // --- بقیه کدهای قبلی (ویرایش نام، حذف اکانت و...) ---
    // (این قسمت‌ها تغییری نکرده‌اند و فقط برای تکمیل فایل آورده شده‌اند)
    
    const editUsernameBtn = document.getElementById('editUsernameBtn');
    const editUsernameContainer = document.getElementById('editUsernameContainer');
    const newUsernameInput = document.getElementById('newUsername');
    const saveUsernameBtn = document.getElementById('saveUsernameBtn');
    const cancelEditUsernameBtn = document.getElementById('cancelEditUsernameBtn');

    if (editUsernameBtn) {
        editUsernameBtn.addEventListener('click', () => {
            profileUsername.style.display = 'none';
            editUsernameContainer.style.display = 'block';
            newUsernameInput.value = profileUsername.textContent;
            editUsernameBtn.style.display = 'none';
        });
    }

    if (cancelEditUsernameBtn) {
        cancelEditUsernameBtn.addEventListener('click', () => {
            profileUsername.style.display = 'inline-block';
            editUsernameContainer.style.display = 'none';
            editUsernameBtn.style.display = 'inline-block';
        });
    }

    if (saveUsernameBtn) {
        saveUsernameBtn.addEventListener('click', async () => {
            const newUsername = newUsernameInput.value.trim();
            if (!newUsername) return alert('نام کاربری نمی‌تواند خالی باشد');

            try {
                const response = await fetch('update_username.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({ new_username: newUsername }),
                });
                const result = await response.json();
                if (result.success) {
                    alert('نام کاربری آپدیت شد');
                    window.location.reload();
                } else {
                    alert(result.error);
                }
            } catch (error) {
                alert('خطا در ارتباط');
            }
        });
    }

    // حذف اکانت
    const deleteAccountBtn = document.getElementById('deleteAccountBtn');
    const confirmationModal = document.getElementById('confirmationModal');
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
    const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');

    if (deleteAccountBtn) deleteAccountBtn.addEventListener('click', () => confirmationModal.style.display = 'flex');
    if (cancelDeleteBtn) cancelDeleteBtn.addEventListener('click', () => confirmationModal.style.display = 'none');

    if (confirmDeleteBtn) {
        confirmDeleteBtn.addEventListener('click', async () => {
            try {
                const response = await fetch('delete_account.php', { method: 'POST' });
                const result = await response.json();
                if (result.success) window.location.href = 'logout.php';
                else alert(result.error);
            } catch (error) {
                alert('خطا در حذف حساب');
            }
        });
    }
});