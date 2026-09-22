@extends('layouts.app')

@section('title', 'Akun Ketua Tim Berhasil Disiapkan - Sikeren')
@section('header_title', 'Daftar Akun Ketua Tim')

@section('content')
<div class="max-w-4xl mx-auto space-y-6 pb-12">

    {{-- HEADER --}}
    <div class="bg-white p-6 rounded-2xl border border-gray-200/80 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <span class="px-2.5 py-0.5 rounded-md text-[11px] font-bold uppercase tracking-wider bg-emerald-100 text-emerald-800 border border-emerald-200">
                Akun Siap Digunakan
            </span>
            <h2 class="text-xl font-bold text-gray-900 mt-2">Daftar Akun Ketua Tim / Pemimpin Rapat</h2>
            <p class="text-xs text-gray-500 mt-0.5">Akun di bawah ini telah disinkronkan dan dapat langsung digunakan untuk login dan menyetujui rapat.</p>
        </div>
        <div>
            <a href="{{ url('/daftar_kegiatan') }}" class="inline-flex items-center px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-xs transition-colors">
                &larr; Ke Daftar Kegiatan
            </a>
        </div>
    </div>

    {{-- CREDENTIALS TABLE --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
            <h3 class="text-sm font-bold text-gray-800">Kredensial Login Ketua Tim ({{ count($createdAccounts) }})</h3>
            <span class="text-xs font-mono text-gray-500">Password default: <strong class="text-gray-900">password</strong></span>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-left text-xs">
                <thead class="bg-gray-50 text-[11px] font-bold text-gray-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-3">No</th>
                        <th class="px-6 py-3">Nama Ketua Tim</th>
                        <th class="px-6 py-3">Email Login</th>
                        <th class="px-6 py-3">Password</th>
                        <th class="px-6 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @foreach($createdAccounts as $idx => $acc)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-6 py-4 text-gray-400">{{ $idx + 1 }}</td>
                        <td class="px-6 py-4 font-bold text-gray-900">
                            {{ $acc['nama'] }}
                        </td>
                        <td class="px-6 py-4 text-blue-600 font-mono font-semibold">
                            {{ $acc['email'] }}
                        </td>
                        <td class="px-6 py-4 font-mono text-gray-700">
                            <span class="px-2.5 py-1 bg-gray-100 rounded-md border border-gray-200 font-bold">
                                {{ $acc['password'] }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <form action="{{ url('/switch-account') }}" method="POST" class="inline">
                                @csrf
                                <input type="hidden" name="email" value="{{ $acc['email'] }}">
                                <button type="submit" class="px-3.5 py-1.5 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-lg text-xs transition-colors shadow-xs">
                                    Login sebagai Akun Ini &rarr;
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
