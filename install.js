let deferredPrompt;
const installBtnIds = ['installButton', 'installButton2'];

function isPWAInstalled() {
  return window.matchMedia('(display-mode: standalone)').matches || 
         navigator.standalone ||
         (window.navigator.standalone !== undefined && window.navigator.standalone);
}

function showSuccessPopup() {
  const popup = document.createElement('div');
  popup.innerHTML = `
    <div style="
      position: fixed;
      top: 20px;
      left: 50%;
      transform: translateX(-50%);
      background-color: #4CAF50;
      color: white;
      padding: 15px 25px;
      border-radius: 5px;
      z-index: 1000;
      font-family: 'Vazirmatn', sans-serif;
      box-shadow: 0 4px 8px rgba(0,0,0,0.2);
      animation: slideIn 0.5s, fadeOut 0.5s 2.5s forwards;
    ">
      نصب با موفقیت انجام شد!
    </div>
  `;
  const style = document.createElement('style');
  style.textContent = `
    @keyframes slideIn {
      from { top: -50px; opacity: 0; }
      to { top: 20px; opacity: 1; }
    }
    @keyframes fadeOut {
      to { opacity: 0; visibility: hidden; }
    }
  `;
  document.head.appendChild(style);
  document.body.appendChild(popup);
  setTimeout(() => {
    popup.remove();
    style.remove();
  }, 3000);
}

function manageInstallButtons() {
  const shouldHide = isPWAInstalled();
  installBtnIds.forEach(id => {
    const btn = document.getElementById(id);
    if (btn) {
      if (shouldHide) {
        btn.style.display = 'none';
        btn.style.visibility = 'hidden';
      } else {
        btn.style.display = 'flex';
        btn.style.visibility = 'visible';
      }
    }
  });
}

function isIOS() {
  const userAgent = navigator.userAgent || navigator.platform || '';
  const isIOSDevice = /iPhone|iPad|iPod/i.test(userAgent);
  const isMacLike = /Mac/i.test(userAgent) && ('ontouchend' in document);
  return isIOSDevice || isMacLike;
}

function showIosInstallModal() {
  const modal = document.getElementById('iosInstallModal');
  console.log('Modal found:', modal);
  if (modal) {
    modal.style.display = 'flex';
    console.log('Modal display set to flex');
  } else {
    console.error('iOS Install Modal not found in the DOM');
  }
}

function closeIosInstallModal() {
  const modal = document.getElementById('iosInstallModal');
  if (modal) {
    modal.style.display = 'none';
  }
}

function showCustomInstallPrompt() {
  console.log('showCustomInstallPrompt called');
  console.log('isIOS:', isIOS());
  if (isIOS()) {
    showIosInstallModal();
  } else if (deferredPrompt) {
    deferredPrompt.prompt();
    deferredPrompt.userChoice.then(choice => {
      if (choice.outcome === 'accepted') {
        showSuccessPopup();
        manageInstallButtons();
      }
      deferredPrompt = null;
    });
  }
}

window.addEventListener('beforeinstallprompt', (e) => {
  e.preventDefault();
  deferredPrompt = e;
  installBtnIds.forEach(id => {
    const btn = document.getElementById(id);
    if (btn) {
      btn.style.display = 'flex';
      btn.addEventListener('click', showCustomInstallPrompt);
    }
  });
});

window.addEventListener('appinstalled', () => {
  showSuccessPopup();
  manageInstallButtons();
});

document.addEventListener('DOMContentLoaded', () => {
  installBtnIds.forEach(id => {
    const btn = document.getElementById(id);
    if (btn) {
      btn.addEventListener('click', showCustomInstallPrompt);
    }
  });

  const closeIosModalBtn = document.getElementById('closeIosModal');
  if (closeIosModalBtn) {
    closeIosModalBtn.addEventListener('click', closeIosInstallModal);
  }

  manageInstallButtons();
});