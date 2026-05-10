@extends('layouts.app')

@section('title', 'Admin - Pengaturan')
@section('page-title', 'Pengaturan Aplikasi')

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">

        {{-- Kolom Kiri: Form Pengaturan Sistem --}}
        <div class="lg:col-span-2 bg-white p-4 sm:p-5 md:p-6 rounded-xl shadow-md border border-gray-200">
            <h3 class="text-xl font-semibold mb-4 text-gray-800">⚙️ Pengaturan Sistem</h3>
            <form id="systemSettingsForm" class="space-y-4" aria-label="Form pengaturan sistem">
                {{-- Pengaturan Dasar Aplikasi --}}
                <div>
                    <label for="app_name" class="block text-sm font-medium text-gray-700">Nama Aplikasi</label>
                    <input type="text" id="app_name" name="app_name" value="{{ config('app.name', 'KampusPay') }}" class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 bg-gray-100 cursor-not-allowed" readonly disabled aria-readonly="true">
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="academic_year" class="block text-sm font-medium text-gray-700">Tahun Akademik</label>
                        <input type="text" id="academic_year" name="academic_year" placeholder="Contoh: 2025/2026" class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 bg-gray-100 cursor-not-allowed" readonly disabled aria-readonly="true">
                    </div>
                    <div>
                        <label for="semester" class="block text-sm font-medium text-gray-700">Semester Aktif</label>
                        <input type="text" id="semester" name="semester" class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 bg-gray-100 cursor-not-allowed" readonly disabled aria-readonly="true">
                    </div>
                    <p class="sm:col-span-2 text-xs text-gray-500 -mt-1">Nilai tahun ajaran dan semester diperbarui otomatis dari tanggal server saat Anda menyimpan pengaturan.</p>
                </div>
                <hr class="my-6">
                {{-- Pengaturan Rekening --}}
                <h4 class="text-lg font-semibold text-gray-800">Informasi Rekening Pembayaran</h4>
                <div>
                    <label for="bank_name" class="block text-sm font-medium text-gray-700">Nama Bank</label>
                    <input type="text" id="bank_name" name="bank_name" placeholder="Contoh: Bank Nagari" class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label for="account_holder" class="block text-sm font-medium text-gray-700">Atas Nama</label>
                    <input type="text" id="account_holder" name="account_holder" placeholder="Contoh: Yayasan Kampus STTP" class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label for="account_number" class="block text-sm font-medium text-gray-700">Nomor Rekening</label>
                    <input type="text" id="account_number" name="account_number" placeholder="Contoh: 1234567890" class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div class="pt-4">
                    <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-700 hover:to-primary-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 shadow-md hover:shadow-lg transition-all duration-200">
                        Simpan Pengaturan
                    </button>
                </div>
            </form>
        </div>

        {{-- Sejajar dengan Pengaturan Sistem (1/3 lebar) --}}
        <div class="lg:col-span-1 lg:self-start">
            <div class="bg-white p-4 sm:p-5 md:p-6 rounded-xl shadow-md border border-gray-200 h-full">
                <h3 class="text-xl font-semibold mb-4 text-gray-800">ℹ️ Informasi Sistem</h3>
                <div class="space-y-4 text-sm bg-gray-50 p-4 rounded-lg border" aria-live="polite">
                    <div class="flex justify-between gap-3"><span class="text-gray-600 shrink-0">Versi PHP:</span><strong id="php-version" class="font-mono text-gray-800 bg-gray-200 px-2 py-0.5 rounded text-right">...</strong></div>
                    <div class="flex justify-between gap-3"><span class="text-gray-600 shrink-0">Versi Laravel:</span><strong id="laravel-version" class="font-mono text-gray-800 bg-gray-200 px-2 py-0.5 rounded text-right">...</strong></div>
                    <div class="flex justify-between gap-3"><span class="text-gray-600 shrink-0">Database Driver:</span><strong id="database-driver" class="font-mono text-gray-800 bg-gray-200 px-2 py-0.5 rounded text-right">...</strong></div>
                    <div class="flex justify-between gap-3"><span class="text-gray-600 shrink-0">Waktu Server:</span><strong id="server-time" class="font-mono text-gray-800 bg-gray-200 px-2 py-0.5 rounded text-right break-all">...</strong></div>
                </div>
            </div>
        </div>

        {{-- Kalender Akademik --}}
        <div class="lg:col-span-2 bg-white p-4 sm:p-5 md:p-6 rounded-xl shadow-md border border-gray-200">
            <h3 class="text-xl font-semibold mb-4 text-gray-800">🗓️ Kalender Akademik</h3>
            <p class="text-sm text-gray-600 mb-4">
                Dipakai untuk aturan penagihan seperti “muncul sebelum UAS/UTS” atau “jatuh tempo sebelum UAS”.
            </p>

            <form id="academicCalendarForm" class="grid grid-cols-1 md:grid-cols-2 gap-4" aria-label="Form kalender akademik">
                <div class="md:col-span-2">
                    <label for="cal_semester_label" class="block text-sm font-medium text-gray-700">Semester Label</label>
                    <input type="text" id="cal_semester_label" name="semester_label"
                           value="{{ \App\Models\Setting::currentSemesterLabel() }}"
                           placeholder="Mengikuti CURRENT_SEMESTER"
                           readonly
                           class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 bg-gray-100 cursor-default">
                    {{-- <p class="mt-1 text-xs text-gray-500">Otomatis mengikuti Pengaturan Sistem (Tahun Akademik + Semester).</p> --}}
                </div>

                <div>
                    <label for="cal_semester_start_at" class="block text-sm font-medium text-gray-700">Mulai Semester</label>
                    <input type="text" id="cal_semester_start_at" name="semester_start_at" inputmode="none" autocomplete="off" placeholder="dd/mm/yyyy"
                           class="mt-1 block w-full min-h-touch border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label for="cal_uts_start_at" class="block text-sm font-medium text-gray-700">Mulai UTS</label>
                    <input type="text" id="cal_uts_start_at" name="uts_start_at" inputmode="none" autocomplete="off" placeholder="dd/mm/yyyy"
                           class="mt-1 block w-full min-h-touch border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label for="cal_uas_start_at" class="block text-sm font-medium text-gray-700">Mulai UAS</label>
                    <input type="text" id="cal_uas_start_at" name="uas_start_at" inputmode="none" autocomplete="off" placeholder="dd/mm/yyyy"
                           class="mt-1 block w-full min-h-touch border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div class="md:col-span-2 pt-2 flex justify-end gap-3">
                    <button type="button" id="calendarReloadBtn" class="px-4 py-2 bg-white border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Muat Ulang dari server
                    </button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-blue-700">
                        Simpan Kalender
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
        const apiRequest = (window.App && window.App.apiRequest) ? window.App.apiRequest : null;
        if (!apiRequest) {
            console.error('apiRequest util tidak tersedia');
            return;
        }

        function displayValidationErrors(errors) {
            const errorContent = document.createElement('div');
            const p = document.createElement('p');
            p.textContent = 'Input tidak valid:';
            errorContent.appendChild(p);
            const ul = document.createElement('ul');
            ul.className = 'list-disc list-inside text-left mt-2';
            Object.values(errors).forEach(e => {
                const li = document.createElement('li');
                li.textContent = e.join(', ');
                ul.appendChild(li);
            });
            errorContent.appendChild(ul);
            Swal.fire({ icon: 'error', title: 'Gagal', html: errorContent });
        }

        function loadSystemInfo() {
            apiRequest("{{ route('admin.system-info') }}").then(response => {
                const data = response.data;
                document.getElementById('php-version').textContent = data.php_version;
                document.getElementById('laravel-version').textContent = data.laravel_version;
                document.getElementById('database-driver').textContent = data.database;
                document.getElementById('server-time').textContent = data.server_time;
            }).catch(error => {
                console.error('Error fetching system info:', error);
                document.getElementById('php-version').textContent = 'Error';
                document.getElementById('laravel-version').textContent = 'Error';
                document.getElementById('database-driver').textContent = 'Error';
                document.getElementById('server-time').textContent = 'Error';
            });
        }

        function syncCalendarSemesterLabelFromSettings(data) {
            const calEl = document.getElementById('cal_semester_label');
            if (!calEl || !data) return;
            const y = (data.academic_year || '').toString().trim();
            const s = (data.semester || '').toString().trim();
            const label = (y + ' ' + s).trim();
            if (label) calEl.value = label;
        }

        function loadSettings() {
            return apiRequest("{{ route('admin.settings.system.show') }}").then(response => {
                const data = response.data;
                document.getElementById('app_name').value = data.app_name || "{{ config('app.name', 'KampusPay') }}";
                document.getElementById('academic_year').value = data.academic_year || '';
                document.getElementById('semester').value = data.semester || '';
                document.getElementById('bank_name').value = data.bank_name || '';
                document.getElementById('account_holder').value = data.account_holder || '';
                document.getElementById('account_number').value = data.account_number || '';
                syncCalendarSemesterLabelFromSettings(data);
            }).catch(error => {
                console.error('Error fetching settings:', error);
                Swal.fire({ icon: 'error', title: 'Gagal Muat', text: 'Gagal memuat pengaturan awal.' });
                return Promise.reject(error);
            });
        }

        const systemSettingsForm = document.getElementById('systemSettingsForm');
        if (systemSettingsForm) {
            systemSettingsForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const url = "{{ route('admin.settings.system.update') }}";
                const data = {
                    app_name: document.getElementById('app_name').value,
                    academic_year: document.getElementById('academic_year').value,
                    semester: document.getElementById('semester').value,
                    bank_name: document.getElementById('bank_name').value,
                    account_holder: document.getElementById('account_holder').value,
                    account_number: document.getElementById('account_number').value,
                };
                const submitButton = this.querySelector('button[type="submit"]');
                const originalButtonText = 'Simpan Pengaturan';
                submitButton.innerHTML = 'Menyimpan...';
                submitButton.disabled = true;

                apiRequest(url, 'POST', data)
                    .then(response => {
                        Swal.fire({ icon: 'success', title: 'Berhasil!', text: response.message || 'Pengaturan berhasil disimpan.', timer: 1500, showConfirmButton: false });
                        return loadSettings().catch(e => {
                            console.error('Muat ulang pengaturan setelah simpan gagal:', e);
                        });
                    })
                    .then(() => loadCalendarIntoForm())
                    .catch(err => {
                        if (err && err.status === 422 && err.errors) {
                            displayValidationErrors(err.errors);
                        } else if (err) {
                            Swal.fire({ icon: 'error', title: 'Gagal Menyimpan', text: 'Terjadi kesalahan: ' + (err.message || 'Error tidak diketahui') });
                        }
                    })
                    .finally(() => {
                        submitButton.innerHTML = originalButtonText;
                        submitButton.disabled = false;
                    });
            });
        }

        loadSystemInfo();

        const initialSemesterLabel = @json(\App\Models\Setting::currentSemesterLabel());
        function getCalendarSemesterLabel() {
            const el = document.getElementById('cal_semester_label');
            const v = el && el.value ? el.value.trim() : '';
            return v || initialSemesterLabel;
        }
        const calendarForm = document.getElementById('academicCalendarForm');
        const reloadBtn = document.getElementById('calendarReloadBtn');
        const calFieldIds = ['cal_semester_start_at', 'cal_uts_start_at', 'cal_uas_start_at'];
        const calPickers = {};

        function initAcademicCalendarFlatpickrs() {
            if (!window.flatpickr) {
                console.warn('Flatpickr tidak tersedia');
                return;
            }
            if (flatpickr.l10ns && flatpickr.l10ns.id) {
                flatpickr.localize(flatpickr.l10ns.id);
            }
            const idLoc = (flatpickr.l10ns && flatpickr.l10ns.id) ? Object.assign({}, flatpickr.l10ns.id) : {};
            const localeOpts = Object.assign({}, idLoc, { firstDayOfWeek: 1 });
            calFieldIds.forEach(id => {
                const el = document.getElementById(id);
                if (!el || calPickers[id]) return;
                calPickers[id] = flatpickr(el, {
                    dateFormat: 'd/m/Y',
                    locale: localeOpts,
                    allowInput: false,
                    disableMobile: true,
                    defaultDate: null,
                    onReady(_d, _s, inst) {
                        inst.input.placeholder = 'dd/mm/yyyy';
                    },
                    onOpen(_d, _s, inst) {
                        inst.input.placeholder = 'dd/mm/yyyy';
                    },
                    onClose(_d, _s, inst) {
                        if (!inst.input.value) inst.input.placeholder = 'dd/mm/yyyy';
                    },
                });
            });
        }

        function setCalFieldFromYmd(fieldId, ymd) {
            const fp = calPickers[fieldId];
            if (!fp) {
                const el = document.getElementById(fieldId);
                if (el) el.value = '';
                return;
            }
            if (!ymd || String(ymd).length < 10) {
                fp.clear();
                return;
            }
            fp.setDate(String(ymd).substring(0, 10), false);
        }

        function ymdFromCalField(fieldId) {
            const fp = calPickers[fieldId];
            if (!fp || !fp.selectedDates || !fp.selectedDates.length) return null;
            const d = fp.selectedDates[0];
            return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
        }

        function applyCalendarRowToForm(r) {
            const calLabelEl = document.getElementById('cal_semester_label');
            if (calLabelEl) calLabelEl.value = getCalendarSemesterLabel();
            if (!r) {
                calFieldIds.forEach(id => setCalFieldFromYmd(id, null));
                return;
            }
            setCalFieldFromYmd('cal_semester_start_at', r.semester_start_at);
            setCalFieldFromYmd('cal_uts_start_at', r.uts_start_at);
            setCalFieldFromYmd('cal_uas_start_at', r.uas_start_at);
        }

        function loadCalendarIntoForm() {
            apiRequest("{{ route('admin.academic-calendar.index') }}")
                .then(res => {
                    const rows = res.data || [];
                    const match = rows.find(x => String(x.semester_label) === String(getCalendarSemesterLabel()));
                    applyCalendarRowToForm(match || null);
                })
                .catch(err => {
                    console.error('Gagal load calendar:', err);
                    Swal.fire({ icon: 'error', title: 'Gagal memuat', text: err.message || 'Tidak bisa memuat kalender dari server.', toast: true, position: 'top-end', timer: 2500, showConfirmButton: false });
                });
        }

        if (reloadBtn) {
            reloadBtn.addEventListener('click', loadCalendarIntoForm);
        }

        if (calendarForm) {
            calendarForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const payload = {
                    semester_label: getCalendarSemesterLabel(),
                    semester_start_at: ymdFromCalField('cal_semester_start_at'),
                    uts_start_at: ymdFromCalField('cal_uts_start_at'),
                    uas_start_at: ymdFromCalField('cal_uas_start_at'),
                };

                apiRequest("{{ route('admin.academic-calendar.upsert') }}", 'POST', payload)
                    .then(() => {
                        Swal.fire({ icon: 'success', title: 'Berhasil!', text: 'Kalender tersimpan.', timer: 1500, showConfirmButton: false });
                        loadCalendarIntoForm();
                    })
                    .catch(err => {
                        if (err && err.status === 422 && err.errors) {
                            displayValidationErrors(err.errors);
                        } else {
                            Swal.fire({ icon: 'error', title: 'Gagal', text: (err && err.message) ? err.message : 'Gagal menyimpan kalender.' });
                        }
                    });
            });
        }

        initAcademicCalendarFlatpickrs();
        loadSettings().finally(() => loadCalendarIntoForm());
    });
</script>
@endpush
