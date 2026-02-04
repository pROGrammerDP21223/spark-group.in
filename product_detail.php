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

// Render breadcrumb (brand > product)
renderBreadcrumb($breadcrumbTitle, [
    ['text' => $currentBrandName, 'url' => SITE_URL . '/' . $currentBrandSlug],
    ['text' => $breadcrumbTitle]
]);
?>

<!-- START SECTION SHOP -->
<div class="section">
	<div class="container">
		<div class="row">
            <div class="col-lg-5 col-md-5 mb-5 mb-md-0">
                
              <div class="product-image">
                    <div class="product_img_box">
                        <?php
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
                        ?>
                        <img id="product_img" src="<?php echo htmlspecialchars($mainImage); ?>" alt="<?php echo htmlspecialchars($currentProduct['name']); ?>">
                    </div>
                    <?php if (count($allImages) > 0): ?>
                    <div id="pr_item_gallery" class="product_gallery_item slick_slider" data-slides-to-show="4" data-slides-to-scroll="1" data-infinite="false">
                        <?php foreach ($allImages as $index => $img): ?>
                        <div class="item">
                            <a href="#" class="product_gallery_item<?php echo $index === 0 ? ' active' : ''; ?>" data-image="<?php echo htmlspecialchars(UPLOAD_URL . '/' . $img); ?>">
                                <img src="<?php echo htmlspecialchars(UPLOAD_URL . '/' . $img); ?>" alt="<?php echo htmlspecialchars($currentProduct['name']); ?>">
                            </a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-lg-6 col-md-6">
                <div class="pr_detail">
                    <div class="product_description">
                        <h4 class="product_title"><a href="#"><?php echo htmlspecialchars($currentProduct['name']); ?></a></h4>
                       <br>
                       <br>
                        <?php if (!empty($pageSEO['h2_text'])): ?>
                    <p class="mb-4"><?php echo htmlspecialchars($pageSEO['h2_text']); ?></p>
                <?php endif; ?>
                        <?php if (!empty($currentProduct['short_description'] ?? '')): ?>
                        <div class="pr_desc">
                            <p><?php echo htmlspecialchars($currentProduct['short_description']); ?></p>
                        </div>
                        <?php endif; ?>
                       
                    </div>
                    <hr>
                    <div class="cart_extra">
                        <div class="cart_btn">
                            <a href="<?php echo SITE_URL; ?>/enquiry?product_id=<?php echo $currentProduct['id']; ?>" class="btn btn-fill-out"><i class="icon-envelope"></i> Send Enquiry</a>
                        </div>
                    </div>
                    <hr>
                    <ul class="product-meta">
                        <?php if (!empty($currentProduct['sku'])): ?>
                        <li>SKU: <a href="#"><?php echo htmlspecialchars($currentProduct['sku']); ?></a></li>
                        <?php endif; ?>
                        <li>Brand: <a href="<?php echo SITE_URL . '/' . htmlspecialchars($brandData['slug']); ?>"><?php echo htmlspecialchars($brandData['name']); ?></a></li>
                    </ul>
                    
                    <div class="product_share">
                        <span>Share:</span>
                        <ul class="social_icons">
                            <?php
                            $shareUrl = urlencode($seoData['canonical_url'] ?? SITE_URL . '/' . $brandData['slug'] . '/' . $currentProduct['slug']);
                            $shareTitle = urlencode($currentProduct['name']);
                            ?>
                            <li><a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo $shareUrl; ?>" target="_blank"><i class="ion-social-facebook"></i></a></li>
                            <li><a href="https://twitter.com/intent/tweet?url=<?php echo $shareUrl; ?>&text=<?php echo $shareTitle; ?>" target="_blank"><i class="ion-social-twitter"></i></a></li>
                            <li><a href="https://www.linkedin.com/shareArticle?url=<?php echo $shareUrl; ?>&title=<?php echo $shareTitle; ?>" target="_blank"><i class="ion-social-googleplus"></i></a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
        	<div class="col-12">
            	<div class="large_divider clearfix"></div>
            </div>
        </div>
        <div class="row">
        	<div class="col-12">
            	<div class="tab-style3">
					<ul class="nav nav-tabs" role="tablist">
						<?php if (!empty($currentProduct['description'] ?? '')): ?>
						<li class="nav-item">
							<a class="nav-link active" id="Description-tab" data-bs-toggle="tab" href="#Description" role="tab" aria-controls="Description" aria-selected="true">Description</a>
                      	</li>
                      	<?php endif; ?>
                      	<?php if (!empty($specs)): ?>
                      	<li class="nav-item">
                        	<a class="nav-link<?php echo empty($currentProduct['description'] ?? '') ? ' active' : ''; ?>" id="Additional-info-tab" data-bs-toggle="tab" href="#Additional-info" role="tab" aria-controls="Additional-info" aria-selected="false">Specifications</a>
                      	</li>
                      	<?php endif; ?>
                    </ul>
                	<div class="tab-content shop_info_tab">
                      	<?php if (!empty($currentProduct['description'] ?? '')): ?>
                      	<div class="tab-pane fade show active" id="Description" role="tabpanel" aria-labelledby="Description-tab">
                        	<?php echo $currentProduct['description']; ?>
                      	</div>
                      	<?php endif; ?>
                      	<?php if (!empty($specs)): ?>
                      	<div class="tab-pane fade<?php echo empty($currentProduct['description'] ?? '') ? ' show active' : ''; ?>" id="Additional-info" role="tabpanel" aria-labelledby="Additional-info-tab">
                        	<table class="table table-bordered">
                            	<?php foreach ($specs as $spec): ?>
                            	<tr>
                                	<td><?php echo htmlspecialchars($spec['spec_name']); ?></td>
                                	<td><?php echo htmlspecialchars($spec['spec_value']); ?></td>
                            	</tr>
                            	<?php endforeach; ?>
                        	</table>
                      	</div>
                      	<?php endif; ?>
                	</div>
                </div>
            </div>
        </div>
        <div class="row">
        	<div class="col-12">
            	<div class="small_divider"></div>
            	<div class="divider"></div>
                <div class="medium_divider"></div>
            </div>
        </div>
        <?php if (!empty($relatedProducts)): ?>
        <div class="row">
        	<div class="col-12">
            	<div class="heading_s1">
                	<h3>Related Products</h3>
                </div>
            	<div class="releted_product_slider carousel_slider owl-carousel owl-theme" data-margin="20" data-responsive='{"0":{"items": "1"}, "481":{"items": "2"}, "768":{"items": "3"}, "1199":{"items": "4"}}'>
                	<?php foreach ($relatedProducts as $rel): ?>
                	<?php
                	$relUrl = SITE_URL . '/' . htmlspecialchars($brandData['slug']) . '/' . htmlspecialchars($rel['slug']);
                	$relImage = !empty($rel['image']) ? UPLOAD_URL . '/' . $rel['image'] : SITE_URL . '/assets/images/product_img1.jpg';
                	?>
                	<div class="item">
                        <a href="<?php echo $relUrl; ?>" class="product_wrap_link" style="display:block;text-decoration:none;color:inherit;">
                            <div class="product">
                                <div class="product_img">
                                    <img src="<?php echo htmlspecialchars($relImage); ?>" alt="<?php echo htmlspecialchars($rel['name']); ?>">
                                </div>
                                <div class="product_info">
                                    <h6 class="product_title"><?php echo htmlspecialchars($rel['name']); ?></h6>
                                    <?php if (!empty($rel['short_description'] ?? '')): ?>
                                    <div class="pr_desc">
                                        <p><?php echo htmlspecialchars(mb_substr($rel['short_description'], 0, 100)) . (mb_strlen($rel['short_description']) > 100 ? '...' : ''); ?></p>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
<!-- END SECTION SHOP -->

<script>
// Disable zoom effect on product images
jQuery(document).ready(function($) {
    // Wait for scripts.js to load, then disable zoom
    setTimeout(function() {
        // Destroy any existing zoom instances
        if ($('#product_img').data('elevateZoom')) {
            $('#product_img').data('elevateZoom').destroy();
        }
        
        // Remove zoom container if it exists
        $('.zoomContainer').remove();
        
        // Prevent zoom initialization by removing data attributes
        $('#product_img').removeAttr('data-zoom-image');
        $('.product_gallery_item').removeAttr('data-zoom-image');
        
        // Disable zoom on gallery items click
        $('.product_gallery_item').off('click').on('click', function(e) {
            e.preventDefault();
            var newImage = $(this).data('image');
            if (newImage) {
                $('#product_img').attr('src', newImage);
                $('.product_gallery_item').removeClass('active');
                $(this).addClass('active');
                
                // Destroy zoom again after image change
                if ($('#product_img').data('elevateZoom')) {
                    $('#product_img').data('elevateZoom').destroy();
                }
                $('.zoomContainer').remove();
            }
        });
    }, 500);
    
    // Also prevent zoom initialization immediately
    $('#product_img').removeAttr('data-zoom-image');
    $('.product_gallery_item').removeAttr('data-zoom-image');
});
</script>

<style>
/* Hide zoom container */
.zoomContainer {
    display: none !important;
}

/* Disable hover effects on product image */
.product_img_box:hover .zoomContainer {
    display: none !important;
}

#product_img {
    cursor: default !important;
}
</style>

<?php require __DIR__ . '/includes/public/footer.php'; ?>
