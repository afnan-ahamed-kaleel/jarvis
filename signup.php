<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Create Account | Jarvis AI Companion</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
  
  <style>
    :root {
      --bg-primary: #070a13;
      --bg-secondary: #0f172a;
      --card-bg: rgba(17, 24, 39, 0.68);
      --input-bg: rgba(30, 41, 59, 0.55);
      --input-border: rgba(255, 255, 255, 0.1);
      --text-main: #f8fafc;
      --text-muted: #94a3b8;
      --accent-primary: #3b82f6;
      --accent-secondary: #06b6d4;
      --error-color: #ef4444;
      --success-color: #10b981;
      --warning-color: #f59e0b;
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
      font-family: 'Inter', system-ui, sans-serif;
    }
    
    body {
      min-height: 100vh;
      background: radial-gradient(ellipse at 15% 0%, rgba(29, 78, 216, 0.25), transparent 50%),
                  radial-gradient(ellipse at 85% 100%, rgba(6, 182, 212, 0.22), transparent 50%),
                  linear-gradient(135deg, var(--bg-primary), var(--bg-secondary));
      display: flex;
      justify-content: center;
      align-items: center;
      color: var(--text-main);
      overflow-x: hidden;
      position: relative;
      padding: 24px 16px;
    }

    /* Ambient animated light orbs */
    .orb {
      position: absolute;
      border-radius: 50%;
      filter: blur(80px);
      pointer-events: none;
      z-index: 1;
      opacity: 0.6;
      animation: drift 14s infinite alternate ease-in-out;
    }
    .orb-1 {
      width: 320px;
      height: 320px;
      background: rgba(59, 130, 246, 0.35);
      top: 5%;
      left: 10%;
    }
    .orb-2 {
      width: 280px;
      height: 280px;
      background: rgba(6, 182, 212, 0.3);
      bottom: 10%;
      right: 12%;
      animation-delay: -5s;
    }
    @keyframes drift {
      0% { transform: translate(0, 0) scale(1); }
      100% { transform: translate(35px, -35px) scale(1.1); }
    }

    .app-container {
      width: 100%;
      max-width: 470px;
      z-index: 10;
    }

    .auth-card {
      background: var(--card-bg);
      backdrop-filter: blur(28px) saturate(180%);
      -webkit-backdrop-filter: blur(28px) saturate(180%);
      border: 1px solid rgba(255, 255, 255, 0.12);
      border-radius: 28px;
      padding: 40px 36px;
      box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.6),
                  0 0 35px rgba(59, 130, 246, 0.12),
                  inset 0 1px 0 rgba(255, 255, 255, 0.15);
      animation: cardAppear 0.6s cubic-bezier(0.16, 1, 0.3, 1);
    }
    @keyframes cardAppear {
      from { opacity: 0; transform: translateY(24px) scale(0.96); }
      to { opacity: 1; transform: translateY(0) scale(1); }
    }

    .badge-status {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      background: rgba(16, 185, 129, 0.12);
      color: #34d399;
      border: 1px solid rgba(16, 185, 129, 0.25);
      padding: 5px 14px;
      border-radius: 9999px;
      font-size: 0.75rem;
      font-weight: 600;
      letter-spacing: 0.5px;
      text-transform: uppercase;
      margin-bottom: 22px;
    }
    .pulse-dot {
      width: 6px;
      height: 6px;
      border-radius: 50%;
      background: #10b981;
      box-shadow: 0 0 8px #10b981;
      animation: pulse 2s infinite;
    }
    @keyframes pulse {
      0%, 100% { opacity: 1; transform: scale(1); }
      50% { opacity: 0.4; transform: scale(0.85); }
    }

    .header {
      text-align: left;
      margin-bottom: 28px;
    }
    .title {
      font-family: 'Outfit', sans-serif;
      font-size: 2rem;
      font-weight: 700;
      letter-spacing: -0.5px;
      background: linear-gradient(135deg, #ffffff 0%, #93c5fd 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      margin-bottom: 8px;
    }
    .subtitle {
      font-size: 0.92rem;
      color: var(--text-muted);
      line-height: 1.5;
    }

    /* Alerts */
    .alert {
      padding: 14px 16px;
      border-radius: 14px;
      font-size: 0.88rem;
      font-weight: 500;
      margin-bottom: 24px;
      display: flex;
      align-items: center;
      gap: 12px;
      backdrop-filter: blur(10px);
    }
    .alert-error {
      background: rgba(239, 68, 68, 0.15);
      color: #fca5a5;
      border: 1px solid rgba(239, 68, 68, 0.3);
    }
    .alert-success {
      background: rgba(16, 185, 129, 0.15);
      color: #6ee7b7;
      border: 1px solid rgba(16, 185, 129, 0.3);
    }

    .form-group {
      margin-bottom: 20px;
      position: relative;
    }
    .label {
      display: block;
      font-size: 0.83rem;
      font-weight: 500;
      color: #cbd5e1;
      margin-bottom: 7px;
    }
    .input-wrapper {
      position: relative;
      display: flex;
      align-items: center;
    }
    .input-icon {
      position: absolute;
      left: 15px;
      color: var(--text-muted);
      pointer-events: none;
      transition: color 0.25s ease;
      display: flex;
      align-items: center;
    }
    .form-input {
      width: 100%;
      padding: 13px 15px 13px 44px;
      border-radius: 14px;
      border: 1.5px solid var(--input-border);
      background: var(--input-bg);
      color: var(--text-main);
      font-size: 0.95rem;
      outline: none;
      transition: all 0.25s ease;
    }
    .form-input:focus {
      border-color: var(--accent-primary);
      background: rgba(30, 41, 59, 0.85);
      box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15);
    }
    .form-input:focus ~ .input-icon {
      color: #60a5fa;
    }
    .form-input.valid {
      border-color: var(--success-color);
    }
    .form-input.invalid {
      border-color: var(--error-color);
    }

    /* Password visibility toggle button */
    .toggle-pwd {
      position: absolute;
      right: 14px;
      background: none;
      border: none;
      color: var(--text-muted);
      cursor: pointer;
      padding: 4px;
      display: flex;
      align-items: center;
      transition: color 0.2s ease;
    }
    .toggle-pwd:hover {
      color: var(--text-main);
    }

    /* Validation feedback message */
    .feedback {
      font-size: 0.78rem;
      margin-top: 6px;
      display: flex;
      align-items: center;
      gap: 6px;
      min-height: 18px;
      transition: all 0.2s ease;
    }
    .feedback-error {
      color: #f87171;
    }
    .feedback-success {
      color: #34d399;
    }

    /* Password strength meter */
    .strength-wrapper {
      margin-top: 10px;
    }
    .strength-bars {
      display: flex;
      gap: 5px;
      height: 4px;
      border-radius: 4px;
      overflow: hidden;
    }
    .strength-bar {
      flex: 1;
      background: rgba(255, 255, 255, 0.12);
      border-radius: 2px;
      transition: background 0.3s ease;
    }
    .strength-label {
      font-size: 0.75rem;
      margin-top: 5px;
      color: var(--text-muted);
      display: flex;
      justify-content: space-between;
    }

    .btn-primary {
      width: 100%;
      padding: 15px;
      margin-top: 10px;
      border-radius: 16px;
      border: none;
      cursor: pointer;
      font-size: 1rem;
      font-weight: 600;
      color: white;
      background: linear-gradient(135deg, #2563eb, #3b82f6, #06b6d4);
      background-size: 200% 200%;
      box-shadow: 0 10px 25px -5px rgba(37, 99, 235, 0.45);
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      position: relative;
      overflow: hidden;
    }
    .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 15px 30px -5px rgba(37, 99, 235, 0.6);
      background-position: 100% 0;
    }
    .btn-primary:active {
      transform: translateY(0px);
    }
    
    .footer {
      margin-top: 26px;
      text-align: center;
      font-size: 0.88rem;
      color: var(--text-muted);
    }
    .footer a {
      color: #60a5fa;
      text-decoration: none;
      font-weight: 600;
      transition: color 0.2s;
    }
    .footer a:hover {
      color: #93c5fd;
      text-decoration: underline;
    }

    /* Responsive adjustments */
    @media (max-width: 480px) {
      .auth-card { padding: 32px 24px; border-radius: 24px; }
      .title { font-size: 1.7rem; }
    }
  </style>
</head>
<body>

  <div class="orb orb-1"></div>
  <div class="orb orb-2"></div>

  <div class="app-container">
    <div class="auth-card">
      
      <div class="badge-status">
        <span class="pulse-dot"></span> Gmail AI Security Enabled
      </div>

      <div class="header">
        <h1 class="title">Create Account</h1>
        <p class="subtitle">Join Jarvis, your AI-powered mental wellness companion, with verified Gmail authentication.</p>
      </div>

      <?php if(isset($_SESSION['error'])): ?>
        <div class="alert alert-error">
          <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
          <span><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></span>
        </div>
      <?php endif; ?>

      <?php if(isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
          <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
          <span><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></span>
        </div>
      <?php endif; ?>

      <form id="registerForm" action="signup_process.php" method="POST" novalidate>
        
        <!-- Username Field -->
        <div class="form-group">
          <label class="label" for="username">Username</label>
          <div class="input-wrapper">
            <input type="text" id="username" name="username" class="form-input" placeholder="Choose your unique identifier" required autocomplete="username">
            <div class="input-icon">
              <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            </div>
          </div>
          <div id="userFeedback" class="feedback"></div>
        </div>

        <!-- Gmail Account Field -->
        <div class="form-group">
          <label class="label" for="email">Gmail Address</label>
          <div class="input-wrapper">
            <input type="email" id="email" name="email" class="form-input" placeholder="yourname@gmail.com" required autocomplete="email">
            <div class="input-icon">
              <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><path d="M22 6l-10 7L2 6"/></svg>
            </div>
          </div>
          <div id="emailFeedback" class="feedback"></div>
        </div>

        <!-- Password Field -->
        <div class="form-group">
          <label class="label" for="password">Password</label>
          <div class="input-wrapper">
            <input type="password" id="password" name="password" class="form-input" placeholder="Create a strong password" required autocomplete="new-password">
            <div class="input-icon">
              <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
            </div>
            <button type="button" class="toggle-pwd" onclick="togglePassword('password', this)" aria-label="Toggle password visibility">
              <svg class="eye-icon" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            </button>
          </div>
          
          <div class="strength-wrapper">
            <div class="strength-bars">
              <div class="strength-bar" id="bar1"></div>
              <div class="strength-bar" id="bar2"></div>
              <div class="strength-bar" id="bar3"></div>
              <div class="strength-bar" id="bar4"></div>
            </div>
            <div class="strength-label">
              <span>Security strength</span>
              <strong id="strengthText">Not evaluated</strong>
            </div>
          </div>
        </div>

        <!-- Confirm Password Field -->
        <div class="form-group">
          <label class="label" for="confirm_password">Confirm Password</label>
          <div class="input-wrapper">
            <input type="password" id="confirm_password" class="form-input" placeholder="Repeat your password" required autocomplete="new-password">
            <div class="input-icon">
              <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            </div>
            <button type="button" class="toggle-pwd" onclick="togglePassword('confirm_password', this)" aria-label="Toggle confirm password visibility">
              <svg class="eye-icon" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            </button>
          </div>
          <div id="confirmFeedback" class="feedback"></div>
        </div>

        <button type="submit" class="btn-primary">Create Verified Account</button>
      </form>

      <div class="footer">
        Already have an account? <a href="index.php">Sign In</a>
      </div>

    </div>
  </div>

  <script>
    // Reveal / hide password helper
    function togglePassword(fieldId, btn) {
      const field = document.getElementById(fieldId);
      if (field.type === "password") {
        field.type = "text";
        btn.style.color = "#60a5fa";
      } else {
        field.type = "password";
        btn.style.color = "var(--text-muted)";
      }
    }

    const form = document.getElementById('registerForm');
    const usernameInput = document.getElementById('username');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const confirmInput = document.getElementById('confirm_password');

    const userFeedback = document.getElementById('userFeedback');
    const emailFeedback = document.getElementById('emailFeedback');
    const confirmFeedback = document.getElementById('confirmFeedback');
    const strengthText = document.getElementById('strengthText');

    // Real-time Username validation
    usernameInput.addEventListener('input', () => {
      const val = usernameInput.value.trim();
      if (val.length === 0) {
        userFeedback.innerHTML = '';
        usernameInput.classList.remove('valid', 'invalid');
      } else if (val.length < 3) {
        userFeedback.innerHTML = '<span class="feedback-error">⚠️ Username must be at least 3 characters long</span>';
        usernameInput.classList.add('invalid');
        usernameInput.classList.remove('valid');
      } else if (!/^[a-zA-Z0-9_.-]+$/.test(val)) {
        userFeedback.innerHTML = '<span class="feedback-error">⚠️ Only letters, numbers, dot, underscore and hyphen allowed</span>';
        usernameInput.classList.add('invalid');
        usernameInput.classList.remove('valid');
      } else {
        userFeedback.innerHTML = '<span class="feedback-success">✓ Valid username format</span>';
        usernameInput.classList.add('valid');
        usernameInput.classList.remove('invalid');
      }
    });

    // Real-time Gmail validation
    emailInput.addEventListener('input', () => {
      const val = emailInput.value.trim().toLowerCase();
      const gmailRegex = /^[a-zA-Z0-9._%+-]+@gmail\.com$/i;
      
      if (val.length === 0) {
        emailFeedback.innerHTML = '';
        emailInput.classList.remove('valid', 'invalid');
      } else if (!gmailRegex.test(val)) {
        if (val.includes('@') && !val.endsWith('@gmail.com')) {
          emailFeedback.innerHTML = '<span class="feedback-error">⚠️ Only @gmail.com accounts are permitted in this system</span>';
        } else {
          emailFeedback.innerHTML = '<span class="feedback-error">⚠️ Please enter a complete Gmail address (@gmail.com)</span>';
        }
        emailInput.classList.add('invalid');
        emailInput.classList.remove('valid');
      } else {
        emailFeedback.innerHTML = '<span class="feedback-success">✓ Verified Gmail Address format</span>';
        emailInput.classList.add('valid');
        emailInput.classList.remove('invalid');
      }
    });

    // Real-time Password strength metering
    passwordInput.addEventListener('input', () => {
      const val = passwordInput.value;
      let score = 0;
      
      if (val.length >= 8) score++;
      if (/[A-Z]/.test(val) && /[a-z]/.test(val)) score++;
      if (/\d/.test(val)) score++;
      if (/[^A-Za-z0-9]/.test(val)) score++;

      const bars = [document.getElementById('bar1'), document.getElementById('bar2'), document.getElementById('bar3'), document.getElementById('bar4')];
      bars.forEach(bar => bar.style.background = "rgba(255, 255, 255, 0.12)");

      if (val.length === 0) {
        strengthText.textContent = "Not evaluated";
        strengthText.style.color = "var(--text-muted)";
      } else if (val.length < 6) {
        bars[0].style.background = "#ef4444";
        strengthText.textContent = "Too short";
        strengthText.style.color = "#ef4444";
      } else {
        const colors = ["#ef4444", "#f59e0b", "#3b82f6", "#10b981"];
        const labels = ["Weak", "Fair", "Good", "Excellent"];
        const idx = Math.min(Math.max(score - 1, 0), 3);
        
        for (let i = 0; i <= idx; i++) {
          bars[i].style.background = colors[idx];
        }
        strengthText.textContent = labels[idx];
        strengthText.style.color = colors[idx];
      }

      // Re-trigger confirm check if already typed
      if (confirmInput.value.length > 0) checkConfirmPassword();
    });

    function checkConfirmPassword() {
      if (confirmInput.value.length === 0) {
        confirmFeedback.innerHTML = '';
        confirmInput.classList.remove('valid', 'invalid');
        return;
      }
      if (confirmInput.value !== passwordInput.value) {
        confirmFeedback.innerHTML = '<span class="feedback-error">⚠️ Passwords do not match</span>';
        confirmInput.classList.add('invalid');
        confirmInput.classList.remove('valid');
      } else {
        confirmFeedback.innerHTML = '<span class="feedback-success">✓ Passwords match</span>';
        confirmInput.classList.add('valid');
        confirmInput.classList.remove('invalid');
      }
    }

    confirmInput.addEventListener('input', checkConfirmPassword);

    // Final Form Submission Verification
    form.addEventListener('submit', (e) => {
      let valid = true;
      const gmailRegex = /^[a-zA-Z0-9._%+-]+@gmail\.com$/i;

      if (!gmailRegex.test(emailInput.value.trim())) {
        emailInput.focus();
        emailInput.classList.add('invalid');
        emailFeedback.innerHTML = '<span class="feedback-error">⚠️ A valid @gmail.com address is required to create an account</span>';
        valid = false;
      }
      if (usernameInput.value.trim().length < 3) {
        usernameInput.classList.add('invalid');
        valid = false;
      }
      if (passwordInput.value.length < 6) {
        passwordInput.classList.add('invalid');
        valid = false;
      }
      if (confirmInput.value !== passwordInput.value) {
        confirmInput.classList.add('invalid');
        confirmFeedback.innerHTML = '<span class="feedback-error">⚠️ Passwords do not match</span>';
        valid = false;
      }

      if (!valid) {
        e.preventDefault();
        // Shake micro-animation for feedback
        const card = document.querySelector('.auth-card');
        card.style.transform = 'translateX(8px)';
        setTimeout(() => { card.style.transform = 'translateX(-8px)'; }, 80);
        setTimeout(() => { card.style.transform = 'translateX(4px)'; }, 160);
        setTimeout(() => { card.style.transform = 'translateY(0) scale(1)'; }, 240);
      }
    });
  </script>
</body>
</html>