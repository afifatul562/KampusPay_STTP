<!DOCTYPE html>
<html>
<head>
    <title>Laporan {{ $jenis_laporan_title }}</title>
    <style>
        /* ===== CSS UNTUK KOP SURAT FIXED ===== */
        @page {
            margin: 0cm 0cm;
        }

        body {
            margin-top: 4.5cm;
            margin-left: 2cm;
            margin-right: 2cm;
            margin-bottom: 2cm;
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
        }

        header {
            position: fixed;
            top: 1cm;
            left: 2cm;
            right: 2cm;
            height: 3cm;
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

        h1 { text-align: center; margin-bottom: 5px; }
        .header-info { margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 6px; text-align: left; }
        th { background-color: #f2f2f2; }
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
    <h1>Laporan {{ $jenis_laporan_title }}</h1>
    <div class="header-info">
        <p><strong>Periode:</strong> {{ $periode_formatted }}</p>
        <p><strong>Tanggal Cetak:</strong> {{ $tanggal_cetak }}</p>
    </div>

    <table>
        <thead>
            <tr>
                @foreach ($kolom_header as $kolom)
                    <th>{{ $kolom }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse ($data_laporan as $item)
                <tr>
                    @if ($jenis_laporan_title === 'Pembayaran')
                        <td>{{ $item->id }}</td>
                        <td>{{ \Carbon\Carbon::parse($item->tanggal_bayar)->format('d-m-Y') }}</td>
                        <td>{{ $item->tagihan->mahasiswa_detail->npm }}</td>
                        <td>{{ $item->tagihan->mahasiswa_detail->user->nama_lengkap }}</td>
                        <td>{{ $item->tagihan->tarif->nama_pembayaran }}</td>
                        <td>Rp {{ number_format($item->tagihan->jumlah_tagihan, 0, ',', '.') }}</td>
                    @elseif ($jenis_laporan_title === 'Mahasiswa')
                        <td>{{ $item->npm }}</td>
                        <td>{{ $item->user->nama_lengkap }}</td>
                        <td>{{ $item->program_studi }}</td>
                        <td>{{ $item->angkatan }}</td>
                        <td>{{ $item->status }}</td>
                    @endif
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($kolom_header) }}" style="text-align: center;">Tidak ada data untuk periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
