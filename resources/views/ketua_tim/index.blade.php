@extends('layouts.app')

@section('title', 'Ketua Tim - Sikeren')
@section('header_title', 'Ketua Tim')

@section('content')

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">


    {{-- =====================================================
        HEADER
    ====================================================== --}}

    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">

        <div>

            <h2 class="text-xl font-bold text-gray-800">
                Dashboard Ketua Tim
            </h2>

            <p class="text-gray-500 text-sm mt-1">
                Pantau alur penugasan dan koordinasi anggota tim secara real-time.
            </p>

        </div>


        <a href="{{ route('ketua-tim.create') }}"
           class="inline-flex items-center justify-center
                  px-6 py-3 bg-indigo-600 hover:bg-indigo-700
                  font-bold rounded-2xl
                  shadow-lg shadow-indigo-200
                  transition-all transform hover:scale-105">

           <svg class="w-5 h-5 mr-2 flex-shrink-0" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
    <circle cx="12" cy="12" r="9.5" fill="#ECFDF5" stroke="#10B981" stroke-width="1.8"/>
    <path d="M12 7.5V16.5M7.5 12H16.5" stroke="#059669" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
</svg>

            Buat Kegiatan Baru

        </a>

    </div>


    {{-- =====================================================
        SUCCESS MESSAGE
    ====================================================== --}}

    @if(session('success'))

        <div class="flex items-center gap-3
                    px-5 py-4
                    bg-emerald-50
                    border border-emerald-100
                    rounded-2xl
                    text-emerald-700 mt-5">

            <div class="w-10 h-10
                        flex items-center justify-center
                        bg-emerald-100
                        rounded-xl
                        flex-shrink-0">

                <svg class="w-5 h-5"
                     fill="none"
                     stroke="currentColor"
                     viewBox="0 0 24 24">

                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M5 13l4 4L19 7"/>

                </svg>

            </div>

            <div>

                <p class="text-sm font-bold">
                    Berhasil!
                </p>

                <p class="text-xs text-emerald-600">
                    {{ session('success') }}
                </p>

            </div>

        </div>

    @endif


    {{-- =====================================================
        STATISTIK
    ====================================================== --}}

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mt-5">


        {{-- TOTAL --}}

       <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex items-center hover:shadow-md transition-shadow">
            <div class="w-14 h-14 rounded-full bg-purple-100 flex items-center justify-center text-purple-600 mr-4">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
            </div>
            <div>
                <p class="text-sm font-semibold text-gray-500 mb-1">Total Kegiatan</p>
                <h3 class="text-3xl font-bold text-gray-800">{{ $totalAgenda }}</h3>
            </div>
        </div>


        {{-- TERJADWAL --}}

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex items-center hover:shadow-md transition-shadow">

    <div class="w-14 h-14 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 mr-4">

        <!-- Icon Kalender -->
        <svg class="w-8 h-8"
             fill="none"
             viewBox="0 0 24 24"
             stroke="currentColor">

            <!-- Kotak kalender -->
            <rect x="3" y="5"
                  width="18"
                  height="16"
                  rx="2"
                  stroke-width="1.8"/>

            <!-- Garis bagian atas -->
            <path stroke-linecap="round"
                  stroke-width="1.8"
                  d="M3 10h18"/>

            <!-- Ring kalender -->
            <path stroke-linecap="round"
                  stroke-width="1.8"
                  d="M8 3v4M16 3v4"/>

            <!-- Tanggal -->
            <path stroke-linecap="round"
                  stroke-width="1.8"
                  d="M8 14h.01M12 14h.01M16 14h.01M8 17h.01M12 17h.01"/>

        </svg>

    </div>

    <div>
        <p class="text-sm font-semibold text-gray-500 mb-1">
            Terjadwal
        </p>

        <h3 class="text-3xl font-bold text-gray-800">
            {{ $agendaTerjadwal }}
        </h3>
    </div>

</div>


        {{-- SELESAI --}}

       <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex items-center hover:shadow-md transition-shadow">

    <div class="w-14 h-14 rounded-full bg-green-100 flex items-center justify-center text-green-600 mr-4">

        <!-- Icon Selesai -->
        <svg class="w-8 h-8"
             fill="none"
             viewBox="0 0 24 24"
             stroke="currentColor">

            <!-- Lingkaran -->
            <circle cx="12"
                    cy="12"
                    r="9"
                    stroke-width="1.8"/>

            <!-- Tanda Centang -->
            <path stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M8 12.5l2.5 2.5L16.5 9"/>

        </svg>

    </div>

    <div>
        <p class="text-sm font-semibold text-gray-500 mb-1">
            Selesai
        </p>

        <h3 class="text-3xl font-bold text-gray-800">
            {{ $agendaSelesai }}
        </h3>
    </div>

</div>

    </div>

    </div>


    {{-- =====================================================
        DAFTAR KEGIATAN
    ====================================================== --}}

    {{-- =====================================================
    TABEL KEGIATAN
====================================================== --}}

<div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden mt-4">

    {{-- Header tabel --}}
    <div class="px-6 py-5 border-b border-gray-100 rounded-5xl">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">

            <div>
                <h2 class="text-lg font-black text-gray-900">
                    Daftar Kegiatan
                </h2>

                <p class="text-sm text-gray-400 mt-1">
                    Daftar kegiatan yang dibuat oleh Ketua Tim
                </p>
            </div>

            {{-- Search --}}
            <div class="relative w-full md:w-72 mt-4 ml-3">
                <input
                    type="text"
                    id="searchKegiatan"
                    placeholder="Cari kegiatan..."
                    class="w-full pl-11 pr-4 py-3 rounded-xl
                           border border-gray-200
                           text-sm text-gray-700
                           focus:outline-none
                           focus:ring-2 focus:ring-indigo-500
                           focus:border-transparent">
            </div>

        </div>
    </div>


    {{-- Tabel --}}
    <div class="overflow-x-auto">

        <table class="w-full text-sm text-left">

            {{-- Header --}}
            <thead class="bg-gray-50 border-b border-gray-100">

                <tr class="text-gray-500 uppercase text-xs tracking-wider">

                    <th class="px-6 py-4 font-bold">
                        No
                    </th>

                    <th class="px-6 py-4 font-bold min-w-[240px]">
                        Kegiatan
                    </th>

                    <th class="px-6 py-4 font-bold min-w-[150px]">
                        PJ Kegiatan
                    </th>

                    <th class="px-6 py-4 font-bold min-w-[180px]">
                        Jadwal
                    </th>

                    <th class="px-6 py-4 font-bold min-w-[150px]">
                        Lokasi
                    </th>

                    <th class="px-6 py-4 font-bold text-center">
                        Status
                    </th>

                    <th class="px-6 py-4 font-bold text-center min-w-[150px]">
                        Aksi
                    </th>

                </tr>

            </thead>


            {{-- Body --}}
            <tbody id="tabelKegiatan" class="divide-y divide-gray-100">

                @forelse($agendaKetuaTim as $index => $agenda)

                    <tr class="hover:bg-gray-50/70 transition-colors">

                        {{-- No --}}
                        <td class="px-6 py-5 text-gray-400 font-semibold">
                            {{ $index + 1 }}
                        </td>


                        {{-- Kegiatan --}}
                        <td class="px-6 py-5">

                            <div class="flex items-start gap-3">

                                <div class="w-10 h-10 rounded-xl
                                            bg-indigo-50
                                            flex items-center justify-center
                                            flex-shrink-0">

                                    <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
    <rect x="3" y="5" width="18" height="16" rx="3" fill="#FFFBEB" stroke="#F59E0B" stroke-width="1.8"/>
    <path d="M3 10H21" stroke="#F59E0B" stroke-width="1.8"/>
    <path d="M8 3V6M16 3V6" stroke="#D97706" stroke-width="2" stroke-linecap="round"/>
    <rect x="7" y="13" width="3" height="3" rx="0.8" fill="#3B82F6"/>
    <rect x="14" y="13" width="3" height="3" rx="0.8" fill="#F59E0B"/>
</svg>

                                </div>


                                <div>

                                    <p class="font-bold text-gray-900">
                                        {{ $agenda->agenda }}
                                    </p>

                                    <p class="text-xs text-gray-400 mt-1">
                                        {{ $agenda->sub_kegiatan ?: 'Sub kegiatan belum diatur' }}
                                    </p>

                                </div>

                            </div>

                        </td>


                        {{-- PJ --}}
                        <td class="px-6 py-5">

                            @if($agenda->pj)

                                <div class="flex items-center gap-2">

                                    <div class="w-8 h-8 rounded-full
                                                bg-indigo-100
                                                flex items-center justify-center
                                                text-indigo-700
                                                text-xs font-bold">

                                        {{ strtoupper(substr($agenda->pj, 0, 1)) }}

                                    </div>

                                    <span class="font-semibold text-gray-700">
                                        {{ $agenda->pj }}
                                    </span>

                                </div>

                            @else

                                <span class="text-gray-400">
                                    Belum Ditunjuk
                                </span>

                            @endif

                        </td>


                        {{-- Jadwal --}}
                        <td class="px-6 py-5">

                            <div class="space-y-1">

                                <div class="flex items-center gap-2">

                                    <svg class="w-4 h-4 flex-shrink-0" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
    <rect x="3" y="6" width="18" height="15" rx="3" fill="#F0F9FF" stroke="#0284C7" stroke-width="1.8"/>
    <path d="M3 9C3 7.34315 4.34315 6 6 6H18C19.6569 6 21 7.34315 21 9V10H3V9Z" fill="#0284C7"/>
    <path d="M8 3V6M16 3V6" stroke="#0369A1" stroke-width="2" stroke-linecap="round"/>
    <circle cx="9" cy="15" r="1.2" fill="#F59E0B"/>
    <circle cx="15" cy="15" r="1.2" fill="#0284C7"/>
</svg>

                                    <span class="font-semibold text-gray-700 ml-2">
                                        {{ $agenda->tanggal ?: '-' }}
                                    </span>

                                </div>


                                @if($agenda->jam)

                                    <div class="flex items-center gap-2">

                                       <svg class="w-4 h-4 flex-shrink-0" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
    <circle cx="12" cy="12" r="9" fill="#EEF2FF" stroke="#6366F1" stroke-width="2"/>
    <path d="M12 7.5V12L14.5 13.5" stroke="#EC4899" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
    <circle cx="12" cy="12" r="1.2" fill="#4F46E5"/>
</svg>

                                        <span class="text-gray-500 ml-2">
                                            {{ $agenda->jam }}
                                        </span>

                                    </div>

                                @endif

                            </div>

                        </td>


                        {{-- Lokasi --}}
                        <td class="px-6 py-5">

                            <div class="flex items-center gap-2">

                                <svg class="w-5 h-5 flex-shrink-0" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
    <!-- Badan Pin Merah -->
    <path d="M12 21.5C12 21.5 19 15.3 19 9.5C19 5.634 15.866 2.5 12 2.5C8.134 2.5 5 5.634 5 9.5C5 15.3 12 21.5 12 21.5Z" fill="#EF4444" stroke="#DC2626" stroke-width="1.5"/>
    <!-- Lingkaran Titik Putih -->
    <circle cx="12" cy="9.5" r="3" fill="#FFFFFF"/>
</svg>

                                <span class="text-gray-600 ml-2">
                                    {{ $agenda->tempat ?: 'TBD' }}
                                </span>

                            </div>

                        </td>


                        {{-- Status --}}
                        <td class="px-6 py-5 text-center">

                            @if($agenda->status === 'selesai')

                                <span class="inline-flex items-center gap-2
                                             px-3 py-1.5
                                             rounded-full
                                             bg-green-50
                                             text-green-700
                                             text-xs font-bold">

                                    <span class="w-2 h-2 rounded-full bg-green-500"></span>

                                    Selesai

                                </span>

                            @else

                                <span class="inline-flex items-center gap-2
                                             px-3 py-1.5
                                             rounded-full
                                             bg-blue-50
                                             text-blue-700
                                             text-xs font-bold">

                                    <span class="w-2 h-2 rounded-full bg-blue-500"></span>

                                    Terjadwal

                                </span>

                            @endif

                        </td>


                        {{-- Aksi --}}
                        <td class="px-6 py-5">

                            <div class="flex items-center justify-center gap-2">

                                {{-- Detail --}}
<a href="{{ route('ketua-tim.show', $agenda->id) }}"
   class="inline-flex items-center justify-center
          w-9 h-9
          rounded-xl
          text-gray-600
          hover:bg-indigo-50
          hover:text-indigo-600
          transition"
   title="Detail">

    <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
    <path d="M6 3H15L19 7V20C19 20.5523 18.5523 21 18 21H6C5.44772 21 5 20.5523 5 20V4C5 3.44772 5.44772 3 6 3Z" fill="#F8FAFC" stroke="#3B82F6" stroke-width="1.8" stroke-linejoin="round"/>
    <path d="M15 3V7H19" fill="#DBEAFE" stroke="#3B82F6" stroke-width="1.8" stroke-linejoin="round"/>
    <path d="M8.5 11.5H15.5" stroke="#F59E0B" stroke-width="2" stroke-linecap="round"/>
    <path d="M8.5 15H15.5" stroke="#8B5CF6" stroke-width="2" stroke-linecap="round"/>
    <path d="M8.5 18H12" stroke="#10B981" stroke-width="2" stroke-linecap="round"/>
</svg>
</a>


                                {{-- Edit --}}
<a href="{{ route('ketua-tim.edit', $agenda->id) }}"
   class="inline-flex items-center justify-center
          w-9 h-9
          rounded-xl
          text-gray-600
          hover:bg-yellow-50
          hover:text-yellow-600
          transition"
   title="Edit">

    <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
    <path d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125" fill="#FDE68A" stroke="#F59E0B" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
    <path d="M19.5 7.125L16.862 4.487" stroke="#D97706" stroke-width="1.8" stroke-linecap="round"/>
</svg>
</a>

                                {{-- Hapus --}}
        <form action="{{ route('ketua-tim.destroy', $agenda->id) }}" 
              method="POST" 
              onsubmit="return confirm('Apakah Anda yakin ingin menghapus kegiatan {{ $agenda->agenda }}?');"
              class="inline-block">
            @csrf
            @method('DELETE')
            <button type="submit"
                    class="inline-flex items-center justify-center
                           w-9 h-9 rounded-xl text-gray-600
                           hover:bg-red-50 hover:text-red-600 transition cursor-pointer"
                    title="Hapus Kegiatan">
                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
    <path d="M19 7L18.133 19.142C18.058 20.189 17.187 21 16.138 21H7.862C6.813 21 5.942 20.189 5.867 19.142L5 7M10 11V17M14 11V17M15 7V4C15 3.448 14.552 3 14 3H10C9.448 3 9 3.448 9 4V7M4 7H20" fill="#FFE4E6" stroke="#F43F5E" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
            </button>
        </form>


                            </div>

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td colspan="7" class="px-6 py-16 text-center">

                            <div class="flex flex-col items-center">

                                <div class="w-16 h-16 rounded-2xl
                                            bg-gray-100
                                            flex items-center justify-center
                                            mb-4">

                                    <svg
                                        class="w-8 h-8 text-gray-400"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24">

                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                        </path>

                                    </svg>

                                </div>

                                <h3 class="font-bold text-gray-700">
                                    Belum Ada Kegiatan
                                </h3>

                                <p class="text-sm text-gray-400 mt-1">
                                    Kegiatan yang diterbitkan akan muncul di sini.
                                </p>

                            </div>

                        </td>

                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

</div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const searchInput = document.getElementById('searchKegiatan');
    const rows = document.querySelectorAll('#tabelKegiatan tr');

    if (!searchInput) return;

    searchInput.addEventListener('keyup', function () {

        const keyword = this.value.toLowerCase().trim();

        rows.forEach(function (row) {

            const text = row.innerText.toLowerCase();

            if (text.includes(keyword)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }

        });

    });

});
</script>

@endsection