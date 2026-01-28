<?php
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit;
}

$user_id = $_SESSION['user_id'];
$file_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($file_id <= 0) {
    http_response_code(400);
    exit;
}

// Get file info from database
$conn = getDBConnection();
$stmt = $conn->prepare("SELECT * FROM files WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $file_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    exit;
}

$file = $result->fetch_assoc();
$stmt->close();
$conn->close();

$file_path = UPLOAD_DIR . $file['filename'];

// Check if file exists
if (!file_exists($file_path)) {
    http_response_code(404);
    exit;
}

$file_size = filesize($file_path);
$file_name = $file['original_filename'];

// Determine MIME type
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime_type = finfo_file($finfo, $file_path);
finfo_close($finfo);

if (!$mime_type) {
    $mime_type = $file['file_type'] ?: 'application/octet-stream';
}

// Support for range requests (for video seeking)
$range = isset($_SERVER['HTTP_RANGE']) ? $_SERVER['HTTP_RANGE'] : null;

// Clear any output buffers
while (ob_get_level()) {
    ob_end_clean();
}

if ($range) {
    // Parse range header
    list($param, $range) = explode('=', $range);
    
    if (strtolower(trim($param)) != 'bytes') {
        http_response_code(400);
        exit;
    }
    
    // Get range values
    $range = explode(',', $range);
    $range = explode('-', $range[0]);
    
    $start = intval($range[0]);
    $end = isset($range[1]) && is_numeric($range[1]) ? intval($range[1]) : $file_size - 1;
    
    // Validate range
    if ($start > $end || $start > $file_size - 1 || $end >= $file_size) {
        http_response_code(416);
        header("Content-Range: bytes */$file_size");
        exit;
    }
    
    $length = $end - $start + 1;
    
    // Send partial content headers
    http_response_code(206);
    header("Content-Type: $mime_type");
    header("Content-Length: $length");
    header("Content-Range: bytes $start-$end/$file_size");
    header("Accept-Ranges: bytes");
    
    // Open file and seek to start position
    $fp = fopen($file_path, 'rb');
    fseek($fp, $start);
    
    // Output the requested range
    $buffer = 8192;
    $bytes_remaining = $length;
    
    while ($bytes_remaining > 0 && !feof($fp)) {
        $bytes_to_read = min($buffer, $bytes_remaining);
        echo fread($fp, $bytes_to_read);
        flush();
        $bytes_remaining -= $bytes_to_read;
    }
    
    fclose($fp);
} else {
    // Send full file
    header("Content-Type: $mime_type");
    header("Content-Length: $file_size");
    header("Accept-Ranges: bytes");
    header("Cache-Control: public, max-age=3600");
    
    // Output file in chunks
    $fp = fopen($file_path, 'rb');
    
    while (!feof($fp)) {
        echo fread($fp, 8192);
        flush();
    }
    
    fclose($fp);
}

exit;
?>