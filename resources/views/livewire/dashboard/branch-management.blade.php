<div>
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <p class="text-2xl font-bold mb-4"><span class="text-transparent bg-clip-text bg-gradient-to-r to-green-600 from-gray-500">Manajemen</span> <span class="underline underline-offset-3 decoration-8 decoration-green-500">Cabang</span></p>
        <hr class="mb-3">

        <!-- Form Input Cabang -->
        <form wire:submit.prevent="{{ $isEditMode ? 'update' : 'store' }}" class="space-y-4 mb-8">
            
            <!-- Input Nama -->
            <div>
                <label for="name" class="block text-sm font-medium">Nama Cabang</label>
                <input id="name" name="name" wire:model="name" type="text" placeholder="Nama Cabang" autocomplete="off"
                    class="w-full border-gray-300 border rounded-md p-2 focus:border-blue-500 focus:ring-blue-500" required>
                @error('name')<span class="text-red-500 text-sm mt-1">{{ $message }}</span>@enderror
            </div>

            <!-- Input No HP -->
            <div>
                <label for="phone" class="block text-sm font-medium">Nomor Telepon/WA</label>
                <input id="phone" name="phone" wire:model="phone" type="text" placeholder="No Telepon/WA" autocomplete="off"
                    class="w-full border-gray-300 border rounded-md p-2 focus:border-blue-500 focus:ring-blue-500">
                @error('phone')<span class="text-red-500 text-sm mt-1">{{ $message }}</span>@enderror
            </div>

            <!-- Input Alamat -->
            <div>
                <label for="address" class="block text-sm font-medium">Alamat Cabang</label>
                <textarea id="address" name="address" wire:model="address" placeholder="Alamat Cabang" rows="3"
                    class="w-full border-gray-300 border rounded-md p-2 focus:border-blue-500 focus:ring-blue-500 resize-none"></textarea>
                @error('address')<span class="text-red-500 text-sm mt-1">{{ $message }}</span>@enderror
            </div>

            <!-- Toggle Switch is_active -->
            <div class="mb-6">
                <label class="inline-flex flex-col items-start cursor-pointer">
                    <span class="text-sm font-medium text-gray-900 mb-2">Status Operasional Cabang Aktif?</span>
                    <div class="flex items-center">
                        <input type="checkbox" wire:model="is_active" class="sr-only peer">
                        <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                    </div>
                </label>
                @error('is_active')<span class="text-red-500 text-sm mt-1">{{ $message }}</span>@enderror
            </div>
            
            <hr class="mb-3">

            <!-- Pengaturan Gambar (Logo) -->
            <div class="mt-4">
                <label class="block mb-2 text-sm font-medium text-gray-900">Upload Logo Cabang</label>
                <div class="flex items-center space-x-4">
                    @if ($store_logo && !$new_logo)
                    <img src="{{ asset($store_logo) }}" alt="Logo" class="h-16 w-16 object-cover border-2 border-gray-400 border-dashed rounded-lg">
                    @elseif ($new_logo)
                    <img src="{{ $new_logo->temporaryUrl() }}" alt="Preview" class="h-16 w-16 object-cover border-2 border-gray-400 border-dashed rounded-lg">
                    @endif
                    <div class="w-full">
                        <input wire:model="new_logo" type="file" class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none" accept=".png,.jpg,.jpeg,.svg">
                        <p class="mt-1 text-sm text-gray-500">SVG, PNG, JPG (Maks: 5MB)</p>
                        @error('new_logo') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <!-- Pengaturan Struk -->
            <p class="text-xl font-bold mt-6"><span class="text-transparent bg-clip-text bg-gradient-to-r to-blue-600 from-gray-500">Pengaturan</span> <span class="underline underline-offset-3 decoration-8 decoration-blue-500"> Struk</span></p>
            <div>
                <label for="store_footer" class="block text-sm font-medium">Footer Struk Order</label>
                <textarea id="store_footer" name="store_footer" wire:model="store_footer" class="w-full border-gray-300 border rounded-md p-2 resize-none" rows="2" required></textarea>
                @error('store_footer')<span class="text-red-500 text-sm">{{ $message }}</span>@enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Pengaturan Pajak -->
                <div>
                    <p class="text-xl font-bold mt-2"><span class="text-transparent bg-clip-text bg-gradient-to-r to-red-600 from-gray-500">Pajak</span> <span class="underline underline-offset-3 decoration-8 decoration-red-500">PB1</span></p>
                    <label class="inline-flex flex-col items-start cursor-pointer mt-3">
                        <span class="text-sm font-medium text-gray-900 mb-2">Gunakan Pajak?</span>
                        <div class="flex items-center">
                            <input type="checkbox" id="isTaxCheckbox" class="sr-only peer" wire:model.live="is_tax">
                            <div class="relative w-11 h-6 bg-gray-200 rounded-full peer-focus:ring-4 peer-focus:ring-purple-300 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-red-600"></div>
                        </div>
                    </label>
                    
                    @if($is_tax)
                    <div class="mt-3 transition-opacity">
                        <label for="tax" class="block text-sm font-medium">Tarif Pajak (%)</label>
                        <input type="number" id="tax" name="tax" wire:model="tax" class="w-full border-gray-300 border rounded-md p-2" min="0" max="100" required>
                    </div>
                    @endif
                </div>

                <!-- Pengaturan Supplier -->
                <div>
                    <p class="text-xl font-bold mt-2"><span class="text-transparent bg-clip-text bg-gradient-to-r to-purple-600 from-gray-500">Fitur</span> <span class="underline underline-offset-3 decoration-8 decoration-purple-500">Produk</span></p>
                    <label class="inline-flex flex-col items-start cursor-pointer mt-3">
                        <span class="text-sm font-medium text-gray-900 mb-2">Aktifkan Supplier pada Produk?</span>
                        <div class="flex items-center">
                            <input type="checkbox" id="isSupplierCheckbox" class="sr-only peer" wire:model="is_supplier">
                            <div class="relative w-11 h-6 bg-gray-200 rounded-full peer-focus:ring-4 peer-focus:ring-purple-300 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600"></div>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Tombol Action -->
            <div class="flex items-center mt-6">
                <button type="submit" wire:loading.attr="disabled"
                    class="text-white bg-gradient-to-r from-purple-500 to-pink-500 hover:bg-gradient-to-l focus:ring-4 focus:outline-none focus:ring-purple-200 font-medium rounded-lg text-sm px-5 py-2.5 text-center me-2">
                    <span wire:loading.remove wire:target="store, update">
                        {{ $isEditMode ? 'Simpan Perubahan' : 'Tambah Cabang' }}
                    </span>
                    <span wire:loading wire:target="store, update">
                        Menyimpan..
                        <svg class="inline w-5 h-5 text-white animate-spin ml-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 100 8v4a8 8 0 01-8-8z"></path></svg>
                    </span>
                </button>

                @if($isEditMode)
                <button type="button" wire:click="resetInput" wire:loading.attr="disabled"
                    class="text-gray-900 bg-white border border-gray-300 focus:outline-none hover:bg-gray-100 focus:ring-4 focus:ring-gray-100 font-medium rounded-lg text-sm px-5 py-2.5 me-2">
                    Batal Edit
                </button>
                @endif
            </div>
        </form>


    <!-- Tabel Cabang -->
    <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
        <table class="w-full text-sm text-left rtl:text-right text-gray-500">
            <thead class="text-xs text-white uppercase bg-gray-500">
                <tr>
                    <th scope="col" class="px-6 py-3">Logo</th>
                    <th scope="col" class="px-6 py-3">Info Cabang</th>
                    <th scope="col" class="px-6 py-3">Pengaturan Lanjutan</th>
                    <th scope="col" class="px-6 py-3 text-center">Status</th>
                    <th scope="col" class="px-6 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($branches as $branch)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4">
                        @if($branch->setting && $branch->setting->store_logo)
                            <img src="{{ asset($branch->setting->store_logo) }}" alt="Logo" class="w-12 h-12 object-cover rounded-lg border">
                        @else
                            <div class="w-12 h-12 bg-gray-200 rounded-lg border flex items-center justify-center text-gray-400 text-xs text-center"><i class="fas fa-image text-lg"></i></div>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <div class="font-bold text-gray-900 text-base mb-1">{{ $branch->name }}</div>
                        <div class="text-xs text-gray-500 mb-1"><i class="fas fa-phone mr-1"></i> {{ $branch->phone ?? '-' }}</div>
                        <div class="text-xs text-gray-500"><i class="fas fa-map-marker-alt mr-1"></i> {{ $branch->address ?? '-' }}</div>
                    </td>
                    <td class="px-6 py-4">
                        @if($branch->setting)
                            <div class="flex flex-col gap-2">
                                <div>
                                    @if($branch->setting->is_tax)
                                        <span class="bg-yellow-100 text-yellow-800 text-xs font-semibold px-2 py-0.5 rounded border border-yellow-300">Pajak ({{ $branch->setting->tax }}%)</span>
                                    @else
                                        <span class="bg-gray-100 text-gray-800 text-xs font-semibold px-2 py-0.5 rounded border border-gray-300">Tanpa Pajak</span>
                                    @endif
                                </div>
                                <div>
                                    @if($branch->setting->is_supplier)
                                        <span class="bg-purple-100 text-purple-800 text-xs font-semibold px-2 py-0.5 rounded border border-purple-300">Supplier Aktif</span>
                                    @endif
                                </div>
                            </div>
                        @else
                            <span class="text-xs text-gray-400 italic">Belum disetting</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-center">
                        @if($branch->is_active)
                            <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded border border-green-400 shadow-sm">Aktif</span>
                        @else
                            <span class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded border border-red-400 shadow-sm">Non-Aktif</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-center">
                        <div class="flex items-center justify-center gap-2">
                            <button wire:click="edit({{ $branch->id }})" class="bg-blue-100 text-blue-800 hover:bg-blue-200 font-medium rounded-md text-xs px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-300 focus:z-10 transition">Edit</button>
                            <button wire:click="deleteConfirmation({{ $branch->id }})" class="bg-red-100 text-red-800 hover:bg-red-200 font-medium rounded-md text-xs px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-red-300 focus:z-10 transition">Hapus</button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-8 text-center text-gray-500 bg-gray-50">
                        <i class="fas fa-store-slash text-3xl mb-3 text-gray-300 block"></i>
                        Belum ada cabang yang terdaftar.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Script Khusus Alert --}}
    <script>
        document.addEventListener('livewire:navigated', () => {
            Livewire.on('showDeleteConfirmation', () => {
                Swal.fire({
                    title: 'Hapus Cabang?',
                    text: 'Semua produk dan transaksi di cabang ini akan terpengaruh/ikon akan error. Lanjutkan?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Livewire.dispatch('deleteConfirmed');
                    }
                });
            });

            Livewire.on('showErrorCannotDeleteLastBranch', () => {
                Swal.fire({
                    title: 'Gagal',
                    text: 'Anda tidak dapat menghapus satu-satunya cabang utama yang tersisa!',
                    icon: 'error',
                });
            });
        });

        const showToastMessage = (title) => {
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
            Toast.fire({ icon: "success", title: title });
        };

        Livewire.on('branchStored', () => showToastMessage("Cabang berhasil ditambahkan!"));
        Livewire.on('branchUpdated', () => showToastMessage("Cabang berhasil diperbarui!"));
        Livewire.on('branchDeleted', () => showToastMessage("Cabang berhasil dihapus!"));
    </script>
</div>
