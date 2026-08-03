<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    if (isset($_GET['action']) && $_GET['action'] === 'export_data') {
        die("Unauthorized access to archive engine");
    }
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit();
}

$user_id = intval($_SESSION['user_id']);
$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : '');
$inputData = json_decode(file_get_contents('php://input'), true);
if (!$action && isset($inputData['action'])) {
    $action = $inputData['action'];
}

// Ensure a user_settings record exists for this user
$check_set = $conn->prepare("SELECT id FROM user_settings WHERE user_id = ?");
$check_set->bind_param("i", $user_id);
$check_set->execute();
if ($check_set->get_result()->num_rows === 0) {
    $ins_set = $conn->prepare("INSERT INTO user_settings (user_id, theme, ai_voice_mode, email_notifications) VALUES (?, 'light', 'empathic', 1)");
    $ins_set->bind_param("i", $user_id);
    $ins_set->execute();
    $ins_set->close();
}
$check_set->close();

// Action: Export complete user database dump as downloadable JSON archive
if ($action === 'export_data') {
    $dump = [
        'archive_generated_at' => date('Y-m-d H:i:s T'),
        'account_id' => $user_id,
        'profile' => [],
        'system_settings' => [],
        'chat_history' => [],
        'thought_vault_logs' => [],
        'somatic_activities' => [],
        'emergency_contacts' => []
    ];

    // User profile data
    $u_res = $conn->query("SELECT username, email FROM users WHERE id = $user_id");
    if ($u_row = $u_res->fetch_assoc()) $dump['profile'] = $u_row;

    // System settings
    $s_res = $conn->query("SELECT theme, ai_voice_mode, email_notifications, updated_at FROM user_settings WHERE user_id = $user_id");
    if ($s_row = $s_res->fetch_assoc()) $dump['system_settings'] = $s_row;

    // Chat sessions and message transcripts
    $c_res = $conn->query("SELECT id, title, created_at, updated_at FROM chat_sessions WHERE user_id = $user_id ORDER BY created_at DESC");
    while ($c_row = $c_res->fetch_assoc()) {
        $sess_id = intval($c_row['id']);
        $c_row['messages'] = [];
        $m_res = $conn->query("SELECT sender, message, timestamp FROM chat_messages WHERE session_id = $sess_id ORDER BY timestamp ASC");
        while ($m_row = $m_res->fetch_assoc()) {
            $c_row['messages'][] = $m_row;
        }
        $dump['chat_history'][] = $c_row;
    }

    // Wellness Thought Vault logs
    $w_res = $conn->query("SELECT log_text, mood_tag, created_at FROM wellness_logs WHERE user_id = $user_id ORDER BY created_at DESC");
    while ($w_row = $w_res->fetch_assoc()) $dump['thought_vault_logs'][] = $w_row;

    // Somatic activities
    $a_res = $conn->query("SELECT activity_type, activity_name, duration_seconds, completed_at FROM wellness_activities WHERE user_id = $user_id ORDER BY completed_at DESC");
    while ($a_row = $a_res->fetch_assoc()) $dump['somatic_activities'][] = $a_row;

    // Emergency contacts
    $e_res = $conn->query("SELECT contact_name, phone_number, relationship, created_at FROM emergency_contacts WHERE user_id = $user_id");
    while ($e_row = $e_res->fetch_assoc()) $dump['emergency_contacts'][] = $e_row;

    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="jarvis_wellness_vault_' . date('Ymd_His') . '.json"');
    echo json_encode($dump, JSON_PRETTY_PRINT);
    exit();
}

header('Content-Type: application/json');

// Action: Fetch user profile details and settings from database
if ($action === 'get_settings') {
    $stmt_u = $conn->prepare("SELECT username, email FROM users WHERE id = ?");
    $stmt_u->bind_param("i", $user_id);
    $stmt_u->execute();
    $user_data = $stmt_u->get_result()->fetch_assoc();
    $stmt_u->close();

    $stmt_s = $conn->prepare("SELECT theme, ai_voice_mode, email_notifications FROM user_settings WHERE user_id = ?");
    $stmt_s->bind_param("i", $user_id);
    $stmt_s->execute();
    $set_data = $stmt_s->get_result()->fetch_assoc();
    $stmt_s->close();

    echo json_encode(['status' => 'success', 'user' => $user_data, 'settings' => $set_data]);
    exit();
}

// Action: Save theme and AI personality profile
if ($action === 'save_preferences') {
    $theme = isset($inputData['theme']) && $inputData['theme'] === 'dark' ? 'dark' : 'light';
    $ai_mode = isset($inputData['ai_voice_mode']) ? trim($inputData['ai_voice_mode']) : 'empathic';
    $notif = isset($inputData['email_notifications']) && $inputData['email_notifications'] ? 1 : 0;

    if (!in_array($ai_mode, ['empathic', 'clinical', 'concise'])) $ai_mode = 'empathic';

    $stmt = $conn->prepare("UPDATE user_settings SET theme = ?, ai_voice_mode = ?, email_notifications = ? WHERE user_id = ?");
    $stmt->bind_param("ssii", $theme, $ai_mode, $notif, $user_id);
    $stmt->execute();
    $stmt->close();

    echo json_encode(['status' => 'success', 'message' => 'Preferences synchronized directly to MySQL vault']);
    exit();
}

// Action: Update username or verified Gmail address
if ($action === 'update_profile') {
    $username = isset($inputData['username']) ? trim($inputData['username']) : '';
    $email = isset($inputData['email']) ? trim($inputData['email']) : '';

    if (empty($username)) {
        echo json_encode(['status' => 'error', 'message' => 'Username cannot be blank']);
        exit();
    }

    if (!empty($email) && (!filter_var($email, FILTER_VALIDATE_EMAIL) || !preg_match('/@gmail\.com$/i', $email))) {
        echo json_encode(['status' => 'error', 'message' => 'Please provide a valid @gmail.com email account address']);
        exit();
    }

    // Check if another account has taken this username or email
    $chk = $conn->prepare("SELECT id FROM users WHERE (username = ? OR (email = ? AND email != '')) AND id != ?");
    $chk->bind_param("ssi", $username, $email, $user_id);
    $chk->execute();
    if ($chk->get_result()->num_rows > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Username or Gmail account is already associated with another user']);
        exit();
    }
    $chk->close();

    $upd = $conn->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
    $upd->bind_param("ssi", $username, $email, $user_id);
    $upd->execute();
    $upd->close();

    $_SESSION['username'] = $username;

    echo json_encode(['status' => 'success', 'message' => 'Account profile updated successfully']);
    exit();
}

// Action: Change account password
if ($action === 'change_password') {
    $current = isset($inputData['current_password']) ? $inputData['current_password'] : '';
    $new_pass = isset($inputData['new_password']) ? $inputData['new_password'] : '';

    if (strlen($new_pass) < 6) {
        echo json_encode(['status' => 'error', 'message' => 'New security password must contain at least 6 characters']);
        exit();
    }

    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$row || !password_verify($current, $row['password'])) {
        echo json_encode(['status' => 'error', 'message' => 'Current password verification failed. Please check your spelling.']);
        exit();
    }

    $hashed = password_hash($new_pass, PASSWORD_BCRYPT);
    $upd = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $upd->bind_param("si", $hashed, $user_id);
    $upd->execute();
    $upd->close();

    echo json_encode(['status' => 'success', 'message' => 'Password recrypted and saved securely']);
    exit();
}

// Action: Clear chat conversation history
if ($action === 'clear_chat') {
    $res = $conn->query("SELECT id FROM chat_sessions WHERE user_id = $user_id");
    while ($r = $res->fetch_assoc()) {
        $s_id = intval($r['id']);
        $conn->query("DELETE FROM chat_messages WHERE session_id = $s_id");
    }
    $conn->query("DELETE FROM chat_sessions WHERE user_id = $user_id");
    echo json_encode(['status' => 'success', 'message' => 'All conversational chat history erased from MySQL server']);
    exit();
}

// Action: Clear Thought Vault logs and activities
if ($action === 'clear_wellness') {
    $conn->query("DELETE FROM wellness_logs WHERE user_id = $user_id");
    $conn->query("DELETE FROM wellness_activities WHERE user_id = $user_id");
    echo json_encode(['status' => 'success', 'message' => 'Thought Vault notes & somatic activity logs reset to clean baseline']);
    exit();
}

echo json_encode(['status' => 'error', 'message' => 'Invalid operation action command']);
?>
