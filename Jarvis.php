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
  <title>Jarvis – Mental Wellness Sanctuary</title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
  <style>
    * {
      box-sizing: border-box;
      font-family: "SF Pro Display", "Segoe UI", Inter, -apple-system, sans-serif;
      margin: 0;
      padding: 0;
    }

    body {
      min-height: 100vh;
      display: flex;
      overflow-x: hidden;
      background: var(--sys-bg-gradient, radial-gradient(circle at center, #306f9f, #f0f7fc), linear-gradient(135deg, #063776, #eef3ff));
      color: var(--sys-text-primary, #1e293b);
    }

    .dashboard-layout {
      display: flex;
      width: 100%; 
      min-height: 100vh;
      position: relative;
    }

    .main-content-area {
      flex: 1;
      padding: 32px;
      overflow-y: auto;
      overflow-x: hidden;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }
    
    .main-content-area::-webkit-scrollbar { width: 6px; }
    .main-content-area::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.18); border-radius: 10px; }
    .main-content-area::-webkit-scrollbar-track { background: transparent; }

    .premium-dashboard-panel {
      background: var(--sys-panel-bg, rgba(255, 255, 255, 0.25));
      backdrop-filter: blur(30px) saturate(170%);
      -webkit-backdrop-filter: blur(30px) saturate(170%);
      border-radius: 28px;
      padding: 32px 40px;
      box-shadow: 0 30px 70px rgba(0, 0, 0, 0.06), inset 0 1px 0 rgba(255, 255, 255, 0.45);
      border: 1px solid var(--sys-border-alpha, rgba(255, 255, 255, 0.45));
      display: flex;
      flex-direction: column;
      gap: 32px;
      flex: 1;
      width: 100%;
      max-width: 1500px; 
      margin: 0 auto;
    }

    /* Supportive Workspace Header */
    .app-header { 
      display: flex; 
      justify-content: space-between; 
      align-items: center; 
      flex-wrap: wrap; 
      gap: 16px; 
      border-bottom: 1px solid var(--sys-border-alpha, rgba(255, 255, 255, 0.4)); 
      padding-bottom: 20px; 
    }
    
    .header-title-group {
      display: flex;
      align-items: center;
      gap: 16px;
    }

    .mobile-menu-btn {
      display: none;
      background: rgba(44, 123, 229, 0.12);
      border: 1px solid rgba(44, 123, 229, 0.3);
      color: #2c7be5;
      border-radius: 12px;
      width: 44px;
      height: 44px;
      cursor: pointer;
      align-items: center;
      justify-content: center;
      transition: all 0.2s;
    }
    
    .mobile-menu-btn:hover {
      background: rgba(44, 123, 229, 0.25);
    }

    .app-header h1 { font-size: 1.85rem; font-weight: 800; letter-spacing: -0.5px; color: var(--sys-text-primary, #0f172a); display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
    .app-header p { font-size: 0.95rem; color: var(--sys-text-secondary, #475569); margin-top: 4px; font-weight: 600; }
    
    .live-indicator {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 8px 16px;
      border-radius: 24px;
      font-weight: 800;
      font-size: 0.82rem;
      letter-spacing: 0.5px;
      transition: all 0.3s ease;
    }
    .live-indicator.online { background: rgba(22, 163, 74, 0.14); color: #15803d; border: 1px solid rgba(22, 163, 74, 0.35); box-shadow: 0 4px 12px rgba(22, 163, 74, 0.08); }
    .live-indicator.offline { background: rgba(220, 38, 38, 0.14); color: #dc2626; border: 1px solid rgba(220, 38, 38, 0.35); box-shadow: 0 4px 12px rgba(220, 38, 38, 0.08); }
    
    .pulse-dot { width: 9px; height: 9px; border-radius: 50%; animation: pulse 1.5s infinite; transition: all 0.3s ease; }
    .live-indicator.online .pulse-dot { background: #16a34a; box-shadow: 0 0 8px #16a34a; }
    .live-indicator.offline .pulse-dot { background: #dc2626; box-shadow: 0 0 8px #dc2626; }
    @keyframes pulse { 0% { transform: scale(0.9); opacity: 0.9; } 50% { transform: scale(1.4); opacity: 0.4; } 100% { transform: scale(0.9); opacity: 0.9; } }

    /* Daily Thought Stream Banner */
    .inspiration-banner {
      background: var(--sys-card-bg, rgba(255, 255, 255, 0.55));
      border: 1px solid var(--sys-border-alpha, rgba(255, 255, 255, 0.6));
      border-radius: 22px;
      padding: 20px 28px;
      display: flex;
      align-items: center;
      gap: 18px;
      box-shadow: 0 8px 25px rgba(0,0,0,0.03);
    }
    .quote-content { display: flex; align-items: center; gap: 14px; min-width: 0; flex: 1; }
    .quote-icon { font-size: 1.6rem; flex-shrink: 0; filter: drop-shadow(0 4px 8px rgba(44, 123, 229, 0.25)); }
    .quote-text-area { flex: 1; min-width: 0; }
    .quote-text { font-size: 1.05rem; font-style: italic; font-weight: 600; color: var(--sys-text-primary, #1e293b); line-height: 1.4; }
    .quote-author { font-size: 0.85rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.8px; color: #2c7be5; white-space: nowrap; flex-shrink: 0; display: block; margin-top: 6px; }
    .blinking-cursor { font-weight: 800; color: #2c7be5; margin-left: 2px; display: inline-block; animation: blink 1s step-end infinite; }
    @keyframes blink { 0%, 100% { opacity: 1; } 50% { opacity: 0; } }

    /* Universal Dashboard Glass Component Card */
    .dashboard-card {
      border-radius: 24px;
      padding: 28px;
      border: 1px solid var(--sys-border-alpha, rgba(255, 255, 255, 0.55));
      background: var(--sys-card-bg, rgba(255, 255, 255, 0.55)) !important;
      box-shadow: 0 10px 30px rgba(0,0,0,0.03);
      transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
      min-width: 0; /* CRITICAL FIX: Prevents grids from blowing out */
    }
    .dashboard-card:hover { transform: translateY(-3px); box-shadow: 0 18px 40px rgba(6, 55, 118, 0.09); border-color: #2c7be5 !important; }

    /* Tier 1: Clinical Telemetry KPIs */
    .kpi-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 24px; }
    .kpi-card { display: flex; align-items: center; justify-content: space-between; gap: 16px; overflow: hidden; padding: 24px; }
    .kpi-meta { min-width: 0; flex: 1; }
    .kpi-meta h3 { font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.8px; color: var(--sys-text-secondary, #475569); font-weight: 800; margin-bottom: 8px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .kpi-meta .value { font-size: 2.2rem; font-weight: 800; color: #2c7be5; line-height: 1.1; display: block; }
    .kpi-meta .subtext { font-size: 0.82rem; color: var(--sys-text-secondary, #475569); font-weight: 600; display: block; margin-top: 6px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .kpi-icon { font-size: 2rem; width: 60px; height: 60px; border-radius: 18px; background: rgba(44, 123, 229, 0.12); display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: inset 0 2px 5px rgba(255,255,255,0.5); }

    /* Tier 2: Middle Interactive Self-Care Center */
    .interactive-center-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 24px; align-items: stretch; }
    .module-card { display: flex; flex-direction: column; justify-content: space-between; gap: 20px; height: 100%; min-height: 360px; overflow: hidden; }
    .module-title { font-size: 1.25rem; font-weight: 800; color: var(--sys-text-primary, #0f172a); display: flex; align-items: center; gap: 10px; margin-bottom: 6px; }

    /* Mood Check-In Matrix */
    .mood-buttons-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; margin-top: 16px; }
    .mood-btn { padding: 16px 12px; border-radius: 16px; border: none; font-weight: 800; font-size: 1rem; cursor: pointer; transition: all 0.2s ease; display: flex; align-items: center; justify-content: center; gap: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.04); }
    .mood-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(0,0,0,0.12); }
    .btn-happy { background: linear-gradient(135deg, #fff6d8, #ffeaa7); color: #1e293b !important; }
    .btn-okay { background: linear-gradient(135deg, #e3ecff, #e9ecef); color: #1e293b !important; }
    .btn-sad { background: linear-gradient(135deg, #3d3e42, #d0ddff); color: #1e293b !important; }
    .btn-stressed { background: linear-gradient(135deg, #ffe3e3, #ffb3b3); color: #1e293b !important; }

    .ai-feedback-box { background: rgba(44, 123, 229, 0.12); border: 1px solid rgba(44, 123, 229, 0.35); border-radius: 16px; padding: 16px; font-size: 0.95rem; line-height: 1.5; display: none; margin-top: 16px; color: var(--sys-text-primary, #0f172a); font-weight: 600; }

    /* Centering Space Ring */
    .breathing-container { position: relative; width: 150px; height: 150px; margin: auto; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .progress-ring__circle { transition: stroke-dashoffset 0.1s linear; transform: rotate(-90deg); transform-origin: 50% 50%; }
    .breathing-label { position: absolute; font-weight: 800; font-size: 1.15rem; color: #2c7be5; text-align: center; }

    /* Hydration Precision Grid */
    .water-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; max-width: 260px; margin: 24px auto; justify-items: center; }
    .water-glass { font-size: 1.8rem; cursor: pointer; filter: grayscale(100%) opacity(25%); transition: transform 0.2s, filter 0.2s; }
    .water-glass:hover { transform: scale(1.15); }
    .water-glass.active { filter: grayscale(0%) opacity(100%); transform: scale(1.15) translateY(-2px); }
    .water-glass.next-up { filter: grayscale(50%) opacity(70%); }

    /* Tier 3: Bottom Analytics Deck */
    .bottom-analytics-grid { display: grid; grid-template-columns: 1.65fr 1.35fr; gap: 24px; align-items: stretch; min-width: 0; }
    .chart-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 12px; }
    .chart-header h2 { font-size: 1.25rem; font-weight: 800; color: var(--sys-text-primary, #0f172a); display: flex; align-items: center; gap: 10px; }
    .chart-header .chart-tag { font-size: 0.8rem; background: rgba(44, 123, 229, 0.14); color: #2c7be5; padding: 6px 16px; border-radius: 14px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; }
    
    .canvas-wrapper, .doughnut-wrapper { position: relative; width: 100%; height: 300px; display: block; overflow: hidden; }

    .emergency-fab {
      position: fixed; bottom: 32px; right: 32px; width: 64px; height: 64px;
      background: #dc2626; border-radius: 50%; display: flex; align-items: center; justify-content: center;
      color: white; font-size: 1.8rem; text-decoration: none; box-shadow: 0 10px 28px rgba(220, 38, 38, 0.45);
      transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1); z-index: 999;
    }
    .emergency-fab:hover { transform: scale(1.12) rotate(12deg); background: #ef4444; box-shadow: 0 14px 38px rgba(220, 38, 38, 0.65); }

    /* Celebration Modal */
    .celebration-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100vh; background: rgba(10, 25, 47, 0.82); backdrop-filter: blur(20px); z-index: 10000; display: flex; align-items: center; justify-content: center; opacity: 0; pointer-events: none; transition: opacity 0.4s ease; }
    .celebration-overlay.show { opacity: 1; pointer-events: auto; }
    .celebration-modal { background: #ffffff; color: #0f172a; padding: 40px; border-radius: 32px; text-align: center; max-width: 420px; width: 90%; transform: scale(0.8); transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); box-shadow: 0 40px 90px rgba(0,0,0,0.4); }
    .celebration-overlay.show .celebration-modal { transform: scale(1); }
    .celebration-badge { font-size: 4rem; margin-bottom: 14px; display: inline-block; animation: badgePop 0.8s ease infinite alternate; }
    @keyframes badgePop { 0% { transform: scale(1); } 100% { transform: scale(1.15) rotate(7deg); } }

    /* Mobile Sidebar Overlay */
    .sidebar-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.6); backdrop-filter: blur(4px); z-index: 900; opacity: 0; transition: opacity 0.3s ease; }

    /* Responsive adjustments */
    @media (max-width: 1400px) { .bottom-analytics-grid { grid-template-columns: 1fr; } }
    @media (max-width: 1200px) {
      .kpi-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
      .interactive-center-grid { grid-template-columns: 1fr; }
      .module-card { min-height: auto; }
    }
    @media (max-width: 768px) {
      .kpi-grid { grid-template-columns: 1fr; }
      .main-content-area { padding: 16px; }
      .premium-dashboard-panel { padding: 24px; border-radius: 24px; }
      .app-header h1 { font-size: 1.5rem; }
      .inspiration-banner { flex-direction: column; text-align: center; }
      
      /* Mobile Menu Styles */
      .mobile-menu-btn { display: flex; }
      
      /* Target the included sidebar dynamically */
      aside, .sidebar, #sidebar {
        position: fixed !important;
        top: 0;
        left: -320px;
        width: 280px !important;
        height: 100vh !important;
        z-index: 950 !important;
        transition: left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 10px 0 30px rgba(0,0,0,0.2);
      }
      
      body.sidebar-open aside, 
      body.sidebar-open .sidebar, 
      body.sidebar-open #sidebar { left: 0; }
      
      body.sidebar-open .sidebar-overlay { display: block; opacity: 1; }
    }
  </style>
</head>
<body>
  <!-- Overlay for Mobile Sidebar -->
  <div id="mobile-overlay" class="sidebar-overlay" onclick="toggleSidebar()"></div>

  <div class="dashboard-layout">
    <?php include 'sidebar.php'; ?>

    <main class="main-content-area">
      <div class="premium-dashboard-panel">
        
        <!-- SUPPORTIVE WARM HEADER -->
        <header class="app-header">
          <div class="header-title-group">
            <button class="mobile-menu-btn" onclick="toggleSidebar()" aria-label="Open Sidebar">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="3" y1="12" x2="21" y2="12"></line>
                <line x1="3" y1="6" x2="21" y2="6"></line>
                <line x1="3" y1="18" x2="21" y2="18"></line>
              </svg>
            </button>
            <div>
              <h1>Welcome back, <?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?>! 🌿</h1>
              <p>Your peaceful sanctuary for emotional balance, mindfulness, and inner self-care</p>
            </div>
          </div>
          <div class="live-indicator online" id="live-status-indicator" title="Connection status and current time">
            <span class="pulse-dot" id="status-pulse-dot"></span>
            <span id="status-text">ONLINE</span>
            <span id="real-time-clock" style="margin-left: 8px; font-variant-numeric: tabular-nums; opacity: 0.85;"></span>
          </div>
        </header>

        <!-- TIER 1: CLINICAL KPI SUMMARY DECK -->
        <section class="kpi-grid">
          <div class="dashboard-card kpi-card">
            <div class="kpi-meta">
              <h3>Harmony Index</h3>
              <span class="value" id="kpi-harmony">--%</span>
              <span class="subtext" style="color: #16a34a; font-weight: 800;">Optimal Homeostasis</span>
            </div>
            <div class="kpi-icon" style="color: #e74c3c;">💗</div>
          </div>

          <div class="dashboard-card kpi-card">
            <div class="kpi-meta">
              <h3>Mindfulness Time</h3>
              <span class="value" id="kpi-mindfulness">0m</span>
              <span class="subtext">Somatic parasympathetic</span>
            </div>
            <div class="kpi-icon" style="color: #16a34a;">🧘‍♂️</div>
          </div>

          <div class="dashboard-card kpi-card">
            <div class="kpi-meta">
              <h3>Thought Vault</h3>
              <span class="value" id="kpi-journal">0</span>
              <span class="subtext">Recorded journal logs</span>
            </div>
            <div class="kpi-icon" style="color: #f39c12;">📝</div>
          </div>

          <div class="dashboard-card kpi-card">
            <div class="kpi-meta">
              <h3>AI Support Sessions</h3>
              <span class="value" id="kpi-ai">0</span>
              <span class="subtext">Gemini therapeutic talks</span>
            </div>
            <div class="kpi-icon" style="color: #2c7be5;">🤖</div>
          </div>
        </section>

        <!-- DAILY THOUGHT STREAM WITH TYPEWRITER EFFECT -->
        <div class="inspiration-banner">
          <div class="quote-content">
            <span class="quote-icon">💫</span>
            <div class="quote-text-area">
              <span id="quote-text" class="quote-text">Connecting to mental wellness thought stream...</span>
              <span id="quote-author" class="quote-author"></span>
            </div>
          </div>
        </div>

        <!-- TIER 2: INTERACTIVE SELF-CARE CENTER -->
        <section class="interactive-center-grid">
          
          <!-- MODULE 1: RAPID MOOD CHECK-IN & AI GUIDANCE -->
          <div class="dashboard-card module-card">
            <div>
              <div class="module-title"><span>🧭</span> Rapid Emotional Telemetry</div>
              <p style="font-size: 0.9rem; color: var(--sys-text-secondary, #475569); margin-top: 4px; font-weight: 600;">Record your current state to calibrate analytics & trigger AI guidance:</p>
              
              <div class="mood-buttons-grid">
                <button class="mood-btn btn-happy" onclick="submitLiveMood('Happy')">😊 Happy</button>
                <button class="mood-btn btn-okay" onclick="submitLiveMood('Okay')">😐 Okay</button>
                <button class="mood-btn btn-sad" onclick="submitLiveMood('Sad')">😞 Sad</button>
                <button class="mood-btn btn-stressed" onclick="submitLiveMood('Stressed')">😡 Stressed</button>
              </div>

              <div id="ai-insight-box" class="ai-feedback-box">
                <strong style="color: #2c7be5; display: block; margin-bottom: 8px; font-weight: 800; font-size: 0.95rem;">🤖 Jarvis Clinical Guidance:</strong>
                <span id="ai-insight-msg"></span>
              </div>
            </div>

            <div style="text-align: right; margin-top: 20px; padding-top: 16px; border-top: 1px solid rgba(255,255,255,0.4);">
              <a href="wellness.php" style="font-size: 0.9rem; font-weight: 800; color: #2c7be5; text-decoration: none; padding: 10px 20px; background: rgba(44,123,229,0.14); border-radius: 100px; transition: all 0.2s; display: inline-block;">Open Thought Vault ➔</a>
            </div>
          </div>
          
          <!-- MODULE 2: 60-SEC VAGUS RESET -->
          <div class="dashboard-card module-card" style="text-align: center;">
            <div style="width: 100%;">
              <div class="module-title" style="justify-content: center;"><span>🫧</span> 60-Sec Vagus Reset</div>
              <p style="font-size: 0.9rem; color: var(--sys-text-secondary, #475569); margin-top: 4px; font-weight: 600;">Sync breathing cadence to anchor focus</p>
            </div>

            <div class="breathing-container">
              <svg width="150" height="150">
                <circle stroke="rgba(44, 123, 229, 0.15)" stroke-width="12" fill="transparent" r="63" cx="75" cy="75"/>
                <circle id="breathing-ring" class="progress-ring__circle" stroke="#2c7be5" stroke-width="12" stroke-linecap="round" fill="transparent" r="63" cx="75" cy="75" stroke-dasharray="395.84" stroke-dashoffset="395.84"/>
              </svg>
              <div id="breathing-label" class="breathing-label">Ready</div>
            </div>

            <button id="breath-btn" onclick="startCircularBreathing()" style="width: 100%; max-width: 240px; margin: auto auto 0 auto; padding: 14px 24px; border: none; background: linear-gradient(135deg, #4da3ff, #2c7be5); color: white; border-radius: 16px; cursor: pointer; font-weight: 800; font-size: 1rem; box-shadow: 0 6px 18px rgba(44, 123, 229, 0.35); transition: all 0.2s;">
              Activate Reset Cycle
            </button>
          </div>

          <!-- MODULE 3: DAILY HYDRATION TARGET -->
          <div class="dashboard-card module-card" style="text-align: center;">
            <div>
              <div class="module-title" style="justify-content: center;"><span>💧</span> Daily Hydration Target</div>
              <p style="font-size: 0.9rem; color: var(--sys-text-secondary, #475569); margin-top: 4px; font-weight: 600;">Cognitive equilibrium fluidity indicator</p>
              
              <strong id="water-counter-label" style="font-size: 1.8rem; font-weight: 800; color: #15803d; display: block; margin-top: 16px;">0 / 8 Glasses</strong>
              
              <div class="water-grid">
                <span class="water-glass" id="glass-1" onclick="toggleWater(1)" title="Glass 1">🥛</span>
                <span class="water-glass" id="glass-2" onclick="toggleWater(2)" title="Glass 2">🥛</span>
                <span class="water-glass" id="glass-3" onclick="toggleWater(3)" title="Glass 3">🥛</span>
                <span class="water-glass" id="glass-4" onclick="toggleWater(4)" title="Glass 4">🥛</span>
                <span class="water-glass" id="glass-5" onclick="toggleWater(5)" title="Glass 5">🥛</span>
                <span class="water-glass" id="glass-6" onclick="toggleWater(6)" title="Glass 6">🥛</span>
                <span class="water-glass" id="glass-7" onclick="toggleWater(7)" title="Glass 7">🥛</span>
                <span class="water-glass" id="glass-8" onclick="toggleWater(8)" title="Glass 8">🥛</span>
              </div>
            </div>

            <div style="font-size: 0.85rem; color: var(--sys-text-secondary, #475569); font-weight: 600; margin-top: auto; padding-top: 16px; border-top: 1px solid rgba(255,255,255,0.4);">
              <span>💡 Tap consecutive glasses to record fluid intake</span>
            </div>
          </div>

        </section>

        <!-- TIER 3: BOTTOM ANALYTICAL CHARTS -->
        <section class="bottom-analytics-grid">
          
          <!-- BOTTOM LEFT: EMOTIONAL BALANCE & MOOD ARC CHART -->
          <div class="dashboard-card">
            <div class="chart-header">
              <h2 id="trend-chart-title"><span>📊</span> 7-Day Emotional Balance & Mood Arc</h2>
              <div style="display: flex; align-items: center; gap: 12px;">
                <select id="trend-range-selector" onchange="changeTrendRange()" style="padding: 8px 16px; border-radius: 12px; border: 1px solid rgba(44, 123, 229, 0.3); background: var(--sys-card-bg, rgba(255, 255, 255, 0.55)); color: var(--sys-text-primary); font-weight: 700; font-size: 0.85rem; cursor: pointer; outline: none; transition: all 0.2s;">
                  <option value="last24hrs">Last 24 Hours</option>
                  <option value="1weeks" selected>Last 1 Week</option>
                  <option value="1month">Last 1 Month</option>
                  <option value="6months">Last 6 Months</option>
                  <option value="1year">Last 1 Year</option>
                  <option value="11yr">Last 11 Years</option>
                </select>
                <span class="chart-tag">Longitudinal Trend</span>
              </div>
            </div>
            <div class="canvas-wrapper">
              <canvas id="moodTrendChart"></canvas>
            </div>
          </div>

          <!-- BOTTOM RIGHT: PRACTICE DISTRIBUTION DOUGHNUT CHART -->
          <div class="dashboard-card">
            <div class="chart-header">
              <h2><span>🎯</span> Practice Distribution</h2>
              <span class="chart-tag">Therapy Matrix</span>
            </div>
            <div class="doughnut-wrapper">
              <canvas id="practiceDistChart"></canvas>
            </div>
          </div>

        </section>

      </div>
    </main>
  </div>

  <div id="celebration-overlay" class="celebration-overlay">
    <div class="celebration-modal">
      <span class="celebration-badge" id="celebration-emoji">🎉</span>
      <h2 id="celebration-title" style="font-size: 1.8rem; margin-bottom: 12px; font-weight: 800; color: #0f172a;">Magnificent Achievement!</h2>
      <p id="celebration-msg" style="color: #475569; font-size: 1rem; line-height: 1.5; font-weight: 600;"></p>
    </div>
  </div>

  <a href="emergency.php" class="emergency-fab" title="Instant Emergency SOS Trigger">🚨</a>

  <script>
    let trendChartInstance = null;
    let distChartInstance = null;
    let celebrationTimeout;
    let typingTimeout = null;
    let selectedRange = '1weeks';

    // Mobile Sidebar Toggle Script
    function toggleSidebar() {
      document.body.classList.toggle('sidebar-open');
    }

    window.addEventListener('DOMContentLoaded', async () => {
        initializeWaterTracker();
        fetchDailyThought();
        await renderExecutiveTelemetry();
        
        // Live real-time clock and status checker
        updateRealTimeAndStatus();
        setInterval(updateRealTimeAndStatus, 1000);
    });

    let lastPingTime = 0;
    async function updateRealTimeAndStatus() {
      const timeEl = document.getElementById('real-time-clock');
      const indicatorEl = document.getElementById('live-status-indicator');
      const dotEl = document.getElementById('status-pulse-dot');
      const textEl = document.getElementById('status-text');
      
      const now = new Date();
      let hours = now.getHours();
      const minutes = String(now.getMinutes()).padStart(2, '0');
      const seconds = String(now.getSeconds()).padStart(2, '0');
      const ampm = hours >= 12 ? 'PM' : 'AM';
      hours = hours % 12;
      hours = hours ? hours : 12;
      const timeStr = `${String(hours).padStart(2, '0')}:${minutes}:${seconds} ${ampm}`;
      if (timeEl) {
        timeEl.textContent = timeStr;
      }
      
      function setOnlineUI() {
        if (indicatorEl) {
          indicatorEl.classList.remove('offline');
          indicatorEl.classList.add('online');
        }
        if (textEl) textEl.textContent = 'ONLINE';
      }

      function setOfflineUI() {
        if (indicatorEl) {
          indicatorEl.classList.remove('online');
          indicatorEl.classList.add('offline');
        }
        if (textEl) textEl.textContent = 'OFFLINE';
      }

      if (!navigator.onLine) {
        setOfflineUI();
        return;
      }

      const nowMs = Date.now();
      if (nowMs - lastPingTime > 10000) {
        lastPingTime = nowMs;
        try {
          const response = await fetch('db.php', { method: 'HEAD', cache: 'no-store' });
          if (response.ok) {
            setOnlineUI();
          } else {
            setOfflineUI();
          }
        } catch (e) {
          setOfflineUI();
        }
      }
    }

    function changeTrendRange() {
        const selector = document.getElementById('trend-range-selector');
        if (selector) {
            selectedRange = selector.value;
            const titleEl = document.getElementById('trend-chart-title');
            if (titleEl) {
                const textMap = {
                    'last24hrs': '24-Hour Emotional Balance & Mood Arc',
                    '1weeks': '7-Day Emotional Balance & Mood Arc',
                    '1month': '30-Day Emotional Balance & Mood Arc',
                    '6months': '6-Month Emotional Balance & Mood Arc',
                    '1year': '12-Month Emotional Balance & Mood Arc',
                    '11yr': '11-Year Emotional Balance & Mood Arc'
                };
                titleEl.innerHTML = `<span>📊</span> ${textMap[selectedRange] || 'Emotional Balance & Mood Arc'}`;
            }
            renderExecutiveTelemetry();
        }
    }

    async function fetchDailyThought() {
      const thoughtEl = document.getElementById("quote-text");
      const authorEl = document.getElementById("quote-author");
      clearTimeout(typingTimeout);
      thoughtEl.innerHTML = '<span style="opacity:0.6;">Fetching daily mental wellness inspiration...</span>';
      authorEl.textContent = "";

      try {
        const response = await fetch("https://api.allorigins.win/raw?url=https://zenquotes.io/api/random");
        if (!response.ok) throw new Error("Network issue");
        const data = await response.json();
        const thoughtText = `"${data[0].q}"`;
        const authorText = `— ${data[0].a}`;
        startTypewriterEffect(thoughtText, authorText);
      } catch (error) {
        const fallbackThoughts = [
          { q: "Your mental health is a priority. Your self-care is an absolute necessity.", a: "Jarvis Clinical Guidance" },
          { q: "You don’t have to see the whole staircase, just take the first step.", a: "Martin Luther King Jr." },
          { q: "Peace comes from within. Do not seek it without.", a: "Buddha" },
          { q: "Almost everything will work again if you unplug it for a few minutes, including yourself.", a: "Anne Lamott" },
          { q: "Self-compassion is simply giving the same kindness to ourselves that we would give to others.", a: "Christopher Germer" }
        ];
        const pick = fallbackThoughts[Math.floor(Math.random() * fallbackThoughts.length)];
        startTypewriterEffect(`"${pick.q}"`, `— ${pick.a}`);
      }
    }

    function startTypewriterEffect(text, author) {
      const thoughtEl = document.getElementById("quote-text");
      const authorEl = document.getElementById("quote-author");
      clearTimeout(typingTimeout);
      
      thoughtEl.innerHTML = '<span id="typing-content"></span><span class="blinking-cursor">|</span>';
      authorEl.textContent = "";
      
      const contentEl = document.getElementById("typing-content");
      let idx = 0;

      function typeChar() {
        if (idx < text.length) {
          contentEl.textContent += text.charAt(idx);
          idx++;
          typingTimeout = setTimeout(typeChar, 32);
        } else {
          authorEl.textContent = author;
          authorEl.style.opacity = 0;
          authorEl.style.transition = "opacity 0.5s ease";
          setTimeout(() => { authorEl.style.opacity = 1; }, 50);
        }
      }
      typeChar();
    }

    async function renderExecutiveTelemetry() {
        try {
            const res = await fetch(`dashboard_analytics.php?action=get_telemetry&range=${selectedRange}`);
            const data = await res.json();
            
            if (data.status === 'success') {
                document.getElementById('kpi-harmony').textContent = data.kpi.harmony_score + "%";
                document.getElementById('kpi-mindfulness').textContent = data.kpi.mindfulness_minutes + "m";
                document.getElementById('kpi-journal').textContent = data.kpi.total_reflections;
                document.getElementById('kpi-ai').textContent = data.kpi.ai_checkins;

                initOrUpdateCharts(data.charts);
            }
        } catch(e) {
            console.error('Error fetching clinical telemetry from MySQL server:', e);
        }
    }

    function initOrUpdateCharts(chartsData) {
        const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
        const fontColor = isDark ? '#f8fafc' : '#0f172a';
        const gridColor = isDark ? 'rgba(255,255,255,0.08)' : 'rgba(0,0,0,0.08)';

        Chart.defaults.color = fontColor;
        Chart.defaults.font.family = "'SF Pro Display', 'Segoe UI', Inter, -apple-system, sans-serif";

        const trendCtx = document.getElementById('moodTrendChart').getContext('2d');
        if (trendChartInstance) trendChartInstance.destroy();
        
        trendChartInstance = new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: chartsData.trend_labels,
                datasets: [
                    { label: 'Happy', data: chartsData.trend.Happy, borderColor: '#16a34a', backgroundColor: 'rgba(22, 163, 74, 0.12)', borderWidth: 3, tension: 0.4, fill: true, pointRadius: 4, pointHoverRadius: 6 },
                    { label: 'Okay', data: chartsData.trend.Okay, borderColor: '#2c7be5', backgroundColor: 'rgba(44, 123, 229, 0.12)', borderWidth: 3, tension: 0.4, fill: true, pointRadius: 4, pointHoverRadius: 6 },
                    { label: 'Sad', data: chartsData.trend.Sad, borderColor: '#9333ea', backgroundColor: 'rgba(147, 51, 234, 0.12)', borderWidth: 3, tension: 0.4, fill: true, pointRadius: 4, pointHoverRadius: 6 },
                    { label: 'Stressed', data: chartsData.trend.Stressed, borderColor: '#dc2626', backgroundColor: 'rgba(220, 38, 38, 0.12)', borderWidth: 3, tension: 0.4, fill: true, pointRadius: 4, pointHoverRadius: 6 }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { 
                    legend: { position: 'top', align: 'end', labels: { color: fontColor, boxWidth: 12, padding: 16, font: { size: 12, weight: '700' } } },
                    tooltip: { padding: 12, cornerRadius: 10, bodyFont: { size: 13 } }
                },
                scales: {
                    y: { beginAtZero: true, ticks: { color: fontColor, precision: 0, font: { size: 11, weight: '700' } }, grid: { color: gridColor } },
                    x: { ticks: { color: fontColor, font: { size: 11, weight: '700' } }, grid: { display: false } }
                }
            }
        });

        const distCtx = document.getElementById('practiceDistChart').getContext('2d');
        if (distChartInstance) distChartInstance.destroy();
        
        distChartInstance = new Chart(distCtx, {
            type: 'doughnut',
            data: {
                labels: chartsData.distribution_labels,
                datasets: [{
                    data: chartsData.distribution_values,
                    backgroundColor: ['#2c7be5', '#16a34a', '#f39c12', '#9333ea'],
                    borderWidth: 2.5,
                    borderColor: isDark ? '#0f172a' : '#ffffff',
                    hoverOffset: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { 
                      position: 'bottom', 
                      labels: { color: fontColor, boxWidth: 10, padding: 12, font: { size: 11, weight: '700' } } 
                    },
                    tooltip: { padding: 12, cornerRadius: 10, bodyFont: { size: 13 } }
                },
                cutout: '70%'
            }
        });
    }

    async function submitLiveMood(mood) {
        const insightBox = document.getElementById("ai-insight-box");
        const insightMsg = document.getElementById("ai-insight-msg");
        insightBox.style.display = "block";

        if (mood === 'Happy') {
            insightMsg.textContent = "Optimal baseline registered! Your positive momentum strengthens your Harmony Index.";
        } else if (mood === 'Stressed') {
            insightMsg.textContent = "Elevated cortisol patterns detected. Complete a 60-Sec Vagus Reset cycle right next to this module.";
        } else if (mood === 'Sad') {
            insightMsg.textContent = "Low energy thresholds acknowledged. Be patient with emotional recovery; consider visiting the AI Chatbot.";
        } else {
            insightMsg.textContent = "Steady equilibrium recorded in database. A balanced day provides an excellent foundation.";
        }

        try {
            await fetch('wellness_api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'save_log', logText: `Dashboard Quick Check-in: Feeling ${mood}`, moodTag: mood })
            });
            await renderExecutiveTelemetry();
        } catch(e) {
            console.warn('Offline logging:', e);
        }
    }

    let breathingInProgress = false;
    function startCircularBreathing() {
      if (breathingInProgress) return;
      breathingInProgress = true;

      const ring = document.getElementById('breathing-ring');
      const label = document.getElementById('breathing-label');
      const btn = document.getElementById('breath-btn');
      const circumference = 2 * Math.PI * 63;

      btn.disabled = true;
      btn.style.background = "#64748b";
      btn.textContent = "Session Active...";

      let phase = 0;
      let cycleCount = 0;
      const targetTotalCycles = 3;

      function runBreathPhase() {
        if (cycleCount >= targetTotalCycles) {
          ring.style.strokeDashoffset = circumference;
          label.textContent = "Done";
          btn.disabled = false;
          btn.style.background = "linear-gradient(135deg, #4da3ff, #2c7be5)";
          btn.textContent = "Activate Reset Cycle";
          breathingInProgress = false;

          logSomaticCompletion("60-Sec Dashboard Vagus Reset", 60);
          launchCelebration("🧘‍♂️", "Vagus Nerve Centered!", "Excellently executed. Your breathing cadence has anchored your nervous system and enhanced your daily score!");
          return;
        }

        if (phase === 0) {
          label.textContent = "Breathe In";
          animateRingOffset(circumference, 0, 4000, () => { phase = 1; runBreathPhase(); });
        } else if (phase === 1) {
          label.textContent = "Hold";
          setTimeout(() => { phase = 2; runBreathPhase(); }, 4000);
        } else if (phase === 2) {
          label.textContent = "Breathe Out";
          animateRingOffset(0, circumference, 4000, () => { phase = 3; runBreathPhase(); });
        } else if (phase === 3) {
          label.textContent = "Hold";
          setTimeout(() => { phase = 0; cycleCount++; runBreathPhase(); }, 4000);
        }
      }

      runBreathPhase();
    }

    function animateRingOffset(fromOffset, toOffset, duration, callback) {
      const ring = document.getElementById('breathing-ring');
      const startTime = performance.now();

      function step(now) {
        const elapsed = now - startTime;
        const progress = Math.min(elapsed / duration, 1);
        ring.style.strokeDashoffset = fromOffset + (toOffset - fromOffset) * progress;

        if (progress < 1) requestAnimationFrame(step);
        else if (callback) callback();
      }
      requestAnimationFrame(step);
    }

    async function logSomaticCompletion(name, seconds) {
        try {
            await fetch('wellness_api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'log_activity', type: 'Breathing/Timer', name: name, duration: seconds })
            });
            await renderExecutiveTelemetry();
        } catch(e) { console.warn("Could not record somatic exercise:", e); }
    }

    let currentWaterCount = 0;
    function initializeWaterTracker() {
      const todayKey = 'jarvis_water_' + new Date().toISOString().slice(0, 10);
      currentWaterCount = parseInt(localStorage.getItem(todayKey) || "0", 10);
      updateWaterUIVisuals();
    }

    function toggleWater(glassId) {
      const todayKey = 'jarvis_water_' + new Date().toISOString().slice(0, 10);
      if (glassId === currentWaterCount + 1) {
        currentWaterCount = glassId;
        if (currentWaterCount === 8) {
          launchCelebration("💧", "Hydration Target Met!", "Superb dedication! You have achieved your 8-glass hydration baseline for optimal cognition today.");
          setTimeout(() => { currentWaterCount = 0; localStorage.setItem(todayKey, "0"); updateWaterUIVisuals(); }, 3500);
        }
      } else if (glassId === currentWaterCount) {
        currentWaterCount = glassId - 1;
      } else return;

      localStorage.setItem(todayKey, currentWaterCount);
      updateWaterUIVisuals();
    }

    function updateWaterUIVisuals() {
      document.getElementById('water-counter-label').textContent = `${currentWaterCount} / 8 Glasses`;
      for (let i = 1; i <= 8; i++) {
        const glass = document.getElementById(`glass-${i}`);
        if (!glass) continue;
        glass.classList.remove('active', 'next-up');
        if (i <= currentWaterCount) glass.classList.add('active');
        else if (i === currentWaterCount + 1) glass.classList.add('next-up');
      }
    }

    function launchCelebration(emoji, title, textMessage) {
      document.getElementById('celebration-emoji').textContent = emoji;
      document.getElementById('celebration-title').textContent = title;
      document.getElementById('celebration-msg').textContent = textMessage;
      const overlay = document.getElementById('celebration-overlay');
      overlay.classList.add('show');
      clearTimeout(celebrationTimeout);
      celebrationTimeout = setTimeout(() => { overlay.classList.remove('show'); }, 3800);
    }
  </script>
</body>
</html>