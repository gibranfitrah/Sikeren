@extends('layouts.app')

@section('title', 'Report Presensi - Sikeren')
@section('header_title', 'Report Presensi')

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
        <h2 class="text-xl font-bold text-gray-800">Laporan Presensi</h2>
        <p class="text-gray-500 text-sm mt-1">Daftar kehadiran dan aktivitas pegawai.</p>
    </div>

    @if (session('success'))
    <div class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 border border-green-200" role="alert">
        {{ session('success') }}
    </div>
    @endif
    
    @if(session('error'))
    <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 border border-red-200" role="alert">
        {{ session('error') }}
    </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <!-- Date Filter -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-center">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Filter Tanggal</h3>
            <form method="GET" action="{{ route('report') }}" class="flex items-center gap-4">
                <div class="flex-1">
                    <input type="date" id="date" name="date" class="w-full shadow-sm border-gray-300 rounded-lg p-2.5 border focus:ring-blue-500 focus:border-blue-500 bg-gray-50 text-gray-900" value="{{ $selectedDate }}">
                </div>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2.5 px-6 rounded-lg text-sm shadow-sm transition-colors">Tampilkan</button>
            </form>
        </div>

        <!-- Import Data (Admin Only) -->
        @if($lastIdJabatan == 14)
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-gray-800">Import Data Excel</h3>
                <a href="{{ url('assets/template_presensi.xlsx') }}" class="text-sm font-medium text-blue-600 hover:text-blue-800 flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    Download Template
                </a>
            </div>
            <form action="{{ route('presensi.import') }}" method="POST" enctype="multipart/form-data" class="flex items-center gap-4">
                @csrf
                <div class="flex-1">
                    <input type="file" name="file" class="w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none p-1.5" required>
                </div>
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-medium py-2.5 px-6 rounded-lg text-sm shadow-sm transition-colors">Import</button>
            </form>
        </div>
        @endif
    </div>

    <!-- Data Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6">
            <div class="overflow-x-auto">
                <table id="example" class="w-full text-sm text-left text-gray-500">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 rounded-tl-lg">Niplama</th>
                            <th class="px-6 py-3">Nama Lengkap</th>
                            <th class="px-6 py-3">Status</th>
                            <th class="px-6 py-3 rounded-tr-lg">Jam</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($report as $status)
                        <tr class="bg-white border-b hover:bg-gray-50">
                            <td class="px-6 py-4 font-medium text-gray-900">{{ $status->niplama }}</td>
                            <td class="px-6 py-4">{{ $status->nama_lengkap }}</td>
                            <td class="px-6 py-4">
                                @php
                                    $badgeClass = '';
                                    $statusText = '';
                                    switch($status->status) {
                                        case 1: $statusText = 'Masuk Kantor'; $badgeClass = 'bg-green-100 text-green-800'; break;
                                        case 2: $statusText = 'Keluar Kantor (Pribadi)'; $badgeClass = 'bg-yellow-100 text-yellow-800'; break;
                                        case 3: $statusText = 'Kembali Kantor'; $badgeClass = 'bg-green-100 text-green-800'; break;
                                        case 4: $statusText = 'Pulang Kantor'; $badgeClass = 'bg-gray-100 text-gray-800'; break;
                                        case 5: $statusText = 'Keluar Kantor (Dinas) - ' . $status->ket_dinas; $badgeClass = 'bg-blue-100 text-blue-800'; break;
                                        default: $statusText = 'Tidak Diketahui'; $badgeClass = 'bg-red-100 text-red-800'; break;
                                    }
                                @endphp
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-medium {{ $badgeClass }}">
                                    {{ $statusText }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-between">
                                    <span class="font-semibold text-gray-700">{{ \Carbon\Carbon::parse($status->created_at)->format('H:i') }}</span>
                                    
                                    @if($lastIdJabatan == 99)
                                    <button type="button" class="btn-edit-jam text-blue-600 hover:text-blue-800 hover:bg-blue-50 p-1.5 rounded-lg transition-colors" data-id="{{ $status->id }}" data-niplama="{{ $status->niplama }}" data-jam="{{ \Carbon\Carbon::parse($status->created_at)->format('H:i') }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Edit Jam Modal (Tailwind) -->
<div id="editJamModal" tabindex="-1" aria-hidden="true" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50">
    <div class="relative w-full max-w-md bg-white rounded-xl shadow-lg mx-4">
        <form id="formEditJam" method="POST" action="">
            @csrf
            <div class="flex items-start justify-between p-4 border-b rounded-t bg-blue-600">
                <h3 class="text-xl font-semibold text-white" id="modalTitle">Edit Jam</h3>
                <button type="button" class="text-white hover:bg-blue-500 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center modal-close">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                </button>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label for="edit_jam_input" class="block mb-2 text-sm font-medium text-gray-900">Jam</label>
                    <input type="time" id="edit_jam_input" name="jam" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required>
                </div>
            </div>
            <div class="flex items-center p-6 space-x-2 border-t border-gray-200 rounded-b">
                <button type="submit" class="text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">Simpan</button>
                <button type="button" class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10 modal-close">Batal</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
    $(document).ready(function() {
        $('#example').DataTable({
            "pageLength": 25,
            "paging": true,
            "info": true,
            "order": [[3, 'desc']],
            "lengthChange": true
        });

        // Modal Logic
        $('.modal-close').on('click', function() {
            $('#editJamModal').addClass('hidden');
        });

        $('.btn-edit-jam').on('click', function() {
            var id = $(this).data('id');
            var niplama = $(this).data('niplama');
            var jam = $(this).data('jam');
            
            // Set form action dynamically
            // Assuming route is defined as '/update-jam/{id}'
            // The original blade had: {{ route('update.jam', $status->id) }}
            // We'll replace the ID part
            var actionUrl = "{{ url('update-jam') }}/" + id; // You might need to adjust this depending on the exact route definition
            
            // Since we might not know the exact route structure if it's named, we can do this instead:
            // Find a way to construct the route or replace a dummy ID
            var baseUrl = "{{ route('update.jam', 'DUMMY_ID') }}";
            actionUrl = baseUrl.replace('DUMMY_ID', id);

            $('#formEditJam').attr('action', actionUrl);
            $('#modalTitle').text('Edit Jam for ' + niplama);
            $('#edit_jam_input').val(jam);
            
            $('#editJamModal').removeClass('hidden');
        });
    });
</script>
@endpush
