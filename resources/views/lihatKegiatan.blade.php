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
    $isPemimpin = Auth::check() && (
        strcasecmp($currentUserNama, trim($rapatPemimpin ?? '')) === 0 ||
        (isset(Auth::user()->level) && strtolower(Auth::user()->level) === 'admin')
    );
    $isNotulis = Auth::check() && (
        strcasecmp($currentUserNama, trim($rapatNotulis ?? '')) === 0 ||
        $isPemimpin ||
        (isset(Auth::user()->level) && strtolower(Auth::user()->level) === 'admin')
    );
@endphp

<style>
    /* Scoped Styles for Detail Rapat Layout */
    .approval-container {
        padding: 18px 22px !important;
        border-radius: 16px !important;
        border: 1px solid !important;
        display: flex !important;
        flex-direction: row !important;
        align-items: center !important;
        justify-content: space-between !important;
        gap: 16px !important;
        flex-wrap: wrap !important;
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
        padding: 20px 22px !important;
        border-radius: 16px !important;
        background-color: #f8fafc !important;
        border: 1px solid #e2e8f0 !important;
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
                <div>
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
                <div class="p-3 rounded-xl {{ $rapatSetuju == 1 ? 'bg-slate-800/80 border-emerald-500/50' : ($rapatSetuju == 3 ? 'bg-slate-800/80 border-red-500/50' : 'bg-slate-800/80 border-amber-500/50 ring-1 ring-amber-500/30') }} border flex items-center gap-3">
                    <span class="w-8 h-8 rounded-lg {{ $rapatSetuju == 1 ? 'bg-emerald-600' : ($rapatSetuju == 3 ? 'bg-red-600' : 'bg-amber-500') }} text-white font-black text-xs flex items-center justify-center flex-shrink-0">2</span>
                    <div>
                        <div class="text-xs font-bold text-white">2. Persetujuan</div>
                        <div class="text-[10px] {{ $rapatSetuju == 1 ? 'text-emerald-400' : ($rapatSetuju == 3 ? 'text-red-400' : 'text-amber-400 font-bold') }}">
                            {{ $rapatSetuju == 1 ? '✓ Telah Disetujui' : ($rapatSetuju == 3 ? '✕ Ditolak' : '⏳ Menunggu Approval') }}
                        </div>
                    </div>
                </div>

                {{-- STEP 3: PRESENSI QR --}}
                <div class="p-3 rounded-xl {{ $rapatSetuju == 1 ? 'bg-slate-800/80 border-blue-500/50' : 'bg-slate-800/40 border-slate-700/50 opacity-60' }} border flex items-center gap-3">
                    <span class="w-8 h-8 rounded-lg {{ $rapatSetuju == 1 ? 'bg-blue-600' : 'bg-slate-700' }} text-white font-black text-xs flex items-center justify-center flex-shrink-0">3</span>
                    <div>
                        <div class="text-xs font-bold text-white">3. Presensi QR</div>
                        <div class="text-[10px] {{ $rapatSetuju == 1 ? 'text-blue-400' : 'text-slate-500' }}">
                            {{ $rapatSetuju == 1 ? '● Siap / QR Aktif' : 'Terkunci' }}
                        </div>
                    </div>
                </div>

                {{-- STEP 4: NOTULEN --}}
                <div class="p-3 rounded-xl {{ (!empty($rapatNotulen) || ($firstItem->notulen_selesai ?? 0) == 1) ? 'bg-slate-800/80 border-emerald-500/50' : ($rapatSetuju == 1 ? 'bg-slate-800/80 border-amber-500/50' : 'bg-slate-800/40 border-slate-700/50 opacity-60') }} border flex items-center gap-3">
                    <span class="w-8 h-8 rounded-lg {{ (!empty($rapatNotulen) || ($firstItem->notulen_selesai ?? 0) == 1) ? 'bg-emerald-600' : ($rapatSetuju == 1 ? 'bg-amber-500' : 'bg-slate-700') }} text-white font-black text-xs flex items-center justify-center flex-shrink-0">4</span>
                    <div>
                        <div class="text-xs font-bold text-white">4. Notulen & Foto</div>
                        <div class="text-[10px] {{ (!empty($rapatNotulen) || ($firstItem->notulen_selesai ?? 0) == 1) ? 'text-emerald-400' : ($rapatSetuju == 1 ? 'text-amber-400' : 'text-slate-500') }}">
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
                <div id="presensi-qr" class="stage-card-panel space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-gray-200">
                        <div class="flex items-center gap-2.5">
                            <span class="w-6 h-6 rounded-lg bg-blue-600 text-white text-[11px] font-black flex items-center justify-center flex-shrink-0">3</span>
                            <h4 class="text-xs font-bold uppercase tracking-wider text-gray-800 ml-3">Tahap Presensi QR Rapat</h4>
                        </div>
                        <span class="text-[11px] font-bold {{ $rapatSetuju == 1 ? 'text-blue-700 bg-blue-100 border border-blue-200' : 'text-gray-500 bg-gray-200 border border-gray-300' }} px-2.5 py-0.5 rounded-full">
                            {{ $rapatSetuju == 1 ? 'QR Siap' : 'Terkunci' }}
                        </span>
                    </div>

                    @if($rapatSetuju == 1)
                        <div class="flex flex-col sm:flex-row items-center gap-4 bg-white p-4 rounded-xl border border-gray-200 shadow-xs">
                            <div class="p-2 bg-white rounded-xl border border-gray-200 shadow-xs flex-shrink-0">
                                {!! QrCode::size(110)->generate($qrUrlHadir ?? url('/daftarhadir/' . $rapatId)) !!}
                            </div>
                            <div class="space-y-2 text-xs text-gray-600">
                                <p class="font-bold text-gray-900">QR Code Presensi Peserta</p>
                                <p class="text-[11px] text-gray-500 leading-relaxed">Peserta rapat dapat melakukan presensi kehadiran dengan memindai kode QR ini atau melalui tautan daftar hadir.</p>
                                <div class="pt-1 flex items-center gap-2 flex-wrap">
                                    <a target="_blank" href="{{ url('/qrcode/' . $rapatId) }}" class="btn-primary-action">
                                        <svg class="ui-icon-xs text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                                        Layar Penuh
                                    </a>
                                    <a target="_blank" href="{{ $qrUrlHadir ?? url('/daftarhadir/' . $rapatId) }}" class="btn-secondary-action ml-3">
                                        Buka Link Hadir
                                    </a>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="p-6 text-center text-xs text-gray-400 bg-white rounded-xl border border-gray-200">
                            <svg class="ui-icon-lg mx-auto text-gray-300 mb-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            Presensi QR Code akan otomatis aktif setelah rapat disetujui oleh Pemimpin Rapat.
                        </div>
                    @endif
                </div>

                {{-- PANEL TAHAP 4: NOTULEN & DOKUMENTASI --}}
                <div id="notulen" class="stage-card-panel space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-gray-200">
                        <div class="flex items-center gap-2.5">
                            <span class="w-6 h-6 rounded-lg bg-emerald-600 text-white text-[11px] font-black flex items-center justify-center flex-shrink-0">4</span>
                            <h4 class="text-xs font-bold uppercase tracking-wider text-gray-800">Tahap Notulen & Foto Dokumentasi</h4>
                        </div>
                        <span class="text-[11px] font-bold {{ !empty($rapatNotulen) ? 'text-emerald-700 bg-emerald-100 border border-emerald-200' : 'text-amber-700 bg-amber-100 border border-amber-200' }} px-2.5 py-0.5 rounded-full">
                            {{ !empty($rapatNotulen) ? 'Notulen Terisi' : 'Menunggu Notulen' }}
                        </span>
                    </div>

                    <div class="bg-white p-4 rounded-xl border border-gray-200 space-y-3 shadow-xs">
                        @if($rapatSetuju == 1)
                            @if(empty($rapatNotulen) || $isNotulis)
                                {{-- Form Pengisian Notulen (Aktif jika Notulis atau belum diisi) --}}
                                <form action="{{ route('update_notulen') }}" method="POST" class="space-y-3">
                                    @csrf
                                    <input type="hidden" name="id" value="{{ $rapatId }}">
                                    <div>
                                        <label for="notulenTextarea" class="block text-xs font-bold text-gray-700 mb-1">
                                            Notulen Hasil Pembahasan Rapat
                                        </label>
                                        <textarea id="notulenTextarea" name="notulen" rows="4" placeholder="Tuliskan ringkasan hasil rapat, keputusan, dan tindak lanjut..." class="w-full text-xs border-gray-300 rounded-lg p-2.5 bg-gray-50 focus:bg-white text-gray-800 focus:ring-blue-500 focus:border-blue-500 border">{{ $rapatNotulen }}</textarea>
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                        <div>
                                            <input type="url" name="materi_link" value="{{ $rapatMateriLink }}" placeholder="Tautan Bahan / Materi (Drive)" class="w-full text-xs border-gray-300 rounded-lg p-2 bg-gray-50 focus:bg-white text-gray-800 border">
                                        </div>
                                        <div>
                                            <input type="url" name="foto_link" value="{{ $rapatFotoLink }}" placeholder="Tautan Foto Dokumentasi" class="w-full text-xs border-gray-300 rounded-lg p-2 bg-gray-50 focus:bg-white text-gray-800 border ml-3">
                                        </div>
                                    </div>

                                    <div class="flex justify-end mt-3">
                                        <button type="submit" class="btn-primary-action">
                                            Simpan Notulen & Dokumentasi
                                        </button>
                                    </div>
                                </form>
                            @else
                                {{-- Tampilan Read-Only Notulen --}}
                                <div class="space-y-2 text-xs">
                                    <h5 class="font-bold text-gray-800">Catatan Notulen:</h5>
                                    <div class="p-3 bg-gray-50 rounded-lg border border-gray-200 whitespace-pre-line text-gray-700 leading-relaxed">
                                        {{ $rapatNotulen }}
                                    </div>
                                    @if($rapatMateriLink || $rapatFotoLink)
                                    <div class="pt-2 flex items-center gap-3 flex-wrap">
                                        @if($rapatMateriLink)
                                            <a target="_blank" href="{{ $rapatMateriLink }}" class="text-xs font-semibold text-blue-600 hover:underline flex items-center gap-1">
                                                📁 Buka Materi Rapat
                                            </a>
                                        @endif
                                        @if($rapatFotoLink)
                                            <a target="_blank" href="{{ $rapatFotoLink }}" class="text-xs font-semibold text-blue-600 hover:underline flex items-center gap-1">
                                                📷 Buka Foto Dokumentasi
                                            </a>
                                        @endif
                                    </div>
                                    @endif
                                </div>
                            @endif
                        @else
                            <div class="p-6 text-center text-xs text-gray-400">
                                Form notulen akan terbuka setelah rapat disetujui.
                            </div>
                        @endif
                    </div>
                </div>

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
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                <h3 class="text-sm font-bold text-gray-800">Daftar Peserta Rapat ({{ count($kegiatans) }})</h3>
                <span class="text-xs text-gray-500">Daftar pegawai yang ditugaskan</span>
            </div>

            <div class="overflow-x-auto max-h-[360px] overflow-y-auto">
                <table class="min-w-full divide-y divide-gray-200 text-left text-xs">
                    <thead class="bg-gray-50 text-[11px] font-bold text-gray-500 uppercase tracking-wider sticky top-0">
                        <tr>
                            <th class="px-5 py-2.5">No</th>
                            <th class="px-5 py-2.5">Nama Peserta</th>
                            <th class="px-5 py-2.5">NIP</th>
                            <th class="px-5 py-2.5 text-right">Status Kehadiran</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse($kegiatans as $idx => $pesertaItem)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-5 py-3 text-gray-400">{{ $idx + 1 }}</td>
                            <td class="px-5 py-3 font-semibold text-gray-800">{{ $pesertaItem->abc }}</td>
                            <td class="px-5 py-3 text-gray-500 font-mono">{{ $pesertaItem->nipbaru ?? $pesertaItem->def }}</td>
                            <td class="px-5 py-3 text-right">
                                @if(($pesertaItem->status_hadir ?? 0) == 1)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                        ✓ Hadir
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-gray-100 text-gray-600">
                                        Belum Hadir
                                    </span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-5 py-6 text-center text-gray-400">
                                Tidak ada data peserta.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</div>
@endsection
