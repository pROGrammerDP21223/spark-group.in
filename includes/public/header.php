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
    <link rel="shortcut icon" href="<?php echo SITE_URL; ?>/aments/assets/images/favicon.ico" type="image/png">

    <!-- ::::::::::::::All CSS Files here :::::::::::::: -->
    <!-- Use the minified version files listed below for better performance -->
     <!-- Animation CSS -->
<link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/animate.css">	
<!-- Latest Bootstrap min CSS -->
<link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/bootstrap/css/bootstrap.min.css">
<!-- Google Font -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
<!-- Icon Font CSS -->
<link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/all.min.css">
<link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/ionicons.min.css">
<link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/themify-icons.css">
<link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/linearicons.css">
<link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/flaticon.css">
<link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/simple-line-icons.css">
<!--- owl carousel CSS-->
<link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/owlcarousel/css/owl.carousel.min.css">
<link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/owlcarousel/css/owl.theme.css">
<link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/owlcarousel/css/owl.theme.default.min.css">
<!-- Magnific Popup CSS -->
<link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/magnific-popup.css">
<!-- Slick CSS -->
<link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/slick.css">
<link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/slick-theme.css">
<!-- Style CSS -->
<link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
<link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/responsive.css">
<link rel="stylesheet" href="<?php echo SITE_URL; ?>/form_styles.css">
<script src="<?php echo SITE_URL; ?>/form_config.js"></script>
   

</head>

<body>



<!-- Home Popup Section -->
<!-- <div class="modal fade subscribe_popup" id="onload-popup" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="ion-ios-close-empty"></i></span>
                </button>
                <div class="row g-0">
                    <div class="col-sm-7">
                        <div class="popup_content  text-start">
                            <div class="popup-text">
                                <div class="heading_s1">
                                    <h3>Subscribe Newsletter and Get 25% Discount!</h3>
                                </div>
                                <p>Subscribe to the newsletter to receive updates about new products.</p>
                            </div>
                            <form method="post">
                            	<div class="form-group mb-3">
                                	<input name="email" required="" type="email" class="form-control" placeholder="Enter Your Email">
                                </div>
                                <div class="form-group mb-3">
                                	<button class="btn btn-fill-out btn-block text-uppercase" title="Subscribe" type="submit">Subscribe</button>
                                </div>
                            </form>
                            <div class="chek-form">
                                <div class="custome-checkbox">
                                    <input class="form-check-input" type="checkbox" name="checkbox" id="exampleCheckbox3" value="">
                                    <label class="form-check-label" for="exampleCheckbox3"><span>Don't show this popup again!</span></label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-5">
                    	<div class="background_bg h-100" data-img-src="assets/images/popup_img3.jpg"></div>
                    </div>
                </div>
            </div>
    	</div>
    </div>
</div> -->
<!-- End Screen Load Popup Section --> 

<!-- START HEADER -->
<header class="header_wrap">
	<div class="top-header light_skin bg_dark d-none d-md-block">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 col-md-8">
                	<div class="header_topbar_info">
                    	<div class="header_offer">
                            <span>Welcome to <?php echo SITE_NAME; ?>!</span>
                        </div>
                       
                    </div>
                </div>
                <div class="col-lg-6 col-md-4">
                	<div class="d-flex align-items-center justify-content-center justify-content-md-end">
                        <div class="lng_dropdown">
                           
                        </div>
                        <div class="ms-3">
                            <?php if ($headerEmail): ?>
                                <span><i class="linearicons-mailbox-full"></i> <?php echo htmlspecialchars($headerEmail); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="middle-header dark_skin">
    	<div class="container">
            <div class="nav_block">
                <a class="navbar-brand" href="<?php echo SITE_URL; ?>">
                    <img class="logo_light" src="<?php echo SITE_URL; ?>/assets/images/logo_light.png" alt="<?php echo SITE_URL; ?>">
                    <img class="logo_dark" src="<?php echo SITE_URL; ?>/assets/images/logo_dark.png" alt="<?php echo SITE_URL; ?>">
                </a>
                <div class="product_search_form radius_input search_form_btn">
                    <form method="get" action="<?php echo SITE_URL; ?>/search">
                        <div class="input-group">
                            <input name="q" class="form-control" placeholder="Search Product..." required="" type="text" value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>">
                            <button type="submit" class="search_btn3">Search</button>
                        </div>
                    </form>
                </div>
                <ul class="navbar-nav attr-nav align-items-center">
                    <li><a href="<?php echo SITE_URL; ?>/enquiry" class="nav-link"><img src="<?php echo SITE_URL; ?>/assets/images/get-quote.png" alt="Get Quote" width=150></a></li>
                
                    
                </ul>
            </div>
        </div>
    </div>
    <div class="bottom_header dark_skin main_menu_uppercase border-top sticky-nav">
    	<div class="container">
            <div class="row align-items-center"> 
            	
                <div class="col-lg-12 col-md-8 col-sm-6 col-12">
                	<nav class="navbar navbar-expand-lg">
                    	<button class="navbar-toggler side_navbar_toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSidetoggle" aria-expanded="false"> 
                            <span class="ion-android-menu"></span>
                        </button>
                        <div class="pr_search_icon">
                            <a href="javascript:;" class="nav-link pr_search_trigger"><i class="linearicons-magnifier"></i></a>
                            <div class="search_wrap">
                                <span class="close-search"><i class="ion-ios-close-empty"></i></span>
                                <form method="get" action="<?php echo SITE_URL; ?>/search">
                                    <input name="q" type="text" placeholder="Search" class="form-control" id="search_input" value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>">
                                    <button type="submit" class="search_icon"><i class="ion-ios-search-strong"></i></button>
                                </form>
                            </div>
                            <div class="search_overlay"></div>
                        </div> 
                        <div class="collapse navbar-collapse mobile_side_menu" id="navbarSidetoggle">
                            <div class="mobile_menu_close">
                                <button type="button" class="btn-close" data-bs-toggle="collapse" data-bs-target="#navbarSidetoggle" aria-expanded="false" aria-label="Close">
                                    <i class="ion-ios-close-empty"></i>
                                </button>
                            </div>
                            <ul class="navbar-nav">
                               
                                <li><a class="nav-link nav_item <?php echo ($type === 'home' && $page === 'home') ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>">Home</a></li>
                                <li><a class="nav-link nav_item <?php echo (isset($page) && $page === 'about') ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>/about-us">About Us</a></li>
                                <li><a class="nav-link nav_item <?php echo (isset($page) && $page === 'certifications') ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>/certifications">Certifications</a></li>
                                <?php if (!empty($brandsWithProducts)): ?>
                                <li class="dropdown <?php echo ($type === 'brand') ? 'active' : ''; ?>">
                                    <a class="dropdown-toggle nav-link" href="#" data-bs-toggle="dropdown">Brands</a>
                                    <div class="dropdown-menu">
                                        <ul>
                                            <?php foreach ($brandsWithProducts as $navBrand): ?>
                                            <li>
                                                <a class="dropdown-item  <?php if (!empty($navBrand['products'])): ?> dropdown-toggler  <?php endif; ?>" href="<?php echo SITE_URL . '/' . $navBrand['slug']; ?>">
                                                    <?php echo htmlspecialchars($navBrand['name']); ?>
                                                </a>
                                                <?php if (!empty($navBrand['products'])): ?>
                                                <div class="dropdown-menu">
                                                    <ul>
                                                        <?php foreach ($navBrand['products'] as $product): ?>
                                                        <li>
                                                            <a class="dropdown-item nav-link nav_item" href="<?php echo SITE_URL . '/' . $navBrand['slug'] . '/' . $product['slug']; ?>">
                                                                <?php echo htmlspecialchars($product['name']); ?>
                                                            </a>
                                                        </li>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                </div>
                                                <?php endif; ?>
                                            </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                </li>
                                <?php endif; ?>
                                
                               
                                <!-- <li><a class="nav-link nav_item <?php echo (isset($page) && $page === 'testimonials') ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>/testimonials">Testimonials</a></li> -->
                                <li><a class="nav-link nav_item <?php echo (isset($page) && $page === 'contact') ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>/contact-us">Contact Us</a></li>
                                <li><a class="nav-link nav_item <?php echo (isset($page) && $page === 'enquiry') ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>/enquiry">Enquiry</a></li>

                               
                            </ul>
                        </div>
                        <div class="contact_phone contact_support">
                            <i class="linearicons-phone-wave"></i>
                            <span><?php if ($headerPhone): ?><?php echo htmlspecialchars($headerPhone); ?><?php endif; ?></span>
                        </div>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</header>
<!-- END HEADER -->

<style>
/* Enable hover dropdown for Brands menu on desktop */
@media (min-width: 992px) {
    .navbar-nav .dropdown .dropdown-menu {
        display: none;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.2s ease, visibility 0.2s ease;
        margin-top: 0;
    }
    
    .navbar-nav .dropdown:hover > .dropdown-menu,
    .navbar-nav .dropdown .dropdown-menu:hover {
        display: block !important;
        opacity: 1;
        visibility: visible;
    }
    
    /* Nested dropdown on hover */
    .navbar-nav .dropdown-menu > ul > li {
        position: relative;
    }
    
    .navbar-nav .dropdown-menu > ul > li .dropdown-menu {
        display: none;
        position: absolute;
        left: 100%;
        top: 0;
        margin: 0;
        border: 0;
        min-width: 12rem;
        box-shadow: 10px 16px 49px 0px rgba(38,42,46,0.05);
        border-radius: 0;
        padding: 5px 0;
    }
    
    .navbar-nav .dropdown-menu > ul > li:hover > .dropdown-menu {
        display: block !important;
        opacity: 1;
        visibility: visible;
    }
}

/* Dropdown menu styling */
.navbar-nav .dropdown .dropdown-menu ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.navbar-nav .dropdown .dropdown-menu > ul > li {
    position: relative;
}

.navbar-nav .dropdown .dropdown-menu .dropdown-toggler {
    position: relative;
}

.navbar-nav .dropdown .dropdown-menu .dropdown-toggler::after {
    content: "\f105";
    font-family: "Font Awesome 5 Free";
    font-weight: 900;
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
}

.navbar-nav .dropdown .dropdown-menu .dropdown-item {
    padding: 8px 15px;
    font-size: 14px;
    color: #333;
    white-space: nowrap;
}

.navbar-nav .dropdown .dropdown-menu .dropdown-item:hover {
    color: #FF324D;
    background: transparent;
}
</style>
