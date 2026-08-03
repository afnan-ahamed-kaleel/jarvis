<?php
session_start();
require_once 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit();
}

$user_id = intval($_SESSION['user_id']);
$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : 'get_telemetry');

if ($action === 'get_telemetry') {
    // 1. Fetch Mindfulness Minutes Mapped from somatic practices
    $stmt_m = $conn->prepare("SELECT COUNT(*) as total_exercises, COALESCE(SUM(duration_seconds), 0) as total_seconds FROM wellness_activities WHERE user_id = ?");
    $stmt_m->bind_param("i", $user_id);
    $stmt_m->execute();
    $res_m = $stmt_m->get_result()->fetch_assoc();
    $stmt_m->close();
    
    $mindfulness_minutes = round($res_m['total_seconds'] / 60);
    if ($res_m['total_seconds'] > 0 && $mindfulness_minutes == 0) $mindfulness_minutes = 1;

    // 2. Fetch Thought Vault reflections count & recent mood entries
    $stmt_v = $conn->prepare("SELECT id, mood_tag, created_at FROM wellness_logs WHERE user_id = ? ORDER BY created_at DESC LIMIT 30");
    $stmt_v->bind_param("i", $user_id);
    $stmt_v->execute();
    $res_v = $stmt_v->get_result();
    $journal_count = 0;
    $mood_counts = ['Happy' => 0, 'Okay' => 0, 'Sad' => 0, 'Stressed' => 0];
    
    while ($row = $res_v->fetch_assoc()) {
        $journal_count++;
        $m = $row['mood_tag'];
        if (isset($mood_counts[$m])) {
            $mood_counts[$m]++;
        } else {
            $mood_counts['Okay']++;
        }
    }
    $stmt_v->close();

    // Get total historical reflections if more than 30
    $total_reflections = $conn->query("SELECT COUNT(*) as cnt FROM wellness_logs WHERE user_id = $user_id")->fetch_assoc()['cnt'];

    // 3. Fetch AI Counseling Support Check-ins count
    $ai_sessions_cnt = $conn->query("SELECT COUNT(*) as cnt FROM chat_sessions WHERE user_id = $user_id")->fetch_assoc()['cnt'];

    // 4. Calculate Dynamic Wellness Harmony Score (0% - 100%)
    // Base stability score starts at 72% for engagement
    $harmony_score = 72;
    
    // Add points for somatic engagement (+0.5 per minute, max +14)
    $harmony_score += min(14, $mindfulness_minutes * 0.5);
    
    // Add points for consistent reflection logging (+2 per entry, max +10)
    $harmony_score += min(10, $total_reflections * 2);
    
    // Adjust based on emotional spectrum balance in recent entries
    if ($journal_count > 0) {
        $positive_ratio = ($mood_counts['Happy'] + $mood_counts['Okay']) / $journal_count;
        if ($positive_ratio >= 0.7) {
            $harmony_score += 4;
        } elseif (($mood_counts['Stressed'] + $mood_counts['Sad']) / $journal_count >= 0.6) {
            $harmony_score -= 5; // Slight depression in index to encourage self-care reset
        }
    }
    
    $harmony_score = max(50, min(98, round($harmony_score))); // Bound between 50% and 98% realistic clinical score

    // 5. Generate Dynamic Longitudinal Mood Trend Analysis
    $range = isset($_GET['range']) ? $_GET['range'] : '1weeks';
    
    $days_labels = [];
    $trend_happy = [];
    $trend_okay = [];
    $trend_sad = [];
    $trend_stressed = [];
    
    if ($range === 'last24hrs') {
        $now = time();
        $intervals = [];
        for ($i = 7; $i >= 0; $i--) {
            $start_time = $now - ($i + 1) * 3 * 3600;
            $end_time = $now - $i * 3 * 3600;
            $label = date('h A', $end_time);
            $days_labels[] = $label;
            $intervals[] = [
                'start' => date('Y-m-d H:i:s', $start_time),
                'end' => date('Y-m-d H:i:s', $end_time)
            ];
        }
        
        $start_limit = date('Y-m-d H:i:s', $now - 24 * 3600);
        $stmt_d = $conn->prepare("SELECT mood_tag, created_at FROM wellness_logs WHERE user_id = ? AND created_at >= ?");
        $stmt_d->bind_param("is", $user_id, $start_limit);
        $stmt_d->execute();
        $d_res = $stmt_d->get_result();
        
        $logs = [];
        while ($d_row = $d_res->fetch_assoc()) {
            $logs[] = $d_row;
        }
        $stmt_d->close();
        
        foreach ($intervals as $interval) {
            $counts = ['Happy' => 0, 'Okay' => 0, 'Sad' => 0, 'Stressed' => 0];
            foreach ($logs as $log) {
                if ($log['created_at'] >= $interval['start'] && $log['created_at'] < $interval['end']) {
                    if (isset($counts[$log['mood_tag']])) {
                        $counts[$log['mood_tag']]++;
                    }
                }
            }
            $trend_happy[] = $counts['Happy'];
            $trend_okay[] = $counts['Okay'];
            $trend_sad[] = $counts['Sad'];
            $trend_stressed[] = $counts['Stressed'];
        }
        
    } elseif ($range === '1weeks') {
        for ($i = 6; $i >= 0; $i--) {
            $target_date = date('Y-m-d', strtotime("-$i days"));
            $days_labels[] = date('D (M d)', strtotime($target_date));
        }
        
        $start_limit = date('Y-m-d', strtotime("-6 days")) . ' 00:00:00';
        $stmt_d = $conn->prepare("SELECT mood_tag, DATE(created_at) as dt, COUNT(*) as count FROM wellness_logs WHERE user_id = ? AND created_at >= ? GROUP BY mood_tag, dt");
        $stmt_d->bind_param("is", $user_id, $start_limit);
        $stmt_d->execute();
        $d_res = $stmt_d->get_result();
        
        $data_map = [];
        while ($d_row = $d_res->fetch_assoc()) {
            $data_map[$d_row['dt']][$d_row['mood_tag']] = intval($d_row['count']);
        }
        $stmt_d->close();
        
        for ($i = 6; $i >= 0; $i--) {
            $target_date = date('Y-m-d', strtotime("-$i days"));
            $trend_happy[] = isset($data_map[$target_date]['Happy']) ? $data_map[$target_date]['Happy'] : 0;
            $trend_okay[] = isset($data_map[$target_date]['Okay']) ? $data_map[$target_date]['Okay'] : 0;
            $trend_sad[] = isset($data_map[$target_date]['Sad']) ? $data_map[$target_date]['Sad'] : 0;
            $trend_stressed[] = isset($data_map[$target_date]['Stressed']) ? $data_map[$target_date]['Stressed'] : 0;
        }
        
    } elseif ($range === '1month') {
        for ($i = 29; $i >= 0; $i--) {
            $target_date = date('Y-m-d', strtotime("-$i days"));
            $days_labels[] = date('M d', strtotime($target_date));
        }
        
        $start_limit = date('Y-m-d', strtotime("-29 days")) . ' 00:00:00';
        $stmt_d = $conn->prepare("SELECT mood_tag, DATE(created_at) as dt, COUNT(*) as count FROM wellness_logs WHERE user_id = ? AND created_at >= ? GROUP BY mood_tag, dt");
        $stmt_d->bind_param("is", $user_id, $start_limit);
        $stmt_d->execute();
        $d_res = $stmt_d->get_result();
        
        $data_map = [];
        while ($d_row = $d_res->fetch_assoc()) {
            $data_map[$d_row['dt']][$d_row['mood_tag']] = intval($d_row['count']);
        }
        $stmt_d->close();
        
        for ($i = 29; $i >= 0; $i--) {
            $target_date = date('Y-m-d', strtotime("-$i days"));
            $trend_happy[] = isset($data_map[$target_date]['Happy']) ? $data_map[$target_date]['Happy'] : 0;
            $trend_okay[] = isset($data_map[$target_date]['Okay']) ? $data_map[$target_date]['Okay'] : 0;
            $trend_sad[] = isset($data_map[$target_date]['Sad']) ? $data_map[$target_date]['Sad'] : 0;
            $trend_stressed[] = isset($data_map[$target_date]['Stressed']) ? $data_map[$target_date]['Stressed'] : 0;
        }
        
    } elseif ($range === '6months') {
        for ($i = 5; $i >= 0; $i--) {
            $target_month = date('Y-m', strtotime("-$i months"));
            $days_labels[] = date('M Y', strtotime($target_month . '-01'));
        }
        
        $start_limit = date('Y-m-01', strtotime("-5 months")) . ' 00:00:00';
        $stmt_d = $conn->prepare("SELECT mood_tag, DATE_FORMAT(created_at, '%Y-%m') as ym, COUNT(*) as count FROM wellness_logs WHERE user_id = ? AND created_at >= ? GROUP BY mood_tag, ym");
        $stmt_d->bind_param("is", $user_id, $start_limit);
        $stmt_d->execute();
        $d_res = $stmt_d->get_result();
        
        $data_map = [];
        while ($d_row = $d_res->fetch_assoc()) {
            $data_map[$d_row['ym']][$d_row['mood_tag']] = intval($d_row['count']);
        }
        $stmt_d->close();
        
        for ($i = 5; $i >= 0; $i--) {
            $target_month = date('Y-m', strtotime("-$i months"));
            $trend_happy[] = isset($data_map[$target_month]['Happy']) ? $data_map[$target_month]['Happy'] : 0;
            $trend_okay[] = isset($data_map[$target_month]['Okay']) ? $data_map[$target_month]['Okay'] : 0;
            $trend_sad[] = isset($data_map[$target_month]['Sad']) ? $data_map[$target_month]['Sad'] : 0;
            $trend_stressed[] = isset($data_map[$target_month]['Stressed']) ? $data_map[$target_month]['Stressed'] : 0;
        }
        
    } elseif ($range === '1year') {
        for ($i = 11; $i >= 0; $i--) {
            $target_month = date('Y-m', strtotime("-$i months"));
            $days_labels[] = date('M Y', strtotime($target_month . '-01'));
        }
        
        $start_limit = date('Y-m-01', strtotime("-11 months")) . ' 00:00:00';
        $stmt_d = $conn->prepare("SELECT mood_tag, DATE_FORMAT(created_at, '%Y-%m') as ym, COUNT(*) as count FROM wellness_logs WHERE user_id = ? AND created_at >= ? GROUP BY mood_tag, ym");
        $stmt_d->bind_param("is", $user_id, $start_limit);
        $stmt_d->execute();
        $d_res = $stmt_d->get_result();
        
        $data_map = [];
        while ($d_row = $d_res->fetch_assoc()) {
            $data_map[$d_row['ym']][$d_row['mood_tag']] = intval($d_row['count']);
        }
        $stmt_d->close();
        
        for ($i = 11; $i >= 0; $i--) {
            $target_month = date('Y-m', strtotime("-$i months"));
            $trend_happy[] = isset($data_map[$target_month]['Happy']) ? $data_map[$target_month]['Happy'] : 0;
            $trend_okay[] = isset($data_map[$target_month]['Okay']) ? $data_map[$target_month]['Okay'] : 0;
            $trend_sad[] = isset($data_map[$target_month]['Sad']) ? $data_map[$target_month]['Sad'] : 0;
            $trend_stressed[] = isset($data_map[$target_month]['Stressed']) ? $data_map[$target_month]['Stressed'] : 0;
        }
        
    } elseif ($range === '11yr') {
        for ($i = 10; $i >= 0; $i--) {
            $target_year = date('Y', strtotime("-$i years"));
            $days_labels[] = $target_year;
        }
        
        $start_limit = date('Y-01-01', strtotime("-10 years")) . ' 00:00:00';
        $stmt_d = $conn->prepare("SELECT mood_tag, YEAR(created_at) as yr, COUNT(*) as count FROM wellness_logs WHERE user_id = ? AND created_at >= ? GROUP BY mood_tag, yr");
        $stmt_d->bind_param("is", $user_id, $start_limit);
        $stmt_d->execute();
        $d_res = $stmt_d->get_result();
        
        $data_map = [];
        while ($d_row = $d_res->fetch_assoc()) {
            $data_map[$d_row['yr']][$d_row['mood_tag']] = intval($d_row['count']);
        }
        $stmt_d->close();
        
        for ($i = 10; $i >= 0; $i--) {
            $target_year = date('Y', strtotime("-$i years"));
            $trend_happy[] = isset($data_map[$target_year]['Happy']) ? $data_map[$target_year]['Happy'] : 0;
            $trend_okay[] = isset($data_map[$target_year]['Okay']) ? $data_map[$target_year]['Okay'] : 0;
            $trend_sad[] = isset($data_map[$target_year]['Sad']) ? $data_map[$target_year]['Sad'] : 0;
            $trend_stressed[] = isset($data_map[$target_year]['Stressed']) ? $data_map[$target_year]['Stressed'] : 0;
        }
    }

    // Check if the trend array is completely bare (brand new account or no logs in selected range)
    $has_trend_data = (array_sum($trend_happy) + array_sum($trend_okay) + array_sum($trend_sad) + array_sum($trend_stressed)) > 0;
    if (!$has_trend_data && $total_reflections == 0) {
        // Provide a sample calm trajectory baseline for preview visualization
        $size = count($days_labels);
        $trend_okay = array_fill(0, $size, 0);
        $trend_happy = array_fill(0, $size, 0);
        for ($idx = 0; $idx < $size; $idx++) {
            $trend_okay[$idx] = ($idx % 3 === 0) ? 2 : 1;
            $trend_happy[$idx] = ($idx % 2 === 0) ? 1 : 0;
        }
    }

    // 6. Somatic & Practice Category Distribution (for Doughnut Chart)
    $stmt_act = $conn->prepare("SELECT activity_type, COUNT(*) as cnt FROM wellness_activities WHERE user_id = ? GROUP BY activity_type");
    $stmt_act->bind_param("i", $user_id);
    $stmt_act->execute();
    $act_res = $stmt_act->get_result();
    
    $act_dist = [
        'Breathing & Resets' => 0,
        'Somatic Meditation' => 0,
        'Thought Vault Notes' => intval($total_reflections),
        'AI Check-in Sessions' => intval($ai_sessions_cnt)
    ];

    while ($a_row = $act_res->fetch_assoc()) {
        if (stripos($a_row['activity_type'], 'Breath') !== false || stripos($a_row['activity_type'], 'Timer') !== false) {
            $act_dist['Breathing & Resets'] += intval($a_row['cnt']);
        } else {
            $act_dist['Somatic Meditation'] += intval($a_row['cnt']);
        }
    }
    $stmt_act->close();

    // Ensure doughnut chart has visual presence even on fresh logins
    if (array_sum(array_values($act_dist)) === 0) {
        $act_dist = [
            'Breathing & Resets' => 2,
            'Somatic Meditation' => 2,
            'Thought Vault Notes' => 3,
            'AI Check-in Sessions' => 2
        ];
    }

    $response = [
        'status' => 'success',
        'kpi' => [
            'harmony_score' => $harmony_score,
            'mindfulness_minutes' => intval($mindfulness_minutes),
            'total_reflections' => intval($total_reflections),
            'ai_checkins' => intval($ai_sessions_cnt)
        ],
        'charts' => [
            'trend_labels' => $days_labels,
            'trend' => [
                'Happy' => $trend_happy,
                'Okay' => $trend_okay,
                'Sad' => $trend_sad,
                'Stressed' => $trend_stressed
            ],
            'distribution_labels' => array_keys($act_dist),
            'distribution_values' => array_values($act_dist)
        ],
        'recent_moods' => $mood_counts
    ];

    echo json_encode($response);
    exit();
}

echo json_encode(['status' => 'error', 'message' => 'Invalid telemetry parameter']);
?>
