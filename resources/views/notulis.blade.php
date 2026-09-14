@extends('layouts.app')

@section('title', 'Notula - Sikeren')
@section('header_title', 'Notula')

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="mb-8">
        <h2 class="text-xl font-bold text-gray-800">Daftar Notula Rapat</h2>
        <p class="text-gray-500 text-sm mt-1">Kelola dan lihat dokumen notula, undangan, serta daftar hadir untuk setiap kegiatan.</p>
    </div>

    <!-- Table Section -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
            <h3 class="text-lg font-bold text-gray-800">Dokumen Kegiatan</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-1/3">Topik</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tanggal</th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Undangan</th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Daftar Hadir</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse ($kegiatans as $post)
                    <tr class="hover:bg-blue-50/50 transition-colors">
                        <td class="px-6 py-4">
                            <a href="{{ url('lihatkegiatan/' . $post->text) }}" class="text-sm font-medium text-blue-600 hover:text-blue-800 hover:underline line-clamp-2">
                                {{ $post->text }}
                            </a>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $post->start_date }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            @if($post->undangan_forum)
                            <a href="{{ url('public/documents/'. $post->undangan_forum) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 text-blue-600 hover:bg-blue-200 transition-colors" title="Download Undangan">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                            </a>
                            @else
                            <span class="text-gray-300">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            @if($post->daftar_hadir)
                            <a href="{{ url('public/documents/'. $post->daftar_hadir) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-green-100 text-green-600 hover:bg-green-200 transition-colors" title="Download Daftar Hadir">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                            </a>
                            @else
                            <span class="text-gray-300">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                Belum Diperiksa
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-400">Tidak ada data notula.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
