<!DOCTYPE html>
<html>
<head>
    <title>Kwitansi Pembayaran - {{ $pembayaran->tagihan->kode_pembayaran }}</title>
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
            font-family: sans-serif;
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


        .header-kwitansi { text-align: center; margin-bottom: 20px; }
        .header-kwitansi h1 { margin: 0; font-size: 24px; }
        .header-kwitansi p { margin: 0; color: #555; }

        .content { margin-top: 30px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; text-align: left; }
        .details-table th { background-color: #f2f2f2; width: 180px; }
        .details-table td, .details-table th { border: 1px solid #ddd; }
        .footer { margin-top: 40px; text-align: right; font-size: 14px; }
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
    <div class="header-kwitansi">
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
