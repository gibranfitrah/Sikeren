@extends('layouts.app')

@section('title', 'Agenda Rapat - Sikeren')
@section('header_title', 'Agenda Rapat')

@section('content')
@php
    $currentUser = Auth::user();
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

<div class="max-w-6xl mx-auto space-y-6 pb-12" x-data="{ 
    activeTab: '{{ request('tab', ((isset($errors) && $errors->any()) ? 'form' : 'daftar')) }}',
    rapatSearch: '',
    approvalModal: { open: false, id: null, text: '', pj: '', jenis: 'Rapat' }
}">

    {{-- ==========================================
        PAGE HEADER & TAB CONTROLS
    =========================================== --}}
    <div class="bg-white p-6 rounded-2xl border border-gray-200/80 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-md text-[11px] font-bold uppercase tracking-wider bg-blue-50 text-blue-700 border border-blue-100">
                    Manajemen Rapat
                </span>
                <span class="text-xs text-gray-400">•</span>
                <span class="text-xs text-gray-500 font-medium">BPS Provinsi Sulawesi Tenggara</span>
            </div>
            <h2 class="text-xl font-bold text-gray-900 mt-1">Agenda Rapat</h2>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <button type="button" 
                    @click="activeTab = 'daftar'"
                    :class="activeTab === 'daftar' ? 'bg-blue-600 text-white shadow-xs' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                    class="px-4 py-2.5 text-xs font-bold rounded-xl transition flex items-center gap-2 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <span>Daftar Agenda Rapat</span>
                <span :class="activeTab === 'daftar' ? 'bg-white/20 text-white' : 'bg-gray-200 text-gray-700'" class="px-2 py-0.5 rounded-full text-[10px] font-extrabold ml-1">
                    {{ count($rapatList ?? []) }}
                </span>
            </button>

            <button type="button" 
                    @click="activeTab = 'form'"
                    :class="activeTab === 'form' ? 'bg-blue-600 text-white shadow-xs' : 'bg-white border border-gray-300 text-gray-700 hover:bg-gray-50'"
                    class="px-4 py-2.5 text-xs font-bold rounded-xl transition flex items-center gap-2 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Buat Rapat Baru</span>
            </button>
        </div>
    </div>

    {{-- ==========================================
        PANEL TAB 1: DAFTAR AGENDA RAPAT
    =========================================== --}}
    <div x-show="activeTab === 'daftar'" x-cloak class="space-y-4">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            {{-- Toolbar: Search & Action --}}
            <div class="p-4 sm:p-5 border-b border-gray-100 bg-gray-50/50 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="relative w-full sm:max-w-md">
                    <input type="text" 
                           x-model="rapatSearch" 
                           placeholder="Cari topik rapat, tanggal, pimpinan, notulis, ruangan..." 
                           class="w-full pl-9 pr-3.5 py-2 text-xs rounded-xl border border-gray-200 bg-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all">
                    <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
            </div>

            {{-- Table --}}
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-left">
                    <thead class="bg-gray-50/80 text-[11px] font-bold text-gray-600 uppercase tracking-wider">
                        <tr>
                            <th scope="col" class="px-6 py-4">Topik & Informasi Rapat</th>
                            <th scope="col" class="px-6 py-4">Petugas Rapat</th>
                            <th scope="col" class="px-6 py-4">Tahapan Alur Rapat</th>
                            <th scope="col" class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100 text-xs">
                        @forelse ($rapatList as $post)
                        @php
                            $canAccessRapat = $currentUser && $currentUser->canAccessDetailKegiatan($post);
                            $isPemimpinThisRapat = $currentUser && (
                                strcasecmp($currentUser->nama_lengkap ?? '', $post->pemimpin ?? '') === 0 ||
                                strcasecmp($currentUser->username ?? '', $post->pemimpin ?? '') === 0 ||
                                (isset($currentUser->level) && strtolower($currentUser->level) === 'admin')
                            );
                            $dateStr = \Carbon\Carbon::parse($post->start_date)->translatedFormat('d M Y');
                            $searchHaystack = strtolower(($post->text ?? '') . ' ' . $dateStr . ' ' . ($post->tempat ?? '') . ' ' . ($post->pemimpin ?? '') . ' ' . ($post->notulis ?? ''));
                        @endphp
                        <tr class="hover:bg-slate-50/60 transition-colors"
                            x-show="!rapatSearch || '{{ addslashes($searchHaystack) }}'.includes(rapatSearch.toLowerCase())">
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
                                            <span>{{ $dateStr }}</span>
                                        </span>

                                        <span class="meta-chip font-mono">
                                            <svg class="w-3.5 h-3.5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            <span>{{ substr($post->start_jam, 0, 5) }} - {{ !empty($post->end_jam) ? substr($post->end_jam, 0, 5) . ' WITA' : 'Selesai' }}</span>
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
                                                class="px-2.5 py-1.5 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-xl text-xs transition inline-flex items-center gap-1 shadow-xs cursor-pointer" 
                                                title="Tinjau dan Berikan Persetujuan Rapat">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            <span>Persetujuan</span>
                                        </button>
                                    @endif

                                    @if($canAccessRapat)
                                        <a href="{{ url('daftarkegiatan/' . ($post->id_kegiatan ?? $post->id)) }}" class="px-3.5 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 font-bold rounded-xl border border-blue-200 text-xs transition inline-flex items-center gap-1">
                                            <span>Detail</span>
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
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
                                <div class="max-w-xs mx-auto text-center space-y-3">
                                    <div class="w-12 h-12 rounded-2xl bg-gray-50 text-gray-300 flex items-center justify-center mx-auto">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                        </svg>
                                    </div>
                                    <p class="text-xs font-medium text-gray-500">Belum ada agenda rapat yang terdaftar.</p>
                                    <button type="button" @click="activeTab = 'form'" class="inline-flex items-center px-3.5 py-1.5 bg-blue-600 text-white text-xs font-semibold rounded-xl hover:bg-blue-700 transition cursor-pointer">
                                        Buat Rapat Sekarang
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 bg-gray-50/50 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500">
                <span>Total: <strong class="text-gray-800">{{ count($rapatList ?? []) }}</strong> agenda rapat</span>
            </div>
        </div>
    </div>

    {{-- ==========================================
        PANEL TAB 2: FORM BUAT RAPAT BARU
    =========================================== --}}
    <div x-show="activeTab === 'form'" x-cloak class="space-y-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <button type="button" 
                        @click="activeTab = 'daftar'"
                        class="p-2.5 bg-white hover:bg-gray-100 text-gray-600 rounded-xl border border-gray-200 shadow-xs transition-colors cursor-pointer" 
                        title="Kembali ke Daftar Agenda Rapat">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </button>
                <div>
                    <h2 class="text-xl font-bold text-gray-800">Formulir Buat Rapat Baru</h2>
                    <p class="text-xs text-gray-500 mt-0.5">Rapat adalah bagian spesifik dari sebuah kegiatan (atau koordinasi mandiri) yang membutuhkan pimpinan, notulis, penentuan ruangan/zoom, dan notulen.</p>
                </div>
            </div>

            {{-- Minimalist Workflow Step Indicator --}}
            <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-white border border-gray-200 rounded-xl text-xs font-semibold text-gray-600 shadow-xs">
                <span class="inline-flex items-center gap-1.5 text-blue-600 font-bold">
                    <span class="w-5 h-5 rounded-full bg-blue-600 text-white text-[10px] flex items-center justify-center font-bold">1</span>
                    <span>1. Buat Rapat</span>
                </span>
                <span class="text-gray-300">&rarr;</span>
                <span class="text-blue-600 font-semibold">2. Booking Ruangan & Zoom</span>
                <span class="text-gray-300">&rarr;</span>
                <span class="text-gray-500">3. Persetujuan</span>
                <span class="text-gray-300">&rarr;</span>
                <span class="text-gray-500">4. Presensi QR</span>
                <span class="text-gray-300">&rarr;</span>
                <span class="text-gray-500">5. Notulen</span>
            </div>
        </div>

        {{-- ALERT NOTIFICATIONS --}}
        @if (session('success'))
        <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded-r-xl shadow-xs">
            <div class="flex items-center">
                <svg class="h-5 w-5 text-green-500 mr-3 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <p class="text-xs font-medium text-green-800">{{ session('success') }}</p>
            </div>
        </div>
        @endif

        @if (isset($errors) && $errors->any())
        <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-r-xl shadow-xs">
            <div class="flex items-start">
                <svg class="h-5 w-5 text-red-500 mr-3 flex-shrink-0 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <div>
                    <h4 class="text-xs font-bold text-red-800">Terdapat kesalahan pengisian formulir:</h4>
                    <ul class="list-disc list-inside mt-1 text-xs text-red-700 space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
        @endif

        {{-- MAIN FORM CARD --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <form action="{{ route('rapat.store') }}" method="POST" enctype="multipart/form-data" id="formBuatRapat">
                @csrf
                <input type="hidden" name="jenis" value="Rapat">
                <input type="hidden" name="tes2" value="{{ $id + 1 }}">

            {{-- ---------------------------------------------------------
                BAGIAN 1: INFORMASI & AGENDA RAPAT
            ---------------------------------------------------------- --}}
            <div class="p-6 sm:p-8 space-y-6">
                <div class="flex items-center gap-3 pb-3 border-b border-gray-100">
                    <span class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 font-bold text-xs flex items-center justify-center border border-blue-200">1</span>
                    <div>
                        <h3 class="text-sm font-bold text-gray-800">Informasi & Agenda Rapat</h3>
                        <p class="text-xs text-gray-500">Tentukan kategori, topik utama, serta agenda bahasan rapat.</p>
                    </div>
                </div>

                {{-- PILIH DARI MASTER PROYEK SIMPATI (OTOMATISASI RAPAT) --}}
                <div class="p-4 bg-gradient-to-r from-blue-50/90 to-indigo-50/90 rounded-2xl border border-blue-200 shadow-2xs space-y-2">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-bold text-blue-900 uppercase tracking-wider flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                            <span>Pilih dari Master Proyek SIMPATI (Otomatisasi)</span>
                        </label>
                        <span class="text-[10px] font-bold text-blue-700 bg-white/90 px-2.5 py-0.5 rounded-full border border-blue-200">
                            Auto-fill Topik, PJ, Pemimpin & Rekomendasi Peserta
                        </span>
                    </div>
                    <select id="selectMasterProyekRapat"
                            onchange="handleSelectMasterProyekRapat(this)"
                            class="w-full px-3 py-2.5 bg-white border border-blue-300 rounded-xl text-xs text-gray-800 font-semibold focus:ring-2 focus:ring-blue-500 focus:border-blue-600 transition shadow-2xs">
                        <option value="">-- Pilih dari Master Kegiatan / Proyek SIMPATI (80 Proyek) --</option>
                        @foreach($masterProyeksByTim ?? [] as $timName => $proyeks)
                            <optgroup label="Tim: {{ $timName }}">
                                @foreach($proyeks as $prj)
                                    <option value="{{ $prj->proyekid }}"
                                            data-nama="{{ $prj->namaproyek }}"
                                            data-tim="{{ $prj->nm_tim }}"
                                            data-pj-nama="{{ $prj->pj_nama ?? '' }}"
                                            data-pj-nip="{{ $prj->pj_nip ?? '' }}"
                                            data-anggota='@json($prj->anggota->map(fn($a) => ["nama" => $a->nama_lengkap, "nip" => $a->niplama]))'>
                                        {{ $prj->namaproyek }} {{ $prj->pj_nama ? '(PJ: ' . $prj->pj_nama . ')' : '(Belum Ada PJ)' }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                    <div id="proyekFeedbackRapat" class="hidden p-2.5 rounded-xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-800 font-medium">
                        <div class="flex items-start gap-2">
                            <svg class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span id="proyekFeedbackRapatText"></span>
                        </div>
                    </div>
                </div>

                {{-- Kategori Rapat (Radio Selection) --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                        Kategori Rapat <span class="text-red-500">*</span>
                    </label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 max-w-xl">
                        <label class="flex items-center gap-3 p-3 rounded-xl border border-gray-200 bg-gray-50/60 hover:bg-blue-50/40 cursor-pointer transition-all has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50/50 has-[:checked]:ring-1 has-[:checked]:ring-blue-500">
                            <input type="radio" name="is_kegiatan" value="1" id="kategoriTerkait" class="text-blue-600 focus:ring-blue-500 h-4 w-4" checked onchange="toggleKategoriRapat()">
                            <div>
                                <div class="text-xs font-bold text-gray-800">Rapat Bagian dari Kegiatan Tim</div>
                                <div class="text-[11px] text-gray-500">Rapat diselenggarakan sebagai bagian / tahapan spesifik dari master kegiatan</div>
                            </div>
                        </label>
                        <label class="flex items-center gap-3 p-3 rounded-xl border border-gray-200 bg-gray-50/60 hover:bg-blue-50/40 cursor-pointer transition-all has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50/50 has-[:checked]:ring-1 has-[:checked]:ring-blue-500">
                            <input type="radio" name="is_kegiatan" value="0" id="kategoriMandiri" class="text-blue-600 focus:ring-blue-500 h-4 w-4" onchange="toggleKategoriRapat()">
                            <div>
                                <div class="text-xs font-bold text-gray-800">Rapat Mandiri / Koordinasi Rutin</div>
                                <div class="text-[11px] text-gray-500">Rapat internal / koordinasi berkala mandiri tanpa terikat proyek kegiatan tertentu</div>
                            </div>
                        </label>
                    </div>
                </div>

                {{-- Master Kegiatan Dropdown (Conditional) --}}
                <div id="sectionRelasiKegiatan" class="p-4 bg-blue-50/40 rounded-xl border border-blue-100 space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="kegiatan_id" class="block text-xs font-bold text-gray-700 mb-1">
                                Pilih Master Kegiatan Tim <span class="text-red-500">*</span>
                            </label>
                            <select id="kegiatan_id" name="kegiatan_id" class="w-full text-xs border-gray-300 rounded-lg p-2.5 bg-white text-gray-800 shadow-xs focus:ring-blue-500 focus:border-blue-500 border" onchange="handleSelectMasterKegiatan(this)">
                                <option value="">-- Pilih Agenda Kegiatan --</option>
                                @foreach($kegiatans as $kg)
                                    <option value="{{ $kg->id }}" data-agenda="{{ $kg->agenda }}" data-sub="{{ $kg->sub_kegiatan ?? '' }}" data-pj="{{ $kg->pj ?? '' }}">
                                        {{ $kg->agenda }} (PJ: {{ $kg->pj ?? '-' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="sub_kegiatan" class="block text-xs font-bold text-gray-700 mb-1">
                                Sub Kegiatan Terkait
                            </label>
                            <input type="text" id="sub_kegiatan" name="sub_kegiatan" value="{{ old('sub_kegiatan') }}" placeholder="Contoh: Tahap Persiapan & Pelatihan Petugas" class="w-full text-xs border-gray-300 rounded-lg p-2.5 bg-white text-gray-800 shadow-xs focus:ring-blue-500 focus:border-blue-500 border">
                        </div>
                    </div>
                </div>

                {{-- Topik & Agenda Pembahasan --}}
                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label for="text" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                            Topik / Nama Rapat <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="text" name="text" value="{{ old('text') }}" placeholder="Contoh: Rapat Koordinasi Evaluasi Pendataan SAKERNAS 2026" required class="w-full text-xs border-gray-300 rounded-lg p-2.5 bg-gray-50 focus:bg-white text-gray-800 shadow-xs focus:ring-blue-500 focus:border-blue-500 border">
                        @error('text') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="agenda" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                            Agenda Pembahasan <span class="text-red-500">*</span>
                        </label>
                        <textarea id="agenda" name="agenda" rows="3" placeholder="Tuliskan poin-poin pembahasan rapat yang akan didiskusikan..." required class="w-full text-xs border-gray-300 rounded-lg p-2.5 bg-gray-50 focus:bg-white text-gray-800 shadow-xs focus:ring-blue-500 focus:border-blue-500 border">{{ old('agenda') }}</textarea>
                        @error('agenda') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            {{-- ---------------------------------------------------------
                BAGIAN 2: WAKTU & TEMPAT PELAKSANAAN
            ---------------------------------------------------------- --}}
            <div class="p-6 sm:p-8 space-y-6 bg-gray-50/40 border-t border-gray-200">
                <div class="flex items-center gap-3 pb-3 border-b border-gray-200">
                    <span class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 font-bold text-xs flex items-center justify-center border border-blue-200">2</span>
                    <div>
                        <h3 class="text-sm font-bold text-gray-800">Waktu & Tempat Pelaksanaan</h3>
                        <p class="text-xs text-gray-500">Jadwalkan tanggal, jam pelaksanaan, dan mode pertemuan rapat.</p>
                    </div>
                </div>

                {{-- Waktu Rapat (Tanggal Tunggal & Jam Mulai) --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="start_date" class="block text-xs font-bold text-gray-700 mb-1">
                            Tanggal Pelaksanaan Rapat <span class="text-red-500">*</span>
                        </label>
                        <input type="date" id="start_date" name="start_date" value="{{ old('start_date', date('Y-m-d')) }}" required class="w-full text-xs border-gray-300 rounded-lg p-2.5 bg-white text-gray-800 shadow-xs focus:ring-blue-500 focus:border-blue-500 border">
                    </div>
                    <div>
                        <label for="start_jam" class="block text-xs font-bold text-gray-700 mb-1">
                            Jam Mulai <span class="text-red-500">*</span>
                        </label>
                        <div class="flex items-center gap-2">
                            <input type="time" id="start_jam" name="start_jam" value="{{ old('start_jam', '08:30') }}" required class="w-full text-xs border-gray-300 rounded-lg p-2.5 bg-white text-gray-800 shadow-xs focus:ring-blue-500 focus:border-blue-500 border">
                            <span class="inline-flex items-center px-3 py-2.5 rounded-lg bg-gray-100 text-gray-600 font-semibold text-xs border border-gray-200 whitespace-nowrap shadow-xs">
                                s.d. Selesai
                            </span>
                        </div>
                        <p class="text-[11px] text-gray-400 mt-1">Rapat dijadwalkan dari jam mulai hingga selesai.</p>
                    </div>
                </div>

                {{-- Mode Pertemuan --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                        Mode Pertemuan <span class="text-red-500">*</span>
                    </label>
                    <div class="grid grid-cols-3 gap-3 max-w-xl">
                        <label class="flex items-center justify-center gap-2 p-2.5 rounded-xl border border-gray-200 bg-white hover:bg-blue-50/40 cursor-pointer text-xs font-semibold text-gray-700 text-center transition-all has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50/50 has-[:checked]:text-blue-700 has-[:checked]:ring-1 has-[:checked]:ring-blue-500">
                            <input type="radio" name="tipe_tempat" value="online" id="modeOnline" class="text-blue-600 focus:ring-blue-500" onchange="toggleModeTempat()">
                            <span>Online (Daring)</span>
                        </label>
                        <label class="flex items-center justify-center gap-2 p-2.5 rounded-xl border border-gray-200 bg-white hover:bg-blue-50/40 cursor-pointer text-xs font-semibold text-gray-700 text-center transition-all has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50/50 has-[:checked]:text-blue-700 has-[:checked]:ring-1 has-[:checked]:ring-blue-500">
                            <input type="radio" name="tipe_tempat" value="offline" id="modeOffline" class="text-blue-600 focus:ring-blue-500" checked onchange="toggleModeTempat()">
                            <span>Offline (Tatap Muka)</span>
                        </label>
                        <label class="flex items-center justify-center gap-2 p-2.5 rounded-xl border border-gray-200 bg-white hover:bg-blue-50/40 cursor-pointer text-xs font-semibold text-gray-700 text-center transition-all has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50/50 has-[:checked]:text-blue-700 has-[:checked]:ring-1 has-[:checked]:ring-blue-500">
                            <input type="radio" name="tipe_tempat" value="hybrid" id="modeHybrid" class="text-blue-600 focus:ring-blue-500" onchange="toggleModeTempat()">
                            <span>Hybrid (Campuran)</span>
                        </label>
                    </div>
                </div>

                {{-- Detail Lokasi (Dinamis) --}}
                <div class="p-4 bg-white rounded-xl border border-gray-200 space-y-4">
                    {{-- Link Online --}}
                    <div id="wrapperLinkOnline" class="hidden">
                        <label for="link_meeting" class="block text-xs font-bold text-gray-700 mb-1">
                            Tautan Meeting Online (Zoom / Google Meet / Teams)
                        </label>
                        <input type="url" id="link_meeting" name="link_meeting" value="{{ old('link_meeting') }}" placeholder="https://meet.google.com/xxx-xxxx-xxx atau https://zoom.us/j/..." class="w-full text-xs border-gray-300 rounded-lg p-2.5 bg-gray-50 focus:bg-white text-gray-800 shadow-xs focus:ring-blue-500 focus:border-blue-500 border">
                    </div>

                    {{-- Lokasi Offline --}}
                    <div id="wrapperRuangOffline" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="venue_id" class="block text-xs font-bold text-gray-700 mb-1">
                                Pilih Ruangan Rapat Kantor
                            </label>
                            <select id="venue_id" name="venue_id" class="w-full text-xs border-gray-300 rounded-lg p-2.5 bg-gray-50 focus:bg-white text-gray-800 shadow-xs focus:ring-blue-500 focus:border-blue-500 border" onchange="handleSelectVenue(this)">
                                <option value="">-- Pilih Ruangan Tersedia --</option>
                                @foreach($venues as $vn)
                                    <option value="{{ $vn->id }}" data-name="{{ $vn->name }}">
                                        {{ $vn->name }} (Kapasitas: {{ $vn->capacity ?? 'Std' }} Orang)
                                    </option>
                                @endforeach
                                <option value="custom">Lokasi / Ruangan Lainnya (Ketik Manual)</option>
                            </select>
                        </div>
                        <div>
                            <label for="tempat_offline" class="block text-xs font-bold text-gray-700 mb-1">
                                Nama Tempat / Lokasi Fisik
                            </label>
                            <input type="text" id="tempat_offline" name="tempat_offline" value="{{ old('tempat_offline', 'Aula Utama BPS') }}" placeholder="Tuliskan nama ruangan atau lokasi" class="w-full text-xs border-gray-300 rounded-lg p-2.5 bg-gray-50 focus:bg-white text-gray-800 shadow-xs focus:ring-blue-500 focus:border-blue-500 border">
                        </div>
                    </div>
                </div>
            </div>

            {{-- ---------------------------------------------------------
                BAGIAN 3: PETUGAS & PEMIMPIN RAPAT
            ---------------------------------------------------------- --}}
            <div class="p-6 sm:p-8 space-y-6 border-t border-gray-200">
                <div class="flex items-center gap-3 pb-3 border-b border-gray-100">
                    <span class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 font-bold text-xs flex items-center justify-center border border-blue-200">3</span>
                    <div>
                        <h3 class="text-sm font-bold text-gray-800">Petugas & Pemimpin Rapat</h3>
                        <p class="text-xs text-gray-500">Tentukan penanggung jawab persetujuan, notulis, dan dokumentasi rapat.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    {{-- Penanggung Jawab (PJ / Ketua Tim) --}}
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label for="penanggung_jawab" class="block text-xs font-bold text-gray-700">
                                Penanggung Jawab (PJ) <span class="text-red-500">*</span>
                            </label>
                            <span class="text-[10px] text-emerald-700 font-bold bg-emerald-50 px-1.5 py-0.5 rounded">Ketua Tim</span>
                        </div>
                        @php
                            $defaultPj = old('penanggung_jawab', (!Auth::user()->isAdmin() ? (Auth::user()->nama_lengkap ?? '') : ''));
                        @endphp
                        <select id="penanggung_jawab" name="penanggung_jawab" class="w-full text-xs border-gray-300 rounded-lg p-2.5 bg-gray-50 focus:bg-white text-gray-800 shadow-xs focus:ring-emerald-500 focus:border-emerald-500 border">
                            <option value="">-- Pilih Penanggung Jawab (PJ) --</option>
                            @if(isset($eligiblePJs) && $eligiblePJs->count() > 0)
                                <optgroup label="Pejabat, Ketua Tim, & Ahli Madya (Eligible PJ)">
                                    @foreach ($eligiblePJs as $u)
                                        <option value="{{ $u->nama_lengkap }}" {{ $defaultPj == $u->nama_lengkap ? 'selected' : '' }}>
                                            {{ $u->select_option_label }}
                                        </option>
                                    @endforeach
                                </optgroup>
                                <optgroup label="Pegawai BPS">
                                    @foreach ($allUsers->diff($eligiblePJs) as $u)
                                        <option value="{{ $u->nama_lengkap }}" {{ $defaultPj == $u->nama_lengkap ? 'selected' : '' }}>
                                            {{ $u->select_option_label }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @else
                                @foreach ($peserta as $p)
                                    <option value="{{ $p->nama_lengkap }}" {{ $defaultPj == $p->nama_lengkap ? 'selected' : '' }}>
                                        {{ $p->nama_lengkap }} ({{ $p->nipbaru ?? $p->niplama }})
                                    </option>
                                @endforeach
                            @endif
                        </select>
                        <div id="pjInfoContainer" class="hidden mt-1.5 px-2.5 py-1 bg-emerald-50 border border-emerald-200 rounded-lg text-[11px] text-emerald-800 font-medium">
                            <div class="flex items-center gap-1.5">
                                <svg width="14" height="14" style="width: 14px; height: 14px; min-width: 14px; min-height: 14px; max-width: 14px; max-height: 14px;" class="text-emerald-600 inline-block flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span id="pjInfoText" class="truncate">PJ otomatis terpilih</span>
                            </div>
                        </div>
                    </div>

                    {{-- Pemimpin Rapat --}}
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label for="pemimpin" class="block text-xs font-bold text-gray-700">
                                Pemimpin Rapat <span class="text-red-500">*</span>
                            </label>
                            <span class="text-[10px] text-blue-600 font-bold bg-blue-50 px-1.5 py-0.5 rounded">Approval</span>
                        </div>
                        @php
                            $defaultPemimpin = old('pemimpin', (!Auth::user()->isAdmin() ? (Auth::user()->nama_lengkap ?? '') : ''));
                        @endphp
                        <select id="pemimpin" name="pemimpin" required class="w-full text-xs border-gray-300 rounded-lg p-2.5 bg-gray-50 focus:bg-white text-gray-800 shadow-xs focus:ring-blue-500 focus:border-blue-500 border">
                            <option value="">-- Pilih Pemimpin Rapat --</option>
                            @if(isset($eligiblePJs) && $eligiblePJs->count() > 0)
                                <optgroup label="Pejabat, Ketua Tim, & Ahli Madya (Eligible PJ)">
                                    @foreach ($eligiblePJs as $u)
                                        <option value="{{ $u->nama_lengkap }}" {{ $defaultPemimpin == $u->nama_lengkap ? 'selected' : '' }}>
                                            {{ $u->select_option_label }}
                                        </option>
                                    @endforeach
                                </optgroup>
                                <optgroup label="Pegawai BPS">
                                    @foreach ($allUsers->diff($eligiblePJs) as $u)
                                        <option value="{{ $u->nama_lengkap }}" {{ $defaultPemimpin == $u->nama_lengkap ? 'selected' : '' }}>
                                            {{ $u->select_option_label }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @else
                                @foreach ($peserta as $p)
                                    <option value="{{ $p->nama_lengkap }}" {{ $defaultPemimpin == $p->nama_lengkap ? 'selected' : '' }}>
                                        {{ $p->nama_lengkap }} ({{ $p->nipbaru ?? $p->niplama }})
                                    </option>
                                @endforeach
                            @endif
                        </select>
                        @error('pemimpin') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- Notulis --}}
                    <div>
                        <label for="notulis" class="block text-xs font-bold text-gray-700 mb-1">
                            Notulis Rapat <span class="text-red-500">*</span>
                        </label>
                        <select id="notulis" name="notulis" required class="w-full text-xs border-gray-300 rounded-lg p-2.5 bg-gray-50 focus:bg-white text-gray-800 shadow-xs focus:ring-blue-500 focus:border-blue-500 border">
                            <option value="">-- Pilih Notulis Rapat --</option>
                            @foreach ($peserta as $p)
                                <option value="{{ $p->nama_lengkap }}" {{ old('notulis') == $p->nama_lengkap ? 'selected' : '' }}>
                                    {{ $p->nama_lengkap }}
                                </option>
                            @endforeach
                        </select>
                        @error('notulis') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- Tim Dokumentasi --}}
                    <div>
                        <label for="tim_dokumentasi" class="block text-xs font-bold text-gray-700 mb-1">
                            Tim Dokumentasi <span class="text-gray-400 font-normal">(Opsional)</span>
                        </label>
                        <select id="tim_dokumentasi" name="tim_dokumentasi" class="w-full text-xs border-gray-300 rounded-lg p-2.5 bg-gray-50 focus:bg-white text-gray-800 shadow-xs focus:ring-blue-500 focus:border-blue-500 border">
                            <option value="">-- Pilih Dokumentasi --</option>
                            @foreach ($peserta as $p)
                                <option value="{{ $p->nama_lengkap }}" {{ old('tim_dokumentasi') == $p->nama_lengkap ? 'selected' : '' }}>
                                    {{ $p->nama_lengkap }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- ---------------------------------------------------------
                BAGIAN 4: BERKAS & DOKUMEN RAPAT
            ---------------------------------------------------------- --}}
            <div class="p-6 sm:p-8 space-y-6 bg-gray-50/40 border-t border-gray-200">
                <div class="flex items-center gap-3 pb-3 border-b border-gray-200">
                    <span class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 font-bold text-xs flex items-center justify-center border border-blue-200">4</span>
                    <div>
                        <h3 class="text-sm font-bold text-gray-800">Berkas & Dokumen Pendukung</h3>
                        <p class="text-xs text-gray-500">Unggah berkas surat undangan resmi dan materi bahan rapat.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    {{-- Surat Undangan (PDF Wajib) --}}
                    <div class="p-4 bg-white rounded-xl border border-gray-200 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-gray-800 flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                Surat Undangan Rapat
                            </span>
                            <span class="text-[10px] font-bold bg-red-100 text-red-700 px-2 py-0.5 rounded">PDF (Wajib)</span>
                        </div>
                        <div>
                            <input type="file" name="undangan_file" id="undangan_file" accept=".pdf" class="w-full text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3.5 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
                        </div>
                        <div>
                            <input type="url" name="link" value="{{ old('link') }}" placeholder="Atau tautan dokumen cloud: https://..." class="w-full text-xs border-gray-300 rounded-lg p-2 bg-gray-50 text-gray-800 focus:bg-white focus:ring-blue-500 focus:border-blue-500 border">
                        </div>
                    </div>

                    {{-- Bahan Materi (Opsional) --}}
                    <div class="p-4 bg-white rounded-xl border border-gray-200 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-gray-800 flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"/>
                                </svg>
                                Materi / Bahan Tayang
                            </span>
                            <span class="text-[10px] font-semibold bg-gray-100 text-gray-600 px-2 py-0.5 rounded">Opsional</span>
                        </div>
                        <div>
                            <input type="file" name="materi_file" id="materi_file" accept=".pdf,.ppt,.pptx,.doc,.docx,.zip,.rar" class="w-full text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3.5 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200 cursor-pointer">
                        </div>
                        <div>
                            <input type="url" name="materi_link" value="{{ old('materi_link') }}" placeholder="Atau tautan Google Drive / OneDrive..." class="w-full text-xs border-gray-300 rounded-lg p-2 bg-gray-50 text-gray-800 focus:bg-white focus:ring-blue-500 focus:border-blue-500 border">
                        </div>
                    </div>
                </div>
            </div>

            {{-- ---------------------------------------------------------
                BAGIAN 5: DAFTAR PESERTA RAPAT
            ---------------------------------------------------------- --}}
            <div class="p-6 sm:p-8 space-y-6 border-t border-gray-200">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-gray-100">
                    <div class="flex items-center gap-3">
                        <span class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 font-bold text-xs flex items-center justify-center border border-blue-200">5</span>
                        <div>
                            <h3 class="text-sm font-bold text-gray-800">Daftar Peserta Rapat <span class="text-red-500">*</span></h3>
                            <p class="text-xs text-gray-500">Centang pegawai yang diundang untuk menghadiri rapat ini.</p>
                        </div>
                    </div>
                    <div class="inline-flex items-center px-3 py-1 bg-blue-50 border border-blue-200 rounded-lg text-xs font-bold text-blue-700" id="badgeCountPeserta">
                        0 Peserta Dipilih
                    </div>
                </div>

                {{-- Live Search Filter & Global Select All --}}
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="relative max-w-md flex-1">
                        <input type="text" id="filterPesertaNama" placeholder="Cari nama peserta rapat..." class="w-full text-xs pl-9 border-gray-300 rounded-lg p-2.5 bg-gray-50 focus:bg-white text-gray-800 shadow-xs focus:ring-blue-500 focus:border-blue-500 border">
                        <svg class="w-4 h-4 text-gray-400 absolute left-2.5 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <button type="button" onclick="selectAllGlobalPeserta(true)" class="text-xs font-bold text-blue-700 bg-blue-50 hover:bg-blue-100 px-3 py-2 rounded-xl border border-blue-200 transition shadow-2xs">
                            Pilih Semua
                        </button>
                        <button type="button" onclick="selectAllGlobalPeserta(false)" class="text-xs font-bold text-gray-600 bg-gray-100 hover:bg-gray-200 px-3 py-2 rounded-xl transition">
                            Batalkan Pilihan
                        </button>
                    </div>
                </div>

                {{-- Daftar Kelompok & Peserta --}}
                <div class="space-y-4 max-h-[420px] overflow-y-auto pr-1 border border-gray-200 rounded-xl p-3 bg-gray-50/50">
                    @forelse ($master_groups as $index => $category)
                    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden shadow-2xs grup-peserta-card">
                        <div class="px-4 py-2.5 bg-gray-100/70 border-b border-gray-200 flex items-center justify-between">
                            <span class="text-xs font-bold text-gray-700 uppercase tracking-wide">{{ $category->grup }}</span>
                            <label class="inline-flex items-center cursor-pointer select-none bg-white px-2.5 py-1 rounded-md border border-gray-300 text-[11px] font-semibold text-gray-700 hover:bg-gray-50 shadow-2xs">
                                <input type="checkbox" class="cb-select-group rounded border-gray-300 text-blue-600 focus:ring-blue-500 h-3.5 w-3.5" data-target="box-group-{{ $index }}">
                                <span class="ml-1.5">Pilih Semua</span>
                            </label>
                        </div>
                        <div class="p-3 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2" id="box-group-{{ $index }}">
                            @foreach ($groups as $participant)
                                @if ($participant->grup == $category->grup)
                                <label class="item-peserta flex items-center p-2 rounded-lg border border-transparent hover:border-blue-200 hover:bg-blue-50/40 cursor-pointer transition-all">
                                    <input type="checkbox" name="owners[]" value="{{ $participant->niplama }}" class="cb-peserta rounded border-gray-300 text-blue-600 focus:ring-blue-500 h-4 w-4" onchange="updateTotalPeserta()">
                                    <span class="ml-2.5 text-xs text-gray-800 font-medium truncate nama-peserta-label" title="{{ $participant->nama_lengkap }}">{{ $participant->nama_lengkap }}</span>
                                </label>
                                @endif
                            @endforeach
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-8 text-gray-400 text-xs">
                        Tidak ada kelompok peserta yang tersedia.
                    </div>
                    @endforelse
                </div>
                @error('owners') <span class="text-xs text-red-500 block">{{ $message }}</span> @enderror
            </div>

            {{-- ==========================================
                FORM ACTIONS (FOOTER)
            =========================================== --}}
            <div class="px-6 sm:p-8 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
                <button type="reset" class="px-5 py-2.5 bg-white border border-gray-300 hover:bg-gray-100 text-gray-700 text-xs font-semibold rounded-lg shadow-xs transition-colors">
                    Reset Formulir
                </button>
                <div class="flex items-center gap-3">
                    <a href="{{ url('/daftar_kegiatan') }}" class="px-5 py-2.5 bg-white border border-gray-300 hover:bg-gray-100 text-gray-700 text-xs font-semibold rounded-lg shadow-xs transition-colors">
                        Batal
                    </a>
                    <button type="submit" class="inline-flex items-center justify-center px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-sm transition-colors">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Simpan & Lanjut Booking Ruangan &rarr;
                    </button>
                </div>
            </div>

        </form>
    </div>
    </div> {{-- Close Panel Tab 2 --}}

    {{-- =========================================================================
        MODAL APPROVAL RAPAT
    ========================================================================== --}}
    <div x-show="approvalModal.open" 
         x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div class="bg-white rounded-3xl shadow-2xl border border-slate-100 w-full max-w-md p-6 space-y-4 my-8 transition-all transform"
             @click.away="approvalModal.open = false">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center font-bold">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-900 text-sm">Persetujuan Agenda Rapat</h3>
                        <p class="text-[11px] text-gray-400">Tentukan status persetujuan rapat</p>
                    </div>
                </div>
                <button type="button" @click="approvalModal.open = false" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="space-y-3 text-xs bg-slate-50 p-4 rounded-2xl border border-slate-100">
                <div>
                    <span class="text-gray-400 text-[10px] uppercase font-bold tracking-wider">Topik Rapat:</span>
                    <p class="font-bold text-gray-900 text-sm mt-0.5" x-text="approvalModal.text"></p>
                </div>
                <div class="flex items-center justify-between text-gray-600 pt-1 border-t border-slate-200/60">
                    <span>Pemimpin Rapat:</span>
                    <span class="font-bold text-gray-800" x-text="approvalModal.pj"></span>
                </div>
            </div>

            <p class="text-xs text-gray-500 leading-relaxed">
                Sebagai Pemimpin Rapat / PJ, silakan tentukan apakah agenda rapat ini disetujui untuk dilaksanakan atau ditolak.
            </p>

            <div class="flex items-center gap-3 pt-2">
                {{-- Form Tolak --}}
                <form action="{{ url('/setuju_rapat') }}" method="POST" class="flex-1">
                    @csrf
                    <input type="hidden" name="id" :value="approvalModal.id">
                    <input type="hidden" name="setuju_rapat" value="3">
                    <button type="submit" 
                            onclick="return confirm('Apakah Anda yakin ingin menolak agenda rapat ini?')" 
                            class="w-full py-2.5 px-4 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 font-bold rounded-xl text-xs transition flex items-center justify-center gap-1.5 cursor-pointer">
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
                            class="w-full py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs transition shadow-md shadow-emerald-600/20 flex items-center justify-center gap-1.5 cursor-pointer">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Setujui Rapat</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener("DOMContentLoaded", function () {
    // 1. Kategori Rapat (Terkait Kegiatan vs Mandiri)
    window.toggleKategoriRapat = function () {
        const isTerkait = document.getElementById('kategoriTerkait').checked;
        const boxRelasi = document.getElementById('sectionRelasiKegiatan');
        const pjInfo = document.getElementById('pjInfoContainer');

        if (isTerkait) {
            boxRelasi.classList.remove('hidden');
        } else {
            boxRelasi.classList.add('hidden');
            document.getElementById('kegiatan_id').value = '';
            document.getElementById('sub_kegiatan').value = '';
            if (pjInfo) pjInfo.classList.add('hidden');
        }
    };

    // Auto-fill saat memilih master kegiatan (termasuk auto-select Ketua Tim / PJ)
    window.handleSelectMasterKegiatan = function (selectElem) {
        const selectedOption = selectElem.options[selectElem.selectedIndex];
        if (!selectedOption || !selectedOption.value) return;

        const sub = selectedOption.getAttribute('data-sub') || '';
        const agenda = selectedOption.getAttribute('data-agenda') || '';
        const pj = selectedOption.getAttribute('data-pj') || '';

        if (sub) {
            document.getElementById('sub_kegiatan').value = sub;
        }
        if (!document.getElementById('text').value && agenda) {
            document.getElementById('text').value = 'Rapat Koordinasi: ' + agenda;
        }

        // Auto-select Ketua Tim (PJ) sebagai Penanggung Jawab & Pemimpin Rapat
        if (pj) {
            const pjSelect = document.getElementById('penanggung_jawab');
            const pemimpinSelect = document.getElementById('pemimpin');
            const pjClean = pj.toLowerCase().trim();

            if (pjSelect) {
                for (let i = 0; i < pjSelect.options.length; i++) {
                    const optVal = pjSelect.options[i].value.toLowerCase().trim();
                    if (optVal === pjClean || optVal.includes(pjClean) || pjClean.includes(optVal)) {
                        pjSelect.selectedIndex = i;
                        break;
                    }
                }
            }

            if (pemimpinSelect) {
                for (let i = 0; i < pemimpinSelect.options.length; i++) {
                    const optVal = pemimpinSelect.options[i].value.toLowerCase().trim();
                    if (optVal === pjClean || optVal.includes(pjClean) || pjClean.includes(optVal)) {
                        pemimpinSelect.selectedIndex = i;
                        break;
                    }
                }
            }

            const infoContainer = document.getElementById('pjInfoContainer');
            const infoText = document.getElementById('pjInfoText');
            if (infoContainer && infoText) {
                infoText.textContent = 'Ketua Tim/PJ otomatis terpilih: ' + pj;
                infoContainer.classList.remove('hidden');
            }
        }
    };

    // Auto-fill saat memilih master proyek SIMPATI (Topik, PJ, Pemimpin, & Anggota Peserta)
    window.handleSelectMasterProyekRapat = function(selectElem) {
        const selectedOpt = selectElem.options[selectElem.selectedIndex];
        if (!selectedOpt || !selectedOpt.value) {
            const fb = document.getElementById('proyekFeedbackRapat');
            if (fb) fb.classList.add('hidden');
            return;
        }

        const nama = selectedOpt.getAttribute('data-nama') || '';
        const tim = selectedOpt.getAttribute('data-tim') || '';
        const pjNama = selectedOpt.getAttribute('data-pj-nama') || '';
        const pjNip = selectedOpt.getAttribute('data-pj-nip') || '';
        let anggotaList = [];
        try {
            anggotaList = JSON.parse(selectedOpt.getAttribute('data-anggota') || '[]');
        } catch (e) {
            anggotaList = [];
        }

        // 1. Set Topik / Nama Rapat
        const inputTopik = document.getElementById('text');
        if (inputTopik) {
            inputTopik.value = 'Rapat Koordinasi: ' + nama;
        }

        // 2. Set Agenda Pembahasan
        const inputAgenda = document.getElementById('agenda');
        if (inputAgenda) {
            inputAgenda.value = 'Pembahasan pelaksanaan dan monitoring kegiatan ' + nama + ' (' + tim + ')';
        }

        // 3. Set PJ Kegiatan & Pemimpin Rapat
        if (pjNama) {
            const pjClean = pjNama.toLowerCase().trim();
            const pjSelect = document.getElementById('penanggung_jawab');
            const pemimpinSelect = document.getElementById('pemimpin');

            if (pjSelect) {
                let matched = false;
                for (let i = 0; i < pjSelect.options.length; i++) {
                    const optVal = pjSelect.options[i].value.toLowerCase().trim();
                    const optText = pjSelect.options[i].text.toLowerCase().trim();
                    if (optVal === pjClean || optVal.includes(pjClean) || pjClean.includes(optVal)) {
                        pjSelect.selectedIndex = i;
                        matched = true;
                        break;
                    }
                }
                if (!matched) {
                    const newOpt = new Option(pjNama + (pjNip ? ' (' + pjNip + ' - PJ SIMPATI)' : ' (PJ SIMPATI)'), pjNama, true, true);
                    pjSelect.add(newOpt);
                }
            }

            if (pemimpinSelect) {
                let matchedPemimpin = false;
                for (let i = 0; i < pemimpinSelect.options.length; i++) {
                    const optVal = pemimpinSelect.options[i].value.toLowerCase().trim();
                    const optText = pemimpinSelect.options[i].text.toLowerCase().trim();
                    if (optVal === pjClean || optVal.includes(pjClean) || pjClean.includes(optVal)) {
                        pemimpinSelect.selectedIndex = i;
                        matchedPemimpin = true;
                        break;
                    }
                }
                if (!matchedPemimpin) {
                    const newOpt = new Option(pjNama + (pjNip ? ' (' + pjNip + ' - PJ SIMPATI)' : ' (PJ SIMPATI)'), pjNama, true, true);
                    pemimpinSelect.add(newOpt);
                }
            }

            const infoContainer = document.getElementById('pjInfoContainer');
            const infoText = document.getElementById('pjInfoText');
            if (infoContainer && infoText) {
                infoText.textContent = 'PJ SIMPATI otomatis terpilih: ' + pjNama;
                infoContainer.classList.remove('hidden');
            }
        }

        // 4. Rekomendasikan & Centang Peserta Rapat dari Anggota Proyek
        let checkedCount = 0;
        if (anggotaList.length > 0) {
            // Uncheck semua dulu
            document.querySelectorAll('.cb-peserta').forEach(cb => {
                cb.checked = false;
            });

            anggotaList.forEach(m => {
                const targetNama = (m.nama || '').toLowerCase().trim();
                const targetNip = (m.nip || '').trim();

                document.querySelectorAll('.item-peserta').forEach(item => {
                    const cb = item.querySelector('.cb-peserta');
                    const label = item.querySelector('.nama-peserta-label');
                    const labelText = label ? label.textContent.toLowerCase() : '';
                    const cbVal = cb ? cb.value.trim() : '';

                    if (cb && !cb.checked) {
                        if ((targetNama && labelText.includes(targetNama)) || (targetNip && cbVal === targetNip)) {
                            cb.checked = true;
                            item.style.display = 'flex';
                            checkedCount++;
                        }
                    }
                });
            });

            updateTotalPeserta();
        }

        // 5. Tampilkan Feedback Visual
        const fb = document.getElementById('proyekFeedbackRapat');
        const fbText = document.getElementById('proyekFeedbackRapatText');
        if (fb && fbText) {
            let msg = `Proyek "${nama}" diterapkan: Tim [${tim}], PJ & Pemimpin [${pjNama || 'Belum Ada PJ'}]`;
            if (checkedCount > 0) {
                msg += `, dan ${checkedCount} anggota proyek otomatis dicentang sebagai peserta rapat!`;
            } else if (anggotaList.length > 0) {
                msg += `, serta ${anggotaList.length} anggota proyek terhubung.`;
            }
            fbText.textContent = msg;
            fb.classList.remove('hidden');
        }
    };

    // 2. Toggle Mode Pertemuan (Online / Offline / Hybrid)
    window.toggleModeTempat = function () {
        const isOnline = document.getElementById('modeOnline').checked;
        const isOffline = document.getElementById('modeOffline').checked;
        const isHybrid = document.getElementById('modeHybrid').checked;

        const wrapperOnline = document.getElementById('wrapperLinkOnline');
        const wrapperOffline = document.getElementById('wrapperRuangOffline');

        if (isOnline) {
            wrapperOnline.classList.remove('hidden');
            wrapperOffline.classList.add('hidden');
        } else if (isOffline) {
            wrapperOnline.classList.add('hidden');
            wrapperOffline.classList.remove('hidden');
        } else if (isHybrid) {
            wrapperOnline.classList.remove('hidden');
            wrapperOffline.classList.remove('hidden');
        }
    };

    // 3. Pilihan Venue (Ruangan Rapat)
    window.handleSelectVenue = function (selectElem) {
        const selectedOption = selectElem.options[selectElem.selectedIndex];
        const val = selectElem.value;
        const inputTempat = document.getElementById('tempat_offline');

        if (val === 'custom') {
            inputTempat.value = '';
            inputTempat.focus();
        } else if (val) {
            const venueName = selectedOption.getAttribute('data-name');
            inputTempat.value = venueName;
        }
    };

    // Sinkronisasi status checkbox "Pilih Semua" pada setiap grup
    window.syncAllGroupCheckboxes = function () {
        document.querySelectorAll('.cb-select-group').forEach(function (groupCheckbox) {
            const targetId = groupCheckbox.getAttribute('data-target');
            const targetBox = document.getElementById(targetId);
            if (targetBox) {
                const total = targetBox.querySelectorAll('.cb-peserta').length;
                const checked = targetBox.querySelectorAll('.cb-peserta:checked').length;
                // Jika semua dicentang, centang groupCheckbox. Jika ada 1 saja yang tidak dicentang, matikan centang groupCheckbox!
                groupCheckbox.checked = (total > 0 && checked === total);
            }
        });
    };

    // 4. Hitung Total Peserta Terpilih
    window.updateTotalPeserta = function () {
        const count = document.querySelectorAll('.cb-peserta:checked').length;
        const badge = document.getElementById('badgeCountPeserta');
        if (badge) {
            badge.textContent = count + ' Peserta Dipilih';
        }
        syncAllGroupCheckboxes();
    };

    // 5. Checkbox Pilih Semua per Grup
    document.querySelectorAll('.cb-select-group').forEach(function (groupCheckbox) {
        groupCheckbox.addEventListener('change', function () {
            const targetId = this.getAttribute('data-target');
            const targetBox = document.getElementById(targetId);
            if (targetBox) {
                const checkboxes = targetBox.querySelectorAll('.cb-peserta');
                checkboxes.forEach(function (cb) {
                    cb.checked = groupCheckbox.checked;
                });
                updateTotalPeserta();
            }
        });
    });

    // Event listener untuk setiap checkbox peserta agar otomatis mematikan "Pilih Semua" jika salah satu dilepas
    document.querySelectorAll('.cb-peserta').forEach(function (cb) {
        cb.addEventListener('change', function () {
            updateTotalPeserta();
        });
    });

    // Global Pilih Semua & Batalkan Pilihan
    window.selectAllGlobalPeserta = function (checked) {
        document.querySelectorAll('.cb-peserta').forEach(function (cb) {
            cb.checked = checked;
        });
        document.querySelectorAll('.cb-select-group').forEach(function (gcb) {
            gcb.checked = checked;
        });
        updateTotalPeserta();
    };

    // 6. Pencarian Peserta Live
    const filterInput = document.getElementById('filterPesertaNama');
    if (filterInput) {
        filterInput.addEventListener('input', function () {
            const keyword = this.value.toLowerCase().trim();
            const items = document.querySelectorAll('.item-peserta');

            items.forEach(function (item) {
                const nameLabel = item.querySelector('.nama-peserta-label');
                const text = nameLabel ? nameLabel.textContent.toLowerCase() : '';
                if (text.includes(keyword)) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    }

    // Inisialisasi awal
    updateTotalPeserta();
    toggleModeTempat();
});
</script>
@endpush
