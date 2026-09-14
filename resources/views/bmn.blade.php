@extends('layouts.app')

@section('title', 'Daftar BMN - Sikeren')
@section('header_title', 'Barang Milik Negara (BMN)')

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <style>
        /* Tailwind override for DataTables */
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
        <h2 class="text-xl font-bold text-gray-800">Daftar Inventaris BMN</h2>
        <p class="text-gray-500 text-sm mt-1">Kelola dan ajukan perbaikan Barang Milik Negara.</p>
    </div>

    <!-- Filter Ruangan -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex flex-wrap items-center justify-between gap-4">
        <form class="flex items-center gap-3">
            <label class="text-sm font-semibold text-gray-700">Pilih Ruangan:</label>
            <select name="ruang" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm border-gray-300 rounded-lg p-2.5 border bg-gray-50 text-gray-900 w-64">
                @foreach($rooms as $room)
                    <option value="{{ $room->nama_ruangan }}">{{ $room->nama_ruangan }}</option>
                @endforeach
            </select>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg text-sm shadow-sm transition-colors">
                Tampilkan
            </button>
        </form>
        @if(isset($ruang) && $ruang)
        <div class="px-4 py-2 bg-blue-50 text-blue-700 rounded-lg text-sm font-medium">
            Menampilkan Ruangan: {{ $ruang }}
        </div>
        @endif
    </div>

    <!-- Table Section -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden p-6">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm text-left text-gray-500 w-full" id="bmn">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3">NUP</th>
                        <th scope="col" class="px-6 py-3">Nama Barang</th>
                        <th scope="col" class="px-6 py-3">Merk</th>
                        <th scope="col" class="px-6 py-3">Penanggung Jawab</th>
                        <th scope="col" class="px-6 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($bmns as $post)  
                    <tr class="bg-white border-b hover:bg-gray-50">
                        <td class="px-6 py-4 font-medium text-gray-900">{{ $post->nup }}</td>
                        <td class="px-6 py-4">{{ $post->nama_barang }}</td>
                        <td class="px-6 py-4">{{ $post->merk }}</td>
                        <td class="px-6 py-4">{{ $post->pemegang }}</td>
                        <td class="px-6 py-4 text-center">
                            <button type="button" class="btn-edit text-white bg-blue-600 hover:bg-blue-700 font-medium rounded-lg text-xs px-3 py-1.5 focus:outline-none focus:ring-4 focus:ring-blue-300" data-id="{{ $post->id }}">
                                Ajukan Perbaikan
                            </button>	
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Modal Tailwind -->
<div id="practice_modal3" tabindex="-1" aria-hidden="true" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50">
    <div class="relative w-full max-w-md bg-white rounded-xl shadow-lg">
        <form id="companydata3" method="post">
            @csrf
            <input type="hidden" id="color_id3" name="color_id3" value="">
            
            <div class="flex items-start justify-between p-4 border-b rounded-t bg-blue-600">
                <h3 class="text-xl font-semibold text-white">Edit BMN</h3>
                <button type="button" class="text-white hover:bg-blue-500 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center modal-close">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                </button>
            </div>
            
            <div class="p-6 space-y-6">
                <div>
                    <label for="nama_barang" class="block mb-2 text-sm font-medium text-gray-900">Nama Barang:</label>
                    <input type="text" name="nama_barang" id="nama_barang" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                </div>
            </div>
            
            <div class="flex items-center p-6 space-x-2 border-t border-gray-200 rounded-b">
                <button type="submit" id="submit3" class="text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">Simpan</button>
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
$(document).ready(function () {
    $('#bmn').DataTable({
        pageLength: 10,
    });

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Close Modal Logic
    $('.modal-close').on('click', function() {
        $('#practice_modal3').addClass('hidden');
    });

    // Open Modal and Fetch Data
    $('body').on('click', '.btn-edit', function (event) {
        event.preventDefault();
        var id = $(this).data('id');
        
        $.get('color6/' + id + '/edit6', function (data) {
            $('#practice_modal3').removeClass('hidden');
            $('#color_id3').val(data.data.id);
            $('#nama_barang').val(data.data.nama_barang);
        });
    });

    // Submit Edit
    $('body').on('click', '#submit3', function (event) {
        event.preventDefault();
        var id = $("#color_id3").val();
        var nama_barang = $("#nama_barang").val();

        $.ajax({
            url: 'color6/' + id,
            type: "POST",
            data: {
                id: id,
                nama_barang: nama_barang
            },
            dataType: 'json',
            success: function (data) {
                $('#companydata3').trigger("reset");
                $('#practice_modal3').addClass('hidden');
                window.location.reload(true);
            }
        });
    });
});
</script>
@endpush
