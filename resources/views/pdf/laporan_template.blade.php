<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan {{ ucfirst($jenis) }} - Periode {{ $periodeFormatted }}</title>
    <style>
        :root { --text:#222; --muted:#555; --line:#000; --soft:#F3F4F6; }
        @page { margin: 0cm; }
        body {
            margin: 5.4cm 2cm 2cm 2cm; /* ruang untuk header, selaras kwitansi */
            font-family: 'Helvetica','Arial',sans-serif; font-size: 12px; color: var(--text);
        }
        /* ===== KOP SURAT (selaras kwitansi) ===== */
        header { position: fixed; top: 1cm; left: 2cm; right: 2cm; height: auto; }
        .kop-surat { width:100%; border:1px solid #000; border-collapse: collapse; margin-bottom: 6px; }
        .kop-surat td { vertical-align: middle; padding: 4px 8px; border:1px solid #000; }
        .logo-cell { width: 100px; text-align:center; }
        .logo { width:80px; height:auto; }
        .info-cell { text-align:center; line-height:1.3; }
        .info-cell .yayasan { font-size:13px; font-weight:bold; text-transform:uppercase; }
        .info-cell .kampus { font-size:17px; font-weight:bold; text-transform:uppercase; text-decoration: underline; margin:4px 0; }
        .info-cell .alamat { font-size:10px; }

        /* ===== TYPOS & TABLE ===== */
        h1 { text-align:center; margin: 0; font-size:20px; font-weight:bold; letter-spacing:.6px; }
        h2 { text-align:center; margin: 4px 0 8px; font-size: 14px; }
        h3.semester-header { margin-top: 16px; margin-bottom: 6px; background:#f0f4f8; padding:6px; border-left:3px solid #3b82f6; font-size:12px; }
        table { width:100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border:1px solid #000; padding:7px 9px; font-size:12px; vertical-align: top; }
        th { background: var(--soft); text-align:left; }
        .group-header td { background:#e2e8f0; font-weight:600; padding:8px; border:1px solid #000; }
        .text-right{ text-align:right; } .text-center{ text-align:center; }
        .badge { display:inline-block; font-size:10px; padding:2px 8px; border-radius:8px; font-weight:700; border:1px solid #000; }
        .badge-lunas { background:#DCFCE7; color:#166534; }
        .badge-belum { background:#FEF3C7; color:#92400E; }
        .badge-ganjil { background:#DBEAFE; color:#1E40AF; }
        .badge-genap { background:#E9D5FF; color:#6B21A8; }
        small { font-size: 10px; color:#000; }
        .total-row th, .total-row td { background:#F8F9FA; font-weight:700; border-top:2px solid #000; }
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
    @if($jenis === 'mahasiswa')
        <h1>DATA MAHASISWA</h1>
    @else
        <h1>LAPORAN {{ strtoupper($jenis) }}</h1>
    @endif
    <h2>Periode: {{ $periodeFormatted }}</h2>

    @if($jenis === 'mahasiswa')
        @php $mahasiswaCount = 0; @endphp
        @if($data && count($data) > 0)
            <table>
                <thead>
                    <tr>
                        <th style="width: 5%;">No</th>
                        <th style="width: 12%;">NPM</th>
                        <th style="width: 25%;">Nama Lengkap</th>
                        <th style="width: 20%;">Email</th>
                        <th style="width: 20%;">Program Studi</th>
                        <th style="width: 10%;" class="text-center">Angkatan</th>
                        <th style="width: 8%;" class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data as $index => $mhs)
                        @php $mahasiswaCount++; @endphp
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $mhs->npm ?? '-' }}</td>
                            <td>{{ $mhs->user->nama_lengkap ?? '-' }}</td>
                            <td>{{ $mhs->user->email ?? '-' }}</td>
                            <td>{{ $mhs->program_studi ?? '-' }}</td>
                            <td class="text-center">{{ $mhs->angkatan ?? '-' }}</td>
                            <td class="text-center">{{ $mhs->status ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <p style="margin-top: 15px;"><strong>Total Mahasiswa: {{ $mahasiswaCount }}</strong></p>
        @else
            <p style="text-align: center; margin-top: 20px;">Tidak ada data mahasiswa ditemukan untuk angkatan {{ $periodeFormatted }}.</p>
        @endif

    @elseif($jenis === 'pembayaran')
        @php
            function parseSemesterLabel($semesterLabel) {
                if (!$semesterLabel) {
                    return ['tahunAkademik' => '-', 'semester' => '-'];
                }
                $parts = explode(' ', trim($semesterLabel));
                if (count($parts) >= 2) {
                    return [
                        'tahunAkademik' => $parts[0],
                        'semester' => $parts[1]
                    ];
                }
                return ['tahunAkademik' => $semesterLabel, 'semester' => '-'];
            }

            function calculateSemesterNumber($tahunAkademik, $angkatan, $semesterType) {
                if (!$tahunAkademik || !$angkatan || !$semesterType || $tahunAkademik === '-') {
                    return null;
                }
                try {
                    $tahunParts = explode('/', $tahunAkademik);
                    if (count($tahunParts) < 1) return null;

                    $tahunAkademikAwal = (int) $tahunParts[0];
                    $angkatanInt = (int) $angkatan;

                    if ($tahunAkademikAwal === 0 || $angkatanInt === 0) {
                        return null;
                    }

                    $selisihTahun = $tahunAkademikAwal - $angkatanInt;
                    $semesterTypeLower = strtolower($semesterType);

                    if ($semesterTypeLower === 'ganjil') {
                        return $selisihTahun * 2 + 1;
                    } else if ($semesterTypeLower === 'genap') {
                        return $selisihTahun * 2 + 2;
                    }
                    return null;
                } catch (Exception $e) {
                    return null;
                }
            }
        @endphp
        <table>
            <thead>
                <tr>
                    <th style="width: 4%;">No</th>
                    <th style="width: 12%;">Kode Pembayaran</th>
                    <th style="width: 18%;">Mahasiswa</th>
                    <th style="width: 12%;">Tahun Akademik</th>
                    <th style="width: 10%;" class="text-center">Semester</th>
                    <th style="width: 20%;">Jenis Tagihan</th>
                    <th style="width: 12%;" class="text-right">Jumlah</th>
                    <th style="width: 8%;" class="text-center">Status</th>
                    <th style="width: 12%;">Tgl Bayar</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $groupedData = $data->sortBy(function($tagihan) {
                        return $tagihan->status === 'Lunas' ? 1 : 0;
                    })->groupBy('status');

                    $rowNumber = 1;
                    $totalLunas = 0;
                    $totalBelumLunas = 0;
                @endphp

                @if(isset($groupedData['Belum Lunas']) && $groupedData['Belum Lunas']->isNotEmpty())
                    @php
                        $belumDibayar = $groupedData['Belum Lunas']->filter(function($tagihan) {
                            $pembayaranAll = $tagihan->pembayaranAll ?? collect();
                            return $pembayaranAll->isEmpty() || ($tagihan->total_angsuran ?? 0) == 0;
                        });
                        $belumLunas = $groupedData['Belum Lunas']->filter(function($tagihan) {
                            $pembayaranAll = $tagihan->pembayaranAll ?? collect();
                            return $pembayaranAll->isNotEmpty() && ($tagihan->total_angsuran ?? 0) > 0;
                        });
                    @endphp

                    @if($belumDibayar->isNotEmpty())
                        <tr class="group-header">
                            <td colspan="9"><strong>Belum Dibayarkan</strong></td>
                        </tr>
                        @foreach ($belumDibayar as $tagihan)
                            @php
                                $totalBelumLunas += $tagihan->jumlah_tagihan;
                                $semesterInfo = parseSemesterLabel($tagihan->semester_label ?? null);
                                $angkatan = $tagihan->mahasiswa->angkatan ?? null;
                                $semesterNumber = calculateSemesterNumber($semesterInfo['tahunAkademik'], $angkatan, $semesterInfo['semester']);
                                if (!$semesterNumber) {
                                    $semesterNumber = $tagihan->mahasiswa->semester_aktif ?? null;
                                }
                                $programStudi = $tagihan->mahasiswa->program_studi ?? null;
                            @endphp
                            <tr>
                                <td>{{ $rowNumber++ }}</td>
                                <td>{{ $tagihan->kode_pembayaran ?? '-' }}</td>
                                <td>
                                    {{ $tagihan->mahasiswa->user->nama_lengkap ?? 'N/A' }}<br>
                                    <small>{{ $tagihan->mahasiswa->npm ?? 'N/A' }}</small>
                                </td>
                                <td>{{ $semesterInfo['tahunAkademik'] }}</td>
                                <td class="text-center">
                                    @if($semesterInfo['semester'] !== '-')
                                        <span class="badge {{ strtolower($semesterInfo['semester']) === 'ganjil' ? 'badge-ganjil' : 'badge-genap' }}">{{ $semesterInfo['semester'] }}</span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    <strong>{{ $tagihan->tarif->nama_pembayaran ?? 'N/A' }}</strong><br>
                                    <small style="color: #666;">
                                        @if($semesterNumber)
                                            Semester {{ $semesterNumber }}
                                            @if($programStudi) • @endif
                                        @endif
                                        @if($programStudi)
                                            {{ $programStudi }}
                                        @endif
                                        @if(!$semesterNumber && !$programStudi)
                                            -
                                        @endif
                                    </small>
                                </td>
                                <td class="text-right">Rp {{ number_format($tagihan->jumlah_tagihan, 0, ',', '.') }}</td>
                                <td class="text-center">
                                    <span class="badge badge-belum">Belum Dibayarkan</span>
                                </td>
                                <td>-</td>
                            </tr>
                        @endforeach
                    @endif

                    @if($belumLunas->isNotEmpty())
                        <tr class="group-header">
                            <td colspan="9"><strong>Belum Lunas</strong></td>
                        </tr>
                        @foreach ($belumLunas as $tagihan)
                            @php
                                $totalBelumLunas += $tagihan->jumlah_tagihan;
                                $pembayaranAll = $tagihan->pembayaranAll ?? collect();
                                $pembayaranTerakhir = $pembayaranAll->sortByDesc('tanggal_bayar')->first();
                                $tglBayar = $pembayaranTerakhir ? \Carbon\Carbon::parse($pembayaranTerakhir->tanggal_bayar)->isoFormat('DD MMM YYYY') : '-';
                                $semesterInfo = parseSemesterLabel($tagihan->semester_label ?? null);
                                $angkatan = $tagihan->mahasiswa->angkatan ?? null;
                                $semesterNumber = calculateSemesterNumber($semesterInfo['tahunAkademik'], $angkatan, $semesterInfo['semester']);
                                if (!$semesterNumber) {
                                    $semesterNumber = $tagihan->mahasiswa->semester_aktif ?? null;
                                }
                                $programStudi = $tagihan->mahasiswa->program_studi ?? null;
                            @endphp
                            <tr>
                                <td>{{ $rowNumber++ }}</td>
                                <td>{{ $tagihan->kode_pembayaran ?? '-' }}</td>
                                <td>
                                    {{ $tagihan->mahasiswa->user->nama_lengkap ?? 'N/A' }}<br>
                                    <small>{{ $tagihan->mahasiswa->npm ?? 'N/A' }}</small>
                                </td>
                                <td>{{ $semesterInfo['tahunAkademik'] }}</td>
                                <td class="text-center">
                                    @if($semesterInfo['semester'] !== '-')
                                        <span class="badge {{ strtolower($semesterInfo['semester']) === 'ganjil' ? 'badge-ganjil' : 'badge-genap' }}">{{ $semesterInfo['semester'] }}</span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    <strong>{{ $tagihan->tarif->nama_pembayaran ?? 'N/A' }}</strong><br>
                                    <small style="color: #666;">
                                        @if($semesterNumber)
                                            Semester {{ $semesterNumber }}
                                            @if($programStudi) • @endif
                                        @endif
                                        @if($programStudi)
                                            {{ $programStudi }}
                                        @endif
                                        @if(!$semesterNumber && !$programStudi)
                                            -
                                        @endif
                                    </small>
                                </td>
                                <td class="text-right">Rp {{ number_format($tagihan->jumlah_tagihan, 0, ',', '.') }}</td>
                                <td class="text-center">
                                    <span class="badge badge-belum">Belum Lunas</span>
                                </td>
                                <td>{{ $tglBayar }}</td>
                            </tr>
                        @endforeach
                    @endif
                @endif

                @if(isset($groupedData['Lunas']) && $groupedData['Lunas']->isNotEmpty())
                     <tr class="group-header">
                        <td colspan="9"><strong>Lunas</strong></td>
                    </tr>
                    @foreach ($groupedData['Lunas'] as $tagihan)
                        @php
                            $totalLunas += $tagihan->jumlah_tagihan;
                            $pembayaran = $tagihan->pembayaran;
                            $tglBayar = $pembayaran ? \Carbon\Carbon::parse($pembayaran->tanggal_bayar)->isoFormat('DD MMM YYYY') : '-';
                            $semesterInfo = parseSemesterLabel($tagihan->semester_label ?? null);
                            $angkatan = $tagihan->mahasiswa->angkatan ?? null;
                            $semesterNumber = calculateSemesterNumber($semesterInfo['tahunAkademik'], $angkatan, $semesterInfo['semester']);
                            if (!$semesterNumber) {
                                $semesterNumber = $tagihan->mahasiswa->semester_aktif ?? null;
                            }
                            $programStudi = $tagihan->mahasiswa->program_studi ?? null;
                        @endphp
                        <tr>
                            <td>{{ $rowNumber++ }}</td>
                            <td>{{ $tagihan->kode_pembayaran ?? '-' }}</td>
                            <td>
                                {{ $tagihan->mahasiswa->user->nama_lengkap ?? 'N/A' }}<br>
                                <small>{{ $tagihan->mahasiswa->npm ?? 'N/A' }}</small>
                            </td>
                            <td>{{ $semesterInfo['tahunAkademik'] }}</td>
                            <td class="text-center">
                                @if($semesterInfo['semester'] !== '-')
                                    <span class="badge {{ strtolower($semesterInfo['semester']) === 'ganjil' ? 'badge-ganjil' : 'badge-genap' }}">{{ $semesterInfo['semester'] }}</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                <strong>{{ $tagihan->tarif->nama_pembayaran ?? 'N/A' }}</strong><br>
                                <small style="color: #666;">
                                    @if($semesterNumber)
                                        Semester {{ $semesterNumber }}
                                        @if($programStudi) • @endif
                                    @endif
                                    @if($programStudi)
                                        {{ $programStudi }}
                                    @endif
                                    @if(!$semesterNumber && !$programStudi)
                                        -
                                    @endif
                                </small>
                            </td>
                            <td class="text-right">Rp {{ number_format($tagihan->jumlah_tagihan, 0, ',', '.') }}</td>
                            <td class="text-center">
                                <span class="badge badge-lunas">Lunas</span>
                            </td>
                            <td>{{ $tglBayar }}</td>
                        </tr>
                    @endforeach
                @endif

                 @if(!isset($groupedData['Belum Lunas']) && !isset($groupedData['Lunas']))
                    <tr>
                        <td colspan="9" class="text-center">Tidak ada data pembayaran/tagihan untuk periode ini.</td>
                    </tr>
                @endif

                @if($totalLunas > 0 || $totalBelumLunas > 0)
                    <tr class="total-row">
                        <th colspan="6" class="text-right">Total Belum Dibayarkan:</th>
                        <th class="text-right">Rp {{ number_format($totalBelumLunas, 0, ',', '.') }}</th>
                        <th colspan="2"></th>
                    </tr>
                    <tr class="total-row">
                        <th colspan="6" class="text-right">Total Lunas:</th>
                        <th class="text-right">Rp {{ number_format($totalLunas, 0, ',', '.') }}</th>
                        <th colspan="2"></th>
                    </tr>
                @endif

            </tbody>
        </table>

    @else
        <p>Jenis laporan tidak dikenal.</p>
    @endif

</body>
</html>
