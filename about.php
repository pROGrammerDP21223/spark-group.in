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

<div class="offcanvas-overlay"></div>

<?php
// Render breadcrumb
renderBreadcrumb($pageSEO['h1_text'], [
    ['text' => $pageSEO['h1_text']]
]);
?>

<div class="container mt-4">
    <div class="row mt-4">
        <div class="col-md-12">
            <?php if ($pageContent && $pageContent['content']): ?>
                <div class="content">
                    <?php echo $pageContent['content']; // Output HTML as-is from rich text editor ?>
                </div>
            <?php else: ?>
                <p>Content coming soon...</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/public/footer.php'; ?>

