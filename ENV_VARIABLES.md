# Environment Variables Documentation

## Environment Variables yang Tersedia

### Admin Configuration

**`ADMIN_DEFAULT_PASSWORD`**
- **Deskripsi:** Password default untuk admin user saat seeder dijalankan
- **Default:** `password123`
- **Contoh:** `ADMIN_DEFAULT_PASSWORD=SecurePassword123!`
- **Catatan:** Pastikan menggunakan password yang kuat di production!

### Sanctum Token Configuration

**`SANCTUM_TOKEN_EXPIRATION`**
- **Deskripsi:** Lama waktu token API akan expired (dalam menit)
- **Default:** `1440` (24 jam)
- **Contoh:** 
  - `SANCTUM_TOKEN_EXPIRATION=60` (1 jam)
  - `SANCTUM_TOKEN_EXPIRATION=1440` (24 jam)
  - `SANCTUM_TOKEN_EXPIRATION=10080` (7 hari)
- **Catatan:** Set `null` untuk disable expiration (tidak direkomendasikan untuk production)

### Sanctum Stateful Domains

**`SANCTUM_STATEFUL_DOMAINS`**
- **Deskripsi:** Daftar domain yang akan menerima stateful API authentication cookies
- **Default:** `localhost,localhost:3000,127.0.0.1,127.0.0.1:8000,::1`
- **Contoh:** `SANCTUM_STATEFUL_DOMAINS=localhost,example.com,app.example.com`

### Sanctum Token Prefix

**`SANCTUM_TOKEN_PREFIX`**
- **Deskripsi:** Prefix untuk token Sanctum (untuk security scanning)
- **Default:** `` (kosong)
- **Contoh:** `SANCTUM_TOKEN_PREFIX=spk_`

---

## Cara Menggunakan

### 1. Tambahkan ke `.env` file

```env
# Admin Configuration
ADMIN_DEFAULT_PASSWORD=SecurePassword123!

# Sanctum Configuration
SANCTUM_TOKEN_EXPIRATION=1440
SANCTUM_STATEFUL_DOMAINS=localhost,app.example.com
SANCTUM_TOKEN_PREFIX=
```

### 2. Clear config cache (jika perlu)

```bash
php artisan config:clear
php artisan config:cache
```

---

## Security Best Practices

1. **Jangan hardcode password** - Selalu gunakan environment variables
2. **Gunakan password yang kuat** - Minimal 12 karakter, kombinasi huruf-angka-simbol
3. **Set token expiration** - Jangan biarkan token tidak pernah expire
4. **Jangan commit `.env`** - Pastikan `.env` ada di `.gitignore`

