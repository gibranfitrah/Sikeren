@extends('layouts.app')

@section('title', 'Kelola Jadwal Layanan - SIMPATI')
@section('header_title', 'Kelola Jadwal Layanan')

@section('content')
@php
    $pageTitle = 'Kelola Jadwal Layanan';
    $pageSubtitle = 'Jadwal harian petugas ' . $grupLabel . ' per bulan. Kalender dan rekap dapat diisi manual, lewat unggah Excel, atau digabung dengan jadwal PST dari STARLA (baca-saja).';
    $canManage = in_array($grup, $manageable, true);
    $today = now()->toDateString();
@endphp

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6" x-data="jadwalUi()">

    @include('admin.simpati._nav')

    {{-- Tabs Grup --}}
    <div class="flex flex-wrap gap-2 mb-5">
        @foreach($grups as $g)
            <a href="{{ route('simpati.jadwal.index', ['grup' => $g, 'bulan' => $bulanInput]) }}"
               class="px-4 py-2.5 rounded-xl text-xs font-bold transition {{ $grup === $g ? 'bg-slate-900 text-white shadow-lg' : 'bg-white text-gray-500 border border-gray-200 hover:border-slate-300' }}">
                {{ \App\Support\SimpatiRoles::grupLabel($g) }}
                <span class="ml-1.5 px-1.5 py-0.5 rounded-md text-[10px] {{ $grup === $g ? 'bg-white/20' : 'bg-gray-100' }}">{{ $grupCounts[$g] ?? 0 }}</span>
            </a>
        @endforeach
    </div>

    {{-- Toolbar Bulan + Aksi --}}
    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-5 mb-5">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <a href="{{ route('simpati.jadwal.index', ['grup' => $grup, 'bulan' => $prevBulan]) }}"
                   title="Bulan sebelumnya"
                   class="w-9 h-9 rounded-xl border border-gray-200 hover:border-blue-300 hover:text-blue-600 text-gray-500 flex items-center justify-center transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                </a>
                <span class="text-sm font-black text-gray-800 min-w-[150px] text-center">{{ $bulanLabel }}</span>
                <a href="{{ route('simpati.jadwal.index', ['grup' => $grup, 'bulan' => $nextBulan]) }}"
                   title="Bulan berikutnya"
                   class="w-9 h-9 rounded-xl border border-gray-200 hover:border-blue-300 hover:text-blue-600 text-gray-500 flex items-center justify-center transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </a>
                <form method="GET" action="{{ route('simpati.jadwal.index') }}">
                    <input type="hidden" name="grup" value="{{ $grup }}">
                    <input type="month" name="bulan" value="{{ $bulanInput }}"
                           onchange="this.form.submit()"
                           class="px-3 py-2 rounded-xl border border-gray-200 text-xs font-bold text-gray-600 outline-none focus:ring-2 focus:ring-blue-500">
                </form>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                @if($canManage)
                    <a href="{{ route('simpati.jadwal.template', ['grup' => $grup]) }}"
                       class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl bg-white border border-gray-200 text-gray-600 text-xs font-bold hover:border-emerald-300 hover:text-emerald-600 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        Template Excel
                    </a>
                    <button type="button" @click="uploadOpen = true"
                            class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl bg-blue-600 text-white text-xs font-bold hover:bg-blue-700 transition shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                        Unggah Jadwal
                    </button>
                    <button type="button" @click="openAdd()"
                            class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl bg-slate-900 text-white text-xs font-bold hover:bg-slate-700 transition shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Tambah Jadwal
                    </button>
                @else
                    <span class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-gray-50 border border-gray-200 text-[11px] font-bold text-gray-400">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                        Mode lihat saja untuk grup ini
                    </span>
                @endif
            </div>
        </div>

        @if($grup === 'pst')
            <p class="text-[11px] text-gray-400 mt-3 pt-3 border-t border-gray-100">
                Catatan: jadwal petugas PST dari sistem STARLA tampil otomatis dengan label
                <span class="font-bold text-indigo-600">STARLA</span> dan bersifat baca-saja (kehadiran dikelola di modul STARLA).
            </p>
        @endif
    </div>

    @php $jumlahStarla = collect($entries)->where('is_starla', true)->count(); @endphp

    @if(count($entries) === 0)
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-10 text-center mb-5">
            <div class="w-14 h-14 rounded-2xl bg-gray-50 border border-gray-100 mx-auto flex items-center justify-center mb-3">
                <svg class="w-7 h-7 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3M4 11h16M5 21h14a1 1 0 001-1V6a1 1 0 00-1-1H5a1 1 0 00-1 1v14a1 1 0 001 1z"></path></svg>
            </div>
            <p class="text-sm font-bold text-gray-600">Belum ada jadwal {{ $grupLabel }} untuk {{ $bulanLabel }}</p>
            <p class="text-xs text-gray-400 mt-1">{{ $canManage ? 'Tambah manual atau unggah Excel untuk mengisi jadwal bulan ini.' : 'Jadwal akan muncul bila tersedia.' }}</p>
        </div>
    @endif

    {{-- Kalender --}}
    @if(count($entries) > 0)
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden mb-5">
            <div class="grid grid-cols-7 bg-gray-50 border-b border-gray-100 text-[11px] font-bold uppercase tracking-wider text-gray-400">
                @foreach(['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'] as $hari)
                    <div class="px-3 py-2.5 text-center">{{ $hari }}</div>
                @endforeach
            </div>
            <div class="grid grid-cols-7">
                @foreach($cells as $cell)
                    @php
                        $cellEntries = $byDate->get($cell, []);
                        $cellDate = \Carbon\Carbon::parse($cell);
                        $isToday = $cell === $today;
                        $isOtherMonth = $cellDate->format('Y-m') !== $bulanInput;
                        $isSunday = $cellDate->isSunday();
                    @endphp
                    <div class="min-h-[104px] border-r border-b border-gray-100 p-2 {{ $isOtherMonth ? 'bg-gray-50/60' : 'bg-white' }} {{ $isSunday ? 'border-r-0' : '' }}">
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="text-[11px] font-bold {{ $isToday ? 'inline-flex w-6 h-6 rounded-lg bg-blue-600 text-white items-center justify-center' : ($isOtherMonth ? 'text-gray-300' : 'text-gray-500') }}">
                                {{ $cellDate->format('j') }}
                            </span>
                            @if(count($cellEntries))
                                <span class="text-[9px] font-bold text-gray-300">{{ count($cellEntries) }}</span>
                            @endif
                        </div>

                        @foreach(collect($cellEntries)->take(3) as $e)
                            <div class="mb-1 px-1.5 py-1 rounded-lg text-[10px] leading-tight {{ $e['is_starla'] ? 'bg-indigo-50 border border-indigo-100' : 'bg-blue-50 border border-blue-100' }}"
                                 title="{{ $e['nama'] }} - {{ $e['status'] }}{{ $e['jam'] !== '-' ? ' (' . $e['jam'] . ')' : '' }}">
                                <span class="font-bold {{ $e['is_starla'] ? 'text-indigo-700' : 'text-blue-800' }} block truncate">{{ $e['nama'] }}</span>
                                <span class="text-gray-400">{{ $e['sesi'] ? 'Sesi ' . $e['sesi'] : '' }}{{ $e['sesi'] && $e['jam'] !== '-' ? ' · ' : '' }}{{ $e['jam'] !== '-' ? $e['jam'] : '' }}</span>
                            </div>
                        @endforeach

                        @if(count($cellEntries) > 3)
                            <span class="text-[9px] font-bold text-gray-400 px-1">+{{ count($cellEntries) - 3 }} lainnya</span>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Rekap Tabel --}}
    @if(count($entries) > 0)
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h3 class="text-sm font-black text-gray-800">Rekap Jadwal {{ $grupLabel }} — {{ $bulanLabel }}</h3>
                    <p class="text-[11px] text-gray-400 mt-0.5">{{ count($entries) }} entri · {{ $jumlahStarla }} dari STARLA</p>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 text-left text-[11px] uppercase tracking-wider text-gray-400">
                            <th class="px-6 py-3 font-bold">Tanggal</th>
                            <th class="px-6 py-3 font-bold">Nama</th>
                            <th class="px-6 py-3 font-bold">Sesi & Jam</th>
                            <th class="px-6 py-3 font-bold">Status</th>
                            <th class="px-6 py-3 font-bold">Sumber</th>
                            <th class="px-6 py-3 font-bold">Keterangan</th>
                            @if($canManage)
                                <th class="px-6 py-3 font-bold text-right">Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($entries as $e)
                            <tr class="hover:bg-gray-50/70 transition-colors">
                                <td class="px-6 py-3.5 whitespace-nowrap text-xs font-semibold text-gray-700">
                                    {{ \Carbon\Carbon::parse($e['tanggal'])->translatedFormat('D, d M Y') }}
                                </td>
                                <td class="px-6 py-3.5">
                                    <p class="font-semibold text-gray-800">{{ $e['nama'] }}</p>
                                    <p class="text-[11px] text-gray-400">{{ $e['niplama'] }}</p>
                                </td>
                                <td class="px-6 py-3.5 text-xs text-gray-600 whitespace-nowrap">
                                    {{ $e['sesi'] ? 'Sesi ' . $e['sesi'] : '—' }} · {{ $e['jam'] }}
                                </td>
                                <td class="px-6 py-3.5">
                                    @if($e['is_starla'] || !$canManage)
                                        @include('admin.simpati._status', ['status' => $e['status']])
                                    @else
                                        <form method="POST" action="{{ route('simpati.jadwal.status', $e['row_id']) }}">
                                            @csrf
                                            <select name="status" onchange="this.form.submit()"
                                                    class="px-2.5 py-1.5 rounded-lg border border-gray-200 text-xs bg-white outline-none focus:ring-2 focus:ring-blue-500 font-semibold text-gray-700">
                                                @foreach($statuses as $s)
                                                    <option value="{{ $s }}" {{ $e['status'] === $s ? 'selected' : '' }}>{{ $s }}</option>
                                                @endforeach
                                            </select>
                                        </form>
                                    @endif
                                </td>
                                <td class="px-6 py-3.5">
                                    @if($e['is_starla'])
                                        <span class="px-2 py-1 rounded-md text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">STARLA</span>
                                    @elseif($e['sumber'] === 'Upload')
                                        <span class="px-2 py-1 rounded-md text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Upload</span>
                                    @else
                                        <span class="px-2 py-1 rounded-md text-[10px] font-bold bg-gray-100 text-gray-500 border border-gray-200">Manual</span>
                                    @endif
                                </td>
                                <td class="px-6 py-3.5 text-xs text-gray-500 max-w-[200px]">{{ $e['keterangan'] }}</td>
                                @if($canManage)
                                    <td class="px-6 py-3.5">
                                        <div class="flex justify-end">
                                            @unless($e['is_starla'])
                                                <form method="POST" action="{{ route('simpati.jadwal.destroy', $e['row_id']) }}"
                                                      onsubmit="return confirm('Hapus jadwal {{ addslashes($e['nama']) }} pada {{ \Carbon\Carbon::parse($e['tanggal'])->translatedFormat('d F Y') }}?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="px-2.5 py-1.5 rounded-lg text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200 hover:bg-rose-100 transition">Hapus</button>
                                                </form>
                                            @else
                                                <span class="text-[10px] text-gray-300 font-semibold italic">baca-saja</span>
                                            @endunless
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- ===== Modal: Tambah Jadwal ===== --}}
    <div x-show="addOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none">
        <div class="absolute inset-0 bg-slate-900/60" @click="addOpen = false"></div>
        <div class="relative bg-white rounded-3xl shadow-2xl w-full max-w-xl p-6 sm:p-7 max-h-[90vh] overflow-y-auto" @click.outside="addOpen = false" x-transition>
            <div class="flex items-start justify-between gap-4 mb-5">
                <div>
                    <h3 class="text-lg font-black text-gray-900">Tambah Jadwal</h3>
                    <p class="text-xs text-gray-400">Grup: <span class="font-bold text-blue-600">{{ $grupLabel }}</span> · {{ $bulanLabel }}</p>
                </div>
                <button type="button" @click="addOpen = false" class="text-gray-300 hover:text-gray-600 text-xl leading-none">&times;</button>
            </div>

            <form method="POST" action="{{ route('simpati.jadwal.store') }}">
                @csrf
                <input type="hidden" name="grup" value="{{ $grup }}">
                <input type="hidden" name="user_id" :value="form.user_id">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-[11px] font-bold text-gray-400 uppercase mb-1.5">Tanggal <span class="text-rose-500">*</span></label>
                        <input type="date" name="tanggal" required x-model="form.tanggal"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-[11px] font-bold text-gray-400 uppercase mb-1.5">Petugas</label>
                        <input type="text" x-model="search" @input.debounce.300ms="searchPegawai()"
                               placeholder="Cari pegawai BPS (nama/NIP)…"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm outline-none focus:ring-2 focus:ring-blue-500">
                        <div class="mt-2 max-h-40 overflow-y-auto rounded-xl border border-gray-100 divide-y divide-gray-50" x-show="results.length > 0">
                            <template x-for="p in results" :key="p.id">
                                <button type="button" @click="pick(p)"
                                        class="w-full text-left px-3.5 py-2.5 text-sm flex items-center justify-between gap-2 transition"
                                        :class="form.user_id === p.id ? 'bg-blue-50' : 'hover:bg-gray-50'">
                                    <span class="min-w-0">
                                        <span class="block font-semibold text-gray-800 truncate" x-text="p.nama_lengkap"></span>
                                        <span class="block text-[11px] text-gray-400 truncate" x-text="p.niplama + ' · ' + p.nm_jabatan"></span>
                                    </span>
                                </button>
                            </template>
                        </div>
                        <div class="mt-2 flex items-center justify-between gap-2" x-show="form.user_id">
                            <span class="text-xs text-gray-500">Dipilih: <span class="font-bold text-gray-800" x-text="pickedName"></span></span>
                            <button type="button" @click="clearPick()" class="text-[11px] font-bold text-rose-500 hover:underline">Ganti</button>
                        </div>
                    </div>

                    <div class="sm:col-span-2 border-t border-gray-100 pt-4">
                        <p class="text-[11px] font-bold text-gray-400 uppercase mb-2">Atau isi manual (petugas non-BPS / tanpa akun)</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[11px] font-bold text-gray-400 uppercase mb-1.5">Nama <span class="text-rose-500">*</span></label>
                                <input type="text" name="nama" x-model="form.nama" maxlength="200"
                                       class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-gray-400 uppercase mb-1.5">NIP Lama (opsional)</label>
                                <input type="text" name="niplama" x-model="form.niplama" maxlength="15"
                                       class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-gray-400 uppercase mb-1.5">Sesi</label>
                        <select name="sesi" x-model="form.sesi"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm bg-white outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">— Tanpa sesi —</option>
                            <option value="1">Sesi 1</option>
                            <option value="2">Sesi 2</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-gray-400 uppercase mb-1.5">Status <span class="text-rose-500">*</span></label>
                        <select name="status" required x-model="form.status"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm bg-white outline-none focus:ring-2 focus:ring-blue-500">
                            @foreach($statuses as $s)
                                <option value="{{ $s }}">{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-gray-400 uppercase mb-1.5">Jam Mulai</label>
                        <input type="time" name="jam_mulai" x-model="form.jam_mulai"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-gray-400 uppercase mb-1.5">Jam Selesai</label>
                        <input type="time" name="jam_selesai" x-model="form.jam_selesai"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-[11px] font-bold text-gray-400 uppercase mb-1.5">Keterangan (opsional)</label>
                        <input type="text" name="keterangan" x-model="form.keterangan" maxlength="300"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div class="flex justify-end gap-2 mt-6">
                    <button type="button" @click="addOpen = false" class="px-4 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-600 text-xs font-bold transition">Batal</button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition">Simpan Jadwal</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ===== Modal: Unggah Excel ===== --}}
    <div x-show="uploadOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none">
        <div class="absolute inset-0 bg-slate-900/60" @click="uploadOpen = false"></div>
        <div class="relative bg-white rounded-3xl shadow-2xl w-full max-w-lg p-6 sm:p-7" @click.outside="uploadOpen = false" x-transition>
            <div class="flex items-start justify-between gap-4 mb-5">
                <div>
                    <h3 class="text-lg font-black text-gray-900">Unggah Jadwal Excel</h3>
                    <p class="text-xs text-gray-400">Format: .xlsx / .xls · Grup: <span class="font-bold text-blue-600">{{ $grupLabel }}</span></p>
                </div>
                <button type="button" @click="uploadOpen = false" class="text-gray-300 hover:text-gray-600 text-xl leading-none">&times;</button>
            </div>

            <form method="POST" action="{{ route('simpati.jadwal.upload') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="grup" value="{{ $grup }}">

                <label class="block border-2 border-dashed border-gray-200 rounded-2xl p-6 text-center cursor-pointer hover:border-blue-300 transition mb-4"
                       :class="file ? 'border-blue-300 bg-blue-50/40' : ''">
                    <input type="file" name="file" accept=".xlsx,.xls" required class="hidden" @change="onFile($event)">
                    <div class="w-11 h-11 rounded-2xl bg-blue-50 text-blue-600 mx-auto flex items-center justify-center mb-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                    </div>
                    <p class="text-xs font-bold text-gray-600" x-text="file || 'Klik untuk memilih file Excel'"></p>
                    <p class="text-[11px] text-gray-400 mt-1">Maks. 5 MB — gunakan Template Excel agar kolom terbaca.</p>
                </label>

                <div class="flex items-center justify-between gap-2">
                    <a href="{{ route('simpati.jadwal.template', ['grup' => $grup]) }}" class="text-xs font-bold text-blue-600 hover:underline">Unduh template dulu</a>
                    <div class="flex gap-2">
                        <button type="button" @click="uploadOpen = false" class="px-4 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-600 text-xs font-bold transition">Batal</button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition">Unggah</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function jadwalUi() {
        return {
            addOpen: false,
            uploadOpen: false,
            search: '',
            results: [],
            pickedName: '',
            file: '',
            form: { tanggal: '', user_id: null, nama: '', niplama: '', sesi: '', jam_mulai: '', jam_selesai: '', status: 'Terjadwal', keterangan: '' },

            openAdd() {
                this.form = { tanggal: '', user_id: null, nama: '', niplama: '', sesi: '', jam_mulai: '', jam_selesai: '', status: 'Terjadwal', keterangan: '' };
                this.search = '';
                this.results = [];
                this.pickedName = '';
                this.addOpen = true;
            },

            searchPegawai() {
                fetch('{{ route('simpati.petugas.cari') }}?q=' + encodeURIComponent(this.search || ''))
                    .then(r => r.json())
                    .then(res => { this.results = res.data || []; })
                    .catch(() => { this.results = []; });
            },

            pick(p) {
                this.form.user_id = p.id;
                this.pickedName = p.nama_lengkap + ' (' + p.niplama + ')';
                this.form.nama = '';
                this.results = [];
                this.search = '';
            },

            clearPick() {
                this.form.user_id = null;
                this.pickedName = '';
            },

            onFile(e) {
                const f = e.target.files && e.target.files[0];
                this.file = f ? f.name : '';
            },
        };
    }
</script>
@endpush
