<?php
// Start output buffering to prevent header errors
if (!ob_get_level()) {
    ob_start();
}

require_once __DIR__ . '/auth.php';
requireAdminLogin();

$currentAdmin = getCurrentAdmin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Admin Dashboard'; ?> - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root {
            --sidebar-width: 250px;
            --header-height: 60px;
        }
        body {
            background-color: #f5f7fa;
        }
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: linear-gradient(180deg, #2c3e50 0%, #34495e 100%);
            color: white;
            padding: 0;
            z-index: 1000;
            overflow-y: auto;
        }
        .sidebar-header {
            padding: 20px;
            background: rgba(0,0,0,0.2);
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .sidebar-menu li a {
            display: block;
            padding: 12px 20px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.3s;
            border-left: 3px solid transparent;
        }
        .sidebar-menu li a:hover,
        .sidebar-menu li a.active {
            background: rgba(255,255,255,0.1);
            color: white;
            border-left-color: #3498db;
        }
        .sidebar-menu li a i {
            width: 20px;
            margin-right: 10px;
        }
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 20px;
        }
        .top-header {
            background: white;
            padding: 15px 20px;
            margin: -20px -20px 20px -20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .content-card {
            background: white;
            border-radius: 8px;
            padding: 25px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <h5 class="mb-0"><i class="bi bi-building"></i> Admin Panel</h5>
            <small class="text-muted"><?php echo SITE_NAME; ?></small>
        </div>
        <ul class="sidebar-menu">
            <li><a href="<?php echo SITE_URL; ?>/admin/" class="<?php echo (!isset($currentPage) || $currentPage == 'dashboard') ? 'active' : ''; ?>">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a></li>
            <li><a href="<?php echo SITE_URL; ?>/admin/brands.php" class="<?php echo (isset($currentPage) && $currentPage == 'brands') ? 'active' : ''; ?>">
                <i class="bi bi-tags"></i> Brands
            </a></li>
            <li><a href="<?php echo SITE_URL; ?>/admin/products.php" class="<?php echo (isset($currentPage) && $currentPage == 'products') ? 'active' : ''; ?>">
                <i class="bi bi-box-seam"></i> Products
            </a></li>
            <li><a href="<?php echo SITE_URL; ?>/admin/cities.php" class="<?php echo (isset($currentPage) && $currentPage == 'cities') ? 'active' : ''; ?>">
                <i class="bi bi-geo-alt"></i> Cities
            </a></li>
            <li><a href="<?php echo SITE_URL; ?>/admin/slider_images.php" class="<?php echo (isset($currentPage) && $currentPage == 'slider_images') ? 'active' : ''; ?>">
                <i class="bi bi-images"></i> Slider Images
            </a></li>
            <li><a href="<?php echo SITE_URL; ?>/admin/certifications.php" class="<?php echo (isset($currentPage) && $currentPage == 'certifications') ? 'active' : ''; ?>">
                <i class="bi bi-award"></i> Certifications
            </a></li>
            <li><a href="<?php echo SITE_URL; ?>/admin/testimonials.php" class="<?php echo (isset($currentPage) && $currentPage == 'testimonials') ? 'active' : ''; ?>">
                <i class="bi bi-chat-quote"></i> Testimonials
            </a></li>
            <li><a href="<?php echo SITE_URL; ?>/admin/pages.php" class="<?php echo (isset($currentPage) && $currentPage == 'pages') ? 'active' : ''; ?>">
                <i class="bi bi-file-text"></i> Static Pages
            </a></li>
            <li><a href="<?php echo SITE_URL; ?>/admin/page_seo.php" class="<?php echo (isset($currentPage) && $currentPage == 'page_seo') ? 'active' : ''; ?>">
                <i class="bi bi-search"></i> Page SEO
            </a></li>
            <li><a href="<?php echo SITE_URL; ?>/admin/contact.php" class="<?php echo (isset($currentPage) && $currentPage == 'contact') ? 'active' : ''; ?>">
                <i class="bi bi-telephone"></i> Contact Details
            </a></li>
            <li><a href="<?php echo SITE_URL; ?>/admin/enquiries.php" class="<?php echo (isset($currentPage) && $currentPage == 'enquiries') ? 'active' : ''; ?>">
                <i class="bi bi-inbox"></i> Enquiries
            </a></li>
            <li><a href="<?php echo SITE_URL; ?>/admin/sitemap.php" class="<?php echo (isset($currentPage) && $currentPage == 'sitemap') ? 'active' : ''; ?>">
                <i class="bi bi-diagram-3"></i> Sitemap
            </a></li>
            <li><a href="<?php echo SITE_URL; ?>/admin/logout.php">
                <i class="bi bi-box-arrow-right"></i> Logout
            </a></li>
        </ul>
    </div>
    
    <div class="main-content">
        <div class="top-header">
            <h4 class="mb-0"><?php echo $pageTitle ?? 'Dashboard'; ?></h4>
            <div>
                <span class="text-muted">Welcome, <?php echo htmlspecialchars($currentAdmin['full_name']); ?></span>
                <a href="<?php echo SITE_URL; ?>" target="_blank" class="btn btn-sm btn-outline-primary ms-2">
                    <i class="bi bi-box-arrow-up-right"></i> View Site
                </a>
            </div>
        </div>
        
        <?php
        $flash = getFlashMessage();
        if ($flash):
        ?>
        <div class="alert alert-<?php echo $flash['type'] == 'error' ? 'danger' : $flash['type']; ?> alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($flash['message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

