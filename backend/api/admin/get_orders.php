<?php
require_once 'admin_config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendError('Method not allowed', 405);
}

// Verify admin session
verifyAdminSession();


try {
    $conn = getDBConnection();
    
    // First, try to get orders from MySQL
    $countResult = $conn->query("SELECT COUNT(*) as total FROM orders");
    $total = $countResult->fetch_assoc()['total'];
    
    $orders = [];
    
    // If MySQL has orders, use them
    if ($total > 0) {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
        $offset = ($page - 1) * $limit;
        
        // Get orders with user information
        $query = "SELECT o.*, u.email as user_email, u.name as user_name 
                  FROM orders o 
                  LEFT JOIN users u ON o.user_id = u.id 
                  ORDER BY o.created_at DESC 
                  LIMIT ? OFFSET ?";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param('ii', $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            // Get order options
            $optionsStmt = $conn->prepare("SELECT * FROM order_options WHERE order_id = ?");
            $optionsStmt->bind_param('s', $row['id']);
            $optionsStmt->execute();
            $optionsResult = $optionsStmt->get_result();
            $row['print_options'] = $optionsResult->fetch_assoc();
            $optionsStmt->close();
            
            // Get delivery address if exists
            $addrStmt = $conn->prepare("SELECT * FROM delivery_addresses WHERE order_id = ?");
            $addrStmt->bind_param('s', $row['id']);
            $addrStmt->execute();
            $addrResult = $addrStmt->get_result();
            if ($addrResult->num_rows > 0) {
                $row['delivery_address'] = $addrResult->fetch_assoc();
            }
            $addrStmt->close();
            
            $orders[] = $row;
        }
        
        $stmt->close();
    } else {
        // MySQL is empty, try to read from Firebase Firestore using Admin SDK
        try {
            require_once 'firebase_admin_setup.php';
            
            $firestoreOrders = FirebaseAdmin::getAllOrders();
            
            foreach ($firestoreOrders as $firestoreOrder) {
                $order = FirebaseAdmin::formatOrderForAdmin($firestoreOrder);
                
                // Get user info from MySQL if available
                $userStmt = $conn->prepare("SELECT email, name FROM users WHERE id = ?");
                $userStmt->bind_param('s', $order['user_id']);
                $userStmt->execute();
                $userResult = $userStmt->get_result();
                if ($userResult->num_rows > 0) {
                    $user = $userResult->fetch_assoc();
                    $order['user_email'] = $user['email'];
                    $order['user_name'] = $user['name'];
                } else {
                    $order['user_email'] = $order['user_id'];
                    $order['user_name'] = 'User ' . substr($order['user_id'], 0, 8);
                }
                $userStmt->close();
                
                $orders[] = $order;
            }
            
            // Sort by created_at descending
            usort($orders, function($a, $b) {
                $timeA = strtotime($a['created_at'] ?? '1970-01-01');
                $timeB = strtotime($b['created_at'] ?? '1970-01-01');
                return $timeB - $timeA;
            });
            
            $total = count($orders);
            
        } catch (Exception $e) {
            // Firebase Admin SDK not set up or error occurred
            error_log('Firebase Admin SDK error: ' . $e->getMessage());
            // Continue with empty orders array
        }
    }
    
    $conn->close();
    
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
    
    sendResponse([
        'success' => true,
        'orders' => $orders,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'pages' => ceil($total / $limit)
        ]
    ]);
    
} catch (Exception $e) {
    sendError('Failed to fetch orders: ' . $e->getMessage(), 500);
}
?>

