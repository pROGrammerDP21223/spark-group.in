<?php
/**
 * Product Detail Page (with city support) - Aments Design
 * Handles: /brand/product or /brand/product-city
 */

// Expect: $brand (brand slug), $slug (product slug), optional $city
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

// Get brand first
$brandStmt = $db->prepare("SELECT * FROM brands WHERE slug = ? AND status = 'active'");
$brandStmt->execute([$brand]);
$brandData = $brandStmt->fetch();

if (!$brandData) {
    redirect(SITE_URL . '/404');
}

// First, try to find product with the full slug as-is
$productStmt = $db->prepare("SELECT * FROM products WHERE slug = ? AND brand_id = ? AND status = 'active'");
$productStmt->execute([$slug, $brandData['id']]);
$product = $productStmt->fetch(PDO::FETCH_ASSOC);

// If product not found and slug contains hyphen, try to extract city from slug
// Only do this if product doesn't exist with full slug
if (!$product && empty($city) && strpos($slug, '-') !== false) {
    $parts = explode('-', $slug);
    $lastPart = end($parts);

    // Check if last part is a valid city slug
    $cityCheck = $db->prepare("SELECT * FROM cities WHERE slug = ? AND status = 'active'");
    $cityCheck->execute([$lastPart]);
    $cityCheckResult = $cityCheck->fetch();

    if ($cityCheckResult) {
        // Last part is a city, so first part(s) is the product slug
        array_pop($parts); // Remove city part
        $newSlug = implode('-', $parts); // Rejoin remaining parts as product slug

        // Try to find product with the new slug (without city)
        $productStmt = $db->prepare("SELECT * FROM products WHERE slug = ? AND brand_id = ? AND status = 'active'");
        $productStmt->execute([$newSlug, $brandData['id']]);
        $product = $productStmt->fetch(PDO::FETCH_ASSOC);

        // Only use city extraction if we found a product with the new slug
        if ($product) {
            $slug = $newSlug;
            $city = $lastPart;
            $cityData = $cityCheckResult;
            $cityId = $cityData['id'];
        }
    }
}

if (!$product) {
    redirect(SITE_URL . '/404');
}

// CRITICAL: Store product data in a protected variable IMMEDIATELY after fetching
// This prevents $product from being accidentally overwritten in loops or includes
$currentProduct = $product;

// Get SEO data
$seoData = getSEOData($db, 'product', $currentProduct['id'], $cityId);

// Build H1 dynamically
if (empty($seoData['h1_text'])) {
    $seoData['h1_text'] = $currentProduct['name'];
    if ($cityData) {
        $seoData['h1_text'] .= ' Authorised Dealer Distributor and Supplier in ' . $cityData['name'];
    }
}

// Build meta title
if (empty($seoData['meta_title'])) {
    $seoData['meta_title'] = $currentProduct['name'] . ' - ' . $brandData['name'];
    if ($cityData) {
        $seoData['meta_title'] .= ' Authorised Dealer Distributor and Supplier in ' . $cityData['name'];
    }
    $seoData['meta_title'] .= ' - ' . SITE_NAME;
}

// Build canonical URL (no category segment)
if (empty($seoData['canonical_url'])) {
    $seoData['canonical_url'] = SITE_URL . '/' . $brandData['slug'] . '/' . $currentProduct['slug'];
    if ($cityData) {
        $seoData['canonical_url'] .= '-' . $cityData['slug'];
    }
}

// Get product specifications
$specs = $db->prepare("SELECT * FROM product_specifications WHERE product_id = ? ORDER BY sort_order ASC, id ASC");
$specs->execute([$currentProduct['id']]);
$specs = $specs->fetchAll();

// Get gallery images
$gallery = [];
if (!empty($currentProduct['gallery'])) {
    $gallery = json_decode($currentProduct['gallery'], true);
    if (!is_array($gallery)) {
        $gallery = [];
    }
}

// Get related products for this brand (excluding current product)
$relatedStmt = $db->prepare("SELECT * FROM products
                             WHERE brand_id = ? AND status = 'active' AND id != ?
                             ORDER BY sort_order ASC, name ASC
                             LIMIT 12");
$relatedStmt->execute([$brandData['id'], $currentProduct['id']]);
$relatedProducts = $relatedStmt->fetchAll();

// Set page SEO
$pageSEO = $seoData;
// Add SEO head code if exists
if (!empty($seoData['seo_head'])) {
    $pageSEO['seo_head'] = $seoData['seo_head'];
}

require __DIR__ . '/includes/public/header.php';
?>

<div class="offcanvas-overlay"></div>

<?php
require_once __DIR__ . '/includes/public/breadcrumb.php';

// Store product/brand data in safe variables before including header
// Use $currentProduct (protected) instead of $product to prevent overwriting
$currentProductName = $currentProduct['name'];
$currentBrandName = $brandData['name'];
$currentBrandSlug = $brandData['slug'];

// Use SEO H1 text for breadcrumb if available, otherwise use product name
$breadcrumbTitle = !empty($seoData['h1_text']) ? $seoData['h1_text'] : $currentProductName;
if ($cityData && stripos($breadcrumbTitle, $cityData['name']) === false) {
    $breadcrumbTitle .= ' Authorised Dealer Distributor and Supplier in ' . $cityData['name'];
}


// Build array of all images (main image + gallery)
// Use $currentProduct (protected) to ensure we always have the correct product data
$allImages = [];
if (!empty($currentProduct['image'])) {
    $allImages[] = $currentProduct['image'];
}
if (!empty($gallery) && is_array($gallery)) {
    $allImages = array_merge($allImages, $gallery);
}

// Get main image (first one)
$mainImage = !empty($allImages[0]) ? UPLOAD_URL . '/' . $allImages[0] : SITE_URL . '/assets/images/product_img1.jpg';

// Render breadcrumb (brand > product)
// renderBreadcrumb($breadcrumbTitle, [
//     ['text' => $currentBrandName, 'url' => SITE_URL . '/' . $currentBrandSlug],
//     ['text' => $breadcrumbTitle]
// ]);
?>


<!-- team-details-start -->
<div class="fx-team-details-person">
    <div class="container fx-container-1">
        <div class="fx-team-details-person-row">

            <!-- left-img -->
            <div class="d-flex justify-content-center border-dark pro-style" align="center">

                <img src="<?php echo htmlspecialchars($mainImage); ?>" alt="">
            </div>

            <!-- right-data -->
            <div class="fx-team-details-person-info ">
                <h5 class="person-bio fx-heading-1">
                    <?php echo htmlspecialchars($currentBrandName); ?>/<?php echo $breadcrumbTitle; ?>
                </h5>
                <h4 class="person-name text-uppercase fx-heading-1 fx-font-800">
                    <?php echo htmlspecialchars($breadcrumbTitle); ?>
                </h4>
                <?php if (!empty($pageSEO['h2_text'])): ?>
                    <p class="person-disc fx-para-1 has-opacity-7"><?php echo htmlspecialchars($pageSEO['h2_text']); ?></p>
                <?php endif; ?>
                <?php if (!empty($currentProduct['short_description'] ?? '')): ?>
                    <div class="person-disc fx-para-1 has-opacity-7">
                        <p><?php echo htmlspecialchars($currentProduct['short_description']); ?></p>
                    </div>
                <?php endif; ?>


                <a href="<?php echo SITE_URL; ?>/enquiry ?>" aria-label="name" class="fx-pr-btn-1">
                    <span class="text" data-back="request a quote" data-front="request a quote"></span>
                </a>


            </div>
        </div>
    </div>
</div>

<div class="fx-team-details-disc fx-scn-redius">
    <div class="container fx-container-1">

        <!-- left-img -->
        <div class="fx-team-details-disc-content">
            <h3 class="title fx-heading-1 text-uppercase has-clr-white fx-font-800">Product Description</h3>

            <div class="fx-para-1 disc  has-clr-white" style="text-align: justify; color: #fff !important;"><?php echo $currentProduct['description']; ?> </div>
        </div>
        <br>
        <?php if (!empty($specs)): ?>
            <div class="fx-team-details-disc-content">
                <h3 class="title fx-heading-1 text-uppercase has-clr-white fx-font-800">Technical Specifications</h3>
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-bordered text-white">
                            <?php foreach ($specs as $spec): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($spec['spec_name']); ?></td>
                                    <td><?php echo htmlspecialchars($spec['spec_value']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    </div>
                </div>




            </div>
        <?php endif; ?>


    </div>
</div>
<!-- blog-start -->
<?php if (count($allImages) > 1): ?>
    <div class="fx-blog-page-area pt-120 pb-120">
        <div class="container fx-container-1">
            <div class="fx-blog-page-item mb-65">
                <?php foreach ($allImages as $index => $img): ?>
                    <!-- single-blog -->
                    <div class="fx-blog-1-item-single">
                        <div class="item-img fix img-cover p-relative mb-35">
                            <img src="<?php echo htmlspecialchars(UPLOAD_URL . '/' . $img); ?>" alt="">

                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- team-details-end -->
<!-- team-start -->
<?php if (!empty($relatedProducts)): ?>
<div class="fx-team-1-area bg-default pt-120 pb-120 " data-background="assets/img/team/t1-bg.png">
    <div class="container fx-container-1">
        <!-- section-title -->
        <div class="fx-team-1-scn-title mb-55">

            <h2 class="fx-scn-title-2 txaa-split-text-3 txaa-split-text-3-ani ">Related Products</h2>
        </div>

        <!-- team-slider -->
            <div class="fx-team-1-slider p-relative">
                
                <div class="swiper-container mb-35 fix fx-t1-active">
                    <div class="swiper-wrapper">
                        <?php foreach ($relatedProducts as $rel): ?>
                            <?php
                                $relUrl = SITE_URL . '/' . htmlspecialchars($brandData['slug']) . '/' . htmlspecialchars($rel['slug']);
                                $relImage = !empty($rel['image']) ? UPLOAD_URL . '/' . $rel['image'] : SITE_URL . '/assets/images/product_img1.jpg';
                            ?>
                            <!-- single-membar -->
                            <div class="swiper-slide">
                                <a href="<?php echo $relUrl; ?>" aria-label="name"
                                   class="fx-team-1-slider-item"
                                   style="display: block; text-decoration: none; color: inherit;">
                                    <div class="item-img">
                                        <img src="<?php echo htmlspecialchars($relImage); ?>" alt="">
                                    </div>
                                    <h5 class="person-name fx-heading-2 fx-font-500">
                                        <?php echo htmlspecialchars($rel['name']); ?>
                                    </h5>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>



<?php require __DIR__ . '/includes/public/footer.php'; ?>