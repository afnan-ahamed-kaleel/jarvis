<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    die("Unauthorized access. Access Denied.");
}

$user_id = $_SESSION['user_id'];

// Get user profile data along with authentication baseline details
$sql = "SELECT u.username, p.* FROM users u LEFT JOIN user_profiles p ON u.id = p.user_id WHERE u.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$data || empty($data['full_name'])) {
    die("<div style='font-family:sans-serif; text-align:center; margin-top:50px;'><h3>No Profile Data Found</h3><p>Please populate your profile data sheet before attempting to print metrics reports.</p><a href='profile.php'>Return to Profile</a></div>");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Medical Informatics Profile - <?php echo htmlspecialchars($data['full_name']); ?></title>
    <style>
        * { box-sizing: border-box; font-family: "Courier New", Courier, monospace; margin: 0; padding: 0; }
        body { background: #f0f4f8; padding: 30px; color: #1a202c; }
        
        .report-sheet { background: white; max-width: 800px; margin: 0 auto; padding: 50px; border: 1px solid #cbd5e0; border-radius: 4px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); position: relative; }
        .actions-panel { max-width: 800px; margin: 0 auto 15px auto; text-align: right; }
        .btn-print { padding: 10px 20px; background: #2b6cb0; color: white; border: none; font-weight: bold; border-radius: 4px; cursor: pointer; text-decoration: none; font-family: sans-serif; }
        
        .report-header { border-bottom: 3px double #2d3748; padding-bottom: 20px; margin-bottom: 30px; display: flex; justify-content: space-between; align-items: flex-start; }
        .system-logo { font-size: 24px; font-weight: bold; letter-spacing: 2px; color: #2b6cb0; }
        .report-meta { text-align: right; font-size: 12px; line-height: 1.5; color: #4a5568; }
        
        h2 { font-size: 16px; text-transform: uppercase; border-bottom: 1px solid #4a5568; padding-bottom: 4px; margin: 25px 0 15px 0; letter-spacing: 1px; color: #2b6cb0; }
        
        .data-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px 25px; margin-bottom: 15px; }
        .data-row { font-size: 14px; display: flex; justify-content: space-between; border-bottom: 1px dashed #e2e8f0; padding-bottom: 4px; }
        .data-label { font-weight: bold; color: #4a5568; }
        .data-value { color: #000; text-align: right; }
        
        .narrative-block { font-size: 14px; line-height: 1.6; margin-bottom: 15px; background: #f7fafc; padding: 15px; border-left: 3px solid #cbd5e0; white-space: pre-line; }
        
        .footer-stamp { margin-top: 50px; text-align: center; font-size: 11px; color: #a0aec0; border-top: 1px solid #e2e8f0; padding-top: 15px; }

        /* ===== CRITICAL CSS PRINT MEDIA RULES OVERRIDE ===== */
        @media print {
            body { background: white; padding: 0; color: black; }
            .report-sheet { border: none; box-shadow: none; padding: 0; max-width: 100%; }
            .actions-panel { display: none; } /* Hides interface components */
            .narrative-block { background: transparent; border-color: #000; }
        }
    </style>
</head>
<body>

<div class="actions-panel">
    <button onclick="window.print();" class="btn-print">🖨️ Print Clinical Report / Save as PDF</button>
</div>

<div class="report-sheet">
    <div class="report-header">
        <div>
            <div class="system-logo">JARVIS // HEALTH INFORMATICS SYSTEM</div>
            <p style="font-size: 12px; margin-top:5px; color: #718096;">Automated Clinical Portfolio Summary Document</p>
        </div>
        <div class="report-meta">
            <p><strong>System ID:</strong> REF-00<?php echo $data['id']; ?></p>
            <p><strong>Issued On:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
            <p><strong>User Account Link:</strong> <?php echo htmlspecialchars($data['username']); ?></p>
        </div>
    </div>

    <h2>1. Personal Analytics & Physical Baselines</h2>
    <div class="data-grid">
        <div class="data-row"><span class="data-label">Full Profile Name:</span><span class="data-value"><?php echo htmlspecialchars($data['full_name']); ?></span></div>
        <div class="data-row"><span class="data-label">Date of Birth:</span><span class="data-value"><?php echo htmlspecialchars($data['date_of_birth']); ?></span></div>
        <div class="data-row"><span class="data-label">Assigned Gender:</span><span class="data-value"><?php echo htmlspecialchars($data['gender']); ?></span></div>
        <div class="data-row"><span class="data-label">Blood Group Marker:</span><span class="data-value"><?php echo htmlspecialchars($data['blood_group']); ?></span></div>
        <div class="data-row"><span class="data-label">Reported Mass (Weight):</span><span class="data-value"><?php echo $data['weight_kg'] ? htmlspecialchars($data['weight_kg'])." kg" : "Unspecified"; ?></span></div>
        <div class="data-row"><span class="data-label">Reported Height:</span><span class="data-value"><?php echo $data['height_cm'] ? htmlspecialchars($data['height_cm'])." cm" : "Unspecified"; ?></span></div>
    </div>

    <h2>2. Medical Conditions History</h2>
    <div class="narrative-block"><?php echo !empty($data['medical_conditions']) ? htmlspecialchars($data['medical_conditions']) : "No active conditions declared."; ?></div>

    <h2>3. Allergies & Contraindications Data</h2>
    <div class="narrative-block"><?php echo !empty($data['allergies']) ? htmlspecialchars($data['allergies']) : "No documented structural allergies."; ?></div>

    <h2>4. Active Pharmacological Interventions (Medications)</h2>
    <div class="narrative-block"><?php echo !empty($data['current_medications']) ? htmlspecialchars($data['current_medications']) : "No pharmaceutical dependencies reported."; ?></div>

    <h2>5. Crisis Response & Emergency Contacts</h2>
    <div class="data-grid">
        <div class="data-row"><span class="data-label">Primary Contact Person:</span><span class="data-value"><?php echo !empty($data['emergency_contact_name']) ? htmlspecialchars($data['emergency_contact_name']) : "None Assigned"; ?></span></div>
        <div class="data-row"><span class="data-label">Primary Contact Phone:</span><span class="data-value"><?php echo !empty($data['emergency_contact_phone']) ? htmlspecialchars($data['emergency_contact_phone']) : "None Assigned"; ?></span></div>
    </div>

    <div class="footer-stamp">
        <p>This data docket is generated systematically by Jarvis Health Engine. The records presented represent user assertions and should be subjected to absolute medical verification prior to critical usage.</p>
        <p style="margin-top: 5px; font-weight: bold;">[ End of Document Security Record ]</p>
    </div>
</div>

</body>
</html>