<?php
/**
 * Public Website Header - Aments Design
 * Includes SEO meta tags and navigation
 */

// Get current page SEO data - merge with defaults if page already set $pageSEO
$defaultSEO = [
    'meta_title' => SITE_NAME,
    'meta_description' => 'Professional dealer website showcasing quality products and brands',
    'meta_keywords' => '',
    'canonical_url' => SITE_URL . $_SERVER['REQUEST_URI'],
    'og_title' => SITE_NAME,
    'og_description' => 'Professional dealer website',
    'og_image' => SITE_URL . '/aments/assets/images/logo/logo.png',
    'h1_text' => '',
    'h2_text' => '',
    'seo_head' => ''
];

// If page already set $pageSEO, merge it with defaults (page values override defaults)
if (isset($pageSEO) && is_array($pageSEO)) {
    // Merge defaults first, then page SEO (page values will override defaults)
    $pageSEO = array_merge($defaultSEO, $pageSEO);
} else {
    $pageSEO = $defaultSEO;
}

// Shared data for navigation & header:
// Prefer using AppContext (created in index.php) to avoid duplicate queries.
if (isset($app) && $app instanceof AppContext) {
    $brands = $app->brands;
    $contactMap = $app->contactMap;
} else {
    // Fallback for cases where header is used without AppContext
    $brands = $db->query(
        "SELECT * FROM brands WHERE status = 'active' ORDER BY sort_order ASC, name ASC"
    )->fetchAll();

    $contactDetails = $db->query(
        "SELECT * FROM contact_details WHERE status = 'active' ORDER BY sort_order ASC"
    )->fetchAll();
    $contactMap = [];
    foreach ($contactDetails as $contact) {
        $contactMap[$contact['type']][] = $contact;
    }
}

// Load products for each brand for navigation menu
$brandsWithProducts = [];
foreach ($brands as $brand) {
    $products = $db->prepare(
        "SELECT id, name, slug FROM products 
         WHERE brand_id = ? AND status = 'active' 
         ORDER BY sort_order ASC, name ASC 
         LIMIT 10"
    );
    $products->execute([$brand['id']]);
    $brand['products'] = $products->fetchAll();
    $brandsWithProducts[] = $brand;
}

// Get phone/email for header
$headerPhone = !empty($contactMap['phone']) ? $contactMap['phone'][0]['value'] : '';
$headerEmail = !empty($contactMap['email']) ? $contactMap['email'][0]['value'] : '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    
    <!-- SEO Meta Tags -->
    <title><?php echo !empty($pageSEO['meta_title']) ? htmlspecialchars($pageSEO['meta_title']) : SITE_NAME; ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($pageSEO['meta_description']); ?>">
    <?php if (!empty($pageSEO['meta_keywords'])): ?>
        <meta name="keywords" content="<?php echo htmlspecialchars($pageSEO['meta_keywords']); ?>">
    <?php endif; ?>
    
    <!-- Canonical URL -->
    <link rel="canonical" href="<?php echo htmlspecialchars($pageSEO['canonical_url']); ?>">
    
    <!-- Open Graph Tags -->
    <meta property="og:title" content="<?php echo htmlspecialchars($pageSEO['og_title'] ?: $pageSEO['meta_title']); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($pageSEO['og_description'] ?: $pageSEO['meta_description']); ?>">
    <meta property="og:image" content="<?php echo htmlspecialchars($pageSEO['og_image']); ?>">
    <meta property="og:url" content="<?php echo htmlspecialchars($pageSEO['canonical_url']); ?>">
    <meta property="og:type" content="website">
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo htmlspecialchars($pageSEO['og_title'] ?: $pageSEO['meta_title']); ?>">
    <meta name="twitter:description" content="<?php echo htmlspecialchars($pageSEO['og_description'] ?: $pageSEO['meta_description']); ?>">
    <meta name="twitter:image" content="<?php echo htmlspecialchars($pageSEO['og_image']); ?>">
    
    <!-- Custom SEO Head Code (Google Analytics, etc.) -->
    <?php if (!empty($pageSEO['seo_head'])): ?>
        <?php echo $pageSEO['seo_head']; ?>
    <?php endif; ?>

    <!-- ::::::::::::::Favicon icon::::::::::::::-->
    <link rel="shortcut icon" href="<?php echo SITE_URL; ?>/assets/img/favicon.png" type="image/png">

    <!-- ::::::::::::::All CSS Files here :::::::::::::: -->
   
	<!-- CSS here -->
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/bootstrap.min.css">
        <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/all.min.css">
        <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/nice-select.css">
        <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/swiper.min.css">
        <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/animate.css">
        <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/magnific-popup.css">
        <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/main.css">
<link rel="stylesheet" href="<?php echo SITE_URL; ?>/form_styles.css">
<script src="<?php echo SITE_URL; ?>/form_config.js"></script>
   

</head>
<body class="fd-home-1" >

<!-- preloader-start -->
<div id="preloader">
    <div class="preloader-wrap">
        <div class="loading">
            <div class="icon-ani">
                <img src="<?php echo SITE_URL; ?>/assets/img/preloader.gif" alt="">
            </div>
        </div>
    </div>
</div>
<!-- preloader-end -->

<!-- header-start -->
<div class="fx-header-1-area txa_sticky_header ">
    <div class="fx-header-1-container">
        <!-- header-top -->
        <div class="fx-header-1-top" >
            <ul class="fx-contact-list">
                <li>
                    Welcome to <?php echo SITE_NAME; ?>!
                </li>
                <li>
                    <a href="#" aria-label="name">
                        <i class="fa-regular fa-envelope"></i>
                        <?php echo htmlspecialchars($headerEmail); ?>
                    </a>
                </li>
                <li>
                    <a href="#" aria-label="name">
                        <i class="fa-regular fa-phone-volume"></i> 
                        <?php echo htmlspecialchars($headerPhone); ?>
                    </a>
                </li>
               
            </ul>
            <div class="fx-social-icon">
                <a href="#" class="fx-social-icon-btn" aria-label="name">
                    <i class="fa-brands fa-facebook-f"></i>
                </a>
                <a href="#" class="fx-social-icon-btn" aria-label="name">
                    <i class="fa-brands fa-linkedin-in"></i>
                </a>
                <a href="#" class="fx-social-icon-btn" aria-label="name">
                    <i class="fa-brands fa-x-twitter"></i>
                </a>
                <a href="#" class="fx-social-icon-btn" aria-label="name">
                    <i class="fa-brands fa-instagram"></i>
                </a>
            </div>
        </div>

        <!-- header-main -->
        <div class="fx-header-1-main">

            <!-- logo -->
            <a href="<?php echo SITE_URL; ?>" aria-label="name" class="fx-header-1-main-logo">
                <img src="<?php echo SITE_URL; ?>/assets/img/logo/logo.png" alt="">
            </a>

            <!-- menu -->
            <nav class="main-navigation fx-ml-auto d-none d-lg-block ">
                <ul id="main-nav" class="nav navbar-nav clearfix">

                    <li>
                        <a class="<?php echo ($type === 'home' && $page === 'home') ? 'is-active' : ''; ?>" href="<?php echo SITE_URL; ?>">home</a>
                    </li>

                    <li>
                        <a class="<?php echo (isset($page) && $page === 'about') ? 'is-active' : ''; ?>" href="<?php echo SITE_URL; ?>/about-us">about</a>
                    </li>
                    <li>
                        <a class="<?php echo (isset($page) && $page === 'certifications') ? 'is-active' : ''; ?>" href="<?php echo SITE_URL; ?>/certifications">Certifications</a>
                    </li>
                    <?php if (!empty($brandsWithProducts)): ?>                 
                    <li class="dropdown">
                        <a class="<?php echo ($type === 'brand') ? 'is-active' : ''; ?>" href="!#">Brands</a>
                        <ul class="dropdown-menu clearfix">
                            <?php foreach ($brandsWithProducts as $navBrand): ?>
                            
                            <li class="<?php if (!empty($navBrand['products'])): ?> dropdown <?php endif; ?>">
                                <a href="<?php echo SITE_URL . '/' . $navBrand['slug']; ?>"> <?php echo htmlspecialchars($navBrand['name']); ?></a>
                                <?php if (!empty($navBrand['products'])): ?>
                                <ul class="dropdown-menu clearfix">
                                    <?php foreach ($navBrand['products'] as $product): ?>
                                    <li>
                                        <a href="<?php echo SITE_URL . '/' . $navBrand['slug'] . '/' . $product['slug']; ?>"><?php echo htmlspecialchars($product['name']); ?></a>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                                <?php endif; ?>
                            </li>
                            <?php endforeach; ?>
                            </ul>
                            <?php endif; ?>
                            <!-- <li>
                                <a class="<?php echo (isset($page) && $page === 'testimonials') ? 'is-active' : ''; ?>" href="<?php echo SITE_URL; ?>/`testimonials">Testimonials</a>
                            </li> -->
                            <li>
                                <a class="<?php echo (isset($page) && $page === 'contact') ? 'is-active' : ''; ?>" href="<?php echo SITE_URL; ?>/contact-us">Contact Us</a>
                            </li>
                            <li>
                                <a class="<?php echo (isset($page) && $page === 'enquiry') ? 'is-active' : ''; ?>" href="<?php echo SITE_URL; ?>/enquiry">Enquiry</a>
                            </li>
                        </ul>
                    </li>
                 </ul>
            </nav>

            <!-- action-btn -->
            <div class="fx-header-1-action-link d-flex align-items-center justify-content-end">

                <!-- search-btn -->
                <button type="button" aria-label="name" class="fx-search-btn-1 search_btn_toggle">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    search...
                </button>

                <!-- pr-btn -->
                <a href="<?php echo SITE_URL; ?>/enquiry" aria-label="name" class="fx-pr-btn-1">
                    <span class="text" data-back="request a quote" data-front="request a quote"></span>
                </a>

                <!-- sidebar-btn -->
                <button type="button" aria-label="name" class="fx-menu-btn-1 offcanvas_toggle" >
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                </button>

            </div>

        </div>
    </div>
</div>
<!-- header-end -->