<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Surat Undangan Rapat / Kegiatan</title>
    <style>
        @page {
            margin: 2cm 2.5cm 2cm 2.5cm;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            line-height: 1.4;
            color: #111;
        }
        .header-table {
            width: 100%;
            border-bottom: 3px double #000;
            padding-bottom: 8px;
            margin-bottom: 18px;
        }
        .header-logo {
            width: 85px;
            text-align: center;
            vertical-align: middle;
        }
        .header-text {
            text-align: center;
            vertical-align: middle;
        }
        .header-text h2 {
            margin: 0;
            font-size: 13pt;
            font-weight: bold;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .header-text h3 {
            margin: 1px 0 0 0;
            font-size: 12pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        .header-text p {
            margin: 3px 0 0 0;
            font-size: 8.5pt;
            color: #222;
            line-height: 1.25;
        }
        .meta-table {
            width: 100%;
            margin-bottom: 15px;
        }
        .meta-table td {
            vertical-align: top;
            font-size: 11pt;
        }
        .content-table {
            width: 100%;
            margin-top: 8px;
            margin-bottom: 12px;
        }
        .content-table td {
            vertical-align: top;
            font-size: 11pt;
            padding: 2px 0;
        }
        .signature-box {
            float: right;
            width: 48%;
            margin-top: 25px;
            text-align: center;
            page-break-inside: avoid;
        }
        .signature-box table {
            width: 100%;
            border: none;
            text-align: center;
        }
        .signature-box td {
            font-size: 11pt;
            text-align: center;
        }
        .clear {
            clear: both;
        }
    </style>
</head>
<body>

    @php
        $tglRaw = $employee->start ? $employee->start : ($employee->start_date ?: date('Y-m-d'));
        $namaHari = [
            'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
        ];
        $namaBulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
            7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        $ts = strtotime($tglRaw);
        $hari = $namaHari[date('l', $ts)] ?? date('l', $ts);
        $tglFormatted = date('d', $ts) . ' ' . ($namaBulan[(int)date('m', $ts)] ?? date('F', $ts)) . ' ' . date('Y', $ts);

        $startJam = $employee->start_jam ? substr($employee->start_jam, 0, 5) : '08:30';
        $endJam = $employee->end_jam ? substr($employee->end_jam, 0, 5) : 'Selesai';

        $perihal = $employee->text ?: ($employee->title ?: ($employee->agenda ?: 'Undangan Rapat / Kegiatan'));
        $nomorSurat = $employee->nomor ?: ('B-' . ($employee->id ?? 1) . '/74000/PL.100/' . date('Y'));
        $agenda = $employee->agenda ?: $perihal;
        $tempat = $employee->tempat ?: 'Ruang Rapat BPS Provinsi Sulawesi Tenggara';
        $pemimpin = $employee->penanggung_jawab ?: ($employee->pemimpin ?: 'Ketua Tim');
    @endphp

    {{-- KOP SURAT RESMI BPS --}}
    <table class="header-table" cellpadding="0" cellspacing="0">
        <tr>
            <td class="header-logo">
                @if(file_exists(public_path('assets/img/logo-sikeren-square.png')))
                    <img src="{{ public_path('assets/img/logo-sikeren-square.png') }}" alt="Logo" style="height: 68px; width: auto;">
                @elseif(file_exists(public_path('assets/img/logo-sikeren.png')))
                    <img src="{{ public_path('assets/img/logo-sikeren.png') }}" alt="Logo" style="height: 60px; width: auto;">
                @endif
            </td>
            <td class="header-text">
                <h2>BADAN PUSAT STATISTIK</h2>
                <h3>PROVINSI SULAWESI TENGGARA</h3>
                <p>
                    Jl. Mayjen Sutoyo No. 49, Kendari 93121, Telp: (0401) 3121544, Faks: (0401) 3121544<br>
                    Website: sultra.bps.go.id | Email: bps7400@bps.go.id
                </p>
            </td>
            <td style="width: 40px; text-align: right; vertical-align: top;">
                @if(!empty($qrcode))
                    <img src="data:image/svg+xml;base64,{!! $qrcode !!}" style="width: 48px; height: 48px;" alt="QR">
                @endif
            </td>
        </tr>
    </table>

    {{-- TANGGAL SURAT --}}
    <div style="text-align: right; margin-bottom: 12px; font-size: 11pt;">
        Kendari, {{ $tglFormatted }}
    </div>

    {{-- NOMOR & PERIHAL --}}
    <table class="meta-table" cellpadding="0" cellspacing="0">
        <tr>
            <td style="width: 90px;">Nomor</td>
            <td style="width: 15px;">:</td>
            <td>{{ $nomorSurat }}</td>
        </tr>
        <tr>
            <td>Lampiran</td>
            <td>:</td>
            <td>-</td>
        </tr>
        <tr>
            <td>Perihal</td>
            <td>:</td>
            <td><strong>{{ $perihal }}</strong></td>
        </tr>
    </table>

    {{-- TUJUAN --}}
    <div style="margin-top: 10px; margin-bottom: 14px;">
        Kepada Yang Terhormat :<br>
        <strong>Bapak/Ibu Peserta Kegiatan / Rapat</strong><br>
        di -<br>
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Tempat
    </div>

    {{-- ISI SURAT --}}
    <p style="text-align: justify; text-indent: 2.5em; margin-bottom: 8px;">
        Dalam rangka pelaksanaan kegiatan <strong>{{ $agenda }}</strong>, bersama ini kami mengundang Bapak/Ibu untuk menghadiri dan berpartisipasi aktif pada kegiatan yang akan diselenggarakan pada:
    </p>

    <table class="content-table" cellpadding="0" cellspacing="0" style="margin-left: 2em; width: 90%;">
        <tr>
            <td style="width: 140px;">Hari, Tanggal</td>
            <td style="width: 15px;">:</td>
            <td><strong>{{ $hari }}, {{ $tglFormatted }}</strong></td>
        </tr>
        <tr>
            <td>Waktu</td>
            <td>:</td>
            <td>{{ $startJam }} s.d. {{ $endJam }} WITA</td>
        </tr>
        <tr>
            <td>Tempat / Media</td>
            <td>:</td>
            <td>{{ $tempat }}</td>
        </tr>
        <tr>
            <td>Agenda Bahasan</td>
            <td>:</td>
            <td>{{ $agenda }}</td>
        </tr>
    </table>

    <p style="text-align: justify; text-indent: 2.5em; margin-top: 10px; margin-bottom: 18px;">
        Mengingat pentingnya agenda tersebut, diharapkan kehadiran Bapak/Ibu tepat waktu sesuai dengan jadwal yang telah ditentukan. Atas perhatian, kesediaan, dan kerja samanya, diucapkan terima kasih.
    </p>

    {{-- TANDA TANGAN --}}
    <div class="signature-box">
        <table>
            @if(stripos($pemimpin, 'Hadi') !== false || stripos($pemimpin, 'Agnes') !== false || stripos($pemimpin, 'Kepala') !== false)
                <tr>
                    <td><strong>Kepala Badan Pusat Statistik<br>Provinsi Sulawesi Tenggara,</strong></td>
                </tr>
            @else
                <tr>
                    <td><strong>a.n. Kepala Badan Pusat Statistik<br>Provinsi Sulawesi Tenggara</strong><br><span style="font-size: 10pt; color: #333;">Ketua Tim / Penanggung Jawab Kegiatan,</span></td>
                </tr>
            @endif
            <tr>
                <td style="height: 60px; vertical-align: middle;">
                    @if(!empty($qrcode2))
                        <img src="data:image/svg+xml;base64,{!! $qrcode2 !!}" style="height: 52px; width: auto;" alt="Signature QR">
                    @else
                        <div style="height: 45px;"></div>
                    @endif
                </td>
            </tr>
            <tr>
                <td>
                    <strong style="text-decoration: underline;">{{ $pemimpin }}</strong>
                </td>
            </tr>
        </table>
    </div>

    <div class="clear"></div>

</body>
</html>
