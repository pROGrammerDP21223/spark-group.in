<?php
/**
 * 404 Not Found Page
 */

// Set 404 status
http_response_code(404);

// Set SEO data
$pageSEO = [
    'meta_title' => '404 - Page Not Found - ' . SITE_NAME,
    'meta_description' => 'The page you are looking for was not found.',
    'meta_keywords' => '',
    'canonical_url' => SITE_URL . '/404',
    'og_title' => '404 - Page Not Found',
    'og_description' => 'The page you are looking for was not found.',
    'og_image' => SITE_URL . '/assets/images/logo_light.png',
    'h1_text' => 'Page Not Found',
    'h2_text' => 'The page you are looking for was moved, removed, renamed or might never existed.',
    'seo_head' => ''
];

require __DIR__ . '/includes/public/header.php';
?>

<div class="offcanvas-overlay"></div>

<div class="breadcrumb-area bg-default " data-background="assets/img/breadcrumb/b1-bg-1.png">
    <div class="container fx-container-1">
        <div class="breadcrumb-wrap">
            <!-- left-content -->
            <div class="breadcrumb-content">
                <div class="breadcrumb-list ">
                    <a href="<?php echo SITE_URL; ?>">Home</a>
                    <span>404</span>
                </div>
                <h1 class="breadcrumb-title fx-heading-1 text-uppercase " data-txaa-split-text-1><?php echo htmlspecialchars($pageSEO['h1_text']); ?></h1>
                <p class="breadcrumb-disc fx-para-1 has-clr-white fix"><span class="d-inline-block breadcrumb-slideup"><?php echo htmlspecialchars($pageSEO['h2_text']); ?></span></p>
                <div class="breadcrumb-btn fix">
                    <span class="d-inline-block breadcrumb-slideup">
                        <a href="<?php echo SITE_URL; ?>" aria-label="Go to home page" class="fx-pr-btn-1 has-hover-white">
                            <span class="text" data-back="back to home" data-front="back to home"></span>
                            <i class="fa-solid fa-angle-right"></i>
                        </a>
                    </span>
                </div>
            </div>
            <!-- right-img -->
            <div class="breadcrumb-img">
                <!-- <img src="assets/img/breadcrumb/b1-img-1.png" alt=""> -->
            </div>
        </div>
    </div>
</div>
<div id="has-jump"></div>
<div class="fx-gap-12"></div>

<!-- 404-error-start -->
<div class="fx-contact-us-1-area pb-70 pt-70">
    <div class="container fx-container-1">
        <div class="row align-items-center justify-content-center">
            <div class="col-lg-8 col-md-10">
                <div class="text-center">
                    <!-- error-number -->
                    <div class="mb-40">
                        <h1 class="fx-heading-1" style="font-size: 120px; line-height: 1; font-weight: 700; color: var(--fx-clr-pr-1);">404</h1>
                    </div>
                    
                    <!-- section-title -->
                    <div class="fx-blog-1-scn-title mb-35">
                        <h6 class="fx-subtitle-1">
                            <span class="txaa-split-text-2 txaa-split-text-2-ani">Oops!</span>
                        </h6>
                        <h2 class="fx-scn-title-1 txaa-split-text-3 txaa-split-text-3-ani">The Page You Requested Was Not Found</h2>
                    </div>
                    
                    <p class="fx-para-1 mb-30">
                        The page you are looking for was moved, removed, renamed or might never existed. 
                        Please use the search below to find what you're looking for.
                    </p>
                    
                    <!-- search-form -->
                    <div class="fx-form-1 mb-40">
                        <form method="get" action="<?php echo SITE_URL; ?>/search" class="fx-form-1">
                            <div class="fx-form-1-box">
                                <label class="fx-form-1-label">search:</label>
                                <div class="d-flex align-items-center" style="gap: 10px;">
                                    <input name="q" id="search-input" type="text" placeholder="Search for products, brands..." class="fx-form-1-input" value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>" style="flex: 1;">
                                    <button type="submit" aria-label="Search" class="fx-pr-btn-1" style="white-space: nowrap;">
                                        <span class="text" data-back="search" data-front="search"></span>
                                        <i class="fa-solid fa-magnifying-glass"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                    
                    <!-- action-buttons -->
                    <div class="fx-form-1-box fix txxaslideup">
                        <span class="txxaslideup-item fx-cube-1">
                            <a href="<?php echo SITE_URL; ?>" aria-label="Go to home page" class="fx-pr-btn-1">
                                <span class="text" data-back="back to home" data-front="back to home"></span>
                                <i class="fa-solid fa-angle-right"></i>
                            </a>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- 404-error-end -->

<?php require __DIR__ . '/includes/public/footer.php'; ?>

