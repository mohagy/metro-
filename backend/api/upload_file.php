<?php
// Disable error display and ensure JSON output
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Set JSON content type early
header('Content-Type: application/json');

require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError('Method not allowed', 405);
}

// Get user ID from headers or request
$userId = $_POST['user_id'] ?? $_SERVER['HTTP_USER_ID'] ?? null;
if (!$userId) {
    sendError('User ID is required', 400);
}

// Ensure user exists in database (create if doesn't exist)
// This is needed because we use Firebase Auth but MySQL has foreign key constraints
try {
    $conn = getDBConnection();
    
    // Check if user exists
    $userCheck = $conn->prepare("SELECT id FROM users WHERE id = ?");
    $userCheck->bind_param('s', $userId);
    $userCheck->execute();
    $result = $userCheck->get_result();
    
    if ($result->num_rows == 0) {
        // User doesn't exist, create a basic user record
        // Note: We don't have email/name from the upload request, so we'll create minimal record
        $createUser = $conn->prepare("INSERT INTO users (id, email, name, created_at) VALUES (?, ?, ?, NOW())");
        $defaultEmail = $userId . '@firebase.local'; // Placeholder email
        $defaultName = 'User ' . substr($userId, 0, 8); // Placeholder name
        $createUser->bind_param('sss', $userId, $defaultEmail, $defaultName);
        
        if (!$createUser->execute()) {
            $userCheck->close();
            $conn->close();
            sendError('Failed to create user record: ' . $createUser->error, 500);
        }
        $createUser->close();
    }
    
    $userCheck->close();
    $conn->close();
} catch (Exception $e) {
    sendError('Database error while checking user: ' . $e->getMessage(), 500);
}

// Get order ID if provided
$orderId = $_POST['order_id'] ?? null;

// Check if file was uploaded
if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    sendError('No file uploaded or upload error', 400);
}

$file = $_FILES['file'];
$fileName = $file['name'];
$fileSize = $file['size'];
$tmpName = $file['tmp_name'];
$fileError = $file['error'];

// Validate file size
if ($fileSize > MAX_FILE_SIZE) {
    sendError('File size exceeds maximum allowed size of ' . (MAX_FILE_SIZE / 1024 / 1024) . 'MB', 400);
}

// Get file extension
$fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

// Validate file type
if (!in_array($fileExt, ALLOWED_TYPES)) {
    sendError('File type not allowed. Allowed types: ' . implode(', ', ALLOWED_TYPES), 400);
}

// Generate unique file ID
$fileId = uniqid('file_', true);
$safeFileName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $fileName);
$newFileName = $fileId . '_' . $safeFileName;

// Create user directory if it doesn't exist
$userDir = UPLOAD_DIR . $userId . '/';
if (!file_exists($userDir)) {
    mkdir($userDir, 0777, true);
}

// Create order directory if order ID is provided
if ($orderId) {
    $orderDir = $userDir . $orderId . '/';
    if (!file_exists($orderDir)) {
        mkdir($orderDir, 0777, true);
    }
    $uploadPath = $orderDir . $newFileName;
} else {
    $uploadPath = $userDir . $newFileName;
}

// Move uploaded file
if (!move_uploaded_file($tmpName, $uploadPath)) {
    sendError('Failed to save uploaded file', 500);
}

// Generate file URL
$fileUrl = '/backend/uploads/' . $userId . '/' . ($orderId ? $orderId . '/' : '') . $newFileName;

// Estimate page count (simplified - you can improve this with PDF library)
$pageCount = null;
if ($fileExt === 'pdf') {
    // Rough estimate: 1 page per 50KB for PDFs
    $pageCount = ceil($fileSize / (50 * 1024));
} elseif (in_array($fileExt, ['jpg', 'jpeg', 'png'])) {
    $pageCount = 1;
}

// Save file info to database
try {
    // Create new database connection
    $conn = getDBConnection();
    
    // Check if table exists, if not create it
    $tableCheck = $conn->query("SHOW TABLES LIKE 'uploaded_files'");
    if ($tableCheck->num_rows == 0) {
        // Table doesn't exist, try to create it
        $createTable = "
        CREATE TABLE IF NOT EXISTS uploaded_files (
            id VARCHAR(50) PRIMARY KEY,
            user_id VARCHAR(50) NOT NULL,
            order_id VARCHAR(50),
            name VARCHAR(255) NOT NULL,
            original_name VARCHAR(255) NOT NULL,
            file_path VARCHAR(500) NOT NULL,
            file_url VARCHAR(500) NOT NULL,
            size_bytes BIGINT NOT NULL,
            file_type VARCHAR(50) NOT NULL,
            page_count INT,
            uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        if (!$conn->query($createTable)) {
            unlink($uploadPath);
            sendError('Database table does not exist and could not be created: ' . $conn->error, 500);
        }
    }
    
    $stmt = $conn->prepare("
        INSERT INTO uploaded_files 
        (id, user_id, order_id, name, original_name, file_path, file_url, size_bytes, file_type, page_count)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    if (!$stmt) {
        unlink($uploadPath);
        sendError('Failed to prepare database statement: ' . $conn->error, 500);
    }
    
    $stmt->bind_param(
        'sssssssiss',
        $fileId,
        $userId,
        $orderId,
        $newFileName,
        $fileName,
        $uploadPath,
        $fileUrl,
        $fileSize,
        $fileExt,
        $pageCount
    );
    
    if (!$stmt->execute()) {
        unlink($uploadPath); // Delete uploaded file if database insert fails
        sendError('Failed to save file information: ' . $stmt->error, 500);
    }
    
    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    if (isset($uploadPath) && file_exists($uploadPath)) {
        unlink($uploadPath);
    }
    sendError('Database error: ' . $e->getMessage(), 500);
}

// Return file information
sendResponse([
    'success' => true,
    'file' => [
        'id' => $fileId,
        'name' => $fileName,
        'original_name' => $fileName,
        'file_url' => $fileUrl,
        'size_bytes' => $fileSize,
        'size_mb' => round($fileSize / 1024 / 1024, 2),
        'file_type' => $fileExt,
        'page_count' => $pageCount,
        'uploaded_at' => date('c')
    ]
]);
?>

