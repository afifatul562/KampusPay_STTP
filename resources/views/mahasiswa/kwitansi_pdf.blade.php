<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kwitansi Pembayaran - {{ $pembayaran->tagihan->kode_pembayaran }}</title>
    <style>
        :root {
            --text: #222;
            --muted: #555;
            --line: #D7DBE0;
            --soft: #F3F4F6;
        }

        @page { margin: 0cm; }

        body {
            margin: 5.4cm 2cm 2cm 2cm; /* ruang untuk header */
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: var(--text);
            font-size: 12px;
        }

        /* ===== KOP SURAT ===== */
        header {
            position: fixed;
            top: 1cm;
            left: 2cm;
            right: 2cm;
            height: auto;
        }

        .kop-surat {
            width: 100%;
            border: 1px solid #000;
            border-collapse: collapse;
            margin-bottom: 6px; /* jarak bawah lebih kecil */
        }

        .kop-surat td {
            vertical-align: middle;
            padding: 4px 8px;
        }

        .logo-cell {
            width: 100px;
            border-right: 1px solid #000;
            text-align: center;
        }

        .logo {
            width: 80px;
            height: auto;
        }

        .info-cell {
            text-align: center;
            line-height: 1.3;
        }

        .info-cell .yayasan {
            font-size: 13px;
            font-weight: bold;
            text-transform: uppercase;
            color: #000;
        }

        .info-cell .kampus {
            font-size: 17px;
            font-weight: bold;
            text-transform: uppercase;
            text-decoration: underline;
            margin: 4px 0;
        }

        .info-cell .alamat {
            font-size: 10px;
            color: #000;
        }

        /* ===== HEADER KWITANSI ===== */
        .header-kwitansi {
            text-align: center;
            margin-top: -0.1cm; /* geser lebih dekat ke kop */
            margin-bottom: 0.15cm;
        }

        .header-kwitansi h2 {
            margin: 0;
            font-size: 20px;
            font-weight: bold;
            letter-spacing: 0.6px;
        }

        .meta-table {
            width: 100%;
            border: 1px solid #000;
            border-collapse: collapse;
            margin-top: 8px;
        }

        .meta-table td {
            padding: 6px 10px;
            font-size: 11px;
            border: 1px solid #000;
        }

        .meta-left { text-align: left; }
        .meta-center { text-align: center; }
        .meta-right { text-align: right; }

        /* ===== DETAIL ===== */
        .section-title {
            font-size: 12px;
            font-weight: 700;
            margin: 14px 0 6px;
            text-transform: uppercase;
            letter-spacing: .6px;
        }

        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin: 0 auto 6px;
        }

        .details-table th,
        .details-table td {
            border: 1px solid #000;
            padding: 7px 9px;
            font-size: 12px;
        }

        .details-table th {
            width: 200px;
            background: var(--soft);
            font-weight: 600;
            text-align: left;
        }

        .amount {
            font-weight: 700;
            text-align: right;
            font-size: 13px;
        }

        /* ===== WATERMARK ===== */
        .watermark {
            position: fixed;
            top: 45%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-20deg);
            font-size: 80px;
            font-weight: 700;
            color: rgba(22, 101, 52, 0.12);
            text-transform: uppercase;
            pointer-events: none;
            z-index: 0;
            white-space: nowrap;
        }

        /* ===== BADGE STATUS ===== */
        /* ===== TANDA TANGAN ===== */
        .signature {
            margin-top: 28px;
            width: 100%;
            display: flex;
        }

        .signature .block {
            margin-left: auto;
            max-width: 260px;
            text-align: center;
        }

        .signature .role {
            font-size: 12px;
            color: #111;
        }

        /* ===== FOOTER ===== */
        .footer-note {
            margin-top: 24px;
            font-size: 10px;
            color: var(--muted);
            text-align: center;
            border-top: 1px solid var(--line);
            padding-top: 8px;
        }
    </style>
</head>
<body>
    @php
        if (!function_exists('penyebut_indonesia')) {
            function penyebut_indonesia($number)
            {
                $number = abs($number);
                $words = ["", "Satu", "Dua", "Tiga", "Empat", "Lima", "Enam", "Tujuh", "Delapan", "Sembilan", "Sepuluh", "Sebelas"];
                $temp = "";

                if ($number < 12) {
                    $temp = " " . $words[$number];
                } elseif ($number < 20) {
                    $temp = penyebut_indonesia($number - 10) . " Belas";
                } elseif ($number < 100) {
                    $temp = penyebut_indonesia($number / 10) . " Puluh" . penyebut_indonesia($number % 10);
                } elseif ($number < 200) {
                    $temp = " Seratus" . penyebut_indonesia($number - 100);
                } elseif ($number < 1000) {
                    $temp = penyebut_indonesia($number / 100) . " Ratus" . penyebut_indonesia($number % 100);
                } elseif ($number < 2000) {
                    $temp = " Seribu" . penyebut_indonesia($number - 1000);
                } elseif ($number < 1000000) {
                    $temp = penyebut_indonesia($number / 1000) . " Ribu" . penyebut_indonesia($number % 1000);
                } elseif ($number < 1000000000) {
                    $temp = penyebut_indonesia($number / 1000000) . " Juta" . penyebut_indonesia($number % 1000000);
                } elseif ($number < 1000000000000) {
                    $temp = penyebut_indonesia($number / 1000000000) . " Miliar" . penyebut_indonesia(fmod($number, 1000000000));
                } elseif ($number < 1000000000000000) {
                    $temp = penyebut_indonesia($number / 1000000000000) . " Triliun" . penyebut_indonesia(fmod($number, 1000000000000));
                }

                return $temp;
            }

            function terbilang_indonesia($number)
            {
                if ($number == 0) {
                    return 'Nol';
                }

                $result = trim(penyebut_indonesia($number));
                $result = preg_replace('/\s+/', ' ', $result);
                return ucwords(strtolower($result));
            }
        }

        $jumlahPembayaran = $pembayaran->tagihan->jumlah_tagihan ?? 0;
        $terbilangPembayaran = terbilang_indonesia($jumlahPembayaran) . ' Rupiah';
        $metodePembayaran = $pembayaran->metode_pembayaran ?? 'Tunai';
        if (stripos($metodePembayaran, 'tunai') !== false) {
            $metodeLabel = 'Tunai';
        } elseif (stripos($metodePembayaran, 'transfer') !== false) {
            $metodeLabel = 'Transfer';
        } else {
            $metodeLabel = ucwords(strtolower($metodePembayaran));
        }
    @endphp
    <!-- ===== KOP SURAT ===== -->
    <header>
        <table class="kop-surat">
            <tr>
                <td class="logo-cell">
                    <img src="{{ public_path('images/logo_kampus.png') }}" alt="Logo" class="logo">
                </td>
                <td class="info-cell">
                    <div class="yayasan">YAYASAN PENDIDIKAN TINGGI PAYAKUMBUH</div>
                    <div class="kampus">SEKOLAH TINGGI TEKNOLOGI PAYAKUMBUH</div>
                    <div class="alamat">
                        Jln. Khatib Sulaiman Sawah Padang, Telp. 0752-796063<br>
                        Fax. 0752-90063, Website: www.sttpyk.ac.id, Email: info@sttpyk.ac.id
                    </div>
                </td>
            </tr>
        </table>
    </header>

    <div class="watermark">LUNAS</div>

    <!-- ===== ISI KWITANSI ===== -->
    <div class="header-kwitansi">
        <h2>BUKTI PEMBAYARAN</h2>
        <table class="meta-table">
            <tr>
                <td class="meta-left">No. Transaksi: <strong>{{ $pembayaran->pembayaran_id }}</strong></td>
                <td class="meta-center">Metode Pembayaran: <strong>{{ $metodeLabel }}</strong></td>
                <td class="meta-right">Tanggal: <strong>{{ $pembayaran->created_at->format('d/m/Y') }}</strong></td>
            </tr>
        </table>
    </div>

    <div class="content" style="position: relative; z-index: 1;">
        <div class="section-title">Identitas Mahasiswa</div>
        <table class="details-table">
            <tr><th>Nama Mahasiswa</th><td>{{ $pembayaran->tagihan->mahasiswa->user->nama_lengkap }}</td></tr>
            <tr><th>NPM</th><td>{{ $pembayaran->tagihan->mahasiswa->npm }}</td></tr>
            <tr><th>Program Studi</th><td>{{ $pembayaran->tagihan->mahasiswa->program_studi }}</td></tr>
        </table>

        <div class="section-title">Rincian Pembayaran</div>
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
                <td class="amount">Rp {{ number_format($pembayaran->tagihan->jumlah_tagihan, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <th>Terbilang Pembayaran</th>
                <td style="font-style: italic;">{{ $terbilangPembayaran }}</td>
            </tr>
        </table>

    </div>

    <div class="signature">
        <div class="block">
            <div class="role">Padang, {{ $pembayaran->created_at->format('d F Y') }}</div>
            <div style="height: 60px;"></div>
            <div><strong>{{ optional($pembayaran->verifier)->nama_lengkap ?? 'Sistem' }}</strong></div>
            <div class="role">Kasir</div>
        </div>
    </div>

    <div class="footer-note">
        Dokumen ini dicetak otomatis oleh Sistem Akademik STT Payakumbuh dan tidak memerlukan tanda tangan basah.
    </div>
</body>
</html>
