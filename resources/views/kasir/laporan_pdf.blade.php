<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Penerimaan Kasir - {{ sprintf('%02d', $selectedMonth) }}/{{ $selectedYear }}</title>
    <style>
        :root { --text:#222; --muted:#555; --line:#000; --soft:#F3F4F6; }
        @page { margin: 0cm; }
        body { margin: 5.4cm 2cm 2cm 2cm; font-family:'Helvetica','Arial',sans-serif; color:var(--text); font-size:12px; }
        header { position: fixed; top: 1cm; left: 2cm; right: 2cm; height: auto; }
        .kop-surat { width:100%; border:1px solid #000; border-collapse:collapse; margin-bottom:6px; }
        .kop-surat td { vertical-align:middle; padding:4px 8px; }
        .logo-cell { width:100px; border-right:1px solid #000; text-align:center; }
        .logo { width:80px; height:auto; }
        .info-cell { text-align:center; line-height:1.3; }
        .info-cell .yayasan { font-size:13px; font-weight:bold; text-transform:uppercase; color:#000; }
        .info-cell .kampus { font-size:17px; font-weight:bold; text-transform:uppercase; text-decoration:underline; margin:4px 0; }
        .info-cell .alamat { font-size:10px; color:#000; }
        .header-judul { text-align:center; margin-top:-0.1cm; margin-bottom:0.15cm; }
        .header-judul h2 { margin:0; font-size:20px; font-weight:bold; letter-spacing:0.6px; }
        .meta-table { width:100%; border:1px solid #000; border-collapse:collapse; margin-top:8px; }
        .meta-table td { padding:6px 10px; font-size:11px; border:1px solid #000; }
        .meta-left{ text-align:left; } .meta-center{ text-align:center; } .meta-right{ text-align:right; }
        .section-title{ font-size:12px; font-weight:700; margin:14px 0 6px; text-transform:uppercase; letter-spacing:.6px; }
        table.report { width:100%; border-collapse:collapse; margin-bottom:6px; }
        table.report th, table.report td { border:1px solid #000; padding:7px 9px; font-size:12px; }
        table.report th { background:var(--soft); text-align:left; }
        .right{text-align:right;} .center{text-align:center;}
        tfoot td{ font-weight:700; }
        .footer-note{ margin-top:24px; font-size:10px; color:var(--muted); text-align:center; border-top:1px solid #D7DBE0; padding-top:8px; }
    </style>
<head>
<body>
    <header>
        <table class="kop-surat">
            <tr>
                <td class="logo-cell"><img src="{{ public_path('images/logo_kampus.png') }}" class="logo" alt="Logo"></td>
                <td class="info-cell">
                    <div class="yayasan">YAYASAN PENDIDIKAN TINGGI PAYAKUMBUH</div>
                    <div class="kampus">SEKOLAH TINGGI TEKNOLOGI PAYAKUMBUH</div>
                    <div class="alamat">Jln. Khatib Sulaiman Sawah Padang, Telp. 0752-796063<br>Fax. 0752-90063, Website: www.sttpyk.ac.id, Email: info@sttpyk.ac.id</div>
                </td>
            </tr>
        </table>
    </header>

    <div class="header-judul">
        <h2>LAPORAN PENERIMAAN</h2>
        <table class="meta-table">
            <tr>
                <td class="meta-left">Periode: <strong>{{ sprintf('%02d', $selectedMonth) }}/{{ $selectedYear }}</strong></td>
                <td class="meta-center">Total Transaksi: <strong>{{ $jumlahTransaksi }}</strong></td>
                <td class="meta-right">Total Penerimaan: <strong>Rp {{ number_format($totalPenerimaan, 0, ',', '.') }}</strong></td>
            </tr>
        </table>
    </div>

    <div class="section-title">Rangkuman per Jenis Pembayaran</div>
    <table class="report">
        <thead>
            <tr>
                <th>Jenis Pembayaran</th>
                <th class="center">Jumlah Transaksi</th>
                <th class="right">Total Nominal (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($laporanPerJenis as $item)
                <tr>
                    <td>{{ $item->nama_pembayaran }}</td>
                    <td class="center">{{ $item->jumlah_transaksi }}</td>
                    <td class="right">{{ number_format($item->total_nominal, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="center">Tidak ada data pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td>Total</td>
                <td class="center">{{ $jumlahTransaksi }}</td>
                <td class="right">{{ number_format($totalPenerimaan, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer-note">Dokumen ini dicetak otomatis oleh Sistem Akademik STT Payakumbuh dan tidak memerlukan tanda tangan basah.</div>
</body>
</html>

