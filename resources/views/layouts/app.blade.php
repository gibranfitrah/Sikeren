<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Sikeren - Sistem Kegiatan Terencana')</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Vite for Tailwind CSS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Custom Head Content -->
    @stack('styles')
    
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f3f4f6; }
        
        /* Custom Sidebar Scrollbar */
        .sidebar-scroll::-webkit-scrollbar {
            width: 6px;
        }
        .sidebar-scroll::-webkit-scrollbar-track {
            background: transparent;
        }
        .sidebar-scroll::-webkit-scrollbar-thumb {
            background-color: #334155; /* slate-700 */
            border-radius: 20px;
        }
        .sidebar-scroll::-webkit-scrollbar-thumb:hover {
            background-color: #475569; /* slate-600 */
        }
        
        /* Firefox */
        .sidebar-scroll {
            scrollbar-width: thin;
            scrollbar-color: #334155 transparent;
        }
    </style>
</head>
<body class="text-gray-800 antialiased h-screen flex overflow-hidden">
    
    <!-- Sidebar -->
    <aside class="w-64 bg-slate-900 shadow-2xl flex-shrink-0 flex flex-col transition-all duration-300 z-20 relative">
        <!-- Sidebar Header (Logo) -->
        <div class="h-20 flex items-center justify-center border-b border-slate-800 px-6 bg-slate-950/50">
            <a href="/dashboard" class="flex items-center gap-3 group">
                <img src="{{ asset('assets/img/logo-sikeren-square.png') }}" alt="Sikeren" class="h-9 w-auto rounded-xl shadow-lg group-hover:scale-105 transition-transform duration-300" onerror="this.style.display='none'">
                <span class="text-2xl font-black text-white tracking-tight bg-clip-text text-transparent bg-gradient-to-r from-blue-400 to-blue-200">Sikeren</span>
            </a>
        </div>
        
        <div class="flex-1 overflow-y-auto py-6 px-4 space-y-1 sidebar-scroll">
            
            @php
                $currentRoute = request()->path();
                $isDashboard = $currentRoute == 'dashboard' || $currentRoute == '/';
                $isDaftar = $currentRoute == 'daftar_kegiatan' || $currentRoute == 'kelola-kegiatan' || $currentRoute == 'ketua-tim';
                $isSubKegiatan = str_starts_with($currentRoute, 'sub-kegiatan');
                $isKegiatanSaya = $currentRoute == 'kegiatan-saya' || $currentRoute == 'tugas-saya';
                $isRapat = $currentRoute == 'rapat';
                $isBooking = str_starts_with($currentRoute, 'booking-ruangan');
                $isTimeSchedule = str_starts_with($currentRoute, 'time-schedule');
                $isSurat = str_starts_with($currentRoute, 'surat-masuk') || str_starts_with($currentRoute, 'disposisi');
                $isBmn = $currentRoute == 'bmn' || $currentRoute == 'pemeliharaan_bmn';
                $isQr = $currentRoute == 'qr';
                $isReport = $currentRoute == 'report';
                $isAbsen = $currentRoute == 'absen-kantor';
                $isSimpati = str_starts_with($currentRoute, 'admin/simpati');
            @endphp

            <!-- Menu Group: Utama -->
            <p class="px-3 text-[11px] font-bold text-slate-500 uppercase tracking-widest mb-3 mt-2">Utama</p>
            <a href="/dashboard" class="flex items-center px-4 py-3 rounded-xl transition-all duration-200 {{ $isDashboard ? 'bg-blue-600 text-white shadow-lg shadow-blue-900/50 font-semibold' : 'text-slate-400 hover:bg-slate-800 hover:text-white font-medium' }}">
                <svg class="w-5 h-5 mr-3 {{ $isDashboard ? 'text-white' : 'text-slate-500 group-hover:text-slate-300' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                <span>Dashboard</span>
            </a>
            
            <!-- Menu Group: Kegiatan & Proyek Tim -->
            <p class="px-3 text-[11px] font-bold text-slate-500 uppercase tracking-widest mb-3 mt-8">Kegiatan & Proyek Tim</p>
            
            <!-- Kelola Kegiatan (Daftar Kegiatan) -->
            <a href="/daftar_kegiatan" class="flex items-center px-4 py-3 rounded-xl transition-all duration-200 {{ $isDaftar ? 'bg-blue-600 text-white shadow-lg shadow-blue-900/50 font-semibold' : 'text-slate-400 hover:bg-slate-800 hover:text-white font-medium' }}">
                <svg class="w-5 h-5 mr-3 {{ $isDaftar ? 'text-white' : 'text-slate-500 group-hover:text-slate-300' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg>
                <span>Kelola Kegiatan</span>
            </a>

            <!-- Buat Kegiatan Baru -->
            <a href="{{ route('ketua-tim.create') }}" class="flex items-center px-4 py-3 rounded-xl transition-all duration-200 {{ request()->is('ketua-tim/create') ? 'bg-blue-600 text-white shadow-lg shadow-blue-900/50 font-semibold' : 'text-slate-400 hover:bg-slate-800 hover:text-white font-medium' }}">
                <svg class="w-5 h-5 mr-3 {{ request()->is('ketua-tim/create') ? 'text-white' : 'text-slate-500 group-hover:text-slate-300' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>Buat Kegiatan Baru</span>
            </a>

            <!-- Sub Kegiatan -->
            <a href="/sub-kegiatan" class="flex items-center px-4 py-3 rounded-xl transition-all duration-200 {{ $isSubKegiatan ? 'bg-blue-600 text-white shadow-lg shadow-blue-900/50 font-semibold' : 'text-slate-400 hover:bg-slate-800 hover:text-white font-medium' }}">
                <svg class="w-5 h-5 mr-3 {{ $isSubKegiatan ? 'text-white' : 'text-slate-500 group-hover:text-slate-300' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                <span>Sub Kegiatan</span>
            </a>

            <!-- Tugas Saya -->
            <a href="/tugas-saya" class="flex items-center px-4 py-3 rounded-xl transition-all duration-200 {{ $isKegiatanSaya ? 'bg-blue-600 text-white shadow-lg shadow-blue-900/50 font-semibold' : 'text-slate-400 hover:bg-slate-800 hover:text-white font-medium' }}">
                <svg class="w-5 h-5 mr-3 {{ $isKegiatanSaya ? 'text-white' : 'text-slate-500 group-hover:text-slate-300' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                <span>Tugas Saya</span>
            </a>

            <!-- Menu Group: Rapat & Sarpras (Ruangan / Zoom) -->
            <p class="px-3 text-[11px] font-bold text-slate-500 uppercase tracking-widest mb-3 mt-8">Rapat & Sarpras Ruangan</p>

            <!-- Buat Rapat -->
            <a href="/rapat" class="flex items-center px-4 py-3 rounded-xl transition-all duration-200 {{ $isRapat ? 'bg-blue-600 text-white shadow-lg shadow-blue-900/50 font-semibold' : 'text-slate-400 hover:bg-slate-800 hover:text-white font-medium' }}">
                <svg class="w-5 h-5 mr-3 {{ $isRapat ? 'text-white' : 'text-slate-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                <span>Buat Rapat</span>
            </a>

            <!-- Booking Ruangan & Zoom -->
            <a href="{{ route('booking-ruangan.index') }}" class="flex items-center px-4 py-3 rounded-xl transition-all duration-200 {{ $isBooking ? 'bg-blue-600 text-white shadow-lg shadow-blue-900/50 font-semibold' : 'text-slate-400 hover:bg-slate-800 hover:text-white font-medium' }}">
                <svg class="w-5 h-5 mr-3 {{ $isBooking ? 'text-white' : 'text-slate-500 group-hover:text-slate-300' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                <span>Booking Ruangan & Zoom</span>
            </a>

            <!-- Time Schedule -->
            <a href="{{ route('time-schedule.index') }}" class="flex items-center px-4 py-3 rounded-xl transition-all duration-200 {{ $isTimeSchedule ? 'bg-blue-600 text-white shadow-lg shadow-blue-900/50 font-semibold' : 'text-slate-400 hover:bg-slate-800 hover:text-white font-medium' }}">
                <svg class="w-5 h-5 mr-3 {{ $isTimeSchedule ? 'text-white' : 'text-slate-500 group-hover:text-slate-300' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                <span>Time Schedule</span>
            </a>

            <!-- Menu Group: Persuratan & BMN -->
            <p class="px-3 text-[11px] font-bold text-slate-500 uppercase tracking-widest mb-3 mt-8">Persuratan & BMN</p>
            
            <!-- E-Office Navigation -->
            <a href="{{ route('surat-masuk.index') }}" class="group flex items-center px-4 py-3 mb-1 text-sm font-medium rounded-xl transition-all duration-200 {{ $isSurat ? 'bg-indigo-600/10 text-indigo-400' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                <svg class="mr-3 h-5 w-5 {{ $isSurat ? 'text-indigo-400' : 'text-slate-500 group-hover:text-slate-300' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                <span>Surat Masuk & Disposisi</span>
            </a>

            <a href="/bmn" class="flex items-center px-4 py-3 rounded-xl transition-all duration-200 {{ $isBmn ? 'bg-blue-600 text-white shadow-lg shadow-blue-900/50 font-semibold' : 'text-slate-400 hover:bg-slate-800 hover:text-white font-medium' }}">
                <svg class="w-5 h-5 mr-3 {{ $isBmn ? 'text-white' : 'text-slate-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>
                <span>BMN</span>
            </a>

            <!-- Menu Group: Presensi -->
            <p class="px-3 text-[11px] font-bold text-slate-500 uppercase tracking-widest mb-3 mt-8">Presensi</p>
            <a href="/qr" class="flex items-center px-4 py-3 rounded-xl transition-all duration-200 {{ $isQr ? 'bg-blue-600 text-white shadow-lg shadow-blue-900/50 font-semibold' : 'text-slate-400 hover:bg-slate-800 hover:text-white font-medium' }}">
                <svg class="w-5 h-5 mr-3 {{ $isQr ? 'text-white' : 'text-slate-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                <span>QR Code</span>
            </a>
            <a href="/report" class="flex items-center px-4 py-3 rounded-xl transition-all duration-200 {{ $isReport ? 'bg-blue-600 text-white shadow-lg shadow-blue-900/50 font-semibold' : 'text-slate-400 hover:bg-slate-800 hover:text-white font-medium' }}">
                <svg class="w-5 h-5 mr-3 {{ $isReport ? 'text-white' : 'text-slate-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                <span>Report Presensi</span>
            </a>
            <a href="/absen-kantor" class="flex items-center px-4 py-3 rounded-xl transition-all duration-200 {{ $isAbsen ? 'bg-blue-600 text-white shadow-lg shadow-blue-900/50 font-semibold' : 'text-slate-400 hover:bg-slate-800 hover:text-white font-medium' }}">
                <svg class="w-5 h-5 mr-3 {{ $isAbsen ? 'text-white' : 'text-slate-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span>Presensi</span>
            </a>
            
            <!-- Menu Group: Sistem & Integrasi -->
            <p class="px-3 text-[11px] font-bold text-slate-500 uppercase tracking-widest mb-3 mt-8">Sistem & Integrasi</p>
            @if(Auth::check() && Auth::user()->isAdmin())
            <a href="{{ route('simpati.index') }}" class="flex items-center px-4 py-3 rounded-xl transition-all duration-200 {{ $isSimpati ? 'bg-blue-600 text-white shadow-lg shadow-blue-900/50 font-semibold' : 'text-slate-400 hover:bg-slate-800 hover:text-white font-medium' }}">
                <svg class="w-5 h-5 mr-3 {{ $isSimpati ? 'text-white' : 'text-slate-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                <span>Integrasi SIMPATI</span>
            </a>
            @endif
            <a href="{{ route('actionlogout') }}" class="flex items-center px-4 py-3 text-rose-400 hover:bg-rose-500/10 hover:text-rose-300 rounded-xl transition-colors font-medium">
                <svg class="w-5 h-5 mr-3 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                <span>Keluar</span>
            </a>
        </div>
    </aside>

    <!-- Main Content wrapper -->
    <div class="flex-1 flex flex-col h-screen overflow-hidden">
        <!-- Top Header -->
        <header class="h-20 bg-white shadow-sm flex items-center justify-between px-8 z-10">
            <div class="flex items-center gap-4">
                <h1 class="text-2xl font-semibold text-gray-800">@yield('header_title', 'Dashboard')</h1>
            </div>
            
                       <div class="flex items-center gap-6">
                
                <!-- =====================================================
                    NOTIFICATIONS DROPDOWN
                ====================================================== -->
                <div class="relative" x-data="{ open: false }">

                    {{-- Tombol Lonceng --}}
                    <button @click="open = !open"
                            type="button"
                            class="relative p-2 text-gray-400 hover:text-blue-600 focus:outline-none transition-colors">
                        
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9">
                            </path>
                        </svg>

                        {{-- Titik Merah Peringatan --}}
                        @if(isset($jumlah_notif) && $jumlah_notif > 0)
                            <span class="absolute top-1 right-1 flex h-3 w-3">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-3 w-3 bg-red-500 text-[9px] text-white font-bold items-center justify-center"></span>
                            </span>
                        @endif

                    </button>

                    {{-- Menu Pop-up Daftar Notifikasi --}}
                    <div x-show="open"
                         @click.away="open = false"
                         x-cloak
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                         x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                         class="absolute right-0 mt-3 w-80 sm:w-96 bg-white rounded-3xl shadow-2xl border border-gray-100 p-4 z-50">

                        {{-- Header Notifikasi --}}
                        <div class="flex items-center justify-between pb-3 border-b border-gray-100 px-2">
                            <div>
                                <h4 class="font-bold text-gray-800 text-sm">Notifikasi</h4>
                                <span class="text-[11px] font-semibold text-blue-600">
                                    {{ $jumlah_notif ?? 0 }} Belum Dibaca
                                </span>
                            </div>
                            @if(isset($jumlah_notif) && $jumlah_notif > 0)
                                <form action="{{ route('notification.markAllRead') }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="text-[11px] font-bold text-gray-500 hover:text-blue-600 transition">
                                        Tandai Semua Dibaca
                                    </button>
                                </form>
                            @endif
                        </div>

                        {{-- Isi Notifikasi --}}
                        <div class="mt-2 space-y-2 max-h-80 overflow-y-auto">

                            @forelse($notifications ?? [] as $notif)
                                @php
                                    $nData = is_array($notif->data) ? $notif->data : (json_decode($notif->data ?? '{}', true) ?: []);
                                    $nJudul = $nData['judul'] ?? ($notif->judul ?? 'Pemberitahuan');
                                    $nPesan = $nData['pesan'] ?? ($notif->pesan ?? '-');
                                    $nUrl   = route('notification.read', $notif->id);
                                    $isUnread = is_null($notif->read_at);
                                @endphp

                                <a href="{{ $nUrl }}"
                                   class="flex items-start gap-3 p-3 rounded-2xl hover:bg-gray-50 transition-colors {{ $isUnread ? 'bg-blue-50/50' : '' }}">
                                    
                                    <div class="w-8 h-8 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center flex-shrink-0 mt-0.5">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>

                                    <div class="flex-1 min-w-0">
                                        <p class="text-xs font-bold text-gray-900 truncate">{{ $nJudul }}</p>
                                        <p class="text-xs text-gray-600 mt-0.5 line-clamp-2 leading-relaxed">{{ $nPesan }}</p>
                                        <span class="text-[10px] text-gray-400 mt-1 block font-medium">
                                            {{ \Carbon\Carbon::parse($notif->created_at)->diffForHumans() }}
                                        </span>
                                    </div>

                                    @if($isUnread)
                                        <span class="w-2 h-2 rounded-full bg-blue-500 mt-2 flex-shrink-0" title="Belum dibaca"></span>
                                    @endif

                                </a>

                            @empty

                                <div class="py-8 text-center text-gray-400">
                                    <div class="w-12 h-12 rounded-2xl bg-gray-50 flex items-center justify-center mx-auto mb-2 text-gray-300">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                        </svg>
                                    </div>
                                    <p class="text-xs font-medium">Tidak ada notifikasi baru</p>
                                </div>

                            @endforelse

                        </div>

                    </div>

                </div>
                
                <!-- Profile -->
                <div class="flex items-center gap-3 border-l border-gray-200 pl-6">
                    <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center text-blue-700 font-bold shadow-xs">
                        {{ strtoupper(substr(Auth::user()->nama_lengkap ?? 'U', 0, 1)) }}
                    </div>
                    <div class="hidden md:block text-xs">
                        <div class="flex items-center gap-1.5">
                            <p class="font-bold text-gray-800">{{ Auth::user()->nama_lengkap ?? 'User' }}</p>
                            @if(Auth::check())
                                <x-badge :variant="Auth::user()->role_badge_variant" size="xs">
                                    {{ Auth::user()->role_label }}
                                </x-badge>
                            @endif
                        </div>
                        <p class="text-gray-400 text-[11px]">{{ Auth::user()->formatted_nip }} • {{ Auth::user()->email }}</p>
                    </div>
                </div>

            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-8">
            <div class="max-w-7xl mx-auto">
                @yield('content')
            </div>
        </main>
    </div>

    @stack('scripts')
</body>

<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<style>[x-cloak] { display: none !important; }</style>


</html>
