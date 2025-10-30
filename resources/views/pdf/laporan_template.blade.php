<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan {{ ucfirst($jenis) }} - Periode {{ $periodeFormatted }}</title>
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
            font-family: 'Helvetica', sans-serif;
            font-size: 10pt;
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


        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; vertical-align: top; }
        th { background-color: #f2f2f2; font-weight: bold; }
        h1, h2 { text-align: center; margin-bottom: 5px;}
        h3.semester-header { margin-top: 20px; margin-bottom: 5px; background-color: #f0f4f8; padding: 5px; border-left: 3px solid #3b82f6; font-size: 11pt;}
        .group-header td { background-color: #e2e8f0; font-weight: bold; padding: 8px; text-align: left; border: 1px solid #ddd; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .badge { display: inline-block; padding: 2px 6px; font-size: 8pt; border-radius: 4px; color: white; white-space: nowrap; }
        .badge-lunas { background-color: #10B981; } /* Hijau */
        .badge-belum { background-color: #F59E0B; } /* Kuning */
        small { font-size: 8pt; color: #666; }
        .total-row th, .total-row td { background-color: #f8f9fa; font-weight: bold; border-top: 2px solid #ccc; }
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
    <h1>Laporan {{ ucfirst($jenis) }}</h1>
    <h2>Periode: {{ $periodeFormatted }}</h2>

    @if($jenis === 'mahasiswa')
        {{-- Logika Laporan Mahasiswa (Tetap Sama) --}}
        @php $mahasiswaCount = 0; @endphp
        @forelse($data as $semester => $mahasiswaGroup)
            <h3 class="semester-header">Semester {{ $semester ?? 'Tidak Diketahui' }}</h3>
            <table>
                <thead>
                    <tr>
                        <th style="width: 5%;">No</th>
                        <th style="width: 15%;">NPM</th>
                        <th style="width: 30%;">Nama Lengkap</th>
                        <th style="width: 25%;">Program Studi</th>
                        <th style="width: 10%;" class="text-center">Angkatan</th>
                        <th style="width: 15%;" class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($mahasiswaGroup as $index => $mhs)
                        @php $mahasiswaCount++; @endphp
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $mhs->npm ?? '-' }}</td>
                            <td>{{ $mhs->user->nama_lengkap ?? '-' }}</td>
                            <td>{{ $mhs->program_studi ?? '-' }}</td>
                            <td class="text-center">{{ $mhs->angkatan ?? '-' }}</td>
                            <td class="text-center">{{ $mhs->status ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center">Tidak ada data mahasiswa untuk semester ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        @empty
            <p style="text-align: center; margin-top: 20px;">Tidak ada data mahasiswa ditemukan.</p>
        @endforelse
         @if($mahasiswaCount > 0)
            <p style="margin-top: 15px;"><strong>Total Mahasiswa: {{ $mahasiswaCount }}</strong></p>
         @endif

    @elseif($jenis === 'pembayaran')
        {{-- ====================================================== --}}
        {{-- !! MODIFIKASI LAPORAN PEMBAYARAN DIMULAI DARI SINI !! --}}
        {{-- ====================================================== --}}
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
                @php
                    $groupedData = $data->sortBy(function($tagihan) {
                        return $tagihan->status === 'Lunas' ? 1 : 0;
                    })->groupBy('status');

                    $rowNumber = 1;
                    $totalLunas = 0;
                    $totalBelumLunas = 0;
                @endphp

                @if(isset($groupedData['Belum Lunas']) && $groupedData['Belum Lunas']->isNotEmpty())
                    <tr class="group-header">
                        <td colspan="7"><strong>Belum Dibayarkan</strong></td>
                    </tr>
                    @foreach ($groupedData['Belum Lunas'] as $tagihan)
                        @php $totalBelumLunas += $tagihan->jumlah_tagihan; @endphp
                        <tr>
                            <td>{{ $rowNumber++ }}</td>
                            <td>{{ $tagihan->kode_pembayaran ?? '-' }}</td>
                            <td>
                                {{ $tagihan->mahasiswa->user->nama_lengkap ?? 'N/A' }}<br>
                                <small>{{ $tagihan->mahasiswa->npm ?? 'N/A' }}</small>
                            </td>
                            <td>{{ $tagihan->tarif->nama_pembayaran ?? 'N/A' }}</td>
                            <td class="text-right">Rp {{ number_format($tagihan->jumlah_tagihan, 0, ',', '.') }}</td>
                            <td class="text-center">
                                <span class="badge badge-belum">Belum Dibayarkan</span>
                            </td>
                            <td>-</td>
                        </tr>
                    @endforeach
                @endif

                @if(isset($groupedData['Lunas']) && $groupedData['Lunas']->isNotEmpty())
                     <tr class="group-header">
                        <td colspan="7"><strong>Lunas</strong></td>
                    </tr>
                    @foreach ($groupedData['Lunas'] as $tagihan)
                        @php
                            $totalLunas += $tagihan->jumlah_tagihan;
                            $pembayaran = $tagihan->pembayaran;
                            $tglBayar = $pembayaran ? \Carbon\Carbon::parse($pembayaran->tanggal_bayar)->isoFormat('DD MMM YYYY') : '-';
                        @endphp
                        <tr>
                            <td>{{ $rowNumber++ }}</td>
                            <td>{{ $tagihan->kode_pembayaran ?? '-' }}</td>
                            <td>
                                {{ $tagihan->mahasiswa->user->nama_lengkap ?? 'N/A' }}<br>
                                <small>{{ $tagihan->mahasiswa->npm ?? 'N/A' }}</small>
                            </td>
                            <td>{{ $tagihan->tarif->nama_pembayaran ?? 'N/A' }}</td>
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
                        <td colspan="7" class="text-center">Tidak ada data pembayaran/tagihan untuk periode ini.</td>
                    </tr>
                @endif

                @if($totalLunas > 0 || $totalBelumLunas > 0)
                    <tr class="total-row">
                        <th colspan="4" class="text-right">Total Belum Dibayarkan:</th>
                        <th class="text-right">Rp {{ number_format($totalBelumLunas, 0, ',', '.') }}</th>
                        <th colspan="2"></th>
                    </tr>
                    <tr class="total-row">
                        <th colspan="4" class="text-right">Total Lunas:</th>
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
