<?php
require_once 'admin_config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendError('Method not allowed', 405);
}

// Verify admin session
verifyAdminSession();

$orderId = $_GET['order_id'] ?? null;

if (!$orderId) {
    sendError('Order ID is required', 400);
}


try {
    $conn = getDBConnection();
    
    // First try MySQL
    $stmt = $conn->prepare("SELECT o.*, u.email as user_email, u.name as user_name, u.phone as user_phone 
                            FROM orders o 
                            LEFT JOIN users u ON o.user_id = u.id 
                            WHERE o.order_id = ?");
    $stmt->bind_param('s', $orderId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $order = null;
    $fromMySQL = false;
    
    if ($result->num_rows > 0) {
        $order = $result->fetch_assoc();
        $fromMySQL = true;
        $stmt->close();
    } else {
        // Not in MySQL, try Firebase using Admin SDK
        $stmt->close();
        
        try {
            require_once 'firebase_admin_setup.php';
            
            $firestoreOrder = FirebaseAdmin::getOrder($orderId);
            
            if ($firestoreOrder) {
                $order = FirebaseAdmin::formatOrderForAdmin($firestoreOrder);
                
                // Get user info
                $userStmt = $conn->prepare("SELECT email, name, phone FROM users WHERE id = ?");
                $userStmt->bind_param('s', $order['user_id']);
                $userStmt->execute();
                $userResult = $userStmt->get_result();
                if ($userResult->num_rows > 0) {
                    $user = $userResult->fetch_assoc();
                    $order['user_email'] = $user['email'];
                    $order['user_name'] = $user['name'];
                    $order['user_phone'] = $user['phone'];
                } else {
                    $order['user_email'] = $order['user_id'];
                    $order['user_name'] = 'User ' . substr($order['user_id'], 0, 8);
                    $order['user_phone'] = null;
                }
                $userStmt->close();
            }
        } catch (Exception $e) {
            // Firebase Admin SDK not set up or error occurred
            error_log('Firebase Admin SDK error: ' . $e->getMessage());
        }
    }
    
    if (!$order) {
        $conn->close();
        sendError('Order not found', 404);
    }
    
    // If order came from MySQL, get related data from MySQL
    if ($fromMySQL) {
        // Get order options
        $optionsStmt = $conn->prepare("SELECT * FROM order_options WHERE order_id = ?");
        $optionsStmt->bind_param('s', $order['id']);
        $optionsStmt->execute();
        $optionsResult = $optionsStmt->get_result();
        $order['print_options'] = $optionsResult->fetch_assoc();
        $optionsStmt->close();
        
        // Get order files
        $filesStmt = $conn->prepare("SELECT f.* FROM uploaded_files f 
                                     JOIN order_files of ON f.id = of.file_id 
                                     WHERE of.order_id = ?");
        $filesStmt->bind_param('s', $order['id']);
        $filesStmt->execute();
        $filesResult = $filesStmt->get_result();
        $order['files'] = [];
        while ($file = $filesResult->fetch_assoc()) {
            $order['files'][] = $file;
        }
        $filesStmt->close();
        
        // Get delivery address if exists
        $addrStmt = $conn->prepare("SELECT * FROM delivery_addresses WHERE order_id = ?");
        $addrStmt->bind_param('s', $order['id']);
        $addrStmt->execute();
        $addrResult = $addrStmt->get_result();
        if ($addrResult->num_rows > 0) {
            $order['delivery_address'] = $addrResult->fetch_assoc();
        }
        $addrStmt->close();
    }
    // If order came from Firebase, the data is already extracted above
    
    $conn->close();
    
    sendResponse([
        'success' => true,
        'order' => $order
    ]);
    
} catch (Exception $e) {
    sendError('Failed to fetch order: ' . $e->getMessage(), 500);
}
?>

