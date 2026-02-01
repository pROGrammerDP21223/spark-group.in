/**
 * Enquiry Form JavaScript
 * 
 * Handles:
 * - Loading security tokens (CSRF, JS token, CAPTCHA)
 * - Form validation
 * - Form submission with anti-spam checks
 * - Error/success message display
 */

// Load backend API URL from config
// If config.js exists and defines BACKEND_API_URL, use it
// Otherwise, fallback to same-domain setup
let API_BASE;
if (typeof BACKEND_API_URL !== 'undefined') {
    API_BASE = BACKEND_API_URL.replace(/\/+$/, ''); // Remove trailing slashes
} else {
    // Fallback: same domain (current project uses /api/* endpoints)
    API_BASE = window.location.origin;
}

// Load file upload API URL from config
// If config.js exists and defines FILE_UPLOAD_API_URL, use it
// Otherwise, fallback to same-domain setup
let FILE_UPLOAD_API_BASE;
if (typeof FILE_UPLOAD_API_URL !== 'undefined') {
    FILE_UPLOAD_API_BASE = FILE_UPLOAD_API_URL.replace(/\/+$/, ''); // Remove trailing slashes
} else {
    // Fallback: same domain
    FILE_UPLOAD_API_BASE = window.location.origin + '/file-upload-api';
}

// Load owner emails from config.js
// If config.js exists and defines OWNER_EMAILS, use it
// Otherwise, backend will use OWNER_EMAIL from backend config.php
let ownerEmailsConfig = null;
if (typeof OWNER_EMAILS !== 'undefined') {
    const configValue = OWNER_EMAILS;
    if (typeof configValue === 'string') {
        ownerEmailsConfig = configValue.split(',').map(email => email.trim()).filter(email => email.length > 0);
    } else if (Array.isArray(configValue)) {
        ownerEmailsConfig = configValue.filter(email => email && email.trim().length > 0);
    }
}

// Load backend API configuration for lead ingestion from config.js
// These are defined in form_config.js as global constants (same pattern as OWNER_EMAILS)
let formClientId = '';
let formApiKey = '';
let formLeadSource = 'website';

if (typeof CLIENT_ID !== 'undefined') {
    formClientId = CLIENT_ID;
}

if (typeof API_KEY !== 'undefined') {
    formApiKey = API_KEY;
}

if (typeof LEAD_SOURCE !== 'undefined') {
    formLeadSource = LEAD_SOURCE;
}

// Form elements
const form = document.getElementById('enquiryForm');
const submitBtn = document.getElementById('submitBtn');
const formMessage = document.getElementById('formMessage');
const refreshCaptchaBtn = document.getElementById('refreshCaptcha');
const fileInput = document.getElementById('file_upload');
const filePreview = document.getElementById('filePreview');
const fileName = document.getElementById('fileName');
const removeFileBtn = document.getElementById('removeFile');
const loaderOverlay = document.getElementById('formLoaderOverlay');

// State
let formTimestamp = null;
let isLoadingTokens = false;
let captchaRefreshTimer = null;

/**
 * Initialize form on page load
 */
document.addEventListener('DOMContentLoaded', () => {
    initializeForm();
    initializeFileUpload();
    
    // Prevent clicks on loader overlay
    if (loaderOverlay) {
        loaderOverlay.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
        });
    }
    
    // Check for prefilled data (URL params or localStorage)
    checkAndPrefillForm();
});

/**
 * Initialize file upload handlers
 */
function initializeFileUpload() {
    // Show file preview when file is selected
    if (fileInput) {
        fileInput.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file) {
                // Validate file size (10MB)
                const maxSize = 10 * 1024 * 1024; // 10MB
                if (file.size > maxSize) {
                    showError('file_upload', 'File size exceeds maximum allowed size (10MB)');
                    fileInput.value = '';
                    if (filePreview) filePreview.style.display = 'none';
                    return;
                }
                
                if (fileName) fileName.textContent = file.name;
                if (filePreview) filePreview.style.display = 'block';
                clearError('file_upload');
            } else {
                if (filePreview) filePreview.style.display = 'none';
            }
        });
    }
    
    // Remove file button
    if (removeFileBtn) {
        removeFileBtn.addEventListener('click', () => {
            if (fileInput) fileInput.value = '';
            if (filePreview) filePreview.style.display = 'none';
            clearError('file_upload');
        });
    }
}

/**
 * Upload file to file-upload-api server
 * This calls the separate file-upload-api, not the backend API
 */
async function uploadFile(file) {
    const formData = new FormData();
    formData.append('file', file);
    
    try {
        const response = await fetch(`${FILE_UPLOAD_API_BASE}/api/upload`, {
            method: 'POST',
            body: formData,
            // credentials: 'include'
        });
        
        const data = await response.json();
        
        if (response.ok && data.success) {
            // Return the file_link from the response
            return data.data.file_link;
        } else {
            throw new Error(data.error || 'File upload failed');
        }
    } catch (error) {
        console.error('File upload error:', error);
        throw error;
    }
}

/**
 * Get label for a form field
 * Priority: 1. data-email-label attribute, 2. Associated label text, 3. Auto-convert field name
 */
function getFieldLabel(element, fieldName) {
    // Priority 1: Check for data-email-label attribute
    if (element.hasAttribute('data-email-label')) {
        return element.getAttribute('data-email-label');
    }
    
    // Priority 2: Get label from associated <label> element
    const fieldId = element.id;
    if (fieldId) {
        const labelElement = document.querySelector(`label[for="${fieldId}"]`);
        if (labelElement) {
            let labelText = labelElement.textContent.trim();
            // Remove required asterisk and extra whitespace
            labelText = labelText.replace(/\s*\*\s*$/, '').trim();
            if (labelText) {
                return labelText;
            }
        }
    }
    
    // Priority 3: Auto-convert field name (fallback)
    return fieldName
        .split('_')
        .map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
        .join(' ');
}

/**
 * Dynamically collect all form data
 * Automatically detects all form fields and excludes system/security fields
 */
function collectFormData() {
    const formData = {};
    const fieldLabels = {}; // Store custom labels for email display
    
    // Fields that should be stored in database (required fields)
    const databaseFields = [
        'company_name', 'full_name', 'email', 'mobile', 
        'address', 'enquiry_details'
    ];
    
    // System/security fields to exclude from general collection
    const systemFields = [
        'website_url',        // Honeypot
        'form_timestamp',    // Time protection
        'js_token',          // JS verification
        'csrf_token',        // CSRF protection
        'captcha_id',        // CAPTCHA ID
        'captcha_text',      // CAPTCHA text (handled separately)
        'owner_emails',      // Owner emails (configured in config.js, not from form)
        'file_upload'        // File upload (handled separately via upload API)
    ];
    
    // Get all form elements
    const formElements = form.elements;
    
    // Process each form element
    for (let i = 0; i < formElements.length; i++) {
        const element = formElements[i];
        const fieldName = element.name;
        
        // Skip if no name attribute or is a system field
        if (!fieldName || systemFields.includes(fieldName)) {
            continue;
        }
        
        // Get label for this field
        fieldLabels[fieldName] = getFieldLabel(element, fieldName);
        
        // Get value based on element type
        let value = '';
        
        if (element.type === 'checkbox') {
            value = element.checked ? (element.value || 'Yes') : '';
        } else if (element.type === 'radio') {
            if (element.checked) {
                value = element.value;
            } else {
                // Skip unchecked radios, we'll handle them below
                continue;
            }
        } else if (element.tagName === 'SELECT') {
            value = element.value;
        } else {
            value = element.value;
        }
        
        // Trim text values
        if (typeof value === 'string') {
            value = value.trim();
        }
        
        // Add to formData (only if not empty or if it's a required database field)
        if (value || databaseFields.includes(fieldName)) {
            formData[fieldName] = value;
        }
    }
    
    // Handle radio buttons (get checked value)
    const radioGroups = {};
    for (let i = 0; i < formElements.length; i++) {
        const element = formElements[i];
        if (element.type === 'radio' && element.name && !systemFields.includes(element.name)) {
            if (element.checked) {
                radioGroups[element.name] = element.value;
                // Get label for radio group (from first radio in group)
                if (!fieldLabels[element.name]) {
                    fieldLabels[element.name] = getFieldLabel(element, element.name);
                }
            }
        }
    }
    Object.assign(formData, radioGroups);
    
    // Handle checkboxes with same name (multiple selections)
    const checkboxGroups = {};
    for (let i = 0; i < formElements.length; i++) {
        const element = formElements[i];
        if (element.type === 'checkbox' && element.name && !systemFields.includes(element.name)) {
            if (!checkboxGroups[element.name]) {
                checkboxGroups[element.name] = [];
            }
            if (element.checked) {
                checkboxGroups[element.name].push(element.value || 'Yes');
            }
            // Get label for checkbox group (from first checkbox in group)
            if (!fieldLabels[element.name]) {
                fieldLabels[element.name] = getFieldLabel(element, element.name);
            }
        }
    }
    // Convert checkbox arrays to comma-separated strings
    for (const name in checkboxGroups) {
        if (checkboxGroups[name].length > 0) {
            formData[name] = checkboxGroups[name].join(', ');
        }
    }
    
    // Add security fields explicitly
    const securityFields = {
        'captcha_id': document.getElementById('captcha_id')?.value || '',
        'captcha_text': document.getElementById('captcha_text')?.value.trim() || '',
        'website_url': document.getElementById('website_url')?.value || '',
        'form_timestamp': document.getElementById('form_timestamp')?.value || '',
        'js_token': document.getElementById('js_token')?.value || '',
        'csrf_token': document.getElementById('csrf_token')?.value || ''
    };
    
    Object.assign(formData, securityFields);
    
    // Don't add field labels to form data - they're not needed
    
    return formData;
}

/**
 * Initialize form: set timestamp and load all tokens
 */
async function initializeForm() {
    // Set form load timestamp (for time-based protection)
    formTimestamp = Math.floor(Date.now() / 1000);
    const timestampField = document.getElementById('form_timestamp');
    if (timestampField) {
        timestampField.value = formTimestamp;
    }

    // Load CAPTCHA first and independently to ensure it always shows
    console.log('Loading CAPTCHA...');
    try {
        const captchaData = await loadCaptcha();
        if (captchaData && captchaData.captcha_id && captchaData.captcha_image) {
            const captchaIdField = document.getElementById('captcha_id');
            const captchaImg = document.getElementById('captchaImage');
            if (captchaIdField) {
                captchaIdField.value = captchaData.captcha_id;
            }
            if (captchaImg) {
                captchaImg.textContent = '';
                const img = document.createElement('img');
                img.src = captchaData.captcha_image;
                img.alt = 'CAPTCHA';
                img.onload = () => {
                    console.log('CAPTCHA image loaded successfully');
                    captchaImg.style.display = 'flex';
                };
                img.onerror = () => {
                    console.error('CAPTCHA image failed to load');
                    captchaImg.textContent = 'Image failed. Click refresh.';
                    captchaImg.style.color = '#b91c1c';
                };
                captchaImg.appendChild(img);
                captchaImg.style.display = 'flex';
            }
        } else {
            console.error('Invalid CAPTCHA data received:', captchaData);
        }
    } catch (error) {
        console.error('Failed to load CAPTCHA on initialization:', error);
        const captchaImg = document.getElementById('captchaImage');
        if (captchaImg) {
            captchaImg.textContent = 'Unable to load CAPTCHA. Please check backend connection.';
            captchaImg.style.color = '#b91c1c';
        }
        // Show user-friendly error message
        showMessage('Unable to connect to backend server. Please check that the server is running and accessible.', 'error');
    }

    // Load other security tokens (CSRF and JS token) in parallel
    // Note: CSRF token is now required for website leads
    loadAllTokens().catch(err => {
        console.error('Error loading security tokens:', err);
        const errorMsg = err.message || 'Unknown error';
        if (errorMsg.includes('Failed to fetch') || errorMsg.includes('ERR_CONNECTION')) {
            showMessage('Unable to connect to backend server. Please check that the server is running at ' + API_BASE, 'error');
        } else {
            showMessage('Failed to initialize form security. Please refresh the page.', 'error');
        }
    });
}

/**
 * Load security tokens (CSRF, JS token)
 * CAPTCHA is loaded separately in initializeForm() to ensure it always displays
 */
async function loadAllTokens() {
    if (isLoadingTokens) return;
    isLoadingTokens = true;

    // Load CSRF and JS tokens independently
    // Note: CSRF token is required for website leads, JS token is optional
    // CAPTCHA is loaded separately in initializeForm() for better reliability
    try {
        // Load CSRF token first (required)
        const csrfData = await loadCSRFToken();
        if (csrfData && csrfData.csrf_token) {
            const csrfField = document.getElementById('csrf_token');
            if (csrfField) {
                csrfField.value = csrfData.csrf_token;
            }
        }
        
        // Load JS token (optional - don't fail if it errors)
        loadJSToken().catch(err => {
            console.error('JS token failed (optional):', err);
        }).then(tokenData => {
            if (tokenData && tokenData.js_token) {
                const jsTokenField = document.getElementById('js_token');
                if (jsTokenField) {
                    jsTokenField.value = tokenData.js_token;
                }
            }
        });
    } catch (error) {
        console.error('Unexpected error loading tokens:', error);
    } finally {
        isLoadingTokens = false;
    }
}

/**
 * Load CSRF token from API (required for website leads)
 */
async function loadCSRFToken() {
    try {
        // Current project uses /api/security/csrf
        const url = `${API_BASE}/api/security/csrf`;
        
        // Create AbortController for timeout
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 10000); // 10 second timeout
        
        const response = await fetch(url, {
            method: 'GET',
            signal: controller.signal
        });
        
        clearTimeout(timeoutId);
        
        if (!response.ok) {
            // CSRF token is required for website leads
            console.error('CSRF token endpoint not available:', response.status, response.statusText);
            throw new Error(`Failed to load CSRF token (${response.status}). Please check backend server.`);
        }
        
        const data = await response.json();
        if (!data.csrf_token) {
            throw new Error('Invalid CSRF token received from server.');
        }
        console.log('CSRF token loaded successfully');
        return data;
    } catch (error) {
        // CSRF token is required for website leads - rethrow error with better message
        console.error('CSRF token loading failed:', error);
        if (error.name === 'AbortError' || error.message.includes('Failed to fetch') || error.message.includes('ERR_CONNECTION')) {
            throw new Error(`Cannot connect to backend server at ${API_BASE}. Please check that the server is running.`);
        }
        throw error;
    }
}

/**
 * Load JavaScript token from API (optional - not required for new backend)
 */
async function loadJSToken() {
    try {
        // Current project uses /api/security/token instead of /api/token.php
        const url = `${API_BASE}/api/security/token`;
        const response = await fetch(url, {
            method: 'GET'
        });
        
        if (!response.ok) {
            // JS token is optional - return empty token if endpoint doesn't exist
            console.warn('JS token endpoint not available, using empty token');
            return { js_token: '' };
        }
        
        return await response.json();
    } catch (error) {
        // JS token is optional - return empty token on error
        console.warn('JS token not available, using empty token:', error.message);
        return { js_token: '' };
    }
}

/**
 * Load CAPTCHA from API (optional - can be disabled if not available)
 */
async function loadCaptcha() {
    try {
        // Current project uses /api/security/captcha
        const url = `${API_BASE}/api/security/captcha`;
        
        // Create AbortController for timeout
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 10000); // 10 second timeout
        
        const response = await fetch(url, {
            method: 'GET',
            signal: controller.signal
        });
        
        clearTimeout(timeoutId);
        
        if (!response.ok) {
            // CAPTCHA is required for website leads
            console.error('CAPTCHA endpoint not available:', response.status, response.statusText);
            throw new Error(`Failed to load CAPTCHA (${response.status}). Please check backend server.`);
        }
        
        const captchaData = await response.json();
        
        if (!captchaData.captcha_id || !captchaData.captcha_image) {
            throw new Error('Invalid CAPTCHA data received from server.');
        }
        
        // Set up auto-refresh timer (refresh after 8 minutes, before 10-minute expiry)
        if (captchaRefreshTimer) {
            clearTimeout(captchaRefreshTimer);
        }
        captchaRefreshTimer = setTimeout(async () => {
            console.log('Auto-refreshing CAPTCHA before expiry...');
            try {
                const newCaptcha = await loadCaptcha();
                if (newCaptcha && newCaptcha.captcha_id && newCaptcha.captcha_image) {
                    document.getElementById('captcha_id').value = newCaptcha.captcha_id;
                    const captchaImg = document.getElementById('captchaImage');
                    captchaImg.innerHTML = `<img src="${newCaptcha.captcha_image}" alt="CAPTCHA">`;
                    document.getElementById('captcha_text').value = '';
                    console.log('CAPTCHA auto-refreshed successfully');
                }
            } catch (error) {
                console.error('Failed to auto-refresh CAPTCHA:', error);
            }
        }, 8 * 60 * 1000); // 8 minutes (480 seconds)
        
        console.log('CAPTCHA loaded successfully');
        return captchaData;
    } catch (error) {
        // CAPTCHA is required for website leads - rethrow error with better message
        console.error('CAPTCHA loading failed:', error);
        console.error('API URL:', `${API_BASE}/api/security/captcha`);
        if (error.name === 'AbortError' || error.message.includes('Failed to fetch') || error.message.includes('ERR_CONNECTION')) {
            throw new Error(`Cannot connect to backend server at ${API_BASE}. Please check that the server is running.`);
        }
        throw error;
    }
}

/**
 * Refresh CAPTCHA
 */
refreshCaptchaBtn.addEventListener('click', async () => {
    refreshCaptchaBtn.disabled = true;
    refreshCaptchaBtn.textContent = '⏳';
    
    try {
        const captchaData = await loadCaptcha();
        if (captchaData && captchaData.captcha_id && captchaData.captcha_image) {
            document.getElementById('captcha_id').value = captchaData.captcha_id;
            const captchaImg = document.getElementById('captchaImage');
            // Clear any placeholder text
            captchaImg.textContent = '';
            // Handle both img and div elements
            if (captchaImg.tagName === 'IMG') {
                captchaImg.src = captchaData.captcha_image;
                captchaImg.style.display = 'block';
            } else {
                // For div, create img element with proper styling
                captchaImg.innerHTML = `<img src="${captchaData.captcha_image}" alt="CAPTCHA">`;
                captchaImg.style.display = 'flex';
            }
            document.getElementById('captcha_text').value = ''; // Clear input
            clearError('captcha_text');
            // Note: loadCaptcha() already sets up the auto-refresh timer
        }
    } catch (error) {
        showMessage('Failed to refresh CAPTCHA. Please try again.', 'error');
    } finally {
        refreshCaptchaBtn.disabled = false;
        refreshCaptchaBtn.textContent = '🔄';
    }
});

/**
 * Form submission handler
 */
form.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    // Clear previous messages
    hideMessage();
    clearAllErrors();
    
    // Client-side validation
    if (!validateForm()) {
        return;
    }
    
    // Check minimum time requirement
    const currentTime = Math.floor(Date.now() / 1000);
    const elapsedTime = currentTime - formTimestamp;
    
    if (elapsedTime < 3) {
        showMessage('Please wait a moment before submitting. This helps us prevent spam.', 'error');
        return;
    }
    
    // Disable submit button
    setSubmitLoading(true);
    
    try {
        // Validate configuration
        if (!formClientId || !formApiKey) {
            showMessage('Form configuration error: CLIENT_ID and API_KEY must be set in form_config.js', 'error');
            setSubmitLoading(false);
            return;
        }
        
        // Handle file upload if file is selected
        let fileLink = null;
        if (fileInput.files.length > 0) {
            try {
                fileLink = await uploadFile(fileInput.files[0]);
            } catch (error) {
                showMessage('File upload failed: ' + error.message, 'error');
                setSubmitLoading(false);
                return;
            }
        }
        
        // Dynamically collect all form data
        const formData = collectFormData();
        
        // Prepare raw_payload with all form fields (preserving all data)
        // Exclude system/security fields and field labels from raw_payload
        const systemFields = ['captcha_id', 'captcha_text', 'csrf_token', 'js_token', 'website_url', '_field_labels'];
        const rawPayload = {
            domain: window.location.hostname
        };
        
        // Add all form fields except system/security fields
        for (const key in formData) {
            if (!systemFields.includes(key) && formData[key] !== undefined && formData[key] !== null && formData[key] !== '') {
                rawPayload[key] = formData[key];
            }
        }
        
        // Add form timestamp if present (for tracking, but will be formatted in UI)
        if (formData.form_timestamp) {
            rawPayload.form_timestamp = formData.form_timestamp;
        }
        
        // Add file link if file was uploaded
        if (fileLink) {
            rawPayload.file_link = fileLink;
        }
        
        // Add owner emails from config.js (if configured)
        if (ownerEmailsConfig && ownerEmailsConfig.length > 0) {
            rawPayload.owner_emails = ownerEmailsConfig;
        }
        
        // Add security fields explicitly (required for backend validation)
        // These are needed for CSRF and CAPTCHA validation even though they're system fields
        if (formData.csrf_token) {
            rawPayload.csrf_token = formData.csrf_token;
        }
        if (formData.captcha_id) {
            rawPayload.captcha_id = formData.captcha_id;
        }
        if (formData.captcha_text) {
            rawPayload.captcha_text = formData.captcha_text;
        }
        
        // Map form fields to backend LeadCreate structure
        const leadPayload = {
            client_id: formClientId,
            name: formData.full_name || null,
            email: formData.email || null,
            phone: formData.mobile || null,
            source: formLeadSource,
            lead_reference_id: null, // Can be set if needed
            raw_payload: rawPayload
        };
        
        // Submit to backend API /api/leads/ingest
        const response = await fetch(`${API_BASE}/api/leads/ingest`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-API-Key': formApiKey
            },
            body: JSON.stringify(leadPayload)
        });
        
        let data;
        try {
            data = await response.json();
        } catch (jsonError) {
            // If response is not JSON, try to get text
            const text = await response.text();
            throw new Error(text || `Server error: ${response.status} ${response.statusText}`);
        }
        
        if (response.ok) {
            // Success - backend returns LeadResponse
            showMessage('Thank you! Your enquiry has been submitted successfully.', 'success');
            
            // Sync to local database after external API success
            // Get product/brand/category IDs from URL or hidden fields
            const urlParams = new URLSearchParams(window.location.search);
            const productId = urlParams.get('product_id') || document.querySelector('input[name="product_id"]')?.value || null;
            const brandId = urlParams.get('brand_id') || document.querySelector('input[name="brand_id"]')?.value || null;
            const categoryId = urlParams.get('category_id') || document.querySelector('input[name="category_id"]')?.value || null;
            
            try {
                await syncToLocalDatabase(formData, productId, brandId, categoryId);
            } catch (syncError) {
                // Log error but don't show to user (external submission succeeded)
                console.error('Failed to sync to local database:', syncError);
            }
            
            form.reset();
            filePreview.style.display = 'none';
            
            // Reload tokens for new submission
            await loadAllTokens();
            formTimestamp = Math.floor(Date.now() / 1000);
            document.getElementById('form_timestamp').value = formTimestamp;
        } else {
            // Error - backend returns error detail
            const errorMsg = data.detail || data.error || `Server error: ${response.status} ${response.statusText}`;
            
            // Handle specific error types
            if (errorMsg.toLowerCase().includes('captcha')) {
                showMessage('The CAPTCHA has expired or is invalid. We\'ve loaded a new one for you - please enter it and submit again.', 'error');
                
                // Reload CAPTCHA automatically
                try {
                    const captchaData = await loadCaptcha();
                    if (captchaData && captchaData.captcha_id) {
                        document.getElementById('captcha_id').value = captchaData.captcha_id;
                        const captchaImg = document.getElementById('captchaImage');
                        captchaImg.innerHTML = `<img src="${captchaData.captcha_image}" alt="CAPTCHA">`;
                    }
                } catch (captchaError) {
                    console.error('Failed to reload CAPTCHA:', captchaError);
                }
                
                // Clear only the CAPTCHA text field (keep other form data)
                document.getElementById('captcha_text').value = '';
                document.getElementById('captcha_text').focus();
            } else if (errorMsg.toLowerCase().includes('csrf')) {
                showMessage('CSRF token expired. Please refresh the page and try again.', 'error');
                
                // Reload CSRF token
                try {
                    const csrfData = await loadCSRFToken();
                    if (csrfData && csrfData.csrf_token) {
                        document.getElementById('csrf_token').value = csrfData.csrf_token;
                    }
                } catch (csrfError) {
                    console.error('Failed to reload CSRF token:', csrfError);
                }
            } else {
                showMessage(errorMsg, 'error');
            }
        }
    } catch (error) {
        console.error('Submission error:', error);
        showMessage('Network error. Please check your connection and try again.', 'error');
    } finally {
        setSubmitLoading(false);
    }
});

/**
 * Client-side form validation
 * Dynamically validates all required fields in the form
 */
function validateForm() {
    let isValid = true;
    
    // System fields to exclude from validation
    const systemFields = ['website_url', 'form_timestamp', 'js_token', 'csrf_token', 'captcha_id', 'file_upload'];
    
    // Get all form elements
    const formElements = form.elements;
    
    // Validate each form element
    for (let i = 0; i < formElements.length; i++) {
        const field = formElements[i];
        const fieldId = field.id || field.name;
        
        // Skip if no ID/name or is a system field
        if (!fieldId || systemFields.includes(fieldId)) {
            continue;
        }
        
        // Check if field is required
        if (field.hasAttribute('required')) {
            let value = '';
            
            if (field.type === 'checkbox') {
                value = field.checked ? (field.value || 'Yes') : '';
            } else if (field.type === 'radio') {
                // Check if any radio in the group is checked
                const radioGroup = document.querySelectorAll(`input[name="${field.name}"][type="radio"]`);
                const isChecked = Array.from(radioGroup).some(radio => radio.checked);
                if (!isChecked) {
                    showError(fieldId, 'This field is required');
                    isValid = false;
                } else {
                    clearError(fieldId);
                }
                continue; // Skip further processing for radio
            } else if (field.tagName === 'SELECT') {
                value = field.value;
            } else {
                value = field.value;
            }
            
            // Trim text values
            if (typeof value === 'string') {
                value = value.trim();
            }
            
            if (!value) {
                showError(fieldId, 'This field is required');
                isValid = false;
            } else {
                clearError(fieldId);
                
                // Additional validations based on field type
                if (field.type === 'email') {
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(value)) {
                        showError(fieldId, 'Please enter a valid email address');
                        isValid = false;
                    }
                }
                
                if (field.type === 'tel' || fieldId === 'mobile') {
                    const mobileRegex = /^[0-9+\-\s()]+$/;
                    if (!mobileRegex.test(value)) {
                        showError(fieldId, 'Please enter a valid mobile number');
                        isValid = false;
                    }
                }
            }
        } else {
            // Clear errors for non-required fields
            clearError(fieldId);
        }
    }
    
    // Special validation for CAPTCHA (only if CAPTCHA is enabled/visible)
    const captchaText = document.getElementById('captcha_text');
    const captchaSection = document.querySelector('.enq-captcha-row')?.closest('div');
    const isCaptchaVisible = captchaSection && captchaSection.style.display !== 'none';
    if (captchaText && isCaptchaVisible && captchaText.hasAttribute('required') && !captchaText.value.trim()) {
        showError('captcha_text', 'CAPTCHA is required');
        isValid = false;
    }
    
    return isValid;
}

/**
 * Show error message for a field
 */
function showError(fieldId, message) {
    const errorElement = document.getElementById(`error_${fieldId}`);
    if (errorElement) {
        errorElement.textContent = message;
    }
    
    const field = document.getElementById(fieldId);
    if (field) {
        field.style.borderColor = '#e74c3c';
    }
}

/**
 * Clear error message for a field
 */
function clearError(fieldId) {
    const errorElement = document.getElementById(`error_${fieldId}`);
    if (errorElement) {
        errorElement.textContent = '';
    }
    
    const field = document.getElementById(fieldId);
    if (field) {
        field.style.borderColor = '';
    }
}

/**
 * Clear all error messages
 */
function clearAllErrors() {
    const errorElements = document.querySelectorAll('.error-message');
    errorElements.forEach(el => {
        el.textContent = '';
    });
    
    const fields = document.querySelectorAll('input, textarea');
    fields.forEach(field => {
        field.style.borderColor = '';
    });
}

/**
 * Show form message (success/error)
 */
function showMessage(message, type) {
    formMessage.textContent = message;
    formMessage.className = `form-message ${type}`;
    formMessage.style.display = 'block';
    
    // Scroll to message
    formMessage.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    
    // Auto-hide success messages after 5 seconds
    if (type === 'success') {
        setTimeout(() => {
            hideMessage();
        }, 5000);
    }
}

/**
 * Hide form message
 */
function hideMessage() {
    formMessage.style.display = 'none';
    formMessage.textContent = '';
    formMessage.className = 'form-message';
}

/**
 * Set submit button loading state
 * Shows loader overlay and disables all form interactions
 */
function setSubmitLoading(loading) {
    submitBtn.disabled = loading;
    if (loading) {
        submitBtn.textContent = 'Submitting...';
        submitBtn.style.opacity = '0.7';
        
        // Show loader overlay
        if (loaderOverlay) {
            loaderOverlay.style.display = 'flex';
        }
        
        // Disable all form inputs and buttons
        const formElements = form.elements;
        for (let i = 0; i < formElements.length; i++) {
            const element = formElements[i];
            element.disabled = true;
        }
        
        // Disable clear button
        const clearBtn = document.getElementById('clearBtn');
        if (clearBtn) {
            clearBtn.disabled = true;
        }
        
        // Disable refresh captcha button
        if (refreshCaptchaBtn) {
            refreshCaptchaBtn.disabled = true;
        }
        
        // Disable remove file button
        if (removeFileBtn) {
            removeFileBtn.disabled = true;
        }
        
        // Prevent form submission by disabling the form
        form.style.pointerEvents = 'none';
    } else {
        submitBtn.textContent = 'Submit Enquiry';
        submitBtn.style.opacity = '1';
        
        // Hide loader overlay
        if (loaderOverlay) {
            loaderOverlay.style.display = 'none';
        }
        
        // Re-enable all form inputs and buttons
        const formElements = form.elements;
        for (let i = 0; i < formElements.length; i++) {
            const element = formElements[i];
            // Don't re-enable hidden fields or system fields
            if (element.type !== 'hidden' && element.id !== 'website_url') {
                element.disabled = false;
            }
        }
        
        // Re-enable clear button
        const clearBtn = document.getElementById('clearBtn');
        if (clearBtn) {
            clearBtn.disabled = false;
        }
        
        // Re-enable refresh captcha button
        if (refreshCaptchaBtn) {
            refreshCaptchaBtn.disabled = false;
        }
        
        // Re-enable remove file button
        if (removeFileBtn) {
            removeFileBtn.disabled = false;
        }
        
        // Re-enable form interactions
        form.style.pointerEvents = 'auto';
    }
}

/**
 * Clear button handler
 */
const clearBtn = document.getElementById('clearBtn');
if (clearBtn) {
    clearBtn.addEventListener('click', async () => {
        form.reset();
        clearAllErrors();
        hideMessage();
        
        // Reload CAPTCHA (this also restarts the auto-refresh timer)
        try {
            const captchaData = await loadCaptcha();
            if (captchaData && captchaData.captcha_id && captchaData.captcha_image) {
                document.getElementById('captcha_id').value = captchaData.captcha_id;
                const captchaImg = document.getElementById('captchaImage');
                captchaImg.innerHTML = `<img src="${captchaData.captcha_image}" alt="CAPTCHA">`;
            }
        } catch (error) {
            console.error('Failed to reload CAPTCHA after clear:', error);
        }
        
        // Reload other tokens
        loadAllTokens();
        formTimestamp = Math.floor(Date.now() / 1000);
        document.getElementById('form_timestamp').value = formTimestamp;
    });
}

/**
 * Real-time validation on input
 */
const formFields = document.querySelectorAll('input[required], textarea[required]');
formFields.forEach(field => {
    field.addEventListener('blur', () => {
        const value = field.value.trim();
        if (!value) {
            showError(field.id, 'This field is required');
        } else {
            clearError(field.id);
        }
    });
    
    field.addEventListener('input', () => {
        if (field.value.trim()) {
            clearError(field.id);
        }
    });
});

/**
 * Prefill form with data
 * Can be called with data object, or will check URL parameters and localStorage
 * Supports both flat data and nested raw_payload structure
 */
function prefillForm(data) {
    if (!data) {
        // Try to get data from URL parameters
        const urlParams = new URLSearchParams(window.location.search);
        const urlData = {};
        urlParams.forEach((value, key) => {
            urlData[key] = decodeURIComponent(value);
        });
        
        // Try to get data from localStorage
        const storedData = localStorage.getItem('enquiry_form_prefill');
        if (storedData) {
            try {
                const parsedData = JSON.parse(storedData);
                Object.assign(urlData, parsedData);
            } catch (e) {
                console.error('Failed to parse stored prefill data:', e);
            }
        }
        
        data = urlData;
    }
    
    if (!data || Object.keys(data).length === 0) {
        return; // No data to prefill
    }
    
    // If data has raw_payload, extract and merge it
    if (data.raw_payload && typeof data.raw_payload === 'object') {
        // Merge raw_payload fields into main data object
        Object.assign(data, data.raw_payload);
        // Remove raw_payload to avoid confusion
        delete data.raw_payload;
    }
    
    // Map of field names (from data) to form field IDs
    const fieldMapping = {
        'company_name': 'company_name',
        'full_name': 'full_name',
        'name': 'full_name', // Alternative mapping
        'email': 'email',
        'mobile': 'mobile',
        'phone': 'mobile', // Alternative mapping
        'address': 'address',
        'enquiry_details': 'enquiry_details',
        'enquiry': 'enquiry_details' // Alternative mapping
    };
    
    // Prefill each field
    for (const [dataKey, value] of Object.entries(data)) {
        if (value === null || value === undefined || value === '') {
            continue; // Skip empty values
        }
        
        // Get the form field ID
        const fieldId = fieldMapping[dataKey] || dataKey;
        const field = document.getElementById(fieldId);
        
        if (field) {
            // Don't prefill hidden/system fields
            if (field.type === 'hidden' || 
                ['csrf_token', 'js_token', 'captcha_id', 'captcha_text', 'form_timestamp', 'website_url'].includes(fieldId)) {
                continue;
            }
            
            // Set the value
            if (field.tagName === 'TEXTAREA' || field.type === 'text' || field.type === 'email' || field.type === 'tel') {
                field.value = value;
                // Trigger input event to clear any errors
                field.dispatchEvent(new Event('input', { bubbles: true }));
            }
        }
    }
    
    console.log('Form prefilled with data:', data);
}

/**
 * Check for prefilled data on page load
 */
function checkAndPrefillForm() {
    // Wait a bit for form to be fully initialized
    setTimeout(() => {
        prefillForm();
        
        // Clear localStorage after prefilling (one-time use)
        localStorage.removeItem('enquiry_form_prefill');
    }, 500);
}

/**
 * Sync enquiry data to local database after external API submission succeeds
 */
async function syncToLocalDatabase(formData, productId = null, brandId = null, categoryId = null) {
    try {
        // Get product/brand/category IDs from URL parameters or hidden fields if not provided
        if (!productId) {
            const urlParams = new URLSearchParams(window.location.search);
            productId = urlParams.get('product_id') || document.querySelector('input[name="product_id"]')?.value || null;
        }
        if (!brandId) {
            const urlParams = new URLSearchParams(window.location.search);
            brandId = urlParams.get('brand_id') || document.querySelector('input[name="brand_id"]')?.value || null;
        }
        if (!categoryId) {
            const urlParams = new URLSearchParams(window.location.search);
            categoryId = urlParams.get('category_id') || document.querySelector('input[name="category_id"]')?.value || null;
        }
        
        // Prepare sync payload
        const syncPayload = {
            full_name: formData.full_name || '',
            email: formData.email || '',
            mobile: formData.mobile || '',
            company_name: formData.company_name || '',
            address: formData.address || '',
            enquiry_details: formData.enquiry_details || '',
            product_id: productId,
            brand_id: brandId,
            category_id: categoryId
        };
        
        // Send to local sync endpoint
        const syncResponse = await fetch(`${window.location.origin}/api/sync-enquiry.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(syncPayload)
        });
        
        const syncResult = await syncResponse.json();
        
        if (!syncResponse.ok || !syncResult.success) {
            throw new Error(syncResult.error || 'Sync failed');
        }
        
        console.log('Enquiry synced to local database:', syncResult);
        return syncResult;
    } catch (error) {
        console.error('Error syncing to local database:', error);
        throw error;
    }
}

/**
 * Expose prefillForm globally for programmatic use
 */
window.prefillEnquiryForm = prefillForm;

