<?php
/**
 * Search Results Page
 */

// Ensure database connection is available
if (!isset($db)) {
    require_once __DIR__ . '/config/config.php';
    require_once __DIR__ . '/config/database.php';
    $db = Database::getInstance()->getConnection();
}

// Get search query from GET parameter
// Try multiple ways to get the query string in case of rewrite issues
$originalSearchQuery = '';
if (isset($_GET['q'])) {
    $originalSearchQuery = trim($_GET['q']);
} elseif (isset($_REQUEST['q'])) {
    $originalSearchQuery = trim($_REQUEST['q']);
} elseif (!empty($_SERVER['QUERY_STRING'])) {
    parse_str($_SERVER['QUERY_STRING'], $queryParams);
    if (isset($queryParams['q'])) {
        $originalSearchQuery = trim($queryParams['q']);
    }
}

// Store original for database search, HTML escape for display
$searchQuery = htmlspecialchars($originalSearchQuery, ENT_QUOTES, 'UTF-8');

// Set SEO data
$pageSEO = [
    'meta_title' => !empty($searchQuery) ? 'Search Results for "' . $searchQuery . '" - ' . SITE_NAME : 'Search - ' . SITE_NAME,
    'meta_description' => !empty($searchQuery) ? 'Search results for: ' . $searchQuery : 'Search our products and brands',
    'meta_keywords' => '',
    'canonical_url' => SITE_URL . '/search' . (!empty($searchQuery) ? '?q=' . urlencode($searchQuery) : ''),
    'og_title' => 'Search Results',
    'og_description' => !empty($searchQuery) ? 'Search results for: ' . $searchQuery : 'Search our products and brands',
    'og_image' => SITE_URL . '/assets/images/logo_light.png',
    'h1_text' => !empty($searchQuery) ? 'Search Results for "' . $searchQuery . '"' : 'Search',
    'h2_text' => '',
    'seo_head' => ''
];

// Search products and brands
$products = [];
$brands = [];

if (!empty($originalSearchQuery)) {
    try {
        // Escape special LIKE characters and create search terms (use original, not HTML-escaped)
        $escapedQuery = str_replace(['%', '_'], ['\%', '\_'], $originalSearchQuery);
        $searchTerm = '%' . $escapedQuery . '%';
        
        // Search products - only match product name (case-insensitive)
        // Removed brand status check to ensure all active products are found
        $productStmt = $db->prepare("
            SELECT p.*, 
                   b.name as brand_name,
                   b.slug as brand_slug
            FROM products p
            LEFT JOIN brands b ON p.brand_id = b.id
            WHERE p.status = 'active' 
            AND LOWER(p.name) LIKE LOWER(?)
            ORDER BY p.name ASC
        ");
        $productStmt->execute([$searchTerm]);
        $products = $productStmt->fetchAll();
        
        // Search brands - only match brand name (case-insensitive, exact substring match)
        $brandStmt = $db->prepare("
            SELECT *
            FROM brands
            WHERE status = 'active' 
            AND LOWER(name) LIKE LOWER(?)
            ORDER BY name ASC
        ");
        $brandStmt->execute([$searchTerm]);
        $brands = $brandStmt->fetchAll();
        
        // Double-check: Filter brands to ensure they actually contain the search term in their name
        $filteredBrands = [];
        $searchLower = strtolower($originalSearchQuery);
        foreach ($brands as $brand) {
            $brandNameLower = strtolower($brand['name']);
            if (strpos($brandNameLower, $searchLower) !== false) {
                $filteredBrands[] = $brand;
            }
        }
        $brands = $filteredBrands;
    } catch (Exception $e) {
        // Log error but don't break the page
        error_log("Search error: " . $e->getMessage());
        $products = [];
        $brands = [];
    }
}

// Store products and brands in different variable names to avoid conflicts
$searchProducts = $products;
$searchBrands = $brands;

require __DIR__ . '/includes/public/header.php';
require_once __DIR__ . '/includes/public/breadcrumb.php';

// Restore after header/breadcrumb (in case they overwrote variables)
$products = $searchProducts;
$brands = $searchBrands;
?>

<div class="offcanvas-overlay"></div>

<?php
// Render breadcrumb
renderBreadcrumb($pageSEO['h1_text'], [
    ['text' => 'Home', 'url' => SITE_URL],
    ['text' => 'Search']
]);

// Ensure products and brands are still arrays after breadcrumb
$products = is_array($products) ? $products : (isset($searchProducts) ? $searchProducts : []);
$brands = is_array($brands) ? $brands : (isset($searchBrands) ? $searchBrands : []);
?>

<!-- START SECTION SHOP -->
<div class="section">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <?php 
                // Ensure variables are arrays
                $products = is_array($products) ? $products : [];
                $brands = is_array($brands) ? $brands : [];
                ?>
                <?php if (empty($originalSearchQuery)): ?>
                    <div class="text-center py-5">
                        <p class="text-muted">Please enter a search term to find products or brands.</p>
                    </div>
                <?php else: ?>
                    <?php 
                    // Filter out brands that don't actually match (double-check)
                    $filteredBrands = [];
                    if (!empty($brands)) {
                        foreach ($brands as $brand) {
                            if (stripos($brand['name'], $originalSearchQuery) !== false) {
                                $filteredBrands[] = $brand;
                            }
                        }
                    }
                    $brands = $filteredBrands;
                    
                    $totalResults = count($products) + count($brands);
                    ?>
                    
                    <?php if ($totalResults == 0): ?>
                        <div class="text-center py-5">
                            <p class="text-muted">No results found for "<strong><?php echo htmlspecialchars($searchQuery); ?></strong>".</p>
                            <p class="text-muted">Try searching with different keywords.</p>
                        </div>
                    <?php else: ?>
                        <p class="mb-4">Found <strong><?php echo $totalResults; ?></strong> result(s) for "<strong><?php echo htmlspecialchars($searchQuery); ?></strong>"</p>

                    <!-- Brands Results -->
                    <?php if (!empty($brands)): ?>
                        <div class="mb-5">
                            <h3 class="mb-4">Brands (<?php echo count($brands); ?>)</h3>
                            <div class="row shop_container">
                                <?php foreach ($brands as $brand): ?>
                                    <?php
                                    $brandUrl = SITE_URL . '/' . $brand['slug'];
                                    ?>
                                    <div class="col-lg-3 col-md-4 col-6 grid_item">
                                        <a href="<?php echo $brandUrl; ?>" class="product_wrap_link" style="display:block;text-decoration:none;color:inherit;">
                                            <div class="product">
                                                <div class="product_img">
                                                    <?php if (!empty($brand['image'])): ?>
                                                        <img src="<?php echo UPLOAD_URL . '/' . htmlspecialchars($brand['image']); ?>" alt="<?php echo htmlspecialchars($brand['name']); ?>">
                                                    <?php else: ?>
                                                        <img src="assets/images/product_img1.jpg" alt="<?php echo htmlspecialchars($brand['name']); ?>">
                                                    <?php endif; ?>
                                                </div>
                                                <div class="product_info">
                                                    <h6 class="product_title"><?php echo htmlspecialchars($brand['name']); ?></h6>
                                                    <?php if (!empty($brand['description'])): ?>
                                                        <div class="pr_desc">
                                                            <p><?php echo htmlspecialchars(substr($brand['description'], 0, 100)); ?><?php echo strlen($brand['description']) > 100 ? '...' : ''; ?></p>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Products Results -->
                    <?php if (!empty($products)): ?>
                        <div class="mb-5">
                            <h3 class="mb-4">Products (<?php echo count($products); ?>)</h3>
                            <div class="row shop_container">
                                <?php foreach ($products as $product): ?>
                                    <?php
                                    $productUrl = SITE_URL . '/' . $product['brand_slug'] . '/' . $product['slug'];
                                    $productImage = !empty($product['image']) ? UPLOAD_URL . '/' . $product['image'] : SITE_URL . '/assets/images/product_img1.jpg';
                                    ?>
                                    <div class="col-lg-3 col-md-4 col-6 grid_item">
                                        <a href="<?php echo $productUrl; ?>" class="product_wrap_link" style="display:block;text-decoration:none;color:inherit;">
                                            <div class="product">
                                                <div class="product_img">
                                                    <img src="<?php echo htmlspecialchars($productImage); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                                                </div>
                                                <div class="product_info">
                                                    <h6 class="product_title"><?php echo htmlspecialchars($product['name']); ?></h6>
                                                    <?php if (!empty($product['brand_name'])): ?>
                                                        <div class="pr_desc">
                                                            <p><small>Brand: <?php echo htmlspecialchars($product['brand_name']); ?></small></p>
                                                        </div>
                                                    <?php endif; ?>
                                                    <?php if (!empty($product['short_description'])): ?>
                                                        <div class="pr_desc">
                                                            <p><?php echo htmlspecialchars(substr($product['short_description'], 0, 100)); ?><?php echo strlen($product['short_description']) > 100 ? '...' : ''; ?></p>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<!-- END SECTION SHOP -->

<?php require __DIR__ . '/includes/public/footer.php'; ?>

