<?php
require_once 'admin_config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendError('Method not allowed', 405);
}

// Verify admin session
verifyAdminSession();

try {
    $conn = getDBConnection();
    
    $result = $conn->query("SELECT * FROM binding_costs ORDER BY binding_type");
    
    $bindings = [];
    while ($row = $result->fetch_assoc()) {
        $bindings[] = $row;
    }
    
    $conn->close();
    
    sendResponse([
        'success' => true,
        'bindings' => $bindings
    ]);
    
} catch (Exception $e) {
    sendError('Failed to fetch binding costs: ' . $e->getMessage(), 500);
}
?>

