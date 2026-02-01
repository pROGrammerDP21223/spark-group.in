<?php
// Start output buffering first (match other admin pages like brands.php)
if (!ob_get_level()) {
    ob_start();
}

// Require auth (which includes config, database, and functions) before header
require_once __DIR__ . '/includes/auth.php';
requireAdminLogin();

$db = Database::getInstance()->getConnection();
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

// Handle form submissions BEFORE header (so redirects work)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_name = sanitize($_POST['customer_name'] ?? '');
    $customer_designation = sanitize($_POST['customer_designation'] ?? '');
    $company_name = sanitize($_POST['company_name'] ?? '');
    $testimonial_text = sanitize($_POST['testimonial_text'] ?? '');
    $rating = intval($_POST['rating'] ?? 5);
    $status = $_POST['status'] ?? 'active';
    $sort_order = intval($_POST['sort_order'] ?? 0);
    
    if (empty($customer_name) || empty($testimonial_text)) {
        $error = 'Customer name and testimonial text are required';
    } else {
        $image = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $upload = uploadImage($_FILES['image'], 'testimonials');
            if ($upload['success']) {
                $image = $upload['path'];
            }
        }
        
        if ($id) {
            $existing = $db->prepare("SELECT image FROM testimonials WHERE id = ?");
            $existing->execute([$id]);
            $oldData = $existing->fetch();
            if (empty($image) && $oldData) $image = $oldData['image'];
            
            $sql = "UPDATE testimonials SET customer_name = ?, customer_designation = ?, company_name = ?, testimonial_text = ?, rating = ?, image = ?, status = ?, sort_order = ? WHERE id = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$customer_name, $customer_designation, $company_name, $testimonial_text, $rating, $image, $status, $sort_order, $id]);
            redirect(SITE_URL . '/admin/testimonials.php', 'Testimonial updated');
        } else {
            $sql = "INSERT INTO testimonials (customer_name, customer_designation, company_name, testimonial_text, rating, image, status, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $db->prepare($sql);
            $stmt->execute([$customer_name, $customer_designation, $company_name, $testimonial_text, $rating, $image, $status, $sort_order]);
            redirect(SITE_URL . '/admin/testimonials.php', 'Testimonial added');
        }
    }
}

// Handle delete BEFORE header
if (isset($_GET['delete']) && $id) {
    $test = $db->prepare("SELECT image FROM testimonials WHERE id = ?");
    $test->execute([$id]);
    $testData = $test->fetch();
    if ($testData && $testData['image']) deleteImage($testData['image']);
    $db->prepare("DELETE FROM testimonials WHERE id = ?")->execute([$id]);
    redirect(SITE_URL . '/admin/testimonials.php', 'Testimonial deleted');
}

// Handle testimonial image delete BEFORE header (so redirects work)
if (isset($_GET['delete_image']) && $id) {
    $testimonial = $db->prepare("SELECT image FROM testimonials WHERE id = ?");
    $testimonial->execute([$id]);
    $testimonialData = $testimonial->fetch();
    
    if ($testimonialData && !empty($testimonialData['image'])) {
        deleteImage($testimonialData['image']);
        $db->prepare("UPDATE testimonials SET image = '' WHERE id = ?")->execute([$id]);
        redirect(SITE_URL . '/admin/testimonials.php?action=edit&id=' . $id, 'Testimonial image deleted');
    }
    redirect(SITE_URL . '/admin/testimonials.php?action=edit&id=' . $id, 'Image not found', 'error');
}

// Now include header after all redirects are handled
$currentPage = 'testimonials';
$pageTitle = 'Testimonials Management';
require_once __DIR__ . '/includes/header.php';

$testimonial = null;
if ($id && $action === 'edit') {
    $stmt = $db->prepare("SELECT * FROM testimonials WHERE id = ?");
    $stmt->execute([$id]);
    $testimonial = $stmt->fetch();
}

if ($action === 'list' || empty($action)) {
    $testimonials = $db->query("SELECT * FROM testimonials ORDER BY sort_order ASC, created_at DESC")->fetchAll();
    ?>
    <div class="content-card">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="mb-0">All Testimonials</h5>
            <a href="?action=add" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Add New</a>
        </div>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Customer</th>
                        <th>Company</th>
                        <th>Rating</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($testimonials)): ?>
                        <tr><td colspan="6" class="text-center text-muted">No testimonials found</td></tr>
                    <?php else: ?>
                        <?php foreach ($testimonials as $t): ?>
                        <tr>
                            <td><?php echo $t['id']; ?></td>
                            <td><?php echo htmlspecialchars($t['customer_name']); ?></td>
                            <td><?php echo htmlspecialchars($t['company_name'] ?? 'N/A'); ?></td>
                            <td><?php echo str_repeat('★', $t['rating']); ?></td>
                            <td>
                                <span class="badge bg-<?php echo $t['status'] == 'active' ? 'success' : 'secondary'; ?>">
                                    <?php echo ucfirst($t['status']); ?>
                                </span>
                            </td>
                            <td>
                                <a href="?action=edit&id=<?php echo $t['id']; ?>" class="btn btn-sm btn-primary"><i class="bi bi-pencil"></i></a>
                                <a href="?delete=1&id=<?php echo $t['id']; ?>" class="btn btn-sm btn-danger" data-confirm="Are you sure?"><i class="bi bi-trash"></i></a>
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
        <h5 class="mb-4"><?php echo $action === 'add' ? 'Add New Testimonial' : 'Edit Testimonial'; ?></h5>
        <form method="POST" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-8">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Customer Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="customer_name" value="<?php echo htmlspecialchars($testimonial['customer_name'] ?? ''); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Designation</label>
                                <input type="text" class="form-control" name="customer_designation" value="<?php echo htmlspecialchars($testimonial['customer_designation'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Company Name</label>
                        <input type="text" class="form-control" name="company_name" value="<?php echo htmlspecialchars($testimonial['company_name'] ?? ''); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Testimonial Text <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="testimonial_text" rows="5" required><?php echo htmlspecialchars($testimonial['testimonial_text'] ?? ''); ?></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Rating</label>
                                <select class="form-select" name="rating">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <option value="<?php echo $i; ?>" <?php echo (($testimonial['rating'] ?? 5) == $i) ? 'selected' : ''; ?>>
                                            <?php echo $i; ?> Star<?php echo $i > 1 ? 's' : ''; ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Sort Order</label>
                                <input type="number" class="form-control" name="sort_order" value="<?php echo $testimonial['sort_order'] ?? 0; ?>">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status">
                            <option value="active" <?php echo (($testimonial['status'] ?? 'active') == 'active') ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo (($testimonial['status'] ?? '') == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Customer Photo</label>
                        <input type="file" class="form-control" name="image" accept="image/*">
                        <?php if (!empty($testimonial['image'])): ?>
                            <div class="position-relative d-inline-block mt-2">
                                <img src="<?php echo UPLOAD_URL . '/' . $testimonial['image']; ?>" alt="" class="img-thumbnail" style="max-width: 200px;">
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
                <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Save</button>
                <a href="<?php echo SITE_URL; ?>/admin/testimonials.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
    <?php
}

require_once __DIR__ . '/includes/footer.php';
?>

