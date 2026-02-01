<?php
/**
 * Admin Authentication Functions
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

/**
 * Check if admin is logged in
 */
function isAdminLoggedIn() {
    return isset($_SESSION[ADMIN_SESSION_KEY]) && $_SESSION[ADMIN_SESSION_KEY] === true;
}

/**
 * Require admin login
 */
function requireAdminLogin() {
    if (!isAdminLoggedIn()) {
        redirect(SITE_URL . '/admin/login.php', 'Please login to continue', 'error');
    }
}

/**
 * Admin login
 */
function adminLogin($username, $password) {
    $db = Database::getInstance()->getConnection();
    
    $sql = "SELECT * FROM admin_users WHERE (username = :username OR email = :username) AND status = 'active'";
    $stmt = $db->prepare($sql);
    $stmt->execute([':username' => $username]);
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
        
        return true;
    }
    
    return false;
}

/**
 * Admin logout
 */
function adminLogout() {
    unset($_SESSION[ADMIN_SESSION_KEY]);
    unset($_SESSION[ADMIN_USER_KEY]);
    session_destroy();
}

/**
 * Get current admin user
 */
function getCurrentAdmin() {
    return $_SESSION[ADMIN_USER_KEY] ?? null;
}

