<?php
$currentPage = 'sitemap';
$pageTitle = 'Sitemap Generator';
require_once __DIR__ . '/includes/header.php';

$db = Database::getInstance()->getConnection();

$message = '';
if (isset($_POST['generate'])) {
    // Sitemap is generated dynamically, but we can create a static file if needed
    $message = 'Sitemap is generated dynamically at: <a href="' . SITE_URL . '/sitemap.xml" target="_blank">' . SITE_URL . '/sitemap.xml</a>';
}

// Get statistics
$stats = [
    'total_urls' => 0,
    'brands' => $db->query("SELECT COUNT(*) FROM brands WHERE status = 'active'")->fetchColumn(),
    'categories' => $db->query("SELECT COUNT(*) FROM product_categories WHERE status = 'active'")->fetchColumn(),
    'products' => $db->query("SELECT COUNT(*) FROM products WHERE status = 'active'")->fetchColumn(),
    'cities' => $db->query("SELECT COUNT(*) FROM cities WHERE status = 'active'")->fetchColumn(),
];

// Calculate approximate total URLs
$stats['total_urls'] = 1 + // Home
                       5 + // Static pages
                       ($stats['brands'] * (1 + $stats['cities'])) + // Brands
                       ($stats['categories'] * (1 + $stats['cities'])) + // Categories
                       ($stats['products'] * (1 + $stats['cities'])); // Products
?>
<div class="content-card">
    <h5 class="mb-4">Sitemap Generator</h5>
    
    <?php if ($message): ?>
        <div class="alert alert-info"><?php echo $message; ?></div>
    <?php endif; ?>
    
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h3><?php echo number_format($stats['total_urls']); ?></h3>
                    <p class="text-muted mb-0">Total URLs</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h3><?php echo $stats['brands']; ?></h3>
                    <p class="text-muted mb-0">Brands</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h3><?php echo $stats['categories']; ?></h3>
                    <p class="text-muted mb-0">Categories</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h3><?php echo $stats['products']; ?></h3>
                    <p class="text-muted mb-0">Products</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="alert alert-info">
        <h6><i class="bi bi-info-circle"></i> About Sitemap</h6>
        <p class="mb-0">
            Your sitemap is automatically generated and available at: 
            <a href="<?php echo SITE_URL; ?>/sitemap.xml" target="_blank">
                <?php echo SITE_URL; ?>/sitemap.xml
            </a>
        </p>
        <p class="mb-0 mt-2">
            The sitemap includes:
            <ul class="mb-0">
                <li>All static pages (Home, About, Contact, etc.)</li>
                <li>All brand pages (base and city-wise)</li>
                <li>All category pages (base and city-wise)</li>
                <li>All product pages (base and city-wise)</li>
            </ul>
        </p>
    </div>
    
    <div class="mt-4">
        <a href="<?php echo SITE_URL; ?>/sitemap.xml" target="_blank" class="btn btn-primary">
            <i class="bi bi-box-arrow-up-right"></i> View Sitemap
        </a>
        <a href="https://search.google.com/search-console" target="_blank" class="btn btn-success">
            <i class="bi bi-google"></i> Submit to Google Search Console
        </a>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

