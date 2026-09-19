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
                    <span class="w-2 h-2 rounded-full" :class="connectionStatus === 'online' ? 'bg-emerald-400 animate-ping' : 'bg-amber-400'"></span>
                    <span x-text="connectionStatus === 'online' ? 'SIMPATI API Terhubung (Online)' : 'SIMPATI API Siap Dikonfigurasi'"></span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white">
                    Integrasi Data SIMPATI &harr; SIKEREN
                </h1>
                <p class="text-sm text-blue-100/90 max-w-2xl leading-relaxed">
                    Sinkronisasi data Pegawai BPS, Struktur Jabatan, dan Tim Kerja dari sistem SIMPATI ke dalam database SIKEREN secara otomatis.
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <button @click="testConnection()" 
                        :disabled="isTesting || isSyncing || isSaving"
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
                        :disabled="isTesting || isSyncing || isSaving"
                        class="px-5 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-white font-bold text-sm transition flex items-center gap-2 shadow-lg shadow-emerald-600/30 disabled:opacity-50">
                    <svg x-show="!isSyncing" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <svg x-show="isSyncing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                    <span x-text="isSyncing ? 'Menyinkronkan...' : 'Sinkronkan Live'"></span>
                </button>
            </div>
        </div>
    </div>

    {{-- Notification Alert Box --}}
    <div x-show="alert.show" 
         x-transition 
         :class="alert.type === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-900' : (alert.type === 'warning' ? 'bg-amber-50 border-amber-200 text-amber-900' : 'bg-rose-50 border-rose-200 text-rose-900')"
         class="p-4 rounded-2xl border flex items-start gap-3 shadow-xs">
        <div class="shrink-0 mt-0.5">
            <template x-if="alert.type === 'success'">
                <svg class="w-5 h-5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
            </template>
            <template x-if="alert.type === 'error'">
                <svg class="w-5 h-5 text-rose-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
            </template>
            <template x-if="alert.type === 'warning'">
                <svg class="w-5 h-5 text-amber-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
            </template>
        </div>
        <div class="flex-1 space-y-1">
            <p class="text-sm font-bold" x-text="alert.title || (alert.type === 'success' ? 'Berhasil' : 'Informasi Koneksi')"></p>
            <p class="text-xs font-medium leading-relaxed" x-text="alert.message"></p>
            
            {{-- Panduan Troubleshooting jika server offline --}}
            <template x-if="alert.type === 'error' && alert.showTroubleshoot">
                <div class="mt-3 p-3 bg-white/80 rounded-xl border border-rose-200 text-xs text-slate-700 space-y-1.5">
                    <p class="font-bold text-rose-800">💡 Langkah Mengatasi:</p>
                    <ul class="list-disc list-inside space-y-1 text-slate-600">
                        <li>Jika aplikasi SIMPATI dijalankan di komputer ini, pastikan terminalnya sudah menjalankan <code class="bg-gray-100 px-1.5 py-0.5 rounded font-mono text-indigo-700">npm run dev</code> atau <code class="bg-gray-100 px-1.5 py-0.5 rounded font-mono text-indigo-700">npm start</code> (biasanya di port 3000).</li>
                        <li>Jika SIMPATI berjalan di port lain (misal: 3001, 8080) atau di server/IP lain, ubah nilai <strong>Base URL</strong> pada form di bawah lalu klik <strong>Simpan Pengaturan</strong>.</li>
                        <li>Anda juga dapat mencoba tombol <strong>"Sinkronkan Simulasi (Demo Data BPS)"</strong> di bawah untuk menguji struktur data.</li>
                    </ul>
                    <div class="pt-2">
                        <button @click="syncMockData()" type="button" class="px-3 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs transition">
                            🧪 Coba Sinkronkan Data Simulasi BPS
                        </button>
                    </div>
                </div>
            </template>
        </div>
        <button @click="alert.show = false" class="text-gray-400 hover:text-gray-600 shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>

    {{-- Sync Results Table --}}
    <div x-show="syncResult" x-transition class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                Hasil Sinkronisasi Terakhir
                <span x-show="syncResult?.is_mock" class="px-2 py-0.5 rounded-md bg-purple-100 text-purple-700 text-xs font-bold">Simulasi / Mock Data</span>
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
            {{-- Kartu Kredensial API (Editable) --}}
            <form @submit.prevent="saveConfig()" class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 space-y-5">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-gray-900">Pengaturan SIMPATI API</h3>
                            <p class="text-xs text-gray-400">Konfigurasi host & token otentikasi</p>
                        </div>
                    </div>
                </div>

                <div class="space-y-3.5">
                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1">
                            Base URL SIMPATI
                        </label>
                        <input type="text" x-model="form.base_url" required placeholder="http://localhost:3000" class="w-full bg-gray-50 focus:bg-white border border-gray-200 focus:border-blue-500 text-gray-800 text-xs font-mono rounded-xl px-3.5 py-2.5 transition">
                        <p class="text-[11px] text-gray-400 mt-1">Gunakan alamat lokal host atau domain SIMPATI server.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1">
                            Header Auth Key
                        </label>
                        <input type="text" readonly value="x-api-key" class="w-full bg-gray-100 border border-gray-200 text-gray-600 text-xs font-mono rounded-xl px-3.5 py-2.5 cursor-not-allowed">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1">
                            API Key (Token)
                        </label>
                        <div class="flex items-center gap-2">
                            <input :type="showKey ? 'text' : 'password'" x-model="form.api_key" required class="flex-1 bg-gray-50 focus:bg-white border border-gray-200 focus:border-blue-500 text-gray-800 text-xs font-mono rounded-xl px-3.5 py-2.5 transition">
                            <button @click="showKey = !showKey" type="button" class="px-3 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-600 text-xs font-medium transition">
                                <span x-text="showKey ? 'Sembunyikan' : 'Lihat'"></span>
                            </button>
                            <button @click="copyApiKey()" type="button" class="px-3 py-2.5 rounded-xl bg-blue-50 hover:bg-blue-100 text-blue-600 text-xs font-bold transition">
                                Salin
                            </button>
                        </div>
                    </div>

                    <div class="pt-2 flex items-center justify-between gap-3 border-t border-gray-100">
                        <button type="submit" 
                                :disabled="isSaving"
                                class="w-full px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold transition flex items-center justify-center gap-2 shadow-sm disabled:opacity-50">
                            <svg x-show="isSaving" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            <span x-text="isSaving ? 'Menyimpan...' : 'Simpan Pengaturan (.env)'"></span>
                        </button>
                    </div>
                </div>
            </form>

            {{-- Kartu Demo / Simulasi & CLI --}}
            <div class="bg-slate-900 rounded-3xl p-6 text-white shadow-sm space-y-4">
                <div class="flex items-center justify-between">
                    <h4 class="text-sm font-bold text-white flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                        Opsi Pengujian & CLI
                    </h4>
                    <span class="text-xs text-slate-400">Pengembang</span>
                </div>
                
                <div class="p-3 bg-slate-800/80 rounded-2xl border border-slate-700 space-y-2">
                    <p class="text-xs text-slate-300 font-medium">Uji Coba Data BPS Tanpa Menjalankan Server SIMPATI:</p>
                    <button @click="syncMockData()" 
                            :disabled="isSyncing"
                            class="w-full px-3 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold transition flex items-center justify-center gap-2 shadow-xs">
                        <span>🧪 Sinkronkan Data Simulasi BPS</span>
                    </button>
                </div>

                <div class="bg-slate-950 rounded-xl p-3 font-mono text-xs text-emerald-400 border border-slate-800 space-y-1">
                    <p class="text-slate-400"># Jalankan via terminal:</p>
                    <p class="text-white">php artisan simpati:sync --test</p>
                    <p class="text-white">php artisan simpati:sync</p>
                </div>
            </div>
        </div>

        {{-- Kolom Kanan: Dokumentasi Endpoint & Postman Setup --}}
        <div class="lg:col-span-7 space-y-6">
            {{-- Kartu Dokumentasi Endpoint --}}
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 space-y-5">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-gray-900">Spesifikasi Endpoint SIMPATI API</h3>
                            <p class="text-xs text-gray-400">Endpoint terverifikasi yang digunakan oleh Sikeren</p>
                        </div>
                    </div>
                </div>

                {{-- Endpoint 1 --}}
                <div class="rounded-2xl border border-gray-100 p-4 space-y-2 bg-gray-50/60">
                    <div class="flex items-center gap-2.5">
                        <span class="px-2.5 py-1 rounded-lg bg-blue-100 text-blue-700 text-xs font-bold font-mono">GET</span>
                        <code class="text-xs font-bold text-gray-800">/api/public/pegawai</code>
                    </div>
                    <p class="text-xs text-gray-600 leading-relaxed">
                        Mengambil seluruh daftar data pegawai BPS aktif. Parameter opsional: <code class="text-indigo-600 font-bold">id_satker</code>.
                    </p>
                </div>

                {{-- Endpoint 2 --}}
                <div class="rounded-2xl border border-gray-100 p-4 space-y-2 bg-gray-50/60">
                    <div class="flex items-center gap-2.5">
                        <span class="px-2.5 py-1 rounded-lg bg-blue-100 text-blue-700 text-xs font-bold font-mono">GET</span>
                        <code class="text-xs font-bold text-gray-800">/api/public/pegawai/{niplama}</code>
                    </div>
                    <p class="text-xs text-gray-600 leading-relaxed">
                        Mengambil detail 1 orang pegawai berdasarkan NIP Lama beserta daftar tim kerja yang diikuti (<code class="text-indigo-600 font-bold">tims</code>).
                    </p>
                </div>

                {{-- Endpoint 3 --}}
                <div class="rounded-2xl border border-gray-100 p-4 space-y-2 bg-gray-50/60">
                    <div class="flex items-center gap-2.5">
                        <span class="px-2.5 py-1 rounded-lg bg-blue-100 text-blue-700 text-xs font-bold font-mono">GET</span>
                        <code class="text-xs font-bold text-gray-800">/api/public/tim-kerja</code>
                    </div>
                    <p class="text-xs text-gray-600 leading-relaxed">
                        Mengambil seluruh daftar tim kerja beserta anggota dan jabatannya dalam tim (<code class="text-indigo-600 font-bold">anggota</code>). Parameter opsional: <code class="text-indigo-600 font-bold">id_satker</code>, <code class="text-indigo-600 font-bold">nm_tim</code>.
                    </p>
                </div>
            </div>

            {{-- Kartu Postman Ready-to-Use --}}
            <div class="bg-gradient-to-br from-amber-500/10 via-orange-500/5 to-transparent rounded-3xl border border-orange-200/70 p-6 space-y-4">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-orange-500 text-white flex items-center justify-center font-black text-xl shadow-md shadow-orange-500/20 shrink-0">
                        P
                    </div>
                    <div class="space-y-1">
                        <h4 class="text-base font-bold text-gray-900">Koleksi Postman Siap Pakai</h4>
                        <p class="text-xs text-gray-600 leading-relaxed">
                            File koleksi Postman <code class="text-orange-700 font-bold">Sikeren_SIMPATI_API.postman_collection.json</code> telah dibuat otomatis di root project. Anda cukup menekan tombol <strong>Import</strong> di Postman dan memilih file tersebut untuk mendapatkan seluruh endpoint lengkap dengan API Key.
                        </p>
                    </div>
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
        isSaving: false,
        showKey: false,
        connectionStatus: 'unknown',
        lastSyncTime: '',
        syncResult: null,
        form: {
            base_url: '{{ $baseUrl }}',
            api_key: '{{ $apiKey }}'
        },
        alert: {
            show: false,
            type: 'success',
            title: '',
            message: '',
            showTroubleshoot: false
        },

        copyApiKey() {
            navigator.clipboard.writeText(this.form.api_key).then(() => {
                this.showAlert('success', 'Berhasil', 'API Key berhasil disalin ke clipboard!', false);
            });
        },

        showAlert(type, title, message, showTroubleshoot = false) {
            this.alert.type = type;
            this.alert.title = title;
            this.alert.message = message;
            this.alert.showTroubleshoot = showTroubleshoot;
            this.alert.show = true;
            if (type === 'success') {
                setTimeout(() => {
                    this.alert.show = false;
                }, 6000);
            }
        },

        async saveConfig() {
            this.isSaving = true;
            try {
                const res = await fetch("{{ route('simpati.update_config') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.form)
                });
                const data = await res.json();
                if (data.success) {
                    this.showAlert('success', 'Konfigurasi Disimpan', data.message);
                } else {
                    this.showAlert('error', 'Gagal Menyimpan', data.message || 'Terjadi kesalahan.');
                }
            } catch (err) {
                this.showAlert('error', 'Kesalahan Server', err.message);
            } finally {
                this.isSaving = false;
            }
        },

        async testConnection() {
            this.isTesting = true;
            try {
                const url = new URL("{{ route('simpati.test') }}", window.location.origin);
                url.searchParams.append('base_url', this.form.base_url);
                url.searchParams.append('api_key', this.form.api_key);

                const res = await fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });
                const data = await res.json();
                if (data.success) {
                    this.connectionStatus = 'online';
                    this.showAlert('success', 'Koneksi Berhasil!', data.message + ' (Ditemukan ' + data.data_count + ' data pegawai aktif)', false);
                } else {
                    this.connectionStatus = 'offline';
                    this.showAlert('error', 'Koneksi ke SIMPATI Gagal', data.message, true);
                }
            } catch (err) {
                this.connectionStatus = 'offline';
                this.showAlert('error', 'Koneksi Gagal', 'Tidak dapat menghubungi server lokal: ' + err.message, true);
            } finally {
                this.isTesting = false;
            }
        },

        async syncData() {
            if (!confirm('Jalankan sinkronisasi langsung dari SIMPATI API ke SIKEREN?')) {
                return;
            }
            this.isSyncing = true;
            try {
                const res = await fetch("{{ route('simpati.sync') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.form)
                });
                const data = await res.json();
                if (data.success) {
                    this.connectionStatus = 'online';
                    this.syncResult = data;
                    this.lastSyncTime = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
                    this.showAlert('success', 'Sinkronisasi Berhasil!', 'Data ' + data.pegawai_total + ' Pegawai dan ' + data.tim_total + ' Tim Kerja berhasil diperbarui ke database Sikeren.', false);
                } else {
                    this.showAlert('error', 'Sinkronisasi Gagal', data.message || 'Gagal mengambil data dari SIMPATI API.', true);
                }
            } catch (err) {
                this.showAlert('error', 'Gagal Memproses Sinkronisasi', err.message, true);
            } finally {
                this.isSyncing = false;
            }
        },

        async syncMockData() {
            this.isSyncing = true;
            try {
                const res = await fetch("{{ route('simpati.sync_mock') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });
                const data = await res.json();
                if (data.success) {
                    data.is_mock = true;
                    this.syncResult = data;
                    this.lastSyncTime = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
                    this.showAlert('success', 'Sinkronisasi Simulasi Berhasil!', 'Data simulasi BPS (Pegawai & Tim Kerja) berhasil masuk ke database Sikeren.', false);
                } else {
                    this.showAlert('error', 'Simulasi Gagal', data.message || 'Terjadi kesalahan saat memproses data simulasi.', false);
                }
            } catch (err) {
                this.showAlert('error', 'Kesalahan', err.message, false);
            } finally {
                this.isSyncing = false;
            }
        }
    };
}
</script>
@endpush
@endsection
