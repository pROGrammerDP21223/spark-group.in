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
    $title = sanitize($_POST['title'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $certificate_number = sanitize($_POST['certificate_number'] ?? '');
    $issued_date = $_POST['issued_date'] ?? null;
    $expiry_date = $_POST['expiry_date'] ?? null;
    $status = $_POST['status'] ?? 'active';
    $sort_order = intval($_POST['sort_order'] ?? 0);
    
    if (empty($title)) {
        $error = 'Title is required';
    } else {
        $image = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $upload = uploadImage($_FILES['image'], 'certifications');
            if ($upload['success']) {
                $image = $upload['path'];
            }
        }
        
        if ($id) {
            $existing = $db->prepare("SELECT image FROM certifications WHERE id = ?");
            $existing->execute([$id]);
            $oldData = $existing->fetch();
            if (empty($image) && $oldData) $image = $oldData['image'];
            
            $sql = "UPDATE certifications SET title = ?, description = ?, image = ?, certificate_number = ?, issued_date = ?, expiry_date = ?, status = ?, sort_order = ? WHERE id = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$title, $description, $image, $certificate_number, $issued_date ?: null, $expiry_date ?: null, $status, $sort_order, $id]);
            redirect(SITE_URL . '/admin/certifications.php', 'Certification updated');
        } else {
            $sql = "INSERT INTO certifications (title, description, image, certificate_number, issued_date, expiry_date, status, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $db->prepare($sql);
            $stmt->execute([$title, $description, $image, $certificate_number, $issued_date ?: null, $expiry_date ?: null, $status, $sort_order]);
            redirect(SITE_URL . '/admin/certifications.php', 'Certification added');
        }
    }
}

if (isset($_GET['delete']) && $id) {
    $cert = $db->prepare("SELECT image FROM certifications WHERE id = ?");
    $cert->execute([$id]);
    $certData = $cert->fetch();
    if ($certData && $certData['image']) deleteImage($certData['image']);
    $db->prepare("DELETE FROM certifications WHERE id = ?")->execute([$id]);
    redirect(SITE_URL . '/admin/certifications.php', 'Certification deleted');
}

// Handle certification image delete BEFORE header (so redirects work)
if (isset($_GET['delete_image']) && $id) {
    $certification = $db->prepare("SELECT image FROM certifications WHERE id = ?");
    $certification->execute([$id]);
    $certificationData = $certification->fetch();
    
    if ($certificationData && !empty($certificationData['image'])) {
        deleteImage($certificationData['image']);
        $db->prepare("UPDATE certifications SET image = '' WHERE id = ?")->execute([$id]);
        redirect(SITE_URL . '/admin/certifications.php?action=edit&id=' . $id, 'Certification image deleted');
    }
    redirect(SITE_URL . '/admin/certifications.php?action=edit&id=' . $id, 'Image not found', 'error');
}

// Now include header after all redirects are handled
$currentPage = 'certifications';
$pageTitle = 'Certifications Management';
require_once __DIR__ . '/includes/header.php';

$certification = null;
if ($id && $action === 'edit') {
    $stmt = $db->prepare("SELECT * FROM certifications WHERE id = ?");
    $stmt->execute([$id]);
    $certification = $stmt->fetch();
}

if ($action === 'list' || empty($action)) {
    $certifications = $db->query("SELECT * FROM certifications ORDER BY sort_order ASC, issued_date DESC")->fetchAll();
    ?>
    <div class="content-card">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="mb-0">All Certifications</h5>
            <a href="?action=add" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Add New</a>
        </div>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Title</th>
                        <th>Certificate #</th>
                        <th>Issued Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($certifications)): ?>
                        <tr><td colspan="7" class="text-center text-muted">No certifications found</td></tr>
                    <?php else: ?>
                        <?php foreach ($certifications as $c): ?>
                        <tr>
                            <td><?php echo $c['id']; ?></td>
                            <td>
                                <?php if ($c['image']): ?>
                                    <img src="<?php echo UPLOAD_URL . '/' . $c['image']; ?>" alt="" style="width: 50px; height: 50px; object-fit: cover;">
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($c['title']); ?></td>
                            <td><?php echo htmlspecialchars($c['certificate_number']); ?></td>
                            <td><?php echo $c['issued_date'] ? formatDate($c['issued_date']) : 'N/A'; ?></td>
                            <td>
                                <span class="badge bg-<?php echo $c['status'] == 'active' ? 'success' : 'secondary'; ?>">
                                    <?php echo ucfirst($c['status']); ?>
                                </span>
                            </td>
                            <td>
                                <a href="?action=edit&id=<?php echo $c['id']; ?>" class="btn btn-sm btn-primary"><i class="bi bi-pencil"></i></a>
                                <a href="?delete=1&id=<?php echo $c['id']; ?>" class="btn btn-sm btn-danger" data-confirm="Are you sure?"><i class="bi bi-trash"></i></a>
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
        <h5 class="mb-4"><?php echo $action === 'add' ? 'Add New Certification' : 'Edit Certification'; ?></h5>
        <form method="POST" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-8">
                    <div class="mb-3">
                        <label class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="title" value="<?php echo htmlspecialchars($certification['title'] ?? ''); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="4"><?php echo htmlspecialchars($certification['description'] ?? ''); ?></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Certificate Number</label>
                                <input type="text" class="form-control" name="certificate_number" value="<?php echo htmlspecialchars($certification['certificate_number'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Sort Order</label>
                                <input type="number" class="form-control" name="sort_order" value="<?php echo $certification['sort_order'] ?? 0; ?>">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Issued Date</label>
                                <input type="date" class="form-control" name="issued_date" value="<?php echo $certification['issued_date'] ?? ''; ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Expiry Date</label>
                                <input type="date" class="form-control" name="expiry_date" value="<?php echo $certification['expiry_date'] ?? ''; ?>">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status">
                            <option value="active" <?php echo (($certification['status'] ?? 'active') == 'active') ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo (($certification['status'] ?? '') == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Certificate Image</label>
                        <input type="file" class="form-control" name="image" accept="image/*">
                        <?php if (!empty($certification['image'])): ?>
                            <div class="position-relative d-inline-block mt-2">
                                <img src="<?php echo UPLOAD_URL . '/' . $certification['image']; ?>" alt="" class="img-thumbnail" style="max-width: 200px;">
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
                <a href="<?php echo SITE_URL; ?>/admin/certifications.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
    <?php
}

require_once __DIR__ . '/includes/footer.php';
?>

