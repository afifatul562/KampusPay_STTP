# KampusPay STTP

Sistem pembayaran kampus untuk **Sekolah Tinggi Teknologi Payakumbuh**. Mahasiswa membayar dan memantau tagihan secara daring; admin dan kasir mengelola data, verifikasi, serta laporan keuangan.

**Repository:** [github.com/afifatul562/KampusPay_STTP](https://github.com/afifatul562/KampusPay_STTP)

---

## Fitur

### Admin
- Dashboard statistik keuangan
- Kelola mahasiswa (tambah, edit, import/export CSV)
- Kelola tarif, tagihan, dan kalender akademik
- Import data legacy (tagihan & pembayaran)
- Laporan pembayaran & tunggakan (PDF)
- Kelola akun kasir dan yayasan

### Kasir
- Verifikasi bukti transfer mahasiswa
- Pembayaran tunai di loket
- Aktivasi semester mahasiswa
- Riwayat transaksi, pembatalan, dan cetak kwitansi
- Laporan (PDF/CSV)

### Mahasiswa
- Lihat tagihan aktif dan riwayat pembayaran
- Bayar tunai atau transfer (upload bukti)
- Cicilan tagihan yang diizinkan
- Download kwitansi & laporan (histori/tunggakan)
- Request aktivasi semester
- Pendaftaran wisuda (jika memenuhi syarat)
- Notifikasi tagihan (web & push FCM via API mobile)

### Yayasan
- Dashboard monitoring (read-only)
- Laporan tunggakan (PDF)

### API Mobile (Mahasiswa)
- Autentikasi Sanctum
- Tagihan, pembayaran, riwayat, profil, kwitansi, laporan, wisuda
- Registrasi token FCM untuk notifikasi push

---

## Teknologi

| Komponen | Stack |
| --- | --- |
| Backend | PHP 8.2+, Laravel 12, Sanctum, Breeze |
| Frontend | Tailwind CSS, Alpine.js, Vite |
| Database | SQLite (dev), MySQL/MariaDB (prod) |
| Lainnya | DomPDF, Laravel Excel, Firebase FCM |

---

## Instalasi

**Prasyarat:** PHP 8.2+, Composer, Node.js 18+

```bash
git clone https://github.com/afifatul562/KampusPay_STTP.git
cd KampusPay_STTP

composer install
npm install

cp .env.example .env
php artisan key:generate

touch database/database.sqlite   # jika pakai SQLite
php artisan migrate --seed
php artisan storage:link
```

Jalankan:

```bash
php artisan serve      # terminal 1
npm run dev            # terminal 2
```

Buka `http://localhost:8000`

> **Penting:** Setelah `migrate --seed`, ganti password default sebelum dipakai di production. Jangan gunakan kredensial bawaan di lingkungan live.

---

## Konfigurasi `.env`

| Key | Keterangan |
| --- | --- |
| `DB_CONNECTION` | `sqlite` (dev) atau `mysql` (prod) |
| `APP_URL` | URL aplikasi (harus sama dengan browser) |
| `APP_PUBLIC_URL` | URL publik untuk link di email (prod) |
| `MAIL_*` | SMTP untuk email notifikasi tagihan |
| `FIREBASE_CREDENTIALS` | Path JSON service account Firebase (push notifikasi) |
| `MAHASISWA_DEFAULT_PASSWORD` | Password awal mahasiswa hasil import |
| `KASIR_DEFAULT_PASSWORD` | Password awal akun kasir |
| `YAYASAN_DEFAULT_PASSWORD` | Password awal akun yayasan |

Untuk push notifikasi, letakkan file service account di `storage/firebase/service-account.json` (folder ini tidak di-commit).

---

## Testing

```bash
php artisan test
```

---

## Author

- **Afifatul Mawaddah** — [afifatul562](https://github.com/afifatul562)

---

## Lisensi

MIT License — lihat file `LICENSE`.
