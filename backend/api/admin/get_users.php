<?php
require_once 'admin_config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendError('Method not allowed', 405);
}

// Verify admin session
verifyAdminSession();

try {
    $conn = getDBConnection();
    
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
    $offset = ($page - 1) * $limit;
    
    // Get total count
    $countResult = $conn->query("SELECT COUNT(*) as total FROM users");
    $total = $countResult->fetch_assoc()['total'];
    
    // Get users
    $query = "SELECT id, email, name, phone, created_at FROM users ORDER BY created_at DESC LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ii', $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $users = [];
    while ($row = $result->fetch_assoc()) {
        // Get order count for each user
        $orderCountStmt = $conn->prepare("SELECT COUNT(*) as order_count FROM orders WHERE user_id = ?");
        $orderCountStmt->bind_param('s', $row['id']);
        $orderCountStmt->execute();
        $orderCountResult = $orderCountStmt->get_result();
        $row['order_count'] = $orderCountResult->fetch_assoc()['order_count'];
        $orderCountStmt->close();
        
        $users[] = $row;
    }
    
    $stmt->close();
    $conn->close();
    
    sendResponse([
        'success' => true,
        'users' => $users,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'pages' => ceil($total / $limit)
        ]
    ]);
    
} catch (Exception $e) {
    sendError('Failed to fetch users: ' . $e->getMessage(), 500);
}
?>

