<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Presensi Peserta Rapat - Sikeren Mobile</title>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --gray-bg: #f8fafc;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            -webkit-tap-highlight-color: transparent;
            background-color: #f1f5f9;
            color: #1e293b;
            margin: 0;
            padding: 0;
        }
        .status-option-card {
            border: 2px solid #e2e8f0;
            background-color: #ffffff;
            transition: all 0.2s ease-in-out;
            cursor: pointer;
        }
        .status-option-card:active {
            transform: scale(0.98);
        }
        .status-option-card.selected-hadir {
            border-color: #10b981 !important;
            background-color: #ecfdf5 !important;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.15);
        }
        .status-option-card.selected-lain {
            border-color: #f59e0b !important;
            background-color: #fffbeb !important;
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.15);
        }
        .status-option-card.selected-tidak {
            border-color: #ef4444 !important;
            background-color: #fef2f2 !important;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.15);
        }
        .status-option-card.selected-belum {
            border-color: #64748b !important;
            background-color: #f8fafc !important;
            box-shadow: 0 4px 12px rgba(100, 116, 139, 0.15);
        }
    </style>
</head>
<body class="min-h-screen flex flex-col justify-between selection:bg-blue-500 selection:text-white pb-10">

    @php
        $rapatText       = $task->text ?? ($task->title ?? 'Rapat Koordinasi BPS');
        $rapatAgenda     = $task->agenda ?? '-';
        $rapatTempat     = $task->tempat ?? 'Aula / Kantor BPS';
        $rapatStart      = $task->start_date ?? ($task->start ?? date('Y-m-d'));
        $rapatJamMulai   = $task->start_jam ?? '09:00';
        $rapatJamSelesai = $task->end_jam ?? null;
        $rapatPemimpin   = $task->pemimpin ?? '-';
        $rapatId         = $task->id ?? $id;

        $countHadir = $pesertaList->where('status_kehadiran', 'Hadir')->count();
        $countTidak = $pesertaList->where('status_kehadiran', 'Tidak Hadir')->count();
        $countLain  = $pesertaList->where('status_kehadiran', 'Sedang Ada Kegiatan Lain')->count();
        $countBelum = $pesertaList->where('status_kehadiran', 'Belum Hadir')->count();

        $initialNip = Auth::check() ? (Auth::user()->niplama ?? '') : '';
        $initialNama = Auth::check() ? (Auth::user()->nama_lengkap ?? '') : '';

        if (empty($initialNip) && isset($assignedParticipants) && $assignedParticipants->count() === 1) {
            $initialNip  = $assignedParticipants->first()->niplama;
            $initialNama = $assignedParticipants->first()->nama_lengkap;
        }
        $isSelesai       = (!empty($task->notulen) || ($task->notulen_selesai ?? 0) == 1);
        $rapatEnd        = $task->date_akhir ?? ($task->start_date ?? ($task->start ?? date('Y-m-d')));
        $jamAkhirClean   = trim($rapatJamSelesai ?: '23:59:59');
        if (strlen($jamAkhirClean) === 5) {
            $jamAkhirClean .= ':00';
        }
        $isLewatWaktu    = false;
        try {
            $waktuAkhirRapat = \Carbon\Carbon::parse($rapatEnd . ' ' . $jamAkhirClean);
            $isLewatWaktu    = \Carbon\Carbon::now()->greaterThan($waktuAkhirRapat) && !$isSelesai;
        } catch (\Exception $e) {
            $isLewatWaktu    = false;
        }
    @endphp

    {{-- HEADER APP BAR --}}
    <header class="bg-gradient-to-r from-blue-900 via-blue-800 to-indigo-900 text-white sticky top-0 z-30 shadow-md">
        <div class="max-w-md mx-auto px-4 py-3 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-blue-500/30 border border-blue-400/40 flex items-center justify-center font-black text-sm text-blue-200">
                    S
                </div>
                <div>
                    <h1 class="font-extrabold text-sm tracking-tight text-white leading-tight">Sikeren Mobile</h1>
                    <p class="text-[10px] text-blue-200 font-medium">Presensi Kehadiran Rapat</p>
                </div>
            </div>

            @if($isLewatWaktu)
                <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-rose-500/20 border border-rose-400/30 text-rose-300 text-[11px] font-bold">
                    <span class="w-2 h-2 rounded-full bg-rose-400"></span>
                    <span>Ditutup</span>
                </div>
            @else
                <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-500/20 border border-emerald-400/30 text-emerald-300 text-[11px] font-bold">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>QR Live</span>
                </div>
            @endif
        </div>
    </header>

    {{-- MAIN CONTAINER --}}
    <main class="max-w-md mx-auto w-full px-4 pt-4 space-y-4 flex-1">

        {{-- ALERT NOTIFIKASI TOAST --}}
        <div id="toast-success" class="{{ session('success_presensi') ? '' : 'hidden' }} p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 shadow-sm flex items-start gap-3">
            <div class="w-8 h-8 rounded-xl bg-emerald-500 text-white flex items-center justify-center flex-shrink-0 mt-0.5">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
            </div>
            <div class="flex-1 text-xs">
                <h4 class="font-bold text-emerald-900 text-sm">Presensi Berhasil Dicatat!</h4>
                <p id="toast-message" class="mt-0.5 text-emerald-700 leading-relaxed">{{ session('success_presensi') }}</p>
            </div>
            <button type="button" onclick="document.getElementById('toast-success').classList.add('hidden')" class="text-emerald-500 hover:text-emerald-700 text-lg font-bold leading-none">&times;</button>
        </div>

        <div id="toast-error" class="{{ session('error_presensi') ? '' : 'hidden' }} p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-900 shadow-sm flex items-start gap-3">
            <div class="w-8 h-8 rounded-xl bg-rose-500 text-white flex items-center justify-center flex-shrink-0 mt-0.5">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
            </div>
            <div class="flex-1 text-xs">
                <h4 class="font-bold text-rose-900 text-sm">Pemberitahuan</h4>
                <p id="toast-error-message" class="mt-0.5 text-rose-700 leading-relaxed">{{ session('error_presensi') }}</p>
            </div>
            <button type="button" onclick="document.getElementById('toast-error').classList.add('hidden')" class="text-rose-500 hover:text-rose-700 text-lg font-bold leading-none">&times;</button>
        </div>

        {{-- BANNER PENEGASAN PRESENSI FISIK RUANG RAPAT --}}
        <div class="p-3.5 rounded-2xl bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 text-blue-950 flex items-start gap-3 shadow-2xs">
            <div class="w-8 h-8 rounded-xl bg-blue-600 text-white flex items-center justify-center shrink-0 mt-0.5 shadow-xs">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
            <div class="text-xs space-y-0.5">
                <h4 class="font-black text-blue-900 text-xs flex items-center gap-1.5">
                    <span>Presensi Fisik di Ruangan Rapat</span>
                    <span class="px-1.5 py-0.2 rounded bg-blue-200/80 text-blue-800 text-[9px] font-extrabold uppercase">Wajib Hadir Fisik</span>
                </h4>
                <p class="text-[11px] text-blue-800 leading-relaxed">
                    Kode QR dan presensi ini hanya tersedia di ruangan rapat untuk memastikan peserta hadir secara fisik di lokasi rapat BPS Provinsi Sulawesi Tenggara.
                </p>
            </div>
        </div>

        {{-- TAMPILAN KHUSUS SETELAH TAMU EKSTERNAL MENGISI PRESENSI (LANGSUNG HADIR) --}}
        @if(session('eksternal_success'))
        <div class="bg-white rounded-3xl p-6 border-2 border-emerald-300 shadow-md text-center space-y-4">
            <div class="w-16 h-16 rounded-full bg-emerald-100 text-emerald-600 border-4 border-emerald-200 flex items-center justify-center mx-auto">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
            </div>
            <div class="space-y-1">
                <span class="inline-block px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 font-extrabold text-[11px] uppercase tracking-wider">
                    Status: Hadir Terverifikasi
                </span>
                <h3 class="text-base sm:text-lg font-black text-slate-900 pt-1">
                    Terima Kasih, Kehadiran Anda Telah Dicatat!
                </h3>
                <p class="text-xs text-slate-600 max-w-xs mx-auto leading-relaxed">
                    Kehadiran Anda sebagai tamu eksternal pada rapat kedinasan ini telah resmi tersimpan.
                </p>
            </div>

            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 text-left text-xs space-y-2">
                <div class="flex justify-between py-1 border-b border-slate-100">
                    <span class="text-slate-500">Nama Tamu:</span>
                    <span class="font-bold text-slate-900">{{ session('eksternal_nama') }}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-100">
                    <span class="text-slate-500">Asal Instansi:</span>
                    <span class="font-bold text-slate-900">{{ session('eksternal_instansi') }}</span>
                </div>
                <div class="flex justify-between py-1">
                    <span class="text-slate-500">Waktu Presensi:</span>
                    <span class="font-bold text-slate-900 font-mono">{{ now()->format('H:i') }} WITA</span>
                </div>
            </div>

            <p class="text-[11px] text-slate-400 italic">
                Selamat mengikuti kegiatan di BPS Provinsi Sulawesi Tenggara. Anda tidak perlu login ke Sikeren Mobile.
            </p>
        </div>
        @endif

        {{-- INFORMASI RAPAT --}}
        <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-xs space-y-3">
            <div>
                <div class="flex items-center justify-between mb-1">
                    <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-blue-100 text-blue-800 border border-blue-200">
                        Kegiatan Rapat
                    </span>
                    <span class="text-[10px] font-mono text-slate-400 font-bold">#{{ $rapatId }}</span>
                </div>
                <h2 class="text-sm sm:text-base font-extrabold text-slate-900 leading-snug">
                    {{ $rapatText }}
                </h2>
                @if(!empty($rapatAgenda) && $rapatAgenda !== '-')
                    <p class="text-xs text-slate-500 mt-1 line-clamp-2 leading-relaxed">{{ $rapatAgenda }}</p>
                @endif
            </div>

            <div class="grid grid-cols-2 gap-2 pt-1 text-[11px]">
                <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                    <span class="text-slate-400 font-semibold block text-[10px] uppercase">Waktu Pelaksanaan</span>
                    <span class="font-bold text-slate-800 block mt-0.5">
                        {{ \Carbon\Carbon::parse($rapatStart)->translatedFormat('d M Y') }}
                    </span>
                    <span class="text-slate-500 font-mono text-[10px]">
                        {{ substr($rapatJamMulai, 0, 5) }} WITA - {{ !empty($rapatJamSelesai) ? substr($rapatJamSelesai, 0, 5) . ' WITA' : 'Selesai' }}
                    </span>
                </div>

                <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                    <span class="text-slate-400 font-semibold block text-[10px] uppercase">Lokasi / Ruangan</span>
                    <span class="font-bold text-slate-800 block mt-0.5 truncate" title="{{ $rapatTempat }}">
                        {{ $rapatTempat }}
                    </span>
                    <span class="text-slate-500 text-[10px] truncate block">
                        Pemimpin: {{ $rapatPemimpin }}
                    </span>
                </div>
            </div>
        </div>

        {{-- FORM PRESENSI UTAMA --}}
        @if($isLewatWaktu)
        <div class="bg-white rounded-2xl p-6 border border-rose-200 shadow-xs text-center space-y-3">
            <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 border border-rose-100 flex items-center justify-center mx-auto">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            </div>
            <div>
                <h3 class="font-extrabold text-rose-900 text-sm">Presensi Rapat Telah Ditutup</h3>
                <p class="text-xs text-rose-700 mt-1 leading-relaxed max-w-xs mx-auto">
                    Jadwal pelaksanaan rapat ini telah berakhir pada {{ \Carbon\Carbon::parse($rapatEnd)->translatedFormat('d M Y') }}{{ !empty($rapatJamSelesai) ? ' pukul ' . substr($rapatJamSelesai, 0, 5) . ' WITA' : '' }}. Pengisian konfirmasi kehadiran tidak dapat dilakukan lagi.
                </p>
            </div>
        </div>
        @else
        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs space-y-4">

            <div class="border-b border-slate-100 pb-3 flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-slate-900 text-sm">Form Konfirmasi Kehadiran</h3>
                    <p class="text-[11px] text-slate-500 mt-0.5">Pilih jenis peserta dan konfirmasi kehadiran fisik</p>
                </div>
            </div>

            {{-- TAB PILIHAN: PEGAWAI BPS VS TAMU / INSTANSI LAIN --}}
            <div class="flex items-center p-1 bg-slate-100 rounded-2xl">
                <button type="button" id="tab-btn-bps" onclick="switchTipePeserta('bps')" 
                        class="flex-1 py-2 text-xs font-bold rounded-xl transition-all flex items-center justify-center gap-1.5 bg-white text-blue-700 shadow-xs">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    <span>Pegawai BPS</span>
                </button>
                <button type="button" id="tab-btn-eksternal" onclick="switchTipePeserta('eksternal')" 
                        class="flex-1 py-2 text-xs font-bold rounded-xl transition-all flex items-center justify-center gap-1.5 text-slate-600 hover:text-slate-900">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    <span>Tamu / Instansi Lain</span>
                </button>
            </div>

            {{-- PANEL TAMU EKSTERNAL (LANGSUNG HADIR TANPA LOGIN) --}}
            <div id="panel-eksternal" class="hidden space-y-4 pt-1">
                <div class="p-3.5 rounded-xl bg-indigo-50/80 border border-indigo-100 text-xs text-indigo-950 space-y-1">
                    <span class="font-extrabold text-[11px] uppercase tracking-wider text-indigo-800 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Presensi Peserta / Tamu Eksternal</span>
                    </span>
                    <p class="text-[11px] text-indigo-800 leading-relaxed">
                        Tamu dari instansi atau lembaga luar BPS cukup mengisi Nama dan Asal Instansi di bawah ini. Anda akan <strong>langsung tercatat Hadir</strong> tanpa perlu akun Sikeren Mobile.
                    </p>
                </div>

                <form action="{{ route('daftarhadir.submit') }}" method="POST" class="space-y-3.5">
                    @csrf
                    <input type="hidden" name="id_kegiatan" value="{{ $rapatId }}">
                    <input type="hidden" name="tipe_peserta" value="eksternal">
                    <input type="hidden" name="status_kehadiran" value="Hadir">

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">
                            Nama Lengkap Tamu <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" 
                               name="nama_eksternal" 
                               required
                               placeholder="Contoh: Dr. Ir. Rahmat Hidayat, M.Si" 
                               class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl p-3 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">
                            Asal Instansi / Kantor / Lembaga <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" 
                               name="instansi" 
                               required
                               placeholder="Contoh: Bappeda Prov. Sultra / Dinas Kominfo" 
                               class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl p-3 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">
                            Keterangan / Jabatan (Opsional)
                        </label>
                        <input type="text" 
                               name="keterangan_eksternal" 
                               placeholder="Contoh: Kepala Bidang Perencanaan" 
                               class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl p-2.5 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                    </div>

                    <button type="submit" 
                            class="w-full py-3.5 px-4 bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 text-white text-xs sm:text-sm font-bold rounded-xl shadow-md transition flex items-center justify-center gap-2 active:scale-98">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        <span>Konfirmasi Kehadiran Tamu (Langsung Hadir)</span>
                    </button>
                </form>
            </div>

            {{-- PANEL PEGAWAI BPS --}}
            <div id="panel-bps" class="space-y-4">

            @if(!Auth::check())
            {{-- Jika belum login, wajib masuk ke akun BPS --}}
            <div class="p-4 rounded-2xl bg-blue-50/70 border border-blue-200 text-center space-y-3">
                <div class="w-12 h-12 rounded-2xl bg-blue-600 text-white flex items-center justify-center mx-auto shadow-xs">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                </div>
                <div>
                    <h4 class="font-extrabold text-slate-900 text-sm">Masuk ke Akun BPS Anda</h4>
                    <p class="text-xs text-slate-600 mt-1 leading-relaxed max-w-xs mx-auto">
                        Untuk memverifikasi presensi mandiri pegawai BPS, silakan masuk ke akun Sikeren Mobile Anda terlebih dahulu.
                    </p>
                </div>
                <a href="{{ route('login') }}" 
                   class="inline-flex items-center justify-center gap-2 w-full py-3 px-4 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow-sm transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                    <span>Masuk Akun BPS & Isi Kehadiran</span>
                </a>
            </div>
            @else
            {{-- Jika sudah login, tampilkan form kehadiran pegawai BPS --}}

            @if(!Auth::user()->isAdmin() && isset($isAssigned) && !$isAssigned && isset($assignedParticipants) && $assignedParticipants->isNotEmpty())
            <div class="p-3.5 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-xs space-y-1.5 shadow-xs">
                <div class="flex items-center gap-1.5 font-bold text-amber-800">
                    <svg class="w-4 h-4 text-amber-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                    <span>Perhatian: Akun Tidak Terdaftar di Rapat Ini</span>
                </div>
                <p class="text-[11px] text-amber-700 leading-relaxed">
                    Akun Anda saat ini (<strong>{{ Auth::user()->nama_lengkap ?? Auth::user()->username }}</strong>) tidak tercatat sebagai peserta undangan rapat ini. Peserta yang diundang: 
                    <strong>{{ $assignedParticipants->pluck('nama_lengkap')->join(', ') }}</strong>.
                </p>
                <div class="pt-1">
                    <a href="{{ route('actionlogout', ['redirect' => url('daftarhadir/' . $rapatId)]) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-lg text-xs shadow-xs transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        <span>Ganti Akun Peserta</span>
                    </a>
                </div>
            </div>
            @endif

            <form id="form-presensi" action="{{ route('daftarhadir.submit') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="id_kegiatan" value="{{ $rapatId }}">
                <input type="hidden" name="tipe_peserta" value="bps">
                <input type="hidden" id="input-niplama" name="niplama" value="{{ $initialNip }}">
                <input type="hidden" id="input-nama" name="peserta_manual" value="{{ $initialNama }}">
                <input type="hidden" id="input-status" name="status_kehadiran" value="Hadir">

                {{-- 1. IDENTITAS PESERTA --}}
                
                {{-- Quick Assigned Participant Chips (Hanya untuk Admin saat mendampingi) --}}
                @if(Auth::check() && Auth::user()->isAdmin() && isset($assignedParticipants) && $assignedParticipants->count() > 0)
                <div id="section-assigned" class="space-y-1.5">
                    <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider">
                        Peserta Rapat Ditugaskan (Admin Assistant):
                    </label>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach($assignedParticipants as $ap)
                            <button type="button" 
                                    onclick="pilihPegawaiDirect('{{ $ap->niplama }}', '{{ addslashes($ap->nama_lengkap) }}')"
                                    class="px-3 py-2 rounded-xl text-xs font-bold bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 transition-all text-left flex items-center gap-1.5 shadow-xs active:scale-95">
                                <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                                <span>{{ $ap->nama_lengkap }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- KARTU PEGAWAI TERPILIH (TERIKAT DENGAN AKUN LOGIN) --}}
                <div id="card-selected-user" class="{{ !empty($initialNama) ? '' : 'hidden' }} p-3.5 rounded-xl bg-blue-50/80 border border-blue-200 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-blue-700 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 text-blue-600 inline" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path></svg>
                            <span>Akun Peserta (Terverifikasi)</span>
                        </span>
                        <div class="flex items-center gap-2">
                            @if(Auth::check() && Auth::user()->isAdmin())
                            <button type="button" onclick="resetPilihanPegawai()" class="text-[11px] font-bold text-blue-600 hover:text-blue-800 underline">
                                Ganti Nama (Admin)
                            </button>
                            @endif
                            <a href="{{ route('actionlogout', ['redirect' => url('daftarhadir/' . $rapatId)]) }}" class="text-[11px] font-semibold text-rose-600 hover:text-rose-800 underline flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                <span>Ganti Akun</span>
                            </a>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <div id="user-avatar" class="w-10 h-10 rounded-xl bg-blue-600 text-white font-bold text-sm flex items-center justify-center flex-shrink-0 shadow-xs">
                            {{ !empty($initialNama) ? strtoupper(substr($initialNama, 0, 1)) : 'P' }}
                        </div>
                        <div class="overflow-hidden">
                            <h4 id="user-nama-display" class="text-xs font-bold text-slate-900 truncate">{{ $initialNama }}</h4>
                            <p id="user-nip-display" class="text-[11px] text-slate-500 font-mono">NIP: {{ $initialNip ?: '-' }}</p>
                        </div>
                    </div>
                </div>

                {{-- SEARCH PEGAWAI BPS (JIKA BELUM TERPILIH) --}}
                <div id="section-search" class="{{ empty($initialNama) ? '' : 'hidden' }} space-y-2">
                    <label class="block text-xs font-bold text-slate-700">
                        Cari Nama / NIP Pegawai
                    </label>

                    <div class="relative">
                        <input type="text" 
                               id="search-input" 
                               onkeyup="filterPegawaiList()" 
                               placeholder="Ketik nama atau NIP pegawai..." 
                               class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl py-2.5 pl-9 pr-4 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>

                    {{-- LIST PEGAWAI DROPDOWN --}}
                    <div id="pegawai-dropdown" class="border border-slate-200 rounded-xl max-h-48 overflow-y-auto divide-y divide-slate-100 bg-white shadow-inner">
                        @foreach($allPegawai as $idx => $pegawai)
                            <div class="pegawai-row" data-search="{{ strtolower($pegawai->nama_lengkap . ' ' . $pegawai->niplama . ' ' . $pegawai->nipbaru) }}">
                                <button type="button" 
                                        onclick="pilihPegawaiDirect('{{ $pegawai->niplama }}', '{{ addslashes($pegawai->nama_lengkap) }}')" 
                                        class="w-full text-left px-3.5 py-2.5 hover:bg-blue-50/70 transition-colors flex items-center justify-between group">
                                    <div class="overflow-hidden pr-2">
                                        <p class="text-xs font-semibold text-slate-800 group-hover:text-blue-700 truncate">{{ $pegawai->nama_lengkap }}</p>
                                        <p class="text-[10px] text-slate-400 font-mono">{{ $pegawai->nipbaru ?: $pegawai->niplama }}</p>
                                    </div>
                                    <span class="text-[11px] text-blue-600 font-bold opacity-0 group-hover:opacity-100 transition-opacity">
                                        Pilih &rarr;
                                    </span>
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- INPUT MANUAL TAMU EKSTERNAL --}}
                <div id="section-manual" class="hidden space-y-2">
                    <label class="block text-xs font-bold text-slate-700">
                        Nama Lengkap (Tamu / Peserta Luar)
                    </label>
                    <input type="text" 
                           id="input-manual-name"
                           placeholder="Masukkan nama lengkap Anda..." 
                           class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl p-2.5 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div class="flex items-center justify-between pt-0.5">
                    <button type="button" id="btn-toggle-manual" onclick="toggleManualInput()" class="text-[11px] font-semibold text-slate-500 hover:text-blue-600 transition-colors">
                        + Bukan Pegawai BPS? Isi Manual
                    </button>
                </div>

                {{-- 2. PILIHAN STATUS KEHADIRAN (4 OPSI UTAMA) --}}
                <div class="pt-3 border-t border-slate-100 space-y-2.5">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-bold text-slate-800 uppercase tracking-wider">
                            Pilih Status Kehadiran:
                        </label>
                        <span id="label-status-terpilih" class="text-[11px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">
                            Status: Hadir
                        </span>
                    </div>

                    <div class="grid grid-cols-1 gap-2.5">
                        
                        {{-- OPSI 1: HADIR --}}
                        <div id="card-status-Hadir" onclick="selectStatus('Hadir')" class="status-option-card selected-hadir p-3.5 rounded-2xl flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-emerald-500 text-white flex items-center justify-center shrink-0 shadow-xs">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold text-emerald-950">Hadir</span>
                                    <span id="badge-Hadir" class="text-[10px] font-bold text-emerald-700 uppercase bg-emerald-100 px-2 py-0.5 rounded-full">Dipilih</span>
                                </div>
                                <p class="text-[11px] text-emerald-700 mt-0.5">Mengikuti rapat di lokasi fisik atau via Zoom/Vicon</p>
                            </div>
                        </div>

                        {{-- OPSI 2: SEDANG ADA KEGIATAN LAIN --}}
                        <div id="card-status-Sedang-Ada-Kegiatan-Lain" onclick="selectStatus('Sedang Ada Kegiatan Lain')" class="status-option-card p-3.5 rounded-2xl flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-amber-500 text-white flex items-center justify-center shrink-0 shadow-xs">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 00.75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 00-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0112 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 01-.673-.38m0 0A2.18 2.18 0 013 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 013.413-.387m7.5 0V5.25A2.25 2.25 0 0013.5 3h-3a2.25 2.25 0 00-2.25 2.25v.894m7.5 0a48.111 48.111 0 00-7.5 0"/></svg>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold text-amber-950">Sedang Ada Kegiatan Lain</span>
                                    <span id="badge-Sedang-Ada-Kegiatan-Lain" class="hidden text-[10px] font-bold text-amber-700 uppercase bg-amber-100 px-2 py-0.5 rounded-full">Dipilih</span>
                                </div>
                                <p class="text-[11px] text-amber-700 mt-0.5">Sedang dinas luar, survei lapangan, atau tugas lain</p>
                            </div>
                        </div>

                        {{-- OPSI 3: TIDAK HADIR --}}
                        <div id="card-status-Tidak-Hadir" onclick="selectStatus('Tidak Hadir')" class="status-option-card p-3.5 rounded-2xl flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-rose-500 text-white flex items-center justify-center shrink-0 shadow-xs">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold text-rose-950">Tidak Hadir</span>
                                    <span id="badge-Tidak-Hadir" class="hidden text-[10px] font-bold text-rose-700 uppercase bg-rose-100 px-2 py-0.5 rounded-full">Dipilih</span>
                                </div>
                                <p class="text-[11px] text-rose-700 mt-0.5">Berhalangan hadir (Izin, sakit, cuti, dll)</p>
                            </div>
                        </div>

                        {{-- OPSI 4: BELUM HADIR (RESET) --}}
                        <div id="card-status-Belum-Hadir" onclick="selectStatus('Belum Hadir')" class="status-option-card p-3 rounded-2xl flex items-center gap-3 opacity-80 hover:opacity-100">
                            <div class="w-8 h-8 rounded-xl bg-slate-400 text-white flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold text-slate-800">Belum Hadir</span>
                                    <span id="badge-Belum-Hadir" class="hidden text-[10px] font-bold text-slate-600 uppercase bg-slate-200 px-2 py-0.5 rounded-full">Dipilih</span>
                                </div>
                                <p class="text-[10px] text-slate-500">Reset status ke belum melakukan presensi</p>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- 3. CATATAN / KETERANGAN --}}
                <div id="section-keterangan" class="hidden space-y-1.5 pt-1">
                    <label class="block text-xs font-bold text-slate-700">
                        Catatan / Keterangan Tugas:
                    </label>
                    <input type="text" 
                           id="input-keterangan"
                           name="keterangan" 
                           placeholder="Contoh: Pengawasan Lapangan Sakernas di Kab. Bone" 
                           class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl p-2.5 focus:bg-white focus:ring-2 focus:ring-blue-500">
                </div>

                {{-- SUBMIT BUTTON --}}
                <div class="pt-2">
                    <button type="submit" 
                            id="btn-submit-presensi"
                            class="w-full py-3.5 px-4 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 active:scale-98 text-white text-xs sm:text-sm font-bold rounded-xl shadow-md transition-all flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        <span id="btn-submit-text">Kirim Konfirmasi Presensi</span>
                    </button>
                </div>
            </form>
            @endif
            </div> {{-- end panel-bps --}}
        </div>
        @endif

        {{-- DAFTAR PESERTA YANG SUDAH MENGISI PRESENSI --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
            <button onclick="toggleDaftarPeserta()" 
                    type="button" 
                    class="w-full p-4 flex items-center justify-between text-left hover:bg-slate-50/60 transition-colors">
                <div class="flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>
                    </span>
                    <div>
                        <h4 class="text-xs font-bold text-slate-900">Daftar Kehadiran Peserta ({{ $pesertaList->count() }})</h4>
                        <p class="text-[10px] text-slate-500">
                            Hadir: <strong class="text-emerald-600">{{ $countHadir }}</strong> • 
                            Lain: <strong class="text-amber-600">{{ $countLain }}</strong> • 
                            Tidak: <strong class="text-rose-600">{{ $countTidak }}</strong> •
                            Belum: <strong class="text-slate-500">{{ $countBelum }}</strong>
                        </p>
                    </div>
                </div>
                <svg id="arrow-daftar-peserta" class="w-4 h-4 text-slate-400 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>

            <div id="content-daftar-peserta" class="hidden border-t border-slate-100 max-h-60 overflow-y-auto divide-y divide-slate-100 bg-slate-50/30">
                @forelse($pesertaList as $idx => $p)
                    @php
                        $st = $p->status_kehadiran ?? 'Belum Hadir';
                    @endphp
                    <div class="px-4 py-2.5 flex items-center justify-between text-xs gap-2">
                        <div class="flex items-center gap-2.5 overflow-hidden pr-2">
                            <span class="text-[10px] font-bold text-slate-400 w-4">{{ $idx + 1 }}</span>
                            <div class="overflow-hidden">
                                <p class="font-semibold text-slate-800 text-xs truncate">{{ $p->nama_lengkap }}</p>
                                <p class="text-[10px] text-slate-400 font-mono">{{ $p->nipbaru ?: $p->niplama }}</p>
                                @if(!empty($p->keterangan))
                                    <p class="text-[10px] text-amber-700 italic truncate">Ket: {{ $p->keterangan }}</p>
                                @endif
                            </div>
                        </div>
                        <div class="text-right shrink-0">
                            @if($st === 'Hadir')
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 border border-emerald-200 inline-flex items-center gap-1 w-fit ml-auto">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    Hadir
                                </span>
                            @elseif($st === 'Sedang Ada Kegiatan Lain')
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-amber-50 text-amber-700 border border-amber-200 inline-flex items-center gap-1 w-fit ml-auto">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 00.75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 00-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0112 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 01-.673-.38m0 0A2.18 2.18 0 013 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 013.413-.387m7.5 0V5.25A2.25 2.25 0 0013.5 3h-3a2.25 2.25 0 00-2.25 2.25v.894m7.5 0a48.111 48.111 0 00-7.5 0"/></svg>
                                    Kegiatan Lain
                                </span>
                            @elseif($st === 'Tidak Hadir')
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-rose-50 text-rose-700 border border-rose-200 inline-flex items-center gap-1 w-fit ml-auto">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                    Tidak Hadir
                                </span>
                            @else
                                <span class="text-[10px] font-medium px-2 py-0.5 rounded-md bg-gray-100 text-gray-600 block w-fit ml-auto">
                                    Belum Hadir
                                </span>
                            @endif
                            @if(!empty($p->waktu_kehadiran) && $st !== 'Belum Hadir')
                                <span class="text-[9px] text-slate-400 block mt-0.5 font-mono">
                                    {{ \Carbon\Carbon::parse($p->waktu_kehadiran)->format('H:i') }} WITA
                                </span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="p-4 text-center text-xs text-slate-400">
                        Belum ada data peserta.
                    </div>
                @endforelse
            </div>
        </div>

    </main>

    {{-- FOOTER --}}
    <footer class="mt-8 text-center text-xs text-slate-400">
        BPS Provinsi Sulawesi Tenggara &bull; Sikeren Mobile
    </footer>

    {{-- VANILLA JAVASCRIPT LOGIC (0 KB DEPENDENCY, ULTRA FAST) --}}
    <script>
        let currentStatus = 'Hadir';
        let isManualMode = false;

        function switchTipePeserta(tipe) {
            const btnBps = document.getElementById('tab-btn-bps');
            const btnEks = document.getElementById('tab-btn-eksternal');
            const panelBps = document.getElementById('panel-bps');
            const panelEks = document.getElementById('panel-eksternal');

            if (tipe === 'eksternal') {
                btnEks.className = 'flex-1 py-2 text-xs font-bold rounded-xl transition-all flex items-center justify-center gap-1.5 bg-white text-indigo-700 shadow-xs';
                btnBps.className = 'flex-1 py-2 text-xs font-bold rounded-xl transition-all flex items-center justify-center gap-1.5 text-slate-600 hover:text-slate-900';
                panelEks.classList.remove('hidden');
                panelBps.classList.add('hidden');
            } else {
                btnBps.className = 'flex-1 py-2 text-xs font-bold rounded-xl transition-all flex items-center justify-center gap-1.5 bg-white text-blue-700 shadow-xs';
                btnEks.className = 'flex-1 py-2 text-xs font-bold rounded-xl transition-all flex items-center justify-center gap-1.5 text-slate-600 hover:text-slate-900';
                panelBps.classList.remove('hidden');
                panelEks.classList.add('hidden');
            }
        }

        function pilihPegawaiDirect(nip, nama) {
            document.getElementById('input-niplama').value = nip;
            document.getElementById('input-nama').value = nama;
            
            document.getElementById('user-avatar').innerText = nama ? nama.charAt(0).toUpperCase() : 'P';
            document.getElementById('user-nama-display').innerText = nama;
            document.getElementById('user-nip-display').innerText = 'NIP: ' + (nip || '-');

            document.getElementById('card-selected-user').classList.remove('hidden');
            document.getElementById('section-search').classList.add('hidden');
            document.getElementById('section-manual').classList.add('hidden');
        }

        function resetPilihanPegawai() {
            document.getElementById('input-niplama').value = '';
            document.getElementById('input-nama').value = '';
            document.getElementById('card-selected-user').classList.add('hidden');
            if (isManualMode) {
                document.getElementById('section-manual').classList.remove('hidden');
            } else {
                document.getElementById('section-search').classList.remove('hidden');
                document.getElementById('search-input').focus();
            }
        }

        function toggleManualInput() {
            isManualMode = !isManualMode;
            const btn = document.getElementById('btn-toggle-manual');
            const searchSec = document.getElementById('section-search');
            const manualSec = document.getElementById('section-manual');
            
            resetPilihanPegawai();

            if (isManualMode) {
                btn.innerText = '← Kembali ke Daftar Pegawai BPS';
                searchSec.classList.add('hidden');
                manualSec.classList.remove('hidden');
                document.getElementById('input-manual-name').focus();
            } else {
                btn.innerText = '+ Bukan Pegawai BPS? Isi Manual';
                searchSec.classList.remove('hidden');
                manualSec.classList.add('hidden');
            }
        }

        function filterPegawaiList() {
            const query = document.getElementById('search-input').value.toLowerCase().trim();
            const rows = document.querySelectorAll('.pegawai-row');
            rows.forEach(r => {
                const text = r.getAttribute('data-search');
                if (!query || text.includes(query)) {
                    r.style.display = 'block';
                } else {
                    r.style.display = 'none';
                }
            });
        }

        function selectStatus(status) {
            currentStatus = status;
            document.getElementById('input-status').value = status;

            // Reset all card styles
            const cards = ['Hadir', 'Sedang-Ada-Kegiatan-Lain', 'Tidak-Hadir', 'Belum-Hadir'];
            cards.forEach(c => {
                const el = document.getElementById('card-status-' + c);
                const badge = document.getElementById('badge-' + c);
                if (el) {
                    el.classList.remove('selected-hadir', 'selected-lain', 'selected-tidak', 'selected-belum');
                }
                if (badge) badge.classList.add('hidden');
            });

            // Set active card style
            const activeKey = status.replace(/\s+/g, '-');
            const activeCard = document.getElementById('card-status-' + activeKey);
            const activeBadge = document.getElementById('badge-' + activeKey);
            const labelTerpilih = document.getElementById('label-status-terpilih');

            if (status === 'Hadir') {
                if (activeCard) activeCard.classList.add('selected-hadir');
                labelTerpilih.className = 'text-[11px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200';
                labelTerpilih.innerText = 'Status: Hadir';
            } else if (status === 'Sedang Ada Kegiatan Lain') {
                if (activeCard) activeCard.classList.add('selected-lain');
                labelTerpilih.className = 'text-[11px] font-bold text-amber-700 bg-amber-50 px-2 py-0.5 rounded-full border border-amber-200';
                labelTerpilih.innerText = 'Status: Sedang Ada Kegiatan Lain';
            } else if (status === 'Tidak Hadir') {
                if (activeCard) activeCard.classList.add('selected-tidak');
                labelTerpilih.className = 'text-[11px] font-bold text-rose-700 bg-rose-50 px-2 py-0.5 rounded-full border border-rose-200';
                labelTerpilih.innerText = 'Status: Tidak Hadir';
            } else {
                if (activeCard) activeCard.classList.add('selected-belum');
                labelTerpilih.className = 'text-[11px] font-bold text-slate-700 bg-slate-100 px-2 py-0.5 rounded-full border border-slate-200';
                labelTerpilih.innerText = 'Status: Belum Hadir';
            }

            if (activeBadge) activeBadge.classList.remove('hidden');

            // Show/hide keterangan input
            const ketSec = document.getElementById('section-keterangan');
            if (status === 'Sedang Ada Kegiatan Lain' || status === 'Tidak Hadir') {
                ketSec.classList.remove('hidden');
            } else {
                ketSec.classList.add('hidden');
            }
        }

        function toggleDaftarPeserta() {
            const content = document.getElementById('content-daftar-peserta');
            const arrow = document.getElementById('arrow-daftar-peserta');
            if (content.classList.contains('hidden')) {
                content.classList.remove('hidden');
                arrow.classList.add('rotate-180');
            } else {
                content.classList.add('hidden');
                arrow.classList.remove('rotate-180');
            }
        }

        // Handle AJAX form submit for instant feedback without waiting
        document.getElementById('form-presensi').addEventListener('submit', function(e) {
            e.preventDefault();

            const nip = document.getElementById('input-niplama').value;
            let nama = document.getElementById('input-nama').value;
            if (isManualMode) {
                nama = document.getElementById('input-manual-name').value.trim();
                document.getElementById('input-nama').value = nama;
            }

            if (!nip && !nama) {
                alert('Silakan pilih nama Anda atau ketik nama Anda terlebih dahulu.');
                return;
            }

            const btn = document.getElementById('btn-submit-presensi');
            const btnText = document.getElementById('btn-submit-text');
            btn.disabled = true;
            btnText.innerText = 'Menyimpan Presensi...';

            const formData = new FormData(this);

            fetch(this.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const toast = document.getElementById('toast-success');
                    document.getElementById('toast-message').innerText = data.message;
                    toast.classList.remove('hidden');
                    document.getElementById('toast-error').classList.add('hidden');
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                } else {
                    alert(data.message || 'Terjadi kesalahan saat menyimpan presensi.');
                }
            })
            .catch(err => {
                // Fallback to standard form submit if fetch fails
                this.submit();
            })
            .finally(() => {
                btn.disabled = false;
                btnText.innerText = 'Konfirmasi Presensi Tersimpan';
                setTimeout(() => {
                    btnText.innerText = 'Kirim Konfirmasi Presensi';
                }, 3000);
            });
        });
    </script>
</body>
</html>
