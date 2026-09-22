<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Hasil Verifikasi Presensi - Sikeren</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: #f8fafc;
        }
    </style>
</head>
<body class="min-h-screen text-slate-800 flex items-center justify-center p-4 selection:bg-blue-600 selection:text-white">

    <div class="max-w-md w-full bg-white rounded-3xl border border-slate-200 shadow-xl overflow-hidden text-center p-6 sm:p-8 space-y-6">
        
        {{-- Icon Status --}}
        <div class="mx-auto w-20 h-20 rounded-full flex items-center justify-center {{ $success ? 'bg-emerald-100 text-emerald-600' : 'bg-rose-100 text-rose-600' }}">
            @if($success)
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                </svg>
            @else
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            @endif
        </div>

        <div class="space-y-1.5">
            <span class="inline-block px-3 py-1 rounded-full text-[11px] font-extrabold uppercase {{ $success ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                {{ $success ? 'Presensi Berhasil Diverifikasi' : 'Verifikasi Gagal' }}
            </span>
            <h1 class="text-xl font-black text-slate-900">
                {{ $success ? ($peserta->nama_lengkap ?? 'Peserta Rapat') : 'Data Tidak Sesuai' }}
            </h1>
            @if($peserta)
                <p class="text-xs text-slate-500 font-mono">{{ $peserta->nipbaru ?? ($peserta->niplama ?? '-') }}</p>
            @endif
        </div>

        <div class="bg-slate-50 rounded-2xl p-4 border border-slate-100 text-left text-xs space-y-2">
            <div>
                <span class="text-slate-400 block text-[10px] uppercase font-semibold">Nama Rapat / Kegiatan</span>
                <p class="font-bold text-slate-800">{{ $task->text ?? 'Rapat BPS' }}</p>
            </div>
            <div class="grid grid-cols-2 gap-2 pt-1 border-t border-slate-200/60">
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-semibold">Waktu Presensi</span>
                    <p class="font-semibold text-slate-700">{{ now()->translatedFormat('d M Y, H:i') }} WITA</p>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-semibold">Status Tercatat</span>
                    <p class="font-bold text-emerald-600">Hadir Fisik</p>
                </div>
            </div>
        </div>

        <div class="space-y-2 pt-2">
            <a href="{{ url('/daftarhadir/' . $task->id) }}" 
               class="w-full py-3 px-4 rounded-2xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs transition flex items-center justify-center gap-2 shadow-sm">
                <span>Lihat Rekapitulasi Daftar Hadir</span>
            </a>
            <a href="{{ url('/detail_kegiatan/' . $task->id) }}" 
               class="w-full py-2.5 px-4 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition flex items-center justify-center gap-1.5">
                <span>Kembali ke Detail Kegiatan</span>
            </a>
        </div>
    </div>

</body>
</html>
