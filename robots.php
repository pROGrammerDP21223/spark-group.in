<?php
/**
 * Robots.txt Generator
 * Automatically generates robots.txt with sitemap reference
 */

require_once __DIR__ . '/config/config.php';

header('Content-Type: text/plain; charset=utf-8');

// User-agent rules
echo "User-agent: *\n";
echo "Allow: /\n";
echo "Disallow: /admin/\n";
echo "Disallow: /api/\n";
echo "Disallow: /config/\n";
echo "Disallow: /includes/\n";
echo "Disallow: /logs/\n";
echo "Disallow: /database/\n";
echo "\n";

// Sitemap references
echo "# Sitemaps\n";
echo "Sitemap: " . SITE_URL . "/sitemap.xml\n";
echo "Sitemap: " . SITE_URL . "/sitemap.txt\n";
echo "\n";

// Crawl-delay (optional, can be removed if not needed)
// echo "Crawl-delay: 1\n";

