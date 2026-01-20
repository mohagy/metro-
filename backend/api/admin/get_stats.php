<?php
require_once 'admin_config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendError('Method not allowed', 405);
}

// Verify admin session
verifyAdminSession();

try {
    $conn = getDBConnection();
    
    // Total orders
    $totalOrdersResult = $conn->query("SELECT COUNT(*) as total FROM orders");
    $totalOrders = $totalOrdersResult->fetch_assoc()['total'];
    
    // Orders by status
    $statusResult = $conn->query("SELECT status, COUNT(*) as count FROM orders GROUP BY status");
    $ordersByStatus = [];
    while ($row = $statusResult->fetch_assoc()) {
        $ordersByStatus[$row['status']] = (int)$row['count'];
    }
    
    // Total revenue
    $revenueResult = $conn->query("SELECT SUM(total_cost) as total FROM orders WHERE status != 'Cancelled'");
    $totalRevenue = $revenueResult->fetch_assoc()['total'] ?? 0;
    
    // Total users
    $usersResult = $conn->query("SELECT COUNT(*) as total FROM users");
    $totalUsers = $usersResult->fetch_assoc()['total'];
    
    // Recent orders (last 7 days)
    $recentOrdersResult = $conn->query("SELECT COUNT(*) as total FROM orders WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
    $recentOrders = $recentOrdersResult->fetch_assoc()['total'];
    
    // Average order value
    $avgOrderResult = $conn->query("SELECT AVG(total_cost) as avg FROM orders WHERE status != 'Cancelled'");
    $avgOrderValue = $avgOrderResult->fetch_assoc()['avg'] ?? 0;
    
    // Orders today
    $todayOrdersResult = $conn->query("SELECT COUNT(*) as total FROM orders WHERE DATE(created_at) = CURDATE()");
    $todayOrders = $todayOrdersResult->fetch_assoc()['total'];
    
    // Revenue today
    $todayRevenueResult = $conn->query("SELECT SUM(total_cost) as total FROM orders WHERE DATE(created_at) = CURDATE() AND status != 'Cancelled'");
    $todayRevenue = $todayRevenueResult->fetch_assoc()['total'] ?? 0;
    
    $conn->close();
    
    sendResponse([
        'success' => true,
        'stats' => [
            'total_orders' => (int)$totalOrders,
            'total_users' => (int)$totalUsers,
            'total_revenue' => (float)$totalRevenue,
            'average_order_value' => (float)$avgOrderValue,
            'recent_orders' => (int)$recentOrders,
            'today_orders' => (int)$todayOrders,
            'today_revenue' => (float)$todayRevenue,
            'orders_by_status' => $ordersByStatus
        ]
    ]);
    
} catch (Exception $e) {
    sendError('Failed to fetch statistics: ' . $e->getMessage(), 500);
}
?>

