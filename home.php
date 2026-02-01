<?php
/**
 * Home Page - Aments Design
 */

// Initialize database connection if not already set
if (!isset($db)) {
    require_once __DIR__ . '/config/config.php';
    require_once __DIR__ . '/config/database.php';
    $db = Database::getInstance()->getConnection();
}

// Get all active brands with their categories
$allBrands = $db->query("SELECT * FROM brands WHERE status = 'active' ORDER BY sort_order ASC, name ASC LIMIT 10")->fetchAll();

// Get categories by brand for tabs
$categoriesByBrand = [];
foreach ($allBrands as $brand) {
    // First get categories
    $brandCategories = $db->prepare("SELECT c.*, ? as brand_slug
                                      FROM product_categories c 
                                      WHERE c.brand_id = ? AND c.status = 'active' 
                                      ORDER BY c.sort_order ASC, c.name ASC");
    $brandCategories->execute([$brand['slug'], $brand['id']]);
    $categories = $brandCategories->fetchAll();
    
    // Then get product counts for each category separately
    foreach ($categories as &$category) {
        $category['brand_slug'] = $brand['slug'];
        // Ensure category_id is an integer
        $categoryId = (int)$category['id'];
        
        // Get active products count - using exact same query as category_detail.php
        $countQuery = $db->prepare("SELECT COUNT(*) FROM products WHERE category_id = ? AND status = 'active'");
        $countQuery->execute([$categoryId]);
        $count = $countQuery->fetchColumn();
        // Ensure we get a proper integer value
        $category['product_count'] = ($count !== false && $count !== null) ? (int)$count : 0;
        
        // Debug: Log the count (remove after fixing)
        // error_log("Category ID: {$categoryId}, Product Count: {$category['product_count']}");
    }
    unset($category); // Break reference
    $categoriesByBrand[$brand['id']] = $categories;
}

// Get all categories with product counts
$categories = $db->query("SELECT c.*, b.slug as brand_slug
                          FROM product_categories c 
                          LEFT JOIN brands b ON c.brand_id = b.id 
                          WHERE c.status = 'active' 
                          ORDER BY c.sort_order ASC, c.name ASC 
                          LIMIT 8")->fetchAll();
// Add product counts separately
foreach ($categories as &$category) {
    $countQuery = $db->prepare("SELECT COUNT(*) FROM products WHERE category_id = ? AND status = 'active'");
    $countQuery->execute([$category['id']]);
    $count = $countQuery->fetchColumn();
    $category['product_count'] = $count !== false ? (int)$count : 0;
}
unset($category); // Break reference

// Get slider images from database
$sliderImages = $db->query("SELECT * FROM slider_images WHERE status = 'active' ORDER BY sort_order ASC, id ASC")->fetchAll();

// Get featured products
$featuredProducts = $db->query("SELECT p.*, b.slug as brand_slug, c.slug as category_slug, b.name as brand_name, c.name as category_name
                                FROM products p
                                LEFT JOIN brands b ON p.brand_id = b.id
                                LEFT JOIN product_categories c ON p.category_id = c.id
                                WHERE p.featured = 1 AND p.status = 'active' AND b.status = 'active' AND c.status = 'active'
                                ORDER BY p.sort_order ASC, p.created_at DESC
                                LIMIT 12")->fetchAll();

// Get contact details
$contactDetails = $db->query("SELECT * FROM contact_details WHERE status = 'active' ORDER BY sort_order ASC")->fetchAll();
$contactMap = [];
foreach ($contactDetails as $contact) {
    $contactMap[$contact['type']][] = $contact;
}

// Get about us page content
$aboutContent = $db->query("SELECT * FROM static_pages WHERE page_key = 'about' AND status = 'active'")->fetch();

// Get page SEO (entity_id = 0 for home page)
$pageSEO = getSEOData($db, 'page', 0, null);
if (empty($pageSEO['meta_title'])) {
    $pageSEO['meta_title'] = SITE_NAME . ' - Professional Dealer Website';
}
if (empty($pageSEO['meta_description'])) {
    $pageSEO['meta_description'] = 'Explore our wide range of quality products from trusted brands';
}
if (empty($pageSEO['h1_text'])) {
    $pageSEO['h1_text'] = 'Welcome to ' . SITE_NAME;
}
if (empty($pageSEO['canonical_url'])) {
    $pageSEO['canonical_url'] = SITE_URL;
}
// Ensure seo_head is included
if (!isset($pageSEO['seo_head'])) {
    $pageSEO['seo_head'] = '';
}

require __DIR__ . '/includes/public/header.php';
?>

<div class="container-fluid">

<div class="row">
    
    <div class="col-md-12">
    <div class="banner_section slide_medium shop_banner_slider staggered-animation-wrap">
    <div id="carouselExampleControls" class="carousel slide carousel-fade light_arrow" data-bs-ride="carousel">
        <div class="carousel-inner">
            <?php if (!empty($sliderImages)): ?>
                <?php foreach ($sliderImages as $index => $slide): ?>
                    <div class="carousel-item background_bg <?php echo $index === 0 ? 'active' : ''; ?>" data-img-src="<?php echo SITE_URL; ?>/uploads/<?php echo htmlspecialchars($slide['image'] ?? ''); ?>">
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Fallback slides when no slider images in database -->
                <div class="carousel-item background_bg active" data-img-src="<?php echo SITE_URL; ?>/assets/images/banner16.jpg">
                </div>
                <div class="carousel-item background_bg" data-img-src="<?php echo SITE_URL; ?>/assets/images/banner17.jpg">
                </div>
                <div class="carousel-item background_bg" data-img-src="<?php echo SITE_URL; ?>/assets/images/banner18.jpg">
                </div>
            <?php endif; ?>
        </div>
        <a class="carousel-control-prev" href="#carouselExampleControls" role="button" data-bs-slide="prev">
            <i class="ion-chevron-left"></i>
        </a>
        <a class="carousel-control-next" href="#carouselExampleControls" role="button" data-bs-slide="next">
            <i class="ion-chevron-right"></i>
        </a>
    </div>
</div>
    </div>
  
</div>  

</div>
<!-- START SECTION BANNER -->

<!-- END SECTION BANNER -->

<!-- END SECTION BANNER -->

<!-- END MAIN CONTENT -->
<div class="main_content">

<div class="section">
	<div class="container">
    	<div class="row align-items-center">
        	<div class="col-lg-6">
            	<div class="about_img scene mb-4 mb-lg-0">
                    <img src="assets/images/about_img.png" alt="about_img">
                </div>
            </div>
            <div class="col-lg-6">
                <div class="heading_s1">
                    <h2><?php echo !empty($aboutContent['title']) ? htmlspecialchars($aboutContent['title']) : 'Who We are'; ?></h2>
                </div>
                <?php if (!empty($aboutContent['content'])): ?>
                    <div class="about_content">
                        <?php echo $aboutContent['content']; ?>
                    </div>
              
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- START SECTION SHOP -->
<div class="section small_pb small_pt">
	<div class="container">
        <div class="row justify-content-center">
			<div class="col-md-6">
            	<div class="heading_s1 text-center">
                	<h2>Exclusive Brands</h2>
                </div>
            </div>
		</div>  
        <div class="row">
            <div class="col-12">
            	<div class="tab-style1">
                    <ul class="nav nav-tabs justify-content-center" role="tablist">
                    <?php if (!empty($allBrands)): ?>
                        <?php $firstBrand = true; foreach ($allBrands as $brand): ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $firstBrand ? 'active' : ''; ?>" id="arrival-tab-<?php echo $brand['id']; ?>" data-bs-toggle="tab" href="#arrival-<?php echo $brand['id']; ?>" role="tab" aria-controls="arrival-<?php echo $brand['id']; ?>" aria-selected="<?php echo $firstBrand ? 'true' : 'false'; ?>"><?php echo htmlspecialchars($brand['name']); ?></a>
                        </li>
                        <?php $firstBrand = false; endforeach; ?>
                    <?php endif; ?>
                    </ul>
                </div>
                <div class="tab_slider tab-content">
                    <?php if (!empty($allBrands)): ?>
                        <?php $firstBrand = true; foreach ($allBrands as $brand): ?>
                        <div class="tab-pane fade <?php echo $firstBrand ? 'show active' : ''; ?>" id="arrival-<?php echo $brand['id']; ?>" role="tabpanel" aria-labelledby="arrival-tab-<?php echo $brand['id']; ?>">
                            <?php 
                            $categoryCount = !empty($categoriesByBrand[$brand['id']]) ? count($categoriesByBrand[$brand['id']]) : 0;
                            $shouldLoop = $categoryCount > 4; // Only loop if more than 4 items
                            ?>
                            <div class="product_slider carousel_slider owl-carousel owl-theme nav_style1" data-loop="<?php echo $shouldLoop ? 'true' : 'false'; ?>" data-dots="false" data-nav="true" data-margin="20" data-responsive='{"0":{"items": "1"}, "481":{"items": "2"}, "768":{"items": "3"}, "991":{"items": "4"}}'>
                                <?php if (!empty($categoriesByBrand[$brand['id']])): ?>
                                    <?php foreach ($categoriesByBrand[$brand['id']] as $index => $category): ?>
                                    <?php $categoryUrl = SITE_URL . '/' . ($category['brand_slug'] ?? $brand['slug']) . '/' . $category['slug']; ?>
                                    <div class="item">
                                        <a href="<?php echo $categoryUrl; ?>" class="product_wrap_link" style="display: block; text-decoration: none; color: inherit;">
                                            <div class="product_wrap">
                                                <div class="product_img">
                                                    <?php if (!empty($category['image'])): ?>
                                                        <img src="<?php echo UPLOAD_URL . '/' . $category['image']; ?>" alt="<?php echo htmlspecialchars($category['name']); ?>">
                                                    <?php endif; ?>
                                                </div>
                                                <div class="product_info">
                                                    <h6 class="product_title"><?php echo htmlspecialchars($category['name']); ?></h6>
                                                    <?php if (!empty($category['description'])): ?>
                                                    <div class="pr_desc">
                                                        <p><?php echo htmlspecialchars(substr($category['description'], 0, 100)); ?><?php echo strlen($category['description']) > 100 ? '...' : ''; ?></p>
                                                    </div>
                                                    <?php endif; ?>
                                                    <div class="product_price">
                                                        <span class="price"><?php 
                                                        // Explicitly get the count
                                                        $prodCount = isset($category['product_count']) ? (int)$category['product_count'] : 0;
                                                        // Debug output (temporary)
                                                        if ($prodCount == 0 && isset($category['id'])) {
                                                            // Double-check the count
                                                            $verifyQuery = $db->prepare("SELECT COUNT(*) FROM products WHERE category_id = ? AND status = 'active'");
                                                            $verifyQuery->execute([(int)$category['id']]);
                                                            $verifyCount = (int)$verifyQuery->fetchColumn();
                                                            $prodCount = $verifyCount; // Use verified count
                                                        }
                                                        echo $prodCount; 
                                                        ?> Products</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="item">
                                        <div class="product_wrap">
                                            <div class="product_info text-center">
                                                <p>No categories available for <?php echo htmlspecialchars($brand['name']); ?></p>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php $firstBrand = false; endforeach; ?>
                    <?php endif; ?>
                               
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- END SECTION SHOP -->



<!-- START SECTION BANNER --> 
<div class="section pb_20 small_pt">
	<div class="container">
    	<div class="row">
        	<div class="col-12">
            	<div class="sale-banner mb-3 mb-md-4">
                	<a class="hover_effect1" href="#">
                		<img src="<?php echo SITE_URL; ?>/assets/images/home-add.png" alt="shop_banner_img11">
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- END SECTION BANNER -->

<!-- START SECTION SHOP -->
<div class="section small_pt">
	<div class="container">
    	<div class="row justify-content-center">
			<div class="col-md-6">
            	<div class="heading_s1 text-center">
                	<h2>Trending Products</h2>
                </div>
            </div>
		</div>
        <div class="row">
            <div class="col-12">
                <?php if (!empty($featuredProducts)): ?>
                    <?php 
                    $productCount = count($featuredProducts);
                    $shouldLoop = $productCount > 4; // Only loop if more than 4 items
                    ?>
                    <div class="product_slider carousel_slider owl-carousel owl-theme nav_style1" data-loop="<?php echo $shouldLoop ? 'true' : 'false'; ?>" data-dots="false" data-nav="true" data-margin="20" data-responsive='{"0":{"items": "1"}, "481":{"items": "2"}, "768":{"items": "3"}, "991":{"items": "4"}}'>
                        <?php foreach ($featuredProducts as $index => $product): ?>
                            <?php 
                            $productUrl = SITE_URL . '/' . htmlspecialchars($product['brand_slug']) . '/' . htmlspecialchars($product['category_slug']) . '/' . htmlspecialchars($product['slug']);
                            $gallery = !empty($product['gallery']) ? json_decode($product['gallery'], true) : [];
                            $hoverImage = !empty($gallery) ? $gallery[0] : $product['image'];
                            ?>
                            <div class="item">
                                <a href="<?php echo $productUrl; ?>" class="product_wrap_link" style="display: block; text-decoration: none; color: inherit;">
                                    <div class="product_wrap">
                                        <div class="product_img">
                                            <?php if (!empty($product['image'])): ?>
                                                <img src="<?php echo UPLOAD_URL . '/' . htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                                                <?php if ($hoverImage && $hoverImage != $product['image']): ?>
                                                  
                                           
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <img src="assets/images/el_img<?php echo ($index % 12) + 1; ?>.jpg" alt="<?php echo htmlspecialchars($product['name']); ?>">
                                                <img class="product_hover_img" src="assets/images/el_hover_img<?php echo ($index % 12) + 1; ?>.jpg" alt="<?php echo htmlspecialchars($product['name']); ?>">
                                            <?php endif; ?>
                                        </div>
                                        <div class="product_info">
                                            <h6 class="product_title"><?php echo htmlspecialchars($product['name']); ?></h6>
                                            <?php if (!empty($product['short_description'])): ?>
                                                <div class="pr_desc">
                                                    <p><?php echo htmlspecialchars(substr($product['short_description'], 0, 100)); ?><?php echo strlen($product['short_description']) > 100 ? '...' : ''; ?></p>
                                                </div>
                                            <?php elseif (!empty($product['description'])): ?>
                                                <div class="pr_desc">
                                                    <p><?php echo htmlspecialchars(substr($product['description'], 0, 100)); ?><?php echo strlen($product['description']) > 100 ? '...' : ''; ?></p>
                                                </div>
                                            <?php endif; ?>
                                            <div class="product_price">
                                                <span class="price"><?php echo htmlspecialchars($product['brand_name']); ?> - <?php echo htmlspecialchars($product['category_name']); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <p>No featured products available at the moment.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<!-- END SECTION SHOP -->






</div>
<!-- END MAIN CONTENT -->
<?php require __DIR__ . '/includes/public/footer.php'; ?>