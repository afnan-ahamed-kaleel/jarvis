<?php
session_start();
require_once 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username'] ?? '');
    $email    = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    // 1. Basic empty field verification
    if (empty($username) || empty($email) || empty($password)) {
        $_SESSION['error'] = "All fields (Username, Gmail Address, and Password) are required.";
        header("Location: signup.php");
        exit();
    }

    // 2. Strict Gmail account verification
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strrchr($email, "@") !== '@gmail.com') {
        $_SESSION['error'] = "Only verified @gmail.com email addresses are permitted in this system.";
        header("Location: signup.php");
        exit();
    }

    // 3. Username format and length verification
    if (strlen($username) < 3 || !preg_match('/^[a-zA-Z0-9_.-]+$/', $username)) {
        $_SESSION['error'] = "Username must be at least 3 characters and contain only alphanumeric characters, dots, underscores, or hyphens.";
        header("Location: signup.php");
        exit();
    }

    // 4. Password minimum security check
    if (strlen($password) < 6) {
        $_SESSION['error'] = "Password must be at least 6 characters long.";
        header("Location: signup.php");
        exit();
    }

    // 5. Check if Username OR Gmail address is already registered
    $check = $conn->prepare("SELECT username, email FROM users WHERE username = ? OR email = ?");
    $check->bind_param("ss", $username, $email);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        while ($existing = $result->fetch_assoc()) {
            if (!empty($existing['email']) && strcasecmp($existing['email'], $email) === 0) {
                $_SESSION['error'] = "This Gmail address is already associated with an existing account.";
                header("Location: signup.php");
                exit();
            }
            if (strcasecmp($existing['username'], $username) === 0) {
                $_SESSION['error'] = "This username is already taken. Please choose another one.";
                header("Location: signup.php");
                exit();
            }
        }
        $_SESSION['error'] = "Account already exists with these credentials.";
        header("Location: signup.php");
        exit();
    }

    // 6. Securely hash the password using modern BCRYPT
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    // 7. Save new user account with Gmail to the MySQL database
    $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $email, $hashed_password);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Account created successfully with verified Gmail! You can now sign in.";
        header("Location: index.php");
    } else {
        $_SESSION['error'] = "A database error occurred during registration. Please try again later.";
        header("Location: signup.php");
    }
    exit();
} else {
    header("Location: signup.php");
    exit();
}
?>