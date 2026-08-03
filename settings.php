<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System & AI Profile Settings – Jarvis</title>
    <style>
        /* Core architectural design variables and dynamic glassmorphism tokens */
        :root {
            --bg-gradient: radial-gradient(circle at center, #306f9f, #f0f7fc), linear-gradient(135deg, #063776, #eef3ff);
            --panel-bg: rgba(255, 255, 255, 0.25);
            --card-bg: rgba(255, 255, 255, 0.5);
            --text-main: #211f3d;
            --text-sub: #34495e;
            --border-color: rgba(255, 255, 255, 0.4);
            --sidebar-bg: rgba(255, 255, 255, 0.3);
            --sidebar-hover: rgba(255, 255, 255, 0.5);
            --tab-active-bg: linear-gradient(135deg, #4da3ff, #2c7be5);
            --card-border-hover: #2c7be5;
        }

        [data-theme="dark"] {
            --bg-gradient: radial-gradient(circle at center, #1e272e, #0f141c), linear-gradient(135deg, #0b0f19, #161f30);
            --panel-bg: rgba(15, 22, 36, 0.55);
            --card-bg: rgba(255, 255, 255, 0.06);
            --text-main: #f1f2f6;
            --text-sub: #ced6e0;
            --border-color: rgba(255, 255, 255, 0.12);
            --sidebar-bg: rgba(15, 22, 36, 0.6);
            --sidebar-hover: rgba(255, 255, 255, 0.08);
            --tab-active-bg: linear-gradient(135deg, #2c7be5, #1e3c72);
            --card-border-hover: #4da3ff;
        }

        * { box-sizing: border-box; font-family: "SF Pro Display", "Segoe UI", Inter, sans-serif; margin: 0; padding: 0; transition: background 0.3s cubic-bezier(0.4, 0, 0.2, 1), border-color 0.3s ease, color 0.2s ease; }
        
        body { 
            height: 100vh; 
            background: var(--bg-gradient); 
            color: var(--text-main); 
            display: flex; 
            overflow: hidden; 
        }
        
        .dashboard-layout { 
            display: flex; 
            width: 100vw; 
            height: 100vh; 
            overflow: hidden; 
        }
        
        .main-content-area { 
            flex: 1; 
            padding: 40px; 
            height: 100vh;
            overflow: hidden; 
            display: flex;
            flex-direction: column;
        }
        
        .premium-dashboard-panel { 
            background: var(--panel-bg); 
            backdrop-filter: blur(30px) saturate(170%); 
            border-radius: 32px; 
            padding: 36px 40px; 
            box-shadow: 0 40px 80px rgba(0, 0, 0, 0.08), inset 0 1px 0 rgba(255, 255, 255, 0.2); 
            height: calc(100vh - 80px); 
            display: flex; 
            flex-direction: column; 
            gap: 20px;
        }
        
        /* Expandable Theme-Aware Sidebar Frame */
        .jarvis-sidebar { 
            width: 80px; 
            height: 100vh;
            background: var(--sidebar-bg); 
            backdrop-filter: blur(25px); 
            border-right: 1px solid var(--border-color); 
            display: flex; 
            flex-direction: column; 
            justify-content: space-between; 
            padding: 24px 12px; 
            transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1); 
            overflow: hidden; 
            z-index: 100; 
            flex-shrink: 0;
        }
        .jarvis-sidebar:hover { width: 260px; }
        .sidebar-brand { display: flex; align-items: center; gap: 16px; padding-left: 10px; margin-bottom: 40px; }
        .brand-img { width: 40px; height: 40px; object-fit: contain; }
        .brand-text { font-size: 1.3rem; font-weight: 700; white-space: nowrap; opacity: 0; transition: opacity 0.2s ease; }
        .jarvis-sidebar:hover .brand-text { opacity: 1; }
        
        .sidebar-menu { list-style: none; display: flex; flex-direction: column; gap: 12px; flex: 1; }
        .sidebar-menu li a, .logout-btn-nav { display: flex; align-items: center; gap: 20px; padding: 14px; border-radius: 16px; text-decoration: none; color: var(--text-sub); font-size: 0.95rem; font-weight: 500; transition: all 0.2s ease; white-space: nowrap; }
        .sidebar-menu li a .icon, .logout-btn-nav .icon { font-size: 1.4rem; display: inline-block; text-align: center; width: 30px; flex-shrink: 0; }
        .menu-text { opacity: 0; transition: opacity 0.2s ease; }
        .jarvis-sidebar:hover .menu-text { opacity: 1; }
        .sidebar-menu li a:hover { background: var(--sidebar-hover); transform: translateX(4px); }
        .sidebar-menu li a.active { background: linear-gradient(135deg, #4da3ff, #2c7be5); color: white; box-shadow: 0 8px 20px rgba(44, 123, 229, 0.3); }
        .logout-btn-nav { color: #d63031; background: rgba(214, 48, 49, 0.08); display: flex; margin-top: auto; }
        .logout-btn-nav:hover { background: #d63031 !important; color: white !important; }

        .app-header { display: flex; justify-content: space-between; align-items: center; gap: 20px; flex-wrap: wrap; }
        .app-header h1 { font-size: 2.2rem; font-weight: 800; letter-spacing: -0.5px; }
        .app-header p { color: var(--text-sub); font-size: 1rem; margin-top: 4px; }

        /* Multi-Tab Workspace Navigation Bar */
        .tabs-navbar { display: flex; gap: 12px; border-bottom: 1px solid var(--border-color); padding-bottom: 14px; flex-shrink: 0; overflow-x: auto; scrollbar-width: none; }
        .tabs-navbar::-webkit-scrollbar { display: none; }
        .tab-btn { padding: 12px 24px; border-radius: 16px; border: none; background: rgba(255,255,255,0.2); color: var(--text-sub); font-size: 0.95rem; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: all 0.2s ease; white-space: nowrap; }
        .tab-btn:hover { background: rgba(255,255,255,0.4); transform: translateY(-2px); color: var(--text-main); }
        .tab-btn.active { background: var(--tab-active-bg); color: white; box-shadow: 0 8px 20px rgba(44, 123, 229, 0.3); }

        /* Scrollable Panels */
        .panel-content-scroll { flex: 1; overflow-y: auto; padding-right: 10px; }
        .panel-content-scroll::-webkit-scrollbar { width: 6px; }
        .panel-content-scroll::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.15); border-radius: 10px; }

        .tab-content { display: none; flex-direction: column; gap: 24px; animation: fadeIn 0.3s ease; }
        .tab-content.active { display: flex; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: translateY(0); } }

        .settings-card { background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 24px; padding: 28px; box-shadow: 0 10px 30px rgba(0,0,0,0.02); }
        .settings-card h2 { font-size: 1.2rem; color: #2c7be5; margin-bottom: 6px; font-weight: 700; display: flex; align-items: center; gap: 8px; }
        .settings-card .card-subtitle { font-size: 0.88rem; color: var(--text-sub); margin-bottom: 20px; }

        .setting-row { display: flex; align-items: center; justify-content: space-between; padding: 16px 0; border-bottom: 1px solid var(--border-color); flex-wrap: wrap; gap: 12px; }
        .setting-row:last-child { border-bottom: none; }
        .setting-info h3 { font-size: 1rem; font-weight: 600; margin-bottom: 4px; }
        .setting-info p { font-size: 0.85rem; color: var(--text-sub); max-width: 500px; line-height: 1.4; }

        /* UI Toggle Elements */
        .switch { position: relative; display: inline-block; width: 52px; height: 30px; flex-shrink: 0; }
        .switch input { opacity: 0; width: 0; height: 0; }
        .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(150,150,150,0.4); transition: .3s cubic-bezier(0.4, 0, 0.2, 1); border-radius: 34px; }
        .slider::before { position: absolute; content: ""; height: 22px; width: 22px; left: 4px; bottom: 4px; background-color: white; transition: .3s cubic-bezier(0.4, 0, 0.2, 1); border-radius: 50%; box-shadow: 0 2px 6px rgba(0,0,0,0.2); }
        input:checked + .slider { background: linear-gradient(135deg, #4da3ff, #2c7be5); }
        input:checked + .slider::before { transform: translateX(22px); }

        /* AI Profile Selection Cards */
        .ai-profiles-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-top: 10px; }
        .ai-profile-card { border: 2px solid var(--border-color); border-radius: 18px; padding: 18px; cursor: pointer; transition: all 0.2s ease; background: rgba(255,255,255,0.2); display: flex; flex-direction: column; gap: 8px; }
        .ai-profile-card:hover { transform: translateY(-3px); border-color: var(--card-border-hover); background: rgba(255,255,255,0.4); }
        .ai-profile-card.selected { border-color: #2c7be5; background: rgba(44, 123, 229, 0.12); box-shadow: 0 10px 20px rgba(44, 123, 229, 0.15); }
        .ai-profile-card h4 { font-size: 1rem; color: var(--text-main); display: flex; align-items: center; justify-content: space-between; }
        .ai-profile-card p { font-size: 0.8rem; color: var(--text-sub); line-height: 1.4; }

        .form-group { display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px; }
        .form-group label { font-size: 0.85rem; font-weight: 600; color: var(--text-main); }
        .form-control { padding: 12px 16px; border-radius: 14px; border: 1px solid var(--border-color); background: var(--card-bg); color: var(--text-main); font-size: 0.95rem; outline: none; transition: border 0.2s; }
        .form-control:focus { border-color: #2c7be5; }

        .action-button { padding: 13px 26px; border-radius: 16px; border: none; font-size: 0.95rem; font-weight: 700; cursor: pointer; transition: all 0.2s ease; display: inline-flex; align-items: center; justify-content: center; gap: 8px; }
        .btn-primary { background: linear-gradient(135deg, #4da3ff, #2c7be5); color: white; box-shadow: 0 8px 20px rgba(44, 123, 229, 0.3); }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 12px 25px rgba(44, 123, 229, 0.4); }
        .btn-danger { background: #d63031; color: white; box-shadow: 0 6px 16px rgba(214, 48, 49, 0.25); }
        .btn-danger:hover { background: #b32424; transform: translateY(-2px); }
        .btn-outline-danger { background: rgba(214,48,49,0.1); border: 1px solid rgba(214,48,49,0.3); color: #d63031; font-weight: 600; }
        .btn-outline-danger:hover { background: #d63031; color: white; }

        /* Notification Toast */
        .status-toast { position: fixed; bottom: 30px; right: 30px; background: #211f3d; color: white; padding: 16px 24px; border-radius: 18px; font-size: 0.95rem; font-weight: 600; display: flex; align-items: center; gap: 12px; box-shadow: 0 20px 45px rgba(0,0,0,0.3); transform: translateY(100px); opacity: 0; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); z-index: 2000; border: 1px solid rgba(255,255,255,0.2); }
        .status-toast.show { transform: translateY(0); opacity: 1; }
        .status-toast.error { background: #d63031; }
        .status-toast.success { background: #2ecc71; }
    </style>
</head>
<body>

  <div class="dashboard-layout">
    <?php include 'sidebar.php'; ?>

    <main class="main-content-area">
      <div class="premium-dashboard-panel">
        
        <header class="app-header">
          <div>
            <h1>Workspace & AI Control Center</h1>
            <p>Tailor Gemini conversational behavior, interface theme layers, security, and personal database vault exports</p>
          </div>
          <a href="logout.php" class="action-button" style="background: rgba(214, 48, 49, 0.1); color: #d63031; border: 1px solid rgba(214, 48, 49, 0.2); text-decoration: none; font-size: 0.85rem; padding: 10px 18px;">🚪 Secure Logout</a>
        </header>

        <nav class="tabs-navbar">
          <button class="tab-btn active" onclick="switchTab('tab-ai-theme', this)"><span>⚙️</span> AI Profile & Appearance</button>
          <button class="tab-btn" onclick="switchTab('tab-security', this)"><span>🔒</span> Account Security & Profile</button>
          <button class="tab-btn" onclick="switchTab('tab-data-vault', this)"><span>📦</span> Data Vault & Export</button>
        </nav>

        <div class="panel-content-scroll">
          
          <!-- TAB 1: AI PROFILE & APPEARANCE -->
          <div id="tab-ai-theme" class="tab-content active">
            
            <div class="settings-card">
              <h2><span>🤖</span> AI Counseling Tone Profile</h2>
              <p class="card-subtitle">Choose the default system personality style injected into Google Gemini during your support chats.</p>
              
              <div class="ai-profiles-grid" id="ai-mode-selector">
                <div class="ai-profile-card selected" onclick="selectAiMode('empathic', this)">
                  <h4>🌿 Empathetic & Warm <span class="chk-indicator">✔</span></h4>
                  <p>Supportive and compassionate voice utilizing gentle encouragement and comforting feedback.</p>
                </div>
                <div class="ai-profile-card" onclick="selectAiMode('clinical', this)">
                  <h4>🔬 Clinical & Objective <span class="chk-indicator" style="display:none;">✔</span></h4>
                  <p>Structured, calm, evidence-based responses focusing on proven therapeutic techniques and clarity.</p>
                </div>
                <div class="ai-profile-card" onclick="selectAiMode('concise', this)">
                  <h4>⚡ Action-Oriented <span class="chk-indicator" style="display:none;">✔</span></h4>
                  <p>Pragmatic, concise advice and bite-sized bullet points designed for immediate grounding actions.</p>
                </div>
              </div>
            </div>

            <div class="settings-card">
              <h2><span>🎨</span> Interface Viewport Architecture</h2>
              <p class="card-subtitle">Control dynamic dark mode rendering and background visual layers.</p>
              
              <div class="setting-row">
                <div class="setting-info">
                  <h3>Dark Mode Structural Integration</h3>
                  <p>Switch ambient UI styling between high-contrast dark matrices and light glassmorphism.</p>
                </div>
                <label class="switch">
                  <input type="checkbox" id="themeToggleInput" onchange="toggleSystemTheme(this)">
                  <span class="slider"></span>
                </label>
              </div>

              <div class="setting-row">
                <div class="setting-info">
                  <h3>System Event & Email Notifications</h3>
                  <p>Receive transaction confirmations and routine wellness checkup notifications via Gmail.</p>
                </div>
                <label class="switch">
                  <input type="checkbox" id="notifToggleInput" checked>
                  <span class="slider"></span>
                </label>
              </div>

              <div style="margin-top: 24px; text-align: right;">
                <button class="action-button btn-primary" onclick="saveSystemPreferences()">💾 Save Preferences to Database</button>
              </div>
            </div>

          </div>

          <!-- TAB 2: ACCOUNT SECURITY & PROFILE -->
          <div id="tab-security" class="tab-content">
            
            <div class="settings-card">
              <h2><span>👤</span> Account Identification Profile</h2>
              <p class="card-subtitle">Update your customized handle or verified @gmail.com login address.</p>
              
              <form id="profileUpdateForm" onsubmit="handleProfileUpdate(event)">
                <div class="form-group" style="max-width: 450px;">
                  <label for="profileUsername">Display Username</label>
                  <input type="text" id="profileUsername" class="form-control" placeholder="Your username" required>
                </div>
                <div class="form-group" style="max-width: 450px;">
                  <label for="profileEmail">Verified Gmail Account</label>
                  <input type="email" id="profileEmail" class="form-control" placeholder="user.name@gmail.com">
                </div>
                <button type="submit" class="action-button btn-primary" style="margin-top:8px;">🔄 Update Profile Credentials</button>
              </form>
            </div>

            <div class="settings-card">
              <h2><span>🔑</span> Password Encryption Matrix</h2>
              <p class="card-subtitle">Recrypt your security access password using Bcrypt standard hashing.</p>
              
              <form id="passwordChangeForm" onsubmit="handlePasswordChange(event)" style="max-width: 450px;">
                <div class="form-group">
                  <label for="currentPass">Current Password</label>
                  <input type="password" id="currentPass" class="form-control" placeholder="••••••••" required>
                </div>
                <div class="form-group">
                  <label for="newPass">New Security Password</label>
                  <input type="password" id="newPass" class="form-control" placeholder="At least 6 characters" minlength="6" required>
                </div>
                <div class="form-group">
                  <label for="confirmNewPass">Confirm New Password</label>
                  <input type="password" id="confirmNewPass" class="form-control" placeholder="Re-type new password" required>
                </div>
                <button type="submit" class="action-button btn-primary" style="margin-top:8px;">🔐 Save New Password Hash</button>
              </form>
            </div>

          </div>

          <!-- TAB 3: DATA VAULT & EXPORT -->
          <div id="tab-data-vault" class="tab-content">
            
            <div class="settings-card" style="background: linear-gradient(135deg, rgba(44, 123, 229, 0.1), rgba(77, 163, 255, 0.15)); border: 1px solid rgba(44, 123, 229, 0.3);">
              <h2><span>📥</span> Download Personal Wellness Archive</h2>
              <p class="card-subtitle">You have absolute ownership over your data. Generate a comprehensive JSON archive containing all your chat conversations, Thought Vault notes, mood charts, and emergency contacts.</p>
              
              <a href="settings_process.php?action=export_data" class="action-button btn-primary" style="text-decoration: none;">
                📦 Export Complete Wellness Dump (JSON)
              </a>
            </div>

            <div class="settings-card" style="border-color: rgba(214, 48, 49, 0.3);">
              <h2 style="color: #d63031;"><span>🗑️</span> Database Cleanup & Maintenance</h2>
              <p class="card-subtitle">Permanently erase recorded history from the MySQL server. These actions cannot be undone.</p>
              
              <div class="setting-row">
                <div class="setting-info">
                  <h3>Clear Conversational Chat Logs</h3>
                  <p>Permanently removes all saved Gemini chatbot threads and messages from the database.</p>
                </div>
                <button class="action-button btn-outline-danger" onclick="clearChatHistory()">Erase Chat Threads</button>
              </div>

              <div class="setting-row">
                <div class="setting-info">
                  <h3>Reset Thought Vault & Activity Analytics</h3>
                  <p>Deletes all saved journal entries, mood charts, and somatic practice milestones.</p>
                </div>
                <button class="action-button btn-outline-danger" onclick="clearWellnessLogs()">Reset Analytics Vault</button>
              </div>
            </div>

          </div>

        </div>

      </div>
    </main>
  </div>

  <div id="statusToast" class="status-toast"><span id="toastIcon">ℹ️</span> <span id="toastText">Status notification</span></div>

  <script>
    let currentAiMode = 'empathic';

    window.addEventListener('DOMContentLoaded', async () => {
        const activeTheme = localStorage.getItem("workspace-theme") || "light";
        if (activeTheme === "dark") {
            document.documentElement.setAttribute("data-theme", "dark");
            document.getElementById("themeToggleInput").checked = true;
        }
        await loadUserSettings();
    });

    function switchTab(tabId, btnElement) {
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
        
        btnElement.classList.add('active');
        document.getElementById(tabId).classList.add('active');
    }

    function toggleSystemTheme(checkbox) {
        if (checkbox.checked) {
            document.documentElement.setAttribute("data-theme", "dark");
            localStorage.setItem("workspace-theme", "dark");
        } else {
            document.documentElement.removeAttribute("data-theme");
            localStorage.setItem("workspace-theme", "light");
        }
        saveSystemPreferences();
    }

    function selectAiMode(mode, cardElement) {
        currentAiMode = mode;
        document.querySelectorAll('.ai-profile-card').forEach(card => {
            card.classList.remove('selected');
            const indicator = card.querySelector('.chk-indicator');
            if (indicator) indicator.style.display = "none";
        });
        cardElement.classList.add('selected');
        const activeInd = cardElement.querySelector('.chk-indicator');
        if (activeInd) activeInd.style.display = "inline";
    }

    async function loadUserSettings() {
        try {
            const res = await fetch('settings_process.php?action=get_settings');
            const data = await res.json();
            if (data.status === 'success') {
                if (data.user) {
                    document.getElementById('profileUsername').value = data.user.username || '';
                    document.getElementById('profileEmail').value = data.user.email || '';
                }
                if (data.settings) {
                    currentAiMode = data.settings.ai_voice_mode || 'empathic';
                    document.querySelectorAll('.ai-profile-card').forEach(card => {
                        card.classList.remove('selected');
                        const indicator = card.querySelector('.chk-indicator');
                        if (indicator) indicator.style.display = "none";
                    });
                    const targetCard = Array.from(document.querySelectorAll('.ai-profile-card')).find(c => c.getAttribute('onclick').includes(currentAiMode));
                    if (targetCard) {
                        targetCard.classList.add('selected');
                        targetCard.querySelector('.chk-indicator').style.display = "inline";
                    }
                    if (data.settings.email_notifications !== undefined) {
                        document.getElementById('notifToggleInput').checked = (data.settings.email_notifications == 1);
                    }
                }
            }
        } catch(e) {
            console.warn('Error loading settings from server:', e);
        }
    }

    async function saveSystemPreferences() {
        const themeVal = document.getElementById("themeToggleInput").checked ? "dark" : "light";
        const notifVal = document.getElementById("notifToggleInput").checked;

        try {
            const res = await fetch('settings_process.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'save_preferences', theme: themeVal, ai_voice_mode: currentAiMode, email_notifications: notifVal })
            });
            const result = await res.json();
            if (result.status === 'success') {
                showToast('success', '✔ Preferences saved directly to database!');
            } else {
                showToast('error', '❌ Could not save preferences: ' + result.message);
            }
        } catch (error) {
            showToast('error', '❌ Network communication error while saving preferences');
        }
    }

    async function handleProfileUpdate(event) {
        event.preventDefault();
        const username = document.getElementById('profileUsername').value.trim();
        const email = document.getElementById('profileEmail').value.trim();

        if (!email.toLowerCase().endsWith('@gmail.com') && email !== "") {
            showToast('error', '❌ Please provide a valid @gmail.com address');
            return;
        }

        try {
            const res = await fetch('settings_process.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'update_profile', username: username, email: email })
            });
            const result = await res.json();
            if (result.status === 'success') {
                showToast('success', '✔ Profile updated successfully!');
            } else {
                showToast('error', '❌ ' + result.message);
            }
        } catch(e) {
            showToast('error', '❌ Error updating account credentials.');
        }
    }

    async function handlePasswordChange(event) {
        event.preventDefault();
        const curr = document.getElementById('currentPass').value;
        const newP = document.getElementById('newPass').value;
        const confP = document.getElementById('confirmNewPass').value;

        if (newP !== confP) {
            showToast('error', '❌ New passwords do not match!');
            return;
        }

        try {
            const res = await fetch('settings_process.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'change_password', current_password: curr, new_password: newP })
            });
            const result = await res.json();
            if (result.status === 'success') {
                showToast('success', '✔ Password recrypted & updated securely!');
                document.getElementById('passwordChangeForm').reset();
            } else {
                showToast('error', '❌ ' + result.message);
            }
        } catch(e) {
            showToast('error', '❌ Network communication failure.');
        }
    }

    async function clearChatHistory() {
        if (!confirm("Are you certain you wish to permanently delete all conversational AI threads from the MySQL server?")) return;
        try {
            const res = await fetch('settings_process.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'clear_chat' })
            });
            const result = await res.json();
            showToast(result.status === 'success' ? 'success' : 'error', result.status === 'success' ? '✔ Chat history erased' : '❌ Error clearing history');
        } catch(e) { showToast('error', '❌ Error contacting database.'); }
    }

    async function clearWellnessLogs() {
        if (!confirm("Are you sure you want to clear your Thought Vault reflections and practice statistics from the database?")) return;
        try {
            const res = await fetch('settings_process.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'clear_wellness' })
            });
            const result = await res.json();
            showToast(result.status === 'success' ? 'success' : 'error', result.status === 'success' ? '✔ Wellness analytics reset' : '❌ Error clearing logs');
        } catch(e) { showToast('error', '❌ Error contacting database.'); }
    }

    let toastTimeout;
    function showToast(type, text) {
        const toast = document.getElementById('statusToast');
        document.getElementById('toastText').textContent = text;
        toast.className = 'status-toast show ' + type;
        clearTimeout(toastTimeout);
        toastTimeout = setTimeout(() => { toast.classList.remove('show'); }, 4000);
    }
  </script>
</body>
</html>