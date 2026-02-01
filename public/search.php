<?php
/**
 * Search Results Page
 */

$searchQuery = isset($_GET['q']) ? trim($_GET['q']) : '';
$searchQuery = htmlspecialchars($searchQuery, ENT_QUOTES, 'UTF-8');

// Set SEO data
$pageSEO = [
    'meta_title' => !empty($searchQuery) ? 'Search Results for "' . $searchQuery . '" - ' . SITE_NAME : 'Search - ' . SITE_NAME,
    'meta_description' => !empty($searchQuery) ? 'Search results for: ' . $searchQuery : 'Search our products and categories',
    'meta_keywords' => '',
    'canonical_url' => SITE_URL . '/search' . (!empty($searchQuery) ? '?q=' . urlencode($searchQuery) : ''),
    'og_title' => 'Search Results',
    'og_description' => !empty($searchQuery) ? 'Search results for: ' . $searchQuery : 'Search our products and categories',
    'og_image' => SITE_URL . '/assets/images/logo_light.png',
    'h1_text' => !empty($searchQuery) ? 'Search Results for "' . $searchQuery . '"' : 'Search',
    'h2_text' => '',
    'seo_head' => ''
];

// Search products and categories
$products = [];
$categories = [];
$brands = [];

if (!empty($searchQuery)) {
    // Escape special LIKE characters and create search terms
    $escapedQuery = str_replace(['%', '_'], ['\%', '\_'], $searchQuery);
    $searchTerm = '%' . $escapedQuery . '%';
    
    // Search products - only match product name
    $productStmt = $db->prepare("
        SELECT p.*, 
               pc.name as category_name, 
               pc.slug as category_slug,
               b.name as brand_name,
               b.slug as brand_slug
        FROM products p
        LEFT JOIN product_categories pc ON p.category_id = pc.id
        LEFT JOIN brands b ON p.brand_id = b.id
        WHERE p.status = 'active' 
        AND p.name LIKE ?
        ORDER BY p.name ASC
    ");
    $productStmt->execute([$searchTerm]);
    $products = $productStmt->fetchAll();
    
    // Search categories - only match category name
    $categoryStmt = $db->prepare("
        SELECT pc.*, 
               b.name as brand_name,
               b.slug as brand_slug
        FROM product_categories pc
        LEFT JOIN brands b ON pc.brand_id = b.id
        WHERE pc.status = 'active' 
        AND pc.name LIKE ?
        ORDER BY pc.name ASC
    ");
    $categoryStmt->execute([$searchTerm]);
    $categories = $categoryStmt->fetchAll();
    
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
    $searchLower = strtolower($searchQuery);
    foreach ($brands as $brand) {
        $brandNameLower = strtolower($brand['name']);
        if (strpos($brandNameLower, $searchLower) !== false) {
            $filteredBrands[] = $brand;
        }
    }
    $brands = $filteredBrands;
}

require __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/breadcrumb.php';
?>

<div class="offcanvas-overlay"></div>

<?php
// Render breadcrumb
renderBreadcrumb($pageSEO['h1_text'], [
    ['text' => 'Home', 'url' => SITE_URL],
    ['text' => 'Search']
]);
?>

<!-- START SECTION SHOP -->
<div class="section">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <?php if (empty($searchQuery)): ?>
                    <div class="text-center py-5">
                        <p class="text-muted">Please enter a search term to find products, categories, or brands.</p>
                    </div>
                <?php elseif (empty($products) && empty($categories) && empty($brands)): ?>
                    <div class="text-center py-5">
                        <p class="text-muted">No results found for "<strong><?php echo $searchQuery; ?></strong>".</p>
                        <p class="text-muted">Try searching with different keywords.</p>
                    </div>
                <?php else: ?>
                    <?php 
                    // Filter out brands that don't actually match (double-check)
                    $filteredBrands = [];
                    foreach ($brands as $brand) {
                        if (stripos($brand['name'], $searchQuery) !== false) {
                            $filteredBrands[] = $brand;
                        }
                    }
                    $brands = $filteredBrands;
                    
                    $totalResults = count($products) + count($categories) + count($brands);
                    ?>
                    <p class="mb-4">Found <strong><?php echo $totalResults; ?></strong> result(s) for "<strong><?php echo $searchQuery; ?></strong>"</p>

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

                    <!-- Categories Results -->
                    <?php if (!empty($categories)): ?>
                        <div class="mb-5">
                            <h3 class="mb-4">Categories (<?php echo count($categories); ?>)</h3>
                            <div class="row shop_container">
                                <?php foreach ($categories as $category): ?>
                                    <?php
                                    $categoryUrl = SITE_URL . '/' . $category['brand_slug'] . '/' . $category['slug'];
                                    ?>
                                    <div class="col-lg-3 col-md-4 col-6 grid_item">
                                        <a href="<?php echo $categoryUrl; ?>" class="product_wrap_link" style="display:block;text-decoration:none;color:inherit;">
                                            <div class="product">
                                                <div class="product_img">
                                                    <?php if (!empty($category['image'])): ?>
                                                        <img src="<?php echo UPLOAD_URL . '/' . htmlspecialchars($category['image']); ?>" alt="<?php echo htmlspecialchars($category['name']); ?>">
                                                    <?php else: ?>
                                                        <img src="assets/images/product_img1.jpg" alt="<?php echo htmlspecialchars($category['name']); ?>">
                                                    <?php endif; ?>
                                                </div>
                                                <div class="product_info">
                                                    <h6 class="product_title"><?php echo htmlspecialchars($category['name']); ?></h6>
                                                    <?php if (!empty($category['brand_name'])): ?>
                                                        <div class="pr_desc">
                                                            <p><small>Brand: <?php echo htmlspecialchars($category['brand_name']); ?></small></p>
                                                        </div>
                                                    <?php endif; ?>
                                                    <?php if (!empty($category['description'])): ?>
                                                        <div class="pr_desc">
                                                            <p><?php echo htmlspecialchars(substr($category['description'], 0, 100)); ?><?php echo strlen($category['description']) > 100 ? '...' : ''; ?></p>
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
                                    $productUrl = SITE_URL . '/' . $product['brand_slug'] . '/' . $product['category_slug'] . '/' . $product['slug'];
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
                                                    <?php if (!empty($product['category_name'])): ?>
                                                        <div class="pr_desc">
                                                            <p><small><?php echo htmlspecialchars($product['category_name']); ?></small></p>
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
            </div>
        </div>
    </div>
</div>
<!-- END SECTION SHOP -->

<?php require __DIR__ . '/includes/footer.php'; ?>

