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
    $type = $_POST['type'] ?? '';
    $label = sanitize($_POST['label'] ?? '');
    $value = sanitize($_POST['value'] ?? '');
    $icon = sanitize($_POST['icon'] ?? '');
    $status = $_POST['status'] ?? 'active';
    $sort_order = intval($_POST['sort_order'] ?? 0);
    
    if (empty($type) || empty($label) || empty($value)) {
        $error = 'Type, label and value are required';
    } else {
        if ($id) {
            $sql = "UPDATE contact_details SET type = ?, label = ?, value = ?, icon = ?, status = ?, sort_order = ? WHERE id = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$type, $label, $value, $icon, $status, $sort_order, $id]);
            redirect(SITE_URL . '/admin/contact.php', 'Contact detail updated');
        } else {
            $sql = "INSERT INTO contact_details (type, label, value, icon, status, sort_order) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $db->prepare($sql);
            $stmt->execute([$type, $label, $value, $icon, $status, $sort_order]);
            redirect(SITE_URL . '/admin/contact.php', 'Contact detail added');
        }
    }
}

// Handle delete BEFORE header
if (isset($_GET['delete']) && $id) {
    $db->prepare("DELETE FROM contact_details WHERE id = ?")->execute([$id]);
    redirect(SITE_URL . '/admin/contact.php', 'Contact detail deleted');
}

// Now include header after all redirects are handled
$currentPage = 'contact';
$pageTitle = 'Contact Details Management';
require_once __DIR__ . '/includes/header.php';

$contact = null;
if ($id && $action === 'edit') {
    $stmt = $db->prepare("SELECT * FROM contact_details WHERE id = ?");
    $stmt->execute([$id]);
    $contact = $stmt->fetch();
}

if ($action === 'list' || empty($action)) {
    $contacts = $db->query("SELECT * FROM contact_details ORDER BY type ASC, sort_order ASC")->fetchAll();
    ?>
    <div class="content-card">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="mb-0">Contact Details</h5>
            <a href="?action=add" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Add New</a>
        </div>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Type</th>
                        <th>Label</th>
                        <th>Value</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($contacts)): ?>
                        <tr><td colspan="6" class="text-center text-muted">No contact details found</td></tr>
                    <?php else: ?>
                        <?php foreach ($contacts as $c): ?>
                        <tr>
                            <td><?php echo $c['id']; ?></td>
                            <td><span class="badge bg-info"><?php echo ucfirst($c['type']); ?></span></td>
                            <td><?php echo htmlspecialchars($c['label']); ?></td>
                            <td><?php echo htmlspecialchars($c['value']); ?></td>
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
        <h5 class="mb-4"><?php echo $action === 'add' ? 'Add New Contact Detail' : 'Edit Contact Detail'; ?></h5>
        <form method="POST">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Type <span class="text-danger">*</span></label>
                        <select class="form-select" name="type" required>
                            <option value="">Select Type</option>
                            <option value="phone" <?php echo (($contact['type'] ?? '') == 'phone') ? 'selected' : ''; ?>>Phone</option>
                            <option value="email" <?php echo (($contact['type'] ?? '') == 'email') ? 'selected' : ''; ?>>Email</option>
                            <option value="address" <?php echo (($contact['type'] ?? '') == 'address') ? 'selected' : ''; ?>>Address</option>
                            <option value="social" <?php echo (($contact['type'] ?? '') == 'social') ? 'selected' : ''; ?>>Social Media</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Label <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="label" value="<?php echo htmlspecialchars($contact['label'] ?? ''); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Value <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="value" value="<?php echo htmlspecialchars($contact['value'] ?? ''); ?>" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Icon (Bootstrap Icons class)</label>
                        <input type="text" class="form-control" name="icon" value="<?php echo htmlspecialchars($contact['icon'] ?? ''); ?>" placeholder="e.g., bi-telephone">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Sort Order</label>
                        <input type="number" class="form-control" name="sort_order" value="<?php echo $contact['sort_order'] ?? 0; ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status">
                            <option value="active" <?php echo (($contact['status'] ?? 'active') == 'active') ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo (($contact['status'] ?? '') == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="mt-4">
                <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Save</button>
                <a href="<?php echo SITE_URL; ?>/admin/contact.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
    <?php
}

require_once __DIR__ . '/includes/footer.php';
?>

