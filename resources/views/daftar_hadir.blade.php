<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Presensi Peserta - {{ $task->text ?? 'Rapat BPS' }}</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS CDN for instant, guaranteed zero-purge standalone mobile styling -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            900: '#1e3a8a',
                        }
                    }
                }
            }
        }
    </script>
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <style>
        [x-cloak] { display: none !important; }
        body { -webkit-tap-highlight-color: transparent; }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 font-sans min-h-screen flex flex-col justify-between selection:bg-blue-500 selection:text-white pb-8">

    @php
        $rapatText      = $task->text ?? ($task->title ?? 'Rapat Koordinasi');
        $rapatAgenda    = $task->agenda ?? '-';
        $rapatTempat    = $task->tempat ?? 'Aula / Kantor BPS';
        $rapatStart     = $task->start_date ?? ($task->start ?? date('Y-m-d'));
        $rapatJamMulai  = $task->start_jam ?? '09:00';
        $rapatJamSelesai= $task->end_jam ?? '12:00';
        $rapatPemimpin  = $task->pemimpin ?? '-';
        $rapatNotulis   = $task->notulis ?? '-';
        $rapatId        = $task->id ?? $id;
    @endphp

    {{-- TOP APP BAR --}}
    <header class="bg-gradient-to-r from-blue-900 via-blue-800 to-indigo-900 text-white sticky top-0 z-30 shadow-md">
        <div class="max-w-md mx-auto px-4 py-3.5 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-blue-500/30 border border-blue-400/40 flex items-center justify-center font-black text-sm text-blue-200">
                    S
                </div>
                <div>
                    <h1 class="font-extrabold text-sm tracking-tight text-white leading-tight">Sikeren Mobile</h1>
                    <p class="text-[10px] text-blue-200 font-medium">BPS Provinsi Sulawesi Tenggara</p>
                </div>
            </div>

            <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-500/20 border border-emerald-400/30 text-emerald-300 text-[11px] font-bold">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span>Presensi Aktif</span>
            </div>
        </div>
    </header>

    {{-- MAIN CONTAINER --}}
    <main class="max-w-md mx-auto w-full px-4 pt-4 space-y-4 flex-1">

        {{-- FLASH ALERT NOTIFICATION --}}
        @if(session('success_presensi'))
            <div x-data="{ show: true }" x-show="show" class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 shadow-sm flex items-start gap-3">
                <div class="w-8 h-8 rounded-xl bg-emerald-500 text-white flex items-center justify-center flex-shrink-0 mt-0.5">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                </div>
                <div class="flex-1 text-xs">
                    <h4 class="font-bold text-emerald-900 text-sm">Presensi Berhasil!</h4>
                    <p class="mt-0.5 text-emerald-700 leading-relaxed">{{ session('success_presensi') }}</p>
                </div>
                <button @click="show = false" class="text-emerald-500 hover:text-emerald-700 text-sm font-bold">&times;</button>
            </div>
        @endif

        @if(session('error_presensi'))
            <div x-data="{ show: true }" x-show="show" class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-900 shadow-sm flex items-start gap-3">
                <div class="w-8 h-8 rounded-xl bg-rose-500 text-white flex items-center justify-center flex-shrink-0 mt-0.5">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                </div>
                <div class="flex-1 text-xs">
                    <h4 class="font-bold text-rose-900 text-sm">Pemberitahuan</h4>
                    <p class="mt-0.5 text-rose-700 leading-relaxed">{{ session('error_presensi') }}</p>
                </div>
                <button @click="show = false" class="text-rose-500 hover:text-rose-700 text-sm font-bold">&times;</button>
            </div>
        @endif

        {{-- MEETING INFO CARD --}}
        <div class="bg-white rounded-2xl p-4.5 border border-slate-200 shadow-sm space-y-3">
            <div>
                <span class="inline-block px-2.5 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider bg-blue-100 text-blue-800 border border-blue-200 mb-1.5">
                    Presensi Rapat
                </span>
                <h2 class="text-base font-extrabold text-slate-900 leading-snug">
                    {{ $rapatText }}
                </h2>
                @if(!empty($rapatAgenda) && $rapatAgenda !== '-')
                    <p class="text-xs text-slate-500 mt-1 line-clamp-2">{{ $rapatAgenda }}</p>
                @endif
            </div>

            <div class="grid grid-cols-2 gap-2 pt-1 text-[11px]">
                <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                    <span class="text-slate-400 font-semibold block text-[10px]">WAKTU</span>
                    <span class="font-bold text-slate-800 block mt-0.5">
                        {{ \Carbon\Carbon::parse($rapatStart)->translatedFormat('d M Y') }}
                    </span>
                    <span class="text-slate-500 font-mono text-[10px]">
                        {{ substr($rapatJamMulai, 0, 5) }} - {{ substr($rapatJamSelesai, 0, 5) }} WITA
                    </span>
                </div>

                <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                    <span class="text-slate-400 font-semibold block text-[10px]">TEMPAT</span>
                    <span class="font-bold text-slate-800 block mt-0.5 truncate" title="{{ $rapatTempat }}">
                        {{ $rapatTempat }}
                    </span>
                    <span class="text-slate-500 text-[10px] truncate block">
                        Pemimpin: {{ $rapatPemimpin }}
                    </span>
                </div>
            </div>
        </div>

        {{-- FORM PRESENSI CARD --}}
        <div x-data="{
            selectedNip: '{{ Auth::check() ? (Auth::user()->niplama ?? '') : '' }}',
            selectedNama: '{{ Auth::check() ? (Auth::user()->nama_lengkap ?? '') : '' }}',
            searchQuery: '',
            isManual: false,
            manualName: '',
            pegawaiList: {{ json_encode($allPegawai ?? []) }},
            get filteredPegawai() {
                if (!this.searchQuery) return this.pegawaiList;
                return this.pegawaiList.filter(p => 
                    p.nama_lengkap.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                    (p.nipbaru && p.nipbaru.includes(this.searchQuery)) ||
                    (p.niplama && p.niplama.includes(this.searchQuery))
                );
            },
            selectPegawai(p) {
                this.selectedNip = p.niplama;
                this.selectedNama = p.nama_lengkap;
                this.searchQuery = '';
            }
        }" class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm space-y-4">

            <div class="border-b border-slate-100 pb-3 flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-slate-900 text-sm">Form Kehadiran Peserta</h3>
                    <p class="text-[11px] text-slate-500 mt-0.5">Pilih nama Anda untuk mencatat kehadiran</p>
                </div>
                <span class="text-xs font-mono font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded-lg">
                    ID #{{ $rapatId }}
                </span>
            </div>

            <form action="{{ route('daftarhadir.submit') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="id_kegiatan" value="{{ $rapatId }}">
                <input type="hidden" name="niplama" :value="isManual ? '' : selectedNip">
                <input type="hidden" name="nama_lengkap" :value="selectedNama">

                {{-- JIKA SUDAH LOGIN / TERPILIH --}}
                <div x-show="!isManual && selectedNip" class="p-3.5 rounded-xl bg-blue-50/70 border border-blue-200 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-blue-700">Identitas Peserta Terpilih</span>
                        <button type="button" @click="selectedNip = ''; selectedNama = '';" class="text-[11px] font-bold text-blue-600 hover:text-blue-800 underline">
                            Ganti Pegawai
                        </button>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-600 text-white font-bold text-sm flex items-center justify-center flex-shrink-0 shadow-xs">
                            <span x-text="selectedNama ? selectedNama.charAt(0).toUpperCase() : 'P'"></span>
                        </div>
                        <div class="overflow-hidden">
                            <h4 class="text-xs font-bold text-slate-900 truncate" x-text="selectedNama"></h4>
                            <p class="text-[11px] text-slate-500 font-mono" x-text="'NIP: ' + selectedNip"></p>
                        </div>
                    </div>
                </div>

                {{-- JIKA BELUM TERPILIH & BUKAN MANUAL --}}
                <div x-show="!isManual && !selectedNip" class="space-y-2">
                    <label class="block text-xs font-bold text-slate-700">
                        Cari & Pilih Nama Pegawai
                    </label>

                    <div class="relative">
                        <input type="text" 
                               x-model="searchQuery"
                               placeholder="Ketik nama atau NIP pegawai..." 
                               class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl py-2.5 pl-9 pr-4 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>

                    {{-- DROPDOWN SELECTION LIST --}}
                    <div class="border border-slate-200 rounded-xl max-h-52 overflow-y-auto divide-y divide-slate-100 bg-white shadow-inner">
                        <template x-for="pegawai in filteredPegawai.slice(0, 30)" :key="pegawai.niplama">
                            <button type="button" 
                                    @click="selectPegawai(pegawai)" 
                                    class="w-full text-left px-3.5 py-2.5 hover:bg-blue-50/70 transition-colors flex items-center justify-between group">
                                <div class="overflow-hidden pr-2">
                                    <p class="text-xs font-semibold text-slate-800 group-hover:text-blue-700 truncate" x-text="pegawai.nama_lengkap"></p>
                                    <p class="text-[10px] text-slate-400 font-mono" x-text="pegawai.nipbaru || pegawai.niplama"></p>
                                </div>
                                <span class="text-[11px] text-blue-600 font-bold opacity-0 group-hover:opacity-100 transition-opacity">
                                    Pilih &rarr;
                                </span>
                            </button>
                        </template>

                        <div x-show="filteredPegawai.length === 0" class="p-4 text-center text-xs text-slate-400">
                            Nama pegawai tidak ditemukan.
                        </div>
                    </div>
                </div>

                {{-- MODE MANUAL INPUT JIKA TAMU LUAR --}}
                <div x-show="isManual" class="space-y-2">
                    <label class="block text-xs font-bold text-slate-700">
                        Nama Lengkap (Tamu / Peserta Luar)
                    </label>
                    <input type="text" 
                           name="peserta_manual"
                           x-model="manualName"
                           placeholder="Masukkan nama lengkap Anda..." 
                           class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl p-2.5 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                {{-- TOGGLE TAMU LUAR --}}
                <div class="flex items-center justify-between pt-1">
                    <button type="button" 
                            @click="isManual = !isManual; if(isManual){ selectedNip = ''; selectedNama = ''; }" 
                            class="text-[11px] font-semibold text-slate-500 hover:text-blue-600 transition-colors">
                        <span x-text="isManual ? '← Kembali ke Daftar Pegawai BPS' : '+ Bukan Pegawai BPS? Isi Manual'"></span>
                    </button>
                </div>

                {{-- SUBMIT BUTTON --}}
                <div class="pt-2">
                    <button type="submit" 
                            :disabled="(!isManual && !selectedNip) || (isManual && !manualName)"
                            class="w-full py-3 px-4 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed text-white text-xs font-bold rounded-xl shadow-md transition-all flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        <span>Konfirmasi Kehadiran Saya</span>
                    </button>
                </div>
            </form>
        </div>

        {{-- DAFTAR PESERTA YANG SUDAH HADIR --}}
        <div x-data="{ openList: false }" class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <button @click="openList = !openList" 
                    type="button" 
                    class="w-full p-4 flex items-center justify-between text-left hover:bg-slate-50/60 transition-colors">
                <div class="flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-emerald-100 text-emerald-700 font-bold text-xs flex items-center justify-center">
                        ✓
                    </span>
                    <div>
                        <h4 class="text-xs font-bold text-slate-900">Daftar Kehadiran Peserta</h4>
                        <p class="text-[10px] text-slate-500">{{ $pesertaList->count() }} orang telah terdaftar/hadir</p>
                    </div>
                </div>
                <svg class="w-4 h-4 text-slate-400 transform transition-transform" :class="openList ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>

            <div x-show="openList" x-cloak class="border-t border-slate-100 max-h-60 overflow-y-auto divide-y divide-slate-100 bg-slate-50/30">
                @forelse($pesertaList as $idx => $p)
                    <div class="px-4 py-2.5 flex items-center justify-between text-xs">
                        <div class="flex items-center gap-2.5 overflow-hidden pr-2">
                            <span class="text-[10px] font-bold text-slate-400 w-4">{{ $idx + 1 }}</span>
                            <div class="overflow-hidden">
                                <p class="font-semibold text-slate-800 text-xs truncate">{{ $p->nama_lengkap }}</p>
                                <p class="text-[10px] text-slate-400 font-mono">{{ $p->nipbaru ?? $p->niplama }}</p>
                            </div>
                        </div>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 border border-emerald-200 flex-shrink-0">
                            Hadir
                        </span>
                    </div>
                @empty
                    <div class="p-4 text-center text-xs text-slate-400">
                        Belum ada peserta yang mengisi daftar hadir.
                    </div>
                @endforelse
            </div>
        </div>

    </main>

    {{-- FOOTER --}}
    <footer class="max-w-md mx-auto w-full px-4 pt-4 text-center text-[10px] text-slate-400">
        &copy; {{ date('Y') }} BPS Provinsi Sulawesi Tenggara &bull; Sikeren
    </footer>

</body>
</html>
