@extends('layouts.app')

@section('title', 'Detail Kegiatan: ' . ($task->text ?? 'Kegiatan') . ' - Sikeren')
@section('header_title', 'Detail Kegiatan')

@section('content')
@php
    $startFormatted = $task->start_date ? \Carbon\Carbon::parse($task->start_date)->translatedFormat('d F Y') : '-';
    $endFormatted   = $task->date_akhir ? \Carbon\Carbon::parse($task->date_akhir)->translatedFormat('d F Y') : $startFormatted;
    $durasiHari     = $task->duration ?? (
        ($task->start_date && $task->date_akhir) 
            ? \Carbon\Carbon::parse($task->start_date)->diffInDays(\Carbon\Carbon::parse($task->date_akhir)) + 1 
            : 1
    );

    // Status Styling
    $statusText = $task->status ?? 'Sedang Berjalan';
    $statusVariant = match(strtolower($statusText)) {
        'selesai' => 'success',
        'disetujui', 'sedang berjalan' => 'primary',
        'menunggu', 'menunggu persetujuan', 'belum' => 'warning',
        'ditolak', 'batal' => 'danger',
        default => 'neutral'
    };

    $pjNama = $task->penanggung_jawab ?? ($task->pemimpin ?? '-');
@endphp

<div class="space-y-6 pb-12" x-data="{ modalTambahSub: false, modalEditSub: false, activeSub: null }">

    {{-- =========================================================================
        1. TOP HEADER & ACTIONS
    ========================================================================== --}}
    <x-heading 
        :title="$task->text"
        :subtitle="'Agenda: ' . ($task->agenda ?? 'Tidak ada catatan agenda khusus')"
        tag="Kegiatan Tim"
        :subtag="$task->tim ?? 'BPS Provinsi Sulawesi Tenggara'"
        :backUrl="url('/daftar_kegiatan')">
        
        <x-slot name="action">
            <x-button 
                variant="secondary" 
                size="sm"
                href="{{ url('/daftar_kegiatan') }}"
                icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>'>
                Daftar Kegiatan
            </x-button>

            @if(!empty($task->id))
                <x-button 
                    variant="secondary" 
                    size="sm"
                    href="{{ url('/employee/pdf_kegiatan/' . $task->id) }}"
                    target="_blank"
                    icon='<svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>'>
                    Unduh PDF
                </x-button>
            @endif

            <x-button 
                variant="primary" 
                size="sm"
                @click="modalTambahSub = true"
                icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>'>
                Tambah Sub Kegiatan
            </x-button>
        </x-slot>
    </x-heading>

    {{-- Flash Notifications --}}
    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold flex items-center gap-3">
            <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    {{-- =========================================================================
        2. SUMMARY KPI METRIC CARDS
    ========================================================================== --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Card 1: Status Pelaksanaan --}}
        <div class="bg-white p-5 rounded-2xl border border-gray-200/80 shadow-xs flex items-center justify-between">
            <div class="space-y-1">
                <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Status Kegiatan</p>
                <div>
                    <x-badge :variant="$statusVariant" size="sm" :dot="true">
                        {{ $statusText }}
                    </x-badge>
                </div>
            </div>
            <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>

        {{-- Card 2: Durasi & Rentang Waktu --}}
        <div class="bg-white p-5 rounded-2xl border border-gray-200/80 shadow-xs flex items-center justify-between">
            <div class="space-y-1">
                <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Durasi Pelaksanaan</p>
                <p class="text-base font-black text-gray-900">{{ $durasiHari }} Hari Kerja</p>
                <p class="text-[10px] text-gray-500 font-medium truncate max-w-[170px]">{{ $startFormatted }}</p>
            </div>
            <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
        </div>

        {{-- Card 3: Progres Keseluruhan --}}
        <div class="bg-white p-5 rounded-2xl border border-gray-200/80 shadow-xs flex items-center justify-between">
            <div class="space-y-1.5 flex-1 pr-3">
                <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Progres Sub Kegiatan</p>
                <div class="flex items-baseline gap-2">
                    <span class="text-lg font-black text-gray-900">{{ $overallProgress }}%</span>
                    <span class="text-[10px] text-gray-500 font-medium">({{ $completedSub }}/{{ $totalSub }} selesai)</span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-1.5 overflow-hidden">
                    <div class="h-1.5 rounded-full {{ $overallProgress >= 100 ? 'bg-emerald-500' : 'bg-blue-600' }}" style="width: {{ $overallProgress }}%"></div>
                </div>
            </div>
            <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                </svg>
            </div>
        </div>

        {{-- Card 4: Tim & Penugasan --}}
        <div class="bg-white p-5 rounded-2xl border border-gray-200/80 shadow-xs flex items-center justify-between">
            <div class="space-y-1">
                <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Tim Pelaksana</p>
                <p class="text-base font-black text-gray-900">{{ $assignedUsers->count() }} Anggota Ditugaskan</p>
                <p class="text-[10px] text-gray-500 font-medium truncate max-w-[170px]">PJ: {{ $pjNama }}</p>
            </div>
            <div class="w-11 h-11 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
        </div>
    </div>

    {{-- =========================================================================
        3. MAIN TWO-COLUMN LAYOUT
    ========================================================================== --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        {{-- =====================================================================
            LEFT COLUMN: SUB KEGIATAN & INFORMASI LENGKAP (Width: 7 cols on lg)
        ====================================================================== --}}
        <div class="lg:col-span-7 space-y-6">

            {{-- SUB KEGIATAN & MILESTONES --}}
            <x-card 
                title="Sub Kegiatan & Rincian Tugas" 
                subtitle="Tahapan pengerjaan kegiatan yang terbagi ke anggota tim"
                tag="Milestone">
                
                <x-slot name="action">
                    <x-button 
                        variant="primary" 
                        size="sm"
                        @click="modalTambahSub = true"
                        icon='<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>'>
                        + Sub Baru
                    </x-button>
                </x-slot>

                @if($subKegiatans->count() > 0)
                    <div class="space-y-4">
                        @foreach($subKegiatans as $sub)
                            @php
                                $deadline = $sub->deadline_status;
                            @endphp
                            <div class="p-4 sm:p-5 rounded-2xl bg-gray-50/70 border border-gray-200/80 hover:border-blue-200 hover:bg-blue-50/20 transition-all space-y-3.5">
                                
                                {{-- Sub Header: Title & Badges --}}
                                <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-2.5">
                                    <div class="space-y-1">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <h4 class="font-bold text-gray-900 text-sm sm:text-base">{{ $sub->nama_sub }}</h4>
                                            @if($sub->tim)
                                                <x-badge variant="neutral" size="xs">{{ $sub->tim }}</x-badge>
                                            @endif
                                        </div>
                                        @if($sub->keterangan)
                                            <p class="text-xs text-gray-600 leading-relaxed">{{ $sub->keterangan }}</p>
                                        @endif
                                    </div>

                                    {{-- Status & Deadline Pill --}}
                                    <div class="shrink-0">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-bold border {{ $deadline['class'] }}">
                                            <span class="w-1.5 h-1.5 rounded-full {{ $deadline['dot'] }}"></span>
                                            <span>{{ $deadline['label'] }}</span>
                                        </span>
                                    </div>
                                </div>

                                {{-- Sub Info Details (PJ, Date, Members) --}}
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs bg-white p-3 rounded-xl border border-gray-100">
                                    {{-- PJ & Waktu --}}
                                    <div class="space-y-1.5">
                                        <div class="flex items-center gap-2 text-gray-500">
                                            <span class="font-semibold text-gray-400 w-16">PJ Sub:</span>
                                            <span class="font-bold text-gray-800">{{ $sub->pj ?: ($task->penanggung_jawab ?? '-') }}</span>
                                        </div>
                                        <div class="flex items-center gap-2 text-gray-500">
                                            <span class="font-semibold text-gray-400 w-16">Waktu:</span>
                                            <span class="text-gray-700">
                                                {{ $sub->start_date ? \Carbon\Carbon::parse($sub->start_date)->translatedFormat('d M Y') : '-' }}
                                                @if($sub->end_date && $sub->end_date != $sub->start_date)
                                                    <span class="text-gray-400">&rarr;</span> {{ \Carbon\Carbon::parse($sub->end_date)->translatedFormat('d M Y') }}
                                                @endif
                                            </span>
                                        </div>
                                    </div>

                                    {{-- Anggota Assigned --}}
                                    <div class="space-y-1">
                                        <span class="font-semibold text-gray-400 block">Anggota Terlibat:</span>
                                        @if(!empty($sub->anggota_list))
                                            <div class="flex items-center gap-1 flex-wrap">
                                                @foreach(array_slice($sub->anggota_list, 0, 4) as $ang)
                                                    <span class="px-2 py-0.5 rounded-md text-[10px] bg-blue-50 text-blue-700 border border-blue-100 font-semibold">
                                                        {{ $ang }}
                                                    </span>
                                                @endforeach
                                                @if(count($sub->anggota_list) > 4)
                                                    <span class="text-[10px] text-gray-400 font-bold">+{{ count($sub->anggota_list) - 4 }} lainnya</span>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-gray-400 italic text-[11px]">Seluruh tim terkait</span>
                                        @endif
                                    </div>
                                </div>

                                {{-- Progress & Quick Actions --}}
                                <div class="pt-1 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                    {{-- Interactive Progress Bar --}}
                                    <div class="flex-1 space-y-1">
                                        <div class="flex items-center justify-between text-[11px]">
                                            <span class="font-bold text-gray-600">Progres Pengerjaan</span>
                                            <span class="font-black {{ $sub->progress >= 100 ? 'text-emerald-600' : 'text-blue-600' }}">{{ $sub->progress }}%</span>
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
                                            <div class="h-2 rounded-full transition-all duration-300 {{ $sub->progress >= 100 ? 'bg-emerald-500' : 'bg-blue-600' }}" style="width: {{ $sub->progress }}%"></div>
                                        </div>
                                    </div>

                                    {{-- Quick Update Dropdown --}}
                                    <div class="flex items-center gap-2 shrink-0">
                                        <form action="{{ route('sub-kegiatan.progress', $sub->id) }}" method="POST" class="flex items-center gap-1.5">
                                            @csrf
                                            <select name="progress" onchange="this.form.submit()" class="text-xs py-1.5 px-2 rounded-xl border border-gray-200 bg-white text-gray-700 font-bold focus:ring-1 focus:ring-blue-500">
                                                <option value="0" {{ $sub->progress == 0 ? 'selected' : '' }}>0%</option>
                                                <option value="25" {{ $sub->progress == 25 ? 'selected' : '' }}>25%</option>
                                                <option value="50" {{ $sub->progress == 50 ? 'selected' : '' }}>50%</option>
                                                <option value="75" {{ $sub->progress == 75 ? 'selected' : '' }}>75%</option>
                                                <option value="100" {{ $sub->progress == 100 ? 'selected' : '' }}>100% (Selesai)</option>
                                            </select>
                                        </form>

                                        {{-- Delete Sub Button --}}
                                        <form action="{{ route('sub-kegiatan.destroy', $sub->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus sub kegiatan ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 rounded-xl text-gray-400 hover:text-rose-600 hover:bg-rose-50 transition" title="Hapus Sub Kegiatan">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </div>

                            </div>
                        @endforeach
                    </div>
                @else
                    {{-- Empty State --}}
                    <div class="p-8 sm:p-12 text-center rounded-2xl border-2 border-dashed border-gray-200 bg-gray-50/50 space-y-4">
                        <div class="w-14 h-14 rounded-2xl bg-blue-50 text-blue-500 flex items-center justify-center mx-auto shadow-xs">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                            </svg>
                        </div>
                        <div class="max-w-sm mx-auto space-y-1">
                            <h4 class="text-sm font-bold text-gray-800">Belum Ada Sub Kegiatan</h4>
                            <p class="text-xs text-gray-500">Pecah kegiatan utama ini menjadi beberapa sub kegiatan terstruktur agar progres dan tanggung jawab tim lebih terpantau.</p>
                        </div>
                        <x-button 
                            variant="primary" 
                            size="sm"
                            @click="modalTambahSub = true"
                            icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>'>
                            Buat Sub Kegiatan Pertama
                        </x-button>
                    </div>
                @endif
            </x-card>

            {{-- INFORMASI LENGKAP & DASAR PELAKSANAAN --}}
            <x-card 
                title="Informasi & Dasar Pelaksanaan" 
                subtitle="Data administrasi dan legalitas kegiatan tim"
                tag="Administrasi">
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    {{-- Nama Kegiatan --}}
                    <div class="p-3.5 rounded-xl bg-gray-50 border border-gray-100 space-y-1">
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Nama Kegiatan</span>
                        <p class="text-xs font-bold text-gray-900">{{ $task->text }}</p>
                    </div>

                    {{-- Tim Pengampu --}}
                    <div class="p-3.5 rounded-xl bg-gray-50 border border-gray-100 space-y-1">
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Tim Pengampu</span>
                        <p class="text-xs font-bold text-gray-900">{{ $task->tim ?? '-' }}</p>
                    </div>

                    {{-- Agenda / Perihal --}}
                    <div class="p-3.5 rounded-xl bg-gray-50 border border-gray-100 space-y-1 sm:col-span-2">
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Perihal / Deskripsi Agenda</span>
                        <p class="text-xs text-gray-800 leading-relaxed font-medium">{{ $task->agenda ?? '-' }}</p>
                    </div>

                    {{-- Dasar Surat / Dokumen Tugas --}}
                    <div class="p-3.5 rounded-xl bg-gray-50 border border-gray-100 space-y-1">
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">No. Surat Tugas / Dasar Dokumen</span>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-bold text-gray-900">{{ $task->surat ?? '-' }}</span>
                            @if(!empty($task->surat) && file_exists(public_path('documents/' . $task->surat)))
                                <a href="{{ asset('documents/' . $task->surat) }}" target="_blank" class="text-blue-600 hover:text-blue-800 text-[11px] font-bold underline inline-flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                    Lihat File
                                </a>
                            @endif
                        </div>
                    </div>

                    {{-- Rentang Waktu --}}
                    <div class="p-3.5 rounded-xl bg-gray-50 border border-gray-100 space-y-1">
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Periode Tanggal Pelaksanaan</span>
                        <p class="text-xs font-bold text-gray-900">{{ $startFormatted }} &rarr; {{ $endFormatted }}</p>
                    </div>
                </div>
            </x-card>

        </div>

        {{-- =====================================================================
            RIGHT COLUMN: WILAYAH, PJ, & TIM PELAKSANA (Width: 5 cols on lg)
        ====================================================================== --}}
        <div class="lg:col-span-5 space-y-6">

            {{-- 1. WILAYAH PELAKSANAAN KEGIATAN --}}
            <x-card 
                title="Cakupan Wilayah Kegiatan" 
                subtitle="Lokasi kabupaten/kota pelaksanaan di Sulawesi Tenggara"
                tag="Wilayah">
                
                @php
                    $wilayahList = $task->wilayah_list;
                @endphp

                <div class="space-y-3">
                    @if(!empty($wilayahList) && count($wilayahList) > 0)
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-xs font-semibold text-gray-500">Daftar Wilayah Terpilih:</span>
                            <x-badge variant="primary" size="xs">{{ count($wilayahList) }} Wilayah</x-badge>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            @foreach($wilayahList as $w)
                                <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-blue-50 text-blue-700 border border-blue-200/80 font-bold text-xs shadow-2xs">
                                    <svg class="w-3.5 h-3.5 text-blue-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    <span>{{ $w }}</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="p-3.5 rounded-xl bg-gray-50 border border-gray-100 flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-gray-800">Provinsi Sulawesi Tenggara</p>
                                <p class="text-[11px] text-gray-500">Seluruh Wilayah / Kantor BPS Provinsi</p>
                            </div>
                        </div>
                    @endif
                </div>
            </x-card>

            {{-- 2. PENANGGUNG JAWAB (PJ) --}}
            <x-card 
                title="Penanggung Jawab Kegiatan" 
                subtitle="Koordinator utama pelaksanaan kegiatan"
                tag="PJ">
                
                <div class="p-4 rounded-2xl bg-slate-900 text-white space-y-3 relative overflow-hidden shadow-md">
                    {{-- Decorative background glow --}}
                    <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-blue-500/20 rounded-full blur-xl pointer-events-none"></div>

                    <div class="flex items-center gap-3.5 relative z-10">
                        <div class="w-12 h-12 rounded-2xl bg-blue-600 text-white flex items-center justify-center font-black text-lg shadow-inner">
                            {{ strtoupper(substr($pjNama, 0, 1)) }}
                        </div>
                        <div class="space-y-0.5">
                            <h4 class="font-bold text-white text-sm sm:text-base">{{ $pjNama }}</h4>
                            <p class="text-xs text-blue-300 font-medium">{{ $task->tim ?? 'Ketua Tim / Penanggung Jawab' }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2 text-[11px] pt-2 border-t border-slate-800 text-slate-300 relative z-10">
                        <div>
                            <span class="text-slate-500 block text-[10px]">NIP:</span>
                            <span class="font-mono text-slate-200">{{ $pjUser->nipbaru ?? ($pjUser->niplama ?? '-') }}</span>
                        </div>
                        <div>
                            <span class="text-slate-500 block text-[10px]">Email:</span>
                            <span class="truncate block text-slate-200">{{ $pjUser->email ?? ($pjNama ? strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $pjNama)) . '@bps.go.id' : '-') }}</span>
                        </div>
                    </div>
                </div>
            </x-card>

            {{-- 3. ANGGOTA TIM PELAKSANA --}}
            <x-card 
                title="Anggota Tim yang Ditugaskan" 
                subtitle="Daftar pegawai yang terlibat dalam kegiatan ini"
                tag="Tim">
                
                <x-slot name="action">
                    <x-badge variant="neutral" size="xs">{{ $assignedUsers->count() }} Anggota</x-badge>
                </x-slot>

                <div class="space-y-2.5 max-h-80 overflow-y-auto pr-1">
                    @forelse($assignedUsers as $usr)
                        <div class="p-3 rounded-xl bg-gray-50 hover:bg-white border border-gray-100 hover:border-blue-200 transition flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-xs shrink-0">
                                    {{ strtoupper(substr($usr->nama_lengkap ?? ($usr->username ?? 'U'), 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="text-xs font-bold text-gray-900 truncate">{{ $usr->nama_lengkap ?? $usr->username }}</p>
                                    <p class="text-[10px] text-gray-400 font-mono">NIP: {{ $usr->nipbaru ?? ($usr->niplama ?? '-') }}</p>
                                </div>
                            </div>
                            <x-badge variant="success" size="xs" :dot="true">Ditugaskan</x-badge>
                        </div>
                    @empty
                        <div class="p-6 text-center text-gray-400 text-xs">
                            <p>Tidak ada anggota tambahan yang tercatat di penugasan.</p>
                        </div>
                    @endforelse
                </div>
            </x-card>

        </div>

    </div>

    {{-- =========================================================================
        4. MODAL TAMBAH SUB KEGIATAN
    ========================================================================== --}}
    <div x-show="modalTambahSub" 
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs overflow-y-auto">
        <div class="bg-white rounded-3xl shadow-2xl border border-gray-100 w-full max-w-2xl max-h-[90vh] flex flex-col overflow-hidden my-8"
             @click.away="modalTambahSub = false">
            
            {{-- Modal Header --}}
            <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between bg-gray-50/60">
                <div>
                    <h3 class="font-black text-gray-900 text-base">Tambah Sub Kegiatan Baru</h3>
                    <p class="text-xs text-gray-500">Untuk Kegiatan: <span class="font-bold text-blue-600">{{ $task->text }}</span></p>
                </div>
                <button type="button" @click="modalTambahSub = false" class="text-gray-400 hover:text-gray-600 text-2xl font-bold">
                    &times;
                </button>
            </div>

            {{-- Modal Body Form --}}
            <form action="{{ route('sub-kegiatan.store') }}" method="POST" class="p-6 space-y-4 overflow-y-auto flex-1">
                @csrf
                <input type="hidden" name="task_id" value="{{ $task->id }}">

                {{-- Nama Sub & Tim --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                            Nama Sub Kegiatan <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="nama_sub" required placeholder="Contoh: Pengumpulan Berkas Lapangan" class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                            Tim / Fungsi
                        </label>
                        <input type="text" name="tim" value="{{ $task->tim }}" placeholder="Nama Tim" class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600">
                    </div>
                </div>

                {{-- Rentang Waktu (Start & End Date) --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 bg-blue-50/40 p-3.5 rounded-2xl border border-blue-100">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                            Tanggal Mulai
                        </label>
                        <input type="date" name="start_date" value="{{ $task->start_date ?? date('Y-m-d') }}" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-xl text-xs text-gray-800">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                            Tanggal Deadline / Selesai <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="end_date" required value="{{ $task->date_akhir ?? date('Y-m-d') }}" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-xl text-xs text-gray-800">
                    </div>
                </div>

                {{-- PJ Sub Kegiatan --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                        Penanggung Jawab (PJ) Sub Kegiatan
                    </label>
                    <select name="pj" class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs text-gray-800">
                        <option value="{{ $task->penanggung_jawab ?? $task->pemimpin }}">{{ $task->penanggung_jawab ?? $task->pemimpin }} (PJ Utama Kegiatan)</option>
                        @foreach($allUsers as $u)
                            @if($u->nama_lengkap !== ($task->penanggung_jawab ?? $task->pemimpin))
                                <option value="{{ $u->nama_lengkap }}">{{ $u->nama_lengkap }} ({{ $u->niplama ?? $u->username }})</option>
                            @endif
                        @endforeach
                    </select>
                </div>

                {{-- Keterangan --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                        Keterangan / Deskripsi Sub
                    </label>
                    <textarea name="keterangan" rows="2" placeholder="Catatan teknis pengerjaan atau target capaian..." class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20"></textarea>
                </div>

                {{-- Pemilihan Anggota Tim --}}
                <div class="border-t border-gray-100 pt-3 space-y-2">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider">
                            Pilih Anggota Ditugaskan
                        </label>
                        <div class="flex gap-2">
                            <button type="button" onclick="selectAllModalAnggota(true)" class="text-[10px] font-bold text-blue-600 bg-blue-50 px-2 py-1 rounded">Select All Filter</button>
                            <button type="button" onclick="selectAllModalAnggota(false)" class="text-[10px] font-bold text-gray-500 bg-gray-100 px-2 py-1 rounded">Clear All</button>
                        </div>
                    </div>

                    {{-- Filter & Search inside modal --}}
                    <div class="grid grid-cols-2 gap-2">
                        <select id="modalFilterTim" onchange="filterModalPegawai()" class="text-xs px-2 py-1.5 rounded-lg border border-gray-200">
                            <option value="all">Semua Tim</option>
                            @foreach($masterGroups as $g)
                                <option value="{{ $g->grup }}">{{ $g->grup }}</option>
                            @endforeach
                        </select>
                        <input type="text" id="modalSearchPegawai" onkeyup="filterModalPegawai()" placeholder="Cari nama pegawai..." class="text-xs px-2 py-1.5 rounded-lg border border-gray-200">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 p-3 bg-gray-50 rounded-xl border border-gray-200 max-h-40 overflow-y-auto" id="modalPegawaiList">
                        @foreach($allUsers as $u)
                            @php
                                $uGroup = '-';
                                foreach($usersByGroup as $gN => $mems) {
                                    if ($mems->contains('niplama', $u->niplama)) { $uGroup = $gN; break; }
                                }
                                $isChecked = $assignedUsers->contains('niplama', $u->niplama) || $assignedUsers->contains('nama_lengkap', $u->nama_lengkap);
                            @endphp
                            <label class="modal-pegawai-card flex items-center gap-2 p-2 rounded-lg bg-white border border-gray-200 text-xs cursor-pointer hover:border-blue-300"
                                   data-group="{{ $uGroup }}"
                                   data-name="{{ strtolower($u->nama_lengkap) }}">
                                <input type="checkbox" name="anggota[]" value="{{ $u->nama_lengkap }}" {{ $isChecked ? 'checked' : '' }} class="modal-anggota-cb rounded text-blue-600">
                                <span class="truncate font-medium text-gray-800">{{ $u->nama_lengkap }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- Modal Footer --}}
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                    <x-button variant="secondary" size="sm" @click="modalTambahSub = false">
                        Batal
                    </x-button>
                    <x-button variant="primary" size="sm" type="submit">
                        Simpan Sub Kegiatan
                    </x-button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
function selectAllModalAnggota(checked) {
    document.querySelectorAll('.modal-pegawai-card').forEach(card => {
        if (card.style.display !== 'none') {
            const cb = card.querySelector('.modal-anggota-cb');
            if (cb) cb.checked = checked;
        }
    });
}

function filterModalPegawai() {
    const group = document.getElementById('modalFilterTim').value;
    const search = document.getElementById('modalSearchPegawai').value.toLowerCase().trim();

    document.querySelectorAll('.modal-pegawai-card').forEach(card => {
        const cGroup = card.getAttribute('data-group');
        const cName = card.getAttribute('data-name');

        const matchG = (group === 'all' || cGroup === group);
        const matchS = (!search || cName.includes(search));

        if (matchG && matchS) {
            card.style.display = 'flex';
        } else {
            card.style.display = 'none';
        }
    });
}
</script>

@endsection
