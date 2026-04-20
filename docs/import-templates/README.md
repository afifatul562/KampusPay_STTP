# Template Import Data (Sistem Lama → KampusPay)

Folder ini berisi contoh template CSV yang bisa diisi oleh **kasir** dari sistem lama, lalu diimpor ke KampusPay.

## Gambaran singkat

- **`mahasiswa_template.csv`**: daftar mahasiswa (identitas & status)
- **`tagihan_template.csv`**: daftar tagihan per mahasiswa
- **`pembayaran_template.csv`**: histori transaksi pembayaran (untuk menu riwayat/kwitansi)

## Kunci agar data “nyambung”

1. **`npm`** harus unik dan konsisten di semua file.
2. **`kode_pembayaran`** harus unik per tagihan dan:
   - muncul di `tagihan_template.csv`, dan
   - dipakai juga di `pembayaran_template.csv` untuk mengaitkan transaksi ke tagihan.

> Jika sistem lama tidak punya `kode_pembayaran`, kasir boleh membuat sendiri dengan format bebas, yang penting **unik** dan **konsisten**.
> Contoh: `KMP-2026-221013001-UKM-001`.

## Aturan format (disarankan)

- **File**: CSV (UTF-8), separator koma `,`.
- **Tanggal**: `YYYY-MM-DD` (contoh `2026-04-20`).
- **Angka**: tanpa pemisah ribuan (contoh `4000000`, bukan `4.000.000`).

## Detail kolom

### 1) `mahasiswa_template.csv`

Kolom:
- `npm` (wajib)
- `nama_lengkap` (wajib)
- `program_studi` (wajib)
- `angkatan` (wajib, tahun penuh: 2021–2025)
- `semester_aktif` (wajib, angka)
- `status` (wajib, contoh: `Aktif`)
- `email` (opsional, boleh kosong)

### 2) `tagihan_template.csv`

Kolom:
- `kode_pembayaran` (wajib, unik)
- `npm` (wajib)
- `nama_pembayaran` (wajib; usahakan konsisten dengan master tarif KampusPay)
- `jumlah_tagihan` (wajib, angka)
- `tanggal_jatuh_tempo` (wajib, `YYYY-MM-DD`)
- `status` (wajib; contoh: `Lunas`, `Belum Dibayarkan`, `Belum Lunas`, `Menunggu Verifikasi Transfer`, `Menunggu Pembayaran Tunai`, `Ditolak`)
- `semester_label` (opsional; contoh: `2025/2026 Genap`)
- `keterangan` (opsional)

### 3) `pembayaran_template.csv`

Kolom:
- `kode_pembayaran` (wajib; harus ada di file tagihan)
- `tanggal_bayar` (wajib)
- `metode_pembayaran` (wajib; contoh: `Tunai`, `Transfer`)
- `jumlah_bayar` (wajib, angka)
- `is_cicilan` (opsional; `0` atau `1`)
- `status_dibatalkan` (opsional; `0` atau `1`)
- `alasan_pembatalan` (opsional)
- `kasir_username` (opsional)

Catatan cicilan:
- Jika satu tagihan dibayar cicilan, maka `pembayaran_template.csv` boleh memiliki **beberapa baris** dengan `kode_pembayaran` yang sama.

## Langkah kerja yang aman

1. Siapkan & isi 3 file template.
2. Pastikan tidak ada `npm` kosong dan `kode_pembayaran` duplikat di file tagihan.
3. Pastikan semua `kode_pembayaran` yang ada di pembayaran **juga ada** di file tagihan.

