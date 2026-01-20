<?php
require_once 'admin_config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendError('Method not allowed', 405);
}

// Verify admin session
verifyAdminSession();

try {
    $conn = getDBConnection();
    
    $query = "SELECT o.*, u.email as user_email, u.name as user_name 
              FROM orders o 
              LEFT JOIN users u ON o.user_id = u.id 
              WHERE 1=1";
    
    $params = [];
    $types = '';
    
    // Search by order ID
    if (isset($_GET['order_id']) && !empty($_GET['order_id'])) {
        $query .= " AND o.order_id LIKE ?";
        $params[] = '%' . $_GET['order_id'] . '%';
        $types .= 's';
    }
    
    // Filter by status
    if (isset($_GET['status']) && !empty($_GET['status'])) {
        $query .= " AND o.status = ?";
        $params[] = $_GET['status'];
        $types .= 's';
    }
    
    // Filter by user ID
    if (isset($_GET['user_id']) && !empty($_GET['user_id'])) {
        $query .= " AND o.user_id = ?";
        $params[] = $_GET['user_id'];
        $types .= 's';
    }
    
    // Filter by date range
    if (isset($_GET['date_from']) && !empty($_GET['date_from'])) {
        $query .= " AND DATE(o.created_at) >= ?";
        $params[] = $_GET['date_from'];
        $types .= 's';
    }
    
    if (isset($_GET['date_to']) && !empty($_GET['date_to'])) {
        $query .= " AND DATE(o.created_at) <= ?";
        $params[] = $_GET['date_to'];
        $types .= 's';
    }
    
    $query .= " ORDER BY o.created_at DESC LIMIT 100";
    
    if (!empty($params)) {
        $stmt = $conn->prepare($query);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        $result = $conn->query($query);
    }
    
    $orders = [];
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
    
    if (isset($stmt)) {
        $stmt->close();
    }
    $conn->close();
    
    sendResponse([
        'success' => true,
        'orders' => $orders,
        'count' => count($orders)
    ]);
    
} catch (Exception $e) {
    sendError('Search failed: ' . $e->getMessage(), 500);
}
?>

