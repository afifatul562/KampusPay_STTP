# 🎓 KampusPay STTP

[![Laravel Version](https://img.shields.io/badge/Laravel-12.0-red?style=flat-square&logo=laravel)](https://laravel.com)
[![PHP Version](https://img.shields.io/badge/PHP-8.2%2B-blue?style=flat-square&logo=php)](https://www.php.net)
[![License](https://img.shields.io/badge/License-MIT-green?style=flat-square)](LICENSE)
[![Author](https://img.shields.io/badge/Maintainer-afifatul562-black?style=flat-square&logo=github)](https://github.com/afifatul562)

**KampusPay STTP** adalah sistem manajemen pembayaran non-tunai yang dirancang khusus untuk digitalisasi proses administrasi keuangan di lingkungan **Sekolah Tinggi Teknologi Payakumbuh**. Aplikasi ini mendukung pembayaran fleksibel, sistem cicilan, dan verifikasi otomatis.

🔗 **Repository:** [https://github.com/afifatul562/KampusPay_STTP](https://github.com/afifatul562/KampusPay_STTP)

---

## 📌 Daftar Isi
- [📖 Deskripsi / Overview](#-deskripsi--overview)
- [✨ Fitur Utama](#-fitur-utama)
- [🛠 Teknologi](#-teknologi-yang-digunakan)
- [📁 Struktur Direktori](#-struktur-direktori)
- [🚀 Instalasi & Setup](#-cara-instalasi--menjalankan-proyek)
- [⚙️ Konfigurasi](#️-konfigurasi)
- [🧪 Testing](#-testing)
- [👨‍💻 Author](#-author)

---

## 📖 Deskripsi / Overview
Sistem ini hadir untuk menggantikan pengelolaan pembayaran manual. Dengan KampusPay, mahasiswa dapat memantau tagihan secara *real-time*, melakukan pembayaran via transfer, dan mengelola administrasi semester secara mandiri.

**Ruang Lingkup:**
* Pengelolaan data mahasiswa & tarif (Uang Semester, BSS, dll).
* Sistem cicilan tagihan yang fleksibel.
* Verifikasi pembayaran oleh kasir secara digital.
* Pelaporan keuangan otomatis (PDF/CSV).

---

## ✨ Fitur Utama

### 👨‍💼 Admin (Manajer Sistem)
* **Dashboard:** Statistik keuangan & registrasi mahasiswa.
* **Master Data:** CRUD Mahasiswa (Import/Export CSV) & Tarif Pembayaran.
* **Reporting:** Laporan tunggakan & pembayaran (PDF).
* **Manajemen User:** Pengaturan akses untuk akun Kasir.

### 💰 Kasir (Operasional)
* **Verifikasi:** Persetujuan atau penolakan bukti transfer mahasiswa.
* **Pembayaran Tunai:** Proses pembayaran langsung di loket kampus.
* **Aktivasi:** Melakukan override status aktivasi semester.
* **Log Transaksi:** Riwayat lengkap & fitur pembatalan transaksi.

### 🎓 Mahasiswa (User)
* **Finance Hub:** Cek tagihan aktif & riwayat pembayaran lengkap.
* **Payment:** Pilih metode (Tunai/Transfer) & Upload bukti bayar.
* **Cicilan:** Pembayaran bertahap untuk tagihan yang diizinkan.
* **Self-Service:** Download kwitansi PDF & Request aktivasi semester.

---

## 🛠 Teknologi yang Digunakan

| Komponen | Teknologi |
| :--- | :--- |
| **Backend** | PHP 8.2+, Laravel 12.0 (Sanctum, Breeze) |
| **Frontend** | Tailwind CSS 3.4, Alpine.js 3.4, Vite 7.0 |
| **Database** | SQLite (Dev), MySQL/MariaDB (Prod) |
| **Library** | DomPDF, Laravel Excel, SweetAlert2, Flatpickr |
| **Testing** | Pest PHP 4.1, PHPUnit |

---

## 📁 Struktur Direktori
```text
KampusPay_STTP/
├── app/               # Logic: Controllers, Models, Services, Exports
├── database/          # Migrations, Seeders, & SQLite DB
├── resources/         # Frontend: Views (Blade), CSS (Tailwind), JS
├── routes/            # Web, API, & Auth Routes
├── storage/           # Logs, Bukti Transfer, & Generated PDF
└── tests/             # Pest & Unit Testing

```

---

## 🚀 Cara Instalasi & Menjalankan Proyek

### 1. Persiapan

Pastikan Anda memiliki **PHP 8.2+**, **Composer**, dan **Node.js 18+**.

### 2. Langkah Instalasi

```bash
# Clone repository
git clone [https://github.com/afifatul562/KampusPay_STTP.git](https://github.com/afifatul562/KampusPay_STTP.git)
cd KampusPay_STTP

# Install dependensi
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Database & Storage
touch database/database.sqlite  # Jika menggunakan SQLite
php artisan migrate --seed
php artisan storage:link

```

### 3. Menjalankan Aplikasi

```bash
# Terminal 1: Laravel Server
php artisan serve

# Terminal 2: Vite (Frontend)
npm run dev

```

Akses di: `http://localhost:8000` | Akun Default: `admin` / `password123`

---

## ⚙️ Konfigurasi `.env` Penting

| Key | Deskripsi |
| --- | --- |
| `DB_CONNECTION` | Gunakan `sqlite` (dev) atau `mysql` (prod). |
| `PAYMENT_CODE_PREFIX` | Default: `KP`. Prefix untuk kode invoice. |
| `CURRENT_SEMESTER` | Mengatur periode semester aktif saat ini. |
| `BSS_AMOUNT` | Nominal standar biaya studi semester. |

---

## 🧪 Testing

Aplikasi telah melalui pengujian menggunakan **Pest PHP**.

```bash
php artisan test

```

---

## 👨‍💻 Author

**Afifatul** GitHub: [@afifatul562](https://github.com/afifatul562)

---

## 📄 Lisensi

Didistribusikan di bawah **MIT License**. Lihat file `LICENSE` untuk informasi lebih lanjut.

**Selamat menggunakan KampusPay STTP! 🎓💳**
