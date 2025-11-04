/**
 * Helper untuk mengatur loading state pada button
 * @param {HTMLElement} button - Button element
 * @param {boolean} isLoading - Status loading
 * @param {string} loadingText - Text saat loading (default: 'Memproses...')
 */
function setButtonLoading(button, isLoading, loadingText = 'Memproses...') {
    if (!button) return;

    if (isLoading) {
        // Simpan original content
        button.dataset.originalContent = button.innerHTML;
        button.disabled = true;

        // Set loading state
        const spinner = `
            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            ${loadingText}
        `;
        button.innerHTML = spinner;
        button.classList.add('opacity-50', 'cursor-not-allowed');
    } else {
        // Restore original content
        if (button.dataset.originalContent) {
            button.innerHTML = button.dataset.originalContent;
            delete button.dataset.originalContent;
        }
        button.disabled = false;
        button.classList.remove('opacity-50', 'cursor-not-allowed');
    }
}

/**
 * Helper untuk membuat form dengan loading state
 * @param {HTMLElement} form - Form element
 * @param {Function} submitHandler - Function yang akan dipanggil saat submit
 */
function setupFormWithLoading(form, submitHandler) {
    if (!form) return;

    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        const submitButton = form.querySelector('button[type="submit"]');
        if (!submitButton) return;

        setButtonLoading(submitButton, true);

        try {
            await submitHandler(form);
        } catch (error) {
            console.error('Form submission error:', error);
        } finally {
            setButtonLoading(submitButton, false);
        }
    });
}

/**
 * Helper untuk membuat empty state di tabel
 * @param {HTMLElement} container - Container element (biasanya tbody atau div)
 * @param {Object} options - Options untuk empty state
 * @param {number} options.colspan - Jumlah kolom untuk colspan
 * @param {string} options.title - Judul empty state
 * @param {string} options.message - Pesan empty state
 * @param {string} options.icon - SVG icon (optional)
 */
function renderEmptyState(container, options = {}) {
    if (!container) return;

    const {
        colspan = 1,
        title = 'Tidak ada data',
        message = 'Belum ada data untuk ditampilkan.',
        icon = null
    } = options;

    // Jika container adalah tbody, buat tr
    if (container.tagName === 'TBODY') {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td colspan="${colspan}" class="px-6 py-12">
                <div class="text-center">
                    ${icon ? `<div class="inline-block mb-4">${icon}</div>` : `
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                        </svg>
                    `}
                    <h3 class="mt-2 text-sm font-medium text-gray-900">${title}</h3>
                    <p class="mt-1 text-sm text-gray-500">${message}</p>
                </div>
            </td>
        `;
        container.innerHTML = '';
        container.appendChild(tr);
    } else {
        // Jika container adalah div atau elemen lain
        container.innerHTML = `
            <div class="text-center py-12 px-4">
                ${icon ? `<div class="inline-block mb-4">${icon}</div>` : `
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                    </svg>
                `}
                <h3 class="mt-2 text-sm font-medium text-gray-900">${title}</h3>
                <p class="mt-1 text-sm text-gray-500">${message}</p>
            </div>
        `;
    }
}

// Export untuk digunakan di file lain (ES6 modules)
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { setButtonLoading, setupFormWithLoading, renderEmptyState };
}

// Expose ke global scope untuk digunakan di Blade template
window.setButtonLoading = setButtonLoading;
window.setupFormWithLoading = setupFormWithLoading;
window.renderEmptyState = renderEmptyState;
