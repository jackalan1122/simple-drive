<?php
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$file_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($file_id <= 0) {
    $_SESSION['message'] = 'Invalid file ID.';
    $_SESSION['message_type'] = 'error';
    header('Location: index.php');
    exit;
}

// Get file info from database
$conn = getDBConnection();
$stmt = $conn->prepare("SELECT * FROM files WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $file_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['message'] = 'File not found or access denied.';
    $_SESSION['message_type'] = 'error';
    header('Location: index.php');
    exit;
}

$file = $result->fetch_assoc();
$stmt->close();
$conn->close();

$file_path = UPLOAD_DIR . $file['filename'];

// Check if file exists
if (!file_exists($file_path)) {
    $_SESSION['message'] = 'File not found on server.';
    $_SESSION['message_type'] = 'error';
    header('Location: index.php');
    exit;
}

// Get the actual file size (in case database value is wrong)
$actual_file_size = filesize($file_path);

// Determine proper MIME type
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime_type = finfo_file($finfo, $file_path);
finfo_close($finfo);

// If mime type detection fails, use stored type or default
if (!$mime_type) {
    $mime_type = $file['file_type'] ?: 'application/octet-stream';
}

// Set headers for file download
header('Content-Description: File Transfer');
header('Content-Type: ' . $mime_type);
header('Content-Disposition: attachment; filename="' . $file['original_filename'] . '"');
header('Content-Length: ' . $actual_file_size);
header('Content-Transfer-Encoding: binary');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');
header('Expires: 0');

// Clear any output buffers
while (ob_get_level()) {
    ob_end_clean();
}

// Read and output file in chunks to handle large files
$handle = fopen($file_path, 'rb');
if ($handle) {
    while (!feof($handle)) {
        echo fread($handle, 8192);
        flush();
    }
    fclose($handle);
}
exit;
?>