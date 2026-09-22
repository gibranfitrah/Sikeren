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

    {{-- FORMULIR 1 LAYAR TERPADU --}}
    <form id="formKegiatan"
          action="{{ route('ketua-tim.store') }}"
          method="POST"
          class="bg-white rounded-2xl border border-gray-200 shadow-xs p-6 sm:p-8 space-y-6">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            {{-- KOLOM KIRI (5 COLS): INFORMASI KEGIATAN & WAKTU / WILAYAH --}}
            <div class="lg:col-span-5 space-y-6">
                
                {{-- SECTION 1: TIM & AGENDA --}}
                <div class="space-y-4">
                    <div class="flex items-center gap-2.5 pb-2 border-b border-gray-100">
                        <div class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 font-bold text-xs flex items-center justify-center border border-blue-100">
                            1
                        </div>
                        <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider">
                            Tim & Agenda Kegiatan
                        </h3>
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

                {{-- SECTION 2: JADWAL & WILAYAH --}}
                <div class="space-y-4 pt-2">
                    <div class="flex items-center gap-2.5 pb-2 border-b border-gray-100">
                        <div class="w-7 h-7 rounded-lg bg-amber-50 text-amber-600 font-bold text-xs flex items-center justify-center border border-amber-100">
                            2
                        </div>
                        <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider">
                            Jadwal Pelaksanaan & Wilayah
                        </h3>
                    </div>

                    {{-- RENTANG TANGGAL --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 bg-amber-50/40 p-3.5 rounded-xl border border-amber-100">
                        <div>
                            <label class="block text-[11px] font-bold text-gray-700 uppercase tracking-wider mb-1">
                                Tanggal Mulai <span class="text-rose-500">*</span>
                            </label>
                            <input type="date"
                                   name="start_date"
                                   id="startDate"
                                   value="{{ old('start_date', date('Y-m-d')) }}"
                                   required
                                   class="w-full px-3 py-1.5 bg-white border border-gray-200 rounded-lg text-xs text-gray-800 focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition">
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-gray-700 uppercase tracking-wider mb-1">
                                Tanggal Selesai <span class="text-rose-500">*</span>
                            </label>
                            <input type="date"
                                   name="date_akhir"
                                   id="dateAkhir"
                                   value="{{ old('date_akhir', date('Y-m-d')) }}"
                                   required
                                   class="w-full px-3 py-1.5 bg-white border border-gray-200 rounded-lg text-xs text-gray-800 focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition">
                        </div>
                    </div>

                    {{-- DROPDOWN WILAYAH KEGIATAN --}}
                    <div>
                        <x-dropdown-wilayah :selected="old('wilayah', [])" />
                    </div>
                </div>

            </div>

            {{-- KOLOM KANAN (7 COLS): PENUGASAN PJ & ANGGOTA TIM --}}
            <div class="lg:col-span-7 space-y-5">
                
                {{-- SECTION 3: PENUGASAN TIM --}}
                <div class="flex items-center justify-between pb-2 border-b border-gray-100">
                    <div class="flex items-center gap-2.5">
                        <div class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 font-bold text-xs flex items-center justify-center border border-emerald-100">
                            3
                        </div>
                        <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider">
                            Penugasan PJ & Anggota Tim
                        </h3>
                    </div>
                    <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200">
                        Prioritas: Ketua Tim / Ahli Madya
                    </span>
                </div>

                {{-- 1. PENANGGUNG JAWAB (PJ) --}}
                <div>
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