<?php
require_once 'admin_config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendError('Method not allowed', 405);
}

// Verify admin session
verifyAdminSession();

try {
    $conn = getDBConnection();
    
    $result = $conn->query("SELECT * FROM paper_size_multipliers ORDER BY paper_size");
    
    $multipliers = [];
    while ($row = $result->fetch_assoc()) {
        $multipliers[] = $row;
    }
    
    $conn->close();
    
    sendResponse([
        'success' => true,
        'multipliers' => $multipliers
    ]);
    
} catch (Exception $e) {
    sendError('Failed to fetch paper multipliers: ' . $e->getMessage(), 500);
}
?>

