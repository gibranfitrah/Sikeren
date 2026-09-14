@props([
    'name' => 'wilayah[]',
    'selected' => [],
    'label' => 'Wilayah Kegiatan'
])

@php
    $wilayahList = [
        'Sulawesi Tenggara (Provinsi)',
        'Kota Kendari',
        'Kota Baubau',
        'Kabupaten Konawe',
        'Kabupaten Kolaka',
        'Kabupaten Muna',
        'Kabupaten Buton',
        'Kabupaten Konawe Selatan',
        'Kabupaten Bombana',
        'Kabupaten Wakatobi',
        'Kabupaten Kolaka Utara',
        'Kabupaten Buton Utara',
        'Kabupaten Konawe Utara',
        'Kabupaten Kolaka Timur',
        'Kabupaten Konawe Kepulauan',
        'Kabupaten Muna Barat',
        'Kabupaten Buton Tengah',
        'Kabupaten Buton Selatan',
        'Sulawesi Selatan',
        'DKI Jakarta',
        'Luar Provinsi / Nasional'
    ];
    $selectedArray = is_array($selected) ? array_values($selected) : (is_array(old('wilayah')) ? array_values(old('wilayah')) : []);
@endphp

<div x-data="{
        open: false,
        search: '',
        selectedList: {{ json_encode($selectedArray) }},
        options: {{ json_encode($wilayahList) }},
        get filteredOptions() {
            if (!this.search || !this.search.trim()) return this.options;
            return this.options.filter(item => item.toLowerCase().includes(this.search.toLowerCase().trim()));
        },
        toggleWilayah(item) {
            if (this.selectedList.includes(item)) {
                this.selectedList = this.selectedList.filter(w => w !== item);
            } else {
                this.selectedList.push(item);
            }
        },
        selectAll() {
            this.selectedList = [...this.options];
        },
        clearAll() {
            this.selectedList = [];
        }
    }"
    @click.outside="open = false"
    class="space-y-1.5 relative">

    <div class="flex items-center justify-between">
        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider">
            {{ $label }} <span class="text-rose-500">*</span>
        </label>
        <span class="text-[11px] font-bold text-blue-600" x-text="selectedList.length + ' Wilayah Dipilih'"></span>
    </div>

    {{-- Dropdown Trigger Box --}}
    <div @click="open = !open"
         class="w-full min-h-[46px] px-3.5 py-2 bg-gray-50 hover:bg-white border border-gray-200 rounded-xl cursor-pointer flex items-center justify-between gap-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition shadow-2xs">
        
        <div class="flex items-center gap-1.5 flex-wrap flex-1">
            <template x-if="selectedList.length === 0">
                <span class="text-xs sm:text-sm text-gray-400">-- Klik untuk memilih satu atau beberapa wilayah --</span>
            </template>

            <template x-for="(wil, idx) in selectedList.slice(0, 3)" :key="idx">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">
                    <span x-text="wil"></span>
                    <button type="button" @click.stop="toggleWilayah(wil)" class="text-blue-500 hover:text-rose-600 text-sm font-bold leading-none">&times;</button>
                </span>
            </template>

            <template x-if="selectedList.length > 3">
                <span class="text-xs font-bold text-gray-600 bg-gray-200/80 px-2 py-1 rounded-md" x-text="'+' + (selectedList.length - 3) + ' lainnya'"></span>
            </template>
        </div>

        <div class="text-gray-400 shrink-0">
            <svg class="w-4 h-4 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </div>
    </div>

    {{-- Popover Dropdown Menu --}}
    <div x-show="open"
         x-cloak
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 translate-y-1 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-1 scale-95"
         class="absolute z-50 left-0 right-0 top-full mt-2 bg-white rounded-2xl shadow-2xl border border-gray-200 p-3 space-y-2.5 max-h-80 overflow-hidden flex flex-col">
        
        {{-- Search & Quick Actions inside Dropdown --}}
        <div class="flex items-center gap-2 pb-2 border-b border-gray-100">
            <div class="relative flex-1">
                <input type="text"
                       x-model="search"
                       placeholder="Cari wilayah..."
                       @click.stop
                       class="w-full pl-8 pr-3 py-1.5 text-xs rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600">
                <svg class="w-3.5 h-3.5 text-gray-400 absolute left-2.5 top-2.5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            <button type="button" @click.stop="selectAll()" class="text-[11px] font-bold text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 px-2.5 py-1.5 rounded-lg shrink-0 transition">
                Pilih Semua
            </button>
            <button type="button" @click.stop="clearAll()" class="text-[11px] font-bold text-gray-600 hover:text-gray-800 bg-gray-100 hover:bg-gray-200 px-2.5 py-1.5 rounded-lg shrink-0 transition">
                Batal
            </button>
        </div>

        {{-- Options Checklist Grid --}}
        <div class="overflow-y-auto flex-1 divide-y divide-gray-100 pr-1 space-y-0.5 max-h-60">
            <template x-for="item in filteredOptions" :key="item">
                <div class="flex items-center gap-2.5 p-2 rounded-xl hover:bg-blue-50/70 cursor-pointer text-xs font-semibold text-gray-800 transition select-none"
                     @click.stop="toggleWilayah(item)">
                    <div class="w-4 h-4 rounded border flex items-center justify-center transition shrink-0"
                         :class="selectedList.includes(item) ? 'bg-blue-600 border-blue-600 text-white' : 'border-gray-300 bg-white'">
                        <template x-if="selectedList.includes(item)">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                            </svg>
                        </template>
                    </div>
                    <span x-text="item" class="flex-1 text-gray-800 font-medium"></span>
                    <span x-show="selectedList.includes(item)" class="text-[10px] font-bold text-blue-600 bg-blue-100/70 px-2 py-0.5 rounded-md shrink-0">
                        Terpilih
                    </span>
                </div>
            </template>
            <template x-if="filteredOptions.length === 0">
                <div class="py-6 text-center text-xs text-gray-400 font-medium">
                    Tidak ada wilayah yang cocok.
                </div>
            </template>
        </div>
    </div>

    {{-- Hidden fallback inputs for direct form submission --}}
    <template x-for="val in selectedList" :key="val">
        <input type="hidden" name="wilayah[]" :value="val">
    </template>
</div>