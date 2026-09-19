@extends('layouts.app')

@section('title', 'Integrasi SIMPATI API - Sikeren')
@section('header_title', 'Integrasi SIMPATI API')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6" x-data="simpatiManager()">
    {{-- Banner Header --}}
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-blue-700 via-indigo-700 to-slate-900 p-6 sm:p-8 text-white shadow-xl shadow-blue-500/10">
        <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div class="space-y-2">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/15 backdrop-blur-md text-xs font-semibold text-blue-100 border border-white/20">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                    <span>REST API Service Active</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white">
                    Integrasi Data SIMPATI &harr; SIKEREN
                </h1>
                <p class="text-sm text-blue-100/90 max-w-2xl leading-relaxed">
                    Sinkronisasi otomatis dan satu arah untuk data Pegawai BPS, Struktur Jabatan, dan Tim Kerja dari sistem SIMPATI ke dalam database SIKEREN.
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <button @click="testConnection()" 
                        :disabled="isTesting || isSyncing"
                        class="px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 border border-white/20 text-white font-semibold text-sm transition flex items-center gap-2 shadow-sm disabled:opacity-50">
                    <svg x-show="!isTesting" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    <svg x-show="isTesting" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                    <span x-text="isTesting ? 'Menguji...' : 'Uji Koneksi'"></span>
                </button>
                <button @click="syncData()" 
                        :disabled="isTesting || isSyncing"
                        class="px-5 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-white font-bold text-sm transition flex items-center gap-2 shadow-lg shadow-emerald-600/30 disabled:opacity-50">
                    <svg x-show="!isSyncing" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <svg x-show="isSyncing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                    <span x-text="isSyncing ? 'Menyinkronkan...' : 'Sinkronkan Sekarang'"></span>
                </button>
            </div>
        </div>
    </div>

    {{-- Notification Alert Box --}}
    <div x-show="alert.show" 
         x-transition 
         :class="alert.type === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-900' : 'bg-rose-50 border-rose-200 text-rose-900'"
         class="p-4 rounded-2xl border flex items-start gap-3 shadow-xs">
        <div class="shrink-0 mt-0.5">
            <template x-if="alert.type === 'success'">
                <svg class="w-5 h-5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
            </template>
            <template x-if="alert.type !== 'success'">
                <svg class="w-5 h-5 text-rose-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
            </template>
        </div>
        <div class="flex-1 text-sm font-medium" x-text="alert.message"></div>
        <button @click="alert.show = false" class="text-gray-400 hover:text-gray-600">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>

    {{-- Sync Results Table (if any) --}}
    <div x-show="syncResult" x-transition class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                Hasil Sinkronisasi Terakhir
            </h3>
            <span class="text-xs text-gray-400 font-mono" x-text="lastSyncTime"></span>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
            <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-100 text-center">
                <span class="text-xs text-gray-500 font-semibold block">Total Pegawai</span>
                <span class="text-2xl font-black text-slate-800" x-text="syncResult?.pegawai_total || 0"></span>
            </div>
            <div class="p-3.5 rounded-2xl bg-emerald-50 border border-emerald-100 text-center">
                <span class="text-xs text-emerald-700 font-semibold block">Pegawai Baru</span>
                <span class="text-2xl font-black text-emerald-600" x-text="syncResult?.pegawai_created || 0"></span>
            </div>
            <div class="p-3.5 rounded-2xl bg-blue-50 border border-blue-100 text-center">
                <span class="text-xs text-blue-700 font-semibold block">Pegawai Diupdate</span>
                <span class="text-2xl font-black text-blue-600" x-text="syncResult?.pegawai_updated || 0"></span>
            </div>
            <div class="p-3.5 rounded-2xl bg-indigo-50 border border-indigo-100 text-center">
                <span class="text-xs text-indigo-700 font-semibold block">Total Tim Kerja</span>
                <span class="text-2xl font-black text-indigo-600" x-text="syncResult?.tim_total || 0"></span>
            </div>
            <div class="p-3.5 rounded-2xl bg-purple-50 border border-purple-100 text-center">
                <span class="text-xs text-purple-700 font-semibold block">Tim Baru</span>
                <span class="text-2xl font-black text-purple-600" x-text="syncResult?.tim_created || 0"></span>
            </div>
            <div class="p-3.5 rounded-2xl bg-amber-50 border border-amber-100 text-center">
                <span class="text-xs text-amber-700 font-semibold block">Anggota Disinkronkan</span>
                <span class="text-2xl font-black text-amber-600" x-text="syncResult?.anggota_synced || 0"></span>
            </div>
        </div>
    </div>

    {{-- Grid 2 Kolom: Konfigurasi API & Endpoints --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        {{-- Kolom Kiri: Konfigurasi & Status --}}
        <div class="lg:col-span-5 space-y-6">
            {{-- Kartu Kredensial API --}}
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 space-y-5">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-gray-900">Kredensial SIMPATI API</h3>
                        <p class="text-xs text-gray-400">Parameter otentikasi header request</p>
                    </div>
                </div>

                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Base URL</label>
                        <div class="flex items-center gap-2">
                            <input type="text" readonly value="{{ $baseUrl }}" class="flex-1 bg-gray-50 border border-gray-200 text-gray-800 text-xs font-mono rounded-xl px-3 py-2">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Header Auth Name</label>
                        <input type="text" readonly value="x-api-key" class="w-full bg-gray-50 border border-gray-200 text-gray-800 text-xs font-mono rounded-xl px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">API Key</label>
                        <div class="flex items-center gap-2">
                            <input type="password" id="apiKeyInput" readonly value="{{ $apiKey }}" class="flex-1 bg-gray-50 border border-gray-200 text-gray-800 text-xs font-mono rounded-xl px-3 py-2">
                            <button @click="toggleApiKeyVisibility()" type="button" class="px-3 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-600 text-xs font-medium">
                                <span x-text="showKey ? 'Sembunyikan' : 'Lihat'"></span>
                            </button>
                            <button @click="copyApiKey()" type="button" class="px-3 py-2 rounded-xl bg-blue-50 hover:bg-blue-100 text-blue-600 text-xs font-bold">
                                Salin
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Kartu CLI Command --}}
            <div class="bg-slate-900 rounded-3xl p-6 text-white shadow-sm space-y-4">
                <div class="flex items-center justify-between">
                    <h4 class="text-sm font-bold text-white flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                        Artisan CLI Command
                    </h4>
                    <span class="text-xs text-slate-400">Terminal</span>
                </div>
                <p class="text-xs text-slate-300 leading-relaxed">
                    Anda juga dapat menjalankan sinkronisasi via cron scheduler atau CLI terminal:
                </p>
                <div class="bg-slate-950 rounded-xl p-3 font-mono text-xs text-emerald-400 border border-slate-800 space-y-1">
                    <p># Uji koneksi</p>
                    <p class="text-white">php artisan simpati:sync --test</p>
                    <p class="pt-2 text-emerald-400"># Jalankan sinkronisasi penuh</p>
                    <p class="text-white">php artisan simpati:sync</p>
                </div>
            </div>
        </div>

        {{-- Kolom Kanan: Dokumentasi Endpoint & Detail --}}
        <div class="lg:col-span-7 space-y-6">
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 space-y-5">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-gray-900">Daftar Endpoint SIMPATI Public API</h3>
                            <p class="text-xs text-gray-400">Endpoint terverifikasi yang digunakan oleh Sikeren</p>
                        </div>
                    </div>
                </div>

                {{-- Endpoint 1 --}}
                <div class="rounded-2xl border border-gray-100 p-4 space-y-2 bg-gray-50/50">
                    <div class="flex items-center gap-2.5">
                        <span class="px-2.5 py-1 rounded-lg bg-blue-100 text-blue-700 text-xs font-bold font-mono">GET</span>
                        <code class="text-xs font-bold text-gray-800">/api/public/pegawai</code>
                    </div>
                    <p class="text-xs text-gray-600 leading-relaxed">
                        Mengambil seluruh data pegawai BPS aktif. Parameter opsional: <code class="text-indigo-600 font-bold">id_satker</code>.
                    </p>
                </div>

                {{-- Endpoint 2 --}}
                <div class="rounded-2xl border border-gray-100 p-4 space-y-2 bg-gray-50/50">
                    <div class="flex items-center gap-2.5">
                        <span class="px-2.5 py-1 rounded-lg bg-blue-100 text-blue-700 text-xs font-bold font-mono">GET</span>
                        <code class="text-xs font-bold text-gray-800">/api/public/pegawai/{niplama}</code>
                    </div>
                    <p class="text-xs text-gray-600 leading-relaxed">
                        Mengambil detail 1 orang pegawai berdasarkan NIP Lama beserta riwayat daftar tim kerjanya (<code class="text-indigo-600 font-bold">tims</code>).
                    </p>
                </div>

                {{-- Endpoint 3 --}}
                <div class="rounded-2xl border border-gray-100 p-4 space-y-2 bg-gray-50/50">
                    <div class="flex items-center gap-2.5">
                        <span class="px-2.5 py-1 rounded-lg bg-blue-100 text-blue-700 text-xs font-bold font-mono">GET</span>
                        <code class="text-xs font-bold text-gray-800">/api/public/tim-kerja</code>
                    </div>
                    <p class="text-xs text-gray-600 leading-relaxed">
                        Mengambil seluruh daftar tim kerja beserta anggota dan jabatannya dalam tim (<code class="text-indigo-600 font-bold">anggota</code>). Parameter opsional: <code class="text-indigo-600 font-bold">id_satker</code>, <code class="text-indigo-600 font-bold">nm_tim</code>.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function simpatiManager() {
    return {
        isTesting: false,
        isSyncing: false,
        showKey: false,
        lastSyncTime: '',
        syncResult: null,
        alert: {
            show: false,
            type: 'success',
            message: ''
        },

        toggleApiKeyVisibility() {
            this.showKey = !this.showKey;
            const el = document.getElementById('apiKeyInput');
            el.type = this.showKey ? 'text' : 'password';
        },

        copyApiKey() {
            const el = document.getElementById('apiKeyInput');
            navigator.clipboard.writeText(el.value).then(() => {
                this.showAlert('success', 'API Key berhasil disalin ke clipboard!');
            });
        },

        showAlert(type, message) {
            this.alert.type = type;
            this.alert.message = message;
            this.alert.show = true;
            setTimeout(() => {
                if (this.alert.type === 'success') {
                    this.alert.show = false;
                }
            }, 6000);
        },

        async testConnection() {
            this.isTesting = true;
            try {
                const res = await fetch("{{ route('simpati.test') }}", {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });
                const data = await res.json();
                if (data.success) {
                    this.showAlert('success', '✓ ' + data.message + ' (Ditemukan ' + data.data_count + ' pegawai aktif)');
                } else {
                    this.showAlert('error', '✗ ' + data.message);
                }
            } catch (err) {
                this.showAlert('error', 'Gagal menghubungi server: ' + err.message);
            } finally {
                this.isTesting = false;
            }
        },

        async syncData() {
            if (!confirm('Apakah Anda yakin ingin menyinkronkan data Pegawai & Tim Kerja dari SIMPATI sekarang?')) {
                return;
            }
            this.isSyncing = true;
            try {
                const res = await fetch("{{ route('simpati.sync') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });
                const data = await res.json();
                if (data.success) {
                    this.syncResult = data;
                    this.lastSyncTime = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
                    this.showAlert('success', '✓ Sinkronisasi berhasil! ' + data.pegawai_total + ' pegawai dan ' + data.tim_total + ' tim kerja diproses.');
                } else {
                    this.showAlert('error', '✗ Gagal sinkronisasi data SIMPATI.');
                }
            } catch (err) {
                this.showAlert('error', 'Gagal memproses sinkronisasi: ' + err.message);
            } finally {
                this.isSyncing = false;
            }
        }
    };
}
</script>
@endpush
@endsection
