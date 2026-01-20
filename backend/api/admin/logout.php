<?php
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError('Method not allowed', 405);
}

$token = $_POST['token'] ?? $_SERVER['HTTP_AUTHORIZATION'] ?? null;

if (!$token) {
    $headers = getallheaders();
    if (isset($headers['Authorization'])) {
        $token = str_replace('Bearer ', '', $headers['Authorization']);
    }
}

if (!$token) {
    sendError('Token required', 400);
}

try {
    $conn = getDBConnection();
    
    $stmt = $conn->prepare("DELETE FROM admin_sessions WHERE token = ?");
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $stmt->close();
    $conn->close();
    
    sendResponse(['success' => true, 'message' => 'Logged out successfully']);
    
} catch (Exception $e) {
    sendError('Logout failed: ' . $e->getMessage(), 500);
}
?>

