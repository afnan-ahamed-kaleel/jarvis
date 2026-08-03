<?php
session_start();
require_once 'db.php';

// Redirect to login if user session is missing
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$sweet_alert = "";

// Handle Profile Creation and Modifications Form State
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = trim($_POST['full_name']);
    $dob = $_POST['date_of_birth'];
    $gender = $_POST['gender'];
    $blood_group = trim($_POST['blood_group']);
    $weight = !empty($_POST['weight_kg']) ? $_POST['weight_kg'] : NULL;
    $height = !empty($_POST['height_cm']) ? $_POST['height_cm'] : NULL;
    $conditions = trim($_POST['medical_conditions']);
    $allergies = trim($_POST['allergies']);
    $medications = trim($_POST['current_medications']);
    
    // Process Dynamic Contacts (Serialized to maintain schema mapping integrity)
    $emergency_names = $_POST['emergency_names'] ?? [];
    $emergency_phones = $_POST['emergency_phones'] ?? [];
    
    $primary_name = !empty($emergency_names) ? trim($emergency_names[0]) : '';
    $primary_phone = !empty($emergency_phones) ? trim($emergency_phones[0]) : '';
    
    // Optional: If your schema supports storing secondary elements, serialize them into conditions/notes, 
    // or map them safely into string delimiters here to avoid schema breaks.
    $serialized_names = implode(' | ', array_map('trim', $emergency_names));
    $serialized_phones = implode(' | ', array_map('trim', $emergency_phones));

    // Check if profile record already exists
    $check_stmt = $conn->prepare("SELECT id FROM user_profiles WHERE user_id = ?");
    $check_stmt->bind_param("i", $user_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows > 0) {
        // Update existing record using prepared statements
        $sql = "UPDATE user_profiles SET full_name=?, date_of_birth=?, gender=?, blood_group=?, weight_kg=?, height_cm=?, medical_conditions=?, allergies=?, current_medications=?, emergency_contact_name=?, emergency_contact_phone=? WHERE user_id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssddsssssi", $full_name, $dob, $gender, $blood_group, $weight, $height, $conditions, $allergies, $medications, $serialized_names, $serialized_phones, $user_id);
    } else {
        // Insert new profile record
        $sql = "INSERT INTO user_profiles (user_id, full_name, date_of_birth, gender, blood_group, weight_kg, height_cm, medical_conditions, allergies, current_medications, emergency_contact_name, emergency_contact_phone) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issssdssssss", $user_id, $full_name, $dob, $gender, $blood_group, $weight, $height, $conditions, $allergies, $medications, $serialized_names, $serialized_phones);
    }

    if ($stmt->execute()) {
        $sweet_alert = "Swal.fire({ icon: 'success', title: 'Profile Committed', text: 'Clinical telemetry records asserted successfully.', toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, timerProgressBar: true });";
    } else {
        $error_msg = addslashes($conn->error);
        $sweet_alert = "Swal.fire({ icon: 'error', title: 'Database Error', text: 'Constraint failed: $error_msg' });";
    }
}

// Fetch existing clinical profile record for data population
$stmt = $conn->prepare("SELECT * FROM user_profiles WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$profile = $stmt->get_result()->fetch_assoc();

// Input fallbacks matching empty states
$full_name = $profile['full_name'] ?? '';
$dob = $profile['date_of_birth'] ?? '';
$gender = $profile['gender'] ?? '';
$blood_group = $profile['blood_group'] ?? '';
$weight = $profile['weight_kg'] ?? '';
$height = $profile['height_cm'] ?? '';
$conditions = $profile['medical_conditions'] ?? '';
$allergies = $profile['allergies'] ?? '';
$medications = $profile['current_medications'] ?? '';
$emergency_name_raw = $profile['emergency_contact_name'] ?? '';
$emergency_phone_raw = $profile['emergency_contact_phone'] ?? '';

// Explode matching values back into Arrays for dynamic repeater population
$saved_names = !empty($emergency_name_raw) ? explode(' | ', $emergency_name_raw) : [''];
$saved_phones = !empty($emergency_phone_raw) ? explode(' | ', $emergency_phone_raw) : [''];
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Jarvis – Clinical Profile</title>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    * {
      box-sizing: border-box;
      font-family: "SF Pro Display", "Segoe UI", Inter, sans-serif;
      margin: 0;
      padding: 0;
    }

    body {
      min-height: 100vh;
      background: radial-gradient(circle at center, #306f9f, #f0f7fc),
        linear-gradient(135deg, #063776, #eef3ff);
      color: #211f3d;
      display: flex;
      overflow-x: hidden;
    }

    .dashboard-layout {
      display: flex;
      width: 100vw;
      height: 100vh;
      overflow: hidden;
    }

    /* System Navigation Side Panel Panel */
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
      z-index: 200;
      box-shadow: 10px 0 35px rgba(0, 0, 0, 0.05);
    }

    .jarvis-sidebar:hover { width: 260px; }
    .sidebar-brand { display: flex; align-items: center; gap: 16px; padding-left: 10px; margin-bottom: 40px; }
    .brand-img { width: 40px; height: 40px; object-fit: contain; }
    .brand-text { font-size: 1.3rem; font-weight: 700; white-space: nowrap; opacity: 0; transition: opacity 0.2s ease; }
    .jarvis-sidebar:hover .brand-text { opacity: 1; }
    .sidebar-menu { list-style: none; display: flex; flex-direction: column; gap: 12px; flex: 1; }
    
    .sidebar-menu li a, .logout-btn-nav {
      display: flex; align-items: center; gap: 20px; padding: 14px; border-radius: 16px;
      text-decoration: none; color: #34495e; font-size: 0.95rem; font-weight: 500; transition: all 0.2s ease; white-space: nowrap;
    }
    .sidebar-menu li a .icon, .logout-btn-nav .icon { font-size: 1.4rem; display: inline-block; text-align: center; width: 30px; }
    .menu-text { opacity: 0; transition: opacity 0.2s ease; }
    .jarvis-sidebar:hover .menu-text { opacity: 1; }
    .sidebar-menu li a:hover { background: rgba(255, 255, 255, 0.5); transform: translateX(4px); }
    .sidebar-menu li a.active { background: linear-gradient(135deg, #4da3ff, #2c7be5); color: white; box-shadow: 0 8px 20px rgba(44, 123, 229, 0.3); }

    .logout-btn-nav { color: #d63031; background: rgba(214, 48, 49, 0.08); margin-top: auto; }
    .logout-btn-nav:hover { background: #d63031 !important; color: white !important; }

    .main-content-area {
      flex: 1;
      padding: 20px 25px;
      overflow-y: auto;
      height: 100vh;
    }

    .premium-dashboard-panel {
      background: rgba(255, 255, 255, 0.22);
      backdrop-filter: blur(30px) saturate(170%);
      -webkit-backdrop-filter: blur(30px) saturate(170%);
      border-radius: 28px;
      padding: 0 25px 25px 25px;
      box-shadow: 0 40px 80px rgba(0, 0, 0, 0.06), inset 0 1px 0 rgba(255, 255, 255, 0.4);
      min-height: calc(100vh - 40px);
      display: flex;
      flex-direction: column;
      gap: 20px;
    }

    /* Sticky System Header Workspace Frame */
    .app-header { 
      position: sticky;
      top: 0;
      background: rgba(243, 248, 252, 0.85);
      backdrop-filter: blur(20px);
      -webkit-backdrop-filter: blur(20px);
      padding: 20px 0;
      margin-bottom: 5px;
      border-bottom: 1px solid rgba(255, 255, 255, 0.4);
      display: flex; 
      justify-content: space-between; 
      align-items: center; 
      gap: 20px;
      z-index: 50;
    }
    .app-header h1 { font-size: 1.8rem; letter-spacing: -0.5px; }
    .app-header p { margin-top: 2px; font-size: 0.9rem; color: rgba(31, 45, 61, 0.65); }

    .header-action-cluster {
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .action-btn-link {
      padding: 10px 18px;
      border-radius: 12px;
      font-weight: 600;
      text-decoration: none;
      font-size: 0.88rem;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      border: none;
      cursor: pointer;
      transition: all 0.2s ease;
      box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }
    .btn-report {
      background: rgba(255, 255, 255, 0.7);
      color: #2c7be5;
      border: 1px solid rgba(44, 123, 229, 0.2);
    }
    .btn-report:hover {
      background: #2c7be5;
      color: white;
      transform: translateY(-1px);
    }
    .commit-profile-btn {
      background: linear-gradient(135deg, #4da3ff, #2c7be5);
      color: white;
      box-shadow: 0 4px 12px rgba(44, 123, 229, 0.2);
    }
    .commit-profile-btn:hover {
      transform: translateY(-1px);
      box-shadow: 0 6px 16px rgba(44, 123, 229, 0.35);
    }

    .dashboard-scroller-content {
      display: flex;
      flex-direction: column;
      gap: 20px;
    }

    /* Balanced Split Grid Core Canvas */
    .upper-workspace-split {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 20px;
      align-items: start;
    }

    .dashboard-card {
      background: rgba(255, 255, 255, 0.45);
      border-radius: 20px;
      padding: 20px 24px;
      border: 1px solid rgba(255, 255, 255, 0.5);
      backdrop-filter: blur(10px);
      box-shadow: 0 6px 20px rgba(0,0,0,0.01);
    }

    .card-title-header-wrapper {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 16px;
    }

    .section-headline {
      font-size: 0.9rem;
      text-transform: uppercase;
      letter-spacing: 0.8px;
      color: #4a5568;
      border-left: 4px solid #2c7be5;
      padding-left: 10px;
      font-weight: 700;
    }

    .vertical-stack-form {
      display: flex;
      flex-direction: column;
      gap: 14px;
    }

    /* Grid layout configurations inside cards */
    .row-grid-fields {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 12px;
    }
    .field-third-split {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 12px;
    }

    .form-field-group {
      display: flex;
      flex-direction: column;
      gap: 6px;
    }
    .form-field-group label {
      font-size: 0.82rem;
      font-weight: 600;
      color: #4a5568;
    }
    .form-field-input {
      padding: 10px 12px;
      border-radius: 10px;
      border: 1px solid rgba(0, 0, 0, 0.08);
      background: rgba(255, 255, 255, 0.65);
      font-size: 0.9rem;
      color: #211f3d;
      outline: none;
      transition: all 0.2s ease;
      width: 100%;
    }
    .form-field-input:focus {
      background: rgba(255, 255, 255, 0.95);
      border-color: #2c7be5;
      box-shadow: 0 0 0 3px rgba(44, 123, 229, 0.1);
    }
    
    textarea.form-field-input {
      resize: none;
      min-height: 80px;
    }

    /* Dynamic Repeater Element Engine Architecture */
    .contact-repeater-container {
      display: flex;
      flex-direction: column;
      gap: 12px;
      max-height: 300px;
      overflow-y: auto;
      padding-right: 4px;
    }

    .contact-row-entry {
      display: grid;
      grid-template-columns: 1fr 1fr 40px;
      gap: 12px;
      align-items: flex-end;
      background: rgba(255,255,255,0.25);
      padding: 10px;
      border-radius: 12px;
      border: 1px dashed rgba(0,0,0,0.05);
      animation: appendFade 0.25s ease-out forwards;
    }

    @keyframes appendFade {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .btn-add-repeater {
      background: rgba(44, 123, 229, 0.1);
      color: #2c7be5;
      border: none;
      padding: 6px 12px;
      border-radius: 8px;
      font-weight: 700;
      font-size: 0.8rem;
      cursor: pointer;
      transition: all 0.2s ease;
    }
    .btn-add-repeater:hover {
      background: #2c7be5;
      color: white;
    }

    .btn-delete-row {
      background: rgba(214, 48, 49, 0.1);
      color: #d63031;
      border: none;
      height: 38px;
      border-radius: 8px;
      cursor: pointer;
      font-weight: 700;
      font-size: 1.1rem;
      transition: all 0.2s ease;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .btn-delete-row:hover {
      background: #d63031;
      color: white;
    }

    .emergency-fab {
      position: fixed;
      bottom: 25px;
      right: 25px;
      width: 55px;
      height: 55px;
      background: #d63031;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-size: 1.4rem;
      text-decoration: none;
      box-shadow: 0 8px 25px rgba(214, 48, 49, 0.3);
      transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
      z-index: 999;
    }
    .emergency-fab:hover { transform: scale(1.08) rotate(12deg); background: #ff7675; }

  </style>
</head>

<body>

  <div class="dashboard-layout">
    <?php include 'sidebar.php'; ?>

    <main class="main-content-area">
      <div class="premium-dashboard-panel">

        <form action="profile.php" method="POST" style="display: contents;">

          <header class="app-header">
            <div>
              <h1>Clinical Metric Profile</h1>
              <p>Update personal medical baseline attributes securely</p>
            </div>
            <div class="header-action-cluster">
              <a href="generate_report.php" target="_blank" class="action-btn-link btn-report">📄 Generate Report</a>
              <button type="submit" class="action-btn-link commit-profile-btn">Commit Changes</button>
              <a href="logout.php" class="action-btn-link btn-logout" style="background: rgba(214, 48, 49, 0.1); color: #d63031; border: 1px solid rgba(214, 48, 49, 0.2); text-decoration: none; padding: 10px 18px; border-radius: 12px; font-weight: 600; font-size: 0.88rem; display: inline-flex; align-items: center; gap: 6px;">🚪 Log Out</a>
            </div>
          </header>

          <div class="dashboard-scroller-content">
            
            <div class="upper-workspace-split">
              
              <div class="dashboard-card">
                <div class="card-title-header-wrapper">
                  <h3 class="section-headline">Baseline Telemetry Metrics</h3>
                </div>
                
                <div class="vertical-stack-form">
                  <div class="form-field-group">
                    <label>Full Legal Name</label>
                    <input type="text" name="full_name" class="form-field-input" value="<?php echo htmlspecialchars($full_name); ?>" required placeholder="John Doe">
                  </div>
                  
                  <div class="row-grid-fields">
                    <div class="form-field-group">
                      <label>Date of Birth</label>
                      <input type="date" name="date_of_birth" class="form-field-input" value="<?php echo htmlspecialchars($dob); ?>" required>
                    </div>
                    <div class="form-field-group">
                      <label>Gender Declaration Identity</label>
                      <select name="gender" class="form-field-input" required>
                        <option value="">Select Option</option>
                        <option value="Male" <?php if($gender == 'Male') echo 'selected'; ?>>Male</option>
                        <option value="Female" <?php if($gender == 'Female') echo 'selected'; ?>>Female</option>
                      </select>
                    </div>
                  </div>

                  <div class="field-third-split">
                    <div class="form-field-group">
                      <label>Blood Group</label>
                      <select name="blood_group" class="form-field-input" required>
                        <option value="">Select</option>
                        <option value="A+" <?php if($blood_group == 'A+') echo 'selected'; ?>>A+</option>
                        <option value="A-" <?php if($blood_group == 'A-') echo 'selected'; ?>>A-</option>
                        <option value="B+" <?php if($blood_group == 'B+') echo 'selected'; ?>>B+</option>
                        <option value="B-" <?php if($blood_group == 'B-') echo 'selected'; ?>>B-</option>
                        <option value="O+" <?php if($blood_group == 'O+') echo 'selected'; ?>>O+</option>
                        <option value="O-" <?php if($blood_group == 'O-') echo 'selected'; ?>>O-</option>
                        <option value="AB+" <?php if($blood_group == 'AB+') echo 'selected'; ?>>AB+</option>
                        <option value="AB-" <?php if($blood_group == 'AB-') echo 'selected'; ?>>AB-</option>
                      </select>
                    </div>
                    <div class="form-field-group">
                      <label>Weight (kg)</label>
                      <input type="number" step="0.01" name="weight_kg" class="form-field-input" value="<?php echo htmlspecialchars($weight); ?>" placeholder="e.g. 72.5">
                    </div>
                    <div class="form-field-group">
                      <label>Height (cm)</label>
                      <input type="number" step="0.01" name="height_cm" class="form-field-input" value="<?php echo htmlspecialchars($height); ?>" placeholder="e.g. 176.5">
                    </div>
                  </div>
                </div>
              </div>

              <div class="dashboard-card">
                <div class="card-title-header-wrapper">
                  <h3 class="section-headline">Emergency Contact Hierarchy</h3>
                  <button type="button" class="btn-add-repeater" id="add-contact-btn">＋ Add Contact</button>
                </div>
                
                <div class="contact-repeater-container" id="repeater-workspace">
                  <?php foreach ($saved_names as $index => $name): 
                      $phone = $saved_phones[$index] ?? '';
                  ?>
                  <div class="contact-row-entry">
                    <div class="form-field-group">
                      <label>Contact Name</label>
                      <input type="text" name="emergency_names[]" class="form-field-input" value="<?php echo htmlspecialchars($name); ?>" placeholder="Next of Kin Name" required>
                    </div>
                    <div class="form-field-group">
                      <label>Phone Line</label>
                      <input type="tel" name="emergency_phones[]" class="form-field-input" value="<?php echo htmlspecialchars($phone); ?>" placeholder="+1 555 0199" required>
                    </div>
                    <button type="button" class="btn-delete-row" onclick="removeContactRow(this)">×</button>
                  </div>
                  <?php endforeach; ?>
                </div>
              </div>

            </div> 
            
            <div class="dashboard-card">
              <h3 class="section-headline">Clinical Risk & Narrative Diagnoses</h3>
              <div class="vertical-stack-form">
                <div class="form-field-group">
                  <label>Chronic Medical Conditions / Structural Anomalies</label>
                  <textarea name="medical_conditions" class="form-field-input" placeholder="Declare any chronic diagnostic logs or past surgeries..."><?php echo htmlspecialchars($conditions); ?></textarea>
                </div>
                <div class="form-field-group">
                  <label>Allergies & Immune Counter-Indicators</label>
                  <textarea name="allergies" class="form-field-input" placeholder="List any food, drug, chemical, or environmental reactions..."><?php echo htmlspecialchars($allergies); ?></textarea>
                </div>
                <div class="form-field-group">
                  <label>Active Pharmaceutical Interventions</label>
                  <textarea name="current_medications" class="form-field-input" placeholder="Detail any persistent clinical prescriptions and operational dosages..."><?php echo htmlspecialchars($medications); ?></textarea>
                </div>
              </div>
            </div>

          </div>
        </form>

      </div>
    </main>
  </div>

  <a href="emergency.php" class="emergency-fab" title="Immediate Care Help">🚨</a>

  <script>
    window.addEventListener('DOMContentLoaded', () => {
      // Execute alert engine state responses
      <?php if (!empty($sweet_alert)) echo $sweet_alert; ?>

      // Core Element Node bindings
      const addBtn = document.getElementById('add-contact-btn');
      const workspace = document.getElementById('repeater-workspace');

      // Append new matching form fields rows dynamically
      addBtn.addEventListener('click', () => {
        const rowNode = document.createElement('div');
        rowNode.className = 'contact-row-entry';
        rowNode.innerHTML = `
          <div class="form-field-group">
            <label>Contact Name</label>
            <input type="text" name="emergency_names[]" class="form-field-input" placeholder="Next of Kin Name" required>
          </div>
          <div class="form-field-group">
            <label>Phone Line</label>
            <input type="tel" name="emergency_phones[]" class="form-field-input" placeholder="+1 555 0199" required>
          </div>
          <button type="button" class="btn-delete-row" onclick="removeContactRow(this)">×</button>
        `;
        workspace.appendChild(rowNode);
        workspace.scrollTop = workspace.scrollHeight;
      });
    });

    // Node Garbage Collector Engine
    function removeContactRow(buttonElement) {
      const rows = document.querySelectorAll('.contact-row-entry');
      if (rows.length > 1) {
        buttonElement.parentElement.remove();
      } else {
        Swal.fire({
          icon: 'warning',
          title: 'Operation Restrained',
          text: 'At least one baseline emergency contact record must remain active.',
          toast: true,
          position: 'top-end',
          showConfirmButton: false,
          timer: 3000
        });
      }
    }
  </script>
</body>

</html>