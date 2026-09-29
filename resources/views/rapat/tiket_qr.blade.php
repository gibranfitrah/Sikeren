<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Tiket Presensi QR - {{ $task->text ?? 'Rapat' }}</title>
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/img/favicon-32x32.png') }}?v=2">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}?v=2" type="image/x-icon">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/img/apple-icon.png') }}?v=2">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: #f8fafc;
        }
    </style>
</head>
<body class="min-h-screen text-slate-800 flex flex-col justify-between selection:bg-blue-600 selection:text-white">

    {{-- Top App Bar --}}
    <header class="bg-white border-b border-slate-200 sticky top-0 z-30 shadow-2xs">
        <div class="max-w-lg mx-auto px-4 py-3 flex items-center justify-between">
            <a href="{{ url('/daftar_kegiatan') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-600 hover:text-slate-900 transition">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                <span>Kembali</span>
            </a>

            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span class="text-xs font-bold text-slate-700">Tiket Presensi Resmi</span>
            </div>
        </div>
    </header>

    {{-- Main Content --}}
    <main class="max-w-lg mx-auto w-full px-4 py-6 space-y-4 flex-1">

        {{-- Ticket Card --}}
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
            
            {{-- Ticket Header --}}
            <div class="bg-gradient-to-r from-blue-700 to-indigo-800 p-5 sm:p-6 text-white relative">
                <div class="flex items-start justify-between gap-3">
                    <div class="space-y-1">
                        <span class="inline-block px-2.5 py-0.5 rounded-md bg-white/20 text-[10px] font-bold uppercase tracking-wider text-blue-100">
                            {{ $task->jenis ?? 'Rapat Kedinasan' }}
                        </span>
                        <h1 class="text-lg sm:text-xl font-black text-white leading-snug">
                            {{ $task->text ?? 'Rapat Koordinasi BPS' }}
                        </h1>
                        @if(!empty($task->agenda))
                            <p class="text-xs text-blue-100/90 line-clamp-2">{{ $task->agenda }}</p>
                        @endif
                    </div>
                </div>

                {{-- Meeting details meta --}}
                <div class="mt-4 pt-4 border-t border-white/15 grid grid-cols-2 gap-3 text-xs">
                    <div>
                        <span class="text-[10px] text-blue-200 block uppercase font-semibold">Waktu Pelaksanaan</span>
                        <span class="font-bold text-white">
                            {{ $task->start_date ? \Carbon\Carbon::parse($task->start_date)->translatedFormat('d M Y') : date('d M Y') }}
                        </span>
                        <span class="text-[11px] text-blue-200 block">
                            {{ substr($task->start_jam ?? '08:30', 0, 5) }} WITA - {{ !empty($task->end_jam) ? substr($task->end_jam, 0, 5) . ' WITA' : 'Selesai' }}
                        </span>
                    </div>
                    <div>
                        <span class="text-[10px] text-blue-200 block uppercase font-semibold">Ruangan / Tempat</span>
                        <span class="font-bold text-white truncate block">
                            {{ $task->tempat ?? 'Aula / Kantor BPS' }}
                        </span>
                        <span class="text-[11px] text-blue-200 block truncate">
                            PJ: {{ $task->penanggung_jawab ?? ($task->pemimpin ?? '-') }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Participant Info & Verification Status --}}
            <div class="p-5 sm:p-6 space-y-6">
                
                @if(isset($currentUser) && $currentUser->id !== $user->id)
                    <div class="p-3 bg-blue-50 border border-blue-200 rounded-2xl flex items-center gap-2 text-xs text-blue-900">
                        <svg class="w-4 h-4 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Mode PJ/Admin: Menampilkan Tiket QR milik <strong>{{ $user->nama_lengkap }}</strong></span>
                    </div>
                @endif

                {{-- Status Banner --}}
                @if($isLewatWaktu && $statusKehadiran !== 'Hadir')
                    <div class="p-3.5 rounded-2xl flex items-center justify-between gap-3 bg-rose-50 border border-rose-200 text-rose-900">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl flex items-center justify-center font-bold shrink-0 bg-rose-500 text-white">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            </div>
                            <div>
                                <span class="text-[10px] font-bold uppercase tracking-wider block opacity-75">Status Presensi</span>
                                <span class="text-sm font-extrabold block">
                                    Presensi Ditutup (Lewat Batas Waktu)
                                </span>
                            </div>
                        </div>

                        <span class="px-2.5 py-1 rounded-lg bg-rose-100 text-rose-800 text-[10px] font-extrabold">Kedaluwarsa</span>
                    </div>
                    <div class="p-3 bg-rose-50/60 border border-rose-200 rounded-2xl text-[11px] text-rose-700 leading-relaxed text-center">
                        Jadwal rapat ini telah berakhir. Presensi fisik dan kode QR tidak dapat lagi digunakan untuk verifikasi kehadiran.
                    </div>
                @else
                    <div class="p-3.5 rounded-2xl flex items-center justify-between gap-3 {{ $statusKehadiran === 'Hadir' ? 'bg-emerald-50 border border-emerald-200 text-emerald-900' : 'bg-amber-50 border border-amber-200 text-amber-900' }}">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl flex items-center justify-center font-bold shrink-0 {{ $statusKehadiran === 'Hadir' ? 'bg-emerald-500 text-white' : 'bg-amber-500 text-white' }}">
                                @if($statusKehadiran === 'Hadir')
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                @else
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                @endif
                            </div>
                            <div>
                                <span class="text-[10px] font-bold uppercase tracking-wider block opacity-75">Status Presensi</span>
                                <span class="text-sm font-extrabold block">
                                    {{ $statusKehadiran === 'Hadir' ? 'Terverifikasi Hadir Fisik' : 'Menunggu Scan Presensi' }}
                                </span>
                                @if($statusKehadiran === 'Hadir' && $waktuHadir)
                                    <span class="text-[10px] text-emerald-700 font-mono block">
                                        Waktu: {{ \Carbon\Carbon::parse($waktuHadir)->format('H:i') }} WITA
                                    </span>
                                @endif
                            </div>
                        </div>

                        <a href="{{ url()->current() }}" class="px-2.5 py-1.5 rounded-xl bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 text-xs font-bold transition flex items-center gap-1 shadow-2xs">
                            <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            <span>Cek</span>
                        </a>
                    </div>
                @endif

                {{-- QR Container --}}
                <div class="flex flex-col items-center justify-center text-center space-y-3 pt-2">
                    <div class="p-4 bg-white rounded-3xl border-2 border-slate-200 shadow-md inline-block relative {{ ($isLewatWaktu && $statusKehadiran !== 'Hadir') ? 'opacity-40 grayscale' : '' }}">
                        <div class="w-64 h-64 flex items-center justify-center">
                            {!! $qrSvg !!}
                        </div>
                        @if($isLewatWaktu && $statusKehadiran !== 'Hadir')
                            <div class="absolute inset-0 bg-slate-900/60 rounded-3xl backdrop-blur-xs flex flex-col items-center justify-center p-4 text-white text-center">
                                <svg class="w-10 h-10 text-rose-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                <span class="font-black text-sm">QR DIKUNCI</span>
                                <span class="text-[11px] text-slate-200 mt-1">Batas waktu rapat telah lewat</span>
                            </div>
                        @endif
                    </div>

                    <div class="space-y-1 max-w-xs">
                        <h4 class="font-bold text-slate-900 text-sm">{{ $user->nama_lengkap }}</h4>
                        <p class="text-xs text-slate-500 font-mono">{{ $user->nipbaru ?? ($user->niplama ?? '-') }}</p>
                        @if(!$isLewatWaktu)
                            <p class="text-[11px] text-slate-500 leading-relaxed pt-1">
                                Tunjukkan kode QR ini kepada <strong>Petugas / Penanggung Jawab (PJ)</strong> di ruangan rapat untuk memverifikasi kehadiran fisik Anda.
                            </p>
                        @endif
                    </div>
                </div>

                {{-- Action / Ruangan Alternative --}}
                <div class="pt-4 border-t border-slate-100 space-y-2.5">
                    @if(!$isLewatWaktu)
                        <a href="{{ $urlPresensiRuangan }}" target="_blank"
                           class="w-full py-3 px-4 rounded-2xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs transition flex items-center justify-center gap-2 shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                            </svg>
                            <span>Atau Buka Lembar Presensi Digital Rapat</span>
                        </a>
                    @endif

                    <div class="grid grid-cols-2 gap-2">
                        <button type="button" onclick="window.print()" class="py-2.5 px-3 rounded-2xl bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 font-bold text-xs transition flex items-center justify-center gap-1.5 shadow-2xs">
                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                            <span>Cetak Tiket</span>
                        </button>

                        <a href="{{ url('/daftarkegiatan/' . $task->id) }}" 
                           class="py-2.5 px-3 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition flex items-center justify-center gap-1.5">
                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Detail Rapat</span>
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </main>

    {{-- Footer --}}
    <footer class="text-center py-4 text-[11px] text-slate-400">
        SIKEREN &bull; BPS Provinsi Sulawesi Tenggara
    </footer>

</body>
</html>
