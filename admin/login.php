<?php
// Start session first
require_once __DIR__ . '/../config/config.php';

// Check if already logged in (check session directly to avoid redirect loop)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Simple check for admin session - if logged in, redirect to dashboard
// Use the constant to match what auth.php expects
if (isset($_SESSION[ADMIN_SESSION_KEY]) && $_SESSION[ADMIN_SESSION_KEY] === true) {
    header("Location: " . SITE_URL . "/admin/");
    exit;
}

// Now include auth functions (but don't call requireAdminLogin on login page)
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter username and password';
    } else {
        // Direct login check without using auth.php functions to avoid redirect
        $db = Database::getInstance()->getConnection();
        
        $sql = "SELECT * FROM admin_users WHERE (username = :username OR email = :email) AND status = 'active'";
        $stmt = $db->prepare($sql);
        $stmt->execute(['username' => $username, 'email' => $username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION[ADMIN_SESSION_KEY] = true;
            $_SESSION[ADMIN_USER_KEY] = [
                'id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'full_name' => $user['full_name']
            ];
            
            // Update last login
            $updateSql = "UPDATE admin_users SET last_login = NOW() WHERE id = :id";
            $updateStmt = $db->prepare($updateSql);
            $updateStmt->execute([':id' => $user['id']]);
            
            header("Location: " . SITE_URL . "/admin/");
            exit;
        } else {
            $error = 'Invalid username or password';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            padding: 40px;
            width: 100%;
            max-width: 400px;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="text-center mb-4">
            <i class="bi bi-shield-lock-fill" style="font-size: 3rem; color: #667eea;"></i>
            <h2 class="mt-3">Admin Login</h2>
            <p class="text-muted">Professional Dealer Website</p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="mb-3">
                <label class="form-label">Username or Email</label>
                <input type="text" class="form-control" name="username" required autofocus>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" class="form-control" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">
                <i class="bi bi-box-arrow-in-right"></i> Login
            </button>
        </form>
        
        <div class="mt-4 text-center text-muted small">
            <p>Default: admin / admin123</p>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
