@extends('layouts.app')

@section('title', 'Buat Kegiatan - Sikeren')
@section('header_title', 'Buat Kegiatan')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div class="mb-8">
        <h2 class="text-xl font-bold text-gray-800">Form Buat Kegiatan Baru</h2>
        <p class="text-gray-500 text-sm mt-1">Silakan lengkapi formulir di bawah ini untuk membuat kegiatan baru.</p>
    </div>

    @if (session('success'))
    <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6 rounded-r">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            </div>
            <div class="ml-3">
                <p class="text-sm text-green-700 font-medium">{{ session('success') }}</p>
            </div>
        </div>
    </div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-8 py-6 border-b border-gray-100 bg-gray-50/50">
            <h3 class="text-lg font-semibold text-gray-800">Detail Kegiatan</h3>
        </div>
        
        <div class="p-8">
            <form action="{{ url('/store_penugasan') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                <input type="hidden" name="jenis" value="Kegiatan">
                <input type="hidden" name="start" value="{{ $start }}">
                <input type="hidden" name="end" value="{{ $end }}">
                <input type="hidden" name="tes" value="{{ $id + 1 }}"> 
                
                <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-2">
                    
                    {{-- Master Proyek SIMPATI Selector --}}
                    <div class="sm:col-span-2 p-4 bg-gradient-to-r from-blue-50/90 to-indigo-50/90 rounded-2xl border border-blue-200 shadow-2xs space-y-2">
                        <div class="flex items-center justify-between">
                            <label class="block text-xs font-bold text-blue-900 uppercase tracking-wider flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                                <span>Pilih dari Master Proyek SIMPATI (Otomatisasi)</span>
                            </label>
                            <span class="text-[10px] font-bold text-blue-700 bg-white/90 px-2.5 py-0.5 rounded-full border border-blue-200">
                                Auto-fill Topik, PJ & Anggota
                            </span>
                        </div>
                        <select id="selectMasterProyekPenugasan"
                                onchange="handleSelectMasterProyekPenugasan(this)"
                                class="w-full px-3 py-2.5 bg-white border border-blue-300 rounded-xl text-xs text-gray-800 font-semibold focus:ring-2 focus:ring-blue-500 focus:border-blue-600 transition shadow-2xs">
                            <option value="">-- Pilih dari Master Kegiatan / Proyek SIMPATI (80 Proyek) --</option>
                            @foreach($masterProyeksByTim ?? [] as $timName => $proyeks)
                                <optgroup label="Tim: {{ $timName }}">
                                    @foreach($proyeks as $prj)
                                        <option value="{{ $prj->proyekid }}"
                                                data-nama="{{ $prj->namaproyek }}"
                                                data-tim="{{ $prj->nm_tim }}"
                                                data-pj-nama="{{ $prj->pj_nama ?? '' }}"
                                                data-pj-nip="{{ $prj->pj_nip ?? '' }}"
                                                data-anggota='@json($prj->anggota->map(fn($a) => ["nama" => $a->nama_lengkap, "nip" => $a->niplama]))'>
                                            {{ $prj->namaproyek }} {{ $prj->pj_nama ? '(PJ: ' . $prj->pj_nama . ')' : '(Belum Ada PJ)' }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                        <div id="proyekFeedbackPenugasan" class="hidden p-2.5 rounded-xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-800 font-medium">
                            <div class="flex items-start gap-2">
                                <svg class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span id="proyekFeedbackPenugasanText"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Jenis Kegiatan -->
                    <div class="sm:col-span-2">
                        <label for="jenis_kegiatan" class="block text-sm font-semibold text-gray-700">Jenis Kegiatan</label>
                        <div class="mt-1">
                            <select id="jenis_kegiatan" name="jenis_kegiatan" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-lg p-2.5 border bg-gray-50 text-gray-900">
                                <option value="Non-Rapat">Non-Rapat / Penugasan</option>
                                <option value="Rapat">Rapat</option>
                            </select>
                        </div>
                    </div>

                    <!-- Topik -->
                    <div class="sm:col-span-2">
                        <label for="text" class="block text-sm font-semibold text-gray-700">Topik Kegiatan / Rapat</label>
                        <div class="mt-1">
                            <input type="text" id="text" name="text" value="{{ old('text') }}" placeholder="Masukkan topik" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-lg p-2.5 border bg-gray-50 text-gray-900">
                        </div>
                        @error('text') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Agenda -->
                    <div class="sm:col-span-2">
                        <label for="agenda" class="block text-sm font-semibold text-gray-700">Agenda</label>
                        <div class="mt-1">
                            <input type="text" id="agenda" name="agenda" value="{{ old('agenda') }}" placeholder="Agenda kegiatan" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-lg p-2.5 border bg-gray-50 text-gray-900">
                        </div>
                        @error('agenda') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    
                    <!-- Hidden field for Surat Dasar (Jika Disposisi) -->
                    @if(request()->has('surat_id'))
                        @php $surat = \App\SuratMasuk::find(request('surat_id')); @endphp
                        @if($surat)
                            <div class="sm:col-span-2 bg-indigo-50 border-l-4 border-indigo-500 p-4 rounded-r-lg">
                                <div class="flex">
                                    <div class="ml-3">
                                        <p class="text-sm text-indigo-700 font-medium">Kegiatan ini berdasarkan Surat Disposisi: <strong>{{ $surat->nomor_surat }} - {{ $surat->perihal }}</strong></p>
                                    </div>
                                </div>
                                <input type="hidden" name="surat_id" value="{{ $surat->id }}">
                            </div>
                        @endif
                    @endif

                    <!-- Hidden field for SubKegiatan -->
                    @if(request()->has('parent_id'))
                        @php $parent = \App\Task::find(request('parent_id')); @endphp
                        @if($parent)
                            <div class="sm:col-span-2 bg-blue-50 border-l-4 border-blue-500 p-4 rounded-r-lg">
                                <div class="flex">
                                    <div class="ml-3">
                                        <p class="text-sm text-blue-700 font-medium">Membuat Sub-Kegiatan untuk Induk: <strong>{{ $parent->text }}</strong></p>
                                    </div>
                                </div>
                                <input type="hidden" name="parent_id" value="{{ $parent->id }}">
                            </div>
                        @endif
                    @endif

                    <!-- Tempat -->
                    <div class="sm:col-span-2">
                        <label for="tempat" class="block text-sm font-semibold text-gray-700">Tempat</label>
                        <div class="mt-1">
                            <input type="text" id="tempat" name="tempat" value="{{ old('tempat') }}" placeholder="Lokasi pelaksanaan kegiatan" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-lg p-2.5 border bg-gray-50 text-gray-900">
                        </div>
                    </div>

                    <!-- Tujuan Penugasan (Only for Non-Rapat) -->
                    <div class="sm:col-span-2 non-rapat-field">
                        <label for="jenis_tujuan" class="block text-sm font-semibold text-gray-700">Tujuan Penugasan</label>
                        <div class="mt-1">
                            <select id="jenis_tujuan" name="jenis_tujuan" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-lg p-2.5 border bg-gray-50 text-gray-900">
                                <option value="">-- Pilih Tujuan --</option>
                                <option value="provinsi">Provinsi</option>
                                <option value="kabupaten">Kabupaten / Kota</option>
                            </select>
                        </div>
                    </div>

                    <!-- Kabupaten (Conditional for Non-Rapat) -->
                    <div class="sm:col-span-2 hidden" id="kabupaten_box">
                        <label for="kabupaten" class="block text-sm font-semibold text-gray-700">Kabupaten / Kota</label>
                        <div class="mt-1">
                            <select id="kabupaten" name="tujuan" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-lg p-2.5 border bg-gray-50 text-gray-900">
                                <option value="">-- Pilih Kabupaten/Kota --</option>
                                <option value="Kabupaten Bombana">Kabupaten Bombana</option>
                                <option value="Kabupaten Buton">Kabupaten Buton</option>
                                <!-- (Other options trimmed for brevity) -->
                                <option value="Kota Kendari">Kota Kendari</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Penanggung Jawab (Only for Non-Rapat) -->
                    <div class="sm:col-span-1 non-rapat-field">
                        <label for="penanggung_jawab" class="block text-sm font-semibold text-gray-700">Penanggung Jawab</label>
                        <div class="mt-1">
                            <select id="penanggung_jawab" name="penanggung_jawab" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-lg p-2.5 border bg-gray-50 text-gray-900">
                                <option value="">-- Pilih Penanggung Jawab --</option>
                                @foreach ($peserta as $p)
                                    <option value="{{ $p->nama_lengkap }}">{{ $p->nama_lengkap }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Tanggal Mulai & Akhir -->
                    <div>
                        <label for="start_date" class="block text-sm font-semibold text-gray-700">Tanggal Mulai</label>
                        <div class="mt-1">
                            <input type="date" id="start_date" name="start_date" value="{{ old('start_date') }}" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-lg p-2.5 border bg-gray-50 text-gray-900">
                        </div>
                    </div>
                    <div>
                        <label for="date_akhir" class="block text-sm font-semibold text-gray-700">Tanggal Akhir</label>
                        <div class="mt-1">
                            <input type="date" id="date_akhir" name="date_akhir" value="{{ old('date_akhir') }}" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-lg p-2.5 border bg-gray-50 text-gray-900">
                        </div>
                    </div>

                    <!-- Jam Mulai & Akhir (Only for Rapat) -->
                    <div class="rapat-field hidden">
                        <label for="start_jam" class="block text-sm font-semibold text-gray-700">Jam Mulai</label>
                        <div class="mt-1">
                            <input type="time" id="start_jam" name="start_jam" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-lg p-2.5 border bg-gray-50 text-gray-900">
                        </div>
                    </div>
                    <div class="rapat-field hidden">
                        <label for="end_jam" class="block text-sm font-semibold text-gray-700">Jam Akhir</label>
                        <div class="mt-1">
                            <input type="time" id="end_jam" name="end_jam" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-lg p-2.5 border bg-gray-50 text-gray-900">
                        </div>
                    </div>

                    <!-- Pemimpin Rapat (Only for Rapat) -->
                    <div class="sm:col-span-1 rapat-field hidden">
                        <label for="pemimpin" class="block text-sm font-semibold text-gray-700">Pemimpin Rapat</label>
                        <div class="mt-1">
                            <select id="pemimpin" name="pemimpin" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-lg p-2.5 border bg-gray-50 text-gray-900">
                                <option value="">-- Pilih Pemimpin --</option>
                                @foreach ($peserta as $p)
                                    <option value="{{ $p->nama_lengkap }}">{{ $p->nama_lengkap }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Notulis (Only for Rapat) -->
                    <div class="sm:col-span-1 rapat-field hidden">
                        <label for="notulis" class="block text-sm font-semibold text-gray-700">Notulis</label>
                        <div class="mt-1">
                            <select id="notulis" name="notulis" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-lg p-2.5 border bg-gray-50 text-gray-900">
                                <option value="">-- Pilih Notulis --</option>
                                @foreach ($peserta as $p)
                                    <option value="{{ $p->nama_lengkap }}">{{ $p->nama_lengkap }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Tim Dokumentasi -->
                    <div class="sm:col-span-2">
                        <label for="tim_dokumentasi" class="block text-sm font-semibold text-gray-700">Tim Dokumentasi (Opsional)</label>
                        <div class="mt-1">
                            <select id="tim_dokumentasi" name="tim_dokumentasi" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-lg p-2.5 border bg-gray-50 text-gray-900">
                                <option value="">-- Pilih Tim Dokumentasi --</option>
                                @foreach ($peserta as $p)
                                    <option value="{{ $p->nama_lengkap }}">{{ $p->nama_lengkap }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Peserta Rapat -->
                    <div class="sm:col-span-2 pt-4 border-t border-gray-100 mt-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-4">Peserta Kegiatan</label>
                        
                        <div class="space-y-4">
                            @foreach ($master_groups as $index => $category)
                            <div class="border border-gray-200 rounded-lg bg-gray-50 overflow-hidden">
                                <div class="px-4 py-3 border-b border-gray-200 bg-gray-100 flex justify-between items-center">
                                    <h4 class="text-sm font-bold text-gray-700">{{ $category->grup }}</h4>
                                    <label class="inline-flex items-center cursor-pointer">
                                        <input type="checkbox" class="select-all-cb rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" data-target="group-{{ $index }}">
                                        <span class="ml-2 text-xs text-gray-600 font-medium">Pilih Semua</span>
                                    </label>
                                </div>
                                <div class="p-4 bg-white grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3" id="group-{{ $index }}">
                                    @foreach ($groups as $participant)
                                        @if ($participant->grup == $category->grup)
                                        <label class="inline-flex items-center">
                                            <input type="checkbox" name="owners[]" id="peserta{{ $participant->niplama }}" value="{{ $participant->niplama }}" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                            <span class="ml-2 text-sm text-gray-700">{{ $participant->nama_lengkap }}</span>
                                        </label>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                            @endforeach
                        </div>
                        
                        @error('owners')
                            <span class="text-xs text-red-500 mt-2 block">{{ $message }}</span>
                        @enderror
                    </div>

                </div>

                <div class="pt-6 flex justify-end gap-3 border-t border-gray-100">
                    <button type="reset" class="bg-white py-2 px-4 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Batal
                    </button>
                    <button type="submit" class="inline-flex justify-center py-2 px-6 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Simpan Kegiatan
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener("DOMContentLoaded", function () {
    const jenisKegiatanSelect = document.getElementById('jenis_kegiatan');
    const rapatFields = document.querySelectorAll('.rapat-field');
    const nonRapatFields = document.querySelectorAll('.non-rapat-field');
    const jenisTujuanSelect = document.getElementById('jenis_tujuan');
    const kabupatenBox = document.getElementById('kabupaten_box');
    const kabupatenSelect = document.getElementById('kabupaten');

    // Toggle fields based on Jenis Kegiatan
    jenisKegiatanSelect.addEventListener('change', function () {
        if (this.value === 'Rapat') {
            rapatFields.forEach(el => el.classList.remove('hidden'));
            nonRapatFields.forEach(el => el.classList.add('hidden'));
            kabupatenBox.classList.add('hidden'); // Ensure hidden when Rapat
        } else {
            rapatFields.forEach(el => el.classList.add('hidden'));
            nonRapatFields.forEach(el => el.classList.remove('hidden'));
            if (jenisTujuanSelect.value === 'kabupaten') {
                kabupatenBox.classList.remove('hidden');
            }
        }
    });

    jenisTujuanSelect.addEventListener('change', function () {
        if (this.value === 'kabupaten') {
            kabupatenBox.classList.remove('hidden');
        } else {
            kabupatenBox.classList.add('hidden');
            kabupatenSelect.value = '';
        }
    });

    // Select All Logic
    document.querySelectorAll('.select-all-cb').forEach(function(selectAllCb) {
        selectAllCb.addEventListener('change', function() {
            const targetId = this.getAttribute('data-target');
            const checkboxes = document.querySelectorAll('#' + targetId + ' input[type="checkbox"]');
            
            checkboxes.forEach(function(cb) {
                cb.checked = selectAllCb.checked;
            });
        });
    });

    window.handleSelectMasterProyekPenugasan = function(selectElem) {
        const selectedOpt = selectElem.options[selectElem.selectedIndex];
        if (!selectedOpt || !selectedOpt.value) {
            const fb = document.getElementById('proyekFeedbackPenugasan');
            if (fb) fb.classList.add('hidden');
            return;
        }

        const nama = selectedOpt.getAttribute('data-nama') || '';
        const tim = selectedOpt.getAttribute('data-tim') || '';
        const pjNama = selectedOpt.getAttribute('data-pj-nama') || '';
        const pjNip = selectedOpt.getAttribute('data-pj-nip') || '';
        let anggotaList = [];
        try {
            anggotaList = JSON.parse(selectedOpt.getAttribute('data-anggota') || '[]');
        } catch (e) {
            anggotaList = [];
        }

        const inputTopik = document.getElementById('text');
        if (inputTopik) inputTopik.value = nama;

        const inputAgenda = document.getElementById('agenda');
        if (inputAgenda) inputAgenda.value = 'Pelaksanaan kegiatan ' + nama + ' (' + tim + ')';

        if (pjNama) {
            const pjSelect = document.getElementById('penanggung_jawab');
            if (pjSelect) {
                let matched = false;
                const pjClean = pjNama.toLowerCase().trim();
                for (let i = 0; i < pjSelect.options.length; i++) {
                    const optVal = pjSelect.options[i].value.toLowerCase().trim();
                    if (optVal === pjClean || optVal.includes(pjClean) || pjClean.includes(optVal)) {
                        pjSelect.selectedIndex = i;
                        matched = true;
                        break;
                    }
                }
                if (!matched) {
                    const newOpt = new Option(pjNama + (pjNip ? ' (' + pjNip + ')' : ''), pjNama, true, true);
                    pjSelect.add(newOpt);
                }
            }
        }

        let checkedCount = 0;
        if (anggotaList.length > 0) {
            document.querySelectorAll('input[name="owners[]"]').forEach(cb => cb.checked = false);

            anggotaList.forEach(m => {
                const targetNama = (m.nama || '').toLowerCase().trim();
                const targetNip = (m.nip || '').trim();

                document.querySelectorAll('input[name="owners[]"]').forEach(cb => {
                    const label = cb.closest('label');
                    const labelText = label ? label.textContent.toLowerCase() : '';
                    if (!cb.checked) {
                        if ((targetNama && labelText.includes(targetNama)) || (targetNip && cb.value === targetNip)) {
                            cb.checked = true;
                            checkedCount++;
                        }
                    }
                });
            });
        }

        const fb = document.getElementById('proyekFeedbackPenugasan');
        const fbText = document.getElementById('proyekFeedbackPenugasanText');
        if (fb && fbText) {
            let msg = `Proyek "${nama}" diterapkan: Tim [${tim}], PJ [${pjNama || 'Belum Ada PJ'}]`;
            if (checkedCount > 0) {
                msg += `, dan ${checkedCount} anggota proyek otomatis dicentang sebagai peserta!`;
            }
            fbText.textContent = msg;
            fb.classList.remove('hidden');
        }
    };
});
</script>
@endpush
