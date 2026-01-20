<?php
require_once 'admin_config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendError('Method not allowed', 405);
}

// Verify admin session
verifyAdminSession();

$userId = $_GET['user_id'] ?? null;

if (!$userId) {
    sendError('User ID is required', 400);
}

try {
    $conn = getDBConnection();
    
    // Get user
    $stmt = $conn->prepare("SELECT id, email, name, phone, created_at FROM users WHERE id = ?");
    $stmt->bind_param('s', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $stmt->close();
        $conn->close();
        sendError('User not found', 404);
    }
    
    $user = $result->fetch_assoc();
    $stmt->close();
    
    // Get user addresses
    $addrStmt = $conn->prepare("SELECT * FROM user_addresses WHERE user_id = ?");
    $addrStmt->bind_param('s', $userId);
    $addrStmt->execute();
    $addrResult = $addrStmt->get_result();
    $user['addresses'] = [];
    while ($addr = $addrResult->fetch_assoc()) {
        $user['addresses'][] = $addr;
    }
    $addrStmt->close();
    
    // Get user orders
    $ordersStmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
    $ordersStmt->bind_param('s', $userId);
    $ordersStmt->execute();
    $ordersResult = $ordersStmt->get_result();
    $user['orders'] = [];
    while ($order = $ordersResult->fetch_assoc()) {
        $user['orders'][] = $order;
    }
    $ordersStmt->close();
    
    $conn->close();
    
    sendResponse([
        'success' => true,
        'user' => $user
    ]);
    
} catch (Exception $e) {
    sendError('Failed to fetch user: ' . $e->getMessage(), 500);
}
?>

