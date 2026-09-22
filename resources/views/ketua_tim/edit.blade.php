@extends('layouts.app')

@section('title', 'Edit Kegiatan - Sikeren')
@section('header_title', 'Edit Kegiatan')

@section('content')

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    {{-- HEADER --}}
    <div class="flex items-center gap-4 mb-10">

        <a href="{{ route('ketua-tim.index') }}"
           class="w-12 h-12 bg-white rounded-2xl shadow-sm border border-gray-100
                  flex items-center justify-center text-gray-400
                  hover:text-indigo-600 hover:border-indigo-100
                  transition-all">
           <svg class="w-6 h-6 flex-shrink-0" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
    <circle cx="12" cy="12" r="9.5" fill="#FEF3C7" stroke="#FDE68A" stroke-width="1.5"/>
    <path d="M15 12H9M9 12L12 9M9 12L12 15" stroke="#D97706" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
        </a>

        <div>
            <h2 class="text-2xl font-black text-gray-900">
                Edit Kegiatan
            </h2>
            <p class="text-gray-500 text-sm mt-1">
                Perbarui rincian kegiatan dan informasi penugasan tim.
            </p>
        </div>

    </div>

    {{-- ERROR VALIDATION --}}
    @if($errors->any())
        <div class="mb-8 bg-red-50 border border-red-100 rounded-3xl p-5">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 bg-red-100 rounded-2xl flex items-center justify-center text-red-600 flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M5.07 19h13.86c1.54 0 2.5-1.67 1.73-3L13.73 4c-.77-1.33-2.69-1.33-3.46 0L3.34 16c-.77 1.33.19 3 1.73 3z"/>
                    </svg>
                </div>
                <div>
                    <p class="font-bold text-red-700 text-sm">Periksa kembali data yang dimasukkan.</p>
                    <ul class="mt-2 text-xs text-red-600 space-y-1">
                        @foreach($errors->all() as $error)
                            <li>• {{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <form action="{{ route('ketua-tim.update', $agenda->id) }}" method="POST" class="space-y-8">
        @csrf
        @method('PUT')

        {{-- =====================================================
            SECTION 1: INFORMASI DASAR
        ====================================================== --}}
        <div class="bg-white rounded-[2.5rem] p-8 shadow-sm border border-gray-100 relative overflow-hidden">
            <div class="absolute top-0 left-0 w-2.5 h-full bg-indigo-500 rounded-l-full"></div>

            <div class="flex items-center gap-4 mb-6">
                <div class="w-12 h-12 bg-indigo-50 rounded-2xl flex items-center justify-center text-indigo-600">
                    <svg class="w-6 h-6 flex-shrink-0" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
    <!-- Papan Belakang -->
    <rect x="4" y="5" width="16" height="17" rx="3" fill="#EEF2FF" stroke="#4F46E5" stroke-width="1.8"/>
    <!-- Lembaran Kertas Putih -->
    <rect x="6.5" y="7" width="11" height="13" rx="1.5" fill="#FFFFFF"/>
    <!-- Penjepit Logam / Clip Atas -->
    <path d="M8.5 4C8.5 2.89543 9.39543 2 10.5 2H13.5C14.6046 2 15.5 2.89543 15.5 4V6H8.5V4Z" fill="#6366F1" stroke="#4338CA" stroke-width="1.5"/>
    <circle cx="12" cy="4" r="1" fill="#FFFFFF"/>
    <!-- Garis Catatan Berwarna -->
    <path d="M9 11H15" stroke="#6366F1" stroke-width="1.8" stroke-linecap="round"/>
    <path d="M9 14.5H15" stroke="#818CF8" stroke-width="1.8" stroke-linecap="round"/>
    <path d="M9 17.5H12.5" stroke="#10B981" stroke-width="1.8" stroke-linecap="round"/>
</svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-800">1. Informasi Dasar & Agenda</h3>
                    <p class="text-xs text-gray-400 mt-1">Perbarui informasi utama kegiatan.</p>
                </div>
            </div>

            <div class="space-y-6">
                {{-- PERIHAL --}}
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Perihal / Judul Surat</label>
                    <input type="text"
                           name="perihal"
                           value="{{ old('perihal', $agenda->perihal) }}"
                           placeholder="Contoh: Surat Tugas Kegiatan Statistik"
                           class="w-full px-4 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:bg-white transition">
                </div>

                {{-- AGENDA + SUB KEGIATAN --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            Agenda Utama <span class="text-red-500">*</span>
                        </label>
                        <input type="text"
                               name="agenda"
                               value="{{ old('agenda', $agenda->agenda) }}"
                               required
                               class="w-full px-4 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:bg-white transition">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Sub Kegiatan</label>
                        <input type="text"
                               name="sub_kegiatan"
                               value="{{ old('sub_kegiatan', $agenda->sub_kegiatan) }}"
                               class="w-full px-4 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:bg-white transition">
                    </div>
                </div>

                {{-- DASAR --}}
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Dasar (Link / Referensi)</label>
                    <input type="text"
                           name="dasar"
                           value="{{ old('dasar', $agenda->dasar) }}"
                           class="w-full px-4 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:bg-white transition">
                </div>
            </div>
        </div>

        {{-- =====================================================
            SECTION 2: WAKTU, LOKASI & STATUS
        ====================================================== --}}
        <div class="bg-white rounded-[2.5rem] p-8 shadow-sm border border-gray-100 relative overflow-hidden">
            <div class="absolute top-0 left-0 w-2.5 h-full bg-orange-400 rounded-l-full"></div>

            <div class="flex items-center gap-4 mb-6">
                <div class="w-12 h-12 bg-orange-50 rounded-2xl flex items-center justify-center text-orange-500">
                    <svg class="w-6 h-6 flex-shrink-0" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
    <!-- Badan Kalender -->
    <rect x="3" y="6" width="18" height="15" rx="3" fill="#FFF7ED" stroke="#EA580C" stroke-width="1.8"/>
    <!-- Header Oranye Kalender -->
    <path d="M3 9C3 7.34315 4.34315 6 6 6H18C19.6569 6 21 7.34315 21 9V10H3V9Z" fill="#F97316"/>
    <!-- Pengait Ring Kalender -->
    <path d="M8 3V6M16 3V6" stroke="#C2410C" stroke-width="2" stroke-linecap="round"/>
    <!-- Titik Tanggal Berwarna -->
    <circle cx="8" cy="14" r="1.2" fill="#F97316"/>
    <circle cx="12" cy="14" r="1.2" fill="#F59E0B"/>
    <circle cx="16" cy="14" r="1.2" fill="#10B981"/>
    <circle cx="8" cy="17.5" r="1.2" fill="#EA580C"/>
    <circle cx="12" cy="17.5" r="1.2" fill="#6366F1"/>
    <circle cx="16" cy="17.5" r="1.2" fill="#F97316"/>
</svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-800">2. Waktu, Lokasi & Status</h3>
                    <p class="text-xs text-gray-400 mt-1">Atur jadwal, tempat, dan status pelaksanaan.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                {{-- TANGGAL --}}
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Tanggal <span class="text-red-500">*</span></label>
                    <input type="date"
                           name="tanggal"
                           value="{{ old('tanggal', $agenda->tanggal) }}"
                           required
                           class="w-full px-4 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-orange-400 focus:bg-white transition">
                </div>

                {{-- JAM --}}
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Jam <span class="text-red-500">*</span></label>
                    <input type="time"
                           name="jam"
                           value="{{ old('jam', $agenda->jam) }}"
                           required
                           class="w-full px-4 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-orange-400 focus:bg-white transition">
                </div>

                {{-- TEMPAT --}}
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Opsi Tempat <span class="text-red-500">*</span></label>
                    <select name="tempat"
                            required
                            class="w-full px-4 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-orange-400 focus:bg-white transition">
                        <option value="Kantor Pusat" {{ old('tempat', $agenda->tempat) == 'Kantor Pusat' ? 'selected' : '' }}>Kantor Pusat</option>
                        <option value="Daring (Zoom)" {{ old('tempat', $agenda->tempat) == 'Daring (Zoom)' ? 'selected' : '' }}>Daring (Zoom)</option>
                        <option value="Luar Kantor" {{ old('tempat', $agenda->tempat) == 'Luar Kantor' ? 'selected' : '' }}>Luar Kantor</option>
                    </select>
                </div>

                {{-- STATUS KEGIATAN --}}
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Status <span class="text-red-500">*</span></label>
                    <select name="status"
                            required
                            class="w-full px-4 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-orange-400 focus:bg-white transition font-semibold">
                        <option value="terjadwal" {{ old('status', $agenda->status) == 'terjadwal' ? 'selected' : '' }}>⏳ Terjadwal</option>
                        <option value="selesai" {{ old('status', $agenda->status) == 'selesai' ? 'selected' : '' }}>Selesai</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- =====================================================
            SECTION 3: PENUGASAN & ANGGOTA
        ====================================================== --}}
        <div class="bg-white rounded-[2.5rem] p-8 shadow-sm border border-gray-100 relative overflow-hidden">
            <div class="absolute top-0 left-0 w-2.5 h-full bg-emerald-500 rounded-l-full"></div>

            <div class="flex items-center gap-4 mb-6">
                <div class="w-12 h-12 bg-emerald-50 rounded-2xl flex items-center justify-center text-emerald-600">
                    <svg class="w-6 h-6 flex-shrink-0" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
    <!-- Anggota Tim di Belakang (Cyan/Teal) -->
    <circle cx="16" cy="7.5" r="3" fill="#E0F2FE" stroke="#0284C7" stroke-width="1.8"/>
    <path d="M14 14.5C14.7 14 15.8 13.7 17 13.7C19.2 13.7 21 15.3 21 17.5V19.5" stroke="#0284C7" stroke-width="1.8" stroke-linecap="round"/>
    
    <!-- Anggota Tim Utama di Depan (Emerald Green) -->
    <circle cx="9" cy="8" r="3.5" fill="#ECFDF5" stroke="#10B981" stroke-width="2"/>
    <path d="M3 19.5V18C3 15.2386 5.23858 13 8 13H10C12.7614 13 15 15.2386 15 18V19.5" fill="#D1FAE5" stroke="#10B981" stroke-width="2" stroke-linecap="round"/>
</svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-800">3. Penugasan & Anggota Tim</h3>
                    <p class="text-xs text-gray-400 mt-1">Perbarui penanggung jawab dan anggota tim terkait.</p>
                </div>
            </div>

            <div class="space-y-6">
                {{-- PJ --}}
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Nama PJ Kegiatan <span class="text-red-500">*</span></label>
                    <input type="text"
                           name="pj"
                           value="{{ old('pj', $agenda->pj) }}"
                           required
                           class="w-full px-4 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:bg-white transition">
                </div>

                {{-- TUJUAN --}}
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Tujuan Penugasan (Kab/Kota)</label>
                    <select name="tujuan"
                            class="w-full px-4 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:bg-white transition">
                        <option value="">-- Pilih Kabupaten/Kota --</option>
                        <optgroup label="Sulawesi Tenggara">
                            @php
                                $daftarKabkot = [
                                    'Kabupaten Buton', 'Kabupaten Muna', 'Kabupaten Konawe', 'Kabupaten Kolaka',
                                    'Kabupaten Konawe Selatan', 'Kabupaten Bombana', 'Kabupaten Wakatobi',
                                    'Kabupaten Kolaka Utara', 'Kabupaten Buton Utara', 'Kabupaten Konawe Utara',
                                    'Kabupaten Kolaka Timur', 'Kabupaten Konawe Kepulauan', 'Kabupaten Muna Barat',
                                    'Kabupaten Buton Tengah', 'Kabupaten Buton Selatan', 'Kota Kendari', 'Kota Baubau'
                                ];
                            @endphp
                            @foreach($daftarKabkot as $kab)
                                <option value="{{ $kab }}" {{ old('tujuan', $agenda->tujuan) == $kab ? 'selected' : '' }}>
                                    {{ $kab }}
                                </option>
                            @endforeach
                        </optgroup>
                    </select>
                </div>

                {{-- ANGGOTA TIM LAIN --}}
                <div class="pt-2">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Kelola Anggota Tim Lain</label>

                    <div class="flex flex-col sm:flex-row gap-3">
                        <input type="text"
                               id="anggotaInput"
                               placeholder="Tambah nama anggota baru..."
                               class="flex-1 px-4 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:bg-white transition">

                        <button type="button"
                                id="tambahAnggota"
                                class="px-6 py-3.5 bg-indigo-600 text-white font-bold rounded-2xl shadow-md hover:bg-indigo-700 transition-all active:scale-95 flex items-center justify-center gap-1.5">
                            + Tambah
                        </button>
                    </div>

                    <!-- Daftar anggota yang sudah ada -->
                    <div id="daftarAnggota" class="mt-4 space-y-2.5">
                        @foreach($anggotaList as $anggota)
                            <div class="flex items-center justify-between px-4 py-3 bg-indigo-50/50 border border-indigo-100 rounded-2xl">
                                <div class="flex items-center gap-3">
                                    <div class="w-7 h-7 rounded-full bg-indigo-600 text-white text-xs font-bold flex items-center justify-center">
                                        {{ strtoupper(substr($anggota, 0, 1)) }}
                                    </div>
                                    <span class="text-sm font-semibold text-gray-800">{{ $anggota }}</span>
                                </div>
                                <button type="button" class="btnHapusAnggota text-red-500 hover:text-red-700 text-xs font-bold px-3 py-1.5 rounded-xl hover:bg-red-50 transition-colors">
                                    Hapus
                                </button>
                                <input type="hidden" name="anggota[]" value="{{ $anggota }}">
                            </div>
                        @endforeach
                    </div>
                </div>

            </div>
        </div>

        {{-- =====================================================
            TOMBOL SIMPAN PERUBAHAN
        ====================================================== --}}
        <div class="flex items-center justify-end gap-4 pt-4">
            <a href="{{ route('ketua-tim.index') }}"
               class="px-6 py-3.5 text-gray-500 font-bold hover:text-gray-700 transition-colors">
                Batal
            </a>

          <button type="submit"
        id="btnSubmit"
        class="inline-flex items-center justify-center
               px-6 py-3 bg-indigo-600 hover:bg-indigo-700
               font-bold rounded-2xl
               shadow-lg shadow-indigo-200
               transition-all transform hover:scale-105">

    Simpan Perubahan

    <svg class="w-5 h-5 ml-2 flex-shrink-0" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
    <!-- Lingkaran Emas -->
    <circle cx="12" cy="12" r="9.5" fill="#FEF3C7" stroke="#F59E0B" stroke-width="1.8"/>
    <!-- Tanda Centang Oranye Emas -->
    <path d="M8 12.5L10.5 15L16 9.5" stroke="#D97706" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
</svg>

</button>
        </div>

    </form>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const inputAnggota  = document.getElementById('anggotaInput');
    const tombolTambah  = document.getElementById('tambahAnggota');
    const daftarAnggota = document.getElementById('daftarAnggota');

    // Event listener untuk tombol hapus anggota yang sudah ada
    document.querySelectorAll('.btnHapusAnggota').forEach(function(btn) {
        btn.addEventListener('click', function() {
            this.closest('div').remove();
        });
    });

    // Tambah anggota baru
    function tambahAnggotaBaru() {
        const nama = inputAnggota.value.trim();
        if (nama === '') {
            inputAnggota.focus();
            return;
        }

        const anggota = document.createElement('div');
        anggota.className = 'flex items-center justify-between px-4 py-3 bg-indigo-50/50 border border-indigo-100 rounded-2xl';

        const leftBox = document.createElement('div');
        leftBox.className = 'flex items-center gap-3';

        const avatar = document.createElement('div');
        avatar.className = 'w-7 h-7 rounded-full bg-indigo-600 text-white text-xs font-bold flex items-center justify-center';
        avatar.textContent = nama.charAt(0).toUpperCase();

        const namaAnggota = document.createElement('span');
        namaAnggota.className = 'text-sm font-semibold text-gray-800';
        namaAnggota.textContent = nama;

        leftBox.appendChild(avatar);
        leftBox.appendChild(namaAnggota);

        const tombolHapus = document.createElement('button');
        tombolHapus.type = 'button';
        tombolHapus.className = 'text-red-500 hover:text-red-700 text-xs font-bold px-3 py-1.5 rounded-xl hover:bg-red-50 transition-colors';
        tombolHapus.textContent = 'Hapus';
        tombolHapus.addEventListener('click', function () {
            anggota.remove();
        });

        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = 'anggota[]';
        hiddenInput.value = nama;

        anggota.appendChild(leftBox);
        anggota.appendChild(tombolHapus);
        anggota.appendChild(hiddenInput);

        daftarAnggota.appendChild(anggota);

        inputAnggota.value = '';
        inputAnggota.focus();
    }

    tombolTambah.addEventListener('click', tambahAnggotaBaru);

    inputAnggota.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            tambahAnggotaBaru();
        }
    });
});
</script>

@endsection