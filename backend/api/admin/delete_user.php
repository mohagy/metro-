<?php
require_once 'admin_config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError('Method not allowed', 405);
}

// Verify admin session
verifyAdminSession();

$userId = $_POST['user_id'] ?? null;

if (!$userId) {
    sendError('User ID is required', 400);
}

try {
    $conn = getDBConnection();
    
    // Check if user has orders
    $checkStmt = $conn->prepare("SELECT COUNT(*) as order_count FROM orders WHERE user_id = ?");
    $checkStmt->bind_param('s', $userId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    $orderCount = $checkResult->fetch_assoc()['order_count'];
    $checkStmt->close();
    
    if ($orderCount > 0) {
        $conn->close();
        sendError('Cannot delete user with existing orders. Delete orders first or cancel them.', 400);
    }
    
    // Delete user (cascade will handle addresses)
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param('s', $userId);
    
    if (!$stmt->execute()) {
        $stmt->close();
        $conn->close();
        sendError('Failed to delete user: ' . $stmt->error, 500);
    }
    
    $stmt->close();
    $conn->close();
    
    sendResponse([
        'success' => true,
        'message' => 'User deleted successfully'
    ]);
    
} catch (Exception $e) {
    sendError('Failed to delete user: ' . $e->getMessage(), 500);
}
?>

