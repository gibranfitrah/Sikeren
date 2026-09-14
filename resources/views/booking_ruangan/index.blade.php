@extends('layouts.app')

@section('title', 'Booking Ruangan & Kelola Zoom - Sikeren')
@section('header_title', 'Booking Ruangan & Jadwal Rapat')

@php
    $isToday = $isToday ?? ($selectedDate === \Carbon\Carbon::today()->format('Y-m-d'));
@endphp

@section('content')
<div class="max-w-7xl mx-auto space-y-6 pb-12"
     x-data="{
         modalOpen: {{ $selectedRapat ? 'true' : 'false' }},
         selectedVenueId: '{{ $selectedRapat->venue_id ?? 1 }}',
         tipePertemuan: '{{ $selectedRapat ? ($selectedRapat->tempat && str_contains(strtolower($selectedRapat->tempat), 'zoom') ? 'hybrid' : 'offline') : 'offline' }}',
         zoomAccount: 'zoom_1',
         namaAcara: '{{ addslashes($selectedRapat->text ?? '') }}',
         penyelenggara: '{{ addslashes(($selectedRapat ? ($selectedRapat->penanggung_jawab ?? $selectedRapat->pemimpin) : null) ?? Auth::user()->nama_lengkap ?? '') }}',
         bookingDate: '{{ $selectedRapat->start_date ?? $selectedDate }}',
         startTime: '{{ ($selectedRapat && $selectedRapat->start_jam) ? substr($selectedRapat->start_jam, 0, 5) : '09:00' }}',
         endTime: '{{ ($selectedRapat && $selectedRapat->end_jam) ? substr($selectedRapat->end_jam, 0, 5) : '11:00' }}',
         taskId: '{{ $selectedRapat->id ?? '' }}',
         keterangan: '',
         openBookingModal(venueId = null, zoomAcc = null, time = null) {
             if (venueId) {
                 this.selectedVenueId = venueId;
                 this.tipePertemuan = 'offline';
             }
             if (zoomAcc) {
                 this.zoomAccount = zoomAcc;
                 this.tipePertemuan = 'online';
             }
             if (time) {
                 this.startTime = time;
                 let h = parseInt(time.split(':')[0]) + 2;
                 this.endTime = (h < 10 ? '0' : '') + h + ':00';
             }
             this.modalOpen = true;
         },
         copyToClipboard(text, label) {
             navigator.clipboard.writeText(text).then(() => {
                 alert('Link ' + label + ' berhasil disalin!');
             });
         }
     }">

    {{-- ==========================================
        HEADER & ACTION BAR
    =========================================== --}}
    <x-heading title="Booking Ruangan & Kelola Zoom"
               subtitle="Pantau ketersediaan 3 Ruangan Rapat dan 2 Akun Zoom resmi BPS Sultra secara real-time"
               tag="Layanan Rapat & Sarpras"
               subtag="Time Schedule Ruangan">
        <x-slot name="actions">
            <button type="button"
                    @click="modalOpen = true"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-sm transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                + Booking Ruangan / Zoom
            </button>
        </x-slot>
    </x-heading>

    {{-- ALERT SUCCESS & ERROR --}}
    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-center gap-3 text-emerald-800 text-xs font-semibold shadow-xs">
            <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div class="flex-1">{{ session('success') }}</div>
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 bg-rose-50 border border-rose-200 rounded-2xl flex items-center gap-3 text-rose-800 text-xs font-semibold shadow-xs">
            <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div class="flex-1">{{ session('error') }}</div>
        </div>
    @endif

    {{-- BANNER DARI BUAT RAPAT --}}
    @if($selectedRapat)
        <div class="p-5 bg-gradient-to-r from-blue-50 via-indigo-50 to-white border border-blue-200 rounded-2xl shadow-xs">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-start gap-3.5">
                    <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center font-bold text-base shrink-0 shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <h3 class="text-sm font-bold text-gray-900">Konfirmasi Booking untuk Rapat: "{{ $selectedRapat->text }}"</h3>
                            <x-badge variant="primary" size="xs">Baru Diterbitkan</x-badge>
                        </div>
                        <p class="text-xs text-gray-600 mt-1">
                            Jadwal Rapat: <strong>{{ \Carbon\Carbon::parse($selectedRapat->start_date)->translatedFormat('l, d F Y') }}</strong> 
                            ({{ $selectedRapat->start_jam ?? '09:00' }} - {{ $selectedRapat->end_jam ?? '11:00' }}) • Penyelenggara: {{ $selectedRapat->penanggung_jawab ?? $selectedRapat->pemimpin }}
                        </p>
                    </div>
                </div>
                <button type="button"
                        @click="modalOpen = true"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-xs shrink-0 transition">
                    Pilih Ruangan & Zoom Sekarang &rarr;
                </button>
            </div>
        </div>
    @endif

    {{-- =========================================================
        MAIN GRID 2 KOLOM (KIRI: RUANGAN & ZOOM, KANAN: TIME SCHEDULE)
    ========================================================== --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        {{-- =====================================================
            SISI KIRI (LG: COL-4): CARD 3 RUANGAN & 2 AKUN ZOOM
        ====================================================== --}}
        <div class="lg:col-span-4 space-y-6">

            {{-- 1. CARD RUANGAN RAPAT FISIK --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-xs space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <div class="flex items-center gap-2">
                        <div class="w-2.5 h-2.5 rounded-full bg-blue-600"></div>
                        <h3 class="text-xs font-bold text-gray-800 uppercase tracking-wider">Ruangan Rapat BPS</h3>
                    </div>
                    <span class="text-[11px] font-semibold text-gray-400">3 Ruangan Fisik</span>
                </div>

                <div class="space-y-3.5">
                    @foreach($venuesStatus as $vId => $vStat)
                        @php
                            $ven = $vStat['venue'];
                            $isUse = $vStat['is_in_use'];
                            $totalBook = $vStat['total_today'];
                            $activeTask = $vStat['current_task'];
                        @endphp
                        <div class="p-4 rounded-xl border transition-all duration-200 {{ $isUse ? 'bg-rose-50/40 border-rose-200' : ($totalBook > 0 ? 'bg-amber-50/30 border-amber-200' : 'bg-gray-50/60 hover:bg-white border-gray-200') }}">
                            <div class="flex items-start justify-between gap-2 mb-2">
                                <div>
                                    <h4 class="text-xs font-bold text-gray-900 flex items-center gap-1.5">
                                        {{ $ven->name }}
                                        <span class="text-[10px] text-gray-400 font-normal">({{ $ven->capacity }} Orang)</span>
                                    </h4>
                                    <p class="text-[11px] text-gray-500 mt-0.5">{{ $ven->description }}</p>
                                </div>
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold shrink-0 {{ $isUse ? 'bg-rose-100 text-rose-700' : ($totalBook > 0 ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700') }}">
                                    {{ $vStat['status_label'] }}
                                </span>
                            </div>

                            @if($isUse && $activeTask)
                                <div class="mt-2.5 p-2.5 bg-white/80 rounded-lg border border-rose-200/80 text-[11px] space-y-0.5">
                                    <p class="font-bold text-rose-900 truncate">{{ $activeTask->nama_acara }}</p>
                                    <p class="text-rose-700 font-medium">Jam: {{ substr($activeTask->start_time, 0, 5) }} - {{ substr($activeTask->end_time, 0, 5) }} • PJ: {{ $activeTask->penyelenggara }}</p>
                                </div>
                            @elseif($totalBook > 0)
                                <div class="mt-2 text-[11px] text-amber-700 font-medium">
                                    Terdapat {{ $totalBook }} jadwal kegiatan pada tanggal ini.
                                </div>
                            @endif

                            <div class="mt-3 pt-2.5 border-t border-gray-100 flex items-center justify-between">
                                <span class="text-[10px] text-gray-400">
                                    {{ $isToday ? 'Hari Ini' : \Carbon\Carbon::parse($selectedDate)->format('d M') }}: {{ $totalBook }} Acara
                                </span>
                                <button type="button"
                                        @click="openBookingModal({{ $ven->id }}, null, '09:00')"
                                        class="text-[11px] font-bold text-blue-600 hover:text-blue-800 hover:underline">
                                    + Booking Ruangan &rarr;
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- 2. CARD KELOLA AKUN ZOOM --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-xs space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <div class="flex items-center gap-2">
                        <div class="w-2.5 h-2.5 rounded-full bg-indigo-600"></div>
                        <h3 class="text-xs font-bold text-gray-800 uppercase tracking-wider">Akun Zoom BPS Sultra</h3>
                    </div>
                    <span class="text-[11px] font-semibold text-gray-400">2 Akun Daring</span>
                </div>

                <div class="space-y-3.5">
                    @foreach($zoomStatus as $zKey => $zStat)
                        @php
                            $zInfo = $zStat['info'];
                            $isUse = $zStat['is_in_use'];
                            $activeTask = $zStat['current_task'];
                        @endphp
                        <div class="p-4 rounded-xl border transition-all duration-200 {{ $isUse ? 'bg-rose-50/40 border-rose-200' : 'bg-gray-50/60 hover:bg-white border-gray-200' }}">
                            <div class="flex items-start justify-between gap-2 mb-1.5">
                                <div>
                                    <h4 class="text-xs font-bold text-gray-900">{{ $zInfo['name'] }}</h4>
                                    <p class="text-[11px] text-gray-500">{{ $zInfo['description'] }}</p>
                                </div>
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold shrink-0 {{ $isUse ? 'bg-rose-100 text-rose-700' : 'bg-emerald-100 text-emerald-700' }}">
                                    {{ $isUse ? 'Sedang Digunakan' : 'Tersedia' }}
                                </span>
                            </div>

                            {{-- Info Meeting ID & Passcode --}}
                            <div class="mt-2 p-2.5 bg-white rounded-lg border border-gray-200/80 text-[11px] space-y-1">
                                <div class="flex items-center justify-between text-gray-600">
                                    <span>Meeting ID:</span>
                                    <span class="font-mono font-bold text-gray-900">{{ $zInfo['meeting_id'] }}</span>
                                </div>
                                <div class="flex items-center justify-between text-gray-600">
                                    <span>Passcode:</span>
                                    <span class="font-mono font-bold text-gray-900">{{ $zInfo['passcode'] }}</span>
                                </div>
                                <div class="pt-1.5 border-t border-gray-100 flex items-center justify-between gap-2">
                                    <a href="{{ $zInfo['link'] }}" target="_blank" class="text-blue-600 font-bold hover:underline truncate">
                                        Buka Zoom Meeting
                                    </a>
                                    <button type="button"
                                            @click="copyToClipboard('{{ $zInfo['link'] }}', '{{ $zInfo['name'] }}')"
                                            class="text-[10px] font-bold text-gray-500 hover:text-gray-800 bg-gray-100 hover:bg-gray-200 px-2 py-0.5 rounded transition">
                                        Salin Link
                                    </button>
                                </div>
                            </div>

                            @if($isUse && $activeTask)
                                <div class="mt-2 text-[10px] text-rose-700 font-medium">
                                    Dipakai: "{{ $activeTask->nama_acara }}" ({{ substr($activeTask->start_time, 0, 5) }} - {{ substr($activeTask->end_time, 0, 5) }})
                                </div>
                            @endif

                            <div class="mt-3 pt-2 border-t border-gray-100 flex justify-end">
                                <button type="button"
                                        @click="openBookingModal(null, '{{ $zKey }}', '09:00')"
                                        class="text-[11px] font-bold text-indigo-600 hover:text-indigo-800 hover:underline">
                                    + Gunakan Akun Ini &rarr;
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>

        {{-- =====================================================
            SISI KANAN (LG: COL-8): CARD TANGGAL, FILTER, & TIME SCHEDULE
        ====================================================== --}}
        <div class="lg:col-span-8 space-y-6">

            {{-- 1. CARD TANGGAL & SWITCHER VIEW --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-xs">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    
                    {{-- Navigasi Tanggal --}}
                    <div class="flex items-center gap-2">
                        <a href="{{ route('booking-ruangan.index', ['date' => \Carbon\Carbon::parse($selectedDate)->subDay()->format('Y-m-d'), 'view' => $viewMode]) }}"
                           class="p-2 bg-gray-50 hover:bg-gray-100 rounded-xl border border-gray-200 text-gray-600 transition"
                           title="Hari Sebelumnya">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                        </a>

                        <form action="{{ route('booking-ruangan.index') }}" method="GET" class="flex items-center gap-2">
                            <input type="hidden" name="view" value="{{ $viewMode }}">
                            <input type="date"
                                   name="date"
                                   value="{{ $selectedDate }}"
                                   onchange="this.form.submit()"
                                   class="px-3 py-1.5 bg-gray-50 focus:bg-white border border-gray-200 rounded-xl text-xs sm:text-sm font-bold text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600">
                        </form>

                        <a href="{{ route('booking-ruangan.index', ['date' => \Carbon\Carbon::parse($selectedDate)->addDay()->format('Y-m-d'), 'view' => $viewMode]) }}"
                           class="p-2 bg-gray-50 hover:bg-gray-100 rounded-xl border border-gray-200 text-gray-600 transition"
                           title="Hari Berikutnya">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>

                        <a href="{{ route('booking-ruangan.index', ['date' => \Carbon\Carbon::today()->format('Y-m-d'), 'view' => $viewMode]) }}"
                           class="px-3 py-1.5 text-xs font-bold rounded-xl {{ $selectedDate === \Carbon\Carbon::today()->format('Y-m-d') ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }} transition">
                            Hari Ini
                        </a>
                    </div>

                    {{-- Switcher Harian vs Bulanan --}}
                    <div class="inline-flex items-center bg-gray-100 p-1 rounded-xl">
                        <a href="{{ route('booking-ruangan.index', ['date' => $selectedDate, 'view' => 'daily']) }}"
                           class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition {{ $viewMode === 'daily' ? 'bg-white text-blue-700 shadow-xs' : 'text-gray-600 hover:text-gray-900' }}">
                            📅 Jadwal Harian
                        </a>
                        <a href="{{ route('booking-ruangan.index', ['date' => $selectedDate, 'month' => substr($selectedDate, 0, 7), 'view' => 'monthly']) }}"
                           class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition {{ $viewMode === 'monthly' ? 'bg-white text-blue-700 shadow-xs' : 'text-gray-600 hover:text-gray-900' }}">
                            🗓️ Jadwal Bulanan
                        </a>
                    </div>
                </div>

                {{-- Header Status Hari Terpilih --}}
                <div class="mt-4 pt-4 border-t border-gray-100 flex items-center justify-between text-xs">
                    <div>
                        <span class="text-gray-400">Jadwal Tanggal:</span>
                        <strong class="text-gray-800 ml-1">{{ \Carbon\Carbon::parse($selectedDate)->translatedFormat('l, d F Y') }}</strong>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-blue-600"></span>
                        <span class="text-gray-600 font-semibold">{{ $dailyBookings->count() }} Kegiatan Terjadwal</span>
                    </div>
                </div>
            </div>

            {{-- 2. TIME SCHEDULE: VIEW HARIAN (HOURLY TIMELINE GRID) --}}
            @if($viewMode === 'daily')
                <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-xs space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-bold text-gray-900">Time Schedule Harian (08:00 - 17:00)</h3>
                            <p class="text-xs text-gray-500">Klik pada slot waktu atau ruangan untuk melakukan reservasi cepat.</p>
                        </div>
                        <div class="flex items-center gap-3 text-[11px]">
                            <span class="flex items-center gap-1.5 text-gray-600"><span class="w-2.5 h-2.5 rounded bg-blue-500"></span> Aula Lt 1</span>
                            <span class="flex items-center gap-1.5 text-gray-600"><span class="w-2.5 h-2.5 rounded bg-purple-500"></span> Vicon Lt 3</span>
                            <span class="flex items-center gap-1.5 text-gray-600"><span class="w-2.5 h-2.5 rounded bg-emerald-500"></span> Aula Lt 4</span>
                            <span class="flex items-center gap-1.5 text-gray-600"><span class="w-2.5 h-2.5 rounded bg-sky-500"></span> Online Zoom</span>
                        </div>
                    </div>

                    {{-- Timeline Table Grid --}}
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs border-collapse min-w-[700px]">
                            <thead>
                                <tr class="bg-gray-50/80 border-b border-gray-200 text-gray-700">
                                    <th class="py-2.5 px-3 text-left w-20 font-bold">Jam</th>
                                    <th class="py-2.5 px-3 text-left font-bold border-l border-gray-200">Aula Lantai 1</th>
                                    <th class="py-2.5 px-3 text-left font-bold border-l border-gray-200">Vicon Lantai 3</th>
                                    <th class="py-2.5 px-3 text-left font-bold border-l border-gray-200">Aula Lantai 4</th>
                                    <th class="py-2.5 px-3 text-left font-bold border-l border-gray-200">Akun Zoom</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($timeSlots as $slot)
                                    @php
                                        $slotStart = $slot . ':00';
                                        $slotEnd = date('H:i:s', strtotime($slot . ' +1 hour'));
                                        
                                        $bookingAula1 = $dailyBookings->first(function($b) use ($slotStart, $slotEnd) {
                                            return $b->venue_id == 1 && $b->start_time < $slotEnd && $b->end_time > $slotStart;
                                        });

                                        $bookingVicon = $dailyBookings->first(function($b) use ($slotStart, $slotEnd) {
                                            return $b->venue_id == 2 && $b->start_time < $slotEnd && $b->end_time > $slotStart;
                                        });

                                        $bookingAula4 = $dailyBookings->first(function($b) use ($slotStart, $slotEnd) {
                                            return $b->venue_id == 3 && $b->start_time < $slotEnd && $b->end_time > $slotStart;
                                        });

                                        $bookingZoom = $dailyBookings->first(function($b) use ($slotStart, $slotEnd) {
                                            return in_array($b->zoom_account, ['zoom_1', 'zoom_2', 'eksternal']) && $b->start_time < $slotEnd && $b->end_time > $slotStart;
                                        });
                                    @endphp
                                    <tr class="hover:bg-gray-50/40 transition">
                                        {{-- Kolom Waktu --}}
                                        <td class="py-3 px-3 font-mono font-bold text-gray-500 align-top">
                                            {{ $slot }}
                                        </td>

                                        {{-- Aula Lantai 1 --}}
                                        <td class="py-2 px-2.5 border-l border-gray-100 align-top">
                                            @if($bookingAula1)
                                                <div class="p-2.5 rounded-xl bg-blue-50 border border-blue-200 text-blue-900 shadow-2xs">
                                                    <p class="font-bold text-[11px] truncate">{{ $bookingAula1->nama_acara }}</p>
                                                    <p class="text-[10px] text-blue-700 mt-0.5">
                                                        {{ substr($bookingAula1->start_time, 0, 5) }} - {{ substr($bookingAula1->end_time, 0, 5) }} • {{ $bookingAula1->penyelenggara }}
                                                    </p>
                                                    @if($bookingAula1->tipe_pertemuan === 'hybrid')
                                                        <span class="inline-block mt-1 px-1.5 py-0.2 bg-blue-200 text-blue-800 rounded text-[9px] font-bold">Hybrid</span>
                                                    @endif
                                                </div>
                                            @else
                                                <button type="button"
                                                        @click="openBookingModal(1, null, '{{ $slot }}')"
                                                        class="w-full py-2 px-2 text-center rounded-lg border border-dashed border-gray-200 hover:border-blue-400 hover:bg-blue-50/50 text-gray-400 hover:text-blue-600 text-[11px] transition">
                                                    + Kosong
                                                </button>
                                            @endif
                                        </td>

                                        {{-- Vicon Lantai 3 --}}
                                        <td class="py-2 px-2.5 border-l border-gray-100 align-top">
                                            @if($bookingVicon)
                                                <div class="p-2.5 rounded-xl bg-purple-50 border border-purple-200 text-purple-900 shadow-2xs">
                                                    <p class="font-bold text-[11px] truncate">{{ $bookingVicon->nama_acara }}</p>
                                                    <p class="text-[10px] text-purple-700 mt-0.5">
                                                        {{ substr($bookingVicon->start_time, 0, 5) }} - {{ substr($bookingVicon->end_time, 0, 5) }} • {{ $bookingVicon->penyelenggara }}
                                                    </p>
                                                    @if($bookingVicon->tipe_pertemuan === 'hybrid')
                                                        <span class="inline-block mt-1 px-1.5 py-0.2 bg-purple-200 text-purple-800 rounded text-[9px] font-bold">Hybrid</span>
                                                    @endif
                                                </div>
                                            @else
                                                <button type="button"
                                                        @click="openBookingModal(2, null, '{{ $slot }}')"
                                                        class="w-full py-2 px-2 text-center rounded-lg border border-dashed border-gray-200 hover:border-purple-400 hover:bg-purple-50/50 text-gray-400 hover:text-purple-600 text-[11px] transition">
                                                    + Kosong
                                                </button>
                                            @endif
                                        </td>

                                        {{-- Aula Lantai 4 --}}
                                        <td class="py-2 px-2.5 border-l border-gray-100 align-top">
                                            @if($bookingAula4)
                                                <div class="p-2.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900 shadow-2xs">
                                                    <p class="font-bold text-[11px] truncate">{{ $bookingAula4->nama_acara }}</p>
                                                    <p class="text-[10px] text-emerald-700 mt-0.5">
                                                        {{ substr($bookingAula4->start_time, 0, 5) }} - {{ substr($bookingAula4->end_time, 0, 5) }} • {{ $bookingAula4->penyelenggara }}
                                                    </p>
                                                    @if($bookingAula4->tipe_pertemuan === 'hybrid')
                                                        <span class="inline-block mt-1 px-1.5 py-0.2 bg-emerald-200 text-emerald-800 rounded text-[9px] font-bold">Hybrid</span>
                                                    @endif
                                                </div>
                                            @else
                                                <button type="button"
                                                        @click="openBookingModal(3, null, '{{ $slot }}')"
                                                        class="w-full py-2 px-2 text-center rounded-lg border border-dashed border-gray-200 hover:border-emerald-400 hover:bg-emerald-50/50 text-gray-400 hover:text-emerald-600 text-[11px] transition">
                                                    + Kosong
                                                </button>
                                            @endif
                                        </td>

                                        {{-- Akun Zoom --}}
                                        <td class="py-2 px-2.5 border-l border-gray-100 align-top">
                                            @if($bookingZoom)
                                                <div class="p-2.5 rounded-xl bg-sky-50 border border-sky-200 text-sky-900 shadow-2xs">
                                                    <div class="flex items-center justify-between">
                                                        <p class="font-bold text-[11px] truncate">{{ $bookingZoom->nama_acara }}</p>
                                                        <span class="text-[9px] font-bold px-1.5 py-0.5 bg-sky-200 text-sky-800 rounded">
                                                            {{ $bookingZoom->zoom_account === 'zoom_1' ? 'Zoom 1' : ($bookingZoom->zoom_account === 'zoom_2' ? 'Zoom 2' : 'Eksternal') }}
                                                        </span>
                                                    </div>
                                                    <p class="text-[10px] text-sky-700 mt-0.5">
                                                        {{ substr($bookingZoom->start_time, 0, 5) }} - {{ substr($bookingZoom->end_time, 0, 5) }} • {{ $bookingZoom->penyelenggara }}
                                                    </p>
                                                </div>
                                            @else
                                                <button type="button"
                                                        @click="openBookingModal(null, 'zoom_1', '{{ $slot }}')"
                                                        class="w-full py-2 px-2 text-center rounded-lg border border-dashed border-gray-200 hover:border-sky-400 hover:bg-sky-50/50 text-gray-400 hover:text-sky-600 text-[11px] transition">
                                                    + Kosong
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

            {{-- 3. TIME SCHEDULE: VIEW BULANAN (MONTHLY AGENDA LIST) --}}
            @else
                <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-xs space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-bold text-gray-900">
                                Agenda Bulanan: {{ \Carbon\Carbon::parse($selectedMonth . '-01')->translatedFormat('F Y') }}
                            </h3>
                            <p class="text-xs text-gray-500">Daftar seluruh reservasi ruangan rapat dan akun Zoom bulan ini.</p>
                        </div>
                    </div>

                    @if($monthlyBookings->isEmpty())
                        <div class="py-12 text-center text-gray-400 text-xs font-medium bg-gray-50 rounded-2xl border border-dashed border-gray-200">
                            Belum ada jadwal booking ruangan pada bulan ini.
                        </div>
                    @else
                        <div class="space-y-3">
                            @foreach($monthlyBookings->groupBy('booking_date') as $bDate => $bList)
                                <div class="p-4 rounded-xl border border-gray-200 bg-gray-50/50 space-y-2.5">
                                    <div class="flex items-center justify-between border-b border-gray-200 pb-2">
                                        <div class="flex items-center gap-2">
                                            <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                                            <strong class="text-xs font-bold text-gray-800">
                                                {{ \Carbon\Carbon::parse($bDate)->translatedFormat('l, d F Y') }}
                                            </strong>
                                        </div>
                                        <span class="text-[11px] font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded-md">
                                            {{ $bList->count() }} Kegiatan
                                        </span>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 pt-1">
                                        @foreach($bList as $bk)
                                            <div class="p-3 bg-white rounded-xl border border-gray-200 shadow-2xs space-y-1.5">
                                                <div class="flex items-start justify-between gap-2">
                                                    <h5 class="text-xs font-bold text-gray-900">{{ $bk->nama_acara }}</h5>
                                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded {{ $bk->venue_id == 1 ? 'bg-blue-50 text-blue-700' : ($bk->venue_id == 2 ? 'bg-purple-50 text-purple-700' : ($bk->venue_id == 3 ? 'bg-emerald-50 text-emerald-700' : 'bg-sky-50 text-sky-700')) }}">
                                                        {{ $bk->nama_ruangan ?: ($bk->zoom_account ? 'Online Zoom' : '-') }}
                                                    </span>
                                                </div>
                                                <p class="text-[11px] text-gray-500">
                                                    ⏰ {{ substr($bk->start_time, 0, 5) }} - {{ substr($bk->end_time, 0, 5) }} • PJ: {{ $bk->penyelenggara }}
                                                </p>
                                                @if($bk->zoom_link)
                                                    <div class="pt-1 flex items-center justify-between text-[11px]">
                                                        <a href="{{ $bk->zoom_link }}" target="_blank" class="text-blue-600 font-bold hover:underline truncate">
                                                            Link Zoom
                                                        </a>
                                                        <span class="text-gray-400 text-[10px]">Tipe: {{ ucfirst($bk->tipe_pertemuan) }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif

        </div>

    </div>

    {{-- =========================================================
        MODAL POPUP: FORM BOOKING RUANGAN & AKUN ZOOM
    ========================================================== --}}
    <div x-show="modalOpen"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 sm:p-6"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">

        {{-- Backdrop --}}
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="modalOpen = false"></div>

        {{-- Modal Content Card --}}
        <div class="relative bg-white rounded-2xl shadow-2xl border border-gray-200 max-w-2xl w-full p-6 sm:p-7 overflow-hidden z-10 space-y-5"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95">

            <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
                        🏛️
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-gray-900">Form Booking Ruangan & Zoom</h3>
                        <p class="text-xs text-gray-500">Pilih ruangan rapat fisik, akun zoom, dan tentukan rentang waktu.</p>
                    </div>
                </div>
                <button type="button" @click="modalOpen = false" class="text-gray-400 hover:text-gray-600 text-lg font-bold p-1">&times;</button>
            </div>

            <form action="{{ route('booking-ruangan.store') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="task_id" :value="taskId">

                {{-- Pilih Rapat Terkait (Opsional jika bukan dari buat rapat) --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                        Hubungkan dengan Rapat Terdaftar (Opsional)
                    </label>
                    <select class="w-full px-3.5 py-2 text-xs rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600"
                            @change="
                                let sel = $event.target.selectedOptions[0];
                                if (sel.value) {
                                    taskId = sel.value;
                                    namaAcara = sel.getAttribute('data-text') || '';
                                    penyelenggara = sel.getAttribute('data-pj') || '';
                                    bookingDate = sel.getAttribute('data-date') || bookingDate;
                                    startTime = sel.getAttribute('data-start') || startTime;
                                    endTime = sel.getAttribute('data-end') || endTime;
                                }
                            ">
                        <option value="">-- Buat Booking Baru / Pilih Rapat Terdaftar --</option>
                        @foreach($availableRapats as $rpt)
                            <option value="{{ $rpt->id }}"
                                    data-text="{{ $rpt->text }}"
                                    data-pj="{{ $rpt->penanggung_jawab ?? $rpt->pemimpin }}"
                                    data-date="{{ $rpt->start_date }}"
                                    data-start="{{ $rpt->start_jam ? substr($rpt->start_jam, 0, 5) : '09:00' }}"
                                    data-end="{{ $rpt->end_jam ? substr($rpt->end_jam, 0, 5) : '11:00' }}"
                                    {{ ($selectedRapat && $selectedRapat->id == $rpt->id) ? 'selected' : '' }}>
                                [{{ \Carbon\Carbon::parse($rpt->start_date)->format('d/m') }}] {{ $rpt->text }} (PJ: {{ $rpt->penanggung_jawab ?? $rpt->pemimpin }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Nama Acara & Penyelenggara --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                            Nama Acara / Agenda <span class="text-rose-500">*</span>
                        </label>
                        <input type="text"
                               name="nama_acara"
                               x-model="namaAcara"
                               required
                               placeholder="Contoh: Rapat Evaluasi Sensus Pertanian"
                               class="w-full px-3.5 py-2 text-xs rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                            Penyelenggara / PJ <span class="text-rose-500">*</span>
                        </label>
                        <input type="text"
                               name="penyelenggara"
                               x-model="penyelenggara"
                               required
                               placeholder="Contoh: Agnes Widiastuti"
                               class="w-full px-3.5 py-2 text-xs rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600">
                    </div>
                </div>

                {{-- Tipe Pertemuan (Radio: Offline / Online / Hybrid) --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                        Tipe Pertemuan <span class="text-rose-500">*</span>
                    </label>
                    <div class="grid grid-cols-3 gap-2.5">
                        <label class="flex items-center gap-2 p-2.5 rounded-xl border cursor-pointer text-xs font-bold transition"
                               :class="tipePertemuan === 'offline' ? 'bg-blue-50 border-blue-500 text-blue-700 shadow-2xs' : 'bg-gray-50 border-gray-200 text-gray-700'">
                            <input type="radio" name="tipe_pertemuan" value="offline" x-model="tipePertemuan" class="text-blue-600">
                            <span>Offline (Ruangan)</span>
                        </label>
                        <label class="flex items-center gap-2 p-2.5 rounded-xl border cursor-pointer text-xs font-bold transition"
                               :class="tipePertemuan === 'online' ? 'bg-sky-50 border-sky-500 text-sky-700 shadow-2xs' : 'bg-gray-50 border-gray-200 text-gray-700'">
                            <input type="radio" name="tipe_pertemuan" value="online" x-model="tipePertemuan" class="text-sky-600">
                            <span>Online (Zoom)</span>
                        </label>
                        <label class="flex items-center gap-2 p-2.5 rounded-xl border cursor-pointer text-xs font-bold transition"
                               :class="tipePertemuan === 'hybrid' ? 'bg-purple-50 border-purple-500 text-purple-700 shadow-2xs' : 'bg-gray-50 border-gray-200 text-gray-700'">
                            <input type="radio" name="tipe_pertemuan" value="hybrid" x-model="tipePertemuan" class="text-purple-600">
                            <span>Hybrid (Campuran)</span>
                        </label>
                    </div>
                </div>

                {{-- Pilihan Ruangan Fisik (Jika Offline atau Hybrid) --}}
                <div x-show="tipePertemuan === 'offline' || tipePertemuan === 'hybrid'" class="space-y-1.5">
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider">
                        Pilih Ruangan Rapat Fisik <span class="text-rose-500">*</span>
                    </label>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                        @foreach($venues as $v)
                            <label class="p-3 rounded-xl border cursor-pointer text-xs transition"
                                   :class="selectedVenueId == {{ $v->id }} ? 'bg-blue-50 border-blue-500 text-blue-900 shadow-2xs' : 'bg-gray-50 border-gray-200 text-gray-700'">
                                <div class="flex items-center gap-2">
                                    <input type="radio" name="venue_id" value="{{ $v->id }}" x-model="selectedVenueId" class="text-blue-600">
                                    <span class="font-bold">{{ $v->name }}</span>
                                </div>
                                <p class="text-[10px] text-gray-500 mt-1">Kapasitas {{ $v->capacity }} Orang</p>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- Pilihan Akun Zoom (Jika Online atau Hybrid) --}}
                <div x-show="tipePertemuan === 'online' || tipePertemuan === 'hybrid'" class="space-y-2 bg-indigo-50/40 p-3.5 rounded-xl border border-indigo-100">
                    <label class="block text-xs font-bold text-indigo-900 uppercase tracking-wider">
                        Pilihan Akun Zoom / Link Meeting <span class="text-rose-500">*</span>
                    </label>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                        <label class="p-2.5 rounded-lg border bg-white cursor-pointer text-xs transition"
                               :class="zoomAccount === 'zoom_1' ? 'border-indigo-500 ring-2 ring-indigo-500/20 text-indigo-900 font-bold' : 'border-gray-200 text-gray-700'">
                            <input type="radio" name="zoom_account" value="zoom_1" x-model="zoomAccount" class="text-indigo-600">
                            <span>Akun Zoom 1 (Utama)</span>
                        </label>
                        <label class="p-2.5 rounded-lg border bg-white cursor-pointer text-xs transition"
                               :class="zoomAccount === 'zoom_2' ? 'border-indigo-500 ring-2 ring-indigo-500/20 text-indigo-900 font-bold' : 'border-gray-200 text-gray-700'">
                            <input type="radio" name="zoom_account" value="zoom_2" x-model="zoomAccount" class="text-indigo-600">
                            <span>Akun Zoom 2 (Cadangan)</span>
                        </label>
                        <label class="p-2.5 rounded-lg border bg-white cursor-pointer text-xs transition"
                               :class="zoomAccount === 'eksternal' ? 'border-indigo-500 ring-2 ring-indigo-500/20 text-indigo-900 font-bold' : 'border-gray-200 text-gray-700'">
                            <input type="radio" name="zoom_account" value="eksternal" x-model="zoomAccount" class="text-indigo-600">
                            <span>Link Zoom Eksternal</span>
                        </label>
                    </div>

                    <div x-show="zoomAccount === 'eksternal'" class="pt-2">
                        <input type="url"
                               name="zoom_link_custom"
                               placeholder="Masukkan link zoom eksternal (https://zoom.us/j/...)"
                               class="w-full px-3 py-1.5 text-xs rounded-lg border border-gray-200 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600">
                    </div>
                </div>

                {{-- Tanggal, Jam Mulai, Jam Selesai --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                            Tanggal <span class="text-rose-500">*</span>
                        </label>
                        <input type="date"
                               name="booking_date"
                               x-model="bookingDate"
                               required
                               class="w-full px-3.5 py-2 text-xs rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                            Jam Mulai <span class="text-rose-500">*</span>
                        </label>
                        <input type="time"
                               name="start_time"
                               x-model="startTime"
                               required
                               class="w-full px-3.5 py-2 text-xs rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                            Jam Selesai <span class="text-rose-500">*</span>
                        </label>
                        <input type="time"
                               name="end_time"
                               x-model="endTime"
                               required
                               class="w-full px-3.5 py-2 text-xs rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600">
                    </div>
                </div>

                {{-- Catatan / Keterangan --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                        Catatan / Permintaan Khusus Sarpras (Opsional)
                    </label>
                    <input type="text"
                           name="keterangan"
                           x-model="keterangan"
                           placeholder="Contoh: Butuh tambahan 2 mic wireless dan layar proyektor ganda"
                           class="w-full px-3.5 py-2 text-xs rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600">
                </div>

                {{-- Tombol Aksi Modal --}}
                <div class="pt-3 border-t border-gray-100 flex items-center justify-end gap-2.5">
                    <button type="button"
                            @click="modalOpen = false"
                            class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-xl transition">
                        Batal
                    </button>
                    <button type="submit"
                            class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-xs transition">
                        Konfirmasi Reservasi &rarr;
                    </button>
                </div>
            </form>

        </div>
    </div>

</div>
@endsection
