@extends('layouts.app')

@section('title', 'Kelola Admin SIMPATI - SIMPATI')
@section('header_title', 'Kelola Admin SIMPATI')

@section('content')
@php
    $pageTitle = 'Kelola Admin SIMPATI';
    $pageSubtitle = 'Tetapkan dan cabut role admin SIMPATI. Setiap role memiliki cakupan grup petugas yang berbeda (lihat rincian di tiap kartu).';
@endphp

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6" x-data="adminsUi()">

    @include('admin.simpati._nav')

    {{-- Tetapkan Admin Baru --}}
    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-5 sm:p-6 mb-6">
        <div class="flex flex-wrap items-start justify-between gap-3 mb-4">
            <div>
                <h3 class="text-sm font-black text-gray-800">Tetapkan Admin Baru</h3>
                <p class="text-xs text-gray-400 mt-0.5">Pilih pegawai lalu pilih role. Satu pegawai boleh memegang lebih dari satu role.</p>
            </div>
            <span class="px-3 py-1.5 rounded-xl bg-blue-50 border border-blue-100 text-[11px] font-bold text-blue-700">
                {{ count($assignedUsers) }} pegawai memegang role
            </span>
        </div>

        <form method="POST" action="{{ route('simpati.admins.store') }}" class="flex flex-col gap-3 lg:flex-row lg:items-start">
            @csrf
            <input type="hidden" name="user_id" :value="pickedId">

            <div class="w-full min-w-0 flex-1">
                <label for="admin-pegawai-search" class="block text-[11px] font-bold text-gray-400 uppercase mb-1.5">Pegawai</label>
                <input type="text" id="admin-pegawai-search" name="pegawai_search" autocomplete="off"
                       x-model="search" @input.debounce.300ms="searchPegawai()"
                       placeholder="Ketik nama atau NIP pegawai…"
                       class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-400">
                <div class="relative z-20">
                    <div class="mt-2 max-h-52 overflow-y-auto rounded-xl border border-gray-100 bg-white shadow-sm divide-y divide-gray-50" x-show="results.length > 0">
                        <template x-for="p in results" :key="p.id">
                            <button type="button" @click="pick(p)"
                                    class="w-full text-left px-3.5 py-2.5 text-sm flex items-center justify-between gap-2 transition"
                                    :class="pickedId === p.id ? 'bg-blue-50' : 'hover:bg-gray-50'">
                                <span class="min-w-0">
                                    <span class="block font-semibold text-gray-800 truncate" x-text="p.nama_lengkap"></span>
                                    <span class="block text-[11px] text-gray-400 truncate" x-text="p.niplama + ' · ' + p.nm_jabatan"></span>
                                </span>
                            </button>
                        </template>
                    </div>
                </div>
                <p x-show="search.length > 0 && results.length === 0" class="text-xs text-gray-400 mt-2">Pegawai tidak ditemukan.</p>
                <p class="text-xs text-gray-500 mt-2" x-show="pickedId">Dipilih: <span class="font-bold text-gray-800" x-text="pickedName"></span></p>
            </div>

            <div class="w-full shrink-0 lg:w-64">
                <label for="admin-role-select" class="block text-[11px] font-bold text-gray-400 uppercase mb-1.5">Role</label>
                <select id="admin-role-select" name="role" x-model="role" required
                        class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm bg-white outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-400">
                    @foreach($roles as $r)
                        <option value="{{ $r }}">{{ $roleLabels[$r] }}</option>
                    @endforeach
                </select>
                <p class="text-[11px] text-gray-400 mt-2 leading-snug" x-text="roleDesc[role] || ''">{{ $roleDesc[$roles[0]] }}</p>
            </div>

            <div class="w-full shrink-0 lg:pt-[26px]">
                <button type="submit" class="w-full lg:w-auto px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition shadow-sm whitespace-nowrap">
                    Tetapkan Role
                </button>
            </div>
        </form>
    </div>

    {{-- Kartu per Role --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
        @foreach($roles as $r)
            @php $roleHolders = $holders[$r] ?? []; @endphp
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-5 flex flex-col {{ count($roleHolders) ? '' : 'opacity-90' }}">
                <div class="flex items-start justify-between gap-3 mb-3">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full {{ $r === 'super_admin' ? 'bg-rose-500' : 'bg-blue-500' }}"></span>
                            <h3 class="text-sm font-black text-gray-900">{{ $roleLabels[$r] }}</h3>
                        </div>
                        <p class="text-[11px] text-gray-400 mt-1 leading-snug">{{ $roleDesc[$r] }}</p>
                    </div>
                    <span class="shrink-0 px-2.5 py-1 rounded-lg text-[11px] font-bold {{ count($roleHolders) ? 'bg-blue-50 text-blue-700 border border-blue-100' : 'bg-gray-50 text-gray-400 border border-gray-100' }}">
                        {{ count($roleHolders) }} orang
                    </span>
                </div>

                @if(!empty($roleManages[$r]))
                    <div class="flex flex-wrap gap-1.5 mb-3">
                        @foreach($roleManages[$r] as $g)
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                {{ $grups[$g] ?? $g }}
                            </span>
                        @endforeach
                    </div>
                @endif

                <div class="border-t border-gray-100 pt-3 flex-1">
                    @if(count($roleHolders))
                        <ul class="space-y-2">
                            @foreach($roleHolders as $h)
                                <li class="flex items-center justify-between gap-2">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <div class="w-8 h-8 rounded-lg {{ $r === 'super_admin' ? 'bg-rose-100 text-rose-700' : 'bg-blue-100 text-blue-700' }} flex items-center justify-center font-bold text-[11px] shrink-0">
                                            {{ strtoupper(substr($h->nama_lengkap, 0, 1)) }}
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-xs font-semibold text-gray-800 truncate">{{ $h->nama_lengkap }}</p>
                                            <p class="text-[10px] text-gray-400 truncate">{{ $h->niplama ?: $h->email }}</p>
                                        </div>
                                    </div>
                                    <form method="POST" action="{{ route('simpati.admins.destroy', $h->role_row_id) }}"
                                          onsubmit="return confirm('Cabut role {{ $roleLabels[$r] }} dari {{ addslashes($h->nama_lengkap) }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Cabut role"
                                                class="px-2.5 py-1.5 rounded-lg text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200 hover:bg-rose-100 transition">Cabut</button>
                                    </form>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-[11px] text-gray-300 italic">Belum ada pegawai yang memegang role ini.</p>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection

@push('scripts')
<script>
    function adminsUi() {
        return {
            search: '',
            results: [],
            pickedId: null,
            pickedName: '',
            role: 'super_admin',
            roleDesc: @json($roleDesc),

            searchPegawai() {
                fetch('{{ route('simpati.petugas.cari') }}?q=' + encodeURIComponent(this.search || ''))
                    .then(r => r.json())
                    .then(res => { this.results = res.data || []; })
                    .catch(() => { this.results = []; });
            },

            pick(p) {
                this.pickedId = p.id;
                this.pickedName = p.nama_lengkap + ' (' + p.niplama + ')';
                this.results = [];
                this.search = '';
            },
        };
    }
</script>
@endpush
