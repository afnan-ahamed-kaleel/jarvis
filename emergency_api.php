<?php
session_start();
require_once 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit();
}

$user_id = intval($_SESSION['user_id']);
$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : '');
$inputData = json_decode(file_get_contents('php://input'), true);
if (!$action && isset($inputData['action'])) {
    $action = $inputData['action'];
}

// Action: Sync legacy LocalStorage emergency contacts to MySQL
if ($action === 'sync_legacy') {
    $contacts = isset($inputData['contacts']) ? $inputData['contacts'] : [];
    $synced = 0;
    if (is_array($contacts) && !empty($contacts)) {
        foreach ($contacts as $contact) {
            $name = isset($contact['name']) ? trim($contact['name']) : '';
            $phone = isset($contact['phone']) ? trim($contact['phone']) : '';
            $relation = isset($contact['relation']) ? trim($contact['relation']) : 'Trusted Contact';
            if (!empty($name) && !empty($phone)) {
                $chk_dup = $conn->prepare("SELECT id FROM emergency_contacts WHERE user_id = ? AND phone_number = ?");
                $chk_dup->bind_param("is", $user_id, $phone);
                $chk_dup->execute();
                if ($chk_dup->get_result()->num_rows === 0) {
                    $ins = $conn->prepare("INSERT INTO emergency_contacts (user_id, contact_name, phone_number, relationship) VALUES (?, ?, ?, ?)");
                    $ins->bind_param("isss", $user_id, $name, $phone, $relation);
                    $ins->execute();
                    $ins->close();
                    $synced++;
                }
                $chk_dup->close();
            }
        }
    }
    echo json_encode(['status' => 'success', 'synced' => $synced]);
    exit();
}

// Action: Retrieve saved emergency contacts from MySQL database
if ($action === 'get_contacts') {
    $stmt = $conn->prepare("SELECT id, contact_name, phone_number, relationship FROM emergency_contacts WHERE user_id = ? ORDER BY id DESC");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $contacts = [];
    while ($row = $res->fetch_assoc()) {
        $contacts[] = [
            'id' => $row['id'],
            'name' => $row['contact_name'],
            'phone' => $row['phone_number'],
            'relation' => $row['relationship']
        ];
    }
    $stmt->close();
    echo json_encode(['status' => 'success', 'contacts' => $contacts]);
    exit();
}

// Action: Add a new emergency contact to database
if ($action === 'add_contact') {
    $name = isset($inputData['name']) ? trim($inputData['name']) : (isset($_POST['name']) ? trim($_POST['name']) : '');
    $phone = isset($inputData['phone']) ? trim($inputData['phone']) : (isset($_POST['phone']) ? trim($_POST['phone']) : '');
    $relation = isset($inputData['relation']) ? trim($inputData['relation']) : (isset($_POST['relation']) ? trim($_POST['relation']) : 'Trusted Contact');

    if (empty($name) || empty($phone)) {
        echo json_encode(['status' => 'error', 'message' => 'Name and phone number are required']);
        exit();
    }

    $stmt = $conn->prepare("INSERT INTO emergency_contacts (user_id, contact_name, phone_number, relationship) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $user_id, $name, $phone, $relation);
    $stmt->execute();
    $insert_id = $stmt->insert_id;
    $stmt->close();

    echo json_encode(['status' => 'success', 'id' => $insert_id]);
    exit();
}

// Action: Delete an emergency contact
if ($action === 'delete_contact') {
    $contact_id = isset($inputData['id']) ? intval($inputData['id']) : (isset($_POST['id']) ? intval($_POST['id']) : 0);
    if ($contact_id > 0) {
        $stmt = $conn->prepare("DELETE FROM emergency_contacts WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $contact_id, $user_id);
        $stmt->execute();
        $stmt->close();
    }
    echo json_encode(['status' => 'success']);
    exit();
}

// Action: Log geolocated SOS distress event into sos_logs table
if ($action === 'log_sos') {
    $lat = isset($inputData['lat']) ? floatval($inputData['lat']) : null;
    $lng = isset($inputData['lng']) ? floatval($inputData['lng']) : null;

    $stmt = $conn->prepare("INSERT INTO sos_logs (user_id, latitude, longitude, status) VALUES (?, ?, ?, 'TRIGGERED')");
    $stmt->bind_param("idd", $user_id, $lat, $lng);
    $stmt->execute();
    $log_id = $stmt->insert_id;
    $stmt->close();

    echo json_encode(['status' => 'success', 'log_id' => $log_id]);
    exit();
}

echo json_encode(['status' => 'error', 'message' => 'Invalid action specification']);
?>
