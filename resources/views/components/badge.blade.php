@props([
    'variant' => 'neutral',
    'size' => 'sm',
    'dot' => false
])

@php
    $baseClasses = "inline-flex items-center font-bold tracking-wide shrink-0 transition-colors";

    $sizeClasses = [
        'xs' => 'px-2 py-0.5 text-[10px] rounded-md gap-1',
        'sm' => 'px-2.5 py-1 text-[11px] rounded-lg gap-1.5',
        'md' => 'px-3 py-1.5 text-xs rounded-xl gap-2',
    ][$size] ?? 'px-2.5 py-1 text-[11px] rounded-lg gap-1.5';

    $variantClasses = [
        'primary' => 'bg-blue-50 text-blue-700 border border-blue-200',
        'success' => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
        'warning' => 'bg-amber-50 text-amber-700 border border-amber-200',
        'danger'  => 'bg-rose-50 text-rose-700 border border-rose-200',
        'info'    => 'bg-indigo-50 text-indigo-700 border border-indigo-200',
        'neutral' => 'bg-gray-100 text-gray-700 border border-gray-200',
    ][$variant] ?? 'bg-gray-100 text-gray-700 border border-gray-200';

    $dotColors = [
        'primary' => 'bg-blue-500',
        'success' => 'bg-emerald-500',
        'warning' => 'bg-amber-500',
        'danger'  => 'bg-rose-500',
        'info'    => 'bg-indigo-500',
        'neutral' => 'bg-gray-400',
    ][$variant] ?? 'bg-gray-400';

    $classes = "{$baseClasses} {$sizeClasses} {$variantClasses}";
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    @if($dot)
        <span class="w-1.5 h-1.5 rounded-full {{ $dotColors }} shrink-0"></span>
    @endif
    <span>{{ $slot }}</span>
</span>