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

$currentPage = 'products';
$pageTitle = 'Products Management';

$db = Database::getInstance()->getConnection();
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

// Get all brands
$brands = $db->query("SELECT * FROM brands WHERE status = 'active' ORDER BY name ASC")->fetchAll();

// Handle form submissions (BEFORE including header to allow redirects)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $brand_id = intval($_POST['brand_id'] ?? 0);
    $name = sanitize($_POST['name'] ?? '');
    $slug = sanitize($_POST['slug'] ?? '');
    $description = $_POST['description'] ?? '';
    $short_description = sanitize($_POST['short_description'] ?? '');
    $status = $_POST['status'] ?? 'active';
    $featured = isset($_POST['featured']) ? 1 : 0;
    $sort_order = intval($_POST['sort_order'] ?? 0);
    
    if (empty($name) || empty($slug) || !$brand_id) {
        $error = 'Brand, name and slug are required';
    } else {
        $slug = generateSlug($slug);
        
        // Handle image upload
        $image = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $upload = uploadImage($_FILES['image'], 'products');
            if ($upload['success']) {
                $image = $upload['path'];
            }
        }
        
        // Handle gallery (multiple images)
        $gallery = [];
        if (isset($_FILES['gallery']) && is_array($_FILES['gallery']['name'])) {
            foreach ($_FILES['gallery']['name'] as $key => $filename) {
                if ($_FILES['gallery']['error'][$key] === UPLOAD_ERR_OK) {
                    $file = [
                        'name' => $filename,
                        'type' => $_FILES['gallery']['type'][$key],
                        'tmp_name' => $_FILES['gallery']['tmp_name'][$key],
                        'error' => $_FILES['gallery']['error'][$key],
                        'size' => $_FILES['gallery']['size'][$key]
                    ];
                    $upload = uploadImage($file, 'products/gallery');
                    if ($upload['success']) {
                        $gallery[] = $upload['path'];
                    }
                }
            }
        }
        
        if ($id) {
            // Update existing
            $existing = $db->prepare("SELECT image, gallery FROM products WHERE id = ?");
            $existing->execute([$id]);
            $oldData = $existing->fetch();
            
            if (empty($image) && $oldData) $image = $oldData['image'];
            
            $oldGallery = !empty($oldData['gallery']) ? json_decode($oldData['gallery'], true) : [];
            if (!empty($gallery)) {
                $oldGallery = array_merge($oldGallery, $gallery);
            }
            
            $sql = "UPDATE products SET brand_id = ?, name = ?, slug = ?, description = ?, short_description = ?, image = ?, gallery = ?, status = ?, featured = ?, sort_order = ? WHERE id = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$brand_id, $name, $slug, $description, $short_description, $image, json_encode($oldGallery), $status, $featured, $sort_order, $id]);
            
            redirect(SITE_URL . '/admin/products.php?action=edit&id=' . $id, 'Product updated successfully');
        } else {
            // Insert new
            $sql = "INSERT INTO products (brand_id, name, slug, description, short_description, image, gallery, status, featured, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $db->prepare($sql);
            $stmt->execute([$brand_id, $name, $slug, $description, $short_description, $image, json_encode($gallery), $status, $featured, $sort_order]);
            $newId = $db->lastInsertId();
            
            redirect(SITE_URL . '/admin/products.php?action=edit&id=' . $newId, 'Product added successfully');
        }
    }
}

// Handle delete
if (isset($_GET['delete']) && $id) {
    $product = $db->prepare("SELECT image, gallery FROM products WHERE id = ?");
    $product->execute([$id]);
    $productData = $product->fetch();
    
    if ($productData) {
        if ($productData['image']) deleteImage($productData['image']);
        if ($productData['gallery']) {
            $gallery = json_decode($productData['gallery'], true);
            if (is_array($gallery)) {
                foreach ($gallery as $img) {
                    deleteImage($img);
                }
            }
        }
    }
    
    $db->prepare("DELETE FROM products WHERE id = ?")->execute([$id]);
    redirect(SITE_URL . '/admin/products.php', 'Product deleted successfully');
}

// Handle main image delete BEFORE header (so redirects work)
if (isset($_GET['delete_main_image']) && $id) {
    $product = $db->prepare("SELECT image FROM products WHERE id = ?");
    $product->execute([$id]);
    $productData = $product->fetch();
    
    if ($productData && !empty($productData['image'])) {
        // Delete the image file
        deleteImage($productData['image']);
        // Update database
        $db->prepare("UPDATE products SET image = '' WHERE id = ?")->execute([$id]);
        redirect(SITE_URL . '/admin/products.php?action=edit&id=' . $id, 'Main image deleted');
    }
    redirect(SITE_URL . '/admin/products.php?action=edit&id=' . $id, 'Image not found', 'error');
}

// Handle gallery image delete BEFORE header (so redirects work)
if (isset($_GET['delete_gallery_image']) && $id && isset($_GET['image_index'])) {
    $product = $db->prepare("SELECT gallery FROM products WHERE id = ?");
    $product->execute([$id]);
    $productData = $product->fetch();
    
    if ($productData && !empty($productData['gallery'])) {
        $gallery = json_decode($productData['gallery'], true);
        if (is_array($gallery)) {
            $imageIndex = intval($_GET['image_index']);
            if (isset($gallery[$imageIndex])) {
                // Delete the image file
                deleteImage($gallery[$imageIndex]);
                // Remove from array
                unset($gallery[$imageIndex]);
                // Re-index array
                $gallery = array_values($gallery);
                // Update database
                $db->prepare("UPDATE products SET gallery = ? WHERE id = ?")->execute([json_encode($gallery), $id]);
                redirect(SITE_URL . '/admin/products.php?action=edit&id=' . $id, 'Gallery image deleted');
            }
        }
    }
    redirect(SITE_URL . '/admin/products.php?action=edit&id=' . $id, 'Image not found', 'error');
}

// Handle specs form submissions BEFORE header (so redirects work)
if ($action === 'specs' && $id) {
    $product = $db->prepare("SELECT * FROM products WHERE id = ?");
    $product->execute([$id]);
    $product = $product->fetch();
    
    if (!$product) {
        redirect(SITE_URL . '/admin/products.php', 'Product not found', 'error');
    }
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['add_spec'])) {
            $spec_name = sanitize($_POST['spec_name'] ?? '');
            $spec_value = sanitize($_POST['spec_value'] ?? '');
            $sort_order = intval($_POST['spec_sort_order'] ?? 0);
            
            if (!empty($spec_name) && !empty($spec_value)) {
                $sql = "INSERT INTO product_specifications (product_id, spec_name, spec_value, sort_order) VALUES (?, ?, ?, ?)";
                $stmt = $db->prepare($sql);
                $stmt->execute([$id, $spec_name, $spec_value, $sort_order]);
                redirect(SITE_URL . '/admin/products.php?action=specs&id=' . $id, 'Specification added');
            }
        } elseif (isset($_POST['update_spec'])) {
            $spec_id = intval($_POST['spec_id'] ?? 0);
            $spec_name = sanitize($_POST['spec_name'] ?? '');
            $spec_value = sanitize($_POST['spec_value'] ?? '');
            $sort_order = intval($_POST['spec_sort_order'] ?? 0);
            
            if ($spec_id && !empty($spec_name) && !empty($spec_value)) {
                $sql = "UPDATE product_specifications SET spec_name = ?, spec_value = ?, sort_order = ? WHERE id = ? AND product_id = ?";
                $stmt = $db->prepare($sql);
                $stmt->execute([$spec_name, $spec_value, $sort_order, $spec_id, $id]);
                redirect(SITE_URL . '/admin/products.php?action=specs&id=' . $id, 'Specification updated');
            }
        }
    }
    
    if (isset($_GET['delete_spec'])) {
        $spec_id = intval($_GET['delete_spec']);
        $db->prepare("DELETE FROM product_specifications WHERE id = ? AND product_id = ?")->execute([$spec_id, $id]);
        redirect(SITE_URL . '/admin/products.php?action=specs&id=' . $id, 'Specification deleted');
    }
}

// Handle SEO form submission BEFORE header (so redirects work)
if ($action === 'seo' && $id && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $product = $db->prepare("SELECT * FROM products WHERE id = ?");
    $product->execute([$id]);
    $product = $product->fetch();
    
    if (!$product) {
        redirect(SITE_URL . '/admin/products.php', 'Product not found', 'error');
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
    
    saveSEOData($db, 'product', $id, $seoData, $cityId);
    redirect(SITE_URL . '/admin/products.php?action=seo&id=' . $id, 'SEO data saved successfully');
}

// Now include header after all redirects are handled
require_once __DIR__ . '/includes/header.php';

// Get product for editing
$product = null;
if ($id && $action === 'edit') {
    $stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch();
}

// List all products
if ($action === 'list' || empty($action)) {
    $page = max(1, intval($_GET['page'] ?? 1));
    $offset = ($page - 1) * ITEMS_PER_PAGE;
    
    $total = $db->query("SELECT COUNT(*) FROM products")->fetchColumn();
    $totalPages = ceil($total / ITEMS_PER_PAGE);
    
    $products = $db->query("SELECT p.*, b.name as brand_name 
                            FROM products p 
                            LEFT JOIN brands b ON p.brand_id = b.id 
                            ORDER BY p.created_at DESC 
                            LIMIT " . ITEMS_PER_PAGE . " OFFSET $offset")->fetchAll();
    ?>
    <div class="content-card">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="mb-0">All Products</h5>
            <a href="?action=add" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Add New Product
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
                        <th>Brand</th>
                        <th>Status</th>
                        <th>Featured</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($products)): ?>
                        <tr><td colspan="8" class="text-center text-muted">No products found</td></tr>
                    <?php else: ?>
                        <?php foreach ($products as $p): ?>
                        <tr>
                            <td><?php echo $p['id']; ?></td>
                            <td>
                                <?php if ($p['image']): ?>
                                    <img src="<?php echo UPLOAD_URL . '/' . $p['image']; ?>" alt="" style="width: 50px; height: 50px; object-fit: cover;">
                                <?php else: ?>
                                    <span class="text-muted">No image</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($p['name']); ?></td>
                            <td><?php echo htmlspecialchars($p['brand_name']); ?></td>
                            <td>
                                <span class="badge bg-<?php echo $p['status'] == 'active' ? 'success' : 'secondary'; ?>">
                                    <?php echo ucfirst($p['status']); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($p['featured']): ?>
                                    <span class="badge bg-warning">Featured</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="?action=edit&id=<?php echo $p['id']; ?>" class="btn btn-sm btn-primary">
                                    <i class="bi bi-pencil"></i> Edit
                                </a>
                                <a href="?action=specs&id=<?php echo $p['id']; ?>" class="btn btn-sm btn-info">
                                    <i class="bi bi-list-ul"></i> Specs
                                </a>
                                <a href="?action=seo&id=<?php echo $p['id']; ?>" class="btn btn-sm btn-secondary">
                                    <i class="bi bi-search"></i> SEO
                                </a>
                                <a href="?delete=1&id=<?php echo $p['id']; ?>" class="btn btn-sm btn-danger" data-confirm="Are you sure?">
                                    <i class="bi bi-trash"></i> Delete
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <?php if ($totalPages > 1): ?>
            <?php echo generatePagination($page, $totalPages, SITE_URL . '/admin/products.php'); ?>
        <?php endif; ?>
    </div>
    <?php
} elseif ($action === 'add' || $action === 'edit') {
    ?>
    <div class="content-card">
        <h5 class="mb-4"><?php echo $action === 'add' ? 'Add New Product' : 'Edit Product'; ?></h5>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data" id="productForm">
            <div class="row">
                <div class="col-md-8">
                    <div class="mb-3">
                        <label class="form-label">Brand <span class="text-danger">*</span></label>
                        <select class="form-select" name="brand_id" id="brand_id" required>
                            <option value="">Select Brand</option>
                            <?php foreach ($brands as $brand): ?>
                                <option value="<?php echo $brand['id']; ?>" <?php echo (($product['brand_id'] ?? '') == $brand['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($brand['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Product Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($product['name'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Slug <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="slug" value="<?php echo htmlspecialchars($product['slug'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Short Description</label>
                        <input type="text" class="form-control" name="short_description" value="<?php echo htmlspecialchars($product['short_description'] ?? ''); ?>" maxlength="500">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" id="product_description" rows="8"><?php echo htmlspecialchars($product['description'] ?? ''); ?></textarea>
                        <small class="text-muted">Rich text editor - Use formatting tools above</small>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="status">
                                    <option value="active" <?php echo (($product['status'] ?? 'active') == 'active') ? 'selected' : ''; ?>>Active</option>
                                    <option value="inactive" <?php echo (($product['status'] ?? '') == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Sort Order</label>
                                <input type="number" class="form-control" name="sort_order" value="<?php echo $product['sort_order'] ?? 0; ?>">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <div class="form-check mt-4">
                                    <input class="form-check-input" type="checkbox" name="featured" id="featured" <?php echo (!empty($product['featured'])) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="featured">Featured Product</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Main Product Image</label>
                        <input type="file" class="form-control" name="image" accept="image/*">
                        <?php if (!empty($product['image'])): ?>
                            <div class="position-relative d-inline-block mt-2">
                                <img src="<?php echo UPLOAD_URL . '/' . $product['image']; ?>" alt="" class="img-thumbnail" style="max-width: 200px;">
                                <a href="?action=edit&id=<?php echo $id; ?>&delete_main_image=1" 
                                   class="btn btn-sm btn-danger position-absolute top-0 end-0" 
                                   style="transform: translate(50%, -50%);"
                                   data-confirm="Delete main image?"
                                   title="Delete Main Image">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Gallery Images</label>
                        <input type="file" class="form-control" name="gallery[]" accept="image/*" multiple>
                        <?php if (!empty($product['gallery'])): 
                            $gallery = json_decode($product['gallery'], true);
                            if (is_array($gallery) && !empty($gallery)):
                        ?>
                            <div class="mt-2">
                                <?php foreach ($gallery as $index => $img): ?>
                                    <div class="position-relative d-inline-block me-2 mb-2">
                                        <img src="<?php echo UPLOAD_URL . '/' . $img; ?>" alt="" class="img-thumbnail" style="width: 80px; height: 80px; object-fit: cover;">
                                        <a href="?action=edit&id=<?php echo $id; ?>&delete_gallery_image=1&image_index=<?php echo $index; ?>" 
                                           class="btn btn-sm btn-danger position-absolute top-0 end-0" 
                                           style="transform: translate(50%, -50%); padding: 2px 6px; font-size: 12px;"
                                           data-confirm="Delete this image?"
                                           title="Delete Image">
                                            <i class="bi bi-x"></i>
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; endif; ?>
                    </div>
                </div>
            </div>
            <!DOCTYPE html>


            <div class="mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Save Product
                </button>
                <a href="<?php echo SITE_URL; ?>/admin/products.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
    
    <!-- CKEditor Rich Text Editor -->
    <script src="https://cdn.ckeditor.com/ckeditor5/41.1.0/classic/ckeditor.js"></script>
    <script>
        // Initialize CKEditor for product description
        ClassicEditor
            .create(document.querySelector('#product_description'), {
                toolbar: {
                    items: [
                        'heading', '|',
                        'bold', 'italic', 'link', '|',
                        'bulletedList', 'numberedList', '|',
                        'outdent', 'indent', '|',
                        'blockQuote', 'insertTable', '|',
                        'undo', 'redo'
                    ]
                },
                height: 400
            })
            .catch(error => {
                console.error(error);
            });
        
        // Categories removed – no dependent selects needed
    </script>
    <?php
} elseif ($action === 'specs' && $id) {
    // Product Specifications Management
    // Note: Form handling and redirects are done before header include
    $product = $db->prepare("SELECT * FROM products WHERE id = ?");
    $product->execute([$id]);
    $product = $product->fetch();
    
    if (!$product) {
        redirect(SITE_URL . '/admin/products.php', 'Product not found', 'error');
    }
    
    // Get all specifications
    $specs = $db->prepare("SELECT * FROM product_specifications WHERE product_id = ? ORDER BY sort_order ASC, id ASC");
    $specs->execute([$id]);
    $specs = $specs->fetchAll();
    
    // Get spec for editing
    $editSpec = null;
    if (isset($_GET['edit_spec'])) {
        $spec_id = intval($_GET['edit_spec']);
        $stmt = $db->prepare("SELECT * FROM product_specifications WHERE id = ? AND product_id = ?");
        $stmt->execute([$spec_id, $id]);
        $editSpec = $stmt->fetch();
    }
    ?>
    <div class="content-card">
        <h5 class="mb-4">Product Specifications - <?php echo htmlspecialchars($product['name']); ?></h5>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0"><?php echo $editSpec ? 'Edit Specification' : 'Add New Specification'; ?></h6>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Specification Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="spec_name" value="<?php echo htmlspecialchars($editSpec['spec_name'] ?? ''); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Specification Value <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="spec_value" value="<?php echo htmlspecialchars($editSpec['spec_value'] ?? ''); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Sort Order</label>
                                <input type="number" class="form-control" name="spec_sort_order" value="<?php echo $editSpec['sort_order'] ?? 0; ?>">
                            </div>
                            <?php if ($editSpec): ?>
                                <input type="hidden" name="spec_id" value="<?php echo $editSpec['id']; ?>">
                                <button type="submit" name="update_spec" class="btn btn-primary">
                                    <i class="bi bi-save"></i> Update Specification
                                </button>
                                <a href="?action=specs&id=<?php echo $id; ?>" class="btn btn-secondary">Cancel</a>
                            <?php else: ?>
                                <button type="submit" name="add_spec" class="btn btn-primary">
                                    <i class="bi bi-plus-circle"></i> Add Specification
                                </button>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">All Specifications</h6>
                    </div>
                    <div class="card-body">
                        <?php if (empty($specs)): ?>
                            <p class="text-muted">No specifications added yet.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Value</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($specs as $spec): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($spec['spec_name']); ?></td>
                                            <td><?php echo htmlspecialchars($spec['spec_value']); ?></td>
                                            <td>
                                                <a href="?action=specs&id=<?php echo $id; ?>&edit_spec=<?php echo $spec['id']; ?>" class="btn btn-sm btn-primary">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <a href="?action=specs&id=<?php echo $id; ?>&delete_spec=<?php echo $spec['id']; ?>" class="btn btn-sm btn-danger" data-confirm="Delete this specification?">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="mt-4">
            <a href="<?php echo SITE_URL; ?>/admin/products.php" class="btn btn-secondary">Back to Products</a>
        </div>
    </div>
    <?php
} elseif ($action === 'seo' && $id) {
    // SEO Management (similar to brands/categories)
    $product = $db->prepare("SELECT * FROM products WHERE id = ?");
    $product->execute([$id]);
    $product = $product->fetch();
    
    if (!$product) {
        redirect(SITE_URL . '/admin/products.php', 'Product not found', 'error');
    }
    
    $cities = $db->query("SELECT * FROM cities WHERE status = 'active' ORDER BY name ASC")->fetchAll();
    
    // Note: Form handling and redirects are done before header include
    
    $selectedCityId = $_GET['city_id'] ?? null;
    $currentSEO = getSEOData($db, 'product', $id, $selectedCityId);
    ?>
    <div class="content-card">
        <h5 class="mb-4">SEO Management - <?php echo htmlspecialchars($product['name']); ?></h5>
        
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
                <a href="<?php echo SITE_URL; ?>/admin/products.php" class="btn btn-secondary">Back to Products</a>
            </div>
        </form>
    </div>
    <?php
}

require_once __DIR__ . '/includes/footer.php';
?>

