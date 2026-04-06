
const BACKEND_API_URL = 'https://intranet.ordbusinesshub.com'; // Updated to correct IP address


const FILE_UPLOAD_API_URL = 'https://file.onerankdigital.com';


//   Multiple emails (array): const OWNER_EMAILS = ['owner1@example.com', 'owner2@example.com'];
// If not defined, backend will use OWNER_EMAIL from backend config.php
const OWNER_EMAILS = 'info@spark-group.in'; // Change to your owner email(s)

// Backend API Configuration for Lead Ingestion
// These are required for the form to work with the backend API
const CLIENT_ID = 'ORD-20260116-001'; // Set your client_id here (e.g., 'client-123')
// const API_KEY = 'lead_live_44WcUyY_MGrBml64T1cFWeR-ly39ypY4lE5uE9CXFWA'; // Set your API key here (get from API Keys page in admin panel)
const API_KEY = 'sk_6kFaKcrA5N4eIcXPX09tiP6H7X3uiJK0KOzowNv0wP5CuRaFI68Y7gDahByOH1ZG'; // Set your API key here (get from API Keys page in admin panel)
const LEAD_SOURCE = 'website'; // Source identifier for leads (default: 'website')

// Export for use in script.js
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { BACKEND_API_URL, FILE_UPLOAD_API_URL, OWNER_EMAILS, CLIENT_ID, API_KEY, LEAD_SOURCE };
}

