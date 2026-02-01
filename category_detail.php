<?php
/**
 * Category Detail Page (with city support) - Aments Design
 * Handles: /brand/category or /brand/category-city
 */

if (empty($brand) || empty($slug)) {
    redirect(SITE_URL . '/404');
}

// If city is passed from URL but cityData not set, look it up
if (!empty($city) && empty($cityData)) {
    $cityCheck = $db->prepare("SELECT * FROM cities WHERE slug = ? AND status = 'active'");
    $cityCheck->execute([$city]);
    $cityData = $cityCheck->fetch();
    if ($cityData) {
        $cityId = $cityData['id'];
    }
}

// If city is not set but slug contains hyphen, try to extract city from slug
if (empty($city) && strpos($slug, '-') !== false) {
    $parts = explode('-', $slug);
    $lastPart = end($parts);
    
    // Check if last part is a valid city slug
    $cityCheck = $db->prepare("SELECT * FROM cities WHERE slug = ? AND status = 'active'");
    $cityCheck->execute([$lastPart]);
    $cityCheckResult = $cityCheck->fetch();
    
    if ($cityCheckResult) {
        // Last part is a city, so first part(s) is the category slug
        array_pop($parts); // Remove city part
        $slug = implode('-', $parts); // Rejoin remaining parts as category slug
        $city = $lastPart;
        $cityData = $cityCheckResult;
        $cityId = $cityData['id'];
    }
}

// Get brand
$brandStmt = $db->prepare("SELECT * FROM brands WHERE slug = ? AND status = 'active'");
$brandStmt->execute([$brand]);
$brandData = $brandStmt->fetch();

if (!$brandData) {
    redirect(SITE_URL . '/404');
}

// Get category
$categoryStmt = $db->prepare("SELECT * FROM product_categories WHERE slug = ? AND brand_id = ? AND status = 'active'");
$categoryStmt->execute([$slug, $brandData['id']]);
$category = $categoryStmt->fetch();

if (!$category) {
    redirect(SITE_URL . '/404');
}

// Get SEO data
$seoData = getSEOData($db, 'category', $category['id'], $cityId);

// Build H1 dynamically
if (empty($seoData['h1_text'])) {
    $seoData['h1_text'] = $category['name'];
    if ($cityData) {
        $seoData['h1_text'] .= ' Authorised Dealer Distributor and Supplier in ' . $cityData['name'];
    }
}

// Build meta title
if (empty($seoData['meta_title'])) {
    $seoData['meta_title'] = $category['name'] . ' - ' . $brandData['name'];
    if ($cityData) {
        $seoData['meta_title'] .= ' Authorised Dealer Distributor and Supplier in ' . $cityData['name'];
    }
    $seoData['meta_title'] .= ' - ' . SITE_NAME;
}

// Build canonical URL
if (empty($seoData['canonical_url'])) {
    $seoData['canonical_url'] = SITE_URL . '/' . $brandData['slug'] . '/' . $category['slug'];
    if ($cityData) {
        $seoData['canonical_url'] .= '-' . $cityData['slug'];
    }
}

// Get products for this category (with pagination)
$pageNum = max(1, intval($_GET['page'] ?? 1));
$offset = ($pageNum - 1) * ITEMS_PER_PAGE;

$totalProducts = $db->prepare("SELECT COUNT(*) FROM products WHERE category_id = ? AND status = 'active'");
$totalProducts->execute([$category['id']]);
$totalProducts = $totalProducts->fetchColumn();
$totalPages = ceil($totalProducts / ITEMS_PER_PAGE);

$products = $db->prepare("SELECT * FROM products 
                          WHERE category_id = ? AND status = 'active' 
                          ORDER BY sort_order ASC, name ASC 
                          LIMIT " . ITEMS_PER_PAGE . " OFFSET $offset");
$products->execute([$category['id']]);
$products = $products->fetchAll();

// Set page SEO
$pageSEO = $seoData;
// Add SEO head code if exists
if (!empty($seoData['seo_head'])) {
    $pageSEO['seo_head'] = $seoData['seo_head'];
}

require __DIR__ . '/includes/public/header.php';
require_once __DIR__ . '/includes/public/breadcrumb.php';
?>

<div class="offcanvas-overlay"></div>

<?php
// Store category data in safe variables before including header (which may overwrite $category)
$currentCategoryName = $category['name'];
$currentCategorySlug = $category['slug'];
$currentBrandName = $brandData['name'];
$currentBrandSlug = $brandData['slug'];

// Use SEO H1 text for breadcrumb if available, otherwise use category name
$breadcrumbTitle = !empty($seoData['h1_text']) ? $seoData['h1_text'] : $currentCategoryName;
if ($cityData && stripos($breadcrumbTitle, $cityData['name']) === false) {
    $breadcrumbTitle .= ' Authorised Dealer Distributor and Supplier in ' . $cityData['name'];
}

// Render breadcrumb
renderBreadcrumb($breadcrumbTitle, [
    ['text' => $currentBrandName, 'url' => SITE_URL . '/' . $currentBrandSlug],
    ['text' => $breadcrumbTitle]
]);
?>

<!-- ...:::: Start Shop Section:::... -->
<div class="shop-section">
    <div class="container">
        <div class="row flex-column-reverse flex-lg-row">
            <!-- Start Product Content -->
              <div class="col-lg-12">
              <br><br>
                <?php if (!empty($pageSEO['h2_text'])): ?>
                    <p class="mb-4"><?php echo htmlspecialchars($pageSEO['h2_text']); ?></p>
                <?php endif; ?>
                <div class="text-center mb-4">
                    <a href="<?php echo SITE_URL; ?>/enquiry?category_id=<?php echo $category['id']; ?>&brand_id=<?php echo $brandData['id']; ?>" class="btn btn-fill-out">
                        <i class="icon-envelope"></i> Send Enquiry for <?php echo htmlspecialchars($currentCategoryName); ?>
                    </a>
                </div>
                    <!-- START SECTION SHOP (dynamic products in Shopwise style) -->
                    <div class="section">
                        <div class="container">
                            <div class="row">
                                <div class="col-12">
                                    <?php if (empty($products)): ?>
                                        <p class="text-center text-muted py-5">No products found in this category.</p>
                                    <?php else: ?>
                                        <div class="row shop_container loadmore"
                                             data-item="8"
                                             data-item-show="4"
                                             data-finish-message="No More Item to Show"
                                             data-btn="Load More">
                                            <?php foreach ($products as $index => $product): ?>
                                                <?php
                                                $productUrl = SITE_URL . '/' . $brandData['slug'] . '/' . $category['slug'] . '/' . $product['slug'];
                                                if ($cityData) {
                                                    $productUrl .= '-' . $cityData['slug'];
                                                }
                                                ?>
                                                <div class="col-lg-3 col-md-4 col-6 grid_item">
                                                    <a href="<?php echo $productUrl; ?>" class="product_wrap_link" style="display:block;text-decoration:none;color:inherit;">
                                                        <div class="product">
                                                            <div class="product_img">
                                                                <?php if ($product['image']): ?>
                                                                    <img src="<?php echo UPLOAD_URL . '/' . $product['image']; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                                                                <?php else: ?>
                                                                    <img src="assets/images/product_img1.jpg" alt="<?php echo htmlspecialchars($product['name']); ?>">
                                                                <?php endif; ?>
                                                            </div>
                                                            <div class="product_info">
                                                                <h6 class="product_title">
                                                                    <?php echo htmlspecialchars($product['name']); ?>
                                                                </h6>
                                                                <?php if ($product['short_description']): ?>
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
                                    <?php endif; ?>

                                    <!-- Pagination (existing, kept) -->
                                    <?php if ($totalPages > 1): ?>
                                        <div class="row mt-3">
                                            <div class="col-12">
                                                <ul class="pagination justify-content-center pagination_style1">
                                                    <?php if ($pageNum > 1): ?>
                                                        <li class="page-item">
                                                            <a class="page-link" href="<?php echo SITE_URL . '/' . $brandData['slug'] . '/' . $category['slug'] . ($cityData ? '-' . $cityData['slug'] : '') . '?page=' . ($pageNum - 1); ?>">
                                                                <i class="linearicons-arrow-left"></i>
                                                            </a>
                                                        </li>
                                                    <?php endif; ?>

                                                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                                        <li class="page-item <?php echo $i == $pageNum ? 'active' : ''; ?>">
                                                            <a class="page-link" href="<?php echo SITE_URL . '/' . $brandData['slug'] . '/' . $category['slug'] . ($cityData ? '-' . $cityData['slug'] : '') . '?page=' . $i; ?>">
                                                                <?php echo $i; ?>
                                                            </a>
                                                        </li>
                                                    <?php endfor; ?>

                                                    <?php if ($pageNum < $totalPages): ?>
                                                        <li class="page-item">
                                                            <a class="page-link" href="<?php echo SITE_URL . '/' . $brandData['slug'] . '/' . $category['slug'] . ($cityData ? '-' . $cityData['slug'] : '') . '?page=' . ($pageNum + 1); ?>">
                                                                <i class="linearicons-arrow-right"></i>
                                                            </a>
                                                        </li>
                                                    <?php endif; ?>
                                                </ul>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- END SECTION SHOP -->

                 
                    
                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                    <div class="container mt-4">
                        <div class="row">
                            <div class="col-12">
                                <nav aria-label="Page navigation">
                                    <ul class="pagination justify-content-center">
                                        <?php if ($pageNum > 1): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="<?php echo SITE_URL . '/' . $brandData['slug'] . '/' . $category['slug'] . ($cityData ? '-' . $cityData['slug'] : '') . '?page=' . ($pageNum - 1); ?>">Previous</a>
                                            </li>
                                        <?php endif; ?>
                                        
                                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                            <?php if ($i == 1 || $i == $totalPages || ($i >= $pageNum - 2 && $i <= $pageNum + 2)): ?>
                                                <li class="page-item <?php echo $i == $pageNum ? 'active' : ''; ?>">
                                                    <a class="page-link" href="<?php echo SITE_URL . '/' . $brandData['slug'] . '/' . $category['slug'] . ($cityData ? '-' . $cityData['slug'] : '') . '?page=' . $i; ?>"><?php echo $i; ?></a>
                                                </li>
                                            <?php elseif ($i == $pageNum - 3 || $i == $pageNum + 3): ?>
                                                <li class="page-item disabled">
                                                    <span class="page-link">...</span>
                                                </li>
                                            <?php endif; ?>
                                        <?php endfor; ?>
                                        
                                        <?php if ($pageNum < $totalPages): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="<?php echo SITE_URL . '/' . $brandData['slug'] . '/' . $category['slug'] . ($cityData ? '-' . $cityData['slug'] : '') . '?page=' . ($pageNum + 1); ?>">Next</a>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </nav>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div> <!-- End Product Content -->
        </div>
    </div>
</div> <!-- ...:::: End Shop Section:::... -->

<?php require __DIR__ . '/includes/public/footer.php'; ?>
