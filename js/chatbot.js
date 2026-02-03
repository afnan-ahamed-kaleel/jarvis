// ========================
// Jarvis AI Responses
// ========================
const responses = [
  // ===== Mood / Emotions =====
  { keywords: ["sad", "unhappy", "depressed", "down"], reply: "I'm sorry you're feeling sad. Remember, every storm passes 🌦️. You're not alone." },
  { keywords: ["stress", "anxious", "tension", "overwhelmed"], reply: "Stress can be heavy. Try a slow breathing exercise for 1 minute 🌬️." },
  { keywords: ["happy", "good", "excited", "joy"], reply: "That's wonderful! Keep enjoying the moment 😊" },
  { keywords: ["angry", "frustrated", "upset"], reply: "I understand. Sometimes stepping back for 5 minutes can help cool down 🔥." },
  { keywords: ["lonely", "alone", "isolated"], reply: "You're not alone. Even small connections matter — reach out to someone you trust 🌟." },
  { keywords: ["tired", "sleepy", "exhausted"], reply: "Rest is important. Try a short nap or calm meditation 💤." },
  { keywords: ["anxiety", "panic", "worried"], reply: "Try grounding yourself: feel your feet, breathe slowly, focus on the present moment 🌿." },

  // ===== Greetings =====
  { keywords: ["hello", "hi", "hey", "hola"], reply: "Hello! I'm Jarvis, your mental wellness companion. How are you feeling today?" },
  { keywords: ["good morning", "morning"], reply: "Good morning! ☀️ Remember, today is a fresh start." },
  { keywords: ["good night", "night"], reply: "Good night! 🌙 Sleep well and recharge your mind." },
  
  // ===== Support / Advice =====
  { keywords: ["help", "support", "advice"], reply: "I am here for you. Take a deep breath and tell me what's on your mind 💙." },
  { keywords: ["problem", "issue", "challenge"], reply: "Take one step at a time. Focus on what you can control, not what you can't ✨." },
  { keywords: ["motivation", "energy", "focus"], reply: "Keep going! Small steps lead to big changes. 💪" },
  { keywords: ["life", "future", "goal"], reply: "Focus on small achievable goals. Progress is progress, no matter how tiny 🚀." },

  // ===== Wellness / Meditation =====
  { keywords: ["meditation", "relax", "calm"], reply: "Meditation can reset your mind. Focus on your breath for a few minutes 🧘." },
  { keywords: ["breathing", "breathe", "deep breath"], reply: "Try 4 seconds inhale, 4 hold, 4 seconds exhale. Repeat 5 times 🌿." },
  { keywords: ["exercise", "workout", "gym", "run"], reply: "Physical activity boosts your mood. Even 10 minutes of movement helps 🏃‍♂️." },
  { keywords: ["sleep", "insomnia", "awake"], reply: "Avoid screens 30 minutes before bed. Try deep breathing or soft music 🌙." },
  { keywords: ["music", "song", "listen"], reply: "Listening to calming music can lift your spirits 🎶." },
  { keywords: ["nature", "walk", "outside"], reply: "A short walk outside can refresh your mind and boost mood 🌳." },

  // ===== Gratitude / Positive Mindset =====
  { keywords: ["grateful", "gratitude", "thankful"], reply: "Try writing down 1 thing you're grateful for today 📝. Small gratitude sparks big joy." },
  { keywords: ["positivity", "positive", "happy thoughts"], reply: "Focus on what went right today. Positive thoughts can transform your day 🌞." },
  { keywords: ["self love", "love yourself"], reply: "Treat yourself kindly. You deserve care and respect 💖." },

  // ===== Fun / Casual Chat =====
  { keywords: ["joke", "funny", "laugh"], reply: "Why don't scientists trust atoms? Because they make up everything 😄" },
  { keywords: ["game", "play"], reply: "I can suggest mindfulness games: like counting breaths or a short gratitude challenge 🎮." },
  { keywords: ["friend", "buddy"], reply: "Friends are important! If you want, you can chat with me anytime 💬." },
  { keywords: ["love", "affection"], reply: "Love and care are vital for mental wellness. Spread some today 💖." },

  // ===== Misc / Daily Tips =====
  { keywords: ["food", "diet", "healthy"], reply: "Balanced nutrition helps mental wellness. Include fruits, vegetables, and stay hydrated 🥗💧." },
  { keywords: ["water", "drink"], reply: "Don't forget to drink water regularly 💦. Hydration keeps both body and mind healthy." },
  { keywords: ["coffee", "tea"], reply: "Moderation is key. Too much caffeine can increase anxiety ☕." },
  { keywords: ["weather", "sunny", "rain"], reply: "I can't see the weather, but a walk outside always helps lift your mood ☀️🌧️." },
  { keywords: ["exercise reminder", "move"], reply: "Take a short break and stretch your body. Movement clears the mind 🏃‍♂️." },

  // ===== Greetings / Farewells =====
  { keywords: ["bye", "goodbye", "see you"], reply: "Take care! Remember, I'm always here when you need me 👋" },
  { keywords: ["thanks", "thank you", "thx"], reply: "You're welcome! 😊 I'm always here to help." },
  { keywords: ["yes", "yeah", "yep"], reply: "Got it! 👍" },
  { keywords: ["no", "nah", "nope"], reply: "Alright, I understand ❗" },

  // ===== Default / Catch-all =====
  { keywords: ["default"], reply: "I hear you. Can you tell me more about how you're feeling? 💭" }
];

// ========================
// Send Message Function with Typing Animation + Emoji Glow
// ========================
function sendMessage() {
  const inputBox = document.getElementById("userInput");
  const input = inputBox.value.trim().toLowerCase();
  const chatBox = document.getElementById("chatBox");

  if (!input) return;

  // Display user's message
  chatBox.innerHTML += `<p><b>You:</b> ${input}</p>`;
  chatBox.scrollTop = chatBox.scrollHeight;
  inputBox.value = "";

  // Typing animation
  const typingMsg = document.createElement("p");
  typingMsg.innerHTML = `<b>Jarvis:</b> <span class="typing">...</span>`;
  chatBox.appendChild(typingMsg);
  chatBox.scrollTop = chatBox.scrollHeight;

  setTimeout(() => {
    // Determine reply
    let reply = "I'm here for you ❤️";
    for (let obj of responses) {
      for (let kw of obj.keywords) {
        if (input.includes(kw)) {
          reply = obj.reply;
          break;
        }
      }
      if (reply !== "I'm here for you ❤️") break;
    }
    if (reply === "I'm here for you ❤️") {
      reply = responses.find(obj => obj.keywords[0] === "default").reply;
    }

    // Replace typing with actual reply with emoji glow
    typingMsg.innerHTML = `<b>Jarvis:</b> <span class="emoji-glow">${reply}</span>`;
    chatBox.scrollTop = chatBox.scrollHeight;

  }, 800 + Math.random() * 500); // Typing delay (0.8–1.3s)
}
