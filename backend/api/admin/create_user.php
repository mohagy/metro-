<?php
require_once 'admin_config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError('Method not allowed', 405);
}

// Verify admin session
verifyAdminSession();

$email = $_POST['email'] ?? null;
$name = $_POST['name'] ?? null;
$phone = $_POST['phone'] ?? null;
$password = $_POST['password'] ?? null;

if (!$email || !$name) {
    sendError('Email and name are required', 400);
}

try {
    $conn = getDBConnection();
    
    // Check if email already exists
    $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $checkStmt->bind_param('s', $email);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows > 0) {
        $checkStmt->close();
        $conn->close();
        sendError('Email already exists', 400);
    }
    $checkStmt->close();
    
    // Generate user ID
    $userId = uniqid('user_', true);
    
    // Hash password if provided
    $passwordHash = null;
    if ($password) {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    }
    
    // Insert user
    if ($passwordHash) {
        $stmt = $conn->prepare("INSERT INTO users (id, email, name, phone, password_hash) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param('sssss', $userId, $email, $name, $phone, $passwordHash);
    } else {
        $stmt = $conn->prepare("INSERT INTO users (id, email, name, phone) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('ssss', $userId, $email, $name, $phone);
    }
    
    if (!$stmt->execute()) {
        $stmt->close();
        $conn->close();
        sendError('Failed to create user: ' . $stmt->error, 500);
    }
    
    $stmt->close();
    $conn->close();
    
    sendResponse([
        'success' => true,
        'message' => 'User created successfully',
        'user_id' => $userId
    ]);
    
} catch (Exception $e) {
    sendError('Failed to create user: ' . $e->getMessage(), 500);
}
?>

