@extends('layouts.app')

@section('title', 'Buat Kegiatan Baru - Sikeren')
@section('header_title', 'Buat Kegiatan Baru')

@section('content')

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-4 space-y-6">

    {{-- HEADER --}}
    <x-heading title="Buat Kegiatan Baru"
               subtitle="Alur 3 langkah: Pilih Tim & Agenda, Tentukan Rentang Waktu & Wilayah, Penugasan PJ & Anggota Tim"
               tag="Manajemen Tim"
               subtag="Form Kegiatan Baru"
               backUrl="{{ route('kegiatan.daftar') }}" />

    {{-- ERROR VALIDATION --}}
    @if($errors->any())
        <div class="bg-rose-50 border border-rose-200 rounded-2xl p-5 shadow-xs">
            <div class="flex items-start gap-3">
                <div class="w-9 h-9 bg-rose-100 rounded-xl flex items-center justify-center text-rose-600 shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M5.07 19h13.86c1.54 0 2.5-1.67 1.73-3L13.73 4c-.77-1.33-2.69-1.33-3.46 0L3.34 16c-.77 1.33.19 3 1.73 3z"/>
                    </svg>
                </div>
                <div>
                    <p class="font-bold text-rose-800 text-sm">
                        Periksa kembali data yang dimasukkan:
                    </p>
                    <ul class="mt-1.5 text-xs text-rose-700 space-y-1">
                        @foreach($errors->all() as $error)
                            <li>• {{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <form id="formKegiatan"
          action="{{ route('ketua-tim.store') }}"
          method="POST"
          class="space-y-6">
        @csrf

        {{-- =====================================================
            LANGKAH 1: PILIH TIM DULU, BARU NAMA KEGIATAN
        ====================================================== --}}
        <div class="bg-white rounded-2xl p-6 sm:p-7 shadow-xs border border-gray-200 border-l-4 border-l-blue-600 relative">
            <div class="flex items-center gap-3.5 mb-6">
                <div class="w-10 h-10 bg-blue-50 rounded-xl flex items-center justify-center text-blue-600 font-bold text-sm">
                    1
                </div>
                <div>
                    <h3 class="text-base font-bold text-gray-900">
                        1. Informasi Tim & Nama Kegiatan
                    </h3>
                    <p class="text-xs text-gray-500">
                        Tentukan tim kerja pengampu terlebih dahulu, kemudian masukkan agenda kegiatan.
                    </p>
                </div>
            </div>

            <div class="space-y-5">
                {{-- PILIH TIM / FUNGSI --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                        Pilih Tim / Fungsi Pengampu <span class="text-rose-500">*</span>
                    </label>
                    <select name="tim"
                            id="selectTim"
                            required
                            class="w-full px-4 py-2.5 bg-gray-50 focus:bg-white border border-gray-200 rounded-xl text-xs sm:text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition">
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
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                        Nama / Agenda Kegiatan <span class="text-rose-500">*</span>
                    </label>
                    <input type="text"
                           name="agenda"
                           value="{{ old('agenda') }}"
                           placeholder="Contoh: Survei Sosial Ekonomi Nasional (Susenas) Triwulan III"
                           required
                           class="w-full px-4 py-2.5 bg-gray-50 focus:bg-white border border-gray-200 rounded-xl text-xs sm:text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition">
                </div>

                {{-- PERIHAL / SUB JUDUL & DASAR SURAT --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                            Perihal / Ringkasan Tugas
                        </label>
                        <input type="text"
                               name="perihal"
                               value="{{ old('perihal') }}"
                               placeholder="Contoh: Pelaksanaan Lapangan dan Pengawasan"
                               class="w-full px-4 py-2.5 bg-gray-50 focus:bg-white border border-gray-200 rounded-xl text-xs sm:text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                            Dasar Pelaksanaan (No. Surat / Dokumen)
                        </label>
                        <input type="text"
                               name="dasar"
                               value="{{ old('dasar') }}"
                               placeholder="Contoh: Surat Tugas No. B-120/7400/VS/2026"
                               class="w-full px-4 py-2.5 bg-gray-50 focus:bg-white border border-gray-200 rounded-xl text-xs sm:text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition">
                    </div>
                </div>
            </div>
        </div>

        {{-- =====================================================
            LANGKAH 2: WAKTU (RANGE TANGGAL) & WILAYAH DROPDOWN
        ====================================================== --}}
        <div class="bg-white rounded-2xl p-6 sm:p-7 shadow-xs border border-gray-200 border-l-4 border-l-amber-500 relative z-30">
            <div class="flex items-center gap-3.5 mb-6">
                <div class="w-10 h-10 bg-amber-50 rounded-xl flex items-center justify-center text-amber-600 font-bold text-sm">
                    2
                </div>
                <div>
                    <h3 class="text-base font-bold text-gray-900">
                        2. Waktu Pelaksanaan & Wilayah Kegiatan
                    </h3>
                    <p class="text-xs text-gray-500">
                        Tentukan rentang tanggal (Start &ndash; End) dan pilih satu atau lebih wilayah cakupan kegiatan via dropdown.
                    </p>
                </div>
            </div>

            <div class="space-y-6">
                {{-- RENTANG TANGGAL --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 bg-amber-50/40 p-4 rounded-2xl border border-amber-100">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                            Tanggal Mulai (Start Date) <span class="text-rose-500">*</span>
                        </label>
                        <input type="date"
                               name="start_date"
                               id="startDate"
                               value="{{ old('start_date', date('Y-m-d')) }}"
                               required
                               class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl text-xs sm:text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                            Tanggal Selesai (End Date) <span class="text-rose-500">*</span>
                        </label>
                        <input type="date"
                               name="date_akhir"
                               id="dateAkhir"
                               value="{{ old('date_akhir', date('Y-m-d')) }}"
                               required
                               class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl text-xs sm:text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition">
                    </div>
                </div>

                {{-- DROPDOWN WILAYAH KEGIATAN --}}
                <div>
                    <x-dropdown-wilayah :selected="old('wilayah', [])" />
                </div>
            </div>
        </div>

        {{-- =====================================================
            LANGKAH 3: PENUGASAN TIM, PJ DULU, LALU ANGGOTA
        ====================================================== --}}
        <div class="bg-white rounded-2xl p-6 sm:p-7 shadow-xs border border-gray-200 border-l-4 border-l-emerald-600 relative z-10">
            <div class="flex items-center gap-3.5 mb-6">
                <div class="w-10 h-10 bg-emerald-50 rounded-xl flex items-center justify-center text-emerald-600 font-bold text-sm">
                    3
                </div>
                <div>
                    <h3 class="text-base font-bold text-gray-900">
                        3. Penugasan Tim, PJ, & Anggota
                    </h3>
                    <p class="text-xs text-gray-500">
                        Tentukan Penanggung Jawab (PJ) kegiatan, lalu pilih anggota tim melalui grup/tim atau pencarian.
                    </p>
                </div>
            </div>

            <div class="space-y-6">
                {{-- 1. PENANGGUNG JAWAB (PJ) --}}
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider">
                            Penanggung Jawab (PJ) Kegiatan <span class="text-rose-500">*</span>
                        </label>
                        <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200">
                            ⭐ Prioritas: Minimal Ketua Tim / Ahli Madya
                        </span>
                    </div>
                    @php
                        $defaultPj = old('pj', (!Auth::user()->isAdmin() ? (Auth::user()->nama_lengkap ?? '') : ''));
                    @endphp
                    <div class="relative">
                        <select name="pj"
                                id="selectPJ"
                                required
                                class="w-full px-4 py-2.5 bg-gray-50 focus:bg-white border border-gray-200 rounded-xl text-xs sm:text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 transition">
                            <option value="">-- Pilih Penanggung Jawab (PJ) --</option>
                            @if(isset($eligiblePJs) && $eligiblePJs->count() > 0)
                                <optgroup label="⭐ Pejabat, Ketua Tim, & Ahli Madya (Eligible PJ)">
                                    @foreach($eligiblePJs as $u)
                                        <option value="{{ $u->nama_lengkap }}" {{ $defaultPj == $u->nama_lengkap ? 'selected' : '' }}>
                                            {{ $u->nama_lengkap }} ({{ $u->formatted_nip }}) - {{ $u->role_label }}
                                        </option>
                                    @endforeach
                                </optgroup>
                                <optgroup label="📋 Pegawai Lainnya">
                                    @foreach($allUsers->diff($eligiblePJs) as $u)
                                        <option value="{{ $u->nama_lengkap }}" {{ $defaultPj == $u->nama_lengkap ? 'selected' : '' }}>
                                            {{ $u->nama_lengkap }} ({{ $u->formatted_nip }}) - {{ $u->role_label }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @else
                                @foreach($allUsers as $u)
                                    <option value="{{ $u->nama_lengkap }}" {{ $defaultPj == $u->nama_lengkap ? 'selected' : '' }}>
                                        {{ $u->nama_lengkap }} ({{ $u->formatted_nip }}) - {{ $u->role_label }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                </div>

                {{-- 2. PILIH ANGGOTA TIM DENGAN GRUP, SELECT ALL, SEARCH --}}
                <div class="border-t border-gray-100 pt-5">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-3">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider">
                                Pilih Anggota Tim
                            </label>
                            <p class="text-[11px] text-gray-400">Pilih dari grup/tim atau cari nama pegawai di bawah.</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" onclick="selectAllAnggota(true)" class="text-[11px] font-bold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 px-3 py-1.5 rounded-lg border border-emerald-200">
                                Select All Filter
                            </button>
                            <button type="button" onclick="selectAllAnggota(false)" class="text-[11px] font-bold text-gray-600 bg-gray-100 hover:bg-gray-200 px-3 py-1.5 rounded-lg">
                                Clear All
                            </button>
                        </div>
                    </div>

                    {{-- Quick Filter per Tim & Live Search --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-4">
                        <div>
                            <select id="filterTimPegawai" onchange="filterPegawaiList()" class="w-full px-3 py-2 text-xs rounded-xl border border-gray-200 bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
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
                                   placeholder="Ketik nama atau NIP pegawai..."
                                   class="w-full pl-8 pr-3 py-2 text-xs rounded-xl border border-gray-200 bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                            <svg class="w-4 h-4 text-gray-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                    </div>

                    {{-- Pegawai Checkbox Card Grid --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2.5 p-4 bg-gray-50/70 rounded-2xl border border-gray-200 max-h-72 overflow-y-auto" id="pegawaiListContainer">
                        @foreach($allUsers as $usr)
                            @php
                                $userGroup = $usr->team_name ?? '-';
                            @endphp
                            <label class="pegawai-card flex items-center gap-3 p-2.5 rounded-xl bg-white border border-gray-200 hover:border-emerald-400 cursor-pointer transition text-xs select-none"
                                   data-group="{{ $userGroup }}"
                                   data-name="{{ strtolower($usr->nama_lengkap) }} {{ strtolower($usr->formatted_nip) }}">
                                <input type="checkbox"
                                       name="anggota[]"
                                       value="{{ $usr->nama_lengkap }}"
                                       class="anggota-checkbox rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 shrink-0">
                                <div class="min-w-0 flex-1">
                                    <p class="font-bold text-gray-800 truncate">{{ $usr->nama_lengkap }}</p>
                                    <p class="text-[10px] text-gray-400 truncate">{{ $userGroup }}</p>
                                </div>
                            </label>
                        @endforeach
                    </div>

                    {{-- Selected Members Summary Counter --}}
                    <div class="mt-3 flex items-center justify-between text-xs text-gray-500 px-1">
                        <span id="counterSelectedAnggota">0 anggota tim dipilih</span>
                        <x-badge variant="success">Notifikasi otomatis ke anggota</x-badge>
                    </div>
                </div>
            </div>
        </div>

        {{-- TOMBOL AKSI --}}
        <div class="flex items-center justify-end gap-3 pt-2">
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
    </form>
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
</script>

@endsection