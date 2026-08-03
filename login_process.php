<?php
session_start();
require_once 'db.php'; // Bring in our database connection with auto-migration support

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize user inputs to prevent malicious code injections
    $identifier = trim($_POST['username'] ?? ''); // Accepts either username or Gmail address
    $password = $_POST['password'] ?? '';

    if (empty($identifier) || empty($password)) {
        $_SESSION['error'] = "Please enter both your Username/Gmail and password.";
        header("Location: index.php");
        exit();
    }

    // Prepare a secure SQL statement to find the user by EITHER custom username OR Gmail address
    $stmt = $conn->prepare("SELECT id, username, email, password FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $identifier, $identifier);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Securely compare the typed password against the BCRYPT hashed password in DB
        if (password_verify($password, $user['password'])) {
            // Success! Store user data in the session cookie
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            if (!empty($user['email'])) {
                $_SESSION['email'] = $user['email'];
            }
            
            header("Location: jarvis.php");
            exit();
        }
    }
    
    // If we reach this point, either identifier or password was incorrect
    $_SESSION['error'] = "Invalid username/Gmail address or password.";
    header("Location: index.php");
    exit();
} else {
    header("Location: index.php");
    exit();
}
?>