@extends('layouts.app')

@section('title', 'Integrasi SIMPATI API - Sikeren')
@section('header_title', 'Integrasi SIMPATI API')

@push('styles')
<style>
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
    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 space-y-4">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 border-b border-gray-100 pb-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-lg">
                    🏢
                </div>
                <div>
                    <h3 class="text-base font-bold text-gray-900">Diambil Sesuai Dari SATKER</h3>
                    <p class="text-xs text-gray-400">Pilih unit kerja BPS untuk memfilter data SDM dan penugasan</p>
                </div>
            </div>

            <form method="GET" action="{{ route('simpati.index') }}" class="flex flex-wrap items-center gap-3">
                <select name="satker" 
                        onchange="this.form.submit()"
                        x-model="selectedSatker"
                        class="bg-gray-50 border border-gray-200 text-gray-800 text-xs font-semibold rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                    @foreach($satkers as $code => $name)
                        <option value="{{ $code }}" {{ $selectedSatker == $code ? 'selected' : '' }}>
                            {{ $name }}
                        </option>
                    @endforeach
                </select>
                <button type="submit" class="px-3.5 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold transition">
                    Terapkan
                </button>
            </form>
        </div>

        {{-- Statistik Alur SDM --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-1">
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-gray-500">Total SDM Pegawai</span>
                    <span class="text-base">👔</span>
                </div>
                <div class="text-2xl font-black text-slate-800 mt-2">{{ $totalPegawai }}</div>
                <span class="text-[11px] text-gray-400 mt-1 block">Tersinkron di Satker ini</span>
            </div>

            <div class="p-4 rounded-2xl bg-indigo-50 border border-indigo-100">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-indigo-700">Database Tim Kerja</span>
                    <span class="text-base">👥</span>
                </div>
                <div class="text-2xl font-black text-indigo-700 mt-2">{{ $totalTimKerja }}</div>
                <span class="text-[11px] text-indigo-500/80 mt-1 block">Struktur Tim Aktif</span>
            </div>

            <div class="p-4 rounded-2xl bg-purple-50 border border-purple-100">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-purple-700">Pegawai Multi-Tim</span>
                    <span class="text-base">🔄</span>
                </div>
                <div class="text-2xl font-black text-purple-700 mt-2">{{ $totalMultiTim }}</div>
                <span class="text-[11px] text-purple-500/80 mt-1 block">Anggota di &gt; 1 Tim</span>
            </div>

            <div class="p-4 rounded-2xl {{ $totalPindah > 0 ? 'bg-amber-50 border-amber-200 text-amber-900' : 'bg-emerald-50 border-emerald-100 text-emerald-900' }}">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold {{ $totalPindah > 0 ? 'text-amber-800' : 'text-emerald-700' }}">Pindah SATKER</span>
                    <span class="text-base">{{ $totalPindah > 0 ? '⚠️' : '✅' }}</span>
                </div>
                <div class="text-2xl font-black {{ $totalPindah > 0 ? 'text-amber-700' : 'text-emerald-700' }} mt-2">
                    {{ $totalPindah }}
                </div>
                <span class="text-[11px] {{ $totalPindah > 0 ? 'text-amber-700 font-semibold' : 'text-emerald-600' }} mt-1 block">
                    {{ $totalPindah > 0 ? 'Perlu Verifikasi Status!' : 'Semua Status Normal' }}
                </span>
            </div>
        </div>
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
                    <p class="font-bold text-rose-800">💡 Langkah Alternatif:</p>
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
                <span class="px-2 py-0.5 text-xs rounded-full bg-blue-100 text-blue-700">{{ $totalPegawai }}</span>
            </button>
            <button @click="activeTab = 'tim'"
                    :class="activeTab === 'tim' ? 'border-blue-600 text-blue-600 font-bold' : 'border-transparent text-gray-500 hover:text-gray-700 font-medium'"
                    class="pb-3 border-b-2 text-sm flex items-center gap-2 transition">
                <span>Database Tim Kerja</span>
                <span class="px-2 py-0.5 text-xs rounded-full bg-indigo-100 text-indigo-700">{{ $totalTimKerja }}</span>
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
                        <span>🧪 Sinkronkan Simulasi BPS</span>
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto rounded-2xl border border-gray-100">
                <table class="w-full text-left text-xs text-gray-600">
                    <thead class="bg-gray-50 text-gray-700 uppercase font-bold text-[11px] border-b border-gray-100">
                        <tr>
                            <th class="px-4 py-3.5">Pegawai & Identitas</th>
                            <th class="px-4 py-3.5">Jabatan & Satker</th>
                            <th class="px-4 py-3.5">Tim Kerja (Multi-Tim)</th>
                            <th class="px-4 py-3.5 text-center">Status Kepegawaian</th>
                            <th class="px-4 py-3.5 text-center">Aksi (QR Nametag)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($syncedPegawai as $p)
                            <tr class="hover:bg-gray-50/70 transition">
                                <td class="px-4 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-blue-100 text-blue-700 font-bold flex items-center justify-center shrink-0">
                                            {{ strtoupper(substr($p->nama_lengkap ?? 'U', 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="font-bold text-gray-900 text-sm">{{ $p->nama_lengkap }}</div>
                                            <div class="text-[11px] text-gray-400 font-mono">
                                                NIP: {{ $p->formatted_nip }} • {{ $p->email }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3.5">
                                    <div class="font-semibold text-gray-800">{{ $p->nm_jabatan ?: 'Pegawai BPS' }}</div>
                                    <div class="text-[11px] text-gray-400">{{ $p->nm_satker ?: ('Satker ' . ($p->id_satker ?: '7400')) }}</div>
                                </td>
                                <td class="px-4 py-3.5">
                                    @if(!empty($p->tims) && count($p->tims) > 0)
                                        <div class="flex flex-wrap gap-1.5 items-center">
                                            @foreach($p->tims as $timName)
                                                <span class="px-2 py-0.5 rounded-lg bg-blue-50 text-blue-700 border border-blue-100 text-[11px] font-medium">
                                                    {{ $timName }}
                                                </span>
                                            @endforeach
                                            @if(count($p->tims) > 1)
                                                <span class="px-1.5 py-0.5 rounded-md bg-purple-100 text-purple-700 text-[10px] font-bold" title="Pegawai tergabung di lebih dari 1 tim kerja">
                                                    Multi-Tim ({{ count($p->tims) }})
                                                </span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-gray-400 italic text-[11px]">- Belum ada tim -</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 text-center">
                                    @if(($p->is_pindahsatker ?? 0) == 1)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-amber-100 text-amber-800 text-[11px] font-bold border border-amber-200" title="Pegawai terdeteksi pindah/mutasi Satker dari data SIMPATI">
                                            <span>⚠️</span> Pindah SATKER
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800 text-[11px] font-semibold">
                                            <span>✓</span> Aktif
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 text-center">
                                    <button @click="openQrNametag({{ $p->id }})" 
                                            type="button"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 text-xs font-bold transition shadow-2xs">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                                        </svg>
                                        <span>QR Nametag</span>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-gray-400">
                                    Belum ada data pegawai di Satker ini. Klik <strong>"Sinkronkan Sesuai SATKER"</strong> atau <strong>"Sinkronkan Simulasi BPS"</strong> untuk menarik data.
                                </td>
                            </tr>
                        @endforelse
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
                    <span>➕ Buat Kegiatan dari Database Tim</span>
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
                                        <span>👤</span>
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
                    <span class="text-lg">🪪</span>
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
                        <span>🖨️ Cetak Nametag</span>
                    </button>
                </div>
            </div>
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
        showKey: false,
        connectionStatus: 'unknown',
        selectedSatker: '{{ $selectedSatker }}',
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
            const satkerLabel = this.selectedSatker === 'all' ? 'Semua Satker' : 'Satker ' + this.selectedSatker;
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
                        msg += ' ⚠️ Terdeteksi ' + data.pindah_satker_count + ' pegawai Pindah SATKER. Notifikasi sistem telah dikirim ke Admin.';
                        this.showAlert('warning', 'Sinkronisasi Selesai (Ada Mutasi)', msg, false);
                    } else {
                        this.showAlert('success', 'Sinkronisasi Berhasil!', msg, false);
                    }
                    setTimeout(() => window.location.reload(), 1500);
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
                        msg += ' ⚠️ Terdeteksi ' + data.pindah_satker_count + ' pegawai Pindah SATKER (Notifikasi admin dibuat).';
                        this.showAlert('warning', 'Simulasi Selesai (Ada Mutasi)', msg, false);
                    } else {
                        this.showAlert('success', 'Simulasi Selesai', msg, false);
                    }
                    setTimeout(() => window.location.reload(), 1500);
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
        }
    };
}
</script>
@endpush
@endsection
