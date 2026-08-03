<?php
session_start();
require 'db_connection.php'; // Create this file to hold your PDO connection

if (!isset($_SESSION['user_id'])) {
    die("Access Denied");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['user_id'];
    
    // 1. Handle File Upload (Optional: Add security checks here)
    $avatarPath = "uploads/default.png"; // Set path logic here
    
    // 2. Prepare SQL Update
    $sql = "UPDATE user_profiles SET 
            full_name = :name, 
            email = :email, 
            phone = :phone, 
            emergency_name = :e_name, 
            emergency_relation = :e_rel, 
            emergency_phone = :e_phone 
            WHERE user_id = :user_id";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':name'   => $_POST['full_name'],
        ':email'  => $_POST['email'],
        ':phone'  => $_POST['phone'],
        ':e_name' => $_POST['emergency_name'],
        ':e_rel'  => $_POST['emergency_relation'],
        ':e_phone'=> $_POST['emergency_phone'],
        ':user_id'=> $userId
    ]);

    header("Location: profile.php?status=success");
    exit();
}
?>