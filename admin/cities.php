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
    $name = sanitize($_POST['name'] ?? '');
    $slug = sanitize($_POST['slug'] ?? '');
    $status = $_POST['status'] ?? 'active';
    
    if (empty($name) || empty($slug)) {
        $error = 'Name and slug are required';
    } else {
        $slug = generateSlug($slug);
        
        if ($id) {
            // Update existing
            $sql = "UPDATE cities SET name = ?, slug = ?, status = ? WHERE id = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$name, $slug, $status, $id]);
            
            redirect(SITE_URL . '/admin/cities.php', 'City updated successfully');
        } else {
            // Insert new
            $sql = "INSERT INTO cities (name, slug, status) VALUES (?, ?, ?)";
            $stmt = $db->prepare($sql);
            $stmt->execute([$name, $slug, $status]);
            
            redirect(SITE_URL . '/admin/cities.php', 'City added successfully');
        }
    }
}

// Handle delete BEFORE header
if (isset($_GET['delete']) && $id) {
    $db->prepare("DELETE FROM cities WHERE id = ?")->execute([$id]);
    redirect(SITE_URL . '/admin/cities.php', 'City deleted successfully');
}

// Now include header after all redirects are handled
$currentPage = 'cities';
$pageTitle = 'Cities Management';
require_once __DIR__ . '/includes/header.php';

// Get city for editing
$city = null;
if ($id && $action === 'edit') {
    $stmt = $db->prepare("SELECT * FROM cities WHERE id = ?");
    $stmt->execute([$id]);
    $city = $stmt->fetch();
}

// List all cities
if ($action === 'list' || empty($action)) {
    $cities = $db->query("SELECT * FROM cities ORDER BY name ASC")->fetchAll();
    ?>
    <div class="content-card">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="mb-0">All Cities</h5>
            <a href="?action=add" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Add New City
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
                        <th>Name</th>
                        <th>Slug</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($cities)): ?>
                        <tr><td colspan="5" class="text-center text-muted">No cities found</td></tr>
                    <?php else: ?>
                        <?php foreach ($cities as $c): ?>
                        <tr>
                            <td><?php echo $c['id']; ?></td>
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
        <h5 class="mb-4"><?php echo $action === 'add' ? 'Add New City' : 'Edit City'; ?></h5>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">City Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($city['name'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Slug <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="slug" value="<?php echo htmlspecialchars($city['slug'] ?? ''); ?>" required>
                        <small class="text-muted">URL-friendly identifier (e.g., pune, mumbai)</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status">
                            <option value="active" <?php echo (($city['status'] ?? 'active') == 'active') ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo (($city['status'] ?? '') == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Save City
                </button>
                <a href="<?php echo SITE_URL; ?>/admin/cities.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
    <?php
}

require_once __DIR__ . '/includes/footer.php';
?>

