@extends('layouts.app')

@section('title', 'Detail Rapat - Sikeren')
@section('header_title', 'Detail Rapat')

@section('content')
@php
    $firstItem = $kegiatans->first() ?? $task;
    $rapatText = $firstItem->text ?? ($task->text ?? 'Rapat');
    $rapatAgenda = $firstItem->agenda ?? ($task->agenda ?? '-');
    $rapatTempat = $firstItem->tempat ?? ($task->tempat ?? 'Aula / Kantor BPS');
    $rapatStart = $firstItem->start_date ?? ($task->start_date ?? date('Y-m-d'));
    $rapatEnd = $firstItem->date_akhir ?? ($task->date_akhir ?? $rapatStart);
    $rapatJamMulai = $firstItem->start_jam ?? ($task->start_jam ?? '09:00');
    $rapatJamSelesai = $firstItem->end_jam ?? ($task->end_jam ?? '12:00');
    $rapatPemimpin = $firstItem->pemimpin ?? ($task->pemimpin ?? '-');
    $rapatNotulis = $firstItem->notulis ?? ($task->notulis ?? '-');
    $rapatDokumentasi = $firstItem->tim_dokumentasi ?? ($task->tim_dokumentasi ?? '-');
    $rapatSetuju = $firstItem->setuju_rapat ?? ($task->setuju_rapat ?? 0);
    $rapatNotulen = $firstItem->notulen ?? ($task->notulen ?? null);
    $rapatMateriLink = $firstItem->materi_link ?? ($task->materi_link ?? null);
    $rapatFotoLink = $firstItem->foto_link ?? ($task->foto_link ?? null);
    $rapatSuratLink = $firstItem->surat ?? ($task->surat ?? null);
    $rapatId = $firstItem->id ?? ($task->id ?? $id);

    $currentUserNama = trim(Auth::user()->nama_lengkap ?? (Auth::user()->name ?? ''));
    $currentUserNip = trim(Auth::user()->niplama ?? '');
    $currentUserNipBaru = trim(Auth::user()->nipbaru ?? '');
    $isAdmin = Auth::check() && isset(Auth::user()->level) && strtolower(Auth::user()->level) === 'admin';
    $rapatPj = $firstItem->penanggung_jawab ?? ($task->penanggung_jawab ?? '');

    $isPemimpin = Auth::check() && (
        strcasecmp($currentUserNama, trim($rapatPemimpin ?? '')) === 0 ||
        $currentUserNip === trim($rapatPemimpin ?? '') ||
        $isAdmin
    );
    $isNotulis = Auth::check() && (
        strcasecmp($currentUserNama, trim($rapatNotulis ?? '')) === 0 ||
        strcasecmp($currentUserNama, trim($rapatDokumentasi ?? '')) === 0 ||
        strcasecmp($currentUserNama, trim($rapatPj ?? '')) === 0 ||
        $currentUserNip === trim($rapatNotulis ?? '') ||
        $currentUserNip === trim($rapatDokumentasi ?? '') ||
        $currentUserNip === trim($rapatPj ?? '') ||
        $isPemimpin ||
        $isAdmin
    );
@endphp

<style>
    /* Scoped Styles for Detail Rapat Layout */
    .approval-container {
        padding: 20px 24px !important;
        border-radius: 18px !important;
        border: 1px solid !important;
        display: flex !important;
        flex-direction: row !important;
        align-items: center !important;
        justify-content: space-between !important;
        gap: 16px !important;
        flex-wrap: wrap !important;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.03) !important;
    }
    .approval-waiting {
        background-color: #fffbeb !important;
        border-color: #fde68a !important;
    }
    .approval-approved {
        background-color: #f0fdf4 !important;
        border-color: #bbf7d0 !important;
    }
    .approval-rejected {
        background-color: #fef2f2 !important;
        border-color: #fecaca !important;
    }
    .stage-pill {
        display: inline-flex !important;
        align-items: center !important;
        gap: 6px !important;
        padding: 8px 16px !important;
        border-radius: 9999px !important;
        font-size: 12px !important;
        font-weight: 700 !important;
        white-space: nowrap !important;
        border: 1px solid transparent !important;
    }
    .stage-pill-waiting {
        background-color: #fef3c7 !important;
        color: #92400e !important;
        border-color: #fde68a !important;
    }
    .stage-pill-approved {
        background-color: #dcfce7 !important;
        color: #166534 !important;
        border-color: #bbf7d0 !important;
    }
    .stage-pill-rejected {
        background-color: #fee2e2 !important;
        color: #991b1b !important;
        border-color: #fecaca !important;
    }
    .stage-card-panel {
        padding: 24px !important;
        border-radius: 20px !important;
        background-color: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.04) !important;
        display: flex !important;
        flex-direction: column !important;
        justify-content: space-between !important;
    }
    .ui-icon-xs {
        width: 14px !important;
        height: 14px !important;
        min-width: 14px !important;
        flex-shrink: 0 !important;
        display: inline-block !important;
        vertical-align: middle !important;
    }
    .ui-icon-sm {
        width: 16px !important;
        height: 16px !important;
        min-width: 16px !important;
        flex-shrink: 0 !important;
        display: inline-block !important;
        vertical-align: middle !important;
    }
    .ui-icon-md {
        width: 20px !important;
        height: 20px !important;
        min-width: 20px !important;
        flex-shrink: 0 !important;
        display: inline-block !important;
        vertical-align: middle !important;
    }
    .ui-icon-lg {
        width: 24px !important;
        height: 24px !important;
        min-width: 24px !important;
        flex-shrink: 0 !important;
        display: inline-block !important;
        vertical-align: middle !important;
    }
    .btn-approve {
        background-color: #059669 !important; /* Emerald 600 */
        color: #ffffff !important;
        border: 1px solid #047857 !important;
        padding: 9px 20px !important;
        border-radius: 12px !important;
        font-weight: 700 !important;
        font-size: 12px !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 6px !important;
        box-shadow: 0 2px 4px rgba(5, 150, 105, 0.25) !important;
        cursor: pointer !important;
        text-decoration: none !important;
        transition: all 0.2s ease !important;
    }
    .btn-approve:hover {
        background-color: #047857 !important;
        box-shadow: 0 4px 8px rgba(5, 150, 105, 0.35) !important;
    }
    .btn-reject {
        background-color: #ffffff !important;
        color: #dc2626 !important;
        border: 1.5px solid #fca5a5 !important;
        padding: 9px 18px !important;
        border-radius: 12px !important;
        font-weight: 700 !important;
        font-size: 12px !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 6px !important;
        cursor: pointer !important;
        text-decoration: none !important;
        transition: all 0.2s ease !important;
    }
    .btn-reject:hover {
        background-color: #fef2f2 !important;
        border-color: #ef4444 !important;
    }
    .btn-primary-action {
        background-color: #2563eb !important;
        color: #ffffff !important;
        border: 1px solid #1d4ed8 !important;
        padding: 8px 16px !important;
        border-radius: 10px !important;
        font-weight: 700 !important;
        font-size: 12px !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 6px !important;
        text-decoration: none !important;
        cursor: pointer !important;
        transition: all 0.2s ease !important;
    }
    .btn-primary-action:hover {
        background-color: #1d4ed8 !important;
    }
    .btn-secondary-action {
        background-color: #ffffff !important;
        color: #374151 !important;
        border: 1px solid #d1d5db !important;
        padding: 8px 16px !important;
        border-radius: 10px !important;
        font-weight: 600 !important;
        font-size: 12px !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 6px !important;
        text-decoration: none !important;
        cursor: pointer !important;
        transition: all 0.2s ease !important;
    }
    .btn-secondary-action:hover {
        background-color: #f9fafb !important;
    }
</style>

<div class="max-w-6xl mx-auto space-y-6 pb-12">

    {{-- ==========================================
        PAGE HEADER & BACK NAVIGATION
    =========================================== --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ url('/daftar_kegiatan') }}" 
               class="p-2.5 bg-white hover:bg-gray-100 text-gray-600 rounded-xl border border-gray-200 shadow-xs transition-colors" 
               title="Kembali ke Daftar Kegiatan">
                <svg class="ui-icon-md text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div>
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider bg-blue-100 text-blue-800 border border-blue-200">
                        Kegiatan Rapat
                    </span>
                    <span class="text-xs text-gray-400 font-mono">ID: #{{ $rapatId }}</span>
                </div>
                <h2 class="text-xl font-bold text-gray-900 mt-1">{{ $rapatText }}</h2>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('rapat.downloadWord', $rapatId) }}" class="inline-flex items-center px-4 py-2 bg-blue-50 border border-blue-200 hover:bg-blue-100 text-blue-700 text-xs font-bold rounded-xl shadow-xs transition-colors">
                <svg class="ui-icon-sm mr-1.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Unduh Word (.docx)
            </a>
            <a target="_blank" href="{{ url('employee/pdf_kegiatan/' . $rapatId) }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-xs font-semibold rounded-xl shadow-xs transition-colors">
                <svg class="ui-icon-sm mr-1.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Unduh PDF Undangan
            </a>
        </div>
    </div>

    {{-- ==========================================
        ALERT NOTIFICATIONS
    =========================================== --}}
    @if (session('success'))
    <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded-r-xl shadow-xs">
        <div class="flex items-center">
            <svg class="ui-icon-md text-green-500 mr-3 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <p class="text-xs font-medium text-green-800">{{ session('success') }}</p>
        </div>
    </div>
    @endif

    {{-- ==========================================
        CARD UTAMA: PEMANTAUAN TAHAPAN ALUR RAPAT
    =========================================== --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        {{-- Banner Header Stepper --}}
        <div class="p-6 bg-slate-900 text-white border-b border-slate-800">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                <div>
                    <h3 class="text-base font-bold text-white flex items-center gap-2">
                        <span>Pemantauan Tahapan Alur Rapat</span>
                    </h3>
                    <p class="text-xs text-slate-400 mt-0.5">Pantau dan kelola jalannya 4 tahap siklus rapat.</p>
                </div>
                <div id="top-banner-status">
                    @if($rapatSetuju == 1 && (!empty($rapatNotulen) || ($firstItem->notulen_selesai ?? 0) == 1))
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                            ✓ Seluruh Tahap Tuntas (Selesai)
                        </span>
                    @elseif($rapatSetuju == 1)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-blue-500/20 text-blue-300 border border-blue-500/30">
                            ● Rapat Disetujui (Sedang Berjalan)
                        </span>
                    @elseif($rapatSetuju == 3)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-red-500/20 text-red-300 border border-red-500/30">
                            ✕ Rapat Ditolak
                        </span>
                    @else
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30 animate-pulse">
                            ⏳ Menunggu Persetujuan Pemimpin
                        </span>
                    @endif
                </div>
            </div>

            {{-- 4-Step Visual Stepper Bar --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                {{-- STEP 1: BUAT --}}
                <div class="p-3 rounded-xl bg-slate-800/80 border border-blue-500/50 flex items-center gap-3">
                    <span class="w-8 h-8 rounded-lg bg-blue-600 text-white font-black text-xs flex items-center justify-center flex-shrink-0">1</span>
                    <div>
                        <div class="text-xs font-bold text-white">1. Buat Rapat</div>
                        <div class="text-[10px] text-blue-400 font-medium">✓ Selesai Diterbitkan</div>
                    </div>
                </div>

                {{-- STEP 2: PERSETUJUAN --}}
                <div id="step-2-box" class="p-3 rounded-xl {{ $rapatSetuju == 1 ? 'bg-slate-800/80 border-emerald-500/50' : ($rapatSetuju == 3 ? 'bg-slate-800/80 border-red-500/50' : 'bg-slate-800/80 border-amber-500/50 ring-1 ring-amber-500/30') }} border flex items-center gap-3">
                    <span id="step-2-badge" class="w-8 h-8 rounded-lg {{ $rapatSetuju == 1 ? 'bg-emerald-600' : ($rapatSetuju == 3 ? 'bg-red-600' : 'bg-amber-500') }} text-white font-black text-xs flex items-center justify-center flex-shrink-0">2</span>
                    <div>
                        <div class="text-xs font-bold text-white">2. Persetujuan</div>
                        <div id="step-2-status" class="text-[10px] {{ $rapatSetuju == 1 ? 'text-emerald-400' : ($rapatSetuju == 3 ? 'text-red-400' : 'text-amber-400 font-bold') }}">
                            {{ $rapatSetuju == 1 ? '✓ Telah Disetujui' : ($rapatSetuju == 3 ? '✕ Ditolak' : '⏳ Menunggu Approval') }}
                        </div>
                    </div>
                </div>

                {{-- STEP 3: PRESENSI QR --}}
                <div id="step-3-box" class="p-3 rounded-xl {{ $rapatSetuju == 1 ? 'bg-slate-800/80 border-blue-500/50' : 'bg-slate-800/40 border-slate-700/50 opacity-60' }} border flex items-center gap-3">
                    <span id="step-3-badge" class="w-8 h-8 rounded-lg {{ $rapatSetuju == 1 ? 'bg-blue-600' : 'bg-slate-700' }} text-white font-black text-xs flex items-center justify-center flex-shrink-0">3</span>
                    <div>
                        <div class="text-xs font-bold text-white">3. Presensi QR</div>
                        <div id="step-3-status" class="text-[10px] {{ $rapatSetuju == 1 ? 'text-blue-400' : 'text-slate-500' }}">
                            {{ $rapatSetuju == 1 ? '● Siap / QR Aktif' : 'Terkunci' }}
                        </div>
                    </div>
                </div>

                {{-- STEP 4: NOTULEN --}}
                <div id="step-4-box" class="p-3 rounded-xl {{ (!empty($rapatNotulen) || ($firstItem->notulen_selesai ?? 0) == 1) ? 'bg-slate-800/80 border-emerald-500/50' : ($rapatSetuju == 1 ? 'bg-slate-800/80 border-amber-500/50' : 'bg-slate-800/40 border-slate-700/50 opacity-60') }} border flex items-center gap-3">
                    <span id="step-4-badge" class="w-8 h-8 rounded-lg {{ (!empty($rapatNotulen) || ($firstItem->notulen_selesai ?? 0) == 1) ? 'bg-emerald-600' : ($rapatSetuju == 1 ? 'bg-amber-500' : 'bg-slate-700') }} text-white font-black text-xs flex items-center justify-center flex-shrink-0">4</span>
                    <div>
                        <div class="text-xs font-bold text-white">4. Notulen & Foto</div>
                        <div id="step-4-status" class="text-[10px] {{ (!empty($rapatNotulen) || ($firstItem->notulen_selesai ?? 0) == 1) ? 'text-emerald-400' : ($rapatSetuju == 1 ? 'text-amber-400' : 'text-slate-500') }}">
                            {{ (!empty($rapatNotulen) || ($firstItem->notulen_selesai ?? 0) == 1) ? '✓ Notulen Selesai' : ($rapatSetuju == 1 ? 'Menunggu Unggah' : 'Terkunci') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Detail Konten per Tahap --}}
        <div class="p-6 sm:p-8 space-y-6">

            {{-- PANEL TAHAP 2: PERSETUJUAN PEMIMPIN (SEJAJAR & TIDAK MEPET) --}}
            <div class="approval-container {{ $rapatSetuju == 1 ? 'approval-approved' : ($rapatSetuju == 3 ? 'approval-rejected' : 'approval-waiting') }}">
                <div class="flex items-center gap-3.5">
                    <div class="w-8 h-8 rounded-xl {{ $rapatSetuju == 1 ? 'bg-emerald-600' : ($rapatSetuju == 3 ? 'bg-red-600' : 'bg-amber-500') }} text-white font-black text-xs flex items-center justify-center flex-shrink-0 shadow-xs">
                        2
                    </div>
                    <div>
                        <h4 class="text-xs font-bold uppercase tracking-wider text-gray-800">
                            Tahap Persetujuan Pemimpin Rapat
                        </h4>
                        <p class="text-xs text-gray-600 mt-1">
                            Pemimpin Rapat: <strong class="text-gray-900 font-semibold">{{ $rapatPemimpin }}</strong>
                        </p>
                    </div>
                </div>

                {{-- Action Form / Status Badge (Sejajar di sebelah kanan) --}}
                <div class="flex items-center gap-3">
                    @if($isPemimpin && $rapatSetuju == 0)
                        <form action="{{ url('/setuju_rapat') }}" method="POST" class="inline">
                            @csrf
                            <input type="hidden" name="id" value="{{ $rapatId }}">
                            <input type="hidden" name="setuju_rapat" value="3">
                            <button type="submit" onclick="return confirm('Apakah Anda yakin ingin menolak rapat ini?')" class="btn-reject">
                                <svg class="ui-icon-xs" style="color: #dc2626;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                Tolak Rapat
                            </button>
                        </form>
                        <form action="{{ url('/setuju_rapat') }}" method="POST" class="inline">
                            @csrf
                            <input type="hidden" name="id" value="{{ $rapatId }}">
                            <input type="hidden" name="setuju_rapat" value="1">
                            <button type="submit" class="btn-approve">
                                <svg class="ui-icon-xs" style="color: #ffffff;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                Setujui Rapat
                            </button>
                        </form>
                    @else
                        @if($rapatSetuju == 1)
                            <span class="stage-pill stage-pill-approved">
                                <svg class="ui-icon-xs text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                Disetujui oleh {{ $rapatPemimpin }}
                            </span>
                        @elseif($rapatSetuju == 3)
                            <span class="stage-pill stage-pill-rejected">
                                <svg class="ui-icon-xs text-red-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                Rapat Ditolak
                            </span>
                        @else
                            <div class="flex items-center gap-2 flex-wrap sm:flex-nowrap">
                                <span class="stage-pill stage-pill-waiting">
                                    <span>⏳</span>
                                    <span>Menunggu Persetujuan Pemimpin Rapat</span>
                                </span>
                            </div>
                        @endif
                    @endif
                </div>
            </div>

            {{-- PANEL TAHAP 3 & 4 (DIBAGI 2 KOLOM) --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                {{-- PANEL TAHAP 3: PRESENSI QR CODE --}}
                <div id="presensi-qr" class="stage-card-panel">
                    <div>
                        <div class="flex items-center justify-between pb-4 border-b border-gray-100 mb-5">
                            <div class="flex items-center gap-2.5">
                                <span class="w-7 h-7 rounded-xl bg-blue-600 text-white text-xs font-black flex items-center justify-center flex-shrink-0 shadow-xs">3</span>
                                <div>
                                    <h4 class="text-xs font-bold uppercase tracking-wider text-gray-800">Tahap Presensi QR Rapat</h4>
                                    <p class="text-[11px] text-gray-400">Pindai QR lewat HP peserta</p>
                                </div>
                            </div>
                            <span class="text-[11px] font-bold {{ $rapatSetuju == 1 ? 'text-blue-700 bg-blue-50 border border-blue-200' : 'text-gray-500 bg-gray-100 border border-gray-200' }} px-3 py-1 rounded-full">
                                {{ $rapatSetuju == 1 ? 'QR Siap & Aktif' : 'Terkunci' }}
                            </span>
                        </div>

                        @if($rapatSetuju == 1)
                            <div class="flex flex-col sm:flex-row items-center sm:items-start gap-5">
                                <div class="p-3 bg-white rounded-2xl border-2 border-slate-100 shadow-sm flex-shrink-0 flex flex-col items-center">
                                    {!! QrCode::size(120)->generate($qrUrlHadir ?? url('/daftarhadir/' . $rapatId)) !!}
                                    <span class="text-[10px] text-slate-400 font-mono mt-1.5 font-bold">SCAN DARI HP</span>
                                </div>
                                <div class="space-y-3 text-xs text-gray-600 flex-1 w-full">
                                    <div>
                                        <p class="font-bold text-gray-900 text-sm">QR Code Presensi Peserta</p>
                                        <p class="text-xs text-gray-500 leading-relaxed mt-1">Peserta rapat (seperti <strong class="text-gray-800">St. Rasnani Manafi</strong>) memindai kode QR ini dari HP untuk memilih status: <strong>Hadir</strong>, <strong>Sedang Ada Kegiatan Lain</strong>, atau <strong>Tidak Hadir</strong>.</p>
                                    </div>
                                    
                                    <div class="p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-[11px] font-mono text-slate-700 break-all flex items-center justify-between gap-2">
                                        <span class="truncate select-all">{{ $qrUrlHadir ?? url('/daftarhadir/' . $rapatId) }}</span>
                                        <button type="button" onclick="navigator.clipboard.writeText('{{ $qrUrlHadir ?? url('/daftarhadir/' . $rapatId) }}'); alert('Tautan presensi berhasil disalin!');" class="text-blue-600 hover:text-blue-800 font-bold shrink-0 font-sans px-2.5 py-1 rounded-lg bg-blue-50 hover:bg-blue-100 border border-blue-200 transition">
                                            Salin
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="p-8 text-center text-xs text-gray-400">
                                <svg class="ui-icon-lg mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                Presensi QR Code akan otomatis aktif setelah rapat disetujui oleh Pemimpin Rapat.
                            </div>
                        @endif
                    </div>

                    @if($rapatSetuju == 1)
                        <div class="pt-4 mt-5 border-t border-gray-100 flex items-center gap-2 flex-wrap">
                            <a target="_blank" href="{{ url('/qrcode/' . $rapatId) }}" class="btn-primary-action">
                                <svg class="ui-icon-xs text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                                Layar Penuh
                            </a>
                            <a target="_blank" href="{{ $qrUrlHadir ?? url('/daftarhadir/' . $rapatId) }}" class="btn-secondary-action">
                                Buka Link Hadir
                            </a>
                        </div>
                    @endif
                </div>

                {{-- PANEL TAHAP 4: NOTULEN & DOKUMENTASI --}}
                <div id="notulen" class="stage-card-panel">
                    <div>
                        <div class="flex items-center justify-between pb-4 border-b border-gray-100 mb-5">
                            <div class="flex items-center gap-2.5">
                                <span class="w-7 h-7 rounded-xl bg-emerald-600 text-white text-xs font-black flex items-center justify-center flex-shrink-0 shadow-xs">4</span>
                                <div>
                                    <h4 class="text-xs font-bold uppercase tracking-wider text-gray-800">Tahap Notulen & Foto Dokumentasi</h4>
                                    <p class="text-[11px] text-gray-400">Ringkasan hasil, materi & foto</p>
                                </div>
                            </div>
                            <span id="badge-tahap4-status" class="text-[11px] font-bold {{ (!empty($rapatNotulen) || ($firstItem->notulen_selesai ?? 0) == 1) ? 'text-emerald-700 bg-emerald-50 border border-emerald-200' : 'text-amber-700 bg-amber-50 border border-amber-200' }} px-3 py-1 rounded-full">
                                {{ (!empty($rapatNotulen) || ($firstItem->notulen_selesai ?? 0) == 1) ? '✓ Selesai (100%)' : 'Menunggu Notulen' }}
                            </span>
                        </div>

                        @if($rapatSetuju == 1)
                            {{-- TAMPILAN VIEW NOTULEN & DOKUMENTASI (SELESAI / SUDAH TERISI) --}}
                            <div id="view-notulen-container" class="{{ (!empty($rapatNotulen) || ($firstItem->notulen_selesai ?? 0) == 1) ? '' : 'hidden' }} space-y-4">
                                <div class="flex items-center justify-between gap-2 flex-wrap">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                                        <h5 class="text-xs font-bold text-gray-900 uppercase tracking-wide">Hasil Notulen & Kesimpulan:</h5>
                                    </div>
                                    @if($isNotulis)
                                        <button type="button" onclick="toggleEditNotulen(true)" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-blue-50 text-blue-700 hover:bg-blue-100 border border-blue-200 text-xs font-bold transition">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            <span>Ubah / Edit Notulen</span>
                                        </button>
                                    @endif
                                </div>

                                {{-- BOX HASIL NOTULEN --}}
                                <div id="notulen-display-text" class="p-4 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm text-slate-800 whitespace-pre-line leading-relaxed max-h-48 overflow-y-auto">
                                    {{ $rapatNotulen }}
                                </div>

                                {{-- ATTACHMENT CARDS & LINKS --}}
                                <div class="pt-3 border-t border-slate-100 flex flex-wrap items-center gap-2">
                                    <a id="materi-link-btn" target="_blank" href="{{ $rapatMateriLink ?: '#' }}" class="{{ $rapatMateriLink ? 'inline-flex' : 'hidden' }} items-center gap-1.5 px-3 py-2 rounded-xl bg-blue-50 text-blue-700 hover:bg-blue-100 border border-blue-200 text-xs font-bold transition shadow-2xs">
                                        <span>📁</span>
                                        <span>Buka Materi (Drive)</span>
                                        <svg class="w-3 h-3 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                    </a>

                                    <a id="foto-link-btn" target="_blank" href="{{ $rapatFotoLink ?: '#' }}" class="{{ $rapatFotoLink ? 'inline-flex' : 'hidden' }} items-center gap-1.5 px-3 py-2 rounded-xl bg-indigo-50 text-indigo-700 hover:bg-indigo-100 border border-indigo-200 text-xs font-bold transition shadow-2xs">
                                        <span>📷</span>
                                        <span>Buka Foto Dokumentasi</span>
                                        <svg class="w-3 h-3 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                    </a>

                                    <a target="_blank" href="{{ url('employee/pdf_kegiatan/' . $rapatId) }}" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-slate-100 text-slate-700 hover:bg-slate-200 border border-slate-300 text-xs font-bold transition shadow-2xs">
                                        <span>📄</span>
                                        <span>Unduh PDF Risalah</span>
                                    </a>
                                </div>
                            </div>

                            {{-- FORM CONTAINER (PENGISIAN ATAU EDIT NOTULEN) --}}
                            <div id="form-notulen-container" class="{{ (!empty($rapatNotulen) || ($firstItem->notulen_selesai ?? 0) == 1) ? 'hidden' : '' }} space-y-4">
                                @if($isNotulis)
                                    <div class="flex items-center justify-between pb-2 border-b border-gray-100">
                                        <h5 id="form-notulen-title" class="text-xs font-bold text-gray-900">
                                            {{ (!empty($rapatNotulen) || ($firstItem->notulen_selesai ?? 0) == 1) ? 'Ubah Notulen & Dokumentasi Rapat' : 'Pengisian Notulen & Dokumentasi Rapat' }}
                                        </h5>
                                        <button type="button" id="btn-cancel-edit-notulen" onclick="toggleEditNotulen(false)" class="{{ (!empty($rapatNotulen) || ($firstItem->notulen_selesai ?? 0) == 1) ? '' : 'hidden' }} text-xs font-bold text-gray-500 hover:text-gray-700 px-2.5 py-1 rounded-lg bg-gray-100 hover:bg-gray-200 transition">
                                            ✕ Batal
                                        </button>
                                    </div>

                                    <form id="form-notulen" action="{{ route('update_notulen') }}" method="POST" onsubmit="handleNotulenSubmit(event, this)" class="space-y-3">
                                        @csrf
                                        <input type="hidden" name="id" value="{{ $rapatId }}">
                                        <div>
                                            <label for="notulenTextarea" class="block text-xs font-bold text-gray-700 mb-1">
                                                Hasil Pembahasan Rapat <span class="text-rose-500">*</span>
                                            </label>
                                            <textarea id="notulenTextarea" name="notulen" rows="4" required placeholder="Tuliskan ringkasan hasil pembahasan rapat, keputusan, dan tindak lanjut..." class="w-full text-xs border-gray-300 rounded-xl p-3 bg-gray-50 focus:bg-white text-gray-800 focus:ring-blue-500 focus:border-blue-500 border">{{ $rapatNotulen }}</textarea>
                                        </div>

                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                            <div>
                                                <label for="materiLinkInput" class="block text-[11px] font-bold text-gray-600 mb-1">Tautan Bahan / Materi (Drive):</label>
                                                <input type="url" id="materiLinkInput" name="materi_link" value="{{ $rapatMateriLink }}" placeholder="https://drive.google.com/..." class="w-full text-xs border-gray-300 rounded-xl p-2.5 bg-gray-50 focus:bg-white text-gray-800 border">
                                            </div>
                                            <div>
                                                <label for="fotoLinkInput" class="block text-[11px] font-bold text-gray-600 mb-1">Tautan Foto Dokumentasi:</label>
                                                <input type="url" id="fotoLinkInput" name="foto_link" value="{{ $rapatFotoLink }}" placeholder="https://photos.app.goo.gl/..." class="w-full text-xs border-gray-300 rounded-xl p-2.5 bg-gray-50 focus:bg-white text-gray-800 border">
                                            </div>
                                        </div>

                                        <div class="flex items-center justify-end gap-2 pt-2">
                                            <button type="button" id="btn-cancel-edit-notulen-bottom" onclick="toggleEditNotulen(false)" class="{{ (!empty($rapatNotulen) || ($firstItem->notulen_selesai ?? 0) == 1) ? '' : 'hidden' }} btn-secondary-action">
                                                Batal
                                            </button>
                                            <button type="submit" id="btn-submit-notulen" class="btn-primary-action">
                                                <span id="btn-submit-notulen-text">Simpan Notulen & Dokumentasi</span>
                                            </button>
                                        </div>
                                    </form>
                                @else
                                    <div class="p-8 text-center text-xs text-gray-400">
                                        <svg class="ui-icon-lg mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        Menunggu Notulis (<strong class="text-gray-600">{{ $rapatNotulis }}</strong>) atau Tim Dokumentasi mengisi notulen dan tautan dokumentasi.
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="p-8 text-center text-xs text-gray-400">
                                <svg class="ui-icon-lg mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                Form notulen & dokumentasi akan terbuka secara otomatis setelah rapat disetujui oleh Pemimpin Rapat.
                            </div>
                        @endif
                    </div>
                </div>

                <script>
                window.isCurrentlyEditingNotulen = false;
                function toggleEditNotulen(isEdit) {
                    window.isCurrentlyEditingNotulen = isEdit;
                    const viewContainer = document.getElementById('view-notulen-container');
                    const formContainer = document.getElementById('form-notulen-container');
                    const btnCancel = document.getElementById('btn-cancel-edit-notulen');
                    const btnCancelBottom = document.getElementById('btn-cancel-edit-notulen-bottom');
                    const formTitle = document.getElementById('form-notulen-title');

                    if (isEdit) {
                        if (viewContainer) viewContainer.classList.add('hidden');
                        if (formContainer) formContainer.classList.remove('hidden');
                        if (btnCancel) btnCancel.classList.remove('hidden');
                        if (btnCancelBottom) btnCancelBottom.classList.remove('hidden');
                        if (formTitle) formTitle.innerText = 'Ubah Notulen & Dokumentasi Rapat';
                    } else {
                        if (viewContainer) viewContainer.classList.remove('hidden');
                        if (formContainer) formContainer.classList.add('hidden');
                    }
                }
                </script>

            </div>

        </div>
    </div>

    {{-- ==========================================
        CARD INFORMASI DETAIL & DAFTAR PESERTA
    =========================================== --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- KOLOM KIRI: DETAIL INFORMASI RAPAT --}}
        <div class="lg:col-span-1 bg-white rounded-2xl shadow-sm border border-gray-200 p-6 space-y-4">
            <h3 class="text-sm font-bold text-gray-800 pb-2 border-b border-gray-100">
                Informasi Pelaksanaan
            </h3>

            <div class="space-y-3 text-xs">
                <div>
                    <span class="text-gray-400 block text-[11px] uppercase font-bold">Agenda Pembahasan:</span>
                    <p class="text-gray-800 mt-0.5 leading-relaxed">{{ $rapatAgenda }}</p>
                </div>
                <div>
                    <span class="text-gray-400 block text-[11px] uppercase font-bold">Waktu Pelaksanaan:</span>
                    <p class="text-gray-800 mt-0.5 font-semibold">
                        {{ \Carbon\Carbon::parse($rapatStart)->translatedFormat('d F Y') }}
                    </p>
                    <p class="text-gray-500 font-mono text-[11px]">
                        {{ substr($rapatJamMulai, 0, 5) }} - {{ substr($rapatJamSelesai, 0, 5) }} WITA
                    </p>
                </div>
                <div>
                    <span class="text-gray-400 block text-[11px] uppercase font-bold">Lokasi / Tempat:</span>
                    <p class="text-gray-800 mt-0.5">{{ $rapatTempat }}</p>
                </div>
                <div class="pt-2 border-t border-gray-100 space-y-2">
                    <div>
                        <span class="text-gray-400 text-[11px]">Pemimpin:</span>
                        <span class="font-bold text-gray-800 ml-1">{{ $rapatPemimpin }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400 text-[11px]">Notulis:</span>
                        <span class="font-medium text-gray-800 ml-1">{{ $rapatNotulis }}</span>
                    </div>
                    @if($rapatDokumentasi && $rapatDokumentasi != '-')
                    <div>
                        <span class="text-gray-400 text-[11px]">Dokumentasi:</span>
                        <span class="font-medium text-gray-800 ml-1">{{ $rapatDokumentasi }}</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- KOLOM KANAN: DAFTAR PESERTA RAPAT --}}
        @php
            $countHadir = $kegiatans->where('status_kehadiran', 'Hadir')->count();
            $countTidak = $kegiatans->where('status_kehadiran', 'Tidak Hadir')->count();
            $countLain  = $kegiatans->where('status_kehadiran', 'Sedang Ada Kegiatan Lain')->count();
            $countBelum = $kegiatans->where('status_kehadiran', 'Belum Hadir')->count();
        @endphp
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden flex flex-col justify-between">
            <div>
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/70 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-sm font-bold text-gray-900">
                                Daftar Peserta Rapat (<span id="total-count">{{ count($kegiatans) }}</span>)
                            </h3>
                            <button type="button" id="btn-refresh-presensi" onclick="refreshPresensi()" class="p-1 rounded-lg hover:bg-gray-200 text-gray-500 hover:text-blue-600 transition" title="Segarkan Data Presensi">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            </button>
                        </div>
                        <p class="text-[11px] text-gray-500 mt-0.5">Pemantauan status kehadiran peserta secara langsung (real-time)</p>
                    </div>

                    {{-- Summary Pills --}}
                    <div class="flex items-center gap-1.5 flex-wrap">
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            <span>Hadir: <strong id="count-hadir">{{ $countHadir }}</strong></span>
                        </span>
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                            <span>Lain: <strong id="count-lain">{{ $countLain }}</strong></span>
                        </span>
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                            <span>Tidak: <strong id="count-tidak">{{ $countTidak }}</strong></span>
                        </span>
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-medium bg-gray-100 text-gray-600 border border-gray-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>
                            <span>Belum: <strong id="count-belum">{{ $countBelum }}</strong></span>
                        </span>
                    </div>
                </div>

                <div class="overflow-x-auto max-h-[380px] overflow-y-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-left text-xs">
                        <thead class="bg-gray-50 text-[11px] font-bold text-gray-500 uppercase tracking-wider sticky top-0 z-10">
                            <tr>
                                <th class="px-5 py-2.5 w-10">No</th>
                                <th class="px-5 py-2.5">Nama Peserta</th>
                                <th class="px-5 py-2.5">NIP</th>
                                <th class="px-5 py-2.5 text-right">Status Kehadiran</th>
                            </tr>
                        </thead>
                        <tbody id="presensi-table-body" class="divide-y divide-gray-100 bg-white">
                            @forelse($kegiatans as $idx => $pesertaItem)
                            @php
                                $st = $pesertaItem->status_kehadiran ?? 'Belum Hadir';
                            @endphp
                            <tr class="hover:bg-gray-50/50 transition-colors">
                                <td class="px-5 py-3 text-gray-400 font-medium">{{ $idx + 1 }}</td>
                                <td class="px-5 py-3">
                                    <span class="font-bold text-gray-900 block text-xs">{{ $pesertaItem->abc }}</span>
                                    @if(!empty($pesertaItem->keterangan))
                                        <span class="text-[10px] text-amber-700 font-medium block truncate max-w-xs">Ket: {{ $pesertaItem->keterangan }}</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-gray-500 font-mono text-xs">{{ $pesertaItem->nipbaru ?? $pesertaItem->def }}</td>
                                <td class="px-5 py-3 text-right">
                                    @if($st === 'Hadir')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            <span>✓ Hadir</span>
                                            @if(!empty($pesertaItem->waktu_kehadiran))
                                                <span class="text-[9px] text-emerald-600 font-mono ml-0.5 font-normal">({{ \Carbon\Carbon::parse($pesertaItem->waktu_kehadiran)->format('H:i') }})</span>
                                            @endif
                                        </span>
                                    @elseif($st === 'Sedang Ada Kegiatan Lain')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                            <span>💼 Kegiatan Lain</span>
                                            @if(!empty($pesertaItem->waktu_kehadiran))
                                                <span class="text-[9px] text-amber-600 font-mono ml-0.5 font-normal">({{ \Carbon\Carbon::parse($pesertaItem->waktu_kehadiran)->format('H:i') }})</span>
                                            @endif
                                        </span>
                                    @elseif($st === 'Tidak Hadir')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                            <span>✕ Tidak Hadir</span>
                                            @if(!empty($pesertaItem->waktu_kehadiran))
                                                <span class="text-[9px] text-rose-600 font-mono ml-0.5 font-normal">({{ \Carbon\Carbon::parse($pesertaItem->waktu_kehadiran)->format('H:i') }})</span>
                                            @endif
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-medium bg-gray-100 text-gray-600 border border-gray-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>
                                            <span>Belum Hadir</span>
                                        </span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-5 py-6 text-center text-gray-400">
                                    Tidak ada data peserta penugasan.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="px-6 py-2.5 bg-gray-50 border-t border-gray-100 flex items-center justify-between text-[11px] text-gray-500">
                <span class="flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>Pembaruan otomatis aktif (tiap 5 detik)</span>
                </span>
                <span class="font-mono text-[10px]">ID Rapat: #{{ $rapatId }}</span>
            </div>
        </div>

    </div>

</div>

{{-- FLOATING TOAST NOTIFICATION --}}
<div id="toast-notification" class="fixed bottom-6 right-6 z-50 transform transition-all duration-300 translate-y-20 opacity-0 pointer-events-none">
    <div class="bg-emerald-600 text-white px-5 py-3.5 rounded-2xl shadow-xl flex items-center gap-3 text-xs font-bold border border-emerald-400">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
        <span id="toast-message">Operasi berhasil</span>
    </div>
</div>

<script>
function showToastSuccess(msg) {
    const toast = document.getElementById('toast-notification');
    const toastMsg = document.getElementById('toast-message');
    if (toast && toastMsg) {
        toastMsg.innerText = msg;
        toast.classList.remove('translate-y-20', 'opacity-0', 'pointer-events-none');
        toast.classList.add('translate-y-0', 'opacity-100');
        setTimeout(() => {
            toast.classList.add('translate-y-20', 'opacity-0', 'pointer-events-none');
            toast.classList.remove('translate-y-0', 'opacity-100');
        }, 4000);
    }
}

function handleNotulenSubmit(event, form) {
    event.preventDefault();
    const btnSubmit = document.getElementById('btn-submit-notulen');
    const btnText = document.getElementById('btn-submit-notulen-text');
    const originalText = btnText ? btnText.innerText : 'Simpan';

    if (btnSubmit) {
        btnSubmit.disabled = true;
        if (btnText) btnText.innerText = 'Menyimpan...';
    }

    const formData = new FormData(form);

    fetch(form.action, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Gagal menyimpan notulen');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            window.isCurrentlyEditingNotulen = false;
            
            showToastSuccess(data.message || 'Notulen & foto dokumentasi berhasil disimpan!');

            const notulenText = form.querySelector('[name="notulen"]')?.value || '';
            const materiLink = form.querySelector('[name="materi_link"]')?.value || '';
            const fotoLink = form.querySelector('[name="foto_link"]')?.value || '';

            const notulenDisplayText = document.getElementById('notulen-display-text');
            if (notulenDisplayText) notulenDisplayText.innerText = notulenText;

            const materiLinkBtn = document.getElementById('materi-link-btn');
            if (materiLinkBtn) {
                if (materiLink) {
                    materiLinkBtn.href = materiLink;
                    materiLinkBtn.classList.remove('hidden');
                    materiLinkBtn.classList.add('inline-flex');
                } else {
                    materiLinkBtn.classList.add('hidden');
                    materiLinkBtn.classList.remove('inline-flex');
                }
            }

            const fotoLinkBtn = document.getElementById('foto-link-btn');
            if (fotoLinkBtn) {
                if (fotoLink) {
                    fotoLinkBtn.href = fotoLink;
                    fotoLinkBtn.classList.remove('hidden');
                    fotoLinkBtn.classList.add('inline-flex');
                } else {
                    fotoLinkBtn.classList.add('hidden');
                    fotoLinkBtn.classList.remove('inline-flex');
                }
            }

            toggleEditNotulen(false);
            refreshPresensi();
        }
    })
    .catch(err => {
        console.error(err);
        alert('Terjadi kesalahan saat menyimpan notulen. Silakan coba lagi.');
    })
    .finally(() => {
        if (btnSubmit) {
            btnSubmit.disabled = false;
            if (btnText) btnText.innerText = originalText;
        }
    });
}

function refreshPresensi() {
    const btn = document.getElementById('btn-refresh-presensi');
    if (btn) btn.classList.add('animate-spin');

    fetch('{{ route("api.presensi.status", $rapatId) }}')
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Update summary counters
                if (data.summary) {
                    const elHadir = document.getElementById('count-hadir');
                    const elLain = document.getElementById('count-lain');
                    const elTidak = document.getElementById('count-tidak');
                    const elBelum = document.getElementById('count-belum');
                    const elTotal = document.getElementById('total-count');

                    if (elHadir) elHadir.innerText = data.summary.hadir;
                    if (elLain) elLain.innerText = data.summary.kegiatan_lain;
                    if (elTidak) elTidak.innerText = data.summary.tidak_hadir;
                    if (elBelum) elBelum.innerText = data.summary.belum_hadir;
                    if (elTotal) elTotal.innerText = data.summary.total;
                }

                // Update table rows
                const tbody = document.getElementById('presensi-table-body');
                if (tbody && data.peserta && data.peserta.length > 0) {
                    let html = '';
                    data.peserta.forEach((p, idx) => {
                        let statusBadge = '';
                        if (p.status_kehadiran === 'Hadir') {
                            statusBadge = `<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                <span>✓ Hadir</span>
                                ${p.waktu ? `<span class="text-[9px] text-emerald-600 font-mono ml-0.5 font-normal">(${p.waktu})</span>` : ''}
                            </span>`;
                        } else if (p.status_kehadiran === 'Sedang Ada Kegiatan Lain') {
                            statusBadge = `<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                <span>💼 Kegiatan Lain</span>
                                ${p.waktu ? `<span class="text-[9px] text-amber-600 font-mono ml-0.5 font-normal">(${p.waktu})</span>` : ''}
                            </span>`;
                        } else if (p.status_kehadiran === 'Tidak Hadir') {
                            statusBadge = `<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                <span>✕ Tidak Hadir</span>
                                ${p.waktu ? `<span class="text-[9px] text-rose-600 font-mono ml-0.5 font-normal">(${p.waktu})</span>` : ''}
                            </span>`;
                        } else {
                            statusBadge = `<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-medium bg-gray-100 text-gray-600 border border-gray-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>
                                <span>Belum Hadir</span>
                            </span>`;
                        }

                        html += `<tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-5 py-3 text-gray-400 font-medium">${idx + 1}</td>
                            <td class="px-5 py-3">
                                <span class="font-bold text-gray-900 block text-xs">${p.nama}</span>
                                ${p.keterangan ? `<span class="text-[10px] text-amber-700 font-medium block truncate max-w-xs">Ket: ${p.keterangan}</span>` : ''}
                            </td>
                            <td class="px-5 py-3 text-gray-500 font-mono text-xs">${p.nipbaru || p.nip}</td>
                            <td class="px-5 py-3 text-right">${statusBadge}</td>
                        </tr>`;
                    });
                    tbody.innerHTML = html;
                }

                // Update Live Rapat & Notulen Status
                if (data.rapat) {
                    const r = data.rapat;
                    const hasNotulen = (r.notulen && r.notulen.trim().length > 0) || r.notulen_selesai == 1;

                    // Top Banner Status
                    const topBanner = document.getElementById('top-banner-status');
                    if (topBanner) {
                        if (r.setuju_rapat == 1 && hasNotulen) {
                            topBanner.innerHTML = `<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">✓ Seluruh Tahap Tuntas (Selesai)</span>`;
                        } else if (r.setuju_rapat == 1) {
                            topBanner.innerHTML = `<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-blue-500/20 text-blue-300 border border-blue-500/30">● Rapat Disetujui (Sedang Berjalan)</span>`;
                        } else if (r.setuju_rapat == 3) {
                            topBanner.innerHTML = `<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-red-500/20 text-red-300 border border-red-500/30">✕ Rapat Ditolak</span>`;
                        } else {
                            topBanner.innerHTML = `<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30 animate-pulse">⏳ Menunggu Persetujuan Pemimpin</span>`;
                        }
                    }

                    // Stepper Step 4
                    const step4Box = document.getElementById('step-4-box');
                    const step4Badge = document.getElementById('step-4-badge');
                    const step4Status = document.getElementById('step-4-status');
                    if (step4Box && step4Badge && step4Status) {
                        if (hasNotulen) {
                            step4Box.className = "p-3 rounded-xl bg-slate-800/80 border-emerald-500/50 border flex items-center gap-3";
                            step4Badge.className = "w-8 h-8 rounded-lg bg-emerald-600 text-white font-black text-xs flex items-center justify-center flex-shrink-0";
                            step4Status.className = "text-[10px] text-emerald-400 font-medium";
                            step4Status.innerText = "✓ Notulen Selesai";
                        } else if (r.setuju_rapat == 1) {
                            step4Box.className = "p-3 rounded-xl bg-slate-800/80 border-amber-500/50 border flex items-center gap-3";
                            step4Badge.className = "w-8 h-8 rounded-lg bg-amber-500 text-white font-black text-xs flex items-center justify-center flex-shrink-0";
                            step4Status.className = "text-[10px] text-amber-400 font-medium";
                            step4Status.innerText = "Menunggu Unggah";
                        }
                    }

                    // Badge Tahap 4
                    const badgeTahap4 = document.getElementById('badge-tahap4-status');
                    if (badgeTahap4) {
                        if (hasNotulen) {
                            badgeTahap4.className = "text-[11px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-3 py-1 rounded-full";
                            badgeTahap4.innerText = "✓ Selesai (100%)";
                        } else {
                            badgeTahap4.className = "text-[11px] font-bold text-amber-700 bg-amber-50 border border-amber-200 px-3 py-1 rounded-full";
                            badgeTahap4.innerText = "Menunggu Notulen";
                        }
                    }

                    // Realtime Notulen view & links
                    const viewContainer = document.getElementById('view-notulen-container');
                    const notulenDisplayText = document.getElementById('notulen-display-text');
                    const materiLinkBtn = document.getElementById('materi-link-btn');
                    const fotoLinkBtn = document.getElementById('foto-link-btn');
                    const formContainer = document.getElementById('form-notulen-container');

                    if (hasNotulen) {
                        if (notulenDisplayText && r.notulen) {
                            notulenDisplayText.innerText = r.notulen;
                        }
                        if (materiLinkBtn) {
                            if (r.materi_link) {
                                materiLinkBtn.href = r.materi_link;
                                materiLinkBtn.classList.remove('hidden');
                                materiLinkBtn.classList.add('inline-flex');
                            } else {
                                materiLinkBtn.classList.add('hidden');
                                materiLinkBtn.classList.remove('inline-flex');
                            }
                        }
                        if (fotoLinkBtn) {
                            if (r.foto_link) {
                                fotoLinkBtn.href = r.foto_link;
                                fotoLinkBtn.classList.remove('hidden');
                                fotoLinkBtn.classList.add('inline-flex');
                            } else {
                                fotoLinkBtn.classList.add('hidden');
                                fotoLinkBtn.classList.remove('inline-flex');
                            }
                        }

                        if (!window.isCurrentlyEditingNotulen) {
                            if (viewContainer) viewContainer.classList.remove('hidden');
                            if (formContainer) formContainer.classList.add('hidden');
                        }

                        const btnCancel = document.getElementById('btn-cancel-edit-notulen');
                        const btnCancelBottom = document.getElementById('btn-cancel-edit-notulen-bottom');
                        if (btnCancel) btnCancel.classList.remove('hidden');
                        if (btnCancelBottom) btnCancelBottom.classList.remove('hidden');
                    }
                }
            }
        })
        .catch(err => console.log('Presensi poll err:', err))
        .finally(() => {
            if (btn) btn.classList.remove('animate-spin');
        });
}

// Auto refresh polling every 5 seconds
setInterval(refreshPresensi, 5000);
</script>
@endsection
