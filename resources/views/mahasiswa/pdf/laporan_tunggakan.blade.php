<!DOCTYPE html>
<html>
<head>
    <title>Laporan Data Tunggakan</title>
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
        <h1>Laporan Data Tunggakan</h1>
        <p>Per tanggal: {{ now()->format('d F Y') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Jenis Pembayaran</th>
                <th>Jatuh Tempo</th>
                <th>Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tunggakan as $item)
            <tr>
                <td>{{ $item->tarif->nama_pembayaran }}</td>
                <td>{{ \Carbon\Carbon::parse($item->tanggal_jatuh_tempo)->format('d M Y') }}</td>
                <td>Rp {{ number_format($item->jumlah_tagihan, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="3" style="text-align: center;">Tidak ada tunggakan.</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2" class="total">TOTAL TUNGGAKAN</td>
                <td class="total">Rp {{ number_format($tunggakan->sum('jumlah_tagihan'), 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
