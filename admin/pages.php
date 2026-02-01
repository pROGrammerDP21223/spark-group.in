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
    $page_key = sanitize($_POST['page_key'] ?? '');
    $title = sanitize($_POST['title'] ?? '');
    $content = $_POST['content'] ?? '';
    $status = $_POST['status'] ?? 'active';
    
    if (empty($page_key) || empty($title)) {
        $error = 'Page key and title are required';
    } else {
        if ($id) {
            $sql = "UPDATE static_pages SET page_key = ?, title = ?, content = ?, status = ? WHERE id = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$page_key, $title, $content, $status, $id]);
            redirect(SITE_URL . '/admin/pages.php', 'Page updated');
        } else {
            $sql = "INSERT INTO static_pages (page_key, title, content, status) VALUES (?, ?, ?, ?)";
            $stmt = $db->prepare($sql);
            $stmt->execute([$page_key, $title, $content, $status]);
            redirect(SITE_URL . '/admin/pages.php', 'Page added');
        }
    }
}

// Handle delete BEFORE header
if (isset($_GET['delete']) && $id) {
    $db->prepare("DELETE FROM static_pages WHERE id = ?")->execute([$id]);
    redirect(SITE_URL . '/admin/pages.php', 'Page deleted');
}

// Now include header after all redirects are handled
$currentPage = 'pages';
$pageTitle = 'Static Pages Management';
require_once __DIR__ . '/includes/header.php';

$page = null;
if ($id && $action === 'edit') {
    $stmt = $db->prepare("SELECT * FROM static_pages WHERE id = ?");
    $stmt->execute([$id]);
    $page = $stmt->fetch();
}

if ($action === 'list' || empty($action)) {
    $pages = $db->query("SELECT * FROM static_pages ORDER BY page_key ASC")->fetchAll();
    ?>
    <div class="content-card">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="mb-0">Static Pages</h5>
            <a href="?action=add" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Add New</a>
        </div>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Page Key</th>
                        <th>Title</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($pages)): ?>
                        <tr><td colspan="5" class="text-center text-muted">No pages found</td></tr>
                    <?php else: ?>
                        <?php foreach ($pages as $p): ?>
                        <tr>
                            <td><?php echo $p['id']; ?></td>
                            <td><code><?php echo htmlspecialchars($p['page_key']); ?></code></td>
                            <td><?php echo htmlspecialchars($p['title']); ?></td>
                            <td>
                                <span class="badge bg-<?php echo $p['status'] == 'active' ? 'success' : 'secondary'; ?>">
                                    <?php echo ucfirst($p['status']); ?>
                                </span>
                            </td>
                            <td>
                                <a href="?action=edit&id=<?php echo $p['id']; ?>" class="btn btn-sm btn-primary"><i class="bi bi-pencil"></i></a>
                                <a href="?delete=1&id=<?php echo $p['id']; ?>" class="btn btn-sm btn-danger" data-confirm="Are you sure?"><i class="bi bi-trash"></i></a>
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
        <h5 class="mb-4"><?php echo $action === 'add' ? 'Add New Page' : 'Edit Page'; ?></h5>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Page Key <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="page_key" value="<?php echo htmlspecialchars($page['page_key'] ?? ''); ?>" required <?php echo $action === 'edit' ? 'readonly' : ''; ?>>
                <small class="text-muted">URL-friendly identifier (e.g., about, contact)</small>
            </div>
            <div class="mb-3">
                <label class="form-label">Title <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="title" value="<?php echo htmlspecialchars($page['title'] ?? ''); ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Content</label>
                <textarea class="form-control" name="content" id="page_content" rows="15"><?php echo htmlspecialchars($page['content'] ?? ''); ?></textarea>
                <small class="text-muted">Rich text editor - Use formatting tools above</small>
            </div>
            <div class="mb-3">
                <label class="form-label">Status</label>
                <select class="form-select" name="status">
                    <option value="active" <?php echo (($page['status'] ?? 'active') == 'active') ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive" <?php echo (($page['status'] ?? '') == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>
            <div class="mt-4">
                <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Save</button>
                <a href="<?php echo SITE_URL; ?>/admin/pages.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
    <?php
}

require_once __DIR__ . '/includes/footer.php';
?>

<!-- CKEditor Rich Text Editor -->
<script src="https://cdn.ckeditor.com/ckeditor5/41.1.0/classic/ckeditor.js"></script>
<script>
    // Initialize CKEditor for page content
    ClassicEditor
        .create(document.querySelector('#page_content'), {
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
            height: 500
        })
        .catch(error => {
            console.error(error);
        });
</script>

