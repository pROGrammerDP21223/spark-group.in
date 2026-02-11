<?php
/**
 * Certifications Page
 */

$certifications = $db->query("SELECT * FROM certifications WHERE status = 'active' ORDER BY sort_order ASC, issued_date DESC")->fetchAll();

// Get page SEO (entity_id = 5 for certifications page)
$pageSEO = getSEOData($db, 'page', 5, null);
if (empty($pageSEO['meta_title'])) {
    $pageSEO['meta_title'] = 'Certifications - ' . SITE_NAME;
}
if (empty($pageSEO['meta_description'])) {
    $pageSEO['meta_description'] = 'View our certifications and quality standards';
}
if (empty($pageSEO['h1_text'])) {
    $pageSEO['h1_text'] = 'Our Certifications';
}
if (empty($pageSEO['canonical_url'])) {
    $pageSEO['canonical_url'] = SITE_URL . '/certifications';
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

                <div class="breadcrumb-list ">
                    <a href="index.html">Home</a>
                    <span><?php echo htmlspecialchars($pageSEO['h1_text']); ?></span>
                </div>

                <h1 class="breadcrumb-title fx-heading-1 text-uppercase " data-txaa-split-text-1><?php echo htmlspecialchars($pageSEO['h1_text']); ?></h1>

             



            </div>

            <!-- right-img -->
            <div class="breadcrumb-img">
                <!-- <img src="assets/img/breadcrumb/b1-img-1.png" alt=""> -->
            </div>

        </div>
    </div>
</div>

<div id="has-jump"></div>
<!-- <div class="d-flex flex-column align-items-center justify-content-center mt-50">
<?php if (!empty($pageSEO['h2_text'])): ?>
    <p class="mb-4"><?php echo htmlspecialchars($pageSEO['h2_text']); ?></p>
<?php endif; ?>

<a href="<?php echo SITE_URL; ?>/enquiry" aria-label="name" class="fx-pr-btn-1">
    <span class="text" data-back="request a quote" data-front="request a quote"></span>
</a>
</div> -->
<!-- blog-start -->


<div class="fx-blog-page-area pt-50 pb-120">
    <div class="container fx-container-1">
        <div class="fx-blog-page-item mb-65">
        <?php if (empty($certifications)): ?>
                <p class="text-center">No certifications found.</p>
            <?php else: ?>  
                <?php foreach ($certifications as $cert): ?>
                <?php
                $certUrl = SITE_URL . '/' . $cert['slug'];
                ?>
                <!-- single-blog -->
                <a href="<?php echo $certUrl; ?>" aria-label="name"
                   class="fx-blog-1-item-single"
                   style="display: block; text-decoration: none; color: inherit;">
                    <div class="item-img fix img-cover p-relative mb-35">
                        <?php if ($cert['image']): ?>
                            <img src="<?php echo UPLOAD_URL . '/' . $cert['image']; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($cert['title']); ?>">
                            <?php else: ?>
                            <img src="assets/images/cert_img1.jpg" class="card-img-top" alt="<?php echo htmlspecialchars($cert['title']); ?>">
                        <?php endif; ?>
                    </div>
                    <h4 class="item-title fx-heading-1 fx-font-500">
                        <?php echo htmlspecialchars($cert['title']); ?>
                    </h4>
                </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
</div>      
<!-- blog-end -->
<?php require __DIR__ . '/includes/public/footer.php'; ?>
