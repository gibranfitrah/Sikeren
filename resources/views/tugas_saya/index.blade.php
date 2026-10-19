@extends('layouts.app')

@section('title', 'Tugas Saya - Sikeren')
@section('header_title', 'Tugas Saya')

@section('content')

<div class="space-y-6 pb-12 max-w-7xl mx-auto" x-data="{ modalTambahTugasSaya: false }">

    {{-- HEADER --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-gray-200/80 shadow-xs">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-md text-[11px] font-bold uppercase tracking-wider bg-emerald-50 text-emerald-700 border border-emerald-100">
                    Personal Dashboard
                </span>
                <span class="text-xs text-gray-400">•</span>
                <span class="text-xs text-gray-500 font-medium">{{ Auth::user()->nama_lengkap }}</span>
            </div>
            <h2 class="text-xl font-bold text-gray-900 mt-1">Tugas Saya</h2>
            <p class="text-xs text-gray-500 mt-0.5">Pantau seluruh penugasan kegiatan, rapat, dan sub kegiatan yang melibatkan Anda.</p>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <button type="button" 
                    @click="modalTambahTugasSaya = true" 
                    class="px-3.5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl transition inline-flex items-center gap-1.5 shadow-xs cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Tambah Tugas Saya</span>
            </button>
            <a href="{{ route('sub-kegiatan.index') }}" class="px-3.5 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-xl transition">
                Lihat Sub Kegiatan
            </a>
            <a href="{{ route('time-schedule.index', ['person' => Auth::user()->nama_lengkap]) }}" class="px-3.5 py-2 bg-white hover:bg-gray-50 border border-gray-300 text-gray-700 text-xs font-bold rounded-xl transition inline-flex items-center gap-1.5">
                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <span>Kalender Jadwal Saya</span>
            </a>
        </div>
    </div>

    {{-- STATS CARDS --}}
    @php
        $totalBooking = isset($myBookings) ? count($myBookings) : 0;
        $totalKegiatanAll = count($myTasks) + count($mySubKegiatans) + $totalBooking;
        $totalKegiatan = count($myTasks);
        $totalSub = count($mySubKegiatans);
    @endphp

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-gray-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-gray-500">Total Tugas Saya</p>
                <h3 class="text-2xl font-bold text-gray-900 mt-1">{{ $totalKegiatanAll }}</h3>
                <span class="text-[10px] text-gray-400">Penugasan Utama & Sub Kegiatan</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
                {{ $totalKegiatanAll }}
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-gray-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-gray-500">Kegiatan & Rapat Utama</p>
                <h3 class="text-2xl font-bold text-indigo-600 mt-1">{{ $totalKegiatan }}</h3>
                <span class="text-[10px] text-indigo-600 font-semibold bg-indigo-50 px-2 py-0.5 rounded">Kegiatan Utama</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-gray-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-gray-500">Sub Kegiatan Mandiri</p>
                <h3 class="text-2xl font-bold text-emerald-600 mt-1">{{ $totalSub }}</h3>
                <span class="text-[10px] text-emerald-600 font-semibold bg-emerald-50 px-2 py-0.5 rounded">Sub Kegiatan</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-gray-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-gray-500">Booking Ruangan Saya</p>
                <h3 class="text-2xl font-bold text-amber-600 mt-1">{{ $totalBooking }}</h3>
                <span class="text-[10px] text-amber-600 font-semibold bg-amber-50 px-2 py-0.5 rounded">Jadwal Ruangan / Zoom</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
        </div>
    </div>

    {{-- SECTION 1: KEGIATAN & RAPAT UTAMA SAYA --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-blue-600"></span>
                <h3 class="font-bold text-gray-900 text-sm">Tugas Kegiatan & Rapat Utama</h3>
            </div>
            <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-blue-50 text-blue-700">
                {{ count($myTasks) }} Agenda
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-left text-xs">
                <thead class="bg-gray-50 text-[11px] font-bold text-gray-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-3.5">Agenda Kegiatan</th>
                        <th class="px-6 py-3.5">Peran / Penugasan</th>
                        <th class="px-6 py-3.5">Waktu Pelaksanaan</th>
                        <th class="px-6 py-3.5">Wilayah / Tempat</th>
                        <th class="px-6 py-3.5">Status</th>
                        <th class="px-6 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($myTasks as $task)
                    @php
                        $st = strtolower(trim($task->status ?? ''));
                        $isDone     = ($st === 'selesai');
                        $isDelayed  = ($st === 'tertunda');
                        $isInactive = ($st === 'tidak berjalan' || $st === 'dibatalkan');
                        $isApproved = ($task->setuju_rapat == 1 || $st === 'disetujui' || $st === 'sedang berjalan' || $isDone || $isDelayed || $isInactive);
                        $isRejected = ($task->setuju_rapat == 3 || $st === 'ditolak');
                    @endphp
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        <td class="px-6 py-4">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $task->jenis === 'Rapat' ? 'bg-indigo-50 text-indigo-700' : 'bg-sky-50 text-sky-700' }} border inline-block mb-1">
                                {{ $task->jenis }}
                            </span>
                            <a href="{{ url('/daftarkegiatan/' . $task->id) }}" class="font-bold text-gray-900 text-sm block hover:text-blue-600 transition">
                                {{ $task->text }}
                            </a>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($task->penanggung_jawab == Auth::user()->nama_lengkap)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-bold bg-purple-50 text-purple-700 border border-purple-200 shadow-sm">
                                    <span class="w-1.5 h-1.5 rounded-full bg-purple-500"></span>
                                    <span>Penanggung Jawab</span>
                                </span>
                            @elseif($task->pemimpin == Auth::user()->nama_lengkap)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200 shadow-sm">
                                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                    <span>Pemimpin Rapat</span>
                                </span>
                            @elseif($task->notulis == Auth::user()->nama_lengkap)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 shadow-sm">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    <span>Notulis</span>
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-bold bg-slate-50 text-slate-700 border border-slate-200 shadow-sm">
                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                    <span>Anggota Tim</span>
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-600 font-medium">
                            {{ $task->start_date ? \Carbon\Carbon::parse($task->start_date)->translatedFormat('d M Y') : '-' }}
                            @if($task->date_akhir && $task->date_akhir != $task->start_date)
                                &rarr; {{ \Carbon\Carbon::parse($task->date_akhir)->translatedFormat('d M Y') }}
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-600">
                            @if(!empty($task->wilayah_list))
                                <span class="truncate max-w-[180px] block" title="{{ implode(', ', $task->wilayah_list) }}">
                                    {{ implode(', ', array_slice($task->wilayah_list, 0, 2)) }}
                                    @if(count($task->wilayah_list) > 2) ... @endif
                                </span>
                            @else
                                <span>{{ $task->tempat ?: 'Kantor BPS' }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($isDone)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    <span>Selesai</span>
                                </span>
                            @elseif($isDelayed)
                                <div class="space-y-0.5">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-300">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                        <span>Tertunda</span>
                                    </span>
                                    @if(!empty($task->alasan_status))
                                        <p class="text-[10px] text-amber-700 italic max-w-[140px] truncate" title="{{ $task->alasan_status }}">{{ $task->alasan_status }}</p>
                                    @endif
                                </div>
                            @elseif($isInactive)
                                <div class="space-y-0.5">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-bold bg-slate-100 text-slate-700 border border-slate-300">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-500"></span>
                                        <span>Tidak Berjalan</span>
                                    </span>
                                    @if(!empty($task->alasan_status))
                                        <p class="text-[10px] text-slate-600 italic max-w-[140px] truncate" title="{{ $task->alasan_status }}">{{ $task->alasan_status }}</p>
                                    @endif
                                </div>
                            @elseif($isApproved)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                    <span>Sedang Berjalan</span>
                                </span>
                            @elseif($isRejected)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-bold bg-red-50 text-red-700 border border-red-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                    <span>Ditolak</span>
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                    <span>Menunggu</span>
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <a href="{{ url('/daftarkegiatan/' . $task->id) }}" class="px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 font-bold rounded-xl border border-blue-200 text-xs transition">
                                Buka Detail
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-400 text-xs">
                            Belum ada kegiatan atau rapat utama yang terdaftar untuk Anda.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- SECTION 2: SUB KEGIATAN SAYA --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                <h3 class="font-bold text-gray-900 text-sm">Tugas Sub Kegiatan Saya</h3>
            </div>
            <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-amber-50 text-amber-700">
                {{ count($mySubKegiatans) }} Sub Kegiatan
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-left text-xs">
                <thead class="bg-gray-50 text-[11px] font-bold text-gray-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-3.5">Sub Kegiatan</th>
                        <th class="px-6 py-3.5">Kegiatan Induk</th>
                        <th class="px-6 py-3.5">Batas Waktu (Deadline)</th>
                        <th class="px-6 py-3.5">Status & Progress</th>
                        <th class="px-6 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($mySubKegiatans as $sub)
                    @php $dl = $sub->deadline_status; @endphp
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        <td class="px-6 py-4">
                            <span class="font-bold text-gray-900 text-sm block">{{ $sub->nama_sub }}</span>
                            <span class="text-[11px] text-gray-400">PJ: {{ $sub->pj ?: '-' }}</span>
                        </td>
                        <td class="px-6 py-4 text-gray-600 font-medium">
                            {{ $sub->task ? $sub->task->text : '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-bold border {{ $dl['class'] }}">
                                <span class="w-2 h-2 rounded-full {{ $dl['dot'] }}"></span>
                                <span>{{ $dl['label'] }}</span>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap min-w-[130px]">
                            <div class="flex items-center gap-2">
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="h-2 rounded-full {{ $sub->progress >= 100 ? 'bg-emerald-500' : 'bg-blue-600' }}" style="width: {{ $sub->progress }}%"></div>
                                </div>
                                <span class="font-bold text-gray-700 text-[11px]">{{ $sub->progress }}%</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <form action="{{ route('sub-kegiatan.progress', $sub->id) }}" method="POST" class="inline-flex items-center gap-1">
                                @csrf
                                <select name="progress" onchange="this.form.submit()" class="text-xs py-1 px-2 rounded-lg border border-gray-200 bg-white text-gray-700">
                                    <option value="0" {{ $sub->progress == 0 ? 'selected' : '' }}>0%</option>
                                    <option value="25" {{ $sub->progress == 25 ? 'selected' : '' }}>25%</option>
                                    <option value="50" {{ $sub->progress == 50 ? 'selected' : '' }}>50%</option>
                                    <option value="75" {{ $sub->progress == 75 ? 'selected' : '' }}>75%</option>
                                    <option value="100" {{ $sub->progress == 100 ? 'selected' : '' }}>100% Selesai</option>
                                </select>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-400 text-xs">
                            Belum ada sub kegiatan yang terdaftar untuk Anda.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- SECTION 3: JADWAL BOOKING RUANGAN SAYA --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                <h3 class="font-bold text-gray-900 text-sm">Jadwal Booking Ruangan Saya</h3>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-amber-50 text-amber-700">
                    {{ isset($myBookings) ? count($myBookings) : 0 }} Jadwal
                </span>
                <a href="{{ route('booking-ruangan.index') }}" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-[11px] font-bold rounded-xl transition">
                    + Booking Baru
                </a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-left text-xs">
                <thead class="bg-gray-50 text-[11px] font-bold text-gray-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-3.5">Acara / Agenda</th>
                        <th class="px-6 py-3.5">Ruangan / Zoom</th>
                        <th class="px-6 py-3.5">Waktu Pelaksanaan</th>
                        <th class="px-6 py-3.5">Status</th>
                        <th class="px-6 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($myBookings ?? [] as $booking)
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        <td class="px-6 py-4">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $booking->tipe_pertemuan === 'online' ? 'bg-sky-50 text-sky-700' : ($booking->tipe_pertemuan === 'hybrid' ? 'bg-purple-50 text-purple-700' : 'bg-amber-50 text-amber-700') }} border inline-block mb-1">
                                {{ ucfirst($booking->tipe_pertemuan) }}
                            </span>
                            <span class="font-bold text-gray-900 text-sm block">{{ $booking->nama_acara }}</span>
                            <span class="text-[11px] text-gray-400">PJ: {{ $booking->penyelenggara ?: '-' }}</span>
                            @if($booking->task)
                                <a href="{{ url('/daftarkegiatan/' . $booking->task->id) }}" class="block text-[11px] text-blue-600 hover:underline mt-0.5">
                                    Terkait: {{ $booking->task->text }}
                                </a>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-600 font-medium">
                            <span class="block">{{ $booking->nama_ruangan ?: 'Online (Tanpa Ruangan)' }}</span>
                            @if($booking->zoom_link)
                                <a href="{{ $booking->zoom_link }}" target="_blank" class="text-[11px] text-blue-600 hover:underline font-bold">Link Zoom</a>
                            @elseif(in_array($booking->zoom_account, ['zoom_1', 'zoom_2']))
                                <span class="text-[11px] text-gray-400">{{ $booking->zoom_account === 'zoom_1' ? 'Zoom 1' : 'Zoom 2' }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-600 font-medium">
                            {{ $booking->booking_date ? \Carbon\Carbon::parse($booking->booking_date)->translatedFormat('d M Y') : '-' }}
                            <span class="block text-[11px] text-gray-500 font-mono">{{ substr($booking->start_time, 0, 5) }} - {{ substr($booking->end_time, 0, 5) }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2.5 py-1 rounded-md text-[10px] font-bold {{ $booking->status === 'Disetujui' ? 'bg-emerald-50 text-emerald-700 border border-emerald-100' : 'bg-gray-100 text-gray-700 border' }}">
                                {{ $booking->status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                <a href="{{ route('booking-ruangan.index', ['date' => $booking->booking_date]) }}" 
                                   class="p-2 bg-blue-50 hover:bg-blue-100 text-blue-700 font-bold rounded-xl border border-blue-200 text-xs transition inline-flex items-center gap-1.5 shadow-2xs"
                                   title="Lihat Jadwal Ruangan">
                                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <span class="hidden sm:inline">Lihat</span>
                                </a>
                                <form action="{{ route('booking-ruangan.destroy', $booking->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan booking ruangan {{ addslashes($booking->nama_acara) }}?')" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="p-2 bg-rose-50 hover:bg-rose-100 text-rose-600 rounded-xl border border-rose-200 text-xs transition inline-flex items-center gap-1.5 shadow-2xs"
                                            title="Batalkan / Hapus Booking">
                                        <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                        <span class="hidden sm:inline">Hapus</span>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-400 text-xs">
                            Belum ada jadwal booking ruangan dari Anda. <a href="{{ route('booking-ruangan.index') }}" class="text-blue-600 font-bold hover:underline">Buat booking sekarang</a>.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- =========================================================
        MODAL POPUP: TAMBAH TUGAS BARU SAYA (TAMPILAN BARU KHUSUS TUGAS PERSONAL)
    ========================================================== --}}
    <div x-show="modalTambahTugasSaya" 
         x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-3 sm:p-4"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">

        {{-- Backdrop --}}
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="modalTambahTugasSaya = false"></div>

        {{-- Modal Box --}}
        <div class="relative bg-white rounded-2xl shadow-2xl border border-gray-200 max-w-lg w-full overflow-hidden z-10"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95">

            {{-- Modal Header --}}
            <div class="flex items-center justify-between p-4 sm:p-5 border-b border-gray-100 bg-slate-50/70">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center font-bold shadow-xs">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-900 text-base">Tambah Tugas Baru Saya</h3>
                        <p class="text-xs text-gray-500">Catat tugas atau pekerjaan individu yang akan Anda selesaikan.</p>
                    </div>
                </div>
                <button type="button" @click="modalTambahTugasSaya = false" class="text-gray-400 hover:text-gray-600 p-1.5 rounded-lg hover:bg-gray-100 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Form Body --}}
            <form action="{{ route('sub-kegiatan.store') }}" method="POST">
                @csrf
                <input type="hidden" name="tim" value="{{ Auth::user()->tim ?? 'Umum' }}">
                <input type="hidden" name="anggota[]" value="{{ Auth::user()->nama_lengkap }}">

                <div class="p-5 space-y-4 text-xs">
                    {{-- Judul / Nama Tugas --}}
                    <div>
                        <label class="block font-bold text-gray-700 uppercase tracking-wider mb-1">
                            Nama / Uraian Tugas <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" 
                               name="nama_sub" 
                               required 
                               placeholder="Contoh: Rekapitulasi Data Responden, Penyusunan Draft Laporan, dll." 
                               class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition">
                    </div>

                    {{-- Terkait Kegiatan --}}
                    <div>
                        <label class="block font-bold text-gray-700 uppercase tracking-wider mb-1">
                            Terkait Kegiatan Tim / Instansi <span class="text-rose-500">*</span>
                        </label>
                        <select name="task_id" required class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 cursor-pointer">
                            @forelse($availableTasks ?? $myTasks as $t)
                                <option value="{{ $t->id }}">
                                    [{{ $t->tim ?? 'Kegiatan' }}] {{ $t->text }} ({{ \Carbon\Carbon::parse($t->start_date)->translatedFormat('d M Y') }})
                                </option>
                            @empty
                                <option value="" disabled>Belum ada kegiatan terdaftar</option>
                            @endforelse
                        </select>
                        <span class="text-[11px] text-gray-400 mt-1 block">Tugas ini akan tercatat langsung pada portofolio dan dashboard Anda.</span>
                    </div>

                    {{-- Waktu Mulai & Deadline --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-gray-700 uppercase tracking-wider mb-1">
                                Tanggal Mulai
                            </label>
                            <input type="date" 
                                   name="start_date" 
                                   value="{{ date('Y-m-d') }}"
                                   class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition">
                        </div>
                        <div>
                            <label class="block font-bold text-gray-700 uppercase tracking-wider mb-1">
                                Tenggat Waktu (Deadline) <span class="text-rose-500">*</span>
                            </label>
                            <input type="date" 
                                   name="end_date" 
                                   required
                                   value="{{ date('Y-m-d', strtotime('+3 days')) }}"
                                   class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition">
                        </div>
                    </div>

                    {{-- Catatan Ringkas --}}
                    <div>
                        <label class="block font-bold text-gray-700 uppercase tracking-wider mb-1">
                            Catatan / Rincian Singkat (Opsional)
                        </label>
                        <textarea name="keterangan" 
                                  rows="3" 
                                  placeholder="Tuliskan target output, link referensi dokumen, atau catatan penting pengerjaan tugas..." 
                                  class="w-full px-3.5 py-2 text-xs rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition resize-none"></textarea>
                    </div>
                </div>

                {{-- Modal Footer --}}
                <div class="p-4 sm:p-5 border-t border-gray-100 bg-gray-50/80 flex items-center justify-end gap-2.5">
                    <button type="button" 
                            @click="modalTambahTugasSaya = false"
                            class="px-4 py-2 bg-white hover:bg-gray-100 border border-gray-200 text-gray-700 font-bold rounded-xl text-xs transition">
                        Batal
                    </button>
                    <button type="submit" 
                            class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl text-xs shadow-xs transition flex items-center gap-1.5 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Simpan Tugas Saya</span>
                    </button>
                </div>
            </form>

        </div>
    </div>

</div>

@endsection