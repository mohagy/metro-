<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError('Method not allowed', 405);
}

// Get file ID from request
$input = json_decode(file_get_contents('php://input'), true);
$fileId = $input['id'] ?? $_POST['id'] ?? null;

if (!$fileId) {
    sendError('File ID is required', 400);
}

$conn = getDBConnection();
$stmt = $conn->prepare("SELECT file_path FROM uploaded_files WHERE id = ?");
$stmt->bind_param('s', $fileId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    sendError('File not found', 404);
}

$file = $result->fetch_assoc();
$filePath = $file['file_path'];

// Delete from database
$stmt = $conn->prepare("DELETE FROM uploaded_files WHERE id = ?");
$stmt->bind_param('s', $fileId);
$stmt->execute();

// Delete physical file
if (file_exists($filePath)) {
    unlink($filePath);
}

sendResponse(['success' => true, 'message' => 'File deleted successfully']);

$stmt->close();
$conn->close();
?>

