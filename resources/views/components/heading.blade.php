@props([
    'title',
    'subtitle' => null,
    'tag' => 'Sistem Kegiatan Terencana',
    'subtag' => 'BPS Kabupaten/Kota',
    'backUrl' => null
])

<div {{ $attributes->merge(['class' => 'bg-white p-6 sm:p-7 rounded-2xl border border-gray-200/80 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4']) }}>
    <div class="flex items-center gap-4">
        @if($backUrl)
            <a href="{{ $backUrl }}"
               class="w-11 h-11 bg-white rounded-xl shadow-xs border border-gray-200 flex items-center justify-center text-gray-500 hover:text-blue-600 hover:border-blue-200 transition-all shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
        @endif

        <div>
            @if($tag)
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-md text-[11px] font-bold uppercase tracking-wider bg-blue-50 text-blue-700 border border-blue-100">
                        {{ $tag }}
                    </span>
                    @if($subtag)
                        <span class="text-xs text-gray-400">•</span>
                        <span class="text-xs text-gray-500 font-medium">{{ $subtag }}</span>
                    @endif
                </div>
            @endif
            <h2 class="text-xl sm:text-2xl font-black text-gray-900 mt-1">{{ $title }}</h2>
            @if($subtitle)
                <p class="text-xs sm:text-sm text-gray-500 mt-0.5">{{ $subtitle }}</p>
            @endif
        </div>
    </div>

    @if(isset($action))
        <div class="flex items-center gap-2.5 flex-wrap sm:flex-nowrap">
            {{ $action }}
        </div>
    @endif
</div>