function saveMood(mood) {
  let moods = JSON.parse(localStorage.getItem("moods")) || [];
  moods.push({ mood: mood, date: new Date().toDateString() });
  localStorage.setItem("moods", JSON.stringify(moods));

  document.getElementById("result").innerText =
    "Mood saved: " + mood;
}
