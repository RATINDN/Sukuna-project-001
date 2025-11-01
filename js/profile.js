/**
 * Profile Section JavaScript
 * Handles user profile display, data fetching, and account management
 */
document.addEventListener('DOMContentLoaded', function() {
    const profileSection = document.getElementById('profileSection');
    const closeProfileBtn = document.getElementById('closeProfileBtn');
    const profileAvatarLarge = document.getElementById('profileAvatarLarge');
    const profileUsername = document.getElementById('profileUsername');
    const profileEmail = document.getElementById('profileEmail');
    const profileID = document.getElementById('profileID');
    const editUsernameBtn = document.getElementById('editUsernameBtn');
    const editUsernameContainer = document.getElementById('editUsernameContainer');
    const newUsernameInput = document.getElementById('newUsername');
    const saveUsernameBtn = document.getElementById('saveUsernameBtn');
    const cancelEditUsernameBtn = document.getElementById('cancelEditUsernameBtn');

    // Check if we should open profile on load (for new registrations)
    const openProfileOnLoad = sessionStorage.getItem('openProfileOnLoad');
    const justRegistered = sessionStorage.getItem('signupSuccess') === '1';
    
    if (openProfileOnLoad) {
        // Clear the flag so it doesn't happen again
        sessionStorage.removeItem('openProfileOnLoad');
        
        // Small delay to ensure session data is available
        setTimeout(function() {
            // Force refresh from database for new registrations
            fetchUserProfile(justRegistered);
        }, 500);
    }

    function openProfile() {
        if (profileSection) {
            fetchUserProfile();
            profileSection.classList.add('active');
        }
    }

    function closeProfile() {
        if (profileSection) {
            profileSection.classList.remove('active');
        }
    }

    document.body.addEventListener('click', function(e) {
        if (e.target.id === 'openProfileLink' || (e.target.parentElement && e.target.parentElement.id === 'openProfileLink')) {
            e.preventDefault();
            openProfile();
        }
    });

    if (closeProfileBtn) {
        closeProfileBtn.addEventListener('click', closeProfile);
    }

    if (profileSection) {
        profileSection.addEventListener('click', function(e) {
            if (e.target === profileSection) {
                closeProfile();
            }
        });
    }

    async function fetchUserProfile(forceRefresh = false) {
        try {
            console.log('Fetching user profile data...');
            
            // Build URL with force_refresh parameter if needed
            let url = 'get_user_profile.php';
            if (forceRefresh) {
                url += '?force_refresh=1';
                console.log('Forcing profile data refresh from database');
            }
            
            const response = await fetch(url, {
                // Add cache-busting parameter to prevent caching
                headers: {
                    'Cache-Control': 'no-cache, no-store, must-revalidate',
                    'Pragma': 'no-cache',
                    'Expires': '0'
                },
                // Add timestamp to prevent caching
                cache: 'no-store'
            });
            
            if (!response.ok) {
                throw new Error('Failed to fetch profile data.');
            }
            
            const data = await response.json();
            console.log('Profile data received:', data);

            if (data.success) {
                profileAvatarLarge.style.backgroundColor = data.avatar_color;
                profileAvatarLarge.textContent = data.username.charAt(0).toUpperCase();
                profileUsername.textContent = data.username;
                profileEmail.textContent = data.email;
                profileID.textContent = 'شناسه کاربری : ' + data.user_id || 'Not provided';
            } else {
                console.error('Error:', data.error);
            }
        } catch (error) {
            console.error('Fetch error:', error);
        }
    }

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
            if (newUsername === '') {
                alert('.نام کاربری نمی تواند خالی باشد');
                return;
            }

            try {
                const response = await fetch('update_username.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        new_username: newUsername,
                    }),
                });

                const result = await response.json();
                if (result.success) {
                    // Show success message
                    alert('  !نام کاربری با موفقیت آپدیت شد ');
                    
                    // Refresh the page to update all instances of the username
                    window.location.reload();
                } else {
                    alert('خطا در آپدیت کردن نام کاربری: ' + result.error);
                }
            } catch (error) {
                console.error('خطا در آپدیت کردن نام کاربری:', error);
                alert('یک خطایی در آپدیت کردن نام کاربری شما رخ داده است.');
            }
        });
    }
    
  
    const deleteAccountBtn = document.getElementById('deleteAccountBtn');
    const confirmationModal = document.getElementById('confirmationModal');
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
    const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');

    if (deleteAccountBtn) {
        deleteAccountBtn.addEventListener('click', () => {
            if (confirmationModal) {
                confirmationModal.style.display = 'flex';
            }
        });
    }

    if (cancelDeleteBtn) {
        cancelDeleteBtn.addEventListener('click', () => {
            if (confirmationModal) {
                confirmationModal.style.display = 'none';
            }
        });
    }

    if (confirmDeleteBtn) {
        confirmDeleteBtn.addEventListener('click', async () => {
            try {
                const response = await fetch('delete_account.php', {
                    method: 'POST',
                });
                const result = await response.json();
                if (result.success) {
                    window.location.href = 'logout.php';
                } else {
                    alert('خطا در حذف کردن حساب: ' + result.error);
                    confirmationModal.style.display = 'none';
                }
            } catch (error) {
                console.error('خطا در حذف کردن حساب:', error);
                alert('.یک خطایی در حذف کردن حساب شما رخ داده است.');
            }
        });
    }

    
});