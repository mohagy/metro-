<?php
require_once 'admin_config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError('Method not allowed', 405);
}

// Verify admin session
verifyAdminSession();

$userId = $_POST['user_id'] ?? null;
$email = $_POST['email'] ?? null;
$name = $_POST['name'] ?? null;
$phone = $_POST['phone'] ?? null;

if (!$userId) {
    sendError('User ID is required', 400);
}

try {
    $conn = getDBConnection();
    
    // Build update query dynamically
    $updates = [];
    $params = [];
    $types = '';
    
    if ($email !== null) {
        $updates[] = "email = ?";
        $params[] = $email;
        $types .= 's';
    }
    
    if ($name !== null) {
        $updates[] = "name = ?";
        $params[] = $name;
        $types .= 's';
    }
    
    if ($phone !== null) {
        $updates[] = "phone = ?";
        $params[] = $phone;
        $types .= 's';
    }
    
    if (empty($updates)) {
        $conn->close();
        sendError('No fields to update', 400);
    }
    
    $params[] = $userId;
    $types .= 's';
    
    $query = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    
    if (!$stmt->execute()) {
        $stmt->close();
        $conn->close();
        sendError('Failed to update user: ' . $stmt->error, 500);
    }
    
    $stmt->close();
    $conn->close();
    
    sendResponse([
        'success' => true,
        'message' => 'User updated successfully'
    ]);
    
} catch (Exception $e) {
    sendError('Failed to update user: ' . $e->getMessage(), 500);
}
?>

