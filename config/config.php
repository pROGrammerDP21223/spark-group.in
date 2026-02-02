<?php
/**
 * Application Configuration
 * Professional Dealer Website - Main Config
 */

// Site Configuration
define('SITE_NAME', 'Professional Dealer Website');

// Auto-detect SITE_URL for production (works on shared hosting)
// For local development, you can override this by uncommenting the line below
if (!defined('SITE_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $scriptPath = dirname($_SERVER['SCRIPT_NAME']);
    // For root domain, use empty path; for subdirectory, use the path
    $basePath = ($scriptPath === '/' || $scriptPath === '\\') ? '' : rtrim($scriptPath, '/');
    define('SITE_URL', $protocol . $host . $basePath);
}
// Uncomment and set manually if auto-detection doesn't work:
// define('SITE_URL', 'https://yourdomain.com');

define('ADMIN_EMAIL', 'admin@dealerwebsite.com');

// Paths
define('ROOT_PATH', dirname(__DIR__));
define('ADMIN_PATH', ROOT_PATH . '/admin');
define('UPLOAD_PATH', ROOT_PATH . '/uploads');
define('UPLOAD_URL', SITE_URL . '/uploads');

// Image Upload Settings
define('MAX_UPLOAD_SIZE', 5242880); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/webp', 'image/gif']);

// Pagination
define('ITEMS_PER_PAGE', 12);

// Session Configuration
define('SESSION_LIFETIME', 3600); // 1 hour

// Admin Authentication
define('ADMIN_SESSION_KEY', 'admin_logged_in');
define('ADMIN_USER_KEY', 'admin_user');

// Timezone
date_default_timezone_set('Asia/Kolkata');

// Error Reporting (Production-safe: log errors but don't display)
// Set to E_ALL for development, 0 for production
error_reporting(E_ALL);
// Don't display errors on production (security best practice)
ini_set('display_errors', 0);
ini_set('log_errors', 1);
// Create logs directory if it doesn't exist
$logsDir = ROOT_PATH . '/logs';
if (!is_dir($logsDir)) {
    @mkdir($logsDir, 0755, true);
}
// Set error log path (fallback to system log if directory creation fails)
$errorLogPath = is_dir($logsDir) ? $logsDir . '/php_errors.log' : null;
if ($errorLogPath) {
    ini_set('error_log', $errorLogPath);
}

// Start Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Autoload Classes
spl_autoload_register(function ($class) {
    $paths = [
        ROOT_PATH . '/includes/classes/' . $class . '.php',
        ROOT_PATH . '/admin/includes/classes/' . $class . '.php',
    ];
    
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            break;
        }
    }
});

