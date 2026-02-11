<?php
/**
 * Contact Us Page
 */

$contactDetails = $db->query("SELECT * FROM contact_details WHERE status = 'active' ORDER BY sort_order ASC")->fetchAll();
$contactMap = [];
foreach ($contactDetails as $contact) {
    $contactMap[$contact['type']][] = $contact;
}

// Get page SEO (entity_id = 2 for contact page)
$pageSEO = getSEOData($db, 'page', 2, null);
if (empty($pageSEO['meta_title'])) {
    $pageSEO['meta_title'] = 'Contact Us - ' . SITE_NAME;
}
if (empty($pageSEO['meta_description'])) {
    $pageSEO['meta_description'] = 'Get in touch with us for any inquiries';
}
if (empty($pageSEO['h1_text'])) {
    $pageSEO['h1_text'] = 'Contact Us';
}
if (empty($pageSEO['canonical_url'])) {
    $pageSEO['canonical_url'] = SITE_URL . '/contact-us';
}
// Ensure seo_head is included
if (!isset($pageSEO['seo_head'])) {
    $pageSEO['seo_head'] = '';
}

require __DIR__ . '/includes/public/header.php';
require_once __DIR__ . '/includes/public/breadcrumb.php';
?>

<div class="offcanvas-overlay"></div>

<?php
// Get contact info for display
$addressText = '';
$emailText = '';
$phoneText = '';
$hoursText = '';

if (!empty($contactMap['address'])) {
    $addressText = htmlspecialchars($contactMap['address'][0]['value']);
}
if (!empty($contactMap['email'])) {
    $emailText = htmlspecialchars($contactMap['email'][0]['value']);
}
if (!empty($contactMap['phone'])) {
    $phoneText = htmlspecialchars($contactMap['phone'][0]['value']);
}

// Build breadcrumb title
$breadcrumbTitle = !empty($pageSEO['h1_text']) ? strtolower($pageSEO['h1_text']) : 'contact us';
$breadcrumbH1 = !empty($pageSEO['h1_text']) ? $pageSEO['h1_text'] : 'Discover Us Through Our Updated Contact Methods.';
$breadcrumbDesc = !empty($pageSEO['h2_text']) ? $pageSEO['h2_text'] : 'Stay connected with us through our newly updated contact methods. Experience faster, more efficient communication.';
?>

<div class="breadcrumb-area bg-default " data-background="assets/img/breadcrumb/b1-bg-1.png">
    <div class="container fx-container-1">
        <div class="breadcrumb-wrap">

            <!-- left-content -->
            <div class="breadcrumb-content">

                <div class="breadcrumb-list ">
                    <a href="<?php echo SITE_URL; ?>">Home</a>
                    <span><?php echo $breadcrumbTitle; ?></span>
                </div>

                <h1 class="breadcrumb-title fx-heading-1 text-uppercase " data-txaa-split-text-1><?php echo htmlspecialchars($breadcrumbH1); ?></h1>

                <p class="breadcrumb-disc fx-para-1 has-clr-white fix"><span class="d-inline-block breadcrumb-slideup"><?php echo htmlspecialchars($breadcrumbDesc); ?></span></p>

               

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

<!-- contact-us-start -->
<div class="fx-contact-us-1-area pb-70 pt-70">
    <div class="container fx-container-1">
        <div class="row align-items-center">
            <!-- left-side -->
            <div class="col-lg-6">
                <div class="fx-contact-us-1-left">
                    <!-- section-title -->
                    <div class="fx-blog-1-scn-title mb-35">
                        <h6 class="fx-subtitle-1 ">
                           
                            <span class="txaa-split-text-2 txaa-split-text-2-ani">Get in Touch</span>
                        </h6>
                        <h2 class="fx-scn-title-1 txaa-split-text-3 txaa-split-text-3-ani">Reach Out for Expert Industrial Service Solutions</h2>
                    </div>

                    <div class="fx-form-1">
                        <p class="fx-para-1 mb-20">
                            For detailed product or project enquiries, please use our dedicated enquiry form.
                            Our team will get back to you with tailored solutions for your requirements.
                        </p>

                        <div class="fx-form-1-box fix txxaslideup">
                            <span class="txxaslideup-item fx-cube-1">
                                <a href="<?php echo SITE_URL; ?>/enquiry" aria-label="Go to enquiry page" class="fx-pr-btn-1">
                                    <span class="text" data-back="go to enquiry page" data-front="go to enquiry page"></span>
                                    <i class="fa-solid fa-angle-right"></i>
                                </a>
                            </span>
                        </div>
                    </div>

                </div>
            </div>

            <!-- right-side -->
            <div class="col-lg-6">
                <div class="fx-contact-us-1-right">
                    <div class="fx-contact-us-1-img fix img-cover">
                        <!-- <img src="assets/img/contact/c7-img-1.png" alt=""> -->
                    </div>
                    <div class="fx-contact-us-1-content fix">
                        <?php if (!empty($addressText)): ?>
                        <div class="fx-contact-us-1-info-box fix txxaslideup">
                            <h6 class="box-title fx-heading-1 fx-font-400">Address:</h6>
                            <p class="address fx-para-1 has-clr-white">
                                <span class="txxaslideup-item fx-cube-1"><?php echo $addressText; ?></span>
                            </p>
                        </div>
                        <?php endif; ?>
                        <div class="fx-contact-us-1-info-box txxaslideup fix">
                            <h6 class="box-title fx-heading-1 fx-font-400">get in touch:</h6>
                            <ul class="info-list txxaslideup-item fx-cube-1">
                                <?php if (!empty($emailText)): ?>
                                <li>
                                    <a href="mailto:<?php echo $emailText; ?>" aria-label="name">
                                        <i class="fa-regular fa-envelope"></i>
                                        <?php echo $emailText; ?>
                                    </a>
                                </li>
                                <?php endif; ?>
                                <?php if (!empty($phoneText)): ?>
                                <li>
                                    <a href="tel:<?php echo preg_replace('/[^0-9+]/', '', $phoneText); ?>" aria-label="name">
                                        <i class="fa-light fa-phone-volume"></i>
                                        <?php echo $phoneText; ?>
                                    </a>
                                </li>
                                <?php endif; ?>
                                <?php if (!empty($hoursText)): ?>
                                <li>
                                    <i class="fa-regular fa-clock"></i>
                                    <?php echo $hoursText; ?>
                                </li>
                                <?php else: ?>
                               
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- contact-us-end -->


<?php require __DIR__ . '/includes/public/footer.php'; ?>

