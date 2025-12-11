# KampusPay STTP - Sistem Pembayaran Kampus

Sistem pembayaran kampus berbasis web yang memungkinkan manajemen pembayaran tagihan mahasiswa secara digital. Aplikasi ini menyediakan antarmuka untuk tiga role pengguna: Admin, Kasir, dan Mahasiswa.

## 📋 Deskripsi

KampusPay STTP adalah aplikasi web untuk mengelola pembayaran tagihan mahasiswa di kampus. Sistem ini memungkinkan:
- Admin mengelola data mahasiswa, tarif, tagihan, dan laporan
- Kasir membuat tagihan, memproses pembayaran tunai (full/cicilan), dan verifikasi transfer
- Mahasiswa melihat tagihan, melakukan pembayaran (tunai/transfer/cicilan), dan mengunduh kwitansi

## ✨ Fitur Utama

### 👨‍💼 Admin
- **Dashboard** - Overview statistik pembayaran dan registrasi
- **Manajemen Mahasiswa** - CRUD data mahasiswa (Create, Read, Update, Delete) dengan import/export Excel
- **Manajemen Tarif** - Kelola jenis pembayaran dan tarif
- **Manajemen Tagihan** - Buat, edit, dan hapus tagihan mahasiswa
- **Manajemen Pembayaran** - Lihat semua pembayaran yang telah diproses (termasuk cicilan)
- **Laporan** - Generate laporan pembayaran, tunggakan, dan mahasiswa dengan format PDF/Excel
- **Pengaturan Sistem** - Konfigurasi aplikasi (nama aplikasi, logo, dll)
- **Manajemen User** - Kelola user admin dan kasir

### 💰 Kasir
- **Dashboard** - Overview transaksi harian
- **Manajemen Tagihan** - Buat dan lihat tagihan mahasiswa (tidak bisa edit/hapus)
- **Transaksi** - Proses pembayaran tunai (full payment atau cicilan) dan lihat riwayat transaksi
- **Verifikasi Pembayaran** - Verifikasi pembayaran transfer dari mahasiswa (full atau cicilan)
- **Aktivasi Mahasiswa** - Kelola status aktivasi mahasiswa (Aktif/BSS) per semester
- **Laporan** - Generate laporan pembayaran, tunggakan, dan bulanan dengan export CSV/PDF
- **Pengaturan** - Ubah password

### 🎓 Mahasiswa
- **Dashboard** - Overview tagihan dan status pembayaran
- **Tagihan & Pembayaran** - Lihat tagihan dan pilih metode pembayaran (Tunai/Transfer/Cicilan)
- **Pembayaran Cicilan** - Bayar tagihan secara cicilan dengan minimum Rp 50.000 (kecuali sisa < 50.000)
- **Upload Bukti Transfer** - Upload bukti pembayaran untuk verifikasi (full atau cicilan)
- **Riwayat Pembayaran** - Lihat riwayat pembayaran yang telah dilakukan (termasuk cicilan)
- **Kwitansi** - Download kwitansi pembayaran dalam format PDF (menampilkan jumlah yang dibayar)
- **Laporan** - Download laporan histori pembayaran dan tunggakan dalam format PDF
- **Aktivasi Semester** - Pilih status aktivasi semester (Aktif/BSS) - hanya sekali, tidak bisa diubah
- **Profil** - Lihat dan update profil
- **Ubah Password** - Ganti password akun

## 🛠 Teknologi yang Digunakan

- **Framework**: Laravel 12.0
- **PHP**: ^8.2
- **Frontend**: 
  - Blade Templates
  - Tailwind CSS
  - JavaScript (Vanilla JS)
- **Database**: SQLite (default) / MySQL / PostgreSQL
- **Libraries**:
  - Laravel Sanctum (API Authentication)
  - DomPDF (PDF Generation)
  - Maatwebsite Excel (Excel Import/Export)
- **Testing**: Pest PHP

## 📦 Persyaratan Sistem

- PHP >= 8.2
- Composer
- Node.js & NPM (untuk asset compilation)
- SQLite (default) atau MySQL/PostgreSQL
- Web Server (Apache/Nginx) atau PHP Built-in Server

## 🚀 Instalasi

1. **Clone repository**
```bash
git clone https://github.com/afifatul562/KampusPay_STTP.git
cd KampusPay_STTP
```

2. **Install dependencies**
```bash
composer install
npm install
```

3. **Setup environment**
```bash
cp .env.example .env
php artisan key:generate
```

4. **Konfigurasi database**

   Edit file `.env` dan sesuaikan konfigurasi database:
```env
DB_CONNECTION=sqlite
# atau
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=kampuspay
DB_USERNAME=root
DB_PASSWORD=
```

   Jika menggunakan SQLite, pastikan file `database/database.sqlite` sudah ada:
```bash
touch database/database.sqlite
```

5. **Jalankan migration dan seeder**
```bash
php artisan migrate
php artisan db:seed
```

6. **Compile assets**
```bash
npm run build
# atau untuk development
npm run dev
```

7. **Buat storage link**
```bash
php artisan storage:link
```

8. **Jalankan aplikasi**
```bash
php artisan serve
```

Aplikasi akan berjalan di `http://localhost:8000`

## 👤 Default Login

Setelah menjalankan seeder, Anda dapat login dengan kredensial default:

### Admin
- **Username**: `admin`
- **Password**: `password123` (atau sesuai yang di-set di seeder)

### Kasir
- Buat akun kasir melalui menu Admin → Registrasi

### Mahasiswa
- Akun mahasiswa dibuat saat Admin mendaftarkan mahasiswa baru

## 🔐 Role & Permission

Aplikasi menggunakan sistem role-based access control dengan 3 role:

1. **Admin** - Akses penuh ke semua fitur (CRUD mahasiswa, tarif, tagihan, laporan, user)
2. **Kasir** - Akses untuk membuat tagihan, memproses pembayaran (full/cicilan), verifikasi transfer, dan generate laporan
3. **Mahasiswa** - Akses terbatas untuk melihat tagihan, melakukan pembayaran (full/cicilan), dan download laporan

## 💳 Fitur Cicilan Pembayaran

Sistem mendukung pembayaran tagihan secara cicilan dengan ketentuan:
- **Minimum cicilan**: Rp 50.000 (kecuali sisa pokok < 50.000)
- **Tagihan yang bisa dicicil**: Semua tagihan kecuali "Uang Kemahasiswaan" dan "Uang Ujian Akhir Semester" (wajib lunas)
- **Tidak ada bunga atau biaya admin**
- **Tidak perlu approval** untuk memilih opsi cicilan
- **Tracking otomatis**: Sistem mencatat total angsuran, sisa pokok, dan jumlah pembayaran per cicilan
- **Status tagihan**: Otomatis berubah dari "Belum Dibayarkan" → "Belum Lunas" → "Lunas"

## 📁 Struktur Project

```
KampusPay_STTP/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/          # Controller untuk Admin
│   │   │   ├── Kasir/          # Controller untuk Kasir
│   │   │   └── Mahasiswa/      # Controller untuk Mahasiswa
│   │   ├── Middleware/         # Custom middleware
│   │   └── Requests/           # Form request validation
│   ├── Models/                 # Eloquent models
│   ├── Policies/               # Authorization policies
│   ├── Services/               # Business logic services
│   └── Mail/                   # Email notifications
├── database/
│   ├── migrations/             # Database migrations
│   └── seeders/                # Database seeders
├── resources/
│   ├── views/                  # Blade templates
│   ├── css/                    # CSS files
│   └── js/                     # JavaScript files
├── routes/
│   ├── web.php                 # Web routes
│   └── api.php                 # API routes
└── tests/                      # Test files
```

## 🔧 Konfigurasi

### Email Configuration

Edit `.env` untuk mengkonfigurasi email (untuk notifikasi tagihan):

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@kampuspay.com
MAIL_FROM_NAME="${APP_NAME}"
```

### Storage Configuration

Pastikan folder storage memiliki permission yang tepat:

```bash
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

## 📝 Testing

Jalankan test suite dengan Pest:

```bash
php artisan test
```

## 🤝 Kontribusi

1. Fork repository
2. Buat feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit perubahan (`git commit -m 'Add some AmazingFeature'`)
4. Push ke branch (`git push origin feature/AmazingFeature`)
5. Buat Pull Request

## 📄 License

Aplikasi ini menggunakan lisensi [MIT](https://opensource.org/licenses/MIT).

## 👥 Authors

- **Afifatul Mawaddah** - [afifatul562](https://github.com/afifatul562)
- **Dinda Apriona Putri Adryan** - [dindaaprionaa](https://github.com/dindaaprionaa)

## 🙏 Acknowledgments

- Laravel Framework
- Tailwind CSS
- DomPDF
- Maatwebsite Excel

---

**Note**: Pastikan untuk mengubah kredensial default setelah instalasi di production!
