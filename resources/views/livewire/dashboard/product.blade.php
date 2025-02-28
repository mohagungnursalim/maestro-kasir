<div>

    <head>
        <style>
            .image {
                position: relative;
            }

            .image img {
                display: block;
                position: absolute;
                left: 0px;
                /* Menyesuaikan dengan padding-left 6 (px-6) */
                top: 12px;
                width: 40px;
                /* Sesuai dengan width gambar (w-10) */
                height: 40px;
                /* Sesuai dengan height gambar (h-10) */
                border-radius: 50%;
                /* Membuat bentuk lingkaran */
              
            }

        </style>
    
    </head>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">

                {{-- Button Add & Search --}}
                <div class="relative bg-white shadow-md sm:rounded-lg">
                    <div
                        class="flex flex-col items-center justify-between p-4 space-y-3 md:flex-row md:space-y-0 md:space-x-4">
                        <div class="w-full md:w-1/2">

                            <!-- Tombol Buka Modal -->
                            <button type="button" id="addProductButton" data-modal-target="addModal"
                                data-modal-toggle="addModal"
                                class="text-white bg-purple-500 to-blue-500 hover:bg-gradient-to-bl focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center me-2 mb-2">
                                Tambah Produk
                            </button>
                            <form class="flex items-center">
                                <label for="simple-search" class="sr-only">Search</label>
                                <div class="relative w-full">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                        <svg class="w-5 h-5 text-gray-500" fill="currentColor"
                                            viewbox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd"
                                                d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <input wire:model.live.debounce.500ms="search" type="text" id="simple-search"
                                        class="block w-full p-2 pl-10 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-primary-500 focus:border-primary-500"
                                        placeholder="Search">
                                </div>
                            </form>
                            <a wire:loading wire:target='search' class="text-secondary text-sm mb-2">
                                Mencari...
                                <svg role="status"
                                    class="inline w-4 h-4 me-3 text-gray-800 animate-spin" viewBox="0 0 100 101"
                                    fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z"
                                        fill="#E5E7EB" />
                                    <path
                                        d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z"
                                        fill="currentColor" />
                                </svg>
                            </a>
                        </div>
                        <div
                            class="flex flex-col items-stretch justify-end flex-shrink-0 w-full space-y-2 md:w-auto md:flex-row md:space-y-0 md:items-center md:space-x-3">
                        </div>
                    </div>
                </div>

                <div wire:init='loadInitialProducts' class="relative overflow-x-auto shadow-md sm:rounded-lg">
                    <table class="w-full text-sm text-left rtl:text-right text-gray-500">
                        <thead class="text-xs text-white uppercase bg-gray-500">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-center">Gambar</th>
                                <th scope="col" class="px-6 py-3 text-center">Nama Produk</th>
                                <th scope="col" class="px-6 py-3 text-center">Sku</th>
                                <th scope="col" class="px-6 py-3 text-center">Supplier</th>
                                <th scope="col" class="px-6 py-3 text-center">Harga</th>
                                <th scope="col" class="px-6 py-3 text-center">Stok</th>
                                <th scope="col" class="px-6 py-3 text-center">Satuan</th>
                                <th scope="col" class="px-6 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if (!$loaded)
                            {{-- Tampilkan skeleton saat data belum dimuat --}}
                            @for ($i = 0; $i < 8; $i++) <tr
                                class="bg-white border-b">
                                <td class="px-6 py-4 text-center">
                                    <div class="w-10 h-10 bg-gray-300 rounded-full animate-pulse">
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="h-4 bg-gray-300 rounded animate-pulse"></div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="h-4 bg-gray-300 rounded animate-pulse"></div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="h-4 bg-gray-300 rounded animate-pulse"></div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="h-4 bg-gray-300 rounded animate-pulse"></div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="h-4 bg-gray-300 rounded animate-pulse"></div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="h-4 w-10 bg-gray-300 rounded animate-pulse"></div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="h-4 mb-2 w-10 bg-gray-300 rounded animate-pulse"></div>
                                    <div class="h-4 mb-2 w-10 bg-gray-300 rounded animate-pulse"></div>
                                    <div class="h-4 w-10 bg-gray-300 rounded animate-pulse"></div>
                                </td>
                                </tr>
                                @endfor
                                @else
                                {{-- Tampilkan data asli setelah dimuat --}}
                                @forelse ($products as $product)
                                <tr class="bg-white border-b hover:bg-gray-300 text-gray-900">
                                    <th scope="row" class="flex items-center px-6 py-4 whitespace-nowrap">
                                        <div class="image flex items-center px-6 py-4">
                                            @if ($product->image)
                                                <img src="{{ asset('storage/' . $product->image) }}" class="lazy-img w-10 h-10 rounded-full">
                                            @endif
                                        </div>
                                    </th>
                                    <td scope="row" class="px-6 py-4 font-medium text-gray-900">
                                        {{ $product->name }}
                                    </td>
                                    <td class="px-6 py-4 text-center">{{ $product->sku }}</td>
                                    <td class="px-6 py-4 text-center">{{ $product->supplier_name }}</td> 
                                    <td class="px-6 py-4 text-center">Rp{{ number_format($product->price, 0, ',', '.') }}</td>
                                    <td class="px-6 py-4 text-center">{{ $product->stock }}</td>
                                    <td class="px-6 py-4 text-center">{{ $product->unit }}</td>
                                    <td class="px-6 py-4">
                                        <div>
                                            <button wire:click="editModal({{ $product->id }})"
                                                class="mb-2 bg-green-100 hover:bg-green-200 text-green-800 text-xs font-semibold me-2 px-2.5 py-0.5 rounded border border-green-400 inline-flex items-center justify-center">
                                                Ubah
                                            </button>                                        
                                        </div>
                                        <div>
                                            <button wire:click="deleteConfirmation({{ $product->id }})"
                                                class="mb-2 bg-red-100 hover:bg-red-200 text-red-800 text-xs font-semibold me-2 px-2.5 py-0.5 rounded border border-red-400 inline-flex items-center justify-center">
                                                Hapus
                                            </button>
                                        </div>
                                        <div>
                                            <button wire:click="detailModal({{ $product->id }})"
                                                class="bg-blue-100 hover:bg-blue-200 text-blue-800 text-xs font-semibold me-2 px-2.5 py-0.5 rounded border border-blue-400 inline-flex items-center justify-center">
                                                Detail
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center align-middle h-20">
                                        Tidak ada data ditemukan!
                                    </td>
                                </tr>
                                @endforelse

                            @endif
                            </tbody>
                            </table>


            </div>
            {{-- Tombol Load More --}}
            @if($products->count() >= $limit && $totalProducts > $limit)
            <div class="mt-4 mb-2 flex justify-center">
                <!-- Tombol "Tampilkan Lebih" (akan hilang saat loading) -->
                <button wire:click="loadMore"
                    class="bg-gray-800 text-white px-6 py-2 rounded-full shadow-md hover:bg-gray-700 focus:outline-none focus:ring focus:ring-gray-400 focus:ring-opacity-50"
                    wire:loading.remove wire:target="loadMore">
                    Tampilkan Lebih
                </button>

                <!-- Tombol Loading (hanya muncul saat loading) -->
                <button
                    class="mb-5 bg-gray-800 text-white px-6 py-2 rounded-full shadow-md cursor-not-allowed focus:outline-none focus:ring focus:ring-gray-400 focus:ring-opacity-50"
                    type="button" disabled wire:loading wire:target="loadMore">
                    Memuat..
                    <svg class="inline w-5 h-5 text-white animate-spin ml-2" xmlns="http://www.w3.org/2000/svg"
                        fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                        </circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8v4a4 4 0 100 8v4a8 8 0 01-8-8z"></path>
                    </svg>
                </button>
            </div>
            @endif
        </div>
    </div>
</div>


{{-- Modal Create --}}
<div id="addModal" tabindex="-1"  data-modal-backdrop="addModal"
    class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-modal md:h-full"
    wire:ignore.self>
    <div class="relative p-4 w-full max-w-2xl h-full md:h-auto">
        <!-- Modal content -->
        <div class="relative p-4 bg-white rounded-lg shadow sm:p-5">
            <!-- Modal header -->
            <div class="flex justify-between items-center pb-4 mb-4 rounded-t border-b sm:mb-5">
                <h3 class="text-lg font-semibold text-gray-900">
                    Tambah Produk
                </h3>
            </div>
            <!-- Modal body -->
            <form wire:submit.prevent="store">
                <div class="grid gap-4 mb-4 sm:grid-cols-2" style="max-height: 60vh; overflow-y: auto;">
                    <div>
                        <label for="name" class="block mb-2 text-sm font-medium text-gray-900">Nama
                            Produk</label>
                        <input wire:model='name' type="text" name="name" id="name"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full"
                            placeholder="Masukan Nama Produk">
                        @error('name') <span class="text-red-500 text-xs">{{ $message }} @enderror
                    </div>
                    <div>
                        <label for="sku"
                            class="block mb-2 Updatetext-sm font-medium text-gray-900">SKU</label>
                        <input wire:model='sku' type="text" name="sku" id="sku"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full"
                            placeholder="Masukan SKU">
                        @error('sku') <span class="text-red-500 text-xs">{{ $message }} @enderror
                    </div>
                    <div>
                        <label for="price"
                            class="block mb-2 text-sm font-medium text-gray-900">Harga</label>
                        <input wire:model='price' type="number" name="price" id="price"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full"
                            placeholder="Masukan Harga">
                        @error('price') <span class="text-red-500 text-xs">{{ $message }} @enderror
                    </div>
                    <div>
                        <label for="stock"
                            class="block mb-2 text-sm font-medium text-gray-900">Stok</label>
                        <input wire:model='stock' type="number" name="stock" id="stock"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full"
                            placeholder="Masukan Stok">
                        @error('stock') <span class="text-red-500 text-xs">{{ $message }} @enderror
                    </div>

                    <div>
                        <label for="unit"
                            class="block mb-2 text-sm font-medium text-gray-900">Satuan</label>
                        <select wire:model='unit' id="unit"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full">
                            <option selected="">-Pilih Satuan-</option>
                            <option value="KG">KG</option>
                            <option value="Pcs">Pcs</option>
                            <option value="Box">Box</option>
                            <option value="Pack">Pack</option>
                        </select>
                        @error('unit') <span class="text-red-500 text-xs">{{ $message }} @enderror
                    </div>
                    
                    <div x-data="supplierSelect()" 
                        x-init="fetchSuppliers(); window.addEventListener('addedSuccess', () => resetSupplier())" 
                        class="relative">
                        
                        
                        <label for="supplier_id" class="block mb-2 text-sm font-medium text-gray-900">Supplier</label>

                        <!-- Input Pencarian -->
                        <input id="supplier_id" type="text" x-model="search" @input.debounce="fetchSuppliers()" placeholder="Cari Supplier..."
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full">

                        <!-- Pesan Loading -->
                        <div x-show="loading" class="text-sm text-gray-500 mt-1">
                            Mencari..
                        </div>

                        <!-- Dropdown Pilihan Supplier -->
                        <ul x-show="suppliers.length > 0" @click.outside="suppliers = []"
                            class="absolute z-10 bg-white border rounded-md w-full mt-1 max-h-48 overflow-auto shadow-lg">
                            <template x-for="supplier in suppliers" :key="supplier.id">
                                <li @click="selectSupplier(supplier)" 
                                    x-text="supplier.name" 
                                    class="cursor-pointer hover:bg-gray-200 px-4 py-2">
                                </li>
                            </template>
                        </ul>

                        <!-- Hidden Input untuk Livewire -->
                        <input type="hidden" x-model="selectedSupplierId" name="supplier_id" wire:model="supplier_id">

                        @error('supplier_id') 
                            <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                    </div>
                    

                    <div class="sm:col-span-2" x-data="imageUploader()">
                        <!-- Input Upload -->
                        <label class="block mb-2 text-sm font-medium text-gray-900" for="image">Upload Gambar</label>
                        <input wire:model='image'
                            type="file"
                            id="image"
                            class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none"
                            accept=".png,.jpg,.jpeg,.gif,.svg"
                            x-on:change="previewImage($event)"
                        >
                        <p class="mt-1 text-sm text-gray-500" id="image">SVG, PNG, JPG or GIF (Max: 5MB)</p>
                    
                        <!-- Progress Container -->
                        <div x-show="uploading" x-transition:enter="transition ease-out duration-300" class="w-full">
                            <div class="flex justify-between mb-1">
                                <span class="text-sm font-medium text-gray-700">Upload Progress</span>
                                <span class="text-sm font-medium text-gray-700" x-text="`${progress}%`"></span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2.5">
                                <div
                                    class="h-2.5 rounded-full transition-all duration-150 ease-out"
                                    :style="`width: ${progress}%`"
                                    :class="{
                                        'bg-blue-600': progress < 60,
                                        'bg-yellow-400': progress >= 60 && progress < 80,
                                        'bg-green-500': progress >= 80
                                    }"
                                ></div>
                            </div>
                        </div>
                    
                        <!-- Error Message -->
                        @error('image') <span class="text-red-500 text-xs">{{ $message }} @enderror
                        <template x-if="error">
                            <span class="text-red-500 text-xs" x-text="error"></span>
                        </template>
                    
                        <!-- Image Preview -->
                        <template x-if="imagePreview">
                            <div class="flex justify-center items-center mt-2">
                                <div class="flex flex-col items-center">
                                    <!-- Preview Image -->
                                    <img :src="imagePreview" alt="Preview" class="max-w-full h-64 rounded-lg object-cover border-2 border-gray-300 border-dashed">
                                    <!-- File Name -->
                                    <p class="mt-4 text-sm text-gray-500">
                                        <span class="font-semibold" x-text="fileName"></span>
                                        <span x-text="fileSize"></span>
                                    </p>
                                </div>
                            </div>
                        </template>
                    </div>
                    

                    <div class="sm:col-span-2">
                        <label for="description"
                            class="block mb-2 text-sm font-medium text-gray-900">Description</label>
                        <textarea wire:model='description' id="description" rows="4"
                            class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-primary-500 focus:border-primary-500"
                            placeholder="Masukan deskripsi disini"></textarea>
                        @error('description') <span class="text-red-500 text-xs">{{ $message }} @enderror
                    </div>
                </div>
                <div class="flex items-center justify-center">
                    <button wire:loading.remove wire:target='store' type="submit"
                        class="text-white bg-gradient-to-r from-purple-500 to-pink-500 hover:bg-gradient-to-l focus:ring-4 focus:outline-none focus:ring-purple-200 font-medium rounded-lg text-sm px-5 py-2.5 text-center me-2">
                        Simpan
                    </button>
                    <button disabled wire:loading wire:target='store' 
                        class="text-white bg-gradient-to-r from-purple-500 to-pink-500 hover:bg-gradient-to-l focus:ring-4 focus:outline-none focus:ring-purple-200 font-medium rounded-lg text-sm px-5 py-2.5 text-center me-2">
                        Menyimpan..
                        <svg class="inline w-5 h-5 text-white animate-spin ml-2" xmlns="http://www.w3.org/2000/svg"
                            fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8v4a4 4 0 100 8v4a8 8 0 01-8-8z"></path>
                        </svg>
                    </button>

                    <button wire:loading.remove wire:target='store' id="closeButtonAddModal"
                    type="button" class="py-2.5 px-5 ms-3 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-100">
                        Tutup
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>



{{-- Modal Edit --}}
<div id="editModal" tabindex="-1"  data-modal-backdrop="editModal"
    class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-modal md:h-full"
    wire:ignore.self>
    <div class="relative p-4 w-full max-w-2xl h-full md:h-auto">
        <!-- Modal content -->
        <div class="relative p-4 bg-white rounded-lg shadow sm:p-5">
            <!-- Modal header -->
            <div class="flex justify-between items-center pb-4 mb-4 rounded-t border-b sm:mb-5">
                <h3 class="text-lg font-semibold text-gray-900">
                    Edit Produk {{ $nameUpdate }}

                </h3>
            </div>
            <!-- Modal body -->
            <form wire:submit.prevent="update">
                <div class="grid gap-4 mb-4 sm:grid-cols-2" style="max-height: 60vh; overflow-y: auto;">
                    <div>
                        <label for="nameUpdate" class="block mb-2 text-sm font-medium text-gray-900">Nama Produk</label>
                        <input wire:model='nameUpdate' type="text" name="nameUpdate" id="nameUpdate"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full"
                            placeholder="Masukan Nama Produk">
                        @error('nameUpdate') <span class="text-red-500 text-xs">{{ $message }} @enderror
                    </div>
                    <div>
                        <label for="skuUpdate" class="block mb-2 text-sm font-medium text-gray-900">SKU</label>
                        <input wire:model='skuUpdate' type="text" name="skuUpdate" id="skuUpdate"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full"
                            placeholder="Masukan SKU">
                        @error('skuUpdate') <span class="text-red-500 text-xs">{{ $message }} @enderror
                    </div>
                    <div>
                        <label for="priceUpdate" class="block mb-2 text-sm font-medium text-gray-900">Harga</label>
                        <input wire:model='priceUpdate' type="number" name="priceUpdate" id="priceUpdate"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full"
                            placeholder="Masukan Harga">
                        @error('priceUpdate') <span class="text-red-500 text-xs">{{ $message }} @enderror
                    </div>
                    <div>
                        <label for="stockUpdate" class="block mb-2 text-sm font-medium text-gray-900">Stok</label>
                        <input wire:model='stockUpdate' type="number" name="stockUpdate" id="stockUpdate"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full"
                            placeholder="Masukan Stok">
                        @error('stockUpdate') <span class="text-red-500 text-xs">{{ $message }} @enderror
                    </div>

                    <div>
                        <label for="unitUpdate" class="block mb-2 text-sm font-medium text-gray-900">Satuan</label>
                        <select wire:model='unitUpdate' id="unitUpdate"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full">
                            <option selected="">-Pilih Satuan-</option>
                            <option value="KG">KG</option>
                            <option value="Pcs">Pcs</option>
                            <option value="Box">Box</option>
                            <option value="Pack">Pack</option>
                        </select>
                        @error('unitUpdate') <span class="text-red-500 text-xs">{{ $message }} @enderror
                    </div>


                    
                <div x-data="supplierSelectUpdate()" 
                    x-init='updateSupplier(@json(["id" => $supplier_idUpdate, "name" => $supplierName]))'
                    class="relative">
                   <label for="supplier_idUpdate" class="block mb-2 text-sm font-medium text-gray-900">Supplier</label>
               
                   <!-- Input Pencarian -->
                   <input id="supplier_idUpdate" type="text" x-model="search" @input.debounce="fetchSuppliers()" placeholder="Cari Supplier..."
                       class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full">
               
                   <!-- Pesan Loading -->
                   <div x-show="loading" class="text-sm text-gray-500 mt-1">
                       Mencari..
                   </div>
               
                   <!-- Dropdown Pilihan Supplier -->
                   <ul x-show="suppliers.length > 0" @click.outside="suppliers = []"
                       class="absolute z-10 bg-white border rounded-md w-full mt-1 max-h-48 overflow-auto shadow-lg">
                       <template x-for="supplier in suppliers" :key="supplier.id">
                           <li @click="selectSupplier(supplier)" 
                               x-text="supplier.name" 
                               class="cursor-pointer hover:bg-gray-200 px-4 py-2">  
                           </li>
                       </template>
                   </ul>
               
                   <!-- Hidden Input untuk Livewire -->
                   <input type="hidden" x-model="selectedSupplierId" name="supplier_idUpdate" wire:model="supplier_idUpdate">
               
                   @error('supplier_idUpdate') 
                       <span class="text-red-500 text-xs">{{ $message }}</span>
                   @enderror
                </div>
                   

                    <div class="sm:col-span-2" x-data="imageUploader()" x-init="init()">
                        <!-- Input Upload -->
                        <label class="block mb-2 text-sm font-medium text-gray-900" for="image">Upload Gambar</label>
                        <input wire:model='imageUpdate'
                            type="file"
                            id="image"
                            class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none"
                            accept=".png,.jpg,.jpeg,.gif,.svg"
                            x-on:change="previewImage($event)"
                            x-ref="fileInput"
                        >
                        <p class="mt-1 text-sm text-gray-500">SVG, PNG, JPG or GIF (Max: 5MB)</p>
                    
                         <!-- Progress Container -->
                        <div x-show="uploading" x-transition:enter="transition ease-out duration-300" class="w-full">
                            <div class="flex justify-between mb-1">
                                <span class="text-sm font-medium text-gray-700">Upload Progress</span>
                                <span class="text-sm font-medium text-gray-700" x-text="`${progress}%`"></span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2.5">
                                <div
                                    class="h-2.5 rounded-full transition-all duration-150 ease-out"
                                    :style="`width: ${progress}%`"
                                    :class="{
                                        'bg-blue-600': progress < 60,
                                        'bg-yellow-400': progress >= 60 && progress < 80,
                                        'bg-green-500': progress >= 80
                                    }"
                                ></div>
                            </div>
                        </div>

                        <!-- Error Message -->
                        @error('imageUpdate') <span class="text-red-500 text-xs">{{ $message }} @enderror
                        <template x-if="error">
                            <span class="text-red-500 text-xs" x-text="error"></span>
                        </template>
                    
                        <!-- Tampilkan gambar preview atau gambar lama -->
                        <template x-if="imagePreview || existingImage">
                            <div class="flex justify-center items-center mt-2">
                                <div class="flex flex-col items-center">
                                    <!-- Preview Image -->
                                    <img 
                                        :src="imagePreview || '{{ asset('storage') }}/' + existingImage" 
                                        alt="Preview" 
                                        class="max-w-full h-64 rounded-lg object-cover border-2 border-gray-300 border-dashed">
                                    
                                    <!-- File Name -->
                                    <p class="mt-4 text-sm text-gray-500">
                                        <span class="font-semibold" x-text="fileName || (existingImage ? existingImage.split('/').pop() : '')"></span>
                                        <span x-text="fileSize"></span>
                                    </p>
                                </div>
                            </div>
                        </template>         

                    </div>
                    

                    <div class="sm:col-span-2">
                        <label for="descriptionUpdate"
                            class="block mb-2 text-sm font-medium text-gray-900">Description</label>
                        <textarea wire:model='descriptionUpdate' id="descriptionUpdate" rows="4"
                            class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-primary-500 focus:border-primary-500"
                            placeholder="Masukan deskripsi disini"></textarea>
                        @error('descriptionUpdate') <span class="text-red-500 text-xs">{{ $message }} @enderror
                    </div>
                </div>
                <div class="flex items-center justify-center">
                    <button wire:loading.remove wire:target='update' wire:click='update' type="submit"
                        class="text-white bg-gradient-to-r from-purple-500 to-pink-500 hover:bg-gradient-to-l focus:ring-4 focus:outline-none focus:ring-purple-200 font-medium rounded-lg text-sm px-5 py-2.5 text-center me-2">
                        Simpan
                    </button>
                    <button disabled wire:loading wire:target='update' 
                        class="text-white bg-gradient-to-r from-purple-500 to-pink-500 hover:bg-gradient-to-l focus:ring-4 focus:outline-none focus:ring-purple-200 font-medium rounded-lg text-sm px-5 py-2.5 text-center me-2">
                        Menyimpan..
                        <svg class="inline w-5 h-5 text-white animate-spin ml-2" xmlns="http://www.w3.org/2000/svg"
                            fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8v4a4 4 0 100 8v4a8 8 0 01-8-8z"></path>
                        </svg>
                    </button>

                    <button wire:loading.remove wire:target='update' id="closeButtonEditModal"
                    type="button" class="py-2.5 px-5 ms-3 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-100">
                        Tutup
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>



{{-- Modal Detail --}}
<div id="detailModal" tabindex="-1" data-modal-backdrop="detailModal"
    class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-modal md:h-full"
    wire:ignore.self>
    <div class="relative p-4 w-full max-w-2xl h-full md:h-auto">
        <!-- Modal content -->
        <div class="relative p-4 bg-white rounded-lg shadow sm:p-5">
            <!-- Modal header -->
            <div class="flex justify-between items-center pb-4 mb-4 rounded-t border-b sm:mb-5">
                <h3 class="text-lg font-semibold text-gray-900">
                   {{ $nameDetail }}
                </h3>
            </div>
            
            <div class="container mx-auto p-4">
                <div class="flex flex-col md:flex-row gap-4">
                    @if ($imageDetail)    
                    <!-- Container untuk gambar dan stock (fixed di sebelah kiri) -->
                    <div class="md:sticky md:top-4 md:w-1/3">
                        <div class="bg-white rounded-lg p-4">
                            <!-- Image container -->
                            <div class="flex justify-center">
                                <img class="h-36 w-36 rounded-lg object-cover" src="{{ asset('storage/' . $imageDetail) }}">
                            </div>
                           
                            <div class="mt-4">
                                <p class="text-xs text-gray-600">Produk: {{ $nameDetail }}</p>
                                <p class="text-xs text-gray-600">SKU: {{ $stockDetail }}</p>
                                <p class="text-xs text-gray-600">Supplier: {{ optional($product->supplier)->name ?? '-' }}</p>
                                <p class="text-xs text-gray-600">Harga: Rp {{ number_format($priceDetail, 0, ',', '.') }}</p>
                                <p class="text-xs text-gray-600">Stok: {{ $stockDetail }}</p>
                                <p class="text-xs text-gray-600">Satuan: {{ $unitDetail }}</p>
                            </div>
                            <div class="mt-4">
                                <p class="text-xs text-gray-600">Ditambahkan: <br>{{ $created_at }}</p>
                                <p class="text-xs text-gray-600">Diperbarui: <br>{{ $updated_at }}</p>
                               
                            </div>
                        </div>
                    </div>
                    
                    <!-- Container untuk description (di sebelah kanan) -->
                    <div class="md:w-2/3 max-h-[60vh] overflow-y-auto">
                        <div class="bg-white rounded-lg p-4">
                            <p class="text-gray-700">
                                {{ $descriptionDetail }}
                            </p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            
            
                <div class="flex items-center justify-center">
                    <button id="closeButtonDetailModal"
                    type="button" class="py-2.5 px-5 ms-3 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-100">
                        Tutup
                    </button>
                </div>
        </div>
    </div>
</div>




{{-- Script modal Add --}}
<script>
    document.addEventListener('livewire:navigated', () => {
        // Menangkap event Livewire dan menutup modal
        Livewire.on('addedSuccess', () => {
            const modalElement = document.getElementById('addModal');
            if (modalElement) {
                const modal = new Modal(modalElement);
                modal.hide(); // Menutup modal
            }
            Livewire.dispatch('resetForm'); // Mereset form di Livewire
        });

        // Tombol untuk membuka modal
        const addProductButton = document.getElementById('addProductButton');
        if (addProductButton) {
            addProductButton.addEventListener('click', () => {
                const modalElement = document.getElementById('addModal');
                if (modalElement) {
                    const modal = new Modal(modalElement);
                    modal.show(); // Membuka modal
                }
            });
        }

       const closeButtonAddModal = document.getElementById('closeButtonAddModal');
        
        if (closeButtonAddModal) {
            closeButtonAddModal.addEventListener('click', () => {
                const modalElement = document.getElementById('addModal');
                if (modalElement) {
                    const modal = new Modal(modalElement);
                    modal.hide(); // Menutup modal
                }

                Livewire.dispatch('resetForm'); // Mereset form di Livewire
            });
        }
    });
</script>


{{-- Alpine FileUpload Form Add --}}
<script>
    function imageUploader() {
        return {
            imagePreview: null,
            fileName: '',
            fileSize: '',
            error: null,
            uploading: false,
            progress: 0,

            previewImage(event) {
                const file = event.target.files[0];

                if (!file) {
                    this.error = "No file selected.";
                    return;
                }

                if (!['image/png', 'image/jpeg', 'image/jpg', 'image/gif', 'image/svg+xml'].includes(file.type)) {
                    this.error = "Gambar harus berupa gambar (PNG, JPG, GIF, SVG).";
                    return;
                }

                if (file.size > 5 * 1024 * 1024) {
                    this.error = "Gambar tidak boleh lebih dari 5MB";
                    return;
                }

                this.error = null;
                this.fileName = file.name;
                this.fileSize = `${(file.size / 1024).toFixed(2)} KB`;

                const reader = new FileReader();
                reader.onload = (e) => {
                    this.imagePreview = e.target.result;
                };
                reader.readAsDataURL(file);

                // Simulate progress bar
                this.simulateProgress();
            },

            simulateProgress() {
                this.uploading = true;
                this.progress = 0;

                const interval = setInterval(() => {
                    this.progress += 10;

                    if (this.progress >= 100) {
                        clearInterval(interval);
                        this.uploading = false;
                    }
                }, 200);
            },

            resetPreview() {
                this.imagePreview = null;
                this.fileName = '';
                this.fileSize = '';
                this.error = null;
                this.uploading = false;
                this.progress = 0;
            },

            init() {
                window.addEventListener('addedSuccess', () => {
                    this.resetPreview(); // Reset preview image ketika event `addedSuccess` ditangkap
                });
            },
        };
    }
</script>


{{-- Script search supplier modal Add --}}
<script>
    function supplierSelect() {
        return {
            suppliers: [],
            search: '',
            selectedSupplierId: null,
            loading: false,
            timeout: null, // Variabel untuk menyimpan timeout

            fetchSuppliers() {
                // Hapus request sebelumnya jika masih dalam antrian
                clearTimeout(this.timeout);

                // Set delay 300ms sebelum request dilakukan
                this.timeout = setTimeout(async () => {
                    if (this.search.length < 1) {
                        this.suppliers = [];
                        return;
                    }

                    this.loading = true;

                    try {
                        let response = await fetch(`/api/suppliers?search=${this.search}`);
                        this.suppliers = await response.json();
                    } catch (error) {
                        console.error('Error fetching suppliers:', error);
                    }

                    this.loading = false;
                }, 500); // Delay 500ms
            },

            selectSupplier(supplier) {
                this.selectedSupplierId = supplier.id;
                this.search = supplier.name;
                this.suppliers = [];
                @this.set('supplier_id', supplier.id); // Update Livewire
            },
            resetSupplier() {
                this.selectedSupplierId = null;
                this.search = '';
            }
        };
    
    }
</script>


{{-- Script modal Edit --}}
<script>
    document.addEventListener('livewire:navigated', () => { 
        Livewire.on('updatedSuccess', (event) => {
            const modal = new Modal(document.getElementById('editModal'));
            modal.hide(); // Menutup modal setelah data berhasil ditambahkan
        
        });

        Livewire.on('showEditModal', (event) => {
            const modal = new Modal(document.getElementById('editModal'));
            modal.show(); // Menampilkan modal saat tombol ditekan
        });


       // Event listener untuk tombol close
       const closeButtonEditModal = document.getElementById('closeButtonEditModal');
        
        if (closeButtonEditModal) {
            closeButtonEditModal.addEventListener('click', () => {
                const modalElement = document.getElementById('editModal');
                if (modalElement) {
                    const modal = new Modal(modalElement);
                    modal.hide(); // Menutup modal
                }
            });
        }
    });
</script>

{{-- Alpine FileUpload Form Update --}}
<script>
    function imageUploader() {
        return {
            imagePreview:  null, 
            fileName: '',
            fileSize: '',
            error: null,
            uploading: false,
            progress: 0,
            existingImage: @entangle('currentImage') ?? null,  // Bind gambar yang sudah ada
            hasImage: false,

            

            previewImage(event) {
                const file = event.target.files[0];

                if (!file) {
                    this.error = "No file selected.";
                    return;
                }

                if (!['image/png', 'image/jpeg', 'image/jpg', 'image/gif', 'image/svg+xml'].includes(file.type)) {
                    this.error = "Gambar harus berupa gambar (PNG, JPG, GIF, SVG).";
                    return;
                }

                if (file.size > 5 * 1024 * 1024) {
                    this.error = "Gambar tidak boleh lebih dari 5MB";
                    return;
                }

                this.error = null;
                this.fileName = file.name;
                this.fileSize = `${(file.size / 1024).toFixed(2)} KB`;

                const reader = new FileReader();
                reader.onload = (e) => {
                    this.imagePreview = e.target.result;
                    this.hasImage = true;
                };
                reader.readAsDataURL(file);

                // Simulate progress bar
                this.simulateProgress();
            },


            simulateProgress() {
                this.uploading = true;
                this.progress = 0;

                const interval = setInterval(() => {
                    this.progress += 10;

                    if (this.progress >= 100) {
                        clearInterval(interval);
                        this.uploading = false;
                    }
                }, 200);
            },

            resetPreview() {
                this.imagePreview = null;
                this.fileName = '';
                this.fileSize = '';
                this.error = null;
                this.uploading = false;
                this.progress = 0;
                
                // Also reset the file input
                const fileInput = document.getElementById('image');
                if (fileInput) {
                    fileInput.value = '';
                }
            },


            init() {
                // Jika ada gambar yang sudah ada (misalnya saat edit), tampilkan gambar tersebut
                if (this.existingImage) {
                    this.hasImage = true;
                }

                 // Listener untuk event addedSuccess dari Livewire
                 Livewire.on('addedSuccess', () => {
                    this.resetPreview(); // Panggil untuk clear preview
                   
                });
                // Listener untuk event updatedSuccess dari Livewire
                Livewire.on('updatedSuccess', () => {
                    this.resetPreview(); // Panggil untuk clear preview
                });
            },
        };
    }
</script>

{{-- Script search supplier modal Edit --}}
<script>
    function supplierSelectUpdate() {
        return {
            suppliers: [],
            search: '',
            selectedSupplierId: null,
            loading: false,
            timeout: null,

            updateSupplier(supplier) {
                this.selectedSupplierId = supplier.id ?? null;
                this.search = supplier.name ?? '';
            },

            fetchSuppliers() {
                clearTimeout(this.timeout);

                this.timeout = setTimeout(async () => {
                    if (this.search.length < 1) {
                        this.suppliers = [];
                        return;
                    }

                    this.loading = true;

                    try {
                        let response = await fetch(`/api/suppliers?search=${this.search}`);
                        this.suppliers = await response.json();
                    } catch (error) {
                        console.error('Error fetching suppliers:', error);
                    }

                    this.loading = false;
                }, 300);
            },

            selectSupplier(supplier) {
                this.selectedSupplierId = supplier.id;
                this.search = supplier.name;
                this.suppliers = [];

                // Mengupdate data Livewire
                @this.set('supplier_idUpdate', supplier.id); // Mengirim id baru ke Livewire
                @this.set('supplierName', supplier.name);   // Mengirim nama baru ke Livewire (Jika perlu)
            }
        };
    }
</script>

{{-- Script modal detail --}}
<script>
    document.addEventListener('livewire:navigated', () => { 
        Livewire.on('showDetailModal', (event) => {
            const modal = new Modal(document.getElementById('detailModal'));
            modal.show(); // Menampilkan modal saat tombol ditekan
        });

        // Event listener untuk tombol close
       const closeButtonDetailModal = document.getElementById('closeButtonDetailModal');
        
        if (closeButtonDetailModal) {
            closeButtonDetailModal.addEventListener('click', () => {
                const modalElement = document.getElementById('detailModal');
                if (modalElement) {
                    const modal = new Modal(modalElement);
                    modal.hide(); // Menutup modal
                }
            });
        }
    });
</script>

{{-- Sweet alert,added success --}}
<script>
        Livewire.on('addedSuccess', (event) => {
            const Toast = Swal.mixin({
                toast: true,
                position: "top-end",
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.onmouseenter = Swal.stopTimer;
                    toast.onmouseleave = Swal.resumeTimer;
                }
            });
            Toast.fire({
                icon: "success",
                title: "Produk berhasil ditambahkan!"
            });

        });
</script>

{{-- Sweet alert,updated success --}}
<script>
   Livewire.on('updatedSuccess', (event) => {
            const Toast = Swal.mixin({
                toast: true,
                position: "top-end",
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.onmouseenter = Swal.stopTimer;
                    toast.onmouseleave = Swal.resumeTimer;
                }
            });
            Toast.fire({
                icon: "success",
                title: "Produk berhasil diperbarui!"
            });

    });

</script>

{{-- Script modal delete --}}
<script>
    Livewire.on('showDeleteConfirmation', (event) => {
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Produk ini akan dihapus?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                Livewire.dispatch('deleteConfirmed');
            }
        });
    });
    
    Livewire.on('deleteSuccess', (event) => {
        const Toast = Swal.mixin({
                toast: true,
                position: "top-end",
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.onmouseenter = Swal.stopTimer;
                    toast.onmouseleave = Swal.resumeTimer;
                }
            });
            Toast.fire({
                icon: "success",
                title: "Produk berhasil dihapus!"
            });

    });
</script>

</div>
