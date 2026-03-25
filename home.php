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

<!-- hero-end -->
<div class="fx-gap-12 "></div>

<!-- about-start -->
<div class="fx-about-1-area pt-120 pb-120 p-relative fix ">

    <div class="fx-about-1-bg-img fix img-cover">
     
        <img src="assets/img/about/a1-bg-img-2.png" alt="ICFS Chemical Anchor Stud Manufacturing Process">
    </div>  

    <div class="container fx-container-1">

        <!-- section-title -->
        <div class="fx-about-1-scn-title mb-55">
            <h6 class="fx-subtitle-1">
                <span>02</span>
                <span class="txaa-split-text-2 txaa-split-text-2-ani">Who We Are</span>
            </h6>
            <h2 class="fx-scn-title-1 txaa-split-text-3 txaa-split-text-3-ani">Authorized Channel Partner and Distributor
                with a Strong Focus on ICFS Fastening Solutions</h2>
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
                            <a href="#" aria-label="name">ICFS-Focused Construction Fastening Portfolio</a>
                        </h5>

                        <p class="fx-para-1 card-disc has-opacity-7">Delivering high-performance power tools, abrasives,
                            and proven ICFS fastening systems for industrial, infrastructure, and project-site applications.</p>
                    </div>
                </div>
            </div>

            <!-- right-side -->
            <div class="col-lg-8">
                <div class="fx-about-1-content">
                    <p class="fx-para-1 disc has-opacity-7">Spark Systems is an authorized dealer and channel partner of
                        Bosch, Tyrolit, and ICFS. Along with industrial tools and abrasives, we place special emphasis on
                        ICFS (IndoSpark) fastening and construction chemical solutions for manufacturing units,
                        infrastructure contractors, fabrication teams, and engineering projects. Based in Pune, we support
                        customers with reliable products, technical guidance, and dependable supply.
                    </p>

                    <div class="content-img fix img-cover mb-50">
                        <img src="assets/img/process/home-add.jpeg"" alt="">
                    </div>

                    <p class=" fx-para-1 disc has-opacity-7">Our ICFS product focus includes
                        <a href="https://spark-group.in/icfs/chemical-anchor">Chemical Anchor</a>,
                        <a href="https://spark-group.in/icfs/chemical-mortars">Chemical Mortars</a>,
                        <a href="https://spark-group.in/icfs/mechanical-anchor">Mechanical Anchor</a>,
                        <a href="https://spark-group.in/icfs/nylog-plug">Nylon Plug</a>, and
                        <a href="https://spark-group.in/icfs/pu-foam">PU Foam</a>.
                        These solutions are selected to improve site productivity, load reliability, and long-term structural
                        performance across commercial and industrial applications.
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

    <!-- ICFS Highlight Start -->
    <div class="fx-icfs-highlight-area pt-100 pb-100 p-relative fix bg-default">
        <div class="container fx-container-1">
            <div class="fx-process-1-scn-title text-center mb-50">
                <h6 class="fx-subtitle-1 has-mb-28 has-clr-white">
                    <span>ICFS</span>
                    <span class="txaa-split-text-2 txaa-split-text-2-ani">IndoSpark Fastening Solutions</span>
                </h6>
                <h2 class="fx-scn-title-3 txaa-split-text-3 txaa-split-text-3-ani has-clr-white">
                    Select the Right ICFS Chemical & Mechanical Anchor System
                </h2>
            </div>

            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <div class="fx-team-1-slider-item" style="height: 100%;">
                        <div class="mb-3" style="font-size: 26px; line-height: 1; color: #ffffff;">
                            <i class="fa-solid fa-anchor"></i>
                        </div>
                        <h5 class="person-name fx-heading-2 fx-font-500">
                            <a href="<?php echo SITE_URL; ?>/icfs/chemical-anchor" style="text-decoration: none; color: inherit;">
                                Chemical Anchor
                            </a>
                        </h5>
                        <p class="fx-para-1 has-opacity-7" style="margin-bottom: 0;">
                            High-strength bonding for reliable fixings in concrete and masonry.
                        </p>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <div class="fx-team-1-slider-item" style="height: 100%;">
                        <div class="mb-3" style="font-size: 26px; line-height: 1; color: #ffffff;">
                            <i class="fa-solid fa-flask"></i>
                        </div>
                        <h5 class="person-name fx-heading-2 fx-font-500">
                            <a href="<?php echo SITE_URL; ?>/icfs/chemical-mortars" style="text-decoration: none; color: inherit;">
                                Chemical Mortars
                            </a>
                        </h5>
                        <p class="fx-para-1 has-opacity-7" style="margin-bottom: 0;">
                            Durable, gap-filling bonding and grouting for long-term performance.
                        </p>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <div class="fx-team-1-slider-item" style="height: 100%;">
                        <div class="mb-3" style="font-size: 26px; line-height: 1; color: #ffffff;">
                            <i class="fa-solid fa-gears"></i>
                        </div>
                        <h5 class="person-name fx-heading-2 fx-font-500">
                            <a href="<?php echo SITE_URL; ?>/icfs/mechanical-anchor" style="text-decoration: none; color: inherit;">
                                Mechanical Anchor
                            </a>
                        </h5>
                        <p class="fx-para-1 has-opacity-7" style="margin-bottom: 0;">
                            Quick installation with secure holding strength for heavy-duty applications.
                        </p>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <div class="fx-team-1-slider-item" style="height: 100%;">
                        <div class="mb-3" style="font-size: 26px; line-height: 1; color: #ffffff;">
                            <i class="fa-solid fa-plug"></i>
                        </div>
                        <h5 class="person-name fx-heading-2 fx-font-500">
                            <a href="<?php echo SITE_URL; ?>/icfs/nylog-plug" style="text-decoration: none; color: inherit;">
                                Nylon Plug
                            </a>
                        </h5>
                        <p class="fx-para-1 has-opacity-7" style="margin-bottom: 0;">
                            Corrosion-resistant fixing base for stable performance over time.
                        </p>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <div class="fx-team-1-slider-item" style="height: 100%;">
                        <div class="mb-3" style="font-size: 26px; line-height: 1; color: #ffffff;">
                            <i class="fa-solid fa-cube"></i>
                        </div>
                        <h5 class="person-name fx-heading-2 fx-font-500">
                            <a href="<?php echo SITE_URL; ?>/icfs/pu-foam" style="text-decoration: none; color: inherit;">
                                PU Foam
                            </a>
                        </h5>
                        <p class="fx-para-1 has-opacity-7" style="margin-bottom: 0;">
                            Sealing and gap-filling support for insulation, openings, and installations.
                        </p>
                    </div>
                </div>
            </div>

            <div class="text-center mt-50">
                <a href="<?php echo SITE_URL; ?>/icfs" aria-label="Explore ICFS" class="fx-pr-btn-1">
                    <span class="text" data-back="Explore ICFS Range" data-front="Explore ICFS Range"></span>
                    <i class="fa-solid fa-angle-right"></i>
                </a>
            </div>
        </div>
    </div>
    <!-- ICFS Highlight End -->

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
                <h2 class="fx-scn-title-3 txaa-split-text-3 txaa-split-text-3-ani has-clr-white">Explore ICFS and Our Range of
                    Industrial Tools, Abrasives & Fastening Solutions
                </h2>
            </div>


            <ul class="fx-process-1-tabs-btn p-relative mb-80" role="tablist">

                <li class="fx-process-1-tabs-btn-line txaascale0 fx-cube-1"></li>

                <?php
                // Make ICFS (IndoSpark) the first visible tab on the homepage.
                $homeBrands = $brands;
                $icfsBrands = [];
                $otherBrands = [];
                foreach ($homeBrands as $b) {
                    $slug = isset($b['slug']) ? strtolower((string) $b['slug']) : '';
                    $name = isset($b['name']) ? (string) $b['name'] : '';
                    if ($slug === 'icfs' || stripos($name, 'ICFS') !== false) {
                        $icfsBrands[] = $b;
                    } else {
                        $otherBrands[] = $b;
                    }
                }
                $homeBrands = array_merge($icfsBrands, $otherBrands);
                ?>

                <?php if (!empty($homeBrands)): ?>
                    <?php $firstBrand = true; ?>
                    <?php foreach ($homeBrands as $brand): ?>
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

                <?php if (!empty($homeBrands)): ?>
                    <?php $firstBrand = true; ?>
                    <?php foreach ($homeBrands as $brand): ?>
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
    <div class="fx-youtube-video-area pt-120 pb-120">
        <div class="container">
            <div class="fx-youtube-video-wrap">
                <iframe width="100%" height="535px" src="https://www.youtube.com/embed/BmEwjzdobek" title="ICFS Chemical Anchor Stud Manufacturing Process" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
            </div>
        </div>
    </div>


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