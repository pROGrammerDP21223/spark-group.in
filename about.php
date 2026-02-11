<?php
/**
 * About Us Page
 */

$pageContent = $db->query("SELECT * FROM static_pages WHERE page_key = 'about' AND status = 'active'")->fetch();

// Get page SEO (entity_id = 1 for about page)
$pageSEO = getSEOData($db, 'page', 1, null);
if (empty($pageSEO['meta_title'])) {
    $pageSEO['meta_title'] = 'About Us - ' . SITE_NAME;
}
if (empty($pageSEO['meta_description'])) {
    $pageSEO['meta_description'] = 'Learn more about our company and our commitment to quality products and services';
}
if (empty($pageSEO['h1_text'])) {
    $pageSEO['h1_text'] = 'About Us';
}
if (empty($pageSEO['canonical_url'])) {
    $pageSEO['canonical_url'] = SITE_URL . '/about-us';
}
// Ensure seo_head is included
if (!isset($pageSEO['seo_head'])) {
    $pageSEO['seo_head'] = '';
}

require __DIR__ . '/includes/public/header.php';
require_once __DIR__ . '/includes/public/breadcrumb.php';
?>



<?php
// // Render breadcrumb
// renderBreadcrumb($pageSEO['h1_text'], [
//     ['text' => $pageSEO['h1_text']]
// ]);
?>


<div class="breadcrumb-area bg-default " data-background="assets/img/breadcrumb/b1-bg-1.png">
            <div class="container fx-container-1">
                <div class="breadcrumb-wrap">

                    <!-- left-content -->
                    <div class="breadcrumb-content">

                        <div class="breadcrumb-list " >
                            <a href="index.html">Home</a>
                            <span>About us</span>
                        </div>

                        <h1 class="breadcrumb-title fx-heading-1 text-uppercase " data-txaa-split-text-1 >Premium Industrial Tools<br> Solutions for <br> Modern Industries</h1>

                        <p class="breadcrumb-disc fx-para-1 has-clr-white fix"><span class="d-inline-block breadcrumb-slideup">Empowering manufacturing, construction, and engineering sectors with high-performance tools, abrasives, and fastening systems that boost efficiency, precision, and productivity.</span></p>

                     

                    </div>

                    <!-- right-img -->
                    <div class="breadcrumb-img">
                        <!-- <img src="assets/img/breadcrumb/b1-img-1.png" alt=""> -->
                    </div>

                </div>
            </div>
        </div>

        <div id="has-jump"></div>

        <!-- about-start -->
<div class="fx-about-1-area pt-120 pb-120 p-relative fix ">

<div class="fx-about-1-bg-img fix img-cover">
    <img src="assets/img/about/a1-bg-img-1.png" alt="">
</div>

<div class="container fx-container-1">

    <!-- section-title -->
    <div class="fx-about-1-scn-title mb-55">
        <h6 class="fx-subtitle-1">
            <span>02</span>
            <span class="txaa-split-text-2 txaa-split-text-2-ani">Who We Are</span>
        </h6>
        <h2 class="fx-scn-title-1 txaa-split-text-3 txaa-split-text-3-ani">AuthorizedChannel partner and Distributor
            of Industrial Tools, Abrasives & Fastening Solutions</h2>
    </div>
    <span class="fx-about-1-line mb-60 txaascale0 fx-cube-1"></span>

    <div class="fx-about-1-wrap row">

        <!-- left-side -->
        <div class="col-lg-4">
            <div class="fx-about-1-left">
                <div class="fx-about-1-card txaaslideup ">
                    <div class="card-img img-cover fix mb-20">
                        <img src="assets/img/about/a1-img-1.jpg" alt="">
                    </div>
                    <ul class="card-tags mb-15">
                        <li>BOSCH</li>
                        <li>TYROLIT</li>
                        <li>ICFS</li>
                    </ul>

                    <h5 class="fx-heading-1 fx-font-500  card-title">
                        <a href="#" aria-label="name">Industrial Tools & Fastening Solutions</a>
                    </h5>

                    <p class="fx-para-1 card-disc has-opacity-7">Supplying high-performance power tools, abrasives,
                        and construction fastening systems for industrial and infrastructure applications.</p>
                </div>
            </div>
        </div>

        <!-- right-side -->
        <div class="col-lg-8">
            <div class="fx-about-1-content">
                <p class="fx-para-1 disc has-opacity-7">Spark Systems is an authorized dealer and channel partner of
                    Bosch, Tyrolit, and ICFS, delivering high-quality power tools, abrasives, construction
                    chemicals, and fastening systems. Based in Pune, we serve manufacturing industries, fabrication
                    units, infrastructure contractors, and engineering companies with reliable products and
                    technical support.
                </p>

                <div class="content-img fix img-cover mb-50">
                    <img src="assets/img/process/home-add.jpeg"" alt="">
                </div>

                <p class=" fx-para-1 disc has-opacity-7">With over a decade of industry experience, we focus on
                    providing durable, performance-driven solutions for cutting, grinding, drilling, anchoring, and
                    precision measurement. Our commitment to genuine products, competitive pricing, and dependable
                    service makes us a trusted partner for industrial and construction requirements.
                    </p>



                   
                </div>
            </div>

        </div>

    </div>
</div>
<!-- about-end -->


<?php require __DIR__ . '/includes/public/footer.php'; ?>

