# TODO: Fix Profile Display Issue - Name and Email Showing as N/A

## Steps to Complete:

1. **Update User Model (app/Models/User.php)**

    - Add hasOne relationship to MahasiswaDetail model to load student details.
    - This enables accessing $user->mahasiswaDetail->npm, etc., in views.

2. **Add Route for Mahasiswa Profile (routes/web.php)**

    - Insert new GET route '/mahasiswa/profil' under 'auth' and 'checkrole:mahasiswa' middleware.
    - Use closure to fetch $user = auth()->user() and $detail = $user->mahasiswaDetail.
    - Return view('mahasiswa.profil', compact('user', 'detail')).

3. **Update Profil View to Dynamic (resources/views/mahasiswa/profil.blade.php)**

    - Replace hardcoded values in "Informasi Pribadi" section with Blade syntax:
        - Nama Lengkap: {{ $user->name ?? 'N/A' }} (uses accessor for nama_lengkap).
        - Email: {{ $user->email ?? 'N/A' }}.
        - NPM: {{ $detail->npm ?? 'N/A' }}.
        - Program Studi: {{ $detail->program_studi ?? 'N/A' }}.
        - Angkatan: {{ $detail->angkatan ?? 'N/A' }}.
        - Semester Aktif: {{ $detail->semester_aktif ?? 'N/A' }}.
    - Ensure navigation links to the new route if needed.

4. **Clear Caches**

    - Run: php artisan route:clear, php artisan view:clear, php artisan cache:clear.
    - Verify with php artisan route:list | grep profil.

5. **Test the Fix**
    - Login as mahasiswa user with filled 'nama_lengkap' and 'email' in DB, and existing MahasiswaDetail record.
    - Navigate to /mahasiswa/profil and confirm name/email display correctly, not N/A.
    - If MahasiswaDetail missing, create one via admin panel or seeder.

## Progress:

-   [x] Step 1: Update User Model
-   [x] Step 2: Add Route
-   [x] Step 3: Update View
-   [x] Step 4: Clear Caches
-   [ ] Step 5: Test

**Notes:**

-   'name' di view merujuk ke accessor di User model yang mengambil dari kolom 'nama_lengkap'. Pastikan data di DB terisi (cek via php artisan tinker: User::find(1)->nama_lengkap).
-   Jika ringkasan keuangan perlu dinamis, tambah query di route (e.g., $payments = $user->payments()->sum('amount')), tapi fokus pada name/email dulu.
-   Setelah selesai, update TODO.md dengan [x] untuk steps yang done.
