<?php
// Start output buffering first (match other admin pages like brands.php)
if (!ob_get_level()) {
    ob_start();
}

// Require auth (which includes config, database, and functions) before header
require_once __DIR__ . '/includes/auth.php';
requireAdminLogin();

$db = Database::getInstance()->getConnection();

// Define static pages
$staticPages = [
    'home' => ['name' => 'Home Page', 'entity_id' => 0],
    'about' => ['name' => 'About Us', 'entity_id' => 1],
    'contact' => ['name' => 'Contact Us', 'entity_id' => 2],
    'enquiry' => ['name' => 'Enquiry Form', 'entity_id' => 3],
    'testimonials' => ['name' => 'Testimonials', 'entity_id' => 4],
    'certifications' => ['name' => 'Certifications', 'entity_id' => 5]
];

$selectedPage = $_GET['page'] ?? 'home';
$pageInfo = $staticPages[$selectedPage] ?? $staticPages['home'];
$entityId = $pageInfo['entity_id'];

// Handle SEO save BEFORE header (so redirects work)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $seoData = [
        'meta_title' => sanitize($_POST['meta_title'] ?? ''),
        'meta_description' => sanitize($_POST['meta_description'] ?? ''),
        'meta_keywords' => sanitize($_POST['meta_keywords'] ?? ''),
        'canonical_url' => sanitize($_POST['canonical_url'] ?? ''),
        'og_title' => sanitize($_POST['og_title'] ?? ''),
        'og_description' => sanitize($_POST['og_description'] ?? ''),
        'og_image' => sanitize($_POST['og_image'] ?? ''),
        'h1_text' => sanitize($_POST['h1_text'] ?? ''),
        'h2_text' => sanitize($_POST['h2_text'] ?? ''),
        'seo_head' => $_POST['seo_head'] ?? '' // Don't sanitize HTML/JS code
    ];
    
    saveSEOData($db, 'page', $entityId, $seoData, null);
    redirect(SITE_URL . '/admin/page_seo.php?page=' . $selectedPage, 'SEO data saved successfully');
}

// Now include header after all redirects are handled
$currentPage = 'page_seo';
$pageTitle = 'Page SEO Management';
require_once __DIR__ . '/includes/header.php';

// Get current SEO
$currentSEO = getSEOData($db, 'page', $entityId, null);
?>
<div class="content-card">
    <h5 class="mb-4">Page SEO Management</h5>
    
    <div class="mb-4">
        <label class="form-label">Select Page</label>
        <select class="form-select" id="pageSelector" onchange="window.location.href='?page=' + this.value">
            <?php foreach ($staticPages as $key => $info): ?>
                <option value="<?php echo $key; ?>" <?php echo ($selectedPage == $key) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($info['name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    
    <div class="alert alert-info">
        <strong>Managing SEO for:</strong> <?php echo htmlspecialchars($pageInfo['name']); ?>
    </div>
    
    <form method="POST">
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Meta Title</label>
                    <input type="text" class="form-control" name="meta_title" value="<?php echo htmlspecialchars($currentSEO['meta_title']); ?>" maxlength="255">
                    <small class="text-muted">Recommended: 50-60 characters</small>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Meta Description</label>
                    <textarea class="form-control" name="meta_description" rows="3"><?php echo htmlspecialchars($currentSEO['meta_description']); ?></textarea>
                    <small class="text-muted">Recommended: 150-160 characters</small>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Meta Keywords</label>
                    <input type="text" class="form-control" name="meta_keywords" value="<?php echo htmlspecialchars($currentSEO['meta_keywords']); ?>">
                    <small class="text-muted">Comma-separated keywords</small>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Canonical URL</label>
                    <input type="url" class="form-control" name="canonical_url" value="<?php echo htmlspecialchars($currentSEO['canonical_url']); ?>">
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">OG Title</label>
                    <input type="text" class="form-control" name="og_title" value="<?php echo htmlspecialchars($currentSEO['og_title']); ?>">
                </div>
                
                <div class="mb-3">
                    <label class="form-label">OG Description</label>
                    <textarea class="form-control" name="og_description" rows="3"><?php echo htmlspecialchars($currentSEO['og_description']); ?></textarea>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">OG Image URL</label>
                    <input type="url" class="form-control" name="og_image" value="<?php echo htmlspecialchars($currentSEO['og_image']); ?>">
                </div>
                
                <div class="mb-3">
                    <label class="form-label">H1 Text</label>
                    <input type="text" class="form-control" name="h1_text" value="<?php echo htmlspecialchars($currentSEO['h1_text']); ?>">
                </div>
                
                <div class="mb-3">
                    <label class="form-label">H2 Text</label>
                    <input type="text" class="form-control" name="h2_text" value="<?php echo htmlspecialchars($currentSEO['h2_text']); ?>">
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Custom SEO Head Code</label>
                    <textarea class="form-control" name="seo_head" rows="5" placeholder="Add custom code like Google Analytics, schema markup, etc."><?php echo htmlspecialchars($currentSEO['seo_head'] ?? ''); ?></textarea>
                    <small class="text-muted">This code will be added in the &lt;head&gt; section.</small>
                </div>
            </div>
        </div>
        
        <div class="mt-4">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save"></i> Save SEO Data
            </button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

