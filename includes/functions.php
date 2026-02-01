<?php
/**
 * Common Utility Functions
 * Professional Dealer Website
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

/**
 * Generate SEO-friendly slug from string
 */
function generateSlug($string) {
    $string = strtolower(trim($string));
    $string = preg_replace('/[^a-z0-9-]/', '-', $string);
    $string = preg_replace('/-+/', '-', $string);
    $string = trim($string, '-');
    return $string;
}

/**
 * Sanitize input data
 */
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate email
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Upload image file
 */
function uploadImage($file, $folder = 'general') {
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'File upload error'];
    }
    
    if ($file['size'] > MAX_UPLOAD_SIZE) {
        return ['success' => false, 'message' => 'File size exceeds limit'];
    }
    
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, ALLOWED_IMAGE_TYPES)) {
        return ['success' => false, 'message' => 'Invalid file type'];
    }
    
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $uploadDir = UPLOAD_PATH . '/' . $folder;
    
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $filepath = $uploadDir . '/' . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return [
            'success' => true,
            'filename' => $filename,
            'path' => $folder . '/' . $filename,
            'url' => UPLOAD_URL . '/' . $folder . '/' . $filename
        ];
    }
    
    return ['success' => false, 'message' => 'Failed to move uploaded file'];
}

/**
 * Delete image file
 */
function deleteImage($path) {
    if (empty($path)) return true;
    
    $filepath = UPLOAD_PATH . '/' . $path;
    if (file_exists($filepath)) {
        return unlink($filepath);
    }
    return true;
}

/**
 * Get SEO data for entity
 */
function getSEOData($db, $entityType, $entityId, $cityId = null) {
    if ($cityId === null) {
        $sql = "SELECT * FROM seo_data 
                WHERE entity_type = :entity_type 
                AND entity_id = :entity_id 
                AND city_id IS NULL";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':entity_type' => $entityType,
            ':entity_id' => $entityId
        ]);
    } else {
        $sql = "SELECT * FROM seo_data 
                WHERE entity_type = :entity_type 
                AND entity_id = :entity_id 
                AND city_id = :city_id";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':entity_type' => $entityType,
            ':entity_id' => $entityId,
            ':city_id' => $cityId
        ]);
    }
    
    $seo = $stmt->fetch();
    
    // Return default values if no SEO data found
    if (!$seo) {
        return [
            'meta_title' => '',
            'meta_description' => '',
            'meta_keywords' => '',
            'canonical_url' => '',
            'og_title' => '',
            'og_description' => '',
            'og_image' => '',
            'h1_text' => '',
            'h2_text' => '',
            'seo_head' => ''
        ];
    }
    
    return $seo;
}

/**
 * Save/Update SEO data
 */
function saveSEOData($db, $entityType, $entityId, $data, $cityId = null) {
    // Truncate fields that have length limits to prevent database errors
    $metaTitle = substr($data['meta_title'] ?? '', 0, 255);
    $metaDescription = $data['meta_description'] ?? '';
    $metaKeywords = substr($data['meta_keywords'] ?? '', 0, 500);
    $canonicalUrl = substr($data['canonical_url'] ?? '', 0, 500);
    $ogTitle = substr($data['og_title'] ?? '', 0, 255);
    $ogDescription = $data['og_description'] ?? '';
    $ogImage = substr($data['og_image'] ?? '', 0, 500);
    $h1Text = substr($data['h1_text'] ?? '', 0, 255);
    $h2Text = $data['h2_text'] ?? ''; // TEXT type - no truncation needed
    $seoHead = $data['seo_head'] ?? '';
    
    // Check if record exists - handle NULL city_id properly
    if ($cityId === null) {
        $checkStmt = $db->prepare("SELECT id FROM seo_data WHERE entity_type = ? AND entity_id = ? AND city_id IS NULL");
        $checkStmt->execute([$entityType, $entityId]);
    } else {
        $checkStmt = $db->prepare("SELECT id FROM seo_data WHERE entity_type = ? AND entity_id = ? AND city_id = ?");
        $checkStmt->execute([$entityType, $entityId, $cityId]);
    }
    $exists = $checkStmt->fetch();
    
    if ($exists) {
        // Update existing record
        if ($cityId === null) {
            $sql = "UPDATE seo_data SET
                    meta_title = ?,
                    meta_description = ?,
                    meta_keywords = ?,
                    canonical_url = ?,
                    og_title = ?,
                    og_description = ?,
                    og_image = ?,
                    h1_text = ?,
                    h2_text = ?,
                    seo_head = ?,
                    updated_at = CURRENT_TIMESTAMP
                    WHERE entity_type = ? AND entity_id = ? AND city_id IS NULL";
            
            $stmt = $db->prepare($sql);
            return $stmt->execute([
                $metaTitle, $metaDescription, $metaKeywords, $canonicalUrl,
                $ogTitle, $ogDescription, $ogImage, $h1Text, $h2Text, $seoHead,
                $entityType, $entityId
            ]);
        } else {
            $sql = "UPDATE seo_data SET
                    meta_title = ?,
                    meta_description = ?,
                    meta_keywords = ?,
                    canonical_url = ?,
                    og_title = ?,
                    og_description = ?,
                    og_image = ?,
                    h1_text = ?,
                    h2_text = ?,
                    seo_head = ?,
                    updated_at = CURRENT_TIMESTAMP
                    WHERE entity_type = ? AND entity_id = ? AND city_id = ?";
            
            $stmt = $db->prepare($sql);
            return $stmt->execute([
                $metaTitle, $metaDescription, $metaKeywords, $canonicalUrl,
                $ogTitle, $ogDescription, $ogImage, $h1Text, $h2Text, $seoHead,
                $entityType, $entityId, $cityId
            ]);
        }
    } else {
        // Insert new record
        $sql = "INSERT INTO seo_data 
                (entity_type, entity_id, city_id, meta_title, meta_description, meta_keywords, 
                 canonical_url, og_title, og_description, og_image, h1_text, h2_text, seo_head)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $db->prepare($sql);
        return $stmt->execute([
            $entityType, $entityId, $cityId, $metaTitle, $metaDescription, $metaKeywords,
            $canonicalUrl, $ogTitle, $ogDescription, $ogImage, $h1Text, $h2Text, $seoHead
        ]);
    }
}

/**
 * Generate pagination HTML
 */
function generatePagination($currentPage, $totalPages, $baseUrl) {
    if ($totalPages <= 1) return '';
    
    $html = '<nav aria-label="Page navigation"><ul class="pagination justify-content-center">';
    
    // Previous
    if ($currentPage > 1) {
        $html .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . '?page=' . ($currentPage - 1) . '">Previous</a></li>';
    } else {
        $html .= '<li class="page-item disabled"><span class="page-link">Previous</span></li>';
    }
    
    // Page Numbers
    $start = max(1, $currentPage - 2);
    $end = min($totalPages, $currentPage + 2);
    
    if ($start > 1) {
        $html .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . '?page=1">1</a></li>';
        if ($start > 2) {
            $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
    }
    
    for ($i = $start; $i <= $end; $i++) {
        if ($i == $currentPage) {
            $html .= '<li class="page-item active"><span class="page-link">' . $i . '</span></li>';
        } else {
            $html .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . '?page=' . $i . '">' . $i . '</a></li>';
        }
    }
    
    if ($end < $totalPages) {
        if ($end < $totalPages - 1) {
            $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
        $html .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . '?page=' . $totalPages . '">' . $totalPages . '</a></li>';
    }
    
    // Next
    if ($currentPage < $totalPages) {
        $html .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . '?page=' . ($currentPage + 1) . '">Next</a></li>';
    } else {
        $html .= '<li class="page-item disabled"><span class="page-link">Next</span></li>';
    }
    
    $html .= '</ul></nav>';
    return $html;
}

/**
 * Format date
 */
function formatDate($date, $format = 'd M Y') {
    if (empty($date)) return '';
    return date($format, strtotime($date));
}

/**
 * Redirect with message
 */
function redirect($url, $message = null, $type = 'success') {
    // Clear any output buffer
    if (ob_get_level()) {
        ob_end_clean();
    }
    
    if ($message) {
        $_SESSION['flash_message'] = $message;
        $_SESSION['flash_type'] = $type;
    }
    header("Location: " . $url);
    exit;
}

/**
 * Get flash message
 */
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        $type = $_SESSION['flash_type'] ?? 'success';
        unset($_SESSION['flash_message'], $_SESSION['flash_type']);
        return ['message' => $message, 'type' => $type];
    }
    return null;
}

