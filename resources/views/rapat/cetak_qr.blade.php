<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak QR Presensi - {{ $task->text ?? 'Rapat BPS' }}</title>
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/img/favicon-32x32.png') }}?v=2">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Plus Jakarta Sans', 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: #f1f5f9;
            color: #0f172a;
        }

        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background-color: #ffffff !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .print-card {
                box-shadow: none !important;
                border: 2px solid #0f172a !important;
                border-radius: 16px !important;
                margin: 0 !important;
                max-width: 100% !important;
                width: 100% !important;
                padding: 24px !important;
                page-break-inside: avoid;
            }
            @page {
                size: A4 portrait;
                margin: 1.5cm;
            }
        }
    </style>
</head>
<body class="min-h-screen py-8 px-4 flex flex-col items-center justify-start">

    {{-- TOP ACTION BAR (HIDDEN IN PRINT) --}}
    <div class="no-print w-full max-w-2xl mb-6 flex flex-wrap items-center justify-between gap-3 bg-white p-4 rounded-2xl shadow-sm border border-slate-200">
        <a href="{{ url('/daftarkegiatan/' . $task->id) }}" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold text-slate-600 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            <span>Kembali ke Detail Rapat</span>
        </a>

        <div class="flex items-center gap-2">
            <span class="text-xs text-slate-500 hidden sm:inline">Ukuran disarankan: <strong>A4 Portrait</strong></span>
            <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs font-bold bg-blue-600 hover:bg-blue-700 text-white shadow-md hover:shadow-lg transition active:scale-95 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Cetak Lembar QR (Print)</span>
            </button>
        </div>
    </div>

    {{-- PRINTABLE DOCUMENT CONTAINER --}}
    <div class="print-card w-full max-w-2xl bg-white rounded-3xl border-2 border-slate-300 shadow-xl p-8 sm:p-12 text-center flex flex-col justify-between">
        
        {{-- KOP SURAT / HEADER RESMI BPS --}}
        <div class="border-b-2 border-slate-800 pb-5 mb-6 text-center">
            <div class="flex items-center justify-center gap-3 mb-2">
                <img src="{{ asset('assets/img/logo-bps.png') }}" alt="Logo BPS" class="h-12 w-auto object-contain" onerror="this.style.display='none'">
                <div>
                    <h2 class="text-sm font-extrabold uppercase tracking-wider text-slate-800 leading-tight">
                        Badan Pusat Statistik
                    </h2>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-600 leading-tight">
                        Provinsi Sulawesi Tenggara
                    </h3>
                </div>
            </div>
            <div class="mt-3 inline-block px-4 py-1 rounded-full bg-slate-900 text-white text-xs font-black uppercase tracking-widest">
                LEMBAR PRESENSI KEHADIRAN (QR CODE)
            </div>
        </div>

        {{-- DETAIL INFORMASI RAPAT --}}
        <div class="space-y-4 mb-6 text-left bg-slate-50 p-5 rounded-2xl border border-slate-200">
            <div>
                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block">Agenda Rapat:</span>
                <h1 class="text-lg sm:text-xl font-black text-slate-900 leading-snug">
                    {{ $task->text ?? 'Rapat Koordinasi BPS' }}
                </h1>
            </div>

            <div class="grid grid-cols-2 gap-3 text-xs pt-2 border-t border-slate-200">
                <div>
                    <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block">Hari / Tanggal:</span>
                    <span class="font-bold text-slate-800">
                        {{ \Carbon\Carbon::parse($task->start_date ?? date('Y-m-d'))->translatedFormat('l, d F Y') }}
                    </span>
                </div>
                <div>
                    <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block">Waktu Pelaksanaan:</span>
                    <span class="font-bold text-slate-800">
                        {{ substr($task->start_jam ?? '08:30', 0, 5) }} WITA - {{ !empty($task->end_jam) ? substr($task->end_jam, 0, 5) . ' WITA' : 'Selesai' }}
                    </span>
                </div>
                <div>
                    <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block">Ruangan / Tempat:</span>
                    <span class="font-bold text-slate-800">
                        {{ $task->tempat ?? 'Aula BPS Provinsi Sulawesi Tenggara' }}
                    </span>
                </div>
                <div>
                    <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block">Penanggung Jawab (PJ):</span>
                    <span class="font-bold text-slate-800">
                        {{ $task->penanggung_jawab ?? ($task->pemimpin ?? '-') }}
                    </span>
                </div>
            </div>
        </div>

        {{-- MAIN QR CODE DISPLAY --}}
        <div class="my-auto py-2 flex flex-col items-center justify-center">
            <div class="p-5 bg-white rounded-3xl border-4 border-slate-900 shadow-md inline-block">
                <div class="flex items-center justify-center">
                    {!! $qrcode !!}
                </div>
            </div>
            
            <div class="mt-4 space-y-1">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-blue-100 text-blue-900 font-extrabold text-xs">
                    <svg class="w-3.5 h-3.5 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                    <span>PINDAI (SCAN) UNTUK MENGISI DAFTAR HADIR</span>
                </span>
                <p class="text-xs text-slate-600 font-medium max-w-md mx-auto pt-1">
                    Gunakan kamera ponsel Anda untuk memindai kode QR di atas untuk mencatat kehadiran resmi Anda pada kegiatan ini.
                </p>
            </div>
        </div>

        {{-- PETUNJUK PESERTA --}}
        <div class="mt-6 pt-5 border-t border-slate-200 grid grid-cols-3 gap-2 text-left text-[11px] text-slate-600">
            <div class="flex items-start gap-2">
                <span class="w-5 h-5 rounded-full bg-slate-900 text-white font-bold flex items-center justify-center shrink-0 text-[10px]">1</span>
                <span>Buka kamera ponsel atau aplikasi pemindai QR.</span>
            </div>
            <div class="flex items-start gap-2">
                <span class="w-5 h-5 rounded-full bg-slate-900 text-white font-bold flex items-center justify-center shrink-0 text-[10px]">2</span>
                <span>Arahkan kamera ke kode QR di atas.</span>
            </div>
            <div class="flex items-start gap-2">
                <span class="w-5 h-5 rounded-full bg-slate-900 text-white font-bold flex items-center justify-center shrink-0 text-[10px]">3</span>
                <span>Masuk dengan akun BPS dan konfirmasi kehadiran.</span>
            </div>
        </div>

        {{-- FOOTER RESMI --}}
        <div class="mt-6 pt-3 border-t border-slate-100 flex items-center justify-between text-[10px] text-slate-400">
            <span>Sistem Kegiatan Terencana (Sikeren) &bull; BPS Sultra</span>
            <span>Dicetak otomatis pada: {{ \Carbon\Carbon::now('Asia/Makassar')->translatedFormat('d F Y, H:i') }} WITA</span>
        </div>

    </div>

</body>
</html>
