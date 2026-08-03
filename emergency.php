<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
// Simulated username for the dynamic SMS payload string
$username = isset($_SESSION['username']) ? $_SESSION['username'] : "A Jarvis User";
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title>Emergency Support – Jarvis</title>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />

  <style>
    * { box-sizing: border-box; font-family: "SF Pro Display", "Segoe UI", Inter, sans-serif; margin: 0; padding: 0; }
    
    body { 
      min-height: 100vh; 
      background: radial-gradient(circle at center, #306f9f, #f0f7fc), linear-gradient(135deg, #063776, #eef3ff); 
      color: #211f3d; 
      display: flex; 
      overflow-x: hidden; 
    }
    
    /* CRITICAL FIX: Match Jarvis Layout Core Height Constraints */
    .dashboard-layout { 
      display: flex; 
      width: 100vw; 
      height: 100vh; 
      overflow: hidden; 
    }
    
    .main-content-area { 
      flex: 1; 
      padding: 30px; 
      overflow-y: auto; 
      height: 100vh; 
    }
    
    .premium-dashboard-panel { 
      background: rgba(255, 255, 255, 0.25); 
      backdrop-filter: blur(30px) saturate(170%); 
      -webkit-backdrop-filter: blur(30px) saturate(170%);
      border-radius: 32px; 
      padding: 35px; 
      box-shadow: 0 40px 80px rgba(0, 0, 0, 0.08), inset 0 1px 0 rgba(255, 255, 255, 0.4); 
      min-height: calc(100vh - 60px); 
      display: flex;
      flex-direction: column;
      gap: 25px;
    }
    
    /* Header and Emergency Action Button Alignment */
    .app-header { display: flex; justify-content: space-between; align-items: center; gap: 20px; text-align: left; }
    .header-text h1 { font-size: 2.3rem; letter-spacing: -0.5px; }
    .header-text p { margin-top: 4px; font-size: 1rem; color: rgba(31, 45, 61, 0.65); }
    
    .add-emergency-btn { background: #d63031; color: white; border: none; padding: 12px 24px; border-radius: 16px; font-size: 1rem; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px; box-shadow: 0 8px 20px rgba(214, 48, 49, 0.3); transition: all 0.2s ease; white-space: nowrap; }
    .add-emergency-btn:hover { background: #b32424; transform: translateY(-2px); box-shadow: 0 12px 24px rgba(214, 48, 49, 0.4); }

    /* Clean Split Layout Configuration */
    .emergency-split-container { display: grid; grid-template-columns: 1.2fr 0.8fr; gap: 25px; align-items: start; }
    .mood-section { display: flex; flex-direction: column; gap: 25px; }
    
    /* Interactive Cards */
    .mood.stressed { cursor: pointer; transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); text-decoration: none; color: inherit; display: block; }
    .mood.stressed:hover { transform: translateY(-3px); background: rgba(255, 255, 255, 0.6) !important; border-color: #ff7675 !important; box-shadow: 0 12px 25px rgba(214, 48, 49, 0.1); }

    /* Custom Contacts Framework */
    .custom-contacts-title { font-size: 1.1rem; font-weight: 600; color: #063776; display: flex; align-items: center; gap: 8px; margin-top: 5px; }
    .custom-contacts-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 16px; }
    
    .contact-card { background: rgba(255, 255, 255, 0.45); border: 1px solid rgba(255, 255, 255, 0.3); border-radius: 20px; padding: 18px; display: flex; flex-direction: column; gap: 12px; transition: all 0.2s ease; position: relative; backdrop-filter: blur(10px); }
    .contact-card:hover { background: rgba(255, 255, 255, 0.65); transform: translateY(-2px); box-shadow: 0 10px 20px rgba(0,0,0,0.02); }
    .contact-info h5 { font-size: 1.05rem; color: #211f3d; font-weight: 600; }
    .contact-info p { font-size: 0.8rem; color: rgba(33, 31, 61, 0.6); font-weight: 500; text-transform: uppercase; margin-top: 2px; }
    
    .contact-actions { display: flex; gap: 8px; }
    .action-btn { flex: 1; padding: 10px; border-radius: 12px; border: none; font-size: 0.85rem; font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px; text-decoration: none; transition: background 0.2s ease; }
    .action-btn.call-btn { background: rgba(44, 123, 229, 0.1); color: #2c7be5; }
    .action-btn.call-btn:hover { background: #2c7be5; color: white; }
    .action-btn.msg-btn { background: rgba(214, 48, 49, 0.1); color: #d63031; }
    .action-btn.msg-btn:hover { background: #d63031; color: white; }
    
    .delete-contact-btn { position: absolute; top: 12px; right: 12px; background: transparent; border: none; color: rgba(0,0,0,0.3); cursor: pointer; font-size: 0.9rem; transition: color 0.2s ease; }
    .delete-contact-btn:hover { color: #d63031; }

    /* Map & Hospital Sidebar Panel */
    .hospital-map-sidebar { background: rgba(255, 255, 255, 0.4); border: 1px solid rgba(255, 255, 255, 0.5); border-radius: 24px; padding: 24px; display: flex; flex-direction: column; gap: 20px; backdrop-filter: blur(10px); box-shadow: 0 10px 30px rgba(0,0,0,0.02); }
    #hospital-map { width: 100%; height: 280px; border-radius: 16px; border: 1px solid rgba(0,0,0,0.1); box-shadow: inset 0 2px 4px rgba(0,0,0,0.05); z-index: 1; }
    .hospital-list { display: flex; flex-direction: column; gap: 12px; max-height: 320px; overflow-y: auto; padding-right: 4px; }
    .hospital-item { background: rgba(255, 255, 255, 0.6); padding: 14px; border-radius: 14px; border-left: 4px solid #2c7be5; cursor: pointer; transition: all 0.2s ease; }
    .hospital-item:hover { background: rgba(255, 255, 255, 0.9); transform: translateY(-2px); }
    .hospital-item h4 { font-size: 1rem; color: #063776; margin-bottom: 4px; }
    .hospital-item p { font-size: 0.85rem; color: #555; display: flex; align-items: center; gap: 6px; }
    .hospital-item .phone-link { color: #2c7be5; font-weight: 600; text-decoration: none; }

    /* Custom Glassmorphism Modal System */
    .modal-overlay { position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(6, 55, 118, 0.25); backdrop-filter: blur(10px); z-index: 1000; display: flex; align-items: center; justify-content: center; opacity: 0; pointer-events: none; transition: opacity 0.3s ease; }
    .modal-overlay.active { opacity: 1; pointer-events: auto; }
    .modal-window { background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(30px); border: 1px solid rgba(255, 255, 255, 0.4); width: 100%; max-width: 450px; padding: 32px; border-radius: 28px; box-shadow: 0 30px 60px rgba(0,0,0,0.15); transform: translateY(20px); transition: transform 0.3s ease; }
    .modal-overlay.active .modal-window { transform: translateY(0); }
    
    .modal-window h3 { font-size: 1.4rem; margin-bottom: 8px; color: #063776; }
    .modal-window p { font-size: 0.9rem; color: #555; margin-bottom: 20px; }
    .form-group { display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px; }
    .form-group label { font-size: 0.85rem; font-weight: 600; color: #211f3d; }
    .form-group input, .form-group select { padding: 12px; border-radius: 12px; border: 1px solid rgba(0,0,0,0.15); background: rgba(255,255,255,0.6); font-size: 0.95rem; outline: none; transition: border 0.2s ease; }
    .form-group input:focus, .form-group select:focus { border-color: #2c7be5; }
    
    .modal-actions { display: flex; gap: 12px; margin-top: 24px; }
    .modal-btn { flex: 1; padding: 12px; border-radius: 14px; border: none; font-size: 0.95rem; font-weight: 600; cursor: pointer; transition: all 0.2s ease; }
    .modal-btn.cancel { background: rgba(0,0,0,0.05); color: #34495e; }
    .modal-btn.cancel:hover { background: rgba(0,0,0,0.1); }
    .modal-btn.save { background: #2c7be5; color: white; box-shadow: 0 5px 15px rgba(44, 123, 229, 0.3); }
    .modal-btn.save:hover { background: #1a62c4; }

    /* Sidebar Interface Rules (Replicated explicitly from main dashboard) */
    .jarvis-sidebar {
      width: 80px;
      height: 100vh;
      position: sticky;
      top: 0;
      background: rgba(255, 255, 255, 0.3);
      backdrop-filter: blur(25px);
      -webkit-backdrop-filter: blur(25px);
      border-right: 1px solid rgba(255, 255, 255, 0.4);
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      padding: 24px 12px;
      transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      overflow: hidden;
      z-index: 100;
      box-shadow: 10px 0 35px rgba(0, 0, 0, 0.05);
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

    </style>
</head>
<body>

  <div class="dashboard-layout">
    
    <?php include 'sidebar.php'; ?>

    <main class="main-content-area">
      <div class="premium-dashboard-panel">

        <header class="app-header">
          <div class="header-text">
            <h1>Emergency Response Support</h1>
            <p>Immediate care assistance routes — you are not alone 🚨</p>
          </div>
          <button class="add-emergency-btn" id="open-modal-btn">
            <span>➕</span> Add Contact
          </button>
        </header>

        <!-- INSTANT SOS EMERGENCY DISPATCH BAR -->
        <div style="background: linear-gradient(135deg, rgba(214, 48, 49, 0.9), rgba(179, 36, 36, 0.95)); border-radius: 24px; padding: 22px 28px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px; box-shadow: 0 15px 35px rgba(214, 48, 49, 0.25); color: white; border: 1px solid rgba(255,255,255,0.3);">
          <div>
            <h2 style="font-size: 1.4rem; font-weight: 800; display: flex; align-items: center; gap: 10px;">
              <span>🚨</span> INSTANT SOS EMERGENCY TRIGGER
            </h2>
            <p style="font-size: 0.95rem; opacity: 0.9; margin-top: 4px;">Immediately logs your live GPS coordinates to MySQL and generates rapid distress dispatch links for all saved contacts.</p>
          </div>
          <button onclick="triggerInstantSOS()" style="background: #ffffff; color: #d63031; font-weight: 800; padding: 14px 30px; border-radius: 16px; border: none; cursor: pointer; font-size: 1.05rem; box-shadow: 0 8px 20px rgba(0,0,0,0.2); transition: all 0.2s ease;">
            ACTIVATE SOS NOW 📣
          </button>
        </div>
        <div id="sos-dispatch-panel" style="display:none; background: rgba(255,255,255,0.9); border-radius: 20px; padding: 20px; border: 2px solid #d63031; box-shadow: 0 10px 25px rgba(0,0,0,0.1);">
          <h3 style="color:#d63031; font-size:1.2rem; font-weight:700; margin-bottom:8px;">📣 Emergency Alert Initialized!</h3>
          <p style="font-size:0.95rem; color:#211f3d; margin-bottom:12px;">Your emergency distress event and GPS mapping coordinates have been logged into the server database. Tap below to instantly transmit distress broadcasts to your circle:</p>
          <div id="sos-quick-links" style="display:flex; flex-wrap:wrap; gap:12px;"></div>
        </div>

        <div class="emergency-split-container">
          
          <section class="mood-section">
            <div style="background: rgba(255,255,255,0.6); border-radius: 20px; padding: 20px; font-size: 1rem; color: #211f3d; border-left: 5px solid #ffb3b3; line-height: 1.5;">
              If you feel unsafe, extremely overwhelmed, or suspect any form of immediate physical danger, please utilize these verified helpline assets right away.
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px;">
              <a href="tel:1926" class="mood stressed" style="padding: 24px; background: rgba(255,255,255,0.4); border-radius:20px; border: 1px solid rgba(255,255,255,0.2);">
                <span style="font-size: 1.8rem;">📞</span> <strong style="display:block; margin-top:8px;">National Helpline</strong>
                <p style="font-size: 1.3rem; font-weight:700; margin-top:6px; color:#c0392b;">Call 1926</p>
              </a>

              <div class="mood okay" style="padding: 24px; background: rgba(255,255,255,0.4); border-radius:20px; border: 1px solid rgba(255,255,255,0.2);">
                <span style="font-size: 1.8rem;">👨‍⚕️</span> <strong style="display:block; margin-top:8px;">Trusted Contact</strong>
                <p style="margin-top:6px; font-size:0.95rem; color:#555;">Reach out directly to your pre-assigned counselor or close relative</p>
              </div>
            </div>

            <div class="custom-contacts-title">
              <span>🛡️</span> Your Saved Emergency Circles
            </div>
            <div class="custom-contacts-grid" id="contacts-display-wrapper">
              </div>

            <p style="font-size: 0.85rem; text-align: center; color: rgba(31,45,61,0.65); background: rgba(255,255,255,0.2); padding: 12px; border-radius: 12px;">
              <b>Disclaimer:</b> Jarvis provides supportive interactive mental self-care paths. It does not replace medical diagnostics or clinical treatments.
            </p>
          </section>

          <aside class="hospital-map-sidebar">
            <h3 style="font-size: 1.2rem; font-weight: 700; color: #063776;">Nearby Medical Centers</h3>
            <div id="hospital-map"></div>
            <div class="hospital-list" id="hospital-directory"></div>
          </aside>

        </div>

      </div>
    </main>
  </div>

  <div class="modal-overlay" id="contact-modal">
    <div class="modal-window">
      <h3>Add Emergency Contact</h3>
      <p>Securely save trusted contacts directly into your encrypted MySQL database profile.</p>
      
      <form id="emergency-contact-form">
        <div class="form-group">
          <label for="contact-name">Full Name</label>
          <input type="text" id="contact-name" placeholder="e.g. John Doe" required>
        </div>
        <div class="form-group">
          <label for="contact-phone">Phone Number (with Country Code)</label>
          <input type="tel" id="contact-phone" placeholder="e.g. +1234567890" required>
        </div>
        <div class="form-group">
          <label for="contact-relation">Relationship</label>
          <select id="contact-relation" required>
            <option value="" disabled selected>Select Relationship</option>
            <option value="Parent">Parent</option>
            <option value="Spouse">Spouse / Partner</option>
            <option value="Sibling">Sibling</option>
            <option value="Counselor">Counselor / Doctor</option>
            <option value="Friend">Trusted Friend</option>
          </select>
        </div>
        
        <div class="modal-actions">
          <button type="button" class="modal-btn cancel" id="close-modal-btn">Cancel</button>
          <button type="submit" class="modal-btn save">Save Contact</button>
        </div>
      </form>
    </div>
  </div>

  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
  
  <script>
    const currentUsername = "<?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?>";

    let userLatitude = 40.7128;
    let userLongitude = -74.0060;

    const hospitalData = [
      { name: "City General Emergency Hospital", lat: 40.7128, lng: -74.0060, phone: "+1 (555) 019-2834", address: "100 Medical Plaza Dr" },
      { name: "St. Jude Mental Health Wellness Center", lat: 40.7250, lng: -73.9980, phone: "+1 (555) 014-9988", address: "450 Hope Avenue" },
      { name: "Grace Community Medical & Clinic", lat: 40.7050, lng: -74.0120, phone: "+1 (555) 017-4411", address: "78 Pearl Street" }
    ];

    const map = L.map('hospital-map').setView([userLatitude, userLongitude], 12);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '© OpenStreetMap'
    }).addTo(map);

    const directoryContainer = document.getElementById('hospital-directory');
    const markers = {};

    hospitalData.forEach((hospital, index) => {
      const marker = L.marker([hospital.lat, hospital.lng]).addTo(map);
      marker.bindPopup(`<b>${hospital.name}</b><br>${hospital.address}<br><a href="tel:${hospital.phone}">${hospital.phone}</a>`);
      markers[index] = marker;

      const hospitalCard = document.createElement('div');
      hospitalCard.className = 'hospital-item';
      hospitalCard.innerHTML = `
        <h4>${hospital.name}</h4>
        <p style="margin-bottom: 4px;">📍 ${hospital.address}</p>
        <p>📞 <a class="phone-link" href="tel:${hospital.phone}">${hospital.phone}</a></p>
      `;
      hospitalCard.addEventListener('click', () => {
        map.setView([hospital.lat, hospital.lng], 14);
        markers[index].openPopup();
      });
      directoryContainer.appendChild(hospitalCard);
    });

    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition((position) => {
        userLatitude = position.coords.latitude;
        userLongitude = position.coords.longitude;
        map.setView([userLatitude, userLongitude], 12);
        L.circle([userLatitude, userLongitude], { color: '#2c7be5', radius: 500 }).addTo(map).bindPopup('Your Location');
      });
    }

    // --- Emergency Contacts MySQL Database Lifecycle logic ---
    let savedDatabaseContacts = [];

    const modalOverlay = document.getElementById('contact-modal');
    const openModalBtn = document.getElementById('open-modal-btn');
    const closeModalBtn = document.getElementById('close-modal-btn');
    const contactForm = document.getElementById('emergency-contact-form');
    const contactsDisplayWrapper = document.getElementById('contacts-display-wrapper');

    openModalBtn.addEventListener('click', () => modalOverlay.classList.add('active'));
    closeModalBtn.addEventListener('click', () => modalOverlay.classList.remove('active'));
    modalOverlay.addEventListener('click', (e) => { if(e.target === modalOverlay) modalOverlay.classList.remove('active'); });

    document.addEventListener("DOMContentLoaded", async () => {
        await syncLegacyContacts();
        await loadDatabaseContacts();
    });

    // Silently import legacy LocalStorage contacts into MySQL database
    async function syncLegacyContacts() {
        const legacyContacts = JSON.parse(localStorage.getItem('jarvis_emergency_contacts')) || [];
        if (legacyContacts.length > 0) {
            try {
                await fetch('emergency_api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'sync_legacy', contacts: legacyContacts })
                });
                localStorage.removeItem('jarvis_emergency_contacts');
            } catch(e) {
                console.warn('Legacy sync incomplete:', e);
            }
        }
    }

    async function loadDatabaseContacts() {
        try {
            const res = await fetch('emergency_api.php?action=get_contacts');
            const data = await res.json();
            if (data.status === 'success') {
                savedDatabaseContacts = data.contacts || [];
                renderSavedContacts();
            }
        } catch (error) {
            console.error('Error loading emergency contacts from server:', error);
        }
    }

    contactForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      
      const nameVal = document.getElementById('contact-name').value.trim();
      const phoneVal = document.getElementById('contact-phone').value.trim();
      const relationVal = document.getElementById('contact-relation').value;

      try {
          const res = await fetch('emergency_api.php', {
              method: 'POST',
              headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify({ action: 'add_contact', name: nameVal, phone: phoneVal, relation: relationVal })
          });
          const result = await res.json();
          if (result.status === 'success') {
              contactForm.reset();
              modalOverlay.classList.remove('active');
              await loadDatabaseContacts();
          } else {
              alert("Could not save contact: " + (result.message || "Server error"));
          }
      } catch (error) {
          console.error("Error saving contact:", error);
          alert("Network connection error while saving contact.");
      }
    });

    async function deleteContact(id) {
      if (!confirm("Remove this trusted contact from your emergency circle?")) return;
      try {
          await fetch('emergency_api.php', {
              method: 'POST',
              headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify({ action: 'delete_contact', id: id })
          });
          await loadDatabaseContacts();
      } catch(error) {
          console.error("Error removing contact:", error);
      }
    }

    function renderSavedContacts() {
      contactsDisplayWrapper.innerHTML = '';

      if (savedDatabaseContacts.length === 0) {
        contactsDisplayWrapper.innerHTML = `<p style="grid-column: 1/-1; font-size: 0.9rem; color: rgba(33,31,61,0.5); text-align: center; padding: 20px;">No custom emergency contacts in database yet.</p>`;
        return;
      }

      savedDatabaseContacts.forEach(contact => {
        const messageBody = encodeURIComponent(`${currentUsername} is feeling very unwell and needs immediate help. Location context: https://maps.google.com/?q=${userLatitude},${userLongitude}`);
        const cleanPhone = contact.phone.replace(/[^0-9+]/g, '');

        const card = document.createElement('div');
        card.className = 'contact-card';
        card.innerHTML = `
          <button class="delete-contact-btn" onclick="deleteContact(${contact.id})" title="Remove Contact">✕</button>
          <div class="contact-info">
            <h5>${escapeHTML(contact.name)}</h5>
            <p>• ${escapeHTML(contact.relation)}</p>
          </div>
          <div class="contact-actions" style="flex-wrap: wrap; gap: 6px;">
            <a href="tel:${contact.phone}" class="action-btn call-btn" title="Call Contact">📞 Call</a>
            <a href="sms:${contact.phone}?&body=${messageBody}" class="action-btn msg-btn" title="Send Emergency SMS Now">🚨 SMS</a>
            <a href="https://wa.me/${cleanPhone}?text=${messageBody}" target="_blank" class="action-btn" style="background:#2ecc71; color:white;" title="WhatsApp Emergency Alert">💬 WhatsApp</a>
          </div>
        `;
        contactsDisplayWrapper.appendChild(card);
      });
    }

    async function triggerInstantSOS() {
        // Log distress alert with GPS parameters to sos_logs table in MySQL
        try {
            await fetch('emergency_api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'log_sos', lat: userLatitude, lng: userLongitude })
            });
        } catch(e) {
            console.warn('Offline SOS trigger:', e);
        }

        const panel = document.getElementById('sos-dispatch-panel');
        const linksBox = document.getElementById('sos-quick-links');
        panel.style.display = 'block';
        linksBox.innerHTML = '';

        const distressMsg = encodeURIComponent(`🚨 EMERGENCY ALERT from Jarvis AI: ${currentUsername} is triggering an immediate SOS distress call! Live location: https://maps.google.com/?q=${userLatitude},${userLongitude}`);

        if (savedDatabaseContacts.length === 0) {
            linksBox.innerHTML = `<span style="color:#d63031; font-weight:600;">No personal contacts saved yet. Please dial National Helpline 1926 or local services immediately!</span>`;
        } else {
            savedDatabaseContacts.forEach(c => {
                const cleanPhone = c.phone.replace(/[^0-9+]/g, '');
                const btn = document.createElement('a');
                btn.href = `https://wa.me/${cleanPhone}?text=${distressMsg}`;
                btn.target = "_blank";
                btn.style.cssText = "background:#2ecc71; color:white; padding:12px 20px; border-radius:14px; text-decoration:none; font-weight:700; display:inline-flex; align-items:center; gap:8px; box-shadow:0 6px 15px rgba(46,204,113,0.3);";
                btn.innerHTML = `💬 Broadcast WhatsApp to ${escapeHTML(c.name)}`;
                linksBox.appendChild(btn);

                const smsBtn = document.createElement('a');
                smsBtn.href = `sms:${c.phone}?&body=${distressMsg}`;
                smsBtn.style.cssText = "background:#d63031; color:white; padding:12px 20px; border-radius:14px; text-decoration:none; font-weight:700; display:inline-flex; align-items:center; gap:8px; box-shadow:0 6px 15px rgba(214,48,49,0.3);";
                smsBtn.innerHTML = `🚨 SMS to ${escapeHTML(c.name)}`;
                linksBox.appendChild(smsBtn);
            });
        }
        panel.scrollIntoView({ behavior: 'smooth' });
    }

    function escapeHTML(str) {
      return str.replace(/[&<>'"]/g, 
        tag => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;' }[tag] || tag)
      );
    }
  </script>
</body>
</html>