import "./bootstrap";

import Alpine from "alpinejs";

window.Alpine = Alpine;

Alpine.start();

document.addEventListener("DOMContentLoaded", function () {
    // Add CSRF token to all fetch requests
    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
    const token = csrfMeta ? csrfMeta.getAttribute("content") : '';

    const originalFetch = window.fetch;
    window.fetch = function (input, init = {}) {
        init.headers = init.headers || {};
        if (!init.headers["X-CSRF-TOKEN"] && token) {
            init.headers["X-CSRF-TOKEN"] = token;
        }
        return originalFetch(input, init);
    };
});
