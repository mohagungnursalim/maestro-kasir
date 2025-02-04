<div>
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
                                class="text-white bg-gradient-to-br from-purple-600 to-blue-500 hover:bg-gradient-to-bl focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center me-2 mb-2">
                                Tambah Supplier
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

                <div wire:init='loadInitialSuppliers' class="relative overflow-x-auto shadow-md sm:rounded-lg">
                    <table class="w-full text-sm text-left rtl:text-right text-gray-500">
                        <thead class="text-xs text-white uppercase bg-gray-500">
                            <tr>
                                <th scope="col" class="px-6 py-3">Nama Supplier</th>
                                <th scope="col" class="px-6 py-3">Email</th>
                                <th scope="col" class="px-6 py-3">No.Hp</th>
                                <th scope="col" class="px-6 py-3">Alamat</th>
                                <th scope="col" class="px-6 py-3">Kota</th>
                                <th scope="col" class="px-6 py-3">Provinsi</th>
                                <th scope="col" class="px-6 py-3">Negara</th>
                                <th scope="col" class="px-6 py-3">Kode Pos</th>
                                <th scope="col" class="px-6 py-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if (!$loaded)
                            {{-- Tampilkan skeleton saat data belum dimuat --}}
                            @for ($i = 0; $i < 8; $i++) <tr
                                class="bg-white border-b">
                                <td class="px-6 py-4">
                                    <div class="h-4 bg-gray-300 rounded animate-pulse"></div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="h-4 bg-gray-300 rounded animate-pulse"></div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="h-4 bg-gray-300 rounded animate-pulse"></div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="h-4 bg-gray-300 rounded animate-pulse"></div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="h-4 bg-gray-300 rounded animate-pulse"></div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="h-4 w-10 bg-gray-300 rounded animate-pulse"></div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="h-4 w-10 bg-gray-300 rounded animate-pulse"></div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="h-4 w-10 bg-gray-300 rounded animate-pulse"></div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="h-4 mb-2 w-10 bg-gray-300 rounded animate-pulse"></div>
                                    <div class="h-4 mb-2 w-10 bg-gray-300 rounded animate-pulse"></div>
                                </td>
                                </tr>
                                @endfor
                                @else
                                {{-- Tampilkan data asli setelah dimuat --}}
                                @forelse ($suppliers as $supplier)
                                <tr class="bg-white border-b hover:bg-gray-300 text-gray-900">
                                <td class="px-6 py-4">
                                    {{ $supplier->name }}
                                </td>
                                <td class="px-6 py-4">{{ $supplier->email ? : '-' }}</td>
                                <td class="px-6 py-4">{{ $supplier->phone ? : '-'}}</td>
                                <td class="px-6 py-4">{{ $supplier->address ? : '-'}}</td>
                                <td class="px-6 py-4">{{ $supplier->city ? : '-'}}</td>
                                <td class="px-6 py-4">{{ $supplier->state ? : '-' }}</td>
                                <td class="px-6 py-4">{{ $supplier->country ? : '-' }}</td>
                                <td class="px-6 py-4">{{ $supplier->zip ? : '-' }}</td>
                                <td class="px-6 py-4">
                                    <div>
                                        <button wire:click="editModal({{ $supplier->id }})"
                                            class="mb-2 bg-green-100 hover:bg-green-200 text-green-800 text-xs font-semibold me-2 px-2.5 py-0.5 rounded  border border-green-400 inline-flex items-center justify-center">
                                            Ubah</button>
                                    </div>
                                    <div>
                                        <button wire:click="deleteConfirmation({{ $supplier->id }})"
                                            class="mb-2 bg-red-100 hover:bg-red-200 text-red-800 text-xs font-semibold me-2 px-2.5 py-0.5 rounded border border-red-400 inline-flex items-center justify-center">
                                            Delete
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
            @if($suppliers->count() >= $limit && $totalSuppliers > $limit)
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
                    Tambah Supplier
                </h3>
            </div>
            <!-- Modal body -->
            <form wire:submit.prevent="store">
                <div class="grid gap-4 mb-4 sm:grid-cols-2" style="max-height: 60vh; overflow-y: auto;">
                    <div>
                        <label for="name" class="block mb-2 text-sm font-medium text-gray-900">Nama Supplier</label>
                        <input autocomplete="name" wire:model="name" type="text" name="name" id="name"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full"
                            placeholder="Masukkan Nama Supplier">
                        @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label for="email" class="block mb-2 text-sm font-medium text-gray-900">Email</label>
                        <input autocomplete="email" wire:model="email" type="email" name="email" id="email"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full"
                            placeholder="Masukkan Email (Opsional)">
                        @error('email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label for="phone" class="block mb-2 text-sm font-medium text-gray-900">No. HP</label>
                        <input autocomplete="phone" wire:model="phone" type="text" name="phone" id="phone"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full"
                            placeholder="Masukkan No. HP (Opsional)">
                        @error('phone') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label for="address" class="block mb-2 text-sm font-medium text-gray-900">Alamat</label>
                        <input autocomplete="address" wire:model="address" type="text" name="address" id="address"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full"
                            placeholder="Masukkan Alamat (Opsional)">
                        @error('address') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label for="city" class="block mb-2 text-sm font-medium text-gray-900">Kota</label>
                        <input autocomplete="city" wire:model="city" type="text" name="city" id="city"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full"
                            placeholder="Masukkan Kota (Opsional)">
                        @error('city') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label for="state" class="block mb-2 text-sm font-medium text-gray-900">Provinsi</label>
                        <input autocomplete="state" wire:model="state" type="text" name="state" id="state"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full"
                            placeholder="Masukkan Provinsi (Opsional)">
                        @error('state') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label for="country" class="block mb-2 text-sm font-medium text-gray-900">Negara</label>
                        <input autocomplete="country" wire:model="country" type="text" name="country" id="country"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full"
                            placeholder="Masukkan Negara (Opsional)">
                        @error('country') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label for="zip" class="block mb-2 text-sm font-medium text-gray-900">Kode Pos</label>
                        <input autocomplete="zip" wire:model="zip" type="text" name="zip" id="zip"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full"
                            placeholder="Masukkan Kode Pos (Opsional)">
                        @error('zip') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
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
                    Edit Supplier {{ $nameUpdate }}

                </h3>
            </div>
        <!-- Modal body -->
        <form wire:submit.prevent="update">
            <div class="grid gap-4 mb-4 sm:grid-cols-2" style="max-height: 60vh; overflow-y: auto;">
                <div>
                    <label for="nameUpdate" class="block mb-2 text-sm font-medium text-gray-900">Nama Supplier</label>
                    <input wire:model='nameUpdate' type="text" name="nameUpdate" id="nameUpdate"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full"
                        placeholder="Masukan Nama Supplier">
                    @error('nameUpdate') <span class="text-red-500 text-xs">{{ $message }} @enderror
                </div>
                <div>
                    <label for="emailUpdate" class="block mb-2 text-sm font-medium text-gray-900">Email</label>
                    <input wire:model='emailUpdate' type="email" name="emailUpdate" id="emailUpdate"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full"
                        placeholder="Masukan Email (Opsional)">
                    @error('emailUpdate') <span class="text-red-500 text-xs">{{ $message }} @enderror
                </div>
                <div>
                    <label for="phoneUpdate" class="block mb-2 text-sm font-medium text-gray-900">No. HP</label>
                    <input wire:model='phoneUpdate' type="text" name="phoneUpdate" id="phoneUpdate"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full"
                        placeholder="Masukan No. HP (Opsional)">
                    @error('phoneUpdate') <span class="text-red-500 text-xs">{{ $message }} @enderror
                </div>
                <div>
                    <label for="addressUpdate" class="block mb-2 text-sm font-medium text-gray-900">Alamat</label>
                    <input wire:model='addressUpdate' type="text" name="addressUpdate" id="addressUpdate"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full"
                        placeholder="Masukan Alamat (Opsional)">
                    @error('addressUpdate') <span class="text-red-500 text-xs">{{ $message }} @enderror
                </div>
                <div>
                    <label for="cityUpdate" class="block mb-2 text-sm font-medium text-gray-900">Kota</label>
                    <input wire:model='cityUpdate' type="text" name="cityUpdate" id="cityUpdate"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full"
                        placeholder="Masukan Kota (Opsional)">
                    @error('cityUpdate') <span class="text-red-500 text-xs">{{ $message }} @enderror
                </div>
                <div>
                    <label for="stateUpdate" class="block mb-2 text-sm font-medium text-gray-900">Provinsi</label>
                    <input wire:model='stateUpdate' type="text" name="stateUpdate" id="stateUpdate"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full"
                        placeholder="Masukan Provinsi (Opsional)">
                    @error('stateUpdate') <span class="text-red-500 text-xs">{{ $message }} @enderror
                </div>
                <div>
                    <label for="countryUpdate" class="block mb-2 text-sm font-medium text-gray-900">Negara</label>
                    <input wire:model='countryUpdate' type="text" name="countryUpdate" id="countryUpdate"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full"
                        placeholder="Masukan Negara (Opsional)">
                    @error('countryUpdate') <span class="text-red-500 text-xs">{{ $message }} @enderror
                </div>
                <div>
                    <label for="zipUpdate" class="block mb-2 text-sm font-medium text-gray-900">Kode Pos</label>
                    <input wire:model='zipUpdate' type="text" name="zipUpdate" id="zipUpdate"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full"
                        placeholder="Masukan Kode Pos (Opsional)">
                    @error('zipUpdate') <span class="text-red-500 text-xs">{{ $message }} @enderror
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


{{-- Script modal tambah --}}
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


{{-- Script modal edit --}}
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


{{-- Script modal delete --}}
<script>
    Livewire.on('showDeleteConfirmation', (event) => {
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Supplier ini akan dihapus?",
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
    
</script>

</div>
