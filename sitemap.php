<?php
/**
 * Sitemap Generator
 * Generates XML sitemap for SEO
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

$db = Database::getInstance()->getConnection();

header('Content-Type: application/xml; charset=utf-8');

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

// Home page
echo "  <url>\n";
echo "    <loc>" . htmlspecialchars(SITE_URL) . "</loc>\n";
echo "    <changefreq>daily</changefreq>\n";
echo "    <priority>1.0</priority>\n";
echo "  </url>\n";

// Static pages
$staticPages = [
    'about-us' => 'monthly',
    'certifications' => 'monthly',
    'testimonials' => 'weekly',
    'contact-us' => 'monthly',
    'enquiry' => 'monthly'
];

foreach ($staticPages as $page => $freq) {
    echo "  <url>\n";
    echo "    <loc>" . htmlspecialchars(SITE_URL . '/' . $page) . "</loc>\n";
    echo "    <changefreq>$freq</changefreq>\n";
    echo "    <priority>0.8</priority>\n";
    echo "  </url>\n";
}

// Get all active cities
$cities = $db->query("SELECT * FROM cities WHERE status = 'active'")->fetchAll();

// Brands (base and city-wise)
$brands = $db->query("SELECT * FROM brands WHERE status = 'active'")->fetchAll();
foreach ($brands as $brand) {
    // Base brand page
    echo "  <url>\n";
    echo "    <loc>" . htmlspecialchars(SITE_URL . '/' . $brand['slug']) . "</loc>\n";
    echo "    <changefreq>weekly</changefreq>\n";
    echo "    <priority>0.9</priority>\n";
    echo "  </url>\n";
    
    // City-wise brand pages
    foreach ($cities as $city) {
        echo "  <url>\n";
        echo "    <loc>" . htmlspecialchars(SITE_URL . '/' . $brand['slug'] . '-' . $city['slug']) . "</loc>\n";
        echo "    <changefreq>weekly</changefreq>\n";
        echo "    <priority>0.9</priority>\n";
        echo "  </url>\n";
    }
}

// Products (base and city-wise) – directly under brands (no categories)
$products = $db->query("SELECT p.*, b.slug as brand_slug 
                        FROM products p 
                        LEFT JOIN brands b ON p.brand_id = b.id 
                        WHERE p.status = 'active' AND b.status = 'active'")->fetchAll();

foreach ($products as $product) {
    // Base product page
    echo "  <url>\n";
    echo "    <loc>" . htmlspecialchars(SITE_URL . '/' . $product['brand_slug'] . '/' . $product['slug']) . "</loc>\n";
    echo "    <changefreq>monthly</changefreq>\n";
    echo "    <priority>0.7</priority>\n";
    echo "  </url>\n";
    
    // City-wise product pages
    foreach ($cities as $city) {
        echo "  <url>\n";
        echo "    <loc>" . htmlspecialchars(SITE_URL . '/' . $product['brand_slug'] . '/' . $product['slug'] . '-' . $city['slug']) . "</loc>\n";
        echo "    <changefreq>monthly</changefreq>\n";
        echo "    <priority>0.7</priority>\n";
        echo "  </url>\n";
    }
}

echo '</urlset>';

