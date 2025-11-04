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

        .details-table,
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }

        .details-table th,
        .details-table td,
        .summary-table th,
        .summary-table td {
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

        .summary-table th {
            background: var(--soft);
            text-align: left;
        }

        .amount {
            font-weight: 700;
            text-align: right;
            font-size: 13px;
        }

        /* ===== BADGE STATUS ===== */
        .badge {
            display: inline-block;
            font-size: 10px;
            padding: 2px 8px;
            border-radius: 8px;
            font-weight: 700;
            border: 1px solid #000;
        }

        .badge-green { background: #DCFCE7; color: #166534; }
        .badge-red { background: #FEE2E2; color: #991B1B; }

        /* ===== TANDA TANGAN ===== */
        .signature {
            margin-top: 28px;
            display: flex;
            justify-content: flex-end;
        }

        .signature .block {
            width: 240px;
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

    <!-- ===== ISI KWITANSI ===== -->
    <div class="header-kwitansi">
        <h2>BUKTI PEMBAYARAN</h2>
        <table class="meta-table">
            <tr>
                <td class="meta-left">No. Transaksi: <strong>{{ $pembayaran->pembayaran_id }}</strong></td>
                <td class="meta-center">
                    @php
                        $status = 'Lunas';
                        $badgeClass = 'badge-green';
                        if ($pembayaran->status_dibatalkan ?? false) {
                            $status = 'Dibatalkan';
                            $badgeClass = 'badge-red';
                        }
                    @endphp
                    <span class="badge {{ $badgeClass }}">{{ $status }}</span>
                </td>
                <td class="meta-right">Tanggal: <strong>{{ $pembayaran->created_at->format('d/m/Y') }}</strong></td>
            </tr>
        </table>
    </div>

    <div class="content">
        <div class="section-title">Identitas Mahasiswa</div>
        <table class="details-table">
            <tr><th>Nama Mahasiswa</th><td>{{ $pembayaran->tagihan->mahasiswa->user->nama_lengkap }}</td></tr>
            <tr><th>NPM</th><td>{{ $pembayaran->tagihan->mahasiswa->npm }}</td></tr>
            <tr><th>Program Studi</th><td>{{ $pembayaran->tagihan->mahasiswa->program_studi }}</td></tr>
        </table>

        <div class="section-title">Rincian Pembayaran</div>
        <table class="details-table">
            <tr><th>Jenis Pembayaran</th><td>{{ $pembayaran->tagihan->tarif->nama_pembayaran }}</td></tr>
            <tr><th>Kode Pembayaran</th><td>{{ $pembayaran->tagihan->kode_pembayaran }}</td></tr>
            <tr><th>Jumlah Pembayaran</th><td class="amount">Rp {{ number_format($pembayaran->tagihan->jumlah_tagihan, 0, ',', '.') }}</td></tr>
            <tr><th>Metode Pembayaran</th><td>{{ $pembayaran->metode_pembayaran }}</td></tr>
        </table>

        <div class="section-title">Ringkasan</div>
        <table class="summary-table" style="width:60%;">
            <tr><th>Total Pembayaran</th><td class="amount">Rp {{ number_format($pembayaran->tagihan->jumlah_tagihan, 0, ',', '.') }}</td></tr>
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
