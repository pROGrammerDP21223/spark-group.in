<?php
/**
 * Sitemap Generator - TXT Format
 * Generates text sitemap (one URL per line)
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

$db = Database::getInstance()->getConnection();

header('Content-Type: text/plain; charset=utf-8');

$urls = [];

// Home page
$urls[] = SITE_URL;

// Static pages
$staticPages = ['about-us', 'certifications', 'testimonials', 'contact-us', 'enquiry'];
foreach ($staticPages as $page) {
    $urls[] = SITE_URL . '/' . $page;
}

// Get all active cities
$cities = $db->query("SELECT * FROM cities WHERE status = 'active'")->fetchAll();

// Brands (base and city-wise)
$brands = $db->query("SELECT * FROM brands WHERE status = 'active'")->fetchAll();
foreach ($brands as $brand) {
    // Base brand page
    $urls[] = SITE_URL . '/' . $brand['slug'];
    
    // City-wise brand pages
    foreach ($cities as $city) {
        $urls[] = SITE_URL . '/' . $brand['slug'] . '-' . $city['slug'];
    }
}

// Products (base and city-wise)
$products = $db->query("SELECT p.*, b.slug as brand_slug 
                        FROM products p 
                        LEFT JOIN brands b ON p.brand_id = b.id 
                        WHERE p.status = 'active' AND b.status = 'active'")->fetchAll();

foreach ($products as $product) {
    // Base product page
    $urls[] = SITE_URL . '/' . $product['brand_slug'] . '/' . $product['slug'];
    
    // City-wise product pages
    foreach ($cities as $city) {
        $urls[] = SITE_URL . '/' . $product['brand_slug'] . '/' . $product['slug'] . '-' . $city['slug'];
    }
}

// Output all URLs (one per line)
echo implode("\n", array_map('htmlspecialchars', $urls));

