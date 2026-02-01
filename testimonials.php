<?php
/**
 * Testimonials Page
 */

$testimonials = $db->query("SELECT * FROM testimonials WHERE status = 'active' ORDER BY sort_order ASC, created_at DESC")->fetchAll();

// Get page SEO (entity_id = 4 for testimonials page)
$pageSEO = getSEOData($db, 'page', 4, null);
if (empty($pageSEO['meta_title'])) {
    $pageSEO['meta_title'] = 'Testimonials - ' . SITE_NAME;
}
if (empty($pageSEO['meta_description'])) {
    $pageSEO['meta_description'] = 'Read what our customers say about us';
}
if (empty($pageSEO['h1_text'])) {
    $pageSEO['h1_text'] = 'Customer Testimonials';
}
if (empty($pageSEO['canonical_url'])) {
    $pageSEO['canonical_url'] = SITE_URL . '/testimonials';
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
        <?php if (empty($testimonials)): ?>
            <div class="col-12">
                <p class="text-muted">No testimonials available.</p>
            </div>
        <?php else: ?>
            <?php foreach ($testimonials as $testimonial): ?>
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="mb-3">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="bi bi-star<?php echo $i <= $testimonial['rating'] ? '-fill' : ''; ?>" style="color: #ffc107;"></i>
                                <?php endfor; ?>
                            </div>
                            <p class="card-text"><?php echo nl2br(htmlspecialchars($testimonial['testimonial_text'])); ?></p>
                            <div class="d-flex align-items-center mt-3">
                                <?php if ($testimonial['image']): ?>
                                    <img src="<?php echo UPLOAD_URL . '/' . $testimonial['image']; ?>" alt="" class="rounded-circle me-3" style="width: 50px; height: 50px; object-fit: cover;">
                                <?php endif; ?>
                                <div>
                                    <strong><?php echo htmlspecialchars($testimonial['customer_name']); ?></strong>
                                    <?php if ($testimonial['customer_designation']): ?>
                                        <br><small class="text-muted"><?php echo htmlspecialchars($testimonial['customer_designation']); ?></small>
                                    <?php endif; ?>
                                    <?php if ($testimonial['company_name']): ?>
                                        <br><small class="text-muted"><?php echo htmlspecialchars($testimonial['company_name']); ?></small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . '/includes/public/footer.php'; ?>

