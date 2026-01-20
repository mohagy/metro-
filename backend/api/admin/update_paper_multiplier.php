<?php
require_once 'admin_config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError('Method not allowed', 405);
}

// Verify admin session
verifyAdminSession();

$paperSize = $_POST['paper_size'] ?? null;
$multiplier = $_POST['multiplier'] ?? null;

if (!$paperSize || $multiplier === null) {
    sendError('Paper size and multiplier are required', 400);
}

try {
    $conn = getDBConnection();
    
    // Check if exists
    $checkStmt = $conn->prepare("SELECT id FROM paper_size_multipliers WHERE paper_size = ?");
    $checkStmt->bind_param('s', $paperSize);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows > 0) {
        // Update
        $stmt = $conn->prepare("UPDATE paper_size_multipliers SET multiplier = ? WHERE paper_size = ?");
        $stmt->bind_param('ds', $multiplier, $paperSize);
    } else {
        // Create
        $paperId = uniqid('paper_', true);
        $stmt = $conn->prepare("INSERT INTO paper_size_multipliers (id, paper_size, multiplier) VALUES (?, ?, ?)");
        $stmt->bind_param('ssd', $paperId, $paperSize, $multiplier);
    }
    
    if (!$stmt->execute()) {
        $stmt->close();
        $conn->close();
        sendError('Failed to update paper multiplier: ' . $stmt->error, 500);
    }
    
    $stmt->close();
    $conn->close();
    
    sendResponse([
        'success' => true,
        'message' => 'Paper multiplier updated successfully'
    ]);
    
} catch (Exception $e) {
    sendError('Failed to update paper multiplier: ' . $e->getMessage(), 500);
}
?>

