<?php
$host = "localhost";
$db_user = "root";
$db_pass = ""; // XAMPP default is empty
$db_name = "jarvis";

// Connect to MySQL
$conn = new mysqli($host, $db_user, $db_pass, $db_name);

// Check if the connection failed
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Auto-migrate database schema to ensure 'email' column exists for Gmail accounts
$col_check = $conn->query("SHOW COLUMNS FROM users LIKE 'email'");
if ($col_check && $col_check->num_rows === 0) {
    $conn->query("ALTER TABLE users ADD COLUMN email VARCHAR(255) NULL AFTER username");
}

// Auto-initialize all application data tables (chat, wellness, SOS, settings)
require_once __DIR__ . '/db_init.php';
?>