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

// Get slider images from database
$sliderImages = $db->query("SELECT * FROM slider_images WHERE status = 'active' ORDER BY sort_order ASC, id ASC")->fetchAll();

// Get featured products
$featuredProducts = $db->query("SELECT p.*, b.slug as brand_slug, b.name as brand_name
                                FROM products p
                                LEFT JOIN brands b ON p.brand_id = b.id
                                WHERE p.featured = 1 AND p.status = 'active' AND b.status = 'active'
                                ORDER BY p.sort_order ASC, p.created_at DESC
                                LIMIT 12")->fetchAll();

// Get about us page content
$aboutContent = $db->query("SELECT * FROM static_pages WHERE page_key = 'about' AND status = 'active'")->fetch();

// Get FAQs from database
$faqs = $db->query("SELECT * FROM faqs WHERE status = 'active' ORDER BY sort_order ASC, id ASC")->fetchAll();

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

<!-- hero-start -->
<div class="fx-hero-1-area p-relative fix">
    <div class="fx-hero-1-slider">
        <div class="swiper-container fx-hero-1-active fix">
            <div class="swiper-wrapper">

                <!-- single-slider -->
                <?php foreach ($sliderImages as $index => $slide): ?>
                    <div class="swiper-slide">
                        <div class="fx-hero-1-slider-item">
                            <div class="fx-hero-1-slider-item-img img-cover fix">
                                <img src="<?php echo UPLOAD_URL; ?>/<?php echo htmlspecialchars($slide['image'] ?? ''); ?>"
                                    alt="">
                            </div>

                        </div>

                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="fx-hero-1-pagination-posi">
        <div class="fx-hero-1-pagination"></div>
    </div>

</div>
<!-- serve-start -->
<div class="fx-serve-1-area fix p-relative pt-120 pb-130">

    <div class="fx-serve-1-bg fix img-cover">
        <img class="fx-cube-1" src="assets/img/serve/s1-bg-1.png" alt="">
    </div>

    <div class="container fx-container-1">

        <!-- section-title -->
        <div class="fx-serve-1-scn-title mb-45">
            <h6 class="fx-subtitle-1">
                <span>01</span>
                <span class="txaa-split-text-2 txaa-split-text-2-ani">industries we serve</span>
            </h6>
            <h2 class="fx-scn-title-1 txaa-split-text-3 txaa-split-text-3-ani">Powering Industries with Advanced Tools,
                Abrasives & Fastening Solutions</h2>
        </div>


        <!-- slider -->
        <div class="fx-serve-1-slider p-relative mb-80">
            <div class="swiper-container fix fx-serve-1-active">
                <div class="swiper-wrapper">

                    <!-- single-item -->
                    <div class="swiper-slide">
                        <div class="fx-serve-1-slider-item">
                            <div class="item-img fix img-cover">
                                <img src="assets/img/serve/automotive-auto-components.jpg" alt="">
                            </div>
                            <h5 class="item-title fx-heading-1 fx-font-500">
                                <a href="#" aria-label="name">Automotive & Auto Components</a>
                            </h5>
                        </div>
                    </div>

                    <!-- single-item -->
                    <div class="swiper-slide">
                        <div class="fx-serve-1-slider-item">
                            <div class="item-img fix img-cover">
                                <img src="assets/img/serve/fabrication-engineering.jpg" alt="">
                            </div>
                            <h5 class="item-title fx-heading-1 fx-font-500">
                                <a href="#" aria-label="name">Fabrication & Engineering</a>
                            </h5>
                        </div>
                    </div>

                    <!-- single-item -->
                    <div class="swiper-slide">
                        <div class="fx-serve-1-slider-item">
                            <div class="item-img fix img-cover">
                                <img src="assets/img/serve/construction-infrastructure.jpg" alt="">
                            </div>
                            <h5 class="item-title fx-heading-1 fx-font-500">
                                <a href="#" aria-label="name">Construction & Infrastructure</a>
                            </h5>
                        </div>
                    </div>

                    <!-- single-item -->
                    <div class="swiper-slide">
                        <div class="fx-serve-1-slider-item">
                            <div class="item-img fix img-cover">
                                <img src="assets/img/serve/energy-sector.jpg" alt="">
                            </div>
                            <h5 class="item-title fx-heading-1 fx-font-500">
                                <a href="#" aria-label="name">Energy Sector</a>
                            </h5>
                        </div>
                    </div>

                    <!-- single-item -->
                    <div class="swiper-slide">
                        <div class="fx-serve-1-slider-item">
                            <div class="item-img fix img-cover">
                                <img src="assets/img/serve/epc-industrial-contractors.jpg" alt="">
                            </div>
                            <h5 class="item-title fx-heading-1 fx-font-500">
                                <a href="#" aria-label="name">EPC & Industrial Contractors</a>
                            </h5>
                        </div>
                    </div>
                </div>
            </div>

            <!-- slider-btn -->
            <div class="fx-serve-1-slider-btn">
                <div class="slider-btn fx-serve-1-slider-prev">
                    <i class="fa-regular fa-angle-left"></i>
                </div>
                <div class="slider-btn fx-serve-1-slider-next">
                    <i class="fa-regular fa-angle-right"></i>
                </div>
            </div>

            <div class="fx-serve-1-slider-shpae">
                <svg width="946" height="64" viewBox="0 0 946 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <g clip-path="url(#clip0_7_989)">
                        <line x1="-30.8178" y1="63.5934" x2="5.95178" y2="-0.093351" stroke="black"
                            stroke-opacity="0.16" />
                        <line x1="-20.8178" y1="63.5934" x2="15.9518" y2="-0.093351" stroke="black"
                            stroke-opacity="0.16" />
                        <line x1="-10.8178" y1="63.5934" x2="25.9518" y2="-0.093351" stroke="black"
                            stroke-opacity="0.16" />
                        <line x1="-0.817778" y1="63.5934" x2="35.9518" y2="-0.093351" stroke="black"
                            stroke-opacity="0.16" />
                        <line x1="9.18222" y1="63.5934" x2="45.9518" y2="-0.093351" stroke="black"
                            stroke-opacity="0.16" />
                        <line x1="19.1822" y1="63.5934" x2="55.9518" y2="-0.093351" stroke="black"
                            stroke-opacity="0.16" />
                        <line x1="29.1822" y1="63.5934" x2="65.9518" y2="-0.093351" stroke="black"
                            stroke-opacity="0.16" />
                        <line x1="39.1822" y1="63.5934" x2="75.9518" y2="-0.093351" stroke="black"
                            stroke-opacity="0.16" />
                        <line x1="49.1822" y1="63.5934" x2="85.9518" y2="-0.093351" stroke="black"
                            stroke-opacity="0.16" />
                        <line x1="59.1822" y1="63.5934" x2="95.9518" y2="-0.093351" stroke="black"
                            stroke-opacity="0.16" />
                        <line x1="69.1822" y1="63.5934" x2="105.952" y2="-0.093351" stroke="black"
                            stroke-opacity="0.16" />
                        <line x1="79.1822" y1="63.5934" x2="115.952" y2="-0.093351" stroke="black"
                            stroke-opacity="0.16" />
                        <line x1="89.1822" y1="63.5934" x2="125.952" y2="-0.093351" stroke="black"
                            stroke-opacity="0.16" />
                        <line x1="99.1822" y1="63.5934" x2="135.952" y2="-0.093351" stroke="black"
                            stroke-opacity="0.16" />
                        <line x1="109.182" y1="63.5934" x2="145.952" y2="-0.093351" stroke="black"
                            stroke-opacity="0.16" />
                        <line x1="119.182" y1="63.5934" x2="155.952" y2="-0.093351" stroke="black"
                            stroke-opacity="0.16" />
                        <line x1="129.182" y1="63.5934" x2="165.952" y2="-0.093351" stroke="black"
                            stroke-opacity="0.16" />
                        <line x1="139.182" y1="63.5934" x2="175.952" y2="-0.093351" stroke="black"
                            stroke-opacity="0.16" />
                        <line x1="149.182" y1="63.5934" x2="185.952" y2="-0.093351" stroke="black"
                            stroke-opacity="0.16" />
                        <line x1="159.182" y1="63.5934" x2="195.952" y2="-0.093351" stroke="black"
                            stroke-opacity="0.16" />
                        <line x1="169.182" y1="63.5934" x2="205.952" y2="-0.093351" stroke="black"
                            stroke-opacity="0.16" />
                        <line x1="179.182" y1="63.5934" x2="215.952" y2="-0.093351" stroke="black"
                            stroke-opacity="0.16" />
                        <line x1="189.182" y1="63.5934" x2="225.952" y2="-0.093351" stroke="black"
                            stroke-opacity="0.16" />
                        <line x1="199.182" y1="63.5934" x2="235.952" y2="-0.093351" stroke="black"
                            stroke-opacity="0.16" />
                        <line x1="209.182" y1="63.5934" x2="245.952" y2="-0.093351" stroke="black"
                            stroke-opacity="0.16" />
                        <line x1="219.182" y1="63.5934" x2="255.952" y2="-0.093351" stroke="black"
                            stroke-opacity="0.16" />
                        <line x1="229.182" y1="63.5934" x2="265.952" y2="-0.093351" stroke="black"
                            stroke-opacity="0.16" />
                        <line x1="239.182" y1="63.5934" x2="275.952" y2="-0.093351" stroke="black"
                            stroke-opacity="0.16" />
                        <line x1="249.182" y1="63.5934" x2="285.952" y2="-0.093351" stroke="black"
                            stroke-opacity="0.16" />
                        <line x1="259.182" y1="63.5934" x2="295.952" y2="-0.093351" stroke="black"
                            stroke-opacity="0.16" />
                        <line x1="269.182" y1="63.5934" x2="305.952" y2="-0.093351" stroke="black"
                            stroke-opacity="0.16" />
                        <line x1="279.182" y1="63.5934" x2="315.952" y2="-0.093351" stroke="black"
                            stroke-opacity="0.16" />
                        <line x1="289.182" y1="63.5934" x2="325.952" y2="-0.093351" stroke="black"
                            stroke-opacity="0.16" />
                        <line x1="299.182" y1="63.5934" x2="335.952" y2="-0.093351" stroke="black"
                            stroke-opacity="0.16" />
                        <line x1="309.182" y1="63.5934" x2="345.952" y2="-0.093351" stroke="black"
                            stroke-opacity="0.16" />
                        <line x1="319.182" y1="63.5934" x2="355.952" y2="-0.093351" stroke="black"
                            stroke-opacity="0.16" />
                        <line x1="329.182" y1="63.5934" x2="365.952" y2="-0.093351" stroke="black"
                            stroke-opacity="0.16" />
                        <line x1="339.182" y1="63.5934" x2="375.952" y2="-0.093351" stroke="black"
                            stroke-opacity="0.16" />
                        <line x1="349.182" y1="63.5934" x2="385.952" y2="-0.093351" stroke="black"
                            stroke-opacity="0.16" />
                        <line x1="359.182" y1="63.5934" x2="395.952" y2="-0.093351" stroke="black"
                            stroke-opacity="0.16" />
                        <line x1="369.182" y1="63.5934" x2="405.952" y2="-0.093351" stroke="black"
                            stroke-opacity="0.16" />
                        <line x1="379.182" y1="63.5934" x2="415.952" y2="-0.093351" stroke="black"
                            stroke-opacity="0.16" />
                        <line x1="389.182" y1="63.5934" x2="425.952" y2="-0.093351" stroke="black"
                            stroke-opacity="0.16" />
                        <line x1="399.182" y1="63.5934" x2="435.952" y2="-0.093351" stroke="black"
                            stroke-opacity="0.16" />
                        <line x1="409.182" y1="63.5934" x2="445.952" y2="-0.093351" stroke="black"
                            stroke-opacity="0.16" />
                        <line x1="419.182" y1="63.5934" x2="455.952" y2="-0.093351" stroke="black"
                            stroke-opacity="0.16" />
                        <line x1="429.182" y1="63.5934" x2="465.952" y2="-0.093351" stroke="black"
                            stroke-opacity="0.16" />
                        <line x1="439.182" y1="63.5934" x2="475.952" y2="-0.093351" stroke="black"
                            stroke-opacity="0.16" />
                        <line x1="449.182" y1="63.5934" x2="485.952" y2="-0.093351" stroke="black"
                            stroke-opacity="0.16" />
                        <line x1="459.182" y1="63.5934" x2="495.952" y2="-0.093351" stroke="black"
                            stroke-opacity="0.16" />
                        <line x1="469.182" y1="63.5934" x2="505.952" y2="-0.093351" stroke="black"
                            stroke-opacity="0.16" />
                        <line x1="479.182" y1="63.5934" x2="515.952" y2="-0.093351" stroke="black"
                            stroke-opacity="0.16" />
                        <line x1="489.182" y1="63.5934" x2="525.952" y2="-0.093351" stroke="black"
                            stroke-opacity="0.16" />
                        <line x1="499.182" y1="63.5934" x2="535.952" y2="-0.093351" stroke="black"
                            stroke-opacity="0.16" />
                        <line x1="509.182" y1="63.5934" x2="545.952" y2="-0.093351" stroke="black"
                            stroke-opacity="0.16" />
                        <line x1="519.182" y1="63.5934" x2="555.952" y2="-0.093351" stroke="black"
                            stroke-opacity="0.16" />
                        <line x1="529.182" y1="63.5934" x2="565.952" y2="-0.093351" stroke="black"
                            stroke-opacity="0.16" />
                        <line x1="539.182" y1="63.5934" x2="575.952" y2="-0.093351" stroke="black"
                            stroke-opacity="0.16" />
                        <line x1="549.182" y1="63.5934" x2="585.952" y2="-0.093351" stroke="black"
                            stroke-opacity="0.16" />
                        <line x1="559.182" y1="63.5934" x2="595.952" y2="-0.093351" stroke="black"
                            stroke-opacity="0.16" />
                        <line x1="569.182" y1="63.5934" x2="605.952" y2="-0.093351" stroke="black"
                            stroke-opacity="0.16" />
                        <line x1="579.182" y1="63.5934" x2="615.952" y2="-0.093351" stroke="black"
                            stroke-opacity="0.16" />
                        <line x1="589.182" y1="63.5934" x2="625.952" y2="-0.093351" stroke="black"
                            stroke-opacity="0.16" />
                        <line x1="599.182" y1="63.5934" x2="635.952" y2="-0.093351" stroke="black"
                            stroke-opacity="0.16" />
                        <line x1="609.182" y1="63.5934" x2="645.952" y2="-0.093351" stroke="black"
                            stroke-opacity="0.16" />
                        <line x1="619.182" y1="63.5934" x2="655.952" y2="-0.093351" stroke="black"
                            stroke-opacity="0.16" />
                        <line x1="629.182" y1="63.5934" x2="665.952" y2="-0.093351" stroke="black"
                            stroke-opacity="0.16" />
                        <line x1="639.182" y1="63.5934" x2="675.952" y2="-0.093351" stroke="black"
                            stroke-opacity="0.16" />
                        <line x1="649.182" y1="63.5934" x2="685.952" y2="-0.093351" stroke="black"
                            stroke-opacity="0.16" />
                        <line x1="659.182" y1="63.5934" x2="695.952" y2="-0.093351" stroke="black"
                            stroke-opacity="0.16" />
                        <line x1="669.182" y1="63.5934" x2="705.952" y2="-0.093351" stroke="black"
                            stroke-opacity="0.16" />
                        <line x1="679.182" y1="63.5934" x2="715.952" y2="-0.093351" stroke="black"
                            stroke-opacity="0.16" />
                        <line x1="689.182" y1="63.5934" x2="725.952" y2="-0.093351" stroke="black"
                            stroke-opacity="0.16" />
                        <line x1="699.182" y1="63.5934" x2="735.952" y2="-0.093351" stroke="black"
                            stroke-opacity="0.16" />
                        <line x1="709.182" y1="63.5934" x2="745.952" y2="-0.093351" stroke="black"
                            stroke-opacity="0.16" />
                        <line x1="719.182" y1="63.5934" x2="755.952" y2="-0.093351" stroke="black"
                            stroke-opacity="0.16" />
                        <line x1="729.182" y1="63.5934" x2="765.952" y2="-0.093351" stroke="black"
                            stroke-opacity="0.16" />
                        <line x1="739.182" y1="63.5934" x2="775.952" y2="-0.093351" stroke="black"
                            stroke-opacity="0.16" />
                        <line x1="749.182" y1="63.5934" x2="785.952" y2="-0.093351" stroke="black"
                            stroke-opacity="0.16" />
                        <line x1="759.182" y1="63.5934" x2="795.952" y2="-0.093351" stroke="black"
                            stroke-opacity="0.16" />
                        <line x1="769.182" y1="63.5934" x2="805.952" y2="-0.093351" stroke="black"
                            stroke-opacity="0.16" />
                        <line x1="779.182" y1="63.5934" x2="815.952" y2="-0.093351" stroke="black"
                            stroke-opacity="0.16" />
                        <line x1="789.182" y1="63.5934" x2="825.952" y2="-0.093351" stroke="black"
                            stroke-opacity="0.16" />
                        <line x1="799.182" y1="63.5934" x2="835.952" y2="-0.093351" stroke="black"
                            stroke-opacity="0.16" />
                        <line x1="809.182" y1="63.5934" x2="845.952" y2="-0.093351" stroke="black"
                            stroke-opacity="0.16" />
                        <line x1="819.182" y1="63.5934" x2="855.952" y2="-0.093351" stroke="black"
                            stroke-opacity="0.16" />
                        <line x1="829.182" y1="63.5934" x2="865.952" y2="-0.093351" stroke="black"
                            stroke-opacity="0.16" />
                        <line x1="839.182" y1="63.5934" x2="875.952" y2="-0.093351" stroke="black"
                            stroke-opacity="0.16" />
                        <line x1="849.182" y1="63.5934" x2="885.952" y2="-0.093351" stroke="black"
                            stroke-opacity="0.16" />
                        <line x1="859.182" y1="63.5934" x2="895.952" y2="-0.093351" stroke="black"
                            stroke-opacity="0.16" />
                        <line x1="869.182" y1="63.5934" x2="905.952" y2="-0.093351" stroke="black"
                            stroke-opacity="0.16" />
                        <line x1="879.182" y1="63.5934" x2="915.952" y2="-0.093351" stroke="black"
                            stroke-opacity="0.16" />
                        <line x1="889.182" y1="63.5934" x2="925.952" y2="-0.093351" stroke="black"
                            stroke-opacity="0.16" />
                        <line x1="899.182" y1="63.5934" x2="935.952" y2="-0.093351" stroke="black"
                            stroke-opacity="0.16" />
                        <line x1="909.182" y1="63.5934" x2="945.952" y2="-0.093351" stroke="black"
                            stroke-opacity="0.16" />
                        <line x1="919.182" y1="63.5934" x2="955.952" y2="-0.093351" stroke="black"
                            stroke-opacity="0.16" />
                        <line x1="929.182" y1="63.5934" x2="965.952" y2="-0.093351" stroke="black"
                            stroke-opacity="0.16" />
                        <line x1="939.182" y1="63.5934" x2="975.952" y2="-0.093351" stroke="black"
                            stroke-opacity="0.16" />
                    </g>
                    <defs>
                        <clipPath id="clip0_7_989">
                            <rect width="946" height="64" fill="white" />
                        </clipPath>
                    </defs>
                </svg>
            </div>
        </div>

        <!-- content  -->
        <div class="fx-serve-1-content">
            <!-- <a href="https://www.youtube.com/watch?v=f8ljCoogGtQ" aria-label="name" class="fx-play-btn-1 popup-video">
            <i class="fa-light fa-circle-play"></i>
            <span>play video</span>
        </a> -->

            <p class="fx-para-1 disc has-opacity-7 fix txxaslideup">
                <span class="txxaslideup-item fx-cube-1">Spark Systems supports leading industries with high-performance
                    power tools, abrasives, and fastening solutions. As an authorized distributor of Bosch, Tyrolit, and
                    ICFS, we deliver reliable products and technical support for manufacturing, construction,
                    infrastructure, and heavy engineering sectors.</span>
            </p>
            <!-- <div class="fix txxaslideup">
            <div class="txxaslideup-item fx-cube-1">
                <div class="btn-wrap">
                    <a href="services.html" aria-label="name" class="fx-pr-btn-1">
                        <span class="text" data-back="browse all services" data-front="browse all services"></span>
                        <i class="fa-solid fa-angle-right"></i>
                    </a>
                </div>
            </div>
        </div> -->

        </div>
    </div>
</div>
<!-- serve-end -->

<!-- hero-end -->
<div class="fx-gap-12 "></div>

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
                            <img src="https://images.unsplash.com/photo-1535732759880-bbd5c7265e3f?q=80&w=764&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" alt="">
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



                        <div class="fx-about-1-content-btn txxaslideup fix">
                            <div class="txxaslideup-item fx-cube-1">
                                <a href="<?php echo SITE_URL; ?>/about-us" aria-label="name" class="fx-pr-btn-1">
                                    <span class="text" data-back="Know About Us" data-front="Know About Us"></span>
                                    <i class="fa-solid fa-angle-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>
    <!-- about-end -->
    <!-- process-start -->
    <div class="fx-process-1-area pt-150 pb-70 fix p-relative bg-default"
        data-background="assets/img/process/p1-bg-img-1.png">



        <div class="container fx-container-1">

            <!-- section-title -->
            <div class="fx-process-1-scn-title text-center mb-50">
                <h6 class="fx-subtitle-1 has-mb-28 has-clr-white">
                    <span>03</span>
                    <span class="txaa-split-text-2 txaa-split-text-2-ani">Our Brands & Products</span>
                </h6>
                <h2 class="fx-scn-title-3 txaa-split-text-3 txaa-split-text-3-ani has-clr-white">Explore Our Range of
                    Industrial Tools, Abrasives & Fastening Solutions
                </h2>
            </div>


            <ul class="fx-process-1-tabs-btn p-relative mb-80" role="tablist">

                <li class="fx-process-1-tabs-btn-line txaascale0 fx-cube-1"></li>

                <?php if (!empty($brands)): ?>
                    <?php $firstBrand = true; ?>
                    <?php foreach ($brands as $brand): ?>
                        <!-- single-btn -->
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fx-heading-2 <?php echo $firstBrand ? 'active' : ''; ?>"
                                id="process-tab-<?php echo $brand['id']; ?>" data-bs-toggle="tab"
                                data-bs-target="#process-<?php echo $brand['id']; ?>" type="button" role="tab"
                                aria-controls="process-<?php echo $brand['id']; ?>"
                                aria-selected="<?php echo $firstBrand ? 'true' : 'false'; ?>">
                                <!-- <img src="<?php echo UPLOAD_URL . '/' . $brand['image']; ?>" alt="" width="200"> -->
                                <?php echo htmlspecialchars($brand['name']); ?>
                            </button>
                        </li>
                        <?php $firstBrand = false; ?>
                    <?php endforeach; ?>
                <?php endif; ?>

            </ul>

            <div class="tab-content fx-process-1-tabs-content" id="myTabContent">

                <?php if (!empty($brands)): ?>
                    <?php $firstBrand = true; ?>
                    <?php foreach ($brands as $brand): ?>
                        <!-- single-pane -->
                        <div class="tab-pane fade animated fadeInUp <?php echo $firstBrand ? 'show active' : ''; ?>"
                            id="process-<?php echo $brand['id']; ?>" role="tabpanel"
                            aria-labelledby="process-tab-<?php echo $brand['id']; ?>">

                            <div class="swiper-container mb-35 fix fx-t1-active">
                                <?php
                                // Get products for this brand
                                $brandProducts = $db->prepare("SELECT * FROM products WHERE brand_id = ? AND status = 'active' ORDER BY sort_order ASC, name ASC LIMIT 12");
                                $brandProducts->execute([$brand['id']]);
                                $brandProducts = $brandProducts->fetchAll();
                                $productCount = count($brandProducts);
                                $centerClass = ($productCount <= 4) ? 'justify-content-center' : '';
                                ?>
                                <div class="swiper-wrapper 
                                <?php 
                                echo $centerClass;
                                 ?>" 
                                 <?php 
                                 echo ($productCount <= 3) ? 'style="justify-content: center;"' : ''; 
                                ?>
                                >
                                    <?php if (!empty($brandProducts)): ?>
                                        <?php foreach ($brandProducts as $product): ?>
                                            <div class="swiper-slide">
                                                <a href="<?php echo SITE_URL . '/' . $brand['slug'] . '/' . $product['slug']; ?>"
                                                   aria-label="<?php echo htmlspecialchars($product['name']); ?>"
                                                   class="fx-team-1-slider-item"
                                                   style="display: block; text-decoration: none; color: inherit;">
                                                    <div class="item-img">
                                                        <?php if (!empty($product['image'])): ?>
                                                            <img src="<?php echo UPLOAD_URL . '/' . $product['image']; ?>"
                                                                alt="<?php echo htmlspecialchars($product['name']); ?>">
                                                        <?php else: ?>
                                                            <img src="assets/images/product_img1.jpg"
                                                                alt="<?php echo htmlspecialchars($product['name']); ?>">
                                                        <?php endif; ?>
                                                    </div>
                                                    <h5 class="person-name fx-heading-2 text-white fx-font-500">
                                                        <?php echo htmlspecialchars($product['name']); ?>
                                                    </h5>
                                                </a>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="swiper-slide">
                                            <div class="fx-team-1-slider-item">
                                                <h5 class="person-name fx-heading-2 fx-font-500">
                                                    <?php echo htmlspecialchars($brand['name']); ?> - No products available.
                                                </h5>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="fx-team-1-slider-pagination text-center mb-50">
                                <div class="fx-slider-pagi-1 fx-t1-pagination">

                                </div>
                            </div>

                            <div class="fx-slider-btn-1">
                                <div class="fx-slider-btn-1-item fx-team-1-slider-btn-left  fx-t1-slider-prev">
                                    <i class="fa-solid fa-angle-left"></i>
                                    <i class="fa-solid fa-angle-left"></i>
                                </div>
                                <div class="fx-slider-btn-1-item fx-team-1-slider-btn-right fx-t1-slider-next">
                                    <i class="fa-solid fa-angle-right"></i>
                                    <i class="fa-solid fa-angle-right"></i>
                                </div>
                            </div>

                        </div>
                        <?php $firstBrand = false; ?>
                    <?php endforeach; ?>
                <?php endif; ?>

            </div>
        </div>

    </div>
    <!-- process-end -->

    <!-- START SECTION SHOP -->


    <!-- END SECTION SHOP -->




    <!-- START SECTION SHOP -->
    <!-- <div class="section small_pt">
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
                    <div class="product_slider carousel_slider owl-carousel owl-theme nav_style1"
                        data-loop="<?php echo $shouldLoop ? 'true' : 'false'; ?>" data-dots="false" data-nav="true"
                        data-margin="20"
                        data-responsive='{"0":{"items": "1"}, "481":{"items": "2"}, "768":{"items": "3"}, "991":{"items": "4"}}'>
                        <?php foreach ($featuredProducts as $index => $product): ?>
                            <?php
                            $productUrl = SITE_URL . '/' . htmlspecialchars($product['brand_slug']) . '/' . htmlspecialchars($product['slug']);
                            $gallery = !empty($product['gallery']) ? json_decode($product['gallery'], true) : [];
                            $hoverImage = !empty($gallery) ? $gallery[0] : $product['image'];
                            ?>
                            <div class="item">
                                <a href="<?php echo $productUrl; ?>" class="product_wrap_link"
                                    style="display: block; text-decoration: none; color: inherit;">
                                    <div class="product_wrap">
                                        <div class="product_img">
                                            <?php if (!empty($product['image'])): ?>
                                                <img src="<?php echo UPLOAD_URL . '/' . htmlspecialchars($product['image']); ?>"
                                                    alt="<?php echo htmlspecialchars($product['name']); ?>">
                                                <?php if ($hoverImage && $hoverImage != $product['image']): ?>


                                                <?php endif; ?>
                                            <?php else: ?>
                                                <img src="assets/images/el_img<?php echo ($index % 12) + 1; ?>.jpg"
                                                    alt="<?php echo htmlspecialchars($product['name']); ?>">
                                                <img class="product_hover_img"
                                                    src="assets/images/el_hover_img<?php echo ($index % 12) + 1; ?>.jpg"
                                                    alt="<?php echo htmlspecialchars($product['name']); ?>">
                                            <?php endif; ?>
                                        </div>
                                        <div class="product_info">
                                            <h6 class="product_title"><?php echo htmlspecialchars($product['name']); ?></h6>
                                            <?php if (!empty($product['short_description'])): ?>
                                                <div class="pr_desc">
                                                    <p><?php echo htmlspecialchars(substr($product['short_description'], 0, 100)); ?><?php echo strlen($product['short_description']) > 100 ? '...' : ''; ?>
                                                    </p>
                                                </div>
                                            <?php elseif (!empty($product['description'])): ?>
                                                <div class="pr_desc">
                                                    <p><?php echo htmlspecialchars(substr($product['description'], 0, 100)); ?><?php echo strlen($product['description']) > 100 ? '...' : ''; ?>
                                                    </p>
                                                </div>
                                            <?php endif; ?>
                                            <div class="product_price">
                                                <span
                                                    class="price"><?php echo htmlspecialchars($product['brand_name']); ?></span>
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
</div> -->
    <!-- END SECTION SHOP -->



    <!-- faqs-start -->
    <div class="fx-faqs-1-area pt-120 pb-120">
        <div class="container">
            <div class="fx-faqs-1-wrap">

                <!-- section-title -->
                <div class="fx-faqs-1-scn-title mb-50 text-center">
                    <h2 class="fx-scn-title-1 txaa-split-text-3 txaa-split-text-3-ani">Faq’s <br> Common Questions</h2>
                </div>

                <div class="fx-accordion" id="accordionExample_31">

                    <?php if (!empty($faqs)): ?>
                        <?php foreach ($faqs as $index => $faq): ?>
                            <?php
                            $faqIndex = $index + 1;
                            $isFirst = $index === 0;
                            ?>
                            <!-- single-faq -->
                            <div class="fx-accordion-item <?php echo $isFirst ? '' : ''; ?>">
                                <div class="item-header" id="heading<?php echo $faqIndex; ?>">
                                    <button
                                        class="item-title fx-heading-1 fx-font-500 <?php echo $isFirst ? '' : 'collapsed'; ?>"
                                        type="button" data-bs-toggle="collapse"
                                        data-bs-target="#collapse<?php echo $faqIndex; ?>"
                                        aria-expanded="<?php echo $isFirst ? 'true' : 'false'; ?>"
                                        aria-controls="collapse<?php echo $faqIndex; ?>">
                                        <span class="icon">
                                            <i class="fa-regular fa-arrow-right-long"></i>
                                        </span>
                                        <?php echo htmlspecialchars($faq['question']); ?>
                                    </button>
                                </div>
                                <div id="collapse<?php echo $faqIndex; ?>"
                                    class="accordion-collapse collapse <?php echo $isFirst ? 'show' : ''; ?>"
                                    aria-labelledby="heading<?php echo $faqIndex; ?>" data-bs-parent="#accordionExample_31">
                                    <div class="item-body">
                                        <p class="fx-para-1">
                                            <?php echo htmlspecialchars($faq['answer']); ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>
    <!-- faqs-end -->



</div>
<!-- END MAIN CONTENT -->
<?php require __DIR__ . '/includes/public/footer.php'; ?>