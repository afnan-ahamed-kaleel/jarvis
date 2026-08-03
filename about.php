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
    <title>About Us - Jarvis Workspace</title>
    <style>
        * { box-sizing: border-box; font-family: "SF Pro Display", "Segoe UI", Inter, sans-serif; margin: 0; padding: 0; }
        
        body { 
            height: 100vh; 
            background: radial-gradient(circle at center, #306f9f, #f0f7fc), linear-gradient(135deg, #063776, #eef3ff); 
            color: var(--sys-text-primary, #211f3d); 
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
            background: var(--sys-panel-bg, rgba(255, 255, 255, 0.25)); 
            backdrop-filter: blur(30px) saturate(170%); 
            border-radius: 32px; 
            padding: 40px; 
            box-shadow: 0 40px 80px rgba(0, 0, 0, 0.08), inset 0 1px 0 rgba(255, 255, 255, 0.4); 
            height: calc(100vh - 80px); 
            display: flex; 
            flex-direction: column; 
            border: 1px solid var(--sys-border-alpha, rgba(255, 255, 255, 0.4));
        }
        
        /* Expandable Sidebar Core Engine Placeholders */
        .jarvis-sidebar { 
            width: 80px; 
            height: 100vh;
            background: rgba(255, 255, 255, 0.3); 
            backdrop-filter: blur(25px); 
            border-right: 1px solid rgba(255, 255, 255, 0.4); 
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
        .sidebar-menu li a, .logout-btn-nav { display: flex; align-items: center; gap: 20px; padding: 14px; border-radius: 16px; text-decoration: none; color: #34495e; font-size: 0.95rem; font-weight: 500; transition: all 0.2s ease; white-space: nowrap; }
        .sidebar-menu li a .icon, .logout-btn-nav .icon { font-size: 1.4rem; display: inline-block; text-align: center; width: 30px; flex-shrink: 0; }
        .menu-text { opacity: 0; transition: opacity 0.2s ease; }
        .jarvis-sidebar:hover .menu-text { opacity: 1; }
        .sidebar-menu li a:hover { background: rgba(255, 255, 255, 0.5); transform: translateX(4px); }
        .sidebar-menu li a.active { background: linear-gradient(135deg, #4da3ff, #2c7be5); color: white; box-shadow: 0 8px 20px rgba(44, 123, 229, 0.3); }
        
        .sidebar-footer { margin-top: auto; }
        .logout-btn-nav { color: #d63031; background: rgba(214, 48, 49, 0.08); display: flex; }
        .logout-btn-nav:hover { background: #d63031 !important; color: white !important; }

        .app-header { text-align: left; margin-bottom: 24px; flex-shrink: 0; }
        .app-header h1 { font-size: 2.3rem; color: var(--sys-text-primary, #211f3d); }
        .app-header p { color: var(--sys-text-secondary, #4a5568); font-size: 1.05rem; margin-top: 4px; }

        /* Premium Segment View Control Switcher */
        .view-switcher-capsule {
            display: inline-flex;
            background: rgba(0, 0, 0, 0.06);
            padding: 6px;
            border-radius: 18px;
            margin-bottom: 30px;
            position: relative;
            align-self: flex-start;
            border: 1px solid rgba(255, 255, 255, 0.15);
            flex-shrink: 0;
        }
        [data-theme="dark"] .view-switcher-capsule {
            background: rgba(255, 255, 255, 0.04);
        }
        .switcher-btn {
            padding: 10px 24px;
            border: none;
            background: transparent;
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--sys-text-secondary, #4a5568);
            cursor: pointer;
            border-radius: 14px;
            z-index: 2;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: color 0.3s ease;
        }
        .switcher-btn.active {
            color: #ffffff;
            background: linear-gradient(135deg, #4da3ff, #2c7be5);
            box-shadow: 0 4px 12px rgba(44, 123, 229, 0.25);
        }

        /* Scrolled Panel Content Container */
        .panel-content-scroll {
            flex: 1;
            overflow-y: auto;
            padding-right: 8px;
            position: relative;
        }
        .panel-content-scroll::-webkit-scrollbar { width: 6px; }
        .panel-content-scroll::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.1); border-radius: 10px; }

        /* Smooth View Deck Content Transitions */
        .about-view-deck {
            display: none;
            animation: fadeInSlide 0.4s cubic-bezier(0.25, 1, 0.5, 1) forwards;
            flex-direction: column;
            gap: 24px;
        }
        .about-view-deck.active {
            display: flex;
        }

        @keyframes fadeInSlide {
            from { opacity: 0; transform: translateY(12px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Card Elements */
        .info-card { 
            background: var(--sys-card-bg, rgba(255, 255, 255, 0.45)); 
            border: 1px solid var(--sys-border-alpha, rgba(255, 255, 255, 0.3)); 
            border-radius: 24px; 
            padding: 30px; 
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.01); 
        }
        .info-card h2 { font-size: 1.4rem; margin-bottom: 16px; color: #2c7be5; display: flex; align-items: center; gap: 10px; }
        .info-card p { font-size: 0.98rem; line-height: 1.6; color: var(--sys-text-primary, #34495e); margin-bottom: 14px; }
        .info-card p:last-child { margin-bottom: 0; }
        
        /* Grid variants */
        .about-grid { display: grid; grid-template-columns: 1.2fr 1fr; gap: 24px; }
        .logo-philosophy-grid { display: grid; grid-template-columns: 180px 1fr; gap: 24px; align-items: center; }
        
        .goals-list { list-style: none; display: flex; flex-direction: column; gap: 14px; }
        .goals-list li { display: flex; align-items: start; gap: 12px; font-size: 0.95rem; color: var(--sys-text-primary, #211f3d); line-height: 1.4; }
        .goals-list li::before { content: "✦"; color: #2c7be5; font-weight: bold; }

        /* System Architecture Cards */
        .arch-features { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-top: 10px; }
        .feature-node { background: rgba(0, 0, 0, 0.02); border: 1px solid rgba(255,255,255,0.2); padding: 18px; border-radius: 16px; text-align: center; }
        [data-theme="dark"] .feature-node { background: rgba(255, 255, 255, 0.02); }
        .feature-node .f-icon { font-size: 1.6rem; margin-bottom: 8px; display: block; }
        .feature-node h4 { font-size: 0.95rem; font-weight: 600; margin-bottom: 4px; }
        .feature-node p { font-size: 0.82rem; color: var(--sys-text-secondary, #718096); line-height: 1.3; margin: 0; }

        /* Developer/Founder Branding Context */
        .developer-section { display: flex; align-items: center; gap: 34px; }
        .avatar-view-container { width: 140px; height: 140px; border-radius: 50%; border: 4px solid #ffffff; box-shadow: 0 10px 25px rgba(0,0,0,0.05); overflow: hidden; background: #eef3ff; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .avatar-view-container img { width: 100%; height: 100%; object-fit: cover; }
        
        .dev-meta { display: flex; flex-direction: column; flex: 1; }
        .dev-meta h3 { font-size: 1.6rem; color: var(--sys-text-primary, #211f3d); margin-bottom: 2px; }
        .dev-meta .subtitle { font-size: 0.95rem; color: #2c7be5; font-weight: 600; text-transform: uppercase; letter-spacing: 0.6px; margin-bottom: 6px; }
        .dev-meta .institution { font-size: 0.9rem; color: var(--sys-text-secondary, #57606f); font-weight: 500; display: flex; align-items: center; gap: 6px; margin-bottom: 10px; }

        /* System Logo Identity Asset styling */
        .embedded-showcase-logo { width: 100%; height: auto; border-radius: 20px; box-shadow: 0 12px 35px rgba(0,0,0,0.1); border: 3px solid #fff; transition: transform 0.4s ease; }
        .embedded-showcase-logo:hover { transform: scale(1.04) rotate(1deg); }

        /* Modern UI Selection Tag Framework Components */
        .pills-container { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 14px; }
        .pill-tag { background: rgba(44, 123, 229, 0.08); color: #2c7be5; padding: 6px 14px; border-radius: 100px; font-size: 0.82rem; font-weight: 600; border: 1px solid rgba(44, 123, 229, 0.15); transition: all 0.2s; }
        .pill-tag:hover { background: #2c7be5; color: white; transform: translateY(-1px); }

        /* Inline Minimal Brand Social Matrix Dock */
        .dev-social-dock { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 12px; align-items: center; }
        .dock-link { 
            display: inline-flex; 
            align-items: center; 
            justify-content: center; 
            width: 32px; 
            height: 32px; 
            border-radius: 50%; 
            background: rgba(255, 255, 255, 0.5); 
            border: 1px solid rgba(0, 0, 0, 0.05);
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); 
            color: #4a5568;
        }
        [data-theme="dark"] .dock-link { background: rgba(255, 255, 255, 0.08); border-color: rgba(255, 255, 255, 0.05); }
        .dock-link svg { width: 16px; height: 16px; fill: currentColor; transition: transform 0.3s ease; }
        
        /* Interactive Animation & Exact Brand Matrix Mapping */
        .dock-link:hover { transform: translateY(-3px) scale(1.1); background: #ffffff; box-shadow: 0 6px 15px rgba(0,0,0,0.1); }
        .dock-link:hover svg { transform: scale(1.05); }
        
        .dock-link.brand-whatsapp:hover { color: #25D366; border-color: rgba(37,211,102,0.3); }
        .dock-link.brand-linkedin:hover { color: #0077B5; border-color: rgba(0,119,181,0.3); }
        .dock-link.brand-github:hover { color: #24292e; border-color: rgba(36,41,46,0.3); }
        [data-theme="dark"] .dock-link.brand-github:hover { color: #f0f6fc; }
        .dock-link.brand-mail:hover { color: #EA4335; border-color: rgba(234,67,53,0.3); }
        .dock-link.brand-telegram:hover { color: #0088cc; border-color: rgba(0,136,204,0.3); }
        .dock-link.brand-fb:hover { color: #1877F2; border-color: rgba(24,119,242,0.3); }
        .dock-link.brand-insta:hover { color: #E1306C; border-color: rgba(225,48,108,0.3); }
        .dock-link.brand-twitter:hover { color: #1DA1F2; border-color: rgba(29,161,242,0.3); }

        /* Responsive Viewports */
        @media (max-width: 1024px) { .about-grid, .arch-features { grid-template-columns: 1fr; } }
        @media (max-width: 768px) {
            body { height: auto; overflow: auto; flex-direction: column; }
            .dashboard-layout { height: auto; overflow: visible; flex-direction: column-reverse; }
            .main-content-area { height: auto; padding: 16px; padding-bottom: 90px; }
            .premium-dashboard-panel { padding: 20px; border-radius: 24px; height: auto; min-height: calc(100vh - 110px); }
            .app-header h1 { font-size: 1.7rem; }
    </style>
</head>
<body>

  <div class="dashboard-layout">
    <?php include 'sidebar.php'; ?>

    <main class="main-content-area">
        <div class="premium-dashboard-panel">
            <div class="app-header">
                <h1>About Jarvis</h1>
                <p>System mission profiles, architectural philosophy, and leadership benchmarks</p>
            </div>

            <div class="view-switcher-capsule">
                <button class="switcher-btn active" id="btn-system" onclick="toggleActiveView('system')">🤖 The System Platform</button>
                <button class="switcher-btn" id="btn-developer" onclick="toggleActiveView('developer')">👨‍💻 The Creator Portfolio</button>
            </div>

            <div class="panel-content-scroll">
                
                <div class="about-view-deck active" id="view-system">
                    <div class="info-card">
                        <div class="logo-philosophy-grid">
                            <div>
                                <img src="pic&animations/JARVIS-LOGOfull.jpg" alt="Jarvis Unified Branding Asset" class="embedded-showcase-logo">
                            </div>
                            <div>
                                <h2>Symbolic Identity & Vision</h2>
                                <p>The Jarvis system icon represents the ultimate alignment of systemic structure and human potentiality. The geometric core reflects clean, scalable software architecture, while the lighting elements signify clear thinking, adaptive processing capabilities, and advanced intelligent routing mechanics.</p>
                                <p>The founder and designer chose this configuration to emphasize that automation should feel responsive and reassuring, maintaining a premium interface balanced with multi-layered secure data segmentation standards.</p>
                            </div>
                        </div>
                    </div>

                    <div class="about-grid">
                        <div class="info-card">
                            <h2>🛡️ The System Motive</h2>
                            <p>Jarvis is a secure full-stack conversational ecosystem designed to balance highly responsive user interfaces with data protection controls. Built tailored for mental health assistant coordination configurations, it handles complex operational prompts while protecting data privacy.</p>
                            <p>By splitting processing duties into separate presentation, logic, and database access areas, Jarvis keeps application boundaries protected. The framework completely handles user query variables, blocking cross-site input vectors to establish an enterprise web platform design.</p>
                        </div>

                        <div class="info-card">
                            <h2>🎯 Core Strategic Targets</h2>
                            <ul class="goals-list">
                                <li><strong>Tiered Isolation:</strong> Complete 3-tier architectural decoupling separating user UI cards from database processing frameworks.</li>
                                <li><strong>Input Sanitization:</strong> Strict middleware parameters to check and clean all background data logs.</li>
                                <li><strong>Fluid Responsiveness:</strong> Hardware-accelerated transitions providing sub-millisecond view adjustments across desktop and mobile.</li>
                            </ul>
                        </div>
                    </div>

                    <div class="info-card">
                        <h2>⚙️ Core Architectural Specifications</h2>
                        <div class="arch-features">
                            <div class="feature-node">
                                <span class="f-icon">💻</span>
                                <h4>Presentation Layer</h4>
                                <p>Responsive CSS viewports adjusting automatically across multiple device widths.</p>
                            </div>
                            <div class="feature-node">
                                <span class="f-icon">🔒</span>
                                <h4>Logic Controller Tier</h4>
                                <p>Structured PHP request parsing designed to filter data strings before execution paths.</p>
                            </div>
                            <div class="feature-node">
                                <span class="f-icon">🗄️</span>
                                <h4>Persistence Layer</h4>
                                <p>Relational database setup utilizing relational link parameters to protect chat logs.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="about-view-deck" id="view-developer">
                    <div class="info-card developer-section">
                        <div class="avatar-view-container">
                            <img id="avatarDisplay" src="pic&animations/aboutUsPic.PNG" alt="Afnan Ahamed Profile View">
                        </div>
                        <div class="dev-meta">
                            <h3>Afnan Ahamed, BSc (Eng)</h3>
                            <p class="subtitle">Founder & Lead Systems Architect</p>
                            <p class="institution">
                                <span>🏛️</span> Department of Computing — London Metropolitan University
                            </p>
                            <p style="font-size: 0.95rem; color: var(--sys-text-secondary, #4a5568); line-height: 1.5;">
                                Acting as both the conceptual Founder and Lead Systems Developer, engineering clean database structures, robust middleware logic layers, intelligent automated routing elements, and responsive user interfaces.
                            </p>
                            
                            <div class="dev-social-dock">
                                <a href="https://wa.me/+94750179991" target="_blank" class="dock-link brand-whatsapp" title="WhatsApp Channel">
                                    <svg viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946C.06 5.348 5.397.01 12.008.01c3.202.001 6.212 1.246 8.477 3.514 2.266 2.268 3.507 5.28 3.505 8.484-.004 6.657-5.34 11.997-11.953 11.997-2.005-.001-3.973-.502-5.713-1.457L0 24zm6.59-4.846c1.66.986 3.296 1.489 4.961 1.491 5.482 0 9.94-4.446 9.943-9.914.001-2.65-1.019-5.143-2.871-7.003C16.828 1.87 14.354.84 11.724.84c-5.486 0-9.946 4.451-9.948 9.922-.001 2.01.536 3.971 1.556 5.707l-.991 3.621 3.71-.976zm12.181-7.055c-.33-.165-1.951-.963-2.251-1.072-.3-.11-.518-.165-.736.165-.218.33-.846 1.072-1.037 1.292-.19.219-.381.247-.712.082-1.644-.817-2.779-1.381-3.886-3.274-.292-.5-.292-.862-.122-1.127.153-.238.33-.385.496-.578.165-.192.22-.33.33-.55.11-.22.055-.413-.028-.578-.082-.165-.736-1.775-1.009-2.433-.266-.64-.539-.554-.736-.554-.19 0-.409-.011-.627-.011-.218 0-.573.082-.873.413-.3.33-1.145 1.118-1.145 2.724 0 1.605 1.172 3.159 1.334 3.379.163.22 2.307 3.522 5.589 4.939.78.337 1.39.539 1.864.69.784.249 1.497.214 2.061.129.629-.094 1.951-.798 2.224-1.57.273-.771.273-1.431.191-1.57-.083-.14-.297-.221-.627-.386z"/></svg>
                                </a>
                                <a href="https://linkedin.com/in/your_username" target="_blank" class="dock-link brand-linkedin" title="LinkedIn Network">
                                    <svg viewBox="0 0 24 24"><path d="M22.23 0H1.77C.8 0 0 .77 0 1.72v20.56C0 23.23.8 24 1.77 24h20.46c.98 0 1.77-.77 1.77-1.72V1.72C24 .77 23.2 0 22.23 0zM7.12 20.45H3.56V9H7.12v11.45zM5.34 7.43c-1.14 0-2.06-.92-2.06-2.06 0-1.14.92-2.06 2.06-2.06 1.14 0 2.06.92 2.06 2.06 0 1.14-.92 2.06-2.06 2.06zm15.11 13.02h-3.56v-5.6c0-1.34-.03-3.05-1.86-3.05-1.86 0-2.14 1.45-2.14 2.95v5.7H9.33V9h3.42v1.56h.05c.48-.9 1.64-1.85 3.37-1.85 3.6 0 4.27 2.37 4.27 5.45v6.29z"/></svg>
                                </a>
                                <a href="https://github.com/your_username" target="_blank" class="dock-link brand-github" title="GitHub Repositories">
                                    <svg viewBox="0 0 24 24"><path d="M12 .297c-6.63 0-12 5.373-12 12 0 5.303 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61C4.422 18.07 3.633 17.7 3.633 17.7c-1.087-.744.084-.729.084-.729 1.205.084 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.417-1.305.76-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.465-2.38 1.235-3.22-.135-.303-.54-1.523.105-3.176 0 0 1.005-.322 3.3 1.23.96-.267 1.98-.399 3-.405 1.02.006 2.04.138 3 .405 2.28-1.552 3.285-1.23 3.285-1.23.645 1.653.24 2.873.12 3.176.765.84 1.23 1.91 1.23 3.22 0 4.61-2.805 5.625-5.475 5.92.42.36.81 1.096.81 2.22 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 22.092 24 17.592 24 12.297c0-6.627-5.373-12-12-12"/></svg>
                                </a>
                                <a href="mailto:your_email@example.com" class="dock-link brand-mail" title="Direct Email Anchor">
                                    <svg viewBox="0 0 24 24"><path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/></svg>
                                </a>
                                <a href="https://t.me/your_username" target="_blank" class="dock-link brand-telegram" title="Telegram Feed">
                                    <svg viewBox="0 0 24 24"><path d="M11.944 0C5.344 0 0 5.344 0 11.944c0 5.622 3.88 10.331 9.124 11.645.105.025.22-.03.25-.133.424-1.42.965-3.23 1.34-4.494l.654-2.196c-.6-.445-.964-1.16-.964-1.942 0-1.4 1.135-2.536 2.536-2.536.653 0 1.25.247 1.706.654l2.585-1.74a.222.222 0 0 1 .324.088c.113.21.056.47-.133.616l-2.072 1.597c.453.53.729 1.214.729 1.961 0 1.678-1.36 3.038-3.038 3.038-.28 0-.55-.043-.807-.116l-.82 2.748c-.35 1.173-.87 2.91-1.256 4.225-.03.1.033.206.133.23A11.93 11.93 0 0 0 11.944 24C18.544 24 24 18.544 24 11.944 24 5.344 18.544 0 11.944 0z" style="display:none;"/>
                                    <path d="M23.91 2.312c-.116-.43-.46-.77-.89-.886C21.754.912 2.38 8.16.48 8.88c-.46.174-.53.58-.11.78l5.44 2.54 2.14 6.94c.14.46.54.54.89.18l3.19-3.19 5.09 4.3c.47.4.95.14 1.07-.46l3.62-17.1c.14-.65-.18-1.02-.71-.58L23.91 2.312zm-15.65 9.7l9.74-6.07c.22-.14.43.08.26.24l-8.32 7.82-.36 3.19-.13-3.14-.19-2.04z"/></svg>
                                </a>
                                <a href="https://facebook.com/your_username" target="_blank" class="dock-link brand-fb" title="Facebook Hub">
                                    <svg viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                                </a>
                                <a href="https://instagram.com/your_username" target="_blank" class="dock-link brand-insta" title="Instagram Feed">
                                    <svg viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.051.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 1 0 0 12.324 6.162 6.162 0 0 0 0-12.324zM12 16a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm6.406-11.845a1.44 1.44 0 1 0 0 2.881 1.44 1.44 0 0 0 0-2.881z"/></svg>
                                </a>
                                <a href="https://twitter.com/your_username" target="_blank" class="dock-link brand-twitter" title="Twitter Network">
                                    <svg viewBox="0 0 24 24"><path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/></svg>
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="about-grid">
                        <div class="info-card">
                            <h2>🚀 Specialized Technical Core Focus</h2>
                            <p>Focused on writing clean, modular code following professional design patterns. Expert at building secure backend data links and designing intuitive user controls that display system options clearly.</p>
                            <div class="pills-container">
                                <span class="pill-tag">3-Tier Web Systems</span>
                                <span class="pill-tag">Secure Database Engineering</span>
                                <span class="pill-tag">C# Desktop Applications</span>
                                <span class="pill-tag">PHP Backend Architecture</span>
                                <span class="pill-tag">Asynchronous AJAX Workflows</span>
                                <span class="pill-tag">Modern UI/UX Selection Controls</span>
                            </div>
                        </div>

                        <div class="info-card">
                            <h2>✉️ Engineering Philosophy</h2>
                            <p>Every line of logic should be written with precision and scale in mind. Software engineering goes beyond creating functional code—it focuses on crafting clear, maintainable system components that elevate the user's experience.</p>
                            <p>Jarvis represents this commitment to clean architecture, turning technical parameters into a polished workspace environment.</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </main>

  </div>

  <script>
    /**
     * Switch content view between the System details and Creator portfolio
     * @param {String} activeTarget - 'system' or 'developer'
     */
    function toggleActiveView(activeTarget) {
        // 1. Remove active visibility classes from all view decks
        document.querySelectorAll('.about-view-deck').forEach(deck => {
            deck.classList.remove('active');
        });
        
        // 2. Clear highlight states from tab buttons
        document.querySelectorAll('.switcher-btn').forEach(btn => {
            btn.classList.remove('active');
        });

        // 3. Set visibility parameters to the active choice
        document.getElementById(`view-${activeTarget}`).classList.add('active');
        document.getElementById(`btn-${activeTarget}`).classList.add('active');

        // 4. Scroll internal viewport canvas content back to baseline top positions
        document.querySelector('.panel-content-scroll').scrollTop = 0;
    }
  </script>
</body>
</html>