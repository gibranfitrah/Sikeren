@php
    $simpatiNav = [
        ['route' => 'simpati.dashboard', 'label' => 'Dashboard'],
        ['route' => 'simpati.petugas.index', 'label' => 'Kelola Petugas'],
        ['route' => 'simpati.jadwal.index', 'label' => 'Kelola Jadwal'],
    ];
    if (Auth::check() && \App\Support\SimpatiRoles::canManageAdmins(Auth::user())) {
        $simpatiNav[] = ['route' => 'simpati.admins.index', 'label' => 'Kelola Admin'];
    }
    $simpatiNav[] = ['route' => 'simpati.index', 'label' => 'Integrasi & Sinkronisasi'];
    $activeName = request()->route() ? request()->route()->getName() : '';
    $skippedRows = session('skipped_rows');
@endphp

<div class="relative overflow-hidden rounded-3xl p-5 sm:p-6 text-white shadow-xl border border-blue-500/30 mb-6"
     style="background: linear-gradient(135deg, #1e3a8a 0%, #1d4ed8 55%, #2563eb 100%);">
    <div class="absolute inset-0 opacity-10 pointer-events-none" style="background-image: radial-gradient(rgba(255,255,255,0.5) 1px, transparent 1px); background-size: 24px 24px;"></div>
    <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="min-w-0">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/20 text-[11px] font-semibold border border-white/30">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                Modul SIMPATI
            </div>
            <h2 class="text-xl sm:text-2xl font-black tracking-tight mt-2">{{ $pageTitle ?? 'Dashboard SIMPATI' }}</h2>
            @if(!empty($pageSubtitle))
                <p class="text-sm text-blue-100 mt-1 leading-relaxed">{{ $pageSubtitle }}</p>
            @endif
        </div>
        @isset($pageActions)
            <div class="flex flex-wrap items-center gap-2 shrink-0">{{ $pageActions }}</div>
        @endisset
    </div>
</div>

<nav class="flex flex-wrap items-center gap-2 mb-6">
    @foreach($simpatiNav as $item)
        @php $isActive = $activeName === $item['route']; @endphp
        <a href="{{ route($item['route']) }}"
           class="px-4 py-2 rounded-xl text-xs font-bold transition-all duration-150 {{ $isActive ? 'bg-blue-600 text-white shadow-lg shadow-blue-900/30' : 'bg-white text-gray-500 border border-gray-200 hover:border-blue-300 hover:text-blue-600' }}">
            {{ $item['label'] }}
        </a>
    @endforeach
</nav>

@if(session('success'))
    <div class="mb-6 flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
        <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <span>{{ session('success') }}</span>
    </div>
@endif

@if(session('error'))
    <div class="mb-6 flex items-start gap-3 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
        <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <span>{{ session('error') }}</span>
    </div>
@endif

@if(is_array($skippedRows) && count($skippedRows))
    <div class="mb-6 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-xs text-amber-800">
        <p class="font-bold mb-1">Baris yang dilewati saat upload:</p>
        <ul class="list-disc list-inside space-y-0.5">
            @foreach(array_slice($skippedRows, 0, 8) as $baris)
                <li>{{ $baris }}</li>
            @endforeach
            @if(count($skippedRows) > 8)
                <li class="font-semibold">… dan {{ count($skippedRows) - 8 }} baris lainnya.</li>
            @endif
        </ul>
    </div>
@endif
