<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Pembayaran — {{ $periodeFormatted }}</title>
    <style>
        :root { --text:#222; --soft:#F3F4F6; }
        @page { margin: 0cm; }
        body {
            margin: 5.4cm 2cm 2cm 2cm;
            font-family: 'Helvetica','Arial',sans-serif; font-size: 12px; color: var(--text);
        }
        header { position: fixed; top: 1cm; left: 2cm; right: 2cm; height: auto; }
        .kop-surat { width:100%; border:1px solid #000; border-collapse: collapse; margin-bottom: 6px; }
        .kop-surat td { vertical-align: middle; padding: 4px 8px; border:1px solid #000; }
        .logo-cell { width: 100px; text-align: center; }
        .logo { width: 80px; height: auto; }
        .info-cell { text-align:center; line-height:1.3; }
        .info-cell .yayasan { font-size:13px; font-weight:bold; text-transform:uppercase; }
        .info-cell .kampus { font-size:17px; font-weight:bold; text-transform:uppercase; text-decoration: underline; margin:4px 0; }
        .info-cell .alamat { font-size:10px; }
        h1 { text-align:center; margin: 0 0 4px; font-size:20px; font-weight:bold; letter-spacing:.4px; }
        h2 { text-align:center; margin: 0 0 10px; font-size: 13px; font-weight: normal; color: #333; }
        .summary { margin: 0 0 14px; padding: 8px 10px; font-size: 11px; background: #f8fafc; border: 1px solid #e2e8f0; }
        table { width:100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border:1px solid #000; padding:6px 8px; font-size:11px; vertical-align: top; }
        th { background: var(--soft); text-align:left; }
        .text-right { text-align:right; } .text-center { text-align:center; }
        .total-row th, .total-row td { background:#F8F9FA; font-weight:700; }
        small { font-size: 10px; color:#555; }
    </style>
</head>
<body>
    <header>
        <table class="kop-surat">
            <tr>
                @include('partials.pdf_kampus_logo_cell')
                <td class="info-cell">
                    <div class="yayasan">YAYASAN PENDIDIKAN TINGGI PAYAKUMBUH</div>
                    <div class="kampus">SEKOLAH TINGGI TEKNOLOGI PAYAKUMBUH</div>
                    <div class="alamat">Jln. Khatib Sulaiman Sawah Padang, Telp. 0752-796063<br>Website: www.sttpyk.ac.id</div>
                </td>
            </tr>
        </table>
    </header>

    <h1>LAPORAN PEMBAYARAN</h1>
    <h2>Periode: {{ $periodeFormatted }}@if(!empty($contextLabel)) &mdash; {{ $contextLabel }}@endif</h2>

    @if (!empty($summaryPembayaran))
        <div class="summary">
            Jumlah transaksi: {{ number_format($summaryPembayaran['jumlah_transaksi']) }}
            &bull; Total uang masuk: Rp {{ number_format($summaryPembayaran['total_uang_masuk'], 0, ',', '.') }}
        </div>
    @endif

    <table>
        <thead>
            <tr>
                <th style="width:4%">No</th>
                <th style="width:11%">Tgl Bayar</th>
                <th style="width:20%">Mahasiswa</th>
                <th style="width:18%">Jenis Tagihan</th>
                <th style="width:14%">Semester Tagihan</th>
                <th style="width:13%" class="text-right">Jumlah Bayar</th>
                <th style="width:10%">Metode</th>
                <th style="width:10%">Kasir</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($data as $i => $bayar)
                @php
                    $tagihan = $bayar->tagihan;
                    $tgl = $bayar->tanggal_bayar ? \Carbon\Carbon::parse($bayar->tanggal_bayar)->isoFormat('DD MMM YYYY') : '-';
                @endphp
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $tgl }}</td>
                    <td>
                        {{ $tagihan?->mahasiswa?->user?->nama_lengkap ?? 'N/A' }}<br>
                        <small>{{ $tagihan?->mahasiswa?->npm ?? '' }}</small>
                    </td>
                    <td>{{ $tagihan?->jenis_tampilan ?? $tagihan?->tarif?->nama_pembayaran ?? 'N/A' }}</td>
                    <td><small>{{ $tagihan?->semester_label ?? '—' }}</small></td>
                    <td class="text-right">Rp {{ number_format((float) $bayar->jumlah_bayar, 0, ',', '.') }}</td>
                    <td>{{ $bayar->metode_pembayaran ?? '—' }}</td>
                    <td><small>{{ $bayar->userKasir?->nama_lengkap ?? '—' }}</small></td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center">Tidak ada transaksi pembayaran pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
        @if ($data->isNotEmpty())
            <tfoot>
                <tr class="total-row">
                    <td colspan="5" class="text-right">Total Uang Masuk</td>
                    <td class="text-right">Rp {{ number_format((float) $data->sum('jumlah_bayar'), 0, ',', '.') }}</td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
        @endif
    </table>
</body>
</html>
