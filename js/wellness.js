// wellness.js

// Function to simulate mood analysis
function analyzeMood() {
  const suggestionsBox = document.getElementById("suggestions");

  // Example: pick random suggestions
  const suggestions = [
    "🌿 Try a 5-minute breathing exercise.",
    "📝 Write down one thing you’re grateful for today.",
    "💤 Take a short nap or relax for 10 minutes.",
    "🎵 Listen to calming music for 5–10 minutes.",
    "🚶‍♂️ Take a short walk outside for fresh air."
  ];

  // Pick 2 random suggestions
  let randomSuggestions = [];
  while (randomSuggestions.length < 2) {
    const pick = suggestions[Math.floor(Math.random() * suggestions.length)];
    if (!randomSuggestions.includes(pick)) {
      randomSuggestions.push(pick);
    }
  }

  // Insert suggestions into the div
  suggestionsBox.innerHTML = randomSuggestions.join("<br>");

  // Make the box visible
  suggestionsBox.style.display = "block";

  // Add smooth fade-in animation
  suggestionsBox.style.opacity = 0;
  suggestionsBox.style.transition = "opacity 0.5s ease";
  setTimeout(() => {
    suggestionsBox.style.opacity = 1;
  }, 50);
}

// Optional: if you want, you can clear suggestions on page load
window.addEventListener("load", () => {
  const suggestionsBox = document.getElementById("suggestions");
  suggestionsBox.style.display = "none";
});
