@props([
    'label' => null,
    'name' => null,
    'type' => 'text',
    'value' => null,
    'placeholder' => null,
    'required' => false,
    'helper' => null,
    'error' => null,
    'icon' => null
])

@php
    $inputClasses = "w-full px-4 py-2.5 bg-gray-50 focus:bg-white border border-gray-200 rounded-xl text-xs sm:text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition";
    if($icon) {
        $inputClasses .= " pl-10";
    }
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

    <div class="relative">
        @if($icon)
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                {!! $icon !!}
            </div>
        @endif
        <input type="{{ $type }}"
               name="{{ $name }}"
               id="{{ $name }}"
               value="{{ old($name, $value) }}"
               placeholder="{{ $placeholder }}"
               {{ $required ? 'required' : '' }}
               {{ $attributes->merge(['class' => $inputClasses]) }}>
    </div>

    @if($helper)
        <p class="text-[11px] text-gray-400">{{ $helper }}</p>
    @endif

    @if($error || ($name && $errors->has($name)))
        <p class="text-xs text-rose-600 font-semibold">{{ $error ?: $errors->first($name) }}</p>
    @endif
</div>