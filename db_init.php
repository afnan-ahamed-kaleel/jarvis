<?php
// Automatic database table architecture initialization for Jarvis AI & Wellness Platform
if (!isset($conn) || $conn->connect_error) {
    return;
}

// 1. Chat Sessions table for conversation threading
$conn->query("CREATE TABLE IF NOT EXISTS chat_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) DEFAULT 'New Conversation',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

// 2. Chat Messages table for real-time history storage
$conn->query("CREATE TABLE IF NOT EXISTS chat_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id INT NOT NULL,
    sender ENUM('user', 'bot') NOT NULL,
    message TEXT NOT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (session_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

// 3. Wellness Logs table for Thought Vault reflections and mood tracking
$conn->query("CREATE TABLE IF NOT EXISTS wellness_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    log_text TEXT NOT NULL,
    mood_tag VARCHAR(50) NOT NULL DEFAULT 'Okay',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

// 4. Wellness Activities table for completed somatic practices and drills
$conn->query("CREATE TABLE IF NOT EXISTS wellness_activities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    activity_type VARCHAR(50) NOT NULL,
    activity_name VARCHAR(150) NOT NULL,
    duration_seconds INT DEFAULT 0,
    completed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

// 5. Emergency Contacts table for user SOS response circles
$conn->query("CREATE TABLE IF NOT EXISTS emergency_contacts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    contact_name VARCHAR(150) NOT NULL,
    phone_number VARCHAR(50) NOT NULL,
    relationship VARCHAR(50) DEFAULT 'Trusted Contact',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

// 6. SOS Distress Logs table for emergency geolocated alerts
$conn->query("CREATE TABLE IF NOT EXISTS sos_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    latitude DECIMAL(10, 8) NULL,
    longitude DECIMAL(11, 8) NULL,
    status VARCHAR(50) DEFAULT 'TRIGGERED',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

// 7. User Settings table for persistent theme, AI personality profiles, & notifications
$conn->query("CREATE TABLE IF NOT EXISTS user_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    theme VARCHAR(20) DEFAULT 'light',
    ai_voice_mode VARCHAR(50) DEFAULT 'empathic',
    email_notifications TINYINT(1) DEFAULT 1,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
?>
