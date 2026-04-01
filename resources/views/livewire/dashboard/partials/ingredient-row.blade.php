{{-- Partial: ingredient-row.blade.php --}}
{{-- Usage: @include('livewire.dashboard.partials.ingredient-row', ['prefix' => 'ingredients', 'index' => $i, 'ingredient' => $ingredient, 'recalcMethod' => 'recalculateAllHpp', 'removeMethod' => 'removeIngredient']) --}}

@php $prefix = $prefix ?? 'ingredients'; @endphp

<div class="border border-gray-200 rounded-lg p-3 bg-white relative">
    {{-- Header bahan --}}
    <div class="flex justify-between items-center mb-2">
        <span class="text-xs font-semibold text-gray-600">Bahan #{{ $index + 1 }}</span>
        @if(($canRemove ?? true))
        <button type="button" wire:click="{{ $removeMethod }}({{ $index }})"
            class="text-red-500 hover:text-red-700 text-xs font-medium flex items-center gap-1">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
            Hapus
        </button>
        @endif
    </div>

    <div class="grid grid-cols-2 gap-2">
        {{-- Nama Bahan --}}
        <div class="col-span-2">
            <label class="block mb-1 text-xs font-medium text-gray-700">Nama Bahan</label>
            <input wire:model.lazy='{{ $prefix }}.{{ $index }}.ingredient_name' wire:change="{{ $recalcMethod }}" type="text"
                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2"
                placeholder="Misal: Mie Kering">
        </div>

        {{-- Harga Beli --}}
        <div>
            <label class="block mb-1 text-xs font-medium text-gray-700">Harga Beli</label>
            <input wire:model.live.debounce.500ms='{{ $prefix }}.{{ $index }}.cost_price' wire:change="{{ $recalcMethod }}" type="number" step="0.01"
                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2"
                placeholder="150000">
        </div>

        {{-- Jumlah Beli --}}
        <div>
            <label class="block mb-1 text-xs font-medium text-gray-700">Jumlah Beli</label>
            <input wire:model.live.debounce.500ms='{{ $prefix }}.{{ $index }}.cost_quantity' wire:change="{{ $recalcMethod }}" type="number" step="0.001"
                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2"
                placeholder="20">
        </div>

        {{-- Satuan Beli --}}
        <div>
            <label class="block mb-1 text-xs font-medium text-gray-700">Satuan Beli</label>
            <select wire:model.live.debounce.500ms='{{ $prefix }}.{{ $index }}.cost_unit' wire:change="{{ $recalcMethod }}"
                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2">
                <option value="">Pilih</option>
                <optgroup label="Berat">
                    <option value="kg">Kilogram (kg)</option>
                    <option value="gram">Gram (g)</option>
                    <option value="ons">Ons</option>
                    <option value="mg">Miligram (mg)</option>
                </optgroup>
                <optgroup label="Volume">
                    <option value="liter">Liter (L)</option>
                    <option value="ml">Mililiter (mL)</option>
                    <option value="cc">CC</option>
                    <option value="gelas">Gelas (250ml)</option>
                </optgroup>
                <optgroup label="Jumlah">
                    <option value="pcs">Pcs</option>
                    <option value="buah">Buah</option>
                    <option value="biji">Biji</option>
                    <option value="butir">Butir</option>
                    <option value="lusin">Lusin (12)</option>
                </optgroup>
            </select>
        </div>

        {{-- Ukuran Per Porsi --}}
        <div>
            <label class="block mb-1 text-xs font-medium text-gray-700">Per Porsi</label>
            <input wire:model.live.debounce.500ms='{{ $prefix }}.{{ $index }}.serving_size' wire:change="{{ $recalcMethod }}" type="number" step="0.001"
                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2"
                placeholder="200">
        </div>

        {{-- Satuan Porsi --}}
        <div>
            <label class="block mb-1 text-xs font-medium text-gray-700">Satuan Porsi</label>
            <select wire:model.live.debounce.500ms='{{ $prefix }}.{{ $index }}.serving_unit' wire:change="{{ $recalcMethod }}"
                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2">
                <option value="">Pilih</option>
                <optgroup label="Berat">
                    <option value="kg">Kilogram (kg)</option>
                    <option value="gram">Gram (g)</option>
                    <option value="ons">Ons</option>
                    <option value="mg">Miligram (mg)</option>
                </optgroup>
                <optgroup label="Volume">
                    <option value="liter">Liter (L)</option>
                    <option value="ml">Mililiter (mL)</option>
                    <option value="cc">CC</option>
                    <option value="gelas">Gelas (250ml)</option>
                </optgroup>
                <optgroup label="Jumlah">
                    <option value="pcs">Pcs</option>
                    <option value="buah">Buah</option>
                    <option value="biji">Biji</option>
                    <option value="butir">Butir</option>
                    <option value="lusin">Lusin (12)</option>
                </optgroup>
            </select>
        </div>

        {{-- Cost per serving indicator --}}
        <div class="col-span-2">
            @if(!empty($ingredient['cost_per_serving']) && $ingredient['cost_per_serving'] > 0)
                <div class="text-right">
                    <span class="text-xs text-gray-500">Modal bahan ini/porsi:</span>
                    <span class="text-xs font-bold text-indigo-700">Rp{{ number_format($ingredient['cost_per_serving'], 0, ',', '.') }}</span>
                </div>
            @endif
        </div>
    </div>
</div>
