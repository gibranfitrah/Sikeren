@extends('layouts.app')

@section('title', 'Tambah Surat Masuk - Sikeren')
@section('header_title', 'Tambah Surat Masuk')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div class="mb-8">
        <h2 class="text-xl font-bold text-gray-800">Form Tambah Surat Masuk</h2>
        <p class="text-gray-500 text-sm mt-1">Isi detail surat masuk di bawah ini.</p>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-8">
            <form action="{{ route('surat-masuk.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-2">
                    
                    <div class="sm:col-span-2">
                        <label for="nomor_surat" class="block text-sm font-semibold text-gray-700">Nomor Surat</label>
                        <div class="mt-1">
                            <input type="text" id="nomor_surat" name="nomor_surat" value="{{ old('nomor_surat') }}" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-lg p-2.5 border bg-gray-50 text-gray-900" required>
                        </div>
                    </div>

                    <div>
                        <label for="tanggal_surat" class="block text-sm font-semibold text-gray-700">Tanggal Surat</label>
                        <div class="mt-1">
                            <input type="date" id="tanggal_surat" name="tanggal_surat" value="{{ old('tanggal_surat') }}" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-lg p-2.5 border bg-gray-50 text-gray-900" required>
                        </div>
                    </div>

                    <div>
                        <label for="asal_surat" class="block text-sm font-semibold text-gray-700">Asal Surat</label>
                        <div class="mt-1">
                            <input type="text" id="asal_surat" name="asal_surat" value="{{ old('asal_surat') }}" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-lg p-2.5 border bg-gray-50 text-gray-900" required>
                        </div>
                    </div>

                    <div class="sm:col-span-2">
                        <label for="perihal" class="block text-sm font-semibold text-gray-700">Perihal</label>
                        <div class="mt-1">
                            <input type="text" id="perihal" name="perihal" value="{{ old('perihal') }}" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-lg p-2.5 border bg-gray-50 text-gray-900" required>
                        </div>
                    </div>

                    <div class="sm:col-span-2">
                        <label for="ringkasan" class="block text-sm font-semibold text-gray-700">Ringkasan (Opsional)</label>
                        <div class="mt-1">
                            <textarea id="ringkasan" name="ringkasan" rows="3" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-lg p-2.5 border bg-gray-50 text-gray-900">{{ old('ringkasan') }}</textarea>
                        </div>
                    </div>

                    <div class="sm:col-span-2">
                        <label for="nomor_agenda" class="block text-sm font-semibold text-gray-700">Nomor Agenda/registrasi</label>
                        <div class="mt-1">
                            <input type="text" id="nomor_agenda" name="nomor_agenda" value="{{ old('nomor_agenda') }}" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-lg p-2.5 border bg-gray-50 text-gray-900">
                        </div>
                    </div>

                    <div class="sm:col-span-2">
                        <label for="lampiran" class="block text-sm font-semibold text-gray-700">Lampiran</label>
                        <div class="mt-1">
                            <input type="text" id="lampiran" name="lampiran" value="{{ old('lampiran') }}" placeholder="Contoh: 2 Berkas" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-lg p-2.5 border bg-gray-50 text-gray-900">
                        </div>
                    </div>

                    <div class="sm:col-span-2">
                        <label for="file_surat" class="block text-sm font-semibold text-gray-700">Tautan Surat (Google Drive Link)</label>
                        <div class="mt-1">
                            <input type="url" id="file_surat" name="file_surat" value="{{ old('file_surat') }}" placeholder="https://drive.google.com/..." class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-lg p-2.5 border bg-gray-50 text-gray-900" required>
                            <p class="text-xs text-gray-500 mt-1">Pastikan hak akses link sudah diatur ke 'Anyone with the link can view' agar bisa ditampilkan di sistem.</p>
                        </div>
                    </div>

                </div>

                <div class="pt-6 flex justify-end gap-3 border-t border-gray-100">
                    <a href="{{ route('surat-masuk.index') }}" class="bg-white py-2 px-4 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Batal
                    </a>
                    <button type="submit" class="inline-flex justify-center py-2 px-6 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
