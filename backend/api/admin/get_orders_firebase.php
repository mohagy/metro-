<?php
require_once 'admin_config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendError('Method not allowed', 405);
}

// Verify admin session
verifyAdminSession();

// Firebase configuration
$firebaseProjectId = 'printing-service-app-8949';
$firebaseApiKey = 'AIzaSyC3Er5dy-dszTP3swA7_frXMV_M5iwAyZg';

try {
    // Get all orders from Firebase Firestore using REST API
    $url = "https://firestore.googleapis.com/v1/projects/{$firebaseProjectId}/databases/(default)/documents/orders";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200) {
        sendError('Failed to fetch orders from Firebase: HTTP ' . $httpCode, 500);
    }
    
    $data = json_decode($response, true);
    
    if (!isset($data['documents'])) {
        sendResponse([
            'success' => true,
            'orders' => [],
            'pagination' => [
                'page' => 1,
                'limit' => 50,
                'total' => 0,
                'pages' => 0
            ]
        ]);
    }
    
    // Convert Firebase documents to order format
    $orders = [];
    $conn = getDBConnection();
    
    foreach ($data['documents'] as $doc) {
        $docId = basename($doc['name']);
        $fields = $doc['fields'];
        
        // Extract order data from Firebase format
        $order = [
            'id' => $docId,
            'order_id' => $docId,
            'user_id' => extractFirestoreValue($fields, 'userId'),
            'status' => extractFirestoreValue($fields, 'status', 'Pending'),
            'total_cost' => (float)extractFirestoreValue($fields, 'totalCost', 0),
            'delivery_option' => extractFirestoreValue($fields, 'deliveryOption', ''),
            'qr_code' => extractFirestoreValue($fields, 'qrCode', ''),
            'created_at' => extractFirestoreTimestamp($fields, 'createdAt'),
            'estimated_ready' => extractFirestoreTimestamp($fields, 'estimatedReady'),
            'completed_at' => extractFirestoreTimestamp($fields, 'completedAt'),
        ];
        
        // Get user info from MySQL if available
        $userStmt = $conn->prepare("SELECT email, name FROM users WHERE id = ?");
        $userStmt->bind_param('s', $order['user_id']);
        $userStmt->execute();
        $userResult = $userStmt->get_result();
        if ($userResult->num_rows > 0) {
            $user = $userResult->fetch_assoc();
            $order['user_email'] = $user['email'];
            $order['user_name'] = $user['name'];
        }
        $userStmt->close();
        
        // Extract print options if available
        if (isset($fields['printOption'])) {
            $printOption = $fields['printOption']['mapValue']['fields'] ?? [];
            $order['print_options'] = [
                'paper_size' => extractFirestoreValue($printOption, 'paperSize'),
                'color' => extractFirestoreValue($printOption, 'color'),
                'quantity' => (int)extractFirestoreValue($printOption, 'quantity', 1),
                'sides' => extractFirestoreValue($printOption, 'sides'),
                'orientation' => extractFirestoreValue($printOption, 'orientation'),
                'binding' => extractFirestoreValue($printOption, 'binding'),
            ];
        }
        
        $orders[] = $order;
    }
    
    $conn->close();
    
    // Sort by created_at descending
    usort($orders, function($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });
    
    sendResponse([
        'success' => true,
        'orders' => $orders,
        'pagination' => [
            'page' => 1,
            'limit' => 50,
            'total' => count($orders),
            'pages' => 1
        ]
    ]);
    
} catch (Exception $e) {
    sendError('Failed to fetch orders: ' . $e->getMessage(), 500);
}

// Helper function to extract Firestore values
function extractFirestoreValue($fields, $key, $default = null) {
    if (!isset($fields[$key])) {
        return $default;
    }
    
    $value = $fields[$key];
    
    // Handle different Firestore value types
    if (isset($value['stringValue'])) {
        return $value['stringValue'];
    } elseif (isset($value['integerValue'])) {
        return (int)$value['integerValue'];
    } elseif (isset($value['doubleValue'])) {
        return (float)$value['doubleValue'];
    } elseif (isset($value['booleanValue'])) {
        return $value['booleanValue'];
    } elseif (isset($value['timestampValue'])) {
        return $value['timestampValue'];
    }
    
    return $default;
}

// Helper function to extract Firestore timestamps
function extractFirestoreTimestamp($fields, $key) {
    $value = extractFirestoreValue($fields, $key);
    if (!$value) {
        return null;
    }
    
    // Firebase timestamps are in format: "2026-01-20T02:22:19.499Z"
    if (is_string($value)) {
        try {
            $date = new DateTime($value);
            return $date->format('Y-m-d H:i:s');
        } catch (Exception $e) {
            return null;
        }
    }
    
    return null;
}
?>

