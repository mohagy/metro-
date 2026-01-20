<?php
/**
 * Quick test to verify Firebase connection
 */
require_once 'firebase_admin_setup.php';

header('Content-Type: text/plain');

try {
    echo "Testing Firebase Admin SDK connection...\n\n";
    
    $orders = FirebaseAdmin::getAllOrders();
    
    echo "SUCCESS: Connected to Firebase!\n";
    echo "Found " . count($orders) . " order(s)\n\n";
    
    if (count($orders) > 0) {
        echo "Sample order:\n";
        $order = $orders[0];
        echo "  Order ID: " . ($order['order_id'] ?? 'N/A') . "\n";
        echo "  Status: " . ($order['status'] ?? 'N/A') . "\n";
        echo "  User ID: " . ($order['user_id'] ?? 'N/A') . "\n";
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString();
}

