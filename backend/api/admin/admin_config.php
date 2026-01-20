<?php
// Admin-specific configuration and helper functions
require_once '../config.php';

// Verify admin session and return admin info
function verifyAdminSession() {
    $token = $_POST['token'] ?? $_GET['token'] ?? null;
    
    if (!$token) {
        // Try to get from headers (getallheaders may not be available in all environments)
        $headers = function_exists('getallheaders') ? getallheaders() : [];
        if (empty($headers)) {
            // Fallback: manually parse headers
            foreach ($_SERVER as $key => $value) {
                if (strpos($key, 'HTTP_') === 0) {
                    $header = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
                    $headers[$header] = $value;
                }
            }
        }
        if (isset($headers['Authorization'])) {
            $token = str_replace('Bearer ', '', $headers['Authorization']);
        }
    }
    
    if (!$token) {
        sendError('Authentication required', 401);
    }
    
    try {
        $conn = getDBConnection();
        
        // Check if admin_sessions table exists
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
            sendError('Invalid or expired session', 401);
        }
        
        $admin = $result->fetch_assoc();
        $stmt->close();
        $conn->close();
        
        return $admin;
        
    } catch (Exception $e) {
        sendError('Session verification failed: ' . $e->getMessage(), 500);
    }
}

// Get Firebase API key from environment or config
function getFirebaseApiKey() {
    // You'll need to add your Firebase API key here
    // For now, return null - orders will be read from MySQL
    return null;
}
?>

