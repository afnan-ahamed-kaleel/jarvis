// Jarvis AI Terminal - Local Gateway Relay Implementation with Audio Synthesis Matrix & DB Persistence

/**
 * Programmatically synthesizes premium notification sounds using the Web Audio API.
 * Eliminates dependencies on external static audio files and prevents loading lag.
 * @param {String} type - The sound motif configuration ('send' or 'receive')
 */
function playSfx(type) {
    try {
        const AudioContext = window.AudioContext || window.webkitAudioContext;
        if (!AudioContext) return;
        
        const ctx = new AudioContext();
        
        if (type === 'send') {
            // Crisp, subtle structural "pop/woosh" frequency slide up
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            
            osc.type = 'sine';
            osc.frequency.setValueAtTime(580, ctx.currentTime);
            osc.frequency.exponentialRampToValueAtTime(880, ctx.currentTime + 0.12);
            
            gain.gain.setValueAtTime(0.15, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.12);
            
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.start();
            osc.stop(ctx.currentTime + 0.12);
            
        } else if (type === 'receive') {
            // Calm, elegant dual-tone therapeutic musical chime (E5 -> G5 harmony)
            const now = ctx.currentTime;
            
            // First chime tone
            const osc1 = ctx.createOscillator();
            const gain1 = ctx.createGain();
            osc1.type = 'triangle';
            osc1.frequency.setValueAtTime(659.25, now); // E5 note
            gain1.gain.setValueAtTime(0.12, now);
            gain1.gain.exponentialRampToValueAtTime(0.01, now + 0.25);
            osc1.connect(gain1);
            gain1.connect(ctx.destination);
            
            // Harmonizing tone delayed slightly
            const osc2 = ctx.createOscillator();
            const gain2 = ctx.createGain();
            osc2.type = 'sine';
            osc2.frequency.setValueAtTime(783.99, now + 0.06); // G5 note
            gain2.gain.setValueAtTime(0.10, now + 0.06);
            gain2.gain.exponentialRampToValueAtTime(0.01, now + 0.3);
            osc2.connect(gain2);
            gain2.connect(ctx.destination);
            
            osc1.start(now);
            osc1.stop(now + 0.25);
            osc2.start(now + 0.06);
            osc2.stop(now + 0.3);
        }
    } catch (e) {
        console.warn("Web Audio context initialization blocked by browser autoplay constraints:", e);
    }
}

async function sendMessage() {
    const inputBox = document.getElementById("userInput");
    const chatBox = document.getElementById("chatBox");
    const sessionInput = document.getElementById("activeSessionId");
    const userText = inputBox.value.trim();
    
    if (!userText) return;

    const currentSessionId = sessionInput ? sessionInput.value : '';

    // 1. Render User Message Bubbles using standardized curvier system wrappers
    chatBox.innerHTML += `
        <div class="chat-bubble-row user-row">
            <span class="msg-bubble user">
                ${userText}
            </span>
        </div>`;

    // Fire the programmatic send sound effect matrix
    playSfx('send');

    inputBox.value = ""; 
    chatBox.scrollTop = chatBox.scrollHeight;

    // 2. Instantiate Asynchronous Typing Indicator Placeholder matching the curvy profile
    const typingId = "typing-" + Date.now();
    chatBox.innerHTML += `
        <div class="chat-bubble-row bot-row" id="${typingId}">
            <span class="msg-bubble bot">
                <b>Jarvis:</b> <i style="color:#747d8c; font-weight: normal;">Typing...</i>
            </span>
        </div>`;
    chatBox.scrollTop = chatBox.scrollHeight;

    try {
        // 3. Dispatch payload context safely toward internal PHP Gateway with session id
        const response = await fetch("gemini_gateway.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ message: userText, session_id: currentSessionId })
        });
        
        if (!response.ok) {
            throw new Error(`Server returned HTTP status ${response.status}`);
        }

        const data = await response.json();

        // Check and sync session_id if returned
        if (data.session_id && sessionInput && (!sessionInput.value || sessionInput.value == '' || sessionInput.value == '0')) {
            sessionInput.value = data.session_id;
            // Silently update browser URL so refreshing retains this specific session thread
            window.history.replaceState({}, '', `chatbot.php?session_id=${data.session_id}`);
            
            // Unhide history drawer if available
            const drawer = document.getElementById('historyDrawer');
            if (drawer) drawer.classList.remove('hidden');
        }

        // 4. Parse returning structures cleanly
        if (data.candidates && data.candidates[0].content && data.candidates[0].content.parts[0].text) {
            const aiReply = data.candidates[0].content.parts[0].text;

            // Render final system response text matching curvy styles
            document.getElementById(typingId).innerHTML = `
                <span class="msg-bubble bot">
                    <b>Jarvis:</b> <span>${aiReply}</span>
                </span>`;
                
            // Fire response chime audio sequence
            playSfx('receive');
            
        } else if (data.error) {
            throw new Error(data.error.message || data.error);
        } else {
            throw new Error("Invalid object signature returned from endpoint");
        }
    } catch (error) {
        console.error("Connection Error Trace:", error);
        
        // Graceful error notification block using curvy structural components
        document.getElementById(typingId).innerHTML = `
            <span class="msg-bubble bot" style="color:#d63031; background:rgba(214,48,49,0.06); border: 1px solid rgba(214,48,49,0.2);">
                <b>Jarvis:</b> I had trouble processing that request. Please try again in a few moments.
            </span>`;
    }
    
    chatBox.scrollTop = chatBox.scrollHeight;
}

// 5. Capture inputs on Enter key press
document.getElementById("userInput").addEventListener("keypress", function(e) {
    if (e.key === "Enter") {
        sendMessage();
    }
});