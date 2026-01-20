<?php
require_once 'admin_config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendError('Method not allowed', 405);
}

// Verify admin session
verifyAdminSession();

$status = $_GET['status'] ?? 'Pending';

// Validate status
$allowedStatuses = ['Pending', 'Printing', 'Ready', 'Completed', 'Cancelled'];
if (!in_array($status, $allowedStatuses)) {
    sendError('Invalid status', 400);
}

try {
    $conn = getDBConnection();
    
    $stmt = $conn->prepare("SELECT o.*, u.email as user_email, u.name as user_name 
                            FROM orders o 
                            LEFT JOIN users u ON o.user_id = u.id 
                            WHERE o.status = ? 
                            ORDER BY o.created_at DESC");
    $stmt->bind_param('s', $status);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $orders = [];
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
    
    $stmt->close();
    $conn->close();
    
    sendResponse([
        'success' => true,
        'orders' => $orders,
        'status' => $status,
        'count' => count($orders)
    ]);
    
} catch (Exception $e) {
    sendError('Failed to fetch orders: ' . $e->getMessage(), 500);
}
?>

