@props([
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'button',
    'href' => null,
    'icon' => null,
    'iconRight' => null,
    'disabled' => false
])

@php
    $baseClasses = "inline-flex items-center justify-center font-bold transition-all duration-150 cursor-pointer select-none focus:outline-none focus:ring-2 focus:ring-offset-1 shrink-0";
    
    $sizeClasses = [
        'sm' => 'px-3 py-1.5 text-xs rounded-xl gap-1.5',
        'md' => 'px-4 py-2.5 text-xs font-bold rounded-xl gap-2',
        'lg' => 'px-6 py-3 text-sm font-bold rounded-xl gap-2.5',
    ][$size] ?? 'px-4 py-2.5 text-xs font-bold rounded-xl gap-2';

    $variantClasses = [
        'primary'   => 'bg-blue-600 hover:bg-blue-700 text-white shadow-xs hover:shadow focus:ring-blue-500 border border-transparent',
        'secondary' => 'bg-white hover:bg-gray-50 text-gray-700 border border-gray-200 shadow-xs hover:border-gray-300 focus:ring-gray-300',
        'success'   => 'bg-emerald-600 hover:bg-emerald-700 text-white shadow-xs hover:shadow focus:ring-emerald-500 border border-transparent',
        'danger'    => 'bg-rose-600 hover:bg-rose-700 text-white shadow-xs hover:shadow focus:ring-rose-500 border border-transparent',
        'warning'   => 'bg-amber-500 hover:bg-amber-600 text-white shadow-xs hover:shadow focus:ring-amber-400 border border-transparent',
        'outline'   => 'bg-transparent hover:bg-blue-50 text-blue-600 border border-blue-200 focus:ring-blue-400',
        'ghost'     => 'bg-transparent hover:bg-gray-100 text-gray-600 focus:ring-gray-300',
    ][$variant] ?? 'bg-blue-600 hover:bg-blue-700 text-white shadow-xs hover:shadow border border-transparent';

    $disabledClasses = $disabled ? 'opacity-50 cursor-not-allowed pointer-events-none' : '';
    $classes = "{$baseClasses} {$sizeClasses} {$variantClasses} {$disabledClasses}";
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if($icon)
            <span class="shrink-0">{!! $icon !!}</span>
        @endif
        <span>{{ $slot }}</span>
        @if($iconRight)
            <span class="shrink-0">{!! $iconRight !!}</span>
        @endif
    </a>
@else
    <button type="{{ $type }}" {{ $disabled ? 'disabled' : '' }} {{ $attributes->merge(['class' => $classes]) }}>
        @if($icon)
            <span class="shrink-0">{!! $icon !!}</span>
        @endif
        <span>{{ $slot }}</span>
        @if($iconRight)
            <span class="shrink-0">{!! $iconRight !!}</span>
        @endif
    </button>
@endif