<?php
$currentPage = 'dashboard';
$pageTitle = 'Dashboard';
require_once __DIR__ . '/includes/header.php';

$db = Database::getInstance()->getConnection();

// Get statistics
$stats = [
    'brands' => $db->query("SELECT COUNT(*) FROM brands WHERE status = 'active'")->fetchColumn(),
    'categories' => $db->query("SELECT COUNT(*) FROM product_categories WHERE status = 'active'")->fetchColumn(),
    'products' => $db->query("SELECT COUNT(*) FROM products WHERE status = 'active'")->fetchColumn(),
    'cities' => $db->query("SELECT COUNT(*) FROM cities WHERE status = 'active'")->fetchColumn(),
    'enquiries' => $db->query("SELECT COUNT(*) FROM enquiries WHERE status = 'new'")->fetchColumn(),
    'testimonials' => $db->query("SELECT COUNT(*) FROM testimonials WHERE status = 'active'")->fetchColumn(),
];

// Recent enquiries
$recentEnquiries = $db->query("SELECT * FROM enquiries ORDER BY created_at DESC LIMIT 5")->fetchAll();
?>
<div class="content-card">
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">Active Brands</h6>
                            <h2 class="mb-0"><?php echo $stats['brands']; ?></h2>
                        </div>
                        <i class="bi bi-tags" style="font-size: 2.5rem; opacity: 0.5;"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">Categories</h6>
                            <h2 class="mb-0"><?php echo $stats['categories']; ?></h2>
                        </div>
                        <i class="bi bi-grid" style="font-size: 2.5rem; opacity: 0.5;"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-info">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">Products</h6>
                            <h2 class="mb-0"><?php echo $stats['products']; ?></h2>
                        </div>
                        <i class="bi bi-box-seam" style="font-size: 2.5rem; opacity: 0.5;"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">New Enquiries</h6>
                            <h2 class="mb-0"><?php echo $stats['enquiries']; ?></h2>
                        </div>
                        <i class="bi bi-inbox" style="font-size: 2.5rem; opacity: 0.5;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Recent Enquiries</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($recentEnquiries)): ?>
                        <p class="text-muted">No enquiries yet.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Subject</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentEnquiries as $enquiry): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($enquiry['name']); ?></td>
                                        <td><?php echo htmlspecialchars($enquiry['email']); ?></td>
                                        <td><?php echo htmlspecialchars($enquiry['subject'] ?? 'N/A'); ?></td>
                                        <td><?php echo formatDate($enquiry['created_at']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $enquiry['status'] == 'new' ? 'danger' : ($enquiry['status'] == 'read' ? 'warning' : 'success'); ?>">
                                                <?php echo ucfirst($enquiry['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="<?php echo SITE_URL; ?>/admin/enquiries.php?view=<?php echo $enquiry['id']; ?>" class="btn btn-sm btn-primary">
                                                View
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
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="<?php echo SITE_URL; ?>/admin/brands.php?action=add" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> Add New Brand
                        </a>
                        <a href="<?php echo SITE_URL; ?>/admin/products.php?action=add" class="btn btn-success">
                            <i class="bi bi-plus-circle"></i> Add New Product
                        </a>
                        <a href="<?php echo SITE_URL; ?>/admin/cities.php?action=add" class="btn btn-info">
                            <i class="bi bi-plus-circle"></i> Add New City
                        </a>
                        <a href="<?php echo SITE_URL; ?>/admin/sitemap.php" class="btn btn-warning">
                            <i class="bi bi-diagram-3"></i> Generate Sitemap
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

