<?php
require_once 'config.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

requireAdmin();

$conn = getDBConnection();

// Handle actions
$action = isset($_GET['action']) ? $_GET['action'] : '';
$message = '';
$message_type = '';

// Delete user action
if ($action === 'delete_user' && isset($_POST['user_id'])) {
    $delete_user_id = intval($_POST['user_id']);
    
    if ($delete_user_id == $_SESSION['user_id']) {
        $message = "Cannot delete your own account";
        $message_type = 'error';
    } else {
        // Check if user is an admin
        $check_stmt = $conn->prepare("SELECT is_admin FROM users WHERE id = ?");
        $check_stmt->bind_param("i", $delete_user_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        $user_to_delete = $check_result->fetch_assoc();
        $check_stmt->close();
        
        if (!$user_to_delete) {
            $message = "User not found";
            $message_type = 'error';
        } elseif ($user_to_delete['is_admin']) {
            // Check if this is the last admin
            $admin_count_stmt = $conn->prepare("SELECT COUNT(*) as count FROM users WHERE is_admin = 1");
            $admin_count_stmt->execute();
            $admin_count_result = $admin_count_stmt->get_result();
            $admin_count = $admin_count_result->fetch_assoc()['count'];
            $admin_count_stmt->close();
            
            if ($admin_count <= 1) {
                $message = "Cannot delete the last admin account";
                $message_type = 'error';
            } else {
                // Delete the admin user
                $delete_stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
                $delete_stmt->bind_param("i", $delete_user_id);
                if ($delete_stmt->execute()) {
                    $message = "User deleted successfully";
                    $message_type = 'success';
                } else {
                    $message = "Error deleting user";
                    $message_type = 'error';
                }
                $delete_stmt->close();
            }
        } else {
            // Delete non-admin user
            $delete_stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            $delete_stmt->bind_param("i", $delete_user_id);
            if ($delete_stmt->execute()) {
                $message = "User deleted successfully";
                $message_type = 'success';
            } else {
                $message = "Error deleting user";
                $message_type = 'error';
            }
            $delete_stmt->close();
        }
    }
}

// Toggle admin status
if ($action === 'toggle_admin' && isset($_POST['user_id'])) {
    $toggle_user_id = intval($_POST['user_id']);
    
    if ($toggle_user_id == $_SESSION['user_id']) {
        $message = "Cannot change your own admin status";
        $message_type = 'error';
    } else {
        // Check current admin status
        $check_stmt = $conn->prepare("SELECT is_admin FROM users WHERE id = ?");
        $check_stmt->bind_param("i", $toggle_user_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        $user_data = $check_result->fetch_assoc();
        $check_stmt->close();
        
        $new_admin_status = $user_data['is_admin'] ? 0 : 1;
        
        // Prevent removing the last admin
        if ($new_admin_status === 0) {
            $admin_count_stmt = $conn->prepare("SELECT COUNT(*) as count FROM users WHERE is_admin = 1");
            $admin_count_stmt->execute();
            $admin_count_result = $admin_count_stmt->get_result();
            $admin_count = $admin_count_result->fetch_assoc()['count'];
            $admin_count_stmt->close();
            
            if ($admin_count <= 1) {
                $message = "Cannot remove admin status from the last admin";
                $message_type = 'error';
            } else {
                $update_stmt = $conn->prepare("UPDATE users SET is_admin = ? WHERE id = ?");
                $update_stmt->bind_param("ii", $new_admin_status, $toggle_user_id);
                if ($update_stmt->execute()) {
                    $message = "Admin status updated successfully";
                    $message_type = 'success';
                } else {
                    $message = "Error updating admin status";
                    $message_type = 'error';
                }
                $update_stmt->close();
            }
        } else {
            $update_stmt = $conn->prepare("UPDATE users SET is_admin = ? WHERE id = ?");
            $update_stmt->bind_param("ii", $new_admin_status, $toggle_user_id);
            if ($update_stmt->execute()) {
                $message = "Admin status updated successfully";
                $message_type = 'success';
            } else {
                $message = "Error updating admin status";
                $message_type = 'error';
            }
            $update_stmt->close();
        }
    }
}

// Delete file action
if ($action === 'delete_file' && isset($_POST['file_id'])) {
    $file_id = intval($_POST['file_id']);
    
    $file_stmt = $conn->prepare("SELECT filename FROM files WHERE id = ?");
    $file_stmt->bind_param("i", $file_id);
    $file_stmt->execute();
    $file_result = $file_stmt->get_result();
    $file = $file_result->fetch_assoc();
    $file_stmt->close();
    
    if ($file) {
        $file_path = UPLOAD_DIR . $file['filename'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }
        
        $delete_file_stmt = $conn->prepare("DELETE FROM files WHERE id = ?");
        $delete_file_stmt->bind_param("i", $file_id);
        if ($delete_file_stmt->execute()) {
            $message = "File deleted successfully";
            $message_type = 'success';
        } else {
            $message = "Error deleting file";
            $message_type = 'error';
        }
        $delete_file_stmt->close();
    }
}

// Get statistics
$stats = [];

// Total users
$user_count_stmt = $conn->prepare("SELECT COUNT(*) as count FROM users");
$user_count_stmt->execute();
$user_count_result = $user_count_stmt->get_result();
$stats['total_users'] = $user_count_result->fetch_assoc()['count'];
$user_count_stmt->close();

// Total admins
$admin_count_stmt = $conn->prepare("SELECT COUNT(*) as count FROM users WHERE is_admin = 1");
$admin_count_stmt->execute();
$admin_count_result = $admin_count_stmt->get_result();
$stats['total_admins'] = $admin_count_result->fetch_assoc()['count'];
$admin_count_stmt->close();

// Total files
$file_count_stmt = $conn->prepare("SELECT COUNT(*) as count, SUM(file_size) as total_size FROM files");
$file_count_stmt->execute();
$file_count_result = $file_count_stmt->get_result();
$file_data = $file_count_result->fetch_assoc();
$stats['total_files'] = $file_data['count'];
$stats['total_storage'] = $file_data['total_size'] ?? 0;
$file_count_stmt->close();

// Get all users
$users_stmt = $conn->prepare("SELECT id, username, email, is_admin, created_at FROM users ORDER BY created_at DESC");
$users_stmt->execute();
$users_result = $users_stmt->get_result();
$users = $users_result->fetch_all(MYSQLI_ASSOC);
$users_stmt->close();

// Get all files
$files_stmt = $conn->prepare("SELECT f.id, f.filename, f.original_filename, f.file_size, f.upload_date, u.username FROM files f JOIN users u ON f.user_id = u.id ORDER BY f.upload_date DESC LIMIT 50");
$files_stmt->execute();
$files_result = $files_stmt->get_result();
$files = $files_result->fetch_all(MYSQLI_ASSOC);
$files_stmt->close();

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

function formatDate($date) {
    return date('M d, Y H:i', strtotime($date));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Simple Drive</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: #f5f5f5;
            color: #333;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header h1 { font-size: 24px; }
        .header-actions {
            display: flex;
            gap: 15px;
            align-items: center;
        }
        .header a, .header button {
            color: white;
            text-decoration: none;
            padding: 8px 16px;
            background: rgba(255,255,255,0.2);
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
        .header a:hover, .header button:hover {
            background: rgba(255,255,255,0.3);
        }
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }
        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .stat-card h3 {
            color: #666;
            font-size: 14px;
            font-weight: 500;
            text-transform: uppercase;
            margin-bottom: 10px;
        }
        .stat-value {
            font-size: 28px;
            font-weight: bold;
            color: #667eea;
        }
        .tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            border-bottom: 1px solid #ddd;
        }
        .tab-btn {
            padding: 12px 20px;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 14px;
            color: #666;
            border-bottom: 3px solid transparent;
            transition: all 0.3s;
        }
        .tab-btn.active {
            color: #667eea;
            border-bottom-color: #667eea;
        }
        .tab-content {
            display: none;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 20px;
        }
        .tab-content.active {
            display: block;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table thead {
            background: #f5f5f5;
            border-bottom: 2px solid #ddd;
        }
        table th {
            padding: 12px;
            text-align: left;
            font-weight: 600;
            color: #333;
        }
        table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }
        table tr:hover {
            background: #f9f9f9;
        }
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        .badge.admin {
            background: #e3f2fd;
            color: #1976d2;
        }
        .badge.user {
            background: #f3e5f5;
            color: #7b1fa2;
        }
        .actions {
            display: flex;
            gap: 10px;
        }
        button {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 13px;
            transition: all 0.3s;
        }
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        .btn-danger:hover {
            background: #c82333;
        }
        .btn-warning {
            background: #ffc107;
            color: black;
        }
        .btn-warning:hover {
            background: #e0a800;
        }
        .btn-primary {
            background: #667eea;
            color: white;
        }
        .btn-primary:hover {
            background: #5568d3;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        .modal.show {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 8px;
            max-width: 400px;
            width: 90%;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }
        .modal-header {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 15px;
            color: #333;
        }
        .modal-body {
            margin-bottom: 20px;
            color: #666;
        }
        .modal-footer {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }
        .modal-footer button {
            padding: 10px 20px;
        }
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #999;
        }
        .empty-state p {
            margin-bottom: 10px;
        }
        @media (max-width: 768px) {
            .stats {
                grid-template-columns: 1fr;
            }
            table {
                font-size: 13px;
            }
            table th, table td {
                padding: 8px;
            }
            .actions {
                flex-direction: column;
            }
            .header {
                flex-direction: column;
                text-align: center;
                gap: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>⚙️ Admin Panel</h1>
        <div class="header-actions">
            <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
            <a href="index.php">← Back to Drive</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <div class="container">
        <?php if (!empty($message)): ?>
            <div class="message <?php echo $message_type; ?>">
                <span><?php echo htmlspecialchars($message); ?></span>
                <button onclick="this.parentElement.style.display='none'" style="background: none; border: none; color: inherit; font-size: 20px; cursor: pointer;">×</button>
            </div>
        <?php endif; ?>

        <!-- Statistics -->
        <div class="stats">
            <div class="stat-card">
                <h3>Total Users</h3>
                <div class="stat-value"><?php echo $stats['total_users']; ?></div>
            </div>
            <div class="stat-card">
                <h3>Admin Users</h3>
                <div class="stat-value"><?php echo $stats['total_admins']; ?></div>
            </div>
            <div class="stat-card">
                <h3>Total Files</h3>
                <div class="stat-value"><?php echo $stats['total_files']; ?></div>
            </div>
            <div class="stat-card">
                <h3>Total Storage</h3>
                <div class="stat-value"><?php echo formatFileSize($stats['total_storage']); ?></div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="tabs">
            <button class="tab-btn active" onclick="switchTab('users')">👥 User Management</button>
            <button class="tab-btn" onclick="switchTab('files')">📁 File Management</button>
        </div>

        <!-- User Management Tab -->
        <div id="users" class="tab-content active">
            <h2>User Management</h2>
            <?php if (empty($users)): ?>
                <div class="empty-state">
                    <p>No users found</p>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <?php if ($user['is_admin']): ?>
                                        <span class="badge admin">Admin</span>
                                    <?php else: ?>
                                        <span class="badge user">User</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo formatDate($user['created_at']); ?></td>
                                <td>
                                    <div class="actions">
                                        <?php if ($user['id'] !== $_SESSION['user_id']): ?>
                                            <form method="POST" action="admin.php?action=toggle_admin" style="margin: 0;">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <button type="submit" class="btn-warning">
                                                    <?php echo $user['is_admin'] ? 'Remove Admin' : 'Make Admin'; ?>
                                                </button>
                                            </form>
                                            <button class="btn-danger" onclick="showDeleteUserModal(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars(addslashes($user['username'])); ?>')">Delete</button>
                                        <?php else: ?>
                                            <span style="color: #999; font-size: 13px;">Current User</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- File Management Tab -->
        <div id="files" class="tab-content">
            <h2>File Management</h2>
            <?php if (empty($files)): ?>
                <div class="empty-state">
                    <p>No files found</p>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Filename</th>
                            <th>Owner</th>
                            <th>Size</th>
                            <th>Uploaded</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($files as $file): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($file['original_filename']); ?></td>
                                <td><?php echo htmlspecialchars($file['username']); ?></td>
                                <td><?php echo formatFileSize($file['file_size']); ?></td>
                                <td><?php echo formatDate($file['upload_date']); ?></td>
                                <td>
                                    <button class="btn-danger" onclick="showDeleteFileModal(<?php echo $file['id']; ?>, '<?php echo htmlspecialchars(addslashes($file['original_filename'])); ?>')">Delete</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- Delete User Modal -->
    <div id="deleteUserModal" class="modal">
        <div class="modal-content">
            <form id="deleteUserForm" method="POST" action="admin.php?action=delete_user" style="margin: 0;">
                <div class="modal-header">Delete User</div>
                <div class="modal-body">
                    Are you sure you want to delete user <strong id="deleteUsername"></strong>? This action cannot be undone.
                </div>
                <div class="modal-footer">
                    <button type="button" onclick="closeDeleteUserModal()" class="btn-primary">Cancel</button>
                    <input type="hidden" name="user_id" id="deleteUserId">
                    <button type="submit" class="btn-danger">Delete</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete File Modal -->
    <div id="deleteFileModal" class="modal">
        <div class="modal-content">
            <form id="deleteFileForm" method="POST" action="admin.php?action=delete_file" style="margin: 0;">
                <div class="modal-header">Delete File</div>
                <div class="modal-body">
                    Are you sure you want to delete file <strong id="deleteFileName"></strong>? This action cannot be undone.
                </div>
                <div class="modal-footer">
                    <button type="button" onclick="closeDeleteFileModal()" class="btn-primary">Cancel</button>
                    <input type="hidden" name="file_id" id="deleteFileId">
                    <button type="submit" class="btn-danger">Delete</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function switchTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
            
            // Show selected tab
            document.getElementById(tabName).classList.add('active');
            event.target.classList.add('active');
        }

        function showDeleteUserModal(userId, username) {
            document.getElementById('deleteUserId').value = userId;
            document.getElementById('deleteUsername').textContent = username;
            document.getElementById('deleteUserModal').classList.add('show');
        }

        function closeDeleteUserModal() {
            document.getElementById('deleteUserModal').classList.remove('show');
        }

        function showDeleteFileModal(fileId, filename) {
            document.getElementById('deleteFileId').value = fileId;
            document.getElementById('deleteFileName').textContent = filename;
            document.getElementById('deleteFileModal').classList.add('show');
        }

        function closeDeleteFileModal() {
            document.getElementById('deleteFileModal').classList.remove('show');
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const userModal = document.getElementById('deleteUserModal');
            const fileModal = document.getElementById('deleteFileModal');
            if (event.target === userModal) {
                closeDeleteUserModal();
            }
            if (event.target === fileModal) {
                closeDeleteFileModal();
            }
        }
    </script>
</body>
</html>
