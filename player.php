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
    header('Location: index.php');
    exit;
}

$file = $result->fetch_assoc();
$stmt->close();
$conn->close();

// Determine file type
$file_extension = strtolower(pathinfo($file['original_filename'], PATHINFO_EXTENSION));

// Define supported file types
$video_extensions = ['mp4', 'webm', 'ogg', 'mov', 'avi', 'mkv', 'flv', 'wmv', 'm4v'];
$image_extensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg', 'ico'];
$audio_extensions = ['mp3', 'wav', 'ogg', 'aac', 'm4a', 'flac', 'wma'];
$pdf_extensions = ['pdf'];
$text_extensions = ['txt', 'log', 'md', 'json', 'xml', 'csv', 'html', 'css', 'js', 'php', 'py', 'java', 'c', 'cpp'];

$is_video = in_array($file_extension, $video_extensions);
$is_image = in_array($file_extension, $image_extensions);
$is_audio = in_array($file_extension, $audio_extensions);
$is_pdf = in_array($file_extension, $pdf_extensions);
$is_text = in_array($file_extension, $text_extensions);

$can_preview = $is_video || $is_image || $is_audio || $is_pdf || $is_text;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($file['original_filename']); ?> - Simple Drive</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: #1a1a1a;
            color: white;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .back-btn {
            background: rgba(255,255,255,0.2);
            color: white;
            padding: 8px 16px;
            border-radius: 5px;
            text-decoration: none;
            transition: background 0.3s;
        }
        .back-btn:hover {
            background: rgba(255,255,255,0.3);
        }
        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }
        .preview-wrapper {
            background: #000;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 30px;
            position: relative;
        }
        video, audio {
            width: 100%;
            height: auto;
            display: block;
        }
        .image-preview {
            width: 100%;
            height: auto;
            display: block;
            max-height: 80vh;
            object-fit: contain;
        }
        .pdf-preview {
            width: 100%;
            height: 80vh;
            border: none;
        }
        .text-preview {
            background: #2a2a2a;
            padding: 20px;
            border-radius: 10px;
            max-height: 600px;
            overflow: auto;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            line-height: 1.6;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        .file-info {
            background: #2a2a2a;
            padding: 30px;
            border-radius: 10px;
        }
        .file-name {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 20px;
            word-break: break-all;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }
        .info-item {
            padding: 15px;
            background: #333;
            border-radius: 8px;
        }
        .info-label {
            font-size: 12px;
            color: #999;
            margin-bottom: 5px;
        }
        .info-value {
            font-size: 16px;
            font-weight: 500;
        }
        .actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            flex-wrap: wrap;
        }
        .btn {
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 16px;
            cursor: pointer;
            border: none;
            font-weight: 500;
            transition: all 0.3s;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .btn-primary:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }
        .btn-secondary {
            background: #444;
            color: white;
        }
        .btn-secondary:hover {
            background: #555;
        }
        .error-message {
            background: #2a2a2a;
            padding: 60px 20px;
            text-align: center;
            border-radius: 10px;
        }
        .zoom-controls {
            position: absolute;
            top: 20px;
            right: 20px;
            display: flex;
            gap: 10px;
            z-index: 10;
        }
        .zoom-btn {
            background: rgba(0,0,0,0.7);
            color: white;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 20px;
            transition: background 0.3s;
        }
        .zoom-btn:hover {
            background: rgba(0,0,0,0.9);
        }
        .image-container {
            overflow: auto;
            max-height: 80vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>📦 Simple Drive</h1>
        <a href="index.php" class="back-btn">← Back to Files</a>
    </div>

    <div class="container">
        <?php if ($can_preview): ?>
            <div class="preview-wrapper">
                <?php if ($is_video): ?>
                    <video controls controlsList="nodownload">
                        <source src="stream.php?id=<?php echo $file_id; ?>" type="<?php echo htmlspecialchars($file['file_type']); ?>">
                        Your browser does not support the video tag.
                    </video>
                    
                <?php elseif ($is_audio): ?>
                    <audio controls controlsList="nodownload" style="width: 100%; padding: 20px;">
                        <source src="stream.php?id=<?php echo $file_id; ?>" type="<?php echo htmlspecialchars($file['file_type']); ?>">
                        Your browser does not support the audio tag.
                    </audio>
                    
                <?php elseif ($is_image): ?>
                    <div class="zoom-controls">
                        <button class="zoom-btn" onclick="zoomIn()">+</button>
                        <button class="zoom-btn" onclick="zoomOut()">−</button>
                        <button class="zoom-btn" onclick="resetZoom()">⟲</button>
                    </div>
                    <div class="image-container">
                        <img src="stream.php?id=<?php echo $file_id; ?>" 
                             alt="<?php echo htmlspecialchars($file['original_filename']); ?>" 
                             class="image-preview" 
                             id="imagePreview">
                    </div>
                    
                <?php elseif ($is_pdf): ?>
                    <iframe src="stream.php?id=<?php echo $file_id; ?>" class="pdf-preview"></iframe>
                    
                <?php elseif ($is_text): ?>
                    <div class="text-preview" id="textPreview">
                        Loading...
                    </div>
                    <script>
                        // Load text content via AJAX
                        fetch('stream.php?id=<?php echo $file_id; ?>')
                            .then(response => response.text())
                            .then(text => {
                                document.getElementById('textPreview').textContent = text;
                            })
                            .catch(error => {
                                document.getElementById('textPreview').textContent = 'Error loading file content.';
                            });
                    </script>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="error-message">
                <p style="font-size: 48px; margin-bottom: 20px;">📄</p>
                <p style="font-size: 18px; margin-bottom: 10px;">Preview not available</p>
                <p style="color: #999;">This file type cannot be previewed in the browser</p>
            </div>
        <?php endif; ?>

        <div class="file-info">
            <div class="file-name">
                <?php 
                if ($is_video) echo '🎥';
                elseif ($is_image) echo '🖼️';
                elseif ($is_audio) echo '🎵';
                elseif ($is_pdf) echo '📕';
                elseif ($is_text) echo '📝';
                else echo '📄';
                ?> 
                <?php echo htmlspecialchars($file['original_filename']); ?>
            </div>
            
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">File Size</div>
                    <div class="info-value">
                        <?php 
                        function formatSize($bytes) {
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
                        echo formatSize($file['file_size']); 
                        ?>
                    </div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">File Type</div>
                    <div class="info-value"><?php echo strtoupper($file_extension); ?></div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">MIME Type</div>
                    <div class="info-value"><?php echo htmlspecialchars($file['file_type']); ?></div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">Uploaded</div>
                    <div class="info-value"><?php echo date('M d, Y H:i', strtotime($file['upload_date'])); ?></div>
                </div>
            </div>

            <div class="actions">
                <a href="download.php?id=<?php echo $file_id; ?>" class="btn btn-primary">📥 Download</a>
                <a href="index.php" class="btn btn-secondary">← Back to Files</a>
            </div>
        </div>
    </div>

    <?php if ($is_image): ?>
    <script>
        let scale = 1;
        const image = document.getElementById('imagePreview');
        
        function zoomIn() {
            scale += 0.2;
            image.style.transform = `scale(${scale})`;
        }
        
        function zoomOut() {
            if (scale > 0.4) {
                scale -= 0.2;
                image.style.transform = `scale(${scale})`;
            }
        }
        
        function resetZoom() {
            scale = 1;
            image.style.transform = `scale(${scale})`;
        }
    </script>
    <?php endif; ?>
</body>
</html>