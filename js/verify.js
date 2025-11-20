/**
 * Verification page JavaScript
 * Handles countdown timer and resend functionality
 */

document.addEventListener('DOMContentLoaded', function() {
    // Elements
    const verificationForm = document.getElementById('verification-form');
    const verificationCode = document.getElementById('verification_code');
    const resendBtn = document.getElementById('resend-code-btn');
    const countdownEl = document.getElementById('countdown');
    const resendTimerMessage = document.getElementById('resend-timer-message');
    const errorMessage = document.getElementById('error-message');
    
    // Initial countdown time in seconds (30 seconds)
    // This should match the value displayed in verify.php
    let countdownTime = 30;
    let countdownInterval;
    let countdownEndTime;
    
    // Start countdown on page load
    initializeCountdown();
    
    /**
     * Initialize countdown based on stored end time or start a new countdown
     */
    function initializeCountdown() {
        // Check if there's a stored end time
        const storedEndTime = sessionStorage.getItem('verificationCountdownEnd');
        
        if (storedEndTime) {
            // Calculate remaining time
            const now = Math.floor(Date.now() / 1000);
            const endTime = parseInt(storedEndTime);
            const remainingTime = endTime - now;
            
            if (remainingTime > 0) {
                // Resume countdown with remaining time
                countdownTime = remainingTime;
                countdownEndTime = endTime;
                startCountdown();
} else {
    // Countdown has expired - clear storage and enable button
    sessionStorage.removeItem('verificationCountdownEnd');
    enableResendButton();
}
} else {
    // Fresh page load - enable resend button, no countdown
    enableResendButton();
}
    }
    
    /**
     * Start countdown timer for resend button
     */
    function startCountdown() {
        // Disable resend button during countdown
        resendBtn.disabled = true;
        resendTimerMessage.style.display = 'block';
        
        // Set end time if not already set
        if (!countdownEndTime) {
            countdownEndTime = Math.floor(Date.now() / 1000) + countdownTime;
            // Store end time in session storage
            sessionStorage.setItem('verificationCountdownEnd', countdownEndTime);
        }
        
        // Update countdown display
        updateCountdownDisplay();
        
        // Set interval to update countdown every second
        countdownInterval = setInterval(function() {
            const now = Math.floor(Date.now() / 1000);
            countdownTime = countdownEndTime - now;
            
            if (countdownTime <= 0) {
                // Enable resend button when countdown reaches zero
                enableResendButton();
            } else {
                // Update countdown display
                updateCountdownDisplay();
            }
        }, 1000);
    }
    
    /**
     * Enable resend button and clear countdown
     */
    function enableResendButton() {
        clearInterval(countdownInterval);
        resendBtn.disabled = false;
        resendTimerMessage.style.display = 'none';
        sessionStorage.removeItem('verificationCountdownEnd');
    }
    
    /**
     * Update countdown display with seconds only
     */
    function updateCountdownDisplay() {
        // For a 30-second timer, just show seconds
        countdownEl.textContent = Math.max(0, countdownTime);
    }
    
    /**
     * Handle resend button click
     */
    if (resendBtn) {
        console.log('Resend button found, adding event listener');
        resendBtn.addEventListener('click', function(e) {
            console.log('Resend button clicked');
            e.preventDefault();
            
            // Show loading state
            resendBtn.disabled = true;
            resendBtn.textContent = 'در حال ارسال...';
            
            // Log session storage state
            console.log('Session storage before resend:', {
                countdownEndTime: sessionStorage.getItem('verificationCountdownEnd')
            });
            
            // Send AJAX request to resend verification code
            console.log('Sending AJAX request to resend_code.php');
            fetch('resend_code.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'resend'
                })
            })
            .then(response => {
                console.log('Response received:', response);
                if (!response.ok) {
                    throw new Error('Network response was not ok: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                console.log('Response data:', data);
                if (data.success) {
                    console.log('Resend successful, resetting countdown');
                    // Reset countdown and start again
                    countdownTime = 30;
                    countdownEndTime = Math.floor(Date.now() / 1000) + countdownTime;
                    sessionStorage.setItem('verificationCountdownEnd', countdownEndTime);
                    startCountdown();
                    
                    // Success message is now shown by PHP via $_SESSION['email_just_sent']
                    // Reload the page to show the success message and reset the UI
                    console.log('Reloading page...');
                    window.location.reload();
                } else {
                    console.error('Resend failed:', data.message);
                    // Show error message
                    showMessage(data.message || 'خطا در ارسال مجدد کد. لطفاً دوباره تلاش کنید.', 'error');
                    resendBtn.disabled = false;
                }
                
                // Reset button text
                resendBtn.textContent = 'ارسال مجدد کد';
            })
            .catch(error => {
                console.error('Error in fetch operation:', error);
                showMessage('خطایی رخ داد. لطفاً دوباره تلاش کنید.', 'error');
                resendBtn.disabled = false;
                resendBtn.textContent = 'ارسال مجدد کد';
            });
        });
    }
    
    /**
     * Handle verification form submission
     */
    if (verificationForm) {
        verificationForm.addEventListener('submit', function(e) {
            // Clear any existing error messages
            errorMessage.textContent = '';
            errorMessage.style.display = 'none';
            
            // Validate verification code
            if (!verificationCode.value.trim() || verificationCode.value.length !== 6) {
                e.preventDefault();
                showMessage('لطفاً یک کد تایید 6 رقمی معتبر وارد کنید.', 'error');
                return false;
            }
            
            // Form will submit normally if validation passes
        });
    }
    
    /**
     * Show message with specified type (error or success)
     */
    function showMessage(message, type) {
        if (type === 'error') {
            errorMessage.textContent = message;
            errorMessage.style.display = 'block';
        } else if (type === 'success') {
            // Create success message element if it doesn't exist
            let successMessage = document.getElementById('success-message');
            if (!successMessage) {
                successMessage = document.createElement('div');
                successMessage.id = 'success-message';
                successMessage.className = 'success-message';
                verificationForm.appendChild(successMessage);
            }
            
            successMessage.textContent = message;
            successMessage.style.display = 'block';
            
            // Hide success message after 5 seconds
            setTimeout(() => {
                successMessage.style.display = 'none';
            }, 5000);
        }
    }
    
    /**
     * Format verification code input and disable copy/paste
     */
    if (verificationCode) {
        verificationCode.addEventListener('input', function() {
            // Remove non-digit characters
            this.value = this.value.replace(/\D/g, '');

            // Limit to 6 digits
            if (this.value.length > 6) {
                this.value = this.value.slice(0, 6);
            }
        });

        // Disable copy/paste and context menu for security
        verificationCode.addEventListener('paste', function(e) {
            e.preventDefault();
            return false;
        });

        verificationCode.addEventListener('copy', function(e) {
            e.preventDefault();
            return false;
        });

        verificationCode.addEventListener('contextmenu', function(e) {
            e.preventDefault();
            return false;
        });

        verificationCode.addEventListener('keydown', function(e) {
            // Prevent keyboard shortcuts Ctrl+C, Ctrl+V, Ctrl+X
            if (e.ctrlKey && (e.keyCode === 67 || e.keyCode === 86 || e.keyCode === 88)) {
                e.preventDefault();
                return false;
            }
        });
    }
});
