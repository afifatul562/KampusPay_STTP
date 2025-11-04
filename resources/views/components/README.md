# Komponen Reusable - KampusPay STTP

Dokumentasi penggunaan komponen reusable untuk menjaga konsistensi UI/UX di seluruh aplikasi.

## 📦 Daftar Komponen

### 1. Page Header (`page-header.blade.php`)

Header konsisten untuk setiap halaman dengan icon dan title.

**Props:**
- `title` (required) - Judul halaman
- `subtitle` (optional) - Subtitle/deskripsi
- `icon` (optional) - SVG icon untuk header

**Slot:**
- `actions` (optional) - Tombol aksi di sebelah kanan header

**Contoh Penggunaan:**
```blade
<x-page-header 
    title="Daftar Mahasiswa" 
    subtitle="Kelola data mahasiswa"
    :icon='<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
    </svg>'>
    <x-slot:actions>
        <x-gradient-button variant="primary" size="md">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Tambah Baru
        </x-gradient-button>
    </x-slot:actions>
</x-page-header>
```

---

### 2. Data Table (`data-table.blade.php`)

Tabel data dengan styling konsisten.

**Props:**
- `headers` (optional, array) - Array header kolom
- `title` (optional) - Judul tabel
- `emptyMessage` (optional) - Pesan ketika tidak ada data
- `emptyIcon` (optional) - Icon untuk empty state

**Slots:**
- Default slot - Konten tabel (tbody)
- `empty` (optional) - Custom empty state
- `pagination` (optional) - Pagination links

**Contoh Penggunaan:**
```blade
<x-data-table 
    title="Daftar Mahasiswa"
    :headers="['Nama', 'NPM', 'Program Studi', 'Aksi']"
    aria-label="Tabel daftar mahasiswa">
    @forelse($mahasiswa as $mhs)
        <tr class="hover:bg-gray-50 transition-colors">
            <td class="px-6 py-4 whitespace-nowrap">{{ $mhs->nama_lengkap }}</td>
            <td class="px-6 py-4 whitespace-nowrap">{{ $mhs->npm }}</td>
            <td class="px-6 py-4 whitespace-nowrap">{{ $mhs->program_studi }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-right">
                <x-gradient-button variant="primary" size="sm">Edit</x-gradient-button>
            </td>
        </tr>
    @empty
        <x-slot:empty>
            <x-empty-state 
                title="Tidak ada data"
                message="Belum ada mahasiswa terdaftar." />
        </x-slot:empty>
    @endforelse
    
    @if($mahasiswa->hasPages())
        <x-slot:pagination>
            {{ $mahasiswa->links() }}
        </x-slot:pagination>
    @endif
</x-data-table>
```

---

### 3. Card (`card.blade.php`)

Container card dengan styling konsisten.

**Props:**
- `title` (optional) - Judul card
- `subtitle` (optional) - Subtitle card
- `withHeader` (optional, boolean) - Apakah menggunakan header gradient
- `withPadding` (optional, boolean) - Apakah menggunakan padding

**Slots:**
- Default slot - Konten card
- `header` (optional) - Custom header content

**Contoh Penggunaan:**
```blade
<x-card title="Informasi Mahasiswa" subtitle="Data pribadi mahasiswa" withHeader>
    <div class="space-y-4">
        <p>Konten card...</p>
    </div>
</x-card>
```

---

### 4. Form Input (`form-input.blade.php`)

Input field dengan label dan error handling.

**Props:**
- `label` (optional) - Label input
- `name` (required) - Nama input (untuk form)
- `type` (optional, default: 'text') - Tipe input
- `required` (optional, boolean) - Apakah required
- `error` (optional) - Pesan error
- `help` (optional) - Teks bantuan
- `icon` (optional) - Icon di sebelah kiri input

**Contoh Penggunaan:**
```blade
<x-form-input 
    label="Nama Lengkap"
    name="nama_lengkap"
    type="text"
    required
    :error="$errors->first('nama_lengkap')"
    help="Masukkan nama lengkap sesuai KTP"
    :icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
    </svg>'
    value="{{ old('nama_lengkap') }}" />
```

---

### 5. Gradient Button (`gradient-button.blade.php`)

Tombol dengan gradient styling konsisten.

**Props:**
- `type` (optional, default: 'button') - Tipe button
- `variant` (optional, default: 'primary') - Variant: primary, success, danger, warning
- `size` (optional, default: 'md') - Ukuran: sm, md, lg

**Contoh Penggunaan:**
```blade
{{-- Primary Button --}}
<x-gradient-button variant="primary" size="md">
    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
    </svg>
    Simpan
</x-gradient-button>

{{-- Success Button --}}
<x-gradient-button variant="success" size="sm">
    Setujui
</x-gradient-button>

{{-- Danger Button --}}
<x-gradient-button variant="danger" type="submit">
    Hapus
</x-gradient-button>
```

---

### 6. Empty State (`empty-state.blade.php`)

Komponen untuk menampilkan state kosong.

**Props:**
- `icon` (optional) - Custom icon
- `title` (optional, default: 'Tidak ada data') - Judul
- `message` (optional, default: 'Belum ada data untuk ditampilkan.') - Pesan
- `action` (optional) - Tombol aksi

**Contoh Penggunaan:**
```blade
<x-empty-state 
    title="Tidak ada tagihan"
    message="Anda belum memiliki tagihan pembayaran."
    :icon='<svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
    </svg>'>
    <x-slot:action>
        <x-gradient-button variant="primary">Buat Tagihan</x-gradient-button>
    </x-slot:action>
</x-empty-state>
```

---

## 🎨 Styling Guidelines

- **Colors**: Gunakan warna dari `tailwind.config.js` (primary, success, danger, warning)
- **Shadows**: Gunakan `shadow-md` untuk depth, `shadow-lg` untuk hover
- **Borders**: Gunakan `border-gray-200` untuk konsistensi
- **Transitions**: Selalu tambahkan `transition-all duration-200` untuk smooth interactions

## 📝 Tips

1. **Konsistensi**: Gunakan komponen ini di semua halaman untuk menjaga konsistensi
2. **Customization**: Props bisa dikombinasikan dengan `$attributes->merge()` untuk custom styling
3. **Slots**: Gunakan named slots untuk fleksibilitas lebih besar
4. **Accessibility**: Selalu tambahkan `aria-label` untuk tabel dan form

---

**Dibuat untuk**: KampusPay STTP  
**Framework**: Laravel + Blade + Tailwind CSS

