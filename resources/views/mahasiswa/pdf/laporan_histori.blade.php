<!DOCTYPE html>
<html>
<head>
    <title>Laporan Histori Pembayaran</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        h1 { margin: 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .total { font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Histori Pembayaran</h1>
        <p>Periode: {{ \Carbon\Carbon::parse($filters['dari_tanggal'] ?? now())->format('d M Y') }} - {{ \Carbon\Carbon::parse($filters['sampai_tanggal'] ?? now())->format('d M Y') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Tanggal Bayar</th>
                <th>Jenis Pembayaran</th>
                <th>Metode</th>
                <th>Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @forelse($histori as $item)
            <tr>
                <td>{{ $item->created_at->format('d M Y, H:i') }}</td>
                <td>{{ $item->tagihan->tarif->nama_pembayaran }}</td>
                <td>{{ $item->metode_pembayaran }}</td>
                <td>Rp {{ number_format($item->tagihan->jumlah_tagihan, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="4" style="text-align: center;">Tidak ada data pada periode ini.</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" class="total">TOTAL</td>
                <td class="total">Rp {{ number_format($histori->sum('tagihan.jumlah_tagihan'), 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
