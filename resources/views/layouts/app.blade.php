<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Sikeren - Sistem Kegiatan Terencana')</title>
    
    <!-- Favicon Resmi BPS -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}?v=2">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/img/favicon-32x32.png') }}?v=2">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/img/favicon-16x16.png') }}?v=2">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('favicon.png') }}?v=2">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}?v=2" type="image/x-icon">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/img/apple-icon.png') }}?v=2">
    
    <!-- Google Fonts (display=swap to prevent font popping) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Vite for Tailwind CSS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Custom Head Content -->
    @stack('styles')
    
    <!-- Instant Sidebar Pre-paint Script (Stops layout shift & jarring navbar flicker) -->
    <script>
        (function() {
            try {
                if (localStorage.getItem('sikeren_sidebar_collapsed') === 'true') {
                    document.documentElement.classList.add('sidebar-collapsed');
                }
            } catch (e) {}
        })();
    </script>

    <style>
        [x-cloak] { display: none !important; }
        
        /* Spacing for FullCalendar Navigation and View Buttons */
        .fc .fc-toolbar-chunk {
            display: flex !important;
            align-items: center !important;
            gap: 0.375rem !important;
        }
        .fc .fc-button-group {
            display: inline-flex !important;
            gap: 0.375rem !important;
        }
        .fc .fc-button-group > .fc-button {
            margin: 0 !important;
            border-radius: 0.75rem !important;
        }
        
        /* Modern System Font Fallback to prevent font shift */
        body { 
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #f8fafc;
        }

        /* Stable scrollbar gutter so page content does not jump horizontally on navigation */
        html {
            scrollbar-gutter: stable;
        }
        
        main#main-content {
            scrollbar-gutter: stable;
        }
        
        /* Completely hide scrollbars in sidebar while preserving scrolling capability */
        .sidebar-scroll {
            scrollbar-width: none !important;
            -ms-overflow-style: none !important;
        }
        .sidebar-scroll::-webkit-scrollbar {
            display: none !important;
            width: 0 !important;
            height: 0 !important;
        }

        /* =====================================================================
           ZERO-LAYOUT-SHIFT SIDEBAR RULES
           Enforces widths synchronously so the navbar never jitters/resizes on page load
        ====================================================================== */
        html.sidebar-collapsed aside#app-sidebar {
            width: 4.5rem !important; /* 72px */
        }
        html.sidebar-collapsed #app-sidebar .sidebar-text,
        html.sidebar-collapsed #app-sidebar .sidebar-heading,
        html.sidebar-collapsed #app-sidebar .sidebar-chevron {
            display: none !important;
        }
        html.sidebar-collapsed #app-sidebar .sidebar-divider {
            display: block !important;
        }
        html.sidebar-collapsed #app-sidebar .sidebar-nav-item {
            justify-content: center !important;
            padding: 0.625rem 0 !important;
        }
        html.sidebar-collapsed #app-sidebar .sidebar-nav-item svg {
            margin-right: 0 !important;
        }
        
        html:not(.sidebar-collapsed) aside#app-sidebar {
            width: 16rem !important; /* 256px - comfortable and spacious */
        }
        html:not(.sidebar-collapsed) #app-sidebar .sidebar-divider {
            display: none !important;
        }

        /* Sidebar Footer Responsive Rules */
        html.sidebar-collapsed #app-sidebar .sidebar-footer-full {
            display: none !important;
        }
        html.sidebar-collapsed #app-sidebar .sidebar-footer-mini {
            display: block !important;
        }
        html:not(.sidebar-collapsed) #app-sidebar .sidebar-footer-mini {
            display: none !important;
        }
        html:not(.sidebar-collapsed) #app-sidebar .sidebar-footer-full {
            display: block !important;
        }

        /* Smooth transition ONLY when user clicks toggle (never during page navigation) */
        aside#app-sidebar.sidebar-interactive {
            transition: width 220ms cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* =====================================================================
           MODERN SAAS SIDEBAR TYPOGRAPHY & INTERACTIVE STATES (Fluid, not stiff)
        ====================================================================== */
        #app-sidebar {
            background-color: #0f172a !important; /* Modern deep slate */
        }

        #app-sidebar .sidebar-heading {
            font-size: 10px !important;
            font-weight: 700 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.08em !important;
            color: #64748b !important;
            line-height: 1rem !important;
            padding: 0 12px !important;
            margin-top: 18px !important;
            margin-bottom: 6px !important;
        }

        #app-sidebar a.sidebar-nav-item,
        #app-sidebar button.sidebar-nav-item {
            font-size: 13px !important;
            font-weight: 500 !important;
            line-height: 1.25rem !important;
            outline: none !important;
            color: #94a3b8 !important;
            transition: all 150ms cubic-bezier(0.4, 0, 0.2, 1) !important;
        }

        #app-sidebar a.sidebar-nav-item:hover,
        #app-sidebar button.sidebar-nav-item:hover {
            background-color: rgba(255, 255, 255, 0.06) !important;
            color: #f8fafc !important;
        }

        #app-sidebar a.sidebar-nav-item:hover svg,
        #app-sidebar button.sidebar-nav-item:hover svg {
            color: #93c5fd !important;
        }

        /* Modern fluid active menu item (No rigid flat block!) */
        #app-sidebar a.sidebar-nav-item.sidebar-active,
        #app-sidebar button.sidebar-nav-item.sidebar-active {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%) !important;
            color: #ffffff !important;
            font-weight: 600 !important;
            box-shadow: 0 4px 14px -3px rgba(37, 99, 235, 0.45) !important;
        }
        #app-sidebar a.sidebar-nav-item.sidebar-active svg,
        #app-sidebar button.sidebar-nav-item.sidebar-active svg {
            color: #ffffff !important;
        }

        #app-sidebar button.sidebar-nav-item:focus,
        #app-sidebar button.sidebar-nav-item:focus-visible,
        #app-sidebar a.sidebar-nav-item:focus,
        #app-sidebar a.sidebar-nav-item:focus-visible {
            outline: none !important;
            box-shadow: none !important;
        }

        #app-sidebar .sidebar-text {
            font-size: 13px !important;
        }

        #app-sidebar .sidebar-subnav a {
            font-size: 12px !important;
            font-weight: 500 !important;
            line-height: 1.25rem !important;
            outline: none !important;
            color: #94a3b8 !important;
            transition: all 150ms ease !important;
        }
        #app-sidebar .sidebar-subnav a:hover {
            background-color: rgba(255, 255, 255, 0.05) !important;
            color: #f1f5f9 !important;
        }

        #app-sidebar .sidebar-subnav a span {
            font-size: 12px !important;
        }

        /* =====================================================================
           NAVBAR USER AVATAR BOX (Explicit styling to guarantee crisp display)
        ====================================================================== */
        .navbar-avatar-box {
            background-color: #eff6ff !important;
            border: 1.5px solid #bfdbfe !important;
            color: #1d4ed8 !important;
            font-weight: 800 !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05) !important;
        }
        .group:hover .navbar-avatar-box {
            background-color: #dbeafe !important;
            border-color: #93c5fd !important;
            color: #1e40af !important;
        }
    </style>
</head>
<body class="text-slate-800 antialiased h-screen flex overflow-hidden bg-slate-50"
      x-data="{
          sidebarCollapsed: localStorage.getItem('sikeren_sidebar_collapsed') === 'true',
          isInteractive: false,
          toggleSidebar() {
              this.isInteractive = true;
              this.sidebarCollapsed = !this.sidebarCollapsed;
              localStorage.setItem('sikeren_sidebar_collapsed', this.sidebarCollapsed);
              if (this.sidebarCollapsed) {
                  document.documentElement.classList.add('sidebar-collapsed');
              } else {
                  document.documentElement.classList.remove('sidebar-collapsed');
              }
          }
      }">
    
    <!-- =========================================================================
         SIDEBAR (Rock-solid width, zero layout shift)
    ========================================================================== -->
    <aside id="app-sidebar"
           :class="{ 'sidebar-interactive': isInteractive }"
           class="bg-slate-900 shadow-xl shrink-0 flex flex-col z-30 relative select-none">
        
        <!-- Sidebar Header (Fixed Height 16 = 64px) -->
        <div class="h-16 shrink-0 flex items-center px-4 border-b border-slate-800/80 bg-slate-950/40">
            <a href="/dashboard" class="flex items-center gap-3 group w-full overflow-hidden" :title="sidebarCollapsed ? 'Sikeren Dashboard' : ''">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-600 flex items-center justify-center shadow-md shadow-blue-500/20 shrink-0 p-1 group-hover:scale-105 transition-transform duration-200">
                    <img src="{{ asset('assets/img/logo-sikeren-square.png') }}" alt="Sikeren"
                         class="w-full h-full object-contain rounded-lg"
                         onerror="this.style.display='none'">
                </div>
                <div class="sidebar-text flex flex-col justify-center min-w-0">
                    <span class="text-base font-extrabold tracking-tight text-white leading-tight">
                        Sikeren
                    </span>
                    <span class="text-[10px] font-semibold text-slate-400 tracking-wider uppercase leading-none mt-0.5">
                        BPS Sultra
                    </span>
                </div>
            </a>
        </div>
        
        <!-- Sidebar Nav Links -->
        <div class="flex-1 overflow-y-auto py-3 px-2.5 space-y-1 sidebar-scroll">
            
            @php
                $currentRoute = request()->path();
                $isDashboard = $currentRoute == 'dashboard' || $currentRoute == '/';
                $isDaftar = $currentRoute == 'daftar_kegiatan' || $currentRoute == 'kelola-kegiatan' || $currentRoute == 'ketua-tim';
                $isSubKegiatan = str_starts_with($currentRoute, 'sub-kegiatan');
                $isKegiatanSaya = $currentRoute == 'kegiatan-saya' || $currentRoute == 'tugas-saya';
                $isRapat = $currentRoute == 'rapat';
                $isBooking = str_starts_with($currentRoute, 'booking-ruangan') || str_starts_with($currentRoute, 'booking_ruangan');
                $isTimeSchedule = str_starts_with($currentRoute, 'time-schedule') || str_starts_with($currentRoute, 'time_schedule');
                $isSurat = str_starts_with($currentRoute, 'surat-masuk') || str_starts_with($currentRoute, 'disposisi');
                $isBmn = $currentRoute == 'bmn' || $currentRoute == 'pemeliharaan_bmn';
                $isQr = $currentRoute == 'qr';
                $isReport = $currentRoute == 'report';
                $isAbsen = $currentRoute == 'absen-kantor';
                $isSimpati = str_starts_with($currentRoute, 'admin/simpati');
            @endphp

            <!-- Menu Group: Utama -->
            <p class="sidebar-heading px-2.5 text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1 mt-2 whitespace-nowrap">Utama</p>
            <div class="sidebar-divider my-1.5 border-t border-slate-800/80"></div>
            
            <a href="/dashboard"
               :title="sidebarCollapsed ? 'Dashboard' : ''"
               class="sidebar-nav-item flex items-center px-3 py-2 rounded-xl text-[13px] {{ $isDashboard ? 'sidebar-active' : '' }}">
                <svg class="w-5 h-5 shrink-0 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                <span class="sidebar-text whitespace-nowrap overflow-hidden text-ellipsis">Dashboard</span>
            </a>
            
            <!-- Menu Group: Kegiatan & Proyek Tim -->
            <p class="sidebar-heading px-2.5 text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1 mt-4 whitespace-nowrap">Kegiatan & Proyek Tim</p>
            <div class="sidebar-divider my-1.5 border-t border-slate-800/80"></div>
            
            <!-- Kelola Kegiatan -->
            <a href="/daftar_kegiatan"
               :title="sidebarCollapsed ? 'Kelola Kegiatan' : ''"
               class="sidebar-nav-item flex items-center px-3 py-2 rounded-xl text-[13px] {{ $isDaftar ? 'sidebar-active' : '' }}">
                <svg class="w-5 h-5 shrink-0 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg>
                <span class="sidebar-text whitespace-nowrap overflow-hidden text-ellipsis">Kelola Kegiatan</span>
            </a>

            <!-- Sub Kegiatan -->
            <a href="/sub-kegiatan"
               :title="sidebarCollapsed ? 'Sub Kegiatan' : ''"
               class="sidebar-nav-item flex items-center px-3 py-2 rounded-xl text-[13px] {{ $isSubKegiatan ? 'sidebar-active' : '' }}">
                <svg class="w-5 h-5 shrink-0 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                <span class="sidebar-text whitespace-nowrap overflow-hidden text-ellipsis">Sub Kegiatan</span>
            </a>

            <!-- Tugas Saya -->
            <a href="/tugas-saya"
               :title="sidebarCollapsed ? 'Tugas Saya' : ''"
               class="sidebar-nav-item flex items-center px-3 py-2 rounded-xl text-[13px] {{ $isKegiatanSaya ? 'sidebar-active' : '' }}">
                <svg class="w-5 h-5 shrink-0 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                <span class="sidebar-text whitespace-nowrap overflow-hidden text-ellipsis">Tugas Saya</span>
            </a>

            <!-- Menu Group: Rapat & Sarpras -->
            <p class="sidebar-heading px-2.5 text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1 mt-4 whitespace-nowrap">Rapat & Sarpras</p>
            <div class="sidebar-divider my-1.5 border-t border-slate-800/80"></div>

            <!-- Agenda Rapat -->
            <a href="/rapat"
               :title="sidebarCollapsed ? 'Agenda Rapat' : ''"
               class="sidebar-nav-item flex items-center px-3 py-2 rounded-xl text-[13px] {{ $isRapat ? 'sidebar-active' : '' }}">
                <svg class="w-5 h-5 shrink-0 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                <span class="sidebar-text whitespace-nowrap overflow-hidden text-ellipsis">Agenda Rapat</span>
            </a>

            <!-- Booking Ruangan & Zoom -->
            <a href="{{ route('booking-ruangan.index') }}"
               :title="sidebarCollapsed ? 'Booking Ruangan & Zoom' : ''"
               class="sidebar-nav-item flex items-center px-3 py-2 rounded-xl text-[13px] {{ $isBooking ? 'sidebar-active' : '' }}">
                <svg class="w-5 h-5 shrink-0 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                <span class="sidebar-text whitespace-nowrap overflow-hidden text-ellipsis">Booking Ruangan & Zoom</span>
            </a>

            <!-- Time Schedule -->
            <a href="{{ route('time-schedule.index') }}"
               :title="sidebarCollapsed ? 'Time Schedule' : ''"
               class="sidebar-nav-item flex items-center px-3 py-2 rounded-xl text-[13px] {{ $isTimeSchedule ? 'sidebar-active' : '' }}">
                <svg class="w-5 h-5 shrink-0 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                <span class="sidebar-text whitespace-nowrap overflow-hidden text-ellipsis">Time Schedule</span>
            </a>

            <!-- Menu Group: Persuratan & BMN -->
            <p class="sidebar-heading px-2.5 text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1 mt-4 whitespace-nowrap">Persuratan & BMN</p>
            <div class="sidebar-divider my-1.5 border-t border-slate-800/80"></div>
            
            <a href="{{ route('surat-masuk.index') }}"
               :title="sidebarCollapsed ? 'Surat Masuk & Disposisi' : ''"
               class="sidebar-nav-item flex items-center px-3 py-2 rounded-xl text-[13px] {{ $isSurat ? 'sidebar-active' : '' }}">
                <svg class="w-5 h-5 shrink-0 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                <span class="sidebar-text whitespace-nowrap overflow-hidden text-ellipsis">Surat & Disposisi</span>
            </a>

            <a href="/bmn"
               :title="sidebarCollapsed ? 'BMN' : ''"
               class="sidebar-nav-item flex items-center px-3 py-2 rounded-xl text-[13px] {{ $isBmn ? 'sidebar-active' : '' }}">
                <svg class="w-5 h-5 shrink-0 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>
                <span class="sidebar-text whitespace-nowrap overflow-hidden text-ellipsis">BMN</span>
            </a>

            <!-- Menu Group: Presensi -->
            <p class="sidebar-heading px-2.5 text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1 mt-4 whitespace-nowrap">Presensi</p>
            <div class="sidebar-divider my-1.5 border-t border-slate-800/80"></div>
            
            <a href="/qr"
               :title="sidebarCollapsed ? 'QR Code' : ''"
               class="sidebar-nav-item flex items-center px-3 py-2 rounded-xl text-[13px] {{ $isQr ? 'sidebar-active' : '' }}">
                <svg class="w-5 h-5 shrink-0 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                <span class="sidebar-text whitespace-nowrap overflow-hidden text-ellipsis">QR Code</span>
            </a>
            <a href="/report"
               :title="sidebarCollapsed ? 'Report Presensi' : ''"
               class="sidebar-nav-item flex items-center px-3 py-2 rounded-xl text-[13px] {{ $isReport ? 'sidebar-active' : '' }}">
                <svg class="w-5 h-5 shrink-0 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                <span class="sidebar-text whitespace-nowrap overflow-hidden text-ellipsis">Report Presensi</span>
            </a>
            <a href="/absen-kantor"
               :title="sidebarCollapsed ? 'Presensi' : ''"
               class="sidebar-nav-item flex items-center px-3 py-2 rounded-xl text-[13px] {{ $isAbsen ? 'sidebar-active' : '' }}">
                <svg class="w-5 h-5 shrink-0 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span class="sidebar-text whitespace-nowrap overflow-hidden text-ellipsis">Presensi</span>
            </a>
            
            <!-- Menu Group: Sistem & Integrasi -->
            @if(Auth::check() && Auth::user()->canAccessSimpati())
            @php
                $activeRouteName = request()->route() ? request()->route()->getName() : '';
                $simpatiOpen = str_starts_with((string) $activeRouteName, 'simpati');
                $canManageSimpatiAdmins = \App\Support\SimpatiRoles::canManageAdmins(Auth::user());
                $simpatiSub = [
                    ['route' => 'simpati.dashboard',     'label' => 'Dashboard'],
                    ['route' => 'simpati.petugas.index', 'label' => 'Kelola Petugas'],
                    ['route' => 'simpati.jadwal.index',  'label' => 'Kelola Jadwal'],
                ];
                if ($canManageSimpatiAdmins) {
                    $simpatiSub[] = ['route' => 'simpati.admins.index', 'label' => 'Kelola Admin'];
                }
                $simpatiSub[] = ['route' => 'simpati.index', 'label' => 'Integrasi SIMPATI'];
            @endphp
            <p class="sidebar-heading px-2.5 text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1 mt-4 whitespace-nowrap">Sistem & Integrasi</p>
            <div class="sidebar-divider my-1.5 border-t border-slate-800/80"></div>

            <div x-data="{ simpatiOpen: {{ $simpatiOpen ? 'true' : 'false' }} }">
                <button type="button"
                        @click="if (sidebarCollapsed) { toggleSidebar(); simpatiOpen = true; } else { simpatiOpen = !simpatiOpen; }"
                        :title="sidebarCollapsed ? 'SIMPATI' : ''"
                        class="sidebar-nav-item w-full flex items-center px-3 py-2 rounded-xl text-[13px] {{ $simpatiOpen ? 'sidebar-active' : '' }} focus:outline-none">
                    <svg class="w-5 h-5 shrink-0 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    <span class="sidebar-text flex-1 text-left whitespace-nowrap">SIMPATI</span>
                    <svg class="sidebar-chevron w-3.5 h-3.5 transition-transform duration-200 shrink-0" :class="simpatiOpen ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </button>
                <div x-show="simpatiOpen && !sidebarCollapsed" x-cloak class="sidebar-subnav mt-1 ml-3 space-y-0.5 border-l border-slate-800 pl-2.5">
                    @foreach($simpatiSub as $sub)
                        @php $isSubActive = $activeRouteName === $sub['route']; @endphp
                        <a href="{{ route($sub['route']) }}"
                           class="flex items-center px-2.5 py-1.5 rounded-lg text-xs {{ $isSubActive ? 'bg-blue-500/20 text-blue-300 font-bold' : 'text-slate-400 hover:bg-slate-800 hover:text-white font-medium' }}">
                            <span class="w-1.5 h-1.5 rounded-full mr-2.5 shrink-0 {{ $isSubActive ? 'bg-blue-400' : 'bg-slate-600' }}"></span>
                            <span class="whitespace-nowrap">{{ $sub['label'] }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
            @endif

        </div>

        <!-- Sidebar Footer: Copyright -->
        <div class="sidebar-footer shrink-0 py-3 px-3 border-t border-slate-800/80 bg-slate-950/60 select-none">
            <div class="sidebar-footer-full text-center">
                <p class="text-[11px] font-semibold text-slate-400 tracking-tight leading-snug">
                    &copy; {{ date('Y') }} BPS Sultra
                </p>
                <p class="text-[10px] text-slate-500 font-medium">SIKEREN App</p>
            </div>
            <div class="sidebar-footer-mini hidden text-center" title="&copy; {{ date('Y') }} BPS Sultra">
                <span class="text-xs font-bold text-slate-500">&copy;</span>
            </div>
        </div>
    </aside>

    <!-- =========================================================================
         MAIN CONTENT WRAPPER
    ========================================================================== -->
    <div class="flex-1 flex flex-col h-screen overflow-hidden min-w-0">
        
        <!-- =====================================================================
             TOP NAVBAR (Fixed Height 16 = 64px, Zero Layout Shift)
        ====================================================================== -->
        <header class="h-16 shrink-0 bg-white shadow-2xs border-b border-slate-200/80 flex items-center justify-between px-4 sm:px-6 z-20 select-none">
            
            <!-- Left: Toggle & Breadcrumb / Page Title (shrink-0 to prevent jumping) -->
            <div class="flex items-center gap-3 sm:gap-4 shrink-0">
                <!-- Toggle Button for Sidebar in Top Navbar -->
                <button @click="toggleSidebar()" type="button"
                        class="p-2 -ml-1 rounded-xl text-slate-500 hover:text-blue-600 hover:bg-slate-100 transition-colors focus:outline-none shrink-0"
                        :title="sidebarCollapsed ? 'Perbesar Sidebar' : 'Perkecil Sidebar'">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>

                <!-- Page Context (Clean single line, no giant duplicate H1) -->
                <div class="flex items-center gap-2">
                    <span class="text-xs font-semibold text-slate-400 hidden sm:inline">Sikeren</span>
                    <svg class="w-3 h-3 text-slate-300 hidden sm:inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    <span class="text-sm font-bold text-slate-800 tracking-tight">@yield('header_title', 'Dashboard')</span>
                </div>
            </div>

            <!-- Center Context Badge (Visible on lg+) -->
            <div class="hidden lg:flex items-center gap-2 px-3 py-1.5 rounded-full bg-slate-50 border border-slate-200/70 text-slate-600 text-xs font-medium shrink-0">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span class="font-semibold text-slate-700">BPS Provinsi Sulawesi Tenggara</span>
            </div>
            
            <!-- Right Utility Controls (shrink-0 to prevent layout wrap) -->
            <div class="flex items-center gap-3 sm:gap-4 shrink-0">
                
                <!-- Live Date Indicator (xl screens) -->
                <div class="hidden xl:flex items-center gap-1.5 text-xs text-slate-500 bg-slate-50 border border-slate-200/80 px-2.5 py-1.5 rounded-xl font-medium shrink-0">
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span>{{ \Carbon\Carbon::now()->translatedFormat('l, d M Y') }}</span>
                </div>

                <!-- =====================================================
                    NOTIFICATIONS DROPDOWN (Professional Container)
                ====================================================== -->
                <div class="relative shrink-0" x-data="{ notifOpen: false }">

                    {{-- Tombol Lonceng dengan Container Modern --}}
                    <button @click="notifOpen = !notifOpen"
                            type="button"
                            class="relative w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-slate-50 hover:bg-slate-100 border border-slate-200/90 text-slate-600 hover:text-blue-600 transition flex items-center justify-center focus:outline-none shrink-0"
                            title="Notifikasi">
                        
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9">
                            </path>
                        </svg>

                        {{-- Badge Notifikasi --}}
                        @if(isset($jumlah_notif) && $jumlah_notif > 0)
                            <span class="absolute -top-1 -right-1 flex h-4 w-4">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-4 w-4 bg-rose-500 text-[9px] text-white font-extrabold items-center justify-center shadow-xs">
                                    {{ $jumlah_notif > 9 ? '9+' : $jumlah_notif }}
                                </span>
                            </span>
                        @endif

                    </button>

                    {{-- Menu Pop-up Daftar Notifikasi --}}
                    <div x-show="notifOpen"
                         @click.away="notifOpen = false"
                         x-cloak
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                         x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                         class="absolute right-0 mt-3 w-80 sm:w-96 bg-white rounded-2xl shadow-2xl border border-slate-100 p-4 z-50">

                        {{-- Header Notifikasi --}}
                        <div class="flex items-center justify-between pb-3 border-b border-slate-100 px-1">
                            <div>
                                <h4 class="font-bold text-slate-800 text-sm">Notifikasi Masuk</h4>
                                <span class="text-[11px] font-semibold text-blue-600">
                                    {{ $jumlah_notif ?? 0 }} Belum Dibaca
                                </span>
                            </div>
                            @if(isset($jumlah_notif) && $jumlah_notif > 0)
                                <form action="{{ route('notification.markAllRead') }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="text-[11px] font-bold text-slate-500 hover:text-blue-600 transition">
                                        Tandai Semua Dibaca
                                    </button>
                                </form>
                            @endif
                        </div>

                        {{-- Isi Notifikasi --}}
                        <div class="mt-2 space-y-1.5 max-h-80 overflow-y-auto">

                            @forelse($notifications ?? [] as $notif)
                                @php
                                    $nData = is_array($notif->data) ? $notif->data : (json_decode($notif->data ?? '{}', true) ?: []);
                                    $nJudul = $nData['judul'] ?? ($notif->judul ?? 'Pemberitahuan');
                                    $nPesan = $nData['pesan'] ?? ($notif->pesan ?? '-');
                                    $nUrl   = route('notification.read', $notif->id);
                                    $isUnread = is_null($notif->read_at);
                                @endphp

                                <a href="{{ $nUrl }}"
                                   class="flex items-start gap-3 p-2.5 rounded-xl hover:bg-slate-50 transition-colors {{ $isUnread ? 'bg-blue-50/50' : '' }}">
                                    
                                    <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center shrink-0 mt-0.5">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>

                                    <div class="flex-1 min-w-0">
                                        <p class="text-xs font-bold text-slate-900 truncate">{{ $nJudul }}</p>
                                        <p class="text-xs text-slate-600 mt-0.5 line-clamp-2 leading-relaxed">{{ $nPesan }}</p>
                                        <span class="text-[10px] text-slate-400 mt-1 block font-medium">
                                            {{ \Carbon\Carbon::parse($notif->created_at)->diffForHumans() }}
                                        </span>
                                    </div>

                                    @if($isUnread)
                                        <span class="w-2 h-2 rounded-full bg-blue-500 mt-2 shrink-0" title="Belum dibaca"></span>
                                    @endif

                                </a>

                            @empty

                                <div class="py-8 text-center text-slate-400">
                                    <div class="w-10 h-10 rounded-xl bg-slate-50 flex items-center justify-center mx-auto mb-2 text-slate-300">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                        </svg>
                                    </div>
                                    <p class="text-xs font-medium">Tidak ada notifikasi baru</p>
                                </div>

                            @endforelse

                        </div>

                    </div>

                </div>
                
                <!-- Divider -->
                <div class="h-6 w-px bg-slate-200 shrink-0"></div>

                <!-- =====================================================
                    PROFESSIONAL USER PROFILE PILL & DROPDOWN MENU
                ====================================================== -->
                <div class="relative shrink-0" x-data="{ userMenuOpen: false }">
                    <button @click="userMenuOpen = !userMenuOpen"
                            type="button"
                            class="flex items-center gap-2.5 p-1 sm:p-1.5 sm:pr-2.5 rounded-xl hover:bg-slate-100/90 transition-all border border-transparent hover:border-slate-200 focus:outline-none group shrink-0">
                        
                        <!-- Avatar Inisial User (Jelas, Terbaca, Kontras BPS) -->
                        <div class="navbar-avatar-box w-8 h-8 sm:w-9 sm:h-9 rounded-xl text-sm shrink-0">
                            {{ strtoupper(substr(Auth::user()->nama_lengkap ?? (Auth::user()->username ?? 'U'), 0, 1)) }}
                        </div>

                        <!-- Info Pegawai: Nama & Jabatan -->
                        <div class="hidden sm:flex flex-col text-left leading-tight shrink-0">
                            <span class="text-xs font-bold text-slate-800 group-hover:text-blue-600 transition truncate max-w-[150px]">
                                {{ Auth::user()->nama_lengkap ?? 'User' }}
                            </span>
                            <span class="text-[10px] font-medium text-slate-500 mt-0.5 truncate max-w-[150px]">
                                {{ Auth::user()->role_label ?: 'Pegawai BPS' }}
                            </span>
                        </div>

                        <!-- Dropdown Chevron -->
                        <svg class="w-3.5 h-3.5 text-slate-400 group-hover:text-slate-600 transition-transform duration-200 shrink-0 ml-0.5"
                             :class="userMenuOpen ? 'rotate-180' : ''"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <!-- Profile Dropdown Menu Card -->
                    <div x-show="userMenuOpen"
                         @click.away="userMenuOpen = false"
                         x-cloak
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                         x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                         class="absolute right-0 mt-2.5 w-64 bg-white rounded-2xl shadow-xl border border-slate-100 p-2 z-50">
                        
                        <!-- Header Detail Akun -->
                        <div class="p-3 border-b border-slate-100 bg-slate-50/70 rounded-xl mb-1 flex items-center gap-3">
                            <div class="navbar-avatar-box w-10 h-10 rounded-xl text-base shrink-0">
                                {{ strtoupper(substr(Auth::user()->nama_lengkap ?? (Auth::user()->username ?? 'U'), 0, 1)) }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-xs font-bold text-slate-800 truncate">{{ Auth::user()->nama_lengkap }}</p>
                                <p class="text-[11px] text-slate-500 truncate mt-0.5">{{ Auth::user()->email ?: (Auth::user()->username . '@bps.go.id') }}</p>
                                <div class="flex items-center gap-1.5 mt-1.5 pt-1 border-t border-slate-200/60 text-[10px] text-slate-500 flex-wrap">
                                    <span class="px-1.5 py-0.5 rounded-md bg-blue-50 text-blue-700 font-bold border border-blue-100">
                                        {{ Auth::user()->role_label ?: 'Pegawai BPS' }}
                                    </span>
                                    @if(Auth::user()->niplama)
                                        <span class="font-mono text-slate-400">NIP: {{ Auth::user()->niplama }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Menu Pintasan Cepat -->
                        <div class="py-1 space-y-0.5">
                            <a href="/tugas-saya" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-medium text-slate-700 hover:bg-slate-50 hover:text-blue-600 transition">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                <span>Tugas & Profil Saya</span>
                            </a>
                            <a href="/absen-kantor" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-medium text-slate-700 hover:bg-slate-50 hover:text-blue-600 transition">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>Riwayat Presensi</span>
                            </a>
                            @if(Auth::check() && Auth::user()->canAccessSimpati())
                            <a href="{{ route('simpati.dashboard') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-medium text-slate-700 hover:bg-slate-50 hover:text-blue-600 transition">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <span>Dashboard SIMPATI</span>
                            </a>
                            @endif
                        </div>

                        <!-- Tombol Logout -->
                        <div class="pt-1.5 border-t border-slate-100 mt-1">
                            <a href="{{ route('actionlogout') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold text-rose-600 hover:bg-rose-50 transition">
                                <div class="w-6 h-6 rounded-lg bg-rose-50 flex items-center justify-center text-rose-500 shrink-0">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                </div>
                                <span class="font-bold">Keluar (Logout)</span>
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </header>

        <!-- Main Content (Padding responsif dan luas) -->
        <main id="main-content" class="flex-1 overflow-x-hidden overflow-y-auto bg-slate-50 p-4 sm:p-6 lg:p-8">
            <div class="max-w-7xl mx-auto">
                @yield('content')
            </div>
        </main>
    </div>

    @stack('scripts')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>
