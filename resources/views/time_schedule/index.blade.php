@extends('layouts.app')

@section('title', 'Time Schedule & Kalender Anggota - Sikeren')
@section('header_title', 'Time Schedule Anggota')

@push('styles')
<!-- FullCalendar CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css">
<style>
    .fc .fc-toolbar-title {
        font-size: 1.15rem !important;
        font-weight: 700 !important;
        color: #1e293b !important;
    }
    .fc .fc-button-primary {
        background-color: #2563eb !important;
        border-color: #2563eb !important;
        border-radius: 0.75rem !important;
        font-size: 0.75rem !important;
        font-weight: 600 !important;
        padding: 0.4rem 0.8rem !important;
        text-transform: capitalize !important;
    }
    .fc .fc-button-primary:hover {
        background-color: #1d4ed8 !important;
        border-color: #1d4ed8 !important;
    }
    .fc .fc-button-primary:disabled {
        background-color: #94a3b8 !important;
        border-color: #94a3b8 !important;
    }
    .fc .fc-daygrid-day-number {
        font-size: 0.75rem !important;
        font-weight: 600 !important;
        color: #475569 !important;
    }
    .fc .fc-event {
        border-radius: 0.5rem !important;
        padding: 2px 4px !important;
        font-size: 0.7rem !important;
        cursor: pointer !important;
        transition: transform 0.15s ease !important;
    }
    .fc .fc-event:hover {
        transform: scale(1.02) !important;
    }
    .fc-theme-standard td, .fc-theme-standard th {
        border-color: #f1f5f9 !important;
    }
    .fc-col-header-cell-cushion {
        font-size: 0.75rem !important;
        font-weight: 700 !important;
        color: #64748b !important;
        text-transform: uppercase !important;
        padding: 8px 0 !important;
    }
</style>
@endpush

@section('content')

<div class="space-y-6 pb-12 max-w-7xl mx-auto">

    {{-- HEADER & CONTROLS --}}
    <div class="bg-white p-6 rounded-2xl border border-gray-200/80 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-md text-[11px] font-bold uppercase tracking-wider bg-blue-50 text-blue-700 border border-blue-100">
                    Jadwal Terintegrasi
                </span>
                <span class="text-xs text-gray-400">•</span>
                <span class="text-xs text-gray-500 font-medium">BPS Kabupaten / Kota</span>
            </div>
            <h2 class="text-xl font-bold text-gray-900 mt-1">Time Schedule & Kalender Kerja Anggota</h2>
            <p class="text-xs text-gray-500 mt-0.5">Filter jadwal penugasan per personil atau per tim dengan opsi tampilan Bulanan, Mingguan, dan Harian.</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ url('/dashboard') }}" class="px-3.5 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-xl transition inline-flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                <span>Dashboard</span>
            </a>
            <a href="{{ route('kegiatan.daftar') }}" class="px-3.5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl transition">
                Kelola Kegiatan
            </a>
        </div>
    </div>

    {{-- FILTER BAR: PILIH ORANG & PILIH TIM --}}
    <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-xs">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            {{-- Filter Orang / Pegawai --}}
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                    Filter Pegawai / Anggota:
                </label>
                <select id="filterPersonSelect" onchange="refreshCalendarEvents()" class="w-full px-3 py-2 text-xs rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 font-medium">
                    <option value="">-- Semua Pegawai (Semua Jadwal) --</option>
                    @foreach($pegawais as $peg)
                        <option value="{{ $peg->nama_lengkap }}" {{ $selectedPerson == $peg->nama_lengkap ? 'selected' : '' }}>
                            {{ $peg->nama_lengkap }} ({{ $peg->niplama ?? $peg->username }})
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Filter Tim / Fungsi --}}
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                    Filter Tim / Fungsi:
                </label>
                <select id="filterTeamSelect" onchange="refreshCalendarEvents()" class="w-full px-3 py-2 text-xs rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 font-medium">
                    <option value="">-- Semua Tim / Fungsi --</option>
                    @foreach($masterGroups as $mg)
                        <option value="{{ $mg->grup }}" {{ $selectedTeam == $mg->grup ? 'selected' : '' }}>
                            {{ $mg->grup }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Legend Info --}}
            <div class="flex flex-col justify-end">
                <div class="flex items-center gap-3 text-xs flex-wrap py-2">
                    <div class="flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded-full bg-indigo-600 inline-block"></span>
                        <span class="text-gray-600 font-medium">Rapat</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded-full bg-sky-600 inline-block"></span>
                        <span class="text-gray-600 font-medium">Kegiatan</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded-full bg-amber-500 inline-block"></span>
                        <span class="text-gray-600 font-medium">Sub Kegiatan</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded-full bg-emerald-500 inline-block"></span>
                        <span class="text-gray-600 font-medium">Selesai</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- CALENDAR CONTAINER --}}
    <div class="bg-white p-6 rounded-3xl border border-gray-200 shadow-sm">
        <div id="fullCalendarContainer" class="min-h-[650px]"></div>
    </div>

</div>

@endsection

@push('scripts')
<!-- FullCalendar JS -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/id.js"></script>
<script>
var calendarInstance = null;

function getEventSourceUrl() {
    var person = document.getElementById('filterPersonSelect').value;
    var team = document.getElementById('filterTeamSelect').value;
    var url = '{{ route("time-schedule.events") }}?person=' + encodeURIComponent(person) + '&team=' + encodeURIComponent(team);
    return url;
}

function refreshCalendarEvents() {
    if (calendarInstance) {
        calendarInstance.refetchEvents();
    }
}

document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('fullCalendarContainer');

    calendarInstance = new FullCalendar.Calendar(calendarEl, {
        locale: 'id',
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
        },
        buttonText: {
            today: 'Hari Ini',
            month: 'Bulanan',
            week: 'Mingguan',
            day: 'Harian',
            list: 'Daftar Agenda'
        },
        events: function(fetchInfo, successCallback, failureCallback) {
            fetch(getEventSourceUrl())
                .then(response => response.json())
                .then(data => successCallback(data))
                .catch(error => failureCallback(error));
        },
        eventClick: function(info) {
            if (info.event.url) {
                info.jsEvent.preventDefault();
                window.location.href = info.event.url;
            }
        },
        eventMouseEnter: function(info) {
            var props = info.event.extendedProps;
            var tooltipText = info.event.title + '\nPJ: ' + (props.pj || '-') + '\nTim: ' + (props.tim || '-') + '\nStatus: ' + (props.status || '-');
            info.el.setAttribute('title', tooltipText);
        }
    });

    calendarInstance.render();
});
</script>
@endpush