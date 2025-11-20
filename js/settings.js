/**
 * Settings Modal and Theme Management
 * Handles theme switching, color customization, and settings persistence
 */

// Theme definitions
const themes = {
  'blue-ocean': {
    primary: '#1976D2',
    bg: '#E3F2FD',
    text: '#0D47A1',
    card: '#BBDEFB',
    border: '#90CAF9',
    hover: '#1565C0',
    accent: '#42A5F5'
  },
  'forest-green': {
    primary: '#2E7D32',
    bg: '#E8F5E8',
    text: '#1B5E20',
    card: '#C8E6C9',
    border: '#81C784',
    hover: '#1B5E20',
    accent: '#4CAF50'
  },
  'sunset-orange': {
    primary: '#F57C00',
    bg: '#FFF3E0',
    text: '#E65100',
    card: '#FFE0B2',
    border: '#FFB74D',
    hover: '#EF6C00',
    accent: '#FF9800'
  },
  'purple-dream': {
    primary: '#7B1FA2',
    bg: '#F3E5F5',
    text: '#4A148C',
    card: '#E1BEE7',
    border: '#BA68C8',
    hover: '#6A1B9A',
    accent: '#9C27B0'
  }
};

// Default theme (current black/white)
const defaultTheme = {
  primary: '#000000',
  bg: '#ffffff',
  text: '#000000',
  card: '#ffffff',
  border: '#dddddd',
  hover: '#333333',
  accent: '#000000'
};

class ThemeManager {
  constructor() {
    this.currentTheme = this.loadTheme();
    this.init();
  }

  init() {
    this.setupEventListeners();
    this.applyTheme(this.currentTheme);
    this.updateUI();
  }

  setupEventListeners() {
    // Settings modal toggle
    const settingsLink = document.getElementById('settingsLink');
    if (settingsLink) {
      settingsLink.addEventListener('click', (e) => {
        e.preventDefault();
        this.openSettingsModal();
      });
    }

    // Modal close buttons
    const closeBtn = document.getElementById('settingsCloseBtn');
    if (closeBtn) {
      closeBtn.addEventListener('click', () => this.closeSettingsModal());
    }

    // Click outside modal to close
    const modal = document.getElementById('settingsModal');
    if (modal) {
      modal.addEventListener('click', (e) => {
        if (e.target === modal) {
          this.closeSettingsModal();
        }
      });
    }

    // Theme selection
    document.querySelectorAll('.theme-option').forEach(option => {
      option.addEventListener('click', () => {
        const themeName = option.dataset.theme;
        this.selectTheme(themeName);
      });
    });

    // Color picker changes
    document.querySelectorAll('.color-input').forEach(input => {
      input.addEventListener('input', (e) => {
        this.updateCustomColor(e.target.id, e.target.value);
      });
    });

    // Save and Reset buttons
    const saveBtn = document.getElementById('settingsSaveBtn');
    const resetBtn = document.getElementById('settingsResetBtn');

    if (saveBtn) {
      saveBtn.addEventListener('click', () => this.saveSettings());
    }

    if (resetBtn) {
      resetBtn.addEventListener('click', () => this.resetToDefault());
    }
  }

  openSettingsModal() {
    const modal = document.getElementById('settingsModal');
    if (modal) {
      modal.classList.add('active');
      document.body.style.overflow = 'hidden';
    }
  }

  closeSettingsModal() {
    const modal = document.getElementById('settingsModal');
    if (modal) {
      modal.classList.remove('active');
      document.body.style.overflow = '';
    }
  }

  selectTheme(themeName) {
    if (themes[themeName]) {
      this.currentTheme = { ...themes[themeName] };
      this.applyTheme(this.currentTheme);
      this.updateUI();
    }
  }

  updateCustomColor(inputId, colorValue) {
    const colorMap = {
      'primaryColorPicker': 'primary',
      'bgColorPicker': 'bg',
      'textColorPicker': 'text',
      'accentColorPicker': 'accent'
    };

    const property = colorMap[inputId];
    if (property) {
      this.currentTheme[property] = colorValue;
      this.applyTheme(this.currentTheme);
    }
  }

  applyTheme(theme) {
    // Update CSS custom properties
    document.documentElement.style.setProperty('--theme-primary', theme.primary);
    document.documentElement.style.setProperty('--theme-bg', theme.bg);
    document.documentElement.style.setProperty('--theme-text', theme.text);
    document.documentElement.style.setProperty('--theme-card', theme.card);
    document.documentElement.style.setProperty('--theme-border', theme.border);
    document.documentElement.style.setProperty('--theme-hover', theme.hover);
    document.documentElement.style.setProperty('--theme-accent', theme.accent);

    // Update existing CSS variables for compatibility
    document.documentElement.style.setProperty('--primary-color', theme.primary);
    document.documentElement.style.setProperty('--bg-color', theme.bg);
    document.documentElement.style.setProperty('--text-color', theme.text);
    document.documentElement.style.setProperty('--card-bg', theme.card);
    document.documentElement.style.setProperty('--border-color', theme.border);
    document.documentElement.style.setProperty('--primary-hover', theme.hover);
    document.documentElement.style.setProperty('--opposite-color', theme.primary === '#000000' ? '#ffffff' : '#000000');

    // Update footer colors - always set so they persist correctly regardless of save mode
    document.documentElement.style.setProperty('--footer-bg', theme.footerBg || theme.primary);
    document.documentElement.style.setProperty('--footer-text', theme.footerText || theme.bg);

    // Update body background color
    document.documentElement.style.setProperty('--bg-body-color', theme.bgBody || theme.bg);

    // Update profile background to match body background (only in light mode)
    if (!document.body.classList.contains('darkmode')) {
      document.documentElement.style.setProperty('--profile-bg', theme.bgBody || theme.bg);
    }
  }

  updateUI() {
    // Update theme selection UI
    document.querySelectorAll('.theme-option').forEach(option => {
      option.classList.remove('selected');
    });

    // Find which predefined theme matches current theme
    for (const [themeName, themeData] of Object.entries(themes)) {
      if (this.themesMatch(themeData, this.currentTheme)) {
        const option = document.querySelector(`[data-theme="${themeName}"]`);
        if (option) {
          option.classList.add('selected');
        }
        break;
      }
    }

    // Update color picker values
    const colorInputs = {
      'primaryColorPicker': this.currentTheme.primary,
      'bgColorPicker': this.currentTheme.bg,
      'textColorPicker': this.currentTheme.text,
      'accentColorPicker': this.currentTheme.accent
    };

    Object.entries(colorInputs).forEach(([inputId, value]) => {
      const input = document.getElementById(inputId);
      if (input) {
        input.value = value;
      }
    });
  }

  themesMatch(theme1, theme2) {
    return theme1.primary === theme2.primary &&
           theme1.bg === theme2.bg &&
           theme1.text === theme2.text &&
           theme1.accent === theme2.accent;
  }

  saveSettings() {
    // Save to localStorage
    localStorage.setItem('userTheme', JSON.stringify(this.currentTheme));

    // Show success message
    this.showNotification('تنظیمات با موفقیت ذخیره شد!', 'success');

    // Close modal after a short delay
    setTimeout(() => {
      this.closeSettingsModal();
    }, 1500);
  }

  resetToDefault() {
    this.currentTheme = { ...defaultTheme };
    this.applyTheme(this.currentTheme);
    this.updateUI();

    // Clear saved theme
    localStorage.removeItem('userTheme');

    this.showNotification('تنظیمات به حالت پیش‌فرض بازنشانی شد!', 'info');
  }

  loadTheme() {
    const savedTheme = localStorage.getItem('userTheme');
    if (savedTheme) {
      try {
        return JSON.parse(savedTheme);
      } catch (e) {
        console.warn('Invalid theme data in localStorage');
      }
    }
    return { ...defaultTheme };
  }

  showNotification(message, type = 'info') {
    // Remove existing notifications
    const existingNotifications = document.querySelectorAll('.settings-notification');
    existingNotifications.forEach(notification => notification.remove());

    // Create new notification
    const notification = document.createElement('div');
    notification.className = `settings-notification ${type}`;
    notification.textContent = message;

    // Style the notification
    Object.assign(notification.style, {
      position: 'fixed',
      top: '20px',
      right: '20px',
      backgroundColor: type === 'success' ? '#4CAF50' : '#2196F3',
      color: 'white',
      padding: '12px 20px',
      borderRadius: '8px',
      boxShadow: '0 4px 12px rgba(0,0,0,0.15)',
      zIndex: '10001',
      fontFamily: 'Vazirmatn, sans-serif',
      fontSize: '14px',
      opacity: '0',
      transform: 'translateY(-20px)',
      transition: 'all 0.3s ease'
    });

    document.body.appendChild(notification);

    // Animate in
    setTimeout(() => {
      notification.style.opacity = '1';
      notification.style.transform = 'translateY(0)';
    }, 100);

    // Animate out and remove
    setTimeout(() => {
      notification.style.opacity = '0';
      notification.style.transform = 'translateY(-20px)';
      setTimeout(() => {
        if (notification.parentNode) {
          notification.parentNode.removeChild(notification);
        }
      }, 300);
    }, 3000);
  }
}

// Initialize theme manager when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
  new ThemeManager();
});

// Export for potential use in other scripts
if (typeof module !== 'undefined' && module.exports) {
  module.exports = ThemeManager;
}
