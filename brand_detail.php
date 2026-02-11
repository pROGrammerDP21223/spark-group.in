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

// Get products for this brand (with pagination)
$pageNum = max(1, intval($_GET['page'] ?? 1));
$offset = ($pageNum - 1) * ITEMS_PER_PAGE;

$totalProducts = $db->prepare("SELECT COUNT(*) FROM products WHERE brand_id = ? AND status = 'active'");
$totalProducts->execute([$brand['id']]);
$totalProducts = $totalProducts->fetchColumn();
$totalPages = ceil($totalProducts / ITEMS_PER_PAGE);

$productsStmt = $db->prepare("SELECT * FROM products 
                          WHERE brand_id = ? AND status = 'active' 
                          ORDER BY sort_order ASC, name ASC 
                          LIMIT " . ITEMS_PER_PAGE . " OFFSET $offset");
$productsStmt->execute([$brand['id']]);
$currentProducts = $productsStmt->fetchAll();

// Set page SEO
$pageSEO = $seoData;
// Add SEO head code if exists
if (!empty($seoData['seo_head'])) {
    $pageSEO['seo_head'] = $seoData['seo_head'];
}

// Store brand data in safe variables before including header (which may overwrite $brand and $products)
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

// // Render breadcrumb
// renderBreadcrumb($breadcrumbTitle, [
//     ['text' => $breadcrumbTitle]
// ]);
?>




<div class="breadcrumb-area bg-default " data-background="assets/img/breadcrumb/b1-bg-1.png">
    <div class="container fx-container-1">
        <div class="breadcrumb-wrap">

            <!-- left-content -->
            <div class="breadcrumb-content">

                <div class="breadcrumb-list ">
                    <a href="index.html">Home</a>
                    <span><?php echo htmlspecialchars($breadcrumbTitle); ?></span>
                </div>

                <h1 class="breadcrumb-title fx-heading-1 text-uppercase " data-txaa-split-text-1><?php echo htmlspecialchars($breadcrumbTitle); ?></h1>

             



            </div>

            <!-- right-img -->
            <div class="breadcrumb-img">
                <!-- <img src="assets/img/breadcrumb/b1-img-1.png" alt=""> -->
            </div>

        </div>
    </div>
</div>

<div id="has-jump"></div>
<div class="d-flex flex-column align-items-center justify-content-center mt-50">
<?php if (!empty($pageSEO['h2_text'])): ?>
    <p class="mb-4"><?php echo htmlspecialchars($pageSEO['h2_text']); ?></p>
<?php endif; ?>

<a href="<?php echo SITE_URL; ?>/enquiry" aria-label="name" class="fx-pr-btn-1">
    <span class="text" data-back="request a quote" data-front="request a quote"></span>
</a>
</div>
<!-- blog-start -->


<div class="fx-blog-page-area pt-50 pb-120">
    <div class="container fx-container-1">
        <div class="fx-blog-page-item mb-65">
            <?php if (empty($currentProducts)): ?>
                <p class="text-center">No products found for this brand.</p>
            <?php else: ?>
                <?php foreach ($currentProducts as $product): ?>
                <?php
                $productUrl = SITE_URL . '/' . $currentBrandSlug . '/' . $product['slug'];
                if ($cityData) {
                    $productUrl .= '-' . $cityData['slug'];
                }
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
                </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
<!-- blog-end -->
<?php require __DIR__ . '/includes/public/footer.php'; ?>