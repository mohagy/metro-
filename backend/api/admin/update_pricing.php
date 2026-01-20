<?php
require_once 'admin_config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError('Method not allowed', 405);
}

// Verify admin session
verifyAdminSession();

$configKey = $_POST['config_key'] ?? null;
$configValue = $_POST['config_value'] ?? null;
$description = $_POST['description'] ?? null;

if (!$configKey || $configValue === null) {
    sendError('Config key and value are required', 400);
}

try {
    $conn = getDBConnection();
    
    // Check if config exists
    $checkStmt = $conn->prepare("SELECT id FROM pricing_config WHERE config_key = ?");
    $checkStmt->bind_param('s', $configKey);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows > 0) {
        // Update existing
        if ($description) {
            $stmt = $conn->prepare("UPDATE pricing_config SET config_value = ?, description = ? WHERE config_key = ?");
            $stmt->bind_param('dss', $configValue, $description, $configKey);
        } else {
            $stmt = $conn->prepare("UPDATE pricing_config SET config_value = ? WHERE config_key = ?");
            $stmt->bind_param('ds', $configValue, $configKey);
        }
    } else {
        // Create new
        $configId = uniqid('price_', true);
        if ($description) {
            $stmt = $conn->prepare("INSERT INTO pricing_config (id, config_key, config_value, description) VALUES (?, ?, ?, ?)");
            $stmt->bind_param('ssds', $configId, $configKey, $configValue, $description);
        } else {
            $stmt = $conn->prepare("INSERT INTO pricing_config (id, config_key, config_value) VALUES (?, ?, ?)");
            $stmt->bind_param('ssd', $configId, $configKey, $configValue);
        }
    }
    
    if (!$stmt->execute()) {
        $stmt->close();
        $conn->close();
        sendError('Failed to update pricing: ' . $stmt->error, 500);
    }
    
    $stmt->close();
    $conn->close();
    
    sendResponse([
        'success' => true,
        'message' => 'Pricing updated successfully'
    ]);
    
} catch (Exception $e) {
    sendError('Failed to update pricing: ' . $e->getMessage(), 500);
}
?>

