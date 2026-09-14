<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Presensi QR - {{ $task->text ?? 'Rapat BPS' }}</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-slate-900 text-white min-h-screen flex flex-col justify-between p-6 sm:p-10">

    {{-- TOP BAR --}}
    <div class="flex items-center justify-between border-b border-slate-800 pb-6 max-w-5xl mx-auto w-full">
        <div class="flex items-center gap-3">
            <span class="text-2xl font-black tracking-tight bg-clip-text text-transparent bg-gradient-to-r from-blue-400 to-indigo-200">
                Sikeren
            </span>
            <span class="text-xs px-2.5 py-1 rounded-full bg-blue-500/20 text-blue-300 font-bold border border-blue-500/30">
                Presensi QR Rapat
            </span>
        </div>
        <a href="{{ url('/daftarkegiatan/' . $id) }}" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-xl text-xs font-semibold border border-slate-700 transition-colors">
            &larr; Kembali ke Detail Rapat
        </a>
    </div>

    {{-- MAIN QR DISPLAY --}}
    <div class="my-auto py-8 max-w-2xl mx-auto w-full text-center space-y-6">
        <div>
            <span class="text-xs font-bold uppercase tracking-wider text-blue-400">Pindai Untuk Mengisi Daftar Hadir</span>
            <h1 class="text-2xl sm:text-3xl font-black text-white mt-1 leading-tight">
                {{ $task->text ?? ($task->agenda ?? 'Rapat Koordinasi BPS') }}
            </h1>
            <p class="text-sm text-slate-400 mt-2">
                {{ \Carbon\Carbon::parse($task->start_date ?? date('Y-m-d'))->translatedFormat('l, d F Y') }} • {{ substr($task->start_jam ?? '09:00', 0, 5) }} - {{ substr($task->end_jam ?? '12:00', 0, 5) }} WITA
            </p>
        </div>

        {{-- QR BOX --}}
        <div class="inline-block p-6 bg-white rounded-3xl shadow-2xl border-4 border-blue-500/40">
            <div class="flex justify-center items-center">
                {!! $qrcode !!}
            </div>
        </div>

        <div class="space-y-3">
            <p class="text-xs sm:text-sm text-slate-300 font-medium">
                Gunakan kamera HP untuk memindai kode QR di atas atau buka tautan di bawah:
            </p>
            <div class="inline-flex items-center gap-2 px-4 py-2 bg-slate-800/80 rounded-xl border border-slate-700 text-xs font-mono text-blue-300">
                <span>{{ $urlHadir }}</span>
            </div>
            <div>
                <a href="{{ $urlHadir }}" target="_blank" class="inline-block mt-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-lg transition-colors">
                    Buka Halaman Presensi Peserta &rarr;
                </a>
            </div>
        </div>
    </div>

    {{-- FOOTER --}}
    <div class="border-t border-slate-800 pt-6 text-center text-xs text-slate-500 max-w-5xl mx-auto w-full">
        BPS Provinsi Sulawesi Tenggara &bull; Sistem Kegiatan Terencana (Sikeren)
    </div>

</body>
</html>
