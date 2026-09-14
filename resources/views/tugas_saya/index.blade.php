@extends('layouts.app')

@section('title', 'Kegiatan Saya - Sikeren')
@section('header_title', 'Kegiatan Saya')

@section('content')

<div class="space-y-6 pb-12 max-w-7xl mx-auto">

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
            <h2 class="text-xl font-bold text-gray-900 mt-1">Kegiatan Saya</h2>
            <p class="text-xs text-gray-500 mt-0.5">Pantau seluruh kegiatan, rapat, dan sub kegiatan yang melibatkan Anda.</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('sub-kegiatan.index') }}" class="px-3.5 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-xl transition">
                Lihat Sub Kegiatan
            </a>
            <a href="{{ route('time-schedule.index', ['person' => Auth::user()->nama_lengkap]) }}" class="px-3.5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl transition inline-flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <span>Kalender Jadwal Saya</span>
            </a>
        </div>
    </div>

    {{-- STATS CARDS --}}
    @php
        $totalKegiatanAll = count($myTasks) + count($mySubKegiatans);
        $totalKegiatan = count($myTasks);
        $totalSub = count($mySubKegiatans);
    @endphp

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-gray-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-gray-500">Total Kegiatan Saya</p>
                <h3 class="text-2xl font-bold text-gray-900 mt-1">{{ $totalKegiatanAll }}</h3>
                <span class="text-[10px] text-gray-400">Kegiatan Utama & Sub Kegiatan</span>
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
    </div>

    {{-- SECTION 1: SUB KEGIATAN SAYA --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                <h3 class="font-bold text-gray-900 text-sm">Sub Kegiatan Saya</h3>
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

    {{-- SECTION 2: KEGIATAN & RAPAT UTAMA SAYA --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-blue-600"></span>
                <h3 class="font-bold text-gray-900 text-sm">Kegiatan & Rapat Utama Saya</h3>
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
                        <th class="px-6 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($myTasks as $task)
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
                                <span class="px-2.5 py-1 rounded-md text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-100">Penanggung Jawab</span>
                            @elseif($task->pemimpin == Auth::user()->nama_lengkap)
                                <span class="px-2.5 py-1 rounded-md text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-100">Pemimpin Rapat</span>
                            @elseif($task->notulis == Auth::user()->nama_lengkap)
                                <span class="px-2.5 py-1 rounded-md text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-100">Notulis</span>
                            @else
                                <span class="px-2.5 py-1 rounded-md text-[10px] font-bold bg-gray-100 text-gray-700">Anggota Tim</span>
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

</div>

@endsection