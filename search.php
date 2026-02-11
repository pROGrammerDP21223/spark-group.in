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
// Ensure products and brands are still arrays after breadcrumb
$products = is_array($products) ? $products : (isset($searchProducts) ? $searchProducts : []);
$brands = is_array($brands) ? $brands : (isset($searchBrands) ? $searchBrands : []);

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
$breadcrumbTitle = !empty($searchQuery) ? 'Search Results for "' . htmlspecialchars($searchQuery) . '"' : 'Search';
?>

<div class="breadcrumb-area bg-default " data-background="assets/img/breadcrumb/b1-bg-1.png">
    <div class="container fx-container-1">
        <div class="breadcrumb-wrap">

            <!-- left-content -->
            <div class="breadcrumb-content">

                <div class="breadcrumb-list ">
                    <a href="<?php echo SITE_URL; ?>">Home</a>
                    <span><?php echo $breadcrumbTitle; ?></span>
                </div>

                <h1 class="breadcrumb-title fx-heading-1 text-uppercase " data-txaa-split-text-1><?php echo $breadcrumbTitle; ?></h1>

            </div>

            <!-- right-img -->
            <div class="breadcrumb-img">
                <!-- <img src="assets/img/breadcrumb/b1-img-1.png" alt=""> -->
            </div>

        </div>
    </div>
</div>

<div id="has-jump"></div>

<?php if (empty($originalSearchQuery)): ?>
    <div class="d-flex flex-column align-items-center justify-content-center mt-50">
        <p class="mb-4">Please enter a search term to find products or brands.</p>
        <a href="<?php echo SITE_URL; ?>/enquiry" aria-label="name" class="fx-pr-btn-1">
            <span class="text" data-back="request a quote" data-front="request a quote"></span>
        </a>
    </div>
<?php elseif ($totalResults == 0): ?>
    <div class="d-flex flex-column align-items-center justify-content-center mt-50">
        <p class="mb-4">No results found for "<strong><?php echo htmlspecialchars($searchQuery); ?></strong>".</p>
        <p class="mb-4">Try searching with different keywords.</p>
        <a href="<?php echo SITE_URL; ?>/enquiry" aria-label="name" class="fx-pr-btn-1">
            <span class="text" data-back="request a quote" data-front="request a quote"></span>
        </a>
    </div>
<?php else: ?>
    <div class="d-flex flex-column align-items-center justify-content-center mt-50">
        <p class="mb-4">Found <strong><?php echo $totalResults; ?></strong> result(s) for "<strong><?php echo htmlspecialchars($searchQuery); ?></strong>"</p>
        <a href="<?php echo SITE_URL; ?>/enquiry" aria-label="name" class="fx-pr-btn-1">
            <span class="text" data-back="request a quote" data-front="request a quote"></span>
        </a>
    </div>
    <!-- blog-start -->

    <!-- Products Results -->
    <?php if (!empty($products)): ?>
        <div class="fx-blog-page-area pt-50 pb-120">
            <div class="container fx-container-1">
                <?php if (count($products) > 0): ?>
                    <h3 class="mb-4 text-center">Products (<?php echo count($products); ?>)</h3>
                <?php endif; ?>
                <div class="fx-blog-page-item mb-65">
                    <?php foreach ($products as $product): ?>
                        <?php
                        $productUrl = SITE_URL . '/' . $product['brand_slug'] . '/' . $product['slug'];
                        $image = !empty($product['image']) ? UPLOAD_URL . '/' . htmlspecialchars($product['image']) : 'assets/images/product_img1.jpg';
                        ?>
                        <!-- single-blog -->
                        <a href="<?php echo $productUrl; ?>" aria-label="name"
                           class="fx-blog-1-item-single"
                           style="display: block; text-decoration: none; color: inherit;">
                            <div class="item-img fix img-cover p-relative mb-35">
                                <img src="<?php echo $image; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                            </div>
                            <h4 class="item-title fx-heading-1 fx-font-500">
                                <?php echo htmlspecialchars($product['name']); ?>
                            </h4>
                            <?php if (!empty($product['brand_name'])): ?>
                                <p class="text-muted"><small>Brand: <?php echo htmlspecialchars($product['brand_name']); ?></small></p>
                            <?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Brands Results -->
    <?php if (!empty($brands)): ?>
        <div class="fx-blog-page-area pt-50 pb-120">
            <div class="container fx-container-1">
                <?php if (count($brands) > 0): ?>
                    <h3 class="mb-4 text-center">Brands (<?php echo count($brands); ?>)</h3>
                <?php endif; ?>
                <div class="fx-blog-page-item mb-65">
                    <?php foreach ($brands as $brand): ?>
                        <?php
                        $brandUrl = SITE_URL . '/' . $brand['slug'];
                        $image = !empty($brand['image']) ? UPLOAD_URL . '/' . htmlspecialchars($brand['image']) : 'assets/images/product_img1.jpg';
                        ?>
                        <!-- single-blog -->
                        <a href="<?php echo $brandUrl; ?>" aria-label="name"
                           class="fx-blog-1-item-single"
                           style="display: block; text-decoration: none; color: inherit;">
                            <div class="item-img fix img-cover p-relative mb-35">
                                <img src="<?php echo $image; ?>" alt="<?php echo htmlspecialchars($brand['name']); ?>">
                            </div>
                            <h4 class="item-title fx-heading-1 fx-font-500">
                                <?php echo htmlspecialchars($brand['name']); ?>
                            </h4>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
    <!-- blog-end -->
<?php endif; ?>

<?php require __DIR__ . '/includes/public/footer.php'; ?>

