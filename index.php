<?php
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Get user's files
$conn = getDBConnection();
$stmt = $conn->prepare("SELECT * FROM files WHERE user_id = ? ORDER BY upload_date DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$files = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Calculate total storage used
$total_size = 0;
foreach ($files as $file) {
    $total_size += $file['file_size'];
}

$conn->close();

function formatFileSize($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' B';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Drive - Simple Drive</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: #f5f7fa;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header h1 {
            font-size: 24px;
        }
        .user-info {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        .admin-btn {
            background: rgba(255,255,255,0.2);
            color: white;
            padding: 8px 16px;
            border-radius: 5px;
            text-decoration: none;
            transition: background 0.3s;
            font-weight: 500;
        }
        .admin-btn:hover {
            background: #ffc107;
            color: #333;
        }
        .logout-btn {
            background: rgba(255,255,255,0.2);
            color: white;
            padding: 8px 16px;
            border-radius: 5px;
            text-decoration: none;
            transition: background 0.3s;
        }
        .logout-btn:hover {
            background: rgba(255,255,255,0.3);
        }
        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .stat-card h3 {
            color: #666;
            font-size: 14px;
            margin-bottom: 10px;
        }
        .stat-card .value {
            font-size: 24px;
            font-weight: 600;
            color: #333;
        }
        .upload-section {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }
        .upload-area {
            border: 2px dashed #667eea;
            border-radius: 10px;
            padding: 40px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        .upload-area:hover {
            background: #f8f9ff;
            border-color: #764ba2;
        }
        .upload-area input[type="file"] {
            display: none;
        }
        .upload-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 20px;
        }
        .upload-btn:hover {
            opacity: 0.9;
        }
        .files-section {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .files-section h2 {
            margin-bottom: 20px;
            color: #333;
        }
        .file-list {
            display: grid;
            gap: 15px;
        }
        .file-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            border: 1px solid #eee;
            border-radius: 8px;
            transition: all 0.3s;
        }
        .file-item:hover {
            background: #f8f9ff;
            border-color: #667eea;
        }
        .file-info {
            flex: 1;
        }
        .file-name {
            font-weight: 500;
            color: #333;
            margin-bottom: 5px;
        }
        .file-meta {
            font-size: 12px;
            color: #999;
        }
        .file-actions {
            display: flex;
            gap: 10px;
        }
        .btn {
            padding: 8px 16px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 14px;
            cursor: pointer;
            border: none;
        }
        .btn-download {
            background: #667eea;
            color: white;
        }
        .btn-play {
            background: #48bb78;
            color: white;
        }
        .btn-delete {
            background: #f56565;
            color: white;
        }
        .btn:hover {
            opacity: 0.8;
        }
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }
        .message {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .message.success {
            background: #efe;
            color: #3c3;
        }
        .message.error {
            background: #fee;
            color: #c33;
        }
        .progress-container {
            display: none;
            margin-top: 20px;
        }
        .progress-bar-wrapper {
            width: 100%;
            height: 30px;
            background: #f0f0f0;
            border-radius: 15px;
            overflow: hidden;
            position: relative;
        }
        .progress-bar {
            height: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            width: 0%;
            transition: width 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 14px;
        }
        .upload-status {
            text-align: center;
            margin-top: 10px;
            color: #666;
            font-size: 14px;
        }
        .upload-speed {
            text-align: center;
            margin-top: 5px;
            color: #999;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>📦 Simple Drive</h1>
        <div class="user-info">
            <span>Welcome, <?php echo htmlspecialchars($username); ?>!</span>
            <?php if (isAdmin()): ?>
                <a href="admin.php" class="admin-btn">⚙️ Admin Panel</a>
            <?php endif; ?>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>

    <div class="container">
        <div class="stats">
            <div class="stat-card">
                <h3>Total Files</h3>
                <div class="value"><?php echo count($files); ?></div>
            </div>
            <div class="stat-card">
                <h3>Storage Used</h3>
                <div class="value"><?php echo formatFileSize($total_size); ?></div>
            </div>
        </div>

        <?php if (isset($_SESSION['message'])): ?>
            <div class="message <?php echo $_SESSION['message_type']; ?>">
                <?php 
                    echo htmlspecialchars($_SESSION['message']); 
                    unset($_SESSION['message']);
                    unset($_SESSION['message_type']);
                ?>
            </div>
        <?php endif; ?>

        <div class="upload-section">
            <h2>Upload File</h2>
            <form action="upload.php" method="POST" enctype="multipart/form-data" id="uploadForm">
                <div class="upload-area" onclick="document.getElementById('fileInput').click()">
                    <input type="file" name="file" id="fileInput" required onchange="handleFileSelect()">
                    <div id="uploadText">
                        <p style="font-size: 48px; margin-bottom: 10px;">📁</p>
                        <p style="color: #667eea; font-weight: 500;">Click to select a file</p>
                        <p style="color: #999; font-size: 14px; margin-top: 5px;">Maximum file size: <?php echo formatFileSize(MAX_FILE_SIZE); ?></p>
                    </div>
                </div>
                <button type="submit" class="upload-btn" id="uploadBtn">Upload File</button>
                <div class="progress-container" id="progressContainer">
                    <div class="progress-bar-wrapper">
                        <div class="progress-bar" id="progressBar">0%</div>
                    </div>
                    <div class="upload-status" id="uploadStatus">Preparing upload...</div>
                    <div class="upload-speed" id="uploadSpeed"></div>
                </div>
            </form>
        </div>

        <div class="files-section">
            <h2>My Files</h2>
            <?php if (empty($files)): ?>
                <div class="empty-state">
                    <p style="font-size: 48px; margin-bottom: 10px;">📂</p>
                    <p>No files yet. Upload your first file!</p>
                </div>
            <?php else: ?>
                <div class="file-list">
                    <?php foreach ($files as $file): ?>
                        <div class="file-item">
                            <div class="file-info">
                                <?php
                                // Define file type categories
                                $video_extensions = ['mp4', 'webm', 'ogg', 'mov', 'avi', 'mkv', 'flv', 'wmv', 'm4v'];
                                $image_extensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg', 'ico'];
                                $audio_extensions = ['mp3', 'wav', 'ogg', 'aac', 'm4a', 'flac', 'wma'];
                                $pdf_extensions = ['pdf'];
                                $text_extensions = ['txt', 'log', 'md', 'json', 'xml', 'csv', 'html', 'css', 'js', 'php', 'py', 'java', 'c', 'cpp'];
                                $archive_extensions = ['zip', 'rar', '7z', 'tar', 'gz'];
                                $document_extensions = ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'];
                                
                                $file_extension = strtolower(pathinfo($file['original_filename'], PATHINFO_EXTENSION));
                                
                                $is_video = in_array($file_extension, $video_extensions);
                                $is_image = in_array($file_extension, $image_extensions);
                                $is_audio = in_array($file_extension, $audio_extensions);
                                $is_pdf = in_array($file_extension, $pdf_extensions);
                                $is_text = in_array($file_extension, $text_extensions);
                                $is_archive = in_array($file_extension, $archive_extensions);
                                $is_document = in_array($file_extension, $document_extensions);
                                
                                // Determine icon
                                if ($is_video) $icon = '🎥';
                                elseif ($is_image) $icon = '🖼️';
                                elseif ($is_audio) $icon = '🎵';
                                elseif ($is_pdf) $icon = '📕';
                                elseif ($is_text) $icon = '📝';
                                elseif ($is_archive) $icon = '📦';
                                elseif ($is_document) $icon = '📄';
                                else $icon = '📄';
                                
                                $can_preview = $is_video || $is_image || $is_audio || $is_pdf || $is_text;
                                ?>
                                <div class="file-name"><?php echo $icon; ?> <?php echo htmlspecialchars($file['original_filename']); ?></div>
                                <div class="file-meta">
                                    <?php echo formatFileSize($file['file_size']); ?> • 
                                    <?php echo date('M d, Y H:i', strtotime($file['upload_date'])); ?>
                                </div>
                            </div>
                            <div class="file-actions">
                                <?php if ($can_preview): ?>
                                    <a href="player.php?id=<?php echo $file['id']; ?>" class="btn btn-play">
                                        <?php 
                                        if ($is_video) echo '▶ Play';
                                        elseif ($is_image) echo '👁 View';
                                        elseif ($is_audio) echo '🔊 Play';
                                        elseif ($is_pdf) echo '📖 View';
                                        elseif ($is_text) echo '📝 View';
                                        else echo '👁 Preview';
                                        ?>
                                    </a>
                                <?php endif; ?>
                                <a href="download.php?id=<?php echo $file['id']; ?>" class="btn btn-download">Download</a>
                                <form action="delete.php" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this file?');">
                                    <input type="hidden" name="file_id" value="<?php echo $file['id']; ?>">
                                    <button type="submit" class="btn btn-delete">Delete</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        let uploadStartTime;
        let uploadedBytes = 0;

        function handleFileSelect() {
            const fileInput = document.getElementById('fileInput');
            const uploadText = document.getElementById('uploadText');
            if (fileInput.files.length > 0) {
                const fileName = fileInput.files[0].name;
                const fileSize = formatFileSize(fileInput.files[0].size);
                uploadText.innerHTML = `<p style="font-size: 48px; margin-bottom: 10px;">✅</p>
                                       <p style="color: #667eea; font-weight: 500;">${fileName}</p>
                                       <p style="color: #999; font-size: 14px; margin-top: 5px;">${fileSize} • Click to change file</p>`;
            }
        }

        function formatFileSize(bytes) {
            if (bytes >= 1073741824) {
                return (bytes / 1073741824).toFixed(2) + ' GB';
            } else if (bytes >= 1048576) {
                return (bytes / 1048576).toFixed(2) + ' MB';
            } else if (bytes >= 1024) {
                return (bytes / 1024).toFixed(2) + ' KB';
            } else {
                return bytes + ' B';
            }
        }

        function formatSpeed(bytesPerSecond) {
            if (bytesPerSecond >= 1048576) {
                return (bytesPerSecond / 1048576).toFixed(2) + ' MB/s';
            } else if (bytesPerSecond >= 1024) {
                return (bytesPerSecond / 1024).toFixed(2) + ' KB/s';
            } else {
                return bytesPerSecond.toFixed(0) + ' B/s';
            }
        }

        function formatTime(seconds) {
            if (seconds < 60) {
                return Math.round(seconds) + 's';
            } else {
                const minutes = Math.floor(seconds / 60);
                const secs = Math.round(seconds % 60);
                return minutes + 'm ' + secs + 's';
            }
        }

        // Handle form submission with AJAX
        document.getElementById('uploadForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const fileInput = document.getElementById('fileInput');
            const uploadBtn = document.getElementById('uploadBtn');
            const progressContainer = document.getElementById('progressContainer');
            const progressBar = document.getElementById('progressBar');
            const uploadStatus = document.getElementById('uploadStatus');
            const uploadSpeed = document.getElementById('uploadSpeed');
            
            if (fileInput.files.length === 0) {
                alert('Please select a file');
                return;
            }
            
            const file = fileInput.files[0];
            const formData = new FormData();
            formData.append('file', file);
            
            // Disable upload button and show progress
            uploadBtn.disabled = true;
            uploadBtn.style.opacity = '0.5';
            uploadBtn.style.cursor = 'not-allowed';
            progressContainer.style.display = 'block';
            
            uploadStartTime = Date.now();
            
            // Create XMLHttpRequest for upload progress tracking
            const xhr = new XMLHttpRequest();
            
            // Track upload progress
            xhr.upload.addEventListener('progress', function(e) {
                if (e.lengthComputable) {
                    const percentComplete = (e.loaded / e.total) * 100;
                    progressBar.style.width = percentComplete + '%';
                    progressBar.textContent = percentComplete.toFixed(1) + '%';
                    
                    // Calculate upload speed and time remaining
                    const elapsedTime = (Date.now() - uploadStartTime) / 1000; // in seconds
                    const uploadSpeed = e.loaded / elapsedTime; // bytes per second
                    const remainingBytes = e.total - e.loaded;
                    const remainingTime = remainingBytes / uploadSpeed;
                    
                    uploadStatus.textContent = `Uploading ${formatFileSize(e.loaded)} of ${formatFileSize(e.total)}`;
                    
                    if (percentComplete < 100) {
                        document.getElementById('uploadSpeed').textContent = 
                            `Speed: ${formatSpeed(uploadSpeed)} • Time remaining: ${formatTime(remainingTime)}`;
                    }
                }
            });
            
            // Handle upload completion
            xhr.addEventListener('load', function() {
                if (xhr.status === 200) {
                    progressBar.style.width = '100%';
                    progressBar.textContent = '100%';
                    uploadStatus.textContent = 'Upload complete! Processing...';
                    uploadSpeed.textContent = '';
                    
                    // Parse response
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            uploadStatus.textContent = '✓ File uploaded successfully!';
                            uploadStatus.style.color = '#3c3';
                            
                            // Reload page after 2 seconds
                            setTimeout(function() {
                                window.location.reload();
                            }, 2000);
                        } else {
                            uploadStatus.textContent = '✗ Error: ' + response.message;
                            uploadStatus.style.color = '#c33';
                            resetUploadForm();
                        }
                    } catch (e) {
                        // Fallback if response is not JSON (old format)
                        window.location.reload();
                    }
                } else {
                    uploadStatus.textContent = '✗ Upload failed. Please try again.';
                    uploadStatus.style.color = '#c33';
                    resetUploadForm();
                }
            });
            
            // Handle upload error
            xhr.addEventListener('error', function() {
                uploadStatus.textContent = '✗ Upload failed. Please check your connection.';
                uploadStatus.style.color = '#c33';
                resetUploadForm();
            });
            
            // Handle upload abort
            xhr.addEventListener('abort', function() {
                uploadStatus.textContent = '✗ Upload cancelled.';
                uploadStatus.style.color = '#c33';
                resetUploadForm();
            });
            
            // Send the request
            xhr.open('POST', 'upload.php', true);
            xhr.send(formData);
        });
        
        function resetUploadForm() {
            setTimeout(function() {
                const uploadBtn = document.getElementById('uploadBtn');
                const progressContainer = document.getElementById('progressContainer');
                
                uploadBtn.disabled = false;
                uploadBtn.style.opacity = '1';
                uploadBtn.style.cursor = 'pointer';
                
                setTimeout(function() {
                    progressContainer.style.display = 'none';
                    document.getElementById('progressBar').style.width = '0%';
                    document.getElementById('uploadStatus').style.color = '#666';
                }, 3000);
            }, 2000);
        }
    </script>
</body>
</html>