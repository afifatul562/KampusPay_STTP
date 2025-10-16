<!DOCTYPE html>
<html>
<head>
    <title>Kwitansi Pembayaran - {{ $pembayaran->tagihan->kode_pembayaran }}</title>
    <style>
        body { font-family: sans-serif; margin: 40px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 24px; }
        .header p { margin: 0; color: #555; }
        .content { margin-top: 30px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; text-align: left; }
        .details-table th { background-color: #f2f2f2; width: 180px; }
        .details-table td, .details-table th { border: 1px solid #ddd; }
        .footer { margin-top: 40px; text-align: right; font-size: 14px; }
    </style>
</head>
<body>

    <div class="header">
        <h1>BUKTI PEMBAYARAN</h1>
        <p>No. Transaksi: {{ $pembayaran->pembayaran_id }}</p>
    </div>

    <div class="content">
        <p>Telah diterima pembayaran dari:</p>
        <table class="details-table">
            <tr>
                <th>Nama Mahasiswa</th>
                <td>{{ $pembayaran->tagihan->mahasiswa->user->nama_lengkap }}</td>
            </tr>
            <tr>
                <th>NPM</th>
                <td>{{ $pembayaran->tagihan->mahasiswa->npm }}</td>
            </tr>
            <tr>
                <th>Program Studi</th>
                <td>{{ $pembayaran->tagihan->mahasiswa->program_studi }}</td>
            </tr>
        </table>

        <p style="margin-top: 30px;">Untuk pembayaran:</p>
        <table class="details-table">
             <tr>
                <th>Jenis Pembayaran</th>
                <td>{{ $pembayaran->tagihan->tarif->nama_pembayaran }}</td>
            </tr>
            <tr>
                <th>Kode Pembayaran</th>
                <td>{{ $pembayaran->tagihan->kode_pembayaran }}</td>
            </tr>
            <tr>
                <th>Jumlah Pembayaran</th>
                <td><strong>Rp {{ number_format($pembayaran->tagihan->jumlah_tagihan, 0, ',', '.') }}</strong></td>
            </tr>
             <tr>
                <th>Metode Pembayaran</th>
                <td>{{ $pembayaran->metode_pembayaran }}</td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <p>Padang, {{ $pembayaran->created_at->format('d F Y') }}</p>
        <br><br><br>
        <p><strong>{{ optional($pembayaran->verifier)->nama_lengkap ?? 'Sistem' }}</strong></p>
        <p>(Kasir)</p>
    </div>

</body>
</html>