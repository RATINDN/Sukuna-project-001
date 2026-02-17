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
    // تابع دریافت و نمایش لیست قراردادها (نسخه دو دکمه‌ای)
    // ============================================
    async function loadUserContracts() {
        if(!contractsList) return;
        
        contractsList.innerHTML = '<div class="loader-small">در حال بارگذاری سوابق...</div>';

        try {
            const response = await fetch('get_my_contracts.php');
            const data = await response.json();

            if (data.success) {
                if (data.contracts.length === 0) {
                    contractsList.innerHTML = '<div class="no-data">هنوز هیچ خریدی انجام نداده‌اید.</div>';
                    return;
                }

                let html = '';
                data.contracts.forEach(c => {
                    let statusText = 'در انتظار';
                    let statusClass = 's-pending';
                    let actionButtons = '';

                    // دکمه مشاهده همیشه هست (لینک به پرینت)
                    const viewBtn = `<a href="print_contract.php?id=${c.id}" class="btn-print-sm" target="_blank">📄 مشاهده</a>`;

                    if(c.status === 'pending') {
                        statusText = 'در انتظار پرداخت';
                        statusClass = 's-pending';
                        // برای پندینگ، هم دکمه پرداخت هست، هم مشاهده
                        actionButtons = `
                            <div style="display:flex; gap:5px;">
                                <a href="payment.php?id=${c.id}" class="btn-pay-sm">💳 پرداخت</a>
                                ${viewBtn}
                            </div>
                        `;
                    } 
                    else if(c.status === 'paid') { 
                        statusText = 'نهایی شده';
                        statusClass = 's-paid';
                        actionButtons = viewBtn; // فقط مشاهده
                    }
                    else if(c.status === 'rejected') { 
                        statusText = 'رد شده';
                        statusClass = 's-rejected';
                        actionButtons = viewBtn; // فقط مشاهده (برای دیدن مهر باطل)
                    }

                    let date = new Date(c.created_at).toLocaleDateString('fa-IR');

                    html += `
                        <div class="contract-item">
                            <div class="c-info">
                                <span class="c-name">${c.car_name}</span>
                                <span class="c-date">${date} | ${c.car_price}</span>
                                <span class="c-code">کد: ${c.tracking_code}</span>
                            </div>
                            <div class="c-actions">
                                <span class="status-badge ${statusClass}" style="margin-bottom:5px;">${statusText}</span>
                                ${actionButtons}
                            </div>
                        </div>
                    `;
                });
                contractsList.innerHTML = html;
            } else {
                contractsList.innerHTML = '<div class="no-data">خطا در دریافت اطلاعات</div>';
            }
        } catch (error) {
            console.error(error);
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