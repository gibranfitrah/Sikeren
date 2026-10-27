@extends('layouts.app')

@section('title', 'Rekap Kehadiran Petugas - SIMPATI')
@section('header_title', 'Rekap Kehadiran Petugas SIMPATI')

@section('content')
@php
    $pageTitle = 'Rekap Kehadiran Petugas';
    $pageSubtitle = 'Laporan rekapitulasi kehadiran tugas per pegawai berdasarkan perannya (Admin PST, PPID, Satpam, CS, Ruangan, Zoom).';
@endphp

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    @include('admin.simpati._nav')

    {{-- Filter Bulan & Grup --}}
    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-5 sm:p-6 mb-6">
        <form method="GET" action="{{ route('simpati.jadwal.rekap') }}" class="flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div class="flex flex-wrap items-center gap-3">
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1.5">Pilih Bulan</label>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('simpati.jadwal.rekap', ['bulan' => $prevBulan, 'grup' => $grupFilter]) }}"
                           class="w-9 h-9 rounded-xl border border-gray-200 bg-gray-50 hover:bg-gray-100 flex items-center justify-center text-gray-600 transition" title="Bulan Sebelumnya">
                            &larr;
                        </a>
                        <input type="month" name="bulan" value="{{ $bulanInput }}"
                               class="px-3.5 py-2 rounded-xl border border-gray-200 text-sm font-semibold text-gray-800 focus:ring-2 focus:ring-blue-500 outline-none">
                        <a href="{{ route('simpati.jadwal.rekap', ['bulan' => $nextBulan, 'grup' => $grupFilter]) }}"
                           class="w-9 h-9 rounded-xl border border-gray-200 bg-gray-50 hover:bg-gray-100 flex items-center justify-center text-gray-600 transition" title="Bulan Berikutnya">
                            &rarr;
                        </a>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1.5">Filter Peran / Grup</label>
                    <select name="grup" class="px-3.5 py-2 rounded-xl border border-gray-200 text-sm font-semibold text-gray-800 focus:ring-2 focus:ring-blue-500 outline-none bg-white">
                        <option value="semua">Semua Peran / Grup</option>
                        @foreach($grups as $k => $v)
                            <option value="{{ $k }}" {{ $grupFilter === $k ? 'selected' : '' }}>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="pt-5">
                    <button type="submit" class="px-5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition shadow-sm">
                        Terapkan Filter
                    </button>
                </div>
            </div>

            <div class="text-right">
                <span class="text-xs text-gray-400 block">Periode Laporan</span>
                <span class="text-base font-extrabold text-blue-900">{{ $bulanLabel }}</span>
            </div>
        </form>
    </div>

    {{-- Tabel Rekap Kehadiran --}}
    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-sm font-bold text-gray-800">Daftar Rekapitulasi Kehadiran Penugasan</h3>
            <span class="text-xs text-gray-400">{{ count($rekap) }} Petugas Terdata</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 text-[11px] font-bold text-gray-400 uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-3.5">No</th>
                        <th class="px-6 py-3.5">Nama Pegawai / Petugas</th>
                        <th class="px-6 py-3.5">NIP</th>
                        <th class="px-6 py-3.5">Peran / Layanan</th>
                        <th class="px-6 py-3.5 text-center">Total Jadwal</th>
                        <th class="px-6 py-3.5 text-center">Hadir</th>
                        <th class="px-6 py-3.5 text-center">Izin / Cuti</th>
                        <th class="px-6 py-3.5 text-center">Digantikan</th>
                        <th class="px-6 py-3.5 text-center">Persentase</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($rekap as $idx => $r)
                        <tr class="hover:bg-gray-50/70 transition">
                            <td class="px-6 py-3.5 text-xs text-gray-400 font-semibold">{{ $idx + 1 }}</td>
                            <td class="px-6 py-3.5 font-bold text-gray-900">{{ $r['nama'] }}</td>
                            <td class="px-6 py-3.5 text-xs text-gray-500 font-mono">{{ $r['niplama'] ?: '-' }}</td>
                            <td class="px-6 py-3.5">
                                <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                    {{ $r['grup_label'] }}
                                </span>
                            </td>
                            <td class="px-6 py-3.5 text-center font-bold text-gray-800">{{ $r['total'] }}</td>
                            <td class="px-6 py-3.5 text-center font-extrabold text-emerald-600">{{ $r['hadir'] }}</td>
                            <td class="px-6 py-3.5 text-center font-semibold text-amber-600">{{ $r['izin'] }}</td>
                            <td class="px-6 py-3.5 text-center font-semibold text-purple-600">{{ $r['diganti'] }}</td>
                            <td class="px-6 py-3.5 text-center">
                                <span class="inline-block px-2.5 py-1 rounded-lg text-xs font-black {{ $r['persentase'] >= 80 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : ($r['persentase'] >= 50 ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-gray-100 text-gray-600') }}">
                                    {{ $r['persentase'] }}%
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-12 text-center text-sm text-gray-400">
                                Belum ada data jadwal atau kehadiran petugas untuk bulan {{ $bulanLabel }}.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
