@extends('layouts.app')

@section('title', 'Detail Kegiatan - Sikeren')
@section('header_title', 'Detail Kegiatan')

@section('content')

<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

    {{-- =====================================================
        TOP BAR & NAVIGATION
    ====================================================== --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">

        <div class="flex items-center gap-4">
           
            <div>
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold px-3 py-1 rounded-full 
                        {{ $agenda->status === 'selesai' ? 'bg-emerald-50 text-emerald-600' : 'bg-blue-50 text-blue-600' }}">
                        ● {{ ucfirst($agenda->status) }}
                    </span>
                    <span class="text-xs text-gray-400">ID: #{{ $agenda->id }}</span>
                </div>
                <h2 class="text-2xl font-black text-gray-900 mt-1">
                    Detail Kegiatan
                </h2>
            </div>
        </div>

        {{-- Action Buttons --}}
       <div class="flex items-center gap-3">
    <a href="{{ route('ketua-tim.index') }}"
       class="inline-flex items-center justify-center
              px-6 py-3 bg-indigo-600 hover:bg-indigo-700
              font-bold rounded-2xl
              shadow-lg shadow-indigo-200
              transition-all transform hover:scale-105 active:scale-95">

        {{-- Ikon Panah Kembali --}}
        <svg class="w-5 h-5 mr-2 flex-shrink-0" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
    <circle cx="12" cy="12" r="9.5" fill="#E0F2FE" stroke="#38BDF8" stroke-width="1.5"/>
    <path d="M15 12H9M9 12L12 9M9 12L12 15" stroke="#0284C7" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
</svg>

        Kembali

    </a>
</div>

    </div>


    {{-- =====================================================
        HERO CARD: INFORMASI UTAMA
    ====================================================== --}}
    <div class="bg-white rounded-[2.5rem] p-8 shadow-sm border border-gray-100 relative overflow-hidden mt-5">
        
        <div class="absolute top-0 left-0 w-3 h-full {{ $agenda->status === 'selesai' ? 'bg-emerald-500' : 'bg-indigo-600' }}"></div>

        <div class="flex flex-col md:flex-row md:items-start justify-between gap-6">

            <div class="space-y-3">
                <span class="text-xs font-bold text-indigo-600 uppercase tracking-wider bg-indigo-50 px-3 py-1.5 rounded-xl inline-block">
                    Agenda Utama
                </span>
                
                <h1 class="text-2xl sm:text-3xl font-black text-gray-900 leading-tight">
                    {{ $agenda->agenda }}
                </h1>

                @if($agenda->sub_kegiatan)
                    <p class="text-gray-500 text-base font-medium">
                        Sub Kegiatan: <span class="text-gray-800 font-semibold">{{ $agenda->sub_kegiatan }}</span>
                    </p>
                @endif
            </div>

            <div class="flex-shrink-0 bg-gray-50/90 px-6 py-3.5 rounded-full border border-gray-100 shadow-sm min-w-[220px]">
    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider mt-2">
        Perihal / Judul Surat
    </p>
    <p class="text-sm font-bold text-gray-800 mt-0.5">
        {{ $agenda->perihal ?: '-' }}
    </p>
</div>

        </div>

    </div>


    {{-- =====================================================
        GRID INFORMASI DETAIL
    ====================================================== --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

        {{-- CARD KIRI: WAKTU & LOKASI --}}
        <div class="bg-white rounded-[2.5rem] p-8 shadow-sm border border-gray-100 space-y-6 mt-4">

            <div class="flex items-center gap-3 pb-4 border-b border-gray-100">
                <div class="w-10 h-10 rounded-2xl bg-orange-50 text-orange-500 flex items-center justify-center">
                    <svg class="w-5 h-5 flex-shrink-0" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
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
                <h3 class="text-lg font-bold text-gray-800">Waktu & Tempat</h3>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                
                {{-- Tanggal --}}
                <div class="p-4 rounded-2xl bg-gray-50/80 border border-gray-100 mr-3">
                    <p class="text-xs text-gray-400 font-semibold">Tanggal Pelaksanaan</p>
                    <p class="text-sm font-bold text-gray-800 mt-1">
                        {{ $agenda->tanggal ? \Carbon\Carbon::parse($agenda->tanggal)->translatedFormat('d F Y') : '-' }}
                    </p>
                </div>

                {{-- Jam --}}
                <div class="p-4 rounded-2xl bg-gray-50/80 border border-gray-100">
                    <p class="text-xs text-gray-400 font-semibold">Waktu Pelaksanaan</p>
                    <p class="text-sm font-bold text-gray-800 mt-1">
                        {{ $agenda->jam ?: '-' }} WITA
                    </p>
                </div>

                {{-- Tempat --}}
                <div class="p-4 rounded-2xl bg-gray-50/80 border border-gray-100 mt-3 mr-3">
                    <p class="text-xs text-gray-400 font-semibold">Opsi Tempat</p>
                    <p class="text-sm font-bold text-gray-800 mt-1">
                        {{ $agenda->tempat ?: '-' }}
                    </p>
                </div>

                {{-- Tujuan --}}
                <div class="p-4 rounded-2xl bg-gray-50/80 border border-gray-100 mt-3">
                    <p class="text-xs text-gray-400 font-semibold">Kabupaten / Kota</p>
                    <p class="text-sm font-bold text-gray-800 mt-1">
                        {{ $agenda->tujuan ?: '-' }}
                    </p>
                </div>

            </div>

            {{-- Dasar Pelaksanaan --}}
            @if($agenda->dasar)
                <div class="pt-4 border-t border-gray-100">
                    <p class="text-xs text-gray-400 font-semibold mb-1">Dasar / Referensi</p>
                    <p class="text-xs text-gray-700 bg-gray-50 p-3.5 rounded-2xl border border-gray-100 break-words">
                        {{ $agenda->dasar }}
                    </p>
                </div>
            @endif

        </div>


        {{-- CARD KANAN: PENUGASAN & ANGGOTA TIM --}}
        <div class="bg-white rounded-[2.5rem] p-8 shadow-sm border border-gray-100 space-y-6 flex flex-col justify-between mt-4">

            <div>
                <div class="flex items-center gap-3 pb-4 border-b border-gray-100">
                    <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <svg class="w-5 h-5 flex-shrink-0" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
    <!-- Anggota Tim di Belakang (Cyan/Sky) -->
    <circle cx="16" cy="7.5" r="3" fill="#E0F2FE" stroke="#0284C7" stroke-width="1.8"/>
    <path d="M14 14.5C14.7 14 15.8 13.7 17 13.7C19.2 13.7 21 15.3 21 17.5V19.5" stroke="#0284C7" stroke-width="1.8" stroke-linecap="round"/>
    
    <!-- Anggota Tim Utama di Depan (Emerald Green) -->
    <circle cx="9" cy="8" r="3.5" fill="#ECFDF5" stroke="#10B981" stroke-width="2"/>
    <path d="M3 19.5V18C3 15.2386 5.23858 13 8 13H10C12.7614 13 15 15.2386 15 18V19.5" fill="#D1FAE5" stroke="#10B981" stroke-width="2" stroke-linecap="round"/>
</svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-800">Personil & Tim</h3>
                </div>

                {{-- Penanggung Jawab (PJ) --}}
                <div class="mt-5 p-5 rounded-2xl bg-indigo-50/50 border border-indigo-100 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-indigo-600 text-white font-black text-lg flex items-center justify-center shadow-md shadow-indigo-200">
                        {{ strtoupper(substr($agenda->pj ?? 'P', 0, 1)) }}
                    </div>
                   <div class="text-left space-y-0.5">
    <p class="text-[13px] font-bold text-indigo-600 uppercase tracking-wider">
        Penanggung Jawab (PJ)
    </p>
    <h4 class="text-base font-bold text-gray-900">
        {{ $agenda->pj ?: 'Belum Ditunjuk' }}
    </h4>
</div>
                </div>

                {{-- Anggota Tim Lainnya --}}
                <div class="mt-6 space-y-3">
    <div class="flex items-center justify-between mt-3">
        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">
            Anggota Tim yang Ditugaskan
        </p>
        <span class="text-xs font-semibold text-gray-500">
            {{ count($anggotaList) }} Orang
        </span>
    </div>

    @if(count($anggotaList) > 0)
        <div class="flex flex-wrap gap-2.5 pt-1 mt-3">
            @foreach($anggotaList as $anggota)
                <div class="inline-flex items-center gap-2 px-3.5 py-2 bg-gray-50 border border-gray-100 rounded-2xl text-xs font-semibold text-gray-700 shadow-sm hover:bg-indigo-50/50 hover:border-indigo-100 transition-colors">
                    
                    {{-- Ikon SVG Orang / User --}}
                    <div class="w-6 h-6 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center flex-shrink-0">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>

                    <span>{{ $anggota }}</span>

                </div>
            @endforeach
        </div>
    @else
        <p class="text-xs text-gray-400 italic bg-gray-50 p-4 rounded-2xl border border-gray-100 mt-3">
            Tidak ada anggota tim tambahan yang ditugaskan.
        </p>
    @endif
</div>
            </div>

            {{-- Timestamp Info --}}
            <div class="pt-4 border-t border-gray-100 text-[11px] text-gray-400 flex items-center justify-between">
                <span>Diterbitkan: {{ $agenda->created_at ? \Carbon\Carbon::parse($agenda->created_at)->translatedFormat('d M Y, H:i') : '-' }}</span>
            </div>

        </div>

    </div>

</div>

@endsection