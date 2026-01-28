<?php
require_once 'config.php';

$message = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    
    if (empty($username)) {
        $message = 'Please enter a username';
    } else {
        $conn = getDBConnection();
        
        // Check if user exists
        $stmt = $conn->prepare("SELECT id, is_admin FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $message = 'User not found';
        } else {
            $user = $result->fetch_assoc();
            
            if ($user['is_admin']) {
                $message = 'This user is already an admin';
            } else {
                // Make user admin
                $update_stmt = $conn->prepare("UPDATE users SET is_admin = 1 WHERE id = ?");
                $update_stmt->bind_param("i", $user['id']);
                
                if ($update_stmt->execute()) {
                    $message = "User '{$username}' has been made an admin!";
                    $success = true;
                } else {
                    $message = 'Error updating user';
                }
                $update_stmt->close();
            }
        }
        
        $stmt->close();
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Setup - Simple Drive</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 400px;
        }
        h1 {
            color: #333;
            margin-bottom: 10px;
            text-align: center;
        }
        .subtitle {
            color: #666;
            text-align: center;
            font-size: 14px;
            margin-bottom: 30px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 500;
        }
        input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        button {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 5px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 10px;
            transition: transform 0.2s;
        }
        button:hover {
            transform: translateY(-2px);
        }
        .message {
            margin-top: 20px;
            padding: 15px;
            border-radius: 5px;
            text-align: center;
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
        .info-box {
            background: #f0f4ff;
            padding: 15px;
            border-radius: 5px;
            font-size: 13px;
            color: #555;
            line-height: 1.6;
            margin-bottom: 20px;
        }
        .info-box strong {
            color: #333;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>⚙️ Admin Setup</h1>
        <p class="subtitle">Make a user an admin</p>
        
        <div class="info-box">
            <strong>Important:</strong> This tool lets you make any existing user an admin. After that, delete this file for security.
        </div>

        <form method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input 
                    type="text" 
                    id="username" 
                    name="username" 
                    placeholder="Enter username"
                    required
                >
            </div>
            <button type="submit">Make Admin</button>
        </form>

        <?php if (!empty($message)): ?>
            <div class="message <?php echo $success ? 'success' : 'error'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
