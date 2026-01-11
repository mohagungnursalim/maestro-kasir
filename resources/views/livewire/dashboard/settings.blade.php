<div>

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <p class="text-2xl font-bold mb-4"><span class="text-transparent bg-clip-text bg-gradient-to-r to-green-600 from-gray-500">Pengaturan</span> <span class="underline underline-offset-3 decoration-8 decoration-green-500">Toko</span></p>
        <hr class="mb-3">
        @if (session('success'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 1500)" x-show="show"
            x-transition.duration.500ms
            class="flex items-center p-4 mb-4 text-sm text-green-800 border border-green-300 rounded-lg bg-green-50 dark:bg-gray-800 dark:text-green-400 dark:border-green-800"
            role="alert">
            <svg class="shrink-0 inline w-4 h-4 me-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                fill="currentColor" viewBox="0 0 20 20">
                <path
                    d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 1 1 1 1v4h1a1 1 0 1 1 0 2Z" />
            </svg>
            <span class="sr-only">Info</span>
            <div>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
        </div>
        @endif



        <form wire:submit.prevent="updateSettings" class="space-y-4">
            <!-- Nama Toko -->
            <div>
                <label for="store_name" class="block text-sm font-medium">Nama Toko</label>
                <input type="text" id="store_name" name="store_name" wire:model="store_name" class="w-full border-gray-300 border rounded-md p-2"
                    required>
                @error('store_name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <!-- Alamat Toko -->
            <div>
                <label for="store_address" class="block text-sm font-medium">Alamat Toko</label>
                <input type="text" id="store_address" name="store_address" wire:model="store_address" class="w-full border-gray-300 border rounded-md p-2"
                    required>
                @error('store_address') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <!-- Nomor Telepon Toko -->
            <div>
                <label for="store_phone" class="block text-sm font-medium">Nomor Telepon</label>
                <input type="text" id="store_phone"  name="store_phone" wire:model="store_phone" class="w-full border-gray-300 border rounded-md p-2"
                    required>
                @error('store_phone') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <!-- Tampilan Logo yang Ada -->
            @if ($store_logo)
            <img src="{{ asset($store_logo) }}" alt="Logo"
                class="h-24 p-2 border-2 border-gray-400 border-dashed rounded-lg">
            @endif

            <!-- Upload Logo -->
            <div class="sm:col-span-2" x-data="imageUploader()">
                <label for="new_logo" class="block mb-2 text-sm font-medium text-gray-900">Unggah Logo</label>
                <input wire:model="new_logo" type="file" id="new_logo" name="new_logo"
                    class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none"
                    accept=".png,.jpg,.jpeg,.svg" x-on:change="previewImage($event)" x-ref="fileInput">
                <p class="mt-1 text-sm text-gray-500">SVG, PNG, JPG or GIF (Max: 5MB)</p>

                <!-- Progress Container -->
                <div x-show="uploading" x-transition:enter="transition ease-out duration-300" class="w-full">
                    <div class="flex justify-between mb-1">
                        <span class="text-sm font-medium text-gray-700">Upload Progress</span>
                        <span class="text-sm font-medium text-gray-700" x-text="`${progress}%`"></span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                        <div class="h-2.5 rounded-full transition-all duration-150 ease-out"
                            :style="`width: ${progress}%`" :class="{
                                    'bg-blue-600': progress < 60,
                                    'bg-yellow-400': progress >= 60 && progress < 80,
                                    'bg-green-500': progress >= 80
                                }"></div>
                    </div>
                </div>

                <!-- Preview Gambar -->
                <template x-if="imagePreview || existingImage">
                    <div class="flex justify-center items-center mt-2">
                        <div class="flex flex-col items-center">
                            <img :src="imagePreview || '{{ asset('storage') }}/' + existingImage" alt="Preview"
                                class="max-w-full h-64 rounded-lg object-cover border-2 border-gray-300 border-dashed">

                            <p class="mt-4 text-sm text-gray-500">
                                <span class="font-semibold"
                                    x-text="fileName || (existingImage ? existingImage.split('/').pop() : '')"></span>
                                <span x-text="fileSize"></span>
                            </p>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Pengaturan Struk -->
            <p class="text-2xl font-bold"><span class="text-transparent bg-clip-text bg-gradient-to-r to-blue-600 from-gray-500">Pengaturan</span> <span class="underline underline-offset-3 decoration-8 decoration-blue-500"> Struk</span></p>
            <hr class="mb-3">
            <div>
                <label for="store_footer" class="block text-sm font-medium">Footer Struk Order</label>
                <textarea id="store_footer" name="store_footer" wire:model="store_footer" class="w-full border-gray-300 border rounded-md p-2 resize-none"
                    rows="3" required></textarea>
                @error('store_footer')
                <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <!-- Pengaturan Pajak -->
            <p class="text-2xl font-bold"><span class="text-transparent bg-clip-text bg-gradient-to-r to-red-600 from-gray-500">Pengaturan</span> <span class="underline underline-offset-3 decoration-8 decoration-red-500">Pajak PB1</span></p>
            <hr class="mb-3">
            <div id="tax-setting-container">

                <!-- Checkbox untuk mengaktifkan pajak -->
                <label class="inline-flex flex-col items-start cursor-pointer">
                    <span class="text-sm font-medium text-gray-900 mb-2">Gunakan Pajak?</span>
                    <div class="flex items-center">
                        <input type="checkbox" id="isTaxCheckbox" class="sr-only peer"
                        wire:model="is_tax" onchange="toggleTaxInput()"
                        @checked($settings->is_tax)>

                        <div
                            class="relative w-11 h-6 bg-gray-200 rounded-full peer-focus:ring-4 peer-focus:ring-purple-300 peer-checked:after:translate-x-full 
                                   peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:start-[2px] after:bg-white after:border-gray-300 
                                   after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-red-600">
                        </div>
                    </div>
                </label>
            
                <!-- Input pajak -->
                <div id="taxInputGroup" class="mt-3 @if(!$settings->is_tax) hidden @endif">
                    <label for="tax" class="block text-sm font-medium">Tarif Pajak (%)</label>
                    <input type="number" id="tax" name="tax" wire:model="tax"
                        class="w-full border-gray-300 border rounded-md p-2" min="0" max="100" required>
                    <span id="taxError" class="text-red-500 text-sm hidden">Masukkan tarif pajak yang valid.</span>
                </div>
            </div>

            <!-- Pengaturan Produk -->
            <p class="text-2xl font-bold"><span class="text-transparent bg-clip-text bg-gradient-to-r to-purple-600 from-gray-500">Pengaturan</span> <span class="underline underline-offset-3 decoration-8 decoration-purple-500">Produk</span></p>
            <hr class="mb-3">
            <div id="supplier-setting-container">

                <label class="inline-flex flex-col items-start cursor-pointer">
                    <span class="text-sm font-medium text-gray-900 mb-2">Aktifkan Supplier pada Produk?</span>
                    <div class="flex items-center">
                        <input type="checkbox" id="isSupplierCheckbox"
                               class="sr-only peer"
                               wire:model="is_supplier"
                               @checked($settings->is_supplier)>
                
                        <div
                            class="relative w-11 h-6 bg-gray-200 rounded-full peer-focus:ring-4 peer-focus:ring-purple-300 peer-checked:after:translate-x-full 
                                   peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:start-[2px] after:bg-white after:border-gray-300 
                                   after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600">
                        </div>
                    </div>
                </label>
                
            </div>
                        


            <!-- Tombol Simpan -->
            <div class="flex items-center justify-center">
                <button wire:loading.remove wire:target="updateSettings" type="submit"
                    class="text-white bg-gradient-to-r from-purple-500 to-pink-500 hover:bg-gradient-to-l focus:ring-4 focus:outline-none focus:ring-purple-200 font-medium rounded-lg text-sm px-5 py-2.5 text-center me-2">
                    Simpan
                </button>
                <button disabled wire:loading wire:target="updateSettings"
                    class="disabled:opacity-50 disabled:cursor-not-allowed text-white bg-gradient-to-r from-purple-500 to-pink-500 hover:bg-gradient-to-l focus:ring-4 focus:outline-none focus:ring-purple-200 font-medium rounded-lg text-sm px-5 py-2.5 text-center me-2">
                    Menyimpan..
                    <svg class="inline w-5 h-5 text-white animate-spin ml-2" xmlns="http://www.w3.org/2000/svg"
                        fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                        </circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8v4a4 4 0 100 8v4a8 8 0 01-8-8z"></path>
                    </svg>
                </button>
            </div>
        </form>

    </div>




    {{-- Alpine FileUpload Form Update --}}
    <script>
        function imageUploader() {
            return {
                imagePreview: null,
                fileName: '',
                fileSize: '',
                error: null,
                uploading: false,
                progress: 0,
                existingImage: null, // Bind gambar yang sudah ada
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

            };
        }

    </script>

</div>
