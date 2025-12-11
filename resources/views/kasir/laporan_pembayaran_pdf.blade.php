<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Pembayaran - Periode {{ $periodeFormatted }}</title>
    <style>
        :root { --text:#222; --muted:#555; --line:#000; --soft:#F3F4F6; }
        @page { margin: 0cm; }
        body {
            margin: 5.4cm 2cm 2cm 2cm;
            font-family: 'Helvetica','Arial',sans-serif; font-size: 12px; color: var(--text);
        }
        header { position: fixed; top: 1cm; left: 2cm; right: 2cm; height: auto; }
        .kop-surat { width:100%; border:1px solid #000; border-collapse: collapse; margin-bottom: 6px; }
        .kop-surat td { vertical-align: middle; padding: 4px 8px; border:1px solid #000; }
        .logo-cell { width: 100px; text-align:center; }
        .logo { width:80px; height:auto; }
        .info-cell { text-align:center; line-height:1.3; }
        .info-cell .yayasan { font-size:13px; font-weight:bold; text-transform:uppercase; }
        .info-cell .kampus { font-size:17px; font-weight:bold; text-transform:uppercase; text-decoration: underline; margin:4px 0; }
        .info-cell .alamat { font-size:10px; }
        h1 { text-align:center; margin: 0; font-size:20px; font-weight:bold; letter-spacing:.6px; }
        h2 { text-align:center; margin: 4px 0 8px; font-size: 14px; }
        table { width:100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border:1px solid #000; padding:7px 9px; font-size:12px; vertical-align: top; }
        th { background: var(--soft); text-align:left; }
        .group-header td { background:#e2e8f0; font-weight:600; padding:8px; border:1px solid #000; }
        .text-right{ text-align:right; } .text-center{ text-align:center; }
        .badge { display:inline-block; font-size:10px; padding:2px 8px; border-radius:8px; font-weight:700; border:1px solid #000; }
        .badge-lunas { background:#DCFCE7; color:#166534; }
        .badge-belum { background:#FEF3C7; color:#92400E; }
        small { font-size: 10px; color:#000; }
        .total-row th, .total-row td { background:#F8F9FA; font-weight:700; border-top:2px solid #000; }
        .signature { margin-top: 28px; width: 100%; display: flex; }
        .signature .block { margin-left: auto; max-width: 260px; text-align: center; }
        .signature .role { font-size: 12px; color: #111; }
    </style>
</head>
<body>
    <header>
        <table class="kop-surat">
            <tr>
                <td class="logo-cell"><img src="{{ public_path('images/logo_kampus.png') }}" alt="Logo" class="logo"></td>
                <td class="info-cell">
                    <div class="yayasan">YAYASAN PENDIDIKAN TINGGI PAYAKUMBUH</div>
                    <div class="kampus">SEKOLAH TINGGI TEKNOLOGI PAYAKUMBUH</div>
                    <div class="alamat">Jln. Khatib Sulaiman Sawah Padang, Telp. 0752-796063<br>Fax. 0752-90063, Website: www.sttpyk.ac.id, Email: info@sttpyk.ac.id</div>
                </td>
            </tr>
        </table>
    </header>
    <h1>LAPORAN PEMBAYARAN</h1>
    <h2>Periode: {{ $periodeFormatted }} | Kasir: {{ $kasir->nama_lengkap ?? $kasir->username }}</h2>

    @php
        $groupedData = $data->sortBy(function($tagihan) {
            return $tagihan->status === 'Lunas' ? 1 : 0;
        })->groupBy('status');

        $rowNumber = 1;
        $totalLunas = 0;
        $totalBelumLunas = 0;
    @endphp

    <table>
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 15%;">Kode Pembayaran</th>
                <th style="width: 25%;">Mahasiswa</th>
                <th style="width: 20%;">Jenis Tagihan</th>
                <th style="width: 15%;" class="text-right">Jumlah</th>
                <th style="width: 10%;" class="text-center">Status</th>
                <th style="width: 10%;">Tgl Bayar</th>
            </tr>
        </thead>
        <tbody>
            @if(isset($groupedData['Belum Lunas']) && $groupedData['Belum Lunas']->isNotEmpty())
                <tr class="group-header">
                    <td colspan="7"><strong>Belum Lunas (Cicilan)</strong></td>
                </tr>
                @foreach ($groupedData['Belum Lunas'] as $tagihan)
                    @php
                        $pembayaranAll = $tagihan->pembayaranAll ?? collect();
                        $totalAngsuran = $tagihan->total_angsuran ?? 0;
                        $totalBelumLunas += $totalAngsuran; // Total yang sudah diterima kasir
                        $pembayaranTerakhir = $pembayaranAll->sortByDesc('tanggal_bayar')->first();
                        $tglBayar = $pembayaranTerakhir ? \Carbon\Carbon::parse($pembayaranTerakhir->tanggal_bayar)->isoFormat('DD MMM YYYY') : '-';
                    @endphp
                    <tr>
                        <td>{{ $rowNumber++ }}</td>
                        <td>{{ $tagihan->kode_pembayaran ?? '-' }}</td>
                        <td>
                            {{ $tagihan->mahasiswa->user->nama_lengkap ?? 'N/A' }}<br>
                            <small>{{ $tagihan->mahasiswa->npm ?? 'N/A' }}</small>
                        </td>
                        <td>{{ $tagihan->tarif->nama_pembayaran ?? 'N/A' }}</td>
                        <td class="text-right">Rp {{ number_format($totalAngsuran, 0, ',', '.') }}</td>
                        <td class="text-center">
                            <span class="badge badge-belum">Belum Lunas</span>
                        </td>
                        <td>{{ $tglBayar }}</td>
                    </tr>
                @endforeach
            @endif

            @if(isset($groupedData['Lunas']) && $groupedData['Lunas']->isNotEmpty())
                <tr class="group-header">
                    <td colspan="7"><strong>Lunas</strong></td>
                </tr>
                @foreach ($groupedData['Lunas'] as $tagihan)
                    @php
                        $pembayaranAll = $tagihan->pembayaranAll ?? collect();
                        $totalAngsuran = $tagihan->total_angsuran ?? $tagihan->jumlah_tagihan;
                        $totalLunas += $totalAngsuran; // Total yang sudah diterima kasir
                        $pembayaranTerakhir = $pembayaranAll->sortByDesc('tanggal_bayar')->first();
                        $tglBayar = $pembayaranTerakhir ? \Carbon\Carbon::parse($pembayaranTerakhir->tanggal_bayar)->isoFormat('DD MMM YYYY') : '-';
                    @endphp
                    <tr>
                        <td>{{ $rowNumber++ }}</td>
                        <td>{{ $tagihan->kode_pembayaran ?? '-' }}</td>
                        <td>
                            {{ $tagihan->mahasiswa->user->nama_lengkap ?? 'N/A' }}<br>
                            <small>{{ $tagihan->mahasiswa->npm ?? 'N/A' }}</small>
                        </td>
                        <td>{{ $tagihan->tarif->nama_pembayaran ?? 'N/A' }}</td>
                        <td class="text-right">Rp {{ number_format($totalAngsuran, 0, ',', '.') }}</td>
                        <td class="text-center">
                            <span class="badge badge-lunas">Lunas</span>
                        </td>
                        <td>{{ $tglBayar }}</td>
                    </tr>
                @endforeach
            @endif

            @if(!isset($groupedData['Belum Lunas']) && !isset($groupedData['Lunas']))
                <tr>
                    <td colspan="7" class="text-center">Tidak ada data pembayaran untuk periode ini.</td>
                </tr>
            @endif

            <tr class="total-row">
                <th colspan="4" class="text-right">TOTAL BELUM LUNAS (YANG DITERIMA):</th>
                <td class="text-right">Rp {{ number_format($totalBelumLunas, 0, ',', '.') }}</td>
                <td colspan="2"></td>
            </tr>
            <tr class="total-row">
                <th colspan="4" class="text-right">TOTAL LUNAS (YANG DITERIMA):</th>
                <td class="text-right">Rp {{ number_format($totalLunas, 0, ',', '.') }}</td>
                <td colspan="2"></td>
            </tr>
            <tr class="total-row">
                <th colspan="4" class="text-right">TOTAL KESELURUHAN PENERIMAAN:</th>
                <td class="text-right">Rp {{ number_format($totalLunas + $totalBelumLunas, 0, ',', '.') }}</td>
                <td colspan="2"></td>
            </tr>
        </tbody>
    </table>

    <div class="signature">
        <div class="block">
            <div class="role">Payakumbuh, {{ \Carbon\Carbon::now()->timezone(config('app.timezone'))->isoFormat('DD MMMM YYYY') }}</div>
            <div style="height: 60px;"></div>
            <div><strong>{{ $kasir->nama_lengkap ?? $kasir->username }}</strong></div>
            <div class="role">Kasir</div>
        </div>
    </div>
</body>
</html>

