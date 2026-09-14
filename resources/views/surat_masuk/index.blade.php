@extends('layouts.app')

@section('title', 'Daftar Surat Masuk - Sikeren')
@section('header_title', 'Surat Masuk')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">

    <div class="flex justify-between items-center mb-8">
        <div>
            <h2 class="text-xl font-bold text-gray-800">Daftar Surat Masuk</h2>
            <p class="text-gray-500 text-sm mt-1">Daftar surat masuk yang perlu ditindaklanjuti.</p>
        </div>
        <a href="{{ route('surat-masuk.create') }}" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700">
            + Tambah Surat Masuk
        </a>
    </div>

    @if (session('success'))
    <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6 rounded-r">
        <div class="flex">
            <div class="ml-3">
                <p class="text-sm text-green-700 font-medium">{{ session('success') }}</p>
            </div>
        </div>
    </div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No Surat</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Asal Surat</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Perihal</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status Disposisi</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($suratMasuks as $surat)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">{{ $surat->nomor_surat }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ \Carbon\Carbon::parse($surat->tanggal_surat)->format('d M Y') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $surat->asal_surat }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $surat->perihal }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-center space-y-1">
                            @if($surat->disposisis->count() > 0)
                                <span class="px-2 block inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">Didisposisi</span>
                            @else
                                <span class="px-2 block inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Belum Disposisi</span>
                            @endif
                            @php $kegiatanCount = \App\Task::where('surat_id', $surat->id)->count(); @endphp
                            <span class="px-2 block inline-flex items-center rounded-full text-xs font-medium bg-purple-100 text-purple-800 mt-1">
                                {{ $kegiatanCount }} Kegiatan
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                            <a href="{{ url('penugasan?surat_id='.$surat->id) }}" class="text-green-600 hover:text-green-900 bg-green-50 px-3 py-1 rounded-md border border-green-200 shadow-sm" title="Buat Kegiatan">Buat Kegiatan</a>
                            @if($surat->disposisis->count() > 0)
                                <a href="{{ route('disposisi.show', $surat->disposisis->last()->id) }}" class="text-blue-600 hover:text-blue-900 bg-blue-50 px-3 py-1 rounded-md border border-blue-200 shadow-sm" title="Lihat Lembar Disposisi">Lihat Disposisi</a>
                            @else
                                <a href="{{ route('disposisi.create', $surat->id) }}" class="text-gray-600 hover:text-gray-900 bg-gray-50 px-3 py-1 rounded-md border border-gray-200 shadow-sm" title="Buat Lembar Disposisi">Buat Disposisi</a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-sm text-gray-500">Belum ada data surat masuk.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
