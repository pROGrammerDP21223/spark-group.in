<?php
/**
 * Public Website - Main Router
 * Handles all public-facing pages including city-wise dynamic pages
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/AppContext.php';

$db = Database::getInstance()->getConnection();
// Create shared application context (brands, categories, contact, etc.)
$app = new AppContext($db);

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
switch ($type) {
    case 'brand':
        require __DIR__ . '/brand_detail.php';
        break;
    
    case 'category':
        require __DIR__ . '/category_detail.php';
        break;
    
    case 'product':
        require __DIR__ . '/product_detail.php';
        break;
    
    case 'search':
        require __DIR__ . '/search.php';
        break;
    
    default:
        // Static pages
        switch ($page) {
            case 'about':
                require __DIR__ . '/about.php';
                break;
            case 'certifications':
                require __DIR__ . '/certifications.php';
                break;
            case 'testimonials':
                require __DIR__ . '/testimonials.php';
                break;
            case 'contact':
                require __DIR__ . '/contact.php';
                break;
            case 'enquiry':
                require __DIR__ . '/enquiry.php';
                break;
            case '404':
                require __DIR__ . '/404.php';
                break;
            default:
                require __DIR__ . '/home.php';
                break;
        }
        break;
}

