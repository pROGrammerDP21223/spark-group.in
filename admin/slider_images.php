<?php
// Start session first
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Start output buffering to prevent header errors
ob_start();

$currentPage = 'slider_images';
$pageTitle = 'Slider Images Management';

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/includes/auth.php';
requireAdminLogin();

$db = Database::getInstance()->getConnection();
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

// Handle form submissions (BEFORE including header to allow redirects)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $alt_text = sanitize($_POST['alt_text'] ?? '');
    $link_url = sanitize($_POST['link_url'] ?? '');
    $status = $_POST['status'] ?? 'active';
    $sort_order = intval($_POST['sort_order'] ?? 0);
    
    // Handle image upload
    $image = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload = uploadImage($_FILES['image'], 'slider');
        if ($upload['success']) {
            $image = $upload['path'];
        }
    }
    
    if ($id) {
        // Update existing
        $existing = $db->prepare("SELECT image FROM slider_images WHERE id = ?");
        $existing->execute([$id]);
        $oldData = $existing->fetch();
        
        if (empty($image) && $oldData) $image = $oldData['image'];
        
        $sql = "UPDATE slider_images SET image = ?, alt_text = ?, link_url = ?, status = ?, sort_order = ? WHERE id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$image, $alt_text, $link_url, $status, $sort_order, $id]);
        
        redirect(SITE_URL . '/admin/slider_images.php?action=edit&id=' . $id, 'Slider image updated successfully');
    } else {
        if (empty($image)) {
            $error = 'Image is required';
        } else {
            // Insert new
            $sql = "INSERT INTO slider_images (image, alt_text, link_url, status, sort_order) VALUES (?, ?, ?, ?, ?)";
            $stmt = $db->prepare($sql);
            $stmt->execute([$image, $alt_text, $link_url, $status, $sort_order]);
            $newId = $db->lastInsertId();
            
            redirect(SITE_URL . '/admin/slider_images.php?action=edit&id=' . $newId, 'Slider image added successfully');
        }
    }
}

// Handle delete (BEFORE including header)
if (isset($_GET['delete']) && $id) {
    $stmt = $db->prepare("SELECT image FROM slider_images WHERE id = ?");
    $stmt->execute([$id]);
    $slider = $stmt->fetch();
    
    if ($slider) {
        if ($slider['image']) {
            deleteImage($slider['image']);
        }
        
        $stmt = $db->prepare("DELETE FROM slider_images WHERE id = ?");
        $stmt->execute([$id]);
        
        redirect(SITE_URL . '/admin/slider_images.php', 'Slider image deleted successfully');
    }
}

// Get slider data for edit
$slider = null;
if ($id && $action === 'edit') {
    $stmt = $db->prepare("SELECT * FROM slider_images WHERE id = ?");
    $stmt->execute([$id]);
    $slider = $stmt->fetch();
    
    if (!$slider) {
        redirect(SITE_URL . '/admin/slider_images.php', 'Slider image not found');
    }
}

// Handle slider image delete BEFORE header (so redirects work)
if (isset($_GET['delete_image']) && $id) {
    $slider = $db->prepare("SELECT image FROM slider_images WHERE id = ?");
    $slider->execute([$id]);
    $sliderData = $slider->fetch();
    
    if ($sliderData && !empty($sliderData['image'])) {
        deleteImage($sliderData['image']);
        $db->prepare("UPDATE slider_images SET image = '' WHERE id = ?")->execute([$id]);
        redirect(SITE_URL . '/admin/slider_images.php?action=edit&id=' . $id, 'Slider image deleted');
    }
    redirect(SITE_URL . '/admin/slider_images.php?action=edit&id=' . $id, 'Image not found', 'error');
}

// Now include header after all redirects are done
require_once __DIR__ . '/includes/header.php';
?>

<div class="content-card">
    <?php if ($action === 'list'): ?>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-images"></i> Slider Images</h2>
            <a href="<?php echo SITE_URL; ?>/admin/slider_images.php?action=add" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Add Slider Image
            </a>
        </div>
        
        <?php
        $sliders = $db->query("SELECT * FROM slider_images ORDER BY sort_order ASC, id DESC")->fetchAll();
        ?>
        
        <?php if (empty($sliders)): ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> No slider images found. <a href="<?php echo SITE_URL; ?>/admin/slider_images.php?action=add">Add your first slider image</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Alt Text</th>
                            <th>Link URL</th>
                            <th>Status</th>
                            <th>Sort Order</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sliders as $item): ?>
                            <tr>
                                <td>
                                    <?php if ($item['image']): ?>
                                        <img src="<?php echo UPLOAD_URL . '/' . $item['image']; ?>" alt="" style="width: 100px; height: 60px; object-fit: cover; border-radius: 4px;">
                                    <?php else: ?>
                                        <span class="text-muted">No image</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($item['alt_text'] ?: '-'); ?></td>
                                <td>
                                    <?php if ($item['link_url']): ?>
                                        <a href="<?php echo htmlspecialchars($item['link_url']); ?>" target="_blank" class="text-decoration-none">
                                            <?php echo htmlspecialchars(substr($item['link_url'], 0, 30)); ?>...
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo $item['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                        <?php echo ucfirst($item['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo $item['sort_order']; ?></td>
                                <td>
                                    <a href="<?php echo SITE_URL; ?>/admin/slider_images.php?action=edit&id=<?php echo $item['id']; ?>" class="btn btn-sm btn-primary">
                                        <i class="bi bi-pencil"></i> Edit
                                    </a>
                                    <a href="<?php echo SITE_URL; ?>/admin/slider_images.php?delete=1&id=<?php echo $item['id']; ?>" 
                                       class="btn btn-sm btn-danger" 
                                       onclick="return confirm('Are you sure you want to delete this slider image?');">
                                        <i class="bi bi-trash"></i> Delete
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
        
    <?php else: // Add/Edit form ?>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-images"></i> <?php echo $id ? 'Edit' : 'Add'; ?> Slider Image</h2>
            <a href="<?php echo SITE_URL; ?>/admin/slider_images.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to List
            </a>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-8">
                    <div class="mb-3">
                        <label class="form-label">Slider Image <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" name="image" accept="image/*" <?php echo !$id ? 'required' : ''; ?>>
                        <small class="text-muted">Recommended size: 1920x600px or similar wide format</small>
                        <?php if ($id && !empty($slider['image'])): ?>
                            <div class="mt-2 position-relative d-inline-block">
                                <img src="<?php echo UPLOAD_URL . '/' . $slider['image']; ?>" alt="" class="img-thumbnail" style="max-width: 100%; max-height: 300px;">
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
                        <label class="form-label">Alt Text</label>
                        <input type="text" class="form-control" name="alt_text" value="<?php echo htmlspecialchars($slider['alt_text'] ?? ''); ?>" placeholder="Image description for SEO">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Link URL (Optional)</label>
                        <input type="url" class="form-control" name="link_url" value="<?php echo htmlspecialchars($slider['link_url'] ?? ''); ?>" placeholder="https://example.com">
                        <small class="text-muted">If provided, clicking the slider image will redirect to this URL</small>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status">
                            <option value="active" <?php echo (!isset($slider) || $slider['status'] === 'active') ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo (isset($slider) && $slider['status'] === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Sort Order</label>
                        <input type="number" class="form-control" name="sort_order" value="<?php echo $slider['sort_order'] ?? 0; ?>" min="0">
                        <small class="text-muted">Lower numbers appear first</small>
                    </div>
                </div>
            </div>
            
            <div class="mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Save Slider Image
                </button>
                <a href="<?php echo SITE_URL; ?>/admin/slider_images.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

