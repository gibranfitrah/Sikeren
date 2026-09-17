@extends('layouts.app')

@section('title', 'Sub Kegiatan - Sikeren')
@section('header_title', 'Kelola Sub Kegiatan')

@section('content')

<div class="space-y-6 pb-12 max-w-7xl mx-auto" x-data="{ modalTambah: false, editModal: false, activeEdit: {} }">

    {{-- HEADER & ACTION BUTTONS --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-gray-200/80 shadow-xs">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-md text-[11px] font-bold uppercase tracking-wider bg-indigo-50 text-indigo-700 border border-indigo-100">
                    Manajemen Tugas
                </span>
                <span class="text-xs text-gray-400">•</span>
                <span class="text-xs text-gray-500 font-medium">Sub Kegiatan & Deadline</span>
            </div>
            <h2 class="text-xl font-bold text-gray-900 mt-1">Daftar Sub Kegiatan</h2>
            <p class="text-xs text-gray-500 mt-0.5">Kelola rincian sub kegiatan di bawah kegiatan utama, penugasan personil, dan pantau indikator batas waktu (deadline).</p>
        </div>
        <div class="flex items-center gap-2.5">
            <button type="button" 
                    @click="modalTambah = true"
                    class="inline-flex items-center justify-center px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-xs hover:shadow transition-all">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Sub Kegiatan
            </button>
        </div>
    </div>

    {{-- SUCCESS ALERT --}}
    @if(session('success'))
    <div class="bg-emerald-50 border border-emerald-200 p-4 rounded-xl shadow-xs flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div>
                <h4 class="text-xs font-bold text-emerald-900">Sukses</h4>
                <p class="text-xs text-emerald-700 mt-0.5">{{ session('success') }}</p>
            </div>
        </div>
    </div>
    @endif

    {{-- STATS SUMMARY CARDS --}}
    @php
        $totalSub = count($subKegiatans);
        $warningCount = 0;
        $overdueCount = 0;
        $doneCount = 0;
        $today = \Carbon\Carbon::today();

        foreach($subKegiatans as $s) {
            if ($s->status === 'Selesai' || $s->progress >= 100) {
                $doneCount++;
            } elseif ($s->end_date) {
                $days = $today->diffInDays(\Carbon\Carbon::parse($s->end_date), false);
                if ($days < 0) $overdueCount++;
                elseif ($days <= 3) $warningCount++;
            }
        }
    @endphp

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-2xl border border-gray-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-gray-500">Total Sub Kegiatan</p>
                <h3 class="text-2xl font-bold text-gray-900 mt-1">{{ $totalSub }}</h3>
            </div>
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-sm">
                {{ $totalSub }}
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-gray-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-amber-700 font-semibold">Mendekati Deadline</p>
                <h3 class="text-2xl font-bold text-amber-600 mt-1">{{ $warningCount }}</h3>
            </div>
            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                <svg class="w-5 h-5 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-gray-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-rose-700 font-semibold">Terlambat</p>
                <h3 class="text-2xl font-bold text-rose-600 mt-1">{{ $overdueCount }}</h3>
            </div>
            <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-gray-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-emerald-700 font-semibold">Selesai</p>
                <h3 class="text-2xl font-bold text-emerald-600 mt-1">{{ $doneCount }}</h3>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
        </div>
    </div>

    {{-- MAIN TABLE & FILTERS --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        {{-- Search and Filter Toolbar --}}
        <div class="p-4 sm:p-6 border-b border-gray-100 bg-gray-50/40 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <h3 class="font-bold text-gray-800 text-sm">Semua Sub Kegiatan</h3>
                <span class="text-xs px-2.5 py-0.5 rounded-full bg-blue-50 text-blue-700 font-semibold">{{ count($subKegiatans) }} Data</span>
            </div>

            <form method="GET" action="{{ route('sub-kegiatan.index') }}" class="flex items-center gap-2.5 flex-wrap sm:flex-nowrap">
                <div class="relative min-w-[200px]">
                    <input type="text"
                           name="search"
                           value="{{ $search }}"
                           placeholder="Cari sub kegiatan, PJ..."
                           class="w-full pl-8 pr-3 py-2 text-xs rounded-xl border border-gray-200 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                    <svg class="w-4 h-4 text-gray-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>

                <select name="status" onchange="this.form.submit()" class="py-2 px-3 text-xs rounded-xl border border-gray-200 bg-white text-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                    <option value="all">Semua Status</option>
                    <option value="Sedang Berjalan" {{ $statusFilter == 'Sedang Berjalan' ? 'selected' : '' }}>Sedang Berjalan</option>
                    <option value="Mendekati Deadline" {{ $statusFilter == 'Mendekati Deadline' ? 'selected' : '' }}>Mendekati Deadline</option>
                    <option value="Selesai" {{ $statusFilter == 'Selesai' ? 'selected' : '' }}>Selesai</option>
                </select>

                <button type="submit" class="px-3.5 py-2 bg-gray-800 hover:bg-gray-900 text-white text-xs font-bold rounded-xl transition">
                    Filter
                </button>
            </form>
        </div>

        {{-- Table Content --}}
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-left text-xs">
                <thead class="bg-gray-50 text-[11px] font-bold text-gray-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-4">Sub Kegiatan & Induk</th>
                        <th class="px-6 py-4">Tim & PJ</th>
                        <th class="px-6 py-4">Rentang Waktu & Deadline</th>
                        <th class="px-6 py-4">Status & Progress</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($subKegiatans as $sub)
                    @php
                        $deadline = $sub->deadline_status;
                    @endphp
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        {{-- 1. Sub Kegiatan & Induk --}}
                        <td class="px-6 py-4">
                            <div class="space-y-1 max-w-md">
                                <span class="font-bold text-gray-900 text-sm block">
                                    {{ $sub->nama_sub }}
                                </span>
                                <div class="flex items-center gap-1.5 text-gray-500 text-[11px]">
                                    <span class="text-blue-600 font-semibold">Induk:</span>
                                    <span class="truncate">{{ $sub->task ? $sub->task->text : '-' }}</span>
                                </div>
                                @if(!empty($sub->anggota_list))
                                    <div class="flex items-center gap-1 flex-wrap pt-1">
                                        <span class="text-[10px] text-gray-400 font-semibold">Anggota:</span>
                                        @foreach(array_slice($sub->anggota_list, 0, 3) as $ang)
                                            <span class="inline-block px-2 py-0.5 rounded text-[10px] bg-gray-100 text-gray-700 font-medium">
                                                {{ $ang }}
                                            </span>
                                        @endforeach
                                        @if(count($sub->anggota_list) > 3)
                                            <span class="text-[10px] text-gray-400 font-bold">+{{ count($sub->anggota_list) - 3 }} lainnya</span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </td>

                        {{-- 2. Tim & PJ --}}
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="space-y-1.5">
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-100 block w-fit">
                                    {{ $sub->tim ?: ($sub->task ? $sub->task->tim : 'Umum') }}
                                </span>
                                <div class="flex items-center gap-1.5">
                                    <div class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-[10px]">
                                        {{ strtoupper(substr($sub->pj ?? 'P', 0, 1)) }}
                                    </div>
                                    <span class="font-medium text-gray-800">{{ $sub->pj ?: '-' }}</span>
                                </div>
                            </div>
                        </td>

                        {{-- 3. Rentang Waktu & Deadline Status --}}
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="space-y-1.5">
                                <div class="text-gray-700 font-medium">
                                    {{ $sub->start_date ? \Carbon\Carbon::parse($sub->start_date)->translatedFormat('d M Y') : '-' }}
                                    @if($sub->end_date && $sub->end_date != $sub->start_date)
                                        <span class="text-gray-400">&rarr;</span> {{ \Carbon\Carbon::parse($sub->end_date)->translatedFormat('d M Y') }}
                                    @endif
                                </div>
                                <div>
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-bold border {{ $deadline['class'] }}">
                                        <span class="w-2 h-2 rounded-full {{ $deadline['dot'] }}"></span>
                                        <span>{{ $deadline['label'] }}</span>
                                    </span>
                                </div>
                            </div>
                        </td>

                        {{-- 4. Status & Progress --}}
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="space-y-2 min-w-[140px]">
                                <div class="flex items-center justify-between text-[11px]">
                                    <span class="font-bold text-gray-700">{{ $sub->progress }}% Selesai</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
                                    <div class="h-2 rounded-full {{ $sub->progress >= 100 ? 'bg-emerald-500' : 'bg-blue-600' }}" style="width: {{ $sub->progress }}%"></div>
                                </div>
                                {{-- Quick Progress Form --}}
                                <form action="{{ route('sub-kegiatan.progress', $sub->id) }}" method="POST" class="flex items-center gap-1.5 pt-1">
                                    @csrf
                                    <select name="progress" onchange="this.form.submit()" class="text-[10px] py-1 px-1.5 rounded-md border border-gray-200 bg-white text-gray-700">
                                        <option value="0" {{ $sub->progress == 0 ? 'selected' : '' }}>0%</option>
                                        <option value="25" {{ $sub->progress == 25 ? 'selected' : '' }}>25%</option>
                                        <option value="50" {{ $sub->progress == 50 ? 'selected' : '' }}>50%</option>
                                        <option value="75" {{ $sub->progress == 75 ? 'selected' : '' }}>75%</option>
                                        <option value="100" {{ $sub->progress == 100 ? 'selected' : '' }}>100% (Selesai)</option>
                                    </select>
                                    <span class="text-[10px] text-gray-400">Update</span>
                                </form>
                            </div>
                        </td>

                        {{-- 5. Aksi --}}
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                <form action="{{ route('sub-kegiatan.destroy', $sub->id) }}" method="POST" onsubmit="return confirm('Hapus sub kegiatan ini?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 text-gray-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition" title="Hapus">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-16 text-center text-gray-400">
                            <div class="max-w-xs mx-auto text-center space-y-3">
                                <div class="w-12 h-12 rounded-2xl bg-gray-50 text-gray-300 flex items-center justify-center mx-auto">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                    </svg>
                                </div>
                                <p class="text-xs font-medium text-gray-500">Belum ada sub kegiatan yang terdaftar.</p>
                                <button type="button" @click="modalTambah = true" class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white text-xs font-semibold rounded-xl hover:bg-blue-700">
                                    Tambah Sub Kegiatan
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- =========================================================
        MODAL TAMBAH SUB KEGIATAN
    ========================================================== --}}
    <div x-show="modalTambah" 
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs overflow-y-auto">
        <div class="bg-white rounded-3xl shadow-2xl border border-gray-100 w-full max-w-2xl max-h-[90vh] flex flex-col overflow-hidden my-8"
             @click.away="modalTambah = false">
            
            {{-- Modal Header --}}
            <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                <div>
                    <h3 class="font-bold text-gray-900 text-base">Tambah Sub Kegiatan Baru</h3>
                    <p class="text-xs text-gray-500">Lengkapi data sub kegiatan, waktu pelaksanaan, dan penugasan anggota.</p>
                </div>
                <button type="button" @click="modalTambah = false" class="text-gray-400 hover:text-gray-600 text-xl font-bold">
                    &times;
                </button>
            </div>

            {{-- Modal Body --}}
            <form action="{{ route('sub-kegiatan.store') }}" method="POST" class="p-6 space-y-5 overflow-y-auto flex-1">
                @csrf

                {{-- Induk Kegiatan --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                        Induk Kegiatan <span class="text-red-500">*</span>
                    </label>
                    <select name="task_id" required class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600">
                        <option value="">-- Pilih Induk Kegiatan --</option>
                        @foreach($tasks as $t)
                            <option value="{{ $t->id }}" {{ $taskId == $t->id ? 'selected' : '' }}>
                                [{{ $t->jenis }}] {{ $t->text }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Nama Sub Kegiatan & Tim --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                            Nama Sub Kegiatan <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="nama_sub" required placeholder="Contoh: Pengawasan Lapangan Tahap 1" class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                            Tim / Fungsi
                        </label>
                        <select name="tim" class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600">
                            <option value="">-- Pilih Tim --</option>
                            @foreach($masterGroups as $g)
                                <option value="{{ $g->grup }}">{{ $g->grup }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Rentang Waktu (Start & End Date) --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 bg-blue-50/40 p-3.5 rounded-2xl border border-blue-100">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                            Tanggal Mulai
                        </label>
                        <input type="date" name="start_date" value="{{ date('Y-m-d') }}" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-xl text-xs text-gray-800">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                            Tanggal Deadline / Selesai <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="end_date" required value="{{ date('Y-m-d') }}" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-xl text-xs text-gray-800">
                    </div>
                </div>

                {{-- Info PJ Otomatis --}}
                <div class="p-3 bg-blue-50/60 rounded-xl border border-blue-100 flex items-center gap-2.5 text-xs text-blue-800">
                    <svg class="w-4 h-4 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span><strong>Penanggung Jawab (PJ):</strong> Otomatis diwarisi langsung dari Kegiatan Induk yang dipilih.</span>
                </div>

                {{-- Pemilihan Anggota Tim (Group Selector, Select All, Search) --}}
                <div class="border-t border-gray-100 pt-4">
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider">
                            Pilih Anggota Tim
                        </label>
                        <div class="flex gap-2">
                            <button type="button" onclick="selectAllModalAnggota(true)" class="text-[10px] font-bold text-blue-600 bg-blue-50 px-2 py-1 rounded">Select All Filter</button>
                            <button type="button" onclick="selectAllModalAnggota(false)" class="text-[10px] font-bold text-gray-500 bg-gray-100 px-2 py-1 rounded">Clear All</button>
                        </div>
                    </div>

                    {{-- Filter & Search inside modal --}}
                    <div class="grid grid-cols-2 gap-2 mb-2">
                        <select id="modalFilterTim" onchange="filterModalPegawai()" class="text-xs px-2 py-1.5 rounded-lg border border-gray-200">
                            <option value="all">Semua Tim</option>
                            @foreach($masterGroups as $g)
                                <option value="{{ $g->grup }}">{{ $g->grup }}</option>
                            @endforeach
                        </select>
                        <input type="text" id="modalSearchPegawai" onkeyup="filterModalPegawai()" placeholder="Cari nama pegawai..." class="text-xs px-2 py-1.5 rounded-lg border border-gray-200">
                    </div>

                    <div class="grid grid-cols-2 gap-2 p-3 bg-gray-50 rounded-xl border border-gray-200 max-h-44 overflow-y-auto" id="modalPegawaiList">
                        @foreach($allUsers as $u)
                            @php
                                $uGroup = '-';
                                foreach($usersByGroup as $gN => $mems) {
                                    if ($mems->contains('niplama', $u->niplama)) { $uGroup = $gN; break; }
                                }
                            @endphp
                            <label class="modal-pegawai-card flex items-center gap-2 p-2 rounded-lg bg-white border border-gray-200 text-xs cursor-pointer"
                                   data-group="{{ $uGroup }}"
                                   data-name="{{ strtolower($u->nama_lengkap) }}">
                                <input type="checkbox" name="anggota[]" value="{{ $u->nama_lengkap }}" class="modal-anggota-cb rounded text-blue-600">
                                <span class="truncate font-medium text-gray-800">{{ $u->nama_lengkap }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- Modal Footer --}}
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                    <button type="button" @click="modalTambah = false" class="px-4 py-2 text-xs font-bold text-gray-500 hover:text-gray-700">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow-xs transition">
                        Simpan Sub Kegiatan
                    </button>
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