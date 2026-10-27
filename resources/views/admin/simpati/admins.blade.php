@extends('layouts.app')

@section('title', 'Kelola Admin SIMPATI - SIMPATI')
@section('header_title', 'Kelola Admin SIMPATI')

@section('content')
@php
    $pageTitle = 'Kelola Admin SIMPATI';
    $pageSubtitle = 'Tetapkan dan kelola role admin SIMPATI. Setiap role memiliki cakupan grup petugas yang berbeda (lihat rincian di tiap kartu).';
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

        <form method="POST" action="{{ route('simpati.admins.store') }}"
              @submit="if(!pickedId) { $event.preventDefault(); alert('Silakan cari dan pilih pegawai dari daftar pencarian terlebih dahulu.'); }"
              class="grid grid-cols-1 md:grid-cols-12 gap-4 items-start">
            @csrf
            <input type="hidden" name="user_id" :value="pickedId">

            {{-- Kolom 1: Cari Pegawai --}}
            <div class="md:col-span-5 relative">
                <label for="admin-pegawai-search" class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1.5">
                    Pegawai <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"></path></svg>
                    </div>
                    <input type="text" id="admin-pegawai-search" name="pegawai_search" autocomplete="off"
                           x-model="search"
                           @focus="if(!pickedId) searchPegawai()"
                           @click="if(!pickedId) searchPegawai()"
                           @input.debounce.300ms="searchPegawai()"
                           placeholder="Ketik nama / NIP atau klik untuk daftar…"
                           class="w-full pl-10 pr-8 py-2.5 rounded-xl border border-gray-200 text-sm outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-400 bg-white">
                    <button type="button" x-show="search || pickedId" @click="clearPick()"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600"
                            title="Reset pilihan">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <p x-show="!pickedId && results.length === 0 && search.length <= 1" class="text-[11px] text-gray-400 mt-1.5">
                    Klik kolom di atas untuk menampilkan daftar pegawai, atau ketik nama/NIP untuk memfilter.
                </p>

                {{-- Dropdown Hasil Pencarian (Floating absolute agar tidak menimpa elemen lain) --}}
                <div class="absolute left-0 right-0 top-full mt-1.5 z-50 bg-white rounded-xl border border-gray-200 shadow-xl max-h-56 overflow-y-auto divide-y divide-gray-100"
                     x-show="results.length > 0"
                     x-cloak
                     @click.away="results = []">
                    <template x-for="p in results" :key="p.id">
                        <button type="button" @click="pick(p)"
                                class="w-full text-left px-3.5 py-2.5 text-sm flex items-center justify-between gap-2 hover:bg-blue-50/70 transition"
                                :class="pickedId === p.id ? 'bg-blue-50 text-blue-900 font-semibold' : ''">
                            <span class="min-w-0">
                                <span class="block font-semibold text-gray-800 truncate" x-text="p.nama_lengkap"></span>
                                <span class="block text-[11px] text-gray-400 truncate" x-text="(p.niplama || '-') + ' · ' + (p.nm_jabatan || 'Pegawai')"></span>
                            </span>
                            <span class="text-xs text-blue-600 shrink-0 font-bold">Pilih &rarr;</span>
                        </button>
                    </template>
                </div>

                <p x-show="search.length > 1 && results.length === 0 && !pickedId" class="text-xs text-rose-500 mt-1.5" x-cloak>
                    Pegawai tidak ditemukan. Pastikan ejaan nama atau NIP sesuai.
                </p>
                <div class="mt-2 flex items-center gap-2 p-2 rounded-xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-800"
                     x-show="pickedId" x-cloak>
                    <svg class="w-4 h-4 shrink-0 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <span class="truncate">Dipilih: <strong x-text="pickedName"></strong></span>
                </div>
            </div>

            {{-- Kolom 2: Pilihan Role --}}
            <div class="md:col-span-4">
                <label for="admin-role-select" class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1.5">
                    Role Admin <span class="text-rose-500">*</span>
                </label>
                <select id="admin-role-select" name="role" x-model="role" required
                        class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm bg-white outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-400">
                    @foreach($roles as $r)
                        <option value="{{ $r }}">{{ $roleLabels[$r] }}</option>
                    @endforeach
                </select>
                <p class="text-xs text-gray-400 mt-2 leading-relaxed" x-text="roleDesc[role] || ''">{{ $roleDesc[$roles[0]] }}</p>
            </div>

            {{-- Kolom 3: Tombol Tetapkan Role --}}
            <div class="md:col-span-3 md:pt-6">
                <button type="submit"
                        class="w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-xs font-bold transition shadow-sm whitespace-nowrap">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    <span>Tetapkan Role</span>
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
                                          onsubmit="return confirm('Batalkan role {{ $roleLabels[$r] }} dari {{ addslashes($h->nama_lengkap) }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Batalkan role"
                                                class="px-2.5 py-1.5 rounded-lg text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200 hover:bg-rose-100 transition">Batal</button>
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
                const q = (this.search || '').trim();
                fetch('{{ route('simpati.petugas.cari') }}?q=' + encodeURIComponent(q))
                    .then(r => r.json())
                    .then(res => { this.results = res.data || []; })
                    .catch(() => { this.results = []; });
            },

            pick(p) {
                this.pickedId = p.id;
                this.pickedName = p.nama_lengkap + (p.niplama ? ' (' + p.niplama + ')' : '');
                this.results = [];
                this.search = p.nama_lengkap;
            },

            clearPick() {
                this.pickedId = null;
                this.pickedName = '';
                this.search = '';
                this.results = [];
            }
        };
    }
</script>
@endpush
