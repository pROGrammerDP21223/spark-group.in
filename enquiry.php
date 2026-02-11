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

// Get product/brand info if provided
$product = null;
$brand = null;
$product_id = intval($_GET['product_id'] ?? 0);
$brand_id = intval($_GET['brand_id'] ?? 0);

if ($product_id) {
    $stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();
    // If product found, get its brand_id for the enquiry
    if ($product && !$brand_id) {
        $brand_id = $product['brand_id'];
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

// Get contact details for display
$contactDetails = $db->query("SELECT * FROM contact_details WHERE status = 'active' ORDER BY sort_order ASC")->fetchAll();
$contactMap = [];
foreach ($contactDetails as $contact) {
    $contactMap[$contact['type']][] = $contact;
}

$addressText = '';
$emailText = '';
$phoneText = '';

if (!empty($contactMap['address'])) {
    $addressText = htmlspecialchars($contactMap['address'][0]['value']);
}
if (!empty($contactMap['email'])) {
    $emailText = htmlspecialchars($contactMap['email'][0]['value']);
}
if (!empty($contactMap['phone'])) {
    $phoneText = htmlspecialchars($contactMap['phone'][0]['value']);
}

require __DIR__ . '/includes/public/header.php';
require_once __DIR__ . '/includes/public/breadcrumb.php';
?>

<div class="offcanvas-overlay"></div>

<?php
// Build breadcrumb title
$breadcrumbTitle = !empty($pageSEO['h1_text']) ? strtolower($pageSEO['h1_text']) : 'send enquiry';
$breadcrumbH1 = !empty($pageSEO['h1_text']) ? $pageSEO['h1_text'] : 'Send Us Your Enquiry';
$breadcrumbDesc = !empty($pageSEO['h2_text']) ? $pageSEO['h2_text'] : 'Please complete the form below and we will get back to you shortly with tailored solutions for your requirements.';
?>

<div class="breadcrumb-area bg-default " data-background="assets/img/breadcrumb/b1-bg-1.png">
    <div class="container fx-container-1">
        <div class="breadcrumb-wrap">

            <!-- left-content -->
            <div class="breadcrumb-content">

                <div class="breadcrumb-list ">
                    <a href="<?php echo SITE_URL; ?>">Home</a>
                    <span><?php echo $breadcrumbTitle; ?></span>
                </div>

                <h1 class="breadcrumb-title fx-heading-1 text-uppercase " data-txaa-split-text-1><?php echo htmlspecialchars($breadcrumbH1); ?></h1>

                <p class="breadcrumb-disc fx-para-1 has-clr-white fix"><span class="d-inline-block breadcrumb-slideup"><?php echo htmlspecialchars($breadcrumbDesc); ?></span></p>

                

            </div>

            <!-- right-img -->
            <div class="breadcrumb-img">
                <!-- <img src="assets/img/breadcrumb/b1-img-1.png" alt=""> -->
            </div>

        </div>
    </div>
</div>
<div id="has-jump"></div>
<div class="fx-gap-12"></div>

<!-- contact-us-start -->
<div class="fx-contact-us-1-area pb-70 pt-70">
    <div class="container fx-container-1">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="fx-contact-us-1-left">
                    <!-- section-title -->
                    <div class="fx-blog-1-scn-title mb-35 text-center">
                        <h6 class="fx-subtitle-1 ">
                            <span class="txaa-split-text-2 txaa-split-text-2-ani">Get in Touch</span>
                        </h6>
                        <h2 class="fx-scn-title-1 txaa-split-text-3 txaa-split-text-3-ani">Reach Out for Expert Industrial Service Solutions</h2>
                    </div>

                    <?php if ($success): ?>
                        <div class="alert alert-success mb-4">
                            <strong>Thank you!</strong> Your enquiry has been submitted successfully. We will get back to you soon.
                        </div>
                    <?php endif; ?>

                    <?php if ($error): ?>
                        <div class="alert alert-danger mb-4">
                            <strong>Error:</strong> <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/form_styles.css">
                    <script src="<?php echo SITE_URL; ?>/form_config.js"></script>

                    <form id="enquiryForm" action="<?php echo SITE_URL; ?>/enquiry" method="POST" class="fx-form-1" novalidate>

                        <div class="row">
                            <!-- full name -->
                            <div class="col-md-6">
                                <div class="fx-form-1-box">
                                    <label class="fx-form-1-label">full name:</label>
                                    <input class="fx-form-1-input" type="text" id="full_name" name="full_name" required maxlength="255" placeholder="e.g. Oliver Spiteri">
                                    <span class="enq-error" id="error_full_name"></span>
                                </div>
                            </div>

                            <!-- company -->
                            <div class="col-md-6">
                                <div class="fx-form-1-box">
                                    <label class="fx-form-1-label">company:</label>
                                    <input class="fx-form-1-input" type="text" id="company_name" name="company_name" maxlength="255" placeholder="e.g. ForgeX LLC">
                                    <span class="enq-error" id="error_company_name"></span>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- phone -->
                            <div class="col-md-6">
                                <div class="fx-form-1-box">
                                    <label class="fx-form-1-label">phone:</label>
                                    <input class="fx-form-1-input" type="tel" id="mobile" name="mobile" maxlength="50" placeholder="+44 20 8980 9731">
                                    <span class="enq-error" id="error_mobile"></span>
                                </div>
                            </div>

                            <!-- Email -->
                            <div class="col-md-6">
                                <div class="fx-form-1-box">
                                    <label class="fx-form-1-label">Email:</label>
                                    <input class="fx-form-1-input" type="email" id="email" name="email" required maxlength="255" placeholder="info@forgexindustry.co.uk">
                                    <span class="enq-error" id="error_email"></span>
                                </div>
                            </div>
                        </div>

                        <!-- address -->
                        <div class="row">
                            <div class="col-12">
                                <div class="fx-form-1-box">
                                    <label class="fx-form-1-label">address:</label>
                                    <input class="fx-form-1-input" type="text" id="address" name="address" placeholder="e.g. Complete address">
                                    <span class="enq-error" id="error_address"></span>
                                </div>
                            </div>
                        </div>

                        <!-- message -->
                        <div class="row">
                            <div class="col-12">
                                <div class="fx-form-1-box">
                                    <label class="fx-form-1-label">message:</label>
                                    <textarea class="fx-form-1-input" id="enquiry_details" name="enquiry_details" rows="6" required placeholder="Write your message here..."></textarea>
                                    <span class="enq-error" id="error_enquiry_details"></span>
                                </div>
                            </div>
                        </div>

                        <!-- FILE UPLOAD (Optional) -->
                        <div class="row">
                            <div class="col-12">
                                <div class="fx-form-1-box">
                                    <label class="fx-form-1-label">file upload (optional):</label>
                                    <input class="fx-form-1-input" type="file" id="file_upload" name="file_upload" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.gif,.txt,.zip">
                                    <small class="d-block mt-2 text-muted">PDF, DOC, DOCX, XLS, XLSX, JPG, PNG, GIF, TXT, ZIP - Max 10MB</small>
                                    <span class="enq-error" id="error_file_upload"></span>
                                    <div id="filePreview" class="mt-3" style="display: none;">
                                        <span id="fileName"></span>
                                        <button type="button" id="removeFile" class="btn btn-sm btn-danger ms-2">Remove</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- CAPTCHA -->
                        <div class="row">
                            <div class="col-12">
                                <div class="fx-form-1-box">
                                    <label class="fx-form-1-label">captcha:</label>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="enq-captcha-img" id="captchaImage" style="padding: 10px; background: #f5f5f5; border: 1px solid #ddd; border-radius: 4px;">CAPTCHA</div>
                                        <button type="button" id="refreshCaptcha" class="btn btn-outline-secondary" aria-label="Refresh captcha">&#x21bb;</button>
                                        <input class="fx-form-1-input flex-grow-1" type="text" id="captcha_text" name="captcha_text" required maxlength="10" placeholder="Enter code" autocomplete="off">
                                    </div>
                                    <span class="enq-error" id="error_captcha"></span>
                                </div>
                            </div>
                        </div>

                        <!-- HONEYPOT -->
                        <input type="text" id="website_url" name="website_url" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px;">
                        <input type="hidden" id="form_timestamp" name="form_timestamp">
                        <input type="hidden" id="js_token" name="js_token">
                        <input type="hidden" id="csrf_token" name="csrf_token">
                        <input type="hidden" id="captcha_id" name="captcha_id">
                        
                        <!-- Product/Brand IDs for database sync -->
                        <?php if ($product_id): ?>
                            <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                        <?php endif; ?>
                        <?php if ($brand_id): ?>
                            <input type="hidden" name="brand_id" value="<?php echo $brand_id; ?>">
                        <?php endif; ?>

                        <!-- single-box -->
                        <div class="fx-form-1-box fix txxaslideup">
                            <span class="txxaslideup-item fx-cube-1">
                                <button type="submit" id="submitBtn" aria-label="name" class="fx-pr-btn-1">
                                    <span class="text" data-back="submit now" data-front="submit now"></span>
                                    <i class="fa-solid fa-angle-right"></i>
                                </button>
                            </span>
                        </div>

                        <div id="formMessage" class="form-message" style="display:none; margin-top: 15px;"></div>

                    </form>

                    <!-- Loader Overlay -->
                    <div id="formLoaderOverlay" class="form-loader-overlay" style="display: none;">
                        <div class="form-loader">
                            <div class="form-loader-spinner"></div>
                            <p>Submitting...</p>
                        </div>
                    </div>

                    <script src="<?php echo SITE_URL; ?>/form_script.js"></script>

                </div>
            </div>
        </div>
    </div>
</div>
<!-- contact-us-end -->

<?php require __DIR__ . '/includes/public/footer.php'; ?>

