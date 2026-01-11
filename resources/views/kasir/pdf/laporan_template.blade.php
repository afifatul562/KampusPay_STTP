<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Bulanan Kasir - {{ $periodeFormatted }}</title>
    <style>
        /* ===================================
           CSS LAPORAN KASIR FORMAL
           =================================== */
        @page {
            margin: 0cm;
            size: A4;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Times New Roman', 'Times', serif;
            font-size: 11pt;
            line-height: 1.6;
            color: #000;
            margin-top: 4.5cm;
            margin-left: 2.5cm;
            margin-right: 2.5cm;
            margin-bottom: 2.5cm;
        }

        /* ===== HEADER INSTITUSI ===== */
        header {
            position: fixed;
            top: 0.5cm;
            left: 0.5cm;
            right: 0.5cm;
            height: 4cm;
            border-bottom: 3px solid #000;
            padding-bottom: 10px;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-table td {
            vertical-align: middle;
            padding: 0;
        }

        .logo-container {
            width: 80px;
            text-align: center;
        }

        .logo {
            width: 65px;
            height: auto;
        }

        .institute-details {
            text-align: center;
        }

        .institute-details .yayasan {
            font-size: 11pt;
            font-weight: bold;
            margin-bottom: 3px;
        }

        .institute-details .nama-kampus {
            font-size: 14pt;
            font-weight: bold;
            margin: 5px 0;
            text-decoration: underline;
        }

        .institute-details .alamat {
            font-size: 9pt;
            line-height: 1.4;
        }

        /* ===== TITLE SECTION ===== */
        h1 {
            font-size: 14pt;
            font-weight: bold;
            text-align: center;
            margin: 20px 0 10px 0;
            text-decoration: underline;
            letter-spacing: 1px;
        }

        h2 {
            font-size: 12pt;
            font-weight: bold;
            text-align: center;
            margin-bottom: 20px;
        }

        /* ===== SUMMARY BOX ===== */
        .summary-box {
            border: 2px solid #000;
            padding: 10px;
            margin: 20px 0;
        }

        .summary-box strong {
            font-size: 10pt;
        }

        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        .summary-table td {
            border: 1px solid #ddd;
            padding: 5px 8px;
        }

        /* ===== TABLE STYLING ===== */
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }

        th, td {
            border: 1px solid #000;
            padding: 6px 8px;
            text-align: left;
            vertical-align: top;
        }

        thead th {
            background-color: #f5f5f5;
            font-weight: bold;
            text-align: center;
        }

        tbody td {
            text-align: left;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        /* ===== TOTALS ===== */
        .total-row {
            background-color: #f9f9f9 !important;
        }

        .total-row th,
        .total-row td {
            background-color: #f9f9f9 !important;
            font-weight: bold;
            border-top: 2px solid #000;
            padding: 8px;
        }

        /* ===== FOOTER ===== */
        footer {
            position: fixed;
            bottom: 0.5cm;
            left: 2.5cm;
            right: 2.5cm;
            text-align: center;
            font-size: 9pt;
            border-top: 1px solid #000;
            padding-top: 5px;
        }

        .footer-info {
            margin: 1px 0;
        }

        /* ===== MISC ===== */
        small {
            font-size: 8pt;
        }
    </style>
</head>
<body>

    {{-- ===== HEADER INSTITUSI ===== --}}
    <header>
        <table class="header-table">
            <tr>
                <td class="logo-container">
                    <img src="{{ public_path('images/logo_kampus.png') }}" alt="Logo" class="logo">
                </td>
                <td class="institute-details">
                    <div class="yayasan">YAYASAN PENDIDIKAN TINGGI PAYAKUMBUH</div>
                    <div class="nama-kampus">SEKOLAH TINGGI TEKNOLOGI PAYAKUMBUH</div>
                    <div class="alamat">
                        Jln. Khatib Sulaiman Sawah Padang, Telp. 0752-796063<br>
                        Fax. 0752-90063, Website: www.sttpyk.ac.id, Email: info@sttpyk.ac.id
                    </div>
                </td>
            </tr>
        </table>
    </header>

    {{-- ===== TITLE ===== --}}
    <h1>LAPORAN BULANAN KASIR</h1>
    <h2>Periode {{ $periodeFormatted }}</h2>

    <div style="text-align: right; margin-bottom: 15px; font-size: 9pt;">
        <strong>Tanggal Cetak:</strong> {{ \Carbon\Carbon::now()->isoFormat('DD MMMM YYYY') }}<br>
        <strong>Kasir:</strong> {{ $kasirName }}
    </div>

    {{-- ===== SUMMARY ===== --}}
    <div class="summary-box">
        <strong>RINGKASAN</strong>
        <table class="summary-table">
            <tr>
                <td style="width: 50%;"><strong>Total Penerimaan:</strong></td>
                <td style="width: 50%;"><strong>Rp {{ number_format($totalPenerimaan, 0, ',', '.') }}</strong></td>
            </tr>
            <tr>
                <td><strong>Jumlah Transaksi:</strong></td>
                <td><strong>{{ $jumlahTransaksi }} transaksi</strong></td>
            </tr>
        </table>
    </div>

    {{-- ===== TABEL RANGKUMAN PER JENIS ===== --}}
    <h3 style="font-size: 11pt; font-weight: bold; margin: 25px 0 10px 0;">Rangkuman per Jenis Pembayaran</h3>
    <table>
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 50%;">Jenis Pembayaran</th>
                <th style="width: 20%;" class="text-center">Jumlah Transaksi</th>
                <th style="width: 25%;" class="text-right">Total Nominal</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($laporanPerJenis as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $item->nama_pembayaran }}</td>
                    <td class="text-center">{{ $item->jumlah_transaksi }}</td>
                    <td class="text-right">Rp {{ number_format($item->total_nominal, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center">Tidak ada data untuk periode ini.</td>
                </tr>
            @endforelse

            {{-- TOTALS --}}
            @if ($laporanPerJenis->isNotEmpty())
                <tr class="total-row">
                    <th colspan="2" class="text-right">TOTAL:</th>
                    <th class="text-center">{{ $jumlahTransaksi }}</th>
                    <th class="text-right">Rp {{ number_format($totalPenerimaan, 0, ',', '.') }}</th>
                </tr>
            @endif
        </tbody>
    </table>

    {{-- ===== TABEL DETAIL TRANSAKSI ===== --}}
    @if ($transaksiDetail->isNotEmpty() && $transaksiDetail->count() <= 50)
        <h3 style="font-size: 11pt; font-weight: bold; margin: 30px 0 10px 0;">Detail Transaksi</h3>
        <table>
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 15%;">Tanggal</th>
                    <th style="width: 25%;">Mahasiswa</th>
                    <th style="width: 20%;">Jenis</th>
                    <th style="width: 15%;" class="text-right">Nominal</th>
                    <th style="width: 10%;" class="text-center">Metode</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($transaksiDetail as $index => $pembayaran)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ \Carbon\Carbon::parse($pembayaran->tanggal_bayar)->isoFormat('DD/MM/YYYY') }}</td>
                        <td>
                            {{ $pembayaran->tagihan->mahasiswa->user->nama_lengkap ?? 'N/A' }}<br>
                            <small>{{ $pembayaran->tagihan->mahasiswa->npm ?? 'N/A' }}</small>
                        </td>
                        <td>{{ $pembayaran->tagihan->tarif->nama_pembayaran ?? 'N/A' }}</td>
                        <td class="text-right">Rp {{ number_format($pembayaran->tagihan->jumlah_tagihan, 0, ',', '.') }}</td>
                        <td class="text-center">{{ $pembayaran->metode_pembayaran }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- ===== FOOTER ===== --}}
    <footer>
        <div class="footer-info">
            <strong>SEKOLAH TINGGI TEKNOLOGI PAYAKUMBUH</strong>
        </div>
        <div class="footer-info">
            Dokumen ini dihasilkan secara otomatis oleh Sistem Informasi KampusPay STTP
        </div>
        <div class="footer-info">
            <script type="text/php">
                if (isset($pdf)) {
                    $text = "Halaman {PAGE_NUM} dari {PAGE_COUNT}";
                    $size = 9;
                    $font = $fontMetrics->getFont("Times-Roman");
                    $width = $fontMetrics->get_text_width($text, $font, $size) / 2;
                    $x = ($pdf->get_width() - $width) / 2;
                    $y = $pdf->get_height() - 35;
                    $pdf->page_text($x, $y, $text, $font, $size);
                }
            </script>
        </div>
    </footer>

</body>
</html>

