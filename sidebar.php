<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<link rel="stylesheet" href="css/responsive.css">
<style>
    :root, [data-theme="light"] {
      --sys-bg-gradient: radial-gradient(circle at center, #306f9f, #f0f7fc), linear-gradient(135deg, #063776, #eef3ff);
      --sys-panel-bg: rgba(255, 255, 255, 0.25);
      --sys-card-bg: rgba(255, 255, 255, 0.45);
      --sys-sidebar-bg: rgba(255, 255, 255, 0.3);
      --sys-text-primary: #211f3d;
      --sys-text-secondary: #4a5568;
      --sys-border-alpha: rgba(255, 255, 255, 0.4);
      --sys-input-bg: rgba(255, 255, 255, 0.65);
    }

    [data-theme="dark"] {
      --sys-bg-gradient: radial-gradient(circle at center, #111a2e, #070d19), linear-gradient(135deg, #030914, #0d1527);
      --sys-panel-bg: rgba(13, 22, 38, 0.45);
      --sys-card-bg: rgba(22, 33, 54, 0.5);
      --sys-sidebar-bg: rgba(13, 22, 38, 0.5);
      --sys-text-primary: #f0f4f8;
      --sys-text-secondary: #a0aec0;
      --sys-border-alpha: rgba(255, 255, 255, 0.08);
      --sys-input-bg: rgba(15, 23, 42, 0.6);
    }

    body, .premium-dashboard-panel, .dashboard-card, .jarvis-sidebar, #chatBox, .form-field-input, input, select, textarea {
      transition: background 0.4s cubic-bezier(0.25, 1, 0.5, 1), 
                  background-color 0.4s cubic-bezier(0.25, 1, 0.5, 1), 
                  border-color 0.4s cubic-bezier(0.25, 1, 0.5, 1), 
                  color 0.4s cubic-bezier(0.25, 1, 0.5, 1),
                  box-shadow 0.4s cubic-bezier(0.25, 1, 0.5, 1) !important;
    }

    body { 
      background: var(--sys-bg-gradient) !important; 
      color: var(--sys-text-primary) !important; 
    }
    .premium-dashboard-panel { 
      background: var(--sys-panel-bg) !important; 
      border-color: var(--sys-border-alpha) !important;
    }
    .dashboard-card { 
      background: var(--sys-card-bg) !important; 
      border-color: var(--sys-border-alpha) !important;
    }
    .jarvis-sidebar { 
      background: var(--sys-sidebar-bg) !important; 
      border-right: 1px solid var(--sys-border-alpha) !important;
    }

    /* Hide theme switch when sidebar is shrunk */
    .theme-switch-wrapper {
      display: none !important;
      opacity: 0;
      max-height: 0px;
      padding: 0;
      margin: 0;
      overflow: hidden;
      transition: all 0.2s ease;
      flex-shrink: 0;
    }

    .jarvis-sidebar.expanded .theme-switch-wrapper {
      display: flex !important;
      align-items: center;
      justify-content: flex-start;
      padding: 0 14px;
      margin-bottom: 22px;
      gap: 16px;
      height: 36px;
      max-height: 60px;
      opacity: 1;
    }

    .premium-theme-pill {
      position: relative;
      width: 52px;
      height: 28px;
      background: rgba(0, 0, 0, 0.1);
      border: 1px solid rgba(255, 255, 255, 0.2);
      border-radius: 100px;
      cursor: pointer;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      flex-shrink: 0;
    }
    [data-theme="dark"] .premium-theme-pill {
      background: #2c7be5;
      border-color: rgba(44, 123, 229, 0.4);
    }

    .theme-knob-core {
      position: absolute;
      top: 2px;
      left: 2px;
      width: 22px;
      height: 22px;
      background: #ffffff;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 0.75rem;
      box-shadow: 0 2px 6px rgba(0,0,0,0.15);
      transition: all 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
    }
    [data-theme="dark"] .theme-knob-core {
      transform: translateX(24px);
      background: #111a2e;
    }

    .theme-label-text {
      font-size: 0.88rem;
      font-weight: 600;
      color: var(--sys-text-secondary);
      white-space: nowrap;
      opacity: 0;
      transition: opacity 0.2s ease;
    }
    .jarvis-sidebar.expanded .theme-label-text {
      opacity: 1;
    }

    /* STRICT SELF-CONTAINED SIDEBAR STYLING */
    .jarvis-sidebar {
      width: 80px;
      height: 100vh;
      position: sticky;
      top: 0;
      background: var(--sys-sidebar-bg, rgba(255, 255, 255, 0.3)) !important;
      backdrop-filter: blur(25px);
      -webkit-backdrop-filter: blur(25px);
      border-right: 1px solid var(--sys-border-alpha, rgba(255, 255, 255, 0.4)) !important;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      padding: 24px 12px;
      transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1), background 0.3s ease, border-color 0.3s ease !important;
      overflow-x: hidden;
      overflow-y: auto;
      z-index: 1000;
      box-shadow: 10px 0 35px rgba(0, 0, 0, 0.05);
      flex-shrink: 0;
    }
    
    /* Expands exclusively when clicked / toggled */
    .jarvis-sidebar.expanded {
      width: 260px;
    }
    .jarvis-sidebar::-webkit-scrollbar { width: 0px; display: none; }

    .sidebar-brand { 
      display: flex; 
      align-items: center; 
      gap: 14px; 
      padding: 4px 6px; 
      margin-bottom: 22px; 
      overflow: hidden;
      flex-shrink: 0;
      cursor: pointer;
      border-radius: 14px;
      transition: background 0.2s ease;
    }
    .sidebar-brand:hover {
      background: rgba(255, 255, 255, 0.2);
    }
    .brand-img { 
      width: 42px !important; 
      height: 42px !important; 
      border-radius: 50%;
      object-fit: cover; 
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      flex-shrink: 0;
    }
    .brand-text, .menu-text, .theme-label-text { 
      font-size: 0.95rem;
      font-weight: 700; 
      white-space: nowrap; 
      opacity: 0; 
      max-width: 0px;
      display: none !important;
      transition: opacity 0.2s ease, max-width 0.2s ease; 
      overflow: hidden;
      color: var(--sys-text-primary, #211f3d) !important;
    }
    .brand-text { font-size: 1.25rem; font-weight: 800; }
    
    .jarvis-sidebar.expanded .brand-text,
    .jarvis-sidebar.expanded .menu-text,
    .jarvis-sidebar.expanded .theme-label-text { 
      opacity: 1; 
      max-width: 220px;
      display: inline-block !important; 
    }
    
    .sidebar-menu { 
      list-style: none !important; 
      display: flex; 
      flex-direction: column; 
      gap: 10px; 
      flex: 1; 
      margin: 0; 
      padding: 0; 
    }
    
    .sidebar-menu li { display: block; margin: 0; padding: 0; width: 100%; }
    .sidebar-menu li a, .logout-btn-nav {
      display: flex; 
      align-items: center; 
      gap: 18px; 
      padding: 13px 12px; 
      border-radius: 16px;
      text-decoration: none; 
      color: var(--sys-text-primary, #34495e) !important; 
      font-size: 0.98rem; 
      font-weight: 600; 
      transition: all 0.2s ease; 
      white-space: nowrap;
      overflow: hidden;
      width: 100%;
    }
    .sidebar-menu li a .icon, .logout-btn-nav .icon { 
      font-size: 1.35rem; 
      display: inline-flex; 
      align-items: center;
      justify-content: center;
      width: 32px; 
      flex-shrink: 0;
    }
    .sidebar-menu li a:hover { background: rgba(255, 255, 255, 0.45) !important; transform: translateX(4px); }
    .sidebar-menu li a.active { 
      background: linear-gradient(135deg, #4da3ff, #2c7be5) !important; 
      color: #ffffff !important; 
      box-shadow: 0 8px 20px rgba(44, 123, 229, 0.35); 
    }
    .sidebar-menu li a.active .menu-text, .sidebar-menu li a.active .icon { color: #ffffff !important; }

    .sidebar-footer { margin-top: 20px; flex-shrink: 0; width: 100%; }
    .logout-btn-nav {
      color: #d63031 !important;
      background: rgba(214, 48, 49, 0.1);
      width: 100%;
    }
    .logout-btn-nav:hover { background: #d63031 !important; color: white !important; }
    .logout-btn-nav:hover .menu-text, .logout-btn-nav:hover .icon { color: white !important; }

    [data-theme="dark"] .sidebar-menu li a:hover {
      background: rgba(255, 255, 255, 0.08) !important;
      color: #f1f2f6 !important;
    }

    /* Embedded Desktop Overrides */
    .mobile-top-bar {
      display: none !important;
    }
    .sidebar-backdrop {
      display: none !important;
    }
</style>

<script>
    const systemThemeEngine = {
      storageKey: "jarvis_sys_theme",

      init() {
        const cachedTheme = localStorage.getItem(this.storageKey) || "light";
        document.documentElement.setAttribute("data-theme", cachedTheme);
        window.addEventListener("DOMContentLoaded", () => {
          this.syncUIElements(cachedTheme);
        });
      },

      toggleTheme() {
        const currentTheme = document.documentElement.getAttribute("data-theme") || "light";
        const targetTheme = currentTheme === "light" ? "dark" : "light";
        
        document.documentElement.setAttribute("data-theme", targetTheme);
        localStorage.setItem(this.storageKey, targetTheme);
        
        this.syncUIElements(targetTheme);
      },

      syncUIElements(theme) {
        const knob = document.getElementById("themeKnob");
        const label = document.getElementById("themeLabel");
        if (!knob || !label) return;
        if (theme === "dark") {
          knob.textContent = "🌙";
          label.textContent = "Dark Mode";
        } else {
          knob.textContent = "☀️";
          label.textContent = "Light Mode";
        }
      }
    };

    function toggleDesktopSidebar() {
      const sidebar = document.querySelector('.jarvis-sidebar');
      if (!sidebar) return;
      sidebar.classList.toggle('expanded');
      const isExp = sidebar.classList.contains('expanded');
      localStorage.setItem('jarvis_sidebar_state', isExp ? 'open' : 'closed');
      setTimeout(() => window.dispatchEvent(new Event('resize')), 320);
    }

    function toggleMobileSidebar() {
      const sidebar = document.querySelector('.jarvis-sidebar');
      const backdrop = document.getElementById('sidebarBackdrop');
      const menuBtn = document.querySelector('.mobile-menu-btn');
      
      if (!sidebar) return;
      sidebar.classList.toggle('mobile-open');
      
      const isOpen = sidebar.classList.contains('mobile-open');
      if (backdrop) {
        if (isOpen) {
          backdrop.classList.add('active');
        } else {
          backdrop.classList.remove('active');
        }
      }
      
      if (menuBtn) {
        if (isOpen) {
          menuBtn.classList.add('active');
        } else {
          menuBtn.classList.remove('active');
        }
      }
    }

    systemThemeEngine.init();
    window.addEventListener("DOMContentLoaded", () => {
      if (localStorage.getItem('jarvis_sidebar_state') === 'open') {
        document.querySelector('.jarvis-sidebar')?.classList.add('expanded');
      }
    });
</script>

<div class="mobile-top-bar">
    <button class="mobile-menu-btn" onclick="toggleMobileSidebar()" title="Toggle Menu">
        <span class="menu-icon-bar"></span>
        <span class="menu-icon-bar"></span>
        <span class="menu-icon-bar"></span>
    </button>
    <div class="mobile-brand-group">
        <img src="pic&animations/JARVIS-LOGOfull.jpg" alt="Jarvis Logo" class="mobile-brand-img">
        <span class="mobile-brand-name">Jarvis AI</span>
    </div>
    <div class="mobile-actions-group">
        <button class="mobile-theme-toggle" onclick="systemThemeEngine.toggleTheme()" title="Switch Workspace Theme">🌓</button>
    </div>
</div>

<div class="sidebar-backdrop" id="sidebarBackdrop" onclick="toggleMobileSidebar()"></div>

<nav class="jarvis-sidebar">
    <div>
        <div class="sidebar-brand" onclick="toggleDesktopSidebar()" title="Click to expand/collapse sidebar">
            <img src="pic&animations/JARVIS-LOGOfull.jpg" alt="Jarvis Logo" class="brand-img">
            <span class="brand-text">Jarvis AI</span>
            <span style="font-size:1.1rem; opacity:0.7; margin-left:auto;">☰</span>
        </div>

        <div class="theme-switch-wrapper">
            <div class="premium-theme-pill" id="themeTogglePill" onclick="systemThemeEngine.toggleTheme()" title="Switch Workspace Theme">
                <div class="theme-knob-core" id="themeKnob">☀️</div>
            </div>
            <span class="theme-label-text" id="themeLabel">Light Mode</span>
        </div>

        <ul class="sidebar-menu">
            <li>
                <a href="jarvis.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'jarvis.php' ? 'active' : ''; ?>">
                    <span class="icon">🏠</span>
                    <span class="menu-text">Dashboard</span>
                </a>
            </li>
            <li>
                <a href="chatbot.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'chatbot.php' ? 'active' : ''; ?>">
                    <span class="icon">💬</span>
                    <span class="menu-text">AI Chatbot</span>
                </a>
            </li>
            <li>
                <a href="wellness.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'wellness.php' ? 'active' : ''; ?>">
                    <span class="icon">🌿</span>
                    <span class="menu-text">Wellness Hub</span>
                </a>
            </li>
            <li>
                <a href="resources.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'resources.php' ? 'active' : ''; ?>">
                    <span class="icon">📚</span>
                    <span class="menu-text">Resources</span>
                </a>
            </li>
            <li>
                <a href="emergency.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'emergency.php' ? 'active' : ''; ?>">
                    <span class="icon">🚨</span>
                    <span class="menu-text">SOS Support</span>
                </a>
            </li>
            <li>
                <a href="profile.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : ''; ?>">
                    <span class="icon">👤</span>
                    <span class="menu-text">My Profile</span>
                </a>
            </li>
            <li>
                <a href="settings.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>">
                    <span class="icon">⚙️</span>
                    <span class="menu-text">Settings</span>
                </a>
            </li>
            <li>
                <a href="about.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'about.php' ? 'active' : ''; ?>">
                    <span class="icon">ℹ️</span>
                    <span class="menu-text">About Us</span>
                </a>
            </li>
        </ul>
    </div>

    <div class="sidebar-footer">
        <a href="logout.php" class="logout-btn-nav">
            <span class="icon">🚪</span>
            <span class="menu-text">Log Out</span>
        </a>
    </div>
</nav>
