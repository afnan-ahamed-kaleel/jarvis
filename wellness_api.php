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

// Action: Sync legacy browser LocalStorage journal records into MySQL
if ($action === 'sync_legacy') {
    $entries = isset($inputData['entries']) ? $inputData['entries'] : [];
    $synced = 0;
    if (is_array($entries) && !empty($entries)) {
        foreach ($entries as $entry) {
            $text = isset($entry['logText']) ? trim($entry['logText']) : '';
            $mood = isset($entry['moodTag']) ? trim($entry['moodTag']) : 'Okay';
            if (!empty($text)) {
                // Prevent duplicate insertions if identical text already exists for this user
                $chk_dup = $conn->prepare("SELECT id FROM wellness_logs WHERE user_id = ? AND log_text = ?");
                $chk_dup->bind_param("is", $user_id, $text);
                $chk_dup->execute();
                if ($chk_dup->get_result()->num_rows === 0) {
                    $ins = $conn->prepare("INSERT INTO wellness_logs (user_id, log_text, mood_tag) VALUES (?, ?, ?)");
                    $ins->bind_param("iss", $user_id, $text, $mood);
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

// Action: Save a new Thought Vault reflection & mood tag
if ($action === 'save_log') {
    $text = isset($inputData['logText']) ? trim($inputData['logText']) : (isset($_POST['logText']) ? trim($_POST['logText']) : '');
    $mood = isset($inputData['moodTag']) ? trim($inputData['moodTag']) : (isset($_POST['moodTag']) ? trim($_POST['moodTag']) : 'Okay');
    
    if (empty($text)) {
        echo json_encode(['status' => 'error', 'message' => 'Log text cannot be empty']);
        exit();
    }
    
    $stmt = $conn->prepare("INSERT INTO wellness_logs (user_id, log_text, mood_tag) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $user_id, $text, $mood);
    $stmt->execute();
    $insert_id = $stmt->insert_id;
    $stmt->close();
    
    echo json_encode(['status' => 'success', 'id' => $insert_id]);
    exit();
}

// Action: Retrieve historical journal logs and compute exact percentage distributions
if ($action === 'get_logs') {
    $stmt = $conn->prepare("SELECT id, log_text, mood_tag, created_at FROM wellness_logs WHERE user_id = ? ORDER BY created_at DESC LIMIT 100");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $logs = [];
    $counts = ['Happy' => 0, 'Okay' => 0, 'Sad' => 0, 'Stressed' => 0];
    $total = 0;
    
    while ($row = $res->fetch_assoc()) {
        $logs[] = [
            'id' => $row['id'],
            'timestamp' => date('M j, g:i a', strtotime($row['created_at'])),
            'logText' => $row['log_text'],
            'moodTag' => $row['mood_tag']
        ];
        $mood_key = $row['mood_tag'];
        if (isset($counts[$mood_key])) {
            $counts[$mood_key]++;
        } else {
            $counts['Okay']++;
        }
        $total++;
    }
    $stmt->close();
    
    $distribution = [
        'Happy' => $total > 0 ? round(($counts['Happy'] / $total) * 100) : 0,
        'Okay' => $total > 0 ? round(($counts['Okay'] / $total) * 100) : 0,
        'Sad' => $total > 0 ? round(($counts['Sad'] / $total) * 100) : 0,
        'Stressed' => $total > 0 ? round(($counts['Stressed'] / $total) * 100) : 0,
        'total' => $total
    ];
    
    echo json_encode(['status' => 'success', 'logs' => $logs, 'distribution' => $distribution]);
    exit();
}

// Action: Record completed somatic practices, meditation, or workouts from resources
if ($action === 'log_activity') {
    $type = isset($inputData['type']) ? trim($inputData['type']) : 'General';
    $name = isset($inputData['name']) ? trim($inputData['name']) : 'Completed Practice';
    $duration = isset($inputData['duration']) ? intval($inputData['duration']) : 0;
    
    $stmt = $conn->prepare("INSERT INTO wellness_activities (user_id, activity_type, activity_name, duration_seconds) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("issi", $user_id, $type, $name, $duration);
    $stmt->execute();
    $stmt->close();
    
    echo json_encode(['status' => 'success']);
    exit();
}

// Action: Retrieve cumulative wellness activity statistics and minutes mapped
if ($action === 'get_metrics') {
    $stmt = $conn->prepare("SELECT COUNT(*) as total_count, COALESCE(SUM(duration_seconds), 0) as total_duration FROM wellness_activities WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $metrics = $res->fetch_assoc();
    $stmt->close();
    
    $total_minutes = round($metrics['total_duration'] / 60);
    if ($metrics['total_duration'] > 0 && $total_minutes == 0) {
        $total_minutes = 1; // Display at least 1 min if any seconds are completed
    }
    
    echo json_encode([
        'status' => 'success',
        'total_completed' => intval($metrics['total_count']),
        'total_minutes' => intval($total_minutes)
    ]);
    exit();
}

echo json_encode(['status' => 'error', 'message' => 'Invalid action specification']);
?>
