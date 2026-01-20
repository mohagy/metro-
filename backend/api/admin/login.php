<?php
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError('Method not allowed', 405);
}

$username = $_POST['username'] ?? null;
$password = $_POST['password'] ?? null;

if (!$username || !$password) {
    sendError('Username and password are required', 400);
}

try {
    $conn = getDBConnection();
    
    $stmt = $conn->prepare("SELECT id, username, password_hash, email, role FROM admin_users WHERE username = ?");
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $stmt->close();
        $conn->close();
        sendError('Invalid username or password', 401);
    }
    
    $admin = $result->fetch_assoc();
    
    if (!password_verify($password, $admin['password_hash'])) {
        $stmt->close();
        $conn->close();
        sendError('Invalid username or password', 401);
    }
    
    // Update last login
    $updateStmt = $conn->prepare("UPDATE admin_users SET last_login = NOW() WHERE id = ?");
    $updateStmt->bind_param('s', $admin['id']);
    $updateStmt->execute();
    $updateStmt->close();
    
    // Generate session token (simple approach - in production use JWT)
    $sessionToken = bin2hex(random_bytes(32));
    
    // Check if admin_sessions table exists, if not create it
    $tableCheck = $conn->query("SHOW TABLES LIKE 'admin_sessions'");
    if ($tableCheck->num_rows == 0) {
        $createTable = "CREATE TABLE IF NOT EXISTS admin_sessions (
            admin_id VARCHAR(50) PRIMARY KEY,
            token VARCHAR(64) UNIQUE NOT NULL,
            expires_at TIMESTAMP NOT NULL,
            FOREIGN KEY (admin_id) REFERENCES admin_users(id) ON DELETE CASCADE
        )";
        $conn->query($createTable);
    }
    
    // Store session in database (or use Redis/Memcached in production)
    $sessionStmt = $conn->prepare("INSERT INTO admin_sessions (admin_id, token, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 24 HOUR)) ON DUPLICATE KEY UPDATE token = ?, expires_at = DATE_ADD(NOW(), INTERVAL 24 HOUR)");
    $sessionStmt->bind_param('sss', $admin['id'], $sessionToken, $sessionToken);
    $sessionStmt->execute();
    $sessionStmt->close();
    
    $stmt->close();
    $conn->close();
    
    sendResponse([
        'success' => true,
        'token' => $sessionToken,
        'admin' => [
            'id' => $admin['id'],
            'username' => $admin['username'],
            'email' => $admin['email'],
            'role' => $admin['role']
        ]
    ]);
    
} catch (Exception $e) {
    sendError('Login failed: ' . $e->getMessage(), 500);
}
?>

