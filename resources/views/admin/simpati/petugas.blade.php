@extends('layouts.app')

@section('title', 'Kelola Petugas Layanan - SIMPATI')
@section('header_title', 'Kelola Petugas Layanan')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6" x-data="petugasUi()">

    @php
        $pageTitle = 'Kelola Petugas Layanan';
        $pageSubtitle = 'Daftar Semua Pegawai serta petugas PST, PPID, Satpam, dan Cleaning Service dalam satu menu. Satpam dan CS merupakan kelompok terpisah.';
        $canManageAny = count($manageable) > 0;
    @endphp

    @include('admin.simpati._nav')

    {{-- Tabs Grup --}}
    <div class="flex flex-wrap gap-2 mb-5">
        <a href="{{ route('simpati.petugas.index', ['tab' => 'semua']) }}"
           class="px-4 py-2.5 rounded-xl text-xs font-bold transition {{ $tab === 'semua' ? 'bg-slate-900 text-white shadow-lg' : 'bg-white text-gray-500 border border-gray-200 hover:border-slate-300' }}">
            Semua Pegawai
            <span class="ml-1.5 px-1.5 py-0.5 rounded-md text-[10px] {{ $tab === 'semua' ? 'bg-white/20' : 'bg-gray-100' }}">{{ number_format($tabCounts['semua'] ?? 0) }}</span>
        </a>
        @foreach($grups as $grup => $label)
            <a href="{{ route('simpati.petugas.index', ['tab' => $grup]) }}"
               class="px-4 py-2.5 rounded-xl text-xs font-bold transition {{ $tab === $grup ? 'bg-slate-900 text-white shadow-lg' : 'bg-white text-gray-500 border border-gray-200 hover:border-slate-300' }}">
                {{ $label }}
                <span class="ml-1.5 px-1.5 py-0.5 rounded-md text-[10px] {{ $tab === $grup ? 'bg-white/20' : 'bg-gray-100' }}">{{ $tabCounts[$grup] ?? 0 }}</span>
            </a>
        @endforeach
    </div>

    {{-- Filter & Pencarian --}}
    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-5 mb-5">
        <form method="GET" action="{{ route('simpati.petugas.index') }}" class="flex flex-col lg:flex-row lg:items-end gap-3">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <div class="flex-1 min-w-0">
                <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Pencarian</label>
                <div class="relative">
                    <svg class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"></path></svg>
                    <input type="text" name="q" value="{{ $q }}" placeholder="{{ $tab === 'semua' ? 'Cari nama, NIP, email, atau jabatan…' : 'Cari nama atau NIP petugas…' }}"
                           class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-400 outline-none">
                </div>
            </div>

            @if($tab === 'semua')
                <div class="w-full lg:w-44">
                    <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Status Penugasan</label>
                    <select name="status" class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm bg-white focus:ring-2 focus:ring-blue-500 outline-none">
                        <option value="">Semua Status</option>
                        <option value="sudah" {{ $status === 'sudah' ? 'selected' : '' }}>Sudah Penugasan</option>
                        <option value="belum" {{ $status === 'belum' ? 'selected' : '' }}>Belum Penugasan</option>
                    </select>
                </div>
                <div class="w-full lg:w-52">
                    <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Tim Kerja</label>
                    <select name="tim" class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm bg-white focus:ring-2 focus:ring-blue-500 outline-none">
                        <option value="">Semua Tim</option>
                        @foreach($timList as $t)
                            <option value="{{ $t }}" {{ $tim === $t ? 'selected' : '' }}>{{ $t }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="flex gap-2">
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition shadow-sm">Terapkan</button>
                @if($q !== '' || $status !== '' || $tim !== '')
                    <a href="{{ route('simpati.petugas.index', ['tab' => $tab]) }}" class="px-4 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-600 text-xs font-bold transition">Reset</a>
                @endif
            </div>
        </form>

        @if($tab === 'semua')
            <div class="flex flex-wrap gap-2 mt-4 pt-4 border-t border-gray-100">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-gray-50 border border-gray-200 text-[11px] font-bold text-gray-600">
                    Total Pegawai: <span class="text-gray-900">{{ number_format($totalStaff) }}</span>
                </span>
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-50 border border-emerald-200 text-[11px] font-bold text-emerald-700">
                    Sudah Penugasan: <span>{{ number_format($totalStaff - $jumlahBelum) }}</span>
                </span>
                <a href="{{ route('simpati.petugas.index', ['tab' => 'semua', 'status' => 'belum']) }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-amber-50 border border-amber-200 text-[11px] font-bold text-amber-700 hover:bg-amber-100 transition">
                    Belum Mendapat Penugasan: <span>{{ number_format($jumlahBelum) }}</span>
                </a>
            </div>
        @else
            <div class="flex flex-wrap items-center justify-between gap-3 mt-4 pt-4 border-t border-gray-100">
                <p class="text-xs text-gray-500">
                    Menampilkan petugas <strong>{{ \App\Support\SimpatiRoles::grupLabel($tab) }}</strong> —
                    kelompok terpisah di bawah menu Kelola Petugas Layanan.
                </p>
                @if($canManageAny && in_array($tab, $manageable, true))
                    <button @click="openAddPetugas('{{ $tab }}')" type="button"
                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Tambah Petugas
                    </button>
                @endif
            </div>
        @endif
    </div>

    {{-- Tabel --}}
    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            @if($tab === 'semua')
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 text-left text-[11px] uppercase tracking-wider text-gray-400">
                            <th class="px-6 py-3 font-bold">Nama Pegawai</th>
                            <th class="px-6 py-3 font-bold">NIP</th>
                            <th class="px-6 py-3 font-bold">Jabatan</th>
                            <th class="px-6 py-3 font-bold">Tim Kerja</th>
                            <th class="px-6 py-3 font-bold">Grup Petugas</th>
                            <th class="px-6 py-3 font-bold text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($rows as $row)
                            @php
                                $u = $row['user'];
                                $assignedGrups = $row['grups']->pluck('grup')->all();
                            @endphp
                            <tr class="hover:bg-gray-50/70 transition-colors">
                                <td class="px-6 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-xs shrink-0">{{ strtoupper(substr($u->nama_lengkap ?? 'U', 0, 1)) }}</div>
                                        <div class="min-w-0">
                                            <p class="font-semibold text-gray-800 truncate">{{ $u->nama_lengkap }}</p>
                                            <p class="text-[11px] text-gray-400 truncate">{{ $u->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-3.5 text-gray-600 whitespace-nowrap text-xs">{{ $u->nipbaru ?: ($u->niplama ?: '-') }}</td>
                                <td class="px-6 py-3.5 text-gray-600 text-xs max-w-[220px]">{{ $row['jabatan'] }}</td>
                                <td class="px-6 py-3.5">
                                    <div class="flex flex-wrap gap-1">
                                        @forelse($row['tims'] as $t)
                                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200">{{ $t }}</span>
                                        @empty
                                            <span class="text-[11px] text-gray-300">—</span>
                                        @endforelse
                                    </div>
                                </td>
                                <td class="px-6 py-3.5">
                                    @if($row['sudah'])
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($row['grups'] as $pg)
                                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">{{ \App\Support\SimpatiRoles::grupLabel($pg->grup) }}</span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Belum ada penugasan
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-3.5">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <button type="button" @click="openDetailPegawai({{ $u->id }})" title="Detail"
                                                class="px-2.5 py-1.5 rounded-lg text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200 hover:bg-blue-100 transition">Detail</button>
                                        <button type="button" @click="openEditPegawai({{ $u->id }})" title="Edit"
                                                class="px-2.5 py-1.5 rounded-lg text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200 hover:bg-amber-100 transition">Edit</button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-6 py-12 text-center text-sm text-gray-400">Tidak ada pegawai yang cocok dengan pencarian.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            @else
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 text-left text-[11px] uppercase tracking-wider text-gray-400">
                            <th class="px-6 py-3 font-bold">Nama Petugas</th>
                            <th class="px-6 py-3 font-bold">NIP Lama</th>
                            <th class="px-6 py-3 font-bold">Jabatan</th>
                            <th class="px-6 py-3 font-bold">Status</th>
                            <th class="px-6 py-3 font-bold">Catatan</th>
                            <th class="px-6 py-3 font-bold text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($petugasList as $petugas)
                            <tr class="hover:bg-gray-50/70 transition-colors {{ $petugas->is_aktif ? '' : 'opacity-60' }}">
                                <td class="px-6 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl {{ $petugas->user_id ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-500' }} flex items-center justify-center font-bold text-xs shrink-0">{{ strtoupper(substr($petugas->nama ?? 'P', 0, 1)) }}</div>
                                        <div class="min-w-0">
                                            <p class="font-semibold text-gray-800 truncate">{{ $petugas->nama }}</p>
                                            <p class="text-[11px] {{ $petugas->user_id ? 'text-blue-500' : 'text-gray-400' }} truncate">{{ $petugas->user_id ? 'Pegawai BPS' : 'Petugas non-BPS' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-3.5 text-gray-600 text-xs">{{ $petugas->niplama ?: ($petugas->nipbaru ?: '-') }}</td>
                                <td class="px-6 py-3.5 text-gray-600 text-xs max-w-[220px]">{{ $petugas->jabatan ?: '-' }}</td>
                                <td class="px-6 py-3.5">
                                    @if($petugas->is_aktif)
                                        <x-badge variant="success" size="sm">Aktif</x-badge>
                                    @else
                                        <x-badge variant="neutral" size="sm">Nonaktif</x-badge>
                                    @endif
                                </td>
                                <td class="px-6 py-3.5 text-gray-500 text-xs max-w-[200px]">{{ \Illuminate\Support\Str::limit($petugas->catatan ?: '-', 60) }}</td>
                                <td class="px-6 py-3.5">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <button type="button" @click="openDetailPetugas({{ $petugas->id }})" title="Detail"
                                                class="px-2.5 py-1.5 rounded-lg text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200 hover:bg-blue-100 transition">Detail</button>
                                        @if(in_array($tab, $manageable, true))
                                            <button type="button" @click="openManagePetugas({{ $petugas->id }})" title="Edit"
                                                    class="px-2.5 py-1.5 rounded-lg text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200 hover:bg-amber-100 transition">Edit</button>
                                            <form method="POST" action="{{ route('simpati.petugas.destroy', $petugas->id) }}" class="inline"
                                                  onsubmit="return confirm('Hapus {{ addslashes($petugas->nama) }} dari {{ \App\Support\SimpatiRoles::grupLabel($tab) }}? Akun pegawai TIDAK ikut terhapus.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" title="Hapus dari grup"
                                                        class="px-2.5 py-1.5 rounded-lg text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200 hover:bg-rose-100 transition">Hapus</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-6 py-12 text-center">
                                <p class="text-sm font-bold text-gray-500">Belum ada petugas {{ \App\Support\SimpatiRoles::grupLabel($tab) }}</p>
                                <p class="text-xs text-gray-400 mt-1">Klik "Tambah Petugas" untuk menugaskan pegawai ke grup ini.</p>
                            </td></tr>
                        @endforelse
                    </tbody>
                </table>
            @endif
        </div>

        @if($pagination->hasPages())
            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50">
                {{ $pagination->links() }}
            </div>
        @endif
    </div>

    {{-- ===== Modal: Detail Pegawai (tab Semua) ===== --}}
    <div x-show="detailPegawai" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none">
        <div class="absolute inset-0 bg-slate-900/60" @click="detailPegawai = null"></div>
        <div class="relative bg-white rounded-3xl shadow-2xl w-full max-w-xl p-6 sm:p-7" @click.outside="detailPegawai = null" x-transition>
            <template x-if="detailPegawai">
                <div>
                    <div class="flex items-start justify-between gap-4 mb-5">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-12 h-12 rounded-2xl bg-blue-100 text-blue-700 flex items-center justify-center font-black shrink-0"
                                 x-text="(detailPegawai.nama_lengkap || 'U').charAt(0).toUpperCase()"></div>
                            <div class="min-w-0">
                                <h3 class="text-lg font-black text-gray-900 truncate" x-text="detailPegawai.nama_lengkap"></h3>
                                <p class="text-xs text-gray-400" x-text="detailPegawai.email"></p>
                            </div>
                        </div>
                        <button type="button" @click="detailPegawai = null" class="text-gray-300 hover:text-gray-600 text-xl leading-none">&times;</button>
                    </div>

                    <dl class="grid grid-cols-2 gap-x-4 gap-y-3 text-sm mb-5">
                        <div><dt class="text-[11px] font-bold text-gray-400 uppercase">NIP Lama</dt><dd class="font-semibold text-gray-800" x-text="detailPegawai.niplama || '-'"></dd></div>
                        <div><dt class="text-[11px] font-bold text-gray-400 uppercase">NIP Baru</dt><dd class="font-semibold text-gray-800" x-text="detailPegawai.nipbaru || '-'"></dd></div>
                        <div class="col-span-2"><dt class="text-[11px] font-bold text-gray-400 uppercase">Jabatan</dt><dd class="font-semibold text-gray-800" x-text="detailPegawai.jabatan"></dd></div>
                        <div class="col-span-2">
                            <dt class="text-[11px] font-bold text-gray-400 uppercase mb-1">Tim Kerja</dt>
                            <dd class="flex flex-wrap gap-1">
                                <template x-if="!detailPegawai.tims || detailPegawai.tims.length === 0"><span class="text-xs text-gray-300">—</span></template>
                                <template x-for="t in (detailPegawai.tims || [])" :key="t">
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200" x-text="t"></span>
                                </template>
                            </dd>
                        </div>
                        <div class="col-span-2">
                            <dt class="text-[11px] font-bold text-gray-400 uppercase mb-1">Grup Petugas</dt>
                            <dd class="flex flex-wrap gap-1">
                                <template x-if="!detailPegawai.grups || detailPegawai.grups.length === 0">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">Belum ada penugasan</span>
                                </template>
                                <template x-for="g in (detailPegawai.grups || [])" :key="g.grup">
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200" x-text="g.label + (g.is_aktif ? '' : ' (Nonaktif)')"></span>
                                </template>
                            </dd>
                        </div>
                    </dl>

                    @if($canManageAny)
                        <div class="border-t border-gray-100 pt-4">
                            <p class="text-[11px] font-bold text-gray-400 uppercase mb-2">Tambahkan ke Grup Petugas</p>
                            <form method="POST" action="{{ route('simpati.petugas.store') }}" class="flex gap-2" x-show="availableGrupsForDetail().length > 0">
                                @csrf
                                <input type="hidden" name="grup" :value="availableGrupsForDetail()[0] || ''">
                                <input type="hidden" name="user_id" :value="detailPegawai ? detailPegawai.id : ''">
                                <select class="flex-1 px-3 py-2.5 rounded-xl border border-gray-200 text-sm bg-white outline-none focus:ring-2 focus:ring-blue-500"
                                        name="grup" x-model="assignGrup">
                                    <template x-for="g in availableGrupsForDetail()" :key="g">
                                        <option :value="g" x-text="grupLabels[g]"></option>
                                    </template>
                                </select>
                                <button type="submit" class="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition">Tugaskan</button>
                            </form>
                            <p x-show="detailPegawai && availableGrupsForDetail().length === 0" class="text-xs text-gray-400">Sudah bertugas di semua grup yang Anda kelola.</p>
                        </div>
                    @endif
                </div>
            </template>
        </div>
    </div>

    {{-- ===== Modal: Edit Pegawai (endpoint SIMPATI lama) ===== --}}
    <div x-show="editPegawai" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none">
        <div class="absolute inset-0 bg-slate-900/60" @click="editPegawai = null"></div>
        <div class="relative bg-white rounded-3xl shadow-2xl w-full max-w-xl p-6 sm:p-7" @click.outside="editPegawai = null" x-transition>
            <template x-if="editPegawai">
                <form method="POST" :action="'{{ url('admin/simpati/pegawai') }}/' + editPegawai.id + '/update'">
                    @csrf
                    <div class="flex items-start justify-between gap-4 mb-5">
                        <div>
                            <h3 class="text-lg font-black text-gray-900">Edit Data Pegawai</h3>
                            <p class="text-xs text-gray-400">Menggunakan endpoint edit SIMPATI yang sudah ada.</p>
                        </div>
                        <button type="button" @click="editPegawai = null" class="text-gray-300 hover:text-gray-600 text-xl leading-none">&times;</button>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-[11px] font-bold text-gray-400 uppercase mb-1.5">Nama Lengkap</label>
                            <input type="text" name="nama_lengkap" required x-model="editPegawai.nama_lengkap"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[11px] font-bold text-gray-400 uppercase mb-1.5">Email</label>
                                <input type="email" name="email" x-model="editPegawai.email"
                                       class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-gray-400 uppercase mb-1.5">Tim Kerja Utama</label>
                                <select name="tim_utama" class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm bg-white outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">— Tidak diubah —</option>
                                    @foreach($timList as $t)
                                        <option value="{{ $t }}">{{ $t }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-gray-400 uppercase mb-1.5">Jabatan</label>
                            <input type="text" name="nm_jabatan" x-model="editPegawai.jabatan"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 mt-6">
                        <button type="button" @click="editPegawai = null" class="px-4 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-600 text-xs font-bold transition">Batal</button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition">Simpan Perubahan</button>
                    </div>
                </form>
            </template>
        </div>
    </div>

    {{-- ===== Modal: Tambah Petugas (tab grup) ===== --}}
    <div x-show="addPetugas" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none">
        <div class="absolute inset-0 bg-slate-900/60" @click="addPetugas = false"></div>
        <div class="relative bg-white rounded-3xl shadow-2xl w-full max-w-xl p-6 sm:p-7" @click.outside="addPetugas = false" x-transition>
            <div class="flex items-start justify-between gap-4 mb-5">
                <div>
                    <h3 class="text-lg font-black text-gray-900">Tambah Petugas</h3>
                    <p class="text-xs text-gray-400">Grup: <span class="font-bold text-blue-600" x-text="grupLabels[addGrup]"></span></p>
                </div>
                <button type="button" @click="addPetugas = false" class="text-gray-300 hover:text-gray-600 text-xl leading-none">&times;</button>
            </div>

            <div class="flex gap-2 mb-4">
                <button type="button" @click="addMode = 'pegawai'" :class="addMode === 'pegawai' ? 'bg-slate-900 text-white' : 'bg-gray-100 text-gray-500'" class="px-3.5 py-2 rounded-xl text-xs font-bold transition">Pilih Pegawai BPS</button>
                <button type="button" @click="addMode = 'manual'" :class="addMode === 'manual' ? 'bg-slate-900 text-white' : 'bg-gray-100 text-gray-500'" class="px-3.5 py-2 rounded-xl text-xs font-bold transition">Petugas Non-BPS</button>
            </div>

            <form method="POST" action="{{ route('simpati.petugas.store') }}">
                @csrf
                <input type="hidden" name="grup" :value="addGrup">

                <div x-show="addMode === 'pegawai'" class="space-y-3">
                    <div>
                        <label class="block text-[11px] font-bold text-gray-400 uppercase mb-1.5">Cari Pegawai</label>
                        <input type="text" x-model="addSearch" @input.debounce.300ms="searchPegawai()" placeholder="Ketik nama atau NIP…"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm outline-none focus:ring-2 focus:ring-blue-500">
                        <input type="hidden" name="user_id" :value="addUserId">
                        <div class="mt-2 max-h-52 overflow-y-auto rounded-xl border border-gray-100 divide-y divide-gray-50" x-show="addResults.length > 0">
                            <template x-for="p in addResults" :key="p.id">
                                <button type="button" @click="pickPegawai(p)"
                                        class="w-full text-left px-3.5 py-2.5 text-sm flex items-center justify-between gap-2 transition"
                                        :class="addUserId === p.id ? 'bg-blue-50' : 'hover:bg-gray-50'">
                                    <span class="min-w-0">
                                        <span class="block font-semibold text-gray-800 truncate" x-text="p.nama_lengkap"></span>
                                        <span class="block text-[11px] text-gray-400 truncate" x-text="p.niplama + ' · ' + p.nm_jabatan"></span>
                                    </span>
                                    <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-gray-100 text-gray-500 shrink-0" x-show="p.grups.length" x-text="p.grups.length + ' grup'"></span>
                                </button>
                            </template>
                        </div>
                        <p x-show="addSearch.length > 0 && addResults.length === 0" class="text-xs text-gray-400 mt-2">Pegawai tidak ditemukan.</p>
                    </div>
                    <p class="text-xs text-gray-500 bg-gray-50 rounded-xl px-3 py-2.5" x-show="addUserId">
                        Dipilih: <span class="font-bold text-gray-800" x-text="addPickedName"></span>
                    </p>
                </div>

                <div x-show="addMode === 'manual'" class="space-y-3" style="display:none">
                    <div>
                        <label class="block text-[11px] font-bold text-gray-400 uppercase mb-1.5">Nama Petugas <span class="text-rose-500">*</span></label>
                        <input type="text" name="nama" placeholder="Nama lengkap petugas (mis. petugas keamanan dari pihak ketiga)"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-gray-400 uppercase mb-1.5">NIP Lama (opsional)</label>
                        <input type="text" name="niplama" maxlength="15"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div class="mt-4">
                    <label class="block text-[11px] font-bold text-gray-400 uppercase mb-1.5">Catatan (opsional)</label>
                    <input type="text" name="catatan" maxlength="500"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="flex justify-end gap-2 mt-6">
                    <button type="button" @click="addPetugas = false" class="px-4 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-600 text-xs font-bold transition">Batal</button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition">Simpan Petugas</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ===== Modal: Detail Petugas (tab grup) ===== --}}
    <div x-show="detailPetugas" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none">
        <div class="absolute inset-0 bg-slate-900/60" @click="detailPetugas = null"></div>
        <div class="relative bg-white rounded-3xl shadow-2xl w-full max-w-lg p-6 sm:p-7" @click.outside="detailPetugas = null" x-transition>
            <template x-if="detailPetugas">
                <div>
                    <div class="flex items-start justify-between gap-4 mb-5">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-12 h-12 rounded-2xl bg-blue-100 text-blue-700 flex items-center justify-center font-black shrink-0" x-text="(detailPetugas.nama || 'P').charAt(0).toUpperCase()"></div>
                            <div class="min-w-0">
                                <h3 class="text-lg font-black text-gray-900 truncate" x-text="detailPetugas.nama"></h3>
                                <p class="text-xs" :class="detailPetugas.user_id ? 'text-blue-500' : 'text-gray-400'" x-text="detailPetugas.user_id ? 'Pegawai BPS' : 'Petugas non-BPS'"></p>
                            </div>
                        </div>
                        <button type="button" @click="detailPetugas = null" class="text-gray-300 hover:text-gray-600 text-xl leading-none">&times;</button>
                    </div>
                    <dl class="grid grid-cols-2 gap-x-4 gap-y-3 text-sm">
                        <div><dt class="text-[11px] font-bold text-gray-400 uppercase">Grup</dt><dd class="font-semibold text-gray-800" x-text="grupLabels[detailPetugas.grup]"></dd></div>
                        <div><dt class="text-[11px] font-bold text-gray-400 uppercase">Status</dt><dd class="font-semibold" x-text="detailPetugas.is_aktif ? 'Aktif' : 'Nonaktif'"></dd></div>
                        <div><dt class="text-[11px] font-bold text-gray-400 uppercase">NIP Lama</dt><dd class="font-semibold text-gray-800" x-text="detailPetugas.niplama || '-'"></dd></div>
                        <div><dt class="text-[11px] font-bold text-gray-400 uppercase">NIP Baru</dt><dd class="font-semibold text-gray-800" x-text="detailPetugas.nipbaru || '-'"></dd></div>
                        <div class="col-span-2"><dt class="text-[11px] font-bold text-gray-400 uppercase">Jabatan</dt><dd class="font-semibold text-gray-800" x-text="detailPetugas.jabatan || '-'"></dd></div>
                        <div class="col-span-2"><dt class="text-[11px] font-bold text-gray-400 uppercase">Email</dt><dd class="font-semibold text-gray-800" x-text="(detailPetugas.user && detailPetugas.user.email) || '-'"></dd></div>
                        <div class="col-span-2"><dt class="text-[11px] font-bold text-gray-400 uppercase">Catatan</dt><dd class="text-gray-700" x-text="detailPetugas.catatan || '-'"></dd></div>
                    </dl>
                </div>
            </template>
        </div>
    </div>

    {{-- ===== Modal: Edit Petugas (tab grup) ===== --}}
    <div x-show="managePetugas" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none">
        <div class="absolute inset-0 bg-slate-900/60" @click="managePetugas = null"></div>
        <div class="relative bg-white rounded-3xl shadow-2xl w-full max-w-lg p-6 sm:p-7" @click.outside="managePetugas = null" x-transition>
            <template x-if="managePetugas">
                <form method="POST" :action="'{{ url('admin/simpati/petugas') }}/' + managePetugas.id + '/update'">
                    @csrf
                    <div class="flex items-start justify-between gap-4 mb-5">
                        <div class="min-w-0">
                            <h3 class="text-lg font-black text-gray-900 truncate" x-text="managePetugas.nama"></h3>
                            <p class="text-xs text-gray-400">Edit data petugas <span x-text="grupLabels[managePetugas.grup]"></span></p>
                        </div>
                        <button type="button" @click="managePetugas = null" class="text-gray-300 hover:text-gray-600 text-xl leading-none">&times;</button>
                    </div>

                    <div class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[11px] font-bold text-gray-400 uppercase mb-1.5">Status Keanggotaan</label>
                                <select name="is_aktif" class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm bg-white outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="1" :selected="managePetugas.is_aktif ? 'selected' : null">Aktif</option>
                                    <option value="0" :selected="!managePetugas.is_aktif ? 'selected' : null">Nonaktif</option>
                                </select>
                            </div>
                            <div x-show="moveableGrups().length > 0">
                                <label class="block text-[11px] font-bold text-gray-400 uppercase mb-1.5">Pindah Grup</label>
                                <select name="grup" class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm bg-white outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">— Tetap —</option>
                                    <template x-for="g in moveableGrups()" :key="g">
                                        <option :value="g" x-text="grupLabels[g]"></option>
                                    </template>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-gray-400 uppercase mb-1.5">Catatan</label>
                            <textarea name="catatan" rows="3" x-model="managePetugas.catatan"
                                      class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 mt-6">
                        <button type="button" @click="managePetugas = null" class="px-4 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-600 text-xs font-bold transition">Batal</button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition">Simpan</button>
                    </div>
                </form>
            </template>
        </div>
    </div>

    {{-- Data untuk Alpine --}}
    @php
        $rowsJson = collect($rows)->values();
        $petugasJson = $tab === 'semua'
            ? collect()
            : collect($petugasList->items());
        $detailJson = $rowsJson->map(fn ($r) => [
            'id' => $r['user']->id,
            'nama_lengkap' => $r['user']->nama_lengkap,
            'niplama' => $r['user']->niplama,
            'nipbaru' => $r['user']->nipbaru,
            'email' => $r['user']->email,
            'jabatan' => $r['jabatan'],
            'tims' => $r['tims'],
            'grups' => $r['grups']->map(fn ($g) => ['grup' => $g->grup, 'label' => \App\Support\SimpatiRoles::grupLabel($g->grup), 'is_aktif' => (bool) $g->is_aktif])->values(),
        ])->values();
    @endphp
    <script>
        window.SIMP_ROWS = @json($rowsJson);
        window.SIMP_PETUGAS = @json($petugasJson);
        window.SIMP_MANAGEABLE = @json($manageable);
        window.SIMP_GRUP_LABELS = @json($grups);
        window.SIMP_DETAIL_ROWS = @json($detailJson);
    </script>
</div>
@endsection

@push('scripts')
<script>
    function petugasUi() {
        return {
            detailPegawai: null,
            editPegawai: null,
            detailPetugas: null,
            managePetugas: null,
            addPetugas: false,
            addGrup: 'pst',
            addMode: 'pegawai',
            addSearch: '',
            addResults: [],
            addUserId: null,
            addPickedName: '',
            assignGrup: '',
            grupLabels: window.SIMP_GRUP_LABELS,

            availableGrupsForDetail() {
                if (!this.detailPegawai) return [];
                const assigned = (this.detailPegawai.grups || []).map(g => g.grup);
                return window.SIMP_MANAGEABLE.filter(g => !assigned.includes(g));
            },

            openDetailPegawai(id) {
                const row = window.SIMP_DETAIL_ROWS.find(r => r.id === id);
                if (!row) return;
                this.detailPegawai = row;
                this.assignGrup = '';
                this.$nextTick(() => { this.assignGrup = this.availableGrupsForDetail()[0] || ''; });
            },

            openEditPegawai(id) {
                const row = window.SIMP_DETAIL_ROWS.find(r => r.id === id);
                if (!row) return;
                this.editPegawai = { id: row.id, nama_lengkap: row.nama_lengkap, email: row.email, jabatan: row.jabatan };
            },

            openDetailPetugas(id) {
                const row = window.SIMP_PETUGAS.find(r => r.id === id);
                if (row) this.detailPetugas = row;
            },

            openManagePetugas(id) {
                const row = window.SIMP_PETUGAS.find(r => r.id === id);
                if (row) this.managePetugas = { id: row.id, nama: row.nama, grup: row.grup, is_aktif: row.is_aktif, catatan: row.catatan || '' };
            },

            moveableGrups() {
                if (!this.managePetugas) return [];
                return window.SIMP_MANAGEABLE.filter(g => g !== this.managePetugas.grup);
            },

            openAddPetugas(grup) {
                this.addGrup = grup;
                this.addMode = 'pegawai';
                this.addSearch = '';
                this.addResults = [];
                this.addUserId = null;
                this.addPickedName = '';
                this.addPetugas = true;
                this.searchPegawai();
            },

            searchPegawai() {
                const q = encodeURIComponent(this.addSearch || '');
                fetch('{{ route('simpati.petugas.cari') }}?q=' + q)
                    .then(r => r.json())
                    .then(res => { this.addResults = res.data || []; })
                    .catch(() => { this.addResults = []; });
            },

            pickPegawai(p) {
                this.addUserId = p.id;
                this.addPickedName = p.nama_lengkap + ' (' + p.niplama + ')';
            },
        };
    }
</script>
@endpush
