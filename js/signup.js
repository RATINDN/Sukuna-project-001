document.getElementById('signup-form').addEventListener('submit', function(event) {
  event.preventDefault(); // جلوگیری از ارسال فرم
  
  const fullname = document.getElementById('fullname');
  const email = document.getElementById('email');
  const phone = document.getElementById('phone');
  const password = document.getElementById('password');
  const confirmPassword = document.getElementById('confirm-password');
  let isValid = true;
  
  // پاک کردن پیام‌های خطا
  document.querySelectorAll('.error-message').forEach(function(el) {
    el.remove();
  });
  
  // پاک کردن استایل خطا
  document.querySelectorAll('.input-group input').forEach(function(input) {
    input.style.border = '';
  });
  
  // اعتبارسنجی نام کامل
  if (fullname.value.trim() === '') {
    showError(fullname, 'نام کامل الزامی است');
    isValid = false;
  }
  
  // اعتبارسنجی ایمیل
  if (email.value.trim() === '') {
    showError(email, 'ایمیل الزامی است');
    isValid = false;
  } else {
    const emailPattern = /^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$/;
    if (!emailPattern.test(email.value)) {
      showError(email, 'ایمیل معتبر نیست');
      isValid = false;
    }
  }
  
  // اعتبارسنجی شماره تلفن
  if (phone.value.trim() === '') {
    showError(phone, 'شماره تلفن الزامی است');
    isValid = false;
  } else {
    const phonePattern = /^[0-9]{11}$/;
    if (!phonePattern.test(phone.value)) {
      showError(phone, 'شماره تلفن معتبر نیست');
      isValid = false;
    }
  }
  
  // اعتبارسنجی رمز عبور
  if (password.value.trim() === '') {
    showError(password, 'رمز عبور الزامی است');
    isValid = false;
  } else {
    const passwordPattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[A-Za-z\d]{8,}$/;
    if (!passwordPattern.test(password.value)) {
      showError(password, 'رمز عبور باید حداقل 8 کاراکتر و شامل حروف بزرگ، حروف کوچک و اعداد باشد');
      isValid = false;
    }
  }
  
  // اعتبارسنجی تکرار رمز عبور
  if (confirmPassword.value.trim() === '') {
    showError(confirmPassword, 'تکرار رمز عبور الزامی است');
    isValid = false;
  } else if (password.value !== confirmPassword.value) {
    showError(confirmPassword, 'رمز عبور و تکرار آن باید یکسان باشند');
    isValid = false;
  }
  
  // اگر همه ورودی‌ها معتبر بودند، فرم ارسال شود
  if (isValid) {
    if (emailDuplicate) {
      showError(email, 'این ایمیل قبلاً ثبت شده است');
      return;
    }
    if (phoneDuplicate) {
      showError(phone, 'این شماره تلفن قبلاً ثبت شده است');
      return;
    }
    this.submit();
  }
});

function showError(input, message) {
  // پاک کردن پیام خطای موجود
  clearError(input);
  
  input.style.border = '1px solid red';
  const error = document.createElement('div');
  error.className = 'error-message';
  error.style.color = 'red';
  error.style.fontSize = '0.8rem';
  error.textContent = message;
  
  const errorContainer = input.closest('.input-group').querySelector('.error-container');
  if (errorContainer) {
    errorContainer.appendChild(error);
  } else {
    input.parentNode.appendChild(error);
  }
}

function clearError(input) {
  input.style.border = '';
  const error = input.parentNode.querySelector('.error-message');
  if (error) {
    error.remove();
  }
  
  const errorContainer = input.closest('.input-group').querySelector('.error-container');
  if (errorContainer) {
    errorContainer.textContent = '';
  }
}

document.querySelectorAll('.input-group input').forEach(function(input) {
  input.addEventListener('input', function() {
    clearError(input);
    validateInput(input);
  });
  
  input.addEventListener('blur', function() {
    validateInput(input);
  });
});

function validateInput(input) {
  const id = input.id;
  const value = input.value.trim();
  
  if (id === 'fullname' && value === '') {
    showError(input, 'نام کامل الزامی است');
  }
  
  if (id === 'email') {
    if (value === '') {
      showError(input, 'ایمیل الزامی است');
    } else {
      const emailPattern = /^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$/;
      if (!emailPattern.test(value)) {
        showError(input, 'ایمیل معتبر نیست');
      }
    }
  }
  
  if (id === 'phone') {
    if (value === '') {
      showError(input, 'شماره تلفن الزامی است');
    } else {
      const phonePattern = /^[0-9]{11}$/;
      if (!phonePattern.test(value)) {
        showError(input, 'شماره تلفن معتبر نیست');
      }
    }
  }
  
  if (id === 'password') {
    if (value === '') {
      showError(input, 'رمز عبور الزامی است');
    } else {
      const passwordPattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[A-Za-z\d]{8,}$/;
      if (!passwordPattern.test(value)) {
        showError(input, 'رمز عبور باید حداقل 8 کاراکتر و شامل حروف بزرگ، حروف کوچک و اعداد باشد');
      }
    }
  }
  
  if (id === 'confirm-password') {
    if (value === '') {
      showError(input, 'تکرار رمز عبور الزامی است');
    } else {
      const password = document.getElementById('password').value;
      if (value !== password) {
        showError(input, "رمز عبور و تکرار آن باید یکسان باشند");
      }
    }
  }
}

// --- Duplicate check logic ---
let emailDuplicate = false;
let phoneDuplicate = false;

function checkDuplicate(type, value, input) {
  if (!value) return;
  fetch(`check_duplicate.php?type=${type}&value=${encodeURIComponent(value)}`)
    .then(res => res.json())
    .then(data => {
      if (data.exists) {
        if (type === 'email') {
          showError(input, 'این ایمیل قبلاً ثبت شده است');
          emailDuplicate = true;
        } else if (type === 'phone') {
          showError(input, 'این شماره تلفن قبلاً ثبت شده است');
          phoneDuplicate = true;
        }
      } else {
        if (type === 'email') {
          emailDuplicate = false;
          clearError(input);
        } else if (type === 'phone') {
          phoneDuplicate = false;
          clearError(input);
        }
      }
    })
    .catch(() => {
      // ignore network errors for now
    });
}

document.getElementById('email').addEventListener('blur', function() {
  const value = this.value.trim();
  checkDuplicate('email', value, this);
});

document.getElementById('phone').addEventListener('blur', function() {
  const value = this.value.trim();
  checkDuplicate('phone', value, this);
});

function togglePasswordVisibility(id) {
  const input = document.getElementById(id);
  const inputGroup = input.closest('.input-group');
  const icon = inputGroup.querySelector('.bi-eye');
  const icon2 = inputGroup.querySelector('.bi-eye-slash');
  
  if (input.type === 'password') {
    input.type = 'text';
    icon.style.display = 'none';
    icon2.style.display = 'inline-block';
  } else {
    input.type = 'password';
    icon.style.display = 'inline-block';
    icon2.style.display = 'none';
  }
}
