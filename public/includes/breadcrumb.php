<?php
/**
 * Reusable Breadcrumb Component - Shopwise Style
 * Usage: renderBreadcrumb($title, $items)
 * 
 * @param string $title The page title (h1)
 * @param array $items Array of breadcrumb items: [['text' => 'Text', 'url' => 'url'], ...]
 *                     Last item should not have 'url' or will be treated as active
 */

function renderBreadcrumb($title, $items = []) {
    // Always start with Home
    $breadcrumbItems = [
        ['text' => 'Home', 'url' => SITE_URL]
    ];
    
    // Add provided items
    foreach ($items as $item) {
        $breadcrumbItems[] = $item;
    }
    
    ?>
<!-- START SECTION BREADCRUMB -->
<div class="breadcrumb_section bg_gray page-title-mini">
    <div class="container"><!-- STRART CONTAINER -->
        <div class="row align-items-center">
        	<div class="col-md-6">
                <div class="page-title">
            		<h1><?php echo htmlspecialchars($title); ?></h1>
                </div>
            </div>
            <div class="col-md-6">
                <ol class="breadcrumb justify-content-md-end">
                    <?php foreach ($breadcrumbItems as $index => $item): ?>
                        <?php if ($index === count($breadcrumbItems) - 1): ?>
                            <!-- Last item is active -->
                            <li class="breadcrumb-item active"><?php echo htmlspecialchars($item['text']); ?></li>
                        <?php else: ?>
                            <!-- Link item -->
                            <li class="breadcrumb-item"><a href="<?php echo htmlspecialchars($item['url'] ?? '#'); ?>"><?php echo htmlspecialchars($item['text']); ?></a></li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ol>
            </div>
        </div>
    </div><!-- END CONTAINER-->
</div>
<!-- END SECTION BREADCRUMB -->
    <?php
}
?>

