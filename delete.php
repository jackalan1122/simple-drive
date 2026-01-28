<?php
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['file_id'])) {
    $file_id = intval($_POST['file_id']);
    
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
    $file_path = UPLOAD_DIR . $file['filename'];
    
    // Delete file from filesystem
    if (file_exists($file_path)) {
        unlink($file_path);
    }
    
    // Delete from database
    $delete_stmt = $conn->prepare("DELETE FROM files WHERE id = ? AND user_id = ?");
    $delete_stmt->bind_param("ii", $file_id, $user_id);
    
    if ($delete_stmt->execute()) {
        $_SESSION['message'] = 'File deleted successfully.';
        $_SESSION['message_type'] = 'success';
    } else {
        $_SESSION['message'] = 'Failed to delete file.';
        $_SESSION['message_type'] = 'error';
    }
    
    $delete_stmt->close();
    $stmt->close();
    $conn->close();
} else {
    $_SESSION['message'] = 'Invalid request.';
    $_SESSION['message_type'] = 'error';
}

header('Location: index.php');
exit;
?>