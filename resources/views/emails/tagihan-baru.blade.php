<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tagihan Baru</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; }
        .container { width: 90%; margin: 20px auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px; }
        .header { font-size: 24px; color: #333; }
        .content { margin-top: 20px; }
        .details-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .details-table th, .details-table td { border: 1px solid #eee; padding: 10px; text-align: left; }
        .details-table th { background-color: #f9f9f9; }
        .button {
            display: inline-block;
            padding: 12px 25px;
            margin: 20px 0;
            background-color: #0d6efd; /* Warna biru */
            color: #ffffff;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }
        .footer { margin-top: 20px; font-size: 12px; color: #888; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">Halo, {{ $tagihan->mahasiswa->user->nama_lengkap }}!</div>

        <div class="content">
            <p>Admin telah membuat tagihan baru untuk Anda. Berikut adalah detailnya:</p>

            <table class="details-table">
                <tr>
                    <th style="width: 30%;">Detail Tagihan</th>
                    <td>{{ $tagihan->tarif->nama_pembayaran }}</td>
                </tr>
                <tr>
                    <th>Total Pembayaran</th>
                    <td><strong>Rp {{ number_format($tagihan->jumlah_tagihan, 0, ',', '.') }}</strong></td>
                </tr>
                <tr>
                    <th>Jatuh Tempo</th>
                    <td>{{ \Carbon\Carbon::parse($tagihan->tanggal_jatuh_tempo)->isoFormat('D MMMM YYYY') }}</td>
                </tr>
                <tr>
                    <th>Kode Pembayaran</th>
                    <td>{{ $tagihan->kode_pembayaran }}</td>
                </tr>
            </table>

            <p style="margin-top: 20px;">
                Silakan lakukan pembayaran sebelum tanggal jatuh tempo melalui link di bawah ini:
            </p>

            {{-- Ini adalah Link Pembayaran yang diminta pembimbing Anda --}}
            <a href="{{ route('mahasiswa.pembayaran.pilih-metode', $tagihan->tagihan_id) }}" class="button">
                Bayar Sekarang
            </a>

            <p>Jika link di atas tidak berfungsi, salin dan tempel URL berikut di browser Anda:</p>
            <p style="font-size: 11px; word-break: break-all;">
                {{ route('mahasiswa.pembayaran.pilih-metode', $tagihan->tagihan_id) }}
            </p>
        </div>

        <div class="footer">
            Terima kasih. <br>
            Tim KampusPay
        </div>
    </div>
</body>
</html>
