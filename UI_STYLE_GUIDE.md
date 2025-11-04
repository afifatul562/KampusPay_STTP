# UI Style Guide (Frontend Konsistensi)

## 1. Tipografi & Heading
- Title halaman: `text-2xl font-bold text-gray-900`
- Subjudul/Section: `text-lg font-semibold text-gray-800`
- Teks sekunder: `text-sm text-gray-500`

## 2. Kartu & Container
- Kartu utama: `bg-white p-6 rounded-xl shadow-sm border border-gray-200`
- Grid responsif: gunakan `grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6`
- Section header: `bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-5 border-b border-gray-200`

## 3. Tabel
- Wrapper: `<div class="overflow-x-auto">`
- Tabel: `min-w-full divide-y divide-gray-200`
- Thead: `bg-gray-50`
- Sel header: `px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider`
- Body: `bg-white divide-y divide-gray-200 text-sm`
- Tambahkan `aria-label` deskriptif di `<table>`

## 4. Tombol
- Primary: `inline-flex items-center justify-center px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700`
- Secondary: `inline-flex items-center px-4 py-2 rounded-lg bg-indigo-100 text-indigo-700 text-sm font-semibold hover:bg-indigo-200`
- Danger: `inline-flex items-center px-4 py-2 rounded-lg bg-red-600 text-white text-sm font-semibold hover:bg-red-700`
- Ghost/Muted: `inline-flex items-center px-4 py-2 rounded-lg bg-gray-100 text-gray-700 text-sm font-semibold hover:bg-gray-200`

## 5. Form & Input
- Input: `block w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500`
- Label: `block text-sm font-medium text-gray-700`
- Spacing antar field: `space-y-4`

## 6. Badge Status
- Lunas: `bg-green-100 text-green-800`
- Menunggu Verifikasi: `bg-yellow-100 text-yellow-800`
- Menunggu Tunai: `bg-blue-100 text-blue-800`
- Ditolak: `bg-orange-100 text-orange-800`
- Dibatalkan/Belum Lunas: `bg-red-100 text-red-800`

## 7. Alert
- Sukses: `flex items-center p-4 text-sm text-green-800 rounded-lg bg-green-100 border-l-4 border-green-500`
- Error: `flex items-center p-4 text-sm text-red-800 rounded-lg bg-red-100 border-l-4 border-red-500`

## 8. Aksesibilitas
- Tambahkan `aria-label` pada link/aksi ikonik.
- Tabel diberi `aria-label` deskriptif.
- Gunakan `aria-live="polite"` untuk area yang dinamis (preview/loader).

## 9. Ikon & Spasi Ikon
- Ukuran ikon di tombol: `w-4 h-4` (kartu `w-6 h-6`/`w-8 h-8`)
- Jarak ikon-teks: `mr-2` atau `ml-2`

## 10. Skeleton Loading
- Gunakan `animate-pulse` pada placeholder: `bg-gray-200 rounded`

## 11. Konvensi Kelas
- Urutan: warna → ukuran teks → font → margin/padding → layout.
- Hindari mencampur `hidden` dan `flex` bersamaan—toggle class lewat JS.

## 12. Komponen Ulang
- Ekstrak komponen Blade bila pola berulang (kartu ringkas, alert, badge).


