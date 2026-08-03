<?php 
session_start(); 
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign In | Jarvis AI Companion</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/responsive.css">
  
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
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
      font-family: 'Inter', system-ui, sans-serif;
    }
    
    body {
      min-height: 100vh;
      background: radial-gradient(ellipse at 80% 10%, rgba(59, 130, 246, 0.25), transparent 50%),
                  radial-gradient(ellipse at 20% 90%, rgba(6, 182, 212, 0.25), transparent 50%),
                  linear-gradient(135deg, var(--bg-primary), var(--bg-secondary));
      display: flex;
      justify-content: center;
      align-items: center;
      color: var(--text-main);
      overflow-x: hidden;
      position: relative;
      padding: 24px 16px;
    }

    /* Animated background light orbs */
    .orb {
      position: absolute;
      border-radius: 50%;
      filter: blur(85px);
      pointer-events: none;
      z-index: 1;
      opacity: 0.65;
      animation: drift 16s infinite alternate ease-in-out;
    }
    .orb-1 {
      width: 350px;
      height: 350px;
      background: rgba(37, 99, 235, 0.35);
      top: 10%;
      right: 15%;
    }
    .orb-2 {
      width: 300px;
      height: 300px;
      background: rgba(6, 182, 212, 0.3);
      bottom: 8%;
      left: 10%;
      animation-delay: -7s;
    }
    @keyframes drift {
      0% { transform: translate(0, 0) scale(1); }
      100% { transform: translate(-40px, 35px) scale(1.15); }
    }

    .app-container {
      width: 100%;
      max-width: 450px;
      z-index: 10;
    }

    .login-card {
      background: var(--card-bg);
      backdrop-filter: blur(28px) saturate(180%);
      -webkit-backdrop-filter: blur(28px) saturate(180%);
      border: 1px solid rgba(255, 255, 255, 0.12);
      border-radius: 28px;
      padding: 42px 36px;
      box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.65),
                  0 0 40px rgba(59, 130, 246, 0.15),
                  inset 0 1px 0 rgba(255, 255, 255, 0.15);
      animation: floatIn 0.7s cubic-bezier(0.16, 1, 0.3, 1);
      transition: transform 0.4s ease, box-shadow 0.4s ease;
    }
    .login-card:hover {
      box-shadow: 0 30px 60px -12px rgba(0, 0, 0, 0.75),
                  0 0 50px rgba(59, 130, 246, 0.25),
                  inset 0 1px 0 rgba(255, 255, 255, 0.2);
    }
    @keyframes floatIn {
      from { opacity: 0; transform: translateY(28px) scale(0.95); }
      to { opacity: 1; transform: translateY(0) scale(1); }
    }

    .logo-container {
      display: flex;
      flex-direction: column;
      align-items: center;
      margin-bottom: 24px;
    }
    .logo-wrapper {
      width: 86px;
      height: 86px;
      border-radius: 24px;
      background: linear-gradient(135deg, rgba(59, 130, 246, 0.2), rgba(6, 182, 212, 0.2));
      border: 1px solid rgba(255, 255, 255, 0.15);
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 16px;
      box-shadow: 0 12px 30px rgba(37, 99, 235, 0.25);
      overflow: hidden;
      animation: gentleHover 4s ease-in-out infinite;
    }
    .logo-wrapper img {
      width: 68px;
      height: 68px;
      object-fit: contain;
      border-radius: 18px;
    }
    @keyframes gentleHover {
      0%, 100% { transform: translateY(0px); }
      50% { transform: translateY(-8px); }
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
      text-align: center;
      margin-bottom: 28px;
    }
    .title {
      font-family: 'Outfit', sans-serif;
      font-size: 2.1rem;
      font-weight: 700;
      letter-spacing: -0.5px;
      background: linear-gradient(135deg, #ffffff 0%, #93c5fd 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      margin-bottom: 6px;
    }
    .subtitle {
      font-size: 0.92rem;
      color: var(--text-muted);
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
    }
    .label {
      display: block;
      font-size: 0.84rem;
      font-weight: 500;
      color: #cbd5e1;
      margin-bottom: 8px;
      text-align: left;
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
      padding: 14px 15px 14px 45px;
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
    .form-input.invalid {
      border-color: var(--error-color);
    }

    /* Password toggle button */
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

    .feedback {
      font-size: 0.78rem;
      margin-top: 6px;
      display: flex;
      align-items: center;
      gap: 6px;
      min-height: 18px;
      color: #34d399;
      text-align: left;
    }

    .btn-primary {
      width: 100%;
      padding: 15px;
      margin-top: 12px;
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
    }
    .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 15px 30px -5px rgba(37, 99, 235, 0.65);
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
      .login-card { padding: 32px 24px; border-radius: 24px; }
      .title { font-size: 1.8rem; }
    }
  </style>
</head>
<body>

  <div class="orb orb-1"></div>
  <div class="orb orb-2"></div>

  <div class="app-container">
    <div class="login-card">

      <div class="logo-container">
        <div class="logo-wrapper">
          <img src="pic&animations/JARVIS-LOGOfull.jpg" alt="Jarvis AI Core" onerror="this.style.display='none'">
        </div>
        <div class="badge-status">
          <span class="pulse-dot"></span> AI Core Online
        </div>
      </div>

      <div class="header">
        <h1 class="title">Jarvis</h1>
        <p class="subtitle">Your AI mental wellness companion</p>
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

      <form id="loginForm" action="login_process.php" method="POST" novalidate>
        
        <!-- Identifier Field: Username or Gmail -->
        <div class="form-group">
          <label class="label" for="username">Username or Gmail Address</label>
          <div class="input-wrapper">
            <input type="text" id="username" name="username" class="form-input" placeholder="Enter Username or name@gmail.com" required autocomplete="username">
            <div class="input-icon">
              <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            </div>
          </div>
          <div id="loginFeedback" class="feedback"></div>
        </div>

        <!-- Password Field -->
        <div class="form-group">
          <label class="label" for="password">Password</label>
          <div class="input-wrapper">
            <input type="password" id="password" name="password" class="form-input" placeholder="Enter your password" required autocomplete="current-password">
            <div class="input-icon">
              <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
            </div>
            <button type="button" class="toggle-pwd" onclick="togglePassword('password', this)" aria-label="Toggle password visibility">
              <svg class="eye-icon" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11-8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            </button>
          </div>
        </div>

        <button type="submit" class="btn-primary">Sign In to AI Core</button>
      </form>

      <div class="footer">
        Don't have an account? <a href="signup.php">Create Account</a>
      </div>

    </div>
  </div>

  <script>
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

    const loginForm = document.getElementById('loginForm');
    const identifierInput = document.getElementById('username');
    const loginFeedback = document.getElementById('loginFeedback');
    const passwordInput = document.getElementById('password');

    identifierInput.addEventListener('input', () => {
      const val = identifierInput.value.trim().toLowerCase();
      if (val.includes('@')) {
        if (val.endsWith('@gmail.com')) {
          loginFeedback.innerHTML = '✓ Gmail account recognized';
          loginFeedback.style.color = '#34d399';
          identifierInput.classList.remove('invalid');
        } else {
          loginFeedback.innerHTML = 'ℹ️ Note: System accounts utilize @gmail.com or custom usernames';
          loginFeedback.style.color = '#f59e0b';
        }
      } else {
        loginFeedback.innerHTML = '';
        identifierInput.classList.remove('invalid');
      }
    });

    loginForm.addEventListener('submit', (e) => {
      let valid = true;
      if (identifierInput.value.trim() === '') {
        identifierInput.classList.add('invalid');
        identifierInput.focus();
        valid = false;
      }
      if (passwordInput.value.trim() === '') {
        passwordInput.classList.add('invalid');
        valid = false;
      }

      if (!valid) {
        e.preventDefault();
        const card = document.querySelector('.login-card');
        card.style.transform = 'translateX(8px)';
        setTimeout(() => { card.style.transform = 'translateX(-8px)'; }, 80);
        setTimeout(() => { card.style.transform = 'translateX(4px)'; }, 160);
        setTimeout(() => { card.style.transform = 'translateY(0) scale(1)'; }, 240);
      }
    });
  </script>
</body>
</html>