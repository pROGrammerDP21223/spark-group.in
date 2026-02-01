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
    'h2_text' => '',
    'seo_head' => ''
];

require __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/breadcrumb.php';
?>

<div class="offcanvas-overlay"></div>

<?php
// Render breadcrumb
renderBreadcrumb('Page Not Found', [
    ['text' => 'Home', 'url' => SITE_URL],
    ['text' => '404']
]);
?>

<!-- START MAIN CONTENT -->
<div class="main_content">

<!-- START 404 SECTION -->
<div class="section">
	<div class="error_wrap">
    	<div class="container">
            <div class="row align-items-center justify-content-center">
                <div class="col-lg-6 col-md-10 order-lg-first">
                	<div class="text-center">
                        <div class="error_txt">404</div>
                        <h5 class="mb-2 mb-sm-3">oops! The page you requested was not found!</h5> 
                        <p>The page you are looking for was moved, removed, renamed or might never existed.</p>
                        <div class="search_form pb-3 pb-md-4">
                            <form method="get" action="<?php echo SITE_URL; ?>/search">
                                <input name="q" id="text" type="text" placeholder="Search" class="form-control" value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>">
                                <button type="submit" class="btn icon_search"><i class="ion-ios-search-strong"></i></button>
                            </form>
                        </div>
                        <a href="<?php echo SITE_URL; ?>" class="btn btn-fill-out">Back To Home</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- END 404 SECTION -->

</div>
<!-- END MAIN CONTENT -->

<?php require __DIR__ . '/includes/footer.php'; ?>

