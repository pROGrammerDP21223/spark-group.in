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

<div class="offcanvas-overlay"></div>

<?php
// Render breadcrumb
renderBreadcrumb($pageSEO['h1_text'], [
    ['text' => $pageSEO['h1_text']]
]);
?>

<div class="container mt-4">
    <div class="row mt-4">
        <?php if (empty($certifications)): ?>
            <div class="col-12">
                <p class="text-muted">No certifications available.</p>
            </div>
        <?php else: ?>
            <?php foreach ($certifications as $cert): ?>
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <?php if ($cert['image']): ?>
                            <img src="<?php echo UPLOAD_URL . '/' . $cert['image']; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($cert['title']); ?>">
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($cert['title']); ?></h5>
                            <?php if ($cert['description']): ?>
                                <p class="card-text"><?php echo htmlspecialchars($cert['description']); ?></p>
                            <?php endif; ?>
                            <?php if ($cert['certificate_number']): ?>
                                <p class="text-muted small"><strong>Certificate #:</strong> <?php echo htmlspecialchars($cert['certificate_number']); ?></p>
                            <?php endif; ?>
                            <?php if ($cert['issued_date']): ?>
                                <p class="text-muted small"><strong>Issued:</strong> <?php echo formatDate($cert['issued_date']); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . '/includes/public/footer.php'; ?>

