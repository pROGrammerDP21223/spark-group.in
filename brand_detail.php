<?php
/**
 * Brand Detail Page (with city support) - Aments Design
 * Handles: /bosch, /bosch-pune, /bosch-mumbai
 */

if (empty($slug)) {
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
        // Last part is a city, so first part(s) is the brand slug
        array_pop($parts); // Remove city part
        $slug = implode('-', $parts); // Rejoin remaining parts as brand slug
        $city = $lastPart;
        $cityData = $cityCheckResult;
        $cityId = $cityData['id'];
    }
}

// Get brand data
$stmt = $db->prepare("SELECT * FROM brands WHERE slug = ? AND status = 'active'");
$stmt->execute([$slug]);
$brand = $stmt->fetch();

if (!$brand) {
    redirect(SITE_URL . '/404');
}

// Get SEO data (base or city-specific)
$seoData = getSEOData($db, 'brand', $brand['id'], $cityId);

// Build H1 text dynamically
if (empty($seoData['h1_text'])) {
    // Capitalize first letter of each word for proper display
    $brandName = ucwords(strtolower($brand['name']));
    $seoData['h1_text'] = $brandName;
    if ($cityData) {
        $cityName = ucwords(strtolower($cityData['name']));
        $seoData['h1_text'] .= ' Authorised Dealer Distributor and Supplier in ' . $cityName;
    }
}

// Build meta title if not set
if (empty($seoData['meta_title'])) {
    $seoData['meta_title'] = $brand['name'];
    if ($cityData) {
        $seoData['meta_title'] .= ' Authorised Dealer Distributor and Supplier in ' . $cityData['name'];
    }
    $seoData['meta_title'] .= ' - ' . SITE_NAME;
}

// Build canonical URL
if (empty($seoData['canonical_url'])) {
    $seoData['canonical_url'] = SITE_URL . '/' . $brand['slug'];
    if ($cityData) {
        $seoData['canonical_url'] .= '-' . $cityData['slug'];
    }
}

// Get categories for this brand
$categories = $db->prepare("SELECT * FROM product_categories WHERE brand_id = ? AND status = 'active' ORDER BY sort_order ASC, name ASC");
$categories->execute([$brand['id']]);
$categories = $categories->fetchAll();

// Get products for this brand (with pagination)
$pageNum = max(1, intval($_GET['page'] ?? 1));
$offset = ($pageNum - 1) * ITEMS_PER_PAGE;

$totalProducts = $db->prepare("SELECT COUNT(*) FROM products WHERE brand_id = ? AND status = 'active'");
$totalProducts->execute([$brand['id']]);
$totalProducts = $totalProducts->fetchColumn();
$totalPages = ceil($totalProducts / ITEMS_PER_PAGE);

$products = $db->prepare("SELECT p.*, c.name as category_name, c.slug as category_slug 
                          FROM products p 
                          LEFT JOIN product_categories c ON p.category_id = c.id 
                          WHERE p.brand_id = ? AND p.status = 'active' 
                          ORDER BY p.sort_order ASC, p.name ASC 
                          LIMIT " . ITEMS_PER_PAGE . " OFFSET $offset");
$products->execute([$brand['id']]);
$products = $products->fetchAll();

// Set page SEO
$pageSEO = $seoData;
// Add SEO head code if exists
if (!empty($seoData['seo_head'])) {
    $pageSEO['seo_head'] = $seoData['seo_head'];
}

// Store brand data in safe variables before including header (which may overwrite $brand)
$currentBrandName = $brand['name'];
$currentBrandSlug = $brand['slug'];
$currentBrandId = $brand['id'];

require __DIR__ . '/includes/public/header.php';
require_once __DIR__ . '/includes/public/breadcrumb.php';
?>

<div class="offcanvas-overlay"></div>

<?php
// Use SEO H1 text for breadcrumb if available, otherwise use brand name
$breadcrumbTitle = !empty($seoData['h1_text']) ? $seoData['h1_text'] : ucwords(strtolower($currentBrandName));
if ($cityData && stripos($breadcrumbTitle, $cityData['name']) === false) {
    $breadcrumbTitle .= ' Authorised Dealer Distributor and Supplier in ' . ucwords(strtolower($cityData['name']));
}

// Render breadcrumb
renderBreadcrumb($breadcrumbTitle, [
    ['text' => $breadcrumbTitle]
]);
?>



<!-- START SECTION SHOP (dynamic brand categories in Shopwise style) -->
<div class="section">
    <div class="container">
        <div class="row">
            <div class="col-12">
              
            <?php if (!empty($pageSEO['h2_text'])): ?>
                    <p class="mb-4"><?php echo htmlspecialchars($pageSEO['h2_text']); ?></p>
                <?php endif; ?>
                <div class="text-center mb-4">
                    <a href="<?php echo SITE_URL; ?>/enquiry?brand_id=<?php echo $currentBrandId; ?>" class="btn btn-fill-out">
                        <i class="icon-envelope"></i> Send Enquiry for <?php echo htmlspecialchars($currentBrandName); ?>
                    </a>
                </div>

                <?php if (empty($categories)): ?>
                    <p class="text-center text-muted py-5">No product categories found for this brand.</p>
                <?php else: ?>
                    <div class="row shop_container loadmore"
                         data-item="8"
                         data-item-show="4"
                         data-finish-message="No More Item to Show"
                         data-btn="Load More">
                        <?php foreach ($categories as $index => $category): ?>
                            <?php
                            $categoryUrl = SITE_URL . '/' . $currentBrandSlug . '/' . $category['slug'];
                            if ($cityData) {
                                $categoryUrl .= '-' . $cityData['slug'];
                            }
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
                                            <h6 class="product_title">
                                                <?php echo htmlspecialchars($category['name']); ?>
                                            </h6>
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
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<!-- END SECTION SHOP -->

<?php require __DIR__ . '/includes/public/footer.php'; ?>
