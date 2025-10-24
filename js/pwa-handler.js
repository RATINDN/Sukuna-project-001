// PWA Registration and Update Handler

// Register the service worker
if ('serviceWorker' in navigator) {
  window.addEventListener('load', () => {
    navigator.serviceWorker.register('/service-worker.js')
      .then((registration) => {
        console.log('Service Worker registered:', registration);
        
        // Check for updates
        registration.addEventListener('updatefound', () => {
          const newWorker = registration.installing;
          console.log('New service worker installing...');
          
          newWorker.addEventListener('statechange', () => {
            if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
              showUpdateNotification();
            }
          });
        });
      })
      .catch((error) => {
        console.log('Service Worker registration failed:', error);
      });
      
    // Handle controller change (when a new service worker takes over)
    navigator.serviceWorker.addEventListener('controllerchange', () => {
      console.log('New service worker activated');
    });
    
    // Request a cache update when back online
    window.addEventListener('online', () => {
      if (navigator.serviceWorker.controller) {
        // Register a background sync if available
        if ('sync' in navigator.serviceWorker) {
          navigator.serviceWorker.ready
            .then(registration => registration.sync.register('update-cache'))
            .catch(err => console.log('Background sync registration failed:', err));
        }
        
        showOnlineNotification();
      }
    });
    
    // Show offline notification
    window.addEventListener('offline', () => {
      showOfflineNotification();
    });
  });
}

// Show a notification that an update is available
function showUpdateNotification() {
  const notification = document.createElement('div');
  notification.className = 'update-notification';
  notification.innerHTML = `
    <div class="notification-content">
      <p>به‌روزرسانی جدید در دسترس است</p>
      <button id="update-button">به‌روزرسانی</button>
    </div>
  `;
  
  document.body.appendChild(notification);
  
  document.getElementById('update-button').addEventListener('click', () => {
    // Tell the service worker to skipWaiting
    navigator.serviceWorker.controller.postMessage({ type: 'SKIP_WAITING' });
    
    // Remove the notification
    notification.remove();
    
    // Reload the page to activate the new service worker
    window.location.reload();
  });
}

// Show a notification when the app goes offline
function showOfflineNotification() {
  const offlineNotification = document.createElement('div');
  offlineNotification.className = 'offline-notification';
  offlineNotification.innerHTML = `
    <div class="notification-content">
      <p>شما آفلاین هستید. برخی از ویژگی‌ها ممکن است در دسترس نباشند.</p>
    </div>
  `;
  
  document.body.appendChild(offlineNotification);
  
  // Remove notification after 3 seconds
  setTimeout(() => {
    offlineNotification.remove();
  }, 3000);
}

// Show a notification when the app goes back online
function showOnlineNotification() {
  const onlineNotification = document.createElement('div');
  onlineNotification.className = 'online-notification';
  onlineNotification.innerHTML = `
    <div class="notification-content">
      <p>شما آنلاین هستید. همه ویژگی‌ها در دسترس هستند.</p>
    </div>
  `;
  
  document.body.appendChild(onlineNotification);
  
  // Remove notification after 3 seconds
  setTimeout(() => {
    onlineNotification.remove();
  }, 3000);
}

// Add some basic styles for the notifications
const style = document.createElement('style');
style.textContent = `
  .update-notification, .offline-notification, .online-notification {
    position: fixed;
    bottom: 20px;
    left: 20px;
    right: 20px;
    background: white;
    padding: 15px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.2);
    z-index: 1000;
    font-family: 'Vazirmatn', sans-serif;
    text-align: center;
  }
  
  .offline-notification {
    background: #ffcc00;
  }
  
  .online-notification {
    background: #4CAF50;
    color: white;
  }
  
  .notification-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  
  #update-button {
    background: #4CAF50;
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 4px;
    cursor: pointer;
  }
`;
document.head.appendChild(style);