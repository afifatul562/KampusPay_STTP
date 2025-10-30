<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Riwayat Pembayaran - {{ $mahasiswa->user->nama_lengkap ?? '' }}</title>
    <style>
        /* ===== CSS UNTUK KOP SURAT FIXED ===== */
        @page {
            margin: 0cm 0cm; /* Hapus margin default */
        }

        body {
            /* Beri ruang di atas untuk kop surat */
            margin-top: 4.5cm;

            /* Atur margin halaman standar */
            margin-left: 2cm;
            margin-right: 2cm;
            margin-bottom: 2cm;

            /* Font dari file asli Anda */
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #333;
            font-size: 11px;
            line-height: 1.4;
        }

        header {
            position: fixed;
            top: 1cm; /* Jarak kop surat dari atas */
            left: 2cm; /* Samakan dengan margin-left body */
            right: 2cm; /* Samakan dengan margin-right body */
            height: 3cm; /* Perkiraan tinggi kop surat */
            border-bottom: 2px solid black;
        }

        .header-table { width: 100%; }
        .header-table td { vertical-align: middle; padding-bottom: 10px; }
        .logo { width: 60px; height: auto; }
        .institute-details { text-align: center; }
        .institute-details .yayasan { font-size: 12px; }
        .institute-details .nama-kampus { font-size: 16px; font-weight: bold; margin: 2px 0; }
        .institute-details .alamat { font-size: 9px; }
        /* ===== AKHIR CSS KOP SURAT ===== */


        /* General Styles */
        .container { width: 100%; /* Lebar 100% krn margin diatur di body */ }

        /* Report Title */
        .report-title { text-align: center; font-size: 14px; font-weight: bold; margin-bottom: 25px; }

        /* Student Details Table */
        .student-details-table { width: 100%; margin-bottom: 25px; font-size: 11px; }
        .student-details-table td { padding: 2px 5px; vertical-align: top; }
        .student-details-table td:nth-child(1), .student-details-table td:nth-child(4) { width: 15%; }
        .student-details-table td:nth-child(2), .student-details-table td:nth-child(5) { width: 2%; }
        .student-details-table td:nth-child(3), .student-details-table td:nth-child(6) { width: 33%; font-weight: bold; }

        /* Payment History Table Styles */
        .summary-title { font-size: 13px; font-weight: bold; margin-bottom: 10px; }
        table.history-table { width: 100%; border-collapse: collapse; margin-top: 5px; }
        table.history-table th, table.history-table td { border: 1px solid #ccc; padding: 7px 10px; text-align: left; }
        table.history-table th { background-color: #eee; font-size: 12px; font-weight: bold; }
        td.text-center { text-align: center; }
        td.text-right { text-align: right; }
        tfoot td { font-weight: bold; background-color: #f9f9f9; }

        /* Footer Styles */
        .footer { margin-top: 40px; font-size: 9px; text-align: right; color: #888; }
    </style>
</head>
<body>

    <header>
        <table class="header-table">
            <tr>
                <td style="width: 80px;">
                    <img src="{{ public_path('images/logo_kampus.png') }}" alt="Logo" class="logo">
                </td>
                <td class="institute-details">
                    <div class="yayasan">Yayasan Pendidikan Tinggi Payakumbuh</div>
                    <div class="nama-kampus">SEKOLAH TINGGI TEKNOLOGI PAYAKUMBUH</div>
                    <div class="alamat">
                        Jln. Khatib Sulaiman Sawah Padang, Telp. 0752-796063, Fax. 0752-90063, Website www.sttpyk.ac.id, Email: info@sttpyk.ac.id
                    </div>
                </td>
                <td style="width: 80px;">&nbsp;</td> </tr>
        </table>
    </header>
    <div class="container">

        {{-- JUDUL LAPORAN --}}
        <div class="report-title">Laporan Riwayat Pembayaran Mahasiswa</div>

        {{-- DETAIL MAHASISWA --}}
        <table class="student-details-table">
            <tr>
                <td>Nama</td><td>:</td><td>{{ $mahasiswa->user->nama_lengkap ?? 'N/A' }}</td>
                <td>Tahun Akademik</td><td>:</td><td>{{ $filters['tahun_akademik'] ?? '-' }}</td> {{-- Sesuaikan variabel --}}
            </tr>
            <tr>
                <td>NIM</td><td>:</td><td>{{ $mahasiswa->npm ?? 'N/A' }}</td>
                <td>Program Studi</td><td>:</td><td>{{ $mahasiswa->program_studi ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td>Dosen PA</td><td>:</td><td>{{ $mahasiswa->dosen_pa ?? '-' }}</td> {{-- Ganti jika ada datanya --}}
                <td>Semester</td><td>:</td><td>{{ $mahasiswa->semester_aktif ?? 'N/A' }}</td>
            </tr>
        </table>

        <div class="summary-title">
            Histori Pembayaran
            ({{ \Carbon\Carbon::parse($filters['dari_tanggal'] ?? now())->isoFormat('D MMM Y') }} -
             {{ \Carbon\Carbon::parse($filters['sampai_tanggal'] ?? now())->isoFormat('D MMM Y') }})
        </div>
        <table class="history-table">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Tanggal Bayar</th>
                    <th>Jenis Pembayaran</th>
                    <th>Metode</th>
                    <th class="text-right">Jumlah</th>
                    <th>Kasir</th>
                </tr>
            </thead>
            <tbody>
                @forelse($histori as $index => $item)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ \Carbon\Carbon::parse($item->tanggal_bayar)->isoFormat('D MMM Y, HH:mm') }}</td>
                        <td>{{ $item->tagihan->tarif->nama_pembayaran ?? 'N/A' }}</td>
                        <td>{{ $item->metode_pembayaran }}</td>
                        <td class="text-right">Rp {{ number_format($item->tagihan->jumlah_tagihan ?? 0, 0, ',', '.') }}</td>
                        <td>{{ $item->userKasir->nama_lengkap ?? 'N/A' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center" style="padding: 20px;">Tidak ada riwayat pembayaran pada periode ini.</td>
                    </tr>
                @endforelse
            </tbody>
            @if($histori->isNotEmpty())
            <tfoot>
                <tr>
                    <td colspan="4" class="text-right"><strong>TOTAL PERIODE INI</strong></td>
                    <td class="text-right"><strong>Rp {{ number_format($histori->sum(function($p){ return $p->tagihan->jumlah_tagihan ?? 0; }), 0, ',', '.') }}</strong></td>
                    <td></td> {{-- Kolom Kasir dikosongkan --}}
                </tr>
            </tfoot>
            @endif
        </table>

        <div class="footer">
            Dicetak pada: {{ \Carbon\Carbon::now()->isoFormat('D MMMM YYYY, HH:mm') }}
        </div>
    </div>
</body>
</html>
