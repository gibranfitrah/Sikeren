@extends('layouts.app')

@section('title', 'Dashboard - Sikeren')
@section('header_title', 'Dashboard')

@push('styles')
<!-- FullCalendar CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css">
<style>
    /* FullCalendar Customizations */
    .fc .fc-toolbar-title {
        font-size: 1.15rem !important;
        font-weight: 700 !important;
        color: #0f172a !important;
    }
    .fc .fc-button-primary {
        background-color: #2563eb !important;
        border-color: #2563eb !important;
        border-radius: 0.75rem !important;
        font-size: 0.75rem !important;
        font-weight: 600 !important;
        padding: 0.45rem 0.9rem !important;
        text-transform: capitalize !important;
    }
    .fc .fc-button-primary:hover {
        background-color: #1d4ed8 !important;
        border-color: #1d4ed8 !important;
    }
    .fc .fc-button-primary:disabled {
        background-color: #94a3b8 !important;
        border-color: #94a3b8 !important;
    }
    .fc .fc-daygrid-day-number {
        font-size: 0.75rem !important;
        font-weight: 600 !important;
        color: #475569 !important;
        padding: 4px 6px !important;
    }
    .fc .fc-event {
        border-radius: 0.375rem !important;
        padding: 2px 6px !important;
        font-size: 0.72rem !important;
        font-weight: 500 !important;
        cursor: pointer !important;
    }
    .fc-theme-standard td, .fc-theme-standard th {
        border-color: #e2e8f0 !important;
    }
    .fc-col-header-cell-cushion {
        font-size: 0.75rem !important;
        font-weight: 700 !important;
        color: #475569 !important;
        text-transform: uppercase !important;
        padding: 8px 0 !important;
    }

    /* Dashboard Layout & Spacing Safeguards */
    .dashboard-container {
        display: flex;
        flex-direction: column;
        gap: 28px;
        padding-bottom: 48px;
        max-width: 1280px;
        margin: 0 auto;
    }

    .dash-header-card {
        background-color: #ffffff;
        border-radius: 20px;
        border: 1px solid #e2e8f0;
        padding: 24px 28px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        display: flex;
        flex-direction: row;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
    }

    .stat-cards-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 24px;
    }

    .stat-card-box {
        background-color: #ffffff;
        border-radius: 20px;
        border: 1px solid #e2e8f0;
        padding: 24px 28px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        display: flex;
        align-items: center;
        justify-content: space-between;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .stat-card-box:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.06), 0 4px 6px -2px rgba(0, 0, 0, 0.04);
    }

    .calendar-section-card {
        background-color: #ffffff;
        border-radius: 20px;
        border: 1px solid #e2e8f0;
        padding: 28px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
    }

    .tables-two-cols {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 24px;
    }

    .table-section-box {
        background-color: #ffffff;
        border-radius: 20px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }

    @media (max-width: 768px) {
        .stat-cards-grid {
            grid-template-columns: 1fr;
            gap: 16px;
        }
        .tables-two-cols {
            grid-template-columns: 1fr;
            gap: 20px;
        }
    }
</style>
@endpush

@section('content')
<div class="dashboard-container">

    {{-- 1. HEADER & ACTION BUTTONS --}}
    <div class="dash-header-card">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-md text-[11px] font-bold uppercase tracking-wider bg-blue-50 text-blue-700 border border-blue-100">
                    Sistem Kegiatan Terencana
                </span>
                <span class="text-xs text-gray-400">•</span>
                <span class="text-xs text-gray-500 font-medium">BPS Kabupaten/Kota</span>
            </div>
            <h2 class="text-xl sm:text-2xl font-black text-gray-900 mt-1">Dashboard Utama & Kalender Kerja</h2>
            <p class="text-xs sm:text-sm text-gray-500 mt-0.5">Pantau kalender jadwal, rincian kegiatan personal, dan aktivitas tim kerja secara real-time.</p>
        </div>

        <div class="flex items-center gap-2.5 flex-wrap sm:flex-nowrap">
            <a href="{{ route('ketua-tim.create') }}" 
               class="inline-flex items-center justify-center px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-xs hover:shadow transition-all duration-200">
                <svg class="w-4 h-4 mr-1.5 text-white shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>+ Buat Kegiatan</span>
            </a>
            <a href="{{ url('/rapat') }}" 
               class="inline-flex items-center justify-center px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-xs hover:shadow transition-all duration-200">
                <svg class="w-4 h-4 mr-1.5 text-white shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <span>+ Buat Rapat</span>
            </a>
            <a href="{{ route('time-schedule.index') }}" 
               class="inline-flex items-center justify-center px-4 py-2.5 bg-white hover:bg-gray-50 border border-gray-200 text-gray-700 text-xs font-bold rounded-xl shadow-xs hover:border-gray-300 transition-all">
                <svg class="w-4 h-4 mr-1.5 text-gray-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <span>Time Schedule</span>
            </a>
        </div>
    </div>

    {{-- 2. STATS CARDS: 2 DI ATAS & 2 DI BAWAH (TERPISAH & BERSPASI RAPI) --}}
    <div class="stat-cards-grid">
        <!-- Card 1: Total Kegiatan -->
        <div class="stat-card-box">
            <div class="space-y-1.5">
                <span class="text-xs font-bold uppercase tracking-wider text-gray-400">Total Kegiatan</span>
                <h3 class="text-3xl sm:text-4xl font-black text-gray-900">{{ $jumlah_kegiatan }}</h3>
                <p class="text-xs text-blue-600 font-semibold bg-blue-50 px-2.5 py-0.5 rounded-lg inline-block">
                    Semua Kegiatan & Rapat
                </p>
            </div>
            <div class="w-14 h-14 rounded-2xl bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-600 shrink-0">
                <svg class="w-7 h-7 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
            </div>
        </div>

        <!-- Card 2: Kegiatan Saya -->
        <div class="stat-card-box">
            <div class="space-y-1.5">
                <span class="text-xs font-bold uppercase tracking-wider text-gray-400">Kegiatan Saya</span>
                <h3 class="text-3xl sm:text-4xl font-black text-indigo-600">{{ $jumlah_kegiatan_saya }}</h3>
                <p class="text-xs text-indigo-600 font-semibold bg-indigo-50 px-2.5 py-0.5 rounded-lg inline-block">
                    Personal & Penugasan Anda
                </p>
            </div>
            <div class="w-14 h-14 rounded-2xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 shrink-0">
                <svg class="w-7 h-7 text-indigo-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
        </div>

        <!-- Card 3: Sedang Berjalan -->
        <div class="stat-card-box">
            <div class="space-y-1.5">
                <span class="text-xs font-bold uppercase tracking-wider text-gray-400">Sedang Berjalan</span>
                <h3 class="text-3xl sm:text-4xl font-black text-amber-600">{{ $jumlah_kegiatan_belum }}</h3>
                <p class="text-xs text-amber-700 font-semibold bg-amber-50 px-2.5 py-0.5 rounded-lg inline-block">
                    Dalam Proses Pelaksanaan
                </p>
            </div>
            <div class="w-14 h-14 rounded-2xl bg-amber-50 border border-amber-100 flex items-center justify-center text-amber-600 shrink-0">
                <svg class="w-7 h-7 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>

        <!-- Card 4: Kegiatan Selesai -->
        <div class="stat-card-box">
            <div class="space-y-1.5">
                <span class="text-xs font-bold uppercase tracking-wider text-gray-400">Kegiatan Selesai</span>
                <h3 class="text-3xl sm:text-4xl font-black text-emerald-600">{{ $jumlah_kegiatan_selesai }}</h3>
                <p class="text-xs text-emerald-700 font-semibold bg-emerald-50 px-2.5 py-0.5 rounded-lg inline-block">
                    Tuntas & Notulen Siap
                </p>
            </div>
            <div class="w-14 h-14 rounded-2xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600 shrink-0">
                <svg class="w-7 h-7 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
    </div>

    {{-- 2.5 STATUS KETERSEDIAAN RUANGAN & ZOOM HARI INI --}}
    <div class="bg-white rounded-2xl border border-gray-200 p-6 sm:p-7 shadow-xs space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-gray-100">
            <div class="flex items-center gap-3.5">
                <div class="w-10 h-10 rounded-2xl bg-blue-50 border border-blue-100 text-blue-600 flex items-center justify-center font-bold text-base shrink-0">
                    🏛️
                </div>
                <div>
                    <h3 class="text-base sm:text-lg font-bold text-gray-900">Ketersediaan Ruangan & Zoom Hari Ini</h3>
                    <p class="text-xs text-gray-400">Pantau ruangan rapat yang kosong/belum dibooking serta status akun Zoom secara real-time.</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('booking-ruangan.index') }}" 
                   class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-xs transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Lihat Time Schedule & Booking Ruangan &rarr;
                </a>
            </div>
        </div>

        {{-- Grid 3 Ruangan Fisik & 2 Akun Zoom --}}
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-3.5">
            {{-- 3 Ruangan Fisik --}}
            @foreach($dashboardVenuesStatus as $vId => $vStat)
                @php
                    $ven = $vStat['venue'];
                    $isUse = $vStat['is_in_use'];
                    $activeNow = $vStat['active_now'];
                    $nextBook = $vStat['next_booking'];
                    $tot = $vStat['total_today'];
                @endphp
                <div class="p-4 rounded-xl border flex flex-col justify-between transition {{ $isUse ? 'bg-rose-50/40 border-rose-200' : ($tot > 0 ? 'bg-amber-50/30 border-amber-200' : 'bg-gray-50/50 hover:bg-white border-gray-200') }}">
                    <div>
                        <div class="flex items-start justify-between gap-1 mb-2">
                            <h4 class="text-xs font-bold text-gray-900 truncate">{{ $ven->name }}</h4>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold shrink-0 {{ $isUse ? 'bg-rose-100 text-rose-700' : ($tot > 0 ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700') }}">
                                {{ $isUse ? 'Terpakai' : ($tot > 0 ? 'Ada Jadwal' : 'Kosong') }}
                            </span>
                        </div>
                        <p class="text-[10px] text-gray-400 mb-2">Kapasitas: {{ $ven->capacity }} Orang</p>

                        @if($isUse && $activeNow)
                            <div class="p-2 bg-white rounded-lg border border-rose-200 text-[10px] space-y-0.5">
                                <p class="font-bold text-rose-900 truncate">{{ $activeNow->nama_acara }}</p>
                                <p class="text-rose-700">{{ substr($activeNow->start_time, 0, 5) }} - {{ substr($activeNow->end_time, 0, 5) }}</p>
                            </div>
                        @elseif($nextBook)
                            <div class="text-[10px] text-amber-800 font-medium">
                                Jadwal berikutnya: {{ substr($nextBook->start_time, 0, 5) }}
                            </div>
                        @else
                            <p class="text-[11px] text-emerald-600 font-semibold">Tersedia sepanjang hari</p>
                        @endif
                    </div>

                    <div class="mt-3 pt-2.5 border-t border-gray-100 flex items-center justify-between">
                        <span class="text-[10px] text-gray-400">{{ $tot }} Acara</span>
                        <a href="{{ route('booking-ruangan.index', ['date' => $today]) }}" class="text-[10px] font-bold text-blue-600 hover:underline">
                            + Booking
                        </a>
                    </div>
                </div>
            @endforeach

            {{-- 2 Akun Zoom --}}
            @foreach($dashboardZoomStatus as $zKey => $zStat)
                @php
                    $zInfo = $zStat['info'];
                    $isUse = $zStat['is_in_use'];
                    $activeNow = $zStat['active_now'];
                @endphp
                <div class="p-4 rounded-xl border flex flex-col justify-between transition {{ $isUse ? 'bg-rose-50/40 border-rose-200' : 'bg-indigo-50/20 hover:bg-white border-indigo-100' }}">
                    <div>
                        <div class="flex items-start justify-between gap-1 mb-2">
                            <h4 class="text-xs font-bold text-indigo-900 truncate">{{ $zInfo['name'] }}</h4>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold shrink-0 {{ $isUse ? 'bg-rose-100 text-rose-700' : 'bg-emerald-100 text-emerald-700' }}">
                                {{ $isUse ? 'Digunakan' : 'Tersedia' }}
                            </span>
                        </div>
                        <p class="text-[10px] text-gray-400 mb-1">ID: {{ $zInfo['meeting_id'] }}</p>

                        @if($isUse && $activeNow)
                            <div class="p-2 bg-white rounded-lg border border-rose-200 text-[10px]">
                                <p class="font-bold text-rose-900 truncate">{{ $activeNow->nama_acara }}</p>
                                <p class="text-rose-700">{{ substr($activeNow->start_time, 0, 5) }} - {{ substr($activeNow->end_time, 0, 5) }}</p>
                            </div>
                        @else
                            <p class="text-[11px] text-indigo-700 font-semibold">Siap digunakan rapat online</p>
                        @endif
                    </div>

                    <div class="mt-3 pt-2.5 border-t border-gray-100 flex items-center justify-between">
                        <span class="text-[10px] text-gray-400">Zoom Daring</span>
                        <a href="{{ route('booking-ruangan.index', ['date' => $today]) }}" class="text-[10px] font-bold text-indigo-600 hover:underline">
                            + Pakai Zoom
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- 3. KALENDER DASHBOARD (BERSPASI KE BAWAH SENDIRI) --}}
    <div class="calendar-section-card">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6 pb-4 border-b border-gray-100">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-blue-50 border border-blue-100 text-blue-600 flex items-center justify-center shrink-0 font-bold">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base sm:text-lg font-bold text-gray-900">Kalender Agenda & Kegiatan BPS</h3>
                    <p class="text-xs text-gray-400">Klik pada agenda untuk membuka detail atau approval persetujuan rapat.</p>
                </div>
            </div>

            <div class="flex items-center gap-4 text-xs font-semibold">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-md bg-indigo-600 inline-block"></span>
                    <span class="text-gray-700">Rapat</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-md bg-sky-500 inline-block"></span>
                    <span class="text-gray-700">Kegiatan</span>
                </div>
            </div>
        </div>

        <div id="dashboardCalendar" class="min-h-[520px]"></div>
    </div>

    {{-- 4. KEGIATAN SAYA & SUB KEGIATAN & DEADLINE (2 KOTAK TERPISAH BERSPASI) --}}
    <div class="tables-two-cols">
        
        <!-- Kotak 1: Kegiatan Saya -->
        <div class="table-section-box">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-blue-600"></span>
                    <div>
                        <h3 class="font-bold text-gray-900 text-sm sm:text-base">Kegiatan Saya</h3>
                        <p class="text-xs text-gray-400">Kegiatan yang menugaskan Anda (PJ, Pemimpin, Peserta)</p>
                    </div>
                </div>
                <a href="{{ route('kegiatan-saya.index') }}" class="text-xs font-bold text-blue-600 hover:text-blue-800 transition">
                    Lihat Semua &rarr;
                </a>
            </div>
            <div class="overflow-x-auto max-h-[380px] overflow-y-auto">
                <table class="min-w-full divide-y divide-gray-100 text-xs text-left">
                    <thead class="bg-gray-50/80 sticky top-0">
                        <tr>
                            <th class="px-6 py-3.5 font-bold text-gray-500 uppercase tracking-wider">Nama Kegiatan</th>
                            <th class="px-6 py-3.5 font-bold text-gray-500 uppercase tracking-wider">Jadwal</th>
                            <th class="px-6 py-3.5 font-bold text-gray-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse($kegiatansSaya->take(6) as $keg)
                        <tr class="hover:bg-blue-50/40 transition">
                            <td class="px-6 py-4">
                                <a href="{{ url('daftarkegiatan/' . $keg->id) }}" class="font-bold text-gray-900 hover:text-blue-600 truncate block max-w-[200px]">
                                    {{ $keg->text }}
                                </a>
                                <span class="text-[11px] text-gray-400 font-medium mt-0.5 block">{{ $keg->jenis }} • PJ: {{ $keg->penanggung_jawab ?: ($keg->pemimpin ?: '-') }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-600 font-medium">
                                {{ $keg->start_date }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($keg->status == 'Selesai' || $keg->notulen_selesai == 1)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">SELESAI</span>
                                @elseif($keg->status == 'Disetujui' || $keg->setuju_rapat == 1)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800">DISETUJUI</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">BERJALAN</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="px-6 py-10 text-center text-gray-400 text-xs">
                                Belum ada kegiatan yang melibatkan akun Anda.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Kotak 2: Sub Kegiatan & Deadline -->
        <div class="table-section-box">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                    <div>
                        <h3 class="font-bold text-gray-900 text-sm sm:text-base">Sub Kegiatan & Deadline</h3>
                        <p class="text-xs text-gray-400">Penugasan sub kegiatan spesifik Anda</p>
                    </div>
                </div>
                <a href="{{ route('sub-kegiatan.index') }}" class="text-xs font-bold text-amber-700 hover:text-amber-900 transition">
                    Kelola Sub &rarr;
                </a>
            </div>
            <div class="overflow-x-auto max-h-[380px] overflow-y-auto">
                <table class="min-w-full divide-y divide-gray-100 text-xs text-left">
                    <thead class="bg-gray-50/80 sticky top-0">
                        <tr>
                            <th class="px-6 py-3.5 font-bold text-gray-500 uppercase tracking-wider">Sub Kegiatan</th>
                            <th class="px-6 py-3.5 font-bold text-gray-500 uppercase tracking-wider">Deadline</th>
                            <th class="px-6 py-3.5 font-bold text-gray-500 uppercase tracking-wider">Progress</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse($subKegiatansSaya->take(6) as $sub)
                        @php $dl = $sub->deadline_status; @endphp
                        <tr class="hover:bg-amber-50/40 transition">
                            <td class="px-6 py-4">
                                <span class="font-bold text-gray-900 block truncate max-w-[190px]">{{ $sub->nama_sub }}</span>
                                <span class="text-[11px] text-gray-400 truncate block mt-0.5">{{ $sub->task ? $sub->task->text : '-' }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[10px] font-bold border {{ $dl['class'] }}">
                                    <span>{{ $dl['label'] }}</span>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <div class="w-16 bg-gray-200 rounded-full h-1.5 overflow-hidden">
                                        <div class="h-1.5 rounded-full {{ $sub->progress >= 100 ? 'bg-emerald-500' : 'bg-blue-600' }}" style="width: {{ $sub->progress }}%"></div>
                                    </div>
                                    <span class="font-bold text-gray-700 text-[10px]">{{ $sub->progress }}%</span>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="px-6 py-10 text-center text-gray-400 text-xs">
                                Tidak ada sub kegiatan yang sedang ditugaskan.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</div>
@endsection

@push('scripts')
<!-- FullCalendar JS -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/id.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    var calendarEl = document.getElementById('dashboardCalendar');
    if (!calendarEl) return;

    var calendar = new FullCalendar.Calendar(calendarEl, {
        locale: 'id',
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,listMonth'
        },
        buttonText: {
            today: 'Hari Ini',
            month: 'Bulan',
            week: 'Minggu',
            list: 'Daftar'
        },
        events: {!! json_encode($calendarEvents) !!},
        eventClick: function(info) {
            if (info.event.url) {
                info.jsEvent.preventDefault();
                window.location.href = info.event.url;
            }
        },
        eventMouseEnter: function(info) {
            var props = info.event.extendedProps;
            var tip = info.event.title + '\nPJ: ' + (props.pj || '-') + '\nTim: ' + (props.tim || '-') + '\nStatus: ' + (props.status || '-');
            info.el.setAttribute('title', tip);
        }
    });

    calendar.render();
});
</script>
@endpush