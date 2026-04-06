if (!window.__enquiryFormScriptLoaded) {
    window.__enquiryFormScriptLoaded = true;

    const API_BASE_URL = (typeof BACKEND_API_URL !== 'undefined' ? BACKEND_API_URL : window.location.origin).replace(/\/+$/, '');
    const API_KEY_VALUE = (typeof API_KEY !== 'undefined' ? API_KEY : '').trim();
    const FILE_UPLOAD_API_BASE = (typeof FILE_UPLOAD_API_URL !== 'undefined' ? FILE_UPLOAD_API_URL : '').replace(/\/+$/, '');
    const REQUEST_TIMEOUT_MS = 15000;

    const form = document.getElementById('enquiryForm');
    const submitBtn = document.getElementById('submitBtn');
    const formMessage = document.getElementById('formMessage');
    const refreshCaptchaBtn = document.getElementById('refreshCaptcha');
    const captchaBox = document.getElementById('captchaImage');
    const captchaAnswerInput = document.getElementById('captcha_text');
    const captchaIdInput = document.getElementById('captcha_id');
    const csrfInput = document.getElementById('csrf_token');
    const honeypotInput = document.getElementById('website_url');
    const fileInput = document.getElementById('file_upload');
    const filePreview = document.getElementById('filePreview');
    const fileName = document.getElementById('fileName');
    const removeFileBtn = document.getElementById('removeFile');
    const loaderOverlay = document.getElementById('formLoaderOverlay');
    const extraFieldIds = ['company_name', 'address', 'enquiry_details'];

    function showMessage(message, type) {
        if (!formMessage) return;
        formMessage.textContent = message;
        formMessage.className = `form-message ${type}`;
        formMessage.style.display = 'block';
    }

    function hideMessage() {
        if (!formMessage) return;
        formMessage.textContent = '';
        formMessage.className = 'form-message';
        formMessage.style.display = 'none';
    }

    function setSubmitLoading(isLoading) {
        if (!submitBtn) return;
        submitBtn.disabled = isLoading;
        submitBtn.style.opacity = isLoading ? '0.7' : '1';
        if (loaderOverlay) loaderOverlay.style.display = isLoading ? 'flex' : 'none';
    }

    function generateCsrfToken() {
        const bytes = new Uint8Array(32);
        window.crypto.getRandomValues(bytes);
        return Array.from(bytes, (b) => b.toString(16).padStart(2, '0')).join('');
    }

    async function fetchWithTimeout(url, options = {}, timeoutMs = REQUEST_TIMEOUT_MS) {
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), timeoutMs);
        try {
            return await fetch(url, { ...options, signal: controller.signal });
        } finally {
            clearTimeout(timeoutId);
        }
    }

    async function uploadFile(file) {
        if (!FILE_UPLOAD_API_BASE) throw new Error('FILE_UPLOAD_API_URL is not configured');
        const fd = new FormData();
        fd.append('file', file);
        const res = await fetchWithTimeout(`${FILE_UPLOAD_API_BASE}/api/upload`, { method: 'POST', body: fd });
        const data = await res.json().catch(() => ({}));
        if (!res.ok || !data.success || !data.data?.file_link) {
            throw new Error(data.error || data.message || 'File upload failed');
        }
        return data.data.file_link;
    }

    function validateInput() {
        const fullName = document.getElementById('full_name')?.value.trim() || '';
        const mobile = document.getElementById('mobile')?.value.trim() || '';
        const email = document.getElementById('email')?.value.trim() || '';
        const captchaAnswer = captchaAnswerInput?.value.trim() || '';
        if (!fullName || fullName.length > 200) return 'Please enter a valid full name (max 200 chars).';
        if (!/^\+?[1-9]\d{1,14}$/.test(mobile)) return 'Please enter a valid mobile number in E.164 format.';
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email) || email.length > 255) return 'Please enter a valid email address.';
        if (!captchaAnswer) return 'Please solve captcha.';
        return null;
    }

    function buildPayload(csrfToken, fileLink) {
        const rawPayload = {};
        extraFieldIds.forEach((id) => {
            const el = document.getElementById(id);
            if (el?.value?.trim()) rawPayload[el.name || id] = el.value.trim();
        });
        const productId = document.querySelector('input[name="product_id"]')?.value;
        const brandId = document.querySelector('input[name="brand_id"]')?.value;
        if (productId) rawPayload.product_id = productId;
        if (brandId) rawPayload.brand_id = brandId;
        if (fileLink) rawPayload.file_link = fileLink;

        return {
            fullName: document.getElementById('full_name')?.value.trim() || '',
            mobileNumber: document.getElementById('mobile')?.value.trim() || '',
            emailId: document.getElementById('email')?.value.trim() || '',
            source: 'website',
            lead_source: 'website',
            referrerUrl: window.location.origin,
            captchaId: captchaIdInput?.value || '',
            captchaAnswer: captchaAnswerInput?.value.trim() || '',
            csrfToken,
            honeypot: honeypotInput?.value || '',
            ...(Object.keys(rawPayload).length ? { rawPayload } : {})
        };
    }

    async function loadCaptchaChallenge() {
        if (captchaBox) captchaBox.textContent = 'Loading captcha...';
        if (captchaIdInput) captchaIdInput.value = '';
        const response = await fetchWithTimeout(`${API_BASE_URL}/api/enquiries/captcha-challenge`, {
            method: 'GET',
            headers: { 'X-API-Key': API_KEY_VALUE }
        });
        const data = await response.json().catch(() => ({}));
        if (!response.ok || !data.data?.captchaId || !data.data?.question) {
            throw new Error(data.message || `Captcha challenge failed (HTTP ${response.status})`);
        }
        if (captchaIdInput) captchaIdInput.value = data.data.captchaId;
        if (captchaBox) captchaBox.textContent = data.data.question;
        if (captchaAnswerInput) captchaAnswerInput.value = '';
    }

    function initFilePreview() {
        fileInput?.addEventListener('change', () => {
            const file = fileInput.files?.[0];
            if (!file) return void (filePreview && (filePreview.style.display = 'none'));
            if (file.size > 10 * 1024 * 1024) {
                showMessage('File size exceeds 10MB limit.', 'error');
                fileInput.value = '';
                if (filePreview) filePreview.style.display = 'none';
                return;
            }
            if (fileName) fileName.textContent = file.name;
            if (filePreview) filePreview.style.display = 'block';
        });
        removeFileBtn?.addEventListener('click', () => {
            if (fileInput) fileInput.value = '';
            if (filePreview) filePreview.style.display = 'none';
        });
    }

    document.addEventListener('DOMContentLoaded', async () => {
        if (!form) return;
        initFilePreview();
        hideMessage();

        refreshCaptchaBtn?.addEventListener('click', async () => {
            try {
                await loadCaptchaChallenge();
            } catch (error) {
                showMessage(error.message || 'Failed to refresh captcha.', 'error');
            }
        });

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            hideMessage();

            if (!API_BASE_URL) return showMessage('API_BASE_URL is not configured.', 'error');
            if (!API_KEY_VALUE || API_KEY_VALUE.includes('PASTE_')) {
                return showMessage('API_KEY is not configured in form_config.js.', 'error');
            }

            const validationError = validateInput();
            if (validationError) return showMessage(validationError, 'error');
            if (honeypotInput?.value) return showMessage('Enquiry submitted successfully!', 'success');

            setSubmitLoading(true);
            try {
                const csrfToken = generateCsrfToken();
                if (csrfInput) csrfInput.value = csrfToken;
                if (!captchaIdInput?.value) await loadCaptchaChallenge();

                let fileLink = '';
                if (fileInput?.files?.[0]) {
                    fileLink = await uploadFile(fileInput.files[0]);
                }

                const payload = buildPayload(csrfToken, fileLink);
                const response = await fetchWithTimeout(`${API_BASE_URL}/api/enquiries`, {
                    method: 'POST',
                    headers: { 'X-API-Key': API_KEY_VALUE, 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const data = await response.json().catch(() => ({}));

                if (response.ok) {
                    showMessage(`Enquiry submitted successfully! ID: ${data.data?.id || 'N/A'}`, 'success');
                    form.reset();
                    if (filePreview) filePreview.style.display = 'none';
                } else {
                    showMessage(data.message || `Failed to submit enquiry (HTTP ${response.status}).`, 'error');
                }
                await loadCaptchaChallenge();
            } catch (err) {
                if (err?.name === 'AbortError') {
                    showMessage('Request timed out. Please try again.', 'error');
                } else {
                    showMessage(`Network/security error: ${err?.message || 'Could not submit enquiry.'}`, 'error');
                }
                try { await loadCaptchaChallenge(); } catch (_) {}
            } finally {
                setSubmitLoading(false);
            }
        });

        try {
            await loadCaptchaChallenge();
        } catch (error) {
            showMessage(`Captcha init failed: ${error.message || 'Unknown error'}`, 'error');
        }
    });
}
