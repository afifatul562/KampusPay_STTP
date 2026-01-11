import "./bootstrap";
import Alpine from "alpinejs";
import { apiRequest } from "./utils/api";
import "./button-helpers";

window.Alpine = Alpine;
Alpine.start();

document.addEventListener("DOMContentLoaded", function () {
    // Tambahkan CSRF token ke semua fetch request
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

    // Tandai notifikasi sebagai sudah dibaca saat diklik
    const notificationItems = document.querySelectorAll('.notification-item');
    if (notificationItems && notificationItems.length > 0) {
        notificationItems.forEach((item) => {
            item.addEventListener('click', function () {
                const notificationId = this.dataset.id;
                if (!notificationId) return;
                const url = `/notifications/${notificationId}/mark-as-read`;

                if (this.classList.contains('bg-blue-50')) {
                    fetch(url, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json'
                        }
                    })
                        .then((response) => response.ok ? response.json() : { success: false })
                        .then((data) => {
                            if (data && data.success) {
                                this.classList.remove('bg-blue-50', 'font-semibold');
                                const ts = this.querySelector('.text-xs');
                                if (ts) ts.classList.remove('font-normal');
                            }
                        })
                        .catch(() => {});
                }
            });
        });
    }

    // Default dan helper SweetAlert2 global (tampilan seragam)
    try {
        if (window.Swal && typeof Swal.mixin === 'function') {
            const DefaultAlert = Swal.mixin({
                buttonsStyling: false,
                showConfirmButton: true,
                customClass: {
                    confirmButton: 'inline-flex items-center px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700',
                    cancelButton: 'inline-flex items-center px-4 py-2 rounded-lg bg-white border border-gray-300 text-sm font-medium text-gray-700 hover:bg-gray-50',
                    denyButton: 'inline-flex items-center px-4 py-2 rounded-lg bg-red-600 text-white text-sm font-semibold hover:bg-red-700',
                    popup: 'rounded-xl',
                    actions: 'gap-2'
                }
            });
            // Override default fire agar semua Swal.fire() menggunakan mixin kita
            Swal.fire = DefaultAlert.fire.bind(DefaultAlert);

            // Helper ringan
            const success = (title = 'Berhasil', text = '') => Swal.fire({ icon: 'success', title, text });
            const error = (title = 'Gagal', text = '') => Swal.fire({ icon: 'error', title, text });
            const info = (title = 'Info', text = '') => Swal.fire({ icon: 'info', title, text });
            const confirm = async (title = 'Anda Yakin?', text = 'Tindakan ini tidak dapat dibatalkan.', confirmText = 'Ya') => {
                const res = await Swal.fire({ icon: 'warning', title, text, showCancelButton: true, confirmButtonText: confirmText, cancelButtonText: 'Batal' });
                return !!res.isConfirmed;
            };
            window.App = Object.assign({}, window.App || {}, {
                alert: { success, error, info, confirm }
            });
        }
    } catch (e) { /* abaikan */ }
});

// Ekspos helper aplikasi global
window.App = Object.assign({}, window.App || {}, { apiRequest });
