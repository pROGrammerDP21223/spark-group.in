<?php
// Start output buffering first
if (!ob_get_level()) {
    ob_start();
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/includes/auth.php';
requireAdminLogin();

$currentPage = 'brands';
$pageTitle = 'Brands Management';

$db = Database::getInstance()->getConnection();
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

// Handle form submissions (BEFORE including header to allow redirects)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $slug = sanitize($_POST['slug'] ?? '');
    $description = $_POST['description'] ?? '';
    $status = $_POST['status'] ?? 'active';
    $sort_order = intval($_POST['sort_order'] ?? 0);
    
    if (empty($name) || empty($slug)) {
        $error = 'Name and slug are required';
    } else {
        $slug = generateSlug($slug);
        
        // Handle image upload
        $image = '';
        $logo = '';
        
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $upload = uploadImage($_FILES['image'], 'brands');
            if ($upload['success']) {
                $image = $upload['path'];
            }
        }
        
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $upload = uploadImage($_FILES['logo'], 'brands');
            if ($upload['success']) {
                $logo = $upload['path'];
            }
        }
        
        if ($id) {
            // Update existing - check if slug conflicts with another brand
            $checkSlug = $db->prepare("SELECT id FROM brands WHERE slug = ? AND id != ?");
            $checkSlug->execute([$slug, $id]);
            if ($checkSlug->fetch()) {
                // Slug exists for another brand, make it unique
                $counter = 1;
                $originalSlug = $slug;
                do {
                    $slug = $originalSlug . '-' . $counter;
                    $checkSlug = $db->prepare("SELECT id FROM brands WHERE slug = ? AND id != ?");
                    $checkSlug->execute([$slug, $id]);
                    $counter++;
                } while ($checkSlug->fetch() && $counter < 100);
            }
            
            $existing = $db->prepare("SELECT image, logo FROM brands WHERE id = ?");
            $existing->execute([$id]);
            $oldData = $existing->fetch();
            
            if (empty($image) && $oldData) $image = $oldData['image'];
            if (empty($logo) && $oldData) $logo = $oldData['logo'];
            
            $sql = "UPDATE brands SET name = ?, slug = ?, description = ?, image = ?, logo = ?, status = ?, sort_order = ? WHERE id = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$name, $slug, $description, $image, $logo, $status, $sort_order, $id]);
            
            redirect(SITE_URL . '/admin/brands.php', 'Brand updated successfully');
        } else {
            // Insert new - check if slug already exists and make it unique
            $checkSlug = $db->prepare("SELECT id FROM brands WHERE slug = ?");
            $checkSlug->execute([$slug]);
            if ($checkSlug->fetch()) {
                // Slug exists, make it unique by appending number
                $counter = 1;
                $originalSlug = $slug;
                do {
                    $slug = $originalSlug . '-' . $counter;
                    $checkSlug = $db->prepare("SELECT id FROM brands WHERE slug = ?");
                    $checkSlug->execute([$slug]);
                    $counter++;
                } while ($checkSlug->fetch() && $counter < 100);
            }
            
            $sql = "INSERT INTO brands (name, slug, description, image, logo, status, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $db->prepare($sql);
            $stmt->execute([$name, $slug, $description, $image, $logo, $status, $sort_order]);
            $newId = $db->lastInsertId();
            
            redirect(SITE_URL . '/admin/brands.php?action=edit&id=' . $newId, 'Brand added successfully');
        }
    }
}

// Handle delete
if (isset($_GET['delete']) && $id) {
    $brand = $db->prepare("SELECT image, logo FROM brands WHERE id = ?");
    $brand->execute([$id]);
    $brandData = $brand->fetch();
    
    if ($brandData) {
        if ($brandData['image']) deleteImage($brandData['image']);
        if ($brandData['logo']) deleteImage($brandData['logo']);
        
        $db->prepare("DELETE FROM brands WHERE id = ?")->execute([$id]);
        redirect(SITE_URL . '/admin/brands.php', 'Brand deleted successfully');
    }
}

// Handle brand image/logo delete BEFORE header (so redirects work)
if (isset($_GET['delete_image']) && $id) {
    $brand = $db->prepare("SELECT image FROM brands WHERE id = ?");
    $brand->execute([$id]);
    $brandData = $brand->fetch();
    
    if ($brandData && !empty($brandData['image'])) {
        deleteImage($brandData['image']);
        $db->prepare("UPDATE brands SET image = '' WHERE id = ?")->execute([$id]);
        redirect(SITE_URL . '/admin/brands.php?action=edit&id=' . $id, 'Brand image deleted');
    }
    redirect(SITE_URL . '/admin/brands.php?action=edit&id=' . $id, 'Image not found', 'error');
}

if (isset($_GET['delete_logo']) && $id) {
    $brand = $db->prepare("SELECT logo FROM brands WHERE id = ?");
    $brand->execute([$id]);
    $brandData = $brand->fetch();
    
    if ($brandData && !empty($brandData['logo'])) {
        deleteImage($brandData['logo']);
        $db->prepare("UPDATE brands SET logo = '' WHERE id = ?")->execute([$id]);
        redirect(SITE_URL . '/admin/brands.php?action=edit&id=' . $id, 'Brand logo deleted');
    }
    redirect(SITE_URL . '/admin/brands.php?action=edit&id=' . $id, 'Logo not found', 'error');
}

// Handle SEO form submission BEFORE header (so redirects work)
if ($action === 'seo' && $id && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $brand = $db->prepare("SELECT * FROM brands WHERE id = ?");
    $brand->execute([$id]);
    $brand = $brand->fetch();
    
    if (!$brand) {
        redirect(SITE_URL . '/admin/brands.php', 'Brand not found', 'error');
    }
    
    $cityId = !empty($_POST['city_id']) ? intval($_POST['city_id']) : null;
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
        'seo_head' => $_POST['seo_head'] ?? ''
    ];
    
    saveSEOData($db, 'brand', $id, $seoData, $cityId);
    redirect(SITE_URL . '/admin/brands.php?action=seo&id=' . $id, 'SEO data saved successfully');
}

// Now include header after all redirects are handled
require_once __DIR__ . '/includes/header.php';

// Get brand for editing
$brand = null;
if ($id && $action === 'edit') {
    $stmt = $db->prepare("SELECT * FROM brands WHERE id = ?");
    $stmt->execute([$id]);
    $brand = $stmt->fetch();
}

// List all brands
if ($action === 'list' || empty($action)) {
    $brands = $db->query("SELECT * FROM brands ORDER BY sort_order ASC, name ASC")->fetchAll();
    ?>
    <div class="content-card">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="mb-0">All Brands</h5>
            <a href="?action=add" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Add New Brand
            </a>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Slug</th>
                        <th>Status</th>
                        <th>Sort Order</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($brands)): ?>
                        <tr><td colspan="7" class="text-center text-muted">No brands found</td></tr>
                    <?php else: ?>
                        <?php foreach ($brands as $b): ?>
                        <tr>
                            <td><?php echo $b['id']; ?></td>
                            <td>
                                <?php if ($b['image']): ?>
                                    <img src="<?php echo UPLOAD_URL . '/' . $b['image']; ?>" alt="" style="width: 50px; height: 50px; object-fit: cover;">
                                <?php else: ?>
                                    <span class="text-muted">No image</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($b['name']); ?></td>
                            <td><code><?php echo htmlspecialchars($b['slug']); ?></code></td>
                            <td>
                                <span class="badge bg-<?php echo $b['status'] == 'active' ? 'success' : 'secondary'; ?>">
                                    <?php echo ucfirst($b['status']); ?>
                                </span>
                            </td>
                            <td><?php echo $b['sort_order']; ?></td>
                            <td>
                                <a href="?action=edit&id=<?php echo $b['id']; ?>" class="btn btn-sm btn-primary">
                                    <i class="bi bi-pencil"></i> Edit
                                </a>
                                <a href="?action=seo&id=<?php echo $b['id']; ?>" class="btn btn-sm btn-info">
                                    <i class="bi bi-search"></i> SEO
                                </a>
                                <a href="?delete=1&id=<?php echo $b['id']; ?>" class="btn btn-sm btn-danger" data-confirm="Are you sure you want to delete this brand?">
                                    <i class="bi bi-trash"></i> Delete
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
} elseif ($action === 'add' || $action === 'edit') {
    ?>
    <div class="content-card">
        <h5 class="mb-4"><?php echo $action === 'add' ? 'Add New Brand' : 'Edit Brand'; ?></h5>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-8">
                    <div class="mb-3">
                        <label class="form-label">Brand Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($brand['name'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Slug <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="slug" value="<?php echo htmlspecialchars($brand['slug'] ?? ''); ?>" required>
                        <small class="text-muted">URL-friendly identifier (e.g., bosch, makita)</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="5"><?php echo htmlspecialchars($brand['description'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="status">
                                    <option value="active" <?php echo (($brand['status'] ?? 'active') == 'active') ? 'selected' : ''; ?>>Active</option>
                                    <option value="inactive" <?php echo (($brand['status'] ?? '') == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Sort Order</label>
                                <input type="number" class="form-control" name="sort_order" value="<?php echo $brand['sort_order'] ?? 0; ?>">
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Brand Image</label>
                        <input type="file" class="form-control" name="image" accept="image/*">
                        <?php if (!empty($brand['image'])): ?>
                            <div class="position-relative d-inline-block mt-2">
                                <img src="<?php echo UPLOAD_URL . '/' . $brand['image']; ?>" alt="" class="img-thumbnail" style="max-width: 200px;">
                                <a href="?action=edit&id=<?php echo $id; ?>&delete_image=1" 
                                   class="btn btn-sm btn-danger position-absolute top-0 end-0" 
                                   style="transform: translate(50%, -50%);"
                                   data-confirm="Delete this image?"
                                   title="Delete Image">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Brand Logo</label>
                        <input type="file" class="form-control" name="logo" accept="image/*">
                        <?php if (!empty($brand['logo'])): ?>
                            <div class="position-relative d-inline-block mt-2">
                                <img src="<?php echo UPLOAD_URL . '/' . $brand['logo']; ?>" alt="" class="img-thumbnail" style="max-width: 200px;">
                                <a href="?action=edit&id=<?php echo $id; ?>&delete_logo=1" 
                                   class="btn btn-sm btn-danger position-absolute top-0 end-0" 
                                   style="transform: translate(50%, -50%);"
                                   data-confirm="Delete this logo?"
                                   title="Delete Logo">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Save Brand
                </button>
                <a href="<?php echo SITE_URL; ?>/admin/brands.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
    <?php
} elseif ($action === 'seo' && $id) {
    // SEO Management for Brand
    $brand = $db->prepare("SELECT * FROM brands WHERE id = ?");
    $brand->execute([$id]);
    $brand = $brand->fetch();
    
    if (!$brand) {
        redirect(SITE_URL . '/admin/brands.php', 'Brand not found', 'error');
    }
    
    // Get all cities
    $cities = $db->query("SELECT * FROM cities WHERE status = 'active' ORDER BY name ASC")->fetchAll();
    
    // Get current SEO (base or city-specific)
    $selectedCityId = $_GET['city_id'] ?? null;
    $currentSEO = getSEOData($db, 'brand', $id, $selectedCityId);
    ?>
    <div class="content-card">
        <h5 class="mb-4">SEO Management - <?php echo htmlspecialchars($brand['name']); ?></h5>
        
        <div class="mb-4">
            <label class="form-label">Select City (or Base Page)</label>
            <select class="form-select" id="citySelector" onchange="window.location.href='?action=seo&id=<?php echo $id; ?>&city_id=' + this.value">
                <option value="">Base Page (No City)</option>
                <?php foreach ($cities as $city): ?>
                    <option value="<?php echo $city['id']; ?>" <?php echo ($selectedCityId == $city['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($city['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <form method="POST">
            <input type="hidden" name="city_id" value="<?php echo $selectedCityId ?? ''; ?>">
            
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
                        <small class="text-muted">This code will be added in the &lt;head&gt; section. Useful for Google Analytics, schema markup, etc.</small>
                    </div>
                </div>
            </div>
            
            <div class="mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Save SEO Data
                </button>
                <a href="<?php echo SITE_URL; ?>/admin/brands.php" class="btn btn-secondary">Back to Brands</a>
            </div>
        </form>
    </div>
    <?php
}

require_once __DIR__ . '/includes/footer.php';
?>

