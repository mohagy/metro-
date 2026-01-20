<?php
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendError('Method not allowed', 405);
}

$token = $_POST['token'] ?? $_SERVER['HTTP_AUTHORIZATION'] ?? null;

if (!$token) {
    // Try to get from Authorization header
    $headers = getallheaders();
    if (isset($headers['Authorization'])) {
        $token = str_replace('Bearer ', '', $headers['Authorization']);
    }
}

if (!$token) {
    sendError('Token required', 401);
}

try {
    $conn = getDBConnection();
    
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
    
    $stmt = $conn->prepare("SELECT a.id, a.username, a.email, a.role FROM admin_sessions s 
                            JOIN admin_users a ON s.admin_id = a.id 
                            WHERE s.token = ? AND s.expires_at > NOW()");
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $stmt->close();
        $conn->close();
        sendError('Invalid or expired token', 401);
    }
    
    $admin = $result->fetch_assoc();
    $stmt->close();
    $conn->close();
    
    sendResponse([
        'success' => true,
        'admin' => [
            'id' => $admin['id'],
            'username' => $admin['username'],
            'email' => $admin['email'],
            'role' => $admin['role']
        ]
    ]);
    
} catch (Exception $e) {
    sendError('Verification failed: ' . $e->getMessage(), 500);
}
?>

