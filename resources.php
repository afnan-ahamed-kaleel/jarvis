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
  <title>Resources – Jarvis</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    * { box-sizing: border-box; font-family: "SF Pro Display", "Segoe UI", Inter, sans-serif; margin: 0; padding: 0; }
    body { min-height: 100vh; background: radial-gradient(circle at center, #306f9f, #f0f7fc), linear-gradient(135deg, #063776, #eef3ff); color: #211f3d; display: flex; overflow-x: hidden; }
    
    .dashboard-layout { display: flex; width: 100vw; height: 100vh; overflow: hidden; }
    .main-content-area { flex: 1; padding: 30px; overflow-y: auto; height: 100vh; display: flex; flex-direction: column; gap: 20px; }
    
    .premium-dashboard-panel { 
      background: rgba(255, 255, 255, 0.25); backdrop-filter: blur(30px) saturate(170%); -webkit-backdrop-filter: blur(30px) saturate(170%);
      border-radius: 32px; padding: 24px; box-shadow: 0 40px 80px rgba(0, 0, 0, 0.08), inset 0 1px 0 rgba(255, 255, 255, 0.4); 
      height: calc(100vh - 60px); display: flex; flex-direction: column; gap: 20px; overflow: hidden;
    }
    
    /* Sticky Sidebar Layout Framework */
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

    .app-header { text-align: left; flex-shrink: 0; }
    .app-header h1 { font-size: 1.9rem; letter-spacing: -0.5px; }
    .app-header p { margin-top: 2px; font-size: 0.9rem; color: rgba(31, 45, 61, 0.65); }

    /* Ambience Strips Layout */
    .ambience-horizontal-strip {
      flex-shrink: 0; background: rgba(255, 255, 255, 0.35); border-radius: 20px; padding: 12px 18px;
      border: 1px solid rgba(255, 255, 255, 0.4); backdrop-filter: blur(10px);
      display: flex; align-items: center; justify-content: space-between; gap: 5px; box-shadow: 0 8px 24px rgba(0,0,0,0.01);
    }
    .audio-strip-meta { min-width: 160px; }
    .audio-tracks-flex-row { display: flex; gap: 12px; overflow-x: auto; flex: 1; padding: 4px 0; scrollbar-width: none; }
    .audio-tracks-flex-row::-webkit-scrollbar { display: none; }
    
    .audio-strip-card {
      flex: 0 0 240px; background: rgba(255, 255, 255, 0.6); border-radius: 12px; padding: 10px 14px;
      display: flex; justify-content: space-between; align-items: center; border: 1px solid rgba(255,255,255,0.4);
    }
    .audio-control-btn { padding: 6px 12px; border: none; background: #211f3d; color: #fff; font-weight: 600; border-radius: 8px; cursor: pointer; font-size: 0.75rem; transition: all 0.2s; }
    .audio-control-btn:hover { background: #2c7be5; }

    /* Boxed & Scrollable Matrix Container (Now without inner category boxes) */
    .practices-scroll-container {
      flex: 1; overflow-y: auto; padding-right: 4px; display: flex; flex-direction: column; gap: 24px;
      scrollbar-width: thin; scrollbar-color: rgba(0, 0, 0, 0.1) transparent;
    }
    .matrix-section-title { font-size: 1.05rem; font-weight: 600; margin-bottom: 10px; display: flex; align-items: center; gap: 8px; color: #063776; }
    .matrix-tiles-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 12px; }
    
    /* Premium Themed Tile Classes */
    .practice-tile-card {
      border: 1px solid rgba(255,255,255,0.4);
      border-radius: 16px; padding: 14px; cursor: pointer; transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
      display: flex; flex-direction: column; justify-content: space-between; gap: 10px; min-height: 110px;
    }
    .practice-tile-card:hover { transform: translateY(-3px); box-shadow: 0 12px 24px rgba(0, 0, 0, 0.08); filter: brightness(1.05); }
    
    /* Track Base Colors (Premium Theme Layering) */
    .tile-breathing { background: linear-gradient(135deg, rgba(230, 247, 242, 0.85), rgba(186, 233, 222, 0.85)); border-color: rgba(46, 204, 113, 0.25); }
    .tile-yoga { background: linear-gradient(135deg, rgba(243, 235, 254, 0.85), rgba(220, 201, 247, 0.85)); border-color: rgba(155, 89, 182, 0.25); }
    .tile-workouts { background: linear-gradient(135deg, rgba(254, 245, 231, 0.85), rgba(249, 221, 180, 0.85)); border-color: rgba(241, 196, 15, 0.25); }
    .tile-meditation { background: linear-gradient(135deg, rgba(253, 235, 235, 0.85), rgba(249, 195, 195, 0.85)); border-color: rgba(231, 76, 60, 0.25); }

    .tile-header { display: flex; justify-content: space-between; align-items: flex-start; gap: 4px; }
    .tile-title { font-size: 0.85rem; font-weight: 700; color: #211f3d; line-height: 1.3; }
    .tile-badge { font-size: 0.65rem; font-weight: bold; padding: 3px 8px; border-radius: 6px; white-space: nowrap; text-transform: uppercase; letter-spacing: 0.3px; }
    .tile-badge.timer-type { background: #2c7be5; color: white; }
    .tile-badge.steps-type { background: #211f3d; color: white; }
    .tile-desc { font-size: 0.75rem; color: #4b525f; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }

    /* High Fidelity Floating Console Dock Overlay */
    .floating-music-dock {
      position: fixed; bottom: -120px; left: 50%; transform: translateX(-50%);
      width: calc(100vw - 360px); max-width: 750px; background: rgba(21, 19, 41, 0.96); 
      backdrop-filter: blur(25px); border: 1px solid rgba(255, 255, 255, 0.12);
      border-radius: 20px; padding: 12px 24px; display: flex; align-items: center; justify-content: space-between;
      box-shadow: 0 20px 45px rgba(0,0,0,0.35); z-index: 998; transition: bottom 0.3s cubic-bezier(0.25, 1, 0.5, 1);
    }
    .floating-music-dock.visible { bottom: 24px; }
    .dock-track-details { min-width: 140px; }
    .dock-track-details h4 { color: #fff; font-size: 0.85rem; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 180px; }
    .dock-track-details span { color: rgba(255,255,255,0.5); font-size: 0.7rem; display: block; margin-top: 1px; }
    
    .dock-controls-cluster { display: flex; align-items: center; gap: 12px; }
    .dock-icon-btn { background: transparent; border: none; color: rgba(255,255,255,0.7); font-size: 1.1rem; cursor: pointer; padding: 6px; display: flex; align-items: center; justify-content: center; transition: color 0.2s, transform 0.1s; }
    .dock-icon-btn:hover { color: #fff; transform: scale(1.1); }
    .dock-icon-btn.play-master { background: #fff; color: #211f3d; width: 34px; height: 34px; border-radius: 50%; font-size: 0.9rem; }
    .dock-icon-btn.play-master:hover { background: #4da3ff; color: #fff; }
    .dock-icon-btn.stop-master { color: #ff7675; }
    .dock-icon-btn.stop-master:hover { color: #d63031; }
    
    /* Live Equalizer Animation Styles */
    .equalizer-wave { display: flex; align-items: flex-end; gap: 3px; height: 16px; width: 20px; }
    .equalizer-bar { width: 3px; height: 100%; background: #4da3ff; transform-origin: bottom; transform: scaleY(0.2); }
    .equalizer-wave.playing .equalizer-bar { animation: bounceVisualizer 0.6s ease infinite alternate; }
    .equalizer-wave.playing .equalizer-bar:nth-child(2) { animation-delay: 0.15s; animation-duration: 0.4s; }
    .equalizer-wave.playing .equalizer-bar:nth-child(3) { animation-delay: 0.3s; animation-duration: 0.5s; }
    @keyframes bounceVisualizer { 0% { transform: scaleY(0.2); } 100% { transform: scaleY(1); } }

    /* Interactive Lightbox Overlay Effect Modules */
    .activity-modal-overlay {
      position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(33, 31, 61, 0.4);
      backdrop-filter: blur(12px); display: none; align-items: center; justify-content: center; z-index: 1000; opacity: 0; transition: opacity 0.3s ease;
    }
    .activity-modal-overlay.active { display: flex; opacity: 1; }
    .activity-modal-window {
      background: white; width: 90%; max-width: 440px; border-radius: 24px; padding: 22px;
      box-shadow: 0 30px 60px rgba(0,0,0,0.15); border: 1px solid rgba(255,255,255,0.8);
      display: flex; flex-direction: column; gap: 16px; transform: scale(0.9); transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }
    .activity-modal-overlay.active .activity-modal-window { transform: scale(1); }
    .modal-header-row { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #f1f2f6; padding-bottom: 10px; }
    .modal-close-btn { background: #f1f2f6; border: none; width: 28px; height: 28px; border-radius: 50%; font-size: 0.85rem; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s; }
    .modal-close-btn:hover { background: #d63031; color: white; }

    /* Stopwatch Circle Loader Elements */
    .stopwatch-container { position: relative; width: 110px; height: 110px; margin: 10px auto; }
    .stopwatch-svg-circle { transition: stroke-dashoffset 0.1s linear; transform: rotate(-90deg); transform-origin: 50% 50%; }
    .stopwatch-timer-label { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 1.2rem; font-weight: 700; color: #211f3d; }
    .step-badge { background: #2c7be5; color: white; padding: 3px 8px; border-radius: 6px; font-size: 0.7rem; font-weight: bold; display: inline-block; }

    /* Floating Action Button */
    .emergency-fab { position: fixed; bottom: 24px; right: 24px; width: 54px; height: 54px; background: #d63031; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.4rem; text-decoration: none; box-shadow: 0 10px 25px rgba(214, 48, 49, 0.35); z-index: 999; transition: transform 0.2s; }
    .emergency-fab:hover { transform: scale(1.08) rotate(8deg); background: #ff7675; }

  </style>
</head>
<body>

  <div class="dashboard-layout">
    <?php include 'sidebar.php'; ?>

    <main class="main-content-area">
      <div class="premium-dashboard-panel">

        <header class="app-header">
          <h1>Resources Workspace</h1>
          <p>Helpful tools and interactive training sessions for your mental wellbeing 📚</p>
        </header>

        <!-- LIVE DATABASE PROGRESS TRACKER BANNER -->
        <div style="background: rgba(255,255,255,0.45); border-radius: 20px; padding: 16px 24px; border: 1px solid rgba(255,255,255,0.6); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; box-shadow: 0 8px 24px rgba(0,0,0,0.02);">
          <div style="display: flex; align-items: center; gap: 14px;">
            <span style="font-size: 2rem;">🏆</span>
            <div>
              <h3 style="font-size: 1.1rem; font-weight: 700; color: #063776;">Your Daily Practice Milestones</h3>
              <p style="font-size: 0.85rem; color: #57606f;">Real-time somatic activity metrics tracked directly inside your MySQL database vault</p>
            </div>
          </div>
          <div style="display: flex; gap: 24px;">
            <div style="background: rgba(255,255,255,0.7); padding: 10px 20px; border-radius: 16px; text-align: center; border: 1px solid rgba(255,255,255,0.5);">
              <span style="font-size: 0.75rem; color: #57606f; font-weight: 600; text-transform: uppercase;">Total Exercises</span>
              <strong id="tracker-completed-count" style="display: block; font-size: 1.4rem; color: #2c7be5; font-weight: 800;">0</strong>
            </div>
            <div style="background: rgba(255,255,255,0.7); padding: 10px 20px; border-radius: 16px; text-align: center; border: 1px solid rgba(255,255,255,0.5);">
              <span style="font-size: 0.75rem; color: #57606f; font-weight: 600; text-transform: uppercase;">Minutes Mapped</span>
              <strong id="tracker-total-minutes" style="display: block; font-size: 1.4rem; color: #2ecc71; font-weight: 800;">0m</strong>
            </div>
          </div>
        </div>

        <!-- COMPACT AUDIO SELECTION BAR Layout -->
        <section class="ambience-horizontal-strip">
          <div class="audio-strip-meta">
            <h2 style="font-size: 0.95rem; font-weight: 700; color: #211f3d;">Ambions Audios</h2>
            <!-- <p style="font-size: 0.75rem; color: #57606f;">Targeted background isolation loops</p> -->
          </div>
          
          <div class="audio-tracks-flex-row" id="audio-tracks-mount-row">
            <!-- JavaScript dynamically hooks into collections here -->
          </div>
        </section>

        <!-- STREAMLINED PRACTICES MATRIX (No Outer Boxes, Premium Colored Tiles) -->
        <section class="practices-scroll-container">
          <div style="margin-bottom: -4px;">
            <h2 style="font-size: 1.1rem; font-weight: 700; color: #211f3d;">Practices Collection</h2>
            <p style="font-size: 0.8rem; color: #57606f;">Somatic structures and interactive mental grounding drills</p>
          </div>

          <!-- Track 1: Breathing -->
          <div>
            <h3 class="matrix-section-title">🫁 Breathing Exercises</h3>
            <div class="matrix-tiles-grid" id="track-breathing"></div>
          </div>

          <!-- Track 2: Yoga -->
          <div>
            <h3 class="matrix-section-title">🧘 Yoga Tutorials</h3>
            <div class="matrix-tiles-grid" id="track-yoga"></div>
          </div>

          <!-- Track 3: Workouts -->
          <div>
            <h3 class="matrix-section-title">⚡ Basic Home Workouts</h3>
            <div class="matrix-tiles-grid" id="track-workouts"></div>
          </div>

          <!-- Track 4: Meditation -->
          <div>
            <h3 class="matrix-section-title">🧠 Meditation Practices</h3>
            <div class="matrix-tiles-grid" id="track-meditation"></div>
          </div>
        </section>

      </div>
    </main>
  </div>

  <!-- COMPREHENSIVE MEDIA CONTROLLER DOCK -->
  <div id="global-audio-dock" class="floating-music-dock">
    <div style="display: flex; align-items: center; gap: 14px; max-width: 40%;">
      <div id="dock-equalizer" class="equalizer-wave">
        <div class="equalizer-bar"></div>
        <div class="equalizer-bar"></div>
        <div class="equalizer-bar"></div>
      </div>
      <div class="dock-track-details">
        <h4 id="dock-track-title">No Audio Loaded</h4>
        <span>Ambient System Engine</span>
      </div>
    </div>

    <div class="dock-controls-cluster">
      <button class="dock-icon-btn" onclick="skipAudioTrack(-1)" title="Previous Track">⏮</button>
      <button class="dock-icon-btn play-master" id="dock-play-pause-toggle" onclick="togglePlaybackConsole()" title="Play / Pause">⏸</button>
      <button class="dock-icon-btn" onclick="skipAudioTrack(1)" title="Next Track">⏭</button>
      <button class="dock-icon-btn" id="dock-mute-unmute-toggle" onclick="toggleMuteConsole()" title="Mute Toggle" style="font-size:1rem; margin-left:6px;">🔊</button>
      <div style="width: 1px; height: 20px; background: rgba(255,255,255,0.2); margin: 0 4px;"></div>
      <button class="dock-icon-btn stop-master" onclick="killGlobalAudioFromConsole()" title="Stop Engine & Dismiss">⏹</button>
    </div>
  </div>

  <!-- LIGHTBOX MODAL PORTAL -->
  <div id="activity-lightbox-modal" class="activity-modal-overlay">
    <div class="activity-modal-window">
      <div class="modal-header-row">
        <h3 id="player-title" style="font-size: 1rem; font-weight: 700; color: #211f3d;">Activity Portal</h3>
        <button class="modal-close-btn" onclick="dismissActivityPortal()">✕</button>
      </div>
      <div id="player-media-canvas" style="text-align: center; min-height: 120px; display: flex; flex-direction: column; justify-content: center;"></div>
      <div id="player-controls" style="display: flex; justify-content: space-between; align-items: center; gap: 10px; border-top: 1px solid #f1f2f6; padding-top: 12px;"></div>
    </div>
  </div>

  <a href="emergency.php" class="emergency-fab" title="Immediate Care Help">🚨</a>

  <script>
    let celebrationTimeout;
    function launchCelebration(emoji, title, textMessage) {
      document.getElementById('celebration-emoji').textContent = emoji;
      document.getElementById('celebration-title').textContent = title;
      document.getElementById('celebration-msg').textContent = textMessage;
      
      const overlay = document.getElementById('celebration-overlay');
      overlay.classList.add('show');

      clearTimeout(celebrationTimeout);
      celebrationTimeout = setTimeout(() => {
        overlay.classList.remove('show');
      }, 3000);
    }

    // ===== AUDIO SOUNDSCAPES DATABASE SYSTEM =====
    const audioTrackDatabase = [
      { id: 'track1', name: 'White Noise Rain', desc: 'Deep isolation rain', url: 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-1.mp3' },
      { id: 'track2', name: 'Lo-Fi Focus Waves', desc: 'Perfect for active studying', url: 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-2.mp3' },
      { id: 'track3', name: 'Zen Forest Harmony', desc: 'Somatic grounding chords', url: 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-3.mp3' },
      { id: 'track4', name: 'Cosmic Brown Noise', desc: 'Deep alpha frequency brain wave', url: 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-4.mp3' },
      { id: 'track5', name: 'Ocean Shore Resonance', desc: 'Rhythmic tide restoration cycles', url: 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-5.mp3' },
      { id: 'track6', name: 'Tibetan Sing Bowls', desc: 'Pure meditative harmonic state', url: 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-6.mp3' }
    ];

    // ===== EXPANDED WELLNESS TRACK DATA SPECIFICATIONS =====
    const wellnessDatabase = {
      breathing: [
        { id: 'b1', name: 'Box Breathing Reset', type: 'timer', duration: 16, description: '4s Inhale, 4s Hold, 4s Exhale, 4s Hold. Tactical grounding drill.', cycleTime: 4 },
        { id: 'b2', name: '4-7-8 Deep Sleep', type: 'timer', duration: 19, description: '4s Inhale, 7s Calm Hold, 8s Complete Auditory Exhale.', cycleTime: 4 },
        { id: 'b3', name: 'Resonant Coherent Pacing', type: 'timer', duration: 30, description: '5s Inhale, 5s Exhale loop configuration to optimize heart rate variability.', cycleTime: 5 },
        { id: 'b4', name: 'Energizing Fire Breath', type: 'timer', duration: 12, description: 'Rapid, forceful abdominal exhalations paired with matching active breaths.', cycleTime: 2 }
      ],
      yoga: [
        { id: 'y1', name: 'Child\'s Pose (Balasana)', type: 'steps', description: 'Restorative stretch calming the central nervous system.', steps: ['Kneel on the floor with toes touching, knees separated wide.', 'Fold your torso forward completely between your thighs.', 'Extend arms straight out ahead, palms down, resting forehead on mat.', 'Hold position for 5-10 deep abdominal breath intervals.'] },
        { id: 'y2', name: 'Cat-Cow Spinal Flow', type: 'steps', description: 'Improves dynamic posture and links respiration to movement.', steps: ['Begin on all fours, hands under shoulders, knees under hips.', 'Inhale: Drop belly down, lift chest and chin high (Cow Pose).', 'Exhale: Arch spine upward like a cat, pulling chin into chest.', 'Repeat fluidly alternating shapes for 8 total cycles.'] },
        { id: 'y3', name: 'Downward Dog Extension', type: 'steps', description: 'Inversion sequence that elongates spine and opens lower body chains.', steps: ['Start on hands and knees, tucking toes under securely.', 'Lift knees away from floor, pushing hips high back toward ceiling.', 'Press hands firmly into mat, creating an inverted V configuration.', 'Pedal out heels slowly to ease tension in hamstrings and calves.'] },
        { id: 'y4', name: 'Warrior II Empowerment', type: 'steps', description: 'Standing kinetic structure to fortify balance and skeletal stability.', steps: ['Step feet wide apart, tracking right toes completely outward.', 'Extend arms wide parallel to floor, palms looking down.', 'Bend right knee deep keeping it directly stacked over ankle alignment.', 'Gaze over right fingertips with slow deliberate breathing rhythms.'] }
      ],
      workouts: [
        { id: 'w1', name: 'Somatic Jumping Jacks', type: 'steps', description: 'Quick physical burst to metabolize excess stress cortisol.', 
        steps: ['Stand tall with arms at sides, feet closely grouped.', 'Jump feet wide while bringing hands completely together overhead.', 'Immediately jump back to starting configuration cleanly.', 'Perform 3 sets of 15 reps to elevate baseline cardiovascular blood flow.'] },
        { id: 'w2', name: 'Bodyweight Deep Squats', type: 'steps', description: 'Grounds emotional energy into lower body kinetic muscle groups.', 
        steps: ['Place feet shoulder-width apart, toes tracking slightly outward.', 'Send hips backward, bending knees as if dropping onto a low stool.', 'Keep chest elevated and heels firmly planted on the floor surface.', 'Drive upward through heels to return to tall standing position.'] },
        { id: 'w3', name: 'Plank Core Stabilization', type: 'steps', description: 'Isometric tension exercise to stabilize abdominal walls and back line.', 
        steps: ['Lower forearms to floor, tracking elbows straight under shoulder sockets.', 'Extend legs straight out behind, balancing body weight on toes.', 'Engage core, glutes, and thighs to form a flat line structure.', 'Maintain positions avoiding spine sagging for 30 seconds.'] },
        { id: 'w4', name: 'Glute Bridge Activation', type: 'steps', description: 'Releases lumbar compression locked during prolonged sitting periods.', 
        steps: ['Lie flat on back, bending knees with feet planted near hips.', 'Press arms flat into floor alongside your torso framework.', 'Drive through heels lifting pelvis upward squeezing muscle groups.', 'Lower smoothly down avoiding abrupt drops onto the surface.'] }
      ],
      meditation: [
        { id: 'm1', name: '5-4-3-2-1 Sensory Grounding', type: 'steps', description: 'Halts panic loops by anchoring awareness to physical space.', 
        steps: ['Acknowledge 5 clear structural things you can SEE around you.', 'Acknowledge 4 discrete physical things you can TOUCH right now.', 'Acknowledge 3 external elements or sounds you can HEAR.', 'Acknowledge 2 individual scents you can SMELL.', 'Acknowledge 1 positive quality about yourself you can think or TASTE.'] },
        { id: 'm2', name: 'Loving-Kindness Meditation', type: 'steps', description: 'Fosters profound self-compassion and emotional regulation.', 
        steps: ['Close eyes, relax shoulders, and sit comfortably upright.', 'Silently repeat: May I be safe. May I be healthy. May I live with ease.', 'Visualize a loved one and project the exact same script phrase to them.', 'Expand energy footprint outward to encompass all living creatures globally.'] },
        { id: 'm3', name: 'Body Scan Deep Release', type: 'steps', description: 'Progressive somatic analysis to isolate and drop physical tension.', 
        steps: ['Close eyes, lying down or seated in relaxed open posture.', 'Direct focused attention down into toes noticing sensory impulses.', 'Slowly transition attention upward through ankles, calves, and knees.', 'Intentionally release muscle contraction as awareness climbs to the jaw line.'] },
        { id: 'm4', name: 'Thought Stream Detachment', type: 'steps', description: 'Observational exercise framing internal thoughts as neutral events.', 
        steps: ['Imagine sitting at the edge of a slow moving stream bank.', 'Visualize thoughts appearing inside consciousness as passing leaves.', 'Place each unique internal narrative onto a leaf item cleanly.', 'Watch them float downstream out of immediate frame without chasing them.'] }
      ]
    };

    // ===== CORE RUNTIME ENGINE VARIABLES =====
    let activeAudioInstance = null;
    let currentLoadedTrackIndex = null;
    let isConsoleMuted = false;
    
    let stopwatchIntervalEngine = null;
    let currentStepActiveIndex = 0;
    let currentlyLoadedDataTrack = null;

    window.addEventListener('DOMContentLoaded', () => {
        renderAmbientAudioStrip();
        initializeMatrixTiles();
        loadActivityMetrics();
    });

    async function loadActivityMetrics() {
        try {
            const res = await fetch('wellness_api.php?action=get_metrics');
            const data = await res.json();
            if (data.status === 'success') {
                document.getElementById('tracker-completed-count').textContent = data.total_completed;
                document.getElementById('tracker-total-minutes').textContent = data.total_minutes + "m";
            }
        } catch (e) {
            console.warn("Could not fetch metrics:", e);
        }
    }

    async function logActivityToDatabase(item) {
        if (!item) return;
        let durationSecs = item.duration || (item.steps ? item.steps.length * 45 : 60);
        try {
            await fetch('wellness_api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'log_activity',
                    type: item.type === 'timer' ? 'Breathing/Timer' : 'Somatic/Steps',
                    name: item.name,
                    duration: durationSecs
                })
            });
            await loadActivityMetrics();
        } catch (e) {
            console.warn("Could not log activity to server:", e);
        }
    }

    // ===== INTERACTIVE GENERATOR ENGINE =====
    function renderAmbientAudioStrip() {
      const mountRow = document.getElementById('audio-tracks-mount-row');
      mountRow.innerHTML = "";
      
      audioTrackDatabase.forEach((track, index) => {
        const card = document.createElement('div');
        card.className = "audio-strip-card";
        card.innerHTML = `
          <div style="max-width: 65%;">
            <strong style="font-size: 0.8rem; display: block; color: #211f3d; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${track.name}</strong>
            <span style="font-size: 0.7rem; color: #57606f; display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${track.desc}</span>
          </div>
          <button class="audio-control-btn" id="btn-${track.id}" onclick="triggerTrackActivation(${index})">Listen</button>
        `;
        mountRow.appendChild(card);
      });
    }

    function initializeMatrixTiles() {
      Object.keys(wellnessDatabase).forEach(categoryKey => {
        const targetContainer = document.getElementById(`track-${categoryKey}`);
        if (!targetContainer) return;
        
        targetContainer.innerHTML = "";
        wellnessDatabase[categoryKey].forEach(item => {
          const tile = document.createElement('div');
          // Dynamically attach the thematic premium layout colors
          tile.className = `practice-tile-card tile-${categoryKey}`;
          tile.onclick = () => launchActivityPortal(item);
          
          const badgeLabel = item.type === 'timer' ? '⏱️ Run' : '📖 View';
          const badgeClass = item.type === 'timer' ? 'timer-type' : 'steps-type';
          
          tile.innerHTML = `
            <div class="tile-header">
              <span class="tile-title">${item.name}</span>
              <span class="tile-badge ${badgeClass}">${badgeLabel}</span>
            </div>
            <p class="tile-desc">${item.description}</p>
          `;
          targetContainer.appendChild(tile);
        });
      });
    }

    // ===== HIGH ARCHITECTURE CONSOLE AUDIO MASTER HANDLERS =====
    function triggerTrackActivation(index) {
      if (index < 0 || index >= audioTrackDatabase.length) return;
      
      const track = audioTrackDatabase[index];
      const dock = document.getElementById('global-audio-dock');
      const dockTitle = document.getElementById('dock-track-title');
      const eq = document.getElementById('dock-equalizer');
      const playPauseBtn = document.getElementById('dock-play-pause-toggle');

      resetGridActionButtons();

      if (activeAudioInstance && currentLoadedTrackIndex === index) {
        togglePlaybackConsole();
        return;
      }

      if (activeAudioInstance) {
        activeAudioInstance.pause();
      }

      activeAudioInstance = new Audio(track.url);
      activeAudioInstance.loop = true;
      activeAudioInstance.muted = isConsoleMuted;
      activeAudioInstance.play();
      
      currentLoadedTrackIndex = index;
      
      document.getElementById(`btn-${track.id}`).textContent = "Active";
      document.getElementById(`btn-${track.id}`).style.background = "#2c7be5";
      
      dockTitle.textContent = track.name;
      playPauseBtn.textContent = "⏸";
      eq.classList.add('playing');
      dock.classList.add('visible');
    }

    function togglePlaybackConsole() {
      if (!activeAudioInstance) return;
      
      const eq = document.getElementById('dock-equalizer');
      const playPauseBtn = document.getElementById('dock-play-pause-toggle');
      const currentTrack = audioTrackDatabase[currentLoadedTrackIndex];
      const gridBtn = document.getElementById(`btn-${currentTrack.id}`);

      if (activeAudioInstance.paused) {
        activeAudioInstance.play();
        playPauseBtn.textContent = "⏸";
        eq.classList.add('playing');
        if (gridBtn) gridBtn.textContent = "Active";
      } else {
        activeAudioInstance.pause();
        playPauseBtn.textContent = "▶";
        eq.classList.remove('playing');
        if (gridBtn) gridBtn.textContent = "Paused";
      }
    }

    function skipAudioTrack(direction) {
      if (currentLoadedTrackIndex === null) return;
      let targetIndex = currentLoadedTrackIndex + direction;
      
      if (targetIndex < 0) targetIndex = audioTrackDatabase.length - 1;
      if (targetIndex >= audioTrackDatabase.length) targetIndex = 0;
      
      triggerTrackActivation(targetIndex);
    }

    function toggleMuteConsole() {
      if (!activeAudioInstance) return;
      const muteBtn = document.getElementById('dock-mute-unmute-toggle');
      
      isConsoleMuted = !isConsoleMuted;
      activeAudioInstance.muted = isConsoleMuted;
      muteBtn.textContent = isConsoleMuted ? "🔇" : "🔊";
    }

    function killGlobalAudioFromConsole() {
      const dock = document.getElementById('global-audio-dock');
      const eq = document.getElementById('dock-equalizer');
      
      if (activeAudioInstance) {
        activeAudioInstance.pause();
        activeAudioInstance = null;
      }
      
      resetGridActionButtons();
      currentLoadedTrackIndex = null;
      eq.classList.remove('playing');
      dock.classList.remove('visible');
    }

    function resetGridActionButtons() {
      audioTrackDatabase.forEach(track => {
        const btn = document.getElementById(`btn-${track.id}`);
        if (btn) {
          btn.textContent = "Listen";
          btn.style.background = "#211f3d";
        }
      });
    }

    // ===== PRACTICE LIGHTBOX INTERACTIVITY CONTROL INTERFACES =====
    function launchActivityPortal(item) {
      clearInterval(stopwatchIntervalEngine);
      currentlyLoadedDataTrack = item;

      const overlay = document.getElementById('activity-lightbox-modal');
      document.getElementById('player-title').textContent = item.name;
      
      const canvas = document.getElementById('player-media-canvas');
      const controls = document.getElementById('player-controls');
      canvas.innerHTML = "";
      controls.innerHTML = "";

      overlay.classList.add('active');

      if (item.type === 'timer') {
        canvas.innerHTML = `
          <div class="stopwatch-container">
            <svg width="110" height="110">
              <circle stroke="rgba(44, 123, 229, 0.1)" stroke-width="5" fill="transparent" r="46" cx="55" cy="55"/>
              <circle id="player-stopwatch-circle" class="stopwatch-svg-circle" stroke="#2c7be5" stroke-width="5" stroke-linecap="round" fill="transparent" r="46" cx="55" cy="55" stroke-dasharray="289.03" stroke-dashoffset="0"/>
            </svg>
            <div id="stopwatch-countdown-label" class="stopwatch-timer-label">${item.duration}s</div>
          </div>
          <p style="font-size:0.8rem; color:#57606f; line-height:1.4; max-width:300px; margin:8px auto 0 auto;">${item.description}</p>
        `;
        
        controls.innerHTML = `
          <div style="font-size:0.8rem; color:#211f3d; font-weight:600;" id="timer-phase-caption">Ready to sync rhythm</div>
          <button onclick="executeStopwatchRuntime()" style="padding:6px 14px; background:#2c7be5; color:white; border:none; border-radius:8px; font-size:0.8rem; font-weight:bold; cursor:pointer;">Start Track</button>
        `;
      } else {
        currentStepActiveIndex = 0;
        canvas.innerHTML = `
          <p style="font-size:0.8rem; font-style:italic; color:#57606f; margin-bottom:10px; text-align:left;">${item.description}</p>
          <div style="background:rgba(240,247,2fc,0.6); padding:14px; border-radius:12px; border:1px solid rgba(44,123,229,0.15); min-height:85px; text-align:left; display:flex; flex-direction:column; gap:6px;">
            <div><span class="step-badge" id="step-number-pill">Step 1</span></div>
            <p id="step-text-display" style="font-size:0.85rem; line-height:1.4; color:#211f3d; font-weight:500;"></p>
          </div>
        `;
        
        controls.innerHTML = `
          <button onclick="navigateStepInstruction(-1)" style="padding:5px 12px; background:#211f3d; color:white; border:none; border-radius:6px; font-size:0.75rem; cursor:pointer;">Back</button>
          <span style="font-size:0.8rem; font-weight:bold; color:#57606f;" id="step-counter-fraction">1 / 4</span>
          <button onclick="navigateStepInstruction(1)" style="padding:5px 12px; background:#2c7be5; color:white; border:none; border-radius:6px; font-size:0.75rem; cursor:pointer;">Next</button>
        `;
        updateStepInterfaceDisplay();
      }
    }

    function dismissActivityPortal() {
      clearInterval(stopwatchIntervalEngine);
      document.getElementById('activity-lightbox-modal').classList.remove('active');
    }

    function updateStepInterfaceDisplay() {
      const stepsList = currentlyLoadedDataTrack.steps;
      document.getElementById('step-number-pill').textContent = `Step ${currentStepActiveIndex + 1}`;
      document.getElementById('step-text-display').textContent = stepsList[currentStepActiveIndex];
      document.getElementById('step-counter-fraction').textContent = `${currentStepActiveIndex + 1} / ${stepsList.length}`;
    }

    function navigateStepInstruction(direction) {
      const stepsList = currentlyLoadedDataTrack.steps;
      currentStepActiveIndex += direction;
      
      if (currentStepActiveIndex < 0) {
        currentStepActiveIndex = 0;
      } else if (currentStepActiveIndex >= stepsList.length) {
        launchCelebration("🧘‍♂️", "Routine Complete!", "Awesome job grounding yourself. You are doing fantastic!");
        logActivityToDatabase(currentlyLoadedDataTrack);
        dismissActivityPortal();
        return;
      }
      updateStepInterfaceDisplay();
    }

    function executeStopwatchRuntime() {
      clearInterval(stopwatchIntervalEngine);
      
      const circle = document.getElementById('player-stopwatch-circle');
      const label = document.getElementById('stopwatch-countdown-label');
      const caption = document.getElementById('timer-phase-caption');
      
      const circumference = 2 * Math.PI * 46; 
      let totalTimeRemaining = currentlyLoadedDataTrack.duration;
      let cycleDurationCounter = 0;
      let cadenceFlipState = true; 

      circle.style.strokeDasharray = circumference;
      
      stopwatchIntervalEngine = setInterval(() => {
        totalTimeRemaining--;
        cycleDurationCounter++;

        if (totalTimeRemaining <= 0) {
          clearInterval(stopwatchIntervalEngine);
          circle.style.strokeDashoffset = circumference;
          label.textContent = "0s";
          caption.textContent = "Done! Great Focus.";
          setTimeout(() => { 
            dismissActivityPortal(); 
            logActivityToDatabase(currentlyLoadedDataTrack);
            launchCelebration("🫁", "Exercise Complete!", "Excellent focus! Your breathing rhythm is aligned.");
          }, 1200);
          return;
        }

        label.textContent = `${totalTimeRemaining}s`;
        
        if (cycleDurationCounter >= currentlyLoadedDataTrack.cycleTime) {
          cadenceFlipState = !cadenceFlipState;
          cycleDurationCounter = 0;
        }
        caption.textContent = cadenceFlipState ? "✨ Breathe Inward..." : "💨 Release Outward...";

        const progressFraction = totalTimeRemaining / currentlyLoadedDataTrack.duration;
        circle.style.strokeDashoffset = circumference - (progressFraction * circumference);
      }, 1000);
    }
  </script>
  <div id="celebration-overlay" class="celebration-overlay">
    <div class="celebration-modal">
      <span class="celebration-badge" id="celebration-emoji">🎉</span>
      <h2 id="celebration-title" style="font-size: 1.8rem; margin-bottom: 10px; color: #063776;">Magnificent Achievement!</h2>
      <p id="celebration-msg" style="color: #57606f; font-size: 1rem; line-height: 1.6;"></p>
    </div>
  </div>

</body>
</html>