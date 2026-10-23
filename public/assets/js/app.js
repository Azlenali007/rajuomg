/**
 * OTP Doctor Global Client Script
 */

document.addEventListener('DOMContentLoaded', () => {
    // Add CSRF token automatically to any AJAX fetch requests
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    
    window.appFetch = async (url, options = {}) => {
        options.headers = options.headers || {};
        if (csrfToken) {
            options.headers['X-CSRF-TOKEN'] = csrfToken;
        }
        return fetch(url, options);
    };
});
