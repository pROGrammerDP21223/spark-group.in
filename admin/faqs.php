<?php
// Start output buffering first (match other admin pages like testimonials.php)
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
    $question = sanitize($_POST['question'] ?? '');
    $answer = $_POST['answer'] ?? '';
    $status = $_POST['status'] ?? 'active';
    $sort_order = intval($_POST['sort_order'] ?? 0);
    
    if (empty($question) || empty($answer)) {
        $error = 'Question and answer are required';
    } else {
        if ($id) {
            $sql = "UPDATE faqs SET question = ?, answer = ?, status = ?, sort_order = ? WHERE id = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$question, $answer, $status, $sort_order, $id]);
            redirect(SITE_URL . '/admin/faqs.php', 'FAQ updated');
        } else {
            $sql = "INSERT INTO faqs (question, answer, status, sort_order) VALUES (?, ?, ?, ?)";
            $stmt = $db->prepare($sql);
            $stmt->execute([$question, $answer, $status, $sort_order]);
            redirect(SITE_URL . '/admin/faqs.php', 'FAQ added');
        }
    }
}

// Handle delete BEFORE header
if (isset($_GET['delete']) && $id) {
    $db->prepare("DELETE FROM faqs WHERE id = ?")->execute([$id]);
    redirect(SITE_URL . '/admin/faqs.php', 'FAQ deleted');
}

// Now include header after all redirects are handled
$currentPage = 'faqs';
$pageTitle = 'FAQs Management';
require_once __DIR__ . '/includes/header.php';

$faq = null;
if ($id && $action === 'edit') {
    $stmt = $db->prepare("SELECT * FROM faqs WHERE id = ?");
    $stmt->execute([$id]);
    $faq = $stmt->fetch();
}

if ($action === 'list' || empty($action)) {
    $faqs = $db->query("SELECT * FROM faqs ORDER BY sort_order ASC, id ASC")->fetchAll();
    ?>
    <div class="content-card">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="mb-0">All FAQs</h5>
            <a href="?action=add" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Add New FAQ</a>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Question</th>
                        <th>Answer Preview</th>
                        <th>Status</th>
                        <th>Sort Order</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($faqs)): ?>
                        <tr><td colspan="6" class="text-center text-muted">No FAQs found</td></tr>
                    <?php else: ?>
                        <?php foreach ($faqs as $f): ?>
                        <tr>
                            <td><?php echo $f['id']; ?></td>
                            <td><strong><?php echo htmlspecialchars(substr($f['question'], 0, 60)); ?><?php echo strlen($f['question']) > 60 ? '...' : ''; ?></strong></td>
                            <td><?php echo htmlspecialchars(substr(strip_tags($f['answer']), 0, 80)); ?><?php echo strlen(strip_tags($f['answer'])) > 80 ? '...' : ''; ?></td>
                            <td>
                                <span class="badge bg-<?php echo $f['status'] == 'active' ? 'success' : 'secondary'; ?>">
                                    <?php echo ucfirst($f['status']); ?>
                                </span>
                            </td>
                            <td><?php echo $f['sort_order']; ?></td>
                            <td>
                                <a href="?action=edit&id=<?php echo $f['id']; ?>" class="btn btn-sm btn-primary"><i class="bi bi-pencil"></i></a>
                                <a href="?delete=1&id=<?php echo $f['id']; ?>" class="btn btn-sm btn-danger" data-confirm="Are you sure?"><i class="bi bi-trash"></i></a>
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
        <h5 class="mb-4"><?php echo $action === 'add' ? 'Add New FAQ' : 'Edit FAQ'; ?></h5>
        <form method="POST">
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="mb-3">
                <label class="form-label">Question <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="question" value="<?php echo htmlspecialchars($faq['question'] ?? ''); ?>" required placeholder="Enter the question">
            </div>
            
            <div class="mb-3">
                <label class="form-label">Answer <span class="text-danger">*</span></label>
                <textarea class="form-control" name="answer" rows="6" required placeholder="Enter the answer"><?php echo htmlspecialchars($faq['answer'] ?? ''); ?></textarea>
                <small class="text-muted">You can use plain text or basic HTML formatting.</small>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status">
                            <option value="active" <?php echo (($faq['status'] ?? 'active') == 'active') ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo (($faq['status'] ?? '') == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Sort Order</label>
                        <input type="number" class="form-control" name="sort_order" value="<?php echo $faq['sort_order'] ?? 0; ?>" placeholder="0">
                        <small class="text-muted">Lower numbers appear first. Leave as 0 for default order.</small>
                    </div>
                </div>
            </div>
            
            <div class="mt-4">
                <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Save FAQ</button>
                <a href="<?php echo SITE_URL; ?>/admin/faqs.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
    <?php
}

require_once __DIR__ . '/includes/footer.php';
?>

