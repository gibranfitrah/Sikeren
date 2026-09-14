@props([
    'title' => null,
    'subtitle' => null,
    'tag' => null,
    'headerBorder' => true
])

<div {{ $attributes->merge(['class' => 'bg-white rounded-2xl border border-gray-200/80 shadow-xs overflow-hidden']) }}>
    @if($title || $subtitle || isset($header))
        <div class="p-5 sm:p-6 {{ $headerBorder ? 'border-b border-gray-100 bg-gray-50/40' : '' }} flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                @if($tag)
                    <span class="px-2.5 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider bg-blue-50 text-blue-700 border border-blue-100 mb-1 inline-block">
                        {{ $tag }}
                    </span>
                @endif
                @if($title)
                    <h3 class="text-base font-bold text-gray-900">{{ $title }}</h3>
                @endif
                @if($subtitle)
                    <p class="text-xs text-gray-500 mt-0.5">{{ $subtitle }}</p>
                @endif
            </div>

            @if(isset($action))
                <div class="flex items-center gap-2">
                    {{ $action }}
                </div>
            @endif
        </div>
    @endif

    <div class="p-5 sm:p-6">
        {{ $slot }}
    </div>

    @if(isset($footer))
        <div class="p-4 sm:p-5 bg-gray-50/70 border-t border-gray-100">
            {{ $footer }}
        </div>
    @endif
</div>