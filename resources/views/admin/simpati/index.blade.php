@extends('layouts.app')

@section('title', 'Integrasi SIMPATI API - Sikeren')
@section('header_title', 'Integrasi SIMPATI API')

@push('styles')
<style>
    /* Smooth filter satker: fade + skeleton tanpa reload */
    #simpati-filter-area, #simpati-stats, #simpati-pegawai-wrap {
        transition: opacity .25s ease, transform .25s ease, filter .25s ease;
    }
    .simpati-filtering {
        opacity: .55;
        pointer-events: none;
        filter: saturate(.85);
    }
    .simpati-skeleton-row {
        animation: simpati-pulse 1.1s ease-in-out infinite;
    }
    @keyframes simpati-pulse {
        0%, 100% { opacity: .45; }
        50% { opacity: 1; }
    }
    #simpati-pegawai-tbody tr {
        animation: simpati-fadein .3s ease;
    }
    @keyframes simpati-fadein {
        from { opacity: 0; transform: translateY(4px); }
        to { opacity: 1; transform: translateY(0); }
    }
    select[name="satker"]:disabled {
        opacity: .7;
        cursor: wait;
    }
    @media print {
        body * {
            visibility: hidden !important;
        }
        #print-nametag-area, #print-nametag-area * {
            visibility: visible !important;
        }
        #print-nametag-area {
            position: fixed !important;
            left: 50% !important;
            top: 50% !important;
            transform: translate(-50%, -50%) !important;
            margin: 0 !important;
            box-shadow: none !important;
            border: 2px solid #000 !important;
        }
    }
</style>
@endpush

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6" x-data="simpatiManager()">
    {{-- Banner Header Berdasarkan Alur SIMPATI --}}
    <div class="relative overflow-hidden rounded-3xl p-6 sm:p-8 text-white shadow-xl border border-blue-500/30"
         style="background: linear-gradient(135deg, #1e3a8a 0%, #1d4ed8 50%, #2563eb 100%);">
        {{-- Subtle Grid Watermark (Crisp, No Blurry Glows) --}}
        <div class="absolute inset-0 opacity-10 pointer-events-none" style="background-image: radial-gradient(rgba(255,255,255,0.5) 1px, transparent 1px); background-size: 24px 24px;"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div class="space-y-2">
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white/20 text-xs font-semibold text-white border border-white/30 shadow-xs">
                    <span class="w-2 h-2 rounded-full" :class="connectionStatus === 'online' ? 'bg-emerald-400 animate-ping' : 'bg-amber-300'"></span>
                    <span x-text="connectionStatus === 'online' ? 'SIMPATI API Terhubung (Online)' : 'Alur SDM SIMPATI Siap Digunakan'"></span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white">
                    Integrasi Data SIMPATI &harr; SIKEREN
                </h1>
                <p class="text-sm text-blue-100 max-w-2xl leading-relaxed font-medium">
                    Alur SDM SIMPATI: Penarikan data per-Satker, Deteksi Pindah Satker, Generate QR Nametag, serta sinkronisasi Struktur Tim Kerja ke Database Tim & Kegiatan.
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <button @click="testConnection()" 
                        :disabled="isTesting || isSyncing || isSaving"
                        class="px-4 py-2.5 rounded-xl bg-white/20 hover:bg-white/30 border border-white/40 text-white font-bold text-sm transition flex items-center gap-2 shadow-sm disabled:opacity-50">
                    <svg x-show="!isTesting" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    <svg x-show="isTesting" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                    <span x-text="isTesting ? 'Menguji...' : 'Uji Koneksi'"></span>
                </button>
                <button @click="syncData()" 
                        :disabled="isTesting || isSyncing || isSaving"
                        class="px-5 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-white font-bold text-sm transition flex items-center gap-2 shadow-lg shadow-emerald-600/30 disabled:opacity-50">
                    <svg x-show="!isSyncing" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <svg x-show="isSyncing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                    <span x-text="isSyncing ? 'Menyinkronkan...' : 'Sinkronkan Sesuai SATKER'"></span>
                </button>
            </div>
        </div>
    </div>

    {{-- Filter Pilihan SATKER & Statistik Ringkas --}}
    <div id="simpati-filter-area" class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 space-y-4">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 border-b border-gray-100 pb-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/></svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-gray-900">Diambil Sesuai Dari SATKER</h3>
                    <p class="text-xs text-gray-400">Pilih unit kerja BPS untuk memfilter data SDM dan penugasan</p>
                    <p class="text-[11px] mt-1 font-medium transition-colors"
                       :class="isFiltering ? 'text-blue-600' : (selectedSatker !== appliedSatker ? 'text-amber-600' : 'text-gray-400')">
                        <span x-show="isFiltering" class="inline-flex items-center gap-1.5">
                            <svg class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            Memuat data satker <span class="font-bold" x-text="satkerLabel(selectedSatker)"></span>...
                        </span>
                        <span x-show="!isFiltering && selectedSatker !== appliedSatker">Ada perubahan belum diterapkan — klik “Terapkan” untuk memuat.</span>
                        <span x-show="!isFiltering && selectedSatker === appliedSatker">Menampilkan: <span class="font-bold text-gray-700" x-text="satkerLabel(appliedSatker)"></span></span>
                    </p>
                </div>
            </div>

            <form method="GET" action="{{ route('simpati.index') }}" @submit.prevent="applySatker()" class="flex flex-wrap items-center gap-3">
                <select name="satker"
                        x-model="selectedSatker"
                        :disabled="isFiltering"
                        class="bg-gray-50 border border-gray-200 text-gray-800 text-xs font-semibold rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition min-w-[220px]">
                    @foreach($satkers as $code => $name)
                        <option value="{{ $code }}" {{ $selectedSatker == $code ? 'selected' : '' }}>
                            {{ $name }}
                        </option>
                    @endforeach
                </select>
                <button type="submit"
                        :disabled="isFiltering || selectedSatker === appliedSatker"
                        :class="(isFiltering || selectedSatker === appliedSatker)
                            ? 'bg-gray-100 text-gray-400 cursor-not-allowed'
                            : 'bg-blue-600 hover:bg-blue-700 text-white shadow-sm shadow-blue-600/30'"
                        class="px-4 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 min-w-[110px] justify-center">
                    <svg x-show="isFiltering" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                    <span x-text="isFiltering ? 'Memuat...' : (selectedSatker === appliedSatker ? 'Diterapkan' : 'Terapkan')"></span>
                </button>
            </form>
        </div>

        {{-- Statistik Alur SDM --}}
        <div id="simpati-stats" class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-1" :class="isFiltering ? 'simpati-filtering' : ''">
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-gray-500">Total SDM Pegawai</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 00.75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 00-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0112 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 01-.673-.38m0 0A2.18 2.18 0 013 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 013.413-.387m7.5 0V5.25A2.25 2.25 0 0013.5 3h-3a2.25 2.25 0 00-2.25 2.25v.894m7.5 0a48.111 48.111 0 00-7.5 0"/></svg>
                </div>
                <div class="text-2xl font-black text-slate-800 mt-2 tabular-nums" x-text="stats.totalPegawai">{{ $totalPegawai }}</div>
                <span class="text-[11px] text-gray-400 mt-1 block">Tersinkron di Satker ini</span>
            </div>

            <div class="p-4 rounded-2xl bg-indigo-50 border border-indigo-100">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-indigo-700">Database Tim Kerja</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>
                </div>
                <div class="text-2xl font-black text-indigo-700 mt-2 tabular-nums" x-text="stats.totalTimKerja">{{ $totalTimKerja }}</div>
                <span class="text-[11px] text-indigo-500/80 mt-1 block">Struktur Tim Aktif</span>
            </div>

            <div class="p-4 rounded-2xl bg-purple-50 border border-purple-100">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-purple-700">Pegawai Multi-Tim</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                </div>
                <div class="text-2xl font-black text-purple-700 mt-2 tabular-nums" x-text="stats.totalMultiTim">{{ $totalMultiTim }}</div>
                <span class="text-[11px] text-purple-500/80 mt-1 block">Anggota di &gt; 1 Tim</span>
            </div>

            <div class="p-4 rounded-2xl border transition-colors duration-300"
                 :class="stats.totalPindah > 0 ? 'bg-amber-50 border-amber-200 text-amber-900' : 'bg-emerald-50 border-emerald-100 text-emerald-900'">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold" :class="stats.totalPindah > 0 ? 'text-amber-800' : 'text-emerald-700'">Pindah SATKER</span>
                    <span class="inline-flex items-center">
                        <svg x-show="stats.totalPindah > 0" class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                        <svg x-show="stats.totalPindah <= 0" class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </span>
                </div>
                <div class="text-2xl font-black mt-2 tabular-nums"
                     :class="stats.totalPindah > 0 ? 'text-amber-700' : 'text-emerald-700'"
                     x-text="stats.totalPindah">{{ $totalPindah }}</div>
                <span class="text-[11px] mt-1 block"
                      :class="stats.totalPindah > 0 ? 'text-amber-700 font-semibold' : 'text-emerald-600'"
                      x-text="stats.totalPindah > 0 ? 'Perlu Verifikasi Status!' : 'Semua Status Normal'">{{ $totalPindah > 0 ? 'Perlu Verifikasi Status!' : 'Semua Status Normal' }}</span>
            </div>
        </div>
        <p x-show="filterError" x-text="filterError" class="text-xs font-semibold text-rose-600 pt-1"></p>
    </div>

    {{-- Notification Alert Box --}}
    <div x-show="alert.show" 
         x-transition 
         :class="alert.type === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-900' : (alert.type === 'warning' ? 'bg-amber-50 border-amber-200 text-amber-900' : 'bg-rose-50 border-rose-200 text-rose-900')"
         class="p-4 rounded-2xl border flex items-start gap-3 shadow-xs">
        <div class="shrink-0 mt-0.5">
            <template x-if="alert.type === 'success'">
                <svg class="w-5 h-5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
            </template>
            <template x-if="alert.type === 'error'">
                <svg class="w-5 h-5 text-rose-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
            </template>
            <template x-if="alert.type === 'warning'">
                <svg class="w-5 h-5 text-amber-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
            </template>
        </div>
        <div class="flex-1 space-y-1">
            <p class="text-sm font-bold" x-text="alert.title || (alert.type === 'success' ? 'Berhasil' : 'Informasi')"></p>
            <p class="text-xs font-medium leading-relaxed" x-text="alert.message"></p>
            
            <template x-if="alert.type === 'error' && alert.showTroubleshoot">
                <div class="mt-3 p-3 bg-white/80 rounded-xl border border-rose-200 text-xs text-slate-700 space-y-1.5">
                    <p class="font-bold text-rose-800 flex items-center gap-1.5"><svg class="w-4 h-4 inline-block align-[-2px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18v-5.25m0 0a6.01 6.01 0 001.5-.189m-1.5.189a6.01 6.01 0 01-1.5-.189m3.75 7.478a12.06 12.06 0 01-4.5 0m3.75 2.383a14.406 14.406 0 01-3 0M14.25 18v-.192c0-.983.658-1.823 1.508-2.316a7.5 7.5 0 10-7.517 0c.85.493 1.509 1.333 1.509 2.316V18"/></svg> Langkah Alternatif:</p>
                    <p>Anda dapat menguji alur SDM SIMPATI lengkap menggunakan <strong>"Sinkronkan Simulasi BPS"</strong> di bawah untuk menguji mutasi pindah satker, multi-tim, dan QR nametag.</p>
                </div>
            </template>
        </div>
        <button @click="alert.show = false" class="text-gray-400 hover:text-gray-600 shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>

    {{-- Tabs Navigasi: SDM Pegawai vs Database Tim Kerja --}}
    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden" x-data="{ activeTab: 'pegawai' }">
        <div class="flex border-b border-gray-100 px-6 pt-4 gap-6 bg-gray-50/50">
            <button @click="activeTab = 'pegawai'"
                    :class="activeTab === 'pegawai' ? 'border-blue-600 text-blue-600 font-bold' : 'border-transparent text-gray-500 hover:text-gray-700 font-medium'"
                    class="pb-3 border-b-2 text-sm flex items-center gap-2 transition">
                <span>Daftar SDM Pegawai</span>
                <span class="px-2 py-0.5 text-xs rounded-full bg-blue-100 text-blue-700 tabular-nums" x-text="stats.totalPegawai">{{ $totalPegawai }}</span>
            </button>
            <button @click="activeTab = 'tim'"
                    :class="activeTab === 'tim' ? 'border-blue-600 text-blue-600 font-bold' : 'border-transparent text-gray-500 hover:text-gray-700 font-medium'"
                    class="pb-3 border-b-2 text-sm flex items-center gap-2 transition">
                <span>Database Tim Kerja</span>
                <span class="px-2 py-0.5 text-xs rounded-full bg-indigo-100 text-indigo-700 tabular-nums" x-text="stats.totalTimKerja">{{ $totalTimKerja }}</span>
            </button>
            <button @click="activeTab = 'kegiatan'"
                    :class="activeTab === 'kegiatan' ? 'border-blue-600 text-blue-600 font-bold' : 'border-transparent text-gray-500 hover:text-gray-700 font-medium'"
                    class="pb-3 border-b-2 text-sm flex items-center gap-2 transition">
                <span>Database Kegiatan & Projek</span>
                <span class="px-2 py-0.5 text-xs rounded-full bg-emerald-100 text-emerald-700 tabular-nums font-bold" x-text="proyekStats.totalProyek">{{ $totalProyek }}</span>
            </button>
            <button @click="activeTab = 'config'"
                    :class="activeTab === 'config' ? 'border-blue-600 text-blue-600 font-bold' : 'border-transparent text-gray-500 hover:text-gray-700 font-medium'"
                    class="pb-3 border-b-2 text-sm flex items-center gap-2 transition">
                <span>Pengaturan API & Endpoint</span>
            </button>
        </div>

        {{-- TAB 1: DAFTAR SDM PEGAWAI & GENERATE QR NAMETAG --}}
        <div x-show="activeTab === 'pegawai'" class="p-6 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h3 class="text-base font-bold text-gray-900">Data Pegawai Tersinkronisasi</h3>
                    <p class="text-xs text-gray-400">Pegawai BPS yang ditarik dari SIMPATI dan aktif di database SIKEREN</p>
                </div>
                <div class="flex items-center gap-2">
                    <button @click="syncMockData()" 
                            :disabled="isSyncing"
                            class="px-3.5 py-2 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 text-xs font-semibold transition flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 inline-block align-[-2px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0112 15a9.065 9.065 0 00-6.23.693L5 14.5m14.8.8l1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0112 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5"/></svg> <span>Sinkronkan Simulasi BPS</span>
                    </button>
                </div>
            </div>

            <div id="simpati-pegawai-wrap" class="overflow-x-auto rounded-2xl border border-gray-100 transition-opacity duration-300" :class="isFiltering ? 'simpati-filtering' : ''">
                <table class="w-full text-left text-xs text-gray-600">
                    <thead class="bg-gray-50 text-gray-700 uppercase font-bold text-[11px] border-b border-gray-100">
                        <tr>
                            <th class="px-4 py-3.5">Pegawai & Identitas</th>
                            <th class="px-4 py-3.5">Jabatan & Satker</th>
                            <th class="px-4 py-3.5">Tim Kerja (Multi-Tim)</th>
                            <th class="px-4 py-3.5 text-center">Status Kepegawaian</th>
                            <th class="px-4 py-3.5 text-center">Aksi & Manajemen</th>
                        </tr>
                    </thead>
                    <tbody id="simpati-pegawai-tbody" class="divide-y divide-gray-100">
                        <template x-for="p in pegawaiList" :key="p.id">
                            <tr class="hover:bg-gray-50/70 transition">
                                <td class="px-4 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-blue-100 text-blue-700 font-bold flex items-center justify-center shrink-0" x-text="p.initial"></div>
                                        <div>
                                            <div class="font-bold text-gray-900 text-sm" x-text="p.nama_lengkap"></div>
                                            <div class="text-[11px] text-gray-400 font-mono" x-text="'NIP: ' + p.formatted_nip + ' • ' + (p.email || '-')"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3.5">
                                    <div class="font-semibold text-gray-800" x-text="p.nm_jabatan"></div>
                                    <div class="text-[11px] text-gray-400" x-text="p.nm_satker"></div>
                                </td>
                                <td class="px-4 py-3.5">
                                    <template x-if="p.tims && p.tims.length > 0">
                                        <div class="flex flex-wrap gap-1.5 items-center">
                                            <template x-for="timName in p.tims" :key="timName">
                                                <span class="px-2 py-0.5 rounded-lg bg-blue-50 text-blue-700 border border-blue-100 text-[11px] font-medium" x-text="timName"></span>
                                            </template>
                                            <template x-if="p.tims.length > 1">
                                                <span class="px-1.5 py-0.5 rounded-md bg-purple-100 text-purple-700 text-[10px] font-bold" title="Pegawai tergabung di lebih dari 1 tim kerja" x-text="'Multi-Tim (' + p.tims.length + ')'"></span>
                                            </template>
                                        </div>
                                    </template>
                                    <template x-if="!p.tims || p.tims.length === 0">
                                        <span class="text-gray-400 italic text-[11px]">- Belum ada tim -</span>
                                    </template>
                                </td>
                                <td class="px-4 py-3.5 text-center">
                                    <template x-if="p.is_pindahsatker == 1">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-amber-100 text-amber-800 text-[11px] font-bold border border-amber-200" title="Pegawai terdeteksi pindah/mutasi Satker dari data SIMPATI">
                                            <svg class="w-3.5 h-3.5 inline-block align-[-2px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg> Pindah SATKER
                                        </span>
                                    </template>
                                    <template x-if="p.is_pindahsatker != 1">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800 text-[11px] font-semibold">
                                            <svg class="w-3 h-3 inline-block align-[-2px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Aktif
                                        </span>
                                    </template>
                                </td>
                                <td class="px-4 py-3.5 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button @click="openQrNametag(p.id)"
                                                type="button"
                                                title="Cetak QR Nametag"
                                                class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 text-xs font-bold transition shadow-2xs">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                                            </svg>
                                            <span class="hidden sm:inline">QR</span>
                                        </button>
                                        <button @click="openEditPegawai(p)"
                                                type="button"
                                                title="Ubah Data Pegawai"
                                                class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl bg-slate-50 hover:bg-slate-100 text-slate-700 border border-slate-200 text-xs font-bold transition shadow-2xs">
                                            <svg class="w-3.5 h-3.5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                            <span class="hidden sm:inline">Ubah</span>
                                        </button>
                                        <button @click="deletePegawai(p.id, p.nama_lengkap)"
                                                type="button"
                                                title="Hapus Pegawai"
                                                class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-xs font-bold transition shadow-2xs">
                                            <svg class="w-3.5 h-3.5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                            <span class="hidden sm:inline">Hapus</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                        <template x-if="isFiltering">
                            <tr class="simpati-skeleton-row">
                                <td colspan="5" class="px-4 py-4">
                                    <div class="space-y-2.5">
                                        <div class="h-10 rounded-xl bg-gray-100"></div>
                                        <div class="h-10 rounded-xl bg-gray-100"></div>
                                        <div class="h-10 rounded-xl bg-gray-100"></div>
                                    </div>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="!isFiltering && pegawaiList.length === 0" x-cloak>
                            <td colspan="5" class="px-4 py-8 text-center text-gray-400">
                                Belum ada data pegawai di <span class="font-bold" x-text="satkerLabel(appliedSatker)"></span>. Klik <strong>"Sinkronkan Sesuai SATKER"</strong> atau <strong>"Sinkronkan Simulasi BPS"</strong> untuk menarik data.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- TAB 2: DATABASE TIM KERJA & DATABASE KEGIATAN --}}
        <div x-show="activeTab === 'tim'" class="p-6 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h3 class="text-base font-bold text-gray-900">Database Tim Kerja & Terhubung ke Kegiatan</h3>
                    <p class="text-xs text-gray-400">Tim kerja yang ditarik dari SIMPATI siap digunakan langsung pada pembuatan kegiatan/proyek tim</p>
                </div>
                <a href="{{ route('ketua-tim.create') }}" 
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs transition shadow-sm">
                    <svg class="w-3.5 h-3.5 inline-block align-[-2px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15"/></svg> <span>Buat Kegiatan dari Database Tim</span>
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @forelse($syncedTims as $tim)
                    <div class="p-5 rounded-2xl border border-gray-200 bg-white hover:border-blue-200 transition space-y-3 shadow-2xs">
                        <div class="flex items-start justify-between gap-3">
                            <div class="space-y-1">
                                <span class="px-2.5 py-0.5 rounded-full bg-indigo-50 text-indigo-700 text-[11px] font-bold">
                                    Tim Kerja SIMPATI
                                </span>
                                <h4 class="text-base font-bold text-gray-900">{{ $tim->grup }}</h4>
                            </div>
                            <span class="text-xs px-2 py-1 rounded-lg bg-gray-100 font-semibold text-gray-700">
                                {{ count($tim->members) }} Anggota
                            </span>
                        </div>

                        <div class="border-t border-gray-100 pt-3 space-y-2">
                            <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider block">Daftar Anggota Tim:</span>
                            <div class="flex flex-wrap gap-1.5">
                                @forelse($tim->members as $m)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-gray-50 border border-gray-200 text-xs font-medium text-gray-700">
                                        <svg class="w-3 h-3 inline-block align-[-1px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                                        <span>{{ $m->nama_lengkap }}</span>
                                    </span>
                                @empty
                                    <span class="text-gray-400 text-xs italic">Belum ada anggota terdaftar</span>
                                @endforelse
                            </div>
                        </div>

                        <div class="border-t border-gray-100 pt-3 flex items-center justify-between text-xs">
                            <span class="text-gray-500 font-medium">Terhubung ke: Database Kegiatan SIKEREN</span>
                            <a href="{{ route('ketua-tim.create') }}" class="font-bold text-blue-600 hover:text-blue-800 transition">
                                Jadwalkan Tugas &rarr;
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="col-span-2 p-8 text-center text-gray-400 border border-dashed rounded-2xl">
                        Belum ada Tim Kerja yang tersinkronisasi. Klik tombol sinkronisasi untuk memuat data tim.
                    </div>
                @endforelse
            </div>
        </div>

        {{-- TAB 3: PENGATURAN API & DOKUMENTASI --}}
        <div x-show="activeTab === 'config'" class="p-6 space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                {{-- Form Konfigurasi --}}
                <div class="lg:col-span-5">
                    <form @submit.prevent="saveConfig()" class="bg-gray-50/70 rounded-2xl border border-gray-200 p-5 space-y-4">
                        <div class="space-y-1">
                            <h4 class="text-sm font-bold text-gray-900">Host & Kredensial SIMPATI API</h4>
                            <p class="text-xs text-gray-500">Tersimpan aman di file konfigurasi .env</p>
                        </div>

                        <div class="space-y-3">
                            <div>
                                <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1">
                                    Base URL SIMPATI
                                </label>
                                <input type="text" x-model="form.base_url" required placeholder="http://localhost:3000" class="w-full bg-white border border-gray-200 focus:border-blue-500 text-gray-800 text-xs font-mono rounded-xl px-3.5 py-2.5 transition">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1">
                                    Header Auth Key
                                </label>
                                <input type="text" readonly value="x-api-key" class="w-full bg-gray-100 border border-gray-200 text-gray-600 text-xs font-mono rounded-xl px-3.5 py-2.5 cursor-not-allowed">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1">
                                    API Key (Token)
                                </label>
                                <div class="flex items-center gap-2">
                                    <input :type="showKey ? 'text' : 'password'" x-model="form.api_key" required class="flex-1 bg-white border border-gray-200 focus:border-blue-500 text-gray-800 text-xs font-mono rounded-xl px-3.5 py-2.5 transition">
                                    <button @click="showKey = !showKey" type="button" class="px-3 py-2.5 rounded-xl bg-gray-200 text-gray-700 text-xs font-medium">
                                        <span x-text="showKey ? 'Tutup' : 'Lihat'"></span>
                                    </button>
                                </div>
                            </div>

                            <button type="submit" 
                                    :disabled="isSaving"
                                    class="w-full px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition flex items-center justify-center gap-2">
                                <span x-text="isSaving ? 'Menyimpan...' : 'Simpan Konfigurasi (.env)'"></span>
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Spesifikasi Endpoint --}}
                <div class="lg:col-span-7 space-y-3">
                    <div class="p-4 rounded-2xl border border-gray-200 bg-gray-50/70 space-y-1.5">
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-0.5 rounded bg-blue-100 text-blue-700 font-bold font-mono text-xs">GET</span>
                            <code class="text-xs font-bold text-gray-800">/api/public/pegawai</code>
                        </div>
                        <p class="text-xs text-gray-600">Mengambil daftar seluruh pegawai BPS aktif. Parameter opsional: <code class="text-indigo-600">id_satker</code>.</p>
                    </div>

                    <div class="p-4 rounded-2xl border border-gray-200 bg-gray-50/70 space-y-1.5">
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-0.5 rounded bg-blue-100 text-blue-700 font-bold font-mono text-xs">GET</span>
                            <code class="text-xs font-bold text-gray-800">/api/public/pegawai/{niplama}</code>
                        </div>
                        <p class="text-xs text-gray-600">Mengambil detail data pegawai tunggal beserta daftar seluruh tim kerja yang diikuti.</p>
                    </div>

                    <div class="p-4 rounded-2xl border border-gray-200 bg-gray-50/70 space-y-1.5">
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-0.5 rounded bg-blue-100 text-blue-700 font-bold font-mono text-xs">GET</span>
                            <code class="text-xs font-bold text-gray-800">/api/public/tim-kerja</code>
                        </div>
                        <p class="text-xs text-gray-600">Mengambil daftar seluruh tim kerja, Ketua, PJ, dan anggota tim. Parameter opsional: <code class="text-indigo-600">id_satker</code>, <code class="text-indigo-600">nm_tim</code>.</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- TAB 4: DATABASE KEGIATAN & PROJEK (INTEGRASI MASTER EXCEL & PENETAPAN 1 PJ) --}}
        <div x-show="activeTab === 'kegiatan'" class="p-6 space-y-6">
            {{-- Header Section & Action --}}
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-gray-100">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        <h3 class="text-base font-bold text-gray-900">Database Kegiatan & Projek SIMPATI BPS</h3>
                    </div>
                    <p class="text-xs text-gray-500">Master 80 Kegiatan & Projek BPS Sultra beserta 2.809 penugasan SDM. Setiap proyek terhubung dengan 1 Penanggung Jawab (PJ) resmi.</p>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <button type="button"
                            @click="syncExcel()"
                            :disabled="isImportingExcel"
                            class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition flex items-center gap-2 shadow-sm shadow-emerald-600/20 disabled:opacity-50">
                        <svg x-show="!isImportingExcel" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        <svg x-show="isImportingExcel" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                        <span x-text="isImportingExcel ? 'Menyinkronkan...' : 'Sinkronkan Ulang Database Excel'"></span>
                    </button>
                </div>
            </div>

            {{-- 4 Kartu Statistik Proyek & PJ --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                <div class="p-4 rounded-2xl bg-blue-50/60 border border-blue-100">
                    <div class="flex items-center justify-between text-blue-700">
                        <span class="text-xs font-bold">Total Master Proyek</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                    </div>
                    <div class="text-2xl font-black text-blue-900 mt-2 tabular-nums" x-text="proyekStats.totalProyek">{{ $totalProyek }}</div>
                    <span class="text-[11px] text-blue-600/80 mt-0.5 block">Tersinkron di SIMPATI</span>
                </div>

                <div class="p-4 rounded-2xl bg-indigo-50/60 border border-indigo-100">
                    <div class="flex items-center justify-between text-indigo-700">
                        <span class="text-xs font-bold">Total Penugasan SDM</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/></svg>
                    </div>
                    <div class="text-2xl font-black text-indigo-900 mt-2 tabular-nums" x-text="Number(proyekStats.totalPenugasan).toLocaleString('id-ID')">{{ number_format($totalPenugasan, 0, ',', '.') }}</div>
                    <span class="text-[11px] text-indigo-600/80 mt-0.5 block">Anggota Proyek Terhubung</span>
                </div>

                <div class="p-4 rounded-2xl bg-emerald-50/60 border border-emerald-100">
                    <div class="flex items-center justify-between text-emerald-700">
                        <span class="text-xs font-bold">1 PJ Terdaftar</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div class="text-2xl font-black text-emerald-900 mt-2 tabular-nums" x-text="proyekStats.totalProyekAdaPj">{{ $totalProyekAdaPj }}</div>
                    <span class="text-[11px] text-emerald-600/80 mt-0.5 block">Kegiatan Berpenanggung Jawab</span>
                </div>

                <div class="p-4 rounded-2xl border transition-colors"
                     :class="proyekStats.totalProyekBelumPj > 0 ? 'bg-amber-50/70 border-amber-200' : 'bg-slate-50 border-slate-100'">
                    <div class="flex items-center justify-between"
                         :class="proyekStats.totalProyekBelumPj > 0 ? 'text-amber-800' : 'text-slate-600'">
                        <span class="text-xs font-bold">Belum Ada PJ</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                    </div>
                    <div class="text-2xl font-black mt-2 tabular-nums"
                         :class="proyekStats.totalProyekBelumPj > 0 ? 'text-amber-800' : 'text-slate-700'"
                         x-text="proyekStats.totalProyekBelumPj">{{ $totalProyekBelumPj }}</div>
                    <span class="text-[11px] mt-0.5 block font-medium"
                          :class="proyekStats.totalProyekBelumPj > 0 ? 'text-amber-700' : 'text-slate-400'"
                          x-text="proyekStats.totalProyekBelumPj > 0 ? 'Perlu Ditetapkan 1 PJ' : 'Semua Lengkap (100%)'"></span>
                </div>
            </div>

            {{-- Filter & Search Bar --}}
            <div class="bg-gray-50/80 rounded-2xl p-4 border border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-3">
                <div class="flex flex-1 flex-col sm:flex-row items-center gap-3">
                    {{-- Search Input --}}
                    <div class="relative w-full sm:w-80">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                        <input type="text"
                               x-model="proyekSearch"
                               @input="proyekPage = 1"
                               placeholder="Cari nama proyek, kode, atau PJ..."
                               class="w-full bg-white border border-gray-200 text-gray-800 text-xs font-medium rounded-xl pl-9 pr-3.5 py-2.5 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition">
                    </div>

                    {{-- Tim Kerja Filter --}}
                    <div class="w-full sm:w-auto">
                        <select x-model="proyekSelectedTim"
                                @change="proyekPage = 1"
                                class="w-full bg-white border border-gray-200 text-gray-800 text-xs font-semibold rounded-xl px-3.5 py-2.5 focus:border-blue-500 transition">
                            <option value="">Semua Tim Kerja BPS ({{ count($proyekTims ?? []) }} Tim)</option>
                            @foreach($proyekTims ?? [] as $t)
                                <option value="{{ $t->nm_tim }}">{{ $t->nm_tim }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- PJ Filter --}}
                    <div class="w-full sm:w-auto">
                        <select x-model="proyekPjFilter"
                                @change="proyekPage = 1"
                                class="w-full bg-white border border-gray-200 text-gray-800 text-xs font-semibold rounded-xl px-3.5 py-2.5 focus:border-blue-500 transition">
                            <option value="">Semua Status PJ</option>
                            <option value="ada">Sudah Ada 1 PJ Resmi</option>
                            <option value="belum">Belum Ada PJ</option>
                        </select>
                    </div>
                </div>

                <div class="text-xs text-gray-500 font-medium shrink-0">
                    Menampilkan <span class="font-bold text-gray-800" x-text="filteredProyeks().length"></span> dari <span class="font-bold text-gray-800" x-text="proyekList.length"></span> proyek
                </div>
            </div>

            {{-- Tabel Proyek --}}
            <div class="overflow-x-auto rounded-2xl border border-gray-100 bg-white">
                <table class="w-full text-left text-xs text-gray-600">
                    <thead class="bg-gray-50 text-gray-700 uppercase font-bold text-[11px] border-b border-gray-100">
                        <tr>
                            <th class="px-4 py-3.5 w-16 text-center">No</th>
                            <th class="px-4 py-3.5">Kode & Nama Kegiatan / Projek</th>
                            <th class="px-4 py-3.5">Tim Kerja BPS</th>
                            <th class="px-4 py-3.5 text-center">Anggota Proyek</th>
                            <th class="px-4 py-3.5">Penanggung Jawab (1 PJ Resmi)</th>
                            <th class="px-4 py-3.5 text-center w-36">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <template x-for="(p, idx) in paginatedProyeks()" :key="p.id">
                            <tr class="hover:bg-blue-50/30 transition">
                                <td class="px-4 py-3.5 text-center font-bold text-gray-400" x-text="(proyekPage - 1) * proyekPerPage + idx + 1"></td>
                                <td class="px-4 py-3.5">
                                    <div class="font-bold text-gray-900 text-sm leading-snug" x-text="p.namaproyek"></div>
                                    <div class="mt-1 flex items-center gap-2">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-slate-100 text-slate-700 border border-slate-200" x-text="'ID: ' + p.proyekid"></span>
                                        <span class="text-[11px] text-gray-400" x-text="'Tim ID: ' + p.id_tim"></span>
                                    </div>
                                </td>
                                <td class="px-4 py-3.5">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-100" x-text="p.nm_tim"></span>
                                </td>
                                <td class="px-4 py-3.5 text-center">
                                    <button type="button"
                                            @click="openAnggotaModal(p)"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 text-xs font-bold transition shadow-2xs">
                                        <svg class="w-3.5 h-3.5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                        <span x-text="p.anggota_count + ' Anggota'"></span>
                                    </button>
                                </td>
                                <td class="px-4 py-3.5">
                                    <template x-if="p.pj_nama && p.pj_nama.trim() !== ''">
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-800 font-black flex items-center justify-center text-xs shrink-0" x-text="p.pj_nama.charAt(0).toUpperCase()"></div>
                                            <div>
                                                <div class="font-bold text-gray-900 text-xs flex items-center gap-1.5">
                                                    <span x-text="p.pj_nama"></span>
                                                    <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[9px] font-extrabold bg-emerald-100 text-emerald-800">1 PJ</span>
                                                </div>
                                                <div class="text-[11px] text-gray-400 font-mono" x-text="'NIP: ' + (p.pj_nip || '-')"></div>
                                            </div>
                                        </div>
                                    </template>
                                    <template x-if="!p.pj_nama || p.pj_nama.trim() === ''">
                                        <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-amber-50 border border-amber-200 text-amber-800 text-xs font-semibold">
                                            <svg class="w-3.5 h-3.5 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                            <span>Belum Ada PJ</span>
                                        </div>
                                    </template>
                                </td>
                                <td class="px-4 py-3.5 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button type="button"
                                                @click="openPjModal(p)"
                                                :class="p.pj_nama ? 'bg-slate-100 hover:bg-slate-200 text-slate-700' : 'bg-blue-600 hover:bg-blue-700 text-white shadow-sm shadow-blue-600/20'"
                                                class="px-2.5 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1"
                                                :title="p.pj_nama ? 'Ganti Penanggung Jawab' : 'Tetapkan Penanggung Jawab'">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                            <span x-text="p.pj_nama ? 'Ganti PJ' : 'Tetapkan PJ'"></span>
                                        </button>
                                        <button type="button"
                                                @click="openAnggotaModal(p)"
                                                class="p-1.5 rounded-xl text-gray-500 hover:text-indigo-600 hover:bg-indigo-50 transition"
                                                title="Lihat Daftar Anggota">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>

                        <template x-if="filteredProyeks().length === 0">
                            <tr>
                                <td colspan="6" class="px-4 py-12 text-center text-gray-400">
                                    <div class="w-12 h-12 rounded-2xl bg-gray-100 text-gray-400 flex items-center justify-center mx-auto mb-3">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                                    </div>
                                    <p class="font-bold text-gray-700">Tidak ada proyek yang sesuai</p>
                                    <p class="text-xs text-gray-400 mt-1">Coba sesuaikan kata kunci pencarian atau filter tim kerja</p>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            {{-- Pagination Proyek --}}
            <template x-if="filteredProyeks().length > proyekPerPage">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-2">
                    <div class="text-xs text-gray-500">
                        Menampilkan <span class="font-bold text-gray-800" x-text="(proyekPage - 1) * proyekPerPage + 1"></span> - <span class="font-bold text-gray-800" x-text="Math.min(proyekPage * proyekPerPage, filteredProyeks().length)"></span> dari <span class="font-bold text-gray-800" x-text="filteredProyeks().length"></span> data
                    </div>
                    <div class="flex items-center gap-1.5">
                        <button type="button"
                                @click="proyekPage = Math.max(1, proyekPage - 1)"
                                :disabled="proyekPage === 1"
                                class="px-3 py-1.5 rounded-xl border border-gray-200 text-xs font-semibold text-gray-600 hover:bg-gray-100 disabled:opacity-40 transition">
                            Sebelumnya
                        </button>
                        <span class="px-3 py-1.5 text-xs font-bold text-gray-700 bg-gray-100 rounded-xl" x-text="'Halaman ' + proyekPage + ' dari ' + totalProyekPages()"></span>
                        <button type="button"
                                @click="proyekPage = Math.min(totalProyekPages(), proyekPage + 1)"
                                :disabled="proyekPage >= totalProyekPages()"
                                class="px-3 py-1.5 rounded-xl border border-gray-200 text-xs font-semibold text-gray-600 hover:bg-gray-100 disabled:opacity-40 transition">
                            Selanjutnya
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>

    {{-- MODAL INTERAKTIF: GENERATE & CETAK QR NAMETAG PEGAWAI --}}
    <div x-show="modalNametag.show" 
         x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs overflow-y-auto">
        
        <div @click.away="modalNametag.show = false" class="bg-white rounded-3xl shadow-2xl max-w-md w-full overflow-hidden border border-gray-100">
            {{-- Header Modal --}}
            <div class="px-6 py-4 bg-slate-900 text-white flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5zm4.125-9.75a1.875 1.875 0 11-3.75 0 1.875 1.875 0 013.75 0zm1.294 6.336a6.721 6.721 0 01-3.17.789 6.721 6.721 0 01-3.168-.789 3.376 3.376 0 016.338 0z"/></svg>
                    <h4 class="font-bold text-sm">QR Nametag Pegawai BPS</h4>
                </div>
                <button @click="modalNametag.show = false" class="text-slate-400 hover:text-white transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Kartu Nametag Resmi BPS (Printable) --}}
            <div class="p-6 flex flex-col items-center">
                <div id="print-nametag-area" class="w-80 bg-white rounded-2xl border-2 border-blue-900 shadow-md p-5 flex flex-col items-center text-center space-y-3 relative overflow-hidden">
                    {{-- Header ID Card --}}
                    <div class="w-full border-b-2 border-blue-900 pb-2 flex items-center justify-center gap-2">
                        <img src="{{ asset('assets/img/logo-sikeren-square.png') }}" alt="BPS" class="h-7 w-auto rounded" onerror="this.style.display='none'">
                        <div>
                            <div class="text-[11px] font-black tracking-wider text-blue-900 uppercase">BADAN PUSAT STATISTIK</div>
                            <div class="text-[9px] font-bold text-gray-500 uppercase" x-text="modalNametag.user?.nm_satker || 'BPS Provinsi Sulawesi Tenggara'"></div>
                        </div>
                    </div>

                    {{-- Avatar --}}
                    <div class="w-16 h-16 rounded-full bg-blue-600 text-white font-bold text-2xl flex items-center justify-center shadow-inner border-2 border-white">
                        <span x-text="(modalNametag.user?.nama_lengkap || 'U').charAt(0)"></span>
                    </div>

                    {{-- Identitas Pegawai --}}
                    <div class="space-y-0.5">
                        <h5 class="text-sm font-black text-gray-900" x-text="modalNametag.user?.nama_lengkap"></h5>
                        <p class="text-[11px] font-bold text-blue-700" x-text="modalNametag.user?.nm_jabatan"></p>
                        <p class="text-[10px] text-gray-500 font-mono" x-text="'NIP: ' + (modalNametag.user?.nipbaru || modalNametag.user?.niplama)"></p>
                    </div>

                    {{-- High Resolution QR Code --}}
                    <div class="p-3 bg-gray-50 rounded-xl border border-gray-200 flex items-center justify-center shadow-inner">
                        <div x-html="modalNametag.qr_svg" class="w-36 h-36 flex items-center justify-center"></div>
                    </div>

                    <div class="text-[9px] text-gray-400 italic">
                        Pindai QR ini untuk absensi & identitas resmi SIKEREN
                    </div>
                </div>

                {{-- Tombol Aksi Modal --}}
                <div class="w-full flex items-center justify-end gap-3 mt-6 pt-4 border-t border-gray-100">
                    <button @click="modalNametag.show = false" type="button" class="px-4 py-2 rounded-xl text-gray-600 hover:bg-gray-100 text-xs font-semibold transition">
                        Tutup
                    </button>
                    <button @click="window.print()" type="button" class="px-5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition flex items-center gap-2 shadow-sm">
                        <svg class="w-3.5 h-3.5 inline-block align-[-2px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.111 48.111 0 00-3.413-.387m-12 0a48.111 48.111 0 00-3.413.387A2.25 2.25 0 003 9.456V15.75a2.25 2.25 0 002.25 2.25h1.091m0 0L7.5 21h9l1.16-3M6.72 13.829L6 12l2.25-2.25M17.28 13.829L18 12l-2.25-2.25"/></svg> <span>Cetak Nametag</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL INTERAKTIF: EDIT DATA PEGAWAI SIMPATI (AKSES ADMIN) --}}
    <div x-show="modalEditPegawai.show" 
         x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs overflow-y-auto">
        <div @click.away="modalEditPegawai.show = false" class="bg-white rounded-3xl shadow-2xl max-w-lg w-full overflow-hidden border border-slate-200">
            <div class="px-6 py-4 bg-slate-900 text-white flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    <h4 class="font-bold text-sm">Ubah Data Pegawai SIMPATI</h4>
                </div>
                <button @click="modalEditPegawai.show = false" class="text-slate-400 hover:text-white transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form @submit.prevent="submitEditPegawai()" class="p-6 space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Nama Lengkap Pegawai</label>
                    <input type="text" x-model="modalEditPegawai.form.nama_lengkap" required class="w-full bg-slate-50 border border-slate-200 focus:bg-white focus:border-blue-500 text-slate-800 text-xs font-semibold rounded-xl px-3.5 py-2.5 transition">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Email Resmi BPS</label>
                    <input type="email" x-model="modalEditPegawai.form.email" class="w-full bg-slate-50 border border-slate-200 focus:bg-white focus:border-blue-500 text-slate-800 text-xs font-semibold rounded-xl px-3.5 py-2.5 transition">
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Nama Jabatan</label>
                        <input type="text" x-model="modalEditPegawai.form.nm_jabatan" class="w-full bg-slate-50 border border-slate-200 focus:bg-white focus:border-blue-500 text-slate-800 text-xs font-semibold rounded-xl px-3.5 py-2.5 transition">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Satuan Kerja (Satker)</label>
                        <select x-model="modalEditPegawai.form.id_satker" class="w-full bg-slate-50 border border-slate-200 focus:bg-white focus:border-blue-500 text-slate-800 text-xs font-semibold rounded-xl px-3 py-2.5 transition">
                            <template x-for="(name, code) in satkerNames" :key="code">
                                <option :value="code" x-text="code + ' - ' + name"></option>
                            </template>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Tim Kerja Utama</label>
                    <input type="text" x-model="modalEditPegawai.form.tim_utama" placeholder="Contoh: Tim IPDS, Tim Nerwilis, dll" class="w-full bg-slate-50 border border-slate-200 focus:bg-white focus:border-blue-500 text-slate-800 text-xs font-semibold rounded-xl px-3.5 py-2.5 transition">
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-slate-100">
                    <button type="button" @click="modalEditPegawai.show = false" class="px-4 py-2 rounded-xl text-slate-600 hover:bg-slate-100 text-xs font-semibold transition">
                        Batal
                    </button>
                    <button type="submit" :disabled="modalEditPegawai.isSaving" class="px-5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition flex items-center gap-1.5 shadow-sm">
                        <span x-text="modalEditPegawai.isSaving ? 'Menyimpan...' : 'Simpan Perubahan'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL INTERAKTIF: DAFTAR ANGGOTA PROYEK & PILIH PJ LANGSUNG --}}
    <div x-show="modalAnggota.show" 
         x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs overflow-y-auto">
        <div @click.away="modalAnggota.show = false" class="bg-white rounded-3xl shadow-2xl max-w-2xl w-full overflow-hidden border border-gray-100">
            <div class="px-6 py-4 bg-slate-900 text-white flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-white/10 text-white flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </div>
                    <div>
                        <h4 class="font-bold text-sm">Daftar Anggota Tim Proyek</h4>
                        <p class="text-xs text-slate-400" x-text="modalAnggota.proyek ? (modalAnggota.proyek.namaproyek + ' • ' + modalAnggota.proyek.nm_tim) : ''"></p>
                    </div>
                </div>
                <button @click="modalAnggota.show = false" class="text-slate-400 hover:text-white transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="p-6 space-y-4">
                <div class="flex items-center justify-between gap-3">
                    <div class="relative flex-1">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                        <input type="text"
                               x-model="modalAnggota.search"
                               placeholder="Cari nama anggota atau NIP..."
                               class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-xs font-medium rounded-xl pl-9 pr-3.5 py-2.5 focus:border-blue-500 focus:bg-white transition">
                    </div>
                    <span class="px-3 py-1.5 rounded-xl bg-indigo-50 text-indigo-700 text-xs font-bold border border-indigo-100 shrink-0"
                          x-text="filteredModalAnggota().length + ' Anggota'"></span>
                </div>

                <div x-show="modalAnggota.isLoading" class="py-12 text-center text-gray-400 space-y-2">
                    <svg class="w-6 h-6 animate-spin text-blue-600 mx-auto" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                    <p class="text-xs font-semibold text-gray-600">Memuat anggota proyek...</p>
                </div>

                <div x-show="!modalAnggota.isLoading" class="max-h-96 overflow-y-auto rounded-2xl border border-gray-100 divide-y divide-gray-100">
                    <template x-for="(m, i) in filteredModalAnggota()" :key="m.id || i">
                        <div class="p-3.5 flex items-center justify-between hover:bg-slate-50/70 transition">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-700 font-bold flex items-center justify-center text-xs shrink-0"
                                     x-text="m.nama_lengkap.charAt(0).toUpperCase()"></div>
                                <div>
                                    <div class="font-bold text-slate-800 text-xs flex items-center gap-1.5">
                                        <span x-text="m.nama_lengkap"></span>
                                        <template x-if="modalAnggota.proyek && modalAnggota.proyek.pj_nama === m.nama_lengkap">
                                            <span class="px-1.5 py-0.2 rounded text-[9px] font-extrabold bg-emerald-100 text-emerald-800">1 PJ Resmi</span>
                                        </template>
                                    </div>
                                    <div class="text-[11px] text-slate-400 font-mono" x-text="'NIP: ' + (m.niplama || '-')"></div>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <template x-if="!modalAnggota.proyek || modalAnggota.proyek.pj_nama !== m.nama_lengkap">
                                    <button type="button"
                                            @click="assignPjFromAnggota(m)"
                                            class="px-2.5 py-1.5 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 text-[11px] font-bold transition flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        <span>Jadikan PJ</span>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </template>

                    <template x-if="filteredModalAnggota().length === 0">
                        <div class="p-8 text-center text-gray-400">
                            <p class="text-xs font-semibold">Tidak ada anggota yang cocok dengan pencarian.</p>
                        </div>
                    </template>
                </div>

                <div class="flex items-center justify-end pt-2 border-t border-slate-100">
                    <button type="button" @click="modalAnggota.show = false" class="px-5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL INTERAKTIF: TETAPKAN 1 PENANGGUNG JAWAB (PJ) PROYEK --}}
    <div x-show="modalPj.show" 
         x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs overflow-y-auto">
        <div @click.away="modalPj.show = false" class="bg-white rounded-3xl shadow-2xl max-w-lg w-full overflow-hidden border border-gray-100">
            <div class="px-6 py-4 bg-slate-900 text-white flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-blue-500/20 text-blue-400 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </div>
                    <div>
                        <h4 class="font-bold text-sm">Tetapkan 1 PJ Proyek</h4>
                        <p class="text-xs text-slate-400 truncate max-w-xs" x-text="modalPj.proyek ? modalPj.proyek.namaproyek : ''"></p>
                    </div>
                </div>
                <button @click="modalPj.show = false" class="text-slate-400 hover:text-white transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form @submit.prevent="submitSavePj()" class="p-6 space-y-4">
                <div class="p-3.5 rounded-2xl bg-blue-50 border border-blue-100 flex items-start gap-2.5 text-xs text-blue-800">
                    <svg class="w-4 h-4 text-blue-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <div>
                        <span class="font-bold">Regulasi SIMPATI:</span> Setiap kegiatan atau projek harus memiliki tepat <strong>1 Penanggung Jawab (PJ)</strong> resmi yang terdaftar.
                    </div>
                </div>

                <div x-show="modalPj.anggotaOptions && modalPj.anggotaOptions.length > 0">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Pilih Cepat dari Anggota Proyek Ini
                    </label>
                    <select @change="onSelectPjAnggota($event)" class="w-full bg-slate-50 border border-slate-200 focus:bg-white focus:border-blue-500 text-slate-800 text-xs font-semibold rounded-xl px-3.5 py-2.5 transition">
                        <option value="">-- Pilih dari anggota yang terdaftar di proyek --</option>
                        <template x-for="m in modalPj.anggotaOptions" :key="m.id">
                            <option :value="m.nama_lengkap + '|||' + (m.niplama || '')" x-text="m.nama_lengkap + (m.niplama ? ' (' + m.niplama + ')' : '')"></option>
                        </template>
                    </select>
                    <p class="text-[11px] text-gray-400 mt-1">Atau ketikkan nama PJ secara manual di kolom bawah:</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Nama Lengkap PJ <span class="text-rose-500">*</span>
                    </label>
                    <input type="text"
                           x-model="modalPj.pj_nama"
                           required
                           placeholder="Contoh: Budi Santoso, S.Si"
                           class="w-full bg-slate-50 border border-slate-200 focus:bg-white focus:border-blue-500 text-slate-800 text-xs font-semibold rounded-xl px-3.5 py-2.5 transition">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        NIP PJ (Opsional)
                    </label>
                    <input type="text"
                           x-model="modalPj.pj_nip"
                           placeholder="Contoh: 198501012010011001"
                           class="w-full bg-slate-50 border border-slate-200 focus:bg-white focus:border-blue-500 text-slate-800 text-xs font-mono rounded-xl px-3.5 py-2.5 transition">
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-slate-100">
                    <button type="button" @click="modalPj.show = false" class="px-4 py-2 rounded-xl text-slate-600 hover:bg-slate-100 text-xs font-semibold transition">
                        Batal
                    </button>
                    <button type="submit"
                            :disabled="modalPj.isSaving || !modalPj.pj_nama || modalPj.pj_nama.trim() === ''"
                            class="px-5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition flex items-center gap-1.5 shadow-sm disabled:opacity-50">
                        <svg x-show="modalPj.isSaving" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                        <span x-text="modalPj.isSaving ? 'Menyimpan...' : 'Simpan 1 PJ Resmi'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function simpatiManager() {
    return {
        isTesting: false,
        isSyncing: false,
        isSaving: false,
        isFiltering: false,
        showKey: false,
        connectionStatus: 'unknown',
        selectedSatker: '{{ $selectedSatker }}',
        appliedSatker: '{{ $selectedSatker }}',
        filterError: '',
        satkerNames: @json($satkers),
        stats: {
            totalPegawai: {{ $totalPegawai }},
            totalPindah: {{ $totalPindah }},
            totalMultiTim: {{ $totalMultiTim }},
            totalTimKerja: {{ $totalTimKerja }}
        },
        pegawaiList: @json($pegawaiList ?? []),
        form: {
            base_url: '{{ $baseUrl }}',
            api_key: '{{ $apiKey }}'
        },
        alert: {
            show: false,
            type: 'success',
            title: '',
            message: '',
            showTroubleshoot: false
        },
        modalNametag: {
            show: false,
            user: null,
            qr_svg: ''
        },
        modalEditPegawai: {
            show: false,
            isSaving: false,
            form: {
                id: null,
                nama_lengkap: '',
                email: '',
                nm_jabatan: '',
                id_satker: '7400',
                tim_utama: ''
            }
        },
        proyekList: @json($proyekListJson ?? []),
        proyekStats: {
            totalProyek: {{ $totalProyek }},
            totalPenugasan: {{ $totalPenugasan }},
            totalProyekAdaPj: {{ $totalProyekAdaPj }},
            totalProyekBelumPj: {{ $totalProyekBelumPj }}
        },
        proyekSearch: '',
        proyekSelectedTim: '',
        proyekPjFilter: '',
        proyekPage: 1,
        proyekPerPage: 12,
        isImportingExcel: false,

        modalAnggota: {
            show: false,
            isLoading: false,
            proyek: null,
            search: '',
            anggotaList: []
        },

        modalPj: {
            show: false,
            isSaving: false,
            proyek: null,
            pj_nama: '',
            pj_nip: '',
            anggotaOptions: []
        },

        init() {
            // Sinkron dari URL saat back/forward browser agar tetap smooth tanpa reload
            window.addEventListener('popstate', () => {
                const satkerFromUrl = new URL(window.location.href).searchParams.get('satker') || this.appliedSatker;
                if (satkerFromUrl && satkerFromUrl !== this.appliedSatker) {
                    this.selectedSatker = satkerFromUrl;
                    this.applySatker();
                }
            });
        },

        satkerLabel(code) {
            return this.satkerNames[code] || ('Satker ' + code);
        },

        async applySatker() {
            if (this.isFiltering) return;
            // Tombol nonaktif saat tidak ada perubahan; cegah fetch ganda
            if (this.selectedSatker === this.appliedSatker && this.pegawaiList !== null) {
                // Tetap selaraskan URL bila belum ada query (misal akses awal tanpa ?satker=)
                const cur = new URL(window.location.href);
                if (cur.searchParams.get('satker') !== this.appliedSatker) {
                    cur.searchParams.set('satker', this.appliedSatker);
                    window.history.replaceState({}, '', cur);
                }
                return;
            }
            this.isFiltering = true;
            this.filterError = '';
            // Jeda kecil agar transisi fade/skeleton terlihat smooth, bukan kedip
            await new Promise(r => setTimeout(r, 120));
            try {
                const url = new URL("{{ route('simpati.filter') }}", window.location.origin);
                url.searchParams.append('satker', this.selectedSatker);
                const res = await fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });
                const data = await res.json();
                if (!res.ok || !data.success) {
                    throw new Error(data.message || 'Gagal memuat data satker.');
                }
                this.stats = data.stats;
                this.pegawaiList = data.pegawai;
                this.appliedSatker = data.selected_satker;
                const newUrl = new URL(window.location.href);
                newUrl.searchParams.set('satker', data.selected_satker);
                window.history.pushState({}, '', newUrl);
            } catch (err) {
                this.filterError = err.message || 'Gagal memuat data. Coba lagi.';
                // Kembalikan pilihan ke yang sedang diterapkan agar tidak menggantung
                this.selectedSatker = this.appliedSatker;
            } finally {
                this.isFiltering = false;
            }
        },

        showAlert(type, title, message, showTroubleshoot = false) {
            this.alert.type = type;
            this.alert.title = title;
            this.alert.message = message;
            this.alert.showTroubleshoot = showTroubleshoot;
            this.alert.show = true;
            if (type === 'success') {
                setTimeout(() => {
                    this.alert.show = false;
                }, 6000);
            }
        },

        async saveConfig() {
            this.isSaving = true;
            try {
                const res = await fetch("{{ route('simpati.update_config') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.form)
                });
                const data = await res.json();
                if (data.success) {
                    this.showAlert('success', 'Konfigurasi Disimpan', data.message);
                } else {
                    this.showAlert('error', 'Gagal Menyimpan', data.message || 'Terjadi kesalahan.');
                }
            } catch (err) {
                this.showAlert('error', 'Kesalahan Server', err.message);
            } finally {
                this.isSaving = false;
            }
        },

        async testConnection() {
            this.isTesting = true;
            try {
                const url = new URL("{{ route('simpati.test') }}", window.location.origin);
                url.searchParams.append('base_url', this.form.base_url);
                url.searchParams.append('api_key', this.form.api_key);

                const res = await fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });
                const data = await res.json();
                if (data.success) {
                    this.connectionStatus = 'online';
                    this.showAlert('success', 'Koneksi Berhasil!', data.message + ' (Ditemukan ' + data.data_count + ' data pegawai aktif)', false);
                } else {
                    this.connectionStatus = 'offline';
                    this.showAlert('error', 'Koneksi ke SIMPATI Gagal', data.message, true);
                }
            } catch (err) {
                this.connectionStatus = 'offline';
                this.showAlert('error', 'Koneksi Gagal', 'Tidak dapat menghubungi server lokal: ' + err.message, true);
            } finally {
                this.isTesting = false;
            }
        },

        async syncData() {
            const satkerLabel = this.satkerLabel(this.selectedSatker);
            if (!confirm('Jalankan sinkronisasi langsung dari SIMPATI API untuk ' + satkerLabel + '?')) {
                return;
            }
            this.isSyncing = true;
            try {
                const res = await fetch("{{ route('simpati.sync') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        base_url: this.form.base_url,
                        api_key: this.form.api_key,
                        id_satker: this.selectedSatker
                    })
                });
                const data = await res.json();
                if (data.success) {
                    this.connectionStatus = 'online';
                    let msg = 'Sinkronisasi berhasil! ' + data.pegawai_total + ' Pegawai dan ' + data.tim_total + ' Tim Kerja diperbarui.';
                    if (data.pindah_satker_count > 0) {
                        msg += ' Perhatian: Terdeteksi ' + data.pindah_satker_count + ' pegawai Pindah SATKER. Notifikasi sistem telah dikirim ke Admin.';
                        this.showAlert('warning', 'Sinkronisasi Selesai (Ada Mutasi)', msg, false);
                    } else {
                        this.showAlert('success', 'Sinkronisasi Berhasil!', msg, false);
                    }
                    setTimeout(() => {
                        const u = new URL(window.location.href);
                        u.searchParams.set('satker', this.selectedSatker);
                        window.location.href = u.toString();
                    }, 1500);
                } else {
                    this.showAlert('error', 'Sinkronisasi Gagal', data.message || 'Gagal mengambil data dari SIMPATI API.', true);
                }
            } catch (err) {
                this.showAlert('error', 'Gagal Memproses Sinkronisasi', err.message, true);
            } finally {
                this.isSyncing = false;
            }
        },

        async syncMockData() {
            this.isSyncing = true;
            try {
                const res = await fetch("{{ route('simpati.sync_mock') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        id_satker: this.selectedSatker
                    })
                });
                const data = await res.json();
                if (data.success) {
                    let msg = 'Sinkronisasi simulasi BPS berhasil! ' + data.pegawai_total + ' Pegawai dan ' + data.tim_total + ' Tim Kerja masuk ke database.';
                    if (data.pindah_satker_count > 0) {
                        msg += ' Perhatian: Terdeteksi ' + data.pindah_satker_count + ' pegawai Pindah SATKER (Notifikasi admin dibuat).';
                        this.showAlert('warning', 'Simulasi Selesai (Ada Mutasi)', msg, false);
                    } else {
                        this.showAlert('success', 'Simulasi Selesai', msg, false);
                    }
                    setTimeout(() => {
                        const u = new URL(window.location.href);
                        u.searchParams.set('satker', this.selectedSatker);
                        window.location.href = u.toString();
                    }, 1500);
                } else {
                    this.showAlert('error', 'Simulasi Gagal', data.message || 'Terjadi kesalahan.', false);
                }
            } catch (err) {
                this.showAlert('error', 'Kesalahan', err.message, false);
            } finally {
                this.isSyncing = false;
            }
        },

        async openQrNametag(userId) {
            try {
                const res = await fetch("/admin/simpati/qr-nametag/" + userId, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });
                const data = await res.json();
                if (data.success) {
                    this.modalNametag.user = data.user;
                    this.modalNametag.qr_svg = data.qr_svg;
                    this.modalNametag.show = true;
                } else {
                    alert('Gagal memuat QR Nametag');
                }
            } catch (e) {
                alert('Terjadi kesalahan memuat QR: ' + e.message);
            }
        },

        openEditPegawai(p) {
            this.modalEditPegawai.form = {
                id: p.id,
                nama_lengkap: p.nama_lengkap || '',
                email: p.email || '',
                nm_jabatan: p.nm_jabatan || '',
                id_satker: p.id_satker || '7400',
                tim_utama: (p.tims && p.tims.length > 0) ? p.tims[0] : ''
            };
            this.modalEditPegawai.show = true;
        },

        async submitEditPegawai() {
            this.modalEditPegawai.isSaving = true;
            try {
                const id = this.modalEditPegawai.form.id;
                const res = await fetch("{{ url('/admin/simpati/pegawai') }}/" + id + "/update", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.modalEditPegawai.form)
                });
                const data = await res.json();
                if (data.success) {
                    this.modalEditPegawai.show = false;
                    this.showAlert('success', 'Berhasil', data.message);
                    const item = this.pegawaiList.find(x => x.id === id);
                    if (item) {
                        item.nama_lengkap = this.modalEditPegawai.form.nama_lengkap;
                        item.email = this.modalEditPegawai.form.email;
                        item.nm_jabatan = this.modalEditPegawai.form.nm_jabatan;
                        item.id_satker = this.modalEditPegawai.form.id_satker;
                        item.nm_satker = this.satkerLabel(this.modalEditPegawai.form.id_satker);
                        if (this.modalEditPegawai.form.tim_utama) {
                            item.tims = [this.modalEditPegawai.form.tim_utama];
                        }
                    }
                } else {
                    alert(data.message || 'Gagal menyimpan perubahan.');
                }
            } catch (e) {
                alert('Terjadi kesalahan: ' + e.message);
            } finally {
                this.modalEditPegawai.isSaving = false;
            }
        },

        async deletePegawai(id, nama) {
            if (!confirm(`Apakah Anda yakin ingin menghapus data pegawai "${nama}" dari database SIKEREN?`)) {
                return;
            }
            try {
                const res = await fetch("{{ url('/admin/simpati/pegawai') }}/" + id, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });
                const data = await res.json();
                if (data.success) {
                    this.pegawaiList = this.pegawaiList.filter(x => x.id !== id);
                    this.stats.totalPegawai = Math.max(0, this.stats.totalPegawai - 1);
                    this.showAlert('success', 'Dihapus', data.message);
                } else {
                    alert(data.message || 'Gagal menghapus data pegawai.');
                }
            } catch (e) {
                alert('Terjadi kesalahan: ' + e.message);
            }
        },

        filteredProyeks() {
            let list = this.proyekList || [];
            if (this.proyekSelectedTim) {
                list = list.filter(p => p.nm_tim === this.proyekSelectedTim);
            }
            if (this.proyekPjFilter === 'ada') {
                list = list.filter(p => p.pj_nama && p.pj_nama.trim() !== '');
            } else if (this.proyekPjFilter === 'belum') {
                list = list.filter(p => !p.pj_nama || p.pj_nama.trim() === '');
            }
            if (this.proyekSearch.trim() !== '') {
                const q = this.proyekSearch.toLowerCase().trim();
                list = list.filter(p => 
                    (p.namaproyek && p.namaproyek.toLowerCase().includes(q)) ||
                    (p.proyekid && p.proyekid.toLowerCase().includes(q)) ||
                    (p.nm_tim && p.nm_tim.toLowerCase().includes(q)) ||
                    (p.pj_nama && p.pj_nama.toLowerCase().includes(q))
                );
            }
            return list;
        },

        paginatedProyeks() {
            const start = (this.proyekPage - 1) * this.proyekPerPage;
            return this.filteredProyeks().slice(start, start + this.proyekPerPage);
        },

        totalProyekPages() {
            return Math.ceil(this.filteredProyeks().length / this.proyekPerPage) || 1;
        },

        async openAnggotaModal(p) {
            this.modalAnggota.proyek = p;
            this.modalAnggota.search = '';
            this.modalAnggota.anggotaList = [];
            this.modalAnggota.isLoading = true;
            this.modalAnggota.show = true;

            try {
                const res = await fetch("{{ url('/admin/simpati/proyek') }}/" + encodeURIComponent(p.proyekid) + "/anggota", {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });
                const data = await res.json();
                if (data.success) {
                    this.modalAnggota.anggotaList = data.anggota;
                }
            } catch (e) {
                console.error('Gagal memuat anggota:', e);
            } finally {
                this.modalAnggota.isLoading = false;
            }
        },

        filteredModalAnggota() {
            if (!this.modalAnggota.search.trim()) {
                return this.modalAnggota.anggotaList;
            }
            const q = this.modalAnggota.search.toLowerCase().trim();
            return this.modalAnggota.anggotaList.filter(m =>
                (m.nama_lengkap && m.nama_lengkap.toLowerCase().includes(q)) ||
                (m.niplama && m.niplama.toLowerCase().includes(q))
            );
        },

        async openPjModal(p) {
            this.modalPj.proyek = p;
            this.modalPj.pj_nama = p.pj_nama || '';
            this.modalPj.pj_nip = p.pj_nip || '';
            this.modalPj.anggotaOptions = [];
            this.modalPj.show = true;

            try {
                const res = await fetch("{{ url('/admin/simpati/proyek') }}/" + encodeURIComponent(p.proyekid) + "/anggota", {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });
                const data = await res.json();
                if (data.success) {
                    this.modalPj.anggotaOptions = data.anggota;
                }
            } catch (e) {
                console.error(e);
            }
        },

        onSelectPjAnggota(event) {
            const val = event.target.value;
            if (!val) return;
            const parts = val.split('|||');
            this.modalPj.pj_nama = parts[0];
            this.modalPj.pj_nip = parts[1] || '';
        },

        async assignPjFromAnggota(m) {
            if (!this.modalAnggota.proyek) return;
            const proyek = this.modalAnggota.proyek;
            if (!confirm(`Tetapkan "${m.nama_lengkap}" sebagai 1 Penanggung Jawab (PJ) resmi untuk proyek "${proyek.namaproyek}"?`)) {
                return;
            }

            try {
                const res = await fetch("{{ url('/admin/simpati/proyek') }}/" + proyek.id + "/pj", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        pj_nama: m.nama_lengkap,
                        pj_nip: m.niplama || ''
                    })
                });
                const data = await res.json();
                if (data.success) {
                    proyek.pj_nama = m.nama_lengkap;
                    proyek.pj_nip = m.niplama || '';
                    const item = this.proyekList.find(x => x.id === proyek.id);
                    if (item) {
                        item.pj_nama = m.nama_lengkap;
                        item.pj_nip = m.niplama || '';
                    }
                    this.recalcProyekStats();
                    this.showAlert('success', 'PJ Ditetapkan', data.message);
                } else {
                    alert(data.message || 'Gagal menyimpan PJ.');
                }
            } catch (e) {
                alert('Terjadi kesalahan: ' + e.message);
            }
        },

        async submitSavePj() {
            if (!this.modalPj.proyek) return;
            this.modalPj.isSaving = true;

            try {
                const res = await fetch("{{ url('/admin/simpati/proyek') }}/" + this.modalPj.proyek.id + "/pj", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        pj_nama: this.modalPj.pj_nama,
                        pj_nip: this.modalPj.pj_nip
                    })
                });
                const data = await res.json();
                if (data.success) {
                    this.modalPj.proyek.pj_nama = data.pj_nama;
                    this.modalPj.proyek.pj_nip = data.pj_nip;
                    const item = this.proyekList.find(x => x.id === this.modalPj.proyek.id);
                    if (item) {
                        item.pj_nama = data.pj_nama;
                        item.pj_nip = data.pj_nip;
                    }
                    this.recalcProyekStats();
                    this.modalPj.show = false;
                    this.showAlert('success', 'Berhasil', data.message);
                } else {
                    alert(data.message || 'Gagal menyimpan PJ');
                }
            } catch (e) {
                alert('Terjadi kesalahan: ' + e.message);
            } finally {
                this.modalPj.isSaving = false;
            }
        },

        recalcProyekStats() {
            let ada = 0;
            this.proyekList.forEach(p => {
                if (p.pj_nama && p.pj_nama.trim() !== '') {
                    ada++;
                }
            });
            this.proyekStats.totalProyekAdaPj = ada;
            this.proyekStats.totalProyekBelumPj = Math.max(0, this.proyekStats.totalProyek - ada);
        },

        async syncExcel() {
            if (!confirm('Apakah Anda ingin menyinkronkan ulang database proyek dan anggota dari file Excel master di server?')) {
                return;
            }
            this.isImportingExcel = true;
            try {
                const res = await fetch("{{ route('simpati.proyek.import') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });
                const data = await res.json();
                if (data.success) {
                    this.showAlert('success', 'Sinkronisasi Berhasil', data.message);
                    setTimeout(() => {
                        window.location.reload();
                    }, 1200);
                } else {
                    this.showAlert('error', 'Gagal Sinkronisasi', data.message);
                }
            } catch (e) {
                this.showAlert('error', 'Kesalahan', e.message);
            } finally {
                this.isImportingExcel = false;
            }
        }
    };
}
</script>
@endpush
@endsection
