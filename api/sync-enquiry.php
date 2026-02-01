<?php
/**
 * API Endpoint: Sync External Enquiry Form Data to Local Database
 * Called after external form submission succeeds
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

$db = Database::getInstance()->getConnection();

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Get JSON input
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid JSON data']);
    exit;
}

// Map external form fields to database fields
$name = sanitize($data['full_name'] ?? $data['name'] ?? '');
$email = sanitize($data['email'] ?? '');
$phone = sanitize($data['mobile'] ?? $data['phone'] ?? '');
$company = sanitize($data['company_name'] ?? $data['company'] ?? '');
$message = sanitize($data['enquiry_details'] ?? $data['message'] ?? $data['enquiry'] ?? '');

// Add address to message if provided
if (!empty($data['address'])) {
    $address = sanitize($data['address']);
    if (!empty($message)) {
        $message .= "\n\nAddress: " . $address;
    } else {
        $message = "Address: " . $address;
    }
}

// Get product/brand/category IDs from URL parameters or data
$product_id = intval($data['product_id'] ?? $_GET['product_id'] ?? 0);
$brand_id = intval($data['brand_id'] ?? $_GET['brand_id'] ?? 0);
$category_id = intval($data['category_id'] ?? $_GET['category_id'] ?? 0);

// If category_id provided, get its brand_id
if ($category_id && !$brand_id) {
    $catStmt = $db->prepare("SELECT brand_id FROM product_categories WHERE id = ?");
    $catStmt->execute([$category_id]);
    $catData = $catStmt->fetch();
    if ($catData) {
        $brand_id = $catData['brand_id'];
    }
}

// If product_id provided, get its brand_id if not already set
if ($product_id && !$brand_id) {
    $prodStmt = $db->prepare("SELECT brand_id FROM products WHERE id = ?");
    $prodStmt->execute([$product_id]);
    $prodData = $prodStmt->fetch();
    if ($prodData) {
        $brand_id = $prodData['brand_id'];
    }
}

// Validate required fields
if (empty($name) || empty($email) || empty($message)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Name, email and message are required']);
    exit;
}

if (!isValidEmail($email)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid email address']);
    exit;
}

// Insert into local database
try {
    $sql = "INSERT INTO enquiries (name, email, phone, company, subject, message, product_id, brand_id, ip_address, user_agent) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $db->prepare($sql);
    
    $subject = !empty($data['subject']) ? sanitize($data['subject']) : 'Enquiry from ' . $company;
    
    $stmt->execute([
        $name, 
        $email, 
        $phone, 
        $company, 
        $subject, 
        $message, 
        $product_id ?: null, 
        $brand_id ?: null,
        $_SERVER['REMOTE_ADDR'] ?? '',
        $_SERVER['HTTP_USER_AGENT'] ?? ''
    ]);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Enquiry synced to local database successfully',
        'enquiry_id' => $db->lastInsertId()
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}

