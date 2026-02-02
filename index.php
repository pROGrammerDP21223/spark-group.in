<?php
/**
 * Public Website - Main Router
 * Handles all public-facing pages including city-wise dynamic pages
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/AppContext.php';

$db = Database::getInstance()->getConnection();
// Create shared application context (brands, categories, contact, etc.)
$app = new AppContext($db);

// Parse QUERY_STRING to ensure parameters set via fastcgi_param QUERY_STRING are available in $_GET
// This is critical for nginx routing where QUERY_STRING is set directly
if (!empty($_SERVER['QUERY_STRING'])) {
    parse_str($_SERVER['QUERY_STRING'], $parsed);
    $_GET = array_merge($parsed, $_GET); // Merge parsed params first, then existing $_GET (so URL params override)
}

// Get page type and parameters
$type = $_GET['type'] ?? 'home';
$slug = $_GET['slug'] ?? '';
$brand = $_GET['brand'] ?? '';
$category = $_GET['category'] ?? '';
$city = $_GET['city'] ?? '';
$page = $_GET['page'] ?? 'home';

// Get city data if city slug is provided
$cityData = null;
$cityId = null;
if (!empty($city)) {
    $stmt = $db->prepare("SELECT * FROM cities WHERE slug = ? AND status = 'active'");
    $stmt->execute([$city]);
    $cityData = $stmt->fetch();
    if ($cityData) {
        $cityId = $cityData['id'];
    }
}

// Route to appropriate page
// Check type first (for dynamic routes)
if ($type === 'brand') {
    require __DIR__ . '/brand_detail.php';
    exit;
} elseif ($type === 'category') {
    require __DIR__ . '/category_detail.php';
    exit;
} elseif ($type === 'product') {
    require __DIR__ . '/product_detail.php';
    exit;
} elseif ($type === 'search') {
    require __DIR__ . '/search.php';
    exit;
}

// Handle static pages
if ($page === 'about') {
    require __DIR__ . '/about.php';
} elseif ($page === 'certifications') {
    require __DIR__ . '/certifications.php';
} elseif ($page === 'testimonials') {
    require __DIR__ . '/testimonials.php';
} elseif ($page === 'contact') {
    require __DIR__ . '/contact.php';
} elseif ($page === 'enquiry') {
    require __DIR__ . '/enquiry.php';
} elseif ($page === '404') {
    require __DIR__ . '/404.php';
} else {
    // Default to home page
    require __DIR__ . '/home.php';
}

