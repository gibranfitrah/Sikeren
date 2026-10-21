@extends('layouts.app')

@section('title', 'Dashboard SIMPATI - Sikeren')
@section('header_title', 'Dashboard SIMPATI')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    @php
        $pageTitle = 'Dashboard Petugas Layanan';
        $pageSubtitle = 'Ringkasan jumlah petugas PST, PPID, Satpam, dan Cleaning Service serta daftar petugas yang bertugas hari ini.';
        $cardStyles = [
            'pst'    => ['icon' => 'bg-blue-50 text-blue-600', 'border' => 'border-blue-100', 'chip' => 'bg-blue-50 text-blue-700 border-blue-200'],
            'ppid'   => ['icon' => 'bg-indigo-50 text-indigo-600', 'border' => 'border-indigo-100', 'chip' => 'bg-indigo-50 text-indigo-700 border-indigo-200'],
            'satpam' => ['icon' => 'bg-amber-50 text-amber-600', 'border' => 'border-amber-100', 'chip' => 'bg-amber-50 text-amber-700 border-amber-200'],
            'cs'     => ['icon' => 'bg-emerald-50 text-emerald-600', 'border' => 'border-emerald-100', 'chip' => 'bg-emerald-50 text-emerald-700 border-emerald-200'],
        ];
        $icons = [
            'pst'    => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path>',
            'ppid'   => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>',
            'satpam' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>',
            'cs'     => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-15l1.5 1.5L14 8l-1.5-1.5L11 5zm5 5l1.5 1.5L19 12l-1.5-1.5L16 10zM9 15l1.5 1.5L12 18l-1.5-1.5L9 15z"></path>',
        ];
    @endphp

    @include('admin.simpati._nav')

    {{-- Kartu Jumlah Petugas per Grup --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
        @foreach($grups as $grup => $label)
            @php $style = $cardStyles[$grup] ?? $cardStyles['pst']; @endphp
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-5 flex items-center gap-4 {{ $style['border'] }}">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center shrink-0 {{ $style['icon'] }}">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $icons[$grup] !!}</svg>
                </div>
                <div class="min-w-0">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">{{ $label }}</p>
                    <p class="text-3xl font-black text-gray-900 leading-none mt-1">{{ $counts[$grup] ?? 0 }}</p>
                    <p class="text-[11px] text-gray-400 mt-1">petugas aktif</p>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Statistik Ringkas --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-xs p-5">
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Petugas Layanan</p>
            <p class="text-2xl font-black text-gray-900 mt-1">{{ $totalPetugas }}</p>
            <p class="text-[11px] text-gray-400 mt-1">dari seluruh grup (PST, PPID, Satpam, CS)</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-xs p-5">
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Bertugas Hari Ini</p>
            <p class="text-2xl font-black text-gray-900 mt-1">{{ $jumlahBertugas }}</p>
            <p class="text-[11px] text-gray-400 mt-1">petugas pada jadwal {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-xs p-5">
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Hari Ini</p>
            <p class="text-2xl font-black text-gray-900 mt-1">{{ \Carbon\Carbon::now()->translatedFormat('d/m/Y') }}</p>
            <p class="text-[11px] text-gray-400 mt-1">{{ \Carbon\Carbon::now()->translatedFormat('l') }}</p>
        </div>
    </div>

    {{-- Daftar Petugas yang Bertugas Hari Ini --}}
    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="p-5 sm:p-6 border-b border-gray-100 bg-gray-50/40 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h3 class="text-base font-bold text-gray-900">Petugas Bertugas Hari Ini</h3>
                <p class="text-xs text-gray-500 mt-0.5">Data aktual dari jadwal layanan dan sinkronisasi STARLA per {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
            </div>
            <a href="{{ route('simpati.jadwal.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-white border border-gray-200 text-xs font-bold text-gray-600 hover:border-blue-300 hover:text-blue-600 transition">
                Lihat Jadwal Bulanan
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </a>
        </div>

        @if(count($bertugasHariIni) === 0)
            <div class="p-12 text-center">
                <div class="w-14 h-14 rounded-2xl bg-gray-50 flex items-center justify-center mx-auto mb-3">
                    <svg class="w-7 h-7 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </div>
                <p class="text-sm font-bold text-gray-600">Tidak ada petugas yang bertugas hari ini</p>
                <p class="text-xs text-gray-400 mt-1 max-w-md mx-auto">Daftar akan muncul otomatis ketika ada jadwal layanan pada tanggal ini atau penugasan PST masuk dari STARLA.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 text-left text-[11px] uppercase tracking-wider text-gray-400">
                            <th class="px-6 py-3 font-bold">Grup</th>
                            <th class="px-6 py-3 font-bold">Nama Petugas</th>
                            <th class="px-6 py-3 font-bold">NIP Lama</th>
                            <th class="px-6 py-3 font-bold">Sesi</th>
                            <th class="px-6 py-3 font-bold">Jam</th>
                            <th class="px-6 py-3 font-bold">Tempat / Keterangan</th>
                            <th class="px-6 py-3 font-bold">Status</th>
                            <th class="px-6 py-3 font-bold">Sumber</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($bertugasHariIni as $item)
                            <tr class="hover:bg-gray-50/70 transition-colors">
                                <td class="px-6 py-3.5">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] font-bold border {{ $cardStyles[$item['grup']]['chip'] ?? 'bg-gray-50 text-gray-700 border-gray-200' }}">
                                        {{ $item['grupLabel'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-3.5 font-semibold text-gray-800">{{ $item['nama'] }}</td>
                                <td class="px-6 py-3.5 text-gray-500">{{ $item['niplama'] }}</td>
                                <td class="px-6 py-3.5 text-gray-600">{{ $item['sesi'] ?: '-' }}</td>
                                <td class="px-6 py-3.5 text-gray-600 whitespace-nowrap">{{ $item['jam'] }}</td>
                                <td class="px-6 py-3.5 text-gray-500 text-xs">{{ $item['tempat'] }}</td>
                                <td class="px-6 py-3.5">@include('admin.simpati._status', ['status' => $item['status']])</td>
                                <td class="px-6 py-3.5">
                                    @if($item['sumber'] === 'STARLA')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span> STARLA
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span> SIMPATI
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
