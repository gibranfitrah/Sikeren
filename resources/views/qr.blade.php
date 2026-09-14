@extends('layouts.app')

@section('title', 'QR Code Presensi - Sikeren')
@section('header_title', 'QR Code Presensi')

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <style>
        .dataTables_wrapper .dataTables_length select,
        .dataTables_wrapper .dataTables_filter input {
            border: 1px solid #e5e7eb;
            border-radius: 0.375rem;
            padding: 0.25rem 0.5rem;
            margin-left: 0.5rem;
        }
        table.dataTable.no-footer { border-bottom: 1px solid #e5e7eb; }
        table.dataTable thead th, table.dataTable thead td { border-bottom: 1px solid #e5e7eb; }
    </style>
@endpush

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="mb-8">
        <h2 class="text-xl font-bold text-gray-800">Presensi Karyawan</h2>
        <p class="text-gray-500 text-sm mt-1">Sistem Kehadiran Berbasis QR Code.</p>
    </div>

    @if(session('error'))
    <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 border border-red-200" role="alert">
        {{ session('error') }}
    </div>
    @endif

    <!-- Filters -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 mb-6">
        @if($userId == 45)
        <form method="GET" action="{{ route('qr') }}" class="flex flex-wrap items-end gap-4">
            <div>
                <label for="date" class="block text-sm font-medium text-gray-700 mb-1">Pilih Tanggal</label>
                <input type="date" id="date" name="date" class="shadow-sm border-gray-300 rounded-lg p-2.5 border focus:ring-blue-500 focus:border-blue-500 bg-gray-50 text-gray-900" value="{{ $selectedDate }}">
            </div>
            <div>
                <label for="satker_id" class="block text-sm font-medium text-gray-700 mb-1">Pilih ID Satker</label>
                <select id="satker_id" name="satker_id" class="shadow-sm border-gray-300 rounded-lg p-2.5 border focus:ring-blue-500 focus:border-blue-500 bg-gray-50 text-gray-900 w-64">
                    <option value="7400" {{ request('satker_id') == '7400' ? 'selected' : '' }}>7400 - BPS Provinsi Sulawesi Tenggara</option>
                    <option value="7401" {{ request('satker_id') == '7401' ? 'selected' : '' }}>7401 - BPS Kabupaten Buton</option>
                    <option value="7402" {{ request('satker_id') == '7402' ? 'selected' : '' }}>7402 - BPS Kabupaten Muna</option>
                    <option value="7403" {{ request('satker_id') == '7403' ? 'selected' : '' }}>7403 - BPS Kabupaten Konawe</option>
                    <option value="7404" {{ request('satker_id') == '7404' ? 'selected' : '' }}>7404 - BPS Kabupaten Kolaka</option>
                    <option value="7405" {{ request('satker_id') == '7405' ? 'selected' : '' }}>7405 - BPS Kabupaten Konawe Selatan</option>
                    <option value="7406" {{ request('satker_id') == '7406' ? 'selected' : '' }}>7406 - BPS Kabupaten Bombana</option>
                    <option value="7407" {{ request('satker_id') == '7407' ? 'selected' : '' }}>7407 - BPS Kabupaten Wakatobi</option>
                    <option value="7408" {{ request('satker_id') == '7408' ? 'selected' : '' }}>7408 - BPS Kabupaten Kolaka Utara</option>
                    <option value="7409" {{ request('satker_id') == '7409' ? 'selected' : '' }}>7409 - BPS Kabupaten Buton Utara</option>
                    <option value="7410" {{ request('satker_id') == '7410' ? 'selected' : '' }}>7410 - BPS Kabupaten Konawe Utara</option>
                    <option value="7411" {{ request('satker_id') == '7411' ? 'selected' : '' }}>7411 - BPS Kabupaten Kolaka Timur</option>
                    <option value="7415" {{ request('satker_id') == '7415' ? 'selected' : '' }}>7415 - BPS Kabupaten Buton Selatan</option>
                    <option value="7471" {{ request('satker_id') == '7471' ? 'selected' : '' }}>7471 - BPS Kota Kendari</option>
                    <option value="7472" {{ request('satker_id') == '7472' ? 'selected' : '' }}>7472 - BPS Kota Baubau</option>
                </select>
            </div>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2.5 px-5 rounded-lg text-sm shadow-sm transition-colors">Filter</button>
        </form>
        @elseif($userId == 66)
        <form method="GET" action="{{ route('qr') }}" class="flex flex-wrap items-end gap-4">
            <div>
                <label for="date" class="block text-sm font-medium text-gray-700 mb-1">Pilih Tanggal</label>
                <input type="date" id="date" name="date" class="shadow-sm border-gray-300 rounded-lg p-2.5 border focus:ring-blue-500 focus:border-blue-500 bg-gray-50 text-gray-900" value="{{ $selectedDate }}">
            </div>
            <div>
                <label for="organisasi_id" class="block text-sm font-medium text-gray-700 mb-1">Pilih Fungsi</label>
                <select id="organisasi_id" name="organisasi_id" class="shadow-sm border-gray-300 rounded-lg p-2.5 border focus:ring-blue-500 focus:border-blue-500 bg-gray-50 text-gray-900 w-64">
                    <option value="92600" {{ request('organisasi_id') == '92600' ? 'selected' : '' }}>IPDS</option>
                    <option value="92100" {{ request('organisasi_id') == '92100' ? 'selected' : '' }}>Bagian Umum</option>
                </select>
            </div>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2.5 px-5 rounded-lg text-sm shadow-sm transition-colors">Filter</button>
        </form>
        @else
        <form method="GET" action="{{ route('qr') }}" class="flex flex-wrap items-end gap-4">
            <div>
                <label for="date" class="block text-sm font-medium text-gray-700 mb-1">Pilih Tanggal</label>
                <input type="date" id="date" name="date" class="shadow-sm border-gray-300 rounded-lg p-2.5 border focus:ring-blue-500 focus:border-blue-500 bg-gray-50 text-gray-900" value="{{ $selectedDate }}">
            </div>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2.5 px-5 rounded-lg text-sm shadow-sm transition-colors">Filter</button>
        </form>
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left Side: QR & Legend -->
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                    <h3 class="text-lg font-bold text-gray-800">QR Code</h3>
                </div>
                <div class="p-6 flex justify-center">
                    @if($lastIdJabatan == 14)
                        {!! $qrcode !!}
                    @else
                        <div class="text-gray-400 text-sm text-center py-10">QR Code hanya tersedia untuk jabatan tertentu.</div>
                    @endif
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                    <h3 class="text-lg font-bold text-gray-800">Keterangan Warna</h3>
                </div>
                <div class="p-4">
                    <table class="w-full text-sm text-left">
                        <tbody>
                            <tr class="border-b">
                                <td class="py-2 w-16"><div class="w-6 h-6 rounded bg-red-600"></div></td>
                                <td class="py-2 text-gray-700">Belum Masuk</td>
                            </tr>
                            <tr class="border-b">
                                <td class="py-2"><div class="w-6 h-6 rounded bg-green-600"></div></td>
                                <td class="py-2 text-gray-700">Masuk Kantor</td>
                            </tr>
                            <tr class="border-b">
                                <td class="py-2"><div class="w-6 h-6 rounded bg-blue-600"></div></td>
                                <td class="py-2 text-gray-700">Keluar Kantor (Dinas)</td>
                            </tr>
                            <tr class="border-b">
                                <td class="py-2"><div class="w-6 h-6 rounded bg-yellow-500"></div></td>
                                <td class="py-2 text-gray-700">Keluar Kantor (Pribadi)</td>
                            </tr>
                            <tr>
                                <td class="py-2"><div class="w-6 h-6 rounded bg-gray-500"></div></td>
                                <td class="py-2 text-gray-700">Pulang Kantor</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Right Side: Data Table -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                    <h3 class="text-lg font-bold text-gray-800">Status Presensi</h3>
                </div>
                <div class="p-6 overflow-x-auto">
                    <table id="example" class="w-full text-sm text-left border-collapse">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 w-1/2">Masuk</th>
                                <th class="px-4 py-3 w-1/2">Keluar</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($statuses as $status)
                            @php
                                $colorClass = '';
                                if(in_array($status->status, [1, 3])) $colorClass = 'bg-green-600';
                                elseif(in_array($status->status, [2])) $colorClass = 'bg-yellow-500';
                                elseif(in_array($status->status, [5])) $colorClass = 'bg-blue-600';
                                elseif(in_array($status->status, [4])) $colorClass = 'bg-gray-500';
                                else $colorClass = 'bg-red-600';
                            @endphp
                            <tr class="border-b"> 
                                <td class="px-2 py-2 align-top">
                                    @if(in_array($status->status, [1, 3]) || (!in_array($status->status, [1,2,3,4,5]) && !isset($presensis[$status->niplama])))
                                        <div class="{{ in_array($status->status, [1,3]) ? 'bg-green-600' : 'bg-red-600' }} text-white p-4 rounded-lg h-full shadow-sm">
                                            <div class="font-bold mb-1">{{ $status->user_name ?? $status->niplama }}</div>
                                            @if(in_array($status->status, [1,3]))
                                                <div class="text-xs opacity-90 mb-2">
                                                    {{ $masukKantorSums[$status->niplama] ?? '-' }} / {{ $istirahatSums[$status->niplama] ?? '-' }}
                                                </div>
                                                @php
                                                    $currentDay = \Carbon\Carbon::now('Asia/Kuala_Lumpur')->format('l');
                                                    $defaultJamPulang = ($currentDay === 'Friday') ? '16:30' : '16:00';
                                                    $estimatedTime = $estimatedJamPulang[$status->niplama] ?? '-';
                                                @endphp
                                                <div class="text-xs opacity-90 mb-3 border-b border-white/20 pb-2">
                                                    @if($estimatedTime !== '-' && \Carbon\Carbon::parse($estimatedTime)->lessThan(\Carbon\Carbon::createFromTime(16, 00)) && !\Carbon\Carbon::now()->isFriday())
                                                        Estimasi Jam Pulang:  {{ $defaultJamPulang }}
                                                    @elseif($estimatedTime !== '-' && \Carbon\Carbon::parse($estimatedTime)->lessThan(\Carbon\Carbon::createFromTime(16, 30)) && \Carbon\Carbon::now()->isFriday())
                                                        Estimasi Jam Pulang:  {{ $defaultJamPulang }}
                                                    @else
                                                        Estimasi Jam Pulang:  {{ $estimatedTime }}
                                                    @endif
                                                </div>
                                                <div class="text-xs space-y-1">
                                                    @if(isset($presensis[$status->niplama]))
                                                        @foreach($presensis[$status->niplama] as $presensi)
                                                            <div>
                                                                @if($presensi->status == 1) Masuk Kantor
                                                                @elseif($presensi->status == 2) Keluar Kantor (Pribadi)
                                                                @elseif($presensi->status == 3) Kembali Kantor
                                                                @elseif($presensi->status == 4) Pulang Kantor
                                                                @elseif($presensi->status == 5) Keluar Kantor (Dinas) - {{ $presensi->ket_dinas }}
                                                                @else Status: {{ $presensi->status }}
                                                                @endif
                                                                <span class="font-semibold">{{ \Carbon\Carbon::parse($presensi->created_at)->format('H:i') }}</span>
                                                            </div>
                                                        @endforeach
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                                
                                <td class="px-2 py-2 align-top">
                                    @if(in_array($status->status, [2, 4, 5]))
                                        <div class="{{ $colorClass }} text-white p-4 rounded-lg h-full shadow-sm">
                                            <div class="font-bold mb-1">{{ $status->user_name ?? $status->niplama }}</div>
                                            <div class="text-xs opacity-90 mb-2">
                                                {{ $masukKantorSums[$status->niplama] ?? '-' }} / {{ $istirahatSums[$status->niplama] ?? '-' }}
                                            </div>
                                            @php
                                                $currentDay = \Carbon\Carbon::now('Asia/Kuala_Lumpur')->format('l');
                                                $defaultJamPulang = ($currentDay === 'Friday') ? '16:30' : '16:00';
                                                $estimatedTime = $estimatedJamPulang[$status->niplama] ?? '-';
                                            @endphp
                                            <div class="text-xs opacity-90 mb-3 border-b border-white/20 pb-2">
                                                @if($estimatedTime !== '-' && \Carbon\Carbon::parse($estimatedTime)->lessThan(\Carbon\Carbon::createFromTime(16, 00)) && !\Carbon\Carbon::now()->isFriday())
                                                    Estimasi Jam Pulang:  {{ $defaultJamPulang }}
                                                @elseif($estimatedTime !== '-' && \Carbon\Carbon::parse($estimatedTime)->lessThan(\Carbon\Carbon::createFromTime(16, 30)) && \Carbon\Carbon::now()->isFriday())
                                                    Estimasi Jam Pulang:  {{ $defaultJamPulang }}
                                                @else
                                                    Estimasi Jam Pulang:  {{ $estimatedTime }}
                                                @endif
                                            </div>
                                            <div class="text-xs space-y-1">
                                                @if(isset($presensis[$status->niplama]))
                                                    @foreach($presensis[$status->niplama] as $presensi)
                                                        <div>
                                                            @if($presensi->status == 1) Masuk Kantor
                                                            @elseif($presensi->status == 2) Keluar Kantor (Pribadi)
                                                            @elseif($presensi->status == 3) Kembali Kantor
                                                            @elseif($presensi->status == 4) Pulang Kantor
                                                            @elseif($presensi->status == 5) Keluar Kantor (Dinas) - {{ $presensi->ket_dinas }}
                                                            @else Status: {{ $presensi->status }}
                                                            @endif
                                                            <span class="font-semibold">{{ \Carbon\Carbon::parse($presensi->created_at)->format('H:i') }}</span>
                                                        </div>
                                                    @endforeach
                                                @endif
                                            </div>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
    $(document).ready(function() {
        @if (Auth::user()->niplama == 340016257)
        $('#example').DataTable({
            "pageLength": 100,
            "paging": false,
            "info": false,
            "order": [[1, 'desc']],
            "columnDefs": [
                { "orderable": false, "targets": [0, 1] }
            ],
            "lengthChange": false
        });
        @else
        $('#example').DataTable({
            "pageLength": 100,
            "paging": false,
            "info": false,
            "order": [[1, 'asc']],
            "columnDefs": [
                { "orderable": false, "targets": [0, 1] }
            ],
            "lengthChange": false
        });
        @endif
        
        let currentKodeQrId = '{{ $data ? $data->id : '' }}';

        function checkForNewQRCode() {
            $.ajax({
                url: '{{ url('check-new-qr') }}',
                method: 'GET',
                success: function (response) {
                    if (response.id && response.id != currentKodeQrId) {
                        location.reload();
                    }
                },
                error: function (xhr) {
                    console.log("Error checking for QR update.");
                }
            });
        }
        setInterval(checkForNewQRCode, 5000);
    });
</script>
@endpush
