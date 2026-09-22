@extends('layouts.app')

@section('title', 'Kelola Kegiatan - Sikeren')
@section('header_title', 'Kelola Kegiatan')

@section('content')
@php
    $totalKegiatan = count($kegiatans) + count($kegiatans2);
    $menungguApprove = 0;
    $berjalan = 0;
    $selesai = 0;

    foreach ($kegiatans as $k) {
        if ($k->setuju_rapat == 0) {
            $menungguApprove++;
        } elseif ($k->setuju_rapat == 1) {
            if (!empty($k->notulen) || ($k->notulen_selesai ?? 0) == 1) {
                $selesai++;
            } else {
                $berjalan++;
            }
        }
    }
    foreach ($kegiatans2 as $k2) {
        if ($k2->status == 'Selesai') {
            $selesai++;
        } elseif ($k2->setuju_rapat == 3 || $k2->status == 'Ditolak' || $k2->status_pemimpin == 'Ditolak') {
            // ditolak
        } elseif ($k2->setuju_rapat == 1 || $k2->status == 'Disetujui' || $k2->status_pemimpin == 'Disetujui') {
            $berjalan++;
        } else {
            $menungguApprove++;
        }
    }

    $currentUser = Auth::user();
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

<div class="space-y-6 pb-12 max-w-7xl mx-auto" x-data="{ 
    expandedSub: null, 
    approvalModal: { open: false, id: null, text: '', pj: '', jenis: 'Kegiatan' },
    statusModal: { open: false, id: null, text: '', currentStatus: 'Sedang Berjalan', newStatus: 'Sedang Berjalan', alasan: '' }
}">

    {{-- HEADER SECTION & ACTION BUTTONS --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-gray-200/80 shadow-xs">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-md text-[11px] font-bold uppercase tracking-wider bg-blue-50 text-blue-700 border border-blue-100">
                    Pengelolaan Tim
                </span>
                <span class="text-xs text-gray-400">•</span>
                <span class="text-xs text-gray-500 font-medium">BPS Kabupaten/Kota</span>
            </div>
            <h2 class="text-xl font-bold text-gray-900 mt-1">Kelola Kegiatan & Sub Kegiatan</h2>
            <p class="text-xs text-gray-500 mt-0.5">
                Kelola seluruh kegiatan, pantau sub-kegiatan terintegrasi melalui menu dropdown, dan delegasikan penugasan tim.
            </p>
        </div>
        <div class="flex items-center gap-2.5 flex-wrap sm:flex-nowrap">
            <a href="{{ route('agenda.downloadWord') }}" class="inline-flex items-center justify-center px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-xs hover:shadow transition-all duration-150">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Download Agenda (.docx)
            </a>

            @if($isPjUser)
                <a href="{{ route('ketua-tim.create') }}" class="inline-flex items-center justify-center px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-xs hover:shadow transition-all duration-150">
                    <svg class="w-4 h-4 mr-1.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Buat Kegiatan Baru
                </a>
                <a href="{{ url('/rapat') }}" class="inline-flex items-center justify-center px-4 py-2.5 bg-white hover:bg-gray-50 border border-gray-200 text-gray-700 text-xs font-bold rounded-xl shadow-xs hover:border-gray-300 transition-all duration-150">
                    <svg class="w-4 h-4 mr-1.5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    Buat Rapat
                </a>
                <a href="{{ route('sub-kegiatan.index') }}" class="inline-flex items-center justify-center px-4 py-2.5 bg-amber-50 hover:bg-amber-100 border border-amber-200 text-amber-800 text-xs font-bold rounded-xl shadow-xs transition-all duration-150">
                    <svg class="w-4 h-4 mr-1.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    Sub Kegiatan
                </a>
            @endif
        </div>
    </div>

    {{-- ALERT NOTIFICATIONS --}}
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

    @if (session('error_access'))
    <div class="bg-amber-50 border border-amber-200 p-4 rounded-xl shadow-xs flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <div>
                <h4 class="text-xs font-bold text-amber-900">Akses Dibatasi</h4>
                <p class="text-xs text-amber-700 mt-0.5">{{ session('error_access') }}</p>
            </div>
        </div>
    </div>
    @endif

    {{-- KPI STATS SUMMARY CARDS --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-2xl border border-gray-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-gray-500">Total Kegiatan</p>
                <h3 class="text-2xl font-bold text-gray-900 mt-1">{{ $totalKegiatan }}</h3>
                <span class="text-[10px] font-semibold text-blue-600 bg-blue-50 px-2 py-0.5 rounded-md mt-1 inline-block">Semua Agenda</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-base">
                {{ $totalKegiatan }}
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-gray-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-gray-500">Sedang Berjalan</p>
                <h3 class="text-2xl font-bold text-sky-600 mt-1">{{ $berjalan }}</h3>
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
                <h3 class="text-2xl font-bold text-amber-600 mt-1">{{ $menungguApprove }}</h3>
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
                <h3 class="text-2xl font-bold text-emerald-600 mt-1">{{ $selesai }}</h3>
                <span class="text-[10px] font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md mt-1 inline-block">Tuntas</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
    </div>

    {{-- MAIN TABS & FILTERABLE TABLES --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        
        {{-- Tab Navigation & Controls --}}
        <div class="border-b border-gray-200 px-6 pt-4 pb-0 bg-gray-50/40 flex flex-col md:flex-row md:items-center justify-between gap-4">
            
            <div class="flex items-center gap-2 -mb-px">
                <button type="button" 
                        onclick="switchTab('tab-kegiatan')" 
                        id="btn-tab-kegiatan" 
                        class="tab-btn active px-4 py-3 text-xs font-bold border-b-2 border-blue-600 text-blue-600 flex items-center gap-2 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                    </svg>
                    <span>Daftar Kegiatan Penugasan</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-700 ml-1">
                        {{ count($kegiatans2) }}
                    </span>
                </button>

                <button type="button" 
                        onclick="switchTab('tab-rapat')" 
                        id="btn-tab-rapat" 
                        class="tab-btn px-4 py-3 text-xs font-bold border-b-2 border-transparent text-gray-500 hover:text-gray-700 flex items-center gap-2 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <span>Agenda Rapat</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-gray-100 text-gray-600 ml-1">
                        {{ count($kegiatans) }}
                    </span>
                </button>
            </div>

            <div class="pb-3 flex items-center gap-2.5 flex-wrap sm:flex-nowrap">
                <div class="relative min-w-[220px]">
                    <input type="text" 
                           id="tableSearchInput" 
                           onkeyup="filterTable()" 
                           placeholder="Cari kegiatan, tim, PJ..." 
                           class="w-full pl-8 pr-3 py-2 text-xs rounded-xl border border-gray-200 bg-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all">
                    <svg class="w-4 h-4 text-gray-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        {{-- ==========================================
            TAB 1: TABEL DAFTAR KEGIATAN PENUGASAN DENGAN DROPDOWN SUB KEGIATAN
        =========================================== --}}
        <div id="panel-tab-kegiatan" class="tab-panel">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-left" id="kegiatanTable">
                    <thead class="bg-gray-50/80 text-[11px] font-bold text-gray-600 uppercase tracking-wider">
                        <tr>
                            <th scope="col" class="px-6 py-4">Nama Kegiatan & Tim</th>
                            <th scope="col" class="px-6 py-4">Rentang Waktu</th>
                            <th scope="col" class="px-6 py-4">Wilayah / Lokasi</th>
                            <th scope="col" class="px-6 py-4">PJ & Sub Kegiatan</th>
                            <th scope="col" class="px-6 py-4">Status Persetujuan</th>
                            <th scope="col" class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100 text-xs">
                        @forelse($kegiatans2 as $item)
                        @php
                            $subs = $item->subKegiatans;
                            $subCount = count($subs);
                            $wilayahList = $item->wilayah_list;
                            $canAccessThis = $currentUser && $currentUser->canAccessDetailKegiatan($item);
                            $isPjOfThis = $currentUser && (
                                strcasecmp($currentUser->nama_lengkap ?? '', $item->penanggung_jawab ?? '') === 0 ||
                                strcasecmp($currentUser->username ?? '', $item->penanggung_jawab ?? '') === 0 ||
                                strcasecmp($currentUser->nama_lengkap ?? '', $item->pemimpin ?? '') === 0 ||
                                (isset($currentUser->level) && strtolower($currentUser->level) === 'admin')
                            );
                            $st = strtolower(trim($item->status ?? ''));
                            $isDone     = ($st === 'selesai');
                            $isDelayed  = ($st === 'tertunda');
                            $isInactive = ($st === 'tidak berjalan' || $st === 'dibatalkan');
                            $isApproved = ($item->setuju_rapat == 1 || $st === 'disetujui' || $st === 'sedang berjalan' || $isDone || $isDelayed || $isInactive);
                            $isRejected = ($item->setuju_rapat == 3 || $st === 'ditolak');
                            $isPending  = ($item->setuju_rapat == 0 && !$isApproved && !$isRejected && in_array($st, ['menunggu', 'menunggu persetujuan', 'belum', '']));
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
                                    @if($canAccessThis)
                                        <a href="{{ url('daftarkegiatan/' . $item->id) }}" class="font-bold text-gray-900 hover:text-blue-600 text-sm block">
                                             {{ $item->text }}
                                        </a>
                                    @else
                                        <span class="font-bold text-gray-900 text-sm block" title="Mode pantau agenda">
                                            {{ $item->text }}
                                        </span>
                                    @endif
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
                                    <span class="text-gray-400 italic">{{ $item->tempat ?: '-' }}</span>
                                @endif
                            </td>

                            {{-- PJ & Dropdown Sub Kegiatan Button --}}
                            <td class="px-6 py-4">
                                <div class="space-y-2">
                                    <div class="font-medium text-gray-800">
                                        PJ: <span class="font-bold">{{ $item->penanggung_jawab ?: '-' }}</span>
                                    </div>
                                    
                                    {{-- Dropdown Sub Kegiatan Trigger --}}
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

                            {{-- Status Persetujuan & Pelaksanaan --}}
                            <td class="px-6 py-4">
                                @if($isDone)
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                        <span>Selesai</span>
                                    </span>
                                @elseif($isDelayed)
                                    <div class="space-y-1">
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-bold bg-amber-50 text-amber-700 border border-amber-300">
                                            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                            <span>Tertunda</span>
                                        </span>
                                        @if(!empty($item->alasan_status))
                                            <p class="text-[10px] text-amber-700 italic max-w-[180px] truncate" title="{{ $item->alasan_status }}">{{ $item->alasan_status }}</p>
                                        @endif
                                    </div>
                                @elseif($isInactive)
                                    <div class="space-y-1">
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-bold bg-slate-100 text-slate-700 border border-slate-300">
                                            <span class="w-2 h-2 rounded-full bg-slate-500"></span>
                                            <span>Tidak Berjalan</span>
                                        </span>
                                        @if(!empty($item->alasan_status))
                                            <p class="text-[10px] text-slate-600 italic max-w-[180px] truncate" title="{{ $item->alasan_status }}">{{ $item->alasan_status }}</p>
                                        @endif
                                    </div>
                                @elseif($isApproved)
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                        <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                                        <span>Sedang Berjalan</span>
                                    </span>
                                @elseif($isRejected)
                                    <div class="space-y-1">
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-bold bg-red-50 text-red-700 border border-red-200">
                                            <span class="w-2 h-2 rounded-full bg-red-500"></span>
                                            <span>Ditolak</span>
                                        </span>
                                        @if(!empty($item->alasan_status))
                                            <p class="text-[10px] text-red-600 italic max-w-[180px] truncate" title="{{ $item->alasan_status }}">{{ $item->alasan_status }}</p>
                                        @endif
                                    </div>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                                        <span>Menunggu Persetujuan</span>
                                    </span>
                                @endif
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    {{-- Tombol Persetujuan Cepat untuk PJ (Saat Menunggu) --}}
                                    @if($isPjOfThis && $isPending)
                                        <button type="button" 
                                                @click="approvalModal = { open: true, id: {{ $item->id }}, text: '{{ addslashes($item->text) }}', pj: '{{ addslashes($item->penanggung_jawab ?: ($item->pemimpin ?: '-')) }}', jenis: 'Kegiatan' }" 
                                                class="px-2.5 py-1.5 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-xl text-xs transition inline-flex items-center gap-1 shadow-xs cursor-pointer" 
                                                title="Tinjau dan Berikan Persetujuan">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            <span>Persetujuan</span>
                                        </button>
                                    @elseif($isPjOfThis)
                                        {{-- Tombol Update Status Pelaksanaan untuk PJ (Saat Sudah Disetujui/Berjalan/Tertunda) --}}
                                        <button type="button" 
                                                @click="statusModal = { open: true, id: {{ $item->id }}, text: '{{ addslashes($item->text) }}', currentStatus: '{{ addslashes($item->status ?? 'Sedang Berjalan') }}', newStatus: '{{ addslashes($item->status ?? 'Sedang Berjalan') }}', alasan: '{{ addslashes($item->alasan_status ?? '') }}' }" 
                                                class="px-2.5 py-1.5 bg-slate-100 hover:bg-blue-50 text-slate-700 hover:text-blue-700 font-bold rounded-xl border border-slate-200 hover:border-blue-200 text-xs transition inline-flex items-center gap-1 shadow-2xs cursor-pointer" 
                                                title="Update Status Pelaksanaan Kegiatan">
                                            <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            <span>Status</span>
                                        </button>
                                    @endif

                                    @if($canAccessThis)
                                        <a href="{{ route('sub-kegiatan.index', ['task_id' => $item->id]) }}" class="px-3 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-800 font-bold rounded-xl border border-amber-200 text-xs transition inline-flex items-center gap-1" title="Tambah Sub Kegiatan">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                            </svg>
                                            <span>+ Sub</span>
                                        </a>

                                        <a href="{{ url('daftarkegiatan/' . $item->id) }}" class="px-3.5 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 font-bold rounded-xl border border-blue-200 text-xs transition">
                                            Detail
                                        </a>

                                        <form action="{{ route('ketua-tim.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Hapus kegiatan ini?');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 text-gray-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition" title="Hapus Kegiatan">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </form>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-gray-100 text-gray-400 font-semibold text-[11px] cursor-not-allowed select-none border border-gray-200" title="Detail kegiatan hanya dapat dibuka oleh PJ / Ketua Tim">
                                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                            </svg>
                                            <span>Hanya PJ</span>
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>

                        {{-- EXPANDED SUB KEGIATAN ACCORDION / DROPDOWN PANEL --}}
                        <tr x-show="expandedSub === {{ $item->id }}" x-cloak class="bg-indigo-50/30">
                            <td colspan="6" class="px-8 py-4 border-t border-b border-indigo-100">
                                <div class="bg-white rounded-2xl p-4 border border-indigo-100 shadow-2xs space-y-3">
                                    <div class="flex items-center justify-between">
                                        <h4 class="font-bold text-gray-900 text-xs flex items-center gap-1.5">
                                            <span class="w-2 h-2 rounded-full bg-indigo-600"></span>
                                            Daftar Sub Kegiatan di bawah "{{ $item->text }}"
                                        </h4>
                                        @if($canAccessThis)
                                            <a href="{{ route('sub-kegiatan.index', ['task_id' => $item->id]) }}" class="text-[11px] font-bold text-blue-600 hover:underline">
                                                + Tambah Sub Kegiatan Baru
                                            </a>
                                        @endif
                                    </div>

                                    @if($subCount > 0)
                                        <div class="divide-y divide-gray-100">
                                            @foreach($subs as $sub)
                                                @php $dl = $sub->deadline_status; @endphp
                                                <div class="py-2.5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                                                    <div class="space-y-0.5">
                                                        <span class="font-bold text-gray-800">{{ $sub->nama_sub }}</span>
                                                        <div class="flex items-center gap-2 text-[11px] text-gray-500">
                                                            <span>PJ: {{ $sub->pj ?: '-' }}</span>
                                                            <span>•</span>
                                                            <span>Waktu: {{ $sub->start_date }} s.d {{ $sub->end_date }}</span>
                                                        </div>
                                                    </div>

                                                    <div class="flex items-center gap-3">
                                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $dl['class'] }}">
                                                            <span class="w-1.5 h-1.5 rounded-full {{ $dl['dot'] }}"></span>
                                                            <span>{{ $dl['label'] }}</span>
                                                        </span>

                                                        <div class="w-24 bg-gray-200 rounded-full h-1.5">
                                                            <div class="h-1.5 rounded-full {{ $sub->progress >= 100 ? 'bg-emerald-500' : 'bg-blue-600' }}" style="width: {{ $sub->progress }}%"></div>
                                                        </div>
                                                        <span class="text-[10px] font-bold text-gray-600">{{ $sub->progress }}%</span>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="py-3 text-center text-gray-400 text-xs">
                                            Belum ada sub kegiatan untuk kegiatan ini. 
                                            <a href="{{ route('sub-kegiatan.index', ['task_id' => $item->id]) }}" class="text-blue-600 font-bold hover:underline ml-1">Tambah sekarang</a>
                                        </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-16 text-center text-gray-400">
                                <div class="max-w-xs mx-auto text-center space-y-3">
                                    <div class="w-12 h-12 rounded-2xl bg-gray-50 text-gray-300 flex items-center justify-center mx-auto">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                        </svg>
                                    </div>
                                    <p class="text-xs font-medium text-gray-500">Belum ada kegiatan penugasan.</p>
                                    <a href="{{ route('ketua-tim.create') }}" class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white text-xs font-semibold rounded-xl hover:bg-blue-700">
                                        Buat Kegiatan Sekarang
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ==========================================
            TAB 2: TABEL AGENDA RAPAT
        =========================================== --}}
        <div id="panel-tab-rapat" class="tab-panel hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-left" id="rapatTable">
                    <thead class="bg-gray-50/80 text-[11px] font-bold text-gray-600 uppercase tracking-wider">
                        <tr>
                            <th scope="col" class="px-6 py-4">Topik & Informasi Rapat</th>
                            <th scope="col" class="px-6 py-4">Petugas Rapat</th>
                            <th scope="col" class="px-6 py-4">Tahapan Alur Rapat</th>
                            <th scope="col" class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100 text-xs">
                        @forelse ($kegiatans as $post)
                        @php
                            $statusKey = 'menunggu';
                            if ($post->setuju_rapat == 3) {
                                $statusKey = 'ditolak';
                            } elseif ($post->setuju_rapat == 1) {
                                if (!empty($post->notulen) || ($post->notulen_selesai ?? 0) == 1) {
                                    $statusKey = 'selesai';
                                } else {
                                    $statusKey = 'presensi';
                                }
                            }
                            $canAccessRapat = $currentUser && $currentUser->canAccessDetailKegiatan($post);
                            $isPemimpinThisRapat = $currentUser && (
                                strcasecmp($currentUser->nama_lengkap ?? '', $post->pemimpin ?? '') === 0 ||
                                strcasecmp($currentUser->username ?? '', $post->pemimpin ?? '') === 0 ||
                                (isset($currentUser->level) && strtolower($currentUser->level) === 'admin')
                            );
                        @endphp
                        <tr class="hover:bg-slate-50/60 transition-colors rapat-row">
                            <td class="px-6 py-4">
                                <div class="space-y-2 max-w-lg">
                                    <div class="flex items-center gap-2">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200 shrink-0">
                                            #{{ $post->id_kegiatan ?? $post->id }}
                                        </span>
                                        @if($canAccessRapat)
                                            <a href="{{ url('daftarkegiatan/' . ($post->id_kegiatan ?? $post->id)) }}" class="font-bold text-gray-900 hover:text-blue-600 transition-colors text-sm line-clamp-1">
                                                {{ $post->text }}
                                            </a>
                                        @else
                                            <span class="font-bold text-gray-900 text-sm line-clamp-1" title="Mode pantau agenda">
                                                {{ $post->text }}
                                            </span>
                                        @endif
                                    </div>
                                    
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="meta-chip">
                                            <svg class="w-3.5 h-3.5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                            <span>{{ \Carbon\Carbon::parse($post->start_date)->translatedFormat('d M Y') }}</span>
                                        </span>

                                        <span class="meta-chip font-mono">
                                            <svg class="w-3.5 h-3.5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            <span>{{ substr($post->start_jam, 0, 5) }} - {{ substr($post->end_jam, 0, 5) }} WITA</span>
                                        </span>

                                        <span class="meta-chip" title="{{ $post->tempat }}">
                                            <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                            </svg>
                                            <span class="truncate max-w-[200px]">{{ $post->tempat ?? 'Aula / Kantor BPS' }}</span>
                                        </span>
                                    </div>
                                </div>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-xs">
                                <div class="space-y-1.5">
                                    <div class="flex items-center gap-2">
                                        <span class="w-5 h-5 rounded-md bg-blue-100 text-blue-700 text-[10px] font-bold flex items-center justify-center shrink-0" title="Pemimpin Rapat">P</span>
                                        <span class="font-semibold text-gray-800 truncate max-w-[160px]">{{ $post->pemimpin ?? '-' }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="w-5 h-5 rounded-md bg-emerald-100 text-emerald-700 text-[10px] font-bold flex items-center justify-center shrink-0" title="Notulis Rapat">N</span>
                                        <span class="text-gray-600 font-medium truncate max-w-[160px]">{{ $post->notulis ?? '-' }}</span>
                                    </div>
                                </div>
                            </td>

                            <td class="px-6 py-4">
                                @if($post->setuju_rapat == 1 && (!empty($post->notulen) || ($post->notulen_selesai ?? 0) == 1))
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                        <span>Tahap 4: Notulen Selesai</span>
                                    </span>
                                @elseif($post->setuju_rapat == 1)
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                        <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                                        <span>Tahap 3: Presensi QR Aktif</span>
                                    </span>
                                @elseif($post->setuju_rapat == 3)
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-bold bg-red-50 text-red-700 border border-red-200">
                                        <span class="w-2 h-2 rounded-full bg-red-500"></span>
                                        <span>Tahap 2: Ditolak</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                                        <span>Tahap 2: Menunggu Persetujuan</span>
                                    </span>
                                @endif
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-right text-xs">
                                <div class="flex items-center justify-end gap-2">
                                    {{-- Tombol Persetujuan Cepat untuk Pemimpin Rapat --}}
                                    @if($isPemimpinThisRapat && $post->setuju_rapat == 0)
                                        <button type="button" 
                                                @click="approvalModal = { open: true, id: {{ $post->id_kegiatan ?? $post->id }}, text: '{{ addslashes($post->text) }}', pj: '{{ addslashes($post->pemimpin ?? '-') }}', jenis: 'Rapat' }" 
                                                class="px-2.5 py-1.5 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-xl text-xs transition inline-flex items-center gap-1 shadow-xs" 
                                                title="Tinjau dan Berikan Persetujuan Rapat">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            <span>Persetujuan</span>
                                        </button>
                                    @endif

                                    @if($canAccessRapat)
                                        <a href="{{ url('daftarkegiatan/' . ($post->id_kegiatan ?? $post->id)) }}" class="px-3.5 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 font-bold rounded-xl border border-blue-200 text-xs transition">
                                            Detail
                                        </a>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl bg-gray-100 text-gray-400 font-semibold text-[11px] cursor-not-allowed select-none border border-gray-200" title="Detail rapat hanya dapat dibuka oleh Pemimpin / Notulis / PJ">
                                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                            </svg>
                                            <span>Hanya PJ</span>
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-16 text-center text-gray-400">
                                <p class="text-xs font-medium text-gray-500">Belum ada agenda rapat yang terdaftar.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    {{-- ==========================================
        MODAL PERSETUJUAN KEGIATAN & RAPAT
    =========================================== --}}
    <div x-show="approvalModal.open" 
         x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-gray-100 space-y-5"
             @click.away="approvalModal.open = false">
            
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center font-bold">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-900 text-sm" x-text="'Persetujuan ' + approvalModal.jenis"></h3>
                        <p class="text-[11px] text-gray-400">Tentukan status persetujuan agenda</p>
                    </div>
                </div>
                <button type="button" @click="approvalModal.open = false" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="space-y-3 text-xs bg-slate-50 p-4 rounded-2xl border border-slate-100">
                <div>
                    <span class="text-gray-400 text-[10px] uppercase font-bold tracking-wider">Nama Agenda:</span>
                    <p class="font-bold text-gray-900 text-sm mt-0.5" x-text="approvalModal.text"></p>
                </div>
                <div class="flex items-center justify-between text-gray-600 pt-1 border-t border-slate-200/60">
                    <span>Penanggung Jawab / Pemimpin:</span>
                    <span class="font-bold text-gray-800" x-text="approvalModal.pj"></span>
                </div>
            </div>

            <p class="text-xs text-gray-500 leading-relaxed">
                Sebagai Ketua Tim / PJ / Pemimpin, silakan tentukan apakah agenda ini disetujui untuk dilaksanakan atau ditolak.
            </p>

            <div class="flex items-center gap-3 pt-2">
                {{-- Form Tolak --}}
                <form action="{{ url('/setuju_rapat') }}" method="POST" class="flex-1">
                    @csrf
                    <input type="hidden" name="id" :value="approvalModal.id">
                    <input type="hidden" name="setuju_rapat" value="3">
                    <button type="submit" 
                            onclick="return confirm('Apakah Anda yakin ingin menolak agenda ini?')" 
                            class="w-full py-2.5 px-4 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 font-bold rounded-xl text-xs transition flex items-center justify-center gap-1.5">
                        <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        <span>Tolak</span>
                    </button>
                </form>

                {{-- Form Setujui --}}
                <form action="{{ url('/setuju_rapat') }}" method="POST" class="flex-1">
                    @csrf
                    <input type="hidden" name="id" :value="approvalModal.id">
                    <input type="hidden" name="setuju_rapat" value="1">
                    <button type="submit" 
                            class="w-full py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs transition shadow-md shadow-emerald-600/20 flex items-center justify-center gap-1.5">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Setujui</span>
                    </button>
                </form>
            </div>

        </div>
    </div>

    {{-- =========================================================================
        MODAL UPDATE STATUS PELAKSANAAN (SELESAI, TERTUNDA, TIDAK BERJALAN, SEDANG BERJALAN)
    ========================================================================== --}}
    <div x-show="statusModal.open" 
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs overflow-y-auto">
        <div class="bg-white rounded-3xl shadow-2xl border border-gray-100 w-full max-w-lg p-6 space-y-4 my-8"
             @click.away="statusModal.open = false">
            
            <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    </div>
                    <div>
                        <h3 class="font-black text-gray-900 text-base">Update Status Kegiatan</h3>
                        <p class="text-xs text-gray-400">Atur progres & kelanjutan pelaksanaan agenda</p>
                    </div>
                </div>
                <button type="button" @click="statusModal.open = false" class="text-gray-400 hover:text-gray-600 text-xl font-bold">
                    &times;
                </button>
            </div>

            <div class="p-3 bg-gray-50 rounded-xl border border-gray-100 space-y-1">
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Nama Kegiatan</span>
                <h4 class="font-bold text-gray-900 text-xs" x-text="statusModal.text"></h4>
            </div>

            <form :action="'/kegiatan/' + statusModal.id + '/status'" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                        Pilih Status Pelaksanaan <span class="text-rose-500">*</span>
                    </label>
                    <div class="space-y-2">
                        <label class="flex items-center justify-between p-3 rounded-xl border cursor-pointer transition hover:bg-emerald-50/50" :class="statusModal.newStatus === 'Selesai' ? 'bg-emerald-50 border-emerald-300 ring-1 ring-emerald-500' : 'border-gray-200'">
                            <div class="flex items-center gap-3">
                                <input type="radio" name="status" value="Selesai" x-model="statusModal.newStatus" class="text-emerald-600 focus:ring-emerald-500">
                                <div>
                                    <span class="text-xs font-bold text-gray-900 block">Selesai (Tuntas 100%)</span>
                                    <span class="text-[11px] text-gray-500 block">Seluruh target dan sub kegiatan telah tuntas dikerjakan</span>
                                </div>
                            </div>
                            <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                        </label>

                        <label class="flex items-center justify-between p-3 rounded-xl border cursor-pointer transition hover:bg-blue-50/50" :class="statusModal.newStatus === 'Sedang Berjalan' ? 'bg-blue-50 border-blue-300 ring-1 ring-blue-500' : 'border-gray-200'">
                            <div class="flex items-center gap-3">
                                <input type="radio" name="status" value="Sedang Berjalan" x-model="statusModal.newStatus" class="text-blue-600 focus:ring-blue-500">
                                <div>
                                    <span class="text-xs font-bold text-gray-900 block">Sedang Berjalan</span>
                                    <span class="text-[11px] text-gray-500 block">Kegiatan aktif berjalan sesuai jadwal dan penugasan</span>
                                </div>
                            </div>
                            <span class="w-3 h-3 rounded-full bg-blue-500"></span>
                        </label>

                        <label class="flex items-center justify-between p-3 rounded-xl border cursor-pointer transition hover:bg-amber-50/50" :class="statusModal.newStatus === 'Tertunda' ? 'bg-amber-50 border-amber-300 ring-1 ring-amber-500' : 'border-gray-200'">
                            <div class="flex items-center gap-3">
                                <input type="radio" name="status" value="Tertunda" x-model="statusModal.newStatus" class="text-amber-600 focus:ring-amber-500">
                                <div>
                                    <span class="text-xs font-bold text-gray-900 block">Tertunda (Pending)</span>
                                    <span class="text-[11px] text-gray-500 block">Pelaksanaan ditunda sementara waktu dengan alasan khusus</span>
                                </div>
                            </div>
                            <span class="w-3 h-3 rounded-full bg-amber-500"></span>
                        </label>

                        <label class="flex items-center justify-between p-3 rounded-xl border cursor-pointer transition hover:bg-rose-50/50" :class="statusModal.newStatus === 'Tidak Berjalan' ? 'bg-rose-50 border-rose-300 ring-1 ring-rose-500' : 'border-gray-200'">
                            <div class="flex items-center gap-3">
                                <input type="radio" name="status" value="Tidak Berjalan" x-model="statusModal.newStatus" class="text-rose-600 focus:ring-rose-500">
                                <div>
                                    <span class="text-xs font-bold text-gray-900 block">Tidak Berjalan / Dibatalkan</span>
                                    <span class="text-[11px] text-gray-500 block">Kegiatan tidak dapat dilaksanakan atau dibatalkan</span>
                                </div>
                            </div>
                            <span class="w-3 h-3 rounded-full bg-rose-500"></span>
                        </label>
                    </div>
                </div>

                <div x-show="statusModal.newStatus === 'Tertunda' || statusModal.newStatus === 'Tidak Berjalan'" x-cloak class="space-y-1">
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider">
                        Alasan / Catatan Status <span class="text-rose-500">*</span>
                    </label>
                    <textarea name="alasan_status" x-model="statusModal.alasan" rows="2" placeholder="Contoh: Menunggu pencairan anggaran DIPA / Pergeseran jadwal lapangan..." class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600"></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-100">
                    <button type="button" @click="statusModal.open = false" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-xl text-xs transition cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl text-xs transition shadow-xs cursor-pointer">
                        Simpan Perubahan Status
                    </button>
                </div>
            </form>

        </div>
    </div>

<script>
function switchTab(tabId) {
    document.querySelectorAll('.tab-panel').forEach(function(el) {
        el.classList.add('hidden');
    });

    document.querySelectorAll('.tab-btn').forEach(function(btn) {
        btn.classList.remove('border-blue-600', 'text-blue-600');
        btn.classList.add('border-transparent', 'text-gray-500');
    });

    const targetPanel = document.getElementById('panel-' + tabId);
    if (targetPanel) {
        targetPanel.classList.remove('hidden');
    }

    const targetBtn = document.getElementById('btn-' + tabId);
    if (targetBtn) {
        targetBtn.classList.remove('border-transparent', 'text-gray-500');
        targetBtn.classList.add('border-blue-600', 'text-blue-600');
    }

    filterTable();
}

function filterTable() {
    const searchVal = document.getElementById('tableSearchInput').value.toLowerCase();

    document.querySelectorAll('.kegiatan-row').forEach(function(row) {
        const text = row.innerText.toLowerCase();
        row.style.display = text.includes(searchVal) ? '' : 'none';
    });

    document.querySelectorAll('.rapat-row').forEach(function(row) {
        const text = row.innerText.toLowerCase();
        row.style.display = text.includes(searchVal) ? '' : 'none';
    });
}
</script>
@endsection