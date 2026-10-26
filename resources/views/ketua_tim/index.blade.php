@extends('layouts.app')

@section('title', 'Dashboard Ketua Tim - Sikeren')
@section('header_title', 'Dashboard Ketua Tim')

@section('content')

@php
    $currentUser = $currentUser ?? Auth::user();
    $isPjUser = $currentUser && ($currentUser->isKetuaTimOrPj() || $currentUser->isAdmin());
@endphp

<style>
    .meta-chip {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 11px;
        color: #475569;
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        padding: 3px 8px;
        border-radius: 6px;
        white-space: nowrap;
    }
</style>

<div class="space-y-6 pb-12 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="{
    openPstModal: false,
    pstSearch: '',
    expandedSub: null,
}">

    {{-- =====================================================
        HEADER SECTION & ACTION BUTTONS
    ====================================================== --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-gray-200/80 shadow-xs">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-md text-[11px] font-bold uppercase tracking-wider bg-blue-50 text-blue-700 border border-blue-100">
                    Pengelolaan Tim
                </span>
                <span class="text-xs text-gray-400">•</span>
                <span class="text-xs text-gray-500 font-medium">BPS Provinsi Sulawesi Tenggara</span>
            </div>
            <h2 class="text-xl font-bold text-gray-900 mt-1">Dashboard Ketua Tim - Kelola Kegiatan</h2>
            <p class="text-xs text-gray-500 mt-0.5">Pantau alur penugasan, jadwal, dan koordinasi kegiatan tim secara terpadu.</p>
        </div>
        <div class="flex items-center gap-2.5 flex-wrap sm:flex-nowrap">
            <a href="{{ route('agenda.downloadWord') }}" class="inline-flex items-center justify-center px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-xs hover:shadow transition-all duration-150">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Download Agenda (.docx)
            </a>

            <a href="{{ route('sub-kegiatan.index') }}" class="inline-flex items-center justify-center px-4 py-2.5 bg-amber-50 hover:bg-amber-100 border border-amber-200 text-amber-800 text-xs font-bold rounded-xl shadow-xs transition-all duration-150">
                <svg class="w-4 h-4 mr-1.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                Sub Kegiatan
            </a>

            <a href="{{ route('ketua-tim.create') }}" class="inline-flex items-center justify-center px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-xs hover:shadow transition-all duration-150">
                <svg class="w-4 h-4 mr-1.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Buat Kegiatan Baru
            </a>
        </div>
    </div>

    {{-- =====================================================
        ALERT NOTIFICATIONS
    ====================================================== --}}
    @if (session('success'))
    <div class="bg-emerald-50 border border-emerald-200 p-4 rounded-xl shadow-xs flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div>
                <h4 class="text-xs font-bold text-emerald-900">Operasi Berhasil</h4>
                <p class="text-xs text-emerald-700 mt-0.5">{{ session('success') }}</p>
            </div>
        </div>
    </div>
    @endif

    {{-- =====================================================
        KPI STATS SUMMARY CARDS (SERAGAM DENGAN KELOLA KEGIATAN)
    ====================================================== --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-2xl border border-gray-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-gray-500">Total Kegiatan</p>
                <h3 class="text-2xl font-bold text-gray-900 mt-1">{{ $totalKegiatan ?? ($totalAgenda ?? 0) }}</h3>
                <span class="text-[10px] font-semibold text-blue-600 bg-blue-50 px-2 py-0.5 rounded-md mt-1 inline-block">Semua Agenda</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-gray-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-gray-500">Sedang Berjalan</p>
                <h3 class="text-2xl font-bold text-sky-600 mt-1">{{ $berjalan ?? 0 }}</h3>
                <span class="text-[10px] font-semibold text-sky-700 bg-sky-50 px-2 py-0.5 rounded-md mt-1 inline-block">Proses</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-gray-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-gray-500">Menunggu Approve</p>
                <h3 class="text-2xl font-bold text-amber-600 mt-1">{{ $menungguApprove ?? 0 }}</h3>
                <span class="text-[10px] font-semibold text-amber-700 bg-amber-50 px-2 py-0.5 rounded-md mt-1 inline-block">Persetujuan</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-gray-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-gray-500">Kegiatan Selesai</p>
                <h3 class="text-2xl font-bold text-emerald-600 mt-1">{{ $selesai ?? ($agendaSelesai ?? 0) }}</h3>
                <span class="text-[10px] font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md mt-1 inline-block">Tuntas</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
        </div>
    </div>

    {{-- =====================================================
        TABEL DAFTAR KEGIATAN KETUA TIM
    ====================================================== --}}
    <div class="bg-white rounded-2xl border border-gray-200/80 shadow-xs overflow-hidden">
        {{-- Card Header & Live Search --}}
        <div class="p-6 border-b border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h3 class="text-base font-bold text-gray-900">Daftar Kegiatan</h3>
                <p class="text-xs text-gray-500 mt-0.5">Daftar kegiatan yang dibuat dan dikoordinasikan oleh Ketua Tim</p>
            </div>
            <div class="relative w-full md:w-72">
                <input type="text"
                       id="tableSearchInput"
                       onkeyup="filterTableKetuaTim()"
                       placeholder="Cari kegiatan, tim, PJ..."
                       class="w-full pl-9 pr-4 py-2 text-xs rounded-xl border border-gray-200 bg-gray-50 focus:bg-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all">
                <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-left" id="kegiatanTable">
                <thead class="bg-gray-50/80 text-[11px] font-bold text-gray-600 uppercase tracking-wider">
                    <tr>
                        <th scope="col" class="px-6 py-4">Nama Kegiatan & Tim</th>
                        <th scope="col" class="px-6 py-4">Rentang Waktu</th>
                        <th scope="col" class="px-6 py-4">Wilayah / Lokasi</th>
                        <th scope="col" class="px-6 py-4">PJ & Sub Kegiatan</th>
                        <th scope="col" class="px-6 py-4">Status</th>
                        <th scope="col" class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100 text-xs">
                    {{-- ==============================================================
                        BARIS TUNGGAL KHUSUS PELAYANAN STATISTIK TERPADU (PST)
                    =============================================================== --}}
                    @if(isset($pstTasks) && $pstTasks->isNotEmpty())
                    @php
                        $pstFirst = $pstTasks->sortBy('start_date')->first();
                        $pstLast = $pstTasks->sortByDesc('start_date')->first();
                        $pstEarliest = $pstFirst ? \Carbon\Carbon::parse($pstFirst->start_date)->translatedFormat('d M Y') : '01 Sep 2026';
                        $pstLatest = $pstLast ? \Carbon\Carbon::parse($pstLast->start_date)->translatedFormat('d M Y') : '30 Sep 2026';
                    @endphp
                    <tr class="hover:bg-blue-50/50 transition-colors kegiatan-row bg-blue-50/20 border-l-4 border-l-blue-600">
                        <td class="px-6 py-4">
                            <div class="space-y-1.5 max-w-md">
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-800 border border-blue-200">
                                        Diseminasi & Layanan
                                    </span>
                                </div>
                                <button type="button" 
                                        @click="openPstModal = true" 
                                        class="font-extrabold text-blue-800 hover:text-blue-950 text-sm block text-left hover:underline cursor-pointer group flex items-center gap-1.5">
                                    <span>Pelayanan Statistik Terpadu (PST)</span>
                                </button>
                            </div>
                        </td>

                        <td class="px-6 py-4 whitespace-nowrap text-gray-600">
                            <div class="font-semibold text-gray-800">
                                {{ $pstEarliest }}
                            </div>
                            <div class="text-[11px] text-gray-500 mt-0.5">
                                s.d {{ $pstLatest }}
                            </div>
                        </td>

                        <td class="px-6 py-4">
                            <span class="meta-chip">Ruang PST BPS Prov. Sultra</span>
                        </td>

                        <td class="px-6 py-4">
                            <div class="space-y-2">
                                <div class="font-medium text-gray-800">
                                    PJ: <span class="font-bold">Ketua Tim PST</span>
                                </div>
                                <button type="button" 
                                        @click="openPstModal = true"
                                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-bold transition bg-blue-100 text-blue-700 hover:bg-blue-200 border border-blue-300 cursor-pointer">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                                    </svg>
                                    <span>Buka Daftar PST ({{ $pstTasks->count() }})</span>
                                </button>
                            </div>
                        </td>

                        <td class="px-6 py-4">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                <span>Operasional Berjalan</span>
                            </span>
                        </td>

                        <td class="px-6 py-4 whitespace-nowrap text-right text-xs">
                            <div class="flex items-center justify-end gap-1.5">
                                <a href="{{ url('daftarkegiatan/' . ($pstFirst ? $pstFirst->id : '')) }}" 
                                   class="px-3.5 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 font-bold rounded-xl border border-blue-200 text-xs transition inline-flex items-center gap-1 shadow-2xs"
                                   title="Buka Detail PST">
                                    Detail
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endif

                    {{-- ==============================================================
                        DAFTAR KEGIATAN REGULER (BUKAN PST)
                    =============================================================== --}}
                    @php
                        $listToRender = $kegiatans2 ?? ($nonPstTasks ?? collect());
                    @endphp

                    @forelse($listToRender as $item)
                    @php
                        $subs = $item->subKegiatans;
                        $subCount = count($subs);
                        $wilayahList = $item->wilayah_list ?? [];
                        
                        $st = strtolower(trim($item->status ?? ''));
                        $isDone = ($st === 'selesai' || (!empty($item->notulen) && ($item->notulen_selesai ?? 0) == 1));
                        $isDelayed = ($st === 'tertunda');
                        $isInactive = ($st === 'tidak berjalan' || $st === 'dibatalkan');
                        $isApproved = ($item->setuju_rapat == 1 || $st === 'disetujui' || $st === 'sedang berjalan' || $isDone || $isDelayed || $isInactive);
                        $isRejected = ($item->setuju_rapat == 3 || $st === 'ditolak');
                        $isPending  = ($item->setuju_rapat == 0 && !$isApproved && !$isRejected && in_array($st, ['menunggu', 'menunggu persetujuan', 'belum', '']));

                        // Untuk kegiatan yang lewat tanggal/waktu, otomatis Selesai
                        $isJanSep2026 = (!empty($item->start_date) && $item->start_date <= '2026-09-30');
                        if ($isJanSep2026 && $item->setuju_rapat != 3 && $st !== 'ditolak') {
                            $isDone = true;
                        }
                    @endphp
                    <tr class="hover:bg-slate-50/60 transition-colors kegiatan-row">
                        <td class="px-6 py-4">
                            <div class="space-y-1 max-w-md">
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-100">
                                         {{ $item->tim ?: 'Umum' }}
                                    </span>
                                    <span class="text-xs text-gray-400 font-mono">#{{ $item->id }}</span>
                                </div>
                                <a href="{{ url('daftarkegiatan/' . $item->id) }}" class="font-bold text-gray-900 hover:text-blue-600 text-sm block">
                                     {{ $item->agenda ?: ($item->text ?: 'Kegiatan') }}
                                </a>
                            </div>
                        </td>

                        <td class="px-6 py-4 whitespace-nowrap text-gray-600">
                            <div class="font-semibold text-gray-800">
                                {{ $item->start_date ? \Carbon\Carbon::parse($item->start_date)->translatedFormat('d M Y') : '-' }}
                            </div>
                            @if($item->date_akhir && $item->date_akhir != $item->start_date)
                                <div class="text-[11px] text-gray-500 mt-0.5">
                                    s.d {{ \Carbon\Carbon::parse($item->date_akhir)->translatedFormat('d M Y') }}
                                </div>
                            @endif
                        </td>

                        <td class="px-6 py-4">
                            @if(!empty($wilayahList))
                                <div class="flex flex-wrap gap-1 max-w-[220px]">
                                    @foreach(array_slice($wilayahList, 0, 3) as $w)
                                        <span class="meta-chip">{{ $w }}</span>
                                    @endforeach
                                    @if(count($wilayahList) > 3)
                                        <span class="text-[10px] text-gray-400 font-bold">+{{ count($wilayahList) - 3 }} lainnya</span>
                                    @endif
                                </div>
                            @else
                                <span class="meta-chip" title="{{ $item->tempat ?: '-' }}">{{ $item->tempat ?: '-' }}</span>
                            @endif
                        </td>

                        {{-- PJ & Dropdown Sub Kegiatan --}}
                        <td class="px-6 py-4">
                            <div class="space-y-2">
                                <div class="font-medium text-gray-800">
                                    PJ: <span class="font-bold">{{ $item->penanggung_jawab ?: ($item->pemimpin ?: '-') }}</span>
                                </div>
                                
                                <button type="button" 
                                        @click="expandedSub = (expandedSub === {{ $item->id }} ? null : {{ $item->id }})"
                                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-bold transition {{ $subCount > 0 ? 'bg-indigo-50 text-indigo-700 hover:bg-indigo-100 border border-indigo-200' : 'bg-gray-100 text-gray-500 hover:bg-gray-200' }}">
                                    <svg class="w-3.5 h-3.5 transition-transform" :class="expandedSub === {{ $item->id }} ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                    <span>Sub Kegiatan ({{ $subCount }})</span>
                                </button>
                            </div>
                        </td>

                        {{-- Status Pelaksanaan --}}
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($isDone)
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                    <span>Selesai</span>
                                </span>
                            @elseif($isDelayed)
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                    <span>Tertunda</span>
                                </span>
                            @elseif($isInactive)
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-bold bg-slate-100 text-slate-700 border border-slate-300">
                                    <span class="w-2 h-2 rounded-full bg-slate-500"></span>
                                    <span>Tidak Berjalan</span>
                                </span>
                            @elseif($isApproved)
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                    <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                                    <span>Sedang Berjalan</span>
                                </span>
                            @elseif($isRejected)
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-bold bg-red-50 text-red-700 border border-red-200">
                                    <span class="w-2 h-2 rounded-full bg-red-500"></span>
                                    <span>Ditolak</span>
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                                    <span>Menunggu</span>
                                </span>
                            @endif
                        </td>

                        {{-- Aksi --}}
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                {{-- Detail --}}
                                <a href="{{ url('daftarkegiatan/' . $item->id) }}" 
                                   class="p-2 text-slate-500 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition border border-transparent hover:border-blue-200" 
                                   title="Lihat Detail Kegiatan">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>

                                {{-- Edit --}}
                                <a href="{{ route('ketua-tim.edit', $item->id) }}" 
                                   class="p-2 text-slate-500 hover:text-amber-600 hover:bg-amber-50 rounded-xl transition border border-transparent hover:border-amber-200" 
                                   title="Edit Kegiatan">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>

                                {{-- Hapus --}}
                                <form action="{{ route('ketua-tim.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus kegiatan ini?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition border border-transparent hover:border-rose-200" title="Hapus Kegiatan">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>

                    {{-- EXPANDED SUB KEGIATAN ACCORDION ROW --}}
                    <tr x-show="expandedSub === {{ $item->id }}" x-cloak class="bg-indigo-50/30 sub-kegiatan-row">
                        <td colspan="6" class="px-8 py-4 border-t border-b border-indigo-100">
                            <div class="bg-white rounded-2xl p-4 border border-indigo-100 shadow-2xs space-y-3">
                                <div class="flex items-center justify-between">
                                    <h4 class="font-bold text-gray-900 text-xs flex items-center gap-1.5">
                                        <span class="w-2 h-2 rounded-full bg-indigo-600"></span>
                                        Daftar Sub Kegiatan di bawah "{{ $item->agenda ?: ($item->text ?: 'Kegiatan') }}"
                                    </h4>
                                    <a href="{{ route('sub-kegiatan.index', ['task_id' => $item->id]) }}" class="text-[11px] font-bold text-indigo-600 hover:text-indigo-800 hover:underline">
                                        + Kelola Sub Kegiatan
                                    </a>
                                </div>
                                @if($subCount > 0)
                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2.5">
                                        @foreach($subs as $s)
                                            <div class="p-3 rounded-xl border border-gray-100 bg-gray-50/50 flex flex-col justify-between gap-1 text-xs">
                                                <div class="font-semibold text-gray-800">{{ $s->nama_sub }}</div>
                                                <div class="flex items-center justify-between text-[11px] text-gray-500 mt-1">
                                                    <span>{{ $s->tanggal ? \Carbon\Carbon::parse($s->tanggal)->format('d/m/Y') : '-' }}</span>
                                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $s->status === 'Selesai' ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800' }}">
                                                        {{ $s->status ?: 'Sedang Berjalan' }}
                                                    </span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-xs text-gray-400 italic">Belum ada rincian sub kegiatan yang ditambahkan.</p>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center justify-center space-y-2">
                                <div class="w-12 h-12 rounded-2xl bg-gray-100 flex items-center justify-center text-gray-400">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                    </svg>
                                </div>
                                <h4 class="font-bold text-gray-700 text-sm">Belum Ada Kegiatan</h4>
                                <p class="text-xs text-gray-400 max-w-sm">Kegiatan yang dibuat oleh Ketua Tim akan muncul di sini.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ==============================================================
        MODAL DAFTAR JADWAL PST LENGKAP DENGAN SEARCH & AKSI EDIT
    =============================================================== --}}
    @if(isset($pstTasks) && $pstTasks->isNotEmpty())
    <div x-show="openPstModal" 
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs transition-opacity"
         @keydown.escape.window="openPstModal = false">
        <div class="bg-white rounded-3xl max-w-4xl w-full p-6 shadow-2xl border border-gray-100 space-y-4 max-h-[88vh] flex flex-col"
             @click.away="openPstModal = false">
            <div class="flex items-center justify-between border-b border-gray-100 pb-4 shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold shrink-0 shadow-2xs border border-blue-100">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="font-black text-slate-900 text-lg">Pelayanan Statistik Terpadu (PST)</h3>
                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-extrabold bg-blue-100 text-blue-800">
                                {{ $pstTasks->count() }} Jadwal Kegiatan
                            </span>
                        </div>
                        <p class="text-xs text-gray-500 mt-0.5">Daftar jadwal harian dan penugasan petugas operasional PST</p>
                    </div>
                </div>
                <button type="button" @click="openPstModal = false" class="text-slate-400 hover:text-slate-600 hover:bg-slate-100 p-2 rounded-xl transition cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Live Search Filter inside PST --}}
            <div class="shrink-0 flex items-center gap-3">
                <div class="relative flex-1">
                    <input type="text" 
                           x-model="pstSearch"
                           placeholder="Cari tanggal PST atau nama petugas..." 
                           class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all">
                    <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
            </div>

            {{-- Scrollable Table of All PST Tasks --}}
            <div class="overflow-y-auto flex-1 border border-gray-100 rounded-2xl">
                <table class="min-w-full divide-y divide-gray-200 text-left text-xs">
                    <thead class="bg-gray-50 text-[11px] font-bold text-gray-600 uppercase tracking-wider sticky top-0 z-10 shadow-2xs">
                        <tr>
                            <th class="px-4 py-3">Tanggal Kegiatan</th>
                            <th class="px-4 py-3">Sesi Pelayanan</th>
                            <th class="px-4 py-3">Petugas Bertugas</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @foreach($pstTasks->sortBy('start_date') as $idx => $pstItem)
                        @php
                            $petugasNames = $pstItem->penugasans->pluck('peserta')->filter()->unique()->values();
                            if ($petugasNames->isEmpty() && !empty($pstItem->petugas_list)) {
                                $petugasNames = collect($pstItem->petugas_list);
                            }
                            $dateFormatted = \Carbon\Carbon::parse($pstItem->start_date)->translatedFormat('l, d F Y');
                            $searchHaystack = strtolower($dateFormatted . ' ' . $petugasNames->implode(' '));
                        @endphp
                        <tr class="hover:bg-blue-50/30 transition-colors"
                            x-show="!pstSearch || '{{ addslashes($searchHaystack) }}'.includes(pstSearch.toLowerCase())">
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="font-bold text-gray-900">{{ $dateFormatted }}</div>
                                <span class="text-[10px] text-gray-400 font-mono">ID #{{ $pstItem->id }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="space-y-0.5 text-gray-700">
                                    <div class="font-medium text-slate-800">Sesi 1: 08:00 - 12:00 WITA</div>
                                    <div class="font-medium text-slate-800">Sesi 2: 13:00 - 16:00 WITA</div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-1 max-w-md">
                                    @foreach($petugasNames as $pName)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                            {{ $pName }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    <span>Selesai</span>
                                </span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    {{-- Edit --}}
                                    <a href="{{ route('ketua-tim.edit', $pstItem->id) }}" 
                                       class="p-1.5 text-slate-500 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition border border-transparent hover:border-blue-200" 
                                       title="Edit Jadwal PST">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>

                                    {{-- Hapus --}}
                                    <form action="{{ route('ketua-tim.destroy', $pstItem->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus jadwal kegiatan PST untuk tanggal {{ $dateFormatted }}?')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition border border-transparent hover:border-rose-200" title="Hapus Jadwal PST">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="flex items-center justify-between pt-2 border-t border-gray-100 text-xs text-gray-500 shrink-0">
                <span>Total: <strong class="text-gray-800">{{ $pstTasks->count() }}</strong> jadwal operasional PST</span>
                <button type="button" @click="openPstModal = false" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-xl transition cursor-pointer">
                    Tutup
                </button>
            </div>
        </div>
    </div>
    @endif
</div>

<script>
function filterTableKetuaTim() {
    const input = document.getElementById("tableSearchInput");
    const filter = input.value.toLowerCase();
    const rows = document.querySelectorAll("#kegiatanTable tbody tr.kegiatan-row");

    rows.forEach(row => {
        const text = row.innerText.toLowerCase();
        if (text.includes(filter)) {
            row.style.display = "";
        } else {
            row.style.display = "none";
        }
    });
}
</script>

@endsection
