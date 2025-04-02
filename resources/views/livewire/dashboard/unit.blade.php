<div x-data="{ showPopover: false }" class="relative">

    <style>
        /* Styling dropdown select agar ada scrollbar */
        select {
            appearance: none;
            /* Hilangkan default style */
            -webkit-appearance: none;
            -moz-appearance: none;
            padding-right: 2rem;
        }

        /* Custom dropdown dengan scrollbar */
        select option {
            max-height: 10px;
            /* Atur tinggi maksimal */
            overflow-y: auto;
            /* Tambahkan scrollbar */
        }

    </style>

    <!-- Label dan Button -->

    <label for="unit" class="block mb-2 text-sm font-medium text-gray-900">
        Satuan

        {{-- User harus punya izin Tambah & Ubah untuk bisa akses --}}
        @can('Tambah')
        @can('Ubah')
        {{-- User harus memiliki peran admin --}}
        @hasrole('admin')
        <button type="button" class="bg-gray-500 rounded-full" @click.prevent="showPopover = true">
            <i class="bi bi-plus-lg text-white"></i>
        </button>
        @endrole
        @endcan
        @endcan
    </label>



    <!-- Dropdown Satuan -->
    <div class="relative w-full">
        <select wire:model.live="selectedUnit" id="selectedUnit"
            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full"
            x-init="selectedUnit = @js($defaultUnit)">
            <option value="">-Pilih Satuan-</option>
            @foreach($units as $unit)
            <option value="{{ $unit }}">{{ $unit }}</option>
            @endforeach
        </select>
    </div>


    <!-- Modal Popover -->
    <div x-show="showPopover" @click.away="showPopover = false" x-transition
        class="absolute z-50 p-4 w-80 text-sm text-gray-500 bg-white border border-gray-200 rounded-lg shadow-lg">

        <div class="flex justify-between mb-3">
            <h3 class="font-semibold text-gray-900">Tambah Satuan</h3>
            <button type="button" @click="showPopover = false" class="text-gray-500 hover:text-gray-900">✕</button>
        </div>


        <!-- Pesan Sukses/Error -->
        @if (session()->has('unit-message'))
        <div class="p-2 mb-2 text-sm text-green-800 bg-green-50 rounded-lg">
            {{ session('unit-message') }}
        </div>
        @endif
        @if (session()->has('unit-error'))
        <div class="p-2 mb-2 text-sm text-red-800 bg-red-50 rounded-lg">
            {{ session('unit-error') }}
        </div>
        @endif

        <!-- Form Tambah Satuan -->
        <input type="text" wire:model.defer="newUnit"
            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full mb-2"
            placeholder="Masukkan satuan baru">
        @error('newUnit')
        <span class="text-red-500 text-xs">{{ $message }}</span>
        @enderror

        <button type="button" wire:click="addUnit"
            class="w-full text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5">
            Tambah Satuan
        </button>
    </div>
</div>
