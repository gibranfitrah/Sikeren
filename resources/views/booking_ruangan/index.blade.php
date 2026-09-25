@extends('layouts.app')

@section('title', 'Booking Ruangan & Kelola Zoom - Sikeren')
@section('header_title', 'Booking Ruangan & Jadwal Rapat')

@php
$isToday = $isToday ?? ($selectedDate === \Carbon\Carbon::today()->format('Y-m-d'));
@endphp

@section('content')
<div class="max-w-7xl mx-auto space-y-6 pb-12" x-data="{
         modalOpen: {{ $selectedRapat ? 'true' : 'false' }},
         detailModalOpen: false,
         selectedBooking: null,
         selectedVenueId: '{{ $selectedRapat->venue_id ?? 1 }}',
         tipePertemuan: '{{ $selectedRapat ? ($selectedRapat->tempat && str_contains(strtolower($selectedRapat->tempat), 'zoom') ? 'hybrid' : 'offline') : 'offline' }}',
         zoomAccount: 'zoom_1',
         namaAcara: '{{ addslashes($selectedRapat->text ?? '') }}',
         penyelenggara: '{{ addslashes(($selectedRapat ? ($selectedRapat->penanggung_jawab ?? $selectedRapat->pemimpin) : null) ?? Auth::user()->nama_lengkap ?? '') }}',
         jumlahPeserta: '{{ $selectedRapat ? (is_array($selectedRapat->owners) ? count($selectedRapat->owners) : ($selectedRapat->owners ? count(explode(',', $selectedRapat->owners)) : 25)) : 25 }}',
         bookingDate: '{{ $selectedRapat->start_date ?? $selectedDate }}',
         startTime: '{{ ($selectedRapat && $selectedRapat->start_jam) ? substr($selectedRapat->start_jam, 0, 5) : '09:00' }}',
         endTime: '{{ ($selectedRapat && $selectedRapat->end_jam) ? substr($selectedRapat->end_jam, 0, 5) : '11:00' }}',
         taskId: '{{ $selectedRapat->id ?? '' }}',
         keterangan: '',
         
          // Sarpras & Layout State (Hybrid: statis + dinamis backend)
          venueDefaults: {
              1: 'Classroom',
              2: 'U-Shape',
              3: 'Theatre'
          },
          layoutMeja: '{{ ($selectedRapat && $selectedRapat->venue_id == 2) ? "U-Shape" : (($selectedRapat && $selectedRapat->venue_id == 3) ? "Theatre" : "Classroom") }}',
          sofaDepan: 'tanpa', // 'tanpa' | 'dengan' (UI statis / SVG)
          sofaConfig: 'tanpa_sofa', // 'tanpa_sofa' | 'dengan_sofa' (backend, sinkron dengan sofaDepan)
         capacityInfo: null,
         capacityLoading: false,
         tipePodium: 'Podium Standar',
         jumlahKursiPodium: '4',
         pasangSpanduk: true,
         keteranganSpanduk: '',
         micCount: '2',
         specialLainnya: '',

         // Data Kapasitas Resmi Ruang Pertemuan BPS Sultra
         roomCapacities: {
             1: {
                 name: 'Ruang Rapat Lantai 1',
                 floor: 'Lantai 1',
                 max: 24,
                 layouts: {
                     'Theatre': { tanpaSofa: 24, denganSofa: 20, hasSofaOption: true, desc: 'Barisan kursi rapat langsung menghadap layar utama/panggung. Sangat efisien untuk sosialisasi dan audiensi.' },
                     'Classroom': { tanpaSofa: 18, denganSofa: 14, hasSofaOption: true, desc: 'Barisan meja dan kursi menghadap depan panggung. Ideal untuk pelatihan, bimtek, dan rapat teknis.' },
                     'U-Shape': { tanpaSofa: 8, denganSofa: null, hasSofaOption: false, desc: 'Susunan meja berbentuk U / tapal kuda. Sangat baik untuk diskusi dua arah dan rapat pimpinan.' },
                     'Boardroom': { tanpaSofa: 12, denganSofa: null, hasSofaOption: false, desc: 'Satu meja rapat besar di tengah. Efektif untuk rapat tertutup dan koordinasi tim inti.' },
                     'Round Table': { tanpaSofa: 12, denganSofa: null, hasSofaOption: false, desc: 'Format meja bundar untuk diskusi kelompok / FGD.' },
                     'Hollow Square': { tanpaSofa: 10, denganSofa: null, hasSofaOption: false, desc: 'Meja persegi dengan ruang terbuka di tengah.' },
                     'Custom Layout': { tanpaSofa: 24, denganSofa: 20, hasSofaOption: true, desc: 'Tata letak khusus disesuaikan kebutuhan acara.' }
                 }
             },
             2: {
                 name: 'Vicon Lantai 3',
                 floor: 'Lantai 3',
                 max: 58,
                 layouts: {
                     'Theatre': { tanpaSofa: 58, denganSofa: 48, hasSofaOption: true, desc: 'Kapasitas maksimal barisan kursi untuk video conference besar dan webinar hybrid.' },
                     'Classroom': { tanpaSofa: 48, denganSofa: 40, hasSofaOption: true, desc: 'Meja kelas dengan akses langsung ke Smart TV Display dan Kamera Vicon 360°.' },
                     'U-Shape': { tanpaSofa: 23, denganSofa: null, hasSofaOption: false, desc: 'Susunan meja U-Shape dengan jangkauan optimal Mic Conference Polycom dan kamera.' },
                     'Boardroom': { tanpaSofa: 26, denganSofa: null, hasSofaOption: false, desc: 'Meja eksekutif panjang dengan fasilitas vicon dan audio terintegrasi.' },
                     'Round Table': { tanpaSofa: 24, denganSofa: null, hasSofaOption: false, desc: 'Format meja bundar untuk workshop dan kolaborasi online-offline.' },
                     'Hollow Square': { tanpaSofa: 20, denganSofa: null, hasSofaOption: false, desc: 'Susunan meja kotak untuk rapat komisi atau evaluasi.' },
                     'Custom Layout': { tanpaSofa: 58, denganSofa: 48, hasSofaOption: true, desc: 'Tata letak custom disesuaikan kebutuhan zoom/vicon.' }
                 }
             },
             3: {
                 name: 'Aula Lantai 4',
                 floor: 'Lantai 4',
                 max: 100,
                 layouts: {
                     'Theatre': { tanpaSofa: 100, denganSofa: 80, hasSofaOption: true, desc: 'Format teater aula megah dengan Videotron LED screen raksasa. Kapasitas hingga 100 orang.' },
                     'Classroom': { tanpaSofa: 62, denganSofa: 52, hasSofaOption: true, desc: 'Susunan meja & kursi berkapasitas besar untuk pelatihan regional, bimtek, dan rapat dinas.' },
                     'U-Shape': { tanpaSofa: 58, denganSofa: null, hasSofaOption: false, desc: 'Susunan meja U-Shape megah menghadap panggung utama dan videotron.' },
                     'Boardroom': { tanpaSofa: 66, denganSofa: null, hasSofaOption: false, desc: 'Format meja gabungan konferensi besar untuk forum lintas instansi.' },
                     'Round Table': { tanpaSofa: 60, denganSofa: null, hasSofaOption: false, desc: 'Format seminar meja bundar atau workshop pleno.' },
                     'Hollow Square': { tanpaSofa: 50, denganSofa: null, hasSofaOption: false, desc: 'Susunan meja persegi besar untuk pleno komisi.' },
                     'Custom Layout': { tanpaSofa: 100, denganSofa: 80, hasSofaOption: true, desc: 'Tata letak aula disesuaikan khusus untuk acara seremonial.' }
                 }
             }
         },

         getCurrentCapacity() {
             let r = this.roomCapacities[this.selectedVenueId] || this.roomCapacities[1];
             let l = r.layouts[this.layoutMeja] || r.layouts['Classroom'];
             if (this.sofaDepan === 'dengan' && l.hasSofaOption && l.denganSofa) {
                 return l.denganSofa;
             }
             return l.tanpaSofa;
         },

         getCurrentLayoutInfo() {
             let r = this.roomCapacities[this.selectedVenueId] || this.roomCapacities[1];
             return r.layouts[this.layoutMeja] || r.layouts['Classroom'];
         },

         getCurrentVenueName() {
             let r = this.roomCapacities[this.selectedVenueId] || this.roomCapacities[1];
             return r.name;
         },

         isSofaOptionAvailable() {
             let info = this.getCurrentLayoutInfo();
             return info && info.hasSofaOption && info.denganSofa !== null;
         },

         setLayout(layout) {
             this.layoutMeja = layout;
             let info = this.getCurrentLayoutInfo();
             if (!info || !info.hasSofaOption) {
                 this.sofaDepan = 'tanpa';
             }
             // Sinkron ke backend + refresh kapasitas dinamis
             this.sofaConfig = (this.sofaDepan === 'dengan' ? 'dengan_sofa' : 'tanpa_sofa');
             this.fetchCapacity();
         },

         setSofaDepan(val) {
             if (this.isSofaOptionAvailable()) {
                 this.sofaDepan = val;
             } else {
                 this.sofaDepan = 'tanpa';
             }
             // Sinkron ke backend + refresh kapasitas dinamis
             this.sofaConfig = (this.sofaDepan === 'dengan' ? 'dengan_sofa' : 'tanpa_sofa');
             this.fetchCapacity();
         },

          onVenueChange(venueId = null) {
              if (venueId) {
                  this.selectedVenueId = venueId;
              }
              let defaultLayout = this.venueDefaults[this.selectedVenueId] || 'Classroom';
              this.layoutMeja = defaultLayout;
              this.sofaDepan = 'tanpa';
              this.sofaConfig = 'tanpa_sofa';
              this.fetchCapacity();
          },

          openBookingModal(venueId = null, zoomAcc = null, time = null) {
              if (venueId) {
                  this.selectedVenueId = venueId;
                  this.tipePertemuan = 'offline';
                  let defaultLayout = this.venueDefaults[venueId] || 'Classroom';
                  this.layoutMeja = defaultLayout;
                  this.sofaDepan = 'tanpa';
                  this.sofaConfig = 'tanpa_sofa';
              }
              if (zoomAcc) {
                  this.zoomAccount = zoomAcc;
                  this.tipePertemuan = 'online';
              }
              if (time) {
                  this.startTime = time;
                  let h = parseInt(time.split(':')[0]) + 2;
                  this.endTime = (h < 10 ? '0' : '') + h + ':00';
              }
              this.modalOpen = true;
              this.fetchCapacity();
          },
          fetchCapacity() {
              if (this.tipePertemuan !== 'offline' && this.tipePertemuan !== 'hybrid') { this.capacityInfo = null; return; }
              if (!this.selectedVenueId || !this.layoutMeja) { this.capacityInfo = null; return; }
              this.capacityLoading = true;
              fetch('{{ route('booking-ruangan.apiCapacity') }}?venue_id=' + encodeURIComponent(this.selectedVenueId) + '&layout=' + encodeURIComponent(this.layoutMeja) + '&sofa_config=' + encodeURIComponent(this.sofaConfig))
                  .then(r => r.json())
                  .then(d => {
                      if (this.sofaConfig === 'dengan_sofa' && d && !d.available && d.capacity_without_sofa) {
                          this.sofaConfig = 'tanpa_sofa';
                          this.sofaDepan = 'tanpa';
                          d.capacity = d.capacity_without_sofa;
                          d.available = true;
                      }
                      this.capacityInfo = d;
                      this.capacityLoading = false;
                  })
                  .catch(() => { this.capacityInfo = null; this.capacityLoading = false; });
          },
         openDetail(booking) {
             this.selectedBooking = booking;
             this.detailModalOpen = true;
         },
         copyToClipboard(text, label) {
             navigator.clipboard.writeText(text).then(() => {
                 alert('Link ' + label + ' berhasil disalin!');
             });
         }
     }">

    {{-- ==========================================
        HEADER & ACTION BAR
    =========================================== --}}
    <x-heading title="Booking Ruangan & Kelola Zoom"
        subtitle="Pantau ketersediaan 3 Ruangan Rapat dan 2 Akun Zoom resmi BPS Sultra secara real-time"
        tag="Layanan Rapat & Sarpras" subtag="Time Schedule Ruangan">
        <x-slot name="actions">
            <button type="button" @click="modalOpen = true"
                class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-sm transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                + Booking Ruangan / Zoom
            </button>
        </x-slot>
    </x-heading>

    {{-- ALERT SUCCESS & ERROR --}}
    @if(session('success'))
    <div
        class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-center gap-3 text-emerald-800 text-xs font-semibold shadow-xs">
        <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <div class="flex-1">{{ session('success') }}</div>
    </div>
    @endif

    @if(session('error'))
    <div
        class="p-4 bg-rose-50 border border-rose-200 rounded-2xl flex items-center gap-3 text-rose-800 text-xs font-semibold shadow-xs">
        <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <div class="flex-1">{{ session('error') }}</div>
    </div>
    @endif

    @if($errors->any())
    <div
        class="p-4 bg-rose-50 border border-rose-200 rounded-2xl flex items-start gap-3 text-rose-800 text-xs font-semibold shadow-xs">
        <svg class="w-5 h-5 text-rose-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <div class="flex-1">
            <p class="font-bold mb-1">Booking gagal disimpan:</p>
            <ul class="list-disc list-inside space-y-0.5 font-medium">
                @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    {{-- BANNER DARI BUAT RAPAT --}}
    @if($selectedRapat)
    <div class="p-5 bg-gradient-to-r from-blue-50 via-indigo-50 to-white border border-blue-200 rounded-2xl shadow-xs">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-start gap-3.5">
                <div
                    class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center font-bold text-base shrink-0 shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <h3 class="text-sm font-bold text-gray-900">Konfirmasi Booking untuk Rapat:
                            "{{ $selectedRapat->text }}"</h3>
                        <x-badge variant="primary" size="xs">Baru Diterbitkan</x-badge>
                    </div>
                    <p class="text-xs text-gray-600 mt-1">
                        Jadwal Rapat:
                        <strong>{{ \Carbon\Carbon::parse($selectedRapat->start_date)->translatedFormat('l, d F Y') }}</strong>
                        ({{ $selectedRapat->start_jam ?? '09:00' }} - {{ $selectedRapat->end_jam ?? '11:00' }}) •
                        Penyelenggara: {{ $selectedRapat->penanggung_jawab ?? $selectedRapat->pemimpin }}
                    </p>
                </div>
            </div>
            <button type="button" @click="modalOpen = true"
                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-xs shrink-0 transition">
                Pilih Ruangan & Zoom Sekarang &rarr;
            </button>
        </div>
    </div>
    @endif

    {{-- =========================================================
        MAIN GRID 2 KOLOM (KIRI: RUANGAN & ZOOM, KANAN: TIME SCHEDULE)
    ========================================================== --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        {{-- =====================================================
            SISI KIRI (LG: COL-4): CARD 3 RUANGAN & 2 AKUN ZOOM
        ====================================================== --}}
        <div class="lg:col-span-4 space-y-6">

            {{-- 1. CARD RUANGAN RAPAT FISIK --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-xs space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <div class="flex items-center gap-2">
                        <div class="w-2.5 h-2.5 rounded-full bg-blue-600"></div>
                        <h3 class="text-xs font-bold text-gray-800 uppercase tracking-wider">Ruangan Rapat BPS</h3>
                    </div>
                    <span class="text-[11px] font-semibold text-gray-400">3 Ruangan Fisik</span>
                </div>

                <div class="space-y-3.5">
                    @foreach($venuesStatus as $vId => $vStat)
                    @php
                    $ven = $vStat['venue'];
                    $isUse = $vStat['is_in_use'];
                    $totalBook = $vStat['total_today'];
                    $activeTask = $vStat['current_task'];
                    @endphp
                    <div
                        class="p-4 rounded-xl border transition-all duration-200 {{ $isUse ? 'bg-rose-50/40 border-rose-200' : ($totalBook > 0 ? 'bg-amber-50/30 border-amber-200' : 'bg-gray-50/60 hover:bg-white border-gray-200') }}">
                        <div class="flex items-start justify-between gap-2 mb-2">
                            <div>
                                <h4 class="text-xs font-bold text-gray-900 flex items-center gap-1.5">
                                    {{ $ven->name }}
                                    <span class="text-[10px] text-gray-400 font-normal">({{ $ven->capacity }}
                                        Orang)</span>
                                </h4>
                                <p class="text-[11px] text-gray-500 mt-0.5">{{ $ven->description }}</p>
                            </div>
                            <span
                                class="px-2 py-0.5 rounded-md text-[10px] font-bold shrink-0 {{ $isUse ? 'bg-rose-100 text-rose-700' : ($totalBook > 0 ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700') }}">
                                {{ $vStat['status_label'] }}
                            </span>
                        </div>

                        @if($isUse && $activeTask)
                        <div
                            class="mt-2.5 p-2.5 bg-white/80 rounded-lg border border-rose-200/80 text-[11px] space-y-0.5">
                            <p class="font-bold text-rose-900 truncate">{{ $activeTask->nama_acara }}</p>
                            <p class="text-rose-700 font-medium">Jam: {{ substr($activeTask->start_time, 0, 5) }} -
                                {{ substr($activeTask->end_time, 0, 5) }} • PJ: {{ $activeTask->penyelenggara }}</p>
                        </div>
                        @elseif($totalBook > 0)
                        <div class="mt-2 text-[11px] text-amber-700 font-medium">
                            Terdapat {{ $totalBook }} jadwal kegiatan pada tanggal ini.
                        </div>
                        @endif

                        <div class="mt-3 pt-2.5 border-t border-gray-100 flex items-center justify-between">
                            <span class="text-[10px] text-gray-400">
                                {{ $isToday ? 'Hari Ini' : \Carbon\Carbon::parse($selectedDate)->format('d M') }}:
                                {{ $totalBook }} Acara
                            </span>
                            <button type="button" @click="openBookingModal({{ $ven->id }}, null, '09:00')"
                                class="text-[11px] font-bold text-blue-600 hover:text-blue-800 hover:underline">
                                + Booking Ruangan &rarr;
                            </button>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- 2. CARD KELOLA AKUN ZOOM --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-xs space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <div class="flex items-center gap-2">
                        <div class="w-2.5 h-2.5 rounded-full bg-indigo-600"></div>
                        <h3 class="text-xs font-bold text-gray-800 uppercase tracking-wider">Akun Zoom BPS Sultra</h3>
                    </div>
                    <span class="text-[11px] font-semibold text-gray-400">2 Akun Daring</span>
                </div>

                <div class="space-y-3.5">
                    @foreach($zoomStatus as $zKey => $zStat)
                    @php
                    $zInfo = $zStat['info'];
                    $isUse = $zStat['is_in_use'];
                    $activeTask = $zStat['current_task'];
                    @endphp
                    <div
                        class="p-4 rounded-xl border transition-all duration-200 {{ $isUse ? 'bg-rose-50/40 border-rose-200' : 'bg-gray-50/60 hover:bg-white border-gray-200' }}">
                        <div class="flex items-start justify-between gap-2 mb-1.5">
                            <div>
                                <h4 class="text-xs font-bold text-gray-900">{{ $zInfo['name'] }}</h4>
                                <p class="text-[11px] text-gray-500">{{ $zInfo['description'] }}</p>
                            </div>
                            <span
                                class="px-2 py-0.5 rounded-md text-[10px] font-bold shrink-0 {{ $isUse ? 'bg-rose-100 text-rose-700' : 'bg-emerald-100 text-emerald-700' }}">
                                {{ $isUse ? 'Sedang Digunakan' : 'Tersedia' }}
                            </span>
                        </div>

                        {{-- Info Meeting ID & Passcode --}}
                        <div class="mt-2 p-2.5 bg-white rounded-lg border border-gray-200/80 text-[11px] space-y-1">
                            <div class="flex items-center justify-between text-gray-600">
                                <span>Meeting ID:</span>
                                <span class="font-mono font-bold text-gray-900">{{ $zInfo['meeting_id'] }}</span>
                            </div>
                            <div class="flex items-center justify-between text-gray-600">
                                <span>Passcode:</span>
                                <span class="font-mono font-bold text-gray-900">{{ $zInfo['passcode'] }}</span>
                            </div>
                            <div class="pt-1.5 border-t border-gray-100 flex items-center justify-between gap-2">
                                <a href="{{ $zInfo['link'] }}" target="_blank"
                                    class="text-blue-600 font-bold hover:underline truncate">
                                    Buka Zoom Meeting
                                </a>
                                <button type="button"
                                    @click="copyToClipboard('{{ $zInfo['link'] }}', '{{ $zInfo['name'] }}')"
                                    class="text-[10px] font-bold text-gray-500 hover:text-gray-800 bg-gray-100 hover:bg-gray-200 px-2 py-0.5 rounded transition">
                                    Salin Link
                                </button>
                            </div>
                        </div>

                        @if($isUse && $activeTask)
                        <div class="mt-2 text-[10px] text-rose-700 font-medium">
                            Dipakai: "{{ $activeTask->nama_acara }}" ({{ substr($activeTask->start_time, 0, 5) }} -
                            {{ substr($activeTask->end_time, 0, 5) }})
                        </div>
                        @endif

                        <div class="mt-3 pt-2 border-t border-gray-100 flex justify-end">
                            <button type="button" @click="openBookingModal(null, '{{ $zKey }}', '09:00')"
                                class="text-[11px] font-bold text-indigo-600 hover:text-indigo-800 hover:underline">
                                + Gunakan Akun Ini &rarr;
                            </button>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

        </div>

        {{-- =====================================================
            SISI KANAN (LG: COL-8): CARD TANGGAL, FILTER, & TIME SCHEDULE
        ====================================================== --}}
        <div class="lg:col-span-8 space-y-6">

            {{-- 1. CARD TANGGAL & SWITCHER VIEW --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-xs">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">

                    {{-- Navigasi Tanggal --}}
                    <div class="flex items-center gap-2">
                        <a href="{{ route('booking-ruangan.index', ['date' => \Carbon\Carbon::parse($selectedDate)->subDay()->format('Y-m-d'), 'view' => $viewMode]) }}"
                            class="p-2 bg-gray-50 hover:bg-gray-100 rounded-xl border border-gray-200 text-gray-600 transition"
                            title="Hari Sebelumnya">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 19l-7-7 7-7" />
                            </svg>
                        </a>

                        <form action="{{ route('booking-ruangan.index') }}" method="GET"
                            class="flex items-center gap-2">
                            <input type="hidden" name="view" value="{{ $viewMode }}">
                            <input type="date" name="date" value="{{ $selectedDate }}" onchange="this.form.submit()"
                                class="px-3 py-1.5 bg-gray-50 focus:bg-white border border-gray-200 rounded-xl text-xs sm:text-sm font-bold text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600">
                        </form>

                        <a href="{{ route('booking-ruangan.index', ['date' => \Carbon\Carbon::parse($selectedDate)->addDay()->format('Y-m-d'), 'view' => $viewMode]) }}"
                            class="p-2 bg-gray-50 hover:bg-gray-100 rounded-xl border border-gray-200 text-gray-600 transition"
                            title="Hari Berikutnya">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5l7 7-7 7" />
                            </svg>
                        </a>

                        <a href="{{ route('booking-ruangan.index', ['date' => \Carbon\Carbon::today()->format('Y-m-d'), 'view' => $viewMode]) }}"
                            class="px-3 py-1.5 text-xs font-bold rounded-xl {{ $selectedDate === \Carbon\Carbon::today()->format('Y-m-d') ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }} transition">
                            Hari Ini
                        </a>
                    </div>

                    {{-- Switcher Harian vs Bulanan --}}
                    <div class="inline-flex items-center bg-gray-100 p-1 rounded-xl">
                        <a href="{{ route('booking-ruangan.index', ['date' => $selectedDate, 'view' => 'daily']) }}"
                            class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg text-xs font-bold transition {{ $viewMode === 'daily' ? 'bg-white text-blue-700 shadow-xs' : 'text-gray-600 hover:text-gray-900' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            Jadwal Harian
                        </a>
                        <a href="{{ route('booking-ruangan.index', ['date' => $selectedDate, 'month' => substr($selectedDate, 0, 7), 'view' => 'monthly']) }}"
                            class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg text-xs font-bold transition {{ $viewMode === 'monthly' ? 'bg-white text-blue-700 shadow-xs' : 'text-gray-600 hover:text-gray-900' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 9h18M3 15h6M9 3v18M15 3v6m0 0h6m-6 0v12M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            Jadwal Bulanan
                        </a>
                    </div>
                </div>

                {{-- Header Status Hari Terpilih --}}
                <div class="mt-4 pt-4 border-t border-gray-100 flex items-center justify-between text-xs">
                    <div>
                        <span class="text-gray-400">Jadwal Tanggal:</span>
                        <strong
                            class="text-gray-800 ml-1">{{ \Carbon\Carbon::parse($selectedDate)->translatedFormat('l, d F Y') }}</strong>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-blue-600"></span>
                        <span class="text-gray-600 font-semibold">{{ $dailyBookings->count() }} Kegiatan
                            Terjadwal</span>
                    </div>
                </div>
            </div>

            {{-- 2. TIME SCHEDULE: VIEW HARIAN (HOURLY TIMELINE GRID) --}}
            @if($viewMode === 'daily')
            <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-xs space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-gray-900">Time Schedule Harian (08:00 - 17:00)</h3>
                        <p class="text-xs text-gray-500">Klik pada slot waktu atau ruangan untuk melakukan reservasi
                            cepat.</p>
                    </div>
                    <div class="flex items-center gap-3 text-[11px]">
                        <span class="flex items-center gap-1.5 text-gray-600"><span
                                class="w-2.5 h-2.5 rounded bg-blue-500"></span> Aula Lt 1</span>
                        <span class="flex items-center gap-1.5 text-gray-600"><span
                                class="w-2.5 h-2.5 rounded bg-purple-500"></span> Vicon Lt 3</span>
                        <span class="flex items-center gap-1.5 text-gray-600"><span
                                class="w-2.5 h-2.5 rounded bg-emerald-500"></span> Aula Lt 4</span>
                        <span class="flex items-center gap-1.5 text-gray-600"><span
                                class="w-2.5 h-2.5 rounded bg-sky-500"></span> Online Zoom</span>
                    </div>
                </div>

                {{-- Timeline Table Grid --}}
                <div class="overflow-x-auto">
                    <table class="w-full text-xs border-collapse min-w-[700px]">
                        <thead>
                            <tr class="bg-gray-50/80 border-b border-gray-200 text-gray-700">
                                <th class="py-2.5 px-3 text-left w-20 font-bold">Jam</th>
                                <th class="py-2.5 px-3 text-left font-bold border-l border-gray-200">Aula Lantai 1</th>
                                <th class="py-2.5 px-3 text-left font-bold border-l border-gray-200">Vicon Lantai 3</th>
                                <th class="py-2.5 px-3 text-left font-bold border-l border-gray-200">Aula Lantai 4</th>
                                <th class="py-2.5 px-3 text-left font-bold border-l border-gray-200">Akun Zoom</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($timeSlots as $slot)
                            @php
                            $slotStart = $slot . ':00';
                            $slotEnd = date('H:i:s', strtotime($slot . ' +1 hour'));

                            $bookingAula1 = $dailyBookings->first(function($b) use ($slotStart, $slotEnd) {
                            return $b->venue_id == 1 && $b->start_time < $slotEnd && $b->end_time > $slotStart;
                                });

                                $bookingVicon = $dailyBookings->first(function($b) use ($slotStart, $slotEnd) {
                                return $b->venue_id == 2 && $b->start_time < $slotEnd && $b->end_time > $slotStart;
                                    });

                                    $bookingAula4 = $dailyBookings->first(function($b) use ($slotStart, $slotEnd) {
                                    return $b->venue_id == 3 && $b->start_time < $slotEnd && $b->end_time > $slotStart;
                                        });

                                        $bookingZoom = $dailyBookings->first(function($b) use ($slotStart, $slotEnd) {
                                        return in_array($b->zoom_account, ['zoom_1', 'zoom_2', 'eksternal']) &&
                                        $b->start_time < $slotEnd && $b->end_time > $slotStart;
                                            });
                                            @endphp
                                            <tr class="hover:bg-gray-50/40 transition">
                                                {{-- Kolom Waktu --}}
                                                <td class="py-3 px-3 font-mono font-bold text-gray-500 align-top">
                                                    {{ $slot }}
                                                </td>

                                        {{-- Aula Lantai 1 --}}
                                        <td class="py-2 px-2.5 border-l border-gray-100 align-top">
                                            @if($bookingAula1)
                                                @php
                                                    $bPayload1 = [
                                                        'id'               => $bookingAula1->id,
                                                        'nama_acara'       => $bookingAula1->nama_acara,
                                                        'penyelenggara'    => $bookingAula1->penyelenggara,
                                                        'jumlah_peserta'   => $bookingAula1->jumlah_peserta,
                                                        'tipe_pertemuan'   => ucfirst($bookingAula1->tipe_pertemuan),
                                                        'nama_ruangan'     => $bookingAula1->nama_ruangan ?? 'Aula Lantai 1',
                                                        'booking_date'     => \Carbon\Carbon::parse($bookingAula1->booking_date)->translatedFormat('l, d F Y'),
                                                        'start_time'       => substr($bookingAula1->start_time, 0, 5),
                                                        'end_time'         => substr($bookingAula1->end_time, 0, 5),
                                                        'layout_meja'      => $bookingAula1->layout_meja ?: 'Classroom',
                                                        'setup_podium'     => $bookingAula1->setup_podium_data,
                                                        'special_requests' => $bookingAula1->special_requests_data,
                                                        'fasilitas'        => $bookingAula1->fasilitas,
                                                        'zoom_account'     => $bookingAula1->zoom_account,
                                                        'zoom_link'        => $bookingAula1->zoom_link,
                                                        'keterangan'       => $bookingAula1->keterangan,
                                                    ];
                                                @endphp
                                                <div class="p-2.5 rounded-xl bg-blue-50/90 border border-blue-200 text-blue-900 shadow-2xs hover:bg-blue-100/60 cursor-pointer transition"
                                                     @click="openDetail({{ json_encode($bPayload1) }})"
                                                     title="Klik untuk melihat detail sarpras">
                                                    <div class="flex items-start justify-between gap-1">
                                                        <p class="font-bold text-[11px] truncate">{{ $bookingAula1->nama_acara }}</p>
                                                        @if($bookingAula1->tipe_pertemuan === 'hybrid')
                                                            <span class="px-1.5 py-0.2 bg-blue-200 text-blue-800 rounded text-[9px] font-bold shrink-0">Hybrid</span>
                                                        @endif
                                                    </div>
                                                    <p class="text-[10px] text-blue-700 mt-0.5">
                                                        {{ substr($bookingAula1->start_time, 0, 5) }} - {{ substr($bookingAula1->end_time, 0, 5) }} • {{ $bookingAula1->penyelenggara }}
                                                    </p>
                                                    <div class="mt-1 flex items-center gap-1 flex-wrap text-[9px] text-blue-800">
                                                        <span class="px-1 py-0.5 bg-white/80 rounded border border-blue-200 font-medium"><svg class="w-3 h-3 inline-block align-[-1px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 4.5v15m6-15v15m-10.875 0h15.75c.621 0 1.125-.504 1.125-1.125V5.625c0-.621-.504-1.125-1.125-1.125H4.125C3.504 4.5 3 5.004 3 5.625v12.75c0 .621.504 1.125 1.125 1.125z"/></svg> {{ $bookingAula1->layout_meja ?: 'Classroom' }}</span>
                                                        @if(!empty($bookingAula1->setup_podium_data['tipe']) && $bookingAula1->setup_podium_data['tipe'] !== 'Tanpa Podium')
                                                            <span class="px-1 py-0.5 bg-white/80 rounded border border-blue-200 font-medium"><svg class="w-3 h-3 inline-block align-[-1px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/></svg> {{ $bookingAula1->setup_podium_data['tipe'] }}</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            @else
                                                <button type="button"
                                                        @click="openBookingModal(1, null, '{{ $slot }}')"
                                                        class="w-full py-2 px-2 text-center rounded-lg border border-dashed border-gray-200 hover:border-blue-400 hover:bg-blue-50/50 text-gray-400 hover:text-blue-600 text-[11px] transition">
                                                    + Kosong
                                                </button>
                                            @endif
                                        </td>

                                        {{-- Vicon Lantai 3 --}}
                                        <td class="py-2 px-2.5 border-l border-gray-100 align-top">
                                            @if($bookingVicon)
                                                @php
                                                    $bPayload2 = [
                                                        'id'               => $bookingVicon->id,
                                                        'nama_acara'       => $bookingVicon->nama_acara,
                                                        'penyelenggara'    => $bookingVicon->penyelenggara,
                                                        'jumlah_peserta'   => $bookingVicon->jumlah_peserta,
                                                        'tipe_pertemuan'   => ucfirst($bookingVicon->tipe_pertemuan),
                                                        'nama_ruangan'     => $bookingVicon->nama_ruangan ?? 'Vicon Lantai 3',
                                                        'booking_date'     => \Carbon\Carbon::parse($bookingVicon->booking_date)->translatedFormat('l, d F Y'),
                                                        'start_time'       => substr($bookingVicon->start_time, 0, 5),
                                                        'end_time'         => substr($bookingVicon->end_time, 0, 5),
                                                        'layout_meja'      => $bookingVicon->layout_meja ?: 'Classroom',
                                                        'setup_podium'     => $bookingVicon->setup_podium_data,
                                                        'special_requests' => $bookingVicon->special_requests_data,
                                                        'fasilitas'        => $bookingVicon->fasilitas,
                                                        'zoom_account'     => $bookingVicon->zoom_account,
                                                        'zoom_link'        => $bookingVicon->zoom_link,
                                                        'keterangan'       => $bookingVicon->keterangan,
                                                    ];
                                                @endphp
                                                <div class="p-2.5 rounded-xl bg-purple-50/90 border border-purple-200 text-purple-900 shadow-2xs hover:bg-purple-100/60 cursor-pointer transition"
                                                     @click="openDetail({{ json_encode($bPayload2) }})"
                                                     title="Klik untuk melihat detail sarpras">
                                                    <div class="flex items-start justify-between gap-1">
                                                        <p class="font-bold text-[11px] truncate">{{ $bookingVicon->nama_acara }}</p>
                                                        @if($bookingVicon->tipe_pertemuan === 'hybrid')
                                                            <span class="px-1.5 py-0.2 bg-purple-200 text-purple-800 rounded text-[9px] font-bold shrink-0">Hybrid</span>
                                                        @endif
                                                    </div>
                                                    <p class="text-[10px] text-purple-700 mt-0.5">
                                                        {{ substr($bookingVicon->start_time, 0, 5) }} - {{ substr($bookingVicon->end_time, 0, 5) }} • {{ $bookingVicon->penyelenggara }}
                                                    </p>
                                                    <div class="mt-1 flex items-center gap-1 flex-wrap text-[9px] text-purple-800">
                                                        <span class="px-1 py-0.5 bg-white/80 rounded border border-purple-200 font-medium"><svg class="w-3 h-3 inline-block align-[-1px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 4.5v15m6-15v15m-10.875 0h15.75c.621 0 1.125-.504 1.125-1.125V5.625c0-.621-.504-1.125-1.125-1.125H4.125C3.504 4.5 3 5.004 3 5.625v12.75c0 .621.504 1.125 1.125 1.125z"/></svg> {{ $bookingVicon->layout_meja ?: 'Classroom' }}</span>
                                                        @if(!empty($bookingVicon->setup_podium_data['tipe']) && $bookingVicon->setup_podium_data['tipe'] !== 'Tanpa Podium')
                                                            <span class="px-1 py-0.5 bg-white/80 rounded border border-purple-200 font-medium"><svg class="w-3 h-3 inline-block align-[-1px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/></svg> {{ $bookingVicon->setup_podium_data['tipe'] }}</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            @else
                                                <button type="button"
                                                        @click="openBookingModal(2, null, '{{ $slot }}')"
                                                        class="w-full py-2 px-2 text-center rounded-lg border border-dashed border-gray-200 hover:border-purple-400 hover:bg-purple-50/50 text-gray-400 hover:text-purple-600 text-[11px] transition">
                                                    + Kosong
                                                </button>
                                            @endif
                                        </td>

                                        {{-- Aula Lantai 4 --}}
                                        <td class="py-2 px-2.5 border-l border-gray-100 align-top">
                                            @if($bookingAula4)
                                                @php
                                                    $bPayload3 = [
                                                        'id'               => $bookingAula4->id,
                                                        'nama_acara'       => $bookingAula4->nama_acara,
                                                        'penyelenggara'    => $bookingAula4->penyelenggara,
                                                        'jumlah_peserta'   => $bookingAula4->jumlah_peserta,
                                                        'tipe_pertemuan'   => ucfirst($bookingAula4->tipe_pertemuan),
                                                        'nama_ruangan'     => $bookingAula4->nama_ruangan ?? 'Aula Lantai 4',
                                                        'booking_date'     => \Carbon\Carbon::parse($bookingAula4->booking_date)->translatedFormat('l, d F Y'),
                                                        'start_time'       => substr($bookingAula4->start_time, 0, 5),
                                                        'end_time'         => substr($bookingAula4->end_time, 0, 5),
                                                        'layout_meja'      => $bookingAula4->layout_meja ?: 'Classroom',
                                                        'setup_podium'     => $bookingAula4->setup_podium_data,
                                                        'special_requests' => $bookingAula4->special_requests_data,
                                                        'fasilitas'        => $bookingAula4->fasilitas,
                                                        'zoom_account'     => $bookingAula4->zoom_account,
                                                        'zoom_link'        => $bookingAula4->zoom_link,
                                                        'keterangan'       => $bookingAula4->keterangan,
                                                    ];
                                                @endphp
                                                <div class="p-2.5 rounded-xl bg-emerald-50/90 border border-emerald-200 text-emerald-900 shadow-2xs hover:bg-emerald-100/60 cursor-pointer transition"
                                                     @click="openDetail({{ json_encode($bPayload3) }})"
                                                     title="Klik untuk melihat detail sarpras">
                                                    <div class="flex items-start justify-between gap-1">
                                                        <p class="font-bold text-[11px] truncate">{{ $bookingAula4->nama_acara }}</p>
                                                        @if($bookingAula4->tipe_pertemuan === 'hybrid')
                                                            <span class="px-1.5 py-0.2 bg-emerald-200 text-emerald-800 rounded text-[9px] font-bold shrink-0">Hybrid</span>
                                                        @endif
                                                    </div>
                                                    <p class="text-[10px] text-emerald-700 mt-0.5">
                                                        {{ substr($bookingAula4->start_time, 0, 5) }} - {{ substr($bookingAula4->end_time, 0, 5) }} • {{ $bookingAula4->penyelenggara }}
                                                    </p>
                                                    <div class="mt-1 flex items-center gap-1 flex-wrap text-[9px] text-emerald-800">
                                                        <span class="px-1 py-0.5 bg-white/80 rounded border border-emerald-200 font-medium"><svg class="w-3 h-3 inline-block align-[-1px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 4.5v15m6-15v15m-10.875 0h15.75c.621 0 1.125-.504 1.125-1.125V5.625c0-.621-.504-1.125-1.125-1.125H4.125C3.504 4.5 3 5.004 3 5.625v12.75c0 .621.504 1.125 1.125 1.125z"/></svg> {{ $bookingAula4->layout_meja ?: 'Classroom' }}</span>
                                                        @if(!empty($bookingAula4->setup_podium_data['tipe']) && $bookingAula4->setup_podium_data['tipe'] !== 'Tanpa Podium')
                                                            <span class="px-1 py-0.5 bg-white/80 rounded border border-emerald-200 font-medium"><svg class="w-3 h-3 inline-block align-[-1px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/></svg> {{ $bookingAula4->setup_podium_data['tipe'] }}</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            @else
                                                <button type="button"
                                                        @click="openBookingModal(3, null, '{{ $slot }}')"
                                                        class="w-full py-2 px-2 text-center rounded-lg border border-dashed border-gray-200 hover:border-emerald-400 hover:bg-emerald-50/50 text-gray-400 hover:text-emerald-600 text-[11px] transition">
                                                    + Kosong
                                                </button>
                                            @endif
                                        </td>

                                        {{-- Akun Zoom --}}
                                        <td class="py-2 px-2.5 border-l border-gray-100 align-top">
                                            @if($bookingZoom)
                                                @php
                                                    $bPayloadZ = [
                                                        'id'               => $bookingZoom->id,
                                                        'nama_acara'       => $bookingZoom->nama_acara,
                                                        'penyelenggara'    => $bookingZoom->penyelenggara,
                                                        'jumlah_peserta'   => $bookingZoom->jumlah_peserta,
                                                        'tipe_pertemuan'   => ucfirst($bookingZoom->tipe_pertemuan),
                                                        'nama_ruangan'     => $bookingZoom->nama_ruangan ?? 'Online Zoom',
                                                        'booking_date'     => \Carbon\Carbon::parse($bookingZoom->booking_date)->translatedFormat('l, d F Y'),
                                                        'start_time'       => substr($bookingZoom->start_time, 0, 5),
                                                        'end_time'         => substr($bookingZoom->end_time, 0, 5),
                                                        'layout_meja'      => $bookingZoom->layout_meja ?: '-',
                                                        'setup_podium'     => $bookingZoom->setup_podium_data,
                                                        'special_requests' => $bookingZoom->special_requests_data,
                                                        'fasilitas'        => $bookingZoom->fasilitas,
                                                        'zoom_account'     => $bookingZoom->zoom_account,
                                                        'zoom_link'        => $bookingZoom->zoom_link,
                                                        'keterangan'       => $bookingZoom->keterangan,
                                                    ];
                                                @endphp
                                                <div class="p-2.5 rounded-xl bg-sky-50/90 border border-sky-200 text-sky-900 shadow-2xs hover:bg-sky-100/60 cursor-pointer transition"
                                                     @click="openDetail({{ json_encode($bPayloadZ) }})"
                                                     title="Klik untuk melihat detail rapat & zoom">
                                                    <div class="flex items-center justify-between">
                                                        <p class="font-bold text-[11px] truncate">{{ $bookingZoom->nama_acara }}</p>
                                                        <span class="text-[9px] font-bold px-1.5 py-0.5 bg-sky-200 text-sky-800 rounded">
                                                            {{ $bookingZoom->zoom_account === 'zoom_1' ? 'Zoom 1' : ($bookingZoom->zoom_account === 'zoom_2' ? 'Zoom 2' : 'Eksternal') }}
                                                        </span>
                                                    </div>
                                                    <p class="text-[10px] text-sky-700 mt-0.5">
                                                        {{ substr($bookingZoom->start_time, 0, 5) }} - {{ substr($bookingZoom->end_time, 0, 5) }} • {{ $bookingZoom->penyelenggara }}
                                                    </p>
                                                </div>
                                            @else
                                                <button type="button"
                                                        @click="openBookingModal(null, 'zoom_1', '{{ $slot }}')"
                                                        class="w-full py-2 px-2 text-center rounded-lg border border-dashed border-gray-200 hover:border-sky-400 hover:bg-sky-50/50 text-gray-400 hover:text-sky-600 text-[11px] transition">
                                                    + Kosong
                                                </button>
                                            @endif
                                        </td>
                                            </tr>
                                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- 3. TIME SCHEDULE: VIEW BULANAN (MONTHLY AGENDA LIST) --}}
            @else
            <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-xs space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-gray-900">
                            Agenda Bulanan: {{ \Carbon\Carbon::parse($selectedMonth . '-01')->translatedFormat('F Y') }}
                        </h3>
                        <p class="text-xs text-gray-500">Daftar seluruh reservasi ruangan rapat dan akun Zoom bulan ini.
                        </p>
                    </div>
                </div>

                @if($monthlyBookings->isEmpty())
                <div
                    class="py-12 text-center text-gray-400 text-xs font-medium bg-gray-50 rounded-2xl border border-dashed border-gray-200">
                    Belum ada jadwal booking ruangan pada bulan ini.
                </div>
                @else
                    <div class="space-y-3">
                        @foreach($monthlyBookings->groupBy('booking_date') as $bDate => $bList)
                            <div class="p-4 rounded-xl border border-gray-200 bg-gray-50/50 space-y-2.5">
                                <div class="flex items-center justify-between border-b border-gray-200 pb-2">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                                        <strong class="text-xs font-bold text-gray-800">
                                             {{ \Carbon\Carbon::parse($bDate)->translatedFormat('l, d F Y') }}
                                        </strong>
                                    </div>
                                    <span class="text-[11px] font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded-md">
                                        {{ $bList->count() }} Kegiatan
                                    </span>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 pt-1">
                                        @foreach($bList as $bk)
                                            @php
                                                $bPayloadM = [
                                                    'id'               => $bk->id,
                                                    'nama_acara'       => $bk->nama_acara,
                                                    'penyelenggara'    => $bk->penyelenggara,
                                                    'jumlah_peserta'   => $bk->jumlah_peserta,
                                                    'tipe_pertemuan'   => ucfirst($bk->tipe_pertemuan),
                                                    'nama_ruangan'     => $bk->nama_ruangan ?: ($bk->zoom_account ? 'Online Zoom' : '-'),
                                                    'booking_date'     => \Carbon\Carbon::parse($bk->booking_date)->translatedFormat('l, d F Y'),
                                                    'start_time'       => substr($bk->start_time, 0, 5),
                                                    'end_time'         => substr($bk->end_time, 0, 5),
                                                    'layout_meja'      => $bk->layout_meja ?: 'Classroom',
                                                    'setup_podium'     => $bk->setup_podium_data,
                                                    'special_requests' => $bk->special_requests_data,
                                                    'fasilitas'        => $bk->fasilitas,
                                                    'zoom_account'     => $bk->zoom_account,
                                                    'zoom_link'        => $bk->zoom_link,
                                                    'keterangan'       => $bk->keterangan,
                                                ];
                                            @endphp
                                            <div class="p-3 bg-white rounded-xl border border-gray-200 shadow-2xs space-y-2 hover:border-blue-300 transition cursor-pointer"
                                                 @click="openDetail({{ json_encode($bPayloadM) }})">
                                                <div class="flex items-start justify-between gap-2">
                                                    <h5 class="text-xs font-bold text-gray-900 hover:text-blue-600 transition">{{ $bk->nama_acara }}</h5>
                                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded {{ $bk->venue_id == 1 ? 'bg-blue-50 text-blue-700' : ($bk->venue_id == 2 ? 'bg-purple-50 text-purple-700' : ($bk->venue_id == 3 ? 'bg-emerald-50 text-emerald-700' : 'bg-sky-50 text-sky-700')) }}">
                                                        {{ $bk->nama_ruangan ?: ($bk->zoom_account ? 'Online Zoom' : '-') }}
                                                    </span>
                                                </div>
                                                <p class="text-[11px] text-gray-500">
                                                    <svg class="w-3 h-3 inline-block align-[-1px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> {{ substr($bk->start_time, 0, 5) }} - {{ substr($bk->end_time, 0, 5) }} • PJ: {{ $bk->penyelenggara }}
                                                    @if($bk->jumlah_peserta)
                                                        • <svg class="w-3.5 h-3.5 inline-block align-[-2px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg> <strong>{{ $bk->jumlah_peserta }} Peserta</strong>
                                                    @endif
                                                </p>
                                                
                                                {{-- Ringkasan Sarpras (A, B, C) --}}
                                                <div class="p-2 bg-slate-50 rounded-lg border border-slate-100 text-[10px] text-slate-700 space-y-1">
                                                    <div class="flex items-center gap-2 flex-wrap">
                                                        <span class="px-1.5 py-0.5 bg-white border border-slate-200 rounded font-medium"><svg class="w-3 h-3 inline-block align-[-1px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 4.5v15m6-15v15m-10.875 0h15.75c.621 0 1.125-.504 1.125-1.125V5.625c0-.621-.504-1.125-1.125-1.125H4.125C3.504 4.5 3 5.004 3 5.625v12.75c0 .621.504 1.125 1.125 1.125z"/></svg> {{ $bk->layout_meja ?: 'Classroom' }}</span>
                                                        @if(!empty($bk->setup_podium_data['tipe']) && $bk->setup_podium_data['tipe'] !== 'Tanpa Podium')
                                                            <span class="px-1.5 py-0.5 bg-white border border-slate-200 rounded font-medium"><svg class="w-3 h-3 inline-block align-[-1px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/></svg> {{ $bk->setup_podium_data['tipe'] }} ({{ $bk->setup_podium_data['jumlah_kursi'] ?? 0 }} Kursi)</span>
                                                        @endif
                                                        @if(!empty($bk->setup_podium_data['pasang_spanduk']))
                                                            <span class="px-1.5 py-0.5 bg-blue-50 text-blue-700 border border-blue-200 rounded font-medium"><svg class="w-3 h-3 inline-block align-[-1px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3v1.5M3 21v-6m0 0l2.77-.693a9 9 0 016.208.682l.108.054a9 9 0 006.086.71l3.114-.732a48.274 48.274 0 01-.005-6.499l-3.11.732a9 9 0 01-6.085-.711l-.108-.054a9 9 0 00-6.208-.682L3 4.5M3 15V4.5"/></svg> Spanduk</span>
                                                        @endif
                                                    </div>
                                                    @if(!empty($bk->special_requests_data['items']))
                                                        <p class="text-slate-500 truncate"><svg class="w-3 h-3 inline-block align-[-1px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/></svg> {{ implode(', ', $bk->special_requests_data['items']) }}</p>
                                                    @endif
                                                </div>

                                                <div class="pt-1 flex items-center justify-between text-[11px]">
                                                    <button type="button" class="text-blue-600 font-bold hover:underline text-[10px] flex items-center gap-1">
                                                        <span><svg class="w-3 h-3 inline-block align-[-1px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg> Lihat Detail Sarpras</span> &rarr;
                                                    </button>
                                                    @if($bk->zoom_link)
                                                        <a href="{{ $bk->zoom_link }}" target="_blank" @click.stop class="text-indigo-600 font-bold hover:underline text-[10px]">
                                                            Link Zoom &rarr;
                                                        </a>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif

        </div>

    </div>

    {{-- =========================================================
        MODAL POPUP: FORM BOOKING RUANGAN & AKUN ZOOM
    ========================================================== --}}
    <div x-show="modalOpen" x-cloak
        class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-3 sm:p-6"
        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

        {{-- Backdrop --}}
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="modalOpen = false"></div>

        {{-- Modal Content Card --}}
        <div class="relative bg-white rounded-2xl shadow-2xl border border-gray-200 max-w-3xl sm:max-w-4xl w-full max-h-[90vh] flex flex-col overflow-hidden z-10 my-auto"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95">

            <form action="{{ route('booking-ruangan.store') }}" method="POST" class="flex flex-col h-full max-h-[90vh]">
                @csrf
                <input type="hidden" name="task_id" :value="taskId">
                <input type="hidden" name="sofa_config" :value="sofaDepan === 'dengan' ? 'dengan_sofa' : 'tanpa_sofa'">

                {{-- Sticky Header --}}
                <div class="flex items-center justify-between p-4 sm:p-5 border-b border-gray-100 shrink-0 bg-white">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shadow-2xs">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/></svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-gray-900">Form Booking Ruangan & Zoom</h3>
                            <p class="text-xs text-gray-500">Pilih ruangan rapat fisik, akun zoom, dan konfigurasi kebutuhan sarpras.</p>
                        </div>
                    </div>
                    <button type="button" @click="modalOpen = false"
                        class="text-gray-400 hover:text-gray-600 text-2xl font-bold p-1 leading-none rounded-lg hover:bg-gray-100 w-8 h-8 flex items-center justify-center transition">&times;</button>
                </div>

                {{-- Scrollable Form Body --}}
                <div class="p-5 sm:p-6 overflow-y-auto space-y-4 flex-1 overscroll-contain">

                {{-- Pilih Rapat Terkait (Opsional jika bukan dari buat rapat) --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                        Hubungkan dengan Rapat Terdaftar (Opsional)
                    </label>
                    <select
                        class="w-full px-3.5 py-2 text-xs rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600"
                        @change="
                                let sel = $event.target.selectedOptions[0];
                                if (sel.value) {
                                    taskId = sel.value;
                                    namaAcara = sel.getAttribute('data-text') || '';
                                    penyelenggara = sel.getAttribute('data-pj') || '';
                                    bookingDate = sel.getAttribute('data-date') || bookingDate;
                                    startTime = sel.getAttribute('data-start') || startTime;
                                    endTime = sel.getAttribute('data-end') || endTime;
                                }
                            ">
                        <option value="">-- Buat Booking Baru / Pilih Rapat Terdaftar --</option>
                        @foreach($availableRapats as $rpt)
                        <option value="{{ $rpt->id }}" data-text="{{ $rpt->text }}"
                            data-pj="{{ $rpt->penanggung_jawab ?? $rpt->pemimpin }}" data-date="{{ $rpt->start_date }}"
                            data-start="{{ $rpt->start_jam ? substr($rpt->start_jam, 0, 5) : '09:00' }}"
                            data-end="{{ $rpt->end_jam ? substr($rpt->end_jam, 0, 5) : '11:00' }}"
                            {{ ($selectedRapat && $selectedRapat->id == $rpt->id) ? 'selected' : '' }}>
                            [{{ \Carbon\Carbon::parse($rpt->start_date)->format('d/m') }}] {{ $rpt->text }} (PJ:
                            {{ $rpt->penanggung_jawab ?? $rpt->pemimpin }})
                        </option>
                        @endforeach
                    </select>
                </div>

                {{-- Nama Acara, Penyelenggara, & Total Peserta --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                            Nama Acara / Agenda <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="nama_acara" x-model="namaAcara" required
                            placeholder="Contoh: Rapat Evaluasi Sensus Pertanian"
                            class="w-full px-3.5 py-2 text-xs rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600">
                    </div>
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider">
                                Total Peserta (Orang) <span class="text-rose-500">*</span>
                            </label>
                            <span class="text-[10px] font-semibold text-blue-700 bg-blue-50 px-2 py-0.5 rounded" x-text="'Maks ' + getCurrentCapacity() + ' Orang'"></span>
                        </div>
                        <div class="relative">
                            <input type="number" name="jumlah_peserta" x-model="jumlahPeserta" min="1" max="500" required
                                placeholder="Contoh: 30"
                                class="w-full pl-8 pr-3.5 py-2 text-xs rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 font-bold text-gray-800"
                                :class="parseInt(jumlahPeserta) > getCurrentCapacity() ? 'border-rose-300 text-rose-700 bg-rose-50/50' : ''">
                            <span class="absolute left-2.5 top-2.5 text-gray-400"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg></span>
                        </div>
                        <template x-if="parseInt(jumlahPeserta) > getCurrentCapacity()">
                            <p class="text-[10.5px] font-bold text-rose-600 mt-1 flex items-center gap-1">
                                <span><svg class="w-3.5 h-3.5 inline-block align-[-2px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg> Jumlah peserta (<span x-text="jumlahPeserta"></span> orang) melebihi kapasitas layout <span x-text="layoutMeja"></span> (<span x-text="getCurrentCapacity()"></span> orang).</span>
                            </p>
                        </template>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                        Penyelenggara / PJ Kegiatan <span class="text-rose-500">*</span>
                    </label>
                    <input type="text"
                           name="penyelenggara"
                           x-model="penyelenggara"
                           required
                           placeholder="Contoh: Hadi Susanto, M.A."
                           class="w-full px-3.5 py-2 text-xs rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600">
                </div>

                {{-- Tipe Pertemuan (Radio: Offline / Online / Hybrid) --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                        Tipe Pertemuan <span class="text-rose-500">*</span>
                    </label>
                    <div class="grid grid-cols-3 gap-2.5">
                        <label
                            class="flex items-center gap-2 p-2.5 rounded-xl border cursor-pointer text-xs font-bold transition"
                            :class="tipePertemuan === 'offline' ? 'bg-blue-50 border-blue-500 text-blue-700 shadow-2xs' : 'bg-gray-50 border-gray-200 text-gray-700'">
                            <input type="radio" name="tipe_pertemuan" value="offline" x-model="tipePertemuan" @change="fetchCapacity()"
                                class="text-blue-600">
                            <span>Offline (Ruangan)</span>
                        </label>
                        <label
                            class="flex items-center gap-2 p-2.5 rounded-xl border cursor-pointer text-xs font-bold transition"
                            :class="tipePertemuan === 'online' ? 'bg-sky-50 border-sky-500 text-sky-700 shadow-2xs' : 'bg-gray-50 border-gray-200 text-gray-700'">
                            <input type="radio" name="tipe_pertemuan" value="online" x-model="tipePertemuan" @change="fetchCapacity()"
                                class="text-sky-600">
                            <span>Online (Zoom)</span>
                        </label>
                        <label
                            class="flex items-center gap-2 p-2.5 rounded-xl border cursor-pointer text-xs font-bold transition"
                            :class="tipePertemuan === 'hybrid' ? 'bg-purple-50 border-purple-500 text-purple-700 shadow-2xs' : 'bg-gray-50 border-gray-200 text-gray-700'">
                            <input type="radio" name="tipe_pertemuan" value="hybrid" x-model="tipePertemuan" @change="fetchCapacity()"
                                class="text-purple-600">
                            <span>Hybrid (Campuran)</span>
                        </label>
                    </div>
                </div>

                {{-- Pilihan Ruangan Fisik (Jika Offline atau Hybrid) --}}
                <div x-show="tipePertemuan === 'offline' || tipePertemuan === 'hybrid'" class="space-y-1.5">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider">
                            Pilih Ruangan Rapat Fisik <span class="text-rose-500">*</span>
                        </label>
                        <span class="text-[10px] text-slate-500 font-semibold">Kapasitas menyesuaikan layout yang dipilih</span>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                        @foreach($venues as $v)
                        <label class="p-3 rounded-xl border cursor-pointer text-xs transition"
                            :class="selectedVenueId == {{ $v->id }} ? 'bg-blue-50 border-blue-500 text-blue-900 shadow-2xs ring-1 ring-blue-500/50' : 'bg-gray-50 border-gray-200 text-gray-700 hover:bg-slate-100/70'">
                            <div class="flex items-center justify-between gap-1 mb-1">
                                <div class="flex items-start gap-2">
                                    <input type="radio" name="venue_id" value="{{ $v->id }}" x-model="selectedVenueId" @change="onVenueChange({{ $v->id }})"
                                        class="text-blue-600 mt-0.5">
                                    <div>
                                        <span class="font-bold text-xs block leading-tight">{{ $v->name }}</span>
                                        <span class="text-[9.5px] text-blue-600 font-medium block mt-0.5">
                                            @if($v->id == 2)
                                                Default: U-Shape
                                            @elseif($v->id == 3)
                                                Default: Theatre
                                            @else
                                                Default: Classroom
                                            @endif
                                        </span>
                                    </div>
                                </div>
                                <span class="text-[9.5px] font-extrabold text-blue-700 bg-blue-100/80 px-1.5 py-0.5 rounded">
                                    Maks {{ $v->capacity }} Org
                                </span>
                            </div>
                            <div class="flex items-center justify-between mt-1 pt-1.5 border-t border-slate-200/60 text-[10px]">
                                <span class="text-slate-500">Format <span class="font-bold text-slate-700" x-text="layoutMeja"></span>:</span>
                                <span class="font-extrabold text-blue-800"
                                      x-text="(roomCapacities[{{ $v->id }}] ? (sofaDepan === 'dengan' && roomCapacities[{{ $v->id }}].layouts[layoutMeja]?.denganSofa ? roomCapacities[{{ $v->id }}].layouts[layoutMeja].denganSofa : roomCapacities[{{ $v->id }}].layouts[layoutMeja]?.tanpaSofa) : {{ $v->capacity }}) + ' Orang'">
                                </span>
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>

                {{-- Pilihan Fasilitas Sarpras Terstruktur (Man Ruangan: A, B, C) --}}
                <div x-show="tipePertemuan === 'offline' || tipePertemuan === 'hybrid'" class="space-y-4 bg-slate-50/90 p-4 sm:p-5 rounded-2xl border border-slate-200">
                    <div class="flex items-center justify-between border-b border-slate-200 pb-2.5">
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 rounded-lg bg-blue-600 text-white flex items-center justify-center shadow-2xs"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/></svg></span>
                            <div>
                                <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Manajemen Ruangan & Sarpras</h4>
                                <p class="text-[11px] text-slate-500">Konfigurasi layout ruangan, contoh gambar tata letak, dan kapasitas resmi BPS</p>
                            </div>
                        </div>
                        <span class="text-[10px] font-bold text-blue-700 bg-blue-100 px-2 py-0.5 rounded-md">Sarpras BPS Sultra</span>
                    </div>

                    {{-- ========================================================
                        A. SET UP RUANGAN (LAYOUT TATA LETAK MEJA & KURSI)
                    ========================================================= --}}
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <label class="flex items-center gap-2 text-xs font-bold text-slate-800 uppercase tracking-wider">
                                <span class="w-5 h-5 rounded-md bg-slate-200 text-slate-700 flex items-center justify-center text-[10px] font-bold">A</span>
                                <span>Set Up Ruangan (Layout / Tata Letak)</span>
                            </label>
                            <span class="text-[11px] text-blue-600 font-bold flex items-center gap-1">
                                <span>Kapasitas:</span>
                                <span class="px-2 py-0.5 bg-blue-100 text-blue-800 rounded-md font-extrabold" x-text="getCurrentCapacity() + ' Orang'"></span>
                            </span>
                        </div>

                        {{-- Tombol Pilihan Layout --}}
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                            {{-- Theatre (kanonis backend) --}}
                            <label class="p-2.5 rounded-xl border text-xs cursor-pointer transition flex flex-col justify-between"
                                   :class="layoutMeja === 'Theatre' ? 'bg-blue-50/90 border-blue-500 ring-1 ring-blue-500 text-blue-900 font-bold shadow-2xs' : 'bg-white border-slate-200 hover:bg-slate-50 text-slate-700'"
                                   @click="setLayout('Theatre')">
                                <div class="flex items-center justify-between mb-1">
                                    <div class="flex items-center gap-1.5">
                                        <input type="radio" name="layout_meja" value="Theatre" x-model="layoutMeja" @change="setLayout('Theatre')" class="text-blue-600">
                                        <span class="text-[11px] font-bold">Theatre</span>
                                    </div>
                                    <span class="text-[9px] px-1 bg-slate-100 rounded text-slate-600" x-text="(roomCapacities[selectedVenueId]?.layouts['Theatre'].tanpaSofa || 0) + ' Org'"></span>
                                </div>
                                <span class="text-[9.5px] text-slate-500 font-normal leading-tight">Hanya kursi berjejer tanpa meja</span>
                            </label>

                            {{-- Classroom --}}
                            <label class="p-2.5 rounded-xl border text-xs cursor-pointer transition flex flex-col justify-between"
                                   :class="layoutMeja === 'Classroom' ? 'bg-blue-50/90 border-blue-500 ring-1 ring-blue-500 text-blue-900 font-bold shadow-2xs' : 'bg-white border-slate-200 hover:bg-slate-50 text-slate-700'"
                                   @click="setLayout('Classroom')">
                                <div class="flex items-center justify-between mb-1">
                                    <div class="flex items-center gap-1.5">
                                        <input type="radio" name="layout_meja" value="Classroom" x-model="layoutMeja" @change="setLayout('Classroom')" class="text-blue-600">
                                        <span class="text-[11px] font-bold">Classroom</span>
                                    </div>
                                    <span class="text-[9px] px-1 bg-slate-100 rounded text-slate-600" x-text="(roomCapacities[selectedVenueId]?.layouts['Classroom'].tanpaSofa || 0) + ' Org'"></span>
                                </div>
                                <span class="text-[9.5px] text-slate-500 font-normal leading-tight">Baris meja & kursi kelas (Default)</span>
                            </label>

                            {{-- U-Shape --}}
                            <label class="p-2.5 rounded-xl border text-xs cursor-pointer transition flex flex-col justify-between"
                                   :class="layoutMeja === 'U-Shape' ? 'bg-blue-50/90 border-blue-500 ring-1 ring-blue-500 text-blue-900 font-bold shadow-2xs' : 'bg-white border-slate-200 hover:bg-slate-50 text-slate-700'"
                                   @click="setLayout('U-Shape')">
                                <div class="flex items-center justify-between mb-1">
                                    <div class="flex items-center gap-1.5">
                                        <input type="radio" name="layout_meja" value="U-Shape" x-model="layoutMeja" @change="setLayout('U-Shape')" class="text-blue-600">
                                        <span class="text-[11px] font-bold">U-Shape</span>
                                    </div>
                                    <span class="text-[9px] px-1 bg-slate-100 rounded text-slate-600" x-text="(roomCapacities[selectedVenueId]?.layouts['U-Shape'].tanpaSofa || 0) + ' Org'"></span>
                                </div>
                                <span class="text-[9.5px] text-slate-500 font-normal leading-tight">Bentuk huruf U / tapal kuda</span>
                            </label>

                            {{-- Boardroom --}}
                            <label class="p-2.5 rounded-xl border text-xs cursor-pointer transition flex flex-col justify-between"
                                   :class="layoutMeja === 'Boardroom' ? 'bg-blue-50/90 border-blue-500 ring-1 ring-blue-500 text-blue-900 font-bold shadow-2xs' : 'bg-white border-slate-200 hover:bg-slate-50 text-slate-700'"
                                   @click="setLayout('Boardroom')">
                                <div class="flex items-center justify-between mb-1">
                                    <div class="flex items-center gap-1.5">
                                        <input type="radio" name="layout_meja" value="Boardroom" x-model="layoutMeja" @change="setLayout('Boardroom')" class="text-blue-600">
                                        <span class="text-[11px] font-bold">Boardroom</span>
                                    </div>
                                    <span class="text-[9px] px-1 bg-slate-100 rounded text-slate-600" x-text="(roomCapacities[selectedVenueId]?.layouts['Boardroom'].tanpaSofa || 0) + ' Org'"></span>
                                </div>
                                <span class="text-[9.5px] text-slate-500 font-normal leading-tight">Satu meja rapat oval / panjang</span>
                            </label>

                            {{-- Round Table --}}
                            <label class="p-2.5 rounded-xl border text-xs cursor-pointer transition flex flex-col justify-between"
                                   :class="layoutMeja === 'Round Table' ? 'bg-blue-50/90 border-blue-500 ring-1 ring-blue-500 text-blue-900 font-bold shadow-2xs' : 'bg-white border-slate-200 hover:bg-slate-50 text-slate-700'"
                                   @click="setLayout('Round Table')">
                                <div class="flex items-center justify-between mb-1">
                                    <div class="flex items-center gap-1.5">
                                        <input type="radio" name="layout_meja" value="Round Table" x-model="layoutMeja" @change="setLayout('Round Table')" class="text-blue-600">
                                        <span class="text-[11px] font-bold">Round Table</span>
                                    </div>
                                    <span class="text-[9px] px-1 bg-slate-100 rounded text-slate-600" x-text="(roomCapacities[selectedVenueId]?.layouts['Round Table'].tanpaSofa || 0) + ' Org'"></span>
                                </div>
                                <span class="text-[9.5px] text-slate-500 font-normal leading-tight">Meja bundar kelompok diskusi</span>
                            </label>

                            {{-- Hollow Square --}}
                            <label class="p-2.5 rounded-xl border text-xs cursor-pointer transition flex flex-col justify-between"
                                   :class="layoutMeja === 'Hollow Square' ? 'bg-blue-50/90 border-blue-500 ring-1 ring-blue-500 text-blue-900 font-bold shadow-2xs' : 'bg-white border-slate-200 hover:bg-slate-50 text-slate-700'"
                                   @click="setLayout('Hollow Square')">
                                <div class="flex items-center justify-between mb-1">
                                    <div class="flex items-center gap-1.5">
                                        <input type="radio" name="layout_meja" value="Hollow Square" x-model="layoutMeja" @change="setLayout('Hollow Square')" class="text-blue-600">
                                        <span class="text-[11px] font-bold">Hollow Square</span>
                                    </div>
                                    <span class="text-[9px] px-1 bg-slate-100 rounded text-slate-600" x-text="(roomCapacities[selectedVenueId]?.layouts['Hollow Square'].tanpaSofa || 0) + ' Org'"></span>
                                </div>
                                <span class="text-[9.5px] text-slate-500 font-normal leading-tight">Persegi berongga tengah</span>
                            </label>

                            {{-- Custom Layout --}}
                            <label class="p-2.5 rounded-xl border text-xs cursor-pointer transition flex flex-col justify-between sm:col-span-2"
                                   :class="layoutMeja === 'Custom Layout' ? 'bg-blue-50/90 border-blue-500 ring-1 ring-blue-500 text-blue-900 font-bold shadow-2xs' : 'bg-white border-slate-200 hover:bg-slate-50 text-slate-700'"
                                   @click="setLayout('Custom Layout')">
                                <div class="flex items-center justify-between mb-1">
                                    <div class="flex items-center gap-1.5">
                                        <input type="radio" name="layout_meja" value="Custom Layout" x-model="layoutMeja" @change="setLayout('Custom Layout')" class="text-blue-600">
                                        <span class="text-[11px] font-bold">Custom / Menyesuaikan</span>
                                    </div>
                                    <span class="text-[9px] px-1 bg-slate-100 rounded text-slate-600" x-text="(roomCapacities[selectedVenueId]?.layouts['Custom Layout'].tanpaSofa || 0) + ' Org'"></span>
                                </div>
                                <span class="text-[9.5px] text-slate-500 font-normal leading-tight">Tata letak khusus disesuaikan kebutuhan acara</span>
                            </label>
                        </div>

                        {{-- ====================================================================
                            PREVIEW VISUAL ILUSTRASI CONTOH GAMBAR RUANGAN & KONTROL SOFA DEPAN
                        ===================================================================== --}}
                        <div class="mt-3 bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden">
                            {{-- Header Preview Layout --}}
                            <div class="px-4 py-3 bg-gradient-to-r from-slate-900 via-slate-800 to-indigo-950 text-white flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-7 h-7 rounded-lg bg-blue-500 text-white flex items-center justify-center font-bold text-xs shadow-xs">
                                        <svg class="w-3.5 h-3.5 inline-block align-[-2px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 6.75V15m6-6v8.25m.503 3.498l4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 00-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0z"/></svg>
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <h5 class="text-xs font-bold tracking-wide text-white">Contoh Visual Layout: <span class="text-amber-300" x-text="layoutMeja"></span></h5>
                                            <span class="text-[9.5px] font-semibold bg-white/20 text-white px-2 py-0.5 rounded-full" x-text="getCurrentVenueName()"></span>
                                        </div>
                                        <p class="text-[10.5px] text-slate-300 mt-0.5" x-text="getCurrentLayoutInfo().desc"></p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 shrink-0">
                                    <span class="text-[11px] text-slate-300">Kapasitas:</span>
                                    <span class="px-2.5 py-1 bg-emerald-500 text-white text-xs font-black rounded-lg shadow-2xs flex items-center gap-1"
                                          x-text="getCurrentCapacity() + ' Kursi'">
                                    </span>
                                </div>
                            </div>

                            <div class="p-4 sm:p-5 grid grid-cols-1 lg:grid-cols-12 gap-5 items-center">
                                {{-- KOLOM KIRI (LG: COL-7): DIAGRAM VISUAL RUANGAN (SVG INTERAKTIF) --}}
                                <div class="lg:col-span-7 flex flex-col items-center justify-center bg-slate-50/70 p-3 sm:p-4 rounded-xl border border-slate-200 relative">
                                    <div class="w-full max-w-[380px] aspect-[16/10] flex items-center justify-center">

                                        {{-- 1. SVG PREVIEW: U-SHAPE --}}
                                        <template x-if="layoutMeja === 'U-Shape'">
                                            <svg viewBox="0 0 400 240" class="w-full h-full drop-shadow-xs select-none">
                                                {{-- Room Frame --}}
                                                <rect width="400" height="240" rx="10" fill="#f8fafc" stroke="#cbd5e1" stroke-width="2"/>
                                                {{-- Stage & Screen --}}
                                                <rect x="90" y="12" width="220" height="26" rx="6" fill="#1e293b"/>
                                                <text x="200" y="29" text-anchor="middle" fill="#ffffff" font-size="10" font-weight="bold" font-family="sans-serif">PANGGUNG & SCREEN PRESENTASI</text>
                                                <rect x="98" y="16" width="20" height="18" rx="3" fill="#3b82f6"/>
                                                <text x="108" y="29" text-anchor="middle" fill="#ffffff" font-size="8.5"></text>

                                                {{-- U-Shape Tables --}}
                                                {{-- Top connector table --}}
                                                <rect x="90" y="58" width="220" height="26" rx="4" fill="#dbeafe" stroke="#3b82f6" stroke-width="2"/>
                                                {{-- Left arm table --}}
                                                <rect x="90" y="84" width="30" height="110" rx="4" fill="#dbeafe" stroke="#3b82f6" stroke-width="2"/>
                                                {{-- Right arm table --}}
                                                <rect x="280" y="84" width="30" height="110" rx="4" fill="#dbeafe" stroke="#3b82f6" stroke-width="2"/>

                                                {{-- Table Labels --}}
                                                <text x="200" y="74" text-anchor="middle" fill="#1e40af" font-size="9" font-weight="bold">Meja Pimpinan / Utama</text>
                                                <text x="105" y="145" text-anchor="middle" fill="#1e40af" font-size="8" font-weight="bold" transform="rotate(-90 105 145)">Meja Peserta Sisi Kiri</text>
                                                <text x="295" y="145" text-anchor="middle" fill="#1e40af" font-size="8" font-weight="bold" transform="rotate(90 295 145)">Meja Peserta Sisi Kanan</text>

                                                {{-- Outer Chairs (Blue) --}}
                                                {{-- Top outer chairs --}}
                                                <rect x="110" y="44" width="16" height="10" rx="2" fill="#2563eb"/>
                                                <rect x="140" y="44" width="16" height="10" rx="2" fill="#2563eb"/>
                                                <rect x="170" y="44" width="16" height="10" rx="2" fill="#2563eb"/>
                                                <rect x="200" y="44" width="16" height="10" rx="2" fill="#2563eb"/>
                                                <rect x="230" y="44" width="16" height="10" rx="2" fill="#2563eb"/>
                                                <rect x="260" y="44" width="16" height="10" rx="2" fill="#2563eb"/>

                                                {{-- Left outer chairs --}}
                                                <rect x="74" y="94" width="10" height="16" rx="2" fill="#2563eb"/>
                                                <rect x="74" y="118" width="10" height="16" rx="2" fill="#2563eb"/>
                                                <rect x="74" y="142" width="10" height="16" rx="2" fill="#2563eb"/>
                                                <rect x="74" y="166" width="10" height="16" rx="2" fill="#2563eb"/>

                                                {{-- Right outer chairs --}}
                                                <rect x="316" y="94" width="10" height="16" rx="2" fill="#2563eb"/>
                                                <rect x="316" y="118" width="10" height="16" rx="2" fill="#2563eb"/>
                                                <rect x="316" y="142" width="10" height="16" rx="2" fill="#2563eb"/>
                                                <rect x="316" y="166" width="10" height="16" rx="2" fill="#2563eb"/>

                                                {{-- Center Presenter Area --}}
                                                <rect x="135" y="105" width="130" height="60" rx="8" fill="#eff6ff" stroke="#93c5fd" stroke-dasharray="4 4"/>
                                                <text x="200" y="132" text-anchor="middle" fill="#2563eb" font-size="9" font-weight="bold">Area Presenter / Pemateri</text>
                                                <text x="200" y="146" text-anchor="middle" fill="#64748b" font-size="8">Format Interaktif U-Shape</text>

                                                {{-- Door Entrance --}}
                                                <text x="345" y="226" text-anchor="middle" fill="#64748b" font-size="9" font-weight="600">Pintu Masuk</text>
                                            </svg>
                                        </template>

                                        {{-- 2. SVG PREVIEW: CLASSROOM --}}
                                        <template x-if="layoutMeja === 'Classroom'">
                                            <svg viewBox="0 0 400 240" class="w-full h-full drop-shadow-xs select-none">
                                                <rect width="400" height="240" rx="10" fill="#f8fafc" stroke="#cbd5e1" stroke-width="2"/>
                                                {{-- Stage & Screen --}}
                                                <rect x="70" y="12" width="260" height="26" rx="6" fill="#1e293b"/>
                                                <text x="200" y="29" text-anchor="middle" fill="#ffffff" font-size="10" font-weight="bold" font-family="sans-serif">PANGGUNG & VIDEOTRON UTAMA</text>
                                                <rect x="80" y="16" width="20" height="18" rx="3" fill="#3b82f6"/>
                                                <text x="90" y="29" text-anchor="middle" fill="#ffffff" font-size="8.5"></text>

                                                {{-- Front Sofa VIP (Jika dengan Sofa Depan) --}}
                                                <g x-show="sofaDepan === 'dengan'">
                                                    <rect x="65" y="48" width="125" height="22" rx="5" fill="#fef3c7" stroke="#f59e0b" stroke-width="2"/>
                                                    <text x="127" y="62" text-anchor="middle" fill="#b45309" font-size="8.5" font-weight="bold">SOFA VIP BARIS 1</text>
                                                    <rect x="210" y="48" width="125" height="22" rx="5" fill="#fef3c7" stroke="#f59e0b" stroke-width="2"/>
                                                    <text x="272" y="62" text-anchor="middle" fill="#b45309" font-size="8.5" font-weight="bold">SOFA VIP BARIS 2</text>
                                                </g>

                                                {{-- Classroom Desk Rows (Left & Right Column with Center Aisle) --}}
                                                {{-- Row 1 --}}
                                                <rect x="65" :y="sofaDepan === 'dengan' ? 82 : 56" width="125" height="18" rx="3" fill="#dbeafe" stroke="#3b82f6" stroke-width="1.5"/>
                                                <rect x="210" :y="sofaDepan === 'dengan' ? 82 : 56" width="125" height="18" rx="3" fill="#dbeafe" stroke="#3b82f6" stroke-width="1.5"/>
                                                {{-- Row 1 Chairs --}}
                                                <rect x="75" :y="sofaDepan === 'dengan' ? 104 : 78" width="22" height="9" rx="2" fill="#2563eb"/>
                                                <rect x="115" :y="sofaDepan === 'dengan' ? 104 : 78" width="22" height="9" rx="2" fill="#2563eb"/>
                                                <rect x="155" :y="sofaDepan === 'dengan' ? 104 : 78" width="22" height="9" rx="2" fill="#2563eb"/>
                                                <rect x="220" :y="sofaDepan === 'dengan' ? 104 : 78" width="22" height="9" rx="2" fill="#2563eb"/>
                                                <rect x="260" :y="sofaDepan === 'dengan' ? 104 : 78" width="22" height="9" rx="2" fill="#2563eb"/>
                                                <rect x="300" :y="sofaDepan === 'dengan' ? 104 : 78" width="22" height="9" rx="2" fill="#2563eb"/>

                                                {{-- Row 2 --}}
                                                <rect x="65" :y="sofaDepan === 'dengan' ? 122 : 98" width="125" height="18" rx="3" fill="#dbeafe" stroke="#3b82f6" stroke-width="1.5"/>
                                                <rect x="210" :y="sofaDepan === 'dengan' ? 122 : 98" width="125" height="18" rx="3" fill="#dbeafe" stroke="#3b82f6" stroke-width="1.5"/>
                                                {{-- Row 2 Chairs --}}
                                                <rect x="75" :y="sofaDepan === 'dengan' ? 144 : 120" width="22" height="9" rx="2" fill="#2563eb"/>
                                                <rect x="115" :y="sofaDepan === 'dengan' ? 144 : 120" width="22" height="9" rx="2" fill="#2563eb"/>
                                                <rect x="155" :y="sofaDepan === 'dengan' ? 144 : 120" width="22" height="9" rx="2" fill="#2563eb"/>
                                                <rect x="220" :y="sofaDepan === 'dengan' ? 144 : 120" width="22" height="9" rx="2" fill="#2563eb"/>
                                                <rect x="260" :y="sofaDepan === 'dengan' ? 144 : 120" width="22" height="9" rx="2" fill="#2563eb"/>
                                                <rect x="300" :y="sofaDepan === 'dengan' ? 144 : 120" width="22" height="9" rx="2" fill="#2563eb"/>

                                                {{-- Row 3 --}}
                                                <rect x="65" :y="sofaDepan === 'dengan' ? 162 : 140" width="125" height="18" rx="3" fill="#dbeafe" stroke="#3b82f6" stroke-width="1.5"/>
                                                <rect x="210" :y="sofaDepan === 'dengan' ? 162 : 140" width="125" height="18" rx="3" fill="#dbeafe" stroke="#3b82f6" stroke-width="1.5"/>
                                                {{-- Row 3 Chairs --}}
                                                <rect x="75" :y="sofaDepan === 'dengan' ? 184 : 162" width="22" height="9" rx="2" fill="#2563eb"/>
                                                <rect x="115" :y="sofaDepan === 'dengan' ? 184 : 162" width="22" height="9" rx="2" fill="#2563eb"/>
                                                <rect x="155" :y="sofaDepan === 'dengan' ? 184 : 162" width="22" height="9" rx="2" fill="#2563eb"/>
                                                <rect x="220" :y="sofaDepan === 'dengan' ? 184 : 162" width="22" height="9" rx="2" fill="#2563eb"/>
                                                <rect x="260" :y="sofaDepan === 'dengan' ? 184 : 162" width="22" height="9" rx="2" fill="#2563eb"/>
                                                <rect x="300" :y="sofaDepan === 'dengan' ? 184 : 162" width="22" height="9" rx="2" fill="#2563eb"/>

                                                {{-- Center Aisle label --}}
                                                <text x="200" y="145" text-anchor="middle" fill="#94a3b8" font-size="8" font-weight="600" transform="rotate(-90 200 145)">LORONG UTAMA</text>
                                                {{-- Door --}}
                                                <text x="345" y="226" text-anchor="middle" fill="#64748b" font-size="9" font-weight="600">Pintu Masuk</text>
                                            </svg>
                                        </template>

                                        {{-- 3. SVG PREVIEW: THEATRE --}}
                                        <template x-if="layoutMeja === 'Theatre'">
                                            <svg viewBox="0 0 400 240" class="w-full h-full drop-shadow-xs select-none">
                                                <rect width="400" height="240" rx="10" fill="#f8fafc" stroke="#cbd5e1" stroke-width="2"/>
                                                {{-- Stage & Screen --}}
                                                <rect x="60" y="12" width="280" height="26" rx="6" fill="#1e293b"/>
                                                <text x="200" y="29" text-anchor="middle" fill="#ffffff" font-size="10" font-weight="bold" font-family="sans-serif">PANGGUNG AULA & PODIUM PIDATO</text>

                                                {{-- Front Sofa VIP --}}
                                                <g x-show="sofaDepan === 'dengan'">
                                                    <rect x="55" y="46" width="135" height="20" rx="4" fill="#fef3c7" stroke="#f59e0b" stroke-width="2"/>
                                                    <text x="122" y="60" text-anchor="middle" fill="#b45309" font-size="8.5" font-weight="bold">SOFA VIP PANGGUNG 1</text>
                                                    <rect x="210" y="46" width="135" height="20" rx="4" fill="#fef3c7" stroke="#f59e0b" stroke-width="2"/>
                                                    <text x="277" y="60" text-anchor="middle" fill="#b45309" font-size="8.5" font-weight="bold">SOFA VIP PANGGUNG 2</text>
                                                </g>

                                                {{-- Dense Theatre Chair Rows --}}
                                                <g :transform="sofaDepan === 'dengan' ? 'translate(0, 24)' : 'translate(0, 0)'">
                                                    {{-- Row 1 --}}
                                                    <g fill="#2563eb">
                                                        <rect x="55" y="52" width="18" height="12" rx="2"/><rect x="80" y="52" width="18" height="12" rx="2"/><rect x="105" y="52" width="18" height="12" rx="2"/><rect x="130" y="52" width="18" height="12" rx="2"/><rect x="155" y="52" width="18" height="12" rx="2"/><rect x="180" y="52" width="18" height="12" rx="2"/>
                                                        <rect x="215" y="52" width="18" height="12" rx="2"/><rect x="240" y="52" width="18" height="12" rx="2"/><rect x="265" y="52" width="18" height="12" rx="2"/><rect x="290" y="52" width="18" height="12" rx="2"/><rect x="315" y="52" width="18" height="12" rx="2"/><rect x="340" y="52" width="18" height="12" rx="2"/>
                                                    </g>
                                                    {{-- Row 2 --}}
                                                    <g fill="#2563eb">
                                                        <rect x="55" y="74" width="18" height="12" rx="2"/><rect x="80" y="74" width="18" height="12" rx="2"/><rect x="105" y="74" width="18" height="12" rx="2"/><rect x="130" y="74" width="18" height="12" rx="2"/><rect x="155" y="74" width="18" height="12" rx="2"/><rect x="180" y="74" width="18" height="12" rx="2"/>
                                                        <rect x="215" y="74" width="18" height="12" rx="2"/><rect x="240" y="74" width="18" height="12" rx="2"/><rect x="265" y="74" width="18" height="12" rx="2"/><rect x="290" y="74" width="18" height="12" rx="2"/><rect x="315" y="74" width="18" height="12" rx="2"/><rect x="340" y="74" width="18" height="12" rx="2"/>
                                                    </g>
                                                    {{-- Row 3 --}}
                                                    <g fill="#2563eb">
                                                        <rect x="55" y="96" width="18" height="12" rx="2"/><rect x="80" y="96" width="18" height="12" rx="2"/><rect x="105" y="96" width="18" height="12" rx="2"/><rect x="130" y="96" width="18" height="12" rx="2"/><rect x="155" y="96" width="18" height="12" rx="2"/><rect x="180" y="96" width="18" height="12" rx="2"/>
                                                        <rect x="215" y="96" width="18" height="12" rx="2"/><rect x="240" y="96" width="18" height="12" rx="2"/><rect x="265" y="96" width="18" height="12" rx="2"/><rect x="290" y="96" width="18" height="12" rx="2"/><rect x="315" y="96" width="18" height="12" rx="2"/><rect x="340" y="96" width="18" height="12" rx="2"/>
                                                    </g>
                                                    {{-- Row 4 --}}
                                                    <g fill="#2563eb">
                                                        <rect x="55" y="118" width="18" height="12" rx="2"/><rect x="80" y="118" width="18" height="12" rx="2"/><rect x="105" y="118" width="18" height="12" rx="2"/><rect x="130" y="118" width="18" height="12" rx="2"/><rect x="155" y="118" width="18" height="12" rx="2"/><rect x="180" y="118" width="18" height="12" rx="2"/>
                                                        <rect x="215" y="118" width="18" height="12" rx="2"/><rect x="240" y="118" width="18" height="12" rx="2"/><rect x="265" y="118" width="18" height="12" rx="2"/><rect x="290" y="118" width="18" height="12" rx="2"/><rect x="315" y="118" width="18" height="12" rx="2"/><rect x="340" y="118" width="18" height="12" rx="2"/>
                                                    </g>
                                                    {{-- Row 5 --}}
                                                    <g fill="#2563eb">
                                                        <rect x="55" y="140" width="18" height="12" rx="2"/><rect x="80" y="140" width="18" height="12" rx="2"/><rect x="105" y="140" width="18" height="12" rx="2"/><rect x="130" y="140" width="18" height="12" rx="2"/><rect x="155" y="140" width="18" height="12" rx="2"/><rect x="180" y="140" width="18" height="12" rx="2"/>
                                                        <rect x="215" y="140" width="18" height="12" rx="2"/><rect x="240" y="140" width="18" height="12" rx="2"/><rect x="265" y="140" width="18" height="12" rx="2"/><rect x="290" y="140" width="18" height="12" rx="2"/><rect x="315" y="140" width="18" height="12" rx="2"/><rect x="340" y="140" width="18" height="12" rx="2"/>
                                                    </g>
                                                </g>

                                                <text x="345" y="226" text-anchor="middle" fill="#64748b" font-size="9" font-weight="600">Pintu Masuk</text>
                                            </svg>
                                        </template>

                                        {{-- 4. SVG PREVIEW: BOARDROOM --}}
                                        <template x-if="layoutMeja === 'Boardroom'">
                                            <svg viewBox="0 0 400 240" class="w-full h-full drop-shadow-xs select-none">
                                                <rect width="400" height="240" rx="10" fill="#f8fafc" stroke="#cbd5e1" stroke-width="2"/>
                                                {{-- Smart Display --}}
                                                <rect x="130" y="12" width="140" height="20" rx="4" fill="#334155"/>
                                                <text x="200" y="26" text-anchor="middle" fill="#ffffff" font-size="9" font-weight="bold">SMART TV DISPLAY VICON</text>

                                                {{-- Large Central Executive Table --}}
                                                <rect x="80" y="54" width="240" height="116" rx="24" fill="#dbeafe" stroke="#3b82f6" stroke-width="2.5"/>
                                                <text x="200" y="106" text-anchor="middle" fill="#1e40af" font-size="12" font-weight="bold">MEJA RAPAT EKSEKUTIF</text>
                                                <text x="200" y="124" text-anchor="middle" fill="#3b82f6" font-size="8.5">Mic Conference Polycom 360°</text>

                                                {{-- Leader Chair (Top & Bottom Head) --}}
                                                <rect x="185" y="38" width="30" height="12" rx="3" fill="#1d4ed8"/>
                                                <text x="200" y="47" text-anchor="middle" fill="#ffffff" font-size="7" font-weight="bold">Pimpinan</text>

                                                <rect x="185" y="174" width="30" height="12" rx="3" fill="#1d4ed8"/>

                                                {{-- Side Chairs --}}
                                                {{-- Left Chairs --}}
                                                <rect x="62" y="68" width="14" height="20" rx="3" fill="#2563eb"/>
                                                <rect x="62" y="96" width="14" height="20" rx="3" fill="#2563eb"/>
                                                <rect x="62" y="124" width="14" height="20" rx="3" fill="#2563eb"/>
                                                <rect x="62" y="152" width="14" height="20" rx="3" fill="#2563eb"/>

                                                {{-- Right Chairs --}}
                                                <rect x="324" y="68" width="14" height="20" rx="3" fill="#2563eb"/>
                                                <rect x="324" y="96" width="14" height="20" rx="3" fill="#2563eb"/>
                                                <rect x="324" y="124" width="14" height="20" rx="3" fill="#2563eb"/>
                                                <rect x="324" y="152" width="14" height="20" rx="3" fill="#2563eb"/>

                                                <text x="345" y="226" text-anchor="middle" fill="#64748b" font-size="9" font-weight="600">Pintu Masuk</text>
                                            </svg>
                                        </template>

                                        {{-- 5. SVG PREVIEW: ROUND TABLE / HOLLOW SQUARE / CUSTOM --}}
                                        <template x-if="layoutMeja === 'Round Table' || layoutMeja === 'Hollow Square' || layoutMeja === 'Custom Layout'">
                                            <svg viewBox="0 0 400 240" class="w-full h-full drop-shadow-xs select-none">
                                                <rect width="400" height="240" rx="10" fill="#f8fafc" stroke="#cbd5e1" stroke-width="2"/>
                                                <rect x="90" y="12" width="220" height="24" rx="5" fill="#1e293b"/>
                                                <text x="200" y="28" text-anchor="middle" fill="#ffffff" font-size="10" font-weight="bold">PANGGUNG PRESENTASI</text>

                                                {{-- Round Tables --}}
                                                <template x-if="layoutMeja === 'Round Table'">
                                                    <g>
                                                        {{-- Table 1 --}}
                                                        <circle cx="110" cy="95" r="32" fill="#dbeafe" stroke="#3b82f6" stroke-width="2"/>
                                                        <text x="110" y="98" text-anchor="middle" fill="#1e40af" font-size="8" font-weight="bold">Meja 1</text>
                                                        <circle cx="110" cy="56" r="6" fill="#2563eb"/><circle cx="145" cy="80" r="6" fill="#2563eb"/><circle cx="145" cy="115" r="6" fill="#2563eb"/><circle cx="110" cy="134" r="6" fill="#2563eb"/><circle cx="75" cy="115" r="6" fill="#2563eb"/><circle cx="75" cy="80" r="6" fill="#2563eb"/>

                                                        {{-- Table 2 --}}
                                                        <circle cx="290" cy="95" r="32" fill="#dbeafe" stroke="#3b82f6" stroke-width="2"/>
                                                        <text x="290" y="98" text-anchor="middle" fill="#1e40af" font-size="8" font-weight="bold">Meja 2</text>
                                                        <circle cx="290" cy="56" r="6" fill="#2563eb"/><circle cx="325" cy="80" r="6" fill="#2563eb"/><circle cx="325" cy="115" r="6" fill="#2563eb"/><circle cx="290" cy="134" r="6" fill="#2563eb"/><circle cx="255" cy="115" r="6" fill="#2563eb"/><circle cx="255" cy="80" r="6" fill="#2563eb"/>

                                                        {{-- Table 3 --}}
                                                        <circle cx="200" cy="165" r="32" fill="#dbeafe" stroke="#3b82f6" stroke-width="2"/>
                                                        <text x="200" y="168" text-anchor="middle" fill="#1e40af" font-size="8" font-weight="bold">Meja 3</text>
                                                        <circle cx="200" cy="126" r="6" fill="#2563eb"/><circle cx="235" cy="150" r="6" fill="#2563eb"/><circle cx="235" cy="185" r="6" fill="#2563eb"/><circle cx="200" cy="204" r="6" fill="#2563eb"/><circle cx="165" cy="185" r="6" fill="#2563eb"/><circle cx="165" cy="150" r="6" fill="#2563eb"/>
                                                    </g>
                                                </template>

                                                {{-- Hollow Square --}}
                                                <template x-if="layoutMeja === 'Hollow Square'">
                                                    <g>
                                                        <rect x="100" y="60" width="200" height="120" rx="6" fill="#eff6ff" stroke="#3b82f6" stroke-width="2"/>
                                                        <rect x="140" y="86" width="120" height="68" rx="4" fill="#f8fafc" stroke="#cbd5e1" stroke-width="1.5"/>
                                                        <text x="200" y="124" text-anchor="middle" fill="#64748b" font-size="8.5" font-weight="bold">Ruang Terbuka Tengah</text>
                                                    </g>
                                                </template>

                                                {{-- Custom Layout --}}
                                                <template x-if="layoutMeja === 'Custom Layout'">
                                                    <g>
                                                        <rect x="70" y="60" width="260" height="120" rx="8" fill="#eff6ff" stroke="#3b82f6" stroke-width="1.5" stroke-dasharray="6 4"/>
                                                        <text x="200" y="115" text-anchor="middle" fill="#1e40af" font-size="11" font-weight="bold">TATA LETAK FLEKSIBEL (CUSTOM)</text>
                                                        <text x="200" y="132" text-anchor="middle" fill="#64748b" font-size="8.5">Disesuaikan dengan permintaan khusus pemohon</text>
                                                    </g>
                                                </template>

                                                <text x="345" y="226" text-anchor="middle" fill="#64748b" font-size="9" font-weight="600">Pintu Masuk</text>
                                            </svg>
                                        </template>

                                    </div>

                                    {{-- Legend Floorplan --}}
                                    <div class="mt-2 flex items-center justify-center gap-3 flex-wrap text-[10px] text-slate-600 font-medium">
                                        <span class="flex items-center gap-1"><span class="w-3 h-2 rounded-xs bg-slate-800 inline-block"></span> Panggung/Screen</span>
                                        <span class="flex items-center gap-1"><span class="w-3 h-2 rounded-xs bg-blue-100 border border-blue-500 inline-block"></span> Meja Rapat</span>
                                        <span class="flex items-center gap-1"><span class="w-3 h-2 rounded-xs bg-blue-600 inline-block"></span> Kursi Peserta</span>
                                        <span class="flex items-center gap-1" x-show="isSofaOptionAvailable()"><span class="w-3 h-2 rounded-xs bg-amber-100 border border-amber-500 inline-block"></span> Sofa VIP Depan</span>
                                    </div>
                                </div>

                                {{-- KOLOM KANAN (LG: COL-5): KONTROL SOFA DEPAN & PERBANDINGAN KAPASITAS RESMI --}}
                                <div class="lg:col-span-5 space-y-3">
                                    {{-- OPSI SOFA DEPAN PANGGUNG (SESUAI DOKUMEN KAPASITAS BPS) --}}
                                    <div class="p-3 bg-slate-50/80 rounded-xl border border-slate-200 shadow-2xs space-y-2">
                                        <div class="flex items-center justify-between">
                                            <label class="block text-[11px] font-bold text-slate-800 uppercase tracking-wider">
                                                <svg class="w-3.5 h-3.5 inline-block align-[-2px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75"/></svg> Opsi Sofa Depan Panggung
                                            </label>
                                            <template x-if="isSofaOptionAvailable()">
                                                <span class="text-[9.5px] font-bold text-amber-800 bg-amber-100 px-1.5 py-0.5 rounded">Tersedia</span>
                                            </template>
                                            <template x-if="!isSofaOptionAvailable()">
                                                <span class="text-[9.5px] font-bold text-slate-500 bg-slate-200 px-1.5 py-0.5 rounded">Tidak Ada Opsi</span>
                                            </template>
                                        </div>

                                        <div class="space-y-1.5">
                                            {{-- Radio: Tanpa Sofa --}}
                                            <label class="flex items-center justify-between p-2 rounded-lg border cursor-pointer text-xs transition"
                                                   :class="sofaDepan === 'tanpa' ? 'bg-blue-50 border-blue-500 text-blue-900 font-bold shadow-2xs ring-1 ring-blue-500/50' : 'bg-white border-slate-200 hover:bg-slate-100 text-slate-700'"
                                                   @click="setSofaDepan('tanpa')">
                                                <div class="flex items-center gap-2">
                                                    <input type="radio" name="sofa_depan" value="tanpa" :checked="sofaDepan === 'tanpa'" class="text-blue-600">
                                                    <div>
                                                        <span class="block text-[11px]">Tanpa Sofa Depan Panggung</span>
                                                        <span class="text-[9.5px] text-slate-400 font-normal">Kapasitas maksimal baris kursi biasa</span>
                                                    </div>
                                                </div>
                                                <span class="text-[11px] font-black text-blue-700 bg-blue-100/60 px-2 py-0.5 rounded border border-blue-200"
                                                      x-text="(getCurrentLayoutInfo().tanpaSofa || 0) + ' Org'"></span>
                                            </label>

                                            {{-- Radio: Dengan Sofa --}}
                                            <label class="flex items-center justify-between p-2 rounded-lg border text-xs transition"
                                                   :class="!isSofaOptionAvailable() ? 'opacity-40 cursor-not-allowed bg-slate-100 border-slate-200 text-slate-400' : (sofaDepan === 'dengan' ? 'bg-amber-50 border-amber-500 text-amber-950 font-bold shadow-2xs ring-1 ring-amber-500/50 cursor-pointer' : 'bg-white border-slate-200 hover:bg-slate-100 text-slate-700 cursor-pointer')"
                                                   @click="if(isSofaOptionAvailable()) setSofaDepan('dengan')">
                                                <div class="flex items-center gap-2">
                                                    <input type="radio" name="sofa_depan" value="dengan" :checked="sofaDepan === 'dengan'" :disabled="!isSofaOptionAvailable()" class="text-amber-600">
                                                    <div>
                                                        <span class="block text-[11px]">Dengan Sofa Depan Panggung</span>
                                                        <span class="text-[9.5px] text-slate-400 font-normal">Sofa VIP khusus di baris depan</span>
                                                    </div>
                                                </div>
                                                <span class="text-[11px] font-black px-2 py-0.5 rounded border"
                                                      :class="sofaDepan === 'dengan' ? 'text-amber-900 bg-amber-100/80 border-amber-300' : 'text-slate-600 bg-slate-100 border-slate-200'"
                                                      x-text="isSofaOptionAvailable() ? (getCurrentLayoutInfo().denganSofa + ' Org') : 'N/A'"></span>
                                            </label>
                                        </div>

                                        <template x-if="!isSofaOptionAvailable()">
                                            <p class="text-[10px] text-slate-500 italic bg-white p-2 rounded-lg border border-slate-200 mt-1">
                                                <svg class="w-3.5 h-3.5 inline-block align-[-2px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/></svg> Format <strong><span x-text="layoutMeja"></span></strong> menggunakan 1 formasi meja terpadu sehingga tidak menggunakan konfigurasi sofa depan panggung.
                                            </p>
                                        </template>

                                        {{-- Indikator Kapasitas & Validasi Peserta --}}
                                        <div x-show="parseInt(jumlahPeserta) > 0" class="pt-2 border-t border-slate-200/70 text-[11px]">
                                            <div x-show="parseInt(jumlahPeserta) > getCurrentCapacity()" class="flex items-center gap-1.5 text-rose-600 font-bold bg-rose-50 px-2.5 py-1.5 rounded-lg border border-rose-200">
                                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                                                <span>Peserta (<span x-text="jumlahPeserta"></span> org) melebihi kapasitas maksimum (<span x-text="getCurrentCapacity()"></span> org)!</span>
                                            </div>
                                            <div x-show="parseInt(jumlahPeserta) <= getCurrentCapacity()" class="flex items-center gap-1.5 text-emerald-700 font-medium bg-emerald-50 px-2.5 py-1 rounded-lg border border-emerald-200 text-[10.5px]">
                                                <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                <span>Peserta sesuai kapasitas (<span x-text="jumlahPeserta"></span> / <span x-text="getCurrentCapacity()"></span> org)</span>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Tabel Mini Standar Kapasitas Resmi Ruangan Terpilih --}}
                                    <div class="p-3 bg-slate-50/80 rounded-xl border border-slate-200 text-xs space-y-1.5">
                                        <div class="flex items-center justify-between">
                                            <span class="font-bold text-slate-800 text-[10.5px]"><svg class="w-3.5 h-3.5 inline-block align-[-2px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.35 3.836c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m8.9-4.414c.376.023.75.05 1.124.08 1.131.094 1.976 1.057 1.976 2.192V16.5A2.25 2.25 0 0118 18.75h-2.25m-7.5-10.5H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V18.75m-7.5-10.5h6.375c.621 0 1.125.504 1.125 1.125v9.375m-8.25-3l1.5 1.5 3-3.75"/></svg> Standar Kapasitas: <strong class="text-blue-700" x-text="getCurrentVenueName()"></strong></span>
                                            <span class="text-[9.5px] text-slate-400">Tabel Resmi</span>
                                        </div>
                                        <div class="grid grid-cols-2 gap-1.5 text-center text-[10px]">
                                            <div class="p-1.5 rounded-lg border transition" :class="layoutMeja === 'Theatre' ? 'bg-blue-100/80 border-blue-400 font-bold text-blue-900' : 'bg-white border-slate-200 text-slate-600'">
                                                <span class="block text-[9.5px] text-slate-500">Theatre</span>
                                                <span class="text-[11px] font-black text-blue-700" x-text="(roomCapacities[selectedVenueId]?.layouts['Theatre'].tanpaSofa || 0) + ' / ' + (roomCapacities[selectedVenueId]?.layouts['Theatre'].denganSofa || 0) + ' Org'"></span>
                                                <span class="block text-[8.5px] text-slate-400">Tanpa / Dgn Sofa</span>
                                            </div>
                                            <div class="p-1.5 rounded-lg border transition" :class="layoutMeja === 'Classroom' ? 'bg-blue-100/80 border-blue-400 font-bold text-blue-900' : 'bg-white border-slate-200 text-slate-600'">
                                                <span class="block text-[9.5px] text-slate-500">Classroom</span>
                                                <span class="text-[11px] font-black text-blue-700" x-text="(roomCapacities[selectedVenueId]?.layouts['Classroom'].tanpaSofa || 0) + ' / ' + (roomCapacities[selectedVenueId]?.layouts['Classroom'].denganSofa || 0) + ' Org'"></span>
                                                <span class="block text-[8.5px] text-slate-400">Tanpa / Dgn Sofa</span>
                                            </div>
                                            <div class="p-1.5 rounded-lg border transition" :class="layoutMeja === 'U-Shape' ? 'bg-blue-100/80 border-blue-400 font-bold text-blue-900' : 'bg-white border-slate-200 text-slate-600'">
                                                <span class="block text-[9.5px] text-slate-500">U-Shape</span>
                                                <span class="text-[11px] font-black text-blue-700" x-text="(roomCapacities[selectedVenueId]?.layouts['U-Shape'].tanpaSofa || 0) + ' Org'"></span>
                                                <span class="block text-[8.5px] text-slate-400">Tanpa Sofa Depan</span>
                                            </div>
                                            <div class="p-1.5 rounded-lg border transition" :class="layoutMeja === 'Boardroom' ? 'bg-blue-100/80 border-blue-400 font-bold text-blue-900' : 'bg-white border-slate-200 text-slate-600'">
                                                <span class="block text-[9.5px] text-slate-500">Boardroom</span>
                                                <span class="text-[11px] font-black text-blue-700" x-text="(roomCapacities[selectedVenueId]?.layouts['Boardroom'].tanpaSofa || 0) + ' Org'"></span>
                                                <span class="block text-[8.5px] text-slate-400">Tanpa Sofa Depan</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ========================================================
                        B. SET UP PODIUM (PANGGUNG, PODIUM, KURSI & SPANDUK)
                    ========================================================= --}}
                    <div class="pt-3 border-t border-slate-200 space-y-3">
                        <label class="flex items-center gap-2 text-xs font-bold text-slate-800 uppercase tracking-wider">
                            <span class="w-5 h-5 rounded-md bg-slate-200 text-slate-700 flex items-center justify-center text-[10px] font-bold">B</span>
                            <span>Set Up Podium & Panggung Depan</span>
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 bg-white p-3.5 rounded-xl border border-slate-200">
                            {{-- Tipe Podium --}}
                            <div class="sm:col-span-5">
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">
                                    Tipe Set Up Podium / Panggung
                                </label>
                                <select name="tipe_podium" x-model="tipePodium" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 font-semibold">
                                    <option value="Podium Standar">Podium Standar (Mimbar Pidato)</option>
                                    <option value="Meja & Kursi Panelis">Meja & Kursi Panelis / Narasumber</option>
                                    <option value="Set Sofa VIP">Set Sofa VIP Panggung</option>
                                    <option value="Kombinasi (Podium + Panelis)">Kombinasi (Podium + Meja Panelis)</option>
                                    <option value="Tanpa Podium">Tanpa Podium / Flat</option>
                                </select>
                            </div>

                            {{-- Jumlah Kursi / Sofa Podium --}}
                            <div class="sm:col-span-3">
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">
                                    Jml Kursi/Sofa Panggung
                                </label>
                                <select name="jumlah_kursi_podium" x-model="jumlahKursiPodium" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 font-semibold">
                                    <option value="0">0 (Tanpa Kursi)</option>
                                    <option value="1">1 Kursi/Sofa</option>
                                    <option value="2">2 Kursi/Sofa</option>
                                    <option value="3">3 Kursi/Sofa</option>
                                    <option value="4">4 Kursi/Sofa</option>
                                    <option value="5">5 Kursi/Sofa</option>
                                    <option value="6">6 Kursi/Sofa</option>
                                    <option value="8">8 Kursi/Sofa</option>
                                    <option value="10">10+ Kursi/Sofa</option>
                                </select>
                            </div>

                            {{-- Pasang Spanduk & Keterangan --}}
                            <div class="sm:col-span-4 space-y-1.5">
                                <label class="flex items-center gap-2 text-[11px] font-bold text-slate-800 cursor-pointer pt-1">
                                    <input type="checkbox" name="pasang_spanduk" value="1" x-model="pasangSpanduk" class="rounded text-blue-600 focus:ring-blue-500">
                                    <span>Pasang Spanduk / Backdrop</span>
                                </label>
                                <div x-show="pasangSpanduk">
                                    <input type="text"
                                           name="keterangan_spanduk"
                                           x-model="keteranganSpanduk"
                                           placeholder="Ukuran / tema spanduk (contoh: 4x2 m)"
                                           class="w-full px-2.5 py-1.5 text-[11px] rounded-lg border border-slate-200 bg-slate-50 focus:bg-white focus:outline-none focus:ring-1 focus:ring-blue-500">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ========================================================
                        C. TAMBAH SPECIAL REQUEST (11 ITEM LENGKAP)
                    ========================================================= --}}
                    <div class="pt-3 border-t border-slate-200 space-y-3">
                        <div class="flex items-center justify-between">
                            <label class="flex items-center gap-2 text-xs font-bold text-slate-800 uppercase tracking-wider">
                                <span class="w-5 h-5 rounded-md bg-slate-200 text-slate-700 flex items-center justify-center text-[10px] font-bold">C</span>
                                <span>Tambah Special Request (Permintaan Khusus)</span>
                            </label>
                            <span class="text-[10px] text-slate-400">11 Kebutuhan Sarpras</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2.5">
                            {{-- 1. Tambah Mic (dengan selector jumlah) --}}
                            <div class="p-2.5 rounded-xl bg-white border border-slate-200 shadow-2xs space-y-1 sm:col-span-2 lg:col-span-1">
                                <label class="block text-xs font-bold text-slate-800">
                                    <svg class="w-3 h-3 inline-block align-[-1px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18.75a6 6 0 006-6v-1.5m-6 7.5a6 6 0 01-6-6v-1.5m6 7.5v3.75m-3.75 0h7.5M12 15.75a3 3 0 01-3-3V4.5a3 3 0 116 0v8.25a3 3 0 01-3 3z"/></svg> 1. Tambah Mic
                                </label>
                                <select name="mic_count" x-model="micCount" class="w-full px-2.5 py-1.5 text-[11px] rounded-lg border border-slate-200 bg-slate-50 focus:bg-white focus:outline-none focus:ring-1 focus:ring-blue-500 font-semibold">
                                    <option value="0">Tidak butuh Mic</option>
                                    <option value="1">1 Microphone Wireless</option>
                                    <option value="2">2 Microphone Wireless</option>
                                    <option value="3">3 Microphone Wireless</option>
                                    <option value="4">4 Microphone Wireless</option>
                                    <option value="5">5 Microphone Wireless</option>
                                    <option value="6+">6+ Microphone Panelis</option>
                                </select>
                            </div>

                            {{-- 2. Tambah Colokan --}}
                            <label class="flex items-center gap-2.5 p-2.5 rounded-xl bg-white border border-slate-200 hover:bg-slate-50 text-xs cursor-pointer shadow-2xs transition">
                                <input type="checkbox" name="special_requests_list[]" value="Tambah Colokan / Stop Kontak Ekstra" checked class="rounded text-blue-600">
                                <div>
                                    <span class="font-bold text-slate-800"><svg class="w-3.5 h-3.5 inline-block align-[-2px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z"/></svg> 2. Tambah Colokan</span>
                                    <p class="text-[10px] text-slate-400">Terminal stop kontak ekstra</p>
                                </div>
                            </label>

                            {{-- 3. Sofa Depan VIP --}}
                            <label class="flex items-center gap-2.5 p-2.5 rounded-xl bg-white border border-slate-200 hover:bg-slate-50 text-xs cursor-pointer shadow-2xs transition">
                                <input type="checkbox" name="special_requests_list[]" value="Sofa Depan VIP" class="rounded text-blue-600">
                                <div>
                                    <span class="font-bold text-slate-800"><svg class="w-3.5 h-3.5 inline-block align-[-2px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75"/></svg> 3. Sofa Depan VIP</span>
                                    <p class="text-[10px] text-slate-400">Baris depan tamu VIP</p>
                                </div>
                            </label>

                            {{-- 4. Monitor Pimpinan --}}
                            <label class="flex items-center gap-2.5 p-2.5 rounded-xl bg-white border border-slate-200 hover:bg-slate-50 text-xs cursor-pointer shadow-2xs transition">
                                <input type="checkbox" name="special_requests_list[]" value="Monitor Pimpinan" class="rounded text-blue-600">
                                <div>
                                    <span class="font-bold text-slate-800"><svg class="w-3.5 h-3.5 inline-block align-[-2px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0V12a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 12V5.25"/></svg> 4. Monitor Pimpinan</span>
                                    <p class="text-[10px] text-slate-400">Standing monitor / prompter</p>
                                </div>
                            </label>

                            {{-- 5. Name Desk --}}
                            <label class="flex items-center gap-2.5 p-2.5 rounded-xl bg-white border border-slate-200 hover:bg-slate-50 text-xs cursor-pointer shadow-2xs transition">
                                <input type="checkbox" name="special_requests_list[]" value="Name Desk" class="rounded text-blue-600">
                                <div>
                                    <span class="font-bold text-slate-800"><svg class="w-3.5 h-3.5 inline-block align-[-2px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3zM6 6h.008v.008H6V6z"/></svg> 5. Name Desk</span>
                                    <p class="text-[10px] text-slate-400">Papan nama meja pimpinan/narasumber</p>
                                </div>
                            </label>

                            {{-- 6. Meja Konsumsi --}}
                            <label class="flex items-center gap-2.5 p-2.5 rounded-xl bg-white border border-slate-200 hover:bg-slate-50 text-xs cursor-pointer shadow-2xs transition">
                                <input type="checkbox" name="special_requests_list[]" value="Meja Konsumsi" checked class="rounded text-blue-600">
                                <div>
                                    <span class="font-bold text-slate-800"><svg class="w-3.5 h-3.5 inline-block align-[-2px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007z"/></svg> 6. Meja Konsumsi</span>
                                    <p class="text-[10px] text-slate-400">Coffee break / snack / makan</p>
                                </div>
                            </label>

                            {{-- 7. Meja Registrasi --}}
                            <label class="flex items-center gap-2.5 p-2.5 rounded-xl bg-white border border-slate-200 hover:bg-slate-50 text-xs cursor-pointer shadow-2xs transition">
                                <input type="checkbox" name="special_requests_list[]" value="Meja Registrasi" checked class="rounded text-blue-600">
                                <div>
                                    <span class="font-bold text-slate-800"><svg class="w-3.5 h-3.5 inline-block align-[-2px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.862 4.487z"/></svg> 7. Meja Registrasi</span>
                                    <p class="text-[10px] text-slate-400">Presensi tamu di pintu masuk</p>
                                </div>
                            </label>

                            {{-- 8. Ruang Transit --}}
                            <label class="flex items-center gap-2.5 p-2.5 rounded-xl bg-white border border-slate-200 hover:bg-slate-50 text-xs cursor-pointer shadow-2xs transition">
                                <input type="checkbox" name="special_requests_list[]" value="Ruang Transit VIP" class="rounded text-blue-600">
                                <div>
                                    <span class="font-bold text-slate-800"><svg class="w-3.5 h-3.5 inline-block align-[-2px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"/></svg> 8. Ruang Transit</span>
                                    <p class="text-[10px] text-slate-400">Ruang transit pejabat / VIP</p>
                                </div>
                            </label>

                            {{-- 9. Meja Asrot --}}
                            <label class="flex items-center gap-2.5 p-2.5 rounded-xl bg-white border border-slate-200 hover:bg-slate-50 text-xs cursor-pointer shadow-2xs transition">
                                <input type="checkbox" name="special_requests_list[]" value="Meja Asrot (Operator Slide)" checked class="rounded text-blue-600">
                                <div>
                                    <span class="font-bold text-slate-800"><svg class="w-3.5 h-3.5 inline-block align-[-2px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0V12a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 12V5.25"/></svg> 9. Meja Asrot</span>
                                    <p class="text-[10px] text-slate-400">Asisten sorot / operator presentasi</p>
                                </div>
                            </label>

                            {{-- 10. Meja Notulensi --}}
                            <label class="flex items-center gap-2.5 p-2.5 rounded-xl bg-white border border-slate-200 hover:bg-slate-50 text-xs cursor-pointer shadow-2xs transition">
                                <input type="checkbox" name="special_requests_list[]" value="Meja Notulensi" checked class="rounded text-blue-600">
                                <div>
                                    <span class="font-bold text-slate-800"><svg class="w-3.5 h-3.5 inline-block align-[-2px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.35 3.836c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m8.9-4.414c.376.023.75.05 1.124.08 1.131.094 1.976 1.057 1.976 2.192V16.5A2.25 2.25 0 0118 18.75h-2.25m-7.5-10.5H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V18.75m-7.5-10.5h6.375c.621 0 1.125.504 1.125 1.125v9.375m-8.25-3l1.5 1.5 3-3.75"/></svg> 10. Meja Notulensi</span>
                                    <p class="text-[10px] text-slate-400">Meja tim pencatat rapat</p>
                                </div>
                            </label>

                            {{-- 11. Lainnya..... --}}
                            <div class="p-2.5 rounded-xl bg-white border border-slate-200 shadow-2xs sm:col-span-2 lg:col-span-2 space-y-1">
                                <label class="block text-xs font-bold text-slate-800">
                                    <svg class="w-3 h-3 inline-block align-[-1px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/></svg> 11. Permintaan Sarpras Lainnya
                                </label>
                                <input type="text"
                                       name="special_lainnya"
                                       x-model="specialLainnya"
                                       placeholder="Tuliskan permintaan khusus lainnya..."
                                       class="w-full px-2.5 py-1.5 text-xs rounded-lg border border-slate-200 bg-slate-50 focus:bg-white focus:outline-none focus:ring-1 focus:ring-blue-500">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Pilihan Akun Zoom (Jika Online atau Hybrid) --}}
                <div x-show="tipePertemuan === 'online' || tipePertemuan === 'hybrid'"
                    class="space-y-2 bg-indigo-50/40 p-3.5 rounded-xl border border-indigo-100">
                    <label class="block text-xs font-bold text-indigo-900 uppercase tracking-wider">
                        Pilihan Akun Zoom / Link Meeting <span class="text-rose-500">*</span>
                    </label>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                        <label class="p-2.5 rounded-lg border bg-white cursor-pointer text-xs transition"
                            :class="zoomAccount === 'zoom_1' ? 'border-indigo-500 ring-2 ring-indigo-500/20 text-indigo-900 font-bold' : 'border-gray-200 text-gray-700'">
                            <input type="radio" name="zoom_account" value="zoom_1" x-model="zoomAccount"
                                class="text-indigo-600">
                            <span>Akun Zoom 1 (Utama)</span>
                        </label>
                        <label class="p-2.5 rounded-lg border bg-white cursor-pointer text-xs transition"
                            :class="zoomAccount === 'zoom_2' ? 'border-indigo-500 ring-2 ring-indigo-500/20 text-indigo-900 font-bold' : 'border-gray-200 text-gray-700'">
                            <input type="radio" name="zoom_account" value="zoom_2" x-model="zoomAccount"
                                class="text-indigo-600">
                            <span>Akun Zoom 2 (Cadangan)</span>
                        </label>
                        <label class="p-2.5 rounded-lg border bg-white cursor-pointer text-xs transition"
                            :class="zoomAccount === 'eksternal' ? 'border-indigo-500 ring-2 ring-indigo-500/20 text-indigo-900 font-bold' : 'border-gray-200 text-gray-700'">
                            <input type="radio" name="zoom_account" value="eksternal" x-model="zoomAccount"
                                class="text-indigo-600">
                            <span>Link Zoom Eksternal</span>
                        </label>
                    </div>

                    <div x-show="zoomAccount === 'eksternal'" class="pt-2">
                        <input type="url" name="zoom_link_custom"
                            placeholder="Masukkan link zoom eksternal (https://zoom.us/j/...)"
                            class="w-full px-3 py-1.5 text-xs rounded-lg border border-gray-200 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600">
                    </div>
                </div>

                {{-- Tanggal, Jam Mulai, Jam Selesai --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                            Tanggal <span class="text-rose-500">*</span>
                        </label>
                        <input type="date" name="booking_date" x-model="bookingDate" required
                            class="w-full px-3.5 py-2 text-xs rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                            Jam Mulai <span class="text-rose-500">*</span>
                        </label>
                        <input type="time" name="start_time" x-model="startTime" required
                            class="w-full px-3.5 py-2 text-xs rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                            Jam Selesai <span class="text-rose-500">*</span>
                        </label>
                        <input type="time" name="end_time" x-model="endTime" required
                            class="w-full px-3.5 py-2 text-xs rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600">
                    </div>
                </div>

                {{-- Catatan / Permintaan Khusus Tambahan --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                        Catatan Khusus Tambahan (Opsional)
                    </label>
                    <input type="text" name="keterangan" x-model="keterangan"
                        placeholder="Contoh: Butuh gladi bersih H-1 jam 16:00"
                        class="w-full px-3.5 py-2 text-xs rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600">
                </div>

                </div>

                {{-- Sticky Footer --}}
                <div class="p-4 sm:p-5 border-t border-gray-100 shrink-0 bg-gray-50/90 flex items-center justify-end gap-2.5 rounded-b-2xl">
                    <button type="button" @click="modalOpen = false"
                        class="px-4 py-2.5 bg-white hover:bg-gray-100 border border-gray-200 text-gray-700 text-xs font-bold rounded-xl transition">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center gap-1.5">
                        <span>Konfirmasi Reservasi</span> &rarr;
                    </button>
                </div>
            </form>

        </div>
    </div>

    {{-- =========================================================
        MODAL POPUP: DETAIL RESERVASI RUANGAN & SARPRAS
    ========================================================== --}}
    <div x-show="detailModalOpen"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-3 sm:p-6"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">

        {{-- Backdrop --}}
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="detailModalOpen = false"></div>

        {{-- Modal Content Card --}}
        <div class="relative bg-white rounded-2xl shadow-2xl border border-gray-200 max-w-3xl sm:max-w-4xl w-full max-h-[90vh] flex flex-col overflow-hidden z-10 my-auto"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95">

            <template x-if="selectedBooking">
                <div class="flex flex-col h-full max-h-[90vh]">
                    {{-- Sticky Header --}}
                    <div class="flex items-start justify-between p-4 sm:p-5 border-b border-gray-100 shrink-0 bg-white">
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-lg shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/></svg>
                            </div>
                            <div>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-800 uppercase tracking-wider"
                                      x-text="selectedBooking.tipe_pertemuan"></span>
                                <h3 class="text-base font-bold text-gray-900 mt-1" x-text="selectedBooking.nama_acara"></h3>
                                <p class="text-xs text-gray-500">
                                    PJ / Penyelenggara: <strong class="text-gray-800" x-text="selectedBooking.penyelenggara"></strong>
                                </p>
                            </div>
                        </div>
                        <button type="button" @click="detailModalOpen = false" class="text-gray-400 hover:text-gray-600 text-2xl font-bold p-1 leading-none rounded-lg hover:bg-gray-100 w-8 h-8 flex items-center justify-center transition">&times;</button>
                    </div>

                    {{-- Scrollable Body --}}
                    <div class="p-5 sm:p-6 overflow-y-auto space-y-5 flex-1 overscroll-contain">
                        {{-- Informasi Waktu & Lokasi --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 bg-gray-50 p-3.5 rounded-xl border border-gray-200 text-xs">
                            <div class="space-y-1">
                                <span class="text-gray-400 font-medium text-[11px]"><svg class="w-3 h-3 inline-block align-[-1px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg> Tanggal & Waktu:</span>
                                <p class="font-bold text-gray-800" x-text="selectedBooking.booking_date"></p>
                                <p class="text-blue-600 font-bold" x-text="'<svg class="w-3 h-3 inline-block align-[-1px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> ' + selectedBooking.start_time + ' - ' + selectedBooking.end_time + ' WITA'"></p>
                            </div>
                            <div class="space-y-1">
                                <span class="text-gray-400 font-medium text-[11px]"><svg class="w-3 h-3 inline-block align-[-1px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0zM19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg> Ruangan / Venue:</span>
                                <p class="font-bold text-gray-800" x-text="selectedBooking.nama_ruangan"></p>
                                <p class="text-gray-600" x-text="'Peserta: ' + (selectedBooking.jumlah_peserta ? selectedBooking.jumlah_peserta + ' Peserta' : 'Jumlah peserta disesuaikan')"></p>
                            </div>
                        </div>

                        {{-- Zoom Info (Jika ada) --}}
                        <template x-if="selectedBooking.zoom_link">
                            <div class="p-3 bg-indigo-50/70 border border-indigo-200 rounded-xl space-y-1.5 text-xs">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-indigo-900"><svg class="w-3.5 h-3.5 inline-block align-[-2px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0V12a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 12V5.25"/></svg> Akses Zoom Meeting:</span>
                                    <span class="text-[10px] font-bold text-indigo-700 bg-indigo-100 px-2 py-0.5 rounded" x-text="selectedBooking.zoom_account === 'zoom_1' ? 'Akun Zoom 1' : (selectedBooking.zoom_account === 'zoom_2' ? 'Akun Zoom 2' : 'Zoom Eksternal')"></span>
                                </div>
                                <div class="flex items-center justify-between text-[11px] pt-1 border-t border-indigo-100">
                                    <a :href="selectedBooking.zoom_link" target="_blank" class="text-blue-600 font-bold hover:underline truncate" x-text="selectedBooking.zoom_link"></a>
                                    <button type="button"
                                            @click="copyToClipboard(selectedBooking.zoom_link, 'Zoom')"
                                            class="px-2 py-0.5 bg-white border border-indigo-200 rounded text-[10px] font-bold text-indigo-700 hover:bg-indigo-50 shrink-0">
                                        Salin Link
                                    </button>
                                </div>
                            </div>
                        </template>

                        {{-- RINCIAN SARPRAS LENGKAP: A, B, C --}}
                        <div class="space-y-3 bg-slate-50 p-4 rounded-xl border border-slate-200 text-xs">
                            <h4 class="font-bold text-slate-800 uppercase tracking-wider text-[11px] pb-1.5 border-b border-slate-200 flex items-center justify-between">
                                <span><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/></svg> Rincian Manajemen Ruangan & Sarpras</span>
                                <span class="text-blue-600 text-[10px] font-bold">Terverifikasi</span>
                            </h4>

                            {{-- Section A --}}
                            <div class="flex items-start gap-2">
                                <span class="w-5 h-5 rounded bg-blue-100 text-blue-700 font-bold text-[10px] flex items-center justify-center shrink-0">A</span>
                                <div>
                                    <span class="text-slate-500 font-medium">Set Up Ruangan (Layout):</span>
                                    <div class="flex items-center gap-2 mt-0.5 flex-wrap">
                                        <p class="font-bold text-slate-900" x-text="selectedBooking.layout_meja || 'Classroom (Default)'"></p>
                                        <template x-if="selectedBooking.setup_podium && selectedBooking.setup_podium.sofa_depan === 'dengan'">
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                                <svg class="w-3.5 h-3.5 inline-block align-[-2px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75"/></svg> Dengan Sofa Depan VIP
                                            </span>
                                        </template>
                                        <template x-if="!selectedBooking.setup_podium || selectedBooking.setup_podium.sofa_depan !== 'dengan'">
                                            <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-700">
                                                Tanpa Sofa Depan
                                            </span>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            {{-- Section B --}}
                            <div class="flex items-start gap-2 pt-2 border-t border-slate-200/60">
                                <span class="w-5 h-5 rounded bg-blue-100 text-blue-700 font-bold text-[10px] flex items-center justify-center shrink-0">B</span>
                                <div class="space-y-1">
                                    <span class="text-slate-500 font-medium">Set Up Podium & Panggung:</span>
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="font-bold text-slate-900" x-text="(selectedBooking.setup_podium && selectedBooking.setup_podium.tipe) ? selectedBooking.setup_podium.tipe : 'Podium Standar'"></span>
                                        <template x-if="selectedBooking.setup_podium && selectedBooking.setup_podium.jumlah_kursi > 0">
                                            <span class="px-1.5 py-0.5 bg-white rounded border border-slate-200 text-[10px] font-semibold text-slate-700"
                                                  x-text="selectedBooking.setup_podium.jumlah_kursi + ' Kursi/Sofa Panggung'"></span>
                                        </template>
                                        <template x-if="selectedBooking.setup_podium && selectedBooking.setup_podium.pasang_spanduk">
                                            <span class="px-1.5 py-0.5 bg-blue-100 text-blue-800 rounded text-[10px] font-bold">
                                                <svg class="w-3 h-3 inline-block align-[-1px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3v1.5M3 21v-6m0 0l2.77-.693a9 9 0 016.208.682l.108.054a9 9 0 006.086.71l3.114-.732a48.274 48.274 0 01-.005-6.499l-3.11.732a9 9 0 01-6.085-.711l-.108-.054a9 9 0 00-6.208-.682L3 4.5M3 15V4.5"/></svg> Pasang Spanduk <span x-show="selectedBooking.setup_podium.keterangan_spanduk" x-text="'(' + selectedBooking.setup_podium.keterangan_spanduk + ')'"></span>
                                            </span>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            {{-- Section C --}}
                            <div class="flex items-start gap-2 pt-2 border-t border-slate-200/60">
                                <span class="w-5 h-5 rounded bg-blue-100 text-blue-700 font-bold text-[10px] flex items-center justify-center shrink-0">C</span>
                                <div class="space-y-1.5 w-full">
                                    <span class="text-slate-500 font-medium">Special Request (Permintaan Khusus):</span>
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <template x-if="selectedBooking.special_requests && selectedBooking.special_requests.items && selectedBooking.special_requests.items.length > 0">
                                            <template x-for="(reqItem, idx) in selectedBooking.special_requests.items" :key="idx">
                                                <span class="px-2 py-1 bg-white border border-slate-200 rounded-lg text-[10px] font-semibold text-slate-800 shadow-2xs"
                                                      x-text="reqItem"></span>
                                            </template>
                                        </template>
                                        <template x-if="!selectedBooking.special_requests || !selectedBooking.special_requests.items || selectedBooking.special_requests.items.length === 0">
                                            <span class="text-slate-400 italic text-[11px]">Standar perlengkapan ruangan</span>
                                        </template>
                                    </div>
                                    <template x-if="selectedBooking.special_requests && selectedBooking.special_requests.lainnya">
                                        <p class="text-[11px] text-slate-600 bg-white p-2 rounded-lg border border-slate-200 mt-1">
                                            <strong>Catatan Tambahan:</strong> <span x-text="selectedBooking.special_requests.lainnya"></span>
                                        </p>
                                    </template>
                                </div>
                            </div>
                        </div>

                        {{-- Keterangan Tambahan --}}
                        <template x-if="selectedBooking.keterangan">
                            <div class="p-3 bg-amber-50/70 border border-amber-200 rounded-xl text-xs space-y-0.5">
                                <span class="font-bold text-amber-900">Catatan Khusus Penyelenggara:</span>
                                <p class="text-amber-800 font-medium" x-text="selectedBooking.keterangan"></p>
                            </div>
                        </template>
                    </div>

                    {{-- Sticky Footer --}}
                    <div class="p-4 sm:p-5 border-t border-gray-100 shrink-0 bg-gray-50/90 flex items-center justify-end rounded-b-2xl">
                        <button type="button"
                                @click="detailModalOpen = false"
                                class="px-5 py-2.5 bg-gray-200 hover:bg-gray-300 text-gray-800 text-xs font-bold rounded-xl transition">
                            Tutup
                        </button>
                    </div>
                </div>
            </template>

        </div>
    </div>

</div>
@endsection