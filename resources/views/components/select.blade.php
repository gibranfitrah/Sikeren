@props([
    'label' => null,
    'name' => null,
    'required' => false,
    'helper' => null,
    'error' => null
])

@php
    $selectClasses = "w-full px-4 py-2.5 bg-gray-50 focus:bg-white border border-gray-200 rounded-xl text-xs sm:text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition";
@endphp

<div class="space-y-1.5">
    @if($label)
        <label for="{{ $name }}" class="block text-xs font-bold text-gray-700 uppercase tracking-wider">
            {{ $label }}
            @if($required)
                <span class="text-rose-500">*</span>
            @endif
        </label>
    @endif

    <select name="{{ $name }}"
            id="{{ $name }}"
            {{ $required ? 'required' : '' }}
            {{ $attributes->merge(['class' => $selectClasses]) }}>
        {{ $slot }}
    </select>

    @if($helper)
        <p class="text-[11px] text-gray-400">{{ $helper }}</p>
    @endif

    @if($error || ($name && $errors->has($name)))
        <p class="text-xs text-rose-600 font-semibold">{{ $error ?: $errors->first($name) }}</p>
    @endif
</div>