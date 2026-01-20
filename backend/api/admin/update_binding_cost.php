<?php
require_once 'admin_config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError('Method not allowed', 405);
}

// Verify admin session
verifyAdminSession();

$bindingType = $_POST['binding_type'] ?? null;
$cost = $_POST['cost'] ?? null;

if (!$bindingType || $cost === null) {
    sendError('Binding type and cost are required', 400);
}

try {
    $conn = getDBConnection();
    
    // Check if exists
    $checkStmt = $conn->prepare("SELECT id FROM binding_costs WHERE binding_type = ?");
    $checkStmt->bind_param('s', $bindingType);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows > 0) {
        // Update
        $stmt = $conn->prepare("UPDATE binding_costs SET cost = ? WHERE binding_type = ?");
        $stmt->bind_param('ds', $cost, $bindingType);
    } else {
        // Create
        $bindingId = uniqid('bind_', true);
        $stmt = $conn->prepare("INSERT INTO binding_costs (id, binding_type, cost) VALUES (?, ?, ?)");
        $stmt->bind_param('ssd', $bindingId, $bindingType, $cost);
    }
    
    if (!$stmt->execute()) {
        $stmt->close();
        $conn->close();
        sendError('Failed to update binding cost: ' . $stmt->error, 500);
    }
    
    $stmt->close();
    $conn->close();
    
    sendResponse([
        'success' => true,
        'message' => 'Binding cost updated successfully'
    ]);
    
} catch (Exception $e) {
    sendError('Failed to update binding cost: ' . $e->getMessage(), 500);
}
?>

