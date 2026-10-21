@php
    $statusVariant = [
        'Hadir'             => 'success',
        'Terjadwal'         => 'primary',
        'Belum Konfirmasi'  => 'warning',
        'Izin'              => 'info',
        'Cuti'              => 'info',
        'Sakit'             => 'warning',
        'Tugas Luar'        => 'primary',
        'Diganti'           => 'neutral',
        'Alpa'              => 'danger',
    ][$status] ?? 'neutral';
@endphp

<x-badge :variant="$statusVariant" size="sm">{{ $status }}</x-badge>
