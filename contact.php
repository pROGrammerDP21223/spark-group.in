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
// Render breadcrumb
renderBreadcrumb($pageSEO['h1_text'], [
    ['text' => $pageSEO['h1_text']]
]);
?>

<!-- START SECTION CONTACT -->
<div class="section pb_70">
	<div class="container">
        <div class="row">
            <div class="col-xl-4 col-md-6">
            	<div class="contact_wrap contact_style3">
                    <div class="contact_icon">
                        <i class="linearicons-map2"></i>
                    </div>
                    <div class="contact_text">
                        <span>Address</span>
                        <?php foreach ($contactMap['address'] as $address): ?>
                        <p><?php echo htmlspecialchars($address['value']); ?></p>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-6">
            	<div class="contact_wrap contact_style3">
                    <div class="contact_icon">
                        <i class="linearicons-envelope-open"></i>
                    </div>
                    <div class="contact_text">
                        <span>Email Address</span>
                        <?php foreach ($contactMap['email'] as $email): ?>
                        <a href="mailto:<?php echo htmlspecialchars($email['value']); ?>"><?php echo htmlspecialchars($email['value']); ?></a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-6">
            	<div class="contact_wrap contact_style3">
                    <div class="contact_icon">
                        <i class="linearicons-tablet2"></i>
                    </div>
                    <div class="contact_text">
                        <span>Phone</span>
                        <?php foreach ($contactMap['phone'] as $phone): ?>
                        <p><?php echo htmlspecialchars($phone['value']); ?></p>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<?php require __DIR__ . '/includes/public/footer.php'; ?>

