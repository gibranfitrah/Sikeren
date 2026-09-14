@extends('layouts.app')

@section('content')
<div class="p-6 h-full max-w-7xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Detail Disposisi</h1>
            <p class="text-gray-500 text-sm mt-1">Lihat rincian disposisi dari Surat Masuk #{{ $disposisi->suratMasuk->nomor_surat }}</p>
        </div>
        <a href="{{ route('surat-masuk.index') }}" class="bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-lg shadow-sm hover:bg-gray-50 text-sm font-medium">
            <i class="bi bi-arrow-left mr-2"></i> Kembali
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Kolom Kiri: Dokumen Surat (Embed Google Drive) -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden h-[800px] flex flex-col">
            <div class="p-4 bg-gray-50 border-b border-gray-100 font-semibold text-gray-700 flex justify-between items-center">
                <span>Dokumen Surat Masuk</span>
                <a href="{{ $disposisi->suratMasuk->file_surat }}" target="_blank" class="text-sm text-blue-600 hover:underline">Buka di Tab Baru <i class="bi bi-box-arrow-up-right"></i></a>
            </div>
            <div class="flex-grow w-full bg-gray-200">
                @if($disposisi->suratMasuk->file_surat)
                    <iframe src="{{ str_replace('/view?usp=sharing', '/preview', $disposisi->suratMasuk->file_surat) }}" class="w-full h-full border-0" allow="autoplay"></iframe>
                @else
                    <div class="flex items-center justify-center h-full text-gray-500">
                        Tidak ada tautan Google Drive yang dilampirkan.
                    </div>
                @endif
            </div>
        </div>

        <!-- Kolom Kanan: Detail Disposisi -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 overflow-y-auto h-[800px]">
            <div class="grid grid-cols-2 gap-4 bg-blue-50 p-4 rounded-lg border border-blue-100 mb-6 text-sm">
                <div>
                    <span class="text-gray-500 block text-xs">Tingkat Keamanan:</span>
                    <span class="font-medium text-gray-900">{{ $disposisi->tingkat_keamanan ?? '-' }}</span>
                </div>
                <div>
                    <span class="text-gray-500 block text-xs">Tgl. Penyelesaian:</span>
                    <span class="font-medium text-gray-900">{{ $disposisi->tgl_penyelesaian ? \Carbon\Carbon::parse($disposisi->tgl_penyelesaian)->format('d M Y') : '-' }}</span>
                </div>
            </div>

            <!-- Disposisi Opsi -->
            <div class="mb-6">
                <h3 class="font-semibold text-gray-700 mb-3 border-b pb-2">Opsi Disposisi</h3>
                <div class="grid grid-cols-2 gap-2 text-sm text-gray-800">
                    @php
                        $opsiDisposisi = [
                            1 => 'Edarkan', 2 => 'Mohon dihadiri/diwakili', 3 => 'Dampingi', 4 => 'Bicarakan dengan saya',
                            5 => 'Dibahas bersama', 6 => 'Dijadwalkan', 7 => 'Teliti dan tanggapi', 8 => 'Siapkan draft/bahan',
                            9 => 'Siapkan jawaban sesuai ketentuan', 10 => 'Siapkan laporan/laporkan', 11 => 'Dapat disetujui',
                            12 => 'Ditolak', 13 => 'Perbaiki', 14 => 'Untuk diselesaikan', 15 => 'Koordinasikan',
                            16 => 'Untuk menjadi perhatian', 17 => 'Tindak Lanjut', 18 => 'Untuk arsip'
                        ];
                        $selectedOpsi = $disposisi->opsi_disposisi ? json_decode($disposisi->opsi_disposisi, true) : [];
                    @endphp
                    @foreach($opsiDisposisi as $key => $opsi)
                        <div class="flex items-center">
                            @if(is_array($selectedOpsi) && in_array($key, $selectedOpsi))
                                <i class="bi bi-check-square-fill text-blue-600 mr-2 text-lg"></i>
                                <span class="font-medium">{{ $key }}. {{ $opsi }}</span>
                            @else
                                <i class="bi bi-square text-gray-300 mr-2 text-lg"></i>
                                <span class="text-gray-500">{{ $key }}. {{ $opsi }}</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Diteruskan Kepada & Diketahui -->
            <div class="mb-6">
                <div class="grid grid-cols-12 gap-2 font-semibold text-gray-700 mb-3 border-b pb-2">
                    <div class="col-span-8">Diteruskan Kepada</div>
                    <div class="col-span-4 text-center">Diketahui</div>
                </div>
                
                <div class="font-medium text-gray-700 mb-2">Tim:</div>
                <div class="grid grid-cols-1 gap-2 text-sm text-gray-800 border border-gray-200 p-3 rounded bg-gray-50">
                    @php
                        $selectedTim = $disposisi->diteruskan_kepada_tim ? json_decode($disposisi->diteruskan_kepada_tim, true) : [];
                        $selectedDiketahui = $disposisi->diketahui ? json_decode($disposisi->diketahui, true) : [];
                        
                        $filteredTim = $tim->filter(function($t) use ($selectedTim, $selectedDiketahui) {
                            return (is_array($selectedTim) && in_array($t->id, $selectedTim)) || 
                                   (is_array($selectedDiketahui) && in_array($t->id, $selectedDiketahui));
                        });
                    @endphp

                    @if($filteredTim->isEmpty())
                        <div class="text-gray-500 italic">Tidak ada tim yang dipilih</div>
                    @else
                        @foreach($filteredTim as $t)
                            <div class="grid grid-cols-12 gap-2 items-center p-1 border-b border-gray-100 last:border-b-0">
                                <div class="col-span-8 flex items-center">
                                    @if(is_array($selectedTim) && in_array($t->id, $selectedTim))
                                        <i class="bi bi-check-square-fill text-blue-600 mr-2 text-lg"></i>
                                        <span class="font-medium">{{ $t->nm_organisasi }}</span>
                                    @else
                                        <i class="bi bi-square text-gray-300 mr-2 text-lg"></i>
                                        <span class="text-gray-500">{{ $t->nm_organisasi }}</span>
                                    @endif
                                </div>
                                <div class="col-span-4 flex justify-center">
                                    @if(is_array($selectedDiketahui) && in_array($t->id, $selectedDiketahui))
                                        <i class="bi bi-check-square-fill text-green-600 text-lg"></i>
                                    @else
                                        <i class="bi bi-square text-gray-300 text-lg"></i>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>

            <!-- Lainnya -->
            <div class="mb-6">
                <div class="font-medium text-gray-700 mb-2">Lainnya:</div>
                <div class="text-sm text-gray-800 space-y-2">
                    <div class="grid grid-cols-12 gap-2 items-center">
                        <div class="col-span-8 flex items-center">
                            @if($disposisi->diteruskan_kepada === 'Kepala BPS Kabupaten/Kota Se-Sultra')
                                <i class="bi bi-check-square-fill text-blue-600 mr-2 text-lg"></i>
                                <span class="font-medium">1. Kepala BPS Kabupaten/Kota Se-Sultra</span>
                            @else
                                <i class="bi bi-square text-gray-300 mr-2 text-lg"></i>
                                <span class="text-gray-500">1. Kepala BPS Kabupaten/Kota Se-Sultra</span>
                            @endif
                        </div>
                        <div class="col-span-4 flex justify-center">
                            @if(is_array($selectedDiketahui) && in_array('Lainnya1', $selectedDiketahui))
                                <i class="bi bi-check-square-fill text-green-600 text-lg"></i>
                            @else
                                <i class="bi bi-square text-gray-300 text-lg"></i>
                            @endif
                        </div>
                    </div>
                    
                    @if($disposisi->diteruskan_kepada_lainnya && $disposisi->diteruskan_kepada_lainnya !== 'Kepala BPS Kabupaten/Kota Se-Sultra')
                    <div class="grid grid-cols-12 gap-2 items-center mt-2">
                        <div class="col-span-8 flex items-center">
                            <i class="bi bi-check-square-fill text-blue-600 mr-2 text-lg"></i>
                            <span class="font-medium">2. {{ $disposisi->diteruskan_kepada_lainnya }}</span>
                        </div>
                        <div class="col-span-4 flex justify-center">
                            @if(is_array($selectedDiketahui) && in_array('Lainnya2', $selectedDiketahui))
                                <i class="bi bi-check-square-fill text-green-600 text-lg"></i>
                            @else
                                <i class="bi bi-square text-gray-300 text-lg"></i>
                            @endif
                        </div>
                    </div>
                    @else
                    <div class="grid grid-cols-12 gap-2 items-center mt-2">
                        <div class="col-span-8 flex items-center">
                            <i class="bi bi-square text-gray-300 mr-2 text-lg"></i>
                            <span class="text-gray-500">2. ................................................................</span>
                        </div>
                        <div class="col-span-4 flex justify-center">
                            @if(is_array($selectedDiketahui) && in_array('Lainnya2', $selectedDiketahui))
                                <i class="bi bi-check-square-fill text-green-600 text-lg"></i>
                            @else
                                <i class="bi bi-square text-gray-300 text-lg"></i>
                            @endif
                        </div>
                    </div>
                    @endif
                    
                    <div class="grid grid-cols-12 gap-2 items-center mt-2">
                        <div class="col-span-8 flex items-center">
                            <i class="bi bi-square text-gray-300 mr-2 text-lg"></i>
                            <span class="text-gray-500">3. ................................................................</span>
                        </div>
                        <div class="col-span-4 flex justify-center">
                            @if(is_array($selectedDiketahui) && in_array('Lainnya3', $selectedDiketahui))
                                <i class="bi bi-check-square-fill text-green-600 text-lg"></i>
                            @else
                                <i class="bi bi-square text-gray-300 text-lg"></i>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Catatan / Arahan -->
            <div>
                <h3 class="font-semibold text-gray-700 mb-2 border-b pb-2">Catatan/Arahan</h3>
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 text-sm text-gray-800 min-h-[100px] whitespace-pre-wrap">
{{ $disposisi->instruksi ?? 'Tidak ada catatan khusus.' }}
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
