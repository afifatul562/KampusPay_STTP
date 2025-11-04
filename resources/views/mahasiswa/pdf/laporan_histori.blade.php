<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Riwayat Pembayaran - {{ $mahasiswa->user->nama_lengkap ?? '' }}</title>
    <style>
        :root { --text:#222; --muted:#555; --line:#000; --soft:#F3F4F6; }
        @page { margin: 0cm; }
        body { margin: 5.4cm 2cm 2cm 2cm; font-family:'Helvetica','Arial',sans-serif; color: var(--text); font-size: 12px; }
        /* Kop surat selaras kwitansi */
        header { position: fixed; top: 1cm; left: 2cm; right: 2cm; height: auto; }
        .kop-surat { width:100%; border:1px solid #000; border-collapse: collapse; margin-bottom:6px; }
        .kop-surat td { vertical-align: middle; padding:4px 8px; border:1px solid #000; }
        .logo-cell { width:100px; text-align:center; }
        .logo { width:80px; height:auto; }
        .info-cell { text-align:center; line-height:1.3; }
        .info-cell .yayasan { font-size:13px; font-weight:bold; text-transform:uppercase; }
        .info-cell .kampus { font-size:17px; font-weight:bold; text-transform:uppercase; text-decoration: underline; margin:4px 0; }
        .info-cell .alamat { font-size:10px; }

        .container { width:100%; }
        .report-title { text-align:center; font-size:20px; font-weight:bold; margin-bottom:6px; letter-spacing:.6px; }
        .student-details-table { width:100%; margin-bottom: 12px; font-size: 12px; border-collapse: collapse; }
        .student-details-table td { padding:6px 10px; vertical-align: top; border:1px solid #000; }
        .student-details-table th { background: var(--soft); text-align:left; border:1px solid #000; }

        .summary-title { font-size: 12px; font-weight: 700; margin: 12px 0 6px; text-transform: uppercase; letter-spacing:.6px; }
        table.history-table { width:100%; border-collapse: collapse; margin-top:5px; }
        table.history-table th, table.history-table td { border:1px solid #000; padding:7px 10px; }
        table.history-table th { background: var(--soft); font-size:12px; font-weight:600; }
        td.text-center { text-align:center; } td.text-right { text-align:right; }
        tfoot td { font-weight:700; background:#F8F9FA; border-top:2px solid #000; }

        .footer { margin-top: 24px; font-size: 10px; text-align: right; color: var(--muted); }
    </style>
</head>
<body>

    <header>
        <table class="kop-surat">
            <tr>
                <td class="logo-cell"><img src="{{ public_path('images/logo_kampus.png') }}" alt="Logo" class="logo"></td>
                <td class="info-cell">
                    <div class="yayasan">YAYASAN PENDIDIKAN TINGGI PAYAKUMBUH</div>
                    <div class="kampus">SEKOLAH TINGGI TEKNOLOGI PAYAKUMBUH</div>
                    <div class="alamat">Jln. Khatib Sulaiman Sawah Padang, Telp. 0752-796063<br>Fax. 0752-90063, Website: www.sttpyk.ac.id, Email: info@sttpyk.ac.id</div>
                </td>
            </tr>
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
                <td colspan="3"></td>
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
