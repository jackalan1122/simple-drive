<?php
require_once 'config.php';

// Set JSON header for AJAX response
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];
    
    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => 'Upload failed. Please try again.']);
        exit;
    }
    
    // Check file size
    if ($file['size'] > MAX_FILE_SIZE) {
        echo json_encode([
            'success' => false, 
            'message' => 'File is too large. Maximum size is ' . (MAX_FILE_SIZE / 1024 / 1024) . 'MB'
        ]);
        exit;
    }
    
    // Generate unique filename
    $original_filename = basename($file['name']);
    $file_extension = pathinfo($original_filename, PATHINFO_EXTENSION);
    $unique_filename = uniqid() . '_' . time() . '.' . $file_extension;
    $destination = UPLOAD_DIR . $unique_filename;
    
    // Ensure upload directory exists
    if (!file_exists(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0755, true);
    }
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        // Verify file was saved correctly
        if (!file_exists($destination)) {
            echo json_encode(['success' => false, 'message' => 'File upload failed - file not saved.']);
            exit;
        }
        
        // Get actual file size after upload
        $actual_size = filesize($destination);
        
        // Verify file size matches
        if ($actual_size != $file['size']) {
            unlink($destination);
            echo json_encode(['success' => false, 'message' => 'File upload incomplete - size mismatch.']);
            exit;
        }
        
        // Get proper MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $detected_mime = finfo_file($finfo, $destination);
        finfo_close($finfo);
        
        $file_type = $detected_mime ?: $file['type'];
        
        // Insert file info into database
        $conn = getDBConnection();
        $stmt = $conn->prepare("INSERT INTO files (user_id, filename, original_filename, file_size, file_type) VALUES (?, ?, ?, ?, ?)");
        $file_size = $actual_size; // Use actual size
        $stmt->bind_param("issis", $user_id, $unique_filename, $original_filename, $file_size, $file_type);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'File uploaded successfully!']);
        } else {
            // Delete the uploaded file if database insert fails
            unlink($destination);
            echo json_encode(['success' => false, 'message' => 'Failed to save file information.']);
        }
        
        $stmt->close();
        $conn->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to upload file.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'No file selected.']);
}
exit;
?>