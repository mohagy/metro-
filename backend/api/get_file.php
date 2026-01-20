<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendError('Method not allowed', 405);
}

$fileId = $_GET['id'] ?? null;
if (!$fileId) {
    sendError('File ID is required', 400);
}

$conn = getDBConnection();
$stmt = $conn->prepare("SELECT * FROM uploaded_files WHERE id = ?");
$stmt->bind_param('s', $fileId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    sendError('File not found', 404);
}

$file = $result->fetch_assoc();
$file['size_mb'] = round($file['size_bytes'] / 1024 / 1024, 2);

sendResponse(['success' => true, 'file' => $file]);

$stmt->close();
$conn->close();
?>

