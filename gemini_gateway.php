<?php
session_start();
require_once 'db.php'; // Bring in database connection & table initialization

// Force error reporting during development
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Security Check: Only logged-in users can access the AI endpoint
if (!isset($_SESSION['user_id'])) {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

$user_id = intval($_SESSION['user_id']);

// Accept inbound JSON payloads from our javascript frontend
$inputData = json_decode(file_get_contents('php://input'), true);
$userText = isset($inputData['message']) ? trim($inputData['message']) : '';
$session_id = isset($inputData['session_id']) && !empty($inputData['session_id']) ? intval($inputData['session_id']) : 0;

if (empty($userText)) {
    echo json_encode(['error' => 'Message cannot be empty']);
    exit();
}

// 1. Check if user has a custom AI Voice Tone configuration in user_settings
$ai_voice_mode = 'empathic';
$stmt_settings = $conn->prepare("SELECT ai_voice_mode FROM user_settings WHERE user_id = ?");
$stmt_settings->bind_param("i", $user_id);
$stmt_settings->execute();
$res_set = $stmt_settings->get_result();
if ($row_set = $res_set->fetch_assoc()) {
    if (!empty($row_set['ai_voice_mode'])) {
        $ai_voice_mode = $row_set['ai_voice_mode'];
    }
}
$stmt_settings->close();

// 2. Manage Chat Session thread in database
if ($session_id === 0) {
    // Create a new session header using the first 35 characters of the initial message
    $title = mb_substr($userText, 0, 35, 'UTF-8');
    if (mb_strlen($userText, 'UTF-8') > 35) {
        $title .= "...";
    }
    $stmt_sess = $conn->prepare("INSERT INTO chat_sessions (user_id, title) VALUES (?, ?)");
    $stmt_sess->bind_param("is", $user_id, $title);
    $stmt_sess->execute();
    $session_id = $stmt_sess->insert_id;
    $stmt_sess->close();
} else {
    // Verify that the session belongs to this user before writing to it
    $chk_sess = $conn->prepare("SELECT id FROM chat_sessions WHERE id = ? AND user_id = ?");
    $chk_sess->bind_param("ii", $session_id, $user_id);
    $chk_sess->execute();
    if ($chk_sess->get_result()->num_rows === 0) {
        echo json_encode(['error' => 'Invalid session context']);
        exit();
    }
    $chk_sess->close();
}

// 3. Save User's incoming message to chat_messages table
$stmt_user_msg = $conn->prepare("INSERT INTO chat_messages (session_id, sender, message) VALUES (?, 'user', ?)");
$stmt_user_msg->bind_param("is", $session_id, $userText);
$stmt_user_msg->execute();
$stmt_user_msg->close();

// ⚠️ Google AI Studio Key
$apiKey = "AQ.Ab8RN6LNpn1GLAeEDsmCoauJPb6Txf7F732eyRit8y0DAW09rg"; 

// Use the stable and highly responsive gemini-1.5-flash model endpoint
$apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent?key=" . $apiKey;

// Structure the prompt matching the user's custom counseling tone setting
$systemPrompt = "Context: You are Jarvis, a supportive AI mental health assistant. Be empathetic, use helpful emojis, and keep replies under 3 sentences.";
if ($ai_voice_mode === 'clinical') {
    $systemPrompt = "Context: You are Jarvis, an objective and evidence-based AI mental health assistant. Be calm, reassuring, structured, and keep replies concise under 3 sentences.";
} elseif ($ai_voice_mode === 'concise') {
    $systemPrompt = "Context: You are Jarvis, a direct and pragmatic AI companion. Provide straightforward actionable guidance or short exercises in under 2 sentences.";
}

$payload = [
    "contents" => [
        [
            "parts" => [
                ["text" => $systemPrompt . "\nUser input: " . $userText]
            ]
        ]
    ]
];

// Dispatching Payload to Google Gateway via cURL
$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

// Disable SSL certificate verification for local XAMPP environment
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

// Execute request
$response = curl_exec($ch);

if (curl_errno($ch)) {
    $err_msg = 'Gateway transport execution failure: ' . curl_error($ch);
    curl_close($ch);
    echo json_encode(['error' => $err_msg]);
    exit();
}
curl_close($ch);

// 4. Parse response and store AI reply into database
$decoded = json_decode($response, true);
$aiReply = "I understood your input and am maintaining our supportive log. How else can I assist you right now?";

if (isset($decoded['candidates'][0]['content']['parts'][0]['text'])) {
    $aiReply = trim($decoded['candidates'][0]['content']['parts'][0]['text']);
}

// Record bot response in chat_messages table
$stmt_bot_msg = $conn->prepare("INSERT INTO chat_messages (session_id, sender, message) VALUES (?, 'bot', ?)");
$stmt_bot_msg->bind_param("is", $session_id, $aiReply);
$stmt_bot_msg->execute();
$stmt_bot_msg->close();

// Update chat_sessions timestamp
$stmt_upd = $conn->prepare("UPDATE chat_sessions SET updated_at = NOW() WHERE id = ?");
$stmt_upd->bind_param("i", $session_id);
$stmt_upd->execute();
$stmt_upd->close();

// Forward the response along with active session_id back to frontend script
header('Content-Type: application/json');
if (is_array($decoded)) {
    $decoded['session_id'] = $session_id;
    echo json_encode($decoded);
} else {
    echo json_encode(['session_id' => $session_id, 'raw_response' => $response]);
}
?>