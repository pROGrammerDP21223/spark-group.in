<?php
/**
 * Enquiry Form Page
 */

$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $company = sanitize($_POST['company'] ?? '');
    $subject = sanitize($_POST['subject'] ?? '');
    $message = sanitize($_POST['message'] ?? '');
    $product_id = intval($_POST['product_id'] ?? 0);
    $brand_id = intval($_POST['brand_id'] ?? 0);
    $category_id = intval($_POST['category_id'] ?? 0);
    
    // If category_id provided, get its brand_id
    if ($category_id && !$brand_id) {
        $catStmt = $db->prepare("SELECT brand_id FROM product_categories WHERE id = ?");
        $catStmt->execute([$category_id]);
        $catData = $catStmt->fetch();
        if ($catData) {
            $brand_id = $catData['brand_id'];
        }
    }
    
    // If product_id provided, get its brand_id if not already set
    if ($product_id && !$brand_id) {
        $prodStmt = $db->prepare("SELECT brand_id FROM products WHERE id = ?");
        $prodStmt->execute([$product_id]);
        $prodData = $prodStmt->fetch();
        if ($prodData) {
            $brand_id = $prodData['brand_id'];
        }
    }
    
    if (empty($name) || empty($email) || empty($message)) {
        $error = 'Name, email and message are required';
    } elseif (!isValidEmail($email)) {
        $error = 'Invalid email address';
    } else {
        $sql = "INSERT INTO enquiries (name, email, phone, company, subject, message, product_id, brand_id, ip_address, user_agent) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            $name, $email, $phone, $company, $subject, $message, 
            $product_id ?: null, $brand_id ?: null,
            $_SERVER['REMOTE_ADDR'] ?? '',
            $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]);
        
        $success = true;
    }
}

// Get product/brand/category info if provided
$product = null;
$brand = null;
$category = null;
$product_id = intval($_GET['product_id'] ?? 0);
$brand_id = intval($_GET['brand_id'] ?? 0);
$category_id = intval($_GET['category_id'] ?? 0);

if ($product_id) {
    $stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();
    // If product found, get its brand_id for the enquiry
    if ($product && !$brand_id) {
        $brand_id = $product['brand_id'];
    }
}

if ($category_id) {
    $stmt = $db->prepare("SELECT * FROM product_categories WHERE id = ?");
    $stmt->execute([$category_id]);
    $category = $stmt->fetch();
    // If category found, get its brand_id for the enquiry
    if ($category && !$brand_id) {
        $brand_id = $category['brand_id'];
    }
}

if ($brand_id) {
    $stmt = $db->prepare("SELECT * FROM brands WHERE id = ?");
    $stmt->execute([$brand_id]);
    $brand = $stmt->fetch();
}

// Get page SEO (entity_id = 3 for enquiry page)
$pageSEO = getSEOData($db, 'page', 3, null);
if (empty($pageSEO['meta_title'])) {
    $pageSEO['meta_title'] = 'Send Enquiry - ' . SITE_NAME;
}
if (empty($pageSEO['meta_description'])) {
    $pageSEO['meta_description'] = 'Send us your enquiry and we will get back to you soon';
}
if (empty($pageSEO['h1_text'])) {
    $pageSEO['h1_text'] = 'Send Enquiry';
}
if (empty($pageSEO['canonical_url'])) {
    $pageSEO['canonical_url'] = SITE_URL . '/enquiry';
}
// Ensure seo_head is included
if (!isset($pageSEO['seo_head'])) {
    $pageSEO['seo_head'] = '';
}

require __DIR__ . '/includes/public/header.php';
require_once __DIR__ . '/includes/public/breadcrumb.php';
?>

<div class="offcanvas-overlay"></div>

<?php
// Render breadcrumb
renderBreadcrumb($pageSEO['h1_text'], [
    ['text' => $pageSEO['h1_text']]
]);
?>

<div class="container mt-4">
<div class="enq-wrap">
<link rel="stylesheet" href="<?php echo SITE_URL; ?>/form_styles.css">
<script src="<?php echo SITE_URL; ?>/form_config.js"></script>
        <div class="enq-card">
      
          <header>
             <h1>Contact Us</h1>
             <p class="enq-lead">Please complete the short form and we will reply shortly.</p>
          </header>
      
          <form id="enquiryForm" class="enq-form" novalidate>
      
             <div>
                <input class="enq-input" type="text" id="company_name" name="company_name" required maxlength="255" placeholder="Company name" value="ORD">
                <span class="enq-error" id="error_company_name"></span>
             </div>
      
             <div>
                <input class="enq-input" type="text" id="full_name" name="full_name" required maxlength="255" placeholder="Your full name" value="Dhananjay Phirke">
                <span class="enq-error" id="error_full_name"></span>
             </div>
      
             <div>
                <input class="enq-input" type="email" id="email" name="email" required maxlength="255" placeholder="you@example.com" value="dphacker93@gmail.com">
                <span class="enq-error" id="error_email"></span>
             </div>
      
             <div>
                <input class="enq-input" type="tel" id="mobile" name="mobile" required maxlength="50" placeholder="+91 98765 43210" value="+919119510726">
                <span class="enq-error" id="error_mobile"></span>
             </div>
      
             <div class="enq-full">
                <input class="enq-input" type="text" id="address" name="address" required placeholder="Complete address" value="203, Prathmesh Society, Kadam wak wasti">
                <span class="enq-error" id="error_address"></span>
             </div>
      
             <div class="enq-full">
                <textarea class="enq-input" id="enquiry_details" name="enquiry_details" required rows="4" placeholder="Describe your enquiry">test mail</textarea>
                <span class="enq-error" id="error_enquiry_details"></span>
             </div>
      
             <!-- FILE UPLOAD (Optional) -->
             <div class="enq-full">
                <input class="enq-input" type="file" id="file_upload" name="file_upload" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.gif,.txt,.zip">
                <span class="enq-error" id="error_file_upload"></span>
                <small style="display: block; margin-top: 5px; color: #666;">Optional: Upload a file (PDF, DOC, DOCX, XLS, XLSX, JPG, PNG, GIF, TXT, ZIP - Max 10MB)</small>
                <div id="filePreview" style="display: none; margin-top: 10px;">
                    <span id="fileName"></span>
                    <button type="button" id="removeFile" style="margin-left: 10px; padding: 2px 8px; background: #dc3545; color: white; border: none; border-radius: 3px; cursor: pointer;">Remove</button>
                </div>
             </div>
      
             <!-- CAPTCHA -->
             <div>
                <div class="enq-captcha-row">
                   <div class="enq-captcha-img" id="captchaImage">CAPTCHA</div>
                   <button type="button" id="refreshCaptcha" class="enq-btn-refresh" aria-label="Refresh captcha">&#x21bb;</button>
                   <input class="enq-input enq-flex-grow" type="text" id="captcha_text" name="captcha_text" required maxlength="10" placeholder="Enter code" autocomplete="off">
                </div>
             </div>
             <span class="enq-error" id="error_captcha"></span>
      
             <!-- HONEYPOT -->
             <input type="text" id="website_url" name="website_url" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px;">
             <input type="hidden" id="form_timestamp" name="form_timestamp">
             <input type="hidden" id="js_token" name="js_token">
             <input type="hidden" id="csrf_token" name="csrf_token">
             <input type="hidden" id="captcha_id" name="captcha_id">
             
             <!-- Product/Brand/Category IDs for database sync -->
             <?php if ($product_id): ?>
                 <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
             <?php endif; ?>
             <?php if ($category_id): ?>
                 <input type="hidden" name="category_id" value="<?php echo $category_id; ?>">
             <?php endif; ?>
             <?php if ($brand_id): ?>
                 <input type="hidden" name="brand_id" value="<?php echo $brand_id; ?>">
             <?php endif; ?>
      
             <!-- ACTION BUTTONS -->
             <div class="enq-full enq-actions">
                <button type="submit" id="submitBtn" class="enq-btn enq-btn-primary">Submit Enquiry</button>
                <button type="button" id="clearBtn" class="enq-btn enq-btn-ghost">Clear</button>
                <div id="formMessage" class="form-message" style="display:none"></div>
             </div>
      
          </form>
      
        </div>
      </div>
    
    <!-- Loader Overlay -->
    <div id="formLoaderOverlay" class="form-loader-overlay" style="display: none;">
        <div class="form-loader">
            <div class="form-loader-spinner"></div>
            <p>Submitting...</p>
        </div>
    </div>
    
    <script src="<?php echo SITE_URL; ?>/form_script.js"></script>
</div>

<?php require __DIR__ . '/includes/public/footer.php'; ?>

