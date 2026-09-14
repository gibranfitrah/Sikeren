@extends('layouts.app')

@section('title', 'Presensi - Sikeren')
@section('header_title', 'Presensi Kantor')

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="mb-8">
        <h2 class="text-xl font-bold text-gray-800">Scan Presensi</h2>
        <p class="text-gray-500 text-sm mt-1">Arahkan kamera ke QR Code untuk mencatat kehadiran Anda.</p>
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

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <!-- QR Scanner Column -->
        <div>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                    <h3 class="text-lg font-bold text-gray-800">Kamera Scanner</h3>
                </div>
                <div class="p-6">
                    <div id="reader" class="w-full h-auto overflow-hidden rounded-lg border-2 border-dashed border-gray-300"></div>
                </div>
            </div>
        </div>

        <!-- Form Column -->
        <div>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                    <h3 class="text-lg font-bold text-gray-800">Data Kehadiran</h3>
                </div>
                <div class="p-6">
                    <form action="{{ route('absen.submit') }}" method="POST" id="absenForm" class="space-y-5">
                        @csrf
                        
                        <!-- Nama -->
                        <div>
                            <label for="nama" class="block mb-2 text-sm font-medium text-gray-900">Nama Pegawai</label>
                            <input type="text" id="nama" class="bg-gray-100 border border-gray-300 text-gray-700 text-sm rounded-lg block w-full p-2.5 cursor-not-allowed" value="{{ $user->nama_lengkap }}" readonly>
                        </div>

                        <!-- Status Presensi -->
                        <div>
                            <label class="block mb-3 text-sm font-medium text-gray-900">Status Kehadiran</label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <label class="relative flex items-center p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                                    <input type="radio" name="status" value="1" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 focus:ring-2" checked>
                                    <span class="ml-3 text-sm font-medium text-gray-900">Masuk</span>
                                </label>
                                <label class="relative flex items-center p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                                    <input type="radio" name="status" value="3" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 focus:ring-2">
                                    <span class="ml-3 text-sm font-medium text-gray-900">Kembali</span>
                                </label>
                                <label class="relative flex items-center p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                                    <input type="radio" name="status" value="2" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 focus:ring-2">
                                    <span class="ml-3 text-sm font-medium text-gray-900">Keluar - Pribadi</span>
                                </label>
                                <label class="relative flex items-center p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                                    <input type="radio" name="status" value="5" id="keluarDinas" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 focus:ring-2">
                                    <span class="ml-3 text-sm font-medium text-gray-900">Keluar - Dinas</span>
                                </label>
                            </div>
                        </div>

                        <!-- Keterangan Dinas -->
                        <div id="dinasInput" style="display: none;">
                            <label for="alasan_dinas" class="block mb-2 text-sm font-medium text-gray-900">Keterangan Dinas</label>
                            <textarea id="alasan_dinas" name="ket_dinas" rows="2" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" placeholder="Masukkan alasan atau tujuan dinas..."></textarea>
                        </div>

                        <!-- QR Data -->
                        <div>
                            <label for="qr_data" class="block mb-2 text-sm font-medium text-gray-900">Data QR Code <span class="text-red-500">*</span></label>
                            <input type="text" id="qr_data" name="qr_data" class="bg-blue-50 border border-blue-300 text-blue-800 text-sm font-mono rounded-lg block w-full p-2.5 focus:outline-none" placeholder="Menunggu scan QR..." required readonly>
                            <p class="mt-2 text-xs text-gray-500">Otomatis terisi setelah Anda men-scan QR code di kamera.</p>
                        </div>

                        <!-- Submit -->
                        <div class="pt-4 border-t border-gray-100">
                            <button type="submit" class="w-full text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-3 text-center transition-colors">
                                Simpan Presensi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.8/html5-qrcode.min.js" integrity="sha512-r6rDA7W6ZeQhvl8S7yRVQUKVHdexq+GAlNkNNqVC7YyIV+NwqCTJe2hDWCiffTyRNOeGEzRRJ9ifvRm/HCzGYg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // --- QR Code Scanner Logic ---
        let html5QRCodeScanner = new Html5QrcodeScanner("reader", {
            fps: 10,
            qrbox: { width: 300, height: 300 },
        });

        function onScanSuccess(decodedText, decodedResult) {
            document.getElementById('qr_data').value = decodedText;
            // Optionally, add a visual cue that scan was successful
            const qrInput = document.getElementById('qr_data');
            qrInput.classList.remove('bg-blue-50', 'border-blue-300', 'text-blue-800');
            qrInput.classList.add('bg-green-50', 'border-green-400', 'text-green-800');
            
            // html5QRCodeScanner.clear(); // Uncomment to stop scanning after 1 success
        }

        html5QRCodeScanner.render(onScanSuccess);

        // --- Form Interaction Logic ---
        const statusRadios = document.querySelectorAll('input[name="status"]');
        const dinasInput = document.getElementById('dinasInput');
        const alasanDinasInput = document.getElementById('alasan_dinas');
        const form = document.getElementById('absenForm');

        function checkStatus() {
            const selectedStatus = document.querySelector('input[name="status"]:checked').value;
            if (selectedStatus === '5') {
                dinasInput.style.display = 'block';
            } else {
                dinasInput.style.display = 'none';
                alasanDinasInput.value = '';
            }
        }

        statusRadios.forEach(radio => {
            radio.addEventListener('change', checkStatus);
        });

        checkStatus();

        form.addEventListener('submit', function(e) {
            const selectedStatus = document.querySelector('input[name="status"]:checked').value;
            if (selectedStatus === '5' && alasanDinasInput.value.trim() === '') {
                e.preventDefault();
                alert('Keterangan Dinas harus diisi ketika Keluar - Dinas dipilih.');
            }
            if (document.getElementById('qr_data').value.trim() === '') {
                e.preventDefault();
                alert('Silahkan scan QR Code terlebih dahulu.');
            }
        });
    });
</script>
@endpush
