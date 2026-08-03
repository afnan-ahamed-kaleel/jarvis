<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Check if the user selected an existing conversation session from their logs
$current_session_id = isset($_GET['session_id']) ? intval($_GET['session_id']) : null;

// Fetch all historical session headers for this specific user to populate the history drawer
$history_stmt = $conn->prepare("SELECT id, title, updated_at FROM chat_sessions WHERE user_id = ? ORDER BY updated_at DESC");
$history_stmt->bind_param("i", $user_id);
$history_stmt->execute();
$history_result = $history_stmt->get_result();

// Fetch previous messages only if an active session context is loaded
$active_messages = [];
if ($current_session_id) {
    $msg_stmt = $conn->prepare("SELECT sender, message, timestamp FROM chat_messages WHERE session_id = ? ORDER BY timestamp ASC");
    $msg_stmt->bind_param("i", $current_session_id);
    $msg_stmt->execute();
    $active_messages = $msg_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jarvis Chatbot</title>
    <style>
        * { box-sizing: border-box; font-family: "SF Pro Display", "Segoe UI", Inter, sans-serif; margin: 0; padding: 0; }
        
        /* Relaxing Dynamic Breathing Background Engine */
        body { 
            height: 100vh; 
            background: linear-gradient(-45deg, #1e3c72, #2a5298, #3a7bd5, #3a6073, #1e5799);
            background-size: 400% 400%;
            animation: calmingGradientMove 20s ease infinite;
            color: #211f3d; 
            display: flex; 
            overflow: hidden; 
            position: relative;
        }

        /* Ambient glowing background blobs for added depth */
        body::before, body::after {
            content: "";
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            z-index: 0;
            opacity: 0.35;
            pointer-events: none;
            animation: slowFloat 25s ease-in-out infinite alternate;
        }
        body::before {
            width: 400px;
            height: 400px;
            background: #4da3ff;
            top: -10%;
            left: 10%;
        }
        body::after {
            width: 500px;
            height: 500px;
            background: #a1c4fd;
            bottom: -10%;
            right: 10%;
            animation-delay: -5s;
        }

        @keyframes calmingGradientMove {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        @keyframes slowFloat {
            0% { transform: translateY(0) rotate(0deg) scale(1); }
            100% { transform: translateY(40px) rotate(45deg) scale(1.15); }
        }
        
        .dashboard-layout { 
            display: flex; 
            width: 100vw; 
            height: 100vh; 
            overflow: hidden; 
            position: relative;
            z-index: 1;
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
            background: rgba(255, 255, 255, 0.22); 
            backdrop-filter: blur(35px) saturate(180%); 
            -webkit-backdrop-filter: blur(35px) saturate(180%);
            border-radius: 32px; 
            padding: 40px; 
            box-shadow: 0 40px 80px rgba(0, 0, 0, 0.06), inset 0 1px 0 rgba(255, 255, 255, 0.4); 
            height: calc(100vh - 80px); 
            display: flex; 
            flex-direction: column; 
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        
        /* Sidebar System Layout Container */
        .jarvis-sidebar { 
            width: 80px; 
            height: 100vh;
            background: rgba(255, 255, 255, 0.25); 
            backdrop-filter: blur(25px); 
            border-right: 1px solid rgba(255, 255, 255, 0.3); 
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
        .brand-text { font-size: 1.3rem; font-weight: 700; white-space: nowrap; opacity: 0; transition: opacity 0.2s ease; color: #1e3c72; }
        .jarvis-sidebar:hover .brand-text { opacity: 1; }
        
        .sidebar-menu { list-style: none; display: flex; flex-direction: column; gap: 12px; flex: 1; }
        .sidebar-menu li a, .logout-btn-nav { display: flex; align-items: center; gap: 20px; padding: 14px; border-radius: 16px; text-decoration: none; color: #2c3e50; font-size: 0.95rem; font-weight: 500; transition: all 0.2s ease; white-space: nowrap; }
        .sidebar-menu li a .icon, .logout-btn-nav .icon { font-size: 1.4rem; display: inline-block; text-align: center; width: 30px; flex-shrink: 0; }
        .menu-text { opacity: 0; transition: opacity 0.2s ease; }
        .jarvis-sidebar:hover .menu-text { opacity: 1; }
        .sidebar-menu li a:hover { background: rgba(255, 255, 255, 0.4); transform: translateX(4px); }
        .sidebar-menu li a.active { background: linear-gradient(135deg, #4da3ff, #2c7be5); color: white; box-shadow: 0 8px 20px rgba(44, 123, 229, 0.25); }
        
        .sidebar-footer { margin-top: auto; }
        .logout-btn-nav { color: #d63031; background: rgba(214, 48, 49, 0.06); display: flex; }
        .logout-btn-nav:hover { background: #d63031 !important; color: white !important; }

        .app-header { 
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px; 
            flex-shrink: 0; 
        }
        .app-header h1 { font-size: 2.3rem; color: #162a45; }
        .app-header p { color: #4a5568; }
        
        .history-btn {
            background: rgba(255, 255, 255, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.6);
            padding: 10px 18px;
            border-radius: 14px;
            font-size: 0.95rem;
            font-weight: 600;
            color: #2c7be5;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s ease;
            box-shadow: 0 4px 12px rgba(0,0,0,0.02);
        }
        .history-btn:hover {
            background: rgba(255, 255, 255, 0.8);
            transform: translateY(-1px);
        }

        .chat-view-workspace {
            display: flex;
            flex: 1;
            gap: 20px;
            overflow: hidden;
            margin-bottom: 20px;
        }

        .history-sidebar-drawer {
            width: 260px;
            background: rgba(255, 255, 255, 0.25);
            border: 1px solid rgba(255, 255, 255, 0.4);
            border-radius: 24px;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            flex-shrink: 0;
        }
        .history-sidebar-drawer.hidden {
            width: 0;
            opacity: 0;
            padding: 0;
            margin: 0;
            border: none;
        }
        .history-title-banner {
            padding: 14px 18px;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            font-weight: 700;
            color: #4a5568;
            border-bottom: 1px solid rgba(0, 0, 0, 0.04);
        }
        .history-list-node {
            text-decoration: none;
            color: #34495e;
            padding: 12px 18px;
            border-bottom: 1px solid rgba(0,0,0,0.03);
            display: block;
            font-size: 0.9rem;
            transition: all 0.2s;
        }
        .history-list-node:hover, .history-list-node.active {
            background: rgba(44, 123, 229, 0.12);
            color: #2c7be5;
            font-weight: 600;
        }

        /* Chat view feed canvas container */
        #chatBox { 
            flex: 1; 
            background: rgba(255, 255, 255, 0.35); 
            border-radius: 28px; 
            padding: 26px; 
            overflow-y: auto; 
            box-shadow: inset 0 4px 20px rgba(0,0,0,0.02); 
            border: 1px solid rgba(255,255,255,0.25); 
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        /* Highly Circular & Curvy Chat Bubble Layout Engine */
        .chat-bubble-row { display: flex; width: 100%; animation: bubblePopIn 0.3s cubic-bezier(0.34, 1.56, 0.64, 1) forwards; }
        .chat-bubble-row.user-row { justify-content: flex-end; }
        .chat-bubble-row.bot-row { justify-content: flex-start; }
        
        @keyframes bubblePopIn {
            from { opacity: 0; transform: scale(0.9) translateY(10px); }
            to { opacity: 1; transform: scale(1) translateY(0); }
        }
        
        .msg-bubble {
            padding: 14px 22px; 
            max-width: 75%; 
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03); 
            font-size: 0.98rem;
            line-height: 1.5;
        }
        
        /* Ultra-curvy asymmetric variants */
        .msg-bubble.bot {
            background: rgba(255, 255, 255, 0.9);
            color: #211f3d; 
            border-radius: 26px 26px 26px 6px;
            border: 1px solid rgba(255,255,255,0.6);
        }
        .msg-bubble.user {
            background: linear-gradient(135deg, #51a7ff, #2575fc); 
            color: white;
            border-radius: 26px 26px 6px 26px;
            box-shadow: 0 6px 18px rgba(37, 117, 252, 0.2);
        }
        
        .chat-input-wrapper { display: flex; gap: 14px; flex-shrink: 0; }
        .chat-input-wrapper input { flex: 1; padding: 18px 24px; border-radius: 24px; border: 1px solid rgba(255,255,255,0.4); outline: none; font-size: 1rem; background: rgba(255,255,255,0.6); box-shadow: 0 8px 20px rgba(0,0,0,0.01); transition: all 0.2s; }
        .chat-input-wrapper input:focus { background: #ffffff; border-color: #4da3ff; box-shadow: 0 0 0 4px rgba(77, 163, 255, 0.15); }
        
        .send-btn { padding: 0 36px; height: 58px; border-radius: 24px; font-weight: 600; border: none; background: linear-gradient(135deg, #4da3ff, #2c7be5); color: white; cursor: pointer; transition: all 0.2s; box-shadow: 0 6px 15px rgba(44,123,229,0.2); font-size: 1rem; }
        .send-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(44,123,229,0.3); }
    </style>
</head>
<body>

  <div class="dashboard-layout">
    <?php include 'sidebar.php'; ?>

    <main class="main-content-area">
        <div class="premium-dashboard-panel">
            <div class="app-header">
                <div>
                    <h1>Jarvis Conversational AI</h1>
                    <p>Secure mental health conversational assistant workspace</p>
                </div>
                <button type="button" class="history-btn" onclick="toggleHistoryDrawer()">⏳ Chat History</button>
            </div>

            <div class="chat-view-workspace">
                
                <aside class="history-sidebar-drawer hidden" id="historyDrawer">
                    <div class="history-title-banner">Saved Conversations</div>
                    <a href="chatbot.php" class="history-list-node" style="color: #2ecc71; font-weight: bold; text-align: center; background: rgba(46,204,113,0.05);">+ Fresh Thread Context</a>
                    <?php while($row = $history_result->fetch_assoc()): ?>
                        <a href="chatbot.php?session_id=<?php echo $row['id']; ?>" class="history-list-node <?php echo ($current_session_id == $row['id']) ? 'active' : ''; ?>">
                            💬 <?php echo htmlspecialchars($row['title']); ?>
                            <span style="display: block; font-size: 0.72rem; color: #718096; margin-top: 2px; font-weight: normal;"><?php echo date('M d, g:i a', strtotime($row['updated_at'])); ?></span>
                        </a>
                    <?php endwhile; ?>
                </aside>

                <div id="chatBox">
                    <?php if (empty($active_messages)): ?>
                        <div class="chat-bubble-row bot-row">
                            <span class="msg-bubble bot">
                                <b>Jarvis:</b> Hello! I am here to support you. How are you feeling today? 😊
                            </span>
                        </div>
                    <?php else: ?>
                        <?php foreach ($active_messages as $msg): ?>
                            <div class="chat-bubble-row <?php echo ($msg['sender'] === 'user') ? 'user-row' : 'bot-row'; ?>">
                                <span class="msg-bubble <?php echo $msg['sender']; ?>">
                                    <?php if($msg['sender'] === 'bot'): ?><b>Jarvis:</b> <?php endif; ?>
                                    <?php echo htmlspecialchars($msg['message']); ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

            </div>

            <div class="chat-input-wrapper">
                <input type="text" id="userInput" placeholder="Type your message..." onkeydown="if(event.key === 'Enter') sendMessage()">
                <input type="hidden" id="activeSessionId" value="<?php echo $current_session_id ?? ''; ?>">
                <button onclick="sendMessage()" class="send-btn">Send</button>
            </div>
        </div>
    </main>

  </div>
  
  <script>
    function toggleHistoryDrawer() {
        document.getElementById('historyDrawer').classList.toggle('hidden');
    }

    window.addEventListener('DOMContentLoaded', () => {
        const box = document.getElementById('chatBox');
        box.scrollTop = box.scrollHeight;
        
        if(document.getElementById('activeSessionId').value !== '') {
            document.getElementById('historyDrawer').classList.remove('hidden');
        }
    });
  </script>
  <script src="js/chatbot.js"></script>
</body> 
</html>