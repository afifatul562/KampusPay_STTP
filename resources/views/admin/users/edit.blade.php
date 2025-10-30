{{-- Ganti 'layouts.admin' dengan layout admin utama Anda --}}
@extends('layouts.app')

@section('title', 'Edit User')

@section('content')
<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Edit User: {{ $user->nama_lengkap }}</h1>
        <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali
        </a>
    </div>

    {{-- Tampilkan Pesan Error Validasi --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
        @csrf
        @method('PUT') {{-- PENTING: Method PUT untuk update --}}

        <div class="row">

            {{-- Kolom Kiri: Edit Detail User --}}
            <div class="col-lg-6">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Detail User</h6>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="nama_lengkap">Nama Lengkap</label>
                            <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap"
                                   value="{{ old('nama_lengkap', $user->nama_lengkap) }}" required>
                        </div>

                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" class="form-control" id="email" name="email"
                                   value="{{ old('email', $user->email) }}" required>
                        </div>

                        <div class="form-group">
                            <label for="role">Role / Peran</label>
                            <select class="form-control" id="role" name="role" required>
                                {{--
                                    PENTING:
                                    Sesuaikan <option> di bawah ini dengan nama role
                                    yang ada di sistem Anda (misal: 'admin', 'mahasiswa', 'kasir')
                                --}}
                                <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>
                                    Admin
                                </option>
                                <option value="mahasiswa" {{ old('role', $user->role) == 'mahasiswa' ? 'selected' : '' }}>
                                    Mahasiswa
                                </option>
                                <option value="kasir" {{ old('role', $user->role) == 'kasir' ? 'selected' : '' }}>
                                    Kasir
                                </option>
                                {{-- Tambahkan role lain jika ada --}}
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Kolom Kanan: Ganti Password --}}
            <div class="col-lg-6">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-warning">Ganti Password (Opsional)</h6>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">
                            <i class="fas fa-exclamation-circle"></i>
                            Kosongkan kedua field di bawah ini jika Anda tidak ingin mengganti password user.
                        </p>
                        <div class="form-group">
                            <label for="password">Password Baru</label>
                            <input type="password" class="form-control" id="password" name="password">
                        </div>

                        <div class="form-group">
                            <label for="password_confirmation">Konfirmasi Password Baru</label>
                            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation">
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- Tombol Simpan --}}
        <div class="row">
            <div class="col-lg-12">
                <button type="submit" class="btn btn-primary btn-lg shadow-sm">
                    <i class="fas fa-save"></i> Simpan Perubahan
                </button>
            </div>
        </div>

    </form>
</div>
@endsection
