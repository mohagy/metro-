<?php
require_once 'admin_config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError('Method not allowed', 405);
}

// Verify admin session
verifyAdminSession();

$orderId = $_POST['order_id'] ?? null;
$status = $_POST['status'] ?? null;

if (!$orderId || !$status) {
    sendError('Order ID and status are required', 400);
}

// Validate status
$allowedStatuses = ['Pending', 'Printing', 'Ready', 'Completed', 'Cancelled'];
if (!in_array($status, $allowedStatuses)) {
    sendError('Invalid status. Allowed: ' . implode(', ', $allowedStatuses), 400);
}

try {
    $conn = getDBConnection();
    
    // Update in MySQL
    $updateData = ['status' => $status];
    if ($status === 'Completed') {
        $updateData['completed_at'] = date('Y-m-d H:i:s');
    }
    
    $stmt = $conn->prepare("UPDATE orders SET status = ?" . 
        ($status === 'Completed' ? ", completed_at = NOW()" : "") . 
        " WHERE order_id = ?");
    
    if ($status === 'Completed') {
        $stmt->bind_param('ss', $status, $orderId);
    } else {
        $stmt->bind_param('ss', $status, $orderId);
    }
    
    if (!$stmt->execute()) {
        $conn->close();
        sendError('Failed to update order status: ' . $stmt->error, 500);
    }
    
    $stmt->close();
    
    // Also update Firebase Firestore if order exists there
    try {
        require_once 'firebase_admin_setup.php';
        FirebaseAdmin::updateOrderStatus($orderId, $status);
    } catch (Exception $e) {
        // Log error but don't fail - MySQL update already succeeded
        error_log('Failed to update Firebase: ' . $e->getMessage());
    }
    
    $conn->close();
    
    sendResponse([
        'success' => true,
        'message' => 'Order status updated successfully',
        'order_id' => $orderId,
        'new_status' => $status
    ]);
    
} catch (Exception $e) {
    sendError('Failed to update order status: ' . $e->getMessage(), 500);
}
?>

