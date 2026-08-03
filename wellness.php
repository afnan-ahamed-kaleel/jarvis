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
  <title>Wellness Hub – Jarvis</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    * { box-sizing: border-box; font-family: "SF Pro Display", "Segoe UI", Inter, sans-serif; margin: 0; padding: 0; }
    body { min-height: 100vh; background: radial-gradient(circle at center, #306f9f, #f0f7fc), linear-gradient(135deg, #063776, #eef3ff); color: #211f3d; display: flex; overflow-x: hidden; }
    
    .dashboard-layout { display: flex; width: 100vw; height: 100vh; overflow: hidden; }
    .main-content-area { flex: 1; padding: 35px; overflow-y: auto; height: 100vh; }
    
    .premium-dashboard-panel { 
      background: rgba(255, 255, 255, 0.25); backdrop-filter: blur(30px) saturate(170%); -webkit-backdrop-filter: blur(30px) saturate(170%);
      border-radius: 32px; padding: 35px; box-shadow: 0 40px 80px rgba(0, 0, 0, 0.08), inset 0 1px 0 rgba(255, 255, 255, 0.4); 
      min-height: calc(100vh - 70px); display: flex; flex-direction: column; gap: 25px;
    }
    
    /* Sticky Sidebar Realignment Configurations */
    .jarvis-sidebar { 
      width: 80px; height: 100vh; position: sticky; top: 0; background: rgba(255, 255, 255, 0.3); backdrop-filter: blur(25px); -webkit-backdrop-filter: blur(25px);
      border-right: 1px solid rgba(255, 255, 255, 0.4); display: flex; flex-direction: column; justify-content: space-between; padding: 24px 12px; 
      transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1); overflow: hidden; z-index: 100; box-shadow: 10px 0 35px rgba(0, 0, 0, 0.05);
    }
    .jarvis-sidebar:hover { width: 260px; }
    .sidebar-brand { display: flex; align-items: center; gap: 16px; padding-left: 10px; margin-bottom: 40px; }
    .brand-img { width: 40px; height: 40px; object-fit: contain; }    
    .brand-text { font-size: 1.3rem; font-weight: 700; white-space: nowrap; opacity: 0; transition: opacity 0.2s ease; }
    .jarvis-sidebar:hover .brand-text { opacity: 1; }
    .sidebar-menu { list-style: none; display: flex; flex-direction: column; gap: 12px; flex: 1; }
    .sidebar-menu li a, .logout-btn-nav { display: flex; align-items: center; gap: 20px; padding: 14px; border-radius: 16px; text-decoration: none; color: #34495e; font-size: 0.95rem; font-weight: 500; transition: all 0.2s ease; white-space: nowrap; }
    .sidebar-menu li a .icon, .logout-btn-nav .icon { font-size: 1.4rem; display: inline-block; text-align: center; width: 30px; }
    .menu-text { opacity: 0; transition: opacity 0.2s ease; }
    .jarvis-sidebar:hover .menu-text { opacity: 1; }
    .sidebar-menu li a:hover { background: rgba(255, 255, 255, 0.5); transform: translateX(4px); }
    .sidebar-menu li a.active { background: linear-gradient(135deg, #4da3ff, #2c7be5); color: white; box-shadow: 0 8px 20px rgba(44, 123, 229, 0.3); }
    .logout-btn-nav { color: #d63031; background: rgba(214, 48, 49, 0.08); margin-top: auto; }
    .logout-btn-nav:hover { background: #d63031 !important; color: white !important; }

    .app-header { text-align: left; }
    .app-header h1 { font-size: 2.3rem; letter-spacing: -0.5px; }
    .app-header p { margin-top: 4px; font-size: 1rem; color: rgba(31, 45, 61, 0.65); }

    /* Fixed Clean Layout Grid - Aligned into 3 Equal Columns */
    .wellness-layout-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 25px; align-items: start; }
    .wellness-card { background: rgba(255, 255, 255, 0.4); border-radius: 24px; padding: 24px; border: 1px solid rgba(255, 255, 255, 0.5); backdrop-filter: blur(10px); box-shadow: 0 10px 30px rgba(0,0,0,0.02); min-height: 440px; display: flex; flex-direction: column; justify-content: flex-start; }

    .wellness-action-card {
      width: 100%; padding: 18px; border-radius: 16px; border: none;
      background: linear-gradient(135deg, #4da3ff, #2c7be5); font-size: 1rem; font-weight: 600;
      color: white; cursor: pointer; transition: all 0.2s; box-shadow: 0 8px 20px rgba(44, 123, 229, 0.2); margin-bottom: 12px;
    }
    .wellness-action-card:hover { transform: translateY(-2px); box-shadow: 0 12px 25px rgba(44, 123, 229, 0.3); }
    
    #suggestions { background: rgba(255,255,255,0.7); backdrop-filter: blur(12px); border-radius: 18px; padding: 20px; font-size: 0.95rem; color: #211f3d; box-shadow: 0 10px 25px rgba(0,0,0,0.03); border: 1px solid rgba(255,255,255,0.4); display: none; line-height: 1.6; flex: 1; overflow-y: auto; }

    /* Analytics Dashboard Bars */
    .metrics-bar-track { width: 100%; height: 12px; background: rgba(0,0,0,0.05); border-radius: 6px; margin-top: 6px; margin-bottom: 4px; overflow: hidden; display: flex; }
    .metrics-bar-fill { height: 100%; transition: width 0.5s ease; }

    /* Emergency Floating Action Trigger Asset styling */
    .emergency-fab { position: fixed; bottom: 30px; right: 30px; width: 60px; height: 60px; background: #d63031; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.6rem; text-decoration: none; box-shadow: 0 10px 30px rgba(214, 48, 49, 0.4); transition: all 0.25s ease; z-index: 999; }
    .emergency-fab:hover { transform: scale(1.1) rotate(12deg); background: #ff7675; box-shadow: 0 14px 35px rgba(214, 48, 49, 0.5); }

    /* Column 1 Specific Layout Frameworks */
    .pill-box { display: flex; gap: 6px; margin: 12px 0; flex-wrap: wrap; }
    .filter-pill { padding: 6px 12px; border-radius: 20px; border: 1px solid rgba(0,0,0,0.05); background: rgba(255,255,255,0.6); font-size: 0.8rem; font-weight: 600; cursor: pointer; transition: all 0.2s; color: #34495e; }
    .filter-pill.active { background: #2c7be5; color: white; border-color: #2c7be5; }
    .journal-entry-item { background: rgba(255,255,255,0.5); padding: 12px; border-radius: 14px; margin-bottom: 10px; border: 1px solid rgba(255,255,255,0.2); }
    .tag-indicator { display: inline-block; padding: 2px 6px; font-size: 0.7rem; font-weight: 700; border-radius: 6px; text-transform: uppercase; }
  </style>
</head>
<body>

  <div class="dashboard-layout">
    <?php include 'sidebar.php'; ?>

    <main class="main-content-area">
      <div class="premium-dashboard-panel">

        <header class="app-header">
          <h1>Wellness Engine</h1>
          <p>Analytical metrics and personalized supportive routines 🌿</p>
        </header>

        <div class="wellness-layout-grid">

          <div class="wellness-card">
            <h2 style="font-size: 1.2rem; font-weight: 600; margin-bottom: 4px;">Interactive Thought Vault</h2>
            <p style="font-size: 0.85rem; color: #57606f; margin-bottom: 12px;">Document internal friction to isolate cognitive trends over time</p>
            
            <div style="display: flex; flex-direction: column; gap: 8px;">
              <textarea id="journal-text" placeholder="Write down a reflection entry about your day..." style="width: 100%; height: 65px; padding: 10px; border-radius: 10px; border: 1px solid rgba(0,0,0,0.1); background: rgba(255,255,255,0.7); outline: none; font-size: 0.85rem; resize: none;"></textarea>
              <div style="display: flex; justify-content: space-between; align-items: center;">
                <select id="journal-mood" style="padding: 6px 10px; border-radius: 8px; border: 1px solid rgba(0,0,0,0.1); background: white; font-size: 0.8rem; outline: none;">
                  <option value="Happy">😊 Happy</option>
                  <option value="Okay">😐 Okay</option>
                  <option value="Sad">😞 Sad</option>
                  <option value="Stressed">😡 Stressed</option>
                </select>
                <button onclick="commitJournalEntry()" style="padding: 6px 14px; background: #211f3d; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 0.8rem;">Save</button>
              </div>
            </div>

            <hr style="margin: 15px 0; border: none; border-top: 1px solid rgba(255,255,255,0.4);">

            <div class="pill-box">
              <span class="filter-pill active" id="pill-all" onclick="filterLogs('All')">All</span>
              <span class="filter-pill" id="pill-happy" onclick="filterLogs('Happy')">😊</span>
              <span class="filter-pill" id="pill-okay" onclick="filterLogs('Okay')">😐</span>
              <span class="filter-pill" id="pill-sad" onclick="filterLogs('Sad')">😞</span>
              <span class="filter-pill" id="pill-stressed" onclick="filterLogs('Stressed')">😡</span>
            </div>

            <div id="journal-logs-mount" style="max-height: 180px; overflow-y: auto; padding-right: 2px;"></div>
          </div> 
          
          <div class="wellness-card">
            <h2 style="font-size: 1.2rem; font-weight: 600; margin-bottom: 4px;">AI Optimization Engine</h2>
            <p style="font-size: 0.85rem; color: #57606f; margin-bottom: 18px;">Process logged interactions to construct an automated mental health baseline diagnostic overview.</p>

            <button class="wellness-action-card" onclick="runComprehensiveMoodAnalysis()">
              Compile Personalized Diagnostic Log
            </button>

            <div id="suggestions"></div>
          </div>

          <div class="wellness-card">
            <h2 style="font-size: 1.2rem; font-weight: 600; margin-bottom: 4px;">Longitudinal Mood Metrics</h2>
            <p style="font-size: 0.85rem; color: #57606f; margin-bottom: 20px;">Percentage-mapped distributions based on active journal logs</p>

            <div style="display: flex; flex-direction: column; gap: 16px; width: 100%;">
              <div>
                <div style="display:flex; justify-content:space-between; font-size:0.85rem; font-weight:600;">
                  <span>😊 Happy Distribution</span><span id="stat-happy-lbl">0%</span>
                </div>
                <div class="metrics-bar-track"><div id="stat-happy-bar" class="metrics-bar-fill" style="background:#ffeaa7; width:0%;"></div></div>
              </div>

              <div>
                <div style="display:flex; justify-content:space-between; font-size:0.85rem; font-weight:600;">
                  <span>😐 Balanced Baseline</span><span id="stat-okay-lbl">0%</span>
                </div>
                <div class="metrics-bar-track"><div id="stat-okay-bar" class="metrics-bar-fill" style="background:#e9ecef; width:0%;"></div></div>
              </div>

              <div>
                <div style="display:flex; justify-content:space-between; font-size:0.85rem; font-weight:600;">
                  <span>😞 Low Energy / Sad</span><span id="stat-sad-lbl">0%</span>
                </div>
                <div class="metrics-bar-track"><div id="stat-sad-bar" class="metrics-bar-fill" style="background:#4da3ff; width:0%;"></div></div>
              </div>

              <div>
                <div style="display:flex; justify-content:space-between; font-size:0.85rem; font-weight:600;">
                  <span>😡 High Stress / Overwhelmed</span><span id="stat-stressed-lbl">0%</span>
                </div>
                <div class="metrics-bar-track"><div id="stat-stressed-bar" class="metrics-bar-fill" style="background:#ffb3b3; width:0%;"></div></div>
              </div>
            </div>
          </div>

        </div> </div>
    </main>
  </div>

  <a href="emergency.php" class="emergency-fab" title="Immediate Care Help">🚨</a>

  <script>
    let currentLogs = [];
    let selectedActiveFilter = 'All';

    window.addEventListener('DOMContentLoaded', async () => {
        await syncLegacyLocalStorage();
        await loadWellnessDatabase();
    });

    // Silently transfer legacy browser entries to MySQL database
    async function syncLegacyLocalStorage() {
        const legacyVault = JSON.parse(localStorage.getItem('jarvis_journal_vault')) || [];
        if (legacyVault.length > 0) {
            try {
                await fetch('wellness_api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'sync_legacy', entries: legacyVault })
                });
                // Once synced to MySQL, clear legacy storage to prevent redundant syncing
                localStorage.removeItem('jarvis_journal_vault');
            } catch (e) {
                console.warn('Silent DB sync incomplete:', e);
            }
        }
    }

    async function loadWellnessDatabase() {
        try {
            const res = await fetch('wellness_api.php?action=get_logs');
            const data = await res.json();
            if (data.status === 'success') {
                currentLogs = data.logs || [];
                updateDistributionBars(data.distribution);
                renderJournalHistoryLog();
            }
        } catch (error) {
            console.error('Error loading wellness records from server:', error);
        }
    }

    function updateDistributionBars(dist) {
        if (!dist) return;
        document.getElementById('stat-happy-lbl').textContent = dist.Happy + "%";
        document.getElementById('stat-happy-bar').style.width = dist.Happy + "%";

        document.getElementById('stat-okay-lbl').textContent = dist.Okay + "%";
        document.getElementById('stat-okay-bar').style.width = dist.Okay + "%";

        document.getElementById('stat-sad-lbl').textContent = dist.Sad + "%";
        document.getElementById('stat-sad-bar').style.width = dist.Sad + "%";

        document.getElementById('stat-stressed-lbl').textContent = dist.Stressed + "%";
        document.getElementById('stat-stressed-bar').style.width = dist.Stressed + "%";
    }

    function runComprehensiveMoodAnalysis() {
        const targetOutput = document.getElementById('suggestions');
        targetOutput.style.display = "block";

        if (currentLogs.length === 0) {
            targetOutput.innerHTML = `
                <strong style="color:#2c7be5; display:block; margin-bottom:6px;">📋 Database Pool Empty</strong>
                I require logged inputs before compiling an assessment. Please create your first entry inside the Thought Vault to begin database analysis!
            `;
            return;
        }

        const primaryCurrentState = currentLogs[0].moodTag;
        let suggestionHtml = ``;

        if (primaryCurrentState === 'Happy') {
            suggestionHtml = `
                <strong style="color:#2c7be5; display:block; margin-bottom:6px;">🤖 Jarvis Optimization Strategy: Stability Profile</strong>
                Your database profile indicates active positive balance markers. To sustain this momentum, consider using your <strong>Resources Workspace</strong> to document exactly what facilitated this outcome.
            `;
        } else if (primaryCurrentState === 'Stressed') {
            suggestionHtml = `
                <strong style="color:#d63031; display:block; margin-bottom:6px;">🤖 Jarvis Optimization Strategy: Sympathetic De-escalation</strong>
                Your profile indicates elevated high-frequency stress tracking signatures in recent database logs.
                <br><br>
                <strong>Prescription Matrix:</strong>
                <ul style="margin-left:20px; margin-top:8px;">
                    <li>Initiate a Box Breathing Reset directly in your Resources workspace to lower heart rate variability.</li>
                    <li>Activate the <em>Lo-Fi Focus Waves</em> track inside the Ambience loops to reduce cognitive friction.</li>
                </ul>
            `;
        } else if (primaryCurrentState === 'Sad') {
            suggestionHtml = `
                <strong style="color:#2c7be5; display:block; margin-bottom:6px;">🤖 Jarvis Optimization Strategy: Dopaminergic Up-regulation</strong>
                Your database logs flag lower energy thresholds. Do not rush to force an artificial baseline change; recovery requires structured patience.
                <br><br>
                <strong>Prescription Matrix:</strong>
                <ul style="margin-left:20px; margin-top:8px;">
                    <li>Listen to the <em>Zen Forest Harmony</em> track inside the soundscape area.</li>
                    <li>Commit one tiny gratitude reflection entry inside the Thought Vault to reinforce balanced perspective.</li>
                </ul>
            `;
        } else {
            suggestionHtml = `
                <strong style="color:#34495e; display:block; margin-bottom:6px;">🤖 Jarvis Optimization Strategy: Homeostasis Sustenance</strong>
                Your baseline metrics show an ideal, steady emotional equilibrium in MySQL storage. Maintain this foundation by continuing your structural habits.
            `;
        }

        targetOutput.innerHTML = suggestionHtml;
    }

    async function commitJournalEntry() {
        const textEl = document.getElementById('journal-text');
        const moodEl = document.getElementById('journal-mood');
        const textValue = textEl.value.trim();
        
        if (!textValue) {
            alert("Please type a thought reflection first before committing a record.");
            return;
        }

        try {
            const response = await fetch('wellness_api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'save_log', logText: textValue, moodTag: moodEl.value })
            });
            
            const result = await response.json();
            if (result.status === 'success') {
                textEl.value = "";
                await loadWellnessDatabase();
            } else {
                alert("Error recording entry: " + (result.message || "Unknown server error"));
            }
        } catch (error) {
            console.error("Transmission failed:", error);
            alert("Unable to save log to database at this time. Please check your network connection.");
        }
    }

    function filterLogs(targetCategory) {
        selectedActiveFilter = targetCategory;
        document.querySelectorAll('.filter-pill').forEach(pill => pill.classList.remove('active'));
        
        const mapId = { 'All': 'pill-all', 'Happy': 'pill-happy', 'Okay': 'pill-okay', 'Sad': 'pill-sad', 'Stressed': 'pill-stressed' };
        document.getElementById(mapId[targetCategory]).classList.add('active');
        renderJournalHistoryLog();
    }

    function renderJournalHistoryLog() {
        const mount = document.getElementById('journal-logs-mount');
        mount.innerHTML = "";

        const filteredSet = currentLogs.filter(item => selectedActiveFilter === 'All' || item.moodTag === selectedActiveFilter);

        if (filteredSet.length === 0) {
            mount.innerHTML = `<p style="font-size:0.75rem; color:#57606f; text-align:center; margin-top:20px;">No notes matching selection in database.</p>`;
            return;
        }

        filteredSet.forEach(item => {
            const entryCard = document.createElement('div');
            entryCard.className = "journal-entry-item";
            
            let badgeBg, badgeText;
            if (item.moodTag === 'Happy') { badgeBg = '#fff6d8'; badgeText = '#211f3d'; }
            else if (item.moodTag === 'Stressed') { badgeBg = '#ffe3e3'; badgeText = '#d63031'; }
            else if (item.moodTag === 'Sad') { badgeBg = '#e3ecff'; badgeText = '#2c7be5'; }
            else { badgeBg = '#e9ecef'; badgeText = '#34495e'; }

            entryCard.innerHTML = `
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 4px;">
                    <span style="font-size:0.7rem; color:#747d8c; font-weight:600;">${item.timestamp}</span>
                    <span class="tag-indicator" style="background:${badgeBg}; color:${badgeText}; margin:0;">${item.moodTag}</span>
                </div>
                <p style="font-size:0.85rem; line-height:1.4; color:#211f3d;">${item.logText}</p>
            `;
            mount.appendChild(entryCard);
        });
    }
  </script>
</body>
</html>