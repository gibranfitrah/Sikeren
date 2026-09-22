@extends('layouts.app')

@section('title', 'Buat Rapat Baru - Sikeren')
@section('header_title', 'Buat Rapat')

@section('content')
<div class="max-w-5xl mx-auto space-y-6 pb-12">

    {{-- ==========================================
        PAGE HEADER & WORKFLOW STEPPER
    =========================================== --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ url('/daftar_kegiatan') }}" 
               class="p-2.5 bg-white hover:bg-gray-100 text-gray-600 rounded-xl border border-gray-200 shadow-xs transition-colors" 
               title="Kembali ke Daftar Kegiatan">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div>
                <h2 class="text-xl font-bold text-gray-800">Form Buat Rapat Baru</h2>
                <p class="text-xs text-gray-500 mt-0.5">Rapat adalah bagian spesifik dari sebuah kegiatan (atau koordinasi mandiri) yang membutuhkan pimpinan, notulis, penentuan ruangan/zoom, dan notulen.</p>
            </div>
        </div>

        {{-- Minimalist Workflow Step Indicator --}}
        <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-white border border-gray-200 rounded-xl text-xs font-semibold text-gray-600 shadow-xs">
            <span class="inline-flex items-center gap-1.5 text-blue-600 font-bold">
                <span class="w-5 h-5 rounded-full bg-blue-600 text-white text-[10px] flex items-center justify-center font-bold">1</span>
                <span>1. Buat Rapat</span>
            </span>
            <span class="text-gray-300">&rarr;</span>
            <span class="text-blue-600 font-semibold">2. Booking Ruangan & Zoom</span>
            <span class="text-gray-300">&rarr;</span>
            <span class="text-gray-500">3. Persetujuan</span>
            <span class="text-gray-300">&rarr;</span>
            <span class="text-gray-500">4. Presensi QR</span>
            <span class="text-gray-300">&rarr;</span>
            <span class="text-gray-500">5. Notulen</span>
        </div>
    </div>

    {{-- ==========================================
        ALERT NOTIFICATIONS
    =========================================== --}}
    @if (session('success'))
    <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded-r-xl shadow-xs">
        <div class="flex items-center">
            <svg class="h-5 w-5 text-green-500 mr-3 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <p class="text-xs font-medium text-green-800">{{ session('success') }}</p>
        </div>
    </div>
    @endif

    @if ($errors->any())
    <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-r-xl shadow-xs">
        <div class="flex items-start">
            <svg class="h-5 w-5 text-red-500 mr-3 flex-shrink-0 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
            </svg>
            <div>
                <h4 class="text-xs font-bold text-red-800">Terdapat kesalahan pengisian formulir:</h4>
                <ul class="list-disc list-inside mt-1 text-xs text-red-700 space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
    @endif

    {{-- ==========================================
        MAIN FORM CARD (UNIFIED & TIDY)
    =========================================== --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <form action="{{ route('rapat.store') }}" method="POST" enctype="multipart/form-data" id="formBuatRapat">
            @csrf
            <input type="hidden" name="jenis" value="Rapat">
            <input type="hidden" name="tes2" value="{{ $id + 1 }}">

            {{-- ---------------------------------------------------------
                BAGIAN 1: INFORMASI & AGENDA RAPAT
            ---------------------------------------------------------- --}}
            <div class="p-6 sm:p-8 space-y-6">
                <div class="flex items-center gap-3 pb-3 border-b border-gray-100">
                    <span class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 font-bold text-xs flex items-center justify-center border border-blue-200">1</span>
                    <div>
                        <h3 class="text-sm font-bold text-gray-800">Informasi & Agenda Rapat</h3>
                        <p class="text-xs text-gray-500">Tentukan kategori, topik utama, serta agenda bahasan rapat.</p>
                    </div>
                </div>

                {{-- Kategori Rapat (Radio Selection) --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                        Kategori Rapat <span class="text-red-500">*</span>
                    </label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 max-w-xl">
                        <label class="flex items-center gap-3 p-3 rounded-xl border border-gray-200 bg-gray-50/60 hover:bg-blue-50/40 cursor-pointer transition-all has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50/50 has-[:checked]:ring-1 has-[:checked]:ring-blue-500">
                            <input type="radio" name="is_kegiatan" value="1" id="kategoriTerkait" class="text-blue-600 focus:ring-blue-500 h-4 w-4" checked onchange="toggleKategoriRapat()">
                            <div>
                                <div class="text-xs font-bold text-gray-800">Rapat Bagian dari Kegiatan Tim</div>
                                <div class="text-[11px] text-gray-500">Rapat diselenggarakan sebagai bagian / tahapan spesifik dari master kegiatan</div>
                            </div>
                        </label>
                        <label class="flex items-center gap-3 p-3 rounded-xl border border-gray-200 bg-gray-50/60 hover:bg-blue-50/40 cursor-pointer transition-all has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50/50 has-[:checked]:ring-1 has-[:checked]:ring-blue-500">
                            <input type="radio" name="is_kegiatan" value="0" id="kategoriMandiri" class="text-blue-600 focus:ring-blue-500 h-4 w-4" onchange="toggleKategoriRapat()">
                            <div>
                                <div class="text-xs font-bold text-gray-800">Rapat Mandiri / Koordinasi Rutin</div>
                                <div class="text-[11px] text-gray-500">Rapat internal / koordinasi berkala mandiri tanpa terikat proyek kegiatan tertentu</div>
                            </div>
                        </label>
                    </div>
                </div>

                {{-- Master Kegiatan Dropdown (Conditional) --}}
                <div id="sectionRelasiKegiatan" class="p-4 bg-blue-50/40 rounded-xl border border-blue-100 space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="kegiatan_id" class="block text-xs font-bold text-gray-700 mb-1">
                                Pilih Master Kegiatan Tim <span class="text-red-500">*</span>
                            </label>
                            <select id="kegiatan_id" name="kegiatan_id" class="w-full text-xs border-gray-300 rounded-lg p-2.5 bg-white text-gray-800 shadow-xs focus:ring-blue-500 focus:border-blue-500 border" onchange="handleSelectMasterKegiatan(this)">
                                <option value="">-- Pilih Agenda Kegiatan --</option>
                                @foreach($kegiatans as $kg)
                                    <option value="{{ $kg->id }}" data-agenda="{{ $kg->agenda }}" data-sub="{{ $kg->sub_kegiatan ?? '' }}" data-pj="{{ $kg->pj ?? '' }}">
                                        {{ $kg->agenda }} (PJ: {{ $kg->pj ?? '-' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="sub_kegiatan" class="block text-xs font-bold text-gray-700 mb-1">
                                Sub Kegiatan Terkait
                            </label>
                            <input type="text" id="sub_kegiatan" name="sub_kegiatan" value="{{ old('sub_kegiatan') }}" placeholder="Contoh: Tahap Persiapan & Pelatihan Petugas" class="w-full text-xs border-gray-300 rounded-lg p-2.5 bg-white text-gray-800 shadow-xs focus:ring-blue-500 focus:border-blue-500 border">
                        </div>
                    </div>
                </div>

                {{-- Topik & Agenda Pembahasan --}}
                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label for="text" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                            Topik / Nama Rapat <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="text" name="text" value="{{ old('text') }}" placeholder="Contoh: Rapat Koordinasi Evaluasi Pendataan SAKERNAS 2026" required class="w-full text-xs border-gray-300 rounded-lg p-2.5 bg-gray-50 focus:bg-white text-gray-800 shadow-xs focus:ring-blue-500 focus:border-blue-500 border">
                        @error('text') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="agenda" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                            Agenda Pembahasan <span class="text-red-500">*</span>
                        </label>
                        <textarea id="agenda" name="agenda" rows="3" placeholder="Tuliskan poin-poin pembahasan rapat yang akan didiskusikan..." required class="w-full text-xs border-gray-300 rounded-lg p-2.5 bg-gray-50 focus:bg-white text-gray-800 shadow-xs focus:ring-blue-500 focus:border-blue-500 border">{{ old('agenda') }}</textarea>
                        @error('agenda') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            {{-- ---------------------------------------------------------
                BAGIAN 2: WAKTU & TEMPAT PELAKSANAAN
            ---------------------------------------------------------- --}}
            <div class="p-6 sm:p-8 space-y-6 bg-gray-50/40 border-t border-gray-200">
                <div class="flex items-center gap-3 pb-3 border-b border-gray-200">
                    <span class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 font-bold text-xs flex items-center justify-center border border-blue-200">2</span>
                    <div>
                        <h3 class="text-sm font-bold text-gray-800">Waktu & Tempat Pelaksanaan</h3>
                        <p class="text-xs text-gray-500">Jadwalkan tanggal, jam pelaksanaan, dan mode pertemuan rapat.</p>
                    </div>
                </div>

                {{-- Waktu Rapat (Tanggal Tunggal & Jam) --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label for="start_date" class="block text-xs font-bold text-gray-700 mb-1">
                            Tanggal Pelaksanaan Rapat <span class="text-red-500">*</span>
                        </label>
                        <input type="date" id="start_date" name="start_date" value="{{ old('start_date', date('Y-m-d')) }}" required class="w-full text-xs border-gray-300 rounded-lg p-2.5 bg-white text-gray-800 shadow-xs focus:ring-blue-500 focus:border-blue-500 border">
                    </div>
                    <div>
                        <label for="start_jam" class="block text-xs font-bold text-gray-700 mb-1">
                            Jam Mulai <span class="text-red-500">*</span>
                        </label>
                        <input type="time" id="start_jam" name="start_jam" value="{{ old('start_jam', '09:00') }}" required class="w-full text-xs border-gray-300 rounded-lg p-2.5 bg-white text-gray-800 shadow-xs focus:ring-blue-500 focus:border-blue-500 border">
                    </div>
                    <div>
                        <label for="end_jam" class="block text-xs font-bold text-gray-700 mb-1">
                            Jam Selesai <span class="text-red-500">*</span>
                        </label>
                        <input type="time" id="end_jam" name="end_jam" value="{{ old('end_jam', '12:00') }}" required class="w-full text-xs border-gray-300 rounded-lg p-2.5 bg-white text-gray-800 shadow-xs focus:ring-blue-500 focus:border-blue-500 border">
                    </div>
                </div>

                {{-- Mode Pertemuan --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                        Mode Pertemuan <span class="text-red-500">*</span>
                    </label>
                    <div class="grid grid-cols-3 gap-3 max-w-xl">
                        <label class="flex items-center justify-center gap-2 p-2.5 rounded-xl border border-gray-200 bg-white hover:bg-blue-50/40 cursor-pointer text-xs font-semibold text-gray-700 text-center transition-all has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50/50 has-[:checked]:text-blue-700 has-[:checked]:ring-1 has-[:checked]:ring-blue-500">
                            <input type="radio" name="tipe_tempat" value="online" id="modeOnline" class="text-blue-600 focus:ring-blue-500" onchange="toggleModeTempat()">
                            <span>Online (Daring)</span>
                        </label>
                        <label class="flex items-center justify-center gap-2 p-2.5 rounded-xl border border-gray-200 bg-white hover:bg-blue-50/40 cursor-pointer text-xs font-semibold text-gray-700 text-center transition-all has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50/50 has-[:checked]:text-blue-700 has-[:checked]:ring-1 has-[:checked]:ring-blue-500">
                            <input type="radio" name="tipe_tempat" value="offline" id="modeOffline" class="text-blue-600 focus:ring-blue-500" checked onchange="toggleModeTempat()">
                            <span>Offline (Tatap Muka)</span>
                        </label>
                        <label class="flex items-center justify-center gap-2 p-2.5 rounded-xl border border-gray-200 bg-white hover:bg-blue-50/40 cursor-pointer text-xs font-semibold text-gray-700 text-center transition-all has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50/50 has-[:checked]:text-blue-700 has-[:checked]:ring-1 has-[:checked]:ring-blue-500">
                            <input type="radio" name="tipe_tempat" value="hybrid" id="modeHybrid" class="text-blue-600 focus:ring-blue-500" onchange="toggleModeTempat()">
                            <span>Hybrid (Campuran)</span>
                        </label>
                    </div>
                </div>

                {{-- Detail Lokasi (Dinamis) --}}
                <div class="p-4 bg-white rounded-xl border border-gray-200 space-y-4">
                    {{-- Link Online --}}
                    <div id="wrapperLinkOnline" class="hidden">
                        <label for="link_meeting" class="block text-xs font-bold text-gray-700 mb-1">
                            Tautan Meeting Online (Zoom / Google Meet / Teams)
                        </label>
                        <input type="url" id="link_meeting" name="link_meeting" value="{{ old('link_meeting') }}" placeholder="https://meet.google.com/xxx-xxxx-xxx atau https://zoom.us/j/..." class="w-full text-xs border-gray-300 rounded-lg p-2.5 bg-gray-50 focus:bg-white text-gray-800 shadow-xs focus:ring-blue-500 focus:border-blue-500 border">
                    </div>

                    {{-- Lokasi Offline --}}
                    <div id="wrapperRuangOffline" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="venue_id" class="block text-xs font-bold text-gray-700 mb-1">
                                Pilih Ruangan Rapat Kantor
                            </label>
                            <select id="venue_id" name="venue_id" class="w-full text-xs border-gray-300 rounded-lg p-2.5 bg-gray-50 focus:bg-white text-gray-800 shadow-xs focus:ring-blue-500 focus:border-blue-500 border" onchange="handleSelectVenue(this)">
                                <option value="">-- Pilih Ruangan Tersedia --</option>
                                @foreach($venues as $vn)
                                    <option value="{{ $vn->id }}" data-name="{{ $vn->name }}">
                                        {{ $vn->name }} (Kapasitas: {{ $vn->capacity ?? 'Std' }} Orang)
                                    </option>
                                @endforeach
                                <option value="custom">Lokasi / Ruangan Lainnya (Ketik Manual)</option>
                            </select>
                        </div>
                        <div>
                            <label for="tempat_offline" class="block text-xs font-bold text-gray-700 mb-1">
                                Nama Tempat / Lokasi Fisik
                            </label>
                            <input type="text" id="tempat_offline" name="tempat_offline" value="{{ old('tempat_offline', 'Aula Utama BPS') }}" placeholder="Tuliskan nama ruangan atau lokasi" class="w-full text-xs border-gray-300 rounded-lg p-2.5 bg-gray-50 focus:bg-white text-gray-800 shadow-xs focus:ring-blue-500 focus:border-blue-500 border">
                        </div>
                    </div>
                </div>
            </div>

            {{-- ---------------------------------------------------------
                BAGIAN 3: PETUGAS & PEMIMPIN RAPAT
            ---------------------------------------------------------- --}}
            <div class="p-6 sm:p-8 space-y-6 border-t border-gray-200">
                <div class="flex items-center gap-3 pb-3 border-b border-gray-100">
                    <span class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 font-bold text-xs flex items-center justify-center border border-blue-200">3</span>
                    <div>
                        <h3 class="text-sm font-bold text-gray-800">Petugas & Pemimpin Rapat</h3>
                        <p class="text-xs text-gray-500">Tentukan penanggung jawab persetujuan, notulis, dan dokumentasi rapat.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    {{-- Penanggung Jawab (PJ / Ketua Tim) --}}
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label for="penanggung_jawab" class="block text-xs font-bold text-gray-700">
                                Penanggung Jawab (PJ) <span class="text-red-500">*</span>
                            </label>
                            <span class="text-[10px] text-emerald-700 font-bold bg-emerald-50 px-1.5 py-0.5 rounded">Ketua Tim</span>
                        </div>
                        @php
                            $defaultPj = old('penanggung_jawab', (!Auth::user()->isAdmin() ? (Auth::user()->nama_lengkap ?? '') : ''));
                        @endphp
                        <select id="penanggung_jawab" name="penanggung_jawab" class="w-full text-xs border-gray-300 rounded-lg p-2.5 bg-gray-50 focus:bg-white text-gray-800 shadow-xs focus:ring-emerald-500 focus:border-emerald-500 border">
                            <option value="">-- Pilih Penanggung Jawab (PJ) --</option>
                            @if(isset($eligiblePJs) && $eligiblePJs->count() > 0)
                                <optgroup label="Pejabat, Ketua Tim, & Ahli Madya (Eligible PJ)">
                                    @foreach ($eligiblePJs as $u)
                                        <option value="{{ $u->nama_lengkap }}" {{ $defaultPj == $u->nama_lengkap ? 'selected' : '' }}>
                                            {{ $u->select_option_label }}
                                        </option>
                                    @endforeach
                                </optgroup>
                                <optgroup label="Pegawai BPS">
                                    @foreach ($allUsers->diff($eligiblePJs) as $u)
                                        <option value="{{ $u->nama_lengkap }}" {{ $defaultPj == $u->nama_lengkap ? 'selected' : '' }}>
                                            {{ $u->select_option_label }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @else
                                @foreach ($peserta as $p)
                                    <option value="{{ $p->nama_lengkap }}" {{ $defaultPj == $p->nama_lengkap ? 'selected' : '' }}>
                                        {{ $p->nama_lengkap }} ({{ $p->nipbaru ?? $p->niplama }})
                                    </option>
                                @endforeach
                            @endif
                        </select>
                        <div id="pjInfoContainer" class="hidden mt-1.5 px-2.5 py-1 bg-emerald-50 border border-emerald-200 rounded-lg text-[11px] text-emerald-800 font-medium">
                            <div class="flex items-center gap-1.5">
                                <svg width="14" height="14" style="width: 14px; height: 14px; min-width: 14px; min-height: 14px; max-width: 14px; max-height: 14px;" class="text-emerald-600 inline-block flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span id="pjInfoText" class="truncate">PJ otomatis terpilih</span>
                            </div>
                        </div>
                    </div>

                    {{-- Pemimpin Rapat --}}
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label for="pemimpin" class="block text-xs font-bold text-gray-700">
                                Pemimpin Rapat <span class="text-red-500">*</span>
                            </label>
                            <span class="text-[10px] text-blue-600 font-bold bg-blue-50 px-1.5 py-0.5 rounded">Approval</span>
                        </div>
                        @php
                            $defaultPemimpin = old('pemimpin', (!Auth::user()->isAdmin() ? (Auth::user()->nama_lengkap ?? '') : ''));
                        @endphp
                        <select id="pemimpin" name="pemimpin" required class="w-full text-xs border-gray-300 rounded-lg p-2.5 bg-gray-50 focus:bg-white text-gray-800 shadow-xs focus:ring-blue-500 focus:border-blue-500 border">
                            <option value="">-- Pilih Pemimpin Rapat --</option>
                            @if(isset($eligiblePJs) && $eligiblePJs->count() > 0)
                                <optgroup label="Pejabat, Ketua Tim, & Ahli Madya (Eligible PJ)">
                                    @foreach ($eligiblePJs as $u)
                                        <option value="{{ $u->nama_lengkap }}" {{ $defaultPemimpin == $u->nama_lengkap ? 'selected' : '' }}>
                                            {{ $u->select_option_label }}
                                        </option>
                                    @endforeach
                                </optgroup>
                                <optgroup label="Pegawai BPS">
                                    @foreach ($allUsers->diff($eligiblePJs) as $u)
                                        <option value="{{ $u->nama_lengkap }}" {{ $defaultPemimpin == $u->nama_lengkap ? 'selected' : '' }}>
                                            {{ $u->select_option_label }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @else
                                @foreach ($peserta as $p)
                                    <option value="{{ $p->nama_lengkap }}" {{ $defaultPemimpin == $p->nama_lengkap ? 'selected' : '' }}>
                                        {{ $p->nama_lengkap }} ({{ $p->nipbaru ?? $p->niplama }})
                                    </option>
                                @endforeach
                            @endif
                        </select>
                        @error('pemimpin') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- Notulis --}}
                    <div>
                        <label for="notulis" class="block text-xs font-bold text-gray-700 mb-1">
                            Notulis Rapat <span class="text-red-500">*</span>
                        </label>
                        <select id="notulis" name="notulis" required class="w-full text-xs border-gray-300 rounded-lg p-2.5 bg-gray-50 focus:bg-white text-gray-800 shadow-xs focus:ring-blue-500 focus:border-blue-500 border">
                            <option value="">-- Pilih Notulis Rapat --</option>
                            @foreach ($peserta as $p)
                                <option value="{{ $p->nama_lengkap }}" {{ old('notulis') == $p->nama_lengkap ? 'selected' : '' }}>
                                    {{ $p->nama_lengkap }}
                                </option>
                            @endforeach
                        </select>
                        @error('notulis') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- Tim Dokumentasi --}}
                    <div>
                        <label for="tim_dokumentasi" class="block text-xs font-bold text-gray-700 mb-1">
                            Tim Dokumentasi <span class="text-gray-400 font-normal">(Opsional)</span>
                        </label>
                        <select id="tim_dokumentasi" name="tim_dokumentasi" class="w-full text-xs border-gray-300 rounded-lg p-2.5 bg-gray-50 focus:bg-white text-gray-800 shadow-xs focus:ring-blue-500 focus:border-blue-500 border">
                            <option value="">-- Pilih Dokumentasi --</option>
                            @foreach ($peserta as $p)
                                <option value="{{ $p->nama_lengkap }}" {{ old('tim_dokumentasi') == $p->nama_lengkap ? 'selected' : '' }}>
                                    {{ $p->nama_lengkap }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- ---------------------------------------------------------
                BAGIAN 4: BERKAS & DOKUMEN RAPAT
            ---------------------------------------------------------- --}}
            <div class="p-6 sm:p-8 space-y-6 bg-gray-50/40 border-t border-gray-200">
                <div class="flex items-center gap-3 pb-3 border-b border-gray-200">
                    <span class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 font-bold text-xs flex items-center justify-center border border-blue-200">4</span>
                    <div>
                        <h3 class="text-sm font-bold text-gray-800">Berkas & Dokumen Pendukung</h3>
                        <p class="text-xs text-gray-500">Unggah berkas surat undangan resmi dan materi bahan rapat.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    {{-- Surat Undangan (PDF Wajib) --}}
                    <div class="p-4 bg-white rounded-xl border border-gray-200 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-gray-800 flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                Surat Undangan Rapat
                            </span>
                            <span class="text-[10px] font-bold bg-red-100 text-red-700 px-2 py-0.5 rounded">PDF (Wajib)</span>
                        </div>
                        <div>
                            <input type="file" name="undangan_file" id="undangan_file" accept=".pdf" class="w-full text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3.5 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
                        </div>
                        <div>
                            <input type="url" name="link" value="{{ old('link') }}" placeholder="Atau tautan dokumen cloud: https://..." class="w-full text-xs border-gray-300 rounded-lg p-2 bg-gray-50 text-gray-800 focus:bg-white focus:ring-blue-500 focus:border-blue-500 border">
                        </div>
                    </div>

                    {{-- Bahan Materi (Opsional) --}}
                    <div class="p-4 bg-white rounded-xl border border-gray-200 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-gray-800 flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"/>
                                </svg>
                                Materi / Bahan Tayang
                            </span>
                            <span class="text-[10px] font-semibold bg-gray-100 text-gray-600 px-2 py-0.5 rounded">Opsional</span>
                        </div>
                        <div>
                            <input type="file" name="materi_file" id="materi_file" accept=".pdf,.ppt,.pptx,.doc,.docx,.zip,.rar" class="w-full text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3.5 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200 cursor-pointer">
                        </div>
                        <div>
                            <input type="url" name="materi_link" value="{{ old('materi_link') }}" placeholder="Atau tautan Google Drive / OneDrive..." class="w-full text-xs border-gray-300 rounded-lg p-2 bg-gray-50 text-gray-800 focus:bg-white focus:ring-blue-500 focus:border-blue-500 border">
                        </div>
                    </div>
                </div>
            </div>

            {{-- ---------------------------------------------------------
                BAGIAN 5: DAFTAR PESERTA RAPAT
            ---------------------------------------------------------- --}}
            <div class="p-6 sm:p-8 space-y-6 border-t border-gray-200">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-gray-100">
                    <div class="flex items-center gap-3">
                        <span class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 font-bold text-xs flex items-center justify-center border border-blue-200">5</span>
                        <div>
                            <h3 class="text-sm font-bold text-gray-800">Daftar Peserta Rapat <span class="text-red-500">*</span></h3>
                            <p class="text-xs text-gray-500">Centang pegawai yang diundang untuk menghadiri rapat ini.</p>
                        </div>
                    </div>
                    <div class="inline-flex items-center px-3 py-1 bg-blue-50 border border-blue-200 rounded-lg text-xs font-bold text-blue-700" id="badgeCountPeserta">
                        0 Peserta Dipilih
                    </div>
                </div>

                {{-- Live Search Filter --}}
                <div class="relative max-w-md">
                    <input type="text" id="filterPesertaNama" placeholder="Cari nama peserta rapat..." class="w-full text-xs pl-9 border-gray-300 rounded-lg p-2.5 bg-gray-50 focus:bg-white text-gray-800 shadow-xs focus:ring-blue-500 focus:border-blue-500 border">
                </div>

                {{-- Daftar Kelompok & Peserta --}}
                <div class="space-y-4 max-h-[420px] overflow-y-auto pr-1 border border-gray-200 rounded-xl p-3 bg-gray-50/50">
                    @forelse ($master_groups as $index => $category)
                    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden shadow-2xs grup-peserta-card">
                        <div class="px-4 py-2.5 bg-gray-100/70 border-b border-gray-200 flex items-center justify-between">
                            <span class="text-xs font-bold text-gray-700 uppercase tracking-wide">{{ $category->grup }}</span>
                            <label class="inline-flex items-center cursor-pointer select-none bg-white px-2.5 py-1 rounded-md border border-gray-300 text-[11px] font-semibold text-gray-700 hover:bg-gray-50 shadow-2xs">
                                <input type="checkbox" class="cb-select-group rounded border-gray-300 text-blue-600 focus:ring-blue-500 h-3.5 w-3.5" data-target="box-group-{{ $index }}">
                                <span class="ml-1.5">Pilih Semua</span>
                            </label>
                        </div>
                        <div class="p-3 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2" id="box-group-{{ $index }}">
                            @foreach ($groups as $participant)
                                @if ($participant->grup == $category->grup)
                                <label class="item-peserta flex items-center p-2 rounded-lg border border-transparent hover:border-blue-200 hover:bg-blue-50/40 cursor-pointer transition-all">
                                    <input type="checkbox" name="owners[]" value="{{ $participant->niplama }}" class="cb-peserta rounded border-gray-300 text-blue-600 focus:ring-blue-500 h-4 w-4" onchange="updateTotalPeserta()">
                                    <span class="ml-2.5 text-xs text-gray-800 font-medium truncate nama-peserta-label" title="{{ $participant->nama_lengkap }}">{{ $participant->nama_lengkap }}</span>
                                </label>
                                @endif
                            @endforeach
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-8 text-gray-400 text-xs">
                        Tidak ada kelompok peserta yang tersedia.
                    </div>
                    @endforelse
                </div>
                @error('owners') <span class="text-xs text-red-500 block">{{ $message }}</span> @enderror
            </div>

            {{-- ==========================================
                FORM ACTIONS (FOOTER)
            =========================================== --}}
            <div class="px-6 sm:p-8 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
                <button type="reset" class="px-5 py-2.5 bg-white border border-gray-300 hover:bg-gray-100 text-gray-700 text-xs font-semibold rounded-lg shadow-xs transition-colors">
                    Reset Formulir
                </button>
                <div class="flex items-center gap-3">
                    <a href="{{ url('/daftar_kegiatan') }}" class="px-5 py-2.5 bg-white border border-gray-300 hover:bg-gray-100 text-gray-700 text-xs font-semibold rounded-lg shadow-xs transition-colors">
                        Batal
                    </a>
                    <button type="submit" class="inline-flex items-center justify-center px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-sm transition-colors">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Simpan & Lanjut Booking Ruangan &rarr;
                    </button>
                </div>
            </div>

        </form>
    </div>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener("DOMContentLoaded", function () {
    // 1. Kategori Rapat (Terkait Kegiatan vs Mandiri)
    window.toggleKategoriRapat = function () {
        const isTerkait = document.getElementById('kategoriTerkait').checked;
        const boxRelasi = document.getElementById('sectionRelasiKegiatan');
        const pjInfo = document.getElementById('pjInfoContainer');

        if (isTerkait) {
            boxRelasi.classList.remove('hidden');
        } else {
            boxRelasi.classList.add('hidden');
            document.getElementById('kegiatan_id').value = '';
            document.getElementById('sub_kegiatan').value = '';
            if (pjInfo) pjInfo.classList.add('hidden');
        }
    };

    // Auto-fill saat memilih master kegiatan (termasuk auto-select Ketua Tim / PJ)
    window.handleSelectMasterKegiatan = function (selectElem) {
        const selectedOption = selectElem.options[selectElem.selectedIndex];
        if (!selectedOption || !selectedOption.value) return;

        const sub = selectedOption.getAttribute('data-sub') || '';
        const agenda = selectedOption.getAttribute('data-agenda') || '';
        const pj = selectedOption.getAttribute('data-pj') || '';

        if (sub) {
            document.getElementById('sub_kegiatan').value = sub;
        }
        if (!document.getElementById('text').value && agenda) {
            document.getElementById('text').value = 'Rapat Koordinasi: ' + agenda;
        }

        // Auto-select Ketua Tim (PJ) sebagai Penanggung Jawab & Pemimpin Rapat
        if (pj) {
            const pjSelect = document.getElementById('penanggung_jawab');
            const pemimpinSelect = document.getElementById('pemimpin');
            const pjClean = pj.toLowerCase().trim();

            if (pjSelect) {
                for (let i = 0; i < pjSelect.options.length; i++) {
                    const optVal = pjSelect.options[i].value.toLowerCase().trim();
                    if (optVal === pjClean || optVal.includes(pjClean) || pjClean.includes(optVal)) {
                        pjSelect.selectedIndex = i;
                        break;
                    }
                }
            }

            if (pemimpinSelect) {
                for (let i = 0; i < pemimpinSelect.options.length; i++) {
                    const optVal = pemimpinSelect.options[i].value.toLowerCase().trim();
                    if (optVal === pjClean || optVal.includes(pjClean) || pjClean.includes(optVal)) {
                        pemimpinSelect.selectedIndex = i;
                        break;
                    }
                }
            }

            const infoContainer = document.getElementById('pjInfoContainer');
            const infoText = document.getElementById('pjInfoText');
            if (infoContainer && infoText) {
                infoText.textContent = 'Ketua Tim/PJ otomatis terpilih: ' + pj;
                infoContainer.classList.remove('hidden');
            }
        }
    };

    // 2. Toggle Mode Pertemuan (Online / Offline / Hybrid)
    window.toggleModeTempat = function () {
        const isOnline = document.getElementById('modeOnline').checked;
        const isOffline = document.getElementById('modeOffline').checked;
        const isHybrid = document.getElementById('modeHybrid').checked;

        const wrapperOnline = document.getElementById('wrapperLinkOnline');
        const wrapperOffline = document.getElementById('wrapperRuangOffline');

        if (isOnline) {
            wrapperOnline.classList.remove('hidden');
            wrapperOffline.classList.add('hidden');
        } else if (isOffline) {
            wrapperOnline.classList.add('hidden');
            wrapperOffline.classList.remove('hidden');
        } else if (isHybrid) {
            wrapperOnline.classList.remove('hidden');
            wrapperOffline.classList.remove('hidden');
        }
    };

    // 3. Pilihan Venue (Ruangan Rapat)
    window.handleSelectVenue = function (selectElem) {
        const selectedOption = selectElem.options[selectElem.selectedIndex];
        const val = selectElem.value;
        const inputTempat = document.getElementById('tempat_offline');

        if (val === 'custom') {
            inputTempat.value = '';
            inputTempat.focus();
        } else if (val) {
            const venueName = selectedOption.getAttribute('data-name');
            inputTempat.value = venueName;
        }
    };

    // 4. Hitung Total Peserta Terpilih
    window.updateTotalPeserta = function () {
        const count = document.querySelectorAll('.cb-peserta:checked').length;
        const badge = document.getElementById('badgeCountPeserta');
        if (badge) {
            badge.textContent = count + ' Peserta Dipilih';
        }
    };

    // 5. Checkbox Pilih Semua per Grup
    document.querySelectorAll('.cb-select-group').forEach(function (groupCheckbox) {
        groupCheckbox.addEventListener('change', function () {
            const targetId = this.getAttribute('data-target');
            const targetBox = document.getElementById(targetId);
            if (targetBox) {
                const checkboxes = targetBox.querySelectorAll('.cb-peserta');
                checkboxes.forEach(function (cb) {
                    cb.checked = groupCheckbox.checked;
                });
                updateTotalPeserta();
            }
        });
    });

    // 6. Pencarian Peserta Live
    const filterInput = document.getElementById('filterPesertaNama');
    if (filterInput) {
        filterInput.addEventListener('input', function () {
            const keyword = this.value.toLowerCase().trim();
            const items = document.querySelectorAll('.item-peserta');

            items.forEach(function (item) {
                const nameLabel = item.querySelector('.nama-peserta-label');
                const text = nameLabel ? nameLabel.textContent.toLowerCase() : '';
                if (text.includes(keyword)) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    }

    // Inisialisasi awal
    updateTotalPeserta();
    toggleModeTempat();
});
</script>
@endpush
