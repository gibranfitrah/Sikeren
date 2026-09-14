@extends('layouts.app')

@section('title', 'Buat Disposisi - Sikeren')
@section('header_title', 'Buat Disposisi')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div class="mb-8">
        <h2 class="text-xl font-bold text-gray-800">Lembar Disposisi</h2>
        <p class="text-gray-500 text-sm mt-1">Buat disposisi dari surat masuk yang diterima.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Kolom Kiri: Dokumen Surat (Embed Google Drive) -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden h-[800px] flex flex-col">
            <div class="p-4 bg-gray-50 border-b border-gray-100 font-semibold text-gray-700 flex justify-between items-center">
                <span>Dokumen Surat Masuk</span>
                <a href="{{ $suratMasuk->file_surat }}" target="_blank" class="text-sm text-blue-600 hover:underline">Buka di Tab Baru <i class="bi bi-box-arrow-up-right"></i></a>
            </div>
            <div class="flex-grow w-full bg-gray-200">
                @if($suratMasuk->file_surat)
                    <iframe src="{{ str_replace('/view?usp=sharing', '/preview', $suratMasuk->file_surat) }}" class="w-full h-full border-0" allow="autoplay"></iframe>
                @else
                    <div class="flex items-center justify-center h-full text-gray-500">
                        Tidak ada tautan Google Drive yang dilampirkan.
                    </div>
                @endif
            </div>
        </div>

        <!-- Kolom Kanan: Form Lembar Disposisi -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-6">
                <form action="{{ route('disposisi.store') }}" method="POST" class="space-y-6 text-sm">
                    @csrf
                    <input type="hidden" name="surat_masuk_id" value="{{ $suratMasuk->id }}">
                    
                    <div class="grid grid-cols-2 gap-4 bg-blue-50 p-4 rounded-lg border border-blue-100 mb-6">
                        <div>
                            <span class="text-gray-500 block text-xs">Nomor Surat:</span>
                            <span class="font-medium text-gray-900">{{ $suratMasuk->nomor_surat }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500 block text-xs">Tanggal Surat:</span>
                            <span class="font-medium text-gray-900">{{ \Carbon\Carbon::parse($suratMasuk->tanggal_surat)->format('d M Y') }}</span>
                        </div>
                        <div class="col-span-2">
                            <span class="text-gray-500 block text-xs">Dari (Asal Surat):</span>
                            <span class="font-medium text-gray-900">{{ $suratMasuk->asal_surat }}</span>
                        </div>
                        <div class="col-span-2">
                            <span class="text-gray-500 block text-xs">Ringkasan / Perihal:</span>
                            <span class="font-medium text-gray-900">{{ $suratMasuk->perihal }}</span>
                        </div>
                    </div>

                    <!-- Header Form -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block font-semibold text-gray-700 mb-1">Tingkat Keamanan</label>
                            <select name="tingkat_keamanan" class="w-full border-gray-300 rounded-lg p-2 bg-gray-50 text-gray-900">
                                <option value="B">B (Biasa)</option>
                                <option value="R">R (Rahasia)</option>
                                <option value="SR">SR (Sangat Rahasia)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold text-gray-700 mb-1">Tgl. Penyelesaian</label>
                            <input type="date" name="tgl_penyelesaian" class="w-full border-gray-300 rounded-lg p-2 bg-gray-50 text-gray-900">
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- Disposisi Opsi -->
                    <div>
                        <label class="block font-semibold text-gray-700 mb-3">Disposisi</label>
                        <div class="grid grid-cols-2 gap-y-2 gap-x-4">
                            @php
                                $opsiDisposisi = [
                                    1 => 'Edarkan', 2 => 'Mohon dihadiri/diwakili', 3 => 'Dampingi', 4 => 'Bicarakan dengan saya',
                                    5 => 'Dibahas bersama', 6 => 'Dijadwalkan', 7 => 'Teliti dan tanggapi', 8 => 'Siapkan draft/bahan',
                                    9 => 'Siapkan jawaban sesuai ketentuan', 10 => 'Siapkan laporan/laporkan', 11 => 'Dapat disetujui',
                                    12 => 'Ditolak', 13 => 'Perbaiki', 14 => 'Untuk diselesaikan', 15 => 'Koordinasikan',
                                    16 => 'Untuk menjadi perhatian', 17 => 'Tindak Lanjut', 18 => 'Untuk arsip'
                                ];
                            @endphp
                            @foreach($opsiDisposisi as $key => $opsi)
                                <label class="flex items-start">
                                    <input type="checkbox" name="opsi_disposisi[]" value="{{ $key }}" class="mt-1 mr-2 text-blue-600 border-gray-300 rounded">
                                    <span class="text-gray-700">{{ $key }}. {{ $opsi }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- Diteruskan Kepada & Diketahui -->
                    <div>
                        <div class="grid grid-cols-12 gap-2 font-semibold text-gray-700 mb-3 border-b pb-2">
                            <div class="col-span-8">Diteruskan Kepada</div>
                            <div class="col-span-4 text-center">Diketahui</div>
                        </div>
                        
                        <div class="font-medium text-gray-700 mb-2">Pilih Divisi/Tim dari Database:</div>
                        <div class="grid grid-cols-1 gap-y-2 mb-4 max-h-80 overflow-y-auto border border-gray-200 p-3 rounded bg-gray-50">
                            @foreach($tim as $t)
                                <div class="grid grid-cols-12 gap-2 items-center hover:bg-gray-100 p-1 rounded">
                                    <div class="col-span-8">
                                        <label class="flex items-center cursor-pointer">
                                            <input type="checkbox" name="diteruskan_kepada_tim[]" value="{{ $t->id }}" class="mr-2 text-blue-600 border-gray-300 rounded">
                                            <span class="text-gray-700 text-sm">{{ $t->nm_organisasi }}</span>
                                        </label>
                                    </div>
                                    <div class="col-span-4 text-center">
                                        <input type="checkbox" name="diketahui[]" value="{{ $t->id }}" class="text-green-600 border-gray-300 rounded focus:ring-green-500 cursor-pointer">
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="font-medium text-gray-700 mb-2 mt-4">Lainnya:</div>
                        <div class="space-y-3">
                            <div class="grid grid-cols-12 gap-2 items-center">
                                <div class="col-span-8">
                                    <label class="flex items-center">
                                        <input type="checkbox" name="diteruskan_kepada_lainnya_check" value="1" class="mr-2 text-blue-600 border-gray-300 rounded">
                                        <span class="text-gray-700">1. Kepala BPS Kabupaten/Kota Se-Sultra</span>
                                    </label>
                                </div>
                                <div class="col-span-4 text-center">
                                    <input type="checkbox" name="diketahui[]" value="Lainnya1" class="text-green-600 border-gray-300 rounded focus:ring-green-500">
                                </div>
                            </div>

                            <div class="grid grid-cols-12 gap-2 items-center">
                                <div class="col-span-8 flex items-center">
                                    <span class="mr-2 text-gray-700">2.</span>
                                    <input type="text" name="diteruskan_kepada_lainnya" class="w-full border-b border-gray-300 bg-transparent p-1 focus:outline-none focus:border-blue-500" placeholder="................................................................">
                                </div>
                                <div class="col-span-4 text-center">
                                    <input type="checkbox" name="diketahui[]" value="Lainnya2" class="text-green-600 border-gray-300 rounded focus:ring-green-500">
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-12 gap-2 items-center">
                                <div class="col-span-8 flex items-center">
                                    <span class="mr-2 text-gray-700">3.</span>
                                    <input type="text" name="diteruskan_kepada_lainnya_3" class="w-full border-b border-gray-300 bg-transparent p-1 focus:outline-none focus:border-blue-500" placeholder="................................................................">
                                </div>
                                <div class="col-span-4 text-center">
                                    <input type="checkbox" name="diketahui[]" value="Lainnya3" class="text-green-600 border-gray-300 rounded focus:ring-green-500">
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- Catatan / Arahan -->
                    <div>
                        <label for="instruksi" class="block font-semibold text-gray-700 mb-2">Catatan/Arahan</label>
                        <textarea id="instruksi" name="instruksi" rows="4" class="w-full border-gray-300 rounded-lg p-2 bg-gray-50 text-gray-900" placeholder="Tuliskan catatan arahan di sini..."></textarea>
                    </div>

                    <div class="pt-6 flex justify-end gap-3 border-t border-gray-100">
                        <a href="{{ route('surat-masuk.index') }}" class="bg-white py-2 px-4 border border-gray-300 rounded-lg shadow-sm font-medium text-gray-700 hover:bg-gray-50">
                            Batal
                        </a>
                        <button type="submit" class="inline-flex justify-center py-2 px-6 border border-transparent shadow-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700">
                            Simpan Lembar Disposisi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
