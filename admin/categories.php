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

$currentPage = 'categories';
$pageTitle = 'Categories Management';

$db = Database::getInstance()->getConnection();
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

// Get all brands for dropdown
$brands = $db->query("SELECT * FROM brands WHERE status = 'active' ORDER BY name ASC")->fetchAll();

// Handle form submissions (BEFORE including header to allow redirects)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $brand_id = intval($_POST['brand_id'] ?? 0);
    $name = sanitize($_POST['name'] ?? '');
    $slug = sanitize($_POST['slug'] ?? '');
    $description = $_POST['description'] ?? '';
    $status = $_POST['status'] ?? 'active';
    $sort_order = intval($_POST['sort_order'] ?? 0);
    
    if (empty($name) || empty($slug) || !$brand_id) {
        $error = 'Brand, name and slug are required';
    } else {
        $slug = generateSlug($slug);
        
        // Handle image upload
        $image = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $upload = uploadImage($_FILES['image'], 'categories');
            if ($upload['success']) {
                $image = $upload['path'];
            }
        }
        
        if ($id) {
            // Update existing
            $existing = $db->prepare("SELECT image FROM product_categories WHERE id = ?");
            $existing->execute([$id]);
            $oldData = $existing->fetch();
            
            if (empty($image) && $oldData) $image = $oldData['image'];
            
            $sql = "UPDATE product_categories SET brand_id = ?, name = ?, slug = ?, description = ?, image = ?, status = ?, sort_order = ? WHERE id = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$brand_id, $name, $slug, $description, $image, $status, $sort_order, $id]);
            
            redirect(SITE_URL . '/admin/categories.php', 'Category updated successfully');
        } else {
            // Insert new
            $sql = "INSERT INTO product_categories (brand_id, name, slug, description, image, status, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $db->prepare($sql);
            $stmt->execute([$brand_id, $name, $slug, $description, $image, $status, $sort_order]);
            $newId = $db->lastInsertId();
            
            redirect(SITE_URL . '/admin/categories.php?action=edit&id=' . $newId, 'Category added successfully');
        }
    }
}

// Handle delete
if (isset($_GET['delete']) && $id) {
    $category = $db->prepare("SELECT image FROM product_categories WHERE id = ?");
    $category->execute([$id]);
    $categoryData = $category->fetch();
    
    if ($categoryData && $categoryData['image']) {
        deleteImage($categoryData['image']);
    }
    
    $db->prepare("DELETE FROM product_categories WHERE id = ?")->execute([$id]);
    redirect(SITE_URL . '/admin/categories.php', 'Category deleted successfully');
}

// Handle category image delete BEFORE header (so redirects work)
if (isset($_GET['delete_image']) && $id) {
    $category = $db->prepare("SELECT image FROM product_categories WHERE id = ?");
    $category->execute([$id]);
    $categoryData = $category->fetch();
    
    if ($categoryData && !empty($categoryData['image'])) {
        deleteImage($categoryData['image']);
        $db->prepare("UPDATE product_categories SET image = '' WHERE id = ?")->execute([$id]);
        redirect(SITE_URL . '/admin/categories.php?action=edit&id=' . $id, 'Category image deleted');
    }
    redirect(SITE_URL . '/admin/categories.php?action=edit&id=' . $id, 'Image not found', 'error');
}

// Handle SEO form submission BEFORE header (so redirects work)
if ($action === 'seo' && $id && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $category = $db->prepare("SELECT * FROM product_categories WHERE id = ?");
    $category->execute([$id]);
    $category = $category->fetch();
    
    if (!$category) {
        redirect(SITE_URL . '/admin/categories.php', 'Category not found', 'error');
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
    
    saveSEOData($db, 'category', $id, $seoData, $cityId);
    redirect(SITE_URL . '/admin/categories.php?action=seo&id=' . $id, 'SEO data saved successfully');
}

// Now include header after all redirects are handled
require_once __DIR__ . '/includes/header.php';

// Get category for editing
$category = null;
if ($id && $action === 'edit') {
    $stmt = $db->prepare("SELECT * FROM product_categories WHERE id = ?");
    $stmt->execute([$id]);
    $category = $stmt->fetch();
}

// List all categories
if ($action === 'list' || empty($action)) {
    $categories = $db->query("SELECT c.*, b.name as brand_name FROM product_categories c 
                              LEFT JOIN brands b ON c.brand_id = b.id 
                              ORDER BY b.name ASC, c.sort_order ASC, c.name ASC")->fetchAll();
    ?>
    <div class="content-card">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="mb-0">All Categories</h5>
            <a href="?action=add" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Add New Category
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
                        <th>Brand</th>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Slug</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($categories)): ?>
                        <tr><td colspan="7" class="text-center text-muted">No categories found</td></tr>
                    <?php else: ?>
                        <?php foreach ($categories as $c): ?>
                        <tr>
                            <td><?php echo $c['id']; ?></td>
                            <td><?php echo htmlspecialchars($c['brand_name']); ?></td>
                            <td>
                                <?php if ($c['image']): ?>
                                    <img src="<?php echo UPLOAD_URL . '/' . $c['image']; ?>" alt="" style="width: 50px; height: 50px; object-fit: cover;">
                                <?php else: ?>
                                    <span class="text-muted">No image</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($c['name']); ?></td>
                            <td><code><?php echo htmlspecialchars($c['slug']); ?></code></td>
                            <td>
                                <span class="badge bg-<?php echo $c['status'] == 'active' ? 'success' : 'secondary'; ?>">
                                    <?php echo ucfirst($c['status']); ?>
                                </span>
                            </td>
                            <td>
                                <a href="?action=edit&id=<?php echo $c['id']; ?>" class="btn btn-sm btn-primary">
                                    <i class="bi bi-pencil"></i> Edit
                                </a>
                                <a href="?action=seo&id=<?php echo $c['id']; ?>" class="btn btn-sm btn-info">
                                    <i class="bi bi-search"></i> SEO
                                </a>
                                <a href="?delete=1&id=<?php echo $c['id']; ?>" class="btn btn-sm btn-danger" data-confirm="Are you sure?">
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
        <h5 class="mb-4"><?php echo $action === 'add' ? 'Add New Category' : 'Edit Category'; ?></h5>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-8">
                    <div class="mb-3">
                        <label class="form-label">Brand <span class="text-danger">*</span></label>
                        <select class="form-select" name="brand_id" required>
                            <option value="">Select Brand</option>
                            <?php foreach ($brands as $brand): ?>
                                <option value="<?php echo $brand['id']; ?>" <?php echo (($category['brand_id'] ?? '') == $brand['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($brand['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Category Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($category['name'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Slug <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="slug" value="<?php echo htmlspecialchars($category['slug'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="5"><?php echo htmlspecialchars($category['description'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="status">
                                    <option value="active" <?php echo (($category['status'] ?? 'active') == 'active') ? 'selected' : ''; ?>>Active</option>
                                    <option value="inactive" <?php echo (($category['status'] ?? '') == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Sort Order</label>
                                <input type="number" class="form-control" name="sort_order" value="<?php echo $category['sort_order'] ?? 0; ?>">
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Category Image</label>
                        <input type="file" class="form-control" name="image" accept="image/*">
                        <?php if (!empty($category['image'])): ?>
                            <div class="position-relative d-inline-block mt-2">
                                <img src="<?php echo UPLOAD_URL . '/' . $category['image']; ?>" alt="" class="img-thumbnail" style="max-width: 200px;">
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
                </div>
            </div>
            
            <div class="mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Save Category
                </button>
                <a href="<?php echo SITE_URL; ?>/admin/categories.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
    <?php
} elseif ($action === 'seo' && $id) {
    // SEO Management (similar to brands)
    $category = $db->prepare("SELECT * FROM product_categories WHERE id = ?");
    $category->execute([$id]);
    $category = $category->fetch();
    
    if (!$category) {
        redirect(SITE_URL . '/admin/categories.php', 'Category not found', 'error');
    }
    
    $cities = $db->query("SELECT * FROM cities WHERE status = 'active' ORDER BY name ASC")->fetchAll();
    
    $selectedCityId = $_GET['city_id'] ?? null;
    $currentSEO = getSEOData($db, 'category', $id, $selectedCityId);
    ?>
    <div class="content-card">
        <h5 class="mb-4">SEO Management - <?php echo htmlspecialchars($category['name']); ?></h5>
        
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
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Meta Description</label>
                        <textarea class="form-control" name="meta_description" rows="3"><?php echo htmlspecialchars($currentSEO['meta_description']); ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Meta Keywords</label>
                        <input type="text" class="form-control" name="meta_keywords" value="<?php echo htmlspecialchars($currentSEO['meta_keywords']); ?>">
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
                <a href="<?php echo SITE_URL; ?>/admin/categories.php" class="btn btn-secondary">Back to Categories</a>
            </div>
        </form>
    </div>
    <?php
}

require_once __DIR__ . '/includes/footer.php';
?>

