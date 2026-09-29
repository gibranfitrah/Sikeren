@extends('layouts.app')

@section('title', 'Buat Kegiatan Baru - Sikeren')
@section('header_title', 'Buat Kegiatan Baru')

@section('content')

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 space-y-5">

    {{-- HEADER & NAVIGATION --}}
    <x-heading title="Buat Kegiatan Baru"
               subtitle="Form 1 layar terpadu: Tim & Agenda, Jadwal & Wilayah, serta Penugasan PJ & Anggota Tim"
               tag="Manajemen Tim"
               subtag="Form Kegiatan Terpadu"
               backUrl="{{ route('kegiatan.daftar') }}" />

    {{-- ERROR VALIDATION --}}
    @if($errors->any())
        <div class="bg-rose-50 border border-rose-200 rounded-2xl p-4 shadow-xs">
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 bg-rose-100 rounded-xl flex items-center justify-center text-rose-600 shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M5.07 19h13.86c1.54 0 2.5-1.67 1.73-3L13.73 4c-.77-1.33-2.69-1.33-3.46 0L3.34 16c-.77 1.33.19 3 1.73 3z"/>
                    </svg>
                </div>
                <div>
                    <p class="font-bold text-rose-800 text-xs">
                        Periksa kembali isian berikut:
                    </p>
                    <ul class="mt-1 text-xs text-rose-700 space-y-0.5">
                        @foreach($errors->all() as $error)
                            <li>• {{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    {{-- BANNER UPLOAD TEMPLATE PST STARLA (OPSIONAL CEPAT) --}}
    <div class="bg-gradient-to-r from-emerald-600 via-teal-600 to-cyan-700 rounded-2xl p-5 text-white shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-start sm:items-center gap-3.5">
            <div class="w-12 h-12 rounded-xl bg-white/20 border border-white/30 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <span class="px-2 py-0.5 rounded bg-white/20 text-[10px] font-extrabold uppercase tracking-wider text-emerald-100">Fitur Otomatis Starla</span>
                    <span class="text-xs text-emerald-100 font-semibold">• Senin s.d. Jumat (2 Sesi/Hari)</span>
                </div>
                <h3 class="text-base font-extrabold text-white mt-0.5">Upload Jadwal Petugas PST (Template Starla)</h3>
                <p class="text-xs text-emerald-100/90 leading-relaxed mt-0.5 max-w-2xl">
                    Import otomatis jadwal petugas Pelayanan Statistik Terpadu (PST) dari template Excel Starla. Jadwal otomatis berulang per hari kerja dan dapat diklik di kalender dashboard untuk melihat petugas yang bertugas.
                </p>
            </div>
        </div>
        <button type="button" 
                onclick="document.getElementById('modalUploadPst').classList.remove('hidden')"
                class="px-4 py-2.5 rounded-xl bg-white hover:bg-emerald-50 text-emerald-800 font-extrabold text-xs shadow-md transition flex items-center justify-center gap-2 shrink-0 active:scale-95">
            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
            <span>Upload Template PST</span>
        </button>
    </div>

    {{-- FORMULIR 1 LAYAR TERPADU (BERURUTAN VERTIKAL) --}}
    <form id="formKegiatan"
          action="{{ route('ketua-tim.store') }}"
          method="POST"
          class="bg-white rounded-2xl border border-gray-200 shadow-xs p-6 sm:p-8 space-y-8">
        @csrf

        <div class="space-y-8">
            
            {{-- SECTION 1: TIM & AGENDA KEGIATAN --}}
            <div class="space-y-5 bg-slate-50/60 p-5 sm:p-6 rounded-2xl border border-slate-200/80">
                <div class="flex items-center gap-2.5 pb-3 border-b border-gray-200">
                    <div class="w-8 h-8 rounded-xl bg-blue-600 text-white font-extrabold text-sm flex items-center justify-center shadow-xs">
                        1
                    </div>
                    <div>
                        <h3 class="text-sm font-extrabold text-gray-900 uppercase tracking-wider">
                            Tim & Agenda Kegiatan
                        </h3>
                        <p class="text-[11px] text-gray-500">Pilih tim pengampu atau integrasikan dengan proyek SIMPATI</p>
                    </div>
                </div>

                    {{-- PILIH DARI MASTER PROYEK SIMPATI --}}
                    <div class="p-3.5 bg-gradient-to-r from-blue-50/90 to-indigo-50/90 rounded-2xl border border-blue-200 shadow-2xs space-y-2">
                        <div class="flex items-center justify-between">
                            <label class="block text-xs font-bold text-blue-900 uppercase tracking-wider flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                                <span>Pilih dari Master Proyek SIMPATI</span>
                            </label>
                            <span class="text-[10px] font-bold text-blue-700 bg-white/90 px-2 py-0.5 rounded-full border border-blue-200">
                                Auto-fill PJ & Tim
                            </span>
                        </div>
                        <select id="selectMasterProyek"
                                onchange="handleSelectMasterProyek(this)"
                                class="w-full px-3 py-2 bg-white border border-blue-300 rounded-xl text-xs text-gray-800 font-semibold focus:ring-2 focus:ring-blue-500 focus:border-blue-600 transition shadow-2xs">
                            <option value="">-- Pilih Master Kegiatan / Proyek SIMPATI (80 Proyek) --</option>
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
                        <div id="proyekFeedback" class="hidden p-2 rounded-lg bg-emerald-50 border border-emerald-200 text-[11px] text-emerald-800 font-medium">
                            <div class="flex items-start gap-1.5">
                                <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span id="proyekFeedbackText"></span>
                            </div>
                        </div>
                    </div>

                    {{-- PILIH TIM / FUNGSI --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                            Pilih Tim / Fungsi Pengampu <span class="text-rose-500">*</span>
                        </label>
                        <select name="tim"
                                id="selectTim"
                                required
                                class="w-full px-3.5 py-2 bg-gray-50 focus:bg-white border border-gray-200 rounded-xl text-xs sm:text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition">
                            <option value="">-- Pilih Tim / Fungsi BPS --</option>
                            @foreach($masterGroups as $group)
                                <option value="{{ $group->grup }}" {{ old('tim') == $group->grup ? 'selected' : '' }}>
                                    {{ $group->grup }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- NAMA / AGENDA KEGIATAN --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                            Nama / Agenda Kegiatan <span class="text-rose-500">*</span>
                        </label>
                        <input type="text"
                               name="agenda"
                               value="{{ old('agenda') }}"
                               placeholder="Contoh: Survei Sosial Ekonomi Nasional (Susenas)"
                               required
                               class="w-full px-3.5 py-2 bg-gray-50 focus:bg-white border border-gray-200 rounded-xl text-xs sm:text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition">
                    </div>

                    {{-- PERIHAL & DASAR SURAT --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                                Perihal / Ringkasan
                            </label>
                            <input type="text"
                                   name="perihal"
                                   value="{{ old('perihal') }}"
                                   placeholder="Contoh: Pelaksanaan Lapangan"
                                   class="w-full px-3.5 py-2 bg-gray-50 focus:bg-white border border-gray-200 rounded-xl text-xs sm:text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                                Dasar Surat / Dokumen
                            </label>
                            <input type="text"
                                   name="dasar"
                                   value="{{ old('dasar') }}"
                                   placeholder="No. Surat Tugas"
                                    class="w-full px-3.5 py-2 bg-gray-50 focus:bg-white border border-gray-200 rounded-xl text-xs sm:text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition">
                        </div>
                    </div>
                </div>
            </div> {{-- END SECTION 1 --}}

            {{-- SECTION 2: JADWAL & WILAYAH --}}
            <div class="space-y-5 bg-amber-50/40 p-5 sm:p-6 rounded-2xl border border-amber-200/80">
                <div class="flex items-center gap-2.5 pb-3 border-b border-amber-200/60">
                    <div class="w-8 h-8 rounded-xl bg-amber-500 text-white font-extrabold text-sm flex items-center justify-center shadow-xs">
                        2
                    </div>
                    <div>
                        <h3 class="text-sm font-extrabold text-gray-900 uppercase tracking-wider">
                            Jadwal Pelaksanaan & Wilayah
                        </h3>
                        <p class="text-[11px] text-gray-500">Rentang waktu pelaksanaan kegiatan dan cakupan wilayah kabupaten/kota</p>
                    </div>
                </div>

                {{-- RENTANG TANGGAL & JAM PELAKSANAAN --}}
                <div class="space-y-3 bg-white p-4 rounded-xl border border-amber-200/70 shadow-2xs">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                                Tanggal Mulai <span class="text-rose-500">*</span>
                            </label>
                            <input type="date"
                                   name="start_date"
                                   id="startDate"
                                   value="{{ old('start_date', date('Y-m-d')) }}"
                                   required
                                   class="w-full px-3.5 py-2 bg-gray-50 focus:bg-white border border-gray-200 rounded-xl text-xs sm:text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                                Tanggal Selesai <span class="text-rose-500">*</span>
                            </label>
                            <input type="date"
                                   name="date_akhir"
                                   id="dateAkhir"
                                   value="{{ old('date_akhir', date('Y-m-d')) }}"
                                   required
                                   class="w-full px-3.5 py-2 bg-gray-50 focus:bg-white border border-gray-200 rounded-xl text-xs sm:text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-3 border-t border-gray-100">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5 flex items-center justify-between">
                                <span>Jam Mulai</span>
                                <span class="text-[10px] text-amber-700 font-semibold bg-amber-50 px-1.5 py-0.5 rounded">WITA</span>
                            </label>
                            <input type="time"
                                   name="start_jam"
                                   id="startJam"
                                   value="{{ old('start_jam', '08:00') }}"
                                   class="w-full px-3.5 py-2 bg-gray-50 focus:bg-white border border-gray-200 rounded-xl text-xs sm:text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5 flex items-center justify-between">
                                <span>Jam Selesai</span>
                                <span class="text-[10px] text-amber-700 font-semibold bg-amber-50 px-1.5 py-0.5 rounded">WITA</span>
                            </label>
                            <input type="time"
                                   name="end_jam"
                                   id="endJam"
                                   value="{{ old('end_jam', '16:00') }}"
                                   class="w-full px-3.5 py-2 bg-gray-50 focus:bg-white border border-gray-200 rounded-xl text-xs sm:text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition">
                        </div>
                    </div>
                </div>

                {{-- DROPDOWN WILAYAH KEGIATAN --}}
                <div class="bg-white p-4 rounded-xl border border-amber-200/70 shadow-2xs">
                    <x-dropdown-wilayah :selected="old('wilayah', [])" />
                </div>
            </div> {{-- END SECTION 2 --}}

            {{-- SECTION 3: PENUGASAN TIM --}}
            <div class="space-y-5 bg-emerald-50/40 p-5 sm:p-6 rounded-2xl border border-emerald-200/80">
                <div class="flex items-center justify-between pb-3 border-b border-emerald-200/60">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-emerald-600 text-white font-extrabold text-sm flex items-center justify-center shadow-xs">
                            3
                        </div>
                        <div>
                            <h3 class="text-sm font-extrabold text-gray-900 uppercase tracking-wider">
                                Penugasan PJ & Anggota Tim
                            </h3>
                            <p class="text-[11px] text-gray-500">Tentukan penanggung jawab dan delegasikan tugas kepada anggota tim</p>
                        </div>
                    </div>
                    <span class="text-[10px] font-bold text-emerald-800 bg-emerald-100 px-2.5 py-1 rounded-lg border border-emerald-200">
                        Prioritas: Ketua Tim / Ahli Madya
                    </span>
                </div>

                {{-- 1. PENANGGUNG JAWAB (PJ) --}}
                <div class="bg-white p-4 rounded-xl border border-emerald-200/70 shadow-2xs">
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                        Penanggung Jawab (PJ) Kegiatan <span class="text-rose-500">*</span>
                    </label>
                    @php
                        $defaultPj = old('pj', (!Auth::user()->isAdmin() ? (Auth::user()->nama_lengkap ?? '') : ''));
                    @endphp
                    <div class="relative">
                        <select name="pj"
                                id="selectPJ"
                                required
                                class="w-full px-3.5 py-2 bg-gray-50 focus:bg-white border border-gray-200 rounded-xl text-xs sm:text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 transition">
                            <option value="">-- Pilih Penanggung Jawab (PJ) --</option>
                            @if(isset($eligiblePJs) && $eligiblePJs->count() > 0)
                                <optgroup label="Pejabat, Ketua Tim, & Ahli Madya (Eligible PJ)">
                                    @foreach($eligiblePJs as $u)
                                        <option value="{{ $u->nama_lengkap }}" {{ $defaultPj == $u->nama_lengkap ? 'selected' : '' }}>
                                            {{ $u->select_option_label }}
                                        </option>
                                    @endforeach
                                </optgroup>
                                <optgroup label="Pegawai BPS">
                                    @foreach($allUsers->diff($eligiblePJs) as $u)
                                        <option value="{{ $u->nama_lengkap }}" {{ $defaultPj == $u->nama_lengkap ? 'selected' : '' }}>
                                            {{ $u->select_option_label }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @else
                                @foreach($allUsers as $u)
                                    <option value="{{ $u->nama_lengkap }}" {{ $defaultPj == $u->nama_lengkap ? 'selected' : '' }}>
                                        {{ $u->select_option_label }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                </div>

                {{-- 2. PILIH ANGGOTA TIM DENGAN GRUP, SELECT ALL, LIVE SEARCH --}}
                <div class="space-y-3 pt-2">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider">
                                Pilih Anggota Tim
                            </label>
                            <p class="text-[11px] text-gray-400">Centang pegawai yang dilibatkan dalam kegiatan ini.</p>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <button type="button" onclick="selectAllAnggota(true)" class="text-[11px] font-bold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 px-3 py-1 rounded-lg border border-emerald-200 transition">
                                Select All Filter
                            </button>
                            <button type="button" onclick="selectAllAnggota(false)" class="text-[11px] font-bold text-gray-600 bg-gray-100 hover:bg-gray-200 px-3 py-1 rounded-lg transition">
                                Clear All
                            </button>
                        </div>
                    </div>

                    {{-- Quick Filter per Tim & Live Search --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        <div>
                            <select id="filterTimPegawai" onchange="filterPegawaiList()" class="w-full px-3 py-1.5 text-xs rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition">
                                <option value="all">-- Tampilkan Semua Tim --</option>
                                @foreach($masterGroups as $g)
                                    <option value="{{ $g->grup }}">{{ $g->grup }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="relative">
                            <input type="text"
                                   id="searchPegawaiInput"
                                   onkeyup="filterPegawaiList()"
                                   placeholder="Ketik nama / NIP pegawai..."
                                   class="w-full pl-8 pr-3 py-1.5 text-xs rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition">
                            <svg class="w-4 h-4 text-gray-400 absolute left-2.5 top-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                    </div>

                    {{-- Pegawai Checkbox Grid (Scrollable dalam 1 Layar) --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 p-3 bg-gray-50/70 rounded-xl border border-gray-200 max-h-[300px] overflow-y-auto" id="pegawaiListContainer">
                        @foreach($allUsers as $usr)
                            @php
                                $userGroup = $usr->team_name ?? '-';
                            @endphp
                            <label class="pegawai-card flex items-center gap-2.5 p-2 rounded-lg bg-white border border-gray-200 hover:border-emerald-400 cursor-pointer transition text-xs select-none"
                                   data-group="{{ $userGroup }}"
                                   data-name="{{ strtolower($usr->nama_lengkap) }} {{ strtolower($usr->formatted_nip) }}">
                                <input type="checkbox"
                                       name="anggota[]"
                                       value="{{ $usr->nama_lengkap }}"
                                       class="anggota-checkbox rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 shrink-0">
                                <div class="min-w-0 flex-1">
                                    <p class="font-bold text-gray-800 truncate text-[11px]">{{ $usr->nama_lengkap }}</p>
                                    <p class="text-[10px] text-gray-400 truncate">{{ $userGroup }}</p>
                                </div>
                            </label>
                        @endforeach
                    </div>

                    {{-- Selected Members Summary Counter --}}
                    <div class="flex items-center justify-between text-xs text-gray-500 px-1 pt-1">
                        <span id="counterSelectedAnggota" class="font-bold text-emerald-700">0 anggota tim dipilih</span>
                        <x-badge variant="success">Notifikasi otomatis ke anggota</x-badge>
                    </div>
                </div>

            </div>

        </div>

        {{-- BOTTOM ACTION BAR --}}
        <div class="pt-6 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-4">
            <p class="text-xs text-gray-400">
                Pastikan informasi agenda, jadwal, dan penugasan tim sudah sesuai sebelum menerbitkan kegiatan.
            </p>
            <div class="flex items-center gap-3 w-full sm:w-auto justify-end">
                <x-button variant="secondary" href="{{ route('kegiatan.daftar') }}">
                    Batal
                </x-button>
                <x-button type="submit" variant="primary" size="lg">
                    <x-slot name="icon">
                        <svg class="w-4 h-4 text-white shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </x-slot>
                    Terbitkan Kegiatan
                </x-button>
            </div>
        </div>

    </form>
</div>

{{-- MODAL UPLOAD TEMPLATE PST STARLA --}}
<div id="modalUploadPst" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs hidden transition-opacity">
    <div class="bg-white rounded-3xl max-w-lg w-full shadow-2xl overflow-hidden border border-slate-200 animate-in fade-in zoom-in-95 duration-200">
        <div class="bg-gradient-to-r from-emerald-600 via-teal-600 to-cyan-700 p-5 text-white flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-white/20 flex items-center justify-center text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-black text-sm text-white">Upload Jadwal Petugas PST (Starla)</h3>
                    <p class="text-[11px] text-emerald-100">Jadwal Harian Senin - Jumat & Penugasan Petugas</p>
                </div>
            </div>
            <button type="button" onclick="document.getElementById('modalUploadPst').classList.add('hidden')" class="w-8 h-8 rounded-xl bg-white/10 hover:bg-white/25 flex items-center justify-center text-white transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form action="{{ route('ketua-tim.upload-pst') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
            @csrf

            <div class="p-3.5 rounded-2xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-950 space-y-1.5">
                <div class="flex items-center gap-1.5 font-bold text-emerald-900">
                    <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Ketentuan Template Excel Starla:</span>
                </div>
                <ul class="text-[11px] text-emerald-800 space-y-1 pl-5 list-disc">
                    <li>Sheet <strong>"Entri Disini"</strong> berisi tanggal, sesi (1: Pagi, 2: Siang), & petugas 1 s.d. 4.</li>
                    <li>Sheet <strong>"petugas"</strong> berisi pemetaan nama panggilan ke nama lengkap & NIP pegawai.</li>
                    <li>Sistem otomatis hanya mengimpor hari kerja (<strong>Senin s.d. Jumat</strong>) dan melewati akhir pekan (Sabtu & Minggu).</li>
                    <li>Setiap jadwal sesi otomatis muncul di <strong>Kalender Dashboard</strong> dan dapat ditekan untuk melihat detail petugas bertugas.</li>
                </ul>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">
                    Pilih File Template Excel (.xlsx)
                </label>
                <input type="file" 
                       name="file_pst" 
                       accept=".xlsx,.xls"
                       class="w-full text-xs text-slate-600 bg-slate-50 border border-slate-200 rounded-xl file:mr-3 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-emerald-600 file:text-white hover:file:bg-emerald-700 cursor-pointer">
                <p class="text-[10px] text-slate-400 mt-1">Kosongkan jika ingin langsung menggunakan template PST September yang telah tersedia di sistem.</p>
            </div>

            <div class="pt-2 flex items-center justify-between gap-3 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('modalUploadPst').classList.add('hidden')" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition flex items-center gap-1.5 shadow-sm active:scale-95">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    <span>Import Jadwal Petugas PST</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function selectAllAnggota(checked) {
    document.querySelectorAll('.pegawai-card').forEach(card => {
        if (card.style.display !== 'none') {
            const cb = card.querySelector('.anggota-checkbox');
            if (cb) cb.checked = checked;
        }
    });
    updateAnggotaCounter();
}

function filterPegawaiList() {
    const selectedGroup = document.getElementById('filterTimPegawai').value;
    const searchVal = document.getElementById('searchPegawaiInput').value.toLowerCase().trim();

    document.querySelectorAll('.pegawai-card').forEach(card => {
        const group = card.getAttribute('data-group');
        const name = card.getAttribute('data-name');

        const matchGroup = (selectedGroup === 'all' || group === selectedGroup);
        const matchSearch = (!searchVal || name.includes(searchVal));

        if (matchGroup && matchSearch) {
            card.style.display = 'flex';
        } else {
            card.style.display = 'none';
        }
    });
}

function updateAnggotaCounter() {
    const total = document.querySelectorAll('.anggota-checkbox:checked').length;
    document.getElementById('counterSelectedAnggota').textContent = total + ' anggota tim dipilih';
}

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.anggota-checkbox').forEach(cb => {
        cb.addEventListener('change', updateAnggotaCounter);
    });

    const selectTim = document.getElementById('selectTim');
    if (selectTim) {
        selectTim.addEventListener('change', function() {
            const val = this.value;
            const filterTim = document.getElementById('filterTimPegawai');
            if (filterTim && val) {
                filterTim.value = val;
                filterPegawaiList();
            }
        });
    }
});

function handleSelectMasterProyek(selectEl) {
    const selectedOpt = selectEl.options[selectEl.selectedIndex];
    if (!selectedOpt || !selectedOpt.value) {
        const fb = document.getElementById('proyekFeedback');
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

    // 1. Set Nama / Agenda Kegiatan
    const inputAgenda = document.querySelector('input[name="agenda"]');
    if (inputAgenda && nama) {
        inputAgenda.value = nama;
    }

    // 2. Set Tim / Fungsi Pengampu
    const selectTim = document.getElementById('selectTim');
    if (selectTim && tim) {
        let matchedTim = false;
        const targetTim = tim.toLowerCase().trim();
        for (let i = 0; i < selectTim.options.length; i++) {
            const optVal = selectTim.options[i].value.toLowerCase().trim();
            const optText = selectTim.options[i].text.toLowerCase().trim();
            if (optVal === targetTim || optText.includes(targetTim) || targetTim.includes(optVal)) {
                selectTim.selectedIndex = i;
                matchedTim = true;
                break;
            }
        }
        if (!matchedTim) {
            const newOpt = new Option(tim, tim, true, true);
            selectTim.add(newOpt);
        }
        const filterTim = document.getElementById('filterTimPegawai');
        if (filterTim) {
            filterTim.value = selectTim.value;
            filterPegawaiList();
        }
    }

    // 3. Set Penanggung Jawab (PJ)
    const selectPJ = document.getElementById('selectPJ');
    if (selectPJ && pjNama) {
        let matchedPj = false;
        const targetPj = pjNama.toLowerCase().trim();
        for (let i = 0; i < selectPJ.options.length; i++) {
            const optVal = selectPJ.options[i].value.toLowerCase().trim();
            const optText = selectPJ.options[i].text.toLowerCase().trim();
            if (optVal === targetPj || optText.includes(targetPj) || targetPj.includes(optVal)) {
                selectPJ.selectedIndex = i;
                matchedPj = true;
                break;
            }
        }
        if (!matchedPj) {
            const label = pjNama + (pjNip ? ' (' + pjNip + ' - PJ SIMPATI)' : ' (PJ SIMPATI)');
            const newPjOpt = new Option(label, pjNama, true, true);
            selectPJ.add(newPjOpt);
        }
    }

    // 4. Rekomendasikan & Centang Anggota Proyek
    let checkedCount = 0;
    if (anggotaList.length > 0) {
        // Reset centang sebelumnya
        document.querySelectorAll('.anggota-checkbox').forEach(cb => {
            cb.checked = false;
        });

        // Tampilkan semua card untuk pencocokan
        const filterTim = document.getElementById('filterTimPegawai');
        if (filterTim) {
            filterTim.value = 'all';
            filterPegawaiList();
        }

        anggotaList.forEach(m => {
            const targetNama = (m.nama || '').toLowerCase().trim();
            const targetNip = (m.nip || '').trim();

            document.querySelectorAll('.pegawai-card').forEach(card => {
                const cardName = (card.getAttribute('data-name') || '').toLowerCase();
                const cb = card.querySelector('.anggota-checkbox');
                if (cb && !cb.checked) {
                    if ((targetNama && cardName.includes(targetNama)) || (targetNip && cardName.includes(targetNip))) {
                        cb.checked = true;
                        card.style.display = 'flex';
                        checkedCount++;
                    }
                }
            });
        });

        updateAnggotaCounter();
    }

    // 5. Tampilkan Feedback Visual
    const fb = document.getElementById('proyekFeedback');
    const fbText = document.getElementById('proyekFeedbackText');
    if (fb && fbText) {
        let msg = `Proyek "${nama}" dipilih: Tim [${tim}], PJ [${pjNama || 'Belum Ada PJ'}]`;
        if (checkedCount > 0) {
            msg += `, dan ${checkedCount} anggota proyek otomatis dicentang.`;
        } else if (anggotaList.length > 0) {
            msg += `, serta ${anggotaList.length} anggota tim terdaftar.`;
        }
        fbText.textContent = msg;
        fb.classList.remove('hidden');
    }
}
</script>

@endsection