<?php
require_once 'admin_config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendError('Method not allowed', 405);
}

// Verify admin session
verifyAdminSession();

try {
    $conn = getDBConnection();
    
    $result = $conn->query("SELECT * FROM pricing_config ORDER BY config_key");
    
    $pricing = [];
    while ($row = $result->fetch_assoc()) {
        $pricing[] = $row;
    }
    
    $conn->close();
    
    sendResponse([
        'success' => true,
        'pricing' => $pricing
    ]);
    
} catch (Exception $e) {
    sendError('Failed to fetch pricing: ' . $e->getMessage(), 500);
}
?>

