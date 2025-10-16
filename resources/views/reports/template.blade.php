<!DOCTYPE html>
<html>
<head>
    <title>Laporan {{ $jenis_laporan_title }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; }
        h1 { text-align: center; }
        .header-info { margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 6px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
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
