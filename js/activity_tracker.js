/**
 * Activity Tracker - Updates user activity timestamps
 * Simple, optimized JavaScript-based activity tracking
 */
(function() {
    'use strict';

    // Configuration
    const UPDATE_INTERVAL = 30000; // 30 seconds (debounced)
    const API_ENDPOINT = 'update_activity.php';

    // State
    let lastUpdate = 0;
    let updateTimeout;

    /**
     * Check if user is logged in by checking for session indicators
     */
    function isLoggedIn() {
        // Check for common login indicators
        return document.querySelector('.profile-avatar') !== null ||
               document.querySelector('#logout') !== null ||
               document.querySelector('.logged-in') !== null ||
               // Check if we have a profile dropdown
               document.querySelector('#profileDropdown') !== null;
    }

    /**
     * Update user activity timestamp on server
     */
    function updateActivity() {
        // Only update if enough time has passed
        const now = Date.now();
        if (now - lastUpdate < UPDATE_INTERVAL) {
            // Schedule update for later
            if (!updateTimeout) {
                updateTimeout = setTimeout(() => {
                    sendUpdate();
                }, UPDATE_INTERVAL - (now - lastUpdate));
            }
            return;
        }

        sendUpdate();
    }

    /**
     * Send the actual network request
     */
    function sendUpdate() {
        lastUpdate = Date.now();
        updateTimeout = null;

        // Simple fetch with minimal headers
        fetch(API_ENDPOINT, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            credentials: 'same-origin', // Send cookies for session
            body: 'activity_update=1'
        }).catch(error => {
            // Silently fail - don't interrupt user experience
            console.debug('Activity update failed:', error);
        });
    }

    /**
     * Activity event handler
     */
    function handleActivity() {
        if (!isLoggedIn()) return;

        updateActivity();
    }

    /**
     * Initialize the activity tracker
     */
    function init() {
        // Only initialize for authenticated pages
        if (!isLoggedIn()) {
            return;
        }

        // Event listeners for user activity
        document.addEventListener('mousemove', handleActivity);
        document.addEventListener('keydown', handleActivity);
        document.addEventListener('touchstart', handleActivity); // Mobile touch
        document.addEventListener('scroll', handleActivity);

        // Initial update when page loads
        updateActivity();

        // Cleanup on page unload
        window.addEventListener('beforeunload', () => {
            // Final activity update before leaving
            sendUpdate();
        });

        console.debug('Activity tracker initialized');
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();
