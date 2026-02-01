<?php
// Start output buffering first (match other admin pages like brands.php)
if (!ob_get_level()) {
    ob_start();
}

// Require auth (which includes config, database, and functions) before header
require_once __DIR__ . '/includes/auth.php';
requireAdminLogin();

$db = Database::getInstance()->getConnection();
$viewId = $_GET['view'] ?? null;
$action = $_GET['action'] ?? 'list';

// Handle status update BEFORE header (so redirects work)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $id = intval($_POST['id'] ?? 0);
    $status = $_POST['status'] ?? 'new';
    if ($id) {
        $db->prepare("UPDATE enquiries SET status = ? WHERE id = ?")->execute([$status, $id]);
        redirect(SITE_URL . '/admin/enquiries.php?view=' . $id, 'Status updated');
    }
}

// Now include header after all redirects are handled
$currentPage = 'enquiries';
$pageTitle = 'Enquiries Management';
require_once __DIR__ . '/includes/header.php';

// View single enquiry
if ($viewId) {
    $enquiry = $db->prepare("SELECT e.*, p.name as product_name, b.name as brand_name 
                             FROM enquiries e 
                             LEFT JOIN products p ON e.product_id = p.id 
                             LEFT JOIN brands b ON e.brand_id = b.id 
                             WHERE e.id = ?");
    $enquiry->execute([$viewId]);
    $enquiry = $enquiry->fetch();
    
    if (!$enquiry) {
        redirect(SITE_URL . '/admin/enquiries.php', 'Enquiry not found', 'error');
    }
    
    // Mark as read if new
    if ($enquiry['status'] == 'new') {
        $db->prepare("UPDATE enquiries SET status = 'read' WHERE id = ?")->execute([$viewId]);
        $enquiry['status'] = 'read';
    }
    ?>
    <div class="content-card">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="mb-0">Enquiry Details</h5>
            <a href="<?php echo SITE_URL; ?>/admin/enquiries.php" class="btn btn-secondary">Back to List</a>
        </div>
        
        <div class="row">
            <div class="col-md-8">
                <table class="table">
                    <tr>
                        <th width="200">Name:</th>
                        <td><?php echo htmlspecialchars($enquiry['name']); ?></td>
                    </tr>
                    <tr>
                        <th>Email:</th>
                        <td><a href="mailto:<?php echo htmlspecialchars($enquiry['email']); ?>"><?php echo htmlspecialchars($enquiry['email']); ?></a></td>
                    </tr>
                    <?php if ($enquiry['phone']): ?>
                    <tr>
                        <th>Phone:</th>
                        <td><?php echo htmlspecialchars($enquiry['phone']); ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if ($enquiry['company']): ?>
                    <tr>
                        <th>Company:</th>
                        <td><?php echo htmlspecialchars($enquiry['company']); ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if ($enquiry['subject']): ?>
                    <tr>
                        <th>Subject:</th>
                        <td><?php echo htmlspecialchars($enquiry['subject']); ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if ($enquiry['product_name']): ?>
                    <tr>
                        <th>Product:</th>
                        <td><?php echo htmlspecialchars($enquiry['product_name']); ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if ($enquiry['brand_name']): ?>
                    <tr>
                        <th>Brand:</th>
                        <td><?php echo htmlspecialchars($enquiry['brand_name']); ?></td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                        <th>Message:</th>
                        <td><?php echo nl2br(htmlspecialchars($enquiry['message'])); ?></td>
                    </tr>
                    <tr>
                        <th>Submitted:</th>
                        <td><?php echo formatDate($enquiry['created_at'], 'd M Y, h:i A'); ?></td>
                    </tr>
                    <tr>
                        <th>Status:</th>
                        <td>
                            <span class="badge bg-<?php echo $enquiry['status'] == 'new' ? 'danger' : ($enquiry['status'] == 'read' ? 'warning' : 'success'); ?>">
                                <?php echo ucfirst($enquiry['status']); ?>
                            </span>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Update Status</h6>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="id" value="<?php echo $enquiry['id']; ?>">
                            <div class="mb-3">
                                <select class="form-select" name="status">
                                    <option value="new" <?php echo ($enquiry['status'] == 'new') ? 'selected' : ''; ?>>New</option>
                                    <option value="read" <?php echo ($enquiry['status'] == 'read') ? 'selected' : ''; ?>>Read</option>
                                    <option value="replied" <?php echo ($enquiry['status'] == 'replied') ? 'selected' : ''; ?>>Replied</option>
                                    <option value="closed" <?php echo ($enquiry['status'] == 'closed') ? 'selected' : ''; ?>>Closed</option>
                                </select>
                            </div>
                            <button type="submit" name="update_status" class="btn btn-primary w-100">Update Status</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
} else {
    // List all enquiries
    $statusFilter = $_GET['status'] ?? 'all';
    $where = "1=1";
    if ($statusFilter != 'all') {
        $where = "status = '$statusFilter'";
    }
    
    $enquiries = $db->query("SELECT * FROM enquiries WHERE $where ORDER BY created_at DESC")->fetchAll();
    ?>
    <div class="content-card">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="mb-0">All Enquiries</h5>
            <div>
                <a href="?status=all" class="btn btn-sm btn-outline-secondary">All</a>
                <a href="?status=new" class="btn btn-sm btn-outline-danger">New</a>
                <a href="?status=read" class="btn btn-sm btn-outline-warning">Read</a>
                <a href="?status=replied" class="btn btn-sm btn-outline-success">Replied</a>
            </div>
        </div>
        
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Subject</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($enquiries)): ?>
                        <tr><td colspan="8" class="text-center text-muted">No enquiries found</td></tr>
                    <?php else: ?>
                        <?php foreach ($enquiries as $e): ?>
                        <tr>
                            <td><?php echo $e['id']; ?></td>
                            <td><?php echo htmlspecialchars($e['name']); ?></td>
                            <td><?php echo htmlspecialchars($e['email']); ?></td>
                            <td><?php echo htmlspecialchars($e['phone'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($e['subject'] ?? 'N/A'); ?></td>
                            <td><?php echo formatDate($e['created_at']); ?></td>
                            <td>
                                <span class="badge bg-<?php echo $e['status'] == 'new' ? 'danger' : ($e['status'] == 'read' ? 'warning' : 'success'); ?>">
                                    <?php echo ucfirst($e['status']); ?>
                                </span>
                            </td>
                            <td>
                                <a href="?view=<?php echo $e['id']; ?>" class="btn btn-sm btn-primary">
                                    <i class="bi bi-eye"></i> View
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
}

require_once __DIR__ . '/includes/footer.php';
?>

